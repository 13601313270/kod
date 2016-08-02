	{
		'js':['http://jssdk.demo.qiniu.io/js/plupload/plupload.full.min.js','http://jssdk.demo.qiniu.io/js/qiniu.js','http://www.goubangwang.com/js/qiniu.js'],
		'list':function(data){
			return $('<td><img src="'+data+'" width=100></td>');
		},
		'writeHTML':function(columnName,data){
			var randomId = parseInt(Math.random()*100000);
			var tempTd = $("<input class='form-control' name='"+columnName+"' value='"+(data?data:'')+"'/>"+
					"<div id='duanluoImg"+randomId+"'><input type='button' value='提交文件' id='duanluoImgButton"+randomId+"' value='"+(data?data:'')+"'/></div>" );
			setTimeout(function(){
				qiniuFileUpButton("duanluoImg"+randomId,"duanluoImgButton"+randomId,function(up, files){
					//$("#userPortraitImgPro").html("上传中...");
				},function(file){
					$("#duanluoImgButton"+randomId).parent().prev().val(file);
				});
			},500);
			return tempTd;
		}
	}