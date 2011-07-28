<?php
/**
 * GNU AGPL v3
 *
 * @package duesenchat
 */

require_once 'Server.php';

$action = $_GET['action'];

$s = new Server();

if($action == 'send'){
    $msg = $_GET['msg'];
    $data = decipherMsg($msg);
    $s->sendMessage($data['To'], $data['msg']);
    echo "Message $msg sent to user $user";
} else {
    $user = $_GET['user'];
    $buf = $s->pollMessage($user);
    echo $buf;
}

function decipherMsg($msg){
    $teile = split("\n", $msg, 2);
    $crypto = $teile[0];
    $text = $teile[1];
    if($crypto == "plain"){
        return readHeader($text);
    }
    return array();
}

function readHeader($text) {
    $erg = array();
    $rest = $text;
    while(substr($rest, 0, 1) != "\n"){ // Leerzeile leitet die Nachricht ein
        $teile = split("\n", $rest, 2); // 1. Zeile abtrennen
        $header = $teile[0];
        $rest =$teile[1];
        $pair = split(": ", $header, 2); // Key/Value Pair lesen
        $erg[$pair[0]] = $pair[1];
    }
    $erg['msg'] = substr($rest, 1); // \n man anfang wegmachen
    return $erg;
}



# kate: space-indent on; indent-width 4; mixedindent off; indent-mode cstyle; dynamic-word-wrap on; line-numbers on;