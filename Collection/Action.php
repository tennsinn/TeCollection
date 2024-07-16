<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Collection_Action extends Typecho_Widget implements Widget_Interface_Do
{
	public function __construct($request, $response, $params = NULL)
	{
		parent::__construct($request, $response, $params);
		$this->_options = Helper::options();
		$this->_security = Helper::security();
		$this->_settings = Helper::options()->plugin('Collection');
		$this->_db = Typecho_Db::get();
		$this->_config = $this->widget('Collection_Config@action');
	}

	public function action()
	{
		$this->on($this->request->is('do=getCollection'))->getCollection();
		$this->_security->protect();
		$this->widget("Widget_User")->pass("administrator");
		$this->on($this->request->is('do=plusEp'))->plusEp();
		$this->on($this->request->is('do=editSubject'))->editSubject();
		$this->on($this->request->is('do=editColumn'))->editColumn();
		$this->on($this->request->is('do=editStatus'))->editStatus();
		$this->on($this->request->is('do=addSubject'))->addSubject();
	}

	/**
	 * 对外展示收藏内容
	 *
	 * @return void
	 */
	public function getCollection()
	{
		$interCategory = array_intersect($this->request->getArray('category'), $this->_config->arrayCategory);
		$interClass = array_intersect($this->request->filter('int')->getArray('class'), array(1, 2, 3, 4, 5, 6));
		$interType = array_intersect($this->request->getArray('type'), $this->_config->arrayType);
		$interStatus = array_intersect($this->request->getArray('status'), array('do', 'wish', 'collect', 'on_hold', 'dropped'));
		$rate = explode(',', $this->request->get('rate'));
		$minRate = ($rate[0]<=$rate[1] && $rate[0]>=0) ? $rate[0] : '0';
		$maxRate = ($rate[0]<=$rate[1] && $rate[1]<=10) ? $rate[1] : '10';

		$query = $this->_db->select()->from('table.collection');
		$query->where('grade <= ?', $this->_settings->grade_output);
		$query->where("category='".implode("' OR category='", $interCategory)."'");
		$query->where("class='".implode("' OR class='", $interClass)."'");
		if(in_array('null', $this->request->getArray('type')))
			$query->where("ISNULL(type) OR type='".implode("' OR type='", $interType)."'");
		else
			$query->where("type='".implode("' OR type='", $interType)."'");
		$query->where("status='".implode("' OR status='", $interStatus)."'");
		$query->where('rate>='.$minRate.' AND rate<='.$maxRate);

		$queryNum = clone $query;
		
		$num = $this->_db->fetchObject($queryNum->select(array('COUNT(table.collection.id)' => 'num')))->num;
		if($num)
		{
			if(in_array($this->request->get('orderby'), array('id', 'rate', 'time_touch', 'time_start', 'time_finish')) && in_array($this->request->get('order'), array('DESC', 'ASC')))
				$query->order($this->request->get('orderby'), $this->request->get('order'));
			$rows = $this->_db->fetchAll($query);
			foreach($rows as $row)
				$row['link'] = Collection_Source::getLink($row['source'], $row['source_id'], $row['class']);
			$this->response->throwJson(array('result' => true, 'count' => $num, 'list' => $rows));
		}
		else
			$this->response->throwJson(array('result' => false, 'message' => '存在0条记录'));
	}

	/**
	 * 进度增加
	 *
	 * @return void
	 */
	private function plusEp()
	{
		if(!$this->request->get('id'))
			$this->response->throwJson(array('result' => false, 'message' => '缺少必要信息'));
		$row = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('id = ?', $this->request->id));
		if(($row['ep_status']+1) < $row['ep_count'] || $row['ep_count'] == 0)
		{
			$this->_db->query($this->_db->update('table.collection')->rows(array('ep_status' => ($row['ep_status']+1), 'time_touch' => Typecho_Date::time()))->where('id = ?', $this->request->id));
			$this->response->throwJson(array('result' => true, 'status' => 'do', 'ep_status' => ($row['ep_status']+1)));
		}
		elseif(($row['ep_status']+1) == $row['ep_count'])
		{
			$this->_db->query($this->_db->update('table.collection')->rows(array('status' => 'collect', 'ep_status' => $row['ep_count'], 'time_finish' => Typecho_Date::time(), 'time_touch' => Typecho_Date::time()))->where('id = ?', $this->request->id));
			$this->response->throwJson(array('result' => true, 'status' => 'collect', 'ep_status' => $row['ep_count']));
		}
		$this->response->throwJson(array('result' => false, 'message' => '没有可以增加的进度'));
	}

	/**
	 * 创建条目验证器
	 *
	 * @access private
	 * @param string $type 验证条目类型
	 * @param string $category 条目大类
	 * @param integer $ep_count 进度总数
	 * @return Collection_Extend_Validate
	 */
	private function validator($type = 'edit', $category = 'subject', $ep_count = NULL, $column = NULL)
	{
		$validator = new Collection_Extend_Validate();
		$valid_all = array(
			'category' => array(
				array('category', 'required', _t('必须选择大类')),
				array('category', 'inArray', _t('请使用支持的大类'), $this->_config->arrayCategory),
			),
			'name' => array(
				array('name', 'required', _t('必须输入名称')),
			),
			'image' => array(
				array('image', 'url', _t('请输入有效图片地址')),
			),
			'media_link' => array(
				array('media_link', 'url', _t('请输入有效链接地址')),
			),
			'source' => array(
				array('source', 'required', _t('必须选择来源')),
				array('source', 'inArray', _t('请使用支持的来源'), $this->_config->arraySource),
			),
			'grade' => array(
				array('grade', 'isInteger', _t('请用0-3的数字表示级别')),
				array('grade', 'inRange', _t('请用0-3的数字表示级别'), 0, 3),
			),
			'status' => array(
				array('status', 'required', _t('必须选择记录状态')),
			),
			'rate' => array(
				array('rate', 'isInteger', _t('请使用0-10的数字表示评价')),
				array('rate', 'inRange', _t('请使用0-10的数字表示评价'), 0, 10),
			),
		);
		$valid_subject = array(
			'class' => array(
				array('class', 'required', _t('必须选择分类')),
				array('class', 'isInteger', _t('分类信息错误')),
				array('class', 'inRange', _t('分类信息错误'), 1, 6),
			),
			'type' => array(
				array('type', 'required', _t('必须选择类型')),
				array('type', 'inArray', _t('类型信息错误'), $this->_config->arrayType),
			),
			'parent' => array(
				array('parent', 'isInteger', _t('请输入正确的父记录ID')),
				array('parent', 'inDb', _t('父记录不存在'), 'id', 0),
			),
			'parent_order' => array(
				array('parent_order', 'isInteger', _t('请使用正整数序号')),
				array('parent_order', 'inRange', _t('请使用正整数序号'), 0),
			),
			'ep_count' => array(
				array('ep_count', 'isInteger', _t('请使用正整数记录进度总数')),
				array('ep_count', 'inRange', _t('请使用正整数记录进度总数'), 0),
			),
			'ep_start' => array(
				array('ep_start', 'isInteger', _t('请使用正整数值标记进度起始')),
				array('ep_start', 'inRange', _t('请使用正整数值标记进度起始'), 0),
			),
			'ep_status' => array(
				array('ep_status', 'isInteger', _t('请使用正整数值标记进度')),
				array('ep_status', 'inRange', _t('请使用正整数值标记进度'), 0),
				array('ep_status', 'isValidProgress', _t('请输入正确的进度'), $ep_count),
			),
		);
		switch($type)
		{
			case 'edit':
				$validator->addRule2('id', 'required', _t('缺少ID信息'));
			case 'new':
				foreach($valid_all as $valids)
					$validator->addRules2($valids);
				if('series' != $category)
					foreach($valid_subject as $valids)
						$validator->addRules2($valids);
				break;
			case 'column':
				$valids = array_merge($valid_all, $valid_subject);
				if(in_array($column, array_keys($valids)))
					$validator->addRules2($valids[$column]);
				break;
		}
		return $validator;
	}

	/**
	 * 记录信息编辑
	 *
	 * @return void
	 */
	private function editSubject()
	{
		$columns = $this->_config->arrayColumn;
		$data = $this->request->from($columns);
		// validate data
		$data['image'] = $this->request->filter('url')->get('image');
		$data['media_link'] = $this->request->filter('url')->get('media_link');
		$validator = $this->validator('edit', $data['category'], $data['ep_count']);
		if($error = $validator->run($data))
			$this->response->throwJson(array('result' => false, 'message' => implode("\n", $error)));
		// data process
		if('series' == $data['category'])
		{
			$row = array(
				'class' => NULL,
				'type' => NULL,
				'author' => NULL,
				'publisher' => NULL,
				'published' => NULL,
				'ep_count' => NULL,
				'ep_start' => NULL,
				'parent' => '0',
				'parent_order' => '0',
				'parent_label' => NULL,
				'ep_status' => NULL,
			);
			$data = array_replace($data, $row);
		}
		else
		{
			if(!empty($data['published']) && '1970-01-01' != $data['published'])
				$data['published'] = strtotime($data['published']) - $this->_options->timezone + $this->_options->serverTimezone;
			else
				$data['published'] = NULL;
			if($data['status'] == 'do' && ($data['ep_count'] > 0 && $data['ep_count'] == $data['ep_status']))
			{
				$data['status'] = 'collect';
				$data['time_finish'] = Typecho_Date::time();
			}
		}
		// clean the blank value
		foreach($data as $key => $val)
			if('' == $val)
				$data[$key] = NULL;
		$data['time_touch'] = Typecho_Date::time();
		$update = $this->_db->query($this->_db->update('table.collection')->where('id = ?', $data['id'])->rows($data));
		if($update > 0)
			$this->response->throwJson(array('result' => true, 'message' => _t('记录已修改'), 'link' => Collection_Source::getLink($data['source'], $data['source_id'], $data['class'])));
		else
			$this->response->throwJson(array('result' => false, 'message' => _t('无记录更新')));
	}

	/**
	 * 记录字段编辑
	 *
	 * @return void
	 */
	private function editColumn()
	{
		$columns = $this->_config->batchColumn;
		$column = $this->request->column;
		if(!in_array($column, $columns))
			$this->response->throwJson(array('result' => false, 'message' => _t('修改字段不存在')));
		$data = array($column => $this->request->value, 'time_touch' => Typecho_Date::time());
		$ids = $this->request->filter('int')->getArray('id');
		$validator = $this->validator('column', NULL, NULL, $column);
		$message = array();
		$success = 0;
		foreach($ids as $id)
		{
			if($error = $validator->run($data))
				$message[] = $id.'：'.implode(' ', $error);
			else
			{
				$update = $this->_db->query($this->_db->update('table.collection')->where('id = ?', $id)->rows($data));
				if($update)
					$success ++;
			}
		}
		$result = '已成功更新：'.$success.'项。';
		if($message)
		{
			$result .= implode('；', $message);
			$this->widget('Widget_Notice')->set($result, 'notice');
		}
		else
			$this->widget('Widget_Notice')->set($result, 'success');
		$this->response->goBack();
	}

	/**
	 * 收藏修改
	 *
	 * @return void
	 */
	private function editStatus()
	{
		if(isset($this->request->status))
		{
			$status = $this->request->get('status');
			if(isset($this->request->id) && $ids = $this->request->filter('int')->getArray('id'))
			{
				if($status == 'delete')
				{
					foreach($ids as $id)
						$this->_db->query($this->_db->delete('table.collection')->where('id = ?', $id));
					$this->widget('Widget_Notice')->set('已删除'.count($ids).'条收藏记录', 'success');
				}
				else
				{
					foreach($ids as $id)
					{
						$row = array('status' => $status, 'time_touch' => Typecho_Date::time());
						$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('id = ?', $id));
						switch($status)
						{
							case 'do':
								if(!$row_temp['time_start'])
									$row['time_start'] = Typecho_Date::time();
								if($row_temp['time_finish'])
									$row['time_finish'] = NULL;
								break;
							case 'collect':
								if($row_temp['ep_count'])
									$row['ep_status'] = $row_temp['ep_count'];
							case 'dropped':
								$row['time_finish'] = Typecho_Date::time();
								break;
						}
						$this->_db->query($this->_db->update('table.collection')->rows($row)->where('id = ?', $id));
					}
				}
			}
			elseif(isset($this->request->source_id) && $source_ids = $this->request->filter('int')->getArray('source_id'))
			{
				$failure = array();
				$source = $this->request->get('source', 'Bangumi');
				foreach($source_ids as $source_id)
				{
					$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('source = ?', $source)->where('source_id = ?', $source_id));
					if($row_temp)
					{
						$row = array(
							'status' => $status,
							'time_touch' => Typecho_Date::time()
						);
						switch($status)
						{
							case 'do':
								if(!$row_temp['time_start'])
									$row['time_start'] = Typecho_Date::time();
								if($row_temp['time_finish'])
									$row['time_finish'] = NULL;
								break;
							case 'collect':
								if($row_temp['ep_count'])
									$row['ep_status'] = $row_temp['ep_count'];
							case 'dropped':
								$row['time_finish'] = Typecho_Date::time();
								break;
						}
						$this->_db->query($this->_db->update('table.collection')->where('source = ?', $source)->where('source_id = ?', $source_id)->rows($row));
					}
					else
					{
						switch($source)
						{
							case 'Bangumi':
							default:
								$row = Collection_Source_Bangumi::getSubject($source_id);
								break;
						}
						if($row)
						{
							switch($status)
							{
								case 'do':
									$row['time_start'] = Typecho_Date::time();
									break;
								case 'collect':
									if(isset($row['ep_count']) && $row['ep_count'])
										$row['ep_status'] = $row['ep_count'];
								case 'dropped':
									$row['time_finish'] = Typecho_Date::time();
									break;
							}
							$row['time_touch'] = Typecho_Date::time();
							$row['status'] = $status;
							$row['source'] = $source;
							$row['source_id'] = $source_id;
							$this->_db->query($this->_db->insert('table.collection')->rows($row));
						}
						else
							array_push($failure, $source_id);
					}
				}
				if($failure)
					$this->widget('Widget_Notice')->set('以下记录修改失败：'.json_encode($failure), 'notice');
				else
					$this->widget('Widget_Notice')->set('已修改'.count($source_ids).'条收藏记录', 'success');
			}
			else
				$this->widget('Widget_Notice')->set('未选中任何项目', 'notice');
		}
		else
			$this->widget('Widget_Notice')->set('未指明收藏状态', 'notice');
		$this->response->goBack(Typecho_Common::url('extending.php?panel=Collection%2FPanel.php&status='.($status == 'delete' ? 'all' : $status).'&page=1', $this->_options->adminUrl));
	}

	/**
	 * 增加收藏
	 * 
	 * @return void
	 */
	public function addSubject()
	{
		$data = $this->formInput()->getParams(array('category','ep_count'));
		$validator = $this->validator('new', $data['category'], $data['ep_count']);
		$message = $this->formInput()->validate2($validator);
		if($message)
			$this->widget('Widget_Notice')->set($message, 'notice');
		else
		{
			if($this->request->source != 'Collection')
				$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('source = ?', $this->request->source)->where('source_id = ?', $source_id));
			else
				$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('name = ?', $this->request->name)->where('class = ?', $this->request->class)->where('category = ?', $this->request->category)->where('type = ?', $this->request->type));
			if($row_temp)
				$this->widget('Widget_Notice')->set('当前记录已存在', 'notice');
			else
			{
				$progress = $this->request->getArray('progress');
				$ep_count = NULL;
				$ep_start = NULL;
				$ep_status = NULL;
				if(in_array('ep_progress', $progress))
				{
					if(!is_null($this->request->ep_count))
						$ep_count = min(999, max(0, intval($this->request->ep_count)));
					if(!is_null($this->request->ep_status))
						$ep_status = min($ep_count, max(0, intval($this->request->ep_status)));
					if(!is_null($this->request->ep_start))
						$ep_start = $this->request->ep_start;
				}
				$time_start = NULL;
				$time_finish = NULL;
				if($this->request->status == 'do')
					$time_start = Typecho_Date::time();
				else
					if($this->request->status == 'collect')
						$time_finish = Typecho_Date::time();
				$published = NULL;
				if($this->request->get('published'))
					$published = strtotime($this->request->published) - $this->_options->timezone + $this->_options->serverTimezone;

				$query_array = array(
					'category' => $this->request->category,
					'name' => $this->request->name,
					'name_cn' => $this->request->name_cn,
					'image' => $this->request->image,
					'source' => $this->request->source,
					'source_id' => $this->request->source_id,
					'media_link' => $this->request->filter('url')->media_link,
					'grade' => $this->request->grade,
					'status' => $this->request->status,
					'time_start' => $time_start,
					'time_finish' => $time_finish,
					'time_touch' => Typecho_Date::time(),
					'rate' => $this->request->rate,
					'tags' => $this->request->tags,
					'comment' => $this->request->comment,
					'note' => $this->request->note,
				);
				if('series' != $this->request->category)
					$query_array = array_merge($query_array, array(
						'class' => $this->request->class,
						'type' => $this->request->type,
						'author' => $this->request->author,
						'publisher' => $this->request->publisher,
						'published' => $published,
						'ep_count' => $ep_count,
						'ep_start' => $ep_start,
						'parent' => $this->request->parent,
						'parent_order' => $this->request->parent_order,
						'parent_label' => $this->request->parent_label,
						'ep_status' => $ep_status,
					));
				$this->_db->query($this->_db->insert('table.collection')->rows($query_array));
				$this->widget('Widget_Notice')->set('记录添加成功', 'success');
			}
		}
		$this->response->goBack();
	}

	/**
	 * 获取收藏条目
	 *
	 * @return array
	 */
	public function showCollection()
	{
		$status = isset($this->request->status) ? $this->request->get('status') : 'do';
		$category = isset($this->request->category) ? $this->request->get('category') : 'subject';
		$class = isset($this->request->class) ? $this->request->get('class') : 0;
		$type = isset($this->request->type) ? $this->request->get('type') : 'all';
		$field = isset($this->request->field) ? $this->request->get('field') : 'name';
		$query = $this->_db->select()->from('table.collection');
		if($status != 'all')
			$query->where('status = ?', $status);
		$query->where('category = ?', $category);
		if($class != 0)
			$query->where('class = ?', $class);
		if($type != 'all')
			$query->where('type = ?', $type);
		if(NULL != ($keywords = $this->request->filter('search')->keywords))
		{
			$args = array();
			$keywordsList = explode(' ', $keywords);
			if($field == 'id')
			{
				$args[] = implode(' OR ', array_fill(0, count($keywordsList), 'table.collection.id = ?'));
				foreach($keywordsList as $keyword)
					$args[] = $keyword;
			}
			else
			{
				switch($field)
				{
					case 'tags':
						$args[] = implode(' AND ', array_fill(0, count($keywordsList), 'table.collection.tags LIKE ?'));
						break;
					case 'comment':
						$args[] = implode(' AND ', array_fill(0, count($keywordsList), 'table.collection.comment LIKE ?'));
						break;
					case 'note':
						$args[] = implode(' AND ', array_fill(0, count($keywordsList), 'table.collection.note LIKE ?'));
						break;
					case 'name':
					default:
						$args[] = implode(' AND ', array_fill(0, count($keywordsList), 'CONCAT_WS(" ",table.collection.name,table.collection.name_cn) LIKE ?'));
						break;
				}
				foreach($keywordsList as $keyword)
					$args[] = '%' . $keyword . '%';
			}
			call_user_func_array(array($query, 'where'), $args);
		}
		$queryNum = clone $query;
		$num = $this->_db->fetchObject($queryNum->select(array('COUNT(table.collection.id)' => 'num')))->num;
		if($num)
		{
			$page = isset($this->request->page) ? $this->request->get('page') : 1;
			if(in_array($orderby = $this->request->get('orderby'), array('id', 'rate', 'time_touch', 'time_start', 'time_finish')) && in_array($order = $this->request->get('order'), array('DESC', 'ASC')))
				$query->order($orderby, $order);
			else
				$query->order('time_touch', Typecho_Db::SORT_DESC);
			$rows = $this->_db->fetchAll($query->page($page, $this->_settings->page_size));
			$query = $this->request->makeUriByRequest('page={page}');
			/*foreach ($rows as $key => $value)
			{
				$rows[$key]['relatedPrev'] = $this->_db->fetchRow($this->_db->select('id', 'name', 'image')->from('table.collection')->where('id = ?', $value['parent']));
				$rows[$key]['relatedNext'] = $this->_db->fetchRow($this->_db->select('id', 'name', 'image')->from('table.collection')->where('parent = ?', $value['id']));
			}*/
			$nav = new Typecho_Widget_Helper_PageNavigator_Box($num, $page, $this->_settings->page_size, $query);
			return array('result' => true, 'list' => $rows, 'nav' => $nav);
		}
		else
			return array('result' => false, 'message' => '存在0条记录');
	}

	/**
	 * 搜索
	 *
	 * @return array
	 */
	public function search()
	{
		if(!isset($this->request->keywords))
			return array('result' => false, 'message' => '请输入关键字');
		$page = $this->request->get('page', 1);
		$source = $this->request->get('source', 'Bangumi');
		$list = array();
		switch($source)
		{
			case 'Bangumi':
			default:
				$results = Collection_Source_Bangumi::searchSubject($this->request->get('keywords'), $this->request->get('class', 0), $this->_settings->page_size, $page);
				break;
		}
		if($results['result'])
			$query = $this->request->makeUriByRequest('page={page}');
			$nav = new Typecho_Widget_Helper_PageNavigator_Box($results['count'], $page, $this->_settings->page_size, $query);
			$results['nav'] = $nav;
		return $results;
	}

	/**
	 * 收藏输入表格
	 * 
	 * @return Collection_Extend_Form
	 */
	public function formInput()
	{
		$form = new Collection_Extend_Form($this->_security->getIndex('/action/collection'), Collection_Extend_Form::POST_METHOD);
		
		$do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
		$form->addInput($do);
		$do->value('addSubject');

		$category = new Typecho_Widget_Helper_Form_Element_Radio('category', $this->_config->dictCategory, 'subject', '大类 *');
		$form->addInput($category);

		$class = new Typecho_Widget_Helper_Form_Element_Radio('class', $this->_config->dictClass, 1, '分类 *');
		$form->addInput($class);

		$type = new Typecho_Widget_Helper_Form_Element_Radio('type', $this->_config->dictType[1], 'Novel', '类型 *');
		$form->addInput($type);

		$name = new Typecho_Widget_Helper_Form_Element_Text('name', NULL, NULL, '名称 *');
		$name->input->setAttribute('class', 'text-s w-40');
		$form->addInput($name);

		$name_cn = new Typecho_Widget_Helper_Form_Element_Text('name_cn', NULL, NULL, '译名');
		$name_cn->input->setAttribute('class', 'text-s w-40');
		$form->addInput($name_cn);

		$author = new Typecho_Widget_Helper_Form_Element_Text('author', NULL, NULL, '作者');
		$author->input->setAttribute('class', 'w-40');
		$form->addInput($author);

		$publisher = new Typecho_Widget_Helper_Form_Element_Text('publisher', NULL, NULL, '出版商');
		$publisher->input->setAttribute('class', 'w-40');
		$form->addInput($publisher);

		$published = new Typecho_Widget_Helper_Form_Element_Text('published', NULL, NULL, '出版时间');
		$published->input->setAttribute('class', 'w-40');
		$form->addInput($published);

		$source = new Typecho_Widget_Helper_Form_Element_Select('source', $this->_config->dictSource, 'Collection', '信息来源 *');
		$form->addInput($source);

		$source_id = new Typecho_Widget_Helper_Form_Element_Text('source_id', NULL, NULL, '来源ID');
		$source_id->input->setAttribute('class', 'text-s w-30');
		$form->addInput($source_id);

		$media_link = new Typecho_Widget_Helper_Form_Element_Text('media_link', NULL, NULL, '媒体链接');
		$form->addInput($media_link);

		$image = new Typecho_Widget_Helper_Form_Element_Text('image', NULL, NULL, '封面地址');
		$form->addInput($image);

		$editProgress = array(
			'ep_progress' => '输入主进度：<input type="text" class="text-s num" name="ep_status"> / <input type="text" class="text num text-s" name="ep_count"> / <input type="text" class="text num text-s" name="ep_start"> ',
		);
		$progress = new Typecho_Widget_Helper_Form_Element_Checkbox('progress', $editProgress, NULL, '进度信息', '选择将要添加的信息，默认为0/0，不选择则认为无进度项');
		$form->addInput($progress->multiMode());

		$parent = new Typecho_Widget_Helper_Form_Element_Text('parent', NULL, 0, '关联记录', '关联的记录ID，无则为0');
		$parent ->input->setAttribute('class', 'text-s w-30');
		$form->addInput($parent);

		$parent_order = new Typecho_Widget_Helper_Form_Element_Text('parent_order', NULL, 0, '关联顺序', '关联的记录排序，无则为0');
		$parent_order ->input->setAttribute('class', 'text-s w-30');
		$form->addInput($parent_order);

		$grade = new Typecho_Widget_Helper_Form_Element_radio('grade', $this->_config->dictGrade, 0, '显示分级');
		$form->addInput($grade);

		$status = new Typecho_Widget_Helper_Form_Element_Radio('status', $this->_config->dictStatus, 'wish', '记录当前状态 *');
		$form->addInput($status);

		$rate = new Typecho_Widget_Helper_Form_Element_Text('rate', NULL, 0, '评价', '请使用0-10的数字表示，0为无评价');
		$rate->input->setAttribute('class', 'text-s');
		$rate->input->setAttribute('type', 'number');
		$rate->input->setAttribute('min', '0');
		$rate->input->setAttribute('max', '10');
		$form->addInput($rate);

		$tags = new Typecho_Widget_Helper_Form_Element_Text('tags', NULL, NULL, '标签', '请使用空格分隔');
		$tags->input->setAttribute('class', 'text-s w-100');
		$form->addInput($tags);

		$comment = new Typecho_Widget_Helper_Form_Element_Textarea('comment', NULL, NULL, '评论');
		$form->addInput($comment);

		$note = new Typecho_Widget_Helper_Form_Element_Textarea('note', NULL, NULL, '备注');
		$form->addInput($note);

		$submit = new Typecho_Widget_Helper_Form_Element_Submit('submit', NULL, '添加记录');
		$submit->input->setAttribute('class', 'btn primary');
		$form->addItem($submit);

		return $form;
	}
}
?>
