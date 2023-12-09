<?php

class  Database {

  private const HOST = 'mysql';
  private const USER_NAME  = 'root';
  private const PASSWORD = 'admin';
  private const DATABASE = 'Orchestra';

  private $conn;

  // crea connessione con il database
  function __construct() {
    $this->conn = new mysqli(self::HOST, self::USER_NAME, self::PASSWORD, self::DATABASE);
  }

  // chiude la connessione
  public function close() {
    if ($this->status()) {
      $this->conn->close();
    }
  }

  // restiruisce lo stato della connessione
  public function status() {
    return $this->conn == true;
  }

  static public function connect_execute_clean($query,$args) {
    try {
      $database = new Database();
      foreach($args as $name => $_) {
        if (strpos($query, $name) === false) {
          throw new Exception("ERRORE: Database::connect_execute_cleanup -> $name non è contenuta nella query");
        }
      }
      foreach ($args as $name => $data) {
        [$value, $filter] = $data;
        $replace = filter_var($value, $filter);
        if ($replace == null) {
          throw new Exception("ERRORE: Database::connect_execute_cleanup -> il filtro ha ritornato null per $name");
        }
        $query = str_replace($name, $replace, $query);
      }
      $result = $database->conn->query($query);
      return $result->fetch_all(MYSQLI_ASSOC);
    } finally {
      if(isset($result)){
        $result->free_result();
      }
      $database->close();
    }
  }
  
}
