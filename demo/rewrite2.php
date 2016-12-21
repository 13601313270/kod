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
kod_web_rewrite::init(dirname(__FILE__).'/test.conf');
//双向获取
$result = kod_web_rewrite::getPathByUrl('/sales/234.html');
print_r($result);
$result = kod_web_rewrite::getUrlByPath('/album/poi-album.php?id=21421');
print_r($result);
/*传统rewrite配置方法*/
/*
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
*/