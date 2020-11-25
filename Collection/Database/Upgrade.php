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
	 * 升级至1.14.3
	 *
	 * @access public
	 * @param Typecho_Db $db 数据库对象
	 * @param Typecho_Widget $options 全局信息组件
	 * @return void
	 */
	public static function v1_14_3($db, $options=NULL)
	{
		return _t('暂无数据表升级至此版本方案');
	}
}