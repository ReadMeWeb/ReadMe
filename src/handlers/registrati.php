<?php
require_once('../data/database.php');
require_once "../components/sessionEstablisher.php";

try {
  set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, $severity, $file, $line);
  });

  try_session();
  [
    "username" => $username,
    "password" => $password,
  ] = $_POST;


  $conn = new Database();
  if($conn->user_exists($username)){
    throw new Exception("Il Nome utente fornito risulta già registrato.");
  }

  if ($conn->user_sign_up($username,$password) !== true) {
    // Questo caso non dovrebbe mai succedere
    throw new Exception("Errore del database.");
  }
  $conn->close();

  //NOT TODO reindirizzamento a una pagina più appropriata <- delegato a handlers/accedi.php
  //TODO reindirizzamento a handlers/accedi.php con accesso automatico
  $_SESSION['user'] = ['username' => $username, 'status' => "USER"];
  header("Location: /");
} catch (Exception $e) {
  $_SESSION['signupErrors'] = $e;
  header("Location: /registrati.php");
}
