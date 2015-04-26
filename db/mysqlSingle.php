<?php
/**
 * Created by PhpStorm.
 * User: kod
 * Date: 14-9-4
 * Time: 上午12:30
 */
abstract class kod_db_mysqlSingle{
	protected $dbName = KOD_COMMENT_MYSQLDB;
	protected $tableName;
	protected $key = "";//当存在主键的时候，可以根据主键调用一些快捷函数
	private $dbHandle;
	function __construct(){
		$this->dbHandle = new kod_db_mysqlDB($this->dbName);
	}
	static function create(){
		$temp = get_called_class();
		return new $temp();
	}
	private $returnSql = false;
	public function sql(){
		$this->returnSql = true;
		return $this;
	}
	final function getByKey($valueOfKey){
		if(!empty($this->key)){
			return $this->dbHandle->runsql("select * from ".$this->tableName." where ".$this->key."=".$valueOfKey);
		}
	}
	final function getByKeys($valuesOfKey){
		if(!empty($this->key)){
			return $this->dbHandle->runsql("select * from ".$this->tableName." where ".$this->key."in(".$valuesOfKey.")");
		}
	}
	protected function getList($arr){
		if(empty($arr)){
			$sql ="select * from ".$this->tableName;
		}elseif(is_array($arr)){
			$sql = "";
			if(empty($arr["select"])){
				$sql.="select * from ".$this->tableName;
			}else{
				$sql.="select ".$arr["select"]." from ".$this->tableName;
			}
			if(empty($arr["where"])) {
				$whereParams = array();
				foreach(array_diff(array_keys($arr),array("select","where","orderBy","limit","groupBy")) as $v){
					$whereParams[] = $v.'="'.$arr[$v].'"';
				}
				if(!empty($whereParams)){
					$sql.=" where ".implode(" and ",$whereParams);
				}
			}else{
				if(is_array($arr["where"])){
					$whereParams = array();
					foreach($arr["where"] as $k=>$v){
						$whereParams[] = $k.'="'.$v.'"';
					}
					$sql.=" where ".implode(" and ",$whereParams);
				}else{
					$sql.=" where ".$arr["where"];
				}
			}
			if(!empty($arr["groupBy"])){
				$sql.=" group by ".$arr["groupBy"];
			}
			if(!empty($arr["orderBy"])){
				$sql.=" order by ".$arr["orderBy"];
			}
			if(!empty($arr["limit"])){
				$sql.=" limit ".$arr["limit"];
			}
		}else if(is_string($arr)){
			if(strtolower(substr($arr,0,6))=="select"){
				$sql = $arr;
			}else{
				$sql ="select * from ".$this->tableName." where ".$arr;
			}
		}else{
			die("wrong");
		}
		if($this->returnSql){
			$this->returnSql = false;
			return $sql;
		}else{
			return $this->dbHandle->runsql($sql);
		}
	}
	final function insert($params){
		$sql = "insert into ".$this->tableName." (".implode(",",array_keys($params)).') VALUES("'.implode('","',array_values($params)).'");';
		//echo $sql;exit;
		$id = $this->dbHandle->runInsertRun($sql);
		return $id;
	}
	public function update($where,$params){
		$sql = "update ".$this->tableName." set ";
		$paramsTemp = array();
		foreach($params as $k=>$v){
			$paramsTemp[] = $k.'="'.$v.'"';
		}
		$sql .= implode(",",$paramsTemp);

		$paramsTemp2 = array();
		foreach($where as $k=>$v){
			$paramsTemp2[] = $k.'="'.$v.'"';
		}
		$sql .= " where ".implode(" and ",$paramsTemp2);
		//echo $sql;exit;
		return $this->dbHandle->runsql($sql);
	}
	final function delete(){
	}
}

//用例
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