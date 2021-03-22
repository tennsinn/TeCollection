<?php
$dictClass = array(1 => '书籍', 2 => '动画', 3 => '音乐', 4 => '游戏', 5 => '广播', 6 => '影视');
$dictType = array(
	1 => array('Novel' => '小说', 'Comic' => '漫画', 'Doujinshi' => '同人志', 'Textbook' => '课本'),
	2 => array('TV' => 'TV', 'OVA' => 'OVA', 'OAD' => 'OAD', 'Movie' => '剧场'),
	3 => array('Album' => '专辑', 'Single' => '单曲', 'Maxi' => 'Maxi', 'EP' => '细碟', 'Selections' => '选集'),
	4 => array('iOS' => 'iOS', 'Android' => 'Andriod', 'PSP' => 'PSP', 'PSV' => 'PSV', 'PS' => 'PS', 'NDS' => 'NDS', '3DS' => '3DS', 'XBox' => 'XBox', 'Windows' => 'Windows', 'Online' => '网游', 'Table' => '桌游'),
	5 => array('RadioDrama' => '广播剧', 'Drama' => '歌剧'),
	6 => array('Film' => '电影', 'Teleplay' => '电视剧', 'Documentary' => '纪录片', 'TalkShow' => '脱口秀', 'VarietyShow' => '综艺')
);
$dictStatus = array('do' => '进行', 'wish' => '计划', 'collect' => '完成', 'on_hold' => '搁置', 'dropped' => '抛弃');
$dictOrderby = array('id' => 'ID', 'rate' => '评价', 'time_touch' => '最后修改', 'time_start' => '开始时间', 'time_finish' => '结束时间');
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php Helper::options()->pluginUrl('Collection/template/stylesheet-common.css'); ?>">
<link rel="stylesheet" type="text/css" href="<?php Helper::options()->pluginUrl('Collection/template/stylesheet-page.css'); ?>">
<div id="Collection-Box">
	<div id="Collection-opt" class="Collection-opt">
		<form id="Collection-form" method="post" action="<?php Helper::options()->index('/action/collection'); ?>">
			<div id="Collection-opt-class">
				<label class="Collection-opt-label">种类：</label>
				<?php foreach($dictClass as $key => $value): ?>
					<span class="Collection-opt-checkbox"><input type="checkbox" id="Collection-opt-class-<?php echo $key; ?>" name="class[]" value="<?php echo $key; ?>" checked><label for="Collection-opt-class-<?php echo $key; ?>"><?php echo $value; ?></label></span>
				<?php endforeach; ?>
				<span class="Collection-opt-checkbox Collection-opt-checkbox-all"><input type="checkbox" id="Collection-opt-class-all" checked><label for="Collection-opt-class-all">全选</label></span>
			</div>
			<div id="Collection-opt-type">
				<label class="Collection-opt-label">类型：</label>
				<?php foreach($dictType as $class => $items): ?>
					<?php foreach($items as $key => $value): ?>
						<span class="Collection-opt-checkbox"><input type="checkbox" class="Collection-opt-type-class-<?php echo $class; ?>" id="Collection-opt-type-<?php echo $key; ?>" name="type[]" value="<?php echo $key; ?>" checked><label for="Collection-opt-type-<?php echo $key; ?>"><?php echo $value; ?></label></span>
					<?php endforeach; ?>
				<?php endforeach; ?>
				<span class="Collection-opt-checkbox Collection-opt-checkbox-all"><input type="checkbox" id="Collection-opt-type-all" checked><label for="Collection-opt-type-all">全选</label></span>
			</div>
			<div id="Collection-opt-status">
				<label class="Collection-opt-label">状态：</label>
				<?php foreach($dictStatus as $key => $value):?>
					<span class="Collection-opt-checkbox"><input type="checkbox" id="Collection-opt-status-<?php echo $key; ?>" name="status[]" value="<?php echo $key; ?>"<?php if($key=='do'): ?> checked<?php endif; ?>><label for="Collection-opt-status-<?php echo $key; ?>"><?php echo $value; ?></label></span>
				<?php endforeach; ?>
				<span class="Collection-opt-checkbox Collection-opt-checkbox-all"><input type="checkbox" id="Collection-opt-status-all"><label for="Collection-opt-status-all">全选</label></span>
			</div>
			<div id="Collection-opt-rate">
				<label class="Collection-opt-label">评价：</label>
				<span class="Collection-opt-text" id="Collection-opt-rate-display">0 - 10</span>
				<span class="Collection-opt-range"><input type="hidden" id="Collection-opt-rate-slider" name="rate" value="0,10"></span>
			</div>
			<div id="Collection-opt-order">
				<label class="Collection-opt-label">排序：</label>
				<?php foreach($dictOrderby as $key => $value): ?>
					<span class="Collection-opt-radio"><input type="radio" id="Collection-opt-orderby-<?php echo $key; ?>" name="orderby" value="<?php echo $key; ?>"<?php if($key=='time_touch'): ?>  checked<?php endif; ?>><label for="Collection-opt-orderby-<?php echo $key; ?>"><?php echo $value; ?></label></span>
				<?php endforeach; ?>
				<span class="Collection-opt-gap">|</span>
				<span class="Collection-opt-radio"><input type="radio" id="Collection-opt-order-desc" name="order" value="DESC" checked><label for="Collection-opt-order-desc">降序</label></span><span class="Collection-opt-radio"><input type="radio" id="Collection-opt-order-asc" name="order" value="ASC"><label for="Collection-opt-order-asc">升序</label></span>
				<span class="Collection-opt-submit"><input type="hidden" value="getCollection" name="do"><button type="submit">筛选</button></span>
			</div>
		</form>
	</div>
	<div id="Collection-nav-1" class="holder"></div>
	<ul id="Collection-list" class="Collection-list"></ul>
	<div id="Collection-nav-2" class="holder"></div>
</div>
<link rel="stylesheet" href="<?php Helper::options()->pluginUrl('Collection/3rdParty/jquery.range.css'); ?>">
<script src="<?php Helper::options()->pluginUrl('Collection/3rdParty/jquery.range-min.js'); ?>"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="<?php Helper::options()->pluginUrl('Collection/3rdParty/jPages.css'); ?>">
<script src="<?php Helper::options()->pluginUrl('Collection/3rdParty/jPages.min.js'); ?>"></script>
<script>
Date.prototype.format = function(format) {  
	var o = {  
		"M+" : this.getMonth() + 1,
		"d+" : this.getDate(),
		"h+" : this.getHours(),
		"m+" : this.getMinutes(), 
		"s+" : this.getSeconds(),
		"q+" : Math.floor((this.getMonth() + 3) / 3),
		"S" : this.getMilliseconds() 
	}		   
	if (/(y+)/.test(format)) {  
		format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));  
	}
	for (var k in o) {  
		if (new RegExp("(" + k + ")").test(format)) {  
format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
		}  
	}  
	return format;
}

var dictTypeTrans = {
	1:{'Mix':'混合', 'Series':'系列', 'Novel':'小说', 'Comic':'漫画', 'Doujinshi':'同人志', 'Textbook':'课本'},
	2:{'Mix':'混合', 'Series':'系列', 'TV':'TV', 'OVA':'OVA', 'OAD':'OAD', 'Movie':'剧场'},
	3:{'Mix':'混合', 'Series':'系列', 'Album':'专辑', 'Single':'单曲', 'Selections':'选集'},
	4:{'Mix':'混合', 'Series':'系列', 'iOS':'iOS', 'Android':'Andriod', 'PSP':'PSP', 'PSV':'PSV', 'PS':'PS', 'NDS':'NDS', '3DS':'3DS', 'XBox':'XBox', 'Windows':'Windows', 'Online':'网游', 'Table':'桌游'},
	5:{'Mix':'混合', 'Series':'系列', 'RadioDrama':'广播剧', 'Drama':'歌剧'},
	6:{'Mix':'混合', 'Series':'系列', 'Film':'电影', 'Teleplay':'电视剧', 'Documentary':'纪录片', 'TalkShow':'脱口秀', 'VarietyShow':'综艺'}
};

var dictStatusTrans = {
	'do':{1:'在读', 2:'在看', 3:'在听', 4:'在玩', 5:'在听', 6:'在看'},
	'collect':{1:'读过', 2:'看过', 3:'听过', 4:'玩过', 5:'过听', 6:'看过'},
	'wish':{1:'想读', 2:'想看', 3:'想听', 4:'想玩', 5:'想听', 6:'想看'},
	'on_hold':{1:'搁置', 2:'搁置', 3:'搁置', 4:'搁置', 5:'搁置', 6:'搁置'},
	'dropped':{1:'抛弃', 2:'抛弃', 3:'抛弃', 4:'抛弃', 5:'抛弃', 6:'抛弃'}
};

$(document).ready(function(){

	$('#Collection-opt-rate-slider').jRange({
		from: 0,
		to: 10,
		step: 1,
		scale: [0,2,4,6,8,10],
		format: '%s',
		width: 300,
		showLabels: false,
		showScale: false,
		theme: 'theme-blue',
		isRange : true,
		snap: true
	});

	$('#Collection-form').submit(function () {
		var items = $(this).serializeArray();
		$.post($(this).attr('action'), items, function (data) {
			var tempHTML = '';
			if(data.result)
			{
				$.each(data.list, function(i, subject){
					tempHTML += '<li class="Collection-subject-content"><div class="Collection-subject-cover"><img src="';
					if(subject.image)
						tempHTML += subject.image;
					else
						tempHTML += "<?php Helper::options()->pluginUrl('Collection/template/default_cover.jpg'); ?>";
					tempHTML += '"></div>';
					tempHTML += '<div class="Collection-subject-info">'
						+ '<div class="Collection-subject-name">'
						+ '<i class="Collection-subject-class-ico Collection-subject-class-'+subject.class+'"></i>'
						+ '<small>（'+dictTypeTrans[subject.class][subject.type]+'）</small>';
					if(subject.source != 'Collection')
					{
						tempHTML += '<a href="';
						switch(subject.source)
						{
							case 'Bangumi':
								tempHTML += 'http://bangumi.tv/subject/';
								break;
							case 'Douban':
								tempHTML += 'http://';
								switch(subject.class)
								{
									case '1':
										tempHTML += 'book';
										break;
									case '3':
										tempHTML += 'music';
										break;
									case '6':
										tempHTML += 'movie';
										break;
								}
								tempHTML += '.douban.com/subject/';
								break;
							case 'Wandoujia':
								tempHTML += 'http://www.wandoujia.com/apps/';
								break;
						}
						tempHTML += subject.subject_id+'" target="_blank">'+subject.name+'</a>';
					}
					else
						tempHTML += subject.name;
					if(subject.name_cn)
						tempHTML += '<small>（'+subject.name_cn+'）</small>';
					tempHTML += '</div>'
						+ '<div class="Collection-subject-meta">'
						+ '<span>记录起止：'+(subject.time_start ? new Date(parseInt(subject.time_start)*1000).format('yyyy-MM-dd') : '??')+' / '
						+ (subject.time_finish ? new Date(parseInt(subject.time_finish)*1000).format("yyyy-MM-dd") : '??')+'</span>'
						+ '<span>最后修改：'+(new Date(parseInt(subject.time_touch)*1000).format("yyyy-MM-dd"))+'</span>'
						+ '</div>'
						+ '<div class="Collection-subject-box-progress">'
						+ '<div>状态：</div>'
						+ '<div>'+dictStatusTrans[subject.status][subject.class]+'</div>';
					if(subject.ep_count != null && subject.ep_status != null)
					{
						tempHTML += '<div>主进度：</div>'
							+ '<div class="Collection-subject-progress"><div class="Collection-subject-progress-inner" style="color:white; width:'+(subject.ep_count!=0 ? subject.ep_status/subject.ep_count*100 : 50)+'%"><small>'+subject.ep_status+' / '+(subject.ep_count!=0 ? subject.ep_count : '??')+'</small></div></div>';
						if(subject.sp_count != null && subject.sp_status != null)
							tempHTML += '<div>副进度：</div>'
							+ '<div class="Collection-subject-progress"><div class="Collection-subject-progress-inner" style="color:white; width:'+(subject.sp_count!=0 ? subject.sp_status/subject.sp_count*100 : 50)+'%"><small>'+subject.sp_status+' / '+(subject.sp_count!=0 ? subject.sp_count : '??')+'</small></div></div>';
					}
					tempHTML += '</div>'
						+ '<div><i>备注：</i><small>'+(subject.note ? subject.note : '无')+'</small></div>'
						+ '<div class="Collection-subject-review">'
							+ '<div><i>评价：</i>'+'<span class="Collection-subject-rate-star Collection-subject-rate-star-rating"></span>'.repeat(subject.rate)+'<span class="Collection-subject-rate-star Collection-subject-rate-star-blank"></span>'.repeat(10-subject.rate)+'</div>'
							+ '<div><i>标签：</i><span>'+(subject.tags ? subject.tags : '无')+'</span></div>'
							+ '<div><i>吐槽：</i><span>'+(subject.comment ? subject.comment : '无')+'</span></div>'
						+ '</div>'
						+ '</div>'
						+ '</li>';
				});
			}
			else
			{
				tempHTML = data.message;
			}
			$('#Collection-list').html(tempHTML);
			$(".holder").jPages({
				containerID : "Collection-list",
				perPage : 20,
				minHeight : false,
				animation : "<?php echo Helper::options()->plugin('Collection')->animation; ?>"
			});
		}, 'json');
		return false;
	});

	$('#Collection-opt-class-all').click(function(){
		$('input[name="class[]"]').prop("checked", this.checked);
		$('.Collection-opt-type-class-0').prop("disabled", !this.checked);
		$('.Collection-opt-type-class-1').prop("disabled", !this.checked);
		$('.Collection-opt-type-class-2').prop("disabled", !this.checked);
		$('.Collection-opt-type-class-3').prop("disabled", !this.checked);
		$('.Collection-opt-type-class-4').prop("disabled", !this.checked);
		$('.Collection-opt-type-class-5').prop("disabled", !this.checked);
		$('.Collection-opt-type-class-6').prop("disabled", !this.checked);
	});

	$('input[name="class[]"]').click(function(){
		if(!$(this).checked)
			$('#Collection-opt-class-all').prop("checked", false);
		if($('input[name="class[]"]:checked').length == 6)
			$('#Collection-opt-class-all').prop("checked", true);
		if($('input[name="class[]"]:checked').length > 0)
			$('.Collection-opt-type-class-0').prop("disabled", false);
		$('.Collection-opt-type-class-'+$(this).val()).prop("disabled", !this.checked);
	});

	$('#Collection-opt-type-all').click(function(){
		$('input[name="type[]"]').prop("checked", this.checked);
	});

	$('input[name="type[]"]').click(function(){
		if(!$(this).checked)
			$('#Collection-opt-type-all').prop("checked", false);
		if($('input[name="type[]"]:checked').length == 32)
			$('#Collection-opt-type-all').prop("checked", true);
	});

	$('#Collection-opt-status-all').click(function(){
		$('input[name="status[]"]').prop("checked", this.checked);
	});

	$('input[name="status[]"]').click(function(){
		if(!$(this).checked)
			$('#Collection-opt-status-all').prop("checked", false);
		if($('input[name="status[]"]:checked').length == 5)
			$('#Collection-opt-status-all').prop("checked", true);
	});

	$('#Collection-opt-rate-slider').change(function(){
		$('#Collection-opt-rate-display').html($(this).val().replace(',', ' - '));
	});

	$('#Collection-form').submit();
});
</script>