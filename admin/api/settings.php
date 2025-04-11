<?php

include_once "../../config.php";
include_once "../app/functions.php";

include_once "../app/utils/qr.php";
include_once "../app/utils/GoogleAuthenticator.php";
include_once "../app/utils/UserAgentParser.php";
include_once "../app/managers/AccountManager.php";
include_once "../app/managers/TelegramManager.php";

session_start();
header('Content-Type: application/json; charset=utf-8');

$link = ConnectDB();

$accountManager = new AccountManager($link);
$accountManager->CheckAuth();

$method                     = isset($_POST["method"]) ? $_POST["method"] : null;

$session_id                 = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
$old_password               = isset($_POST["old_password"]) ? $_POST["old_password"] : null;
$new_password               = isset($_POST["new_password"]) ? $_POST["new_password"] : null;

$onetime_code               = isset($_POST["onetime_code"]) ? $_POST["onetime_code"] : null;

$notify_logins              = isset($_POST['notify_logins']) ? intval($_POST['notify_logins']) : 0;
$notify_twofa_change        = isset($_POST['notify_twofa_change']) ? intval($_POST['notify_twofa_change']) : 0;
$notify_password_change     = isset($_POST['notify_password_change']) ? intval($_POST['notify_password_change']) : 0;
$notify_all_logs            = isset($_POST['notify_all_logs']) ? intval($_POST['notify_all_logs']) : 0;
$notify_only_crypto_logs    = isset($_POST['notify_only_crypto_logs']) ? intval($_POST['notify_only_crypto_logs']) : 0;
$notify_with_screen         = isset($_POST['notify_with_screen']) ? intval($_POST['notify_with_screen']) : 0;

$page                       = isset($_POST['page']) ? intval($_POST['page']) : 0;
$draw                       = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start                      = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length                     = isset($_POST['length']) ? intval($_POST['length']) : 10;

if($method != null)
{
    switch($method)
    {
        case "get_configuration":
            getConfiguration();
            break;

        case "get_sessions":
            getSessions($page, $draw, $start, $length);
            break;

        case "delete_session":
            deleteSession($session_id);
            break;

        case "change_password":
            ChangePassword($old_password, $new_password);
            break;

        case "get_twofa":
            getTwofa();
            break;

        case "save_twofa":
            saveTwofa($onetime_code);
            break;

        case "disable_twofa":
            disableTwofa($onetime_code);
            break;

        case "change_notifications":
            ChangeNotifications($notify_logins, $notify_twofa_change, $notify_password_change, $notify_all_logs, $notify_only_crypto_logs, $notify_with_screen);
            break;

        case "create_token":
            CreateTelegramToken();
            break;

        case "check_telegram":
            CheckTelegram();
            break;

        case "disable_telegram":
            DisableTelegram();
            break;

        default:
            echo json_encode(['error' => 'Unknown method']);
            exit(0);
            break;
    }
}

function getConfiguration()
{
    $user_id = $_SESSION['user_id'];
    $link = ConnectDB();

    $user = getUser($link);
    
    echo json_encode([
        'success'                   => true, 
        'twofa_status'              => $user["twofa_status"],
        'telegram_enable'           => $user["telegram_enable"],
        'notify_logins'             => $user["notify_logins"],
        'notify_twofa_change'       => $user["notify_twofa_change"],
        'notify_password_change'    => $user["notify_password_change"],
        'notify_all_logs'           => $user["notify_all_logs"],
        'notify_only_crypto_logs'   => $user["notify_only_crypto_logs"],
        'notify_with_screen'        => $user["notify_with_screen"],
    ]);

    $link->close();

    exit();
}

function getSessions($page, $draw, $start, $length)
{
    $user_id = $_SESSION['user_id'];
    $link = ConnectDB();
    
    $countQuery = "SELECT COUNT(*) AS total FROM `users_sessions` WHERE `user_id`='$user_id' ";
    $countResult = $link->query($countQuery);
    $totalRecords = 0;
    if ($countResult) {
        $row = $countResult->fetch_assoc();
        $totalRecords = (int)$row['total'];
    }
    
    $limitStart = $start;
    $limitCount = (int)$length;
    
    $query = "SELECT * FROM `users_sessions` WHERE `user_id`='$user_id' ORDER BY `id` DESC LIMIT {$limitStart}, {$limitCount}";
    $result = $link->query($query);

    $data = [];
    
    while ($row = $result->fetch_assoc()) 
    {
        $browserInfo = parseUserAgent($row["user_agent"]);
        $row['browser'] = $browserInfo['browser'];
        $row['version'] = $browserInfo['version'];

        $data[] = $row;
    }

    $result->free();

    $response = [
        "draw"            => $draw,
        "recordsTotal"    => $totalRecords,
        "recordsFiltered" => $totalRecords,
        "current_page"    => $page,
        "total_pages"     => $total_pages,
        "data"            => $data
    ];
    
    echo json_encode($response);
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

function ChangePassword($old_password, $new_password)
{
    $user_id = $_SESSION['user_id'];

    $link = ConnectDB();

    $stmt = $link->prepare("SELECT * FROM users WHERE id = ? AND active = 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) 
    {
        if (password_verify($old_password, $user['password'])) 
        {
            $passwordHash = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $link->prepare("UPDATE `users` SET `password`= ? WHERE `id`= ?");

            if (!$stmt) 
            {
                echo json_encode(['error' => 'Error preparing UPDATE']);
                exit();
            }

            $stmt->bind_param("si", $passwordHash, $user_id);

            if ($stmt->execute()) 
            {
                $telegramManager = new TelegramManager($link);
                $telegramManager->NotifyChangePassword($user);

                echo json_encode(['success' => 'Password change successfully']);
            } 
            else 
            {
                echo json_encode(['error' => 'Unknown Error']);
            }
            
            $stmt->close();
            exit();
        }
        else
        {
            echo json_encode(['error' => 'Old password is incorrect']);
        }
    }
}

function getUser($link)
{
    $user_id = $_SESSION['user_id'];

    $user_id = mysqli_real_escape_string($link, $user_id);
	$user = $link->query("SELECT * FROM `users` WHERE `id`='$user_id'")->fetch_array();

    if($user != null)
    {
        return $user;
    }
    else
    {
        return false;
    }
}

function getTwofa()
{
    $user_id = $_SESSION['user_id'];

    $link = ConnectDB();

    $ga = new PHPGangsta_GoogleAuthenticator();
    $secret = $ga->createSecret();

    $stmt = $link->prepare("UPDATE `users` SET `twofa_secret`= ? WHERE `id`= ?");

    if (!$stmt) 
    {
        echo json_encode(['error' => 'Error preparing UPDATE']);
        exit();
    }

    $stmt->bind_param("si", $secret, $user_id);

    if ($stmt->execute()) 
    {
        $info_in_qr = 'otpauth://totp/stealc_v2:'. $_SESSION["username"] .'?secret='.$secret.'&issuer=stealc_v2';
        $qr = QRCode::svg($info_in_qr);

        echo json_encode([
            'success' => true, 
            'qr' => $qr,
            'secret' => $secret
        ]);
    } 
    else 
    {
        echo json_encode(['error' => 'Unknown Error']);
    }
            
    $stmt->close();
    exit();
}

function saveTwofa($onetime_code)
{
    $user_id = $_SESSION['user_id'];

    if(strlen($onetime_code) === 6)
    {
        $link = ConnectDB();

        $telegramManager = new TelegramManager($link);
        $ga = new PHPGangsta_GoogleAuthenticator();

        $user = $link->query("SELECT * FROM `users` WHERE `id`='$user_id'")->fetch_array();

        if($user != null)
        {
            if($user["twofa_status"] == 0)
            {
                if ($ga->verifyCode((string)$user["twofa_secret"], (string)$onetime_code, 0)) 
                {
                    $stmt = $link->prepare("UPDATE `users` SET `twofa_status`= 1 WHERE `id`= ?");
                    $stmt->bind_param("i", $user_id);

                    if ($stmt->execute()) 
                    {
                        $telegramManager->NotifyChangeTwoFa($user);

                        echo json_encode(['success' => '2FA enabled']);
                    }
                    else
                    {
                        echo json_encode(['error' => 'Unknown Error']);
                    }
                } 
                else 
                {
                    echo json_encode(['error' => 'Confirm code incorrect']);
                }
            }
            else
            {
                echo json_encode(['error' => '2FA already installed']);
            }
        }
        else
        {
            return 0;
        }
    }
    else
    {
        echo json_encode(['error' => 'Code Length not 6 symbols']);
    }
}

function disableTwofa($onetime_code)
{
    $user_id = $_SESSION['user_id'];

    if(strlen($onetime_code) === 6)
    {
        $link = ConnectDB();

        $telegramManager = new TelegramManager($link);
        $ga = new PHPGangsta_GoogleAuthenticator();

        $user = $link->query("SELECT * FROM `users` WHERE `id`='$user_id'")->fetch_array();

        if($user != null)
        {
            if($user["twofa_status"] == 1)
            {
                if ($ga->verifyCode((string)$user["twofa_secret"], (string)$onetime_code, 0)) 
                {
                    $stmt = $link->prepare("UPDATE `users` SET `twofa_status`= 0 WHERE `id`= ?");
                    $stmt->bind_param("i", $user_id);

                    if ($stmt->execute()) 
                    {
                        $telegramManager->NotifyChangeTwoFa($user);

                        echo json_encode(['success' => '2FA disabled']);
                    }
                    else
                    {
                        echo json_encode(['error' => 'Unknown Error']);
                    }
                } 
                else 
                {
                    echo json_encode(['error' => 'Confirm code incorrect']);
                }
            }
            else
            {
                echo json_encode(['error' => '2FA already installed']);
            }
        }
        else
        {
            return 0;
        }
    }
    else
    {
        echo json_encode(['error' => 'Code Length not 6 symbols']);
    }
}

function ChangeNotifications($notify_logins, $notify_twofa_change, $notify_password_change, $notify_all_logs, $notify_only_crypto_logs, $notify_with_screen)
{
    $user_id = $_SESSION['user_id'];
    $link = ConnectDB();

    $stmt = $link->prepare("UPDATE `users` SET `notify_logins`= ?, `notify_twofa_change`= ?, `notify_password_change`= ?, `notify_all_logs`= ?, `notify_only_crypto_logs`= ?, `notify_with_screen`= ? WHERE `id`= ?");
    $stmt->bind_param("iiiiiii", $notify_logins, $notify_twofa_change, $notify_password_change, $notify_all_logs, $notify_only_crypto_logs, $notify_with_screen, $user_id);

    if ($stmt->execute()) 
    {
        echo json_encode(['success' => 'Notifications saved']);
    }
    else
    {
        echo json_encode(['error' => 'Unknown Error']);
    }
}

function CreateTelegramToken()
{
    $user_id = $_SESSION['user_id'];
    $link = ConnectDB();

    $token = bin2hex(random_bytes(16));
    $expires_at = date("Y-m-d H:i:s", time() + 3600);

    $stmt = $link->prepare("INSERT INTO users_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $token, $expires_at);

    if ($stmt->execute()) 
    {
        $query = "SELECT setting_value FROM settings WHERE setting_key = 'telegram_bot_username'";
        $result = $link->query($query);

        if ($row = $result->fetch_assoc())
        {
            // run poll.php script
            /*$phpPath = PHP_BINDIR . '/php';
            $cmd = escapeshellcmd($phpPath) . " /var/www/html/poll.php > /dev/null 2>&1 & echo $!";
            $pid = exec($cmd);

            exec("ps -p $pid", $output);

            if(count($output) > 1)
            {

            } 
            else 
            {
                echo json_encode(['error' => 'Unknown Error']);
            }*/

            $phpPath = PHP_BINDIR . '/php';
            $cmd = escapeshellcmd($phpPath) . " /var/www/html/poll.php";

            // Определяем дескрипторы для stdin, stdout и stderr
            $descriptorspec = [
                0 => ["pipe", "r"], // стандартный ввод
                1 => ["pipe", "w"], // стандартный вывод
                2 => ["pipe", "w"]  // стандартный поток ошибок
            ];

            // Запускаем процесс
            $process = proc_open($cmd, $descriptorspec, $pipes);

            if (is_resource($process)) 
            {
                $status = proc_get_status($process);
                $pid = $status['pid'];

                if (!$status['running']) 
                {
                    echo json_encode(['error' => 'Unknown Error']);
                } 
            } else 
            {
                echo json_encode(['error' => 'Unknown Error']);
            }

            $telegram_bot_username = $row['setting_value'];
            $link = "https://t.me/{$telegram_bot_username}?start={$token}";

            echo json_encode([
                'success' => true,
                'link' => $link
            ]);
        }
        else
        {
            echo json_encode(['error' => 'Unknown Error']);
        }
    }
    else
    {
        echo json_encode(['error' => 'Unknown Error']);
    }

    $stmt->close();
}

function CheckTelegram()
{
    $user_id = $_SESSION['user_id'];
    $link = ConnectDB();

    $timeout = 50;
    $startTime = time();

    $stmt = $link->prepare("SELECT telegram_enable FROM users WHERE id = ?");

    if (!$stmt) 
    {
        die(json_encode(['error' => 'Unknown Error']));
    }

    $stmt->bind_param("i", $user_id);

    while ((time() - $startTime) < $timeout)
    {
        if (!$stmt->execute()) 
        {
            die(json_encode(['error' => 'Unknown Error']));
        }
        $stmt->store_result();

        if ($stmt->num_rows > 0)
        {
            $stmt->bind_result($telegram_enable);
            $stmt->fetch();

            if ($telegram_enable == 1) 
            {
                echo json_encode([
                    'success' => true,
                    'status' => 'success'
                ]);

                $stmt->close();
                exit;
            }
        }

        sleep(5);
    }

    echo json_encode([
        'success' => true,
        'status' => 'timeout'
    ]);
    $link->close();
    exit;
}

function DisableTelegram()
{
    $user_id = $_SESSION['user_id'];
    $link = ConnectDB();

    $telegramManager = new TelegramManager($link);
    $user = $link->query("SELECT * FROM `users` WHERE `id`='$user_id'")->fetch_array();

    $stmt = $link->prepare("UPDATE `users` SET `telegram_enable`= 0, `chat_id`= NULL WHERE `id`= ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) 
    {
        $telegramManager->NotifyTelegramUnlink($user);
        echo json_encode(['success' => 'Telegram Disabled']);
    }
    else
    {
        echo json_encode(['error' => 'Unknown Error']);
    }
}

?>