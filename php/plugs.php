<?php

// Approximations of the code-stats plugs

const API_TOKEN = 'SFMyNTY.VG1salpBPT0jI05RPT0.aUP9RqQp-7UWbFcx_cuCZQYmv98ix6vpvqJ1II3_jyQ';
const API_USERNAME = 'Nicd';
const API_MACHINE = 5;

function api_auth_required($request_data, $db) {
  $pdo = $db->getPDO();

  if (!isset($_SERVER['HTTP_X_API_TOKEN'])) {
    http_response_code(400);
    echo 'API token must be given!';
    exit();
  }

  $token = $_SERVER['HTTP_X_API_TOKEN'];

  if ($token === API_TOKEN) {
    $user = get_user($db, API_USERNAME);

    $q = $pdo->prepare('SELECT m0."id", m0."name", m0."api_salt", m0."user_id", m0."inserted_at", m0."updated_at", m0."id" FROM "machines" AS m0 WHERE (m0."user_id" = ?) AND (m0."id" = ?);');
    $q->bindValue(1, $user['id'], PDO::PARAM_INT);
    $q->bindValue(2, API_MACHINE, PDO::PARAM_INT);
    $q->execute();
    $machine = $q->fetch(PDO::FETCH_ASSOC);

    $request_data['user'] = $user;
    $request_data['machine'] = $machine;
  }

  return $request_data;
}

function set_session_user($request_data, $db) {
  $pdo = $db->getPDO();

  if ($_COOKIE['_code_stats_key'] === LOGIN_COOKIE) {
    $q = $pdo->prepare('SELECT u0."id", u0."username", u0."email", u0."password", u0."last_cached", u0."private_profile", u0."cache", u0."inserted_at", u0."updated_at" FROM "users" AS u0 WHERE (u0."id" = ?)');
    $q->bindValue(1, LOGIN_USERID, PDO::PARAM_INT);
    $q->execute(); // We don't need to handle the "not found case", assume it's always found
    $request_data['user'] = $q->fetch(PDO::FETCH_ASSOC);
  }

  return $request_data;
}

function request_time($request_data, $db) {
  $request_data['request_start_time'] = microtime(true);
  return $request_data;
}

function calculate_request_time($time) {
  $now = microtime(true);
  $diff = $now - $time;

  return humanize_seconds($diff);
}

const UNIT_MAPPING = [
  [1, 's'],
  [1000, 'ms'],
  [1000 * 1000, 'µs'],
  [1000 * 1000 * 1000, 'ns']
];

function humanize_seconds($diff) {
  foreach (UNIT_MAPPING as $unit) {
    $m = $diff * $unit[0];
    if ($m > 1) {
      return [$m, $unit[1]];
    }
  }
}
