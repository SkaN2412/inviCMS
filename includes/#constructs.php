<?php
$_config = new SconfigQueries;
$_mysqli = new inviMysql($_config->get("db_server"), $_config->get("db_login"), $_config->get("db_password"), $_config->get("db"));
?>
