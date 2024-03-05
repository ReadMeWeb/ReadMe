<?php

class  Database
{

    private const HOST = 'mysql_server';
    private const USER_NAME  = 'root';
    private const PASSWORD = 'admin';
    private const DATABASE = 'Orchestra';

    private mysqli $conn;

    // crea connessione con il database
    public function __construct()
    {
        $this->conn = new mysqli(self::HOST, self::USER_NAME, self::PASSWORD, self::DATABASE);
    }

    // esegue una query generica e ne restituisce il risultato sotto forma di un array bidimensionale associativo
    private function execute_query(string $query, ...$params)
    {
        try {
            $stmt = $this->conn->prepare($query);

            if ($stmt === false) {
                throw new Exception("Errore nella preparazione della query");
            }

            if (!empty($params)) {
                $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            }

            $succes = $stmt->execute();
            $res_set = $stmt->get_result();

            if ($res_set !== false) {
                $ret_set = $res_set->fetch_all(MYSQLI_ASSOC);
            } else {
                $ret_set = $succes;
            }

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
    public function user_with_mail_password(string $nome, string $password): array
    {
        $query =  "SELECT username,password,status FROM Users WHERE username = ? AND password = ? LIMIT 1;";
        return $this->execute_query($query, $nome, $password);
    }

    // ritorna se l'utente è registrato
    // ritorno: true  -> l'utente è registrato nel database
    //          false -> l'utente non è registrato nel database
    public function user_exists(string $id): bool
    {
        $query = "SELECT COUNT(*) AS num FROM Users WHERE username = ?;";
        return $this->execute_query($query, $id)[0]['num'] != "0";
    }

    // registra l'utente
    // ritorno: true  -> registrazione con successo
    //          false -> registrazione fallita
    public function user_sign_up(string $name, string $password): bool
    {
        $query =  "INSERT INTO Users(username, password, status) VALUES(?,?,'USER');";
        $this->execute_query($query, $name, $password);
        return $this->user_exists($name);
    }


    // ritorna gli artisti nelle colonne id,nome
    public function artisti(): array
    {
        $query =  "SELECT id,name FROM Artist;";
        return $this->execute_query($query);
    }

    // ritorna se l'album esiste nel database
    public function album_exists($name, $artist): bool
    {
        $query =  "SELECT COUNT(*) AS num FROM Album WHERE name = ? AND artist_id = ?";
        return $this->execute_query($query, $name, $artist)[0]['num'] != "0";
    }

    // inserisce l'abum nel database
    public function album_add($artist, $name, $file): bool
    {
        $query =  "INSERT INTO Album(name,artist_id,file_name) VALUES(?,?,?)";
        $this->execute_query($query, $artist, $name, $file);
        return $this->album_exists($artist, $name);
    }
    // restituisce il numero di artisti
    public function artist_count(): int
    {
        return $this->execute_query('SELECT COUNT(*) as count FROM Artist')[0]['count'];
    }

    // restituisce il numero di album
    public function album_count(): int
    {
        return $this->execute_query('SELECT COUNT(*) as count FROM Album')[0]['count'];
    }

    // restituisce il numero di canzoni
    public function song_count(): int
    {
        return $this->execute_query('SELECT COUNT(*) as count FROM Music')[0]['count'];
    }

    // restituisce ultime $num uscite
    public function latest_releases(int $num): array
    {
        return $this->execute_query('SELECT Artist.name as artist, Music.name as song, Music.added_date, graphic_file_name as img  FROM Artist join Music on Artist.id=Music.producer ORDER BY added_date DESC LIMIT ?', $num);
    }

    public function fetch_all_artists_except_the_following($id): array
    {
        $query =  "SELECT * FROM Artist WHERE id != ?;";
        return $this->execute_query($query, $id);
    }

    public function fetch_artist_info(): array
    {
        return $this->execute_query('SELECT id, name, biography, file_name FROM Artist');
    }

    public function fetch_artist_info_by_id(int $id): array|null
    {
        $res = $this->execute_query('SELECT id, name, biography, file_name FROM Artist WHERE id = ?', $id);
        if (sizeof($res) == 1) {
            return $res[0];
        }
        return null;
    }

    public function fetch_song_info_by_id(int $song_id): array|null
    {
        $res = $this->execute_query('SELECT * FROM Music WHERE id = ?', $song_id);
        if (sizeof($res) == 1) {
            return $res[0];
        }
        return null;
    }

    public function fetch_song_info_by_title_and_artist_id(string $title, int $artist_id): array|null
    {
        $res = $this->execute_query('SELECT * FROM Music WHERE Music.producer = ? AND Music.name = ?', $artist_id, $title);
        if (sizeof($res) == 1) {
            return $res[0];
        }
        return null;
    }

    public function fetch_songs_info(): array
    {
        return $this->execute_query('SELECT producer, Music.name as name, audio_file_name, graphic_file_name, A.name as producer_name FROM Music JOIN Orchestra.Artist A on Music.producer = A.id');
    }

    public function fetch_albums_info(): array
    {
        return $this->execute_query('SELECT id, name, file_name FROM Album');
    }

    public function fetch_albums_info_by_artist_id(string $id): array
    {
        return $this->execute_query('SELECT id, name, file_name FROM Album WHERE artist_id = ?', $id);
    }

    public function insert_artist(string $nome, string $biography, string $image): bool
    {
        return $this->execute_query('INSERT INTO Artist(name, biography, file_name) VALUES(?, ?, ?)', $nome, $biography, $image);
    }

    public function insert_song(string $artist_id, string $title, string $audio_file, string $graphic_file, string|null $album_id): bool
    {

        return $this->execute_query('INSERT INTO Music(producer, name, audio_file_name, graphic_file_name, album, added_date) VALUES(?, ?, ?, ?, ?, now())', $artist_id, $title, $audio_file, $graphic_file, $album_id);
    }

    public function check_album_belong_to_artist(string $artist_id, string $album_id): bool
    {
        $res = $this->execute_query('SELECT * FROM Album WHERE artist_id = ? AND id = ?', $artist_id, $album_id);
        if (sizeof($res) == 1) {
            return true;
        }
        return false;
    }

    public function update_song(int $song_id, int $artist_id, string $song_title, string $file_name_A, string $file_name_G, int $album_id): bool
    {
        $res = $this->execute_query('UPDATE Music SET producer = ?, name = ?, audio_file_name = ?, graphic_file_name = ?, album = ? WHERE id = ?',$artist_id, $song_title,$file_name_A, $file_name_G, $album_id, $song_id);
        if (sizeof($res) == 1) {
            return true;
        }
        return false;
    }

    public function update_user_info(string $oldUsername, string $newUsername, string $newPassword)
    {
        $res = $this->execute_query('UPDATE Users SET username = ?, password = ? WHERE username = ?', $newUsername, $newPassword, $oldUsername);
        return $res;
    }

    // chiude la connessione
    public function close(): void
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    // restiruisce lo stato della connessione
    public function status(): bool
    {
        return $this->conn->ping();
    }

    // distruzione oggetto DB in cui viene chiusa la connessione
    public function __destructor(): void
    {
        $this->close();
    }
}
