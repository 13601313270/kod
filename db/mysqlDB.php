<?php
/**
 * Created by PhpStorm.
 * User: kod
 * Date: 14-9-4
 * Time: 上午12:08
 * 使用方法
 */
final class kod_db_mysqlDB{
	private $dbName;
	private $loginUser = KOD_MYSQL_USER;
	private $loginPass = KOD_MYSQL_PASSWORD;
	private $charset;
	function __construct($db=KOD_COMMENT_MYSQLDB,$charset = KOD_COMMENT_MYSQLDB_CHARSET){
		$this->dbName = $db;
		$this->charset = $charset;
	}
	public function getConnect(){
		return mysql_connect(KOD_MYSQL_SERVER,$this->loginUser,$this->loginPass);
	}

	/**
	 * create
	 * 函数的含义说明
	 *
	 * @access public
	 * @since 1.0
	 * @return $this
	 */
	static function create($db=KOD_COMMENT_MYSQLDB,$charset = KOD_COMMENT_MYSQLDB_CHARSET){
		$temp = __CLASS__;
		return new $temp($db,$charset);
	}
	//default默认返回
	//mysql_insert_id在插入时候，返回自增的id
	public function runsql($sql,$returnType='default',$con=null){
		if($con===null){
			$con = $this->getConnect();
		}
		if (!$con){
			throw new Exception('无法连接到数据库:' . mysql_error());
		}
		$db_selected = mysql_select_db($this->dbName, $con);
		if (!$db_selected){
			throw new Exception("数据库【".$this->dbName."】不存在");
		}
		if(KOD_SQL_MODE!=""&&KOD_SQL_MODE!="KOD_SQL_MODE"){
			mysql_query("set @@sql_mode=".KOD_SQL_MODE,$con);
		}
		mysql_query("SET NAMES {$this->charset}");
		$result = mysql_query($sql,$con);
		//查询是resource
		if($returnType=='mysql_insert_id'){
			return mysql_insert_id();
		}elseif(gettype($result)=="resource"){
			$returnData = array();
			while($row = mysql_fetch_assoc($result)){
				$returnData[] = $row;
			}
		}else{
			$returnData = mysql_affected_rows();
			if($returnData==-1){
				$dataTemp = mysql_query("show tables from ".$this->dbName,$con);
				while($row = mysql_fetch_assoc($dataTemp)){
					$allTables[$row["Tables_in_".$this->dbName]] = $row;
				}
				preg_match("/select (\S+) from (\S+)/",$sql,$tableName);
				if(count($tableName)==0){//非select语句
					$type = strtolower(substr($sql,0,6));
					if($type=="delete"){
						preg_match("/delete from (\S+)/",$sql,$tableName);
						if(count($tableName)==0){
							throw new Exception("sql语句规范不合法，必须符合【delete from 表名】的格式，检查from是否拼错");
						}
						if(!isset($allTables[$tableName[1]])){
							throw new Exception("数据库【".$this->dbName."】不包含表【".$tableName[1]."】",2);
						}else{
							preg_match("/delete from (\S+) where (.*)/",$sql,$wrongWhere);
							throw new Exception("删除语句条件【".$wrongWhere[2]."】存在问题，请检查查询语句【".$sql."】的条件部分");
						}
					}elseif($type=="insert"){
						//preg_match("INSERT ([LOW_PRIORITY|DELAYED]? )(IGNORE)? [INTO] tbl_name [(col_name,...)]  VALUES (expression,...),(...),...",$sql,$tableName);
						preg_match("/insert into (\S+)/",$sql,$tableName);
						if(count($tableName)==0){
							throw new Exception("sql语句规范不合法，必须符合【insert into 表名】的格式");
						}
						if(!isset($allTables[$tableName[1]])){
							throw new Exception("数据库【".$this->dbName."】不包含表【".$tableName[1]."】");
						}else{
							//preg_match("/delete from (\S+) where (.*)/",$sql,$wrongWhere);
							throw new Exception("请检查insert查询语句【".$sql."】");
						}
					}elseif($type=="update"){
						preg_match("/update (\S+) set/",$sql,$tableName);
						if(count($tableName)==0){
							throw new Exception("sql语句规范不合法，必须符合【insert into 表名】的格式");
						}
						if(!isset($allTables[$tableName[1]])){
							throw new Exception("数据库【".$this->dbName."】不包含表【".$tableName[1]."】");
						}else{
							preg_match("/delete from (\S+) where (.*)/",$sql,$wrongWhere);
							throw new Exception("请检查查询语句【".$sql."】");
						}
					}
				}else{//select语句
					if(!isset($allTables[$tableName[2]])){
						throw new Exception("数据库【".$this->dbName."】不包含表【".$tableName[2]."】");
					}else{//select语句，表存在，可能是字段不存在或者条件不合法
						$dataTemp = mysql_query("SHOW COLUMNS FROM ".$tableName[2],$con);
						$dataTemp2 = array();
						while($row = mysql_fetch_assoc($dataTemp)){
							$dataTemp2[$row["Field"]] = $row;
						}
						$allSearchField = explode(",",$tableName[1]);
						foreach($allSearchField as $field){
							if(in_array($field,array("*"))){
								continue;
							}elseif(!isset($dataTemp2[$field])){
								throw new Exception("字段【".$field."】在表【".$tableName[2]."】中不存在，请检查查询语句【".$sql."】");
							}
						}
						preg_match("/select (\S+) from (\S+) where (.*)/",$sql,$wrongWhere);
						throw new Exception("查询语句条件【".$wrongWhere[3]."】存在问题，请检查查询语句【".$sql."】");
					}
				}
			}else{
				return $returnData;
			}
		}
		mysql_close($con);
		return $returnData;
	}
}