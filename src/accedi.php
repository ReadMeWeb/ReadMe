<?php
function gethandlererror($name) {
  if (array_key_exists($name, $_SESSION)) {
    $e = $_SESSION[$name];
    unset($_SESSION[$name]);
    return $e;
  }
  return false;
}

if (session_start()) {
  if (array_key_exists('user', $_SESSION)) {
    header("Location: /");
  }

  //TODO sostituire con il file_get_contents di layout
  $page = "<!DOCTYPE html><html><body>{{content}}</body></html>";
  $accedi = file_get_contents("./components/accedi.html");
  $errori = "";
  if ($e = gethandlererror('loginErrors')) {
    $errori = "<h1>Errore</h1>
      <p class='error'>" . (strip_tags($e->getmessage())) . "</p>";
  }

  //TODO ripristinare la mail / password all'interno degli input

  $page = str_replace("{{content}}", $accedi, $page);
  $page = str_replace("{{errori}}", $errori, $page);
  echo $page;
}
