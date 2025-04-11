<?php

/*
* ConnectDB
* return $link
*/
function ConnectDB()
{
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die('No connect');
	mysqli_set_charset($link, 'utf8' );
	
	return $link;
}

/*
* CloseConnection
* 
*/
function CloseConnection($link)
{
	mysqli_close($link);
}

function generateRandomString($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) 
    {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

?>