<?php
require_once '../Pangine/HTMLBuilder.php';
require_once '../components/breadcrumbs/breadcrumbItem.php';
require_once '../components/breadcrumbs/breadcrumbsBuilder.php';
require_once '../components/navbar.php';
require_once '../components/sessionEstablisher.php';
require_once '../data/database.php';
require_once '../handlers/utils.php';

set_error_handler(function ($severity, $message, $file, $line) {
  throw new \ErrorException($message, $severity, $severity, $file, $line);
});

if (!try_session()) {
  throw new ErrorException("try_session ha fallito");
}

if (is_user_signed_in() || is_admin_signed_in()) {
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

  $_SESSION['user'] = $res[0];
  redirect(extract_from_array_else('redirection', $_SESSION, '/'));
} catch (Exception $e) {
  $errori = $e->getMessage();
}

// ========================================================================================================================
GET:
// ========================================================================================================================

echo (new HTMLBuilder('../components/layout.html'))
  ->set('title', 'Accedi')
  ->set('description', 'Pagina di accesso di Orchestra')
  ->set('keywords', 'Orchestra, musica classica, accesso, log in, sign in')
  ->set('menu', navbar())
  ->set('breadcrumbs', (new BreadcrumbsBuilder())
    ->addBreadcrumb(new BreadcrumbItem("Home"))
    ->addBreadcrumb(new BreadcrumbItem("Accedi", isCurrent: true))
    ->build()
    ->getBreadcrumbsHtml())
  ->set('content', (new HTMLBuilder('../components/accedi.html'))
    ->set('nome', $nome)
    ->set('errori', $errori, HTMLBuilder::ERROR_P)
    ->build())
  ->build();
