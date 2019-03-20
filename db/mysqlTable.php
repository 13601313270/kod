<?php

/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 16/5/9
 * Time: 下午8:10
 */

class kod_db_mysqlTable extends kod_tool_lifeCycle
{
    protected $dbName = '';
    protected $tableName = '';
    protected $key = '';
    protected $keyDataType = 'int';

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
                    if (in_array($item[1], ['=', '>', '<', '!=', '>=', '<='])) {
                        if (is_numeric($item[2])) {
                            $returnSqlArr[] = $item[0] . $item[1] . $item[2];
                        } else {
                            $returnSqlArr[] = $item[0] . $item[1] . '?';
                            $returnSlotData[] = $item[2];
                        }
                    } elseif ($item[1] === 'in') {
                        if (array_keys(array_keys($item[2])) === array_keys($item[2])) {
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
            //需要查询的掉垂直分表的其他表字段
//            $needToSelectVerticalTable = array_intersect($selectArr, array_keys($this->verticalTable));
            //排除掉垂直分表的其他表字段
//            $selectArr = array_diff($selectArr, array_keys($this->verticalTable));
//            $sql .= "select " . implode(",", $selectArr) . " from " . $this->getTableName();
//            $whereSql = $this->getWhereSqlByArr($arr);
            return array(
                'select' => $selectArr,
            );
        });
        $this->bind('join', function ($data) {
            $data['join'] = ['', []];
            return $data;
        });
        $this->bind('sql', function ($arr) {
            $sql = 'select ' . implode(',', $arr['select']) . ' from ' . $this->tableName;
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
            return kod_db_mysqlDB::create($this->dbName)->sql($step[0], $step[1]);
        });
    }

    public function where($arr)
    {
        $this->bind('select', function ($data) use ($arr) {
            if (is_string($arr)) {
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
                    $whereParams['and'][] = [$k, '=', $v];
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

    protected function _join($joinType, $table, $select = '*')
    {
        $tableKey = 'table' . rand(10000, 90000);
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
                $select[$k] = $tableKey . '.' . $item;
            }
            $arr["select"] = array_merge($arr["select"], $select);
            return $arr;
        });
        $this->bind('join', function ($data) use ($joinType, $table, $tableKey) {
            if (gettype($table) === 'object' && $table instanceof kod_db_mysqlTable) {
                $class = get_class($table);
                $key = array_keys($this->joinList[$class])[0];
                $key2 = array_values($this->joinList[$class])[0];
                $childSql = $table->sql()->get();
                $joinTableName = $childSql[0];
                if ($this->dbName !== $table->dbName) {
                    $joinTableName = $table->dbName . '.' . $joinTableName;
                }
                $data['join'][0] .= ' ' . $joinType . ' (' . $joinTableName . ') as ' . $tableKey . ' on ' . $tableKey . '.' . $key2 . '=' . $this->getTableName() . '.' . $key;
                $data['join'][1] = array_merge($data['join'][1], $childSql[1]);
            } else {
                $tableObj = new $table();
                $joinTableName = $tableObj->getTableName();
                if ($this->dbName !== $tableObj->dbName) {
                    $joinTableName = $tableObj->dbName . '.' . $joinTableName;
                }
                $class = $table;
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
     * @since 1.0
     * @return $this
     */
    public function leftJoin($table, $select = '*')
    {
        return $this->_join('left join', $table, $select);
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
     * @since 1.0
     * @return $this
     */
    public function fullJoin($table, $select = '*')
    {
        return $this->_join('full join', $table, $select);
    }

    /**
     * join
     * 函数的含义说明
     *
     * @access public
     * @since 1.0
     * @return $this
     */
    public function join($table, $select = '*')
    {
        return $this->_join('join', $table, $select);
    }

    private $limit_ = '';

    public function limit($limit)
    {
        $this->limit_ = $limit;
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
            $allKeys = array_column($data, 'course');
            if ($this->foreignKey[$foreignKey]) {
                $dbObject = new $this->foreignKey[$foreignKey];
                $outerArr = $dbObject->onlyColumn($select)->getByKeys($allKeys);
                $temp = array();
                foreach ($outerArr as $item) {
                    $temp[$item['id']] = $item;
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

    public function select($list)
    {
        $this->bind('select', function ($arr) use ($list) {
            if (is_string($list)) {
                $arr['select'] = explode(',', $list);
            } else if (count($arr['select']) === 1 && $arr['select'][0] === '*') {
                $arr['select'] = $list;
            } else {
                $arr['select'] = array_intersect($arr['select'], $list);
            }
            return $arr;
        });
        return $this;
    }

    public function first($column = '')
    {
        if ($column) {
            $this->select($column);
        }
        $returnData = $this->action();
        $data = current($returnData);
        if ($column) {
            return $data[$column];
        } else {
            return $data;
        }
    }

    public function get()
    {
        return $this->action();
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

    public function getByKey($id)
    {
        $key = $this->key;
        $this->where(array(
            $key => $id
        ));
        return ($this->action())[0];
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
            "insert into " . $this->getTableName() . " (" . implode(",", array_keys($params)) . ") VALUES(:" . implode(",:", array_keys($params)) . ")",
            $params
        );
        return kod_db_mysqlDB::create($this->dbName)->sql($sql[0], $sql[1]);
    }

    public function update($where, $params)
    {
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
                    $paramsTemp[] = $k . "=?";
                    $excuteArr[] = $v;
                } else {
                    $paramsTemp[] = $v;
                }
            }
            $sql .= implode(",", $paramsTemp);
        }
        $paramsTemp2 = array();
        if (gettype($where) == "string") {
            $lastCreateWhereStr = $where;
        } else {
            foreach ($where as $k => $v) {
                $paramsTemp2[] = $k . '=?';
                $excuteArr[] = $v;
            }
            $lastCreateWhereStr = implode(' and ', $paramsTemp2);
        }
        $sql .= " where " . $lastCreateWhereStr;
        return kod_db_mysqlDB::create($this->dbName)->sql($sql, $excuteArr);
    }
}

/*
$mddObj->leftJoin('mdd_feature_info', $item['feature'])
    ->join(
        mdd_service_info_new::create()->where($serviceWhere)
    )
    ->limit($per * ($page - 1) . ',' . $per)
    ->get();
 * */
