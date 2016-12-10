<?php
$sysConfig = [
    "formatReg" => [
        "\\" => "\\\\",
        "[" => "\[",
        "]" => "\]",
        "(" => "\(",
        ")" => "\)",
        "*" => "\*",
        "." => "\.",
        "/" => "\/",
        //url path
        "%url_path" => "([^\s\?]+)",
        //url_query
        "%url_query" => "\??(\S+?)?",
        "%http_method" => "(post|get|put|head|trace|delete|options)",
        "%http_status" => "(\d{3})",
        "%server_protocol" => "(http.+)",
        //int number
        "%number" => "(\d+)",
        //float number
        "%float" => "(\d+\.\d+)",
        //string with empty character
        "%string" => "(.+)",
        //string without empty character
        "%str" => "(\S+)",
        "%ip" => "(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})",
    ],
    "rootPath" => dirname(__FILE__).'/../',
    "timezone" => 'Asia/Shanghai'
];
