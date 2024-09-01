<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Bangumi 源处理
 *
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 */
class Collection_Source_Bangumi
{
	const SEARCH = true;
	const SERVER = 'https://api.bgm.tv';

	/**
	 * 获取单一条目信息
	 *
	 * @access public
	 * @param int $source_id Bangumi条目ID
	 * @return bool|array
	 */
	public static function getSubject($source_id)
	{
		$response = @file_get_contents(self::SERVER.'/subject/'.$source_id.'?responseGroup=large');
		$response = json_decode($response, true);
		if(!$response)
			return false;
		$row = self::parseBasicInfo($response);
		if($row['ep_count'])
			$row['ep_status'] = 0;
		if(isset($response['staff']))
		{
			$staff = self::parseStaffInfo($response['staff']);
			$row = array_merge($row, $staff);
		}
		return $row;
	}

	/**
	 * 根据关键字和分类搜索
	 *
	 * @access public
	 * @param string $keywords 搜索关键字
	 * @param int $class Bangumi分类
	 * @param int $pageSize 分页条目数
	 * @param int $page 分页号
	 * @return array
	 */
	public static function searchSubject($keywords, $class=0, $pageSize=20, $page=1)
	{
		$response = @file_get_contents(self::SERVER.'/search/subject/'.$keywords.'?responseGroup=large&max_results='.$pageSize.'&start='.($page-1)*$pageSize.'&type='.$class);
		if(empty($response))
			return array('result' => false, 'message' => '搜索超时');
		$response = json_decode($response, true);
		if(!isset($response['results']) && !isset($response['error']))
			return array('result' => false, 'message' => '搜索被拒绝');
		else if(!isset($response['results']) && isset($response['error']))
			if(404 == $response['code'])
				return array('result' => false, 'message' => '关键字：'.$keywords.' 搜索到0个结果 '.$response['code'].':'.$response['error']);
			else
				return array('result' => false, 'message' => '关键字：'.$keywords.' 搜索出现错误 '.$response['code'].':'.$response['error']);

		foreach($response['list'] as $key => $value)
		{
			$info = '';
			if(isset($value['eps']))
				$info .= '<div>总集数：'.$value['eps'].'</div>';
			if(isset($value['summary']))
				$info .= '<div>简介：'.$value['summary'].'</div>';
			$list[$value['id']] = self::parseBasicInfo($value);
			$list[$value['id']]['info'] = $info;
		}
		return array('result' => true, 'count' => $response['results'], 'list' => $list);
	}

	/**
	 * 获取条目基本信息
	 *
	 * @access private
	 * @param array $value 解析后的数组
	 * @return array
	 */
	private static function parseBasicInfo($value)
	{
		$info = array();
		$info['class'] = isset($value['type']) ? $value['type'] : 1;
		$info['name'] = isset($value['name']) ? $value['name'] : 'Unknown Subject';
		$info['name_cn'] = isset($value['name_cn']) ? $value['name_cn'] : NULL;
		$info['published'] = (isset($value['air_date']) && !empty($value['air_date']) && ('0000-00-00' != $value['air_date'])) ? strtotime($value['air_date']) : NULL;
		$info['image'] = isset($value['images']['common']) ? $value['images']['common'] : NULL;
		$info['ep_count'] = isset($value['eps_count']) ? $value['eps_count'] : NULL;
		return $info;
	}

	/**
	 * 获取条目Staff信息
	 *
	 * @access private
	 * @param array $value 解析后的数组
	 * @return array
	 */
	private static function parseStaffInfo($value)
	{
		$info = array('author' => NULL, 'publisher' => NULL);
		foreach($value as $val)
		{
			foreach($val['jobs'] as $job)
			{
				switch($job)
				{
					case '作者':
					case '开发':
					case '动画制作':
					case '作画':
						$info['author'] = $info['author'] ? $info['author'].' / '.$val['name'] : $val['name'];
						break;
					case '出版社':
					case '连载杂志':
						$info['publisher'] = $info['publisher'] ? $info['publisher'].' / '.$val['name'] : $val['name'];
						break;
				}
			}
		}
		return $info;
	}

	/**
	 * 获取链接
	 *
	 * @access public
	 * @param array $source_id 条目ID
	 * @return string
	 */
	public static function getLink($source_id, $class, $type)
	{
		$link = 'http://bgm.tv/subject/'.$source_id;
		return $link;
	}
}
?>
