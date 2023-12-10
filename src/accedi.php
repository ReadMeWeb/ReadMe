<?php
if (session_start()) {
  if (array_key_exists('user', $_SESSION)) {
    header("Location: /");
  }

  //TODO sostituire con il file_get_contents di layout
  $page = "<!DOCTYPE html><html><body>{{content}}</body></html>";
  $accedi = file_get_contents("./components/accedi.html");
  $errori = !array_key_exists('loginErrors', $_SESSION) ? "" : "<p class=errori>" . (strip_tags($_SESSION['loginErrors'])) .  "</p>";

  $page = str_replace("{{content}}", $accedi, $page);
  $page = str_replace("{{errori}}", $errori, $page);
  echo $page;
}
