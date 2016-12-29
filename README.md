#Logcat
Simple yet powerful log analyser, based on PHP

[中文版](https://github.com/questionlin/logcat/blob/master/README_zh.md)

##Overview
Logcat is a log analyzation platform. It's not only for log files, but also for all structed text files.  
Logcat makes index for all files, and enables you search logs in a way like using SQL( count(),sum()... from... where )  
This version uses swoole to speed up by costing more momery and CPU. If you mind it, checkout branch sapi.( Branch sapi is stoped because the plugin system is different )

##Requirement
[swoole](https://github.com/swoole/swoole-src)( Needs cygwin or BashOnWindows in windows )

##Installation
```shell
git clone https://github.com/questionlin/logcat.git
```
Logcat contains a demo data. If you only want to see the demo, skip this part.  
Change the configuration in config.json  
[click here](https://github.com/questionlin/logcat/blob/master/docs/config.md) to see the explaination.  
Put the log files in subfolders in data/  
It doesn't matter how many subfolders it has.

##Usage
Type the following code to make index for the first time. It may cost some time.
```shell
php index.php &
```
You can use it by open a broswer and typeing 127.0.0.1:8080.

##Performance
PHP version：5.5  
CPU：2.7GHz I5  
log files：22 files, 2.56G totally  
making index costs 283sec  
one full search costs：3sec  
( excluding compiling time, full search costs 16sec in branch sapi, PHP7 is much faster )

##Debug
If the logformat is wrong, it will write wrong lines in data/error.log.  
I advise to put one line of logs and test before put whole log file in data/.

##Collect log
If you can't put log files in data/, or there are more than one log source, you need log collection.  
Change configuration in config.json, copy whole program to the machine making log files, and then type
```shell
php collector.php &
```
Logcat will collect log automatically.

##Plugin
The main part of Logcat concentrates on making index and filter logs, leaves other parts to plugins.  
[click here](https://github.com/questionlin/logcat/blob/master/docs/plugin.md) to see how to make plugins.

##License
MIT
