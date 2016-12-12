#Logcat
简单好用的日志分析套件，基于PHP

##安装
```shell
git clone https://github.com/questionlin/logcat.git
#第一次建立索引，所需所需时间可能较久，请耐心等待。
#每次请求程序会自动更新索引
php index.php
```
在 config.php 填写配置信息
将日志文件放入 data 目录的子目录下，可以全放到一个子目录或者多个，程序会自动检测录入。

##使用
将程序接入 apache, nginx 等 web 服务器或者使用 php 内置的 web 服务器
```shell
php -S localhost:8080 index.php &
```
浏览器打开网址后就可以开始使用了。

##性能
PHP 版本：7
CPU：2.4GHz
日志文件：22个文件一共2.56G
索引建立时间：41.131秒
一次搜索时间：5.587秒
（未包含PHP编译时间，请使用 opcache 加速）

##debug
如果格式表达式错误，会在 data 目录下输出错误行。建议先取一行日志测试成功后再大量导入日志。

##收集日志
如果日志文件无法直接放在 data 目录下，或者有多个日志来源，则需要收集日志。
将程序拷贝到生成日志的主机上，填写 config.php 配置，执行命令
```shell
php sender.php &
```
程序将自动发送并收集日志

##License
MIT