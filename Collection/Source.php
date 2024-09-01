<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 信息源处理
 *
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 */
class Collection_Source
{
	/**
	 * 获取源链接
	 *
	 * @access public
	 * @param string $value 分级设置名称组
	 * @return bool|string
	 */
	public static function getLink($source, $source_id, $class, $type = null)
	{
		if(!class_exists('Collection_Source_'.$source) || empty($source_id))
			return false;
		$link = call_user_func(array('Collection_Source_'.$source, 'getLink'), $source_id, $class, $type);
		return $link;
	}
}
?>
