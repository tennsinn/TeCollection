<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * BiliBili 源处理
 *
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 */
class Collection_Source_BiliBili
{
	const SEARCH = false;

	/**
	 * 获取链接
	 *
	 * @access public
	 * @param array $source_id 条目ID
	 * @return string
	 */
	public static function getLink($source_id, $class, $type)
	{
		switch($class)
		{
			case '1':
				$link = 'https://manga.bilibili.com/detail/'.$source_id;
				break;
			case '2':
			case '6':
				$link = 'https://www.bilibili.com/bangumi/media/'.$source_id;
				break;
			default:
				$link = false;
		}
		return $link;
	}
}
?>
