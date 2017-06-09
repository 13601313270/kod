<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 14-9-4
 * Time: 上午12:57
 */

//webDIR参数末尾要带上【/】


define('KOD_DIR_NAME',dirname(__FILE__));//kod框架所在的地址
define('KOD_DS', DIRECTORY_SEPARATOR);//文件夹分隔符

//mysql默认服务器
if(!defined('KOD_MYSQL_SERVER')){
	define('KOD_MYSQL_SERVER','localhost');
}

//mysql默认数据库
if(!defined('KOD_COMMENT_MYSQLDB')){
	define('KOD_COMMENT_MYSQLDB','jihe');
}

//mysql默认登录账号
if(!defined('KOD_MYSQL_USER') || !defined('KOD_MYSQL_PASSWORD') ){
	echo '请设置mysql默认登录账号和密码配置【KOD_MYSQL_USER】【KOD_MYSQL_PASSWORD】';
}

//mysql数据库编码
if(!defined('KOD_COMMENT_MYSQLDB_CHARSET')){
	define('KOD_COMMENT_MYSQLDB_CHARSET','utf8');
}
//mysql模式，开发过程中建议使用TRADITIONAL，以便获取更准确的错误提示,ANSI
if(!defined('KOD_SQL_MODE')){
	define('KOD_SQL_MODE','ANSI');
}

//smarty框架预编译存储路径
if(!defined('KOD_SMARTY_COMPILR_DIR')){
	define('KOD_SMARTY_COMPILR_DIR','/var/www/smarty_cache');
}

//smarty框架模板文件默认路径
if(!defined('KOD_SMARTY_TEMPLATE_DIR')){
	//define('KOD_SMARTY_TEMPLATE_DIR',"");
}

//smarty生成可访问CSS静态文件默认路径
if(!defined('KOD_SMARTY_CSS_DIR')){
	//define('KOD_SMARTY_TEMPLATE_DIR',"");
}

//是否自动把html中的链接地址，进行反向重定向
if(!defined('KOD_REWRITE_HTML_LINK')){
	define('KOD_REWRITE_HTML_LINK',false);
}
//kod_web_rewrite::getUrlByPath

//define("KOD_SMARTY_TEMPLETE_PATH","");//smarty根目录
//define("KOD_WEB_","./");
//是否开启memcache
if(!defined('KOD_MEMCACHE_OPEN')){
	define('KOD_MEMCACHE_OPEN',false);
}
if(KOD_MEMCACHE_OPEN){
	if(!defined('KOD_MEMCACHE_HOST')){
		define('KOD_MEMCACHE_HOST','localhost');
	}
	if(!defined('KOD_MEMCACHE_PORT')){
		define('KOD_MEMCACHE_PORT',11211);
	}
}

//是否开启metaPHP
if(!defined('KOD_METAPHP_OPEN')){
	define('KOD_METAPHP_OPEN',true);
}
if(KOD_METAPHP_OPEN){
	include_once(KOD_DIR_NAME.'/metaPHP/include.php');
	//必须定义一个自定义的存储函数
	if(!function_exists('metaPHPSave_Function')){
		function metaPHPSave_Function($file,$code){
			var_dump('meta计划往'.$file.'存储代码,可以通过自定义metaPHPSave_Function函数来实现自定义的存储');
			echo $code;
		}
	}
}

date_default_timezone_set('PRC');

spl_autoload_register(function($model){
	if(strpos($model,'kod_')===0){
		if(!include_once(dirname(KOD_DIR_NAME).KOD_DS.str_replace('_', KOD_DS,$model).'.php')){
			throw new Exception('类【'.$model.'】不存在，KOD自动加载机制尝试加载'.dirname(KOD_DIR_NAME).KOD_DS.str_replace('_',KOD_DS,$model).'.php发现文件不存在。');
		}
	}
});

if(function_exists('kod_ControlAutoLoad')){
	spl_autoload_register('kod_ControlAutoLoad');
}else{
	throw new Exception('请定义kod_ControlAutoLoad控制器使用的自动加载类');
}