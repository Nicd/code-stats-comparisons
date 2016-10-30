<?php

/**
 * Simple router that calls the given page function based on the request URL.
 *
 * The given routes list should contain 3-tuples of:
 * 1. Regex for matching the URL
 * 2. Function to execute on a match
 * 3. Plugs to use in request. Plugs are passed $request_data and should return a new
 *    copy of it which will be passed to the next plug and then to the request function.
 *    The second argument passed to all plugs and the request function is the database
 *    handler ($db).
 *
 * The first match is used. If there is no match, a 404 error is thrown.
 */
class Router {
  private $routes;
  private $db;
  private $request_data;

  public function __construct($routes, $db) {
    $this->routes = $routes;
    $this->db = $db;
    $this->request_data = [];
  }

  public function run() {
    $path = $_SERVER['REQUEST_URI'];

    foreach ($this->routes as $route) {
      if (preg_match($route[0], $path)) {
        foreach ($route[2] as $plug) {
          $this->request_data = call_user_func($plug, $this->request_data, $this->db);
        }

        call_user_func($route[1], $this->request_data, $this->db);
        return;
      }
    }
  }
}
