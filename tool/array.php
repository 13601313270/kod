<?php
/**
 * 数组工具集,不依赖任何其他类的算法糖
 * User: mfw
 * Date: 16/7/20
 * Time: 上午7:49
 */
class kod_tool_array{

	public static function groupByColumn($arr,$column){
		$returnArr = array();
		foreach($arr as $k=>$v){
			if(isset($returnArr[$v[$column]])){
				$returnArr[$v[$column]][] = $v;
			}else{
				$returnArr[$v[$column]] = array($v);
			}
		}
		return $returnArr;
	}
	//把某个数组的某个字段提取出来成为新的数组
	public static function getNewArrOfArrColumn($arr,$column){
		$returnArr = array();
		foreach($arr as $v){
			$returnArr[] = $v[$column];
		}
		return $returnArr;
	}
}