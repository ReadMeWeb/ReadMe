<?php
require_once '../data/database.php';
require_once '../components/sessionEstablisher.php';

try {
  set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, $severity, $file, $line);
  });

  try_session();
  [
    "artista" => $artista,
    "nome" => $nome,
    "copertina" => $copertina,
  ] = $_POST;

  $conn = new Database();
  if ($conn->album_exists($nome, $artista)) {
    throw new Exception("L'album risulta già essere registrato");
  }
  if (!$conn->album_add($nome, $artista)) {
    throw new Exception("Errore di inserimento nel database");
  }
  $conn->close();
  header("Location: /aggiungiAlbum.php");
} catch (Exception $e) {
  $_SESSION['addAlbumErrors'] = $e;
  header("Location: /aggiungiAlbum.php");
}
