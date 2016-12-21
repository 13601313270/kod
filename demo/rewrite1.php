<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 2016/12/21
 * Time: 上午2:38
 */
final class kod_web_rewrite extends kod_tool_config{
    public static function getArrByStr($str){
        $splitStr = '/';
        $arr = explode($splitStr,$str);
        return $arr;
    }
    public static function getPageContent($confPath)
    {
        $file = fopen($confPath, "r") or exit("Unable to open file!");
        $lineList = array();
        $before = array();
        while(!feof($file)) {
            $oneLine = fgets($file);
            $oneArr = explode(" ",$oneLine);
            $oneArr[count($oneArr)-1] = trim($oneArr[count($oneArr)-1]);
            if(count($oneArr)==1){
                if(substr($oneArr[0],0,2)=="\t\t"){
                    $before = array($before[0],$before[1],trim($oneArr[0]));
                }elseif(substr($oneArr[0],0,1)=="\t"){
                    $before = array($before[0],trim($oneArr[0]));
                }else{
                    $before = array(trim($oneArr[0]));
                }
                continue;
            }elseif(count($oneArr)==0){
                continue;
            }
            $oneArr[0] = implode('',$before).trim($oneArr[0]);
            $lineList[] = $oneArr;

        }
        fclose($file);
        return $lineList;
    }
}
kod_web_rewrite::init(dirname(__FILE__).'/test.conf');
//双向获取
$result = kod_web_rewrite::getPathByUrl('/sales/234.html');
print_r($result);
$result = kod_web_rewrite::getUrlByPath('/album/poi-album.php?id=98');
print_r($result);

/*
 * test.conf示例
/index.php / 301
/sales
	/(\d+).html /sales/info.php?id=$1 301
	/ota/(\d+).html /sales/ota.php?id=$1
	/(\d+)-(\d+)-(\d+)-(\d+).html?(.*) /sales/?date=$1&from=$2&to=$3&type=$4&$5
/photo
	/(\d+)/scenery_(\d+)_(\d+).html /mdd/plistdetail.php?mddid=$1&topiid=$2&page=$3&static_url=1
	/(\d+)/scenery_(\d+)/(\d+).html /mdd/pdetail.php?mddid=$1&topiid=$2&pid=$3
	/poi
		/(\d+).html /album/poi-album.php?id=$1
		/(\d+)_(\d+).html /album/photoDetail.php?poiid=$1&id=$2
	/mdd
		/(\d+)_(\d+).html /album/mddPicDetail.php?mddid=$1&id=$2
		/(\d+).html /album/mdd-album.php?mddid=$1
/poi
	/(\d+).html /mdd/poi.php?id=$1
	/map_(\d+).html /mdd/poi_map.php?poiid=$1
	/intro_(\d+).html /mdd/poi.php/intro/?id=$1
	/guide_(\d+).html /mdd/poi.php/guide/?id=$1
	/comment_(\d+).html /mdd/poi.php/comment/?id=$1
*/

exit;