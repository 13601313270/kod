	{
		'js':['http://cdn.staticfile.org/Plupload/2.1.1/plupload.full.min.js','http://cdn.staticfile.org/qiniu-js-sdk/1.0.14-beta/qiniu.min.js','http://42.96.173.125/js/qiniu.js'],
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