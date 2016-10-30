<?php

const LEVEL_FACTOR = 0.025;

function get_level($xp) {
  return (int) floor(LEVEL_FACTOR * sqrt($xp));
}

function get_next_level_xp($level) {
  return (int) pow(ceil(($level + 1) / LEVEL_FACTOR), 2);
}

function get_level_progress($xp) {
  $level = get_level($xp);
  $current_level_xp = get_next_level_xp($level - 1);
  $next_level_xp = get_next_level_xp($level);

  $have_xp = $xp - $current_level_xp;
  $needed_xp = $next_level_xp - $current_level_xp;

  return (int) round(($have_xp / $needed_xp) * 100);
}

function get_xp_bar_widths($total_xp, $new_xp) {
  $level = get_level($total_xp);
  $current_level_xp = get_next_level_xp($level - 1);

  $have_xp = $total_xp - $current_level_xp;

  if ($have_xp > $new_xp) {
    return [
      get_level_progress($total_xp - $new_xp),
      get_level_progress($total_xp) - get_level_progress($total_xp - $new_xp)
    ];
  }
  else {
    return [
      0,
      get_level_progress($total_xp)
    ];
  }
}

const LANG_XP_AMNT = 10;

function has_language_xps¿($language_xps) {
  return !empty($language_xps);
}

function has_more_language_xps¿($language_xps) {
  return count($language_xps) > LANG_XP_AMNT;
}

function has_machine_xps¿($machine_xps) {
  return !empty($machine_xps);
}

function can_access_profile($authed_user, $user) {
  return ($user['private_profile'] === false || $authed_user['id'] === $user['id']);
}

function str_keys_to_int($arr) {
  $keys = array_keys($arr);
  $vals = array_values($arr);
  $string_keys = array_map('strval', $keys);
  return array_combine($string_keys, $vals);
}

function str_keys_to_date($arr) {
  $tupled_arr = [];

  foreach ($arr as $key => $value) {
    $tupled_arr[] = [$key, $value];
  }

  return array_map(function ($value) {
    return [new DateTimeImmutable($value[0]), $value[1]];
  }, $tupled_arr);
}

function unformat_cache_from_db($cache) {
  $cache = json_decode($cache, true);
  $languages = str_keys_to_int($cache['languages']);
  $machines = str_keys_to_int($cache['machines']);
  $dates = str_keys_to_date($cache['dates']);

  return [
    'languages' => $languages,
    'machines' => $machines,
    'dates' => $dates,
    'caching_duration' => $cache['caching_duration'],
    'total_caching_duration' => $cache['total_caching_duration']
  ];
}

function get_cached_xps($db, $user) {
  $cached_data = [
    'languages' => [],
    'machines' => [],
    'dates' => [],
    'caching_duration' => 0,
    'total_caching_duration' => 0
  ];

  $cached_data = unformat_cache_from_db($user['cache']);

  // Here we would load user's new XP and merge it but there is none so it is skipped

  $pdo = $db->getPDO();
  $q = $pdo->prepare('update users set last_cached=? where username=?;');
  $q->bindValue(1, (new DateTimeImmutable('now'))->format('c'), PDO::PARAM_STR);
  $q->bindValue(2, $user['username']);
  $q->execute();

  return $cached_data;
}

function preload_cached_xps($db, $xps, $user) {
  return [
    'languages' => process_language_xps($db, $xps['languages']),
    'machines' => process_machine_xps($db, $xps['machines'], $user),
    'dates' => $xps['dates']
  ];
}

function process_language_xps($db, $xps) {
  $pdo = $db->getPDO();

  $language_ids = array_keys($xps);

  $q = $pdo->prepare('SELECT l0."id", l0."name", l0."inserted_at", l0."updated_at", l0."id" FROM "languages" AS l0 WHERE (l0."id" = ANY(?::int[]));');
  $q->execute(['{' . implode(',', $language_ids) . '}']);
  $languages = $q->fetchAll(PDO::FETCH_ASSOC);

  $languages_map = array_reduce($languages, function ($acc, $val) {
    $acc[$val['id']] = $val;
    return $acc;
  }, []);

  $xps_list = [];
  foreach ($xps as $id => $amount) {
    $xps_list[] = [$id, $amount];
  }

  return array_map(function ($lang) use ($languages_map) {
    list($id, $amount) = $lang;
    return [$languages_map[$id], $amount];
  }, $xps_list);
}

function process_machine_xps($db, $xps, $user) {
  $pdo = $db->getPDO();

  $language_ids = array_keys($xps);

  $q = $pdo->prepare('SELECT m0."id", m0."name", m0."api_salt", m0."user_id", m0."inserted_at", m0."updated_at", m0."id" FROM "machines" AS m0 WHERE (m0."user_id" = ?);');
  $q->bindValue(1, $user['id'], PDO::PARAM_INT);
  $q->execute();
  $machines = $q->fetchAll(PDO::FETCH_ASSOC);

  $machines_map = array_reduce($machines, function ($acc, $val) {
    $acc[$val['id']] = $val;
    return $acc;
  }, []);

  $xps_list = [];
  foreach ($xps as $id => $amount) {
    $xps_list[] = [$id, $amount];
  }

  return array_map(function ($lang) use ($machines_map) {
    list($id, $amount) = $lang;
    return [$machines_map[$id], $amount];
  }, $xps_list);
}

function process_date_xps($date_xps) {
  $date_xps = array_reduce(array_keys($date_xps), function ($acc, $date) use ($date_xps) {
    $acc[] = [$date, $date_xps[$date]];
    return $acc;
  }, []);

  usort($date_xps, function ($a, $b) {
    if ($a[0] === $b[0]) {
      return 0;
    }

    return ($a[0] > $b[0]) ? -1 : 1;
  });

  return $date_xps;
}

function get_language_xps_since($db, $user, $since) {
  $pdo = $db->getPDO();
  $q = $pdo->prepare('SELECT l2."id", l2."name", l2."inserted_at", l2."updated_at", x0."amount" FROM "xps" AS x0 INNER JOIN "pulses" AS p1 ON p1."id" = x0."pulse_id" INNER JOIN "languages" AS l2 ON l2."id" = x0."language_id" WHERE ((p1."user_id" = ?) AND (p1."sent_at" >= ?));');
  $q->bindValue(1, $user['id'], PDO::PARAM_INT);
  $q->bindValue(2, $since->format('c'));
  $q->execute();
  return array_reduce($q->fetchAll(), function ($acc, $val) {
    $acc[$val[0]] = get($acc, $val[0], 0) + $val[4];
    return $acc;
  }, []);
}

function get_machine_xps_since($db, $user, $since) {
  $pdo = $db->getPDO();
  $q = $pdo->prepare('SELECT m0."id", m0."name", m0."api_salt", m0."user_id", m0."inserted_at", m0."updated_at", sum(x2."amount") FROM "machines" AS m0 INNER JOIN "pulses" AS p1 ON m0."id" = p1."machine_id" INNER JOIN "xps" AS x2 ON p1."id" = x2."pulse_id" WHERE ((m0."user_id" = ?) AND (p1."sent_at" >= ?)) GROUP BY m0."id" ORDER BY sum(x2."amount") DESC');
  $q->bindValue(1, $user['id'], PDO::PARAM_INT);
  $q->bindValue(2, $since->format('c'));
  $q->execute();
  return array_reduce($q->fetchAll(), function ($acc, $val) {
    $acc[$val[0]] = get($acc, $val[0], 0) + $val[6];
    return $acc;
  }, []);
}

function profilepage($request_data, $db) {
  $username = substr($_SERVER['REQUEST_URI'], strlen('/users/'));
  $user = get_user($db, $username);

  $xps = preload_cached_xps($db, get_cached_xps($db, $user), $user);

  $language_xps = $xps['languages'];
  $machine_xps = $xps['machines'];
  $date_xps = $xps['dates'];

  $total_xp = array_reduce($language_xps, function ($acc, $xp) {
    return $acc + $xp[1];
  }, 0);

  $sorter = function ($a, $b) {
    if ($a[1] === $b[1]) {
      return 0;
    }

    return ($a[1] > $b[1]) ? -1 : 1;
  };

  usort($language_xps, $sorter);
  usort($machine_xps, $sorter);

  $date_xps = process_date_xps($date_xps);

  $now = new DateTimeImmutable('now');
  $latest_xp_since = $now->sub(new DateInterval('PT12H'));
  $new_language_xps = get_language_xps_since($db, $user, $latest_xp_since);
  $new_machine_xps = get_machine_xps_since($db, $user, $latest_xp_since);
  $total_new_xp = array_reduce(array_values($new_language_xps), function ($acc, $xp) {
    return $acc + $xp;
  }, 0);

  $last_day_coded = null;
  if (!empty($date_xps)) {
    $last_day_coded = $date_xps[0][1][0]->format('c');
  }

  $xp_per_day = 0;
  if ($last_day_coded !== null) {
    $xp_per_day = (int) ($total_xp / count($date_xps));
  }

  $new_xps = $new_language_xps;

  // Render template
  include 'profile.tpl.php';
}
