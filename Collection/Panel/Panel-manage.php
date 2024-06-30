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
	<form method="post" class="operate-form">
		<div class="typecho-list-operate clearfix">
			<div class="operate">
				<label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
				<div class="btn-group btn-drop">
					<button class="btn dropdown-toggle btn-s" type="button"><?php _e('<i class="sr-only">操作</i>选中项'); ?> <i class="i-caret-down"></i></button>
					<ul class="dropdown-menu">
						<li><a lang="<?php _e('你确认要删除记录中的这些记录吗?'); ?>" href="<?php $security->index('/action/collection?do=editStatus&status=delete'); ?>"><?php _e('删除记录'); ?></a></li>
						<li class="multiline">
							<label><?php _e('修改状态为'); ?></label>
							<?php foreach($config->arrayStatus as $value): ?>
							<a lang="<?php _e('你确认要修改这些记录到'.$config->dictStatusAll[$value][$class].'吗?'); ?>" href="<?php $security->index('/action/collection?do=editStatus&status='.$value); ?>"><?php _e($config->dictStatusAll[$value][$class]); ?></a>
							<?php endforeach; ?>
						</li>
						<li class="multiline">
							<button type="button" class="btn edit btn-s" rel="<?php $security->index('/action/collection?do=editColumn'); ?>"><?php _e('修改字段'); ?></button>
							<select name="column">
								<?php
								$columns = $config->batchColumn;
								foreach($columns as $column)
									echo '<option value="'.$column.'">'.$config->dictColumn[$column].'</option>';
								?>
							</select>
							<select name="value">
							<?php foreach($config->dictCategory as $key => $val)
								echo '<option value="'.$key.'">'.$val.'</option>';
							?>
							</select>
						</li>
					</ul>
				</div>
			</div>
			<div class="search" role="search">
				<input type="hidden" value="Collection/Panel.php" name="panel">
				<input type="hidden" value="<?php echo $category; ?>" name="category">
				<input type="hidden" value="<?php echo $class; ?>" name="class">
				<input type="hidden" value="<?php echo $status; ?>" name="status">
				<?php if ('' != $request->keywords || '' != $request->field): ?>
					<a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&category='.$category.'&class='.$class.'&status='.$status); ?>"><?php _e('&laquo; 取消筛选'); ?></a>
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
		</div>
		<div class="typecho-table-wrap">
			<table class="typecho-list-table">
				<colgroup>
					<col width="3%">
					<col width="12%">
					<col width="25%">
					<col width="50%">
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
									<div class="Collection-subject-status"><?php echo $config->dictStatusAll[$subject['status']][$subject['class']?$subject['class']:0].' / '.$config->dictGrade[$subject['grade']]; ?></div>
									<div class="Collection-subject-image"><img src="<?php echo $subject['image'] ? $subject['image'] : Typecho_common::url('Collection/Cover/'.$subject['id'].'.jpg', $options->pluginUrl); ?>" width="100%"></div>
									<div class="Collection-subject-type">
										<?php
										echo $config->dictCategory[$subject['category']];
										if('series' != $subject['category'] && !is_null($subject['class']))
										{
											echo ' / ';
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
										}
										?>
									</div>
								</td>
								<td class="Collection-subject-meta">
									<div class="Collection-subject-name">
										<?php if($subject['media_link']): ?>
										<a href="<?=$subject['media_link']?>" target="_blank"><i class="Collection-subject-class-ico Collection-subject-class-<?=$subject['class']?>"></i></a>
										<?php else: ?>
										<i class="Collection-subject-class-ico Collection-subject-class-<?=$subject['class']?>"></i>
										<?php endif; ?>
										<small>(#<?=$subject['id']?>)</small>
										<?php $link = Collection_Source::getLink($subject['source'], $subject['source_id'], $subject['class']); if($link): ?>
										<a href="<?=$link?>" target="_blank"><?=$subject['name']?></a>
										<?php else: ?>
										<?=$subject['name']?>
										<?php endif; ?>
									</div>
									<div class="Collection-subject-name_cn">
										<?php echo $subject['name_cn'] ? $subject['name_cn'] : ''; ?>
									</div>
									<?php
										echo '<div id="Collection-subject-'.$subject['id'].'-ep">';
										if(!is_null($subject['ep_count']) && !is_null($subject['ep_status']))
										{
											$ep_current = $subject['ep_status'] + (NULL == $subject['ep_start'] ? 1 : $subject['ep_start']) - 1;
											$ep_end = (NULL == $subject['ep_start'] ? 1 : $subject['ep_start']) + $subject['ep_count'] - 1;
											echo '<label for="Collection-subject-'.$subject['id'].'-progress-ep">'._t('主进度').'</label>';
											echo '<div id="Collection-subject-'.$subject['id'].'-progress-ep" class="Collection-subject-progress"><div class="Collection-subject-progress-inner" style="color:white; width:'.($subject['ep_count'] ? $subject['ep_status']/$subject['ep_count']*100 : 50).'%"><small>'.($ep_current < 0 ? '??' : $ep_current).' / '.($ep_end > 0 ? $ep_end : '??').'</small></div></div>';
											if(!$subject['ep_count'] || $subject['ep_count']>$subject['ep_status'])
											{
												echo '<div class="hidden-by-mouse"><small><a href="#'.$subject['id'].'" rel="';
												$security->index('/action/collection?do=plusEp');
												echo '" class="Collection-subject-progress-plus" id="Collection-subject-'.$subject['id'].'-progress-ep-plus">ep.'.($ep_current+1).'已'.$config->dictStatusAll['collect'][$subject['class']].'</a></small></div>';
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
		<div class="typecho-list-operate clearfix">
			<?php if($response['result']): ?>
			<ul class="typecho-pager">
				<?php $response['nav']->render(_t('&laquo;'), _t('&raquo;')); ?>
			</ul>
			<?php endif; ?>
		</div>
	</form>
</div>