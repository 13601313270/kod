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
		$this->smarty = $this->initSmarty();
		$this->assign("allParams",$funParam);//将网址的所有参数传入网页
		if($method->getNumberOfParameters()>0){
			$method->invokeArgs($this,$funParam);
		}else{
			$method->invoke($this);
		}
		if(!empty($this->smartyTpl)){
			try{
				$this->beforeRun();
				$this->smarty->fetch($this->smartyTpl, null, null, null, true);
			}catch(Exception $e){
				switch($e->getCode()){
					case 1:
						throw new Exception("smarty无法对预编译文件进行存储，请尝试在【".get_called_class()."】类中定义一个initSmarty方法，initSmarty第一个参数是关联的smarty的对象，对这个参数传入的smarty对象赋予compile_dir属性值。也可以在kod框架加载文件中配置变量【KOD_SMARTY_COMPILR_DIR】作为默认值",0,$e);
						break;
					case 2:
						throw new Exception("【".get_called_class()."】类设置的smartyTpl属性值【{$this->smartyTpl}】，文件无法找到",0,$e);
						break;
					default :
						throw new Exception("其他错误",0,$e);
						break;
				}
			}
		}else{
			throw new Exception("请给【".get_called_class()."】类设置smartyTpl属性用来指定对应tpl文件");
		}
		foreach($this->afterFetchHtml as $k=>$v){
			echo $v;
		}
	}
	public function initSmarty(){
		$smartyObject = new kod_smarty_smarty();
		if(count($this->smartyPlutPath)>=1){
			foreach($this->smartyPlutPath as $k=>$v){
				$smartyObject->addPluginsDir($v);
			}
		}
		$smartyObject->compile_dir = KOD_SMARTY_COMPILR_DIR;//设置编译目录
		//$smartyObject->template_dir = KOD_DIR_NAME."/testRun/";//设置模板目录
		//$smartyObject->config_dir = "smarty/templates/config";//目录变量
		//$smartyObject->cache_dir = "smarty/templates/cache"; //缓存文件夹
		return $smartyObject;
	}
	public function assign($key,$val){
		$this->smarty->assign($key,$val);
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