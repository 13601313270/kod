<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 2017/3/20
 * Time: 下午2:17
 */
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set('PRC');
/*
*设置kod环境变量
*/
define("webDIR",dirname(__FILE__)."/http/");//网站根目录
define("KOD_SMARTY_TEMPLATE_DIR",dirname(__FILE__)."/http/");
define("KOD_REWRITE_HTML_LINK",true);
define('KOD_SMARTY_COMPILR_DIR',dirname(__FILE__).'/html/smarty_cache');

define('KOD_MYSQL_SERVER','118.190.95.219');
define('KOD_MYSQL_USER','root');
define('KOD_MYSQL_PASSWORD','1082322$%&whr309568486');
define('KOD_COMMENT_MYSQLDB','dbName');

define("KOD_SMARTY_CSS_DIR",dirname(__FILE__)."/cssCreate/");
define('KOD_SMARTY_CSS_HOST','http://118.190.95.234/androidCreate/cssCreate/');



spl_autoload_register(function($model){
    $classAutoLoad = array(
        'projectClass' => 'include/project.php',
        'mem' => 'include/mem.php',
        'pvClass' => 'include/pv.php',
        'articleTypeClass' => 'include/articleType.php',
        'articleClass' => 'include/article.php',
        'webObjectbase' => 'include/webObjectbase.php',
    );
    if(isset($classAutoLoad[$model])){
        include_once $classAutoLoad[$model];
    }
});
/*
 * 进行rewrite规则
*/
kod_web_rewrite::init(dirname(__FILE__)."/rewrite.conf");
$path = kod_web_rewrite::getPathByUrl($_SERVER["SCRIPT_URL"].$_SERVER["QUERY_STRING"]);

$houzhui = explode('.',$_SERVER["SCRIPT_URL"]);
$houzhui = end($houzhui);
if($houzhui=='css'){
    header("Content-Type: text/css");
}elseif(in_array($houzhui,array('css','png','jpeg','jpg','gif'))){
    header("Content-Type: image/".$houzhui);
}
if($path!=""){
    $new = parse_url($path);
    $_SERVER["SCRIPT_URL"] = $new["path"];
    $_SERVER["SCRIPT_URI"] = "http://".$_SERVER["HTTP_HOST"].$new["path"];
    $_SERVER["REQUEST_URI"] = $new["path"]."?".$new["query"];
    $_SERVER["SCRIPT_FILENAME"] = dirname(__FILE__)."/http".$new["path"];
    $_SERVER["SCRIPT_NAME"] = $new["path"];
    $_SERVER["PHP_SELF"] = $new["path"];

    $columnList = explode("&",$new["query"]);
    $_GET = array();
    foreach($columnList as $column){
        $temp = explode("=",$column);
        $key = urldecode($temp[0]);
        if(strpos($key,"[")>-1){
            if(preg_match("/(\w+)\[(\w+)\]/",$key,$match)){
                if(!isset($_GET[$match[1]])){
                    $_GET[$match[1]] = array();
                }
                $_GET[$match[1]][$match[2]] = urldecode($temp[1]);
            }
        }else{
            $_GET[$key] = urldecode($temp[1]);
        }
        $_REQUEST[$temp[0]] = $temp[1];
    }
    unset($path);
    unset($new);
    if(substr($_SERVER["SCRIPT_FILENAME"],strlen($_SERVER["SCRIPT_FILENAME"])-1,1)=="/"){
        include_once($_SERVER["SCRIPT_FILENAME"]."index.php");
    }else{

        include_once($_SERVER["SCRIPT_FILENAME"]);
    }
}else{
    $_SERVER["SCRIPT_FILENAME"] = webDIR.$_SERVER["SCRIPT_URL"];
    if(substr($_SERVER["SCRIPT_FILENAME"],strlen($_SERVER["SCRIPT_FILENAME"])-1,1)=="/"){
        $_SERVER["SCRIPT_FILENAME"] = $_SERVER["SCRIPT_FILENAME"]."index.php";
    }
    include_once($_SERVER["SCRIPT_FILENAME"]);exit;
    echo "404";exit;
}