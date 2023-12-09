<?php
require_once('../data/database.php');
try {
  [
    "mail" => $mail,
    "password" => $password,
  ] = $_POST;
  if ($mail == null || $password == null) {
    throw new Exception("Dati \$_POST invalidi");
  }

  $query =  "SELECT mail,status FROM Users WHERE mail = '@MAIL' AND password = '@PASSWORD' LIMIT 1;";
  $args = [
    '@MAIL' => [$mail, FILTER_SANITIZE_EMAIL],
    '@PASSWORD' => [$password, FILTER_SANITIZE_FULL_SPECIAL_CHARS]
  ];
  $res = Database::connect_execute_clean($query, $args);
  if (count($res) == 0) {
    throw new Exception("Nessun utente trovato");
  }

  if (session_start() === false) {
    throw new Exception("Errore durante l'avvio della sessione");
  }
  $user = $res[0];
  $_SESSION = $user;
  header("Location: /");
} catch (Exception $e) {
  // TODO sostituire con il reindirazzamento a una pagina di errore
  echo "<a href='./esci.php'>clicca per fare il sign out</a>";
  echo "<p>dump dell'eccezione</p>";
  echo "<textarea style='width:90%; height:90%;' readonly>";
  var_dump($e);
  echo "</textarea>";
}
