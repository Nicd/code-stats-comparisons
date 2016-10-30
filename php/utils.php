<?php

// Convert all errors to exceptions
function exception_error_handler($severity, $message, $file, $line) {
  if (!(error_reporting() & $severity)) {
    return;
  }
  throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");

// Get from array with default value
function get($arr, $key, $default=null) {
  if (array_key_exists($key, $arr)) {
    return $arr[$key];
  }

  return $default;
}

function format_xp($xp) {
  return number_format($xp);
}

function get_user($db, $username) {
  $pdo = $db->getPDO();
  $q = $pdo->prepare('SELECT u0."id", u0."username", u0."email", u0."password", u0."last_cached", u0."private_profile", u0."cache", u0."inserted_at", u0."updated_at" FROM "users" AS u0 WHERE (u0."username" = ?)');
  $q->bindValue(1, $username, PDO::PARAM_STR);
  $q->execute(); // We don't need to handle the "not found case", assume it's always found
  return $q->fetch(PDO::FETCH_ASSOC);
}
