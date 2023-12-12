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
      // riordina le chiavi secondo l'ordine in cui sono scritte nella query
      // e lancia un eccezzione in caso non ci sia la variabile
      uksort($args,function ($a,$b) use($query){
        if(($apos = strpos($query,$a)) === false){
          throw new Exception("ERRORE: Database::connect_execute_cleanup -> $a non è contenuta nella query");
        }
        if(($bpos = strpos($query,$b)) === false){
          throw new Exception("ERRORE: Database::connect_execute_cleanup -> $b non è contenuta nella query");
        }
        return $apos < $bpos ? 0 : 1 ;
      });
      $offset = 0;
      foreach ($args as $name => $data) {
        [$value, $filter] = $data;
        $replace = filter_var($value, $filter);
        if ($replace == null) {
          throw new Exception("ERRORE: Database::connect_execute_cleanup -> il filtro ha ritornato null per $name");
        }
        $idx = strpos($query, $name, $offset);
        $query = substr_replace($query, $replace, $idx, strlen($name));
        $offset = $idx + strlen($replace);
      }
      $result = $database->conn->query($query);
      return match($result) {
        true, false => $result,
        default => $result->fetch_all(MYSQLI_ASSOC),
      };
    } finally {
      if (isset($result)) {
        match($result){
          true, false => $result,
          default => $result->free_result(),
        };
      }
      if (isset($database)) {
        $database->close();
      }
    }
  }
  
}
