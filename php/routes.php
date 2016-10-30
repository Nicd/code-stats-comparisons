<?php

require_once 'plugs.php';
require_once 'front.page.php';
require_once 'profile.page.php';
require_once 'pulse.api.php';

const ROUTES = [
  ['(^/users/)', 'profilepage', ['request_time', 'set_session_user']],
  ['(^/$)', 'frontpage', ['request_time', 'set_session_user']],
  ['(^/api/my/pulses/?$)', 'pulse', ['api_auth_required']]
];
