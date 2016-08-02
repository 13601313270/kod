	{
		'writeHTML':function(columnName,data){
			var dateTimeRand = 'dateTimeRand'+parseInt(Math.random()*100000);
			var temp = $("<input id='"+dateTimeRand+"' class='form-control' name='"+columnName+"' value='"+(data?data:'')+"'/>");
			setTimeout(function(){
				$('#'+dateTimeRand).datepicker({ dateFormat: 'yy-mm-dd' });
			},500);
			return temp;
		}
	}