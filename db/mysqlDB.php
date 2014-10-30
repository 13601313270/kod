<?php
/**
 * Created by PhpStorm.
 * User: kod
 * Date: 14-9-4
 * Time: 上午12:08
 */
final class kod_db_mysqlDB{
	private $dbName;
	private $loginUser = "root";
	private $loginPass = "1082322";
	function __construct($db){
		$this->dbName = $db;
	}
	static function create($db){
		$temp = __CLASS__;
		return new $temp($db);
	}
	public function runsql($sql){
		$con = mysql_connect("localhost",$this->loginUser,$this->loginPass);
		if (!$con){
			die('Could not connect: ' . mysql_error());
		}
		$db_selected = mysql_select_db($this->dbName, $con);
		if (!$db_selected)
		{
			throw new Exception("数据库【".$this->dbName."】不存在");
		}
		$result = mysql_query($sql,$con);
		if(gettype($result)=="resource"){
			$returnData = array();
			while($row = mysql_fetch_assoc($result)){
				$returnData[] = $row;
			}
		}else{
			$returnData = mysql_affected_rows()>-1;
			if($returnData==false){
				//echo $this->tableName;exit;
				$dataTemp = mysql_query("show tables from ".$this->dbName,$con);
				while($row = mysql_fetch_assoc($dataTemp)){
					$returnData[$row["Tables_in_".$this->dbName]] = $row;
				}
				preg_match("/select (\S+) from (\S+) /",$sql,$tableName);
				if(!isset($returnData[$tableName[2]])){
					throw new Exception("数据库【".$this->dbName."】不包含表【".$tableName[2]."】");
				}
			}
		}
		mysql_close($con);
		return $returnData;
	}
	public function runInsertRun($sql){
		$con = mysql_connect("localhost",$this->loginUser,$this->loginPass);
		if (!$con){
			die('Could not connect: ' . mysql_error());
		}
		mysql_select_db($this->dbName, $con);
		mysql_query($sql);
		$incrementID = mysql_insert_id($con);
		mysql_close($con);
		return $incrementID;
	}
}