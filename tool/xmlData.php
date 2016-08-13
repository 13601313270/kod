<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 16/3/27
 * Time: 下午5:43
 */
class kod_tool_xmlData{
	private static function echoTab($count){
		$returnTxt = "";
		for($i=0;$i<$count;$i++){
			$returnTxt.="\t";
		}
		return $returnTxt;
	}
	private static function removeAttr($name){
		$name = explode(" ",$name);
		return $name[0];
	}
	protected static function isNeedCDATA($str){
		$CDATAfind = array("<",">","&","'",'"',";");
		$isNeedCDATA = false;
		foreach($CDATAfind as $word){
			if(strpos($str,$word)>-1){
				$isNeedCDATA = true;
			}
		}
		return $isNeedCDATA;
	}
	public static function createDataArr($params,$this_=null,$withValue=array()){
		$returnArr_ = array();
		foreach($params as $k=>$v){
			preg_match('/list\(([^)]*)\) data=(\S*) as \$(.*)=>\$(.*)/',$k,$matchTemp);
			if(count($matchTemp)>0){//存在list
				foreach($withValue as $kk=>$vv){
					if($kk=="val"){
						$matchTemp[2] = str_replace($kk,"withValue[\"$kk\"]",$matchTemp[2]);
					}
				}
				eval('$dataList='.$matchTemp[2].";");//数组的内容
				$domType = $matchTemp[1];
				if(count($dataList)>0){
					if(empty($returnArr_[$domType])){
						$returnArr_[$domType]=array();
					}
					foreach($dataList as $kk=>$vv){//把数组每一项当做设定的key/value值传递到下一层
						$dropArr = $withValue;
						$dropArr[$matchTemp[3]] = $kk;
						$dropArr[$matchTemp[4]] = $vv;
						if(is_array($v)){
							$returnArr_[$domType][]=self::createDataArr($v,$dropArr);
						}else{
							$returnArr_[$domType][] = self::replaceValue($v,$dropArr);
						}
					}
				}
			}elseif(is_array($v)){
				$returnArr_[$k]=self::createDataArr($v,$withValue);
			}else{
				$v = self::replaceValue($v,$withValue);
				$returnArr_[$k]=$v;
			}
		}
		return $returnArr_;
	}

	private static  function replaceValue($str_____,$kAndV_____){
		//构造所有传递进来的变量
		foreach($kAndV_____ as $k_____=>$v_____){
			$$k_____ = $v_____;
		}
		$rplaceKVs_____ = array();
		preg_match_all('/\{([^}]+)\}/e',$str_____,$match_____);
		foreach($match_____[1] as $v_____){
			//$v可以是一个php语句，一个变量名
			//eval('$isHasBianLiang = is_string('.$v_____.');');
			//if($isHasBianLiang){
			eval('$rplaceKVs_____[$v_____] = '.$v_____.';');
			if(in_array(gettype($rplaceKVs_____[$v_____]),array("string","integer"))){
				eval('$str_____ = str_replace("{".$v_____."}",'.$v_____.',$str_____);');
			}else{
				$str_____ = $rplaceKVs_____[$v_____];
			}
			//}
		}
		return $str_____;
	}

	public static function getXmlByArray($datas,$tabCount = 0){
		$return = "";
		foreach($datas as $k=>$each){
			$key = $k;
			if(gettype($each)=="array"){
				if(array_values($each) === $each){//数组
					if(count($each)==0){$return.="\n".self::echoTab($tabCount);
						$return.="<$key>";
						$return.="</".self::removeAttr($key).">";
					}else{
						foreach($each as $v){
							if(in_array(gettype($v),array("string","integer"))){
								$return.="\n".self::echoTab($tabCount);
								$return.="<$key>";
								//$v = preg_replace( '/[\x00-\x1F]/','',$v);
								if(self::isNeedCDATA($v)){
									$return.="<![CDATA[".$v."]]>";
								}else{
									$return.=$v;
								}
								$return.="</".self::removeAttr($key).">";
							}else{
								$return.="\n".self::echoTab($tabCount);$return.="<$key>";
								$return.=self::getXmlByArray($v,$tabCount+1);$return.="\n".self::echoTab($tabCount);
								$return.="</".self::removeAttr($key).">";
							}
						}
					}
				}else{//对象
					$return.="\n".self::echoTab($tabCount);$return.='<'.$key.'>';
					$return.=self::getXmlByArray($each,$tabCount+1);
					$return.="\n".self::echoTab($tabCount);
					$return.="</".self::removeAttr($key).">";
				}
			}else{
				$return.="\n".self::echoTab($tabCount);$return.="<$key>";
				if(self::isNeedCDATA($each)){
					$return.="<![CDATA[".$each."]]>";
				}else{
					$return.=strval($each);
				}
				$return.="</".self::removeAttr($key).">";
			}
		}
		return $return;
	}
}