<?php

class FileRule
{
    public $name;
    public $type;
    public $csidl;
    public $start_path;
    public $masks;
    public $recursive;
    public $max_size;
    public $iterations;

    public function __construct($row)
    {
        $this->name                     = $row["name"];
        $this->type                     = $row["type"];
        $this->csidl                    = $row["csidl"];
        $this->start_path               = $row["start_path"];
        $this->masks                    = $row["masks"];
        $this->recursive                = (bool) $row["recursive"];
        $this->max_size                 = $row["max_size"];
        $this->iterations               = $row["iterations"];
    }
}

?>