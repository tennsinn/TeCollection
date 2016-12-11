<?php
include 'common.php';
include 'header.php';
include 'menu.php';
include 'common-js.php';
include 'table-js.php';

$do = isset($request->do) ? $request->get('do') : 'manage';
$class = isset($request->class) ? $request->get('class') : '0';
$status = isset($request->status) ? $request->get('status') : 'do';

$arrayClassStatus = array(
	'all' => array('全部', '书籍', '动画', '音乐', '游戏', '广播', '影视'),
	'do' => array('进行', '在读', '在看', '在听', '在玩', '在听', '在看'),
	'collect' => array('完成', '读过', '看过', '听过', '玩过', '听过', '看过'),
	'wish' => array('计划', '想读', '想看', '想听', '想玩', '想听', '想看'),
	'on_hold' => array('搁置', '搁置', '搁置', '搁置', '搁置', '搁置', '搁置'),
	'dropped' => array('抛弃', '抛弃', '抛弃', '抛弃', '抛弃', '抛弃', '抛弃')
);

$dictClass = array(1 => '书籍', 2 => '动画', 3 => '音乐', 4 => '游戏', 5 => '广播', 6 => '影视');

$dictType = array(
	'Mix' => '混合', 'Series' => '系列',
	'Novel' => '小说', 'Comic' => '漫画', 'Doujinshi' => '同人志', 'Textbook' => '课本',
	'TV' => 'TV', 'OVA' => 'OVA', 'OAD' => 'OAD', 'Movie' => '剧场',
	'Album' => '专辑', 'Single' => '单曲', 'Maxi' => 'Maxi', 'EP' => '细碟', 'Selections' => '选集',
	'iOS' => 'iOS', 'Android' => 'Android', 'PSP' => 'PSP', 'PSV' => 'PSV', 'PS' => 'PS', 'NDS' => 'NDS', '3DS' => '3DS', 'XBox' => 'XBox', 'Windows' => 'Windows', 'Online' => '网游', 'Table' => '桌游', 
	'RadioDrama' => '广播剧', 'Drama' => '歌剧',
	'Film' => '电影', 'Teleplay' => '电视剧', 'Documentary' => '纪录片', 'TalkShow' => '脱口秀', 'VarietyShow' => '综艺'
);
?>

<link rel="stylesheet" type="text/css" href="<?php $options->pluginUrl('Collection/template/stylesheet-common.css'); ?>">
<link rel="stylesheet" type="text/css" href="<?php $options->pluginUrl('Collection/template/stylesheet-panel.css'); ?>">
<script type="text/javascript">
var dictClass = {1:'书籍', 2:'动画', 3:'音乐', 4:'游戏', 5:'广播', 6:'影视'};
var dictType = {
	0:{'Mix':'混合', 'Series':'系列'},
	1:{'Novel':'小说', 'Comic':'漫画', 'Doujinshi':'同人志', 'Textbook':'课本'},
	2:{'TV':'TV', 'OVA':'OVA', 'OAD':'OAD', 'Movie':'剧场'},
	3:{'Album':'专辑', 'Single':'单曲', 'Maxi':'Maxi', 'EP':'细碟', 'Selections':'选集'},
	4:{'iOS':'iOS', 'Android':'Android', 'PSP':'PSP', 'PSV':'PSV', 'PS':'PS', 'NDS':'NDS', '3DS':'3DS', 'XBox':'XBox', 'Windows':'Windows', 'Online':'网游', 'Table':'桌游'}, 
	5:{'RadioDrama':'广播剧', 'Drama':'歌剧'},
	6:{'Film':'电影', 'Teleplay':'电视剧', 'Documentary':'纪录片', 'TalkShow':'脱口秀', 'VarietyShow':'综艺'}
};
</script>
<div class="main">
	<div class="body container">
		<?php include 'page-title.php'; ?>
		<div class="colgroup typecho-page-main" role="main">
			<div class="col-mb-12">
				<?php if($do == 'manage'): ?>
					<ul class="typecho-option-tabs right">
						<?php foreach($arrayClassStatus as $key => $value): ?>
							<li<?php if($status == $key): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&class='.$class.'&status='.$key); ?>"><?php _e($arrayClassStatus[$key][$class]); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<ul class="typecho-option-tabs clearfix">
						<?php foreach($arrayClassStatus['all'] as $key => $value): ?>
							<li<?php if($class == $key): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&class='.$key.'&status='.$status); ?>"><?php _e($value); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<div class="col-mb-12 typecho-list" role="main">
						<?php $response = Typecho_Widget::widget('Collection_Action')->showCollection(); ?>
						<div class="typecho-list-operate clearfix">
							<form method="get">
								<div class="operate">
									<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
									<div class="btn-group btn-drop">
										<button class="dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
										<ul class="dropdown-menu">
											<?php foreach(array('do', 'collect', 'wish', 'on_hold', 'dropped') as $value): ?>
												<li><a lang="<?php _e('你确认要修改这些记录到'.$arrayClassStatus[$value][$class].'吗?'); ?>" href="<?php $options->index('/action/collection?do=editStatus&&status='.$value); ?>"><?php _e('修改到'.$arrayClassStatus[$value][$class]); ?></a></li>
											<?php endforeach; ?>
											<li><a lang="<?php _e('你确认要删除记录中的这些记录吗?'); ?>" href="<?php $options->index('/action/collection?do=editStatus&status=delete'); ?>"><?php _e('删除记录'); ?></a></li>
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
												<tr id="Collection-subject-<?php echo $subject['id']; ?>" data-subject="<?php echo htmlspecialchars(json_encode($subject)); ?>">
													<td><input type="checkbox" name="id[]" value="<?php echo $subject['id']; ?>"></td>
													<td>
														<div class="Collection-subject-image"><img src="<?php echo $subject['image'] ? $subject['image'] : Typecho_common::url('Collection/template/default_cover.jpg', $options->pluginUrl); ?>" width="100px"></div>
														<div class="Collection-subject-type"><?php echo $dictClass[$subject['class']].'/'.(isset($dictType[$subject['type']]) ? $dictType[$subject['type']] : 'Unkown'); ?></div>
													</td>
													<td class="Collection-subject-meta">
														<div class="Collection-subject-name">
															<i class="Collection-subject-class-ico Collection-subject-class-<?php echo $subject['class']; ?>"></i>
															<?php
																if($subject['source'] != 'Collection')
																{
																	echo '<a href="';
																	switch($subject['source'])
																	{
																		case 'Bangumi':
																			echo 'http://bangumi.tv/subject/';
																			break;
																		case 'Douban':
																			$dictDoubanClass = array('1' => 'book', '3' => 'music', '6' => 'movie');
																			echo 'http://'.$dictDoubanClass[$subject['class']].'.douban.com/subject/';
																			break;
																		case 'Wandoujia':
																			echo 'http://www.wandoujia.com/apps/';
																			break;
																	}
																	echo $subject['subject_id'].'" target="_blank">'.$subject['name'].'</a>';
																}
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
																echo '<label for="Collection-subject-'.$subject['id'].'-progress-ep">'._t('进度一进度').'</label>';
																echo '<div id="Collection-subject-'.$subject['id'].'-progress-ep" class="Collection-subject-progress"><div class="Collection-subject-progress-inner" style="color:white; width:'.($subject['ep_count'] ? $subject['ep_status']/$subject['ep_count']*100 : 50).'%"><small>'.$subject['ep_status'].' / '.($subject['ep_count'] ? $subject['ep_count'] : '??').'</small></div></div>';
																if(!$subject['ep_count'] || $subject['ep_count']>$subject['ep_status'])
																{
																	echo '<div class="hidden-by-mouse"><small><a href="#'.$subject['id'].'" rel="';
																	$options->index('/action/collection?do=plusEp&plus=ep');
																	echo '" class="Collection-subject-progress-plus" id="Collection-subject-'.$subject['id'].'-progress-ep-plus">ep.'.($subject['ep_status']+1).'已'.$arrayClassStatus['collect'][$subject['class']].'</a></small></div>';
																}
															}
															echo '</div>';
															echo '<div id="Collection-subject-'.$subject['id'].'-sp">';
															if(!is_null($subject['sp_count']) && !is_null($subject['sp_status']))
															{
																echo '<label for="Collection-subject-'.$subject['id'].'-progress-sp">'._t('进度二进度').'</label>';
																echo '<div id="Collection-subject-'.$subject['id'].'-progress-sp" class="Collection-subject-progress"><div class="Collection-subject-progress-inner" style="color:white; width:'.($subject['sp_count'] ? $subject['sp_status']/$subject['sp_count']*100 : 50).'%"><small>'.$subject['sp_status'].' / '.($subject['sp_count'] ? $subject['sp_count'] : '??').'</small></div></div>';
																if(!$subject['sp_count'] || $subject['sp_count']>$subject['sp_status'])
																{
																	echo '<div class="hidden-by-mouse"><small><a href="#'.$subject['id'].'" rel="';
																	$options->index('/action/collection?do=plusEp&plus=sp');
																	echo '" class="Collection-subject-progress-plus" id="Collection-subject-'.$subject['id'].'-progress-sp-plus">sp.'.($subject['sp_status']+1).'已'.$arrayClassStatus['collect'][$subject['class']].'</a></small></div>';
																}
															}
															echo '</div>';
														?>
													</td>
													<td class="Collection-subject-review">
														<p class="Collection-subject-rate"><i>评价：</i><?php echo str_repeat('<span class="Collection-subject-rate-star Collection-subject-rate-star-rating"></span>', $subject['rate']); echo str_repeat('<span class="Collection-subject-rate-star Collection-subject-rate-star-blank"></span>', 10-$subject['rate']); ?></p>
														<p class="Collection-subject-tags"><i>标签：</i><?php echo $subject['tags'] ? $subject['tags'] : '无'; ?></p>
														<p class="Collection-subject-comment"><i>吐槽：</i><?php echo $subject['comment'] ? $subject['comment'] : '无'; ?></p>
														<p class="hidden-by-mouse"><a href="#<?php echo $subject['id']; ?>" rel="<?php $options->index('/action/collection?do=editSubject'); ?>" class="Collection-subject-edit"><?php _e('编辑'); ?></a></p>
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
												<li><a lang="<?php _e('你确认要修改这些记录到'.$arrayClassStatus[$value][$class].'吗?'); ?>" href="<?php $options->index('/action/collection?do=editStatus&status='.$value); ?>"><?php _e('修改到'.$arrayClassStatus[$value][$class]); ?></a></li>
											<?php endforeach; ?>
											<li><a lang="<?php _e('你确认要删除记录中的这些记录吗?'); ?>" href="<?php $options->index('/action/collection?do=editStatus&status=delete'); ?>"><?php _e('删除记录'); ?></a></li>
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
							$('.Collection-subject-progress-plus').click(function(){
								var tr = $(this).parents('tr');
								var t = $(this);
								var id = tr.attr('id');
								var subject = tr.data('subject');
								$.post(t.attr('rel'), {"id": subject.id}, function (data) {
									if(data.result)
									{
										if(data.plus == 'ep')
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
										{
											subject.sp_status = data.sp_status;
											if(data.status == 'collect')
												subject.status = 'collect';
											else
												t.html('sp.'+(Number(data.sp_status)+1)+'已看过');
											tr.data('subject', subject);
											t.parent().parent().prev().html('<div class="Collection-subject-progress-inner" style="color:white; width:'+(subject.sp_count != 0 ? subject.sp_status/subject.sp_count*100 : 50)+'%"><small>'+subject.sp_status+' / '+(subject.sp_count != 0 ? subject.sp_count : '??')+'</small></div>');
											if(subject.sp_count != 0 && subject.sp_status == subject.sp_count)
												t.parent().parent().remove();
										}
									} 
									else
										alert(data.message);
								});
							});

							$('.Collection-subject-edit').click(function () {
								var tr = $(this).parents('tr');
								var t = $(this);
								var id = tr.attr('id');
								var subject = tr.data('subject');
								tr.hide();
								var string = '<tr class="Collection-subject-edit">'
									+ '<td> </td>'
									+ '<td><form method="post" action="'+t.attr('rel')+'" class="Collection-subject-edit-content">'
										+ '<p><label for="'+id+'-image"><?php _e('封面'); ?></label>'
										+ '<textarea name="image" id="'+id+'-image" rows="3" class="w-100 mono"></textarea></p>'
										+ '<p><label for="'+id+'-class"><?php _e('种类'); ?></label><select id="'+id+'-class" name="class" class="w-100">'
											+ '<option value="1">书籍</option>'
											+ '<option value="2">动画</option>'
											+ '<option value="3">音乐</option>'
											+ '<option value="4">游戏</option>'
											+ '<option value="5">广播</option>'
											+ '<option value="6">影视</option>'
										+ '</select></p>'
										+ '<p><label for="'+id+'-type"><?php _e('类型'); ?></label><select id="'+id+'-type" name="type" class="w-100">'
										+ '<option value="Mix">混合</option>'
										+ '<option value="Series">系列</option>';
								$.each(dictType[subject.class], function(key, value){
									string += '<option value="'+key+'">'+value+'</option>';
								});
								string += '</select></p>'
									+ '<p><label for="'+id+'-source"><?php _e('信息来源'); ?></label><select id="'+id+'-source" name="source" class="w-100">'
										+ '<option value="Collection">收藏</option>'
										+ '<option value="Bangumi">Bangumi</option>'
										+ '<option value="Douban">豆瓣</option>'
										+ '<option value="Wandoujia">豌豆荚</option>'
									+ '</select></p>'
									+ '<p><label for="'+id+'-subject_id">来源ID</label><input class="text-s" type="text" id="'+id+'-subject_id" name="subject_id"></p>'
									+ '</form></td>'
									+ '<td><form method="post" action="'+t.attr('rel')+'" class="Collection-subject-edit-info">'
									+ '<p><label for="'+id+'-name">原名</label><input class="text-s" type="text" id="'+id+'-name" name="name"></p>'
									+ '<p><label for="'+id+'-name_cn">译名</label><input class="text-s" type="text" id="'+id+'-name_cn" name="name_cn"></p>'
									+ '<p><label for="'+id+'-parent">所属系列</label><select id="'+id+'-parent" name="parent" class="w-100"></select></p>'
									+ '<p><label for="'+id+'-ep_status">进度一进度</label><input class="text-s w-100" id="'+id+'-ep_status" name="ep_status" type="number" min="0" max="9999"></p>'
									+ '<p><label for="'+id+'-ep_count">进度一总数</label><input class="text-s w-100" type="number" name="ep_count" id="'+id+'-ep_count" min="0" max="9999"></p>'
									+ '<p><label for="'+id+'-sp_status">进度二进度</label><input class="text-s w-100" id="'+id+'-sp_status" name="sp_status" type="number" min="0" max="999"></p>'
									+ '<p><label for="'+id+'-sp_count">进度二总数</label><input class="text-s w-100" type="number" name="sp_count" id="'+id+'-sp_count" min="0" max="999"></p>'
									+ '</form></td>'
									+ '<td id="review-'+id+'"><form method="post" action="'+t.attr('rel')+'" class="Collection-subject-edit-content">'
									+ '<p><label for="'+id+'-grade">显示分级</label>'
									+ '<input type="radio" id="Collection-subject-'+id+'-edit-grade-0" name="grade" value="0" class="Collection-subject-edit-grade"><label for="Collection-subject-'+id+'-edit-grade-0">私密</label>'
									+ '<input type="radio" id="Collection-subject-'+id+'-edit-grade-1" name="grade" value="1" class="Collection-subject-edit-grade"><label for="Collection-subject-'+id+'-edit-grade-1">公开</label></p>'
									//+ '<p><label for="'+id+'-rate">评价：'
										//+'<span class="Collection-subject-rate-star Collection-subject-rate-star-rating"></span>'.repeat(subject.rate)
										//+'<span class="Collection-subject-rate-star Collection-subject-rate-star-blank"></span>'.repeat(10-subject.rate)
									//+'</label><input class="text-s w-100" type="range" name="rate" id="'+id+'-rate" min="0" max="10"></p>'
									+ '<p><label for="'+id+'-rate">评价：</label><input class="text-s w-100" type="number" name="rate" id="'+id+'-rate" min="0" max="10"></p>'
									+ '<p><label for="'+id+'-tags">标签</label>'
									+ '<input class="text-s w-100" type="text" name="tags" id="'+id+'-tags"></p>'
									+ '<p><label for="'+id+'-comment">吐槽</label>'
									+ '<textarea name="comment" id="'+id+'-comment" rows="6" class="w-100 mono"></textarea></p>'
									+ '<p><button type="submit" class="btn-s primary">提交</button>'
									+ '<button type="button" class="btn-s cancel">取消</button></p>'
									+ '</form></td>'
									+ '</tr>';
								var edit = $(string).data('id', id).data('subject', subject).insertAfter(tr);
								
								$('textarea[name=image]', edit).val(subject.image);
								$('select[name=class]', edit).val(subject.class);
								$('select[name=type]', edit).val(subject.type);
								$('select[name=source]', edit).val(subject.source);
								$('input[name=subject_id]', edit).val(subject.subject_id);
								$('input[name=name]', edit).val(subject.name);
								$('input[name=name_cn]', edit).val(subject.name_cn);
								$('select[name=parent]', edit).val(subject.parent);
								$('input[name=ep_status]', edit).val(subject.ep_status);
								$('input[name=ep_count]', edit).val(subject.ep_count);
								$('input[name=sp_status]', edit).val(subject.sp_status);
								$('input[name=sp_count]', edit).val(subject.sp_count);
								$('input[name=grade][value="'+subject.grade+'"]', edit).attr("checked", true);
								$('input[name=rate]', edit).val(subject.rate);
								$('input[name=tags]', edit).val(subject.tags);
								$('textarea[name=comment]', edit).val(subject.comment).focus();
								$.post('<?php $options->index('/action/collection'); ?>', {'do': 'getSeries'}, function(data){
									var tempHTML = '<option value="0">无</option>';
									$.each(data, function(i, value){
										tempHTML +='<option value="'+value['id']+'">'+value['name']+'</option>';
									});
									$('select[name=parent]', edit).html(tempHTML);
									$('select[name=parent]', edit).val(subject.parent);
								}, 'json');

								$('select[name=class]', edit).change(function(){
									var tempHTML = '';
									tempHTML = '<option value="Mix">混合</option>';
									$.each(dictType[$('select[name=class]', edit).val()], function(key, value){
										tempHTML += '<option value="'+key+'">'+value+'</option>';
									});
									$('select[name=type]', edit).html(tempHTML);
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

									oldTr.data('subject', subject);

									$.post(t.attr('action'), subject, function (data) {
										var stringProgress = '';
										if(data.status)
										{
											$('.Collection-subject-image', oldTr).html('<img src="'+(subject.image ? subject.image : '<?php $options->pluginUrl('Collection/template/default_cover.jpg'); ?>')+'" width="100px">');
											$('.Collection-subject-type', oldTr).html(dictClass[subject.class]+'/'+(subject.type=='Mix'||subject.type=='Series' ? dictType[0][subject.type] : dictType[subject.class][subject.type]));
											var tempHTML = '<i class="Collection-subject-class-ico Collection-subject-class-'+subject.class+'"></i>';
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
														tempHTML += '.douban.com/subject/'
														break;
													case 'Wandoujia':
														tempHTML += 'http://www.wandoujia.com/apps/';
														break;
												}
												tempHTML += subject.subject_id+'" target="_blank">'+subject.name+'</a>';
											}
											else
												tempHTML += subject.name;
											$('.Collection-subject-name', oldTr).html(tempHTML);
											$('.Collection-subject-name_cn', oldTr).html(subject.name_cn);
											if(subject.ep_count == '' && subject.ep_status == '')
												$('#Collection-subject-'+subject.id+'-ep', oldTr).html('');
											else
											{
												stringProgress += '<label for="Collection-subject-'+subject.id+'-progress-ep">进度一进度</label>'
													+ '<div id="Collection-subject-'+subject.id+'-progress-ep" class="Collection-subject-progress"><div class="Collection-subject-progress-inner" style="color:white; width:'+(subject.ep_count != 0 ? subject.ep_status/subject.ep_count*100 : 50)+'%"><small>'+subject.ep_status+' / '+(subject.ep_count != 0 ? subject.ep_count : '??')+'</small></div></div>';
												if(subject.ep_count == '0' || Number(subject.ep_count) > Number(subject.ep_status))
													stringProgress += '<div class="hidden-by-mouse"><small><a href="#'+subject.id+'" rel="<?php $options->index('/action/collection?do=plusEp&plus=ep'); ?>" class="Collection-subject-progress-plus" id="Collection-subject-'+subject.id+'-progress-ep-plus">ep.'+String(Number(subject.ep_status)+1)+'已'+arrayStatus[String(subject.class-1)]+'</a></small></div>';
												$('#Collection-subject-'+subject.id+'-ep', oldTr).html(stringProgress);
											}
											if(subject.sp_count == '' && subject.sp_status == '')
												$('#Collection-subject-'+subject.id+'-sp', oldTr).html('');
											else
											{
												stringProgress = '<label for="Collection-subject-'+subject.id+'-progress-sp">进度二进度</label>'
													+ '<div id="Collection-subject-'+subject.id+'-progress-sp" class="Collection-subject-progress"><div class="Collection-subject-progress-inner" style="color:white; width:'+(subject.sp_count != 0 ? subject.sp_status/subject.sp_count*100 : 50)+'%"><small>'+subject.sp_status+' / '+(subject.sp_count != 0 ? subject.sp_count : '??')+'</small></div></div>';
												if(subject.sp_count == '0' || Number(subject.sp_count) > Number(subject.sp_status))
													stringProgress += '<div class="hidden-by-mouse"><small><a href="#'+subject.id+'" rel="<?php $options->index('/action/collection?do=plusEp&plus=sp'); ?>" class="Collection-subject-progress-plus" id="Collection-subject-'+subject.id+'-progress-sp-plus">sp.'+String(Number(subject.sp_status)+1)+'已'+arrayStatus[String(subject.class-1)]+'</a></small></div>';
												$('#Collection-subject-'+subject.id+'-sp', oldTr).html(stringProgress);
											}
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
						<li<?php if($do == 'search'): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&do=search'); ?>">搜索</a></li>
						<li<?php if($do == 'input'): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&do=input'); ?>">输入</a></li>
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
													<li><a lang="<?php _e('你确认要添加这些记录到'.$arrayClassStatus[$value][$class].'吗?'); ?>" href="<?php $options->index('/action/collection?do=editStatus&status='.$value.'&source='.$request->source.'&class='.$request->class); ?>"><?php _e('添加到'.$arrayClassStatus[$value][$class]); ?></a></li>
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
											<option value="Douban"<?php if($request->get('source') == 'Douban'): ?> selected="ture"<?php endif;?>>豆瓣</option>
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
												<?php foreach($response['list'] as $subject_id => $subject): ?>
													<tr>
														<td><input type="checkbox" name="subject_id[]" value="<?php echo $subject_id; ?>"></td>
														<td><img src="<?php echo $subject['image']; ?>" width="100px"></td>
														<td><div>
															<i class="Collection-subject-class-ico Collection-subject-class-<?php echo $subject['class']; ?>"></i>
															<?php
																echo '<a href="';
																switch($request->get('source'))
																{
																	case 'Bangumi':
																		echo 'http://bangumi.tv/subject/';
																		break;
																	case 'Douban':
																		$arrayDoubanClass = array('1' => 'book', '3' => 'music', '6' => 'movie');
																		echo 'http://'.$arrayDoubanClass[$subject['class']].'.douban.com/subject/';
																		break;
																}
																echo $subject_id.'">'.$subject['name'].'</a></div>';
																echo $subject['name_cn'] ? '<div><small>'.$subject['name_cn'].'</small></div>' : '';
															?>
														</td>
														<td><?php echo $subject['info']; ?></td>
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
									'Bangumi':{0:'全部', 1:'书籍', 2:'动画', 3:'音乐', 4:'游戏', 5:'广播', 6:'影视'},
									'Douban':{1:'图书',3:'音乐',6:'电影'}
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
								$.post('<?php $options->index('/action/collection'); ?>', {'do': 'getSeries'}, function(data){
									var tempHTML = '<option value="0">无</option>';
									$.each(data, function(i, value){
										tempHTML +='<option value="'+value['id']+'">'+value['name']+'</option>';
									});
									$('select[name=parent]').html(tempHTML);
								}, 'json');

								$('input[name=class]').click(function(){
									var tempHTML = '';
									tempHTML = '<li><label class="typecho-label">类型</label>';
									tempHTML += '<span><input name="type" type="radio" value="Mix" id="type-Mix" checked><label for="type-Mix">混合</label></span>';
									tempHTML += '<span><input name="type" type="radio" value="Series" id="type-Series"><label for="type-Series">系列</label></span>';
									$.each(dictType[$('input[name=class]:checked').val()], function(key, value){
										tempHTML += '<span><input name="type" type="radio" value="'+key+'" id="type-'+key+'"><label for="type-'+key+'">'+value+'</label></span>';
									});
									tempHTML += '</li>';
									$('#typecho-option-item-type-2').html(tempHTML);
								});
							});
						</script>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php
include 'copyright.php';
include 'footer.php';
?>

