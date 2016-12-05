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
        "%ip" => "(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})",
        "%string" => "(.+)",
        "%number" => "(\d+)",
        "%float" => "(\d+\.\d+)",
        "%http_path" => "([^\s\?]+)",
        "%http_query" => "\??(\S+?)?",
        "%http_method" => "(post|get|put|head|trace|delete|options)",
        "%http_status" => "(\w{3})",
        "%server_protocol" => "(http.+)"
    ],
    "rootPath" => dirname(__FILE__).'/../'
];
