<?php

const TOTAL_XP_CACHE = [
  ['C++', 16625],
  ['Elixir', 15316],
  ['Java', 215],
  ['Elm', 315531],
  ['C', 3155],
  ['Qwerty', 316361],
  ['Plain text', 57427],
  ['Python', 42747]
];

function frontpage($request_data, $db) {
  // Load total language XPs from "cache"
  $total_lang_xps = TOTAL_XP_CACHE;
  usort($total_lang_xps, function ($a, $b) {
    if ($a[1] === $b[1]) {
      return 0;
    }

    return ($a[1] > $b[1]) ? -1 : 1;
  });

  $total_xp = array_reduce($total_lang_xps, function ($acc, $lang) {
    return $acc + $lang[1];
  }, 0);

  $most_popular = array_slice($total_lang_xps, 0, 10);

  // Render template
  include 'front.tpl.php';
}
