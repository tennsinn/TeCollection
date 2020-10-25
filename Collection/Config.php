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
		$default = array('公开','私密1','私密2','私密3','私密4','私密5','私密6','私密7','私密8','私密9');
		$grades = explode(',', Helper::options()->plugin('Collection')->grade);
		$dictGrade = array_replace($default, $grades);
		return $dictGrade;
	}
}
