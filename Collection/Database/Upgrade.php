<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 插件升级
 *
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 */
class Collection_Database_Upgrade
{
	/**
	 * 升级至v1.14.5
	 *
	 * @access public
	 * @param Typecho_Db $db 数据库对象
	 * @param string $adapter 数据库类型
	 * @param string $prefix 数据表前缀
	 * @return void
	 */
	public static function v1_14_5($db, $adapter, $prefix)
	{
		switch($adapter)
		{
			case 'Mysql':
				$db->query('ALTER TABLE `'.$prefix.'collection` ADD `media_link` varchar(255) default NULL', Typecho_Db::WRITE);
				break;
			case 'Pgsql':
				$db->query('ALTER TABLE "'.$prefix.'collection" ADD COLUMN "media_link" VARCHAR(255) NULL DEFAULT NULL', Typecho_Db::WRITE);
				break;
			case 'SQLite':
				$db->query('ALTER TABLE "'.$prefix.'collection" ADD COLUMN "media_link" varchar(255) default NULL', Typecho_Db::WRITE);
				break;
		}
	}

	/**
	 * 升级至v1.14.4
	 *
	 * @access public
	 * @param Typecho_Db $db 数据库对象
	 * @param string $adapter 数据库类型
	 * @param string $prefix 数据表前缀
	 * @return void
	 */
	public static function v1_14_4($db, $adapter, $prefix)
	{
		switch($adapter)
		{
			case 'Mysql':
				$db->query('ALTER TABLE `'.$prefix.'collection` ADD `author` varchar(50) default NULL', Typecho_Db::WRITE);
				break;
			case 'Pgsql':
				$db->query('ALTER TABLE "'.$prefix.'collection" ADD COLUMN "author" VARCHAR(50) NULL DEFAULT NULL', Typecho_Db::WRITE);
				break;
			case 'SQLite':
				$db->query('ALTER TABLE "'.$prefix.'collection" ADD COLUMN "author" varchar(50) default NULL', Typecho_Db::WRITE);
				break;
		}
	}

	/**
	 * 升级至v1.14.0
	 *
	 * @access public
	 * @param Typecho_Db $db 数据库对象
	 * @param string $adapter 数据库类型
	 * @param string $prefix 数据表前缀
	 * @return void
	 */
	public static function v1_14_0($db, $adapter, $prefix)
	{
		$installed_version = Collection_Database::getInstalledVersion();
		if(version_compare($installed_version, '1.14.0'))
			throw new Typecho_Exception(_t('暂无数据表从v'.$installed_version.'升级至此版本方案，请先安装v1.14.0或以上版本'));
	}
}
