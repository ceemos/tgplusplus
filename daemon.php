<?php
/**
 * GNU AGPL v3
 *
 * @package duesenchat
 */

class Daemon {
    private $socket;
    
    private $port = 13456;
    
    
    
    function start(){
        //`echo Start > log.txt`;
        $this->socket = socket_create_listen($this->port);
    }
    
    function run(){
        $clients = array();
        while(true){
            //`echo run >> log.txt`;
            // sleep(1);
            $client = socket_accept($this->socket);
            $clients[] = $client;
            $user = socket_read($client, 1024, PHP_NORMAL_READ);
            $buf = "User $user connected.";
            foreach($clients as $c) {
                socket_write($c, $buf, strlen($buf));
                
            }
            
        }
    }
}

$d = new Daemon();

$res = $d->start();

ignore_user_abort(1); // vom Aufrufenden Trennen
header("Content-Length: 0");
header("Connection: close");
flush();
$d->run(); // forever


# kate: space-indent on; indent-width 4; mixedindent off; indent-mode cstyle; dynamic-word-wrap on; line-numbers on;