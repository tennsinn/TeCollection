# Collection

**一个不明意义的Typecho插件**

建立本地收藏记录，并管理与展示

## Instructions

*版本更新请根据需要自行重建数据表*

通过搜索和自主输入建立收藏记录条目信息，可通过Bangumi、豆瓣的api获取部分信息

在管理面版可进行条目状态的更改，并在博客页面进行展示

`Collection_Plugin::render();` 预设模板输出

`action/collection?do=getCollection` 获取公开收藏信息

`$collections = Typecho_Widget::widget('Collection_Action')->showCollection($pageSize);` 根据请求的参数返回相应的记录条目，$pageSize为分页大小（返回格式`array('resault' => ture/false, 'message' => false时的错误信息, 'list' => 记录条目, 'nav' => 分页盒)）