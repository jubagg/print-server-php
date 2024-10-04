<?php
namespace PrintServer;

require_once __DIR__ . '/../vendor/autoload.php';
use PrintServer\Main;

$server = new Main();

$server->start_server();