<?php

/**
 * Created by PhpStorm.
 * User: wanghaoran
 * Date: 2019-07-24
 * Time: 16:38
 */
class kod_db_mongoDB extends kod_tool_lifeCycle
{
    protected $dbName = '';
    protected $tableName = '';

//    protected $key = '';
//    protected $keyDataType = 'int';

    protected $dbWriteUser = KOD_MONGODB_USER;      // 数据库写账号
    protected $dbWritePass = KOD_MONGODB_PASSWORD;  // 数据库写密码
    protected $dbReadUser = KOD_MONGODB_USER;       // 数据库读账号
    protected $dbReadPass = KOD_MONGODB_PASSWORD;   // 数据库读密码

//    protected $foreignKey = array();//外键，可以通过设置获取语法糖
//    private static $cacheData = array();//缓存的mysql查询结果
//    protected $joinList = array(); // 垂直分表


    private function object_array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = $this->object_array($value);
            }
        }
        return $array;
    }

    public function __construct()
    {
        $this->bind('where', function () {
            return array(
                'filter' => array(),
                'options' => array(),
                'select' => array(),
                'sort' => array()
            );
        });
        $this->bind('data', function ($step) {
//            print_r($step);
            $manager = new MongoDB\Driver\Manager("mongodb://" . $this->dbWriteUser . ":" . $this->dbWritePass . "@dds-2zef5960fed0f8f41813-pub.mongodb.rds.aliyuncs.com:3717,dds-2zef5960fed0f8f42943-pub.mongodb.rds.aliyuncs.com:3717/admin?replicaSet=mgset-5320935");
            if (count($step['select']) > 0) {
                $step['options']['projection'] = [];
                foreach ($step['select'] as $v) {
                    $step['options']['projection'][$v] = true;
                }
            }

            if(!empty($step['sort'])){
                $step['options']['sort'] = $step['sort'];
            }

            if (in_array('count(*)', $step['select'])) {
                $command = new MongoDB\Driver\Command(array(
                    'count' => $this->tableName,
                    'query' => $step['filter']
                ));
                $cursor = $manager->executeCommand($this->dbName, $command);
            } else {
                $query = new MongoDB\Driver\Query($step['filter'], $step['options']);
                $cursor = $manager->executeQuery($this->dbName . '.' . $this->tableName, $query);
            }
            $result = array();
            foreach ($cursor as $v) {
                $result[] = $this->object_array($v);
            }
            return $result;
        });
        $this->bind('modified', function ($data) {
            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    if (is_array($v) && $v['_id'] && $v['_id']['oid']) {
                        $data[$k]['_id'] = $v['_id']['oid'];
                    }
                }
            }
            return $data;
        });
    }

    public $stage = ['where', 'data', 'modified'];

    public function where($arr)
    {
        $this->bind('where', function ($data) use ($arr) {
            $data['filter'] = $arr;
            return $data;
        });
        return $this;
    }

    public function count()
    {
        $this->bind('where', function ($arr) {
            $arr['select'] = array('count(*)');
            return $arr;
        });
        return $this->action()[0]['n'];
    }

    public function select($list)
    {
        $this->bind('where', function ($data) use ($list) {

            if (is_string($list)) {
                $data['select'] = explode(',', $list);
            }
//            else if (count($data['select']) === 1 && $data['select'][0] === '*') {
//                $data['select'] = $list;
//            }
            else {
                // 外层再次调用select，取交集
                $data['select'] = array_intersect($data['select'], $list);
            }
            return $data;
        });
        return $this;
    }

    public function sort($type, $sortType = -1)
    {
        $this->bind('where', function ($data) use ($type, $sortType) {
            $data['sort'] = array(
                $type => $sortType
            );
            return $data;
        });
        return $this;
    }

    public function limit($count, $count2 = '')
    {
        $this->bind('where', function ($data) use ($count, $count2) {
            if ($count2) {
                $data['options']['limit'] = $count2;
                $data['options']['skip'] = $count;
            } else {
                $data['options']['limit'] = $count;
            }
            return $data;
        });
        return $this;
    }

    public function get()
    {
        return $this->action();
    }

    public function first($column = '')
    {
        if ($column) {
            $this->select($column);
        }
        $returnData = $this->action();
        $data = current($returnData);
        if ($column) {
            if (preg_match('/ as (.*)/', $column, $match)) {
                return $data[$match[1]];
            } else {
                return $data[$column];
            }
        } else {
            return $data;
        }
    }

    public function getByKey($id)
    {
        $key = $this->key;
        $this->where(array(
            $key => $id
        ));
        $result = $this->action();
        if (count($result) > 0) {
            return $result[0];
        } else {
            return null;
        }
    }

    /**
     * create
     * 函数的含义说明
     *
     * @access public
     * @since 1.0
     * @return $this
     */
    static function create()
    {
        $temp = get_called_class();
        return new $temp();
    }
}
