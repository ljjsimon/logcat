{
  /* main folder of all log files
   * put all log files in subfolders of this folder
   */
  "dataDir": "data",

  /* the format of each line of logs
   * escape characters(ESC) begin with '%', see lib/config.php for more definition
   * the logFormat string will be compiled into regular expression finally
   * all regular expression will be replaced
   * 
   * lib/config.php 里提供了其他转义字符，通常建议转义字符越精确越好
   * 但是通常 nginx 会把未获得的地段用'-'替换，因此这里所有字段都用 %str (非空字符串) 定义
   */
  "logFormat": '%str,? ?%str %str %str [%string] "%str %url_path%url_query %str" %number %number "%str" "%string"',

  /* name of each part in one line of logs
   */
  "logFormatAs": [
    "client_ip",
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

  /* the name of primary key, from logFormatAs
   * for nginx log, I advise url_path
   */
  "table": "url_path",

  /* the name of time, from logFormatAs
   * this part stands for the time the log was made
   */
  "time": "time",

  /* when there is url in log, you can set this item
   * (optional)
   * once been set, you can search subparts in url_query
   */
  "url_query": "url_query",

  /* timezone
   * see http://php.net/manual/en/timezones.php
   */
  "timezone": 'Asia/Shanghai',

  /* log collector
   * (optional, set if you use collector)
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
   * (optional, set if you use collector)
   * key stands for collector_id
   * value stand for log file server writes when gets log
   * log file should be in the subfolder of dataDir, you should make subfolder yourself
   * you can put as many collectors as you like
   */
  "collector_log_file": {
    "1": "nginx1/nginx_access.log"
  }
}
