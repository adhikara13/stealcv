<?php

class Build
{
    public $build_id;
    public $name;
    public $password;
    public $version;
    public $logs_count;
    public $active;

    public $self_delete;
    public $take_screenshot;
    public $block_hwid;
    public $block_ips;

    public $loader_before_grabber;

    public $steal_telegram;
    public $steal_discord;
    public $steal_tox;
    public $steal_pidgin;

    public $steal_steam;
    public $steal_battlenet;
    public $steal_uplay;

    public $steal_protonvpn;
    public $steal_openvpn;

    public $steal_outlook;
    public $steal_thunderbird;

    public function __construct($row)
    {
        $this->build_id             = $row["build_id"];
        $this->name                 = $row["name"];
        $this->password             = $row["password"];
        $this->version              = $row["version"];
        $this->logs_count           = $row["logs_count"];
        $this->active               = $row["active"];

        $this->self_delete          = $row["self_delete"];
        $this->take_screenshot      = $row["take_screenshot"];
        $this->block_hwid           = $row["block_hwid"];
        $this->block_ips            = $row["block_ips"];

        $this->loader_before_grabber = $row["loader_before_grabber"];

        $this->steal_telegram       = $row["steal_telegram"];
        $this->steal_discord        = $row["steal_discord"];
        $this->steal_tox            = $row["steal_tox"];
        $this->steal_pidgin         = $row["steal_pidgin"];

        $this->steal_steam          = $row["steal_steam"];
        $this->steal_battlenet      = $row["steal_battlenet"];
        $this->steal_uplay          = $row["steal_uplay"];

        $this->steal_protonvpn      = $row["steal_protonvpn"];
        $this->steal_openvpn        = $row["steal_openvpn"];

        $this->steal_outlook        = $row["steal_outlook"];
        $this->steal_thunderbird    = $row["steal_thunderbird"];
    }
}

?>