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

	private $arrayType = array(
		'Novel', 'Comic', 'Doujinshi', 'Textbook',
		'TV', 'Movie', 'OVA', 'OAD', 'SP',
		'Album', 'Single', 'Maxi', 'EP', 'Selections',
		'iOS', 'Android', 'PSP', 'PSV', 'PS4', 'NDS', '3DS', 'NSwitch', 'XBox', 'Windows', 'Online', 'Table', 
		'RadioDrama', 'Drama',
		'Film', 'Teleplay', 'Documentary', 'TalkShow', 'VarietyShow'
	);
	private $arrayCategory = array('series', 'subject', 'volume', 'episode');

	private $dictCategory = array('series' => '系列', 'subject' => '记录', 'volume' => '分卷', 'episode' => '章节');
	private $dictClass = array(1 => '书籍', 2 => '动画', 3 => '音乐', 4 => '游戏', 5 => '广播', 6 => '影视');
	private $dictType = array('Novel' => '小说', 'Comic' => '漫画', 'Doujinshi' => '同人志', 'Textbook' => '课本');
	private $dictSource = array('Collection' => '无来源', 'Bangumi' => 'Bangumi', 'Douban' => '豆瓣', 'Wandoujia' => '豌豆荚', 'Steam' => 'Steam', 'TapTap' => 'TapTap', 'BiliBili' => 'BiliBili');
	private $dictGrade = array(0 => '公开', 1 => '私密1', 2 => '私密2', 3 => '私密3', 4 => '私密4', 5 => '私密5', 6 => '私密6', 7 => '私密7', 8 => '私密8', 9 => '私密9');
	private $dictStatus = array('do' => '进行', 'collect' => '完成', 'wish' => '计划', 'on_hold' => '搁置', 'dropped' => '抛弃');

	/**
	 * 对外展示收藏内容
	 *
	 * @return void
	 */
	public function getCollection()
	{
		$interCategory = array_intersect($this->request->getArray('category'), $this->arrayCategory);
		$interClass = array_intersect($this->request->filter('int')->getArray('class'), array(1, 2, 3, 4, 5, 6));
		$interType = array_intersect($this->request->getArray('type'), $this->arrayType);
		$interStatus = array_intersect($this->request->getArray('status'), array('do', 'wish', 'collect', 'on_hold', 'dropped'));
		$rate = explode(',', $this->request->get('rate'));
		$minRate = ($rate[0]<=$rate[1] && $rate[0]>=0) ? $rate[0] : '0';
		$maxRate = ($rate[0]<=$rate[1] && $rate[1]<=10) ? $rate[1] : '10';

		$query = $this->_db->select()->from('table.collection')->where('grade = ?', 0);
		$query->where("category='".implode("' OR type='", $interCategory)."'");
		$query->where('class='.implode(' OR class=', $interClass));
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
		if(!$this->request->get('id'))
			$this->response->throwJson(array('result' => false, 'message' => '缺少ID信息'));

		if(!is_null($this->request->get('category')) && !in_array($this->request->get('category'), $this->arrayCategory))
			$this->response->throwJson(array('result' => false, 'message' => '大类信息错误'));

		$category = $this->request->get('category');
		if('series' != $category)
		{
			if(!is_numeric($this->request->get('class')) || $this->request->class<=0 || $this->request->class>6)
				$this->response->throwJson(array('result' => false, 'message' => '种类信息错误'));

			if(!is_null($this->request->get('type')) && !in_array($this->request->get('type'), $this->arrayType))
				$this->response->throwJson(array('result' => false, 'message' => '类型信息错误'));
		}

		if(!$this->request->get('name'))
			$this->response->throwJson(array('result' => false, 'message' => '必须输入名称'));

		if(!filter_var($this->request->get('image'), FILTER_VALIDATE_URL) && !is_null($this->request->get('image')))
			$this->response->throwJson(array('result' => false, 'message' => '图片地址错误'));

		if('series' != $category)
		{
			if((!is_null($this->request->get('ep_status')) || !is_null($this->request->get('ep_count'))) && (!is_numeric($this->request->ep_status) || !is_numeric($this->request->ep_count) || $this->request->ep_status<0 || $this->request->ep_count<0 || ($this->request->ep_count>0 && $this->request->ep_status>$this->request->ep_count)))
				$this->response->throwJson(array('result' => false, 'message' => '请输入正确的主进度'));
		}

		if(!in_array($this->request->get('source'), array('Collection', 'Bangumi', 'Douban', 'Steam', 'Wandoujia', 'TapTap', 'BiliBili')))
			$this->response->throwJson(array('result' => false, 'message' => '来源信息错误'));

		if('series' != $category)
		{
			if($this->request->get('parent') && !is_numeric($this->request->parent) && $this->_db->fetchRow($this->_db->select()->from('table.collection')->where('id = ?', $this->request->parent)))
				$this->response->throwJson(array('result' => false, 'message' => '父记录错误或不存在父记录'));

			if($this->request->get('parent_order') && !is_numeric($this->request->parent_order))
				$this->response->throwJson(array('result' => false, 'message' => '记录序号错误'));
		}

		if(!is_numeric($this->request->get('grade')) || $this->request->grade<0 || $this->request->grade>9)
			$this->response->throwJson(array('result' => false, 'message' => '请用0-9的数字表示级别'));

		if($this->request->get('rate') && (!is_numeric($this->request->get('rate')) || $this->request->rate>10 || $this->request->rate<0))
			$this->response->throwJson(array('result' => false, 'message' => '评价请使用0-10的数字表示'));

		$published = NULL;
		if($this->request->get('published'))
			$published = strtotime($this->request->published) - $this->_options->timezone + $this->_options->serverTimezone;

		if('series' == $category)
			$row = array(
				'category' => 'series',
				'class' => NULL,
				'type' => NULL,
				'name' => $this->request->name,
				'name_cn' => $this->request->name_cn,
				'image' => $this->request->image,
				'publisher' => NULL,
				'published' => NULL,
				'ep_count' => NULL,
				'source' => $this->request->source,
				'source_id' => $this->request->source_id,
				'parent' => 0,
				'parent_order' => 0,
				'parent_label' => NULL,
				'grade' => $this->request->grade,
				'time_touch' => Typecho_Date::gmtTime(),
				'ep_status' => NULL,
				'rate' => $this->request->rate,
				'tags' => $this->request->tags,
				'comment' => $this->request->comment,
				'note' => $this->request->note
			);
		else
			$row = array(
				'category' => $this->request->category,
				'class' => $this->request->class,
				'type' => $this->request->type,
				'name' => $this->request->name,
				'name_cn' => $this->request->name_cn,
				'image' => $this->request->image,
				'publisher' => $this->request->get('publisher'),
				'published' => $published,
				'ep_count' => $this->request->ep_count,
				'source' => $this->request->source,
				'source_id' => $this->request->source_id,
				'parent' => $this->request->parent,
				'parent_order' => $this->request->parent_order,
				'parent_label' => $this->request->get('parent_label'),
				'grade' => $this->request->grade,
				'time_touch' => Typecho_Date::gmtTime(),
				'ep_status' => $this->request->ep_status,
				'rate' => $this->request->rate,
				'tags' => $this->request->tags,
				'comment' => $this->request->comment,
				'note' => $this->request->note
			);

		if($this->request->status == 'do' && ($this->request->ep_count > 0 && $this->request->ep_count == $this->request->ep_status))
		{
			$row['status'] = 'collect';
			$row['time_finish'] = Typecho_Date::gmtTime();
			$json['status'] = 'collect';
		}
		else
			$json['status'] = $this->request->status;
		$update = $this->_db->query($this->_db->update('table.collection')->where('id = ?', $this->request->id)->rows($row));
		if($update > 0)
			$json = array('result' => true, 'message' => '已修改'.$update.'项');
		else
			$json = array('result' => false, 'message' => '数据库更新失败');
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
				$source = $this->request->get('source');
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
								$response = @file_get_contents('http://api.bgm.tv/subject/'.$source_id);
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
							/* case 'Douban':
								$arrayDoubanClass = array('1' => 'book', '3' => 'music', '6' => 'movie/subject');
								$class = $this->request->get('class');
								$response = file_get_contents('https://api.douban.com/v2/'.$arrayDoubanClass[$class].'/'.$source_id);
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
												$dictVideoType = array('movie' => 'Film', 'tv' => 'Teleplay');
												$row['type'] = $dictVideoType[$response['subtype']];
											}
											if(isset($response['episodes_count']) && $response['episodes_count'])
												$row['ep_count'] = $response['episodes_count'];
											break;
									}
									$row['class'] = $class;
								} */
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
							'publisher' => $this->request->get('publisher'),
							'published' => $published,
							'ep_count' => $ep_count,
							'source' => $this->request->source,
							'source_id' => $this->request->source_id,
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
			/* case 'Douban':
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
				break;*/
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
		$form = new Typecho_Widget_Helper_Form($this->_security->getIndex('/action/collection'), Typecho_Widget_Helper_Form::POST_METHOD);
		
		$do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
		$form->addInput($do);
		$do->value('addSubject');

		$category = new Typecho_Widget_Helper_Form_Element_Radio('category', $this->dictCategory, 'subject', '大类 *');
		$category->addRule('required', '必须选择大类');
		$form->addInput($category);

		$class = new Typecho_Widget_Helper_Form_Element_Radio('class', $this->dictClass, 1, '分类 *');
		$class->addRule('required', '必须选择分类');
		$form->addInput($class);

		$type = new Typecho_Widget_Helper_Form_Element_Radio('type', $this->dictType, 'Novel', '类型 *');
		$type->addRule('required', '必须选择类型');
		$form->addInput($type);

		$name = new Typecho_Widget_Helper_Form_Element_Text('name', NULL, NULL, '名称 *');
		$name->addRule('required', '必须填写记录名称');
		$name->input->setAttribute('class', 'text-s w-40');
		$form->addInput($name);

		$name_cn = new Typecho_Widget_Helper_Form_Element_Text('name_cn', NULL, NULL, '译名');
		$name_cn->input->setAttribute('class', 'text-s w-40');
		$form->addInput($name_cn);

		$publisher = new Typecho_Widget_Helper_Form_Element_Text('publisher', NULL, NULL, '出版商');
		$publisher->input->setAttribute('class', 'w-40');
		$form->addInput($publisher);

		$published = new Typecho_Widget_Helper_Form_Element_Text('published', NULL, NULL, '出版时间');
		$published->input->setAttribute('class', 'w-40');
		$form->addInput($published);

		$source = new Typecho_Widget_Helper_Form_Element_Select('source', $this->dictSource, 'Collection', '信息来源 *');
		$source->addRule('required', '必须选择来源');
		$form->addInput($source);

		$source_id = new Typecho_Widget_Helper_Form_Element_Text('source_id', NULL, NULL, '来源ID');
		$source_id->input->setAttribute('class', 'text-s w-30');
		$form->addInput($source_id);

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

		$grade = new Typecho_Widget_Helper_Form_Element_radio('grade', $this->dictGrade, 0, '显示分级');
		$form->addInput($grade);

		$status = new Typecho_Widget_Helper_Form_Element_Radio('status', $this->dictStatus, 'wish', '记录当前状态 *');
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
