<?php

class Plugin
{
    public $name;
    public $token;
    public $from_local;
    public $from_sync;
    public $from_IndexedDB;

    public function __construct($row)
    {
        $this->name                     = $row["name"];
        $this->token                    = $row["token"];
        $this->from_local               = (bool) $row["from_local"];
        $this->from_sync                = (bool) $row["from_sync"];
        $this->from_IndexedDB           = (bool) $row["from_IndexedDB"];
    }
}

?>