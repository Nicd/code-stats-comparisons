<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require __DIR__ . '/vendor/autoload.php';

require '../php/profile.page.php';
require '../php/utils.php';
require '../php/config.php';
require '../php/db.php';

$db = new DB(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);

function process_cache($user, $cache) {
  $now = new \DateTimeImmutable('now');
  $latest_xp_since = $now->sub(new \DateInterval('PT12H'));
  $new_language_xps = get_language_xps_since($db, $user, $latest_xp_since);
  $new_machine_xps = get_machine_xps_since($db, $user, $latest_xp_since);

  $languages = array_map(function ($value) use ($new_language_xps) {
    list($language, $amount) = $value;

    $new_xp = get($new_language_xps, $language['id'], 0);

    return [
      'name' => $language['name'],
      'xp' => $amount,
      'new_xp' => $new_xp
    ];
  }, $cache['languages']);

  $machines = array_map(function ($value) use ($new_machine_xps) {
    list($machine, $amount) = $value;

    $new_xp = get($new_machine_xps, $machine['id'], 0);

    return [
      'name' => $machine['name'],
      'xp' => $amount,
      'new_xp' => $new_xp
    ];
  }, $cache['machines']);

  return [
    'total' => [
      'xp' => array_reduce($languages, function ($acc, $val) {
        return $acc + $val['xp'];
      }, 0),
      'new_xp' => array_reduce($languages, function ($acc, $val) {
        return $acc + $val['new_xp'];
      }, 0)
    ],
    'languages' => $languages,
    'machines' => $machines
  ];
}

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

    // Simulate code executed by Code::Stats on channel join
    $split_topic = explode(':', $topic);
    if (count($split_topic) > 1) {
      $username = $split_topic[1];

      $user = get_user($db, $user);

      if (!$user['private_profile']) {
        $updated_cache = get_cached_xps($db, $user);
        $preloaded_cache = preload_cached_xps($db, $updated_cache, $user);
        $processed_cache = process_cache($user, $preloaded_cache);

        $conn->send(json_encode($processed_cache));
      }
    }
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

$app = new Ratchet\App('192.168.10.59', 1337, '0.0.0.0', $loop);
$app->route('/live_update_socket/websocket', $channels);
$app->run();
