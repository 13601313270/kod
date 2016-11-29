{
	'writeHTML':function(columnName,data){
		return $("<input class='form-control' type='number' name='"+columnName+"' value='"+(data?data:'')+"'/>");
	}
}