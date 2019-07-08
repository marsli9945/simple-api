# simple-api

#### 介绍
The api frame for PHP.
用于提供接口的PHP框架，由于需求问题为多库前提下项目提供API接口框架。

#### 软件架构
自动加载依赖于composer提供的自动加载类，
所以可以很方便的用composer扩展已有类库

#### 安装教程
1. git地址:https://github.com/marsli9945/simple-api.git
2. composer create-project marsli/simple-api **Path** -sdev --no-progress --no-interaction --ansi
2. 通过phpstrom新建
![blockchain](http://pmahf7gv8.bkt.clouddn.com/create.png "区块链")
3. nginx配置
```
server {
    listen       9003;
    server_name  localhost;
    root   project-path/public;
    index  index.html index.php index.htm;
    access_log  log-path/access.log;
    error_log   log-path/error.log;
    location / {
        if (!-e $request_filename) {
        rewrite  ^(.*)$  /index.php$1  last;
        break;
      }
    }
     location ~ \.(php|phar)(/.*)?$ {
      		fastcgi_split_path_info ^(.+\.(?:php|phar))(/.*)$;
      		set $path_info $fastcgi_path_info;
      		fastcgi_param PATH_INFO $path_info;
      		fastcgi_pass   127.0.0.1:9000;
      		fastcgi_index  index.php;
      		include        fastcgi.conf;
      		set $fastcgi_script_name2 $fastcgi_script_name;
      		if ($fastcgi_script_name ~ "^(.+\.php)(/.+)$") {
        		set $fastcgi_script_name2 $1;
        		set $path_info $2;
      		}
      		fastcgi_param   PATH_INFO $path_info;
      		fastcgi_param   SCRIPT_FILENAME   $document_root$fastcgi_script_name2;
      		fastcgi_param   SCRIPT_NAME   $fastcgi_script_name2;
    	}
}
```

4. 在项目根目录下使用composer install 命令安装composer组件

## 使用说明
### 目录结构

```
├── README.md --- 说明文档
├── app --- 使用者所有代码都应在app目录下
│   ├── app 
│   │   ├── controller
│   │   └── model
│   ├── common --- 改分组无法直接访问，用户自己扩展类库存放
│   │   ├── lib --- 第三方扩展
│   │   └── unit --- 用户自己的工具类
│   └── index--- 主分组，只有域名的情况下访问改分组的index控制器的index方法
│       ├── controller --- 控制器目录
|	├── model --- 模型类目录
│       └── view --- 不推荐使用，但提供了简单的视图实现
├── artisan.php --- 框架命令实现文件，提供简单的命令行功能
├── build ---  phar包存放目录
│   └── simple.phar --- 代码最终打好的压缩包
├── composer.json --- PHP包管理描述文件
├── composer.lock --- PHP包管理版本管理文件
├── config  --- 框架配置文件目录
│   ├── config.ini --- 主配置文件
│   ├── database.ini --- 数据库配置
│   └── route.ini --- 路由配置
├── public
│   └── index.php 框架入口，
├── shell  --- 脚本存放目录，仅作为调试用，正式环境用phar
│   └── demo.php--- 提供范例
└── simple --- 框架核心代码，轻易不要改动
    ├── App.php
    ├── Request.php
    ├── Response.php
    ├── Unit.php
    ├── controller
    │   ├── Controller.php
    │   ├── ModelController.php
    │   ├── QueryController.php
    │   └── Restful.php
    ├── db
    │   ├── DB.php
    │   ├── Model.php
    │   ├── Query.php
    │   └── Sql.php
    ├── exception
    │   ├── ApiException.php
    │   └── LoadException.php
    └── strap
        ├── function.php --- 助手函数
        ├── start.php --- 入口文件，框架生命周期由此启动
        └── webStart.php --- 浏览器直接访问.phar的入口，仅做显示，无实际逻辑
```
### 快速入门
#### 配置   
1. 与市面其他框架不同，我们不使用.php而使用.ini文件作为配置文件。    
2. .ini是一种序列化文本文件，作为php的官方配置文件，php本身对其就有专属的解析方法，可以很方便解析为常用的数组形式。   
3. 搭配虚拟控制器功能，可以让不懂PHP的前端同学也能快速上手，实现数据库的API驱动功能。
```
; This is a sample configuration file
; Comments start with ';', as in php.ini

;单库或单主库系统database配置
;model_default_database = default

;自定义错误提示
error_message = 系统错误！请稍后再试～

;是否显示自定义错误提示,关闭会显示系统内部报错信息
show_error_msg = 0

;是否显示打印位置
debug_show_line = 0
```

```
; This is a sample configuration file
; Comments start with ';', as in php.ini
[app] --- 数据库配置名，很重要与分组名对应，一般为库名
dbms         = mysql  --- 数据库类型
host         = 127.0.0.1 --- 数据库地址
port         = 3306 --- 端口
database     = app --- 库名
user         = root --- 用户
password     = li123456 --- 密码
charset      = utf8 --- 指定编码集
prefix       = ims_ --- 表名前缀
```

#### 生成控制器和模型类   
在框架根目录下执行   
```
php artisan.php <group> <controller> --no_model
```

可生成对应的控制器
```
<?php
namespace app\app\controller;
use simple\controller\ModelController;
class Test extends ModelController
{
}
```

和模型类   
```
<?php
namespace app\app\model;
use simple\db\Model;
class Test extends Model
{
}
```

--no_model参数为可选，对模型类没有需求可添加该参数，只生成控制器
```
<?php
namespace app\app1\controller;
use simple\controller\QueryController;
class Test extends QueryController
{
}
```
#### 路由和访问规则
```
http://simple.api.com/<group>/<controller>/<action>
```
1. 三级访问原则   
>>> group 分组名，app目录下的第一级目录名，通常与所访问的数据库配置名一致   
>>> controller 控制器名，controller目录下的控制器类名，通常与所访问的表名一致   
>>> action 方法名，控制器中所访问的方法名，不同的http请求方式会自动判别
2. restful访问规则,用http请求method区分action，只写两级即可
```
'GET'     => 'fetchList',
'POST'    => 'update',
'PUT'     => 'create',
'DELETE'  => 'delete',
'OPTIONS' => 'options'
```
ps: GET请求下action写数字表示请求的是getInfo，表示获取指定ID的数据

#### 助手函数
1. config() 获取配置信息
2. model() 获取模型类实例
3. query() 获取虚拟模型实例
4. debug() 信息打印方法
5. input() 客户端信息获取方法
6. success() 成功数据返回方法
7. error() 错误信息返回方法

### 线上部署
#### 代码打包
```
php artisan.php build <phar_name>

```
phar_name 为包名，缺省值为simple

在线上的项目目录下新建index.php,直接引入压缩包即可

# simple-api
