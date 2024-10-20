<?php

/**
 * Created by PhpStorm.
 * User: kod
 * Date: 14-9-4
 * Time: 上午12:30
 */
abstract class kod_db_mysqlSingle
{
    protected $dbName = KOD_COMMENT_MYSQLDB;
    protected $tableName;
    protected $key = "";//当存在主键的时候，可以根据主键调用一些快捷函数
    protected $keyDataType = 'int';//主键的数据类型int varchar
    protected $charset = KOD_COMMENT_MYSQLDB_CHARSET;
    private $dbHandle;
    protected $foreignKey = array();//外键，可以通过设置获取语法糖
    private static $cacheData = array();//缓存的mysql查询结果
    protected $verticalTable = array();//纵向表字段

    function __construct()
    {
        if (empty($this->getTableName())) {
            throw new Exception("类【" . get_called_class() . "】需要设置属性【tableName】用来说明调用的表名称");
        }
        $this->dbHandle = new kod_db_mysqlDB($this->dbName, $this->charset);
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

    private $returnSql = false;

    public function sql()
    {
        $this->returnSql = true;
        return $this;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    //通过主键的值获取单条记录了
    public function getKeyColumnName()
    {
        return $this->key;
    }

    public function getByKey($valueOfKey, $select = '*')
    {
        if (empty($this->key)) {
            throw new Exception("类【" . get_called_class() . "】没有设置属性【key】，所以无法使用【getByKey】方法");
        }
        if ($select == '*') {
            if (empty($this->getListSelectColumn)) {
                $selectArr = array("*");
            } else {
                $selectArr = $this->getListSelectColumn;
                $this->getListSelectColumn = array();
            }
        } else {
            if (is_array($select)) {
                $selectArr = $select;
            } else {
                $selectArr = explode(",", $select);
            }
            if (!empty($this->getListSelectColumn)) {
                $selectArr = array_intersect($selectArr, $this->getListSelectColumn);
            }
            $this->getListSelectColumn = array();
        }
        $con = null;
        if ($this->keyDataType == 'int') {
            $sql = "select " . implode(',', $selectArr) . " from " . $this->getTableName() . " where " . $this->key . "=" . $valueOfKey;
        } else {
            $con = $this->dbHandle->getConnect();
//				$sql = "select ".implode(',',$selectArr)." from ".$this->getTableName()." where ".$this->key.'="'.mysql_real_escape_string($valueOfKey,$con).'"';
            $sql = "select " . implode(',', $selectArr) . " from " . $this->getTableName() . " where " . $this->key . '="' . $valueOfKey . '"';
        }
        if ($this->returnSql) {
            $this->returnSql = false;
            return $sql;
        } else {
            $returnData = $this->dbHandle->runsql($sql, 'default', $con);
            if (!empty($returnData)) {
                $returnData = current($returnData);
                //扩展外键数据
                foreach ($this->getForeignDataKeys as $kk => $vv) {
                    //echo $this->foreignKey[$kk]."\n";
                    if ($vv !== null) {
                        $dbObject = new $this->foreignKey[$kk];
                        if ($returnData[$kk]) {
                            $outerArr = $dbObject->onlyColumn($vv)->getByKey($returnData[$kk]);
                            if (!empty($outerArr)) {
                                $returnData[$kk] = $outerArr;
                            }
                        } else {
                            throw new Exception("使用外键时,必须保证数据表【" . $this->getTableName() . "】存在【" . $kk . "】字段，并且在使用onlyColumn方法时,参数必须包含【" . $kk . "】");
                        }
                    }
                }
            }
            $this->getForeignDataKeys = array();
            return $returnData;
        }
    }

    //通过一组主键的值获取一组记录
    function getByKeys($valuesOfKey)
    {
        $con = $this->dbHandle->getConnect();
        //$keyDataType
        if (is_array($valuesOfKey)) {
            if (empty($valuesOfKey)) {
                return array();
            } elseif ($this->keyDataType == 'int') {
                $valuesOfKey = implode(',', $valuesOfKey);
            } else {
                foreach ($valuesOfKey as $k => $v) {
                    $valuesOfKey[$k] = mysql_real_escape_string($v, $con);
                }
                $valuesOfKey = '"' . implode('","', $valuesOfKey) . '"';
            }
        }
        if (empty($this->getListSelectColumn)) {
            $selectArr = array("*");
        } else {
            $selectArr = $this->getListSelectColumn;
            $this->getListSelectColumn = array();
        }
        if (!empty($this->key)) {
            $returnData = $this->dbHandle->runsql("select " . implode(',', $selectArr) . " from " . $this->getTableName() . " where " . $this->key . " in(" . $valuesOfKey . ")", $con);
            foreach ($returnData as $k => $v) {
                foreach ($this->getForeignDataKeys as $kk => $vv) {
                    //echo $this->foreignKey[$kk]."\n";
                    if ($vv !== null) {
                        $dbObject = new $this->foreignKey[$kk];
                        $outerArr = $dbObject->onlyColumn($vv)->getByKey($returnData[$k][$kk]);
                        if (!empty($outerArr)) {
                            $returnData[$k][$kk] = $outerArr;
                        }
                    }
                }
            }
            $this->getForeignDataKeys = array();
            return $returnData;
        } else {
            throw new Exception("类【" . get_called_class() . "】没有设置属性【key】，所以无法使用【getByKey】方法");
        }
    }

    //减少mysql吞吐量，只获得需要的字段
    private $getListSelectColumn = array();

    public function getListSelectColumn()
    {
        return $this->getListSelectColumn;
    }

    public function clearListSelectColumn()
    {
        $this->getListSelectColumn = array();
    }

    /**
     * onlyColumn
     * 限制字段数量
     *
     * @access public
     * @param array $array 需要的字段
     * @param mixed $arg1 代表是否多次调用,可以修改,以最后一次调用为准
     * @return $this
     */
    final function onlyColumn($array, $isCanReWrite = true)
    {
        if ($isCanReWrite || empty($this->getListSelectColumn)) {
            if (!is_array($array)) {
                $array = explode(",", $array);
            }
            $this->getListSelectColumn = $array;
        }
        return $this;
    }

    //只返回count的语法糖
    private $getCountSelectColumn = false;

    public function getCount()
    {
        $this->getCountSelectColumn = true;
        return $this;
    }

    function getIsReturnCount()
    {
        return $this->getCountSelectColumn;
    }


    //自动关联外键的数据
    private $getForeignDataKeys = array();

    /**
     * foreignData
     * 自动扩展外键
     *
     * @access public
     * @param string $foreignKey 需要扩展额外数据的字段
     * @param array $select 需要扩展的外表字段
     * @return $this
     */
    public function foreignData($foreignKey, $select = '*')
    {
        $this->getForeignDataKeys[$foreignKey] = $select;
        return $this;
    }

    //在一个会话pv里，遇到多次相同的sql请求，只有第一次真正去mysql请求
    private $isCacheInPv = false;

    /**
     * cacheInPv
     * 是否请求内缓存
     *
     * @access public
     * @return $this
     */
    final function cacheInPv()
    {
        $this->isCacheInPv = true;
        return $this;
    }

    protected function getWhereSqlByArr($arr)
    {
        $foreignWhere = array();
        if (empty($arr["where"])) {
            $whereParams = array();
            if (array_keys($arr) === range(0, count($arr) - 1)) {
                foreach ($arr as $v) {
                    $whereParams[] = $v;
                }
            } else {
                foreach (array_diff(array_keys($arr), array("select", "where", "orderBy", "limit", "groupBy" ,"group")) as $v) {
                    if (is_numeric($arr[$v])) {
                        $whereParams[] = $v . '=' . $arr[$v];
                    } else {
                        $whereParams[] = $v . '="' . $arr[$v] . '"';
                    }
                }
            }
            if (!empty($whereParams)) {
                $thisTableSqlArr = implode(" and ", $whereParams);
            } else {
                $thisTableSqlArr = '';
            }
        } else {
            if (is_array($arr["where"])) {
                $whereParams = array();
                if (array_keys($arr["where"]) === range(0, count($arr["where"]) - 1)) {
                    foreach ($arr["where"] as $k => $v) {
                        if (!empty($this->verticalTable) && preg_match('/^(\S+) like /', $v, $match)) {
                            if (isset($this->verticalTable[$match[1]])) {
                                $foreignWhere[$this->verticalTable[$match[1]]][] = $v;
                                continue;
                            }
                        }
                        $whereParams[] = $v;
                    }
                } else {
                    foreach ($arr["where"] as $k => $v) {
                        if (is_numeric($v)) {
                            $whereParams[] = $k . '=' . $v;
                        } else {
                            $whereParams[] = $k . '="' . $v . '"';
                        }
                    }
                }
                $thisTableSqlArr = implode(" and ", $whereParams);

            } else {
                $thisTableSqlArr = $arr["where"];
            }
        }
        return array(
            'where' => $thisTableSqlArr,
            'foreignWhere' => $foreignWhere,
        );
    }

    private $joinArr = array();

    public function join($key)
    {
        $this->joinArr[] = $key;
        return $this;
    }

    public function getList($arr)
    {
        $needToSelectVerticalTable = array();
        if (empty($arr)) {
            $sql = "";
        } elseif (is_array($arr)) {
            $sql = "";
            if ($this->getCountSelectColumn == true) {
                $selectArr = array('count(*) as count');
            } else if (empty($arr["select"])) {
                if (empty($this->getListSelectColumn)) {
                    $selectArr = array("*");
                } else {
                    $selectArr = $this->getListSelectColumn;
                    $this->getListSelectColumn = array();
                }
            } else {
                if (is_array($arr["select"])) {
                    $selectArr = $arr["select"];
                } else {
                    $selectArr = explode(",", $arr["select"]);
                }
                if (!empty($this->getListSelectColumn)) {
                    $selectArr = array_intersect($selectArr, $this->getListSelectColumn);
                }
                $this->getListSelectColumn = array();
            }
            //需要查询的掉垂直分表的其他表字段
            $needToSelectVerticalTable = array_intersect($selectArr, array_keys($this->verticalTable));
            //排除掉垂直分表的其他表字段
            $selectArr = array_diff($selectArr, array_keys($this->verticalTable));
            $sql .= "select " . implode(",", $selectArr) . " from " . $this->getTableName();
            $whereSql = $this->getWhereSqlByArr($arr);

            if (empty($whereSql['where'])) {//组合的情况现在不支持

                if (!empty($this->verticalTable) && !empty($whereSql['foreignWhere'])) {
                    $con = $this->dbHandle->getConnect();
                    foreach ($whereSql['foreignWhere'] as $tableItem => $tableWheres) {
                        $ids = $this->dbHandle->runsql('select ' . $this->key . ' from ' . $tableItem . ' where ' . implode(" and ", $tableWheres));
                        $ids_ = array();
                        foreach ($ids as $v) {
                            $ids_[] = $v['id'];
                        }
                        if (count($ids_) > 0) {
                            if ($this->keyDataType == 'int') {
                                $whereSql['where'] = $this->key . ' in(' . implode(',', $ids_) . ')';
                            } else {
                                foreach ($ids_ as $k => $v) {
                                    $valuesOfKey[$k] = mysql_real_escape_string($v, $con);
                                }
                                $whereSql['where'] = $this->key . ' in("' . implode('","', $ids_) . '")';
                            }
                        }
                    }
                }
            }
            $whereSql = $whereSql['where'];
            if ($whereSql != '') {
                $sql .= " where " . $whereSql;
            }
            if (!empty($arr["groupBy"])) {
                $sql .= " group by " . $arr["groupBy"];
            }
            if (!empty($arr["orderBy"])) {
                $sql .= " order by " . $arr["orderBy"];
            }
            if (!empty($arr["limit"])) {
                $sql .= " limit " . $arr["limit"];
            }
        } else if (is_string($arr)) {
            if (strtolower(substr($arr, 0, 6)) == "select") {
                $sql = $arr;
            } else {
                if (!empty($this->getListSelectColumn)) {
                    $sql = "select " . implode(",", $this->getListSelectColumn) . " from " . $this->getTableName() . " where " . $arr;
                } else {
                    $sql = "select * from " . $this->getTableName() . " where " . $arr;
                }
            }
        } else {
            die("wrong");
        }
//        // 增加join数据
//        foreach ($this->joinArr as $joinKey) {
//            $dbObject = new $joinKey;
//            $temp = $this->join[$joinKey];
//            $sql .= ' LEFT JOIN ' . $dbObject->getTableName() . ' ON ';
//            foreach ($temp as $kk => $vv) {
//                $sql .= $kk . '=' . $vv;
//            }
//            var_dump($sql);
//        }
        //拼装mysql完成，执行sql
        $this->getListSelectColumn = array();
        if ($this->returnSql) {
            $this->returnSql = false;
            return $sql;
        } else {
            if ($this->isCacheInPv) {
                if (isset(self::$cacheData[$sql])) {
                    return self::$cacheData[$sql];
                }
            }
            if ($this->getCountSelectColumn == true) {
                $returnData = $this->dbHandle->runsql($sql);
                $this->getCountSelectColumn = false;
                return intval($returnData[0]['count']);
            } else {
                $returnData = $this->dbHandle->runsql($sql);
                //扩展外键数据
                foreach ($this->getForeignDataKeys as $kk => $vv) {
                    //echo $this->foreignKey[$kk]."\n";
                    if ($this->foreignKey[$kk]) {
                        $dbObject = new $this->foreignKey[$kk];
                    } else {
                        throw new Exception("如果使用外键【" . $kk . "】，必须在在【" . get_called_class() . "】的foreignKey属性里加上配置");
                    }
                    foreach ($returnData as $dataK => $oneData) {
                        if (!isset($oneData[$kk])) {
                            throw new Exception("如果使用外键【'" . $kk . "'】，对应字段必须在onlyColumn里包含");
                        }
                        $outerArr = $dbObject->onlyColumn($vv)->getByKey($oneData[$kk]);
                        if (!empty($outerArr)) {
                            $returnData[$dataK][$kk] = $outerArr;
                        }
                    }
                }
                if (!empty($needToSelectVerticalTable) && !empty($returnData)) {
                    $tableNeedSearch = array();
                    foreach ($needToSelectVerticalTable as $key_) {
                        if (empty($tableNeedSearch[$this->verticalTable[$key_]])) {
                            $tableNeedSearch[$this->verticalTable[$key_]] = array($this->key);
                        }
                        $tableNeedSearch[$this->verticalTable[$key_]][] = $key_;
                    }
                    $allMainTableId = array();
                    foreach ($returnData as $k => $v) {
                        if (!isset($v[$this->key])) {
                            throw new Exception("如果使用垂直分表功能,必须查询值加上主键");
                        }
                        $allMainTableId[] = $v[$this->key];
                    }
                    foreach ($tableNeedSearch as $tableName => $tableKeyArr) {
                        $thisTbaleData_ = array();
                        $thisTbaleData = $this->dbHandle->runsql('select ' . implode(',', $tableKeyArr) . ' from ' . $tableName . ' where ' . $this->key . ' in(' . implode(',', $allMainTableId) . ')');
                        foreach ($thisTbaleData as $itemVal) {
                            $thisTbaleData_[$itemVal[$this->key]] = $itemVal;
                        }
                    }
                    foreach ($returnData as $k => $v) {
                        $itemVal = $thisTbaleData_[$v[$this->key]];
                        foreach ($itemVal as $kk => $vv) {
                            if ($kk != $this->key) {
                                $returnData[$k][$kk] = $vv;
                            }
                        }
                    }
                }
                //扩展垂直分表数据
                if ($this->isCacheInPv) {
                    self::$cacheData[$sql] = $returnData;
                    $this->isCacheInPv = false;
                }
                $this->getForeignDataKeys = array();
                return $returnData;
            }
        }
    }

    //insert第二个函数传入default会造成错误,忘记了当初为什么不直接只能使用mysql_insert_id方式了
    public function insert($params, $mysql_insert_id = true)
    {
        $con = $this->dbHandle->getConnect();
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

        $sql = "insert into " . $this->getTableName() . " (" . implode(",", array_keys($params)) . ") VALUES('" . implode("','", array_values($params)) . "');";
        if ($this->returnSql) {
            $this->returnSql = false;
            return $sql;
        }
        $stmt = $con->prepare("insert into " . $this->getTableName() . " (" . implode(",", array_keys($params)) . ") VALUES(:" . implode(",:", array_keys($params)) . ")");
        $return = $stmt->execute($params);
        if ($mysql_insert_id) {
            if ($return !== false) {
                if ($this->keyDataType == 'int') {
                    $return = intval($con->lastInsertId());
                } else {
                    $return = $con->lastInsertId();
                }
            }
            if (!empty($verticalArr)) {
                foreach ($verticalArr as $k => $v) {
                    $verticalArr[$k][$this->key] = $return;
                }
            }
        }
        if (!empty($verticalArr)) {
            $con2 = $this->dbHandle->getConnect();
            foreach ($verticalArr as $tableName => $attrKeyVal) {
                $isIsset = $this->dbHandle->runsql('select count(*) as count from ' . $tableName . ' where ' . $this->key . '=' . $return, 'default', $con);
                if ($isIsset[0]['count'] == 0) {
                    $sql = "insert into " . $tableName . " (" . implode(",", array_keys($attrKeyVal)) . ") VALUES('" . implode("','", array_values($attrKeyVal)) . "');";
                    try {
                        $this->dbHandle->runsql($sql, 'default', $con2);
                    } catch (Exception $e) {
                        throw new Exception("执行此语句时出错" . $sql, 130);
                    }
                }
            }
        }
        if ($return === false) {
            $allColumnsTemp = $this->dbHandle->runsql("show columns from " . $this->getTableName());
            $allColumns = array();//表中所有的字段
            foreach ($allColumnsTemp as $v) {
                $allColumns[$v["Field"]] = $v;
            }
            //mysql关键词检查
            foreach ($allColumns as $k => $v) {
                if (in_array(strtolower($k), array('left', 'character'))) {
                    throw new Exception("表【" . $this->getTableName() . "】定义时，使用了不建议使用的关键词【" . $k . "】", 100);
                    //throw new Exception("表【".$this->getTableName()."】定义时，使用了不建议使用的关键词【".$k."】",100,$e);
                }
            }
            //检查相同主键的值是否已经存在
            foreach ($allColumns as $k => $v) {
                if ($v["Key"] == "PRI" && $v['Extra'] != 'auto_increment') {
                    $count = $this->dbHandle->runsql("select count(*) as count from " . $this->getTableName() . " where " . $v["Field"] . '="' . $params[$k] . '";');
                    if ($count[0]["count"] > 0) {
                        //throw new Exception("向表【".$this->getTableName()."】插入数据时，主键【".$k."】值为【".$params[$k]."】的数据已经存在",100,$e);
                        throw new Exception("向表【" . $this->getTableName() . "】插入数据时，主键【" . $k . "】值为【" . $params[$k] . "】的数据已经存在", 100);
                    }
                }
            }
            //检查数据类型错误
            foreach ($params as $k => $v) {
                if ($allColumns[$k]) {
                    $dataType = explode("(", $allColumns[$k]["Type"]);
                    //$dataTypeName = $dataType[0];
                    switch ($dataType[0]) {
                        case "int":
                        case "tinyint":
                            if (strval(intval($v)) != $v) {
                                throw new Exception("向表【" . $this->getTableName() . "】插入数据时,字段【" . $k . "】传入的值为【" . $v . "】，这个值必须是整形", 110);
                            }
                            break;
                    }
                } else {
                    //throw new Exception("向表【".$this->getTableName()."】插入数据时，传入的字段【".$k."】在表结构中不存在",111,$e);
                    throw new Exception("向表【" . $this->getTableName() . "】插入数据时，传入的字段【" . $k . "】在表结构中不存在", 111);
                }
            }
            //检查不能为空的字段是否全部传入
            $allMustWrite = array();
            foreach ($allColumns as $k => $v) {
                if ($v["Null"] == "NO") {
                    if (!isset($params[$k])) {
                        if ($v["Extra"] != "auto_increment") {
                            throw new Exception("向表【" . $this->getTableName() . "】插入数据时【" . $k . "】字段为必填字段", 120);
                        }
                    }
                    $allMustWrite[$k] = $v;
                }
            }

            throw new Exception("向表【" . $this->getTableName() . "】插入数据未知错误" . $e->getMessage(), 130, $e);
            //未完成
            $keys = $this->dbHandle->runsql("show create table " . $this->getTableName());
            print_r($keys);
            exit;
            exit;
            $err = new Exception("插入失败", 0);
            print_r($err);
        }
        return $return;
    }

    public function update($where, $params)
    {
        $con = $this->dbHandle->getConnect();
        $isUp = true;
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
            if (empty($paramsTemp)) {
                $isUp = false;
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
        if ($this->returnSql) {
            $this->returnSql = false;
            return $sql;
        }
        try {
            if ($isUp) {
                $stmt = $this->dbHandle->getConnect()->prepare($sql);
                $result = $stmt->execute($excuteArr);
            }
            if (!empty($sqlList)) {
                foreach ($sqlList as $k => $v) {
                    $resultIds = $this->dbHandle->runsql('select ' . $this->key . ' from ' . $this->getTableName() . ' where ' . $lastCreateWhereStr, 'default', $con);
                    if ($resultIds && count($resultIds) > 0) {
                        $id_ = array();
                        $con = $this->dbHandle->getConnect();
                        foreach ($resultIds as $vv) {
                            if ($this->keyDataType == 'int') {
                                $id_[] = $vv[$this->key];
                            } else {
                                $id_[] = mysql_real_escape_string($vv[$this->key], $con);
                            }

                        }
                        $sqlTemp = array();
                        foreach ($v as $kk => $vv) {
                            $sqlTemp[] = $kk . "='" . mysql_real_escape_string($vv, $con) . "'";
                        }
                        if ($this->keyDataType == 'int') {
                            $id_ = implode(',', $id_);
                        } else {
                            $id_ = '"' . implode('","', $id_) . '"';
                        }
                        $result2 = $this->dbHandle->runsql("update " . $k . " set " . implode(",", $sqlTemp) . " where " . $this->key . " in(" . $id_ . ")", 'default', $con);
                        if (!$isUp) {
                            return $result2;
                        }
                    }
                }
            }
            return $result;
        } catch (Exception $e) {
            throw new Exception("更新错误", 0, $e);
        }
    }

    public function deleteById($id)
    {
        $stmt = $this->dbHandle->getConnect()->prepare("delete from " . $this->getTableName() . " where " . $this->key . "=?");
        return $stmt->execute(array($id));
    }

    public function deleteByIds($ids)
    {
        if (empty($ids)) {
            return false;
        } elseif ($this->keyDataType == 'int') {
            $sql = "delete from " . $this->getTableName() . " where " . $this->key . " in(" . implode(',', $ids) . ")";
        } else {
            $con = $this->dbHandle->getConnect();
            foreach ($ids as $k => $v) {
                $ids[$k] = mysql_real_escape_string($v, $con);
            }
            $sql = "delete from " . $this->getTableName() . " where " . $this->key . " in(\"" . implode('","', $ids) . "\")";
        }
        if ($this->returnSql) {
            $this->returnSql = false;
            return $sql;
        } else {
            return $this->dbHandle->runsql($sql);
        }
    }

    final function delete($aWhereArr)
    {
        $whereSql = $this->getWhereSqlByArr($aWhereArr);
        $whereSql = $whereSql['where'];
        if ($whereSql != '') {
            $whereSql = ' where ' . $whereSql;
        }
        $sql = "delete from " . $this->getTableName() . $whereSql;
        if ($this->returnSql) {
            $this->returnSql = false;
            return $sql;
        } else {
            return $this->dbHandle->runsql($sql);
        }
    }

    function showCreateTable()
    {
        $tableInfo = $this->dbHandle->runsql("show create table " . $this->dbName . "." . $this->getTableName());
        if (preg_match('/CREATE TABLE [`|"].+?[`|"]\s*\(([\S|\s]*)\)/', $tableInfo[0]['Create Table'], $match)) {
            $tableInfo = explode(',', $match[1]);
            $option = array();
            foreach ($tableInfo as $k => $v) {
                if (preg_match("/[`|\"](\S+)[`|\"] (int|smallint|varchar|tinyint|char|bigint)\((\d+)\)( NOT NULL| DEFAULT NULL)?( DEFAULT '(\S+)'| AUTO_INCREMENT)?( COMMENT '(\S+)')?/", $v, $match)) {
                    $item = array(
                        'dataType' => $match[2],
                        'maxLength' => intval($match[3]),
                        'notNull' => !empty($match[4]) && $match[4] !== 'NOT NULL',
                        'title' => empty($match[8]) ? $match[1] : $match[8],
                    );
                    if ($match[6] !== null) {
                        $item['default'] = $match[6];
                    }
                    $option[$match[1]] = $item;
                    if (!empty($match[5]) && $match[5] == " AUTO_INCREMENT") {
                        $option[$match[1]]["AUTO_INCREMENT"] = true;
                    }
                } elseif (preg_match("/[`|\"](\S+)[`|\"] (text|datetime|date)( NOT NULL| DEFAULT NULL)?( DEFAULT '([^']+)')?( COMMENT '(\S+)')?/", $v, $match)) {
                    $item = array(
                        'dataType' => $match[2],
                        'notNull' => !empty($match[3]),
                        'title' => empty($match[7]) ? $match[1] : $match[7],
                    );
                    if ($match[5] !== null) {
                        $item['default'] = $match[5];
                    }
                    $option[$match[1]] = $item;
                } elseif (preg_match("/[`|\"](\S+)[`|\"] timestamp( NOT NULL| DEFAULT NULL)( DEFAULT CURRENT_TIMESTAMP)?( ON UPDATE CURRENT_TIMESTAMP)?( COMMENT '(\S+)')?/", $v, $match)) {
                    $option[$match[1]] = array(
                        "dataType" => 'timestamp',
                        "notNull" => !empty($match[2]),
                        "title" => "",
                    );
                } elseif (preg_match("/UNIQUE KEY [`|\"](\S+)[`|\"] \([`|\"]([^,]+)[`|\"]\)/", $v, $match)) {
                    $option[$match[2]]['unique'] = true;
                } elseif (preg_match("/PRIMARY KEY \([`|\"]([^,]+)[`|\"]\)/", $v, $match)) {
                    $option[$match[1]]['primarykey'] = true;
                }
            }
            return $option;
        } else {
            return array();
        }
    }

    //通过数组生成添加字段的sql
    function addColumnSql($arr)
    {
        foreach (array('notNull', 'primarykey', 'unique', 'listShowType') as $keyName) {
            if ($arr[$keyName] === true || $arr[$keyName] === 'true') {
                $arr[$keyName] = true;
            } else {
                $arr[$keyName] = false;
            }
        }
        return 'ALTER TABLE `' . $this->dbName . '`.`' . $this->getTableName() . '` ADD COLUMN `' . $arr['name'] .
            '` ' . $arr['dataType'] .
            ($arr['notNull'] ? ' NOT NULL' : '') .
            ($arr['default'] !== '' ? ' DEFAULT "' . $arr['default'] . '"' : '');
    }
}

//用例
/*
class baiduBack extends kod_db_mysqlSingle{
	protected $tableName = 'baiduBack';
	protected $key = 'keyWord';
	protected $keyDataType = 'varchar';
}
*/

//$result = $userHandle->getList("SELECT ticket from user where username='wanghaoran'");
//$result = $userHandle->getList("username='lvjinlong' and password='lvjinlong'");
//$result = $userHandle->getList("select"=>"username,ticket");
/*
$result = $userHandle->getList(array(
	"username"=>"lvjinlong",
	"password"=>"lvjinlong"
));
*/
/*
$result = $userHandle->getList(array(
	"where"=>"username='wanghaoran'"
));
*/
/*
$result = $userHandle->getList(array(
	"where"=>array(
		"username"=>"lvjinlong",
		"password"=>"lvjinlong",
	)
));
*/
/*
$result = $userHandle->getList(array(
	"select"=>"username,ticket",
	"where"=>array(
		"username"=>"wanghaoran",
		"password"=>"wanghaoran",
	),
	"orderBy"=>"username desc",
));
*/
/*
$result = $userHandle->getList(array(
	"select"=>"username",
	"username"=>"lvjinlong",
	"password"=>"lvjinlong",
	"limit"=>"0,5",
));
*/

//错误编码
