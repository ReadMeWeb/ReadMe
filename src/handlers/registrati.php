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

  $conn = new Database();
  if($conn->user_exists($mail)){
    throw new Exception("La mail fornita risulta già registrata.");
  }
  if ($conn->user_sign_up($username,$mail,$password) !== true) {
    // Questo caso non dovrebbe mai succedere
    throw new Exception("Errore del database.");
  }
  $conn->close();

  //NOT TODO reindirizzamento a una pagina più appropriata <- delegato a handlers/accedi.php
  //TODO reindirizzamento a handlers/accedi.php con accesso automatico
  $_SESSION['user'] = ['username' => $username, 'mail' => $mail, 'status' => "USER"];
  header("Location: /");
} catch (Exception $e) {
  $_SESSION['signupErrors'] = $e;
  header("Location: /registrati.php");
}
