<?php
/**
 * Created by PhpStorm.
 * User: kod
 * Date: 14-9-4
 * Time: 上午12:08
 */
final class db_mysqlDB{
	private $dbName;
	function __construct($db){
		$this->dbName = $db;
	}
	static function create($db){
		$temp = __CLASS__;
		return new $temp($db);
	}
	public function runsql($sql){
		$con = mysql_connect("localhost","root","1082322");
		if (!$con){
			die('Could not connect: ' . mysql_error());
		}
		mysql_select_db($this->dbName, $con);
		$result = mysql_query($sql,$con);
		if(gettype($result)=="resource"){
			$returnData = array();
			while($row = mysql_fetch_assoc($result)){
				$returnData[] = $row;
			}
		}else{
			$returnData = mysql_affected_rows()>-1;
		}
		mysql_close($con);
		return $returnData;
	}
	public function runInsertRun($sql){
		$con = mysql_connect("localhost","root","1082322");
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