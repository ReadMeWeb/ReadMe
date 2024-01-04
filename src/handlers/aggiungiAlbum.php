<?php
require_once '../data/database.php';
require_once '../components/sessionEstablisher.php';

try {
  set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, $severity, $file, $line);
  });

  try_session();

  // TODO aggiornare quando la copertina verrà usata
  [
    "artista" => $artista,
    "nome" => $nome,
  ] = $_POST;

  $conn = new Database();
  if ($conn->album_exists($nome, $artista)) {
    throw new Exception("L'album risulta già essere registrato");
  }

  $dir = "../assets/albumPhotos";
  if ($e = file_exists($dir)) {
    if ($d = is_dir($dir) === false) {
      throw new Exception("'$dir' esiste ma non è una directory");
    }
  } else {
    if ($m = mkdir($dir, 0777, true) === false) {
      throw new Exception("Directory '$dir' mancante e non può essere creata : $dir");
    }
  };

  if ($_FILES["copertina"]["size"] > 500000) {
    throw new Exception("File tropppo grande");
  }

  if (!move_uploaded_file($_FILES["copertina"]["tmp_name"], "$dir/$artista-$nome")) {
    throw new Exception("Errore nel salvataggio della copertina");
  }

  if (!$conn->album_add($nome, $artista, "$artista-$nome")) {
    throw new Exception("Errore di inserimento nel database");
  }
  $conn->close();


  $_SESSION['addAlbumSuccess'] = new Exception("Album $nome aggiunto con successo");
  header("Location: /aggiungiAlbum.php");
} catch (Exception $e) {
  $_SESSION['addAlbumErrors'] = $e;
  header("Location: /aggiungiAlbum.php");
}
