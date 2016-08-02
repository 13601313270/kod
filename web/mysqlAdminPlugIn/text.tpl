	{
		'js':['http://ueditor.baidu.com/ueditor/ueditor.all.js'],
		'writeHTML':function(columnName,data){
			var randomTextId = "textareaRand"+parseInt(Math.random()*10000);
			var temp = $("<div class='baiduEditor'><textarea id='"+randomTextId+"' style='width:1000px;' name='"+columnName+"'>"+"</textarea></div>");
			setTimeout(function(){
				UE.getEditor(randomTextId,{
					//这里可以选择自己需要的工具按钮名称,此处仅选择如下五个
					toolbars:[[
						'fullscreen', 'source', '|', 'undo', 'redo', '|',
						'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
						'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
						'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
						'directionalityltr', 'directionalityrtl', 'indent', '|',
						'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
						'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
						'simpleupload', 'insertimage', 'emotion', 'scrawl', 'insertvideo', 'music', 'attachment', 'map', 'gmap', 'insertframe', 'insertcode', 'webapp', 'pagebreak', 'template', 'background', '|',
						'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
						'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
						'print', 'preview', 'searchreplace', 'help', 'drafts'
					]],
					//focus时自动清空初始化时的内容
					autoClearinitialContent:true,
					//关闭字数统计
					wordCount:false,
					//关闭elementPath
					elementPathEnabled:false,
					//默认的编辑区域高度
					initialFrameHeight:300,
					theme:'default',
					themePath:'http://ueditor.baidu.com/ueditor/themes/',
					UEDITOR_HOME_URL:'http://ueditor.baidu.com/ueditor/',
					initialContent:data?data:'',
					autoClearinitialContent:false,
					//更多其他参数，请参考ueditor.config.js中的配置项
					serverUrl: 'http://admin.appcpu.cn/admin/http/imgUp.php',

					imageUrl: "http://admin.appcpu.cn/admin/http/imgUp.php?action=uploadimage",
					imagePath: "/ueditor/php/",
					imageFieldName: "upfile",
					imageMaxSize: 2048,
					imageAllowFiles: [".png", ".jpg", ".jpeg", ".gif", ".bmp"],

					catchRemoteImageEnable:false
				});
			},300);
			return temp;
		}
	}