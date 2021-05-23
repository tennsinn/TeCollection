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