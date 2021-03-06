# xf-admin
thinkphp 封装后台管理系统
===============
> PHP 版本必须大于7
> ES 版本 6.4
> ThinkPHP 版本 >= 5.1

|   开发人员 |   联系方式 |
| --- | --- |
|  赵先方  |  1748331509@qq.com   |

|   包含功能 |   模块&插件 |
| --- | --- |
| Pjax | Pjax  |
| 表格 | bootstrap-table 插件封装  |
| QQ登录 | zxf\Qqlogin  |
| 微博登录 | zxf\sina  |
| 逆波兰式计算(RPN) | util\sina  |
| redis | util\Redis  |
| ElasticSearch | Elasticsearch\ClientBuilder,app\common\elastic\service\Doc  |
| 邮件发送 | util\PHPmailer  |
| 拼音生成 | util\Pinyin  |
| Rsa加解密 | util\Rsa  |
| Rsa加解密 | util\Rsa  |
| 地区五级联动 | -- |
| 动态网页截图 | -- |

##联系我
[![avatar](http://wpa.qq.com/pa?p=2:1748331509:51)](http://wpa.qq.com/msgrd?v=3&uin=1748331509&site=qq&menu=yes)

### composer 下载安装
composer create-project zxf/xfadmin xf-admin


创建时间：2018/12/14

***

## 规范
### 目录和文件命名
目录命令使用小写加下划线。
类库、函数文件统一以.php为后缀。
类的文件名均以命名空间定义，并且命名空间的路径和类库文件所在路径一致。
类文件采用驼峰法命名（首字母大写），其它文件采用小写加下划线命名。
类名和类文件名保持一致，统一采用驼峰法命名（首字母大写）。
### 函数和类、属性命名
类的命名采用驼峰法（首字母大写），例如 User。
函数的命名使用小写字母和下划线（小写字母开头）的方式，例如 get_client_ip。
方法的命名使用驼峰法（首字母小写），例如 getUserName。
属性的命名使用驼峰法（首字母小写），例如 tableName、instance。
类名和类文件名保持一致，统一采用驼峰法命名（首字母大写）。
### 常量和配置命名
常量以大写字母和下划线命名，例如 APP_PATH。
配置参数以小写字母和下划线命名，例如 url_route_on。
###数据表和字段命名
数据表和字段采用小写加下划线方式命名，并注意字段名不要以下划线开头，例如 think_user 表和 user_name字段，不建议使用驼峰和中文作为数据表字段命名。
### 参考建议
每个类（不含注释）代码应在200行内，每个方法（不含注释）代码应在20行内，每行末尾不能有空格。
控制器层（controller）中，尽量不出现 if else switch 等流程分支语句。
业务逻辑尽量封装在逻辑层（logic）中，供控制器调用。
数据模型层（model）尽量在逻辑层 logic 中使用，尽量不要再控制器中直接使用model。
数据验证尽量写在验证层（validate）中，供逻辑层调用，尽量不要在控制器中进行数据验证。
API接口尽量根据APP界面实现聚合接口，减少APP接口请求。
