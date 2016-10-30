<?php

function get_language($db, $name) {
  $pdo = $db->getPDO();

  $q = $pdo->prepare('select id, name from languages where name = ?;');
  $q->bindValue(1, $name, PDO::PARAM_STR);
  $q->execute();
  return $q->fetch(PDO::FETCH_ASSOC);
}

function create_language($db, $name) {
  $pdo = $db->getPDO();

  $language = [
    'name' => $name
  ];

  $q = $pdo->prepare('insert into languages (name), values (?) returning id;');
  $q->bindValue(1, $name, PDO::PARAM_STR);
  $q->execute();
  $language['id'] = $q->fetchColumn();

  return $language;
}

function get_or_create_language($db, $name) {
  $language = get_language($db, $name);

  if (!$language) {
    try {
      $language = create_language($db, $name);
    }
    catch (PDOException $e) {
      $language = get_language($db, $name);
    }
  }

  return $language;
}

function pulse($request_data, $db) {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo 'Only post allowed!';
    return;
  }

  $pdo = $db->getPDO();

  $data = json_decode(file_get_contents('php://input'), true);

  $datetime = new DateTimeImmutable($data['coded_at']);
  $now = new DateTimeImmutable('now');

  $diff = $datetime->diff($now);

  if ($diff->days > 14) {
    http_response_code(400);
    echo 'Invalid date.';
    return;
  }

  $q = $pdo->prepare('insert into pulses (sent_at, user_id, inserted_at, updated_at, machine_id) values (?, ?, ?, ?, ?) returning id;');
  $q->bindValue(1, $datetime->format('c'), PDO::PARAM_STR);
  $q->bindValue(2, $request_data['user']['id'], PDO::PARAM_INT);
  $q->bindValue(3, $now->format('c'), PDO::PARAM_STR);
  $q->bindValue(4, $now->format('c'), PDO::PARAM_STR);
  $q->bindValue(5, $request_data['machine']['id'], PDO::PARAM_INT);
  $q->execute();

  $pulse_id = $q->fetchColumn();

  foreach ($data['xps'] as $xp) {
    $language = get_or_create_language($db, $xp['language']);

    $q = $pdo->prepare('insert into xps (amount, pulse_id, language_id, inserted_at, updated_at) values (?, ?, ?, ?, ?);');
    $q->bindValue(1, $xp['xp'], PDO::PARAM_INT);
    $q->bindValue(2, $pulse_id, PDO::PARAM_INT);
    $q->bindValue(3, $language['id'], PDO::PARAM_INT);
    $q->bindValue(4, $now->format('c'), PDO::PARAM_STR);
    $q->bindValue(5, $now->format('c'), PDO::PARAM_STR);
    $q->execute();
  }

  // Open ZMQ connection and push pulse data to it
  $socket = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_PUSH, 'pushersocket');
  $socket->connect('tcp://127.0.0.1:8079');
  $socket->send(json_encode([
    'username' => $request_data['user']['username'],
    'machine' => $request_data['machine']['name'],
    'xps' => $data['xps']
  ]));

  http_response_code(201);
  echo json_encode(["ok" => "Great success!"]);
}
