<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 表单处理扩展
 *
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 */
class Collection_Extend_Form extends Typecho_Widget_Helper_Form
{
	/**
	 * 验证表单
	 *
	 * @access public
	 * @return mixed
	 */
	public function validate(Collection_Extend_Validate $validator = NULL)
	{
		$inputs = $this->getInputs();
		$id = md5(implode('"', array_keys($inputs)));
		if($validator)
		{
			$formData = $this->getParams($validator->getItems());
			$error = $validator->run($formData);
		}
		else
		{
			$validator = new Collection_Extend_Validate();
			$rules = array();
			foreach ($inputs as $name => $input)
				$rules[$name] = $input->rules;
			$formData = $this->getParams(array_keys($rules));
			$error = $validator->run($formData, $rules);
		}
		if($error)
		{
			/** 利用session记录错误 */
			Typecho_Cookie::set('__typecho_form_message_' . $id, Json::encode($error));
			/** 利用session记录表单值 */
			Typecho_Cookie::set('__typecho_form_record_' . $id, Json::encode($formData));
		}
		return $error;
	}
}
