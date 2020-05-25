<?php

/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 16/5/9
 * Time: 下午8:10
 */

class kod_db_mysqlTable extends kod_tool_lifeCycle
{
    protected $dbName = KOD_COMMENT_MYSQLDB;
    protected $tableName = '';
    protected $key = '';
    protected $keyDataType = 'int';

    protected $dbWriteUser = KOD_MYSQL_USER;      // 数据库写账号
    protected $dbWritePass = KOD_MYSQL_PASSWORD;  // 数据库写密码
    protected $dbReadUser = KOD_MYSQL_USER;       // 数据库读账号
    protected $dbReadPass = KOD_MYSQL_PASSWORD;   // 数据库读密码

    protected $foreignKey = array();//外键，可以通过设置获取语法糖
    private static $cacheData = array();//缓存的mysql查询结果
    protected $joinList = array(); // 垂直分表

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

    public function getTableName()
    {
        return $this->tableName;
    }

    // 获取主键
    public function getKeyColumnName()
    {
        return $this->key;
    }

    public $stage = ['select', 'join', 'sql', 'afterSql', 'data'];

    private function getWhereStr($arr)
    {
        // 必须是一个只有and/or为唯一key的数组
        if (is_string($arr)) {
            return [$arr, array()];
        }
        $mergeType = '';
        if ($arr['and']) {
            $mergeType = 'and';
        } else if ($arr['or']) {
            $mergeType = 'or';
        }
        $returnSqlArr = [];
        $returnSlotData = [];
        if ($arr && $arr[$mergeType]) {
            foreach ($arr[$mergeType] as $item) {
                if (array_keys(array_keys($item)) === array_keys($item)) {
                    if (in_array($item[1], ['=', '>', '<', '!=', '>=', '<=', 'like'])) {
                        if ($item[1] === 'like') {
                            $action = ' ' . $item[1] . ' ';
                        } else {
                            $action = $item[1];
                        }
                        if (is_numeric($item[2])) {
                            $returnSqlArr[] = $item[0] . $action . $item[2];
                        } else {
                            if (in_array($item[0], ['desc', 'table', 'default', 'count', 'replace'])) {
                                $returnSqlArr[] = '`' . $item[0] . '`' . $action . '?';
                            } else {
                                $returnSqlArr[] = $item[0] . $action . '?';
                            }
                            $returnSlotData[] = $item[2];
                        }
                    } elseif ($item[1] === 'in') {
                        if (gettype($item[2]) === 'object' && $item[2] instanceof kod_db_mysqlTable) {
                            $dbName = $item[2]->dbName;
                            $item[2]->bind('select', function ($data) use ($dbName) {
                                $data['from'] = $dbName . '.' . $data['from'];
                                return $data;
                            });
                            $childSql = $item[2]->sql()->get();
                            $returnSqlArr[] = $item[0] . ' ' . $item[1] . ' (' . $childSql[0] . ')';
                            $returnSlotData = array_merge($returnSlotData, $childSql[1]);
                        } else {
                            $item[2] = array_values($item[2]);
                            $temp = array();
                            foreach ($item[2] as $enum) {
                                if (is_numeric($enum)) {
                                    $temp[] = $enum;
                                } else {
                                    $temp[] = '?';
                                    $returnSlotData[] = $enum;
                                }
                            }
                            $returnSqlArr[] = $item[0] . ' ' . $item[1] . ' (' . implode(',', $temp) . ')';
                        }
                    }
                } else {
                    $temp = $this->getWhereStr($item);
                    $returnSqlArr[] = '(' . $temp[0] . ')';
                    $returnSlotData = array_merge($returnSlotData, $temp[1]);
                }
            }
        }
        return [implode(' ' . $mergeType . ' ', $returnSqlArr), $returnSlotData];
    }

    public function __construct()
    {
        $this->bind('select', function ($arr) {
            // 初始化select
            if (empty($arr["select"])) {
                $selectArr = array("*");
            } else {
                if (is_array($arr["select"])) {
                    $selectArr = $arr["select"];
                } else {
                    $selectArr = explode(",", $arr["select"]);
                }
            }
            return array(
                'select' => $selectArr,
                'from' => $this->tableName
            );
        });
        $this->bind('join', function ($data) {
            $data['join'] = ['', []];
            return $data;
        });
        $this->bind('sql', function ($arr) {
            foreach ($arr['select'] as $k => $v) {
                if (in_array($v, ['desc', 'table', 'default', 'count', 'replace'])) {
                    $arr['select'][$k] = '`' . $v . '`';
                }
            }
            $sql = 'select ' . implode(',', $arr['select']) . ' from ' . $arr['from'];
            if ($arr['join']) {
                $sql .= $arr['join'][0];
            }
            $arr['where'] = $this->getWhereStr($arr['where']);
            if ($arr['where'] && !empty($arr['where'][0])) {
                $sql .= ' where ' . $arr['where'][0];
            }
            return [$sql, array_merge($arr['join'][1], $arr['where'][1])];
        });

        $this->bind('afterSql', function ($step) {
            if ($this->groupBy) {
                $step[0] .= ' group by ' . $this->groupBy;
            }
            if ($this->orderBy) {
                $step[0] .= ' order by ' . $this->orderBy;
            }
            if ($this->limit_) {
                $step[0] .= ' limit ' . $this->limit_;
            }
            return $step;
        });
        $this->bind('data', function ($step) {
            return kod_db_mysqlDB::create($this->dbName)
                ->setUserAndPass($this->dbReadUser, $this->dbReadPass)
                ->sql($step[0], $step[1]);
        });
    }

    public function where($arr)
    {
        $this->bind('select', function ($data) use ($arr) {
            if (is_string($arr)) {
                $whereParams = $arr;
            } else if ($arr['and'] || $arr['or']) {
                $whereParams = $arr;
            } else if (array_keys($arr) === range(0, count($arr) - 1)) {
                $whereParams = array(
                    'and' => array()
                );
//                foreach ($arr as $v) {
//                    $whereParams['and'][] = $v;
//                }
            } else {
                $whereParams = array(
                    'and' => array()
                );
                foreach ($arr as $k => $v) {
                    $list = explode(' ', $k);
                    if (in_array($list[1], array('like', 'in'))) {
                        $whereParams['and'][] = [$list[0], $list[1], $v];
                    } else if (in_array(substr($k, -2), array('>=', '<=', '<>'))) {
                        $whereParams['and'][] = [substr($k, 0, -2), substr($k, -2), $v];
                    } else if (in_array(substr($k, -1), array('>', '<'))) {
                        $whereParams['and'][] = [substr($k, 0, -1), substr($k, -1), $v];
                    } else {
                        $whereParams['and'][] = [$k, '=', $v];
                    }
                }
            }
            $data['where'] = $whereParams;
            return $data;
        });

//        格式
//        array(
//            'and' => [
//                ['id', '=', 0],
//                ['c', '>', "c"],
//                array(
//                    'or' => [
//                        ['time', '>', "2018-10-10"],
//                        ['a', '!=', "a"],
//                        ['b', 'in', ['bin', 2, 'bin3', 4]]
//                    ]
//                ),
//                ['d', '>', "d"],
//                ['b', 'in', [1, 2, 3, 4]]
//            ]
//        );
        return $this;
    }

    /**
     * in
     * 设置in的条件
     *
     * @access public
     * @param mixed $columnKey 键
     * @param mixed $columnArr 值数组
     * @since 1.0
     * @return $this
     */
    public function in($columnKey, $columnArr)
    {
        $this->bind('select', function ($data) use ($columnKey, $columnArr) {
            if (!isset($data['where'])) {
                $data['where'] = array(
                    'and' => array()
                );
            }
            $data['where']['and'][] = [$columnKey, 'in', $columnArr];
            return $data;
        });
        return $this;
    }

    public function getCount()
    {
        $this->bind('select', function ($arr) {
            $arr['select'] = array('count(*) as count');
            return $arr;
        });
        return $this;
    }

    public function cacheInPv()
    {
        $cacheSql = '';
        $this->bind('sql', function ($sql) use ($cacheSql) {
            if (isset(self::$cacheData[$sql])) {
                $cacheSql = $sql;
                $this->breakAll();
                return self::$cacheData[$sql];
            }
            return $sql;
        });
        $this->bind('data', function ($returnData) use ($cacheSql) {
            if (!isset(self::$cacheData[$cacheSql])) {
                self::$cacheData[$cacheSql] = $returnData;
                $this->breakAll();
            }
            return $returnData;
        });
        return $this;
    }

    protected function _join($joinType, $table, $select = '*', $linkArr = '')
    {
        if (is_string($table)) {
            $tableObj = explode(' ', $table);
            $table = $tableObj[0];
        }
        if (is_string($table) && count($tableObj) > 2 && $tableObj[1] === 'as') {
            $tableKey = $tableObj[2];
        } else {
            $tableKey = 'table' . rand(10000, 90000);
        }
        $this->bind('select', function ($arr) use ($joinType, $select, $tableKey) {
            foreach ($arr["select"] as $k => $item) {
                if (!strpos($item, '.') && strpos($item, '(') === false) {
                    $arr["select"][$k] = $this->getTableName() . '.' . $item;
                }
            }
            // 初始化select
            if (!is_array($select)) {
                $select = explode(',', $select);
            }
            foreach ($select as $k => $item) {
                if (strpos($item, '(') === false) {
                    $select[$k] = $tableKey . '.' . $item;
                }
            }
            $arr["select"] = array_merge($arr["select"], $select);
            return $arr;
        });
        $this->bind('join', function ($data) use ($joinType, $table, $tableKey, $linkArr) {
            if (gettype($table) === 'object' && $table instanceof kod_db_mysqlTable) {
                $tableClone = clone $table;
                // 为啥要clone？忘了！！
                $class = get_class($tableClone);
                if (is_array($linkArr)) {
                    $key = array_keys($linkArr)[0];
                    $key2 = array_values($linkArr)[0];
                } else {
                    $key = array_keys($this->joinList[$class])[0];
                    $key2 = array_values($this->joinList[$class])[0];
                }


                if ($this->dbName !== $tableClone->dbName) {
                    $tableClone->bind('select', function ($data) use ($tableClone) {
                        $data['from'] = $tableClone->dbName . '.' . $data['from'];
                        return $data;
                    });
                }
                $childSql = $tableClone->sql()->get();

                $joinTableName = $childSql[0];
//                if ($this->dbName !== $tableClone->dbName) {
//                    $joinTableName = $tableClone->dbName . '.' . $joinTableName;
//                }
                $data['join'][0] .= ' ' . $joinType . ' (' . $joinTableName . ') as ' . $tableKey . ' on ' . $tableKey . '.' . $key2 . '=' . $this->getTableName() . '.' . $key;
                $data['join'][1] = array_merge($data['join'][1], $childSql[1]);
            } else {
                $tableObj = new $table();
                $joinTableName = $tableObj->getTableName();
                // 不能只是判断调用者是否一样，因为存在 c.a join (b.a join b.b)，这时候内部的join是同一个库，但是因为外层库不一样，所以必须加上库名
//                if ($this->dbName !== $tableObj->dbName) {
                $joinTableName = $tableObj->dbName . '.' . $joinTableName;
//                }
                $class = $table;
                if (!isset($this->joinList[$class])) {
                    throw new Exception('类【' . get_called_class() . '】的joinList中没有【' . $class . '】类的配置');
                }
                $key = array_keys($this->joinList[$class])[0];
                $key2 = array_values($this->joinList[$class])[0];
                $data['join'][0] .= ' ' . $joinType . ' ' . $joinTableName . ' as ' . $tableKey . ' on ' . $tableKey . '.' . $key2 . '=' . $this->getTableName() . '.' . $key;
            }
            return $data;

        });
        return $this;
    }

    /**
     * leftJoin
     * 函数的含义说明
     *
     * @access public
     * @param mixed $table 要连接的表的对应类名
     * @param mixed $select 连接后提取的数字
     * @param mixed $linkArr 连接的表的别名
     * @since 1.0
     * @return $this
     */
    public function leftJoin($table, $select = '*', $linkArr = '')
    {
        return $this->_join('left join', $table, $select, $linkArr);
    }

    public function sqlAfter($addSql)
    {
        $this->bind('sql', function ($sql) use ($addSql) {
            $sql[0] .= ' ' . $addSql;
            return $sql;
        });
        return $this;
    }

    /**
     * fullJoin
     * 函数的含义说明
     *
     * @access public
     * @param mixed $table 要连接的表的对应类名
     * @param mixed $select 连接后提取的数字
     * @param mixed $linkArr 连接的表的别名
     * @since 1.0
     * @return $this
     */
    public function fullJoin($table, $select = '*', $linkArr = '')
    {
        return $this->_join('full join', $table, $select, $linkArr);
    }

    /**
     * join
     * 函数的含义说明
     *
     * @access public
     * @param mixed $table 要连接的表的对应类名
     * @param mixed $select 连接后提取的数字
     * @param mixed $linkArr 连接的表的别名
     * @since 1.0
     * @return $this
     */
    public function join($table, $select = '*', $linkArr = '')
    {
        return $this->_join('join', $table, $select, $linkArr);
    }

    private $limit_ = '';

    public function limit($limit, $limit2 = '')
    {
        if ($limit2) {
            $this->limit_ = intval($limit) . ',' . intval($limit2);
        } else {
            $this->limit_ = intval($limit);
        }

        return $this;
    }

    private $orderBy = '';

    public function orderBy($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    private $groupBy = '';

    public function groupBy($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    public function foreignData($foreignKey, $select = '*')
    {
        $this->bind('data', function ($data) use ($foreignKey, $select) {
            if ($this->foreignKey[$foreignKey]) {
                $dbObject = new $this->foreignKey[$foreignKey];
                $allKeys = array_column($data, $foreignKey);
                $temp = array();
                if (count($allKeys) > 0) {
                    $outerArr = $dbObject->select($select)->getByKeys($allKeys);
                    foreach ($outerArr as $item) {
                        $temp[$item['id']] = $item;
                    }
                }
                foreach ($data as $k => $v) {
                    $data[$k][$foreignKey] = $temp[$v[$foreignKey]];
                }
            } else {
                throw new Exception('没有这个外键');
                exit;
            }
            return $data;
        });
        return $this;
    }

    public function sql()
    {
        $this->bind('afterSql', function ($sql) {
            $this->breakAll();
            return $sql;
        });
        return $this;
    }

    public function echoSql()
    {
        $this->bind('afterSql', function ($sql) {
            $this->breakAll();
            print_r($sql);
            exit;
            return $sql;
        });
        return $this->action();
    }

    public function select($list)
    {
        $this->bind('select', function ($arr) use ($list) {
            if (is_string($list)) {
                $arr['select'] = explode(',', $list);
            } else if (count($arr['select']) === 1 && $arr['select'][0] === '*') {
                $arr['select'] = $list;
            } else {
                // 外层再次调用select，取交集
                $arr['select'] = array_intersect($arr['select'], $list);
            }
            return $arr;
        });
        return $this;
    }

    public function first($column = '')
    {
        $this->limit_ = 1;
        if ($column) {
            $this->select($column);
        }
        $returnData = $this->action();
        if (count($returnData) === 0) {
            return null;
        }
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

    public function get(Closure $mapFunction = null)
    {
        $data = $this->action();
        if ($mapFunction === null) {
            return $data;
        } else {
            foreach ($data as $k => $item) {
                $data[$k] = $mapFunction($item);
            }
            return $data;
        }
    }

    public function getList($params)
    {
        $this->where($params);
        return $this->action();
    }

    public function exist()
    {
        $data = $this->action();
        return !empty($data);
    }

    public function count()
    {
        $this->bind('select', function ($arr) {
            $arr['select'] = array('count(*) as count');
            return $arr;
        });
        $data = $this->action();
        if (is_string($data[0])) {
            return $data[0];
        } else {
            return $data[0]['count'];
        }
    }

    public function getByKey($id)
    {
        $key = $this->tableName . '.' . $this->key;
        $this->where(array(
            $key => $id
        ));
        return ($this->action())[0];
    }

    public function getByKeys($keys, $isObject = true)
    {
        $key = $this->tableName . '.' . $this->key . ' in';
        $this->where(array(
            $key => $keys
        ));
        $result = array();
        if ($isObject) {
            foreach ($this->action() as $val) {
                $result[$val[$this->key]] = $val;
            }
            return $result;
        } else {
            return $this->action();
        }
    }

    public function insert($params, $mysql_insert_id = true)
    {
        $verticalArr = array();
        if (!empty($this->verticalTable)) {
            foreach ($this->verticalTable as $k => $tableName) {
                if (isset($params[$k])) {
                    if (!isset($verticalArr[$tableName])) {
                        if ($mysql_insert_id == true) {
                            $verticalArr[$tableName] = array();
                        } else {
                            $verticalArr[$tableName] = array();
                            $verticalArr[$tableName][$this->key] = $params[$this->key];

                        }
                    }
                    $verticalArr[$tableName][$k] = $params[$k];
                    unset($params[$k]);
                }
            }
        }
        $sql = array(
            "insert into " . $this->getTableName() . " (`" . implode("`,`", array_keys($params)) . "`) VALUES(:" . implode(",:", array_keys($params)) . ")",
            $params
        );
        return kod_db_mysqlDB::create($this->dbName)->setUserAndPass($this->dbWriteUser, $this->dbWritePass)->lastInsertId()->sql($sql[0], $sql[1]);
    }

    public function update($params)
    {
        $this->bind('select', function ($step) use ($params) {
            $sql = "update " . $this->getTableName() . " set ";
            $sqlList = array();
            $excuteArr = array();
            if (gettype($params) == "string") {
                $sql .= $params;
            } else {
                $paramsTemp = array();
                $keyValueType = true;
                if (array_keys(array_keys($params)) === array_keys($params)) {//索引型数组
                    $keyValueType = false;
                }
                foreach ($params as $k => $v) {
                    if ($keyValueType) {
                        if (!empty($this->verticalTable) && !empty($this->verticalTable[$k])) {
                            $sqlList[$this->verticalTable[$k]][$k] = $v;
                            continue;
                        }
                        if (in_array($k, ['desc', 'table', 'default', 'count', 'replace'])) {
                            $paramsTemp[] = '`' . $k . '`' . "=?";
                        } else {
                            $paramsTemp[] = $k . "=?";
                        }
                        $excuteArr[] = $v;
                    } else {
                        $paramsTemp[] = $v;
                    }
                }
                $sql .= implode(",", $paramsTemp);
            }
            $paramsTemp2 = array();
            $where = array();
            if (gettype($where) == "string") {
            } else {
                foreach ($where as $k => $v) {
                    $paramsTemp2[] = $k . '=?';
                    $excuteArr[] = $v;
                }
            }
            $where = $this->getWhereStr($step['where']);
            $sql .= " where " . $where[0];
            $this->breakAll();
            return kod_db_mysqlDB::create($this->dbName)->setUserAndPass($this->dbWriteUser, $this->dbWritePass)->rowCount()->sql($sql, array_merge($excuteArr, $where[1]));
        });
        return $this->action();
    }

    public function deleteById($id)
    {
        $sql = "delete from " . $this->getTableName() . " where " . $this->key . "=?";
        return kod_db_mysqlDB::create($this->dbName)->setUserAndPass($this->dbWriteUser, $this->dbWritePass)->rowCount()->sql($sql, [$id]);
    }

    final function delete()
    {
        $this->bind('select', function ($step) {
            $where = $this->getWhereStr($step['where']);
            $sql = "delete from " . $step['from'] . " where " . $where[0];
            $this->breakAll();
            return kod_db_mysqlDB::create($this->dbName)->setUserAndPass($this->dbWriteUser, $this->dbWritePass)->rowCount()->sql($sql, $where[1]);
        });
        $this->action();
    }
}

/*
$mddObj->leftJoin('mdd_feature_info', $item['feature'])
    ->join(
        mdd_service_info::create()->where($serviceWhere)
    )
    ->limit($per * ($page - 1) . ',' . $per)
    ->get();
 * */
