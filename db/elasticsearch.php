<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 16/9/14
 * Time: 上午12:52
 */
abstract class kod_db_elasticsearch{
	public $index;
	function __construct($server = 'http://localhost:9200'){
		if (!$this->index){
			throw new Exception('$this->index needs a value(请在kod_db_elasticsearch的子类'.get_class($this).'中定义index索引)');
		}
		$this->server = $server;
	}
	private function getTrueUrl($path){
		return $this->server . '/' . $this->index . '/'.$path;
	}

	function call($path, $http = array()){
		return json_decode(file_get_contents($this->getTrueUrl($path), NULL, stream_context_create(array('http' => $http))));
	}

	//创建索引
	//curl -X PUT http://localhost:9200/{INDEX}/
	function create(){
		$this->call(NULL, array('method' => 'PUT'));
	}

	//删除索引
	//curl -X DELETE http://localhost:9200/{INDEX}/
	function drop(){
		$this->call(NULL, array('method' => 'DELETE'));
	}

	//查询状态
	//curl -X GET http://localhost:9200/{INDEX}/_status
	function status(){
		return $this->call('_status');
	}

	//查询某一类表的总数
	//curl -X GET http://localhost:9200/{INDEX}/{TYPE}/_count -d {matchAll:{}}
	function count($type){
		return $this->call($type . '/_count', array('method' => 'GET', 'content' => '{ matchAll:{} }'));
	}

	//查询某一个文档的结构类型
	//curl -X PUT http://localhost:9200/{INDEX}/{TYPE}/_mapping -d ...
	function map($type, $data){
		return $this->call($type . '/_mapping', array('method' => 'PUT', 'content' => json_encode($data)));
	}

	//curl -X PUT http://localhost:9200/{INDEX}/{TYPE}/{ID} -d ...
	//插入或者一条数据
	function add($type, $id, $data){
		return $this->call($type . '/' . $id, array('method' => 'PUT', 'content' => json_encode($data)));
	}

	//curl -X GET http://localhost:9200/{INDEX}/{TYPE}/_search?q= ...
	function query($type, $sQuery,$onlyData=true){
		if(empty($sQuery)){
			$data = $this->call($type . '/_search');
		}else{
			$data = $this->call($type . '/_search?' . http_build_query(array('q' => $sQuery)));
		}
		if($onlyData){
			return $data->hits;
		}else{
			return $data;
		}
	}
}
/*
 * demo
class testEs extends kod_db_elasticsearch{
	public $index = 'test';
}
*/