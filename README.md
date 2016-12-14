#Logcat
Simple yet powerful log analyser, based on PHP

[中文版](https://github.com/questionlin/logcat/blob/master/README_zh.md)

##Installation
```shell
git clone https://github.com/questionlin/logcat.git
git checkout v0.1
# It may cost some time to make index at the first time.
php index.php
```
Change the configure in config.php  
Put the log files in subfolders in data/  
It doesn't matter how many subfolders it has.

##Use
Combine Logcat with apache, nginx or anyother web server. Or you can use PHP's web server
```shell
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
I advise test before put whole log file in data/, by puting one line of logs in it.

##Collect log
If you can't put log files in data/, or there are more than one log source, you need log collectiong.  
Copying whole program to the machine making log files. Change configure in config.json, and then do
```shell
php collector.php &
```
Logcat will collect log automatically.

##License
MIT