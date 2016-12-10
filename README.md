#Logcat
简单好用的日志分析套件，基于PHP

##安装
```shell
git clone https://github.com/questionlin/logcat.git
```
在 config.php 填写配置信息
将日志文件放入 data 目录的子目录下，可以全放到一个子目录或者多个，程序会自动检测录入。

##使用
将程序接入 apache, nginx 等 web 服务器或者使用 php 内置的 web 服务器
```shell
php -S localhost:8080 index.php &
```
浏览器打开网址后就可以开始使用了。

##注意
第一次访问的时候会给所有日志建立索引，如果格式表达式错误，会在data 目录下输出错误行。建议先取一行日志测试成功后再大量导入日志。
建立索引时间取决于日志文件的大小和数量。当日志文件有更新时，程序会自动更新索引。

##收集日志
如果日志文件无法直接放在 data 目录下，或者有多个日志来源，则需要收集日志。
将程序拷贝到生成日志的主机上，填写 config.php 配置，执行命令
```shell
php sender.php &
```
程序将自动发送并收集日志

##License
MIT