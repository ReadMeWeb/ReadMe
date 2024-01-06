<?php

require_once 'components/breadcrumbs/breadcrumbItem.php';
require_once 'components/breadcrumbs/breadcrumbsBuilder.php';
require_once 'components/navbar.php';
require_once 'components/sessionEstablisher.php';
require_once 'data/database.php';

set_error_handler(function ($severity, $message, $file, $line) {
  throw new \ErrorException($message, $severity, $severity, $file, $line);
});

if (!try_session()) {
  throw new ErrorException("try_session ha fallito");
}

if (is_user_signed_in()) {
  redirect('/');
}

$errori = '';
$nome = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  goto GET;
}

// ========================================================================================================================
// POST
// ========================================================================================================================

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
  if ($conn->user_exists($username)) {
    throw new Exception("Il Nome utente fornito risulta già registrato.");
  }

  if ($conn->user_sign_up($username, $password) !== true) {
    // Questo caso non dovrebbe mai succedere
    throw new Exception("Errore del database.");
  }
  $conn->close();

  $_POST = ['name' => $username, 'password' => $password];
  require_once('accedi.php');
  exit();
} catch (Exception $e) {
  //var_dump($e);
  $_SESSION['signupErrors'] = $e;
  header("Location: /registrati.php");
}

if (count($_POST) > 0) {
  exit();
}

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

if (try_session()) {
  if (array_key_exists('mail', $_SESSION["user"])) {
    header("Location: /");
  }

  $page = file_get_contents("./components/layout.html");
  $content = file_get_contents("./components/registrati.html");
  $errori = "";
  if ($e = gethandlererror('signupErrors')) {
    $errori = "<h1>Errore</h1>
      <p class='error'>" . (strip_tags($e->getmessage())) . "</p>";
  }

  //TODO ripristinare nome utente / mail / password all'interno degli input

  $breadcrumbs = (new BreadcrumbsBuilder())
    ->addBreadcrumb(new BreadcrumbItem("Home"))
    ->addBreadcrumb(new BreadcrumbItem("Registrati", isCurrent: true))
    ->build()
    ->getBreadcrumbsHtml();

  $page = str_replace("{{title}}", "Registrati", $page);
  $page = str_replace("{{description}}", "Pagina di registrazione di Orchestra", $page);
  $page = str_replace("{{keywords}}", "Orchestra, musica classica, registrazione, sign up", $page);
  $page = str_replace("{{menu}}", navbar(), $page);
  $page = str_replace("{{breadcrumbs}}", $breadcrumbs, $page);

  $page = str_replace("{{content}}", $content, $page);
  $page = str_replace("{{errori}}", $errori, $page);
  echo $page;
}
