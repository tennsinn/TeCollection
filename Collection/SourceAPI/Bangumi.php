<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Bangumi API 处理
 *
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 */
class Collection_SourceAPI_Bangumi
{
	/**
	 * 获取单一条目信息
	 *
	 * @access public
	 * @param int $source_id Bangumi条目ID
	 * @return bool|array
	 */
	public static function getSubject($source_id)
	{
		$response = @file_get_contents('http://api.bgm.tv/subject/'.$source_id);
		$response = json_decode($response, true);
		if(!$response)
			return false;
		$row = self::formatInfo($response);
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
		$response = @file_get_contents('http://api.bgm.tv/search/subject/'.$keywords.'?responseGroup=large&max_results='.$pageSize.'&start='.($page-1)*$pageSize.'&type='.$class);
		$response = json_decode($response, true);
		if(!$response || (isset($response['results']) && !$response['results']))
			return array('result' => false, 'message' => '搜索到0个结果');
		else if(!isset($response['results']) && isset($response['error']))
			return array('result' => false, 'message' => '关键字：'.$keywords.' 搜索出现错误 '.$response['code'].':'.$response['error']);
		foreach($response['list'] as $key => $value)
		{
			$info = '';
			if(isset($value['eps']))
				$info .= '<div>总集数：'.$value['eps'].'</div>';
			if(isset($value['summary']))
				$info .= '<div>简介：'.$value['summary'].'</div>';
			$list[$value['id']] = self::formatInfo($value);
			$list[$value['id']]['info'] = $info;
		}
		return array('result' => true, 'count' => $response['results'], 'list' => $list);
	}

	/**
	 * 根据已解析数据获取所需内容
	 *
	 * @access private
	 * @param array $value 解析后的数组
	 * @return array
	 */
	private static function formatInfo($value)
	{
		$info = array(
			'class' => isset($value['type']) ? $value['type'] : 1,
			'name' => isset($value['name']) ? $value['name'] : 'Unknown Subject',
			'name_cn' => isset($value['name_cn']) ? $value['name_cn'] : NULL,
			'published' => (isset($value['air_date']) && $value['air_date'] && ('0000-00-00' != $value['air_date'])) ? $value['air_date'] : NULL,
			'image' => isset($value['images']['common']) ? $value['images']['common'] : NULL,
			'ep_count' => isset($value['eps']) ? $value['eps'] : NULL,
			'ep_status' => isset($value['eps']) ? 0 : NULL,
		);
		return $info;
	}
}

