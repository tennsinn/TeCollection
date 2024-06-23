<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 设置验证及内容获取
 *
 * @author 两仪天心
 * @copyright Copyright (c) 2020 Tennsinn
 * @license GNU General Public License v3.0
 */
class Collection_Config extends Typecho_Widget
{
	/**
	 * 条目字段字典
	 *
	 * @access public
	 * @var array
	 */
	public $dictColumn = array('id' => 'ID', 'category' => '大类', 'class' => '分类', 'type' => '类型', 'name' => '原名', 'name_cn' => '译名', 'image' => '封面', 
		'ep_count' => '子集总数', 'ep_start' => '子集起始', 'author' => '作者', 'publisher' => '出版商', 'published' => '出版时间', 'source' => '信息来源', 'source_id' => '来源ID', 'media_link' => '媒体链接', 
		'parent' => '关联记录', 'parent_order' => '关联顺序', 'parent_label' => '关联标签', 'grade' => '显示分级', 'status' => '状态', 'time_start' => '开始时间', 
		'time_finish' => '结束时间', 'time_touch' => '修改时间', 'ep_status' => '当前进度', 'rate' => '评价', 'tags' => '标签', 'comment' => '评论', 'note' => '备注');

	/**
	 * 状态字典，全类
	 *
	 * @access public
	 * @var array
	 */
	public $dictStatusAll = array(
		'all' => array('全部', '书籍', '动画', '音乐', '游戏', '演出', '影视'),
		'do' => array('进行', '在读', '在看', '在听', '在玩', '在看', '在看'),
		'collect' => array('完成', '读过', '看过', '听过', '玩过', '看过', '看过'),
		'wish' => array('计划', '想读', '想看', '想听', '想玩', '想看', '想看'),
		'on_hold' => array('搁置', '搁置', '搁置', '搁置', '搁置', '搁置', '搁置'),
		'dropped' => array('抛弃', '抛弃', '抛弃', '抛弃', '抛弃', '抛弃', '抛弃')
	);

	/**
	 * 状态字典，基本
	 *
	 * @access public
	 * @var array
	 */
	public $dictStatus = array('do' => '进行', 'collect' => '完成', 'wish' => '计划', 'on_hold' => '搁置', 'dropped' => '抛弃');

	/**
	 * 大类字典
	 *
	 * @access public
	 * @var array
	 */
	public $dictCategory = array('series' => '系列', 'subject' => '记录', 'volume' => '分卷', 'episode' => '章节');

	/**
	 * 分类字典
	 *
	 * @access public
	 * @var array
	 */
	public $dictClass = array(1 => '书籍', 2 => '动画', 3 => '音乐', 4 => '游戏', 5 => '演出', 6 => '影视');

	/**
	 * 类型字典
	 *
	 * @access public
	 * @var array
	 */
	public $dictType = array(
		1 => array('Novel' => '小说', 'Comic' => '漫画', 'Doujinshi' => '同人志', 'Textbook' => '教材'),
		2 => array('TV' => 'TV', 'Movie' => '剧场', 'OVA' => 'OVA', 'OAD' => 'OAD', 'SP' => 'SP', 'WEB' => 'WEB'),
		3 => array('Album' => '专辑', 'Single' => '单曲', 'Maxi' => 'Maxi', 'EP' => '细碟', 'Selections' => '选集'),
		4 => array('iOS' => 'iOS', 'Android' => 'Android', 'PSP' => 'PSP', 'PSV' => 'PSV', 'PS4' => 'PS4', 'NDS' => 'NDS', '3DS' => '3DS', 'NSwitch' => 'NSwitch', 'XBox' => 'XBox', 'Windows' => 'Windows', 'Online' => '网游', 'Table' => '桌游'),
		5 => array('Radio' => '广播', 'Drama' => '话剧', 'Opera' => '歌剧', 'TalkShow' => '脱口秀', 'Musical' => '歌舞', 'Circus' => '马戏', 'Acrobatics' => '杂技'),
		6 => array('Film' => '电影', 'Teleplay' => '电视剧', 'Documentary' => '纪录片', 'TalkShow' => '脱口秀', 'VarietyShow' => '综艺')
	);

	/**
	 * 源字典
	 *
	 * @access public
	 * @var array
	 */
	public $dictSource = array(
		'Collection' => array('name' => '收藏', 'url' => NULL, 'search' => false),
		'Bangumi' => array('name' => 'Bangumi', 'url' => 'http://bgm.tv/subject/', 'search' => array('全部','书籍','动画','音乐','游戏','演出','影视')),
		'Douban' => array('name' => '豆瓣', 'url' => 'https://www.douban.com/subject/', 'search' => false),
		'Steam' => array('name' => 'Steam', 'url' => 'http://store.steampowered.com/app/', 'search' => false),
		'Wandoujia' => array('name' => '豌豆荚', 'url' => 'http://www.wandoujia.com/apps/', 'search' => false),
		'TapTap' => array('name' => 'TapTap', 'url' => 'https://www.taptap.com/app/', 'search' => false),
		'BiliBili' => array('name' => 'BiliBili', 'url' => 'https://www.bilibili.com/bangumi/media/', 'search' => false),
		'BiliManga' => array('name' => 'BiliBili漫画', 'url' => 'https://manga.bilibili.com/detail/', 'search' => false),
		'KuaiKan' => array('name' => '快看漫画', 'url' => 'https://www.kuaikanmanhua.com/web/topic/', 'search' => false),
	);

	/**
	 * 分级名称字典
	 *
	 * @access public
	 * @var array
	 */
	public $dictGrade = array('公开', '私密', '绝密', '禁忌');

	/**
	 * 评价名称字典
	 *
	 * @access public
	 * @var array
	 */
	public $dictRate = array('0 未评价', '1 不忍直视', '2 很差', '3 差', '4 较差', '5 不过不失', '6 还行', '7 推荐', '8 力荐', '9 神作', '10 超神作');

	/**
	 * 源字典，仅名称
	 *
	 * @access public
	 * @return array
	 */
	public function ___dictSourceName()
	{
		$dictSourceName = array();
		foreach($this->dictSource as $key => $value)
			$dictSourceName[$key] = $value['name'];
		return $dictSourceName;
	}

	/**
	 * 条目字段数组
	 *
	 * @access public
	 * @return array
	 */
	public function ___arrayColumn()
	{
		return array_keys($this->dictColumn);
	}

	/**
	 * 允许批量条目字段数组
	 *
	 * @access public
	 * @return array
	 */
	public function ___batchColumn()
	{
		return array_diff($this->arrayColumn, array('id','name','status','time_touch'));
	}

	/**
	 * 状态数组
	 *
	 * @access public
	 * @return array
	 */
	public function ___arrayStatus()
	{
		return array_keys($this->dictStatus);
	}

	/**
	 * 大类名称数组
	 *
	 * @access public
	 * @return array
	 */
	public function ___arrayCategory()
	{
		return array_keys($this->dictCategory);
	}

	/**
	 * 类型名称数组
	 *
	 * @access public
	 * @return array
	 */
	public function ___arrayType()
	{
		$arrayType = array();
		foreach($this->dictType as $key => $value)
			$arrayType = array_merge($arrayType, array_keys($value));
		return $arrayType;
	}

	/**
	 * 源名称数组
	 *
	 * @access public
	 * @return array
	 */
	public function ___arraySource()
	{
		return array_keys($this->dictSource);
	}

	/**
	 * 字段js字典
	 *
	 * @access public
	 * @return string
	 */
	public function ___jsColumn()
	{
		return self::transArrayToJs($this->dictColumn);
	}

	/**
	 * 大类js字典
	 *
	 * @access public
	 * @return string
	 */
	public function ___jsCategory()
	{
		return self::transArrayToJs($this->dictCategory);
	}

	/**
	 * 分类js字典
	 *
	 * @access public
	 * @return string
	 */
	public function ___jsClass()
	{
		return self::transArrayToJs($this->dictClass);
	}

	/**
	 * 类型js字典
	 *
	 * @access public
	 * @return string
	 */
	public function ___jsType()
	{
		return self::transArrayToJs($this->dictType);
	}

	/**
	 * 源js字典
	 *
	 * @access public
	 * @return string
	 */
	public function ___jsSource()
	{
		return self::transArrayToJs($this->dictSource);
	}

	/**
	 * 分级js字典
	 *
	 * @access public
	 * @return string
	 */
	public function ___jsGrade()
	{
		return self::transArrayToJs($this->dictGrade);
	}

	/**
	 * 评价js字典
	 *
	 * @access public
	 * @return string
	 */
	public function ___jsRate()
	{
		return self::transArrayToJs($this->dictRate);
	}

	/**
	 * 状态分类js字典
	 *
	 * @access public
	 * @return string
	 */
	public function ___jsStatusAll()
	{
		return self::transArrayToJs($this->dictStatusAll);
	}

	/**
	 * 数组转为Js数组
	 *
	 * @access public
	 * @param array $dict 待转换数组
	 * @return string
	 */
	public static function transArrayToJs($dict)
	{
		$str = '{';
		foreach($dict as $key => $value)
		{
			if(is_array($value))
			{
				$str .= '"'.$key.'":';
				$str .= self::transArrayToJs($value);
				$str .= ',';
			}
			else
				$str .= '"'.$key.'":"'.str_replace('"', '\"', $value).'",';
		}
		$str .= '}';
		return $str;
	}
}
?>
