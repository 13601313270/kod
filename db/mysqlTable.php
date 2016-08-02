<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 16/5/9
 * Time: 下午8:10
 */
abstract class kod_db_mysqlTable{
    protected static $dbName = KOD_COMMENT_MYSQLDB;
    protected static $tableName;
    protected static $key = '';//当存在主键的时候，可以根据主键调用一些快捷函数
    protected static $keyDataType = 'int';//主键的数据类型int varchar
    protected static $charset = KOD_COMMENT_MYSQLDB_CHARSET;

    private static $getListSelectColumn = array();
    private static function getDbHandle(){
        return new kod_db_mysqlDB(self::$dbName,self::$charset);
    }
    //通过主键获取
    public static function getByKey($valueOfKey,$select='*'){
        if(empty(self::$key)){return false;}
        if($select=='*'){
            $selectArr = '*';
        }else{
            if(is_array($select)){
                $selectArr = $select;
            }else{
                $selectArr = explode(",",$select);
            }
            if(!empty(self::$getListSelectColumn)){
                $selectArr = array_intersect($selectArr,self::$getListSelectColumn);
            }
        }
        if(self::$keyDataType=='int'){
            $sql = "select ".implode(',',$selectArr)." from ".self::$tableName." where ".self::$key."=".$valueOfKey;
        }else{
            $sql = "select ".implode(',',$selectArr)." from ".self::$tableName." where ".self::$key.'="'.mysql_real_escape_string($valueOfKey,$con).'"';
        }
        $con = self::getDbHandle();
        $con->runsql($sql,'default',$con->getConnect());
    }
}