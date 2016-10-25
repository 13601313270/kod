<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 15/7/5
 * Time: 下午4:39
 */
final class kod_web_rewrite{
	public static $pathArr = array();
	public static $urlArr = array();
	public static function init($confPath){
		$file = fopen($confPath, "r") or exit("Unable to open file!");
		while(!feof($file)) {
			$one = fgets($file);
			$one = explode(" ",$one);
			if(count($one)!=2){
				continue;
			}
			$paths = explode("/",$one[0]);
			self::setPathVal(self::$pathArr,$paths,trim($one[1]));

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
			self::$urlArr[trim($one[1])] = $one[0];
		}
		fclose($file);
	}
	//给rewrite树增加对应php文件
	private static function setPathVal(&$arr,$pathArr,$val){
		if(empty($pathArr[0])){
			array_shift($pathArr);
		}
		$name = $pathArr[0];
		array_shift($pathArr);
		if(count($pathArr)!=0){
			$pregKeyWord = array("(","$");
			$isNeedPreg = false;
			foreach($pregKeyWord as $v){
				if(strstr($name,$v)>-1){
					$isNeedPreg = true;
				}
			}
			if($isNeedPreg){
				self::setPathVal($arr["preg"][$name],$pathArr,$val);
			}else{
				self::setPathVal($arr["equal"][$name],$pathArr,$val);
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
	public static function getPathByUrl($url,$path = array(),$column=array()){
		if($path==array()){
			$path = self::$pathArr;
		}
		if(is_string($url)){
			$url = explode("/",$url);
			if(empty($url[0])){
				array_shift($url);
			}
		}

		if(is_string($path["equal"])){//equal叶子节点
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
			$returnUrl = self::getPathByUrl($url,$childPath,$column);
			if($returnUrl!=""){
				return $returnUrl;
			}else{
				$url = $oldUrl;
				unset($oldUrl);
			}
		}
		if(isset($path["preg"])){
			foreach($path["preg"] as $k=>$v){
				if(preg_match("/^".$k."$/",$url[0],$match)){
					$childPath = $path["preg"][$k];
					array_shift($match);
					$column = array_merge($column,$match);
					array_shift($url);
					return self::getPathByUrl($url,$childPath,$column);
				}
			}
			return "";
		}else{
			return "";
		}
	}
	public static function getUrlByPath($filePath){
		foreach(kod_web_rewrite::$urlArr as $path=>$url){
			$returnStr = preg_replace("/".$path."/",$url,$filePath,-1,$count);
			if($count==1){
				return $returnStr;
			}
		}
		return "";
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
/*一组测试用例*/
/*
/travel-scenic-spot/mafengwo/(\d+).html /mdd/mdd.php?id=$1
/i/(\d+).html /mdd/article.php?id=$1&static_url=true
/essay/(\d+).html /mdd/article.php?id=$1&static_url=true
/gtopic/(\d+).html /mdd/article.php?id=$1&static_url=true
/mdd/(\d+)$ /mdd/mdd.php?id=$1
/qq$ /qq/
/yj/(\d+)/ /mdd/article_list.php?id=$1
/i/(\d+)$ /mdd/article.php?id=$1&static_url=true
/l/(.+) /jump.php?code=$1
/mfwapp/(.+) /mfwapp/entry.php/$1
/mfwapp2/(.+) /mfwapp2/entry.php/$1
/yuntu/(.+) /yuntu/entry.php/$1
/wenda/detail-(\d+).html /wenda/detail.php?qid=$1
/wenda/area-(\d+).html /wenda/mdd.php?mddid=$1
/hotel/(\d+)/ /hotel/index.php/hotel_list_no_booking?city_id=$1
/hotel/area_(\d+).html /hotel/index.php/hotel_list_no_booking?area_id=$1
/hotel/area_map_(\d+).html /hotel/index.php/hotel_map?area_id=$1
/hotel/(\d+).html /hotel/index.php/info?id=$1
/hotel/map_(\d+).html /hotel/index.php/hotel_map?city_id=$1
/hotel/map/ /hotel/index.php/hotel_map
/travel-news/(\d+).html /news/info.php?id=$1
/travel-news/note/(\d+).html /news/noteInfo.php?id=$1
/hotel/12684/ /hotel/index.php/hotel_list_no_booking?city_id=10819

/localdeals/(\d+)/$ /localdeals/index_mdd.php?mddid=$1&rewrite=1
/localdeals/(\d+)/tag-(.*).html$ /localdeals/list.php?mddid=$1&tag=$2&rewrite=1
/localdeals/(\d+)/rec-(.*).html$ /localdeals/list.php?mddid=$1&subject_id=$2&rewrite=1
/localdeals/(\d+)/jingqu-(.*).html$ /localdeals/list.php?mddid=$1&jingqu_id=$2&rewrite=1
/localdeals/(\d+).html /localdeals/info.php?id=$1

/sales/(\d+).html /sales/info.php?id=$1
/sales/ota/(\d+).html /sales/ota.php?id=$1
/sales/(\d+)-(\d+)-(\d+)-(\d+).html?(.*) /sales/?date=$1&from=$2&to=$3&type=$4&$5
/photo/(\d+)/scenery_(\d+)_(\d+).html /mdd/plistdetail.php?mddid=$1&topiid=$2&page=$3&static_url=1
/photo/(\d+)/scenery_(\d+)/(\d+).html /mdd/pdetail.php?mddid=$1&topiid=$2&pid=$3
/photo/poi/(\d+).html /album/poi-album.php?id=$1
/photo/poi/(\d+)_(\d+).html /album/photoDetail.php?poiid=$1&id=$2
/photo/mdd/(\d+)_(\d+).html /album/mddPicDetail.php?mddid=$1&id=$2
/photo/mdd/(\d+).html /album/mdd-album.php?mddid=$1
/poi/(\d+).html /mdd/poi.php?id=$1
/poi/map_(\d+).html /mdd/poi_map.php?poiid=$1
/mdd/map_(\d+).html /mdd/poi_map.php?mddid=$1
/poi/intro_(\d+).html /mdd/poi.php/intro/?id=$1
/poi/guide_(\d+).html /mdd/poi.php/guide/?id=$1
/poi/comment_(\d+).html /mdd/poi.php/comment/?id=$1
/xc/(\d+)/gonglve.html /gl/static_gl.php?mddid=$1&type=xc
/baike/(\w+)-(\d+).html /gl/baike_detail.php?mddid=$2&type=$1
/baike/(\d+)/ /gl/baike_list.php?mddid=$1
/(cy|jd|gw|yl)/(\d+)/(tese|gonglve).html /gl/guide_index.php?mddid=$2&type=$1&ext=$3
/(cy|jd|gw|yl)/(\d+)/$ /gl/guide_index.php?mddid=$2&type=$1
/(cy|jd|gw|yl)/gl-(\d+).html /gl/guide_list.php?mddid=$2&type=$1
/(cy|jd|gw|yl|xc)/(\d+)/(\d+)(-0)?.html /gl/guide_detail.php?id=$3&mddid=$2&type=$1&map=$4
/cy/(\d+)/(\d+)-(\d+)-(\d+)-(\d+)-(\d+)-(\d+).html /gl/poi_list.php?iType=1&iMddId=$1&iDistrictId=$2&iAreaId=$3&iTagId=$4&iSort=$5&iOrder=$6&iPage=$7
/jd/(\d+)/(\d+)-(\d+)-(\d+)-(\d+)-(\d+)-(\d+).html /gl/poi_list.php?iType=3&iMddId=$1&iDistrictId=$2&iAreaId=$3&iTagId=$4&iSort=$5&iOrder=$6&iPage=$7
/gw/(\d+)/(\d+)-(\d+)-(\d+)-(\d+)-(\d+)-(\d+).html /gl/poi_list.php?iType=4&iMddId=$1&iDistrictId=$2&iAreaId=$3&iTagId=$4&iSort=$5&iOrder=$6&iPage=$7
/yl/(\d+)/(\d+)-(\d+)-(\d+)-(\d+)-(\d+)-(\d+).html /gl/poi_list.php?iType=5&iMddId=$1&iDistrictId=$2&iAreaId=$3&iTagId=$4&iSort=$5&iOrder=$6&iPage=$7
/thirdparty/duomeng /thirdparty/duomeng.php
/thirdparty/device_report /thirdparty/device_report.php
/freego/qingming /game/freego/2015/qingming.php
/freego/qingming/(\w+) /game/freego/2015/qingming.php?page=$1
/poi/(\d+)\.html@(\S+) /mdd/poi.php?id=$1
/app/down/(\w+)/ /app/down/down.php/$1
/together/ /mate/v5/index.php
/together/(\d+)$ /mate/v5/index.php?mddid=$1
/together/detail/(\d+)\.html /mate/v5/detail.php?tid=$1
/together/apply/(\d+)\.html /mate/v5/apply.php?tid=$1
/together/publish/ /mate/v5/publish.php
/together/myself/ /mate/v5/myself.php
/app/tg/faq.html /nb/h5/faq_tg.php
/app/tg/daka.html /nb/h5/daka.php
/static_url/(\w+).html /static_url/index.php?page=$1
*/