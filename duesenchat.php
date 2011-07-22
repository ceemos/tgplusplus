<?php
/**
 * GNU AGPL v3
 *
 * @package duesenchat
 */

require_once 'Server.php';


$user = $_GET['user'];
$action = $_GET['action'];
$s = new Server();


if($action == 'send'){
    $msg = $_GET['msg'];
    $s->sendMessage($user, $msg);
    echo "Message $msg sent to user $user";
} else {
    $buf = $s->pollMessage($user);
    echo "got Message: \n $buf";
}


# kate: space-indent on; indent-width 4; mixedindent off; indent-mode cstyle; dynamic-word-wrap on; line-numbers on;