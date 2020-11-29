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
	 * @return string
	 */
	public static function checkDatabase()
	{
		$db = Typecho_Db::get();
		$info = Typecho_Plugin::parseInfo(__DIR__.'/Plugin.php');
		$current_version = $info['version'];
		$installed_version = self::getInstalledVersion();
		if($installed_version)
		{
			$message = _t('检测到插件版本信息');
			$result = self::upgrade($current_version, $installed_version);
			if($result)
				$message .= ' / '.implode($result, ' / ');
			return $message;
		}
		else
		{
			self::install($current_version);
			return _t('已创建数据表');
		}
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
		$adapter = self::getAdapter();
		$scripts = file_get_contents(__DIR__.'/Database/Install/'.$adapter.'.sql');
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
	 * @return array
	 */
	public static function upgrade($current_version, $installed_version)
	{
		$db = Typecho_Db::get();
		$packages = get_class_methods('Collection_Database_Upgrade');
		$filterPackage = function($package) use ($installed_version) {
			$version = substr(str_replace('_', '.', $package), 1);
			return version_compare($version, $installed_version, '>');
		};
		$sortPackage = function($a, $b) {
			$version_a = substr(str_replace('_', '.', $a), 1);
			$version_b = substr(str_replace('_', '.', $b), 1);
			return version_compare($version_a, $version_b, '>') ? 1 : -1;
		};
		$packages = array_filter($packages, $filterPackage);
		usort($packages, $sortPackage);
		$adapter = self::getAdapter();
		$prefix  = $db->getPrefix();
		$message = array();
		foreach($packages as $package)
		{
			$version = substr(str_replace('_', '.', $package), 1);
			$result = call_user_func(array('Collection_Database_Upgrade', $package), $db, $adapter, $prefix);
			if(!empty($result))
				$message[] = $result;
			$db->query($db->update('table.options')->where('name = ?', 'Collection:version')->rows(array('value' => $version)));
		}
		$db->query($db->update('table.options')->where('name = ?', 'Collection:version')->rows(array('value' => $current_version)));
		if(empty($packages))
			$message[] = _t('未检测到升级包');
		elseif(empty($message))
			$message[] = _t('已完成数据表升级');
		return $message;
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

	/**
	 * 获取已记录版本号
	 *
	 * @access public
	 * @return string|bool
	 */
	public static function getInstalledVersion()
	{
		$db = Typecho_Db::get();
		$row = $db->fetchRow($db->select()->from('table.options')->where('name = ?', 'Collection:version'));
		if(!empty($row))
			return $row['value'];
		else
			return false;
	}

	/**
	 * 获取数据库类型
	 *
	 * @access private
	 * @return string
	 */
	private static function getAdapter()
	{
		$db = Typecho_Db::get();
		$adapter = explode('_', $db->getAdapterName());
		$adapter = array_pop($adapter);
		$adapter = $adapter == 'Mysqli' ? 'Mysql' : $adapter;
		if(in_array($adapter, array('Mysql', 'Pgsql', 'SQLite')))
			return $adapter;
		else
			throw new Typecho_Db_Exception("Adapter {$adapter} is not available");
	}
}
?>
