<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 14-9-8
 * Time: 下午12:13
 */
class apiList_user extends db_mysqlSingle{
	public $tableName = "user";
	//public $key = "";
	public function getAllUser(){
		return $this->getList("");
	}
	public function getByNameAndPass($username,$password){
		return $this->getList(array(
			"select"=>"username,ticket",
			"username"=>$username,
			"password"=>$password,
		));
	}
	public function getInfoByTicket($ticket){
		return $this->getList("ticket='".$ticket."'");
	}
}