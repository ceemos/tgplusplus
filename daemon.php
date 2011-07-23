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
                // evtl. alte Verbindung schließen
                if(array_key_exists($user, $clients)){
                    socket_close($clients[$user]);
                }
                $clients[$user] = $client;
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
                    $client = $clients[$user];
                    $msg = array_shift($msgs);
                    socket_write($client, $msg, strlen($msg));
                    socket_close($client);
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

$d = new Daemon();

$res = $d->start();

ignore_user_abort(1); // vom Aufrufenden Trennen
header("Content-Length: 0");
header("Connection: close");
flush();
$d->run(); // forever


# kate: space-indent on; indent-width 4; mixedindent off; indent-mode cstyle; dynamic-word-wrap on; line-numbers on;