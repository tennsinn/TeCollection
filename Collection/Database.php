<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 数据库处理
 *
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 */
class Collection_Database
{
	/**
	 * 版本检查
	 *
	 * @access public
	 * @return void
	 */
	public static function checkVersion()
	{
		$db = Typecho_Db::get();
		$info = Typecho_Plugin::parseInfo(__DIR__.'/Plugin.php');
		if($db->fetchRow($db->select()->from('table.options')->where('name = ?', 'Collection:version')))
			$db->query($db->update('table.options')->where('name = ?', 'Collection:version')->rows(array('value' => $info['version'])));
		else
			$db->query($db->insert('table.options')->rows(array('name' => 'Collection:version', 'user' => 0, 'value' => $info['version'])));
	}

	/**
	 * 创建数据表
	 *
	 * @access public
	 * @return void
	 */
	public static function createTable()
	{
		$db = Typecho_Db::get();
		$charset = Helper::options()->charset == 'UTF-8' ? 'utf8' : 'gbk';
		$type = explode('_', $db->getAdapterName());
		$type = array_pop($type);
		$type = $type == 'Mysqli' ? 'Mysql' : $type;
		$scripts = file_get_contents(__DIR__.'/Database/'.$type.'_create.sql');
		$scripts = str_replace('typecho_', $db->getPrefix(), $scripts);
		$scripts = str_replace('%charset%', $charset, $scripts);
		$db->query($scripts);
	}

	/**
	 * 删除插件数据
	 *
	 * @access public
	 * @return void
	 */
	public static function dropData()
	{
		$db = Typecho_Db::get();
		$db->query('DROP TABLE IF EXISTS '.$db->getPrefix().'collection');
		$db->query($db->delete('table.options')->where('name = ?', 'Collection:version'));
	}
}
?>
