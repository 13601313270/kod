<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 14-9-26
 * Time: 下午11:07
 */
abstract class kod_web_httpObject{
	protected $smartyTpl;//tpl文件
	protected $smarty;
	protected $smartyPlutPath = array();//自定义smarty插件目录
	protected function beforeRun(){

	}
	protected $afterFetchHtml = array();//在渲染输出后输出的部分
	public function afterFetchContent($content){
		$this->afterFetchHtml[] = $content;
	}
	protected function getFuncNameAndParams($allParams){
		if(isset($allParams["function"])){
			$funcName = $allParams["function"];
		}else{
			$funcName = "main";
		}
		unset($allParams['function']);
		return array(
			'function'=>$funcName,
			'params'=>$allParams,
		);
	}
	final public function run($allowMethod_="GET,POST"){//$_GET $_POST $_COOKIE $_REQUEST $_FILES
		$this->smarty = new kod_web_page();
		$allowMethod = explode(",",$allowMethod_);
		$allParams = array();//所有网址输入的参数值
		foreach($allowMethod as $k){
			$allParams = array_merge($allParams,$GLOBALS["_".$k]);
		}
		if(isset($GLOBALS['_TEST'])){
			$allParams = array_merge($allParams,$GLOBALS['_TEST']);
		}
		$runInfo = $this->getFuncNameAndParams($allParams);
		$funcName = $runInfo['function'];
		$allParams = $runInfo['params'];
		try{
			$method = new ReflectionMethod($this,$funcName);
		}catch(Exception $e){
			$message = $e->getMessage();
			if(strpos($message,"does not exist")>0){
				throw new Exception("函数路由机制尝试调用".get_called_class()."类的".$funcName."函数，但这个函数在类中不存在");
			}
		}
		if(!$method->isPublic()){
			throw new Exception("函数路由机制尝试调用".$method->class."类的".$funcName."函数，但这个函数在类中必须是public的才能调用");
		}
		$funParam = array();
		foreach($method->getParameters() as $k=>$v){
			if(isset($allParams[$v->getName()])){//如果网址中输入了对应参数
				$funParam[$v->getName()] = $allParams[$v->getName()];
			}else{
				if(!$v->isDefaultValueAvailable()){
					throw new Exception("浏览器网址输入缺少".$v->getName()."参数");
				}else{
					$funParam[$v->getName()] = $v->getDefaultValue();
				}
			}
		}
		if($method->getNumberOfParameters()>0){
			$method->invokeArgs($this,$funParam);
		}else{
			$method->invoke($this);
		}
		if(!empty($this->smartyTpl)){
			$this->beforeRun();
			$this->smarty->fetch($this->smartyTpl);
		}else{
			throw new Exception("请给【".get_called_class()."】类设置smartyTpl属性用来指定对应tpl文件");
		}
		foreach($this->afterFetchHtml as $k=>$v){
			echo $v;
		}
	}
	public function assign($key,$val){
		$this->smarty->$key = $val;
	}

}

/*demo*/
/*
class index extends kod_web_httpObject{
	protected $smartyTpl = "articleList.tpl";
	public function main($subjectId){
		$typeInfo = subjectHandle::create()->getByKey($subjectId);
		$this->assign('typeInfo',$typeInfo);
	}
}
$a = new index();
$a->run();
*/