<script type="text/javascript">
	$(document).ready(function(){
		var dictSearch = {'Bangumi':{'0':'全部','1':'书籍','2':'动画','3':'音乐','4':'游戏','5':'演出','6':'影视'}};
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