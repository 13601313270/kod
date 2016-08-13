<!--
js依赖，调用方必须加载jquery插件
css依赖，按照bootstrap规范定义，所以可以随意加载一套bootstrap主题，或者自己按照bootstrap规范定义样式即可
-->
<style>
	@font-face {
		font-family: 'iconfont';
		src: url('//at.alicdn.com/t/font_1449982954_687468.eot'); /* IE9*/
		src: url('//at.alicdn.com/t/font_1449982954_687468.eot?#iefix') format('embedded-opentype'), /* IE6-IE8 */
		url('//at.alicdn.com/t/font_1449982954_687468.woff') format('woff'), /* chrome、firefox */
		url('//at.alicdn.com/t/font_1449982954_687468.ttf') format('truetype'), /* chrome、firefox、opera、Safari, Android, iOS 4.2+*/
		url('//at.alicdn.com/t/font_1449982954_687468.svg#iconfont') format('svg'); /* iOS 4.1- */
	}
	.updateHtml{
		width: 100%;min-height:100%;background-color: rgba(55, 55, 55, 0.62);position:fixed;z-index:99;top: 0;left:0;
	}
	.updateHtml .updateHtmlContent{
		background-color: white;width:90%;max-height: 100%;margin:10px auto 0 auto;padding:10px;border: solid 1px #d2d2d9;
	}
	.updateHtml .updateHtmlContent>table{
		width:100%;
	}
	.columnValueArr{
		border:solid 1px #818181;
	}
	.columnValueArr>table{
		width: 100%;
	}
	#fastTableInfo .deleteAll{
		display: none;
		left:0;
		top:0;
		position:fixed;
		left:50%;
		top:50%;
		width:200px;
		margin-left: -100px;
		margin-top: -50px;
		background-color: white;
		border:solid 1px black;
		padding: 10px;
		border-radius: 2px;
		height:100px;
	}
	#fastTableInfo .deleteAll>div{
	}
</style>
<div id="fastTableInfo" class="container">
	<div class="deleteAll">
		<div>
			<input type="button" value="删除">
		</div>
	</div>
	<div style="width:100%;height:30px;">
		<div id="fastTableInfoPage"></div>
		<button class="btn btn-default" style="float: right;" onclick="mysqlAJAXClass.initInsertHtml()">添加</button>
	</div>
	<table class="table table-striped table-hover table-responsive table-bordered">
		<thead>
		<tr>
			<th><input type="checkbox"/></th>
			{foreach $column as $k=>$v}
				{if $v.listShowType!='hidden'}
					<th column="{$k}">
						<p>{$v.title}</p>
						{if $v.listsearch}
							{if count($v.listsearch)>1}
								<select>
									{foreach $v.listsearch as $kk=>$vv}
										{if isset($vv.title)}
											<option value="{$vv.match|escape:"html"}">{$vv.title}</option>
										{else}
											<option value="{$vv.match|escape:"html"}">{$vv.match}</option>
										{/if}
									{/foreach}
								</select>
							{else}
								{if isset($v.listsearch[0].title)}
									{$v.listsearch[0].title}
								{/if}
							{/if}
							{foreach $v.listsearch as $num=>$search}
								{if substr_count($search.match,'?')==0}
								{elseif $num==0}
									{section name=loop loop=substr_count($search.match,'?')}
										<input match="{$search.match|escape:"html"}"/>
									{/section}
								{else}
									{section name=loop loop=substr_count($search.match,'?')}
										<input tmp="{substr_count($search.match,'?')}" match="{$search.match|escape:"html"}" style="display: none;"/>
									{/section}
								{/if}
							{/foreach}
						{/if}
					</th>
				{/if}
			{/foreach}
			<th>操作</th>
		</tr>
		</thead>
		<tbody></tbody>
	</table>
	<script>
		var column = {json_encode($column)};
		var perPage ={$perPage};
		var page = 0;
		var key = "{$key}";
		var searchArr = {
		};
		var htmlObject = {
			{foreach $allColumnDataType as $typeName}
				'{$typeName}':{include file="./mysqlAdminPlugIn/{$typeName}.tpl"},
			{/foreach}
		};
		var oHead = document.getElementsByTagName('HEAD').item(0);
		for(var i in htmlObject){
			if(htmlObject[i].js){
				for(var j=0;j<htmlObject[i].js.length;j++){
					var oscript= document.createElement("script");
					oscript.type = "text/javascript";
					oscript.src = htmlObject[i].js[j];
					oHead.appendChild( oscript);
				}
			}
		}
		var mysqlAJAXClass = {
			getList:function(){
				$.post("",{
					'function':'getList',
					'page' : page,
					'searchArr':searchArr
				},function(data_){
					var data_ = JSON.parse(data_);
					//分页
					(function(){
						var pageButtonsHtml = '';
						for(var i=0;i<data_.dataCount/perPage;i++){
							if(i<=page-10){
								if(i==page-10){
									pageButtonsHtml+='<span data-page="'+i+'">更多</span>';
								}
							}else if(i>=page+10){
								if(i==page+10){
									pageButtonsHtml+='<span data-page="'+i+'">更多</span>';
								}
							}else if(i==page){
								pageButtonsHtml+='<span class="select btn btn-link" style="padding: 5px 5px;" data-page="'+i+'">'+(i+1)+'</span>';
							}else{
								pageButtonsHtml+='<span class="btn btn-link" style="padding: 5px 5px;" data-page="'+i+'">'+(i+1)+'</span>';
							}
						}
						pageButtonsHtml+='总共'+data_.dataCount+'条，'+Math.ceil(data_.dataCount/perPage)+'页';
						$('#fastTableInfo #fastTableInfoPage').html(pageButtonsHtml);
					})();
					//当前页数据
					var data = data_.data;
					var tbody = $("#fastTableInfo table tbody");
					tbody.html("");
					for(var i=0;i<data.length;i++){
						var tr = $("<tr data-id='"+data[i][key]+"'></tr>");
						tr.append($("<td class='select'><input data-id='"+data[i][key]+"' type='checkbox'/></td>"));
						for(var j in column){
							if(column[j].listShowType!='hidden'){
								if(data_.dataHtml[i] && data_.dataHtml[i][j]){
									tr.append($("<td>"+(data_.dataHtml[i][j]?data_.dataHtml[i][j]:'')+"</td>"));
								} else if(column[j].dataList!=undefined && column[j].dataList.type=='Enum'){
									tr.append($("<td>"+column[j].dataList.data[data[i][j]]+"</td>"));
								}else if(htmlObject[column[j].dataType]!=undefined && htmlObject[column[j].dataType].list!=undefined){
									tr.append(htmlObject[column[j].dataType].list(data[i][j]));
								}else{
									tr.append($("<td>"+(data[i][j]?data[i][j]:'')+"</td>"));
								}
							}
						}
						tr.append($("<td style='text-align: center;'><button class='updateButton btn btn-default'>修改</button><button class='deleteButton btn btn-default'>删除</button></td>"));
						tbody.append(tr);
					}
				})
			},
			initInsertHtml:function(){
				var updateHtmlDom = $('<div class="updateHtml"></div>');
				var insertDom = $('<div class="updateHtmlContent"></div>');
				mysqlAJAXClass.makeHtml(insertDom,column);
				var buttonsDiv = $('<div class="updateButtons"></div>');
				buttonsDiv.append($("<button class='btn btn-danger' onclick='mysqlAJAXClass.insertDataSent(this)'>保存</button>"));
				buttonsDiv.append($("<button class='btn btn-default' onclick=\"$(this).parents('.updateHtml').remove()\">取消</button>"));
				insertDom.append(buttonsDiv);
				updateHtmlDom.append(insertDom);
				$("body").append(updateHtmlDom);
			},
			getDataByTable:function(table){
				var columnData = {
				};
				var allsolumnData = table.find(">tbody>tr>.columnValue");
				for(var i=0;i<allsolumnData.length;i++){
					if($(allsolumnData[i]).is('.columnValueObj')){
						columnData[$(allsolumnData[i]).parent().attr('data-name')] = arguments.callee($(allsolumnData[i]).find('>table'));
					}else if($(allsolumnData[i]).is('.columnValueArr')){
						var insertArr = [];
						var allTables = $(allsolumnData[i]).find('>table');
						for(var j=0;j<allTables.length;j++){
							insertArr.push( arguments.callee($(allTables[j])) );
						}
						columnData[$(allsolumnData[i]).parent().attr('data-name')] = insertArr;
					}else if($(allsolumnData[i]).find('[name]').attr('name')==''){
						if($(allsolumnData[i]).find('[name]').attr('type')=='number'){
							columnData = parseInt($(allsolumnData[i]).find('[name]').val());
						}else{
							columnData = $(allsolumnData[i]).find('[name]').val();
						}
					}else{
						if($(allsolumnData[i]).find('[name]').attr('type')=='number'){
							columnData[$(allsolumnData[i]).find('[name]').attr('name')] = parseInt($(allsolumnData[i]).find('[name]').val());
						}else{
							columnData[$(allsolumnData[i]).find('[name]').attr('name')] = $(allsolumnData[i]).find('[name]').val();
						}
					}
				}
				return columnData;
			},
			insertDataSent:function(sentButton){
				$(".updateHtmlContent .baiduEditor").each(function(){
					$(this).find('textarea').val($(this).find(".edui-editor-iframeholder iframe")[0].contentDocument.body.innerHTML);
				});
				var dataOfColumn = this.getDataByTable($(sentButton).parents('.updateHtmlContent:eq(0)').find('>table'));
				for(var i in dataOfColumn){
					if(dataOfColumn[i] instanceof Array==true){
						dataOfColumn[i] = JSON.stringify(dataOfColumn[i]);
					}else if(typeof(dataOfColumn[i])=='object'){
						dataOfColumn[i] = JSON.stringify(dataOfColumn[i]);
					}
				}
				var sendData = {
					function:"insertData",
					data:dataOfColumn
				};
				$.post("",sendData,function(data){
					mysqlAJAXClass.getList();
					$(sentButton).parents('.updateHtml').remove();
				});
			},
			makeHtml:function(dom,column,data,deleteButton){
				if(deleteButton){
					var div = $("<table style='border: solid 1px;margin-bottom: 10px;'></table>");
					div.append($('<tr style="background-color:#95A7BD">' +
							'<td></td>' +
							'<td></td>' +
							'<td style="font-family:iconfont"><span class="deleteArrButton" style="float:right;color:#970000;">&#xe601;</span></td>' +
						'</tr>'));
				}else{
					var div = $("<table class='table table-striped table-hover table-responsive table-bordered'></table>");
				}
				for(var i in column){
					if( column[i].dataType instanceof Array==true){
						var thisFunction = arguments.callee;
						(function(){
							var newInsertDiv = $("<td class='columnValue columnValueArr'></td>");
							//如果数组存的是直接的值
							if(column[i].dataType[0]['title']!=undefined && typeof column[i].dataType[0]['title']=='string'){
								if(data==undefined || data[i]==undefined){
									thisFunction(newInsertDiv,{
										'':column[i].dataType[0]
									},null,true);
								}else{
									for(var j=0;j<data[i].length;j++){
										thisFunction(newInsertDiv,{
											'':column[i].dataType[0]
										},{
											'':data[i][j]
										},true);
									}
								}
							}else{
								//数组存的是对象
								if(data==undefined || data[i]==undefined){
									thisFunction(newInsertDiv,column[i].dataType[0],null,true);
								}else{
									for(var j=0;j<data[i].length;j++){
										thisFunction(newInsertDiv,column[i].dataType[0],data[i][j],true);
									}
								}
							}
							newInsertDiv.append($('<button class="addArrDataButton">增加</button>'));
							var temp = $("<tr class='column' data-name='"+i+"'>" +
									"<td class='columnName'>"+column[i].title+"</td><td></td></tr>");
							temp.append(newInsertDiv);
							div.append(temp);
						})();
					}else if(typeof(column[i].dataType)=='object'){
						var thisFunction = arguments.callee;
						(function(){
							var newInsertDiv = $("<td class='columnValue columnValueObj'></td>");
							if(data==undefined){
								thisFunction(newInsertDiv,column[i].dataType);
							}else{
								thisFunction(newInsertDiv,column[i].dataType,data[i]);
							}
							var temp = $("<tr class='column' data-name='"+i+"'>" +
									"<td class='columnName'>"+column[i].title+"</td><td></td></tr>");
							temp.append(newInsertDiv);
							div.append(temp);
						})();
					}else{
						if(column[i].AUTO_INCREMENT){
						}else{
							var tempTd = $("<td class='columnValue'></td>");
							if(column[i].dataList!=undefined && column[i].dataList.type=='Enum'){
								(function(){
									var select = $('<select name="'+i+'"><select>');
									for(var j in column[i].dataList.data){
										if(data && data[i] && data[i]==j ){
											select.append($('<option value="'+j+'" selected>'+column[i].dataList.data[j]+'</option>'));
										}else{
											select.append($('<option value="'+j+'">'+column[i].dataList.data[j]+'</option>'));
										}
									}
									tempTd.append(select);
								})();
							}else if(htmlObject[column[i].dataType]!=undefined && htmlObject[column[i].dataType].writeHTML!=undefined){
								tempTd.append(htmlObject[column[i].dataType].writeHTML(i,data?data[i]:''));
							}else{
								tempTd.append($("<input class='form-control' name='"+i+"' value='"+((data&&data[i])?data[i]:'')+"'/>"));
							}
							var tr = $("<tr class='column' data-name='"+i+"'></tr>");
							tr.append($("<td class='columnName'>"+column[i].title+"</td><td>:</td>"));
							tr.append(tempTd);
							div.append(tr);
						}
					}
				}
				if(dom.find('>.addArrDataButton').length>0){
					dom.find('>.addArrDataButton').before(div);
				}else{
					dom.append(div);
				}
			},
			initUpdateHtml:function(){
				$.getJSON('',{
					'function':'getOneData',
					'id':$(this).parents('tr').attr('data-id')
				},function(data){
					var updateDom = $('<div class="updateHtml"></div>');
					var tempDom = $('<div data-id="'+data[key]+'" class="updateHtmlContent"></div>');
					for(var i in column){
						if(typeof(column[i].dataType)=='object'){
							if(typeof data[i]=='string'){
								data[i] = JSON.parse(data[i]);
							}
						}
					}
					mysqlAJAXClass.makeHtml(tempDom,column,data);
					var buttonsDiv = $('<div class="updateButtons"></div>');
					buttonsDiv.append($("<button class='btn btn-danger' onclick='mysqlAJAXClass.updateDataSent(\""+data[key]+"\",this)'>保存</button>"));
					buttonsDiv.append($("<button class='btn btn-default' onclick=\"$(this).parents('.updateHtml').remove()\">取消</button>"));
					tempDom.append(buttonsDiv);
					updateDom.append(tempDom);
					$("body").append(updateDom);
				});
			},
			updateDataSent:function(id,buttonDom){
				$(".updateHtmlContent .baiduEditor").each(function(){
					$(this).find('textarea').val($(this).find(".edui-editor-iframeholder iframe")[0].contentDocument.body.innerHTML);
				});
				var dataOfColumn = this.getDataByTable($(buttonDom).parents('.updateHtmlContent:eq(0)').find('>table'));

				for(var i in dataOfColumn){
					if(dataOfColumn[i] instanceof Array==true){
						dataOfColumn[i] = JSON.stringify(dataOfColumn[i]);
					}else if(typeof(dataOfColumn[i])=='object'){
						dataOfColumn[i] = JSON.stringify(dataOfColumn[i]);
					}
				}
				$.post('',{
					function:'update',
					id:id,
					data:dataOfColumn
				},function(data){
					if(data==1){
						$(buttonDom).parents('.updateHtml').remove();
						mysqlAJAXClass.getList();
					}else{
						alert('更新失败');
					}
				});
			},
			addArrListHtml:function(){
				var columnOfThis = column;
				var allTrParents = $(this).parents('.updateHtml tr');
				for(var i=allTrParents.length-1;i>=0;i--){
					columnOfThis = columnOfThis[ $(allTrParents[i]).attr('data-name')  ].dataType;
					if(columnOfThis instanceof Array==true ){
						columnOfThis = columnOfThis[0];
					}
				}
				if(columnOfThis['title']!=undefined && typeof columnOfThis['title']=="string"){
					mysqlAJAXClass.makeHtml($(this).parent(),{
						'':columnOfThis
					},null,true);
				}else{
					mysqlAJAXClass.makeHtml($(this).parent(),columnOfThis,null,true);
				}
			},
			deleteSomeDataSent:function(allKeys,deleteAllCom){
				function deleteAll(){
					$.post('',{
						function:'deleteSomeData',
						id:allKeys
					},function(data){
						alert('成功删除'+data+'条记录')
						mysqlAJAXClass.getList();
						deleteAllCom.hide();
					});
					deleteAllCom.parent('#fastTableInfo').find('table>thead>tr>th:eq(0)>:checkbox').attr('checked',false);
				}
				if(window.confirm('是否确认删除'+allKeys.length+'条数据么?')){
					if(allKeys.length>10){
						if(window.confirm('数量较多请谨慎检查,以免误删')){
							deleteAll();
						}
					}else{
						deleteAll();
					}
				}
			},
			deleteDataSent:function(){
				if(window.confirm('是否确认删除')){
					var id = $(this).parents('tr').attr('data-id');
					$.getJSON('',{
						function:'deleteData',
						id:id
					},function(data){
						if(data==1){
							alert('已经删除');
							mysqlAJAXClass.getList();
						}else{
							alert('删除异常');
						}
					});
				}
			}
		};
		mysqlAJAXClass.getList();
		$('#fastTableInfo #fastTableInfoPage span').live('click',function(){
			page = parseInt($(this).attr('data-page'));
			mysqlAJAXClass.getList();
		});
		$('#fastTableInfo .updateButton').live('click',mysqlAJAXClass.initUpdateHtml);
		$('#fastTableInfo .deleteButton').live('click',mysqlAJAXClass.deleteDataSent);
		$('.updateHtml .addArrDataButton').live('click',mysqlAJAXClass.addArrListHtml);
		$('.updateHtml .deleteArrButton').live('click',function(){
			$(this).parents('table').eq(0).remove();
		});
		$('#fastTableInfo table thead th select').live('change',function(){
			$('#fastTableInfo table thead th input').val('');
			delete searchArr[$(this).parents('th').attr('column')];
			var allInput = $(this).parents('th').find('[match]');
			allInput.hide();
			var isNeedToInput = false;
			for(var i=0;i<allInput.length;i++){
				if($(this).val()==allInput.eq(i).attr('match')){
					isNeedToInput = true;
					allInput.eq(i).show();
				}
			}
			if(isNeedToInput ==false){
				searchArr[$(this).parents('th').attr('column')] = $(this).val();
				mysqlAJAXClass.getList();
			}
		});
		$('#fastTableInfo table thead [column] input').live('change',function(){
			var input = $(this);
			var searchVal = input.attr('match');
			var sameMatchCount = 0;
			var isAllWrite = 0;//是否所有字段都已经填写

			$('#fastTableInfo table thead th input').each(function(){
				if($(this).attr('match')==input.attr('match')){
					sameMatchCount++;
					if($(this).val()!=''){
						isAllWrite ++;
					}
					searchVal = searchVal.replace('?',$(this).val());
				}
			});
			//要么都写了,要么都没写
			console.log(isAllWrite);
			console.log(sameMatchCount);
			if(isAllWrite==0){
				delete searchArr[$(this).parents('th').attr('column')];
				page=0;
				mysqlAJAXClass.getList();
			}else if(isAllWrite==sameMatchCount){
				searchArr[$(this).parents('th').attr('column')] = searchVal;
				page=0;
				mysqlAJAXClass.getList();
			}else{
				delete searchArr[$(this).parents('th').attr('column')];
			}
		});
		$('#fastTableInfo .deleteAll :button').click(function(){
			var allKeys = [];
			$(this).parents('#fastTableInfo').find('>table>tbody>tr>.select>:checked').each(function(){
				allKeys.push($(this).attr('data-id'));
			});
			mysqlAJAXClass.deleteSomeDataSent(allKeys, $(this).parents('.deleteAll') );
		});
		$('#fastTableInfo table').on('click','>tbody>tr>.select :checkbox',function(){
			var allBrother = $(this).parents('table').find('>tbody>tr>.select>:checked');
			if(allBrother.length>=2){
				$(this).parents('table').parent().find('>.deleteAll').show();
			}else{
				$(this).parents('table').parent().find('>.deleteAll').hide();
			}
		});
		$('#fastTableInfo table>thead>tr>th:eq(0)>:checkbox').click(function(){
			if(this.checked==true){
				$(this).parents('table').parent().find('>.deleteAll').show();
			}else{
				$(this).parents('table').parent().find('>.deleteAll').hide();
			}
			$('#fastTableInfo table>tbody>tr>.select>:checkbox').attr('checked',this.checked);
		});
	</script>
</div>