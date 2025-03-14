<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $db;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->db = new mysqli('localhost', 'root', '', 'chat');
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Message received: $msg\n"; // Add this line
        $data = json_decode($msg, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($data['username']) && isset($data['message'])) {
            $username = $this->db->real_escape_string($data['username']);
            $message = $this->db->real_escape_string($data['message']);

            $this->db->query("INSERT INTO messages (username, message) VALUES ('$username', '$message')");

            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send("$username: $message");
                }
            }
        } else {
            echo "Invalid message format received\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8081
);

$server->run();