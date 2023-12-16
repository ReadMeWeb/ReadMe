<?php
require_once 'components/navbar.php';
require_once 'components/sessionEstablisher.php';

function gethandlererror($name) {
  if (array_key_exists($name, $_SESSION)) {
    $e = $_SESSION[$name];
    unset($_SESSION[$name]);
    return $e;
  }
  return false;
}

if (try_session()) {
  if (array_key_exists('mail', $_SESSION["user"])) {
    header("Location: /");
  }

  //TODO sostituire con il file_get_contents di layout
  $page = file_get_contents("./components/layout.html");
  $content = file_get_contents("./components/accedi.html");
  $errori = "";
  if ($e = gethandlererror('loginErrors')) {
    $errori = "<h1>Errore</h1>
      <p class='error'>" . (strip_tags($e->getmessage())) . "</p>";
  }

  //TODO ripristinare la mail / password all'interno degli input

  //TODO prevenire gli <a> ricorsivi / ad anello
  //soluzione temporanea
  $menu = navbar();
  $menu = str_replace("href='accedi.php'","",$menu);

  $page = str_replace("{{title}}", "Accedi", $page);
  $page = str_replace("{{description}}", "Pagina di accesso di Orchestra", $page);
  $page = str_replace("{{keywords}}", "Orchestra, musica classica, accesso, log in, sign in", $page);
  $page = str_replace("{{menu}}", $menu, $page);
  $page = str_replace("{{breadcrumbs}}", "<a href='./'>Home</a> &gt <a>Accedi</a>", $page);

  $page = str_replace("{{content}}", $content, $page);
  $page = str_replace("{{errori}}", $errori, $page);
  echo $page;
}
