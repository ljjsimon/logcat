#Logcat
Simple yet powerful log analyser, based on PHP

[中文版](https://github.com/questionlin/logcat/blob/master/README_zh.md)

##Overview
Logcat is a log analyzation platform. It's not only for log files, but also for all structed text files.  
Logcat makes index for all files, and enables you search logs in a way like using SQL( select... from... where ) 

##Installation
```shell
git clone https://github.com/questionlin/logcat.git
```
Logcat contains a demo data. If you only want to see the demo, skip this part.  
Change the configure in config.json  
[click here](https://github.com/questionlin/logcat/blob/master/config.md) to see the explaination.  
Put the log files in subfolders in data/  
It doesn't matter how many subfolders it has.

# It may cost some time to make index at the first time.
php index.php

##Use
Type the following code to make index for thr first time. It may cost some time.
```shell
php index.php
php -S localhost:8080 index.php &
```
You can use it by open a broswer and typeing localhost:8080.

##Performance
PHP version：7  
CPU：2.4GHz  
log files：22 files, 2.56G totally  
making index costs.131sec  
one full search costs：5.587sec  
( excluding compiling time, please use opcache )

##Debug
If the logformat is wrong, it will write wrong lines in data/error.log.  
I advise to put one line of logs and test before put whole log file in data/.

##Collect log
If you can't put log files in data/, or there are more than one log source, you need log collection.  
Copy whole program to the machine making log files. Change configure in config.json, and then type
```shell
php collector.php &
```
Logcat will collect log automatically.

##plugin
The main part of Logcat concentrates on making index and filter logs, leaves other parts to plugins.  
[click here](https://github.com/questionlin/logcat/blob/master/plugin.md) to see how to make plugins.

##License
MIT