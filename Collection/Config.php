<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Collection_Config
{
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

	public static function getGrade()
	{
		$default = array(_t('公开'), _t('私密1'), _t('私密2'), _t('私密3'), _t('私密4'), _t('私密5'), _t('私密6'), _t('私密7'), _t('私密8'), _t('私密9'));
		$grades = explode(',', Helper::options()->plugin('Collection')->grade);
		$dictGrade = array_replace($default, $grades);
		return $dictGrade;
	}
}
