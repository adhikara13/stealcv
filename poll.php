<?php

// -------------------------------------------------------------------------------------------
// limit
set_time_limit(0);

// -------------------------------------------------------------------------------------------
// проверяем, что мы запущены из терминала
if (PHP_SAPI !== 'cli') 
{
    exit();
}

// -------------------------------------------------------------------------------------------
// mutex
$lockFile = '/tmp/telegram_bot.lock';
$fp = fopen($lockFile, 'w+');

if (!$fp) 
{
    die("!$fp");
    exit();
}

if (!flock($fp, LOCK_EX | LOCK_NB)) 
{
    die("!flock");
    exit();
}

// -------------------------------------------------------------------------------------------
// Регистрируем функцию, которая снимет блокировку при завершении скрипта
register_shutdown_function(function() use ($fp) 
{
    flock($fp, LOCK_UN);
    fclose($fp);
});

// -------------------------------------------------------------------------------------------
// includes
include_once 'config.php';

// -------------------------------------------------------------------------------------------
// Отслеживание update_id, чтобы не обрабатывать одни и те же обновления повторно
$offset = 0;

$db = ConnectDB();
$telegram_bot_token = getTelegramToken($db);

// -------------------------------------------------------------------------------------------
// Основной цикл polling
while (true)
{
    $apiUrl = "https://api.telegram.org/bot{$telegram_bot_token}/";

    $url = $apiUrl . "getUpdates?timeout=60&offset=" . $offset;
    $response = file_get_contents($url);
    $updates = json_decode($response, true);

    if (isset($updates['result']) && is_array($updates['result']))
    {
        foreach ($updates['result'] as $update)
        {
            $offset = $update['update_id'] + 1;

            if (isset($update['message']['text']))
            {
                $text = trim($update['message']['text']);

                if (strpos($text, "/start") === 0)
                {
                    $parts = explode(" ", $text);
                    $token = $parts[1] ?? '';

                    if (!$token) 
                    {
                        continue; // если токен не передан, пропускаем обработку
                    }

                    $stmt = $db->prepare("SELECT * FROM users_tokens WHERE token = ? AND expires_at > NOW()");

                    if (!$stmt) 
                    {
                        error_log("Ошибка prepare: " . $db->error);
                        continue;
                    }

                    $stmt->bind_param("s", $token);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($row = $result->fetch_assoc()) 
                    {
                        $chat_id = $update['message']['chat']['id'];

                        $stmt2 = $db->prepare("UPDATE users SET chat_id = ?, telegram_enable = 1 WHERE id = ?");

                        if (!$stmt2) 
                        {
                            error_log("Ошибка prepare (users): " . $db->error);
                            continue;
                        }

                        $user_id = $row['user_id'];

                        $stmt2->bind_param("ii", $chat_id, $user_id);
                        $stmt2->execute();
                        $stmt2->close();

                        $stmt3 = $db->prepare("DELETE FROM users_tokens WHERE token = ?");

                        if ($stmt3) 
                        {
                            $stmt3->bind_param("s", $token);
                            $stmt3->execute();
                            $stmt3->close();
                        }

                        // Отправляем сообщение об успешной привязке
                        sendMessage($telegram_bot_token, $chat_id, "✅ Your Telegram has been successfully linked!");
                    }
                }
            }
        }
    }

    sleep(5);
}

function ConnectDB()
{
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die('No connect');
	mysqli_set_charset($link, 'utf8' );
	
	return $link;
}

function sendMessage($telegram_bot_token, $chat_id, $text) 
{
    $url = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";

    $post_fields = [
        'chat_id' => $chat_id,
        'text'    => $text
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function getTelegramToken($link) 
{
    $query = "SELECT setting_value FROM settings WHERE setting_key = ?";
    $stmt = $link->prepare($query);
    if (!$stmt) {
        return false;
    }

    $key = 'telegram_token';
    $stmt->bind_param('s', $key);
    $stmt->execute();

    $stmt->bind_result($token);
    $stmt->fetch();
    $stmt->close();

    return $token;
}


?>