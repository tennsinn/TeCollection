<script type="text/javascript">
$(document).ready(function () {
	var arrayStatus = ['读过', '看过', '听过', '玩过', '听过', '看过'];
	var clickPlusEp = function(){
		var tr = $(this).parents('tr');
		var t = $(this);
		var id = tr.attr('id');
		var subject = tr.data('subject');
		$.post(t.attr('rel'), {"id": subject.id}, function (data) {
			if(data.result)
			{
				subject.ep_status = data.ep_status;
				ep_current = Number(subject.ep_status) + (null === subject.ep_start ? 1 : Number(subject.ep_start)) - 1;
				ep_end = (null === subject.ep_start ? 1 : Number(subject.ep_start)) + Number(subject.ep_count) - 1;
				if(data.status == 'collect')
					subject.status = 'collect';
				else
					t.html('ep.'+(ep_current+1)+'已看过');
				tr.data('subject', subject);
				t.parent().parent().prev().html('<div class="Collection-subject-progress-inner" style="color:white; width:'+(subject.ep_count > 0 ? subject.ep_status/subject.ep_count*100 : 50)+'%"><small>'+(ep_current < 0 ? '??' : ep_current)+' / '+(ep_end > 0 ? ep_end : '??')+'</small></div>');
				if(subject.ep_count != 0 && subject.ep_status == subject.ep_count)
					t.parent().parent().remove();
			}
			else
				alert(data.message);
		});
	}
	$('.Collection-subject-progress-plus').click(clickPlusEp);

	$('.Collection-subject-edit').click(function () {
		var tr = $(this).parents('tr');
		var t = $(this);
		var id = tr.attr('id');
		var subject = tr.data('subject');
		tr.hide();
		var string = '<tr class="Collection-subject-edit">'
			+ '<td> </td>'
			+ '<td><form method="post" action="'+t.attr('rel')+'" class="Collection-subject-edit-content">'
				+ '<p><label for="'+id+'-category"><?=_t('大类')?></label><select id="'+id+'-category" name="category" class="w-100">'+select_options['category']+'</select></p>'
				+ '<p><label for="'+id+'-image"><?=_t('封面')?></label>'
				+ '<textarea name="image" id="'+id+'-image" rows="3" class="w-100 mono"></textarea></p>'
				+ '<p><label for="'+id+'-class"><?=_t('种类')?></label><select id="'+id+'-class" name="class" class="w-100">'+select_options['class']+'</select></p>'
				+ '<p><label for="'+id+'-type"><?=_t('类型')?></label><select id="'+id+'-type" name="type" class="w-100">'+select_options['type'][subject.class]+'</select></p>'
			+ '<p><label for="'+id+'-author"><?=_t('作者')?></label><input type="text" id="'+id+'-author" name="author" class="text-s w-100"></p>'
			+ '<p><label for="'+id+'-publisher"><?=_t('出版商')?></label><input type="text" id="'+id+'-publisher" name="publisher" class="text-s w-100"></p>'
			+ '<p><label for="'+id+'-published"><?=_t('出版时间')?></label><input type="text" id="'+id+'-published" name="published" class="text-s w-100"></p>'
			+ '<p><label for="'+id+'-source"><?=_t('信息来源')?></label><select id="'+id+'-source" name="source" class="w-100">'+select_options['source']+'</select></p>'
			+ '<p><label for="'+id+'-source_id"><?=_t('来源ID')?></label><input type="text" id="'+id+'-source_id" name="source_id" class="text-s w-100"></p>'
			+ '</form></td>'
			+ '<td><form method="post" action="'+t.attr('rel')+'" class="Collection-subject-edit-info">'
			+ '<p><label for="'+id+'-name"><?=_t('原名')?></label><input type="text" id="'+id+'-name" name="name" class="text-s w-100"></p>'
			+ '<p><label for="'+id+'-name_cn"><?=_t('译名')?></label><input type="text" id="'+id+'-name_cn" name="name_cn" class="text-s w-100"></p>'
			+ '<p><label for="'+id+'-parent"><?=_t('关联记录')?></label><input type="text" id="'+id+'-parent" name="parent" class="text-s w-100"></p>'
			+ '<p><label for="'+id+'-parent_order"><?=_t('关联顺序')?></label><input type="text" id="'+id+'-parent_order" name="parent_order" class="text-s w-100"></p>'
			+ '<p><label for="'+id+'-parent_label"><?=_t('关联标签')?></label><input type="text" id="'+id+'-parent_label" name="parent_label" class="text-s w-100"></p>'
			+ '<p><label for="'+id+'-ep_status"><?=_t('主进度')?></label><input type="text" id="'+id+'-ep_status" name="ep_status" class="text-s w-100"></p>'
			+ '<p><label for="'+id+'-ep_count"><?=_t('主进度总数')?></label><input type="text" name="ep_count" id="'+id+'-ep_count" class="text-s w-100"></p>'
			+ '<p><label for="'+id+'-ep_start"><?=_t('主进度起始')?></label><input type="text" name="ep_start" id="'+id+'-ep_start" class="text-s w-100"></p>'
			+ '</form></td>'
			+ '<td><form method="post" action="'+t.attr('rel')+'" class="Collection-subject-edit-content">'
			+ '<p><label for="'+id+'-media_link"><?=_t('媒体链接')?></label><input type="text" name="media_link" id="'+id+'-media_link" class="text-s w-100 w-100"></p>'
			+ '<p><label for="'+id+'-grade"><?=_t('显示分级')?></label><select id="'+id+'-grade" name="grade" class="w-100">'+select_options['grade']+'</select></p>'
			+ '<p><label for="'+id+'-note"><?=_t('备注')?></label>'
			+ '<textarea id="'+id+'-note" name="note" rows="2" class="w-100 mono"></textarea></p>'
			+ '<p><label for="'+id+'-rate"><?=_t('评价')?></label><select id="'+id+'-rate" name="rate" class="w-100">'+select_options['rate']+'</select></p>'
			+ '<p><label for="'+id+'-tags"><?=_t('标签')?></label><input type="text" name="tags" id="'+id+'-tags" class="text-s w-100 w-100"></p>'
			+ '<p><label for="'+id+'-comment"><?=_t('吐槽')?></label><textarea id="'+id+'-comment" name="comment" rows="6" class="w-100 mono"></textarea></p>'
			+ '<p><button type="submit" class="btn btn-s primary"><?=_t('提交')?></button> <button type="button" class="btn btn-s cancel"><?=_t('取消')?></button></p>'
			+ '</form></td>'
			+ '</tr>';
		var edit = $(string).data('id', id).data('subject', subject).insertAfter(tr);
		
		$('select[name=category]', edit).val(subject.category);
		$('textarea[name=image]', edit).val(subject.image);
		$('select[name=class]', edit).val(subject.class);
		$('select[name=type]', edit).val(subject.type);
		$('select[name=source]', edit).val(subject.source);
		$('input[name=author]', edit).val(subject.author);
		$('input[name=publisher]', edit).val(subject.publisher);
		$('input[name=published]', edit).val(subject.published);
		$('input[name=source_id]', edit).val(subject.source_id);
		$('input[name=media_link]', edit).val(subject.media_link);
		$('input[name=name]', edit).val(subject.name);
		$('input[name=name_cn]', edit).val(subject.name_cn);
		$('input[name=parent]', edit).val(subject.parent);
		$('input[name=parent_order]', edit).val(subject.parent_order);
		$('input[name=parent_label]', edit).val(subject.parent_label);
		$('input[name=ep_status]', edit).val(subject.ep_status);
		$('input[name=ep_count]', edit).val(subject.ep_count);
		$('input[name=ep_start]', edit).val(subject.ep_start);
		$('select[name=grade]', edit).val(subject.grade);
		$('textarea[name=note]', edit).val(subject.note);
		$('select[name=rate]', edit).val(subject.rate);
		$('input[name=tags]', edit).val(subject.tags);
		$('textarea[name=comment]', edit).val(subject.comment).focus();

		var reactCategory = function(){
			var flag = 'series' == $('select[name=category]', edit).val();
			$('select[name=class]', edit).attr('disabled',flag);
			$('select[name=type]', edit).attr('disabled',flag);
			$('input[name^=parent]', edit).attr('disabled',flag);
			$('input[name$=status]', edit).attr('disabled',flag);
			$('input[name$=count]', edit).attr('disabled',flag);
		}
		reactCategory();

		$('select[name=category]', edit).change(reactCategory);

		$('select[name=class]', edit).change(function(){
			var valClass = $('select[name=class]', edit).val();
			if(valClass && valClass > 0 && valClass <= 6)
				$('select[name=type]', edit).html(select_options['type'][valClass]);
		});

		$('input[name=published]').mask('9999-99-99').datepicker({
			prevText        :   '<?php _e('上一月'); ?>',
			nextText        :   '<?php _e('下一月'); ?>',
			monthNames      :   ['<?php _e('一月'); ?>', '<?php _e('二月'); ?>', '<?php _e('三月'); ?>', '<?php _e('四月'); ?>', '<?php _e('五月'); ?>', '<?php _e('六月'); ?>', '<?php _e('七月'); ?>', '<?php _e('八月'); ?>', '<?php _e('九月'); ?>', '<?php _e('十月'); ?>', '<?php _e('十一月'); ?>', '<?php _e('十二月'); ?>'],
			dayNames        :   ['<?php _e('星期日'); ?>', '<?php _e('星期一'); ?>', '<?php _e('星期二'); ?>', '<?php _e('星期三'); ?>', '<?php _e('星期四'); ?>', '<?php _e('星期五'); ?>', '<?php _e('星期六'); ?>'],
			dayNamesShort   :   ['<?php _e('周日'); ?>', '<?php _e('周一'); ?>', '<?php _e('周二'); ?>', '<?php _e('周三'); ?>', '<?php _e('周四'); ?>', '<?php _e('周五'); ?>', '<?php _e('周六'); ?>'],
			dayNamesMin     :   ['<?php _e('日'); ?>', '<?php _e('一'); ?>', '<?php _e('二'); ?>', '<?php _e('三'); ?>', '<?php _e('四'); ?>', '<?php _e('五'); ?>', '<?php _e('六'); ?>'],
			dateFormat      :   'yy-mm-dd',
			timezone        :   <?php $options->timezone(); ?> / 60,
		});

		$('.cancel', edit).click(function () {
			var tr = $(this).parents('tr');
			$('#' + tr.data('id')).show();
			tr.remove();
		});

		$('form', edit).submit(function () {
			var t = $(this), tr = t.parents('tr'),
				oldTr = $('#' + tr.data('id')),
				subject = oldTr.data('subject');

			$('form', tr).each(function () {
				var items  = $(this).serializeArray();

				for (var i = 0; i < items.length; i ++) {
					var item = items[i];
					subject[item.name] = item.value;
				}
			});
			if('series' == subject['category'])
			{
				subject['class'] = null;
				subject['type'] = null;
				subject['author'] = null;
				subject['publisher'] = null;
				subject['published'] = null;
				subject['type'] = null;
				subject['parent'] = 0;
				subject['parent_order'] = 0;
				subject['parent_label'] = null;
				subject['ep_count'] = null;
				subject['ep_start'] = null;
				subject['ep_status'] = null;
			}

			oldTr.data('subject', subject);

			$.post(t.attr('action'), subject, function (data) {
				var stringProgress = '';
				if(data.result)
				{
					if('series' == subject.category || null == (subject.class))
						$('.Collection-subject-status', oldTr).html(dictStatusAll[subject.status][0]+' / '+dictGrade[subject.grade]);
					else
						$('.Collection-subject-status', oldTr).html(dictStatusAll[subject.status][subject.class]+' / '+dictGrade[subject.grade]);
					$('.Collection-subject-image', oldTr).html('<img src="'+(subject.image ? subject.image : '<?php $options->pluginUrl('Collection/Cover/'); ?>'+subject.id+'.jpg')+'" width="100%">');
					var tempHTML = '';
					tempHTML += dictCategory[subject.category];
					if('series' != subject.category && subject.class)
					{
						tempHTML += ' / ';
						if(subject.class > 0 && subject.class <= 6)
						{
							tempHTML += dictClass[subject.class]+' / ';
							if(subject.type && $.inArray(subject.type,dictClass[subject.class]))
								tempHTML += dictType[subject.class][subject.type];
							else
								tempHTML += '未知';
						}
						else
							tempHTML = '未知 / 未知';
					}
					$('.Collection-subject-type', oldTr).html(tempHTML);

					if(subject.media_link)
						tempHTML = '<a href="'+subject.media_link+'" target="_blank"><i class="Collection-subject-class-ico Collection-subject-class-'+subject.class+'"></i></a>';
					else
						tempHTML = '<i class="Collection-subject-class-ico Collection-subject-class-'+subject.class+'"></i>';
					tempHTML += '<small>(#'+subject.id+')</small>';
					if(data.link)
						tempHTML += '<a href="' + data.link + '" target="_blank">' + subject.name + '</a>';
					else
						tempHTML += subject.name;
					$('.Collection-subject-name', oldTr).html(tempHTML);
					$('.Collection-subject-name_cn', oldTr).html(subject.name_cn);
					if((subject.ep_count == '' && subject.ep_status == '') || (subject.ep_count == null && subject.ep_status == null))
						$('#Collection-subject-'+subject.id+'-ep', oldTr).html('');
					else
					{
						ep_current = Number(subject.ep_status) + ('' == subject.ep_start ? 1 : Number(subject.ep_start)) - 1;
						ep_end = ('' == subject.ep_start ? 1 : Number(subject.ep_start)) + Number(subject.ep_count) - 1;
						stringProgress += '<label for="Collection-subject-'+subject.id+'-progress-ep">主进度</label>'
							+ '<div id="Collection-subject-'+subject.id+'-progress-ep" class="Collection-subject-progress"><div class="Collection-subject-progress-inner" style="color:white; width:'+(subject.ep_count > 0 ? subject.ep_status/subject.ep_count*100 : 50)+'%"><small>'+(ep_current < 0 ? '??' : ep_current)+' / '+(ep_end > 0 ? ep_end : '??')+'</small></div></div>';
						if(subject.ep_count == '0' || Number(subject.ep_count) > Number(subject.ep_status))
							stringProgress += '<div class="hidden-by-mouse"><small><a href="#'+subject.id+'" rel="<?php $security->index('/action/collection?do=plusEp'); ?>" class="Collection-subject-progress-plus" id="Collection-subject-'+subject.id+'-progress-ep-plus">ep.'+(ep_current+1)+'已'+arrayStatus[String(subject.class-1)]+'</a></small></div>';
						$('#Collection-subject-'+subject.id+'-ep', oldTr).html(stringProgress);
						$('.Collection-subject-progress-plus', oldTr).click(clickPlusEp);
					}
					$('.Collection-subject-note', oldTr).html('<i>备注：</i>'+(subject.note ? subject.note : '无'));
					$('.Collection-subject-rate', oldTr).html('<i>评价：</i>'+ '<span class="Collection-subject-rate-star Collection-subject-rate-star-rating"></span>'.repeat(subject.rate)+'<span class="Collection-subject-rate-star Collection-subject-rate-star-blank"></span>'.repeat(10-subject.rate));
					$('.Collection-subject-tags', oldTr).html('<i>标签：</i>'+(subject.tags ? subject.tags : '无'));
					$('.Collection-subject-comment', oldTr).html('<i>吐槽：</i>'+(subject.comment ? subject.comment : '无'));
					$(oldTr).effect('highlight');
				}
				else
					alert(data.message);
			}, 'json');
			
			oldTr.show();
			tr.remove();

			return false;
		});

		return false;
	});

	$('.dropdown-menu button.edit').click(function () {
		var btn = $(this);
		btn.parents('form').attr('action', btn.attr('rel')).submit();
	});

	$('.dropdown-menu select[name=column]').change(function () {
		if(select_options.hasOwnProperty($(this).val()))
		{
			if('type' == $(this).val())
				if('0' == <?=$class?>)
				{
					options = '';
					$.each(select_options['type'],function(key,val){
						options += val;
					});
					$(this).next().replaceWith('<select name="value">'+options+'</select>');
				}
				else
					$(this).next().replaceWith('<select name="value">'+select_options[$(this).val()][<?=$class?>]+'</select>');
			else
				$(this).next().replaceWith('<select name="value">'+select_options[$(this).val()]+'</select>');
		}
		else
			$(this).next().replaceWith('<input type="text" name="value" class="text-s w-100">');
	})
});
</script>