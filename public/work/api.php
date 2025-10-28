<?php

$frontController = '/index.php';

$_SERVER['SCRIPT_NAME']     = $frontController;
$_SERVER['PHP_SELF']        = $frontController;
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../index.php';

require __DIR__ . '/../index.php';
