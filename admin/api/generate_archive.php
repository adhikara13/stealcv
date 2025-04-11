<?php

set_time_limit(0);

include_once '../../config.php';

if ($argc < 2) 
{
    error_log("Token не передан");
    exit(1);
}

$token = $argv[1];
$link = ConnectDB();

$stmt = $link->prepare("SELECT selected_ids FROM downloads WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $selected_ids = json_decode($row['selected_ids'], true);
} else {
    error_log("Запись для token $token не найдена");
    exit(1);
}
$stmt->close();

if (empty($selected_ids)) {
    error_log("Нет выбранных ID для token $token");
    exit(1);
}

$placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
$query = "SELECT log_id, filename FROM logs WHERE log_id IN ($placeholders)";
$stmt = $link->prepare($query);
$types = str_repeat('i', count($selected_ids));
$stmt->bind_param($types, ...$selected_ids);
$stmt->execute();
$result = $stmt->get_result();

$files = [];
while ($log = $result->fetch_assoc()) {
    $files[] = $log['filename'];
}
$stmt->close();

if (empty($files)) {
    $stmt = $link->prepare("UPDATE downloads SET status = ? WHERE token = ?");
    $errorStatus = 'error';
    $stmt->bind_param("ss", $errorStatus, $token);
    $stmt->execute();
    $stmt->close();
    error_log("Нет файлов для token $token");
    exit(1);
}

$logsDir = '../../' . LOGS_PATH;
$downloadsDir = '../../'. LOGS_PATH. '/downloads';

if (!is_dir($downloadsDir)) {
    mkdir($downloadsDir, 0755, true);
}

$archiveName = 'logs_' . $token . '.zip';
$archivePath = $downloadsDir . '/' . $archiveName;

$zip = new ZipArchive();
if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    error_log("Не удалось создать архив $archivePath");
    $stmt = $link->prepare("UPDATE downloads SET status = ? WHERE token = ?");
    $errorStatus = 'error';
    $stmt->bind_param("ss", $errorStatus, $token);
    $stmt->execute();
    $stmt->close();
    exit(1);
}

$totalFiles = count($files);
$processed = 0;

// Инициализируем общее количество файлов в БД
$stmtUpdate = $link->prepare("UPDATE downloads SET total_files = ?, processed_files = 0 WHERE token = ?");
$stmtUpdate->bind_param("is", $totalFiles, $token);
$stmtUpdate->execute();
$stmtUpdate->close();

foreach ($files as $filename) {
    $filePath = $logsDir . '/' . $filename;
    if (file_exists($filePath)) {
        $zip->addFile($filePath, $filename);
    } else {
        error_log("Файл не найден: $filePath");
    }
    $processed++;
    $stmtProgress = $link->prepare("UPDATE downloads SET processed_files = ? WHERE token = ?");
    $stmtProgress->bind_param("is", $processed, $token);
    $stmtProgress->execute();
    $stmtProgress->close();
}

// Обновляем статус на "finishing" перед закрытием архива
$stmtStatus = $link->prepare("UPDATE downloads SET status = ? WHERE token = ?");
$statusFinishing = 'finishing';
$stmtStatus->bind_param("ss", $statusFinishing, $token);
$stmtStatus->execute();

$zip->close();

$downloadUrl = '../'. LOGS_PATH .'/downloads/' . $archiveName; // скорректируйте путь по необходимости
$stmt = $link->prepare("UPDATE downloads SET status = ?, download_url = ? WHERE token = ?");
$readyStatus = 'ready';
$stmt->bind_param("sss", $readyStatus, $downloadUrl, $token);
$stmt->execute();
$stmt->close();

$link->close();
exit(0);

// Функция подключения к базе данных
function ConnectDB() {
    $host = DB_HOST;
    $user = DB_USER;
    $password = DB_PASS;
    $database = DB_NAME;
    $mysqli = new mysqli($host, $user, $password, $database);
    if ($mysqli->connect_error) {
        die('Ошибка подключения (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    $mysqli->set_charset("utf8");
    return $mysqli;
}
?>
