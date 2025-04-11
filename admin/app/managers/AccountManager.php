<?php

class AccountManager
{
    private $connection;

    // ---------------------------------------------------------------------------------------
    // __construct
    //
    // class constructor
    // ---------------------------------------------------------------------------------------
    public function __construct($connection)
    {
        $this->connection = $connection;

        
    }

    // ---------------------------------------------------------------------------------------
    // CheckAuth
    //
    // Check user auth
    // ---------------------------------------------------------------------------------------
    public function CheckAuth()
    {
        if (!isset($_SESSION['user_id'])) 
        {
            header("Location: login");
            exit();
        }

        $currentSessionId = session_id();

        $stmt = $this->connection->prepare("SELECT session_id FROM users_sessions WHERE session_id = ? AND user_id = ? AND active = 1");

        if ($stmt) 
        {
            $stmt->bind_param("si", $currentSessionId, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            // Если запись не найдена, считаем сессию недействительной и разлогиниваем пользователя
            if ($result->num_rows === 0) 
            {
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
            }
            $stmt->close();
        } 
        else 
        {
            // Если произошла ошибка подготовки запроса — разлогиниваем
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
        }

        $stmt = $this->connection->prepare("UPDATE users_sessions SET last_activity = NOW() WHERE session_id = ?");

        if ($stmt) 
        {
            $stmt->bind_param("s", $currentSessionId);
            $stmt->execute();
            $stmt->close();
        }
    }

    // ---------------------------------------------------------------------------------------
    // getSeed
    //
    // user seed
    // ---------------------------------------------------------------------------------------
    public function getSeed()
    {
        $seed = sha1($_SESSION['username'] ."_". $_SESSION['created_at']);

        return $seed;
    }
}

?>