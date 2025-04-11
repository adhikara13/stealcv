<?php

class MarkersManager
{
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
    // Scan
    //
    // Search markers in files
    // ---------------------------------------------------------------------------------------
    public function Scan($log, $data)
    {
        $markers = $this->connection->query("SELECT * FROM `markers` WHERE `active` = 1;");
	
        while ($marker = $markers->fetch_assoc())
        {
            $_marker = explode(',', $marker["urls"]);
            $_name = $marker["name"];
            
            foreach ($_marker as $_url)
            {
                if ($this->match_wildcard($_url, $data) == 1)
                {
                    $log->log_info["marker"][$_name][$_url]["count"]++;
                    $log->log_info["marker"][$_name][$_url]["color"] = $marker["color"];
                }
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // match_wildcard
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function match_wildcard($pattern, $value)
    {
        if ($pattern == $value) return true;
        
        $value = explode("\n", $value);
        $pattern = str_replace('*', '([-\w]+.)+', $pattern);
        
        foreach($value as &$str)
        {
            if(preg_match('/'.$pattern.'/', $str))
            {
                return true;
            }
        }
    
        return false;
    }
}

?>