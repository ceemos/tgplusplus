<?php
/**
 * GNU AGPL v3
 *
 * @package duesenchat
 */
class Server {
    // Socket zum internen Daemon
    private $socket;
    // Port des Daemon
    private $port = 13456;
    // URL, um den Daemon zu starten
    private $daemon = "http://localhost/~marcel/duesenchat/daemon.php";
    
    /**
     * Öffnet die Verbindung zum Daemon.
     * Der Daemon könnte danach blockiert sein, weil er auf eine Reaktion wartet.
     */
    private function connect (){
        // TCP-Socket
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        // Versuchen zu connecten
        $res = socket_connect($this->socket, "localhost", $this->port);
        // wenns net klappt, den Daemon zu starten versuchen
        if(!$res){
            echo "Starting daemon";
            ob_flush();
            $daemon = file_get_contents($this->daemon);
            sleep(1);
            echo " retry...";
            $res = socket_connect($this->socket, "localhost", $this->port);
            echo " res = $res";
            if(!$res){
                exit("Unable to connect to Server");
            }
        }
    }
    
    /**
     * Sendet eine Nachricht an den Empfänger rcpt.
     * 
     */
    public function sendMessage($rcpt, $data){
        $this->connect();
        $buf = "POST $rcpt\n";
        socket_write($this->socket, $buf, strlen($buf));
        socket_write($this->socket, $data, strlen($data));
        socket_close($this->socket);
    }
    
    /**
     * Ruft eine Nachricht für den User ab.
     * 
     * gibt false zurück, wenn die Wartezeit abgelaufen ist.
     * 
     */
    public function pollMessage($user){
        $this->connect();
        $buf = "POLL $user\n";
        socket_write($this->socket, $buf, strlen($buf));
        $data = socket_read($this->socket, 1024);
        socket_close($this->socket);
        return $data;
        
    }

}

# kate: space-indent on; indent-width 4; mixedindent off; indent-mode cstyle; dynamic-word-wrap on; line-numbers on;

