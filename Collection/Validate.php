<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 内容验证
 *
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 */
class Collection_Validate extends Typecho_Validate
{
	/**
	 * 分级名称设置检查
	 *
	 * @access public
	 * @param string $value 分级设置名称组
	 * @return bool
	 */
	public static function checkGrade($value)
	{
		$grades = explode(',', $value);
		if(count($grades) > 10)
			return false;
		foreach($grades as $grade)
			if(!trim($grade, ' '))
				return false;
		return true;
	}

	/**
	 * 检测是否在数组中
	 *
	 * @access public
	 * @param string $value
	 * @param array $the_array
	 * @return bool
	 */
	public static function inArray($value, array $the_array)
	{
		return in_array($value, $the_array);
	}

	/**
	 * 检测是否在范围内
	 *
	 * @access public
	 * @param int|float $value
	 * @param int|float $min 范围下限
	 * @param int|float $max 范围上限
	 * @return bool
	 */
	public static function inRange($value, $min=NULL, $max=NULL)
	{
		if((isset($min) && $value < $min) || (isset($max) && $value > $max))
			return false;
		else
			return true;
	}

	/**
	 * 检测是否位于数据库中
	 *
	 * @access public
	 * @param string $value
	 * @param string $column 检索列名
	 * @param string $exception 排除值
	 * @return bool
	 */
	public static function inDb($value, $column, $exception)
	{
		if($value == $exception)
			return true;
		$db = Typecho_Db::get();
		$response = $db->fetchRow($db->select()->from('table.collection')->where($column.' = ?', $value));
		if(empty($response))
			return false;
		else
			return true;
	}

	/**
	 * 检测是否为有效进度设置
	 *
	 * @access public
	 * @param int $value
	 * @param int $count 进度总数
	 * @return bool
	 */
	public static function isValidProgress($value, $count)
	{
		return !((!is_null($value) || !is_null($count)) && (!is_numeric($value) || !is_numeric($count) || $value<0 || $count<0 || ($count>0 && $value>$count)));
	}
}
