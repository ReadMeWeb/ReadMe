<?php

require_once 'components/breadcrumbs/breadcrumbItem.php';
require_once 'components/breadcrumbs/breadcrumbsBuilder.php';
require_once 'components/navbar.php';
require_once 'components/sessionEstablisher.php';
require_once 'data/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  goto GET;
}

if(!try_session()){
  throw new ErrorException("try_session ha fallito");
}

// ========================================================================================================================
// POST
// ========================================================================================================================

try {
  set_error_handler(function ($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, $severity, $file, $line);
  });

  [
    "name" => $nome,
    "password" => $password,
  ] = $_POST;

  $conn = new Database();
  $res = $conn->user_with_mail_password($nome, $password);
  $conn->close();
  if (count($res) == 0) {
    throw new Exception("Nessun utente trovato. Le credenziali potrebbero essere errate.");
  }

  $user = $res[0];
  $_SESSION['user'] = $user;
  //TODO reindirizzamento a una pagina più appropriata
  header("Location: /");
} catch (Exception $e) {
  //TODO reindirizzamento a una pagina più appropriata (o almeno gestione dell'errore)
  $_SESSION['loginErrors'] = $e;
  header("Location: /accedi.php");
}

exit();

// ========================================================================================================================
GET:
// ========================================================================================================================

function gethandlererror($name)
{
  if (array_key_exists($name, $_SESSION)) {
    $e = $_SESSION[$name];
    unset($_SESSION[$name]);
    return $e;
  }
  return false;
}

  if (array_key_exists('mail', $_SESSION["user"])) {
    header("Location: /");
  }

  $page = file_get_contents("./components/layout.html");
  $content = file_get_contents("./components/accedi.html");
  $errori = "";
  if ($e = gethandlererror('loginErrors')) {
    $errori = "<h1>Errore</h1>
      <p class='error'>" . (strip_tags($e->getmessage())) . "</p>";
  }

  //TODO ripristinare la mail / password all'interno degli input

  $breadcrumbs = (new BreadcrumbsBuilder())
    ->addBreadcrumb(new BreadcrumbItem("Home"))
    ->addBreadcrumb(new BreadcrumbItem("Accedi", isCurrent: true))
    ->build()
    ->getBreadcrumbsHtml();

  $page = str_replace("{{title}}", "Accedi", $page);
  $page = str_replace("{{description}}", "Pagina di accesso di Orchestra", $page);
  $page = str_replace("{{keywords}}", "Orchestra, musica classica, accesso, log in, sign in", $page);
  $page = str_replace("{{menu}}", navbar(), $page);
  $page = str_replace("{{breadcrumbs}}", $breadcrumbs, $page);

  $page = str_replace("{{content}}", $content, $page);
  $page = str_replace("{{errori}}", $errori, $page);
  echo $page;
