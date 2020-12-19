<?php

/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2019-02-18
 * Time: 23:31
 */
abstract class kod_web_restApi
{
    static public $instance;//声明一个静态变量（保存在类中唯一的一个实例）
    static protected $action = '';

    /**
     * getInstance
     * 截获post请求
     *
     * @access public
     * @since 1.0
     * @return $this
     */
    static public function getInstance($action = '')
    {
        if ($action !== '') {
            self::$action = $action;
        }
        $temp = get_called_class();
        if (!self::$instance) self::$instance = new $temp();
        return self::$instance;
    }

    public static $checkList = [];

    public function newCheck($funcCallback)
    {
        self::$checkList[] = array($funcCallback);
    }

    public function step($funcCallback)
    {
        self::$checkList[count(self::$checkList) - 1][] = $funcCallback;
    }

    /**
     * post
     * 截获post请求
     *
     * @access public
     * @since 1.0
     * @return $this
     */
    public static function post($where = array())
    {
        self::getInstance()->newCheck(function () use ($where) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (empty($_POST)) {
                    $data = json_decode(file_get_contents("php://input"), true);
                    if (!empty($data)) {
                        $_POST = $data;
                    }
                }
                $_POST = array_merge($_POST, $_GET);//后面盖住前面
                if (is_array($where)) {
                    if (count($where) > 0) {
                        foreach ($where as $k => $v) {
                            if ($_POST[$k] !== $v) {
                                return false;
                            }
                        }
                    }
                } else {
                    if ($where !== self::$action) {
                        return false;
                    }
                }
                return $_POST;
            } else {
                return false;
            }
        });
        return self::getInstance();
    }

    /**
     * delete
     * 截获delete请求
     *
     * @access public
     * @since 1.0
     * @return $this
     */
    public static function delete($where = array())
    {
        self::getinstance()->newCheck(function () use ($where) {
            if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                $data = json_decode(file_get_contents("php://input"), true);
                if ($data === null) {
                    $data = array();
                }
                $data = array_merge($_GET, $data);//后面盖住前面
                if (is_array($where)) {
                    if (count($where) > 0) {
                        foreach ($where as $k => $v) {
                            if ($data[$k] !== $v) {
                                return false;
                            }
                        }
                    }
                } else {
                    if ($where !== self::$action) {
                        return false;
                    }
                }
                return $data;
            } else {
                return false;
            }
        });
        return self::getinstance();
    }

    /**
     * get
     * 截获get请求
     *
     * @access public
     * @since 1.0
     * @return $this
     */
    public static function get($where = array())
    {
        $class = get_called_class();
        $class::getInstance()->newCheck(function () use ($where) {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                if (is_array($where)) {
                    if (count($where) > 0) {
                        foreach ($where as $k => $v) {
                            if ($_GET[$k] !== $v) {
                                return false;
                            }
                        }
                    }
                } else {
                    if ($where !== self::$action) {
                        return false;
                    }
                }
                return $_GET;
            } else {
                return false;
            }
        });
        return self::getInstance();
    }

    /**
     * put
     * 截获put请求
     *
     * @access public
     * @since 1.0
     * @return $this
     */
    public static function put($where = array())
    {
        self::getInstance()->newCheck(function () use ($where) {
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                if (strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') > -1) {
                    $temp = explode('--------------------------', $_SERVER['CONTENT_TYPE']);
                    $temp = explode('----------------------------' . $temp[1], file_get_contents("php://input"));
                    $temp = array_slice($temp, 1, count($temp) - 2);
                    $data = array();
                    foreach ($temp as $item) {
                        preg_match("/Content-Disposition: form-data; name=\"(\S+)\"\s+([\S|\s]+)\s/", $item, $match);
                        $data[$match[1]] = $match[2];
                    }
                } else if ($_SERVER['CONTENT_TYPE'] === 'application/x-www-form-urlencoded') {
                    parse_str(file_get_contents("php://input"), $data);
                } else {
                    $data = json_decode(file_get_contents("php://input"), true);
                }
                if (empty($data)) {
                    $data = array();
                }
                $data = array_merge($_GET, $data);//后面盖住前面
                if (is_array($where)) {
                    if (count($where) > 0) {
                        foreach ($where as $k => $v) {
                            if ($data[$k] !== $v) {
                                return false;
                            }
                        }
                    }
                } else {
                    if ($where !== self::$action) {
                        return false;
                    }
                }
                return $data;
            } else {
                return false;
            }
        });
        return self::getInstance();
    }

    /**
     * run
     * 处理收操的请求
     *
     * @access public
     * @return $this
     */
    public function run(Closure $callback)
    {
        $this->step(function ($params) use ($callback) {
            $method = new \ReflectionFunction($callback);
            $args = array();
            foreach ($method->getParameters() as $param) {

                $name = $param->getName();//获取参数名
                //$params　参数存在于　传入的参数之中
                if (!is_array($params)) {
                    throw new Exception('每一个step闭包函数必须返回数组');
                    exit;
                }
                if (array_key_exists($name, $params)) {  //有传参数，　按传入的参数
                    //反射方法 测试该参数是否为数组类型
                    if ($param->isArray()) {
                        $args[] = (array)$params[$name];
                    } else if ($param->getType() && $param->getType()->getName() === 'int') {
                        $args[] = intval($params[$name]);
                    } elseif (!is_array($params[$name])) { //参数不是数组类型　如 name = lemon
                        $args[] = $params[$name];
                    } else {
                        kod_web_httpError::set(400, "error:需要指定闭包函数的参数{$name}的数据类型");
                        throw new Exception("error:需要指定闭包函数的参数{$name}的数据类型");
                    }
                    unset($params[$name]);
                } elseif ($param->isDefaultValueAvailable()) {  //没有传参数，　检测时候参数有默认值
                    //getDefaultValue 获取参数默认值
                    $args[] = $param->getDefaultValue();
                } else {
                    kod_web_httpError::set(400, '必须传入参数' . $name);
                }
            }
            if (is_callable($callback)) {
                $bindCallback = Closure::bind($callback, $this);
                return call_user_func_array($bindCallback, $args);
            }
        });
        return $this;
    }

    /**
     * fetch
     * 使用模版引擎渲染
     *
     * @access public
     * @return $this
     */
    public function fetch($tpl)
    {
        $this->step(function ($datas) use ($tpl) {
            //创建一个网页对象
            $page = new kod_web_page();
            if (is_array($datas)) {
                foreach ($datas as $k => $v) {
                    $page->$k = $v;
                }
            }
            $page->fetch($tpl);
        });
        return $this;
    }

    public function lastExit()
    {
        foreach (self::$checkList as $k => $steps) {
            $params = array();
            foreach ($steps as $step) {
                $params = $step($params);
                if ($params === false) {
                    break;
                }
            }
            if ($params === false) {
                continue;
            } else {
                if (is_array($params)) {
                    echo json_encode($params);
                    exit;
                } else {
                    if ($params === true) {
                        echo 'true';
                    } else if ($params === true) {
                        echo 'false';
                    } else {
                        echo $params;
                    }
                    exit;
                }
            }
        }
        kod_web_httpError::set(404, '未找到服务,请检查地址和http类型');
    }
}

/*
demo
// 获取列表
kod_web_restApi::get(array('action' => 'list'))
    ->run(function ($id) {
        return lesson::create()->getLessonListByCouseId($id);
    });

kod_web_restApi::get(array('action' => 'list'))
    ->run(function ($id) {
        return lesson::create()->getLessonListByCouseId($id);
    });

// 获取详情
kod_web_restApi::get(array('action' => 'detail'))
    ->run(function ($id) {
        return lesson::create()->getSesson($id);
    });

// 获取修改
kod_web_restApi::post()
    ->isLogin()
    ->run(function ($id, $content) {
        lesson::create()->update(array(
            'id' => $id
        ), array(
            'content' => $content
        ));
    });

// 新增课程
kod_web_restApi::put()
    ->isLogin()
    ->run(function ($title, $content, $course, $status) {
        return lesson::create()->insert(array(
            'title' => $title,
            'content' => $content,
            'course' => intval($course),
            'status' => intval($status)
        ));
    });
 */
