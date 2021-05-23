<script type="text/javascript">
	$(document).ready(function(){
		$('input[name=class]').click(function(){
			var tempHTML = '';
			tempHTML = '<li><label class="typecho-label">类型</label>';
			$.each(dictType[$('input[name=class]:checked').val()], function(key, value){
				tempHTML += '<span><input name="type" type="radio" value="'+key+'" id="type-'+key+'"><label for="type-'+key+'">'+value+'</label></span>';
			});
			tempHTML += '</li>';
			$('[id^=typecho-option-item-type]').html(tempHTML);
			$('[id^=typecho-option-item-type] input').first().attr('checked','true');
		});
		$('input[name=category]').click(function(){
			var flag = 'series' == $('input[name=category]:checked').val();
			$('[id^=typecho-option-item-class] input').attr("disabled",flag);
			$('[id^=typecho-option-item-type] input').attr("disabled",flag);
			$('[id^=typecho-option-item-progress] input').attr("disabled",flag);
			$('[id^=typecho-option-item-parent] input').attr("disabled",flag);
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
	});
</script>