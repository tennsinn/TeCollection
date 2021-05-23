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
<?php include 'Panel\Panel-common.js.php'; ?>
<div class="main">
	<div class="body container">
		<?php include 'page-title.php'; ?>
		<div class="colgroup typecho-page-main" role="main">
			<div class="col-mb-12">
				<?php if($do == 'manage'): ?>
					<?php include 'Panel\Panel-manage.php'; ?>
					<?php include 'Panel\Panel-manage.js.php'; ?>
				<?php else: ?>
					<a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php'); ?>">返回</a>
					<ul class="typecho-option-tabs right">
						<li<?php if($do == 'input'): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&do=input'); ?>">输入</a></li>
						<li<?php if($do == 'search'): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=Collection%2FPanel.php&do=search'); ?>">搜索</a></li>
					</ul>
					<?php if($do == 'search'): ?>
						<?php include 'Panel\Panel-search.php'; ?>
						<?php include 'Panel\Panel-search.js.php'; ?>
					<?php else: ?>
						<div class="col-mb-12 typecho-list" role="main">
							<?php Typecho_Widget::widget('Collection_Action')->formInput()->render(); ?>
						</div>
						<?php include 'Panel\Panel-input.js.php'; ?>
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
