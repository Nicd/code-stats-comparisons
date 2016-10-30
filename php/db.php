<?php
/**
 * Utility class for database stuff.
 */
class DB {
  private $pdo;

  public function __construct($db_host, $db_port, $db_name, $db_user, $db_pass) {
    $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name;user=$db_user;password=$db_pass";
    $this->pdo = new PDO($dsn);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  }

  public function getPDO() {
    return $this->pdo;
  }
}
