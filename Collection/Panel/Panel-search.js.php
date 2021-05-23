<script type="text/javascript">
	$(document).ready(function(){
		var dictSearch = {};
			$.each(dictSource, function(key, val) {
				if(val['search'])
					dictSearch[key] = val['search'];
			});
		var select_search = transArrayToOption(dictSearch);
		var changeSource = function(){
			$('select[name=class]').html(select_search[$('select[name=source]').val()]);
		};
		changeSource();
		$('select[name=class]').val(<?=$class?>);
		$('select[name=source]').change(function(){
			changeSource();
		});
	});
</script>