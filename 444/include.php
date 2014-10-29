<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 14-9-4
 * Time: 上午12:57
 */
define("KOD_DIR_NAME",dirname(__FILE__));
define("KOD_COMMENT_MYSQLDB","pointList");
//define("KOD_WEB_","./");
date_default_timezone_set('PRC');
function __autoload($model){
	include_once(KOD_DIR_NAME."/".str_replace("_", "/",$model).".php");
}