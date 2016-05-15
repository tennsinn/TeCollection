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
		$this->on($this->request->is('do=getCollection'))->getCollection();
		$this->on($this->request->is('do=getSeries'))->getSeries();
		$this->widget("Widget_User")->pass("administrator");
		$this->on($this->request->is('do=plusEp'))->plusEp();
		$this->on($this->request->is('do=editSubject'))->editSubject();
		$this->on($this->request->is('do=editStatus'))->editStatus();
		$this->on($this->request->is('do=addSubject'))->addSubject();
	}

	private $arrayType = array(
		'Mix', 'Series',
		'Novel', 'Comic', 'Doujinshi', 'Textbook',
		'TV', 'OVA', 'OAD', 'Movie',
		'Album', 'Single', 'Maxi', 'EP', 'Selections',
		'iOS', 'Android', 'PSP', 'PSV', 'PS', 'NDS', '3DS', 'XBox', 'Windows', 'Online', 'Table', 
		'RadioDrama', 'Drama',
		'Film', 'Teleplay', 'TalkShow', 'VarietyShow'
	);

	/**
	 * 对外展示收藏内容
	 *
	 * @return void
	 */
	public function getCollection()
	{
		$query = $this->_db->select(array('COUNT(table.collection.id)' => 'num'))->from('table.collection')->where('grade>=1');
		//两次判断合并尝试
		if(empty(array_diff($this->request->filter('int')->getArray('class'), array(1, 2, 3, 4, 5, 6))))
			$query->where('class='.implode(' OR class=', $this->request->filter('int')->getArray('class')));
		
		if(in_array($this->request->getArray('type'), $this->arrayType))
			$query->where("type='".implode("' OR type='", $this->request->getArray('type'))."'");

		if(empty(array_diff($this->request->getArray('status'), array('do', 'wish', 'finish', 'on_hold', 'dropped'))))
			$query->where("status='".implode("' OR status='", $this->request->getArray('status'))."'");

		$rate = explode(',', $this->request->get('rate'));
		if($rate[0]<=$rate[1] && $rate[0]>=0 && $rate[1]<=10)
			$query->where('rate>='.$rate[0].' AND rate<='.$rate[1]);

		$num = $this->_db->fetchObject($query)->num;
		if(!$num)
			$this->response->throwJson(array('result' => false, 'message' => '存在0条记录'));

		$query = $this->_db->select()->from('table.collection')->where('grade>=1');

		if(empty(array_diff($this->request->filter('int')->getArray('class'), array(1, 2, 3, 4, 5, 6))))
			$query->where('class='.implode(' OR class=', $this->request->filter('int')->getArray('class')));

		if(in_array($this->request->getArray('type'), $this->arrayType))
			$query->where("type='".implode("' OR type='", $this->request->getArray('type'))."'");

		if(empty(array_diff($this->request->getArray('status'), array('do', 'wish', 'finish', 'on_hold', 'dropped'))))
			$query->where("status='".implode("' OR status='", $this->request->getArray('status'))."'");

		if($rate[0]<=$rate[1] && $rate[0]>=0 && $rate[1]<=10)
			$query->where('rate>='.$rate[0].' AND rate<='.$rate[1]);

		if(in_array($this->request->get('orderby'), array('id', 'rate', 'time_touch', 'time_start', 'time_finish')) && in_array($this->request->get('order'), array('DESC', 'ASC')))
			$query->order($this->request->get('orderby'), $this->request->get('order'));

		$rows = $this->_db->fetchAll($query);
		$this->response->throwJson(array('result' => true, 'count' => $num, 'list' => $rows));
	}

	/**
	 * 获取系列条目
	 *
	 * @return void
	 */
	public function getSeries()
	{
		$query = $this->_db->select('id', 'name')->from('table.collection')->where("type='Series'")->order('id', Typecho_Db::SORT_DESC);
		$rows = $this->_db->fetchAll($query);
		$this->response->throwJson($rows);
	}

	/**
	 * 进度增加
	 *
	 * @return void
	 */
	private function plusEp()
	{
		if(!$this->request->get('id') || ($this->request->get('plus') != 'ep' && $this->request->get('plus') != 'sp'))
			$this->response->throwJson(array('result' => false, 'message' => '缺少必要信息'));
		$row = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('id = ?', $this->request->id));
		if($this->request->plus == 'ep')
		{
			if(($row['ep_status']+1) < $row['ep_count'] || $row['ep_count'] == 0)
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
			if(($row['sp_status']+1) < $row['sp_count'] || $row['sp_count'] == 0)
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
	 * 记录信息编辑
	 *
	 * @return void
	 */
	private function editSubject()
	{
		if(!$this->request->get('id'))
			$this->response->throwJson(array('success' => false, 'message' => '缺少ID信息'));

		if(!is_numeric($this->request->get('class')) || $this->request->class<=0 || $this->request->class>6)
			$this->response->throwJson(array('success' => false, 'message' => '种类信息错误'));

		if(!in_array($this->request->get('type'), $this->arrayType))
			$this->response->throwJson(array('success' => false, 'message' => '类型信息错误'));

		if(!$this->request->get('name'))
			$this->response->throwJson(array('success' => false, 'message' => '必须输入名称'));

		if(!filter_var($this->request->get('image'), FILTER_VALIDATE_URL))
			$this->response->throwJson(array('success' => false, 'message' => '图片地址错误'));

		if((!is_null($this->request->get('ep_status')) || !is_null($this->request->get('ep_count'))) && (!is_numeric($this->request->ep_status) || !is_numeric($this->request->ep_count) || $this->request->ep_status<0 || $this->request->ep_count<0 || ($this->request->ep_count>0 && $this->request->ep_status>$this->request->ep_count)))
			$this->response->throwJson(array('success' => false, 'message' => '请输入正确的本篇进度'));

		if((!is_null($this->request->get('sp_status')) || !is_null($this->request->get('sp_count'))) && (!is_numeric($this->request->sp_status) || !is_numeric($this->request->sp_count) || $this->request->sp_status<0 || $this->request->sp_count<0 || ($this->request->sp_count>0 && $this->request->sp_status>$this->request->sp_count)))
			$this->response->throwJson(array('success' => false, 'message' => '请输入正确的特典进度'));

		if(!in_array($this->request->get('source'), array('Collection', 'Bangumi', 'Douban')))
			$this->response->throwJson(array('success' => false, 'message' => '来源信息错误'));

		if($this->request->get('parent') && !is_numeric($this->request->parent) && $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('id = ?', $this->request->parent)))
			$this->response->throwJson(array('success' => false, 'message' => '父记录错误或不存在父记录'));

		if(!is_numeric($this->request->get('grade')) || $this->request->grade<0 || $this->request->grade>9)
			$this->response->throwJson(array('success' => false, 'message' => '请用0-9的数字表示级别'));

		if($this->request->get('rate') && (!is_numeric($this->request->get('rate')) || $this->request->rate>10 || $this->request->rate<0))
			$this->response->throwJson(array('success' => false, 'message' => '评价请使用0-10的数字表示'));

		$row = array(
			'class' => $this->request->class,
			'type' => $this->request->type,
			'name' => $this->request->name, 
			'name_cn' => $this->request->name_cn,
			'image' => $this->request->image,
			'ep_count' => $this->request->ep_count,
			'sp_count' => $this->request->sp_count,
			'source' => $this->request->source,
			'subject_id' => $this->request->subject_id,
			'parent' => $this->request->parent,
			'grade' => $this->request->grade,
			'time_touch' => Typecho_Date::gmtTime(),
			'ep_status' => $this->request->ep_status,
			'sp_status' => $this->request->sp_status,
			'rate' => $this->request->rate,
			'tags' => $this->request->tags,
			'comment' => $this->request->comment
		);
		$json = array('result' => true, 'message' => '修改成功');
		if(($this->request->ep_count > 0 && $this->request->ep_count == $this->request->ep_status) && (is_null($this->request->sp_count) || ($this->request->sp_count > 0 && $this->request->sp_count == $this->request->sp_status)))
		{
			$row['status'] = 'collect';
			$json['status'] = 'collect';
		}
		else
			$json['status'] = $this->request->status;
		$this->_db->query($this->_db->update('table.collection')->where('id = ?', $this->request->id)->rows($row));
		$this->response->throwJson($json);	
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
								if($row_temp['sp_count'])
									$row['sp_status'] = $row_temp['sp_count'];
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
				//尝试通过JS传入数据
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
								if($row_temp['sp_count'])
									$row['sp_status'] = $row_temp['sp_count'];
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
										'image' => $response['images']['common']
									);
									if($response['eps'])
									{
										$row['ep_count'] = $response['eps'];
										$row['ep_status'] = 0;
									}
								}
								break;
							case 'Douban':
								$arrayDoubanClass = array('1' => 'book', '3' => 'music', '6' => 'movie/subject');
								$class = $this->request->get('class');
								$response = file_get_contents('https://api.douban.com/v2/'.$arrayDoubanClass[$class].'/'.$subject_id);
								$response = json_decode($response, true);
								if($response)
								{
									switch($class)
									{
										case '1':
											$row = array(
												'name_cn' => $response['alt_title'],
												'image' => $response['images']['medium']
											);
											if(isset($response['origin_title']) && $response['origin_title'])
												$row['name'] = $response['origin_title'];
											else
												$row['name'] = $response['title'];
											if(isset($response['tags']) && $response['tags'])
											{
												$tags = '';
												foreach($response['tags'] as $num => $item)
													$tags .= $item['name'].' ';
												$row['tags'] = $tags;
											}
											if(isset($response['series']) && isset($response['series']['id']) && $row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where("source='Douban'")->where('subject_id = ?', $response['series']['id'])))
												$row['parent'] = $row_temp['id'];
											break;
										case '3':
											$row = array(
												'name' => $response['title'],
												'name_cn' => $response['alt_title'],
												'image' => $response['image']
											);
											if(isset($response['tags']) && $response['tags'])
											{
												$tags = '';
												foreach($response['tags'] as $num => $item)
													$tags .= $item['name'].' ';
												$row['tags'] = $tags;
											}
											if(isset($response['attr']['tracks']) && $response['attr']['tracks'])
												$row['ep_count'] = count(explode('\n', implode('\n', $response['attr']['tracks'])));
											if(isset($response['attr']['version'][0]) && $response['attr']['version'][0] && in_array($response['attr']['version'][0], array('Album', '专辑', 'Single', '单曲', 'EP', '细碟', 'Maxi')))
											{
												$dictMusicType = array(
													'Album' => 'Album', '专辑' => 'Album', 
													'Single' => 'Single', '单曲' => 'Single',
													'EP' => 'EP', '细碟' => 'EP',
													'Maxi' => 'Maxi'
												);
												$row['type'] = $dictMusicType[$response['attr']['version'][0]];
											}
											break;
										case '6':
											$row = array(
												'name_cn' => $response['title'],
												'image' => $response['images']['medium']
											);
											if(isset($response['origin_title']) && $response['origin_title'])
												$row['name'] = $response['origin_title'];
											else
												$row['name'] = $response['title'];
											if(isset($response['genres']) && $response['genres'])
												$row['tags'] = implode(' ', $response['genres']);
											if(isset($response['subtype']) && $response['subtype'] && in_array($response['subtype'], array('movie', 'tv')))
											{
												$dictVideoType = array('movie' => 'Movie', 'tv' => 'Teleplay');
												$row['type'] = $dictVideoType[$response['subtype']];
											}
											if(isset($response['episodes_count']) && $response['episodes_count'])
												$row['ep_count'] = $response['episodes_count'];
											break;
									}
									$row['class'] = $class;
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
									if(isset($row['ep_count']) && $row['ep_count'])
										$row['ep_status'] = $row['ep_count'];
									if(isset($row['sp_count']) && $row['sp_count'])
										$row['sp_status'] = $row['sp_count'];
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
					$this->widget('Widget_Notice')->set('以下记录修改失败：'.json_encode($failure), 'notice');
				else
					$this->widget('Widget_Notice')->set('已修改'.count($subject_ids).'条收藏记录', 'success');
			}
			else
				$this->widget('Widget_Notice')->set('未选中任何项目', 'notice');
		}
		else
			$this->widget('Widget_Notice')->set('未指明收藏状态', 'notice');
		$class = isset($this->request->class) ? $this->request->get('class') : '0';
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Collection%2FPanel.php&class='.$class.'&status='.($status == 'delete' ? 'all' : $status), $this->_options->adminUrl));
	}

	/**
	 * 增加收藏
	 * 
	 * @return void
	 */
	public function addSubject()
	{
		if(!$this->formInput()->validate())
		{
			if($this->request->source != 'Collection')
				$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('source = ?', $this->request->source)->where('subject_id = ?', $subject_id));
			else
				$row_temp = $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('name = ?', $this->request->name));
			if($row_temp)
				$this->widget('Widget_Notice')->set('当前记录已存在', 'notice');
			else
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
					case 'Collection':
					default:
						$image = $this->request->collection_image;
						$subject_id = NULL;
				}
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
				$time_start = NULL;
				$time_finish = NULL;
				if($this->request->status == 'do')
					$time_start = Typecho_Date::gmtTime();
				else
					if($this->request->status == 'collect')
						$time_finish = Typecho_Date::gmtTime();
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
						'grade' => $this->request->grade,
						'status' => $this->request->status,
						'time_start' => $time_start,
						'time_finish' => $time_finish,
						'time_touch' => Typecho_Date::gmtTime(),
						'ep_status' => $ep_status,
						'sp_status' => $sp_status,
						'rate' => $this->request->rate,
						'tags' => $this->request->tags,
						'comment' => $this->request->comment
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
		$class = isset($this->request->class) ? $this->request->get('class') : 0;
		$type = isset($this->request->type) ? $this->request->get('type') : 'all';
		$query = $this->_db->select(array('COUNT(table.collection.id)' => 'num'))->from('table.collection');
		if($status != 'all')
			$query->where('status = ?', $status);
		if($class != 0)
			$query->where('class = ?', $class);
		if($type != 'all')
			$query->where('type = ?', $type);
		$num = $this->_db->fetchObject($query)->num;
		if(!$num)
			return array('result' => false, 'message' => '存在0条记录');
		$page = isset($this->request->page) ? $this->request->get('page') : 1;
		$query = $this->_db->select()->from('table.collection')->order('time_touch', Typecho_Db::SORT_DESC)->page($page, $pageSize);
		if($status != 'all')
			$query->where('status = ?', $status);
		if($class != 0)
			$query->where('class = ?', $class);
		if($type != 'all')
			$query->where('type = ?', $type);
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

	/**
	 * 收藏输入表格
	 * 
	 * @return Typecho_Widget_Helper_Form
	 */
	public function formInput()
	{
		$form = new Typecho_Widget_Helper_Form(Typecho_Common::url('/action/collection', $this->_options->index), Typecho_Widget_Helper_Form::POST_METHOD);
		
		$do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
		$form->addInput($do);
		$do->value('addSubject');

		$dictClass = array(1 => '书籍', 2 => '动画', 3 => '音乐', 4 => '游戏', 5 => '广播', 6 => '影视');
		$class = new Typecho_Widget_Helper_Form_Element_Radio('class', $dictClass, 1, '分类 *');
		$class->addRule('required', '必须选择分类');
		$form->addInput($class);

		$dictType = array(
			'Mix' => '混合', 'Series' => '系列',
			'Novel' => '小说', 'Comic' => '漫画', 'Doujinshi' => '同人志', 'Textbook' => '课本'
		);
		$type = new Typecho_Widget_Helper_Form_Element_Radio('type', $dictType, 'Mix', '类型 *');
		$type->addRule('required', '必须选择类型');
		$form->addInput($type);

		$name = new Typecho_Widget_Helper_Form_Element_Text('name', NULL, NULL, '名称 *');
		$name->addRule('required', '必须填写记录名称');
		$name->input->setAttribute('class', 'w-40 mono');
		$form->addInput($name);

		$name_cn = new Typecho_Widget_Helper_Form_Element_Text('name_cn', NULL, NULL, '译名');
		$name_cn->input->setAttribute('class', 'w-40 mono');
		$form->addInput($name_cn);

		$arraySource = array(
			'Collection' => '手动输入 封面： <input type="text" class="text-s mono w-50" name="collection_image">',
			'Bangumi' => 'Bangumi ID： <input type="text" class="text-s mono w-30" name="bangumi_id"> 封面： <input type="text" class="text-s mono w-50" name="bangumi_image">',
			'Douban' => '豆瓣 ID： <input type="text" class="text-s mono w-30" name="douban_image"> 封面： <input type="text" class="text-s mono w-50" name="douban_id">'
		);
		$source = new Typecho_Widget_Helper_Form_Element_Radio('source', $arraySource, 'Collection', '信息来源');
		$source->addRule('required', '必须选择来源');
		$form->addInput($source->multiMode());

		$arrayProgress = array(
			'ep_progress' => '输入进度一：<input type="text" class="text-s num mono" name="ep_status"> / <input type="text" class="text num text-s" name="ep_count"> ',
			'sp_progress' => '输入进度二：<input type="text" class="text-s num mono" name="sp_status"> / <input type="text" class="text num text-s" name="sp_count"> '
		);
		$progress = new Typecho_Widget_Helper_Form_Element_Checkbox('progress', $arrayProgress, NULL, '进度信息', '选择将要添加的信息，默认为0/0，不选择则认为无进度项');
		$form->addInput($progress->multiMode());

		$parent = new Typecho_Widget_Helper_Form_Element_Text('parent', NULL, 0, '父记录ID');
		$parent->addRule('required', '必须填写父记录ID，无则为0');
		$parent->addRule('isInteger', '父记录ID为数字');
		$parent->addRule('maxLength', '父记录ID最大为10位');
		$parent->input->setAttribute('class', 'w-30 mono');
		$form->addInput($parent);

		$arrayGrade = array(0 => '私密', 1 => '公开');
		$grade = new Typecho_Widget_Helper_Form_Element_radio('grade', $arrayGrade, 1, '显示分级');
		$grade->addRule('required', '必须选择显示分级');
		$form->addInput($grade);

		$arrayStatus = array('do' => '进行', 'collect' => '完成', 'wish' => '计划', 'on_hold' => '搁置', 'dropped' => '抛弃');
		$status = new Typecho_Widget_Helper_Form_Element_Radio('status', $arrayStatus, 'wish', '记录当前状态');
		$status->addRule('required', '必须选择记录状态');
		$form->addInput($status);

		$rate = new Typecho_Widget_Helper_Form_Element_Text('rate', NULL, 0, '评价', '请使用0-10的数字表示，0为无评价');
		$rate->input->setAttribute('class', 'num text-s mono');
		$rate->addRule('required', '必须输入评价');
		$rate->addRule('isInteger', '请使用0-10的数字表示');
		$form->addInput($rate);

		$tags = new Typecho_Widget_Helper_Form_Element_Text('tags', NULL, NULL, '标签', '请使用空格分隔');
		$form->addInput($tags);

		$comment = new Typecho_Widget_Helper_Form_Element_Textarea('comment', NULL, NULL, '评论');
		$form->addInput($comment);

		$submit = new Typecho_Widget_Helper_Form_Element_Submit('submit', NULL, '添加记录');
		$submit->input->setAttribute('class', 'btn primary');
		$form->addItem($submit);

		return $form;
	}
}

?>