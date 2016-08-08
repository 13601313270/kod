<?php
/**
 * Created by PhpStorm.
 * User: mfw
 * Date: 15/4/10
 * Time: 下午7:22
 */
include_once KOD_DIR_NAME."/smarty/Smarty.class.php";
final class kod_smarty_smarty extends Smarty{
	public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false){
		if($this->compile_dir==null){
			throw new Exception("您并未给Smarty的实例配置预编译文件存储路径属性compile_dir，请进行配置。您也可以在加载文件中配置静态变量【KOD_SMARTY_COMPILR_DIR】作为smarty的默认值",1);
		}
		return parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
	}
	public static $test = true;//是否是测试开发

	//测试环境下追加的一些数据展示信息
	public static function compilerTestAfter($data){
		if(self::$test==true){
			return '<script>
						function dump(arr,level) {
							var huanhang = "<br//>";
							var dumped_text = "";
							if(!level) level = 0;
							var level_padding = "";
							for(var j=0;j<level+1;j++) level_padding += "&nbsp;&nbsp;&nbsp;&nbsp;";
							if(arr instanceof Array){
								if(level==0){
									dumped_text+="["+huanhang;
								}
								for(var item in arr) {
									var value = arr[item];
									if(value instanceof Array){
										dumped_text += level_padding + "["+huanhang;
										dumped_text += dump(value,level+1);
										dumped_text += level_padding + "]+huanhang";
									}else if(typeof(value) == "object") {
										dumped_text += level_padding + "{"+huanhang;
										dumped_text += dump(value,level+1);
										dumped_text += level_padding + "}";
										if(item==arr.length-1){
											dumped_text += huanhang;
										}else{
											dumped_text += ","+huanhang;
										}
									} else if(typeof(value)=="number" ){
										if(item==0){
											dumped_text+=level_padding;
										}
										dumped_text += value;
										if(item!=arr.length-1){
											dumped_text += ",";
										}else{
											dumped_text += huanhang;
										}
									} else{
										dumped_text += level_padding + \'"\' + value + "\","+huanhang;
									}
								}
								if(level==0){
									dumped_text+="]";
								}
							}else if(typeof(arr) =="object") {
								if(level==0){
									dumped_text+="{"+huanhang;
								}
								for(var item in arr) {
									var value = arr[item];
									var itemHtml = "<span style=\'color:rgb(158,78,163)\'>"+item+"</span>";
									if(value instanceof Array){
										dumped_text += level_padding + "\'" + itemHtml + "\':<span style=\'color:rgb(49,31,215)\'>[</span>"+huanhang;
										dumped_text += dump(value,level+1);
										dumped_text += level_padding + "<span style=\'color:rgb(49,31,215)\'>]</span>";
									}else if(typeof(value) == "object") {
										if(level==0 && value._loop==true){
											continue;
										}
										dumped_text += level_padding + "\'" + itemHtml + "\':{"+huanhang;
										dumped_text += dump(value,level+1);
										dumped_text += level_padding + "}";
									} else {
										dumped_text += level_padding + "\'" + itemHtml + "\' : " + value;
									}
									dumped_text +=","+huanhang;
								}
								dumped_text = dumped_text.substring(0,dumped_text.length-huanhang.length-1)+huanhang;
								if(level==0){
									dumped_text+="}";
								}
							} else {
								//dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
								dumped_text = arr;
							}
							return dumped_text;
						}
						function init(){
							var data = '.json_encode($data).';
							testwin.document.body.innerHTML="";
							for(var i in data){
								testwin.document.body.innerHTML += "<p>"+i+"=>"+dump(data[i])+"</p>";
							}
						}
						var testwin=window.open("Called.html","test");
						setTimeout(init,1000);
					</script>';
		}
	}
	//对smarty里面include方法加载的tpl文件生成的php编译文件进行加工，再存入硬盘
	public static function compilerIncludeTplHtml($allHtml,$tplFile){
		//分解css
		if(defined('KOD_SMARTY_CSS_DIR')){
			preg_match_all("/<style>(.*?)<\/style>/is",$allHtml,$match);
			$cssHtmlArr = $match[1];

			if(!empty($cssHtmlArr)){//如果匹配到了style内容
				//写入文件
				$content = "";
				foreach($cssHtmlArr as $cssHtml){
					$cssHtml = str_replace("\n",'',$cssHtml);
					$cssHtml = str_replace("\t",'',$cssHtml);
					preg_match_all("/@keyframes\s+(([^{|}]+?{\s*[^}]+?\s*})+})/is",$cssHtml,$match2);
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
				$fileName = str_replace(array(".tpl","/","."),array("","+","_"),$tplFile).".css";
				$file = fopen(KOD_SMARTY_CSS_DIR.$fileName,'w'); // a模式就是一种追加模式，如果是w模式则会删除之前的内容再添加
				if($file===false){
					throw new Exception("写入文件失败，请保证路径【".KOD_SMARTY_CSS_DIR."】存在，并有写入权限");
				}
				fwrite($file,$content);
				fclose($file);
				unset($file);
				//获取可以访问生成css的url地址
				$cssUrl = "/".str_replace(webDIR,"",KOD_SMARTY_CSS_DIR).$fileName;
				if($cssUrl==""){
					throw new Exception("生成的css文件【".str_replace(webDIR,"",KOD_SMARTY_CSS_DIR).$fileName."】，并不能生成对应的url网址，请配置对应的rewrite规则");
				}
				$cssLinkHtml = '<link rel="stylesheet" type="text/css" href="'.$cssUrl.'?'.time().'"/>';
				if(strpos($allHtml,'</head>')>-1){
					$allHtml = preg_replace("/<style>(.*?)<\/style>/is","",$allHtml);
					$allHtml = str_replace('</head>',"\t".$cssLinkHtml."\n</head>",$allHtml);
				}else{
					$allHtml = preg_replace("/<style>(.*?)<\/style>/is",$cssLinkHtml,$allHtml);
				}
			}else{
			}
		}
		//$allHtml = '<!--没有css2-->'.$allHtml;
		return $allHtml;
	}

	//生成的php文件进行加工，再存入文件
	public static function compilerAfter($content,$file){
		//将零散的css定义统一放到头部加载
		preg_match_all('/<link rel="stylesheet" type="text\/css" href="(.*?)"\/>/is',$content,$match);
		if(strpos($content,'</head>')>-1){
			foreach($match[0] as $cssHtml){
				$content = str_replace($cssHtml,'',$content);
				$content = str_replace('</head>',"\t".$cssHtml."\n</head>",$content);
			}
		}
		//将零散的js定义统一放到头部加载
		preg_match_all('/<script type="text\/javascript" src="(.*?)"><\/script>/is',$content,$match);
		if(strpos($content,'</head>')>-1){
			foreach($match[0] as $jsHtml){
				$content = str_replace($jsHtml,'',$content);
				$content = str_replace('</head>',"\t".$jsHtml."\n</head>",$content);
			}
		}

		//增加上数据统计的信息
		/*
		$content = str_replace("</html>",'<?php
			$appendTestArr = array();
		 	foreach($_smarty_tpl->smarty->template_objects as $k=>$v){
		 		$appendTestArr[$k] = $v->tpl_vars;
		 	}
		 	echo kod_smarty_smarty::compilerTestAfter($appendTestArr);
		 	?></html>',$content);
		*/
		/*$content = str_replace("</html>",'<?php print_r($_smarty_tpl);?></html>',$content);*/
		return $content;
	}
}?>