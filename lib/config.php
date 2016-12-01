<?
$sysConfig = [
    "formatReg" => [
        "\\" => "\\\\",
        "[" => "\[",
        "]" => "\]",
        "(" => "\(",
        ")" => "\)",
        "*" => "\*",
        "?" => "\?",
        "." => "\.",
        "/" => "\/",
        "%ip" => "(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})",
        "%string" => "(.+)",
        "%number" => "(\d+)",
        "%float" => "(\d+\.\d+)",
        "%http_path" => "(\S+)",
        "%http_query" => "\?(\S+)?",
        "%http_method" => "(post|get|put|head|trace|delete|options)",
        "%http_code" => "(\w{3})",
        "%http_version" => "(http.+)"
    ],
    "rootPath" => dirname(__FILE__).'/../'
];

