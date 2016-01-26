<?php

class Collection_Action extends Typecho_Widget implements Widget_Interface_Do
{
	public function __construct($request, $response, $params = NULL)
	{
		parent::__construct($request, $response, $params);
		$this->_options = Helper::options();
		$this->_settings = Helper::options()->plugin('Collection');
		$this->_db = Typecho_Db::get();
	}

	public function action()
	{
		$this->widget("Widget_User")->pass("administrator");
		$this->on($this->request->is('do=plusEp'))->plusEp();
		$this->on($this->request->is('do=editSubject'))->editSubject();
		$this->on($this->request->is('do=editStatus'))->editStatus();
		$this->on($this->request->is('do=addSubject'))->addSubject();
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
					$this->widget('Widget_Notice')->set(_t('已删除'.count($ids).'条收藏记录'), 'success');
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
			elseif(isset($this->request->subject_id) && $subject_ids = $this->request->filter('int')->getArray('subject_id'))
			{
				$failure = array();
				$source = $this->request->get('source');
				foreach($subject_ids as $subject_id)
				{
					$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('source = ?', $source)->where('subject_id = ?', $subject_id));
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
						$this->_db->query($this->_db->update('table.collection')->where('source = ?', $source)->where('subject_id = ?', $subject_id)->rows($row));
					}
					else
					{
						switch($source)
						{
							case 'Bangumi':
								$response = @file_get_contents('http://api.bgm.tv/subject/'.$subject_id);
								$response = json_decode($response, true);
								if($response)
								{
									$row = array(
										'class' => $response['type'],
										'name' => $response['name'],
										'name_cn' => $response['name_cn'],
										'image' => $response['images']['common'],
									);
									if($response['eps'])
									{
										$row['ep_count'] = $response['eps'];
										$row['ep_status'] = 0;
									}
								}
								break;
							case 'Douban':
								$arrayDoubanClass = array('1' => 'book', '3' => 'music', '6' => 'movie');
								$class = $this->request->get('class');
								$response = file_get_contents('https://api.douban.com/v2/'.$arrayDoubanClass[$class].'/'.$subject_id);
								$response = json_decode($response, true);
								if($response)
								{
									switch($class)
									{
										case '1':
											if(isset($response['origin_title']) && $response['origin_title'])
												$name = $response['origin_title'];
											else
												$name = $response['title'];
											if($response['tags'])
											{
												$tags = '';
												foreach($response['tags'] as $num => $tag)
													$tags .= $tag['name'].' ';
											}
											$row = array(
												'class' => $class,
												'name' => $name,
												'name_cn' => $response['alt_title'],
												'image' => $response['images']['medium'],
												'tags' => $tags
											);
											break;
										case '3':
											$tags = '';
											foreach($response['tags'] as $num => $tag)
												$tags .= $tag['name'].' ';
											$row = array(
												'class' => $class,
												'name' => $response['title'],
												'name_cn' => $response['alt_title'],
												'image' => $response['image'],
												'tags' => $tags
											);
											break;
										case '6':
											if(isset($response['origin_title']) && $response['origin_title'])
												$name = $response['origin_title'];
											else
												$name = $response['title'];
											$tags = '';
											foreach($response['genres'] as $genres)
												$tags .= $genres.' ';
											$row = array(
												'class' => $class,
												'name' => $name,
												'name_cn' => $response['title'],
												'image' => $response['image'],
												'tags' => $tags
											);
											break;
									}
								}
						}
						if($response)
						{
							switch($status)
							{
								case 'do':
									$row['time_start'] = Typecho_Date::gmtTime();
									break;
								case 'collect':
									$row['ep_status'] = $row['ep_count'];
								case 'dropped':
									$row['time_finish'] = Typecho_Date::gmtTime();
									break;
							}
							$row['time_touch'] = Typecho_Date::gmtTime();
							$row['status'] = $status;
							$row['source'] = $source;
							$row['subject_id'] = $subject_id;
							$this->_db->query($this->_db->insert('table.collection')->rows($row));
						}
						else
							array_push($failure, $subject_id);
					}
				}
				if($failure)
					$this->widget('Widget_Notice')->set(_t('以下记录修改失败：'.json_encode($failure)), 'notice');
				else
					$this->widget('Widget_Notice')->set(_t('已修改'.count($subject_ids).'条收藏记录'), 'success');
			}
			else
				$this->widget('Widget_Notice')->set(_t('未选中任何项目'), 'notice');
		}
		else
			$this->widget('Widget_Notice')->set(_t('未指明收藏状态'), 'notice');
		$class = isset($this->request->class) ? $this->request->get('class') : '0';
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Collection%2FPanel.php&class='.$class.'&status='.($status == 'delete' ? 'all' : $status), $this->_options->adminUrl));
	}

	/**
	 * 记录信息编辑
	 *
	 * @return void
	 */
	private function editSubject()
	{
		if($this->request->get('id') && isset($this->request->name))
		{
			if($this->request->name == '')
				$this->response->throwJson(array('success' => false, 'message' => _t('必须输入名称')));
			if( !is_numeric($this->request->class) && $this->request->class <= 0)
				$this->response->throwJson(array('success' => false, 'message' => _t('必须输入名称')));
			if((!is_null($this->request->get('ep_status')) || !is_null($this->request->get('ep_count'))) && (!is_numeric($this->request->ep_status) || !is_numeric($this->request->ep_count) || $this->request->ep_status<0 || $this->request->ep_count<0 || ($this->request->ep_count>0 && $this->request->ep_status>$this->request->ep_count)))
				$this->response->throwJson(array('success' => false, 'message' => _t('请输入正确的本篇进度')));
			if((!is_null($this->request->get('sp_status')) || !is_null($this->request->get('sp_count'))) && (!is_numeric($this->request->sp_status) || !is_numeric($this->request->sp_count) || $this->request->sp_status<0 || $this->request->sp_count<0 || ($this->request->sp_count>0 && $this->request->sp_status>$this->request->sp_count)))
				$this->response->throwJson(array('success' => false, 'message' => _t('请输入正确的特典进度')));
			if($this->request->get('rate') && (!is_numeric($this->request->rate) || $this->request->rate>10 || $this->request->rate<0))
				$this->response->throwJson(array('success' => false, 'message' => _t('评价请使用0-10的数字表示')));
			{
				$row = array(
					'class' => $this->request->class,
					'type' => $this->request->type,
					'name' => $this->request->name, 
					'name_cn' => $this->request->name_cn,
					'image' => $this->request->image,
					'ep_count' => $this->request->ep_count,
					'sp_count' => $this->request->sp_count,
					'time_touch' => Typecho_Date::gmtTime(),
					'ep_status' => $this->request->ep_status,
					'sp_status' => $this->request->sp_status,
					'rate' => $this->request->rate,
					'tags' => $this->request->tags,
					'comment' => $this->request->comment
				);
				$json = array('result' => true, 'message' => _t('修改成功'));
				if(($this->request->ep_count > 0 && $this->request->ep_count == $this->request->ep_status) && ($this->request->sp_count == 0 || ($this->request->sp_count > 0 && $this->request->sp_count == $this->request->sp_status)))
				{
					$row['status'] = 'collect';
					$json['status'] = 'collect';
				}
				else
					$json['status'] = $this->request->status;
				$this->_db->query($this->_db->update('table.collection')->where('id = ?', $this->request->id)->rows($row));
				$this->response->throwJson($json);
			}
		}
		else
			$this->response->throwJson(array('success' => false, 'message' => _t('缺少必要信息')));
	}

	/**
	 * 进度增加
	 *
	 * @return void
	 */
	private function plusEp()
	{
		if(!$this->request->get('id') || ($this->request->get('plus') != 'ep' && $this->request->get('plus') != 'sp'))
			$this->response->throwJson(array('result' => false, 'message' => _t('缺少必要信息')));
		$row = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('id = ?', $this->request->id));
		//if($row['class'] != 1 && $row['class'] != 2 && $row['class'] != 6)
		//	$this->response->throwJson(array('result' => false, 'message' => _t('所选记录无进度数据')));
		if($this->request->plus == 'ep')
		{
			if(($row['ep_status']+1) < $row['ep_count'] || $row['ep_count'] == '0')
			{
				$this->_db->query($this->_db->update('table.collection')->rows(array('ep_status' => ($row['ep_status']+1), 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
				$this->response->throwJson(array('result' => true, 'status' => 'do', 'plus' => 'ep', 'ep_status' => ($row['ep_status']+1)));
			}
			elseif($row['sp_status'] != $row['sp_count'])
			{
				$this->_db->query($this->_db->update('table.collection')->rows(array('ep_status' => $row['ep_count'], 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
				$this->response->throwJson(array('result' => true, 'status' => 'do', 'plus' => 'ep', 'ep_status' => $row['ep_count']));
			}
			else
			{
				$this->_db->query($this->_db->update('table.collection')->rows(array('status' => 'collect', 'ep_status' => $row['ep_count'], 'time_finish' => Typecho_Date::gmtTime(), 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
				$this->response->throwJson(array('result' => true, 'status' => 'collect', 'plus' => 'ep', 'ep_status' => $row['ep_count']));
			}
		}
		else
		{
			if(($row['sp_status']+1) < $row['sp_count'] || $row['sp_count'] == '0')
			{
				$this->_db->query($this->_db->update('table.collection')->rows(array('sp_status' => ($row['sp_status']+1), 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
				$this->response->throwJson(array('result' => true, 'status' => 'do', 'plus' => 'sp', 'sp_status' => ($row['sp_status']+1)));
			}
			elseif($row['ep_status'] != $row['ep_count'])
			{
				$this->_db->query($this->_db->update('table.collection')->rows(array('sp_status' => $row['sp_count'], 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
				$this->response->throwJson(array('result' => true, 'status' => 'do', 'plus' => 'sp', 'sp_status' => $row['sp_count']));
			}
			else
			{
				$this->_db->query($this->_db->update('table.collection')->rows(array('status' => 'collect', 'sp_status' => $row['sp_count'], 'time_finish' => Typecho_Date::gmtTime(), 'time_touch' => Typecho_Date::gmtTime()))->where('id = ?', $this->request->id));
				$this->response->throwJson(array('result' => true, 'status' => 'collect', 'plus' => 'sp', 'sp_status' => $row['sp_count']));
			}
		}
	}

	/**
	 * 获取收藏条目
	 *
	 * @param  integer $pageSize 分页大小
	 * @return array
	 */
	public function getCollection($pageSize=20)
	{
		$status = isset($this->request->status) ? $this->request->get('status') : 'do';
		$class = isset($this->request->class) ? $this->request->get('class') : '0';
		$query = $this->_db->select(array('COUNT(table.collection.id)' => 'num'))->from('table.collection');
		if($status != 'all')
			$query->where('status = ?', $status);
		if($class != 0)
			$query->where('class = ?', $class);
		$num = $this->_db->fetchObject($query)->num;
		if(!$num)
			return array('result' => false, 'message' => '存在0条记录');
		$page = isset($this->request->page) ? $this->request->get('page') : 1;
		$query = $this->_db->select()->from('table.collection')->order('time_touch', Typecho_Db::SORT_DESC)->page($page, $pageSize);
		if($status != 'all')
			$query->where('status = ?', $status);
		if($class != 0)
			$query->where('class = ?', $class);
		$rows = $this->_db->fetchAll($query);
		$query = $this->request->makeUriByRequest('page={page}');
		$nav = new Typecho_Widget_Helper_PageNavigator_Box($num, $page, $pageSize, $query);
		return array('result' => true, 'list' => $rows, 'nav' => $nav);
	}

	/**
	 * 搜索
	 *
	 * @param  integer $pageSize 分页大小
	 * @return array
	 */
	public function search($pageSize=10)
	{
		if(!isset($this->request->keywords))
			return array('result' => false, 'message' => '请输入关键字');
		$page = isset($this->request->page) ? $this->request->get('page') : 1;
		$class = isset($this->request->class) ? $this->request->get('class') : '0';
		$keywords = $this->request->get('keywords');
		$source = isset($this->request->source) ? $this->request->get('source') : 'Bangumi';
		$list = array();
		switch($source)
		{
			case 'Bangumi':
				$response = @file_get_contents('http://api.bgm.tv/search/subject/'.$keywords.'?responseGroup=large&max_results='.$pageSize.'&start='.($page-1)*$pageSize.'&type='.$class);
				$response = json_decode($response, true);
				if(!$response || (isset($response['results']) && !$response['results']))
					return array('result' => false, 'message' => '搜索到0个结果');
				elseif(!isset($response['results']) && isset($response['error']))
					return array('result' => false, 'message' => '关键字：'.$keywords.' 搜索出现错误 '.$response['code'].':'.$response['error']);
				else
				{
					foreach($response['list'] as $key => $value)
					{
						$info = '';
						if($value['eps'])
							$info .= '<div>总集数：'.$value['eps'].'</div>';
						if($value['summary'])
							$info .= '<div>简介：'.$value['summary'].'</div>';
						$list[$value['id']] = array(
							'class' => $value['type'],
							'name' => $value['name'],
							'name_cn' => $value['name_cn'],
							'image' => $value['images']['medium'],
							'info' => $info
						);
					}
					$count = $response['results'];
				}
				break;
			case 'Douban':
				$arrayDoubanClass = array('1' => 'book', '3' => 'music', '6' => 'movie');
				$response = @file_get_contents('https://api.douban.com/v2/'.$arrayDoubanClass[$class].'/search?q='.$keywords.'&start='.($page-1)*$pageSize.'&count='.$pageSize);
				$response = json_decode($response, true);
				if(!$response || (isset($response['total']) && !$response['total']))
					return array('result' => false, 'message' => '搜索到0个结果');
				else
				{
					switch($class)
					{
						case '1':
							foreach($response['books'] as $key => $value)
							{
								if(isset($value['origin_title']) && $value['origin_title'])
									$name = $value['origin_title'];
								else
									$name = $value['title'];
								$info = '<div>作者：';
								foreach($value['author'] as $author)
									$info .= $author;
								$info .= '</div>';
								$info .= '<div>标签：';
								foreach($value['tags'] as $num => $tag)
									$info .= $tag['name'].' ';
								$info .= '</div>';
								if(isset($value['series']) && $value['series'])
									$info .= '<div>系列：'.$value['series']['title'].'</div>';
								$list[$value['id']] = array(
									'class' => $class,
									'name' => $name,
									'name_cn' => $value['alt_title'],
									'image' => $value['images']['medium'],
									'info' => $info
								);
							}
							break;
						case '3':
							foreach($response['musics'] as $key => $value)
							{
								$info = '';
								if(isset($value['author']))
								{
									$info .= '<div>歌手：';
									foreach($value['author'] as $author)
										$info .= $author['name'];
									$info .= '</div>';
								}
								$info .= '<div>标签：';
								foreach($value['tags'] as $num => $tag)
									$info .= $tag['name'].' ';
								$info .= '</div>';
								$list[$value['id']] = array(
									'class' => $class,
									'name' => $value['title'],
									'name_cn' => $value['alt_title'],
									'image' => $value['image'],
									'info' => $info
								);
							}
							break;
						case '6':
							foreach($response['subjects'] as $key => $value)
							{
								if(isset($value['origin_title']) && $value['origin_title'])
									$name = $value['origin_title'];
								else
									$name = $value['title'];
								$info = '<div>年份：'.$value['year'].'</div>';
								$info .= '<div>类型：';
								foreach($value['genres'] as $genres)
									$info .= $genres.' ';
								$info .= '</div>';
								$list[$value['id']] = array(
									'class' => $class,
									'name' => $name,
									'name_cn' => $value['title'],
									'image' => $value['images']['medium'],
									'info' => $info
								);
							}
							break;
					}
					$count = $response['total'];
				}
				break;
		}
		$query = $this->request->makeUriByRequest('page={page}');
		$nav = new Typecho_Widget_Helper_PageNavigator_Box($count, $page, $pageSize, $query);
		return array('result' => true, 'list' => $list, 'nav' => $nav);
	}

	public function formNew()
	{
		$form = new Typecho_Widget_Helper_Form(Typecho_Common::url('/action/collection', $this->_options->index), Typecho_Widget_Helper_Form::POST_METHOD);
		
		$do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
		$form->addInput($do);
		$do->value('addSubject');

		$arrayClass = array(
			'1' => '书籍',
			'2' => '动画',
			'3' => '音乐',
			'4' => '游戏',
			'5' => '广播',
			'6' => '影视'
		);
		$class = new Typecho_Widget_Helper_Form_Element_Radio('class', $arrayClass, NULL, _t('分类'));
		$class->addRule('required', _t('必须选择分类'));
		$form->addInput($class);

		$arrayType = array(
			'Collection' => '收藏',
			'Series' => '系列',
			'Tankōbon' => '单行本',
			'TV' => 'TV',
			'OVA' => 'OVA',
			'OAD' => 'OAD',
			'Album' => '专辑',
			'Single' => '单曲',
			'iOS' => 'iOS',
			'Android' => 'Andriod',
			'PSV' => 'PSV',
			'3DS' => '3DS',
			'PC' => 'PC',
			'RadioDrama' => '广播剧',
			'Teleplay' => '电视剧',
			'TalkShow' => '脱口秀',
			'Movie' => '电影'
		);
		$type = new Typecho_Widget_Helper_Form_Element_Radio('type', $arrayType, 'Collection', _t('类型'));
		$form->addInput($type);

		$name = new Typecho_Widget_Helper_Form_Element_Text('name', NULL, NULL, _t('名称 *'));
		$name->addRule('required', _t('必须填写记录名称'));
		$name->input->setAttribute('class', 'w-40 mono');
		$form->addInput($name);

		$name_cn = new Typecho_Widget_Helper_Form_Element_Text('name_cn', NULL, NULL, _t('译名'));
		$name_cn->input->setAttribute('class', 'w-40 mono');
		$form->addInput($name_cn);

		$arrayProgress = array(
			'ep_progress' => _t('输入进度一：%s / %s ', '<input type="text" class="text num text-s" name="ep_status">', '<input type="text" class="text num text-s" name="ep_count">'),
			'sp_progress' => _t('输入进度二：%s / %s ', '<input type="text" class="text num text-s" name="sp_status">', '<input type="text" class="text num text-s" name="sp_count">')
		);
		$progress = new Typecho_Widget_Helper_Form_Element_Checkbox('progress', $arrayProgress, NULL, _t('进度信息'), _t('选择将要添加的信息，默认为0/0，不选择则认为无进度项'));
		$form->addInput($progress->multiMode());

		$arraySource = array(
			'Collection' => _t('手动输入 封面： %s ', '<input type="text" class="text-s mono w-50" name="collection_image">'),
			'Bangumi' => _t('Bangumi ID： %s 封面： %s ', '<input type="text" class="text-s mono w-30" name="bangumi_id">', '<input type="text" class="text-s mono w-50" name="bangumi_image">'),
			'Douban' => _t('豆瓣 ID： %s 封面： %s ', '<input type="text" class="text-s mono w-30" name="douban_id">', '<input type="text" class="text-s mono w-50" name="douban_image">')
		);
		$source = new Typecho_Widget_Helper_Form_Element_Radio('source', $arraySource, 'Collection', _t('信息来源'));
		$form->addInput($source->multiMode());

		$parent = new Typecho_Widget_Helper_Form_Element_Text('parent', NULL, NULL, _t('父记录ID'));
		$parent->input->setAttribute('class', 'w-30 mono');
		$form->addInput($parent);

		$arrayStatus = array(
			'do' => '进行',
			'collect' => '完成',
			'wish' => '计划',
			'on_hold' => '搁置',
			'dropped' => '抛弃'
		);
		$status = new Typecho_Widget_Helper_Form_Element_Radio('status', $arrayStatus, 'wish', _t('记录当前状态'));
		$form->addInput($status);

		$rate = new Typecho_Widget_Helper_Form_Element_Text('rate', NULL, NULL, _t('评价'), _t('请使用0-10的数字表示'));
		$rate->addRule('isInteger', _t('请使用0-10的数字表示'));
		$form->addInput($rate);

		$tags = new Typecho_Widget_Helper_Form_Element_Text('tags', NULL, NULL, _t('标签'), _t('请使用空格分隔'));
		$form->addInput($tags);

		$comment = new Typecho_Widget_Helper_Form_Element_Textarea('comment', NULL, NULL, _t('评论'));
		$form->addInput($comment);

		$submit = new Typecho_Widget_Helper_Form_Element_Submit('submit', NULL, _t('添加记录'));
		$submit->input->setAttribute('class', 'btn primary');
		$form->addItem($submit);

		return $form;
	}

	public function addSubject()
	{
		if(!$this->formNew()->validate())
		{
			switch($this->request->source)
			{
				case 'Bangumi':
					$image = $this->request->bangumi_image;
					$subject_id = $this->request->bangumi_id;
					break;
				case 'Douban':
					$image = $this->request->douban_image;
					$subject_id = $this->request->douban_id;
					break;
				default:
					$image = $this->request->input_image;
					$subject_id = NULL;
			}
			if($this->request->source != 'Collection')
				$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('source = ?', $this->request->source)->where('subject_id = ?', $subject_id));
			else
				$row_temp = false;
			if($row_temp)
				$this->widget('Widget_Notice')->set(_t('当前记录已存在'), 'notice');
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
				if(in_array('sp_progress', $progress))
				{
					if(!is_null($this->request->sp_count))
						$sp_count = min(999, max(0, intval($this->request->sp_count)));
					if(!is_null($this->request->sp_status))
						$sp_status = min($sp_count, max(0, intval($this->request->sp_status)));
				}
				else
				{
					$sp_count = NULL;
					$sp_status = NULL;
				}
				$this->_db->query($this->_db->insert('table.collection')->rows(
					array(
						'class' => $this->request->class,
						'type' => $this->request->type,
						'name' => $this->request->name,
						'name_cn' => $this->request->name_cn,
						'image' => $image,
						'ep_count' => $ep_count,
						'sp_count' => $sp_count,
						'source' => $this->request->source,
						'subject_id' => $subject_id,
						'parent' => $this->request->parent,
						'status' => $this->request->status,
						'time_start' => Typecho_Date::gmtTime(),
						'time_finish' => NULL,
						'time_touch' => Typecho_Date::gmtTime(),
						'ep_status' => $ep_status,
						'sp_status' => $sp_status,
						'rate' => $this->request->rate,
						'tags' => $this->request->tags,
						'comment' => $this->request->comment
					)
				));
				$this->widget('Widget_Notice')->set(_t('记录添加成功'), 'success');
			}	
		}
		$this->response->goBack();
	}
}

?>