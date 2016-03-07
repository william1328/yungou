<?php

$_SERVER['QUERY_STRING'] = "plugin=1&api=Oauth&action=callback&data=qq&".$_SERVER['QUERY_STRING'];

$_GET['plugin'] = 1;
$_GET['api'] = "Oauth";
$_GET['action'] = "callback";
$_GET['data'] = "qq";


include "index.php";


