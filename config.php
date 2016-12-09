<?php
$config = [
  /* 放置日志文件的总目录
   * 将日志文件放在此目录下的子目录下
   */
  "dataDir" => "data",

  /* 日志文件每行对应的格式
   * 转义字符以%开始，意义参见lib/config.php
   * 转义字符最终会被替换为正则表达式
   */
  "logFormat" => "%ip,? ?%ip? %string %ip - [%string] \"%http_method %http_path%http_query %server_protocol\" %http_status %number \"%string\" %float %number \"%string\"",

  /* 日志格式中每个转义字符字段对应的名称
   * 用来在统计的时候使用
   */
  "logFormatAs" => [
    "client_ip",
    "x_forward_ip",
    "host",
    "server_ip",
    "time",
    "method",
    "http_path",
    "http_query",
    "http_protocol",
    "http_status",
    "body_bytes_sent",
    "http_referer",
    "request_time",
    "request_length",
    "http_user_agent"
  ],

  /* 主索引对应的转义字符字段名称
   * 将每次统计中最常使用的字段定义为主索引
   * nginx 日志推荐 http_path
   */
  "table" => "http_path",

  /* 时间对应的转义字符字段名称
   * 次字段对应该行日志生成的时间
   */
  "time" => "time",

  /* 当日志中有 url 时，可以设置这个字段
   * （可选）
   * 统计时该字段可以被拆开设置搜索条件
   */
  "http_query" => "http_query",

  /* 日志搜集器配置
   * （可选，收集器配置）
   */
  "sender" => [
    /* 日志文件位置
     */
    "log_file" => "a.txt",

    /* 收集器编号
     */
    "sender_id" => 1,

    /* 服务器地址
     * 收集器会把日志文件增量部分通过 http post 发送给服务器
     */
    "server_addr" => "http://localhot:8080"
  ],

  /* 日志收集服务器配置
   * （可选，服务器配置）
   * 数组的 key 对应 收集器的id
   * value 对应将收集到的日志写入的本地文件位置
   * 此位置在 dataDir 下，子目录需要自己建立，只能有一层子目录
   * 有多少个收集器，就在数组里写多少个配置
   */
  "sender_log_file" => [
    "1" => "nginx1/nginx_access.log"
  ]
];
