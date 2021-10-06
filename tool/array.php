<?php
/**
 * 数组工具集,不依赖任何其他类的算法糖
 * User: mfw
 * Date: 16/7/20
 * Time: 上午7:49
 */
class kod_tool_array{

	public static function groupByColumn($arr,$column,$onlyOne=false){
		$returnArr = array();
		foreach($arr as $k=>$v){
			if($onlyOne){
				$returnArr[$v[$column]] = $v;
			}else{
				if(isset($returnArr[$v[$column]])){
					$returnArr[$v[$column]][] = $v;
				}else{
					$returnArr[$v[$column]] = array($v);
				}
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

	//把数组按照某个字段排序
	//SORT_ASC
	//SORT_ASC
	public static function orderByColumn($arr,$column,$sorting=SORT_ASC){
		$arr = array_values($arr);
		$newArr=array();
		for($j=0;$j<count($arr);$j++){
			$newArr[]=$arr[$j][$column];
		}
		array_multisort($newArr,$sorting,$arr);
		return $arr;
	}

    public static function filter($arr, Closure $callback)
    {
        $returnList = [];
        foreach ($arr as $item) {
            if ($callback($item) === true) {
                $returnList[] = $item;
            }
        }
        return $returnList;
    }
}
