	{
		'list':function(data){
			if(data==true){
				return $('<td><input type="radio" disabled="true" checked/>是</td>');
			}else{
				return $('<td><input type="radio" disabled="true"/>否</td>');
			}
		},
		'writeHTML':function(columnName,data){
			if(data===undefined){
				return $("<select class='form-control' name='"+columnName+"'><option value='1'>是</option><option value='0'>否</option></select>");
			}else{
				return $("<select class='form-control' name='"+columnName+"'><option value='1' "+(data==true?'selected ="selected"':'')+">是</option><option value='0' "+(data==false?'selected ="selected"':'')+">否</option></select>");
			}
		}
	}