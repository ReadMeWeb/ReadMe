<?php
require_once('../data/database.php');
try {
  set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, $severity, $file, $line);
  });

  session_start();
  [
    "username" => $username,
    "mail" => $mail,
    "password" => $password,
  ] = $_POST;

  // controllo che la mail non sia già registrata
  $query = "SELECT COUNT(*) AS num FROM Users WHERE mail = '@MAIL';";
  $args = ['@MAIL' => [$mail, FILTER_SANITIZE_FULL_SPECIAL_CHARS],];
  $res = Database::connect_execute_clean($query, $args);
  if ($res[0]['num'] != "0") {
    throw new Exception("La mail fornita risulta già registrata.");
  }

  // inserimento del nuovo utente come user
  $query =  "INSERT INTO Users(mail, password, username, status) VALUES('@MAIL','@PASSWORD','@NAME','USER');";
  $args = [
    '@NAME' => [$username, FILTER_SANITIZE_FULL_SPECIAL_CHARS],
    '@MAIL' => [$mail, FILTER_SANITIZE_EMAIL],
    '@PASSWORD' => [$password, FILTER_SANITIZE_FULL_SPECIAL_CHARS]
  ];
  $res = Database::connect_execute_clean($query, $args);
  if ($res !== true) {
    // Questo caso non dovrebbe mai succedere
    throw new Exception("Errore del database.");
  }

  $_SESSION['user'] = ['mail' => $mail, 'status' => "USER"];
  //TODO reindirizzamento a una pagina più appropriata
  header("Location: /");
} catch (Exception $e) {
  $_SESSION['signupErrors'] = $e;
  header("Location: /registrati.php");
}
