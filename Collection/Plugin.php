<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 一个本地收藏整理展示插件
 * 
 * @package Collection
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 * @version 1.18.0
 * @link http://tennsinn.com
 */
class Collection_Plugin implements Typecho_Plugin_Interface
{
	/**
	 * 启用插件
	 *
	 * @access public
	 * @return string
	 */
	public static function activate()
	{
		$message = Collection_Database::checkDatabase();
		Helper::addAction('collection', 'Collection_Action');
		Helper::addPanel(3, "Collection/Panel.php", _t("Collection"), _t("Collection"), 'administrator', false, 'extending.php?panel=Collection%2FPanel.php&do=input');
		return _t('插件已启用').' / '.$message;
	}

	/**
	 * 禁用插件
	 *
	 * @access public
	 * @return string
	 */
	public static function deactivate()
	{
		Helper::removeAction('collection');
		Helper::removePanel(3, 'Collection/Panel.php');
		if (Helper::options()->plugin('Collection')->drop_data)
		{
			Collection_Database::dropData();
			return(_t('插件已禁用').' / '._t('插件数据已删除'));
		}
		else
			return(_t('插件已禁用').' / '._t('插件数据保留'));
	}

	/**
	 * 插件通用设置
	 *
	 * @access public
	 * @param Typecho_Widget_Helper_Form $form
	 * @return void
	 */
	public static function config(Typecho_Widget_Helper_Form $form)
	{
		$grade = new Typecho_Widget_Helper_Form_Element_Text('grade', NULL, _t('公开,私密1,私密2,私密3,私密4,私密5,私密6,私密7,私密8,私密9'), _t('分级标签'), _t('依次写入至多10个分级标签名称，以逗号分隔，不允许空标签。'));
		$grade->addRule(array('Collection_Extend_Validate', 'checkGrade'), _t('请以逗号分隔填入至多10个非空有效标签'));
		$form->addInput($grade);

		$arrayAnimation = array('fadeIn'=>'fadeIn', 'fadeInUp' => 'fadeInUp', 'fadeInDown' => 'fadeInDown', 'fadeInLeft' => 'fadeInLeft', 'fadeInRight' => 'fadeInRight', 'fadeInUpBig' => 'fadeInUpBig', 'fadeInDownBig' => 'fadeInDownBig', 'fadeInLeftBig' => 'fadeInLeftBig', 'fadeInRightBig' => 'fadeInRightBig', 'flipInX' => 'flipInX', 'bounceIn' => 'bounceIn', 'bounceInDown' => 'bounceInDown', 'bounceInUp' => 'bounceInUp', 'bounceInLeft' => 'bounceInLeft', 'bounceInRight' => 'bounceInRight', 'rotateIn' => 'rotateIn', 'rotateInDownLeft' => 'rotateInDownLeft', 'rotateInDownRight' => 'rotateInDownRight', 'rotateInUpLeft' => 'rotateInUpLeft', 'rotateInUpRight' => 'rotateInUpRight', 'rollIn' => 'rollIn');
		$animation = new Typecho_Widget_Helper_Form_Element_Radio('animation', $arrayAnimation, 'fadeInUp', _t('展示模板列表显示动画'), _t('选择在展示模板显示列表时的动画效果'));
		$form->addInput($animation->multiMode());

		$drop_data = new Typecho_Widget_Helper_Form_Element_Radio('drop_data', array(0 => _t('不刪除'), 1 => _t('刪除')), 0, _t('禁用时是否删除数据'), _t('选择在禁用插件的同时是否删除数据库中的插件数据内容'));
		$form->addInput($drop_data);
	}

	/**
	 * 插件个人设置
	 *
	 * @access public
	 * @param Typecho_Widget_Helper_Form $form
	 * @return void
	 */
	public static function personalConfig(Typecho_Widget_Helper_Form $form){}

	/**
	 * 模板化输出
	 *
	 * @access public
	 * @return void
	 */
	public static function render()
	{
		$export = Typecho_Plugin::export();
		if(array_key_exists('Collection', $export['activated']))
			include 'template/template.php';
		else
			echo '<div>Collection 插件未开启</div>';
	}
}
?>
