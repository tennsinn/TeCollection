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

		$query = $this->_db->select()->from('table.collection')->where('grade = ?', 0);
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
			$this->_db->query($this->_db->update('table.collection')->rows(array('ep_status' => ($row['ep_status']+1), 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
			$this->response->throwJson(array('result' => true, 'status' => 'do', 'ep_status' => ($row['ep_status']+1)));
		}
		elseif(($row['ep_status']+1) == $row['ep_count'])
		{
			$this->_db->query($this->_db->update('table.collection')->rows(array('status' => 'collect', 'ep_status' => $row['ep_count'], 'time_finish' => Typecho_Date::gmtTime(), 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
			$this->response->throwJson(array('result' => true, 'status' => 'collect', 'ep_status' => $row['ep_count']));
		}
		$this->response->throwJson(array('result' => false, 'message' => '没有可以增加的进度'));
	}

	/**
	 * 记录信息编辑
	 *
	 * @return void
	 */
	private function editSubject()
	{
		$data = array('id', 'category', 'class', 'type', 'name', 'name_cn', 'author', 'publisher', 'published', 'ep_count', 'source', 'source_id', 'parent', 'parent_order', 'parent_label', 'grade', 'status', 'ep_status', 'rate', 'tags', 'comment', 'note');
		$data = $this->request->from($data);
		if($data['published'])
			$data['published'] = strtotime($data['published']) - $this->_options->timezone + $this->_options->serverTimezone;
		$data['image'] = $this->request->filter('url')->get('image');
		$data['media_link'] = $this->request->filter('url')->get('media_link');

		$validator = new Collection_Extend_Validate();
		$validator->addRule('id', 'required', _t('缺少ID信息'));
		$validator->addRule('category', 'required', _t('缺少大类信息'));
		$validator->addRule('category', 'inArray', _t('请使用支持的大类'), $this->_config->arrayCategory);
		$validator->addRule('name', 'required', _t('必须输入名称'));
		$validator->addRule('image', 'url', _t('请输入有效图片地址'));
		$validator->addRule('media_link', 'url', _t('请输入有效链接地址'));
		$validator->addRule('source', 'inArray', _t('请使用支持的来源'), $this->_config->arraySource);
		$validator->addRule('grade', 'isInteger', _t('请用0-9的数字表示级别'));
		$validator->addRule('grade', 'inRange', _t('请用0-9的数字表示级别'), 0, 9);
		$validator->addRule('rate', 'isInteger', _t('请使用0-10的数字表示评价'));
		$validator->addRule('rate', 'inRange', _t('请使用0-10的数字表示评价'), 0, 10);
		if('series' != $data['category'])
		{
			$validator->addRule('class', 'required', _t('必须选择分类'));
			$validator->addRule('class', 'isInteger', _t('分类信息错误'));
			$validator->addRule('class', 'inRange', _t('分类信息错误'), 1, 6);
			$validator->addRule('type', 'required', _t('必须选择类型'));
			$validator->addRule('type', 'inArray', _t('类型信息错误'), $this->_config->arrayType);
			$validator->addRule('parent', 'isInteger', _t('父记录错误'));
			$validator->addRule('parent', 'inDb', _t('父记录不存在'), 'id', 0);
			$validator->addRule('parent_order', 'isInteger', _t('记录序号错误'));
			$validator->addRule('parent_order', 'inRange', _t('记录序号错误'), 0);
			$validator->addRule('ep_count', 'isInteger', _t('请使用整数值标记进度'));
			$validator->addRule('ep_status', 'isInteger', _t('请使用整数值标记进度'));
			$validator->addRule('ep_status', 'isValidProgress', _t('请输入正确的进度'), $data['ep_count']);
		}
		if($error = $validator->run($data))
			$this->response->throwJson(array('result' => false, 'message' => implode("\n", $error)));

		if('series' == $data['category'])
		{
			$row = array(
				'class' => NULL,
				'type' => NULL,
				'author' => NULL,
				'publisher' => NULL,
				'published' => NULL,
				'ep_count' => NULL,
				'parent' => 0,
				'parent_order' => 0,
				'parent_label' => NULL,
				'ep_status' => NULL,
			);
			$data = array_replace($data, $row);
		}
		$data['time_touch'] = Typecho_Date::gmtTime();
		if($data['status'] == 'do' && ($data['ep_count'] > 0 && $data['ep_count'] == $data['ep_status']))
		{
			$data['status'] = 'collect';
			$data['time_finish'] = Typecho_Date::gmtTime();
			$json['status'] = 'collect';
		}

		$update = $this->_db->query($this->_db->update('table.collection')->where('id = ?', $data['id'])->rows($data));
		if($update > 0)
			$this->response->throwJson(array('result' => true, 'message' => _t('记录已修改')));
		else
			$this->response->throwJson(array('result' => false, 'message' => _t('记录修改失败')));
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
						$row = array('status' => $status, 'time_touch' => Typecho_Date::gmtTime());
						$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('id = ?', $id));
						switch($status)
						{
							case 'do':
								if(!$row_temp['time_start'])
									$row['time_start'] = Typecho_Date::gmtTime();
								if($row_temp['time_finish'])
									$row['time_finish'] = NULL;
								break;
							case 'collect':
								if($row_temp['ep_count'])
									$row['ep_status'] = $row_temp['ep_count'];
							case 'dropped':
								$row['time_finish'] = Typecho_Date::gmtTime();
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
							'time_touch' => Typecho_Date::gmtTime()
						);
						switch($status)
						{
							case 'do':
								if(!$row_temp['time_start'])
									$row['time_start'] = Typecho_Date::gmtTime();
								if($row_temp['time_finish'])
									$row['time_finish'] = NULL;
								break;
							case 'collect':
								if($row_temp['ep_count'])
									$row['ep_status'] = $row_temp['ep_count'];
							case 'dropped':
								$row['time_finish'] = Typecho_Date::gmtTime();
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
									$row['time_start'] = Typecho_Date::gmtTime();
									break;
								case 'collect':
									if(isset($row['ep_count']) && $row['ep_count'])
										$row['ep_status'] = $row['ep_count'];
								case 'dropped':
									$row['time_finish'] = Typecho_Date::gmtTime();
									break;
							}
							$row['time_touch'] = Typecho_Date::gmtTime();
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
		$this->response->goBack(Typecho_Common::url('extending.php?panel=Collection%2FPanel.php&status='.($status == 'delete' ? 'all' : $status), $this->_options->adminUrl));
	}

	/**
	 * 增加收藏
	 * 
	 * @return void
	 */
	public function addSubject()
	{
		$message = $this->formInput()->validate();
		if($message)
			$this->widget('Widget_Notice')->set($message, 'notice');
		else
		{
			if($this->request->source != 'Collection')
				$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('source = ?', $this->request->source)->where('source_id = ?', $source_id));
			else
				$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('name = ?', $this->request->name)->where('category = ?', $this->request->category));
			if($row_temp)
				$this->widget('Widget_Notice')->set('当前记录已存在', 'notice');
			else
			{
				$progress = $this->request->getArray('progress');
				if(in_array('ep_progress', $progress))
				{
					if(!is_null($this->request->ep_count))
						$ep_count = min(999, max(0, intval($this->request->ep_count)));
					if(!is_null($this->request->ep_status))
						$ep_status = min($ep_count, max(0, intval($this->request->ep_status)));
				}
				else
				{
					$ep_count = NULL;
					$ep_status = NULL;
				}
				$time_start = NULL;
				$time_finish = NULL;
				if($this->request->status == 'do')
					$time_start = Typecho_Date::gmtTime();
				else
					if($this->request->status == 'collect')
						$time_finish = Typecho_Date::gmtTime();
				$published = NULL;
				if($this->request->get('published'))
					$published = strtotime($this->request->published) - $this->_options->timezone + $this->_options->serverTimezone;

				if('series' == $this->request->category)
					$this->_db->query($this->_db->insert('table.collection')->rows(
						array(
							'category' => 'series',
							'name' => $this->request->name,
							'name_cn' => $this->request->name_cn,
							'image' => $this->request->image,
							'source' => $this->request->source,
							'source_id' => $this->request->source_id,
							'media_link' => $this->request->filter('url')->get('media_link'),
							'grade' => $this->request->grade,
							'status' => $this->request->status,
							'time_start' => $time_start,
							'time_finish' => $time_finish,
							'time_touch' => Typecho_Date::gmtTime(),
							'rate' => $this->request->rate,
							'tags' => $this->request->tags,
							'comment' => $this->request->comment,
							'note' => $this->request->note
						)
					));
				else
					$this->_db->query($this->_db->insert('table.collection')->rows(
						array(
							'category' => $this->request->category,
							'class' => $this->request->class,
							'type' => $this->request->type,
							'name' => $this->request->name,
							'name_cn' => $this->request->name_cn,
							'image' => $this->request->image,
							'author' => $this->request->get('author'),
							'publisher' => $this->request->get('publisher'),
							'published' => $published,
							'ep_count' => $ep_count,
							'source' => $this->request->source,
							'source_id' => $this->request->source_id,
							'media_link' => $this->request->filter('url')->get('media_link'),
							'parent' => $this->request->parent,
							'parent_order' => $this->request->parent_order,
							'parent_label' => $this->request->get('parent_label'),
							'grade' => $this->request->grade,
							'status' => $this->request->status,
							'time_start' => $time_start,
							'time_finish' => $time_finish,
							'time_touch' => Typecho_Date::gmtTime(),
							'ep_status' => $ep_status,
							'rate' => $this->request->rate,
							'tags' => $this->request->tags,
							'comment' => $this->request->comment,
							'note' => $this->request->note
						)
					));
				$this->widget('Widget_Notice')->set('记录添加成功', 'success');
			}
		}
		$this->response->goBack();
	}

	/**
	 * 获取收藏条目
	 *
	 * @param  integer $pageSize 分页大小
	 * @return array
	 */
	public function showCollection($pageSize=20)
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
			$rows = $this->_db->fetchAll($query->page($page, $pageSize));
			$query = $this->request->makeUriByRequest('page={page}');
			/*foreach ($rows as $key => $value)
			{
				$rows[$key]['relatedPrev'] = $this->_db->fetchRow($this->_db->select('id', 'name', 'image')->from('table.collection')->where('id = ?', $value['parent']));
				$rows[$key]['relatedNext'] = $this->_db->fetchRow($this->_db->select('id', 'name', 'image')->from('table.collection')->where('parent = ?', $value['id']));
			}*/
			$nav = new Typecho_Widget_Helper_PageNavigator_Box($num, $page, $pageSize, $query);
			return array('result' => true, 'list' => $rows, 'nav' => $nav);
		}
		else
			return array('result' => false, 'message' => '存在0条记录');
	}

	/**
	 * 搜索
	 *
	 * @param  integer $pageSize 分页大小
	 * @return array
	 */
	public function search($pageSize=20)
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
				$results = Collection_Source_Bangumi::searchSubject($this->request->get('keywords'), $this->request->get('class', 0), $pageSize, $page);
				break;
		}
		if($results['result'])
			$query = $this->request->makeUriByRequest('page={page}');
			$nav = new Typecho_Widget_Helper_PageNavigator_Box($results['count'], $page, $pageSize, $query);
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
		$category->addRule('required', '必须选择大类');
		$form->addInput($category);

		$class = new Typecho_Widget_Helper_Form_Element_Radio('class', $this->_config->dictClass, 1, '分类 *');
		$class->addRule('required', '必须选择分类');
		$form->addInput($class);

		$type = new Typecho_Widget_Helper_Form_Element_Radio('type', $this->_config->dictType[1], 'Novel', '类型 *');
		$type->addRule('required', '必须选择类型');
		$form->addInput($type);

		$name = new Typecho_Widget_Helper_Form_Element_Text('name', NULL, NULL, '名称 *');
		$name->addRule('required', '必须填写记录名称');
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

		$source = new Typecho_Widget_Helper_Form_Element_Select('source', $this->_config->dictSourceName, 'Collection', '信息来源 *');
		$source->addRule('required', '必须选择来源');
		$form->addInput($source);

		$source_id = new Typecho_Widget_Helper_Form_Element_Text('source_id', NULL, NULL, '来源ID');
		$source_id->input->setAttribute('class', 'text-s w-30');
		$form->addInput($source_id);

		$media_link = new Typecho_Widget_Helper_Form_Element_Text('media_link', NULL, NULL, '媒体链接');
		$media_link->addRule('url', '请输入有效链接地址');
		$form->addInput($media_link);

		$image = new Typecho_Widget_Helper_Form_Element_Text('image', NULL, NULL, '封面地址');
		$image->addRule('url', '请正确输入图片地址');
		$form->addInput($image);

		$editProgress = array(
			'ep_progress' => '输入主进度：<input type="text" class="text-s num" name="ep_status"> / <input type="text" class="text num text-s" name="ep_count"> ',
		);
		$progress = new Typecho_Widget_Helper_Form_Element_Checkbox('progress', $editProgress, NULL, '进度信息', '选择将要添加的信息，默认为0/0，不选择则认为无进度项');
		$form->addInput($progress->multiMode());

		$parent = new Typecho_Widget_Helper_Form_Element_Text('parent', NULL, 0, '关联记录', '关联的记录ID，无则为0');
		$parent ->input->setAttribute('class', 'text-s w-30');
		$parent->addRule('isInteger', '请正确输入ID');
		$form->addInput($parent);

		$parent_order = new Typecho_Widget_Helper_Form_Element_Text('parent_order', NULL, 0, '关联顺序', '关联的记录排序，无则为0');
		$parent_order ->input->setAttribute('class', 'text-s w-30');
		$parent_order->addRule('isInteger', '请正确输入ID');
		$form->addInput($parent_order);

		$grade = new Typecho_Widget_Helper_Form_Element_radio('grade', $this->_config->dictGrade, 0, '显示分级');
		$form->addInput($grade);

		$status = new Typecho_Widget_Helper_Form_Element_Radio('status', $this->_config->dictStatus, 'wish', '记录当前状态 *');
		$status->addRule('required', '必须选择记录状态');
		$form->addInput($status);

		$rate = new Typecho_Widget_Helper_Form_Element_Text('rate', NULL, 0, '评价', '请使用0-10的数字表示，0为无评价');
		$rate->input->setAttribute('class', 'text-s');
		$rate->input->setAttribute('type', 'number');
		$rate->input->setAttribute('min', '0');
		$rate->input->setAttribute('max', '10');
		$rate->addRule('isInteger', '请使用0-10的数字表示');
		$form->addInput($rate);

		$tags = new Typecho_Widget_Helper_Form_Element_Text('tags', NULL, NULL, '标签', '请使用空格分隔');
		$tags->input->setAttribute('class', 'text-s');
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
