<?php

session_start();

include_once "../config.php";
include_once "app/functions.php";

if (!isset($_SESSION['user_id'])) 
{
    header("Location: login");
    exit();
}

$currentSessionId = session_id();

// connect to db
$link = ConnectDB();

// Проверяем, существует ли запись текущей сессии для данного пользователя
$stmt = $link->prepare("SELECT session_id FROM users_sessions WHERE session_id = ? AND user_id = ?");
$stmt->bind_param("si", $currentSessionId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) 
{
    // Если записи нет, перенаправляем пользователя, не нагружая базу лишними запросами
    header("Location: login");
    exit();
}
$stmt->close();

// Определяем порог бездействия из настроек PHP для сессий (в секундах)
$gcMaxLifetime = (int) ini_get('session.gc_maxlifetime');

// Помечаем просроченные сессии как неактивные
$stmt = $link->prepare("UPDATE users_sessions SET active = 0 WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND) AND active = 1");
$stmt->bind_param("i", $gcMaxLifetime);
$stmt->execute();
$stmt->close();

// Обновляем статус текущей сессии на неактивный (active = 0)
$stmt = $link->prepare("UPDATE users_sessions SET active = 0 WHERE session_id = ?");
$stmt->bind_param("s", $currentSessionId);
$stmt->execute();
$stmt->close();

// Удаляем данные сессии и разрушаем её

// Очистка данных сессии
$_SESSION = [];

// Если используется cookie для сессии, удаляем её
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// Перенаправляем на страницу входа
header("Location: login");
exit();

?>