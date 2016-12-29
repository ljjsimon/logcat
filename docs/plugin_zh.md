##简介
Logcat 的主体 Map 每份日志文件到一个子进程，子进程再将每行日志封装成数组。在子进程中的插件得到并且分析数组，把得到的结果传回主进程。主进程把所有文件的分析结果传给插件的 Reduce 函数，得到最终数据。

##文件
在 plugin/map 有一个例子。Logcat 把所有插件放到 plugin/ 目录。这份说明也以这个插件为例子。

##后端
你的主 PHP class 应该和你的插件名一样，首字母大写，要继承自 lib/Log.php 里的 class Log。  
以下是你应该在子类中覆盖的函数：
- getHtml()，读取并返回 html 内容
- filterInput(array $input)，过滤 $_GET 和 $_POST
- beforeGet()，初始化你的数据
- getFields(array $fields)，你得到了用字段名和一行日志组成的数组
- getReduceData()，返回一个文件的分析结果
- reduceLog(array $results)，处理所有文件得到的所有结果，返回最终结果。注意现在在主进程，beforeGet() 函数初始化的数据是空的

在 getFields($fields) 里，当日志文件是
```
123.65.150.10 - - [23/Aug/2016:14:50:59 +0800] "POST /wordpress3/wp-admin/admin-ajax.php?id=1234 HTTP/1.1" 200 2 "http://www.example.com/wordpress3/wp-admin/post-new.php" "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.472.25 Safari/534.3"
```
而在 config.json 里的 logFormatAs 是
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
那么参数 $fields 就是
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
你可以提取你要的数据，活的跳过这一行。

##前端
Logcat 主体部分依赖 [jQuery](jquery.com)，[echarts](https://github.com/ecomfe/echarts)，[vue](https://github.com/vuejs/vue)，[element](https://github.com/ElemeFE/element)。所以以上文件已被连同 html 头尾预加载。但是你不是必须使用它们。在你的 html 里你想加载什么就加载什么。

##配置
把插件的信息加到 config.json 里的 plugin 部分
add the information of your plugin to the configuration in config.json