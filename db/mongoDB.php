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
            $manager = new MongoDB\Driver\Manager("mongodb://" . $this->dbWriteUser . ":" . $this->dbWritePass . "@" . KOD_MONGODB_HOST);
            if (count($step['select']) > 0) {
                $step['options']['projection'] = [];
                foreach ($step['select'] as $v) {
                    $step['options']['projection'][$v] = true;
                }
            }

            if (!empty($step['sort'])) {
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
                foreach ($data as $k => $single) {
                    if (is_array($single) && $single['_id'] && $single['_id']['oid']) {
                        $data[$k]['_id'] = $single['_id']['oid'];
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

    public function exist()
    {
        $this->bind('where', function ($arr) {
            $arr['select'] = ['_id'];
            $arr['options']['limit'] = 1;
            return $arr;
        });
        return count($this->action()) > 0;
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
        $this->bind('where', function ($data) {
            $data['options']['limit'] = 1;
            return $data;
        });
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

    static function listCollections($db)
    {
        $manager = new MongoDB\Driver\Manager("mongodb://" . KOD_MONGODB_USER . ":" . KOD_MONGODB_PASSWORD . "@" . KOD_MONGODB_HOST);
        $command = new MongoDB\Driver\Command(array(
            'listCollections' => 1
        ));

        $cursor = $manager->executeCommand($db, $command);

        $result = array();
        foreach ($cursor as $v) {
            $result[] = $v->name;
        }
        return $result;
    }

    public function insert($params)
    {
        $manager = new MongoDB\Driver\Manager("mongodb://" . $this->dbWriteUser . ":" . $this->dbWritePass . "@" . KOD_MONGODB_HOST);
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->insert($params);

        try {
            $result = $manager->executeBulkWrite($this->dbName . '.' . $this->tableName, $bulk);
            $this->breakAll();
            return $result->getInsertedCount();
        } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
            var_dump($e->getWriteResult()->getWriteErrors());
        }
    }

    public function update($params)
    {
        $this->bind('where', function ($step) use ($params) {
            if (!empty($step['filter'])) {
                $manager = new MongoDB\Driver\Manager("mongodb://" . $this->dbWriteUser . ":" . $this->dbWritePass . "@" . KOD_MONGODB_HOST);
                $bulk = new MongoDB\Driver\BulkWrite();
                $bulk->update($step['filter'], $params);
//            $bulk->delete(array('product_id' => 125));

                $result = $manager->executeBulkWrite($this->dbName . '.' . $this->tableName, $bulk);
                try {
                    $this->breakAll();
                    return $result->getModifiedCount();
//                    var_dump($result->getDeletedCount());
                } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
                    var_dump($e->getWriteResult()->getWriteErrors());
                }
            }
        });
        return $this->action();
    }

    public function deleteById($id)
    {
        $manager = new MongoDB\Driver\Manager("mongodb://" . $this->dbWriteUser . ":" . $this->dbWritePass . "@" . KOD_MONGODB_HOST);
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->delete(array('_id' => new \MongoDB\BSON\ObjectId($id)));

        $result = $manager->executeBulkWrite($this->dbName . '.' . $this->tableName, $bulk);
        return $result->getDeletedCount();
    }

    final function delete()
    {
        $this->bind('where', function ($step) {
            if (!empty($step['filter'])) {
                $manager = new MongoDB\Driver\Manager("mongodb://" . $this->dbWriteUser . ":" . $this->dbWritePass . "@" . KOD_MONGODB_HOST);
                $bulk = new MongoDB\Driver\BulkWrite();
                $bulk->delete($step['filter']);
                $result = $manager->executeBulkWrite($this->dbName . '.' . $this->tableName, $bulk);
                try {
                    $this->breakAll();
                    return $result->getDeletedCount();
                } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
                    var_dump($e->getWriteResult()->getWriteErrors());
                }
            } else {
                throw new Exception('正在尝试删除所有数据');
            }
        });
        return $this->action();
    }
}
