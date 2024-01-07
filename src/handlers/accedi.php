<?php
require_once('../data/database.php');
require_once "../components/sessionEstablisher.php";

try {
  set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, $severity, $file, $line);
  });

  try_session();
  [
    "name" => $nome,
    "password" => $password,
  ] = $_POST;

  $conn = new Database();
  $res = $conn->user_with_mail_password($nome,$password);
  $conn->close();
  if (count($res) == 0) {
    throw new Exception("Nessun utente trovato. Le credenziali potrebbero essere errate.");
  }

  $user = $res[0];
  $_SESSION['user'] = $user;
  //TODO reindirizzamento a una pagina più appropriata
  header("Location: /");
} catch (Exception $e) {
    //TODO reindirizzamento a una pagina più appropriata (o almeno gestione dell'errore)
  $_SESSION['loginErrors'] = $e;
  header("Location: /accedi.php");
}
