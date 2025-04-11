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

$rule_type              = isset($_POST["rule_type"]) ? $_POST["rule_type"] : null;
$rule_value             = isset($_POST["rule_value"]) ? $_POST["rule_value"] : null;

$rule_ip                = isset($_POST["rule_ip"]) ? $_POST["rule_ip"] : null;
$rule_hwid              = isset($_POST["rule_hwid"]) ? $_POST["rule_hwid"] : null;

$rule_id                = isset($_POST["rule_id"]) ? $_POST["rule_id"] : null;

$draw                   = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start                  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length                 = isset($_POST['length']) ? intval($_POST['length']) : 10;

/**
 * check actions
 */
if($method != null)
{
    switch($method)
    {
        case "get_blocklist":
            ViewBlocklistTable($page, $draw, $start, $length, $rule_type, $rule_value);
            break;

        case "create_rule":
            CreateRule($rule_type, $rule_value);
            break;

        case "block_log":
            BlockLog($rule_ip, $rule_hwid);
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

function ViewBlocklistTable($page, $draw, $start, $length, $filter_type = null, $filter_value = null)
{
    // Подключение к базе данных (функция ConnectDB должна возвращать mysqli-подключение)
    $link = ConnectDB();
    
    $conditions = [];
    
    // Фильтрация по типу блокировки (ip, mask, hwid)
    if ($filter_type !== null && $filter_type !== '') {
        $filter_type = $link->real_escape_string($filter_type);
        $conditions[] = "`type` = '{$filter_type}'";
    }
    
    // Фильтрация по значению блокировки (например, конкретный IP или часть маски)
    if ($filter_value !== null && $filter_value !== '') {
        $filter_value = $link->real_escape_string($filter_value);
        $conditions[] = "`value` LIKE '%{$filter_value}%'";
    }
    
    // Формирование условия WHERE
    $whereClause = '';
    if (!empty($conditions)) {
        $whereClause = ' WHERE ' . implode(' AND ', $conditions);
    }
    
    // Подсчёт общего количества записей, удовлетворяющих условиям
    $countQuery = "SELECT COUNT(*) AS total FROM `blocklist`" . $whereClause;
    $countResult = $link->query($countQuery);
    $totalRecords = 0;
    if ($countResult) {
        $row = $countResult->fetch_assoc();
        $totalRecords = (int)$row['total'];
    }
    
    $limitStart = $start;
    $limitCount = (int)$length;
    
    // Формирование основного запроса для выборки записей
    $query = "SELECT * FROM `blocklist`" . $whereClause . " ORDER BY `id` DESC LIMIT {$limitStart}, {$limitCount}";
    $result = $link->query($query);
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id'    => $row['id'],
            'type'  => $row['type'],
            'value' => $row['value']
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

function CreateRule($rule_type, $rule_value)
{
    $link = ConnectDB();
    
    $stmt = $link->prepare("SELECT id FROM blocklist WHERE type = ? AND value = ?");
    
    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing SELECT: ' . $link->error]);
        exit();
    }
    
    $stmt->bind_param("ss", $rule_type, $rule_value);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) 
    {
        $stmt->close();
        echo json_encode(['error' => 'Such a block already exists.']);
        exit();
    }
    else
    {
        $stmt->close();
        
        $stmt = $link->prepare("INSERT INTO blocklist (type, value) VALUES (?, ?)");
        
        if (!$stmt) 
        {
            $stmt->close();
            echo json_encode(['error' => 'Error preparing SELECT: ' . $link->error]);
            exit();
        }
        
        $stmt->bind_param("ss", $rule_type, $rule_value);
        
        if ($stmt->execute()) 
        {
            echo json_encode(['success' => 'Block rule created']);
            exit();
        } 
        else
        {
            echo json_encode(['error' => 'Error adding block rule']);
            exit();
        }
        $stmt->close();
    }
}

function BlockLog($rule_ip, $rule_hwid)
{
    $link = ConnectDB();

    // Если ни один из параметров не задан, возвращаем ошибку
    if (empty($rule_ip) && empty($rule_hwid)) {
        echo json_encode(['error' => 'No rule provided']);
        exit();
    }

    // Обработка правила для IP
    if (!empty($rule_ip)) {
        $stmt = $link->prepare("SELECT id FROM blocklist WHERE type = 'ip' AND value = ?");
        if (!$stmt) {
            echo json_encode(['error' => 'Error preparing SELECT for IP: ' . $link->error]);
            exit();
        }
        $stmt->bind_param("s", $rule_ip);
        if (!$stmt->execute()) {
            echo json_encode(['error' => 'Error executing SELECT for IP: ' . $stmt->error]);
            exit();
        }
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            $stmt->close();
            $stmt = $link->prepare("INSERT INTO blocklist (type, value) VALUES ('ip', ?)");
            if (!$stmt) {
                echo json_encode(['error' => 'Error preparing INSERT for IP: ' . $link->error]);
                exit();
            }
            $stmt->bind_param("s", $rule_ip);
            if (!$stmt->execute()) {
                echo json_encode(['error' => 'Error inserting IP rule: ' . $stmt->error]);
                exit();
            }
        } else {
            $stmt->close();
        }
    }

    // Обработка правила для HWID
    if (!empty($rule_hwid)) {
        $stmt = $link->prepare("SELECT id FROM blocklist WHERE type = 'hwid' AND value = ?");
        if (!$stmt) {
            echo json_encode(['error' => 'Error preparing SELECT for HWID: ' . $link->error]);
            exit();
        }
        $stmt->bind_param("s", $rule_hwid);
        if (!$stmt->execute()) {
            echo json_encode(['error' => 'Error executing SELECT for HWID: ' . $stmt->error]);
            exit();
        }
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            $stmt->close();
            $stmt = $link->prepare("INSERT INTO blocklist (type, value) VALUES ('hwid', ?)");
            if (!$stmt) {
                echo json_encode(['error' => 'Error preparing INSERT for HWID: ' . $link->error]);
                exit();
            }
            $stmt->bind_param("s", $rule_hwid);
            if (!$stmt->execute()) {
                echo json_encode(['error' => 'Error inserting HWID rule: ' . $stmt->error]);
                exit();
            }
        } else {
            $stmt->close();
        }
    }

    echo json_encode(['success' => 'Block rule created']);
    exit();
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
    $stmt = $link->prepare("DELETE FROM blocklist WHERE id = ?");
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
    if ($stmt->affected_rows === 0) {
        echo json_encode(['error' => 'No rule found with provided ID']);
        exit();
    }
    
    $stmt->close();
    
    echo json_encode(['success' => 'Block rule deleted']);
    exit();
}



?>