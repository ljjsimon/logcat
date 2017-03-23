#Logcat
简单好用的日志分析套件，基于PHP  
[例子截图](https://github.com/questionlin/logcat/blob/master/docs/demo.png)

##简介
Logcat 是一个日志分析套件。但是不限于日志文件，Logcat 可以分析所有有特定结构的文本文件。  
Logcat 通过给文件建立索引，使使用者能像使用 SQL 一样搜索日志。  
这个版本使用了 swoole 来加速搜索，但是也因此使用了更多的内存和 CPU。如果你对此比较在意，请使用 sapi 分支（ sapi 分支已经停止开发，因为和主版本的插件系统不兼容 ）

![预览图片](http://https://github.com/questionlin/logcat/tree/master/docs/overview.png)

##依赖
[swoole](https://github.com/swoole/swoole-src)（ 在 windows 下需要 cygwin 或 BashOnWindows )

##安装
```shell
git clone https://github.com/questionlin/logcat.git
```
Logcat 包含了一个 demo 数据。如果你只想看 demo，可以跳过这个部分。  
在 config.php 填写配置信息  
[点击这里](https://github.com/questionlin/logcat/blob/master/docs/config_zh.md) 来看如何配置  
将日志文件放入 data 目录的子目录下，可以全放到一个子目录或者多个，程序会自动检测录入。

##使用
输入下面的命令来建立索引。第一次建立可能需要比较久的时间。
```shell
php index.php &
```
浏览器打开 127.0.0.1:8080 后就可以开始使用了。

##性能
PHP 版本：5.5  
CPU：2.7GHz I5  
日志文件：22个文件一共2.56G  
索引建立时间：283秒  
一次全搜索时间：10秒  
（ 未包含PHP编译时间，在单进程情况下耗时16秒，PHP7 快得多 ）

##debug
如果格式表达式错误，会在 data 目录下输出错误行。建议先取一行日志测试成功后再大量导入日志。

##收集日志
如果日志文件无法直接放在 data 目录下，或者有多个日志来源，则需要收集日志。  
将程序拷贝到生成日志的主机上，填写 config.json 配置，执行命令
```shell
php collector.php &
```
程序将自动发送并收集日志

##插件
Logcat 的主体专注于索引和过滤日志，其他交给插件。
[点击这里](https://github.com/questionlin/logcat/blob/master/docs/plugin_zh.md) 查看如何开发插件。

##License
MIT