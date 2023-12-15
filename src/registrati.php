<?php
require_once 'components/navbar.php';

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
  $page = file_get_contents("./components/layout.html");
  $content = file_get_contents("./components/registrati.html");
  $errori = "";
  if ($e = gethandlererror('signupErrors')) {
    $errori = "<h1>Errore</h1>
      <p class='error'>" . (strip_tags($e->getmessage())) . "</p>";
  }

  //TODO ripristinare nome utente / mail / password all'interno degli input

  //TODO prevenire gli <a> ricorsivi / ad anello
  //soluzione temporanea
  $menu = navbar();
  $menu = str_replace("href='registrati.php'","",$menu);

  $page = str_replace("{{title}}", "Registrati", $page);
  $page = str_replace("{{description}}", "Pagina di registrazione di Orchestra", $page);
  $page = str_replace("{{keywords}}", "Orchestra, musica classica, registrazione, sign up", $page);
  $page = str_replace("{{menu}}", $menu, $page);
  $page = str_replace("{{breadcrumbs}}", "<a href='./'>Home</a> &gt <a>Registrati</a>", $page);

  $page = str_replace("{{content}}", $content, $page);
  $page = str_replace("{{errori}}", $errori, $page);
  echo $page;
}
