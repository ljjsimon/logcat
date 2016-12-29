##overview
The main part of Logcat Maps each log file to a child process, which turns each line into an array. Plugins in child process get and filter that array, return result to parent process. Main part calls Reduce function offered by plugin with all results, then returns final data.

##files
There is a demo in plugin/map. Logcat puts all plugins in plugin/ like that. And this introduction takes the map plugin as example.

##backend
Your main PHP class should be named the same as your plugin name with the first letter in upper case. And it should extend from class Log which is in lib/Log.php.  
These are functions you should overwrite in your main class:
- getHtml(), read and return your html file
- filterInput($input), filter $_GET and $_POST
- beforeGet(), initialize your result data
- getFields($fields), you get an array combined one line of log with the name of every part of it
- getReduceData(), return your result data. Be aware it's from one file
- reduceLog($results), deal with the results array from all files, and return your final result. Be aware in this process, the data initialized in beforeGet() is empty

In getFields($fields), when the log is
```
123.65.150.10 - - [23/Aug/2016:14:50:59 +0800] "POST /wordpress3/wp-admin/admin-ajax.php?id=1234 HTTP/1.1" 200 2 "http://www.example.com/wordpress3/wp-admin/post-new.php" "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.472.25 Safari/534.3"
```
and the logFormatAs in config.json is
```
  "logFormatAs": [
    "x_forward_ip",
    "host",
    "server_ip",
    "time",
    "method",
    "url_path",
    "url_query",
    "http_protocol",
    "http_status",
    "body_bytes_sent",
    "http_referer",
    "http_user_agent"
  ]
```
this $fields is
```
[
    "x_forward_ip"=> '123.65.150.10',
    "host"=> '-',
    "server_ip"=> '-',
    "time"=> '23/Aug/2016:14:50:59 +0800',
    "method"=> 'POST',
    "url_path"=> '/wordpress3/wp-admin/admin-ajax.php',
    "url_query"=> 'id=1234',
    "http_protocol"=> 'HTTP/1.1',
    "http_status"=> '200',
    "body_bytes_sent"=> '2',
    "http_referer"=> 'http://www.example.com/wordpress3/wp-admin/post-new.php',
    "http_user_agent"=> 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.472.25 Safari/534.3'
]
```
you can either add the field to your result data, or skip this line.

##frontend
The main part of Logcat frontend relaies on [jQuery](jquery.com) [echarts](https://github.com/ecomfe/echarts) [vue](https://github.com/vuejs/vue) [element](https://github.com/ElemeFE/element). All these files are preloaded with header and footer html. However, you don't have to use them. Just include anything you need in your html.

##config
add the information of your plugin to the configuration in config.json