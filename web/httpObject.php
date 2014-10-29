<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 14-9-26
 * Time: 下午11:07
 */
class web_httpObject{
	protected function main(){

	}
	function __construct(){
	}
	public function run($allowMethod_="GET,POST"){//$_GET $_POST $_COOKIE $_REQUEST $_FILES
		$allowMethod = explode(",",$allowMethod_);
		$allParams = array();
		foreach($allowMethod as $k){
			$allParams = array_merge($allParams,$GLOBALS["_".$k]);
		}
		if(isset($allParams["function"])){
			$method = new ReflectionMethod($this,$allParams["function"]);
		}else{
			$method = new ReflectionMethod($this,"main");
		}
		$funParam = array();
		foreach($method->getParameters() as $v){
			if(isset($allParams[$v->getName()])){
				$funParam[$v->getName()] = $allParams[$v->getName()];
			}else{
				echo "缺少".$v->getName()."参数";exit;
			}
		}
		if($method->getNumberOfParameters()>0){
			$method->invokeArgs($this,$funParam);
		}else{
			$method->invoke($this);
		}
	}
}