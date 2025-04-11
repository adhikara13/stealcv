<?php

class Log
{
    public $log_id;
    public $bot_id;
    public $build;
    public $access_token;
    public $ip;
    public $iso;
    public $date;
    public $hwid;
    public $system;
    public $architecture;
    public $decrypt_keys;
    public $count_passwords;
    public $count_cookies;
    public $count_wallets;
    public $count_cc;
    public $array_passwords;
    public $array_cookies;
    public $array_wallets;
    public $information;
    public $screenshot;
    public $repeated;
    public $download;
    public $favorite;
    public $comment;
    public $filename;
    public $log_info;
    public $log_status;
    public $size;

    // ---------------------------------------------------------------------------------------
    // __construct
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function __construct($row)
    {
        $this->log_id               = $row["log_id"];
        $this->bot_id               = $row["bot_id"];
        $this->build                = $row["build"];
        $this->access_token         = $row["access_token"];
        $this->ip                   = $row["ip"];
        $this->iso                  = $row["iso"];
        $this->date                 = $row["date"];
        $this->hwid                 = $row["hwid"];
        $this->system               = $row["system"];
        $this->architecture         = $row["architecture"];
        $this->decrypt_keys         = json_decode($row["decrypt_keys"], true);
        $this->count_passwords      = $row["count_passwords"];
        $this->count_cookies        = $row["count_cookies"];
        $this->count_wallets        = $row["count_wallets"];
        $this->count_cc             = $row["count_cc"];
        $this->array_passwords      = $row["array_passwords"];
        $this->array_cookies        = $row["array_cookies"];
        $this->array_wallets        = $row["array_wallets"];
        $this->information          = $row["information"];
        $this->screenshot           = $row["screenshot"];
        $this->repeated             = $row["repeated"];
        $this->download             = $row["download"];
        $this->favorite             = $row["favorite"];
        $this->comment              = $row["comment"];
        $this->filename             = $row["filename"];
        $this->log_info             = json_decode($row["log_info"], true);
        $this->log_status           = $row["log_status"];
        $this->size                 = $row["size"];
    }

    // ---------------------------------------------------------------------------------------
    // UpdateSize
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function UpdateSize($size)
    {
        $this->size += $size;
    }

    // ---------------------------------------------------------------------------------------
    // UpdatePasswordsCount
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function UpdatePasswordsCount($count)
    {
        $this->count_passwords += $count;
    }

    // ---------------------------------------------------------------------------------------
    // UpdateWalletsCount
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function UpdateWalletsCount($count)
    {
        $this->count_wallets += $count;
    }
}

?>