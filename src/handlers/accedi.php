<?php
require_once('../data/database.php');
require_once "../components/sessionEstablisher.php";

try {
  set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, $severity, $file, $line);
  });

  try_session();
  [
    "mail" => $mail,
    "password" => $password,
  ] = $_POST;

  $conn = new Database();
  $res = $conn->user_with_mail_password($mail,$password);
  $conn->close();
  if (count($res) == 0) {
    throw new Exception("Nessun utente trovato. Le credenziali potrebbero essere errate.");
  }

  $user = $res[0];
  $_SESSION['user'] = $user;
  //TODO reindirizzamento a una pagina più appropriata
  header("Location: /");
} catch (Exception $e) {
  $_SESSION['loginErrors'] = $e;
  header("Location: /accedi.php");
}
