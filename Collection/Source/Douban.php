<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Douban 源处理
 *
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 */
class Collection_Source_Douban
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
		switch($class)
		{
			case '1':
				$link = 'https://book.douban.com/subject/'.$source_id;
				break;
			case '2':
			case '6':
				$link = 'https://movie.douban.com/subject/'.$source_id;
				break;
			case '3':
				$link = 'https://music.douban.com/subject/'.$source_id;
				break;
			case '4':
				$link = 'https://www.douban.com/game/'.$source_id;
				break;
			case '5':
				$link = 'https://www.douban.com/location/drama/'.$source_id;
				break;
			default:
				$link = false;
		}
		return $link;
	}
}
?>
