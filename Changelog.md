# Changelog

## v1.20

- v1.20.0 change grade to totally 4 levels

## v1.19

- v1.19.8 separate the different parts of Panel to individual files
- v1.19.7 move the volumn control of batch modification to Config
- v1.19.6 auto change the options based on the selection of the column
- v1.19.5 fix the status display when class is NULL
- v1.19.4 use a variable to store the options of select input in html format
- v1.19.3 exclude status and time_touched from the batch modification
- v1.19.2 update the style of the dropdown menu in the manage panel
- v1.19.1 add translation for the name of columns
- v1.19.0 add the basic function of batch modification

## v1.18

- v1.18.6 fix the display of status after the item edited
- v1.18.5 add setting of grade level to the plugin config to control the output of collection
- v1.18.4 display subject status in item list
- v1.18.3 move the setting of grade from plugin config to class Config
- v1.18.2 fix the issue of jumping to category subject when searching for items
- v1.18.1 add setting of page size to the plugin config
- v1.18.0 fix the issue of redirecting to the unkown page after edits status

## v1.17

- v1.17.4 fix the issue of grade validation in plugin config
- v1.17.3 remove redundant condition sentences and replace the deprecated time function
- v1.17.2 move all validation rules to the function validator
- v1.17.1 allow validating the form with an external defined validator
- v1.17.0 add an extended Form class

## v1.16

- v1.16.5 optimize the default template
- v1.16.4 update the js arrays in the default template
- v1.16.3 update the source of 3rd party scripts
- v1.16.2 replace the date formatting function with Moment.js
- v1.16.1 fix the subjects selection error in rendering the default template
- v1.16.0 update the 3rd party scripts

## v1.15

- v1.15.2 add parser for the staff info of source Bangumi subjects
- v1.15.1 add class Validate to validate all the submission values
- v1.15.0 move the basic arrays to Config and use Typecho widget factory method to load the configs

## v1.14

- v1.14.6 fix the auto-fill of field published for source Bangumi
- v1.14.5 add data column of media link; move adpater exception to function getAdpater
- v1.14.4 add data column of author; block the upgrade process for version below v1.14.0
- v1.14.3 add upgrade feature
- v1.14.2 rebuild the plugin installation entry
- v1.14.1 add the description for Plugin file; optimize the setting config; add the data removal instruction
- v1.14.0 move DB creation function and SQL to individual files; add support for other DB adapter

## v1.13

- v1.13.10 add description for functions in Config
- v1.13.9 set the default value with get method for Panel
- v1.13.8 add getLink func for source Bangumi
- v1.13.7 change Bangumi API server url to const var
- v1.13.6 change the name of Source classes
- v1.13.5 fix the redirection of action editStatus
- v1.13.4 use air_date as field published for source Bangumi
- v1.13.3 fix access description
- v1.13.2 optimize part of CN string with _t func
- v1.13.1 optimize the subject edit form
- v1.13.0 move the processing of the source to individual class

## v1.12

- v1.12.0 add config for label of grade

## v1.11

- v1.11.2 delete the invalid code for Douban API
- v1.11.1 fix the lose of plusEp event after the submission of edit
- v1.11.0 cancel the support of sp progress

## v1.10

- v1.10.2 add action and file entry security check
- v1.10.1 add version info in table.options
- v1.10.0 add publish info and parent label

## v1.9

- v1.9.1 增加管理页category筛选菜单，修正查询条件错误
- v1.9.0 重定义series条目相关项默认值，修正编辑表单返回判断问题

## v1.8

- v1.8.7 修正输入grade0值错误问题，优化输入表单
- v1.8.6 修改grade等级序列
- v1.8.5 整理类型等字典列表输出
- v1.8.4 修正新增-输入中类型切换问题
- v1.8.3 修改Bangumi链接
- v1.8.2 修改subject_id为source_id
- v1.8.1 增加parent_order
- v1.8.0 增加记录分类series/subject/volume/episode

## v1.7

- v1.7.4 增加BiliBili链接
- v1.7.3 修改新增默认选项为输入
- v1.7.2 设置输入页默认类型
- v1.7.1 豆瓣api失效，关闭相关搜索选项
- v1.7.0 删除后台模板关联显示

## v1.6

- v1.6.0 增加关联记录显示，其他显示修正

## v1.5

- v1.5.0 修正记录添加错误，增加记录关联显示，删除默认type

## v1.4

- v1.4.8 修正名称筛选问题，增加备注字段，撤销series分类
- v1.4.7 增强管理面板筛选功能
- v1.4.6 修改记录type&source类型，修正完成时间错误，增加管理面板记录搜索功能
- v1.4.5 预设输出模板修正
- v1.4.4 修正评价显示，修正多条件筛选错误，来源及类型修改
- v1.4.3 模板进度显示修正，模板筛选修正，管理编辑显示修正
- v1.4.2 增加展示模板列表显示动画选项
- v1.4.1 修正输入面版表格记录验证错误，修改输入面版父记录获取，其他细节修正
- v1.4.0 修改插件数据表，增加显示分级，增加系列标识，展示模板增加分类搜索，部分内容修正

## v1.3

- v1.3.0 增加手动录入，增加豆瓣搜索，删除Bangumi同步

## v1.2

- v1.2.1 修正状态记录，修正前台模板
- v1.2.0 修改class、type字段，增加编辑内容，修正status修改

## v1.1

- v1.1.3 增加抛弃时间，修正编辑问题
- v1.1.2 记录时间修正，封面显示修正
- v1.1.1 增加notes字段，扩展type至8种
- v1.1.0 更改插件名称为Collection，增加SP进度调整
