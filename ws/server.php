<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require __DIR__ . '/vendor/autoload.php';

class Channels implements MessageComponentInterface {
  private $clients;
  private $rooms;
  private $queue;

  public function __construct() {
    $this->clients = new \SplObjectStorage();
    $this->topics = [];
  }

  public function onOpen(ConnectionInterface $conn) {
    $this->clients->attach($conn);
  }

  // Join message: {"topic":"users:Nicd","event":"phx_join","payload":{},"ref":"1"}
  public function onMessage(ConnectionInterface $from, $msg) {
    $data = json_decode($msg, true);

    if ($data === null) {
      return;
    }

    if (isset($data['event']) && $data['event'] === 'phx_join') {
      $this->joinTopic($from, $data['topic']);
    }
  }

  public function onZMQMessage($msg) {
    $msg = json_decode($msg, true);
    $username = $msg['username'];

    // Out payload frontpage: {"topic":"frontpage","ref":null,"payload":{"xps":[{"xp":2,"username":"Nicd","language":"PHP"}]},"event":"new_pulse"}
    if (array_key_exists('frontpage', $this->topics)) {
      $payload = ['xps' => []];
      foreach ($msg['xps'] as $xp) {
        $payload['xps'][] = [
          'username' => $username,
          'xp' => $xp['xp'],
          'language' => $xp['language']
        ];
      }

      $this->broadcast('frontpage', json_encode([
        'topic' => 'frontpage',
        'ref' => 'null',
        'payload' => $payload,
        'event' => 'new_pulse'
      ]));
    }

    // Out payload profile: {"topic":"users:Nicd","ref":null,"payload":{"xps":[{"machine":"Kerosene","language":"PHP","amount":2}]},"event":"new_pulse"}
    if (array_key_exists("users:$username", $this->topics)) {
      $machine = $msg['machine'];
      $payload = ['xps' => []];

      foreach ($msg['xps'] as $xp) {
        $payload['xps'][] = [
          'machine' => $machine,
          'xp' => $xp['xp'],
          'language' => $xp['language']
        ];
      }

      $this->broadcast("users:$username", json_encode([
        'topic' => "users:$username",
        'ref' => 'null',
        'payload' => $payload,
        'event' => 'new_pulse'
      ]));
    }
  }

  public function onClose(ConnectionInterface $conn) {
    foreach ($this->topics as $topic) {
      $topic->detach($conn);
    }

    $this->clients->detach($conn);
  }

  public function onError(ConnectionInterface $conn, \Exception $e) {
    $conn->close();
  }

  private function joinTopic(ConnectionInterface $conn, string $topic) {
    if (!array_key_exists($topic, $this->topics)) {
      $this->topics[$topic] = new \SplObjectStorage();
    }

    $this->topics[$topic]->attach($conn);
  }

  private function broadcast(string $topic, string $msg) {
    foreach ($this->topics[$topic] as $client) {
      $client->send($msg);
    }
  }
}

$channels = new Channels();

$loop = React\EventLoop\Factory::create();

$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://127.0.0.1:8079');
$pull->on('message', array($channels, 'onZMQMessage'));

$app = new Ratchet\App('localhost', 8081, '0.0.0.0', $loop);
$app->route('/live_update_socket', $channels);
$app->run();
