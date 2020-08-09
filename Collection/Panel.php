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

$dictCategory = array('series' => '系列', 'subject' => '记录', 'volume' => '分卷', 'episode' => '章节');
$dictClass = array(1 => '书籍', 2 => '动画', 3 => '音乐', 4 => '游戏', 5 => '广播', 6 => '影视');
$dictType = array(
	'Novel' => '小说', 'Comic' => '漫画', 'Doujinshi' => '同人志', 'Textbook' => '教材',
	'TV' => 'TV', 'OVA' => 'OVA', 'OAD' => 'OAD', 'Movie' => '剧场',
	'Album' => '专辑', 'Single' => '单曲', 'Maxi' => 'Maxi', 'EP' => '细碟', 'Selections' => '选集',
	'iOS' => 'iOS', 'Android' => 'Android', 'PSP' => 'PSP', 'PSV' => 'PSV', 'PS4' => 'PS4', 'NDS' => 'NDS', '3DS' => '3DS', 'NSwitch' => 'NSwitch', 'XBox' => 'XBox', 'Windows' => 'Windows', 'Online' => '网游', 'Table' => '桌游', 
	'RadioDrama' => '广播剧', 'Drama' => '歌剧',
	'Film' => '电影', 'Teleplay' => '电视剧', 'Documentary' => '纪录片', 'TalkShow' => '脱口秀', 'VarietyShow' => '综艺'
);
$dictSource = array(
	'Bangumi' => array('name' => 'Bangumi', 'url' => 'http://bangumi.tv/subject/'),
	'Douban' => array('name' => '豆瓣', 'url' => 'https://www.douban.com/subject/'),
	'Steam' => array('name' => 'Steam', 'url' => 'http://store.steampowered.com/app/'),
	'Wandoujia' => array('name' => '豌豆荚', 'url' => 'http://www.wandoujia.com/apps/'),
	'TapTap' => array('name' => 'TapTap', 'url' => 'https://www.taptap.com/app/'),
	'BiliBili' => array('name' => 'BiliBili', 'url' => 'https://www.bilibili.com/bangumi/media/')
);
?>

<link rel="stylesheet" type="text/css" href="<?php $options->pluginUrl('Collection/template/stylesheet-common.css'); ?>">
<link rel="stylesheet" type="text/css" href="<?php $options->pluginUrl('Collection/template/stylesheet-panel.css'); ?>">
<script type="text/javascript">
var dictCategory = {'series' : '系列', 'subject' : '记录', 'volume' : '分卷', 'episode' : '章节'};
var dictClass = {1:'书籍', 2:'动画', 3:'音乐', 4:'游戏', 5:'广播', 6:'影视'};
var dictType = {
	1:{'Novel':'小说', 'Comic':'漫画', 'Doujinshi':'同人志', 'Textbook':'教材'},
	2:{'TV':'TV', 'OVA':'OVA', 'OAD':'OAD', 'Movie':'剧场'},
	3:{'Album':'专辑', 'Single':'单曲', 'Maxi':'Maxi', 'EP':'细碟', 'Selections':'选集'},
	4:{'iOS':'iOS', 'Android':'Android', 'PSP':'PSP', 'PSV':'PSV', 'PS4':'PS4', 'NDS':'NDS', '3DS':'3DS', 'NSwitch':'NSwitch', 'XBox':'XBox', 'Windows':'Windows', 'Online':'网游', 'Table':'桌游'}, 
	5:{'RadioDrama':'广播剧', 'Drama':'歌剧'},
	6:{'Film':'电影', 'Teleplay':'电视剧', 'Documentary':'纪录片', 'TalkShow':'脱口秀', 'VarietyShow':'综艺'}
};
var dictSource = {
	'Bangumi' : {'name' : 'Bangumi', 'url' : 'http://bangumi.tv/subject/'},
	'Douban' : {'name' : '豆瓣', 'url' : 'https://www.douban.com/subject/'},
	'Steam' : {'name' : 'Steam', 'url' : 'http://store.steampowered.com/app/'},
	'Wandoujia' : {'name' : '豌豆荚', 'url' : 'http://www.wandoujia.com/apps/'},
	'TapTap' : {'name' : 'TapTap', 'url' : 'https://www.taptap.com/app/'},
	'BiliBili': {'name' : 'BiliBili', 'url' : 'https://www.bilibili.com/bangumi/media/'}
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
										<button class="btn dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
										<ul class="dropdown-menu">
											<?php foreach(array('do', 'collect', 'wish', 'on_hold', 'dropped') as $value): ?>
												<li><a lang="<?php _e('你确认要修改这些记录到'.$arrayClassStatus[$value][$class].'吗?'); ?>" href="<?php $options->index('/action/collection?do=editStatus&status='.$value); ?>"><?php _e('修改到'.$arrayClassStatus[$value][$class]); ?></a></li>
											<?php endforeach; ?>
											<li><a lang="<?php _e('你确认要删除记录中的这些记录吗?'); ?>" href="<?php $options->index('/action/collection?do=editStatus&status=delete'); ?>"><?php _e('删除记录'); ?></a></li>
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
												<tr id="Collection-subject-<?php echo $subject['id']; ?>" data-subject="<?php echo htmlspecialchars(json_encode($subject)); ?>">
													<td><input type="checkbox" name="id[]" value="<?php echo $subject['id']; ?>"></td>
													<td>
														<div class="Collection-subject-category"><?php echo $dictCategory[$subject['category']]; ?></div>
														<div class="Collection-subject-image"><img src="<?php echo $subject['image'] ? $subject['image'] : Typecho_common::url('Collection/template/default_cover.jpg', $options->pluginUrl); ?>" width="100px"></div>
														<div class="Collection-subject-type">
															<?php if('subject' == $subject['category'])
																echo $dictClass[$subject['class']].'/'.($dictType[$subject['type']] ? $dictType[$subject['type']] : '未知');
															?>
														</div>
													</td>
													<td class="Collection-subject-meta">
														<div class="Collection-subject-name">
															<i class="Collection-subject-class-ico Collection-subject-class-<?php echo $subject['class']; ?>"></i><small>(#<?php echo $subject['id']; ?>)</small>
															<?php
																if(array_key_exists($subject['source'], $dictSource))
																	echo '<a href="'.$dictSource[$subject['source']]['url'].$subject['subject_id'].'" target="_blank">'.$subject['name'].'</a>';
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
																	$options->index('/action/collection?do=plusEp&plus=ep');
																	echo '" class="Collection-subject-progress-plus" id="Collection-subject-'.$subject['id'].'-progress-ep-plus">ep.'.($subject['ep_status']+1).'已'.$arrayClassStatus['collect'][$subject['class']].'</a></small></div>';
																}
															}
															echo '</div>';
															echo '<div id="Collection-subject-'.$subject['id'].'-sp">';
															if(!is_null($subject['sp_count']) && !is_null($subject['sp_status']))
															{
																echo '<label for="Collection-subject-'.$subject['id'].'-progress-sp">'._t('副进度').'</label>';
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
														<p class="Collection-subject-note"><i>备注：</i><?php echo $subject['note'] ? $subject['note'] : '无'; ?></p>
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
										+ '<p><label for="'+id+'-category"><?php _e('大类'); ?></label><select id="'+id+'-category" name="category" class="w-100">'
											+ '<option value="series">系列</option>'
											+ '<option value="subject">记录</option>'
											+ '<option value="volume">分卷</option>'
											+ '<option value="episode">章节</option>'
										+ '</select></p>'
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
										+ '<option value="">未知</option>';
								$.each(dictType[subject.class], function(key, value){
									string += '<option value="'+key+'">'+value+'</option>';
								});
								string += '</select></p>'
									+ '<p><label for="'+id+'-source"><?php _e('信息来源'); ?></label><select id="'+id+'-source" name="source" class="w-100">'
										+ '<option value="Collection">收藏</option>'
										+ '<option value="Bangumi">Bangumi</option>'
										+ '<option value="Douban">豆瓣</option>'
										+ '<option value="Steam">Steam</option>'
										+ '<option value="Wandoujia">豌豆荚</option>'
										+ '<option value="TapTap">TapTap</option>'
										+ '<option value="BiliBili">BiliBili</option>'
									+ '</select></p>'
									+ '<p><label for="'+id+'-subject_id">来源ID</label><input class="text-s" type="text" id="'+id+'-subject_id" name="subject_id"></p>'
									+ '</form></td>'
									+ '<td><form method="post" action="'+t.attr('rel')+'" class="Collection-subject-edit-info">'
									+ '<p><label for="'+id+'-name">原名</label><input class="text-s" type="text" id="'+id+'-name" name="name"></p>'
									+ '<p><label for="'+id+'-name_cn">译名</label><input class="text-s" type="text" id="'+id+'-name_cn" name="name_cn"></p>'
									+ '<p><label for="'+id+'-parent">关联记录</label><input class="text-s" type="text" id="'+id+'-parent" name="parent"></p>'
									+ '<p><label for="'+id+'-ep_status">主进度</label><input class="text-s w-100" id="'+id+'-ep_status" name="ep_status" type="number" min="0" max="9999"></p>'
									+ '<p><label for="'+id+'-ep_count">主进度总数</label><input class="text-s w-100" type="number" name="ep_count" id="'+id+'-ep_count" min="0" max="9999"></p>'
									+ '<p><label for="'+id+'-sp_status">副进度</label><input class="text-s w-100" id="'+id+'-sp_status" name="sp_status" type="number" min="0" max="999"></p>'
									+ '<p><label for="'+id+'-sp_count">副进度总数</label><input class="text-s w-100" type="number" name="sp_count" id="'+id+'-sp_count" min="0" max="999"></p>'
									+ '</form></td>'
									+ '<td id="review-'+id+'"><form method="post" action="'+t.attr('rel')+'" class="Collection-subject-edit-content">'
									+ '<p><label for="'+id+'-grade">显示分级</label>'
									+ '<input type="radio" id="Collection-subject-'+id+'-edit-grade-0" name="grade" value="0" class="Collection-subject-edit-grade"><label for="Collection-subject-'+id+'-edit-grade-0">私密</label>'
									+ '<input type="radio" id="Collection-subject-'+id+'-edit-grade-1" name="grade" value="1" class="Collection-subject-edit-grade"><label for="Collection-subject-'+id+'-edit-grade-1">公开</label></p>'
									+ '<p><label for="'+id+'-note">备注</label>'
									+ '<textarea name="note" id="'+id+'-note" rows="2" class="w-100 mono"></textarea></p>'
									//+ '<p><label for="'+id+'-rate">评价：'
										//+'<span class="Collection-subject-rate-star Collection-subject-rate-star-rating"></span>'.repeat(subject.rate)
										//+'<span class="Collection-subject-rate-star Collection-subject-rate-star-blank"></span>'.repeat(10-subject.rate)
									//+'</label><input class="text-s w-100" type="range" name="rate" id="'+id+'-rate" min="0" max="10"></p>'
									+ '<p><label for="'+id+'-rate">评价：</label><input class="text-s w-100" type="number" name="rate" id="'+id+'-rate" min="0" max="10"></p>'
									+ '<p><label for="'+id+'-tags">标签</label>'
									+ '<input class="text-s w-100" type="text" name="tags" id="'+id+'-tags"></p>'
									+ '<p><label for="'+id+'-comment">吐槽</label>'
									+ '<textarea name="comment" id="'+id+'-comment" rows="6" class="w-100 mono"></textarea></p>'
									+ '<p><button type="submit" class="btn btn-s primary">提交</button>'
									+ '<button type="button" class="btn btn-s cancel">取消</button></p>'
									+ '</form></td>'
									+ '</tr>';
								var edit = $(string).data('id', id).data('subject', subject).insertAfter(tr);
								
								$('select[name=category]', edit).val(subject.category);
								$('textarea[name=image]', edit).val(subject.image);
								$('select[name=class]', edit).val(subject.class);
								$('select[name=type]', edit).val(subject.type);
								$('select[name=source]', edit).val(subject.source);
								$('input[name=subject_id]', edit).val(subject.subject_id);
								$('input[name=name]', edit).val(subject.name);
								$('input[name=name_cn]', edit).val(subject.name_cn);
								$('input[name=parent]', edit).val(subject.parent);
								$('input[name=ep_status]', edit).val(subject.ep_status);
								$('input[name=ep_count]', edit).val(subject.ep_count);
								$('input[name=sp_status]', edit).val(subject.sp_status);
								$('input[name=sp_count]', edit).val(subject.sp_count);
								$('input[name=grade][value="'+subject.grade+'"]', edit).attr("checked", true);
								$('textarea[name=note]', edit).val(subject.note);
								$('input[name=rate]', edit).val(subject.rate);
								$('input[name=tags]', edit).val(subject.tags);
								$('textarea[name=comment]', edit).val(subject.comment).focus();

								$('select[name=class]', edit).change(function(){
									var tempHTML = '';
									tempHTML = '<option value="">未知</option>';
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
											$('.Collection-subject-category', oldTr).html(dictCategory[subject.category]);
											$('.Collection-subject-image', oldTr).html('<img src="'+(subject.image ? subject.image : '<?php $options->pluginUrl('Collection/template/default_cover.jpg'); ?>')+'" width="100px">');
											if('subject' == subject.category)
												$('.Collection-subject-type', oldTr).html(dictClass[subject.class]+'/'+(subject.type ? dictType[subject.class][subject.type] : '未知'));
											else
												$('.Collection-subject-type', oldTr).html('');
											var tempHTML = '<i class="Collection-subject-class-ico Collection-subject-class-'+subject.class+'"></i><small>(#'+subject.id+')</small>';
											if(dictSource.hasOwnProperty(subject.source))
												tempHTML += '<a href="' + dictSource[subject.source]['url'] + subject.subject_id + '" target="_blank">' + subject.name + '</a>';
											else
												tempHTML += subject.name;
											$('.Collection-subject-name', oldTr).html(tempHTML);
											$('.Collection-subject-name_cn', oldTr).html(subject.name_cn);
											if(subject.ep_count == '' && subject.ep_status == '')
												$('#Collection-subject-'+subject.id+'-ep', oldTr).html('');
											else
											{
												stringProgress += '<label for="Collection-subject-'+subject.id+'-progress-ep">主进度</label>'
													+ '<div id="Collection-subject-'+subject.id+'-progress-ep" class="Collection-subject-progress"><div class="Collection-subject-progress-inner" style="color:white; width:'+(subject.ep_count != 0 ? subject.ep_status/subject.ep_count*100 : 50)+'%"><small>'+subject.ep_status+' / '+(subject.ep_count != 0 ? subject.ep_count : '??')+'</small></div></div>';
												if(subject.ep_count == '0' || Number(subject.ep_count) > Number(subject.ep_status))
													stringProgress += '<div class="hidden-by-mouse"><small><a href="#'+subject.id+'" rel="<?php $options->index('/action/collection?do=plusEp&plus=ep'); ?>" class="Collection-subject-progress-plus" id="Collection-subject-'+subject.id+'-progress-ep-plus">ep.'+String(Number(subject.ep_status)+1)+'已'+arrayStatus[String(subject.class-1)]+'</a></small></div>';
												$('#Collection-subject-'+subject.id+'-ep', oldTr).html(stringProgress);
											}
											if(subject.sp_count == '' && subject.sp_status == '')
												$('#Collection-subject-'+subject.id+'-sp', oldTr).html('');
											else
											{
												stringProgress = '<label for="Collection-subject-'+subject.id+'-progress-sp">副进度</label>'
													+ '<div id="Collection-subject-'+subject.id+'-progress-sp" class="Collection-subject-progress"><div class="Collection-subject-progress-inner" style="color:white; width:'+(subject.sp_count != 0 ? subject.sp_status/subject.sp_count*100 : 50)+'%"><small>'+subject.sp_status+' / '+(subject.sp_count != 0 ? subject.sp_count : '??')+'</small></div></div>';
												if(subject.sp_count == '0' || Number(subject.sp_count) > Number(subject.sp_status))
													stringProgress += '<div class="hidden-by-mouse"><small><a href="#'+subject.id+'" rel="<?php $options->index('/action/collection?do=plusEp&plus=sp'); ?>" class="Collection-subject-progress-plus" id="Collection-subject-'+subject.id+'-progress-sp-plus">sp.'+String(Number(subject.sp_status)+1)+'已'+arrayStatus[String(subject.class-1)]+'</a></small></div>';
												$('#Collection-subject-'+subject.id+'-sp', oldTr).html(stringProgress);
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
											<?php /* <option value="Douban"<?php if($request->get('source') == 'Douban'): ?> selected="ture"<?php endif;?>>豆瓣</option> */ ?>
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
														<td class="Collection-box-title"><div>
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
									// 'Douban':{1:'图书',3:'音乐',6:'电影'}
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
									$('#typecho-option-item-type-2').html(tempHTML);
									$('#typecho-option-item-type-2 input').first().attr('checked','true');
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

