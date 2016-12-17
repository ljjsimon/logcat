```
{
  /* main folder of all log files
   * put all log files in subfolders of this folder
   */
  "dataDir": "data",

  /* the format of each line of logs
   * every part comes after a '%'
   * %string stands for string with empty character( space etc.), %str stand for string without empty character
   * nomally %str and %string should be ok. However url_path and url_query comes together in nginx. So you have to point out them specially
   * more explaination is in lib/config.php
   */
  "logFormat": '%string %str %str [%string] "%str %url_path%url_query %str" %number %number "%str" "%string"',

  /* name of each part in one line of logs
   * you will use them when you fill form in frontend
   * it doesn't matter what name it is. For nginx log, just use the official name is OK.
   */
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
  ],

  /* the name of primary key, it's from logFormatAs
   * the index use it as table
   * for nginx log, I advise url_path
   */
  "table": "url_path",

  /* the name of time, it's from logFormatAs
   * this part stands for the time the log was made
   */
  "time": "time",

  /* when there is url in log, you can set this item
   * optional
   * once been set, you can search subparts in url_query
   * for abd.com/?name=ljj&age=16
   * you can search 'url_query.name=' 'ljj' in frontend
   */
  "url_query": "url_query",

  /* timezone
   * optional
   * see http://php.net/manual/en/timezones.php
   * default is Aisa/Shanghai
   */
  "timezone": 'Asia/Shanghai',

  /* log collector
   * optional, collector part, set if you use collector
   */
  "collector": {
    /* path of log file
     */
    "log_file": "nginx_access.log",

    /* id of collector
     */
    "collector_id": 1,

    /* http address of server
     * collector will send new log to server via http by post
     */
    "server_addr": "http://localhot:8080"
  },

  /* collect server
   * optional, server part, set if you use collector
   * key stands for collector_id
   * value stand for log file server writes when gets log
   * log file should be in the subfolder of dataDir, you should make subfolder yourself
   * you can put as many collectors as you like
   */
  "collector_log_file": {
    "1": "nginx1/nginx_access.log"
  }

  /* plugin part */
  "plugin": {
    /* the key is the name of the plugin
     * your main PHP class should be named the same as it with the first letter in upper case 
     */
    "map": {
      /* path where the main PHP file is
       */
      "file": "plugin/logcat_map/map.php",
      /* title of the plugin, which appers in frontend
       */
      "title": "地图"
    }
  }
}
```