<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 设置验证及内容获取
 *
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 */
class Collection_Config
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
	 * 获取分级名称
	 *
	 * @access public
	 * @return array
	 */
	public static function getGrade()
	{
		$default = array(_t('公开'), _t('私密1'), _t('私密2'), _t('私密3'), _t('私密4'), _t('私密5'), _t('私密6'), _t('私密7'), _t('私密8'), _t('私密9'));
		$grades = explode(',', Helper::options()->plugin('Collection')->grade);
		$dictGrade = array_replace($default, $grades);
		return $dictGrade;
	}
}
?>
