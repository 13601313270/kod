<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 15/7/5
 * Time: 下午4:39
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






/*一组测试用例*/
/*
/index.php / 301
/404/ /404.php 404
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
/wenda
	/detail-(\d+).html /wenda/detail.php?qid=$1
	/area-(\d+).html /wenda/mdd.php?mddid=$1
/hotel
    /(\d+)/ /hotel/index.php/hotel_list_no_booking?city_id=$1
	/area_(\d+).html /hotel/index.php/hotel_list_no_booking?area_id=$1
	/area_map_(\d+).html /hotel/index.php/hotel_map?area_id=$1
	/(\d+).html /hotel/index.php/info?id=$1
	/map_(\d+).html /hotel/index.php/hotel_map?city_id=$1
	/map/ /hotel/index.php/hotel_map
	/12684/ /hotel/index.php/hotel_list_no_booking?city_id=10819
/password
	/email /passport.php get
	/email /passport.php post
/travel-news
	/(\d+).html /news/info.php?id=$1
	/note/(\d+).html /news/noteInfo.php?id=$1
/localdeals
	/(\d+)/$ /localdeals/index_mdd.php?mddid=$1&rewrite=1
	/(\d+)/tag-(.*).html$ /localdeals/list.php?mddid=$1&tag=$2&rewrite=1
	/(\d+)/rec-(.*).html$ /localdeals/list.php?mddid=$1&subject_id=$2&rewrite=1
	/(\d+)/jingqu-(.*).html$ /localdeals/list.php?mddid=$1&jingqu_id=$2&rewrite=1
	/(\d+).html /localdeals/info.php?id=$1
/sales
	/(\d+).html /sales/info.php?id=$1
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
/mdd/map_(\d+).html /mdd/poi_map.php?mddid=$1
/xc/(\d+)/gonglve.html /gl/static_gl.php?mddid=$1&type=xc
/baike
	/(\w+)-(\d+).html /gl/baike_detail.php?mddid=$2&type=$1
	/(\d+)/ /gl/baike_list.php?mddid=$1
/(cy|jd|gw|yl)
	/(\d+)/(tese|gonglve).html /gl/guide_index.php?mddid=$2&type=$1&ext=$3
	/(\d+)/$ /gl/guide_index.php?mddid=$2&type=$1
	/gl-(\d+).html /gl/guide_list.php?mddid=$2&type=$1
/(cy|jd|gw|yl|xc)/(\d+)/(\d+)(-0)?.html /gl/guide_detail.php?id=$3&mddid=$2&type=$1&map=$4
/cy/(\d+)/(\d+)-(\d+)-(\d+)-(\d+)-(\d+)-(\d+).html /gl/poi_list.php?iType=1&iMddId=$1&iDistrictId=$2&iAreaId=$3&iTagId=$4&iSort=$5&iOrder=$6&iPage=$7
/jd/(\d+)/(\d+)-(\d+)-(\d+)-(\d+)-(\d+)-(\d+).html /gl/poi_list.php?iType=3&iMddId=$1&iDistrictId=$2&iAreaId=$3&iTagId=$4&iSort=$5&iOrder=$6&iPage=$7
/gw/(\d+)/(\d+)-(\d+)-(\d+)-(\d+)-(\d+)-(\d+).html /gl/poi_list.php?iType=4&iMddId=$1&iDistrictId=$2&iAreaId=$3&iTagId=$4&iSort=$5&iOrder=$6&iPage=$7
/yl/(\d+)/(\d+)-(\d+)-(\d+)-(\d+)-(\d+)-(\d+).html /gl/poi_list.php?iType=5&iMddId=$1&iDistrictId=$2&iAreaId=$3&iTagId=$4&iSort=$5&iOrder=$6&iPage=$7
/thirdparty
	/duomeng /thirdparty/duomeng.php
	/device_report /thirdparty/device_report.php
/freego
	/qingming /game/freego/2015/qingming.php
	/qingming/(\w+) /game/freego/2015/qingming.php?page=$1
/poi/(\d+)\.html@(\S+) /mdd/poi.php?id=$1
/app/down/(\w+)/ /app/down/down.php/$1
/together
	/ /mate/v5/index.php
	/(\d+)$ /mate/v5/index.php?mddid=$1
	/detail/(\d+)\.html /mate/v5/detail.php?tid=$1
	/apply/(\d+)\.html /mate/v5/apply.php?tid=$1
	/publish/ /mate/v5/publish.php
	/myself/ /mate/v5/myself.php
/app
	/tg/faq.html /nb/h5/faq_tg.php
	/tg/daka.html /nb/h5/daka.php
/static_url/(\w+).html /static_url/index.php?page=$1
*/