<?php

class  Database {

  private const HOST = 'mysql_server';
  private const USER_NAME  = 'root';
  private const PASSWORD = 'admin';
  private const DATABASE = 'Orchestra';
  
  private mysqli $conn;

  // crea connessione con il database
  public function __construct() {
    $this->conn = new mysqli(self::HOST, self::USER_NAME, self::PASSWORD, self::DATABASE);
  }

  // esegue una query generica e ne restituisce il risultato sotto forma di un array bidimensionale associativo
    private function execute_query(string $query, ...$params) {
        try {
            $stmt = $this->conn->prepare($query);

            if ($stmt === false) {
                throw new Exception("Errore nella preparazione della query");
            }

            if (!empty($params)) {
                $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            }

            $stmt->execute();
            $res_set = $stmt->get_result();
            $ret_set = match($res_set){
              true, false => $res_set, // Gestisce il caso di inserimento, cancellazione, update (non ci deve essere restituito niente)
              default => $res_set->fetch_all(MYSQLI_ASSOC), // Gestisce il caso di SELECT (ci deve essere restituito qualcosa)
            };

            $stmt->close();
        } catch (mysqli_sql_exception $ex) {
            // TODO: implementazione pagine di errore personalizzate
            http_response_code(500);
            echo $ex->getMessage();
            exit;
        } catch (Exception $ex) {
            // Gestione di altri tipi di eccezioni, se necessario
            http_response_code(500);
            echo $ex->getMessage();
            exit;
        }

        return $ret_set;
    }

  // ritorna l'utente con mail e password date
  // ritorno: array vuoto                 -> credenziali invalide o non registrate
  //          array con singolo elemento  -> utente ricercato
  public function user_with_mail_password(string $mail, string $password): array {
    $query =  "SELECT username,mail,status FROM Users WHERE mail = ? AND password = ? LIMIT 1;";
    return $this->execute_query($query,$mail,$password);
  }

  // ritorna se l'utente è registrato
  // ritorno: true  -> l'utente è registrato nel database
  //          false -> l'utente non è registrato nel database
  public function user_exists(string $mail): bool {
    $query = "SELECT COUNT(*) AS num FROM Users WHERE mail = ?;";
    return $this->execute_query($query, $mail)[0]['num'] != "0";
  }

  // registra l'utente
  // ritorno: true  -> registrazione con successo
  //          false -> registrazione fallita
  public function user_sign_up(string $name, string $mail, string $password): bool {
    $query =  "INSERT INTO Users(mail, password, username, status) VALUES(?,?,?,'USER');";
    $this->execute_query($query, $mail, $password, $name);
    return $this->user_exists($mail);
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

  public function fetch_artist_info(): array {
      $res = $this->execute_query('SELECT id, name, biography FROM Artist');
      return $res;
  }

  public function fetch_songs_info(): array {
      $res = $this->execute_query('SELECT producer, name, audio_file_name, graphic_file_name FROM Music');
      return $res;
  }

  public function fetch_albums_info(): array {
      $res = $this->execute_query('SELECT id, name, file_name FROM Album');
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
