<?php

include_once "../../config.php";
include_once "../app/functions.php";
include_once "../app/managers/AccountManager.php";
include_once "../app/utils/UserAgentParser.php";

session_start();
header('Content-Type: application/json; charset=utf-8');

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

$user_id                = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$user_status            = isset($_POST['user_status']) ? intval($_POST['user_status']) : 0;

$username               = isset($_POST["username"]) ? $_POST["username"] : null;
$password               = isset($_POST["password"]) ? $_POST["password"] : null;
$user_builds            = isset($_POST["user_builds"]) ? base64_decode($_POST["user_builds"]) : null;
$role                   = isset($_POST["role"]) ? $_POST["role"] : null;

$twofa_disable          = isset($_POST['twofa_disable']) ? intval($_POST['twofa_disable']) : 0;

$session_id             = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;

if($method != null)
{
    switch($method)
    {
        case "get_users":
            getUsers($page, $draw, $start, $length);
            break;

        case "delete_user":
            deleteUser($user_id);
            break;

        case "change_status":
            ChangeStatus($user_id, $user_status);
            break;

        case "create_user":
            CreateUser($username, $password, $user_builds, $role);
            break;

        case "get_sessions":
            getSessions($user_id);
            break;

        case "delete_session":
            deleteSession($session_id);
            break;

        case "get_user":
            getUser($user_id);
            break;

        case "edit_user":
            editUser($user_id, $username, $password, $role, $user_builds, $twofa_disable);
            break;

        default:
            echo json_encode(['error' => 'Unknown method']);
            exit(0);
            break;
    }
}

function getUsers($page, $draw, $start, $length)
{
    $link = ConnectDB();
    
    $conditions = [];
    
    $whereClause = '';
    if (!empty($conditions)) {
        $whereClause = ' WHERE ' . implode(' AND ', $conditions);
    }
    
    $countQuery = "SELECT COUNT(*) AS total FROM `users`" . $whereClause;
    $countResult = $link->query($countQuery);
    $totalRecords = 0;
    if ($countResult) {
        $row = $countResult->fetch_assoc();
        $totalRecords = (int)$row['total'];
    }
    
    $limitStart = $start;
    $limitCount = (int)$length;
    
    $query = "SELECT * FROM `users`" . $whereClause . " ORDER BY `id` DESC LIMIT {$limitStart}, {$limitCount}";
    $result = $link->query($query);
    
    $data = [];
    while ($row = $result->fetch_assoc()) 
    {
        $seed                       = sha1($row['username'] ."_". $row['created_at']);

        $data[] = [
            'id'                    => $row['id'],
            'username'              => $row['username'],
            'builds'                => $row['builds'],
            'user_group'            => $row['user_group'],
            'created_at'            => $row['created_at'],
            'last_login'            => $row['last_login'],
            'twofa'                 => $row['twofa_status'],
            'seed'                  => $seed,
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

function deleteUser($user_id)
{
    $link = ConnectDB();
    
    if (empty($user_id))
    {
        echo json_encode(['error' => 'No user_id provided']);
        exit();
    }
    
    $stmt = $link->prepare("DELETE FROM users WHERE id = ?");

    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing DELETE statement: ' . $link->error]);
        exit();
    }
    
    $stmt->bind_param("i", $user_id);

    if (!$stmt->execute()) 
    {
        echo json_encode(['error' => 'Error executing DELETE: ' . $stmt->error]);
        exit();
    }
    
    if ($stmt->affected_rows === 0) 
    {
        echo json_encode(['error' => 'No user found with provided ID']);
        exit();
    }
    
    $stmt->close();
    
    echo json_encode(['success' => 'User deleted']);
    exit();
}

function ChangeStatus($user_id, $user_status)
{
    $link = ConnectDB();
    
    if (empty($user_id) || !isset($user_status)) 
    {
        echo json_encode(['error' => 'No user_id or user_status provided']);
        exit();
    }
    
    $stmt = $link->prepare("UPDATE users SET active = ? WHERE id = ?");
    
    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing UPDATE statement: ' . $link->error]);
        exit();
    }
    
    $stmt->bind_param("ii", $user_status, $user_id);
    
    // Выполнение запроса
    if (!$stmt->execute()) 
    {
        echo json_encode(['error' => 'Error executing UPDATE: ' . $stmt->error]);
        exit();
    }
    
    if ($stmt->affected_rows === 0) 
    {
        echo json_encode(['error' => 'No user found with provided ID']);
        exit();
    }
    
    $stmt->close();

    if($user_status == 0)
    {
        $stmt = $link->prepare("UPDATE users_sessions SET active = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
    
    echo json_encode(['success' => 'User status updated']);
    exit();
}

function CreateUser($username, $password, $user_builds, $role)
{
    $allowed_groups = ['Worker', 'Administrator'];

    if (!in_array($role, $allowed_groups)) 
    {
        echo json_encode(['error' => 'Invalid user group: $role']);
        exit();
    }

    // hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $link = ConnectDB();
    $stmt = $link->prepare("INSERT INTO users (username, password, user_group, builds, twofa_status, created_at, active) VALUES (?, ?, ?, ?, 0, NOW(), 1)");
    
    if (!$stmt) 
    {
        $stmt->close();
        echo json_encode(['error' => 'Error preparing SELECT: ' . $link->error]);
        exit();
    }

    $stmt->bind_param("ssss", $username, $passwordHash, $role, $user_builds);

    if ($stmt->execute()) 
    {
        echo json_encode(['success' => 'User created']);
        $stmt->close();
        exit();
    }
    else
    {
        echo json_encode(['error' => 'Error adding user']);
        $stmt->close();
        exit();
    }
}

function getSessions($user_id)
{
    $link = ConnectDB();

    $sql = "SELECT id, session_id, user_agent, active, created_at, last_activity FROM users_sessions WHERE `user_id`='$user_id' ORDER BY created_at DESC";
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
        $browserInfo = parseUserAgent($row["user_agent"]);
        $row['browser'] = $browserInfo['browser'];
        $row['version'] = $browserInfo['version'];

        $data[] = $row;
    }

    $result->free();
    $link->close();
    
    echo json_encode(['success' => true, 'data' => $data]);

    exit();
}

function deleteSession($session_id)
{
    $link = ConnectDB();
    
    if (empty($session_id)) 
    {
        echo json_encode(['error' => 'No user_id provided']);
        exit();
    }
    
    $stmt = $link->prepare("DELETE FROM users_sessions WHERE id = ?");

    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing DELETE statement: ' . $link->error]);
        exit();
    }
    
    $stmt->bind_param("i", $session_id);

    if (!$stmt->execute()) 
    {
        echo json_encode(['error' => 'Error executing DELETE: ' . $stmt->error]);
        exit();
    }
    
    if ($stmt->affected_rows === 0) 
    {
        echo json_encode(['error' => 'No session found with session_id']);
        exit();
    }
    
    $stmt->close();
    
    echo json_encode(['success' => 'Session deleted']);
    exit();
}

function getUser($user_id)
{
    $link = ConnectDB();
    
    $user_id = mysqli_real_escape_string($link, $user_id);
	$user = $link->query("SELECT * FROM `users` WHERE `id`='$user_id'")->fetch_array();

    $data = [];

    if($user != null)
    {
        $data["user_id"]            = $user["id"];
        $data["username"]           = $user["username"];
        $data["role"]               = $user["user_group"];
        $data["created_at"]         = $user["created_at"];
        $data["last_login"]         = $user["last_login"];
        $data["twofa"]              = (strlen($user['twofa_secret']) > 0);

        $data["builds"]             = $user["builds"];
    }
    else
    {
        echo json_encode(['error' => 'User not found']);
        exit();
    }

    echo json_encode(['success' => true, 'data' => $data]);
    exit();
}

function editUser($user_id, $username, $password, $role, $user_builds, $twofa_disable)
{
    $allowed_groups = ['Worker', 'Administrator'];

    if (!in_array($role, $allowed_groups)) 
    {
        echo json_encode(['error' => 'Invalid user group: $role']);
        exit();
    }

    // Подключаемся к базе данных
    $link = ConnectDB();

    // Массив для частей запроса, типы и значения параметров
    $fields = [];
    $types = "";
    $params = [];

    // Обновляем имя пользователя
    $fields[] = "`username` = ?";
    $types .= "s";
    $params[] = $username;

    // Если передан новый пароль, хэшируем и добавляем в запрос
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $fields[] = "`password` = ?";
        $types .= "s";
        $params[] = $password_hash;
    }

    // Обновляем список билдов (предполагается, что это строка, при необходимости можно изменить тип)
    $fields[] = "`builds` = ?";
    $types .= "s";
    $params[] = $user_builds;

    // Обновляем группу пользователя (роль) – предполагается, что это целое число
    $fields[] = "`user_group` = ?";
    $types .= "s";
    $params[] = $role;

    // Если нужно отключить 2FA, сбрасываем секрет
    if ($twofa_disable) {
        $fields[] = "`twofa_secret` = ?";
        $types .= "s";
        $params[] = ""; // или можно использовать NULL, если поле допускает null
    }

    // Формируем окончательный SQL-запрос
    $sql = "UPDATE `users` SET " . implode(", ", $fields) . " WHERE `id` = ?";
    $stmt = $link->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['error' => 'Error preparing UPDATE: ' . $link->error]);
        exit();
    }

    // Привязываем id пользователя к запросу
    $types .= "i";
    $params[] = $user_id;

    // Готовим массив параметров для динамического связывания
    $stmt_params = array_merge([$types], $params);
    $bind_names = [];
    foreach ($stmt_params as $key => $value) {
        $bind_names[$key] = &$stmt_params[$key];
    }
    
    // Динамически связываем параметры
    call_user_func_array([$stmt, 'bind_param'], $bind_names);

    // Выполняем запрос и выводим результат
    if ($stmt->execute()) {
        echo json_encode(['success' => 'User updated successfully']);
    } else {
        echo json_encode(['error' => 'Error update user: ' . $stmt->error]);
    }
    
    $stmt->close();
}

?>