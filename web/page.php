<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 16/8/6
 * Time: 下午3:23
 */

class kod_web_page{
	public static function output($_output,$template){
		preg_match_all('/<link rel="stylesheet" type="text\/css" href="(.*?)"\/>/is',$_output,$match);
		if(strpos($_output,'</head>')>-1){
			foreach($match[0] as $cssHtml){
				$_output = str_replace($cssHtml,'',$_output);
				$_output = str_replace('</head>',"\t".$cssHtml."\n</head>",$_output);
			}
		}
//		if(KOD_REWRITE_HTML_LINK){
//			//把.php的文件，改为rewrite规则的文件
//			preg_match_all("/(<a[^\>]*href=[\"|\'])(.*?)([\"|\'][^\>]*>)/",$_output,$matchLink);
//			$_output = preg_replace_callback("/(<a[^\>]*href=[\"|\'])(.*?)([\"|\'][^\>]*>)/",function($matchLink){
//				$rewriteUrl = kod_web_rewrite::getUrlByPath($matchLink[2]);
//				if($rewriteUrl){
//					return $matchLink[1].$rewriteUrl.$matchLink[3];
//				}else{
//					return $matchLink[0];//如果没有匹配rewrite配置，则原样放回
//				}
//			},$_output);
//		}
		return $_output;
	}
	public function fetch($smartyTpl,$assiObj){
		$smartyObject = new kod_smarty_smarty();
//		if(count($this->smartyPlutPath)>=1){
//			foreach($this->smartyPlutPath as $k=>$v){
//				$smartyObject->addPluginsDir($v);
//			}
//		}

		$smartyObject->registerFilter('output',array('kod_web_page','output'));
		$smartyObject->compile_dir = KOD_SMARTY_COMPILR_DIR;//设置编译目录
		//$smartyObject->template_dir = KOD_DIR_NAME."/testRun/";//设置模板目录
		//$smartyObject->config_dir = "smarty/templates/config";//目录变量
		//$smartyObject->cache_dir = "smarty/templates/cache"; //缓存文件夹

		foreach($assiObj as $k=>$v){
			$smartyObject->assign($k,$v);
		}
		$smartyObject->fetch($smartyTpl, null, null, null, true);
	}
}