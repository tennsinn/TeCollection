<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 默认前台输出模板
 *
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 */

Typecho_Widget::widget('Collection_Config@panel')->to($config);
$dictOrderby = array('id' => 'ID', 'rate' => '评价', 'time_touch' => '最后修改', 'time_start' => '开始时间', 'time_finish' => '结束时间');
?>

<script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php Helper::options()->pluginUrl('Collection/template/stylesheet-common.css'); ?>">
<link rel="stylesheet" type="text/css" href="<?php Helper::options()->pluginUrl('Collection/template/stylesheet-page.css'); ?>">
<div id="Collection-Box">
	<div id="Collection-opt" class="Collection-opt">
		<form id="Collection-form" method="post" action="<?php Helper::options()->index('/action/collection'); ?>">
			<div id="Collection-opt-category">
				<label class="Collection-opt-label">大类：</label>
				<?php foreach($config->dictCategory as $key => $value): ?>
					<span class="Collection-opt-checkbox"><input type="checkbox" id="Collection-opt-category-<?php echo $key; ?>" name="category[]" value="<?php echo $key; ?>" checked><label for="Collection-opt-category-<?php echo $key; ?>"><?php echo $value; ?></label></span>
				<?php endforeach; ?>
				<span class="Collection-opt-checkbox Collection-opt-checkbox-all"><input type="checkbox" id="Collection-opt-category-all" checked><label for="Collection-opt-category-all">全选</label></span>
			</div>
			<div id="Collection-opt-class">
				<label class="Collection-opt-label">种类：</label>
				<?php foreach($config->dictClass as $key => $value): ?>
					<span class="Collection-opt-checkbox"><input type="checkbox" id="Collection-opt-class-<?php echo $key; ?>" name="class[]" value="<?php echo $key; ?>" checked><label for="Collection-opt-class-<?php echo $key; ?>"><?php echo $value; ?></label></span>
				<?php endforeach; ?>
				<span class="Collection-opt-checkbox Collection-opt-checkbox-all"><input type="checkbox" id="Collection-opt-class-all" checked><label for="Collection-opt-class-all">全选</label></span>
			</div>
			<div id="Collection-opt-type">
				<label class="Collection-opt-label">类型：</label>
				<?php foreach($config->dictType as $class => $items): ?>
					<span class="Collection-opt-checkbox"><input type="checkbox" class="Collection-opt-type-class-0" id="Collection-opt-type-null" name="type[]" value="null" checked><label for="Collection-opt-type-null">未知</label></span>
					<?php foreach($items as $key => $value): ?>
						<span class="Collection-opt-checkbox"><input type="checkbox" class="Collection-opt-type-class-<?php echo $class; ?>" id="Collection-opt-type-<?php echo $key; ?>" name="type[]" value="<?php echo $key; ?>" checked><label for="Collection-opt-type-<?php echo $key; ?>"><?php echo $value; ?></label></span>
					<?php endforeach; ?>
				<?php endforeach; ?>
				<span class="Collection-opt-checkbox Collection-opt-checkbox-all"><input type="checkbox" id="Collection-opt-type-all" checked><label for="Collection-opt-type-all">全选</label></span>
			</div>
			<div id="Collection-opt-status">
				<label class="Collection-opt-label">状态：</label>
				<?php foreach($config->dictStatus as $key => $value):?>
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
<link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="<?php Helper::options()->pluginUrl('Collection/3rdParty/jPages.css'); ?>">
<script src="<?php Helper::options()->pluginUrl('Collection/3rdParty/jPages.min.js'); ?>"></script>
<script src="https://cdn.bootcdn.net/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
var dictTypeTrans = <?php echo $config->jsType; ?>;
var dictStatusTrans = <?php echo $config->jsStatusAll; ?>;
var dictSource = <?php echo $config->jsSource; ?>;

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
						+ '<div class="Collection-subject-name">';
					if(subject.category != 'series')
					{
						+ '<i class="Collection-subject-class-ico Collection-subject-class-'+subject.class+'"></i>'
						+ '<small>（'+dictTypeTrans[subject.class][subject.type]+'）</small>';
					}
					if(subject.source != 'Collection')
						tempHTML += '<a href="'+dictSource[subject.source]['url']+subject.source_id+'" target="_blank">'+subject.name+'</a>';
					else
						tempHTML += subject.name;
					if(subject.name_cn)
						tempHTML += '<small>（'+subject.name_cn+'）</small>';
					tempHTML += '</div>'
						+ '<div class="Collection-subject-meta">'
						+ '<span>记录起止：'+(subject.time_start ? moment.unix(subject.time_start).format('YYYY-MM-DD') : '??')+' / '
						+ (subject.time_finish ? moment.unix(subject.time_finish).format('YYYY-MM-DD') : '??')+'</span>'
						+ '</div>'
						+ '<div class="Collection-subject-box-progress">'
						+ '<div>状态：</div>'
						+ '<div>'+dictStatusTrans[subject.status][subject.class]+'</div>';
					if(subject.ep_count != null && subject.ep_status != null)
						tempHTML += '<div class="Collection-subject-progress"><div class="Collection-subject-progress-inner" style="color:white; width:'+(subject.ep_count!=0 ? subject.ep_status/subject.ep_count*100 : 50)+'%"><small>'+subject.ep_status+' / '+(subject.ep_count!=0 ? subject.ep_count : '??')+'</small></div></div>';
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

	$('#Collection-opt-category-all').click(function(){
		$('input[name="category[]"]').prop("checked", this.checked);
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
