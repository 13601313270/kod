<?php
/**
 * 数组工具集,不依赖任何其他类的算法糖
 * User: mfw
 * Date: 16/7/20
 * Time: 上午7:49
 */
interface kod_tool_configInterface{
	public static function getPageContent($aOption);
}
abstract class kod_tool_config implements kod_tool_configInterface{
	public static $pathArr = array();
	public static $urlArr = array();
	/**
	 * create
	 * 函数的含义说明
	 *
	 * @access public
	 * @since 1.0
	 */
	//把字符串拆分成一段一段的数组,以便于逐段匹配
	public static function getArrByStr($str){
		return explode('/',$str);
	}
	public static function init($aOption){
		$lineList = static::getPageContent($aOption);
		foreach($lineList as $one){
			$paths = static::getArrByStr($one[0]);
//			self::setPathVal(static::$pathArr,$paths,trim($one[1]));
			self::setPathVal(static::$pathArr,$paths,$one);
			//定义path=>url的数组
			$allColumn = array();
			$one[0] = preg_replace_callback("/(\(.*?\))/",function($matchs) use (&$allColumn){
				$allColumn["$".(count($allColumn)+1)] = $matchs[0];
				return "$".count($allColumn);
			},$one[0]);

			//. \ + * ? [ ^ ] $ ( ) { } = ! < > | : -
			$one[1] = preg_replace_callback("(\.|\\|\+|\*|\?|\[|\^|\]|\/)",function($matchs){
				return "\\".$matchs[0];
			},$one[1]);
			$one[1] = preg_replace_callback("/(\\$\d+)/",function($matchs) use (&$allColumn){
				return $allColumn[$matchs[0]];
			},$one[1]);
			static::$urlArr[trim($one[1])] = $one[0];
		}
	}
	public static function getPathByUrl($url,$path = array(),$column=array()){
		if($path==array()){
			$path = static::$pathArr;
		}
		if(is_string($url)){
			$url = static::getArrByStr($url);//explode("/",$url);
			if(empty($url[0])){
				array_shift($url);
			}
		}
		return self::__getPathBase($url,$path,$column);
	}
	//给rewrite树增加对应php文件
	private static function setPathVal(&$arr,$_pathArr, $val){
		if(empty($_pathArr[0])){
			array_shift($_pathArr);
		}
		$name = $_pathArr[0];
		array_shift($_pathArr);
		if(count($_pathArr)!=0){
			$pregKeyWord = array('(',')','$','*','+','?','.','[',']','\\','^','{','}','|');
			$isNeedPreg = false;
			foreach($pregKeyWord as $v){
				$pos = strstr($name,$v);
				if($pos && substr($name,$pos-2,1)!=="\\"){
					$isNeedPreg = true;
				}
			}
			if($isNeedPreg){
				self::setPathVal($arr["preg"][$name],$_pathArr,$val);
			}else{
				self::setPathVal($arr["equal"][$name],$_pathArr,$val);
			}
		}else{
			$pregKeyWord = array("(","$");
			$isNeedPreg = false;
			foreach($pregKeyWord as $v){
				if(strstr($name,$v)>-1){
					$isNeedPreg = true;
				}
			}
			if($isNeedPreg){
				$arr["preg"][$name]["equal"] = $val;
			}else{
				$arr["equal"][$name]["equal"] = $val;
			}
		}
	}
	public static function __getPathBase($url,$path = array(),$column=array()){
		if(is_string($path["equal"]) || (isset($path["equal"]) && array_keys(array_keys($path["equal"]))===array_keys($path["equal"])) ){//equal叶子节点
			if(empty($url)){
				$childPath = $path["equal"];
				foreach($column as $kk=>$vv){
					$childPath = preg_replace('(\$'.($kk+1).')',$vv,$childPath);
				}
				if($childPath!=""){
					return $childPath;
				}
			}else{
				return "";
			}
		}else if(!empty($url) && isset($path["equal"][$url[0]])){//equal普通节点
			$childPath = $path["equal"][$url[0]];
			$oldUrl = $url;
			array_shift($url);
			$returnUrl = self::__getPathBase($url,$childPath,$column);
			if($returnUrl!=""){
				return $returnUrl;
			}else{
				$url = $oldUrl;
				unset($oldUrl);
			}
		}
		if(isset($path['preg']) || (isset($path['equal']) && array_keys(array_keys($path['equal']))===array_keys($path['equal'])) ){
			foreach($path['preg'] as $k=>$v){
				if(preg_match("/^".$k."$/",$url[0],$match)){
					$childPath = $path['preg'][$k];
					array_shift($match);
					$column = array_merge($column,$match);
					array_shift($url);
					return self::__getPathBase($url,$childPath,$column);
				}
			}
			return '';
		}else{
			return '';
		}
	}

	public static function getUrlByPath($filePath){
		foreach(static::$urlArr as $path=>$url){
			$returnStr = preg_replace("/".$path."/",$url,$filePath,-1,$count);
			if($count==1){
				return $returnStr;
			}
		}
		return $filePath;
	}

	public static function getParams(){
		$args = func_get_args ();
		$tryUrl = $args[0];
		if(preg_match($tryUrl,$_SERVER['REQUEST_URI'],$match)){
			if(count($match)==count($args)){
				foreach($match as $k=>$v){
					$temp = &$args[$k];
					//$temp.='asdfas';
					$temp = $v;
				}
				return true;
			}
		}
		return false;
	}
}



//用法事例
/*
final class configTemp extends kod_tool_config{
	public static function getArrByStr($str){
		$splitStr = '/';
		$arr = explode($splitStr,$str);
		for($i=0;$i<count($arr);$i++){
			if(substr($arr[$i],-1)=="\\" && substr($arr[$i],-2)!=="\\\\"){
				if(isset($arr[$i+1])){
					$arr[$i] = $arr[$i].$splitStr.$arr[$i+1];
					$arr[$i+1] = '';
				}
			}
		}
		return $arr;
	}
	public static function getPageContent($confPath)
	{
		$file = fopen($confPath, "r") or exit("Unable to open file!");
		$lineList = array();
		while(!feof($file)) {
			$one = fgets($file);
			$one = explode(" ",$one);
			if(count($one)!=2){
				continue;
			}
			$lineList[] = $one;
		}
		fclose($file);
		return $lineList;
	}
}
configTemp::init(dirname(__FILE__).'/test.conf');
$result = configTemp::getPathByUrl('/travel-scenic-spot/bucuomafengwo/222.html');
print_r($result);
*/
/*
/travel-scenic-spot/mafengwo/(\d+).html /mdd/mdd.php?id=$1
/i/(\d+).html /mdd/article.php?id=$1&static_url=true
/essay/(\d+).html /mdd/article.php?id=$1&static_url=true
*/