##files
There is a demo in plugin/map. Logcat puts all plugins in plugin/ like that. And this introduction takes the map plugin as example.

##frontend
The main part of Logcat frontend relaies on [jQuery](jquery.com) [echarts](https://github.com/ecomfe/echarts) [vue](https://github.com/vuejs/vue) [element](https://github.com/ElemeFE/element). So all these files are preloaded. However, you don't have to use them. Just include anything you need in your html.

##backend
Your main PHP class should be named the same as your plugin name with the first letter in upper case. And it should extend from class Log which is in lib/Log.php.  
There are functions you can overwrite in your main class:
- getHtml() where you read your html file and return it
- filterInput() where you can filter $_GET and $_POST
- beforeGet() where you can initialize your result array
- getFields($fields) where you get an array combined one line of log with the name of every part of it
- got() where you can deal with the result array, and you munst return it

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
you can either add the field to your result array, or skip this line.

##config
add the information of your plugin to the configuration in config.json