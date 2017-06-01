<?php
/**
 * Created by PhpStorm.
 * User: 王浩然
 * Date: 16/8/6
 * Time: 下午3:23
 */
include_once KOD_DIR_NAME."/smarty/libs/Smarty.class.php";
class kod_web_page extends stdClass{
	final public function __construct()
	{
		$this->beforeRun();
	}

	public function beforeRun(){
	}
	public function beforeFetch(){

	}
	public static function machiningTemplate($tpl_source, Smarty_Internal_Template $template){
		//分解css
		if(defined('KOD_SMARTY_CSS_DIR')){
			preg_match_all("/<style>(.*?)<\/style>/is",$tpl_source,$match);
			$cssHtmlArr = $match[1];
			if(!empty($cssHtmlArr)){//如果匹配到了style内容
				//写入文件
				$content = "";
				foreach($cssHtmlArr as $cssHtml){
					$cssHtml = str_replace("\n",'',$cssHtml);
					$cssHtml = str_replace("\t",'',$cssHtml);
					preg_match_all("/@keyframes\s+(([^{|}]+?{\s*[^}]+?\s*})+\s*})/is",$cssHtml,$match2);
					foreach($match2[1] as $v){
						$cssHtml.='@-moz-keyframes '.$v;
						$cssHtml.='@-webkit-keyframes '.$v;
						$cssHtml.='@-o-keyframes '.$v;
					}
					preg_match_all("/animation\s*:\s*[^;]+[;|}]/is",$cssHtml,$match2);
					foreach($match2[0] as $v){
						$cssHtml.='-moz-'.$v;
						$cssHtml.='-webkit-'.$v;
						$cssHtml.='-o-'.$v;
					}
					$content .=$cssHtml;
				}
				//array(KOD_DIR_NAME,'~KOD+'),
				$fileName = str_replace(KOD_DIR_NAME,'~kod',$template->template_resource);
				$fileName = str_replace(array(".tpl","/","."),array("","+","_"),$fileName).".css";
				$file = fopen(KOD_SMARTY_CSS_DIR.$fileName,'w'); // a模式就是一种追加模式，如果是w模式则会删除之前的内容再添加
				if($file===false){
					throw new Exception("写入文件失败，请保证路径【".KOD_SMARTY_CSS_DIR."】存在，并有写入权限");
				}
				fwrite($file,$content);
				fclose($file);
				unset($file);
				//获取可以访问生成css的url地址
				if(defined('KOD_SMARTY_CSS_HOST')){
					$cssUrl = KOD_SMARTY_CSS_HOST.$fileName;
				}else{
					$cssUrl = "/".str_replace(webDIR,"",KOD_SMARTY_CSS_DIR).$fileName;
					if($cssUrl==""){
						throw new Exception("生成的css文件【".str_replace(webDIR,"",KOD_SMARTY_CSS_DIR).$fileName."】，并不能生成对应的url网址，请配置对应的rewrite规则");
					}
				}
				$cssLinkHtml = '<link rel="stylesheet" type="text/css" href="'.$cssUrl.'?'.time().'"/>';
				if(strpos($tpl_source,'</head>')>-1){
					$tpl_source = preg_replace("/<style>(.*?)<\/style>/is","",$tpl_source);
					$tpl_source = str_replace('</head>',"\t".$cssLinkHtml."\n</head>",$tpl_source);
				}else{
					$tpl_source = preg_replace("/<style>(.*?)<\/style>/is",$cssLinkHtml,$tpl_source);
				}

			}else{
			}
			$tpl_source = $tpl_source;
		}
		return $tpl_source;
	}
	public static function machiningPHP($phoCode, Smarty_Internal_Template $template){
		//将零散的css定义统一放到头部加载
		preg_match_all('/<link rel="stylesheet" type="text\/css" href="(.*?)"\/>/is',$phoCode,$match);
		if(strpos($phoCode,'</head>')>-1){
			foreach($match[0] as $cssHtml){
				$phoCode = str_replace($cssHtml,'',$phoCode);
				$phoCode = explode('<body>',$phoCode);
				$phoCode[0] = str_replace('</head>',"\t".$cssHtml."\n</head>",$phoCode[0]);
				$phoCode = implode('<body>',$phoCode);
			}
		}

		//将零散的js定义统一放到头部加载
		preg_match_all('/<script type="text\/javascript" src="(.*?)"><\/script>/is',$phoCode,$match);
		if(strpos($phoCode,'</head>')>-1){
			foreach($match[0] as $jsHtml){
				$phoCode = str_replace($jsHtml,'',$phoCode);
				$phoCode = explode('<body>',$phoCode);
				$phoCode[0] = str_replace('</head>',"\t".$jsHtml."\n</head>",$phoCode[0]);
				$phoCode = implode('<body>',$phoCode);

			}
		}

		//增加上数据统计的信息
		/*
		$phoCode = str_replace("</html>",'<?php
			$appendTestArr = array();
		 	foreach($_smarty_tpl->smarty->template_objects as $k=>$v){
		 		$appendTestArr[$k] = $v->tpl_vars;
		 	}
		 	echo kod_smarty_smarty::compilerTestAfter($appendTestArr);
		 	?></html>',$phoCode);
		*/
		/*$phoCode = str_replace("</html>",'<?php print_r($_smarty_tpl);?></html>',$phoCode);*/
		return $phoCode;
	}
	public static function machiningHtml($_output,$template){
		preg_match_all('/<link rel="stylesheet" type="text\/css" href="(.*?)"\/>/is',$_output,$match);
		if(strpos($_output,'</head>')>-1){
			foreach($match[0] as $cssHtml){
				$_output = str_replace($cssHtml,'',$_output);
				$_output = explode('<body>',$_output);
				$_output[0] = str_replace('</head>',"\t".$cssHtml."\n</head>",$_output[0]);
				$_output = implode('<body>',$_output);
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
	public function fetchJSON(){
		$allData = array();
		foreach($this as $k=>$v){
			$allData[$k] = $v;
		}
		echo json_encode($allData);
	}
	public function fetch($smartyTpl,$returnHtml=false){
		$this->beforeFetch();
		$smartyObject = new kod_web_smarty();
//		$smartyObject = new Smarty();

//		$smartyObject->caching = true;
//		if(count($this->smartyPlutPath)>=1){
//			foreach($this->smartyPlutPath as $k=>$v){
//				$smartyObject->addPluginsDir($v);
//			}
//		}
		$smartyObject->registerFilter('pre',array($this,'machiningTemplate'));
		$smartyObject->registerFilter('post',array($this,'machiningPHP'));
		$smartyObject->registerFilter('output',array($this,'machiningHtml'));


		$smartyObject->setCompileDir(KOD_SMARTY_COMPILR_DIR);//设置编译目录
		//$smartyObject->template_dir = KOD_DIR_NAME."/testRun/";//设置模板目录
		//$smartyObject->config_dir = "smarty/templates/config";//目录变量
//		$smartyObject->setCacheDir("smarty/templates/cache"); //缓存文件夹

		foreach($this as $k=>$v){
			$smartyObject->assign($k,$v);
		}
		if($returnHtml == true){
			return $smartyObject->fetch($smartyTpl);
		}else{
			$smartyObject->display($smartyTpl);
		}
	}
}