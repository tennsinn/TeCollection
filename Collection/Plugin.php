<?php
/**
 * 一个本地收藏整理展示插件
 * 
 * @package Collection
 * @author 息E-敛
 * @version 1.4.1
 * @link http://tennsinn.com
 */
class Collection_Plugin implements Typecho_Plugin_Interface
{
	public static function activate()
	{
		Helper::addAction('collection', 'Collection_Action');
		Helper::addPanel(3, "Collection/Panel.php", _t("Collection"), _t("Collection"), 'administrator', false, 'extending.php?panel=Collection%2FPanel.php&do=search');
		$db = Typecho_Db::get();
		$charset = Helper::options()->charset == 'UTF-8' ? 'utf8' : 'gbk';
		$query = 'CREATE TABLE IF NOT EXISTS '. $db->getPrefix() . 'collection' ." (
			`id` int unsigned NOT NULL auto_increment PRIMARY KEY,
			`class` tinyint(1) unsigned NOT NULL,
			`type` varchar(10) NOT NULL default 'Mix',
			`name` varchar(50) NOT NULL,
			`name_cn` varchar(50) default NULL,
			`image` varchar(200) default NULL,
			`ep_count` smallint(4) unsigned default NULL,
			`sp_count` smallint(3) unsigned default NULL,
			`source` varchar(10) NOT NULL default 'Collection',
			`subject_id` varchar(10) default NULL,
			`parent` int unsigned NOT NULL default 0,
			`grade` tinyint(1) unsigned NOT NULL default 1,
			`status` char(7) NOT NULL default 'wish',
			`time_start` int(10) unsigned default NULL,
			`time_finish` int(10) unsigned default NULL,
			`time_touch` int(10) unsigned NOT NULL,
			`ep_status` smallint(4) unsigned default NULL,
			`sp_status` smallint(3) unsigned default NULL,
			`rate` tinyint(2) unsigned NOT NULL default 0,
			`tags` varchar(100) default NULL,
			`comment` text default NULL
			) ENGINE=MyISAM DEFAULT CHARSET=". $charset;
		$db->query($query);
	}
	
	public static function deactivate()
	{
		Helper::removeAction('collection');
		Helper::removePanel(3, 'Collection/Panel.php');
		if (Helper::options()->plugin('Collection')->drop)
		{
			$db = Typecho_Db::get();
			$db->query('DROP TABLE IF EXISTS '.$db->getPrefix().'collection');
			return('插件已经禁用, 插件数据已经删除');
		}
		else
			return('插件已经禁用, 插件数据保留');
	}
	
	public static function config(Typecho_Widget_Helper_Form $form)
	{
		$drop = new Typecho_Widget_Helper_Form_Element_Radio('drop', array(0 => _t('不刪除'), 1 => _t('刪除')), 0, _t('禁用时是否删除数据'), _t('选择在禁用插件的同时是否删除数据库中的插件数据内容'));
		$form->addInput($drop);
	}
	
	public static function personalConfig(Typecho_Widget_Helper_Form $form){}

	public static function render()
	{
		$export = Typecho_Plugin::export();
		if(array_key_exists('Collection', $export['activated']))
			include 'template/template.php';
		else
			echo '<div>Collection 插件未开启</div>';
	}
}
?>