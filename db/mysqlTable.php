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

    public $stage = ['array', 'sql', 'afterSql', 'data'];

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
        $this->bind('array', function ($arr) {
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
                'from' => $this->tableName,
            );
        });
        $this->bind('sql', function ($arr) {
            $arr['where'] = $this->getWhereStr($arr['where']);
            $sql = 'select ' . implode(',', $arr['select']) . ' from ' . $arr['from'];
            if ($arr['where'] && !empty($arr['where'][0])) {
                $sql .= ' where ' . $arr['where'][0];
            }
            return [$sql, $arr['where'][1]];
        });

        $this->bind('afterSql', function ($step) {
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
        $this->bind('array', function ($data) use ($arr) {
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
        $this->bind('array', function ($arr) {
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
        if (gettype($table) === 'object' && $table instanceof kod_db_mysqlTable) {
            $this->bind('array', function ($arr) use ($joinType, $select, $tableKey) {
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
            $this->bind('sql', function ($sql) use ($joinType, $table, $tableKey) {
                $key = array_keys($this->joinList[get_class($table)])[0];
                $key2 = array_values($this->joinList[get_class($table)])[0];
                $childSql = $table->sql()->get();
                $sql[0] .= ' ' . $joinType . ' (' . $childSql[0] . ') as ' . $tableKey . ' on ' . $tableKey . '.' . $key2 . '=' . $this->getTableName() . '.' . $key;
                $sql[1] = array_merge($sql[1], $childSql[1]);
                return $sql;
            });
        } else {
            $tableObj = new $table();
            $joinTableName = $tableObj->getTableName();
            $this->bind('array', function ($arr) use ($joinType, $select, $tableKey) {
                if ($select !== '*') {
                    foreach ($arr["select"] as $k => $item) {
                        if (!strpos($item, '.')) {
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
                }
                return $arr;
            });
            $this->bind('sql', function ($sql) use ($joinType, $joinTableName, $table, $tableKey) {
                $key = array_keys($this->joinList[$table])[0];
                $key2 = array_values($this->joinList[$table])[0];
                $sql[0] .= ' ' . $joinType . ' ' . $joinTableName . ' as ' . $tableKey . ' on ' . $tableKey . '.' . $key2 . '=' . $this->getTableName() . '.' . $key;
                return $sql;
            });
        }
        return $this;
    }

    public function leftJoin($table, $select = '*')
    {
        return $this->_join('left join', $table, $select);
    }

    public function fullJoin($table, $select = '*')
    {
        return $this->_join('full join', $table, $select);
    }

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
        $this->bind('array', function ($arr) use ($list) {
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

    public function get()
    {
        return $this->action();
    }

    public function getList($params)
    {
        $this->where($params);
        return $this->action();
    }

    public function getByKey($id)
    {
        $key = $this->key;
        $this->where(array(
            $key => $id
        ));
        return ($this->action())[0];
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
