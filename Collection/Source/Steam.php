<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Steam 源处理
 *
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 */
class Collection_Source_Steam
{
	const SEARCH = false;

	/**
	 * 获取链接
	 *
	 * @access public
	 * @param array $source_id 条目ID
	 * @return string
	 */
	public static function getLink($source_id, $class)
	{
		$link = 'http://store.steampowered.com/app/'.$source_id;
		return $link;
	}
}
?>
