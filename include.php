<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 14-9-4
 * Time: 上午12:57
 */
define("KOD_DIR_NAME",dirname(__FILE__));//kod框架所在的地址
define("KOD_COMMENT_MYSQLDB","jihe");//mysql默认数据库
//define("KOD_WEB_","./");
date_default_timezone_set('PRC');

//因为使用了自动加载函数，所以kod内所有的文件中的类名称，必须和文件存储结构对应。
//因为用户可能希望有自己的__autoload逻辑，为了避免产生混淆，kod所有的类名，都以kod开头，这样就可以通过这个的不同来走不同的逻辑。
function __autoload($model){
	include_once(dirname(KOD_DIR_NAME)."/".str_replace("_", "/",$model).".php");
}