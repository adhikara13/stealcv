<?php

class TelegramManager
{
    private $connection;
    private $settings;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->settings = [];

        $this->getSettings();
    }

    public function NotifyLogin($user, $user_agent)
    {
        if ($user['telegram_enable'] === 1 && $user['notify_logins'] === 1) 
        {
            $browserInfo = parseUserAgent($user_agent);

            $message = sprintf(
                "⚠️ <b>New Login</b>: %s (%s)\nDate: %s",
                $browserInfo['browser'],
                $browserInfo['version'],
                date("d.m.Y H:i:s")
            );

            $this->SendMessage($user["chat_id"], $message);
        }
    }

    public function NotifyChangePassword($user)
    {
        if ($user['telegram_enable'] == 1 && $user['notify_password_change'] == 1) 
        {
            $message = sprintf(
                "⚠️ <b>WARNING</b>: Password have been changed!\nDate: %s",
                date("d.m.Y H:i:s")
            );

            $this->SendMessage($user["chat_id"], $message);
        }
    }

    public function NotifyChangeTwoFa($user)
    {
        if ($user['telegram_enable'] == 1 && $user['notify_twofa_change'] == 1) 
        {
            $message = sprintf(
                "⚠️ <b>WARNING</b>: Two-factor authentication settings have been changed!\nDate: %s",
                date("d.m.Y H:i:s")
            );

            $this->SendMessage($user["chat_id"], $message);
        }
    }

    public function NotifyTelegramUnlink($user)
    {
        if ($user['telegram_enable'] == 1) 
        {
            $message = sprintf(
                "⚠️ <b>WARNING</b>: Your Telegram account has been unlinked!\nDate: %s",
                date("d.m.Y H:i:s")
            );

            $this->SendMessage($user["chat_id"], $message);
        }
    }

    public function SendMessage($chat_id, $message)
    {
        $ch = curl_init();
        $url = "https://api.telegram.org/bot" . $this->settings["telegram_token"] ."/sendMessage";
		
		$post_fields = array
		(
			'chat_id'   => $chat_id,
			'disable_web_page_preview' => false,
			'text'	=> $message,
			'parse_mode' => 'HTML',
		);
				
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

		curl_exec($ch);
    }

    public function getSettings()
    {
        $query = "SELECT setting_key, setting_value FROM settings";
        $result = $this->connection->query($query);

        if ($result) 
        {
            while ($row = $result->fetch_assoc()) 
            {
                $this->settings[$row['setting_key']] = $row['setting_value'];
            }
            $result->free();
        }
        else
        {
            exit();
        }


    }
}

?>