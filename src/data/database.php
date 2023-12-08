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
    if($this->status()) 
    {
      $this->conn->close();
    }
  }

  // restiruisce lo stato della connessione
  public function status() {
    return $this->conn == true;
  }
}
