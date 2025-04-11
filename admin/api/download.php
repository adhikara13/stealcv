<?php

include_once '../../config.php';
include_once "../app/functions.php";
include_once "../app/managers/AccountManager.php";

session_start();
header('Content-Type: application/json; charset=utf-8');

$link = ConnectDB();

$accountManager = new AccountManager($link);
$accountManager->CheckAuth();

switch($_SERVER['REQUEST_METHOD'])
{
    case "POST":
        CreateDownload();
        break;

    case "GET":
        CheckStatus();
        break;

    default:
        exit(0);
        break;
}

function CreateDownload()
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['ids']) || !is_array($data['ids']) || empty($data['ids'])) 
    {
        echo json_encode(['success' => false, 'error' => 'Incorrect input data']);
        exit;
    }

    // Подключаемся к БД
    $link = ConnectDB();

    // Генерируем уникальный token для задачи
    $token = bin2hex(random_bytes(16)); // 32-символьная строка

    // Сохраняем выбранные ID (в виде JSON) и статус "pending"
    $ids_json = json_encode(array_map('intval', $data['ids']));
    $status = 'pending';
    $stmt = $link->prepare("INSERT INTO downloads (token, selected_ids, status, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $token, $ids_json, $status);
    $stmt->execute();
    $stmt->close();

    // Запускаем фоновый процесс для генерации архива
    $phpPath = PHP_BINDIR . '/php';
    $cmd = escapeshellcmd($phpPath) . " " . escapeshellarg(__DIR__ . '/generate_archive.php') . " " . escapeshellarg($token) . " > /dev/null 2>&1 &";
    exec($cmd);

    // Возвращаем клиенту token для дальнейших запросов статуса
    echo json_encode(['success' => true, 'token' => $token]);
    exit;
}

function CheckStatus()
{
    if (!isset($_GET['token'])) {
        echo json_encode(['success' => false, 'error' => 'Token not transferred']);
        exit;
    }
    
    $token = $_GET['token'];
    $link = ConnectDB();
    $stmt = $link->prepare("SELECT status, download_url, processed_files, total_files FROM downloads WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $status = $row['status'];
        $download_url = $row['download_url'] ?? '';
        $processed = (int)$row['processed_files'];
        $total = (int)$row['total_files'];
        
        if ($total > 0) {
            $percentage = round(($processed / $total) * 100);
            $progressMessage = "Files processed: $processed of $total ($percentage%)";
        } else {
            $progressMessage = "Writing to disk...";
        }
        
        echo json_encode([
          'success'         => true,
          'status'          => $status,
          'processed_files' => $processed,
          'total_files'     => $total,
          'progress_message'=> $progressMessage,
          'download_url'    => $download_url
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Record not found']);
    }
    $stmt->close();
    $link->close();
    exit;
}

function ConnectDB() 
{
    $host = DB_HOST;
    $user = DB_USER;
    $password = DB_PASS;
    $database = DB_NAME;
    $mysqli = new mysqli($host, $user, $password, $database);

    if ($mysqli->connect_error) 
    {
        exit();
    }
    $mysqli->set_charset("utf8");
    return $mysqli;
}
?>
