<?php

class  Database {

  private const string HOST = 'mysql';
  private const string USER_NAME  = 'root';
  private const string PASSWORD = 'admin';
  private const string DATABASE = 'Orchestra';
  
  private mysqli $conn;

  // crea connessione con il database
  public function __construct() {
    $this->conn = new mysqli(self::HOST, self::USER_NAME, self::PASSWORD, self::DATABASE);
  }

  // esegue una query generica e ne restiruisce il risultato sotto forma di un array bidimensionale associativo
  private function execute_query(string $query, ...$params) {
    try {
      $res_set = $this->conn->execute_query($query, $params);
      $ret_set =  $res_set->fetch_all(MYSQLI_ASSOC);
      $res_set->free();
    }
    catch(mysqli_sql_exception $ex) {
      // TODO: implementazione pagine di errore personalizzate
      http_response_code(500);
      echo  $ex->getMessage();
      exit;
    }
    return $ret_set;
  }

  // restituisce il numero di artisti
  public function artist_count(): int {
    return $this->execute_query('SELECT COUNT(*) as count FROM Artist')[0]['count'];
  } 

  // restituisce il numero di album
  public function album_count(): int {
    return $this->execute_query('SELECT COUNT(*) as count FROM Album')[0]['count'];

  } 

  // restituisce il numero di canzoni
  public function song_count(): int {
    return $this->execute_query('SELECT COUNT(*) as count FROM Music')[0]['count'];
  } 

  // restituisce ultime $num uscite
  public function latest_releases(int $num): array {
    $res = $this->execute_query('SELECT Artist.name as artist, Music.name as song, Music.added_date  FROM Artist join Music on Artist.id=Music.producer ORDER BY added_date DESC LIMIT ?', $num);
    return $res;
  }

  // chiude la connessione
  public function close(): void {
    if($this->conn) 
    {
      $this->conn->close();
    }
  }

  // restiruisce lo stato della connessione
  public function status(): bool{
    return $this->conn == true && $this->conn->ping();
  }

  // distruzione oggetto DB in cui viene chiusa la connessione
  public function __destructor() {
    $this->close();
  }
}
