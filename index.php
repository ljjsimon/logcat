<?php
include "helper.php";
$config = json_decode(file_get_contents('./config.json'));
makeIndex($config);

