<?php

include_once 'app/utils/AvatarGenerator.php';

$seed = isset($_GET['seed']) ? $_GET['seed'] : 'default_seed';
$avatar = new AvatarGenerator($seed, 200, 200, 5, 5);
$avatar->output();

?>