<?php

session_start();

if (isset($_SESSION['user_id'])) 
{
    header("Location: dashboard");
    exit();
}

include_once "../config.php";
include_once "app/functions.php";
include_once 'app/managers/TelegramManager.php';
include_once 'app/utils/GoogleAuthenticator.php';
include_once 'app/utils/UserAgentParser.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $username   = trim($_POST['username']);
    $password   = $_POST['password'];
    $captcha    = strtoupper(trim($_POST['captcha']));
    $twofa_code = strtoupper(trim($_POST['twofa_code']));

    $link = ConnectDB();

    if ($captcha !== $_SESSION['captcha_code']) 
    {
        $error = "Invalid captcha code";
    } 
    else
    {
        // Определяем порог бездействия из настроек PHP для сессий (в секундах)
        $gcMaxLifetime = (int) ini_get('session.gc_maxlifetime');

        // Помечаем просроченные сессии как неактивные
        $stmt = $link->prepare("UPDATE users_sessions SET active = 0 WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? SECOND) AND active = 1");
        $stmt->bind_param("i", $gcMaxLifetime);
        $stmt->execute();
        $stmt->close();

        $stmt = $link->prepare("SELECT * FROM users WHERE username = ? AND active = 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) 
        {
            if (password_verify($password, $user['password'])) 
            {
                if ($user['twofa_status'] == 1)
                {
                    $ga = new PHPGangsta_GoogleAuthenticator();
                    $checkResult = $ga->verifyCode($user['twofa_secret'], $twofa_code, 0);

                    if (!$checkResult) 
                    {
                        $error = "Incorrect 2FA code";
                    }
                }

                if (!isset($error))
                {
                    $telegramManager = new TelegramManager($link);

                    $_SESSION['user_id']        = $user['id'];
                    $_SESSION['username']       = $username;
                    $_SESSION['user_group']     = $user['user_group'];
                    $_SESSION['builds']         = $user['builds'];
                    $_SESSION['created_at']     = $user['created_at'];
                    $_SESSION['theme']          = $user['theme'];
                    
                    $stmt = $link->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->bind_param("i", $user['id']);
                    $stmt->execute();
                    
                    session_regenerate_id(true);
                    $session_id = session_id();
                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    $stmt = $link->prepare("INSERT INTO users_sessions (session_id, user_id, user_agent, active) VALUES (?, ?, ?, 1)");
                    $stmt->bind_param("sis", $session_id, $user['id'], $user_agent);
                    $stmt->execute();

                    $telegramManager->NotifyLogin($user, $user_agent);
                    
                    header("Location: dashboard");
                    exit();
                }
            }
            else
            {
                $error = "Incorrect login or password";
            }
        }
        else
        {
            $error = "Incorrect login or password";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="stylesheet" href="assets/css/styles.css" />
    <title>Login</title>
    <style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }

    input[type=number] {
    -moz-appearance: textfield;
    }
    </style>
</head>

<body>
    <div id="main-wrapper">
        <div class="position-relative overflow-hidden min-vh-100 w-100 d-flex align-items-center justify-content-center" style="background: white;">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100 my-5 my-xl-0">
                    <div class="col-md-9 d-flex flex-column justify-content-center">
                        <div class="card mb-0 bg-body auth-login m-auto w-100" style="max-width: 600px; --bs-card-box-shadow: none;">
                            <div class="row gx-0">
                                
                                <div>
                                    <div class="row justify-content-center py-4">
                                        <div class="col-lg-11">
                                            <div class="card-body">
                                                <h2 class="mb-2 mt-4 fs-7 fw-bolder">Sign In</h2>
                                                <p class="mb-9">Your Admin Dashboard</p>

                                                <?php if(isset($error)): ?>
                                                <div class="alert alert-danger text-danger" role="alert"><strong>Error - </strong> <?php echo htmlspecialchars($error); ?></div>
                                                <?php endif; ?>
                                                
                                                <form method="POST" action="login">
                                                    <div class="mb-3">
                                                        <label for="login" class="form-label">Login</label>
                                                        <input type="text" name="username" class="form-control" id="login" placeholder="Enter your login" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="password" class="form-label">Password</label>
                                                        <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="twofa_code" class="form-label">2FA Code</label>
                                                        <input type="number" name="twofa_code" class="form-control" id="twofa_code" placeholder="Enter 2FA code" oninput="if(this.value.length > 6) this.value = this.value.slice(0,6)" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                                                    </div>
                                                    <label for="captcha" class="form-label">Captcha</label>
                                                    <div class="mb-1 d-flex align-items-center">
                                                        <img src="captcha" class="me-2" alt="Captcha">
                                                        <input type="text" name="captcha" class="form-control" id="captcha" placeholder="Enter captcha" oninput="if(this.value.length > 6) this.value = this.value.slice(0,6)" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                                                    </div>

                                                    <br>
                                                    <button type="submit" class="btn btn-dark w-100 py-8 mb-4 rounded-1">Sign In</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>