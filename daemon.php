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
        `echo Start > log.txt`;
        if(socket_connect($this->socket, "localhost", $this->port)){
            exit(0);
        }
        $this->socket = socket_create_listen($this->port);
    }
    
    function run(){
        $clients = array();
        $messages = array();
        while(true){
            `echo run >> log.txt`;
            $client = socket_accept($this->socket);
            $request = socket_read($client, 1024, PHP_NORMAL_READ);
            $rq = split(" ", $request, 2);
            $user = trim($rq[1]);
            if($user == ""){ 
                // Kaputte User, scheint manchmal zu passieren?
                socket_close($client);
                continue;
            }
            `echo "Got User $user" >> log.txt`;
            $mode = $rq[0];
            if($mode == 'POST'){
                // Nachricht lesen
                $msg = socket_read($client, 1024, PHP_BINARY_READ);
                socket_close($client);
                // Nachricht im Buffer lagern
                if(!array_key_exists($user, $messages)){
                    $messages[$user] = array();
                }
                $messages[$user][] = $msg;
                `echo "Got msg $msg" >> log.txt`;
            } else { 
                `echo "remembering client" >> log.txt`;
                //Poll
                if(array_key_exists($user, $clients)){
                    $clients[$user][] = $client;
                } else {
                     $clients[$user] = array($client);
                }
               
            }
            unset($client);
            unset($request);
            unset($rq);
            unset($mode);
            unset($user);
            
            // Jetzt nach zustellbaren Nachrichten suchen
            foreach($messages as $user => $msgs) {
                `echo "Found msg $msgs[0] for user $user" >> log.txt`;
                if(array_key_exists($user, $clients)){
                    `echo "Found {$user}s Socket" >> log.txt`;
                    $msg = array_shift($msgs);
                    
                    foreach($clients[$user] as $client) { // mehrere Clients versorgen
                        socket_write($client, $msg, strlen($msg));
                        socket_close($client);
                    }
                    unset($clients[$user]); // Sockets sind "Einweg"   
                    
                    if(count($msgs) == 0){ // falls das die letzte Nachricht dieses Users war 
                        unset($messages[$user]);;
                    } else {
                        $messages[$user] = $msgs;
                    }
                    `echo "sent msg." >> log.txt`;
                }
            } 
        }
    }
}

if(isset($_GET["kill"])){
    `cat daemon-*.pid > daemon.pid`;
    $oldpid = file_get_contents("daemon.pid");
    `kill -9 $oldpid`;
    exit(0);
}
$pid = getmypid();
file_put_contents("daemon-$pid.pid", $pid . " ");

$d = new Daemon();

$res = $d->start();

ignore_user_abort(1); // vom Aufrufenden Trennen
header("Content-Length: 0");
header("Connection: close");
flush();
$d->run(); // forever


# kate: space-indent on; indent-width 4; mixedindent off; indent-mode cstyle; dynamic-word-wrap on; line-numbers on;