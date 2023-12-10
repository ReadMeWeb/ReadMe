<?php
require_once('../data/database.php');
try {
  set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, $severity, $file, $line);
  });

  session_start();
  [
    "mail" => $mail,
    "password" => $password,
  ] = $_POST;

  $query =  "SELECT mail,status FROM Users WHERE mail = '@MAIL' AND password = '@PASSWORD' LIMIT 1;";
  $args = [
    '@MAIL' => [$mail, FILTER_SANITIZE_EMAIL],
    '@PASSWORD' => [$password, FILTER_SANITIZE_FULL_SPECIAL_CHARS]
  ];
  $res = Database::connect_execute_clean($query, $args);
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
