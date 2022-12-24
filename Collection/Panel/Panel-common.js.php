<script type="text/javascript">
var dictColumn = <?=$config->jsColumn?>;
var dictStatusAll = <?=$config->jsStatusAll?>;
var dictCategory = <?=$config->jsCategory?>;
var dictClass = <?=$config->jsClass?>;
var dictType = <?=$config->jsType?>;
var dictSource = <?=$config->jsSource?>;
var dictGrade = <?=$config->jsGrade?>;
var dictRate = <?=$config->jsRate?>;

var transArrayToOption = function(dict, subname=null) {
	var tempHTML = '';
	var tempArray = {};
	$.each(dict, function(key, value){
		if(subname)
			tempHTML += '<option value="'+key+'">'+value[subname]+'</option>';
		else if('object' == typeof(value)) {
			$.each(value, function(subkey, subvalue) {
				tempHTML += '<option value="'+subkey+'">'+subvalue+'</option>';
			});
			tempArray[key] = tempHTML;
			tempHTML = '';
		}
		else
			tempHTML += '<option value="'+key+'">'+value+'</option>';
	});
	return tempHTML ? tempHTML : tempArray;
};
var select_options = {};
	select_options['category'] = transArrayToOption(dictCategory);
	select_options['class'] = transArrayToOption(dictClass);
	select_options['type'] = transArrayToOption(dictType);
	select_options['source'] = transArrayToOption(dictSource, 'name');
	select_options['grade'] = transArrayToOption(dictGrade);
	select_options['rate'] = transArrayToOption(dictRate);
</script>
