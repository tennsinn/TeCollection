<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

include 'common.php';
include 'header.php';
include 'menu.php';
include 'common-js.php';
include 'table-js.php';

$do = $request->get('do', 'manage');
$category = $request->get('category', 'subject');
$class = $request->get('class', '0');
$status = $request->get('status', 'do');

Typecho_Widget::widget('Collection_Config@panel')->to($config);
?>

<link rel="stylesheet" type="text/css" href="<?php $options->pluginUrl('Collection/template/stylesheet-common.css'); ?>">
<link rel="stylesheet" type="text/css" href="<?php $options->pluginUrl('Collection/template/stylesheet-panel.css'); ?>">
<script type="text/javascript">
<?php
echo "var dictCategory = ".$config->jsCategory.";\n";
echo "var dictClass = ".$config->jsClass.";\n";
echo "var dictType = ".$config->jsType.";\n";
echo "var dictSource = ".$config->jsSource.";\n";
echo "var dictGrade = ".$config->jsGrade.";\n";
?>
</script>
<div class="main">
	<div class="body container">
		<?php include 'page-title.php'; ?>
		<div class="colgroup typecho-page-main" role="main">
			<div class="col-mb-12">
				<?php if($do == 'manage'): ?>
					<ul class="typecho-option-tabs clearfix">
						<?php foreach($config->dictCategory as $key => $value): ?>
							<li<?php if($category == $key): ?> class="current"<?php endif; ?>><a href="<?php 'series' == $key ? $options->adminUrl('extending.php?panel=Collection%2FPanel.php&category=series&status='.$status) : $options->adminUrl('extending.php?panel=Collection%2FPanel.php&category='.$key.'&class='.$class.'&status='.$status); ?>"><?php _e($value); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<ul class="typecho-option-tabs clearfix">
						<?php if('series' != $category) : ?>
							<?php foreach($config->dictStatusAll['all'] as $key => $value): ?>
								<li<?php if($class == $key): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&category='.$category.'&class='.$key.'&status='.$status); ?>"><?php _e($value); ?></a></li>
							<?php endforeach; ?>
						<?php else: ?>
							<li class="current"><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&class='.$key.'&status='.$status); ?>"><?php echo $config->dictStatusAll['all'][0] ?></a></li>
						<?php endif; ?>
					</ul>
					<ul class="typecho-option-tabs clearfix">
						<?php foreach($config->dictStatusAll as $key => $value): ?>
							<li<?php if($status == $key): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&category='.$category.'&class='.$class.'&status='.$key); ?>"><?php _e($config->dictStatusAll[$key][$class]); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<div class="col-mb-12 typecho-list" role="main">
						<?php $response = Typecho_Widget::widget('Collection_Action')->showCollection(); ?>
						<div class="typecho-list-operate clearfix">
							<form method="get">
								<div class="operate">
									<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
									<div class="btn-group btn-drop">
										<button class="btn dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
										<ul class="dropdown-menu">
											<?php foreach(array('do', 'collect', 'wish', 'on_hold', 'dropped') as $value): ?>
												<li><a lang="<?php _e('你确认要修改这些记录到'.$config->dictStatusAll[$value][$class].'吗?'); ?>" href="<?php $security->index('/action/collection?do=editStatus&status='.$value); ?>"><?php _e('修改到'.$config->dictStatusAll[$value][$class]); ?></a></li>
											<?php endforeach; ?>
											<li><a lang="<?php _e('你确认要删除记录中的这些记录吗?'); ?>" href="<?php $security->index('/action/collection?do=editStatus&status=delete'); ?>"><?php _e('删除记录'); ?></a></li>
										</ul>
									</div>
								</div>
								<div class="search" role="search">
									<input type="hidden" value="Collection/Panel.php" name="panel">
									<input type="hidden" value="manage" name="do">
									<input type="hidden" value="<?php echo $class; ?>" name="class">
									<input type="hidden" value="<?php echo $status; ?>" name="status">
									<?php if ('' != $request->keywords || '' != $request->field): ?>
										<a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php' . (isset($request->class) ? '&class=' . htmlspecialchars($request->get('class')) : '') . (isset($request->status) ? '&status=' . htmlspecialchars($request->get('status')) : '')); ?>"><?php _e('&laquo; 取消筛选'); ?></a>
									<?php endif; ?>
									<label>搜索</label>
									<input type="text" class="text-s" placeholder="<?php _e('请输入关键字'); ?>" value="<?php echo htmlspecialchars($request->keywords); ?>"<?php if ('' == $request->keywords): ?> onclick="value='';name='keywords';" <?php else: ?> name="keywords"<?php endif; ?>>
									<select name="field">
										<option value="name"<?php if($request->get('field') == 'name'): ?> selected="true"<?php endif; ?>>名称</option>
										<option value="id"<?php if($request->get('field') == 'id'): ?> selected="true"<?php endif; ?>>ID</option>
										<option value="tags"<?php if($request->get('field') == 'tags'): ?> selected="true"<?php endif; ?>>标签</option>
										<option value="comment"<?php if($request->get('field') == 'comment'): ?> selected="true"<?php endif; ?>>吐槽</option>
										<option value="note"<?php if($request->get('field') == 'note'): ?> selected="true"<?php endif; ?>>备注</option>
									</select>
									<label>排序</label>
									<select name="orderby">
										<option value="id"<?php if($request->get('orderby') == 'id'): ?> selected="true"<?php endif; ?>>ID</option>
										<option value="rate"<?php if($request->get('orderby') == 'rate'): ?> selected="true"<?php endif; ?>>评价</option>
										<option value="time_touch"<?php if($request->get('orderby') == 'time_touch'): ?> selected="true"<?php endif; ?>>最后修改</option>
										<option value="time_start"<?php if($request->get('orderby') == 'time_start'): ?> selected="true"<?php endif; ?>>开始时间</option>
										<option value="time_finish"<?php if($request->get('orderby') == 'time_finish'): ?> selected="true"<?php endif; ?>>完成时间</option>
									</select>
									<select name="order">
										<option value="DESC"<?php if($request->get('order') == 'DESC'): ?> selected="true"<?php endif; ?>>降序</option>
										<option value="ASC"<?php if($request->get('order') == 'ASC'): ?> selected="true"<?php endif; ?>>升序</option>
									</select>
									<button type="submit" class="btn btn-s"><?php _e('筛选'); ?></button>
								</div>
							</form>
						</div>
						<form method="post" class="operate-form">
							<div class="typecho-table-wrap">
								<table class="typecho-list-table">
									<colgroup>
										<col width="20px">
										<col width="120px">
										<col width="200px">
										<col>
									</colgroup>
									<thead>
										<tr>
											<th></th>
											<th>封面</th>
											<th>名称</th>
											<th>简评</th>
										</tr>
									</thead>
									<tbody>
										<?php if($response['result']): ?>
											<?php foreach($response['list'] as $subject): ?>
												<?php $subject['published'] = date('Y-m-d m:s' ,$subject['published']); ?>
												<tr id="Collection-subject-<?php echo $subject['id']; ?>" data-subject="<?php echo htmlspecialchars(json_encode($subject)); ?>">
													<td><input type="checkbox" name="id[]" value="<?php echo $subject['id']; ?>"></td>
													<td>
														<div class="Collection-subject-category"><?php echo $config->dictCategory[$subject['category']].' / '.$config->dictgrade[$subject['grade']]; ?></div>
														<div class="Collection-subject-image"><img src="<?php echo $subject['image'] ? $subject['image'] : Typecho_common::url('Collection/template/default_cover.jpg', $options->pluginUrl); ?>" width="100px"></div>
														<div class="Collection-subject-type">
															<?php if('series' != $subject['category'] && !is_null($subject['class']))
																if($subject['class'] > 0 && $subject['class'] <= 6)
																{
																	echo $config->dictClass[$subject['class']].' / ';
																	if(!is_null($subject['type']) && isset($config->dictType[$subject['class']][$subject['type']]))
																		echo $config->dictType[$subject['class']][$subject['type']];
																	else
																		echo '未知';
																}
																else
																	echo '未知 / 未知';
															?>
														</div>
													</td>
													<td class="Collection-subject-meta">
														<div class="Collection-subject-name">
															<?php
																if($subject['media_link'])
																	echo '<a href="'.$subject['media_link'].'" target="_blank"><i class="Collection-subject-class-ico Collection-subject-class-.'.$subject['class'].'"></i></a>';
																else
																	echo '<i class="Collection-subject-class-ico Collection-subject-class-.'.$subject['class'].'"></i>';
																echo '<small>(#'.$subject['id'].')</small>';
																if(array_key_exists($subject['source'], $config->dictSource) && ('Collection' != $subject['source']))
																	echo '<a href="'.$config->dictSource[$subject['source']]['url'].$subject['source_id'].'" target="_blank">'.$subject['name'].'</a>';
																else
																	echo $subject['name'];
															?>
														</div>
														<div class="Collection-subject-name_cn">
															<?php echo $subject['name_cn'] ? $subject['name_cn'] : ''; ?>
														</div>
														<?php
															echo '<div id="Collection-subject-'.$subject['id'].'-ep">';
															if(!is_null($subject['ep_count']) && !is_null($subject['ep_status']))
															{
																echo '<label for="Collection-subject-'.$subject['id'].'-progress-ep">'._t('主进度').'</label>';
																echo '<div id="Collection-subject-'.$subject['id'].'-progress-ep" class="Collection-subject-progress"><div class="Collection-subject-progress-inner" style="color:white; width:'.($subject['ep_count'] ? $subject['ep_status']/$subject['ep_count']*100 : 50).'%"><small>'.$subject['ep_status'].' / '.($subject['ep_count'] ? $subject['ep_count'] : '??').'</small></div></div>';
																if(!$subject['ep_count'] || $subject['ep_count']>$subject['ep_status'])
																{
																	echo '<div class="hidden-by-mouse"><small><a href="#'.$subject['id'].'" rel="';
																	$security->index('/action/collection?do=plusEp');
																	echo '" class="Collection-subject-progress-plus" id="Collection-subject-'.$subject['id'].'-progress-ep-plus">ep.'.($subject['ep_status']+1).'已'.$config->dictStatusAll['collect'][$subject['class']].'</a></small></div>';
																}
															}
															echo '</div>';
														?>
													</td>
													<td class="Collection-subject-review">
														<p class="Collection-subject-note"><i>备注：</i><?php echo $subject['note'] ? $subject['note'] : '无'; ?></p>
														<p class="Collection-subject-rate"><i>评价：</i><?php echo str_repeat('<span class="Collection-subject-rate-star Collection-subject-rate-star-rating"></span>', $subject['rate']); echo str_repeat('<span class="Collection-subject-rate-star Collection-subject-rate-star-blank"></span>', 10-$subject['rate']); ?></p>
														<p class="Collection-subject-tags"><i>标签：</i><?php echo $subject['tags'] ? $subject['tags'] : '无'; ?></p>
														<p class="Collection-subject-comment"><i>吐槽：</i><?php echo $subject['comment'] ? $subject['comment'] : '无'; ?></p>
														<p class="hidden-by-mouse"><a href="#<?php echo $subject['id']; ?>" rel="<?php $security->index('/action/collection?do=editSubject'); ?>" class="Collection-subject-edit"><?php _e('编辑'); ?></a></p>
													</td>
												</tr>
											<?php endforeach; ?>
										<?php else: ?>
											<tr><td colspan="6"><h6 class="typecho-list-table-title"><?php echo $response['message']; ?></h6></td></tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</form>
						<div class="typecho-list-operate clearfix">
							<form method="get">
								<div class="operate">
									<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
									<div class="btn-group btn-drop">
										<button class="dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
										<ul class="dropdown-menu">
											<?php foreach(array('do', 'collect', 'wish', 'on_hold', 'dropped') as $value): ?>
												<li><a lang="<?php _e('你确认要修改这些记录到'.$config->dictStatusAll[$value][$class].'吗?'); ?>" href="<?php $security->index('/action/collection?do=editStatus&status='.$value); ?>"><?php _e('修改到'.$config->dictStatusAll[$value][$class]); ?></a></li>
											<?php endforeach; ?>
											<li><a lang="<?php _e('你确认要删除记录中的这些记录吗?'); ?>" href="<?php $security->index('/action/collection?do=editStatus&status=delete'); ?>"><?php _e('删除记录'); ?></a></li>
										</ul>
									</div>
								</div>
							</form>
							<?php if($response['result']): ?>
								<ul class="typecho-pager">
									<?php $response['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>
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
										if(data.status == 'collect')
											subject.status = 'collect';
										else
											t.html('ep.'+(Number(data.ep_status)+1)+'已看过');
										tr.data('subject', subject);
										t.parent().parent().prev().html('<div class="Collection-subject-progress-inner" style="color:white; width:'+(subject.ep_count != 0 ? subject.ep_status/subject.ep_count*100 : 50)+'%"><small>'+subject.ep_status+' / '+(subject.ep_count != 0 ? subject.ep_count : '??')+'</small></div>');
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
										+ '<p><label for="'+id+'-category"><?=_t('大类')?></label><select id="'+id+'-category" name="category" class="w-100">';
								$.each(dictCategory, function(key, value){
									string += '<option value="'+key+'">'+value+'</option>';
								});
								string += '</select></p>'
										+ '<p><label for="'+id+'-image"><?=_t('封面')?></label>'
										+ '<textarea name="image" id="'+id+'-image" rows="3" class="w-100 mono"></textarea></p>'
										+ '<p><label for="'+id+'-class"><?=_t('种类')?></label><select id="'+id+'-class" name="class" class="w-100">';
								$.each(dictClass, function(key, value){
									string += '<option value="'+key+'">'+value+'</option>';
								});
								string += '</select></p>'
										+ '<p><label for="'+id+'-type"><?=_t('类型')?></label><select id="'+id+'-type" name="type" class="w-100">';
								if(subject.class && subject.class > 0 && subject.class <=6)
									$.each(dictType[subject.class], function(key, value){
										string += '<option value="'+key+'">'+value+'</option>';
									});
								string += '</select></p>'
									+ '<p><label for="'+id+'-author"><?=_t('作者')?></label><input type="text" id="'+id+'-author" name="author" class="text-s"></p>'
									+ '<p><label for="'+id+'-publisher"><?=_t('出版商')?></label><input type="text" id="'+id+'-publisher" name="publisher" class="text-s"></p>'
									+ '<p><label for="'+id+'-published"><?=_t('出版时间')?></label><input type="text" id="'+id+'-published" name="published" class="text-s"></p>'
									+ '<p><label for="'+id+'-source"><?=_t('信息来源')?></label><select id="'+id+'-source" name="source" class="w-100">'
								$.each(dictSource, function(key, value){
									string += '<option value="'+key+'">'+value['name']+'</option>';
								});
								string += '</select></p>'
									+ '<p><label for="'+id+'-source_id"><?=_t('来源ID')?></label><input type="text" id="'+id+'-source_id" name="source_id" class="text-s"></p>'
									+ '</form></td>'
									+ '<td><form method="post" action="'+t.attr('rel')+'" class="Collection-subject-edit-info">'
									+ '<p><label for="'+id+'-name"><?=_t('原名')?></label><input type="text" id="'+id+'-name" name="name" class="text-s"></p>'
									+ '<p><label for="'+id+'-name_cn"><?=_t('译名')?></label><input type="text" id="'+id+'-name_cn" name="name_cn" class="text-s"></p>'
									+ '<p><label for="'+id+'-parent"><?=_t('关联记录')?></label><input type="text" id="'+id+'-parent" name="parent" class="text-s"></p>'
									+ '<p><label for="'+id+'-parent_order"><?=_t('关联顺序')?></label><input type="text" id="'+id+'-parent_order" name="parent_order" class="text-s"></p>'
									+ '<p><label for="'+id+'-parent_label"><?=_t('关联标签')?></label><input type="text" id="'+id+'-parent_label" name="parent_label" class="text-s"></p>'
									+ '<p><label for="'+id+'-ep_status"><?=_t('主进度')?></label><input type="text" id="'+id+'-ep_status" name="ep_status" class="text-s"></p>'
									+ '<p><label for="'+id+'-ep_count"><?=_t('主进度总数')?></label><input type="text" name="ep_count" id="'+id+'-ep_count" class="text-s"></p>'
									+ '</form></td>'
									+ '<td><form method="post" action="'+t.attr('rel')+'" class="Collection-subject-edit-content">'
									+ '<p><label for="'+id+'-media_link"><?=_t('媒体链接')?></label><input type="text" name="media_link" id="'+id+'-media_link" class="text-s w-100"></p>'
									+ '<p><label for="'+id+'-grade"><?=_t('显示分级')?></label><select id="'+id+'-grade" name="grade" class="w-100">'
								$.each(dictGrade, function(key, value){
									string += '<option value="'+key+'">'+value+'</option>';
								});
								string += '</select></p>'
									+ '<p><label for="'+id+'-note"><?=_t('备注')?></label>'
									+ '<textarea id="'+id+'-note" name="note" rows="2" class="w-100 mono"></textarea></p>'
									+ '<p><label for="'+id+'-rate"><?=_t('评价')?></label><input type="text" name="rate" id="'+id+'-rate" class="text-s w-100"></p>'
									+ '<p><label for="'+id+'-tags"><?=_t('标签')?></label><input type="text" name="tags" id="'+id+'-tags" class="text-s w-100"></p>'
									+ '<p><label for="'+id+'-comment"><?=_t('吐槽')?></label><textarea id="'+id+'-comment" name="comment" rows="6" class="w-100 mono"></textarea></p>'
									+ '<p><button type="submit" class="btn btn-s primary"><?=_t('提交')?></button>'
									+ '<button type="button" class="btn btn-s cancel"><?=_t('取消')?></button></p>'
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
								$('select[name=grade]', edit).val(subject.grade);
								$('textarea[name=note]', edit).val(subject.note);
								$('input[name=rate]', edit).val(subject.rate);
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
									var tempHTML = '';
									var valClass = $('select[name=class]', edit).val();
									if(valClass && valClass > 0 && valClass <= 6)
										$.each(dictType[valClass], function(key, value){
											tempHTML += '<option value="'+key+'">'+value+'</option>';
										});
									$('select[name=type]', edit).html(tempHTML);
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

								$('input[name=rate]', edit).change(function(){
									$('label[for="'+id+'-rate"]', edit).html('评价：'+'<span class="Collection-subject-rate-star Collection-subject-rate-star-rating"></span>'.repeat($('input[name=rate]', edit).val())+'<span class="Collection-subject-rate-star Collection-subject-rate-star-blank"></span>'.repeat(10-$('input[name=rate]', edit).val()));
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
										subject['ep_status'] = null;
									}

									oldTr.data('subject', subject);

									$.post(t.attr('action'), subject, function (data) {
										var stringProgress = '';
										if(data.result)
										{
											$('.Collection-subject-category', oldTr).html(dictCategory[subject.category]+' / '+dictGrade[subject.grade]);
											$('.Collection-subject-image', oldTr).html('<img src="'+(subject.image ? subject.image : '<?php $options->pluginUrl('Collection/template/default_cover.jpg'); ?>')+'" width="100px">');
											var tempHTML = ''
											if('series' != subject.category && subject.class)
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
											$('.Collection-subject-type', oldTr).html(tempHTML);

											if(subject.media_link)
												tempHTML = '<a href="'+subject.media_link+'" target="_blank"><i class="Collection-subject-class-ico Collection-subject-class-'+subject.class+'"></i></a>';
											else
												tempHTML = '<i class="Collection-subject-class-ico Collection-subject-class-'+subject.class+'"></i>';
											tempHTML += '<small>(#'+subject.id+')</small>';
											if(dictSource.hasOwnProperty(subject.source) && (subject.source != 'Collection'))
												tempHTML += '<a href="' + dictSource[subject.source]['url'] + subject.source_id + '" target="_blank">' + subject.name + '</a>';
											else
												tempHTML += subject.name;
											$('.Collection-subject-name', oldTr).html(tempHTML);
											$('.Collection-subject-name_cn', oldTr).html(subject.name_cn);
											if((subject.ep_count == '' && subject.ep_status == '') || (subject.ep_count == null && subject.ep_status == null))
												$('#Collection-subject-'+subject.id+'-ep', oldTr).html('');
											else
											{
												stringProgress += '<label for="Collection-subject-'+subject.id+'-progress-ep">主进度</label>'
													+ '<div id="Collection-subject-'+subject.id+'-progress-ep" class="Collection-subject-progress"><div class="Collection-subject-progress-inner" style="color:white; width:'+(subject.ep_count != 0 ? subject.ep_status/subject.ep_count*100 : 50)+'%"><small>'+subject.ep_status+' / '+(subject.ep_count != 0 ? subject.ep_count : '??')+'</small></div></div>';
												if(subject.ep_count == '0' || Number(subject.ep_count) > Number(subject.ep_status))
													stringProgress += '<div class="hidden-by-mouse"><small><a href="#'+subject.id+'" rel="<?php $security->index('/action/collection?do=plusEp'); ?>" class="Collection-subject-progress-plus" id="Collection-subject-'+subject.id+'-progress-ep-plus">ep.'+String(Number(subject.ep_status)+1)+'已'+arrayStatus[String(subject.class-1)]+'</a></small></div>';
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
						});
					</script>
				<?php else: ?>
					<a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php'); ?>">返回</a>
					<ul class="typecho-option-tabs right">
						<li<?php if($do == 'input'): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&do=input'); ?>">输入</a></li>
						<li<?php if($do == 'search'): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&do=search'); ?>">搜索</a></li>
					</ul>
					<?php if($do == 'search'): ?>
						<div class="col-mb-12 typecho-list" role="main">
							<?php $response = Typecho_Widget::widget('Collection_Action')->search(); ?>
							<div class="typecho-list-operate clearfix">
								<form method="get">
									<div class="operate">
										<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
										<div class="btn-group btn-drop">
											<button class="dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
											<ul class="dropdown-menu">
												<?php foreach(array('do', 'collect', 'wish', 'on_hold', 'dropped') as $value): ?>
													<li><a lang="<?php _e('你确认要添加这些记录到'.$config->dictStatusAll[$value][$class].'吗?'); ?>" href="<?php $security->index('/action/collection?do=editStatus&status='.$value.'&source='.$request->source.'&class='.$request->class); ?>"><?php _e('添加到'.$config->dictStatusAll[$value][$class]); ?></a></li>
												<?php endforeach; ?>
											</ul>
										</div>
									</div>
									<div class="search" role="search">
										<input type="hidden" value="Collection/Panel.php" name="panel">
										<input type="hidden" value="search" name="do">
										<input type="text" class="text-s" placeholder="<?php _e('请输入关键字'); ?>" value="<?php echo htmlspecialchars($request->keywords); ?>"<?php if ('' == $request->keywords): ?> onclick="value='';name='keywords';" <?php else: ?> name="keywords"<?php endif; ?>>
										<select name="source">
											<option value="Bangumi"<?php if($request->get('source') == 'Bangumi'): ?> selected="ture"<?php endif;?>>Bangumi</option>
										</select>
										<select name="class">
										</select>
										<button type="submit" class="btn-s"><?php _e('搜索'); ?></button>
									</div>
								</form>
							</div>
							<form method="post" class="operate-form">
								<div class="typecho-table-wrap">
									<table class="typecho-list-table">
										<colgroup>
											<col width="20px">
											<col width="120px">
											<col width="180px">
											<col width="">
										</colgroup>
										<thead>
											<tr>
												<th></th>
												<th>封面</th>
												<th>名称</th>
												<th>信息</th>
											</tr>
										</thead>
										<tbody>
											<?php if($response['result']): ?>
												<?php foreach($response['list'] as $source_id => $subject): ?>
													<tr>
														<td><input type="checkbox" name="source_id[]" value="<?php echo $source_id; ?>"></td>
														<td><img src="<?php echo $subject['image']; ?>" width="100px"></td>
														<td class="Collection-box-title"><div>
															<i class="Collection-subject-class-ico Collection-subject-class-<?php echo $subject['class']; ?>"></i>
															<?php
																echo '<a href="';
																switch($request->get('source'))
																{
																	case 'Bangumi':
																		echo 'http://bgm.tv/subject/';
																		break;
																	case 'Douban':
																		$arrayDoubanClass = array('1' => 'book', '3' => 'music', '6' => 'movie');
																		echo 'http://'.$arrayDoubanClass[$subject['class']].'.douban.com/subject/';
																		break;
																}
																echo $source_id.'">'.$subject['name'].'</a></div>';
																echo $subject['name_cn'] ? '<div><small>'.$subject['name_cn'].'</small></div>' : '';
															?>
														</td>
														<td class="Collection-box-info"><?php echo $subject['info']; ?></td>
													</tr>
												<?php endforeach; ?>
											<?php else: ?>
												<tr><td colspan="4"><h6 class="typecho-list-table-title"><?php echo $response['message']; ?></h6></td></tr>
											<?php endif; ?>
										</tbody>
									</table>
								</div>
							</form>
							<div class="typecho-list-operate clearfix">
								<?php if($response['result']): ?>
									<ul class="typecho-pager">
										<?php $response['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
									</ul>
								<?php endif; ?>
							</div>
						</div>
						<script type="text/javascript">
							$(document).ready(function(){
								var dictClass = {
									'Bangumi':{0:'全部', 1:'书籍', 2:'动画', 3:'音乐', 4:'游戏', 5:'广播', 6:'影视'}
								};
								var objectSource = $('select[name=source]');
								var changeSource = function(){
									var tempHTML = '';
									$.each(dictClass[objectSource.val()], function(key, value){
										tempHTML += '<option value ='+key+'>'+value+'</option>';
									});
									$('select[name=class]').html(tempHTML);
								}
								changeSource();
								$('select[name=class]').val(<?php echo $request->get('class'); ?>);
								objectSource.change(function(){
									changeSource();
								});
							});
						</script>
					<?php else: ?>
						<div class="col-mb-12 typecho-list" role="main">
							<?php Typecho_Widget::widget('Collection_Action')->formInput()->render(); ?>
						</div>
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
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<script src="<?php $options->adminStaticUrl('js', 'timepicker.js?v=' . $suffixVersion); ?>"></script>

<?php
include 'copyright.php';
include 'footer.php';
?>
