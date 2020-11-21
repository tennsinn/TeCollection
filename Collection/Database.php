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
	public static function checkDatabase()
	{
		$db = Typecho_Db::get();
		$info = Typecho_Plugin::parseInfo(__DIR__.'/Plugin.php');
		$current_version = $info['version'];
		$installed_version = $db->fetchRow($db->select()->from('table.options')->where('name = ?', 'Collection:version'));
		if($installed_version)
			self::upgrade($current_version, $installed_version);
		else
			self::install($current_version);
	}

	/**
	 * 创建数据表
	 *
	 * @access public
	 * @param string $current_version 当前版本号
	 * @return void
	 */
	public static function install($current_version)
	{
		$db = Typecho_Db::get();
		$charset = Helper::options()->charset == 'UTF-8' ? 'utf8' : 'gbk';
		$type = explode('_', $db->getAdapterName());
		$type = array_pop($type);
		$type = $type == 'Mysqli' ? 'Mysql' : $type;
		$scripts = file_get_contents(__DIR__.'/Database/Install/'.$type.'.sql');
		$scripts = str_replace('typecho_', $db->getPrefix(), $scripts);
		$scripts = str_replace('%charset%', $charset, $scripts);
		$db->query($scripts);
		$db->query($db->insert('table.options')->rows(array('name' => 'Collection:version', 'user' => 0, 'value' => $current_version)));
	}

	/**
	 * 升级数据表
	 *
	 * @access public
	 * @param string $current_version 当前版本号
	 * @param string $installed_version 已安装版本号
	 * @return void
	 */
	public static function upgrade($current_version, $installed_version)
	{
		$db = Typecho_Db::get();
		$db->query($db->update('table.options')->where('name = ?', 'Collection:version')->rows(array('value' => $current_version)));
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
