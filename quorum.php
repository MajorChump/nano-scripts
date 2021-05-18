<?php
require __DIR__ . '/vendor/autoload.php';

$ipPort = '172.17.0.2:7076';
(new \Nano\Quorum($ipPort))();