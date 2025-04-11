<?php

// -------------------------------------------------------------------------------------------
// проверяем, что мы запущены из терминала
if (PHP_SAPI !== 'cli')
{
    exit();
}

// -------------------------------------------------------------------------------------------
// проверка на дебаг мод
define('DEBUG_MODE', true);

if(DEBUG_MODE)
{
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ERROR);
}
else
{
    error_reporting(0);
}

// -------------------------------------------------------------------------------------------
// includes
include_once 'config.php';

include_once 'app/models/Log.php';

include_once 'app/managers/LogsManager.php';
include_once 'app/managers/TelegramManager.php';
include_once 'app/managers/plugins/MetaMask.php';

include_once 'app/managers/CronManager.php';

if(isset($argv[1]))
{
    $access_token = $argv[1];
    new CronManager($access_token);
}

?>