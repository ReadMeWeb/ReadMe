<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'include/HTMLBuilder.php';
require_once 'components/breadcrumbs.php';
require_once 'components/navbar.php';
require_once 'include/sessionEstablisher.php';
require_once 'include/database.php';
require_once 'include/utils.php';

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

  [
    "name" => $nome,
    "password" => $password,
  ] = $_POST;

  $conn = new Database();
  if ($conn->user_exists($nome)) {
    throw new Exception("Il nome utente fornito risulta già registrato.");
  }

  if ($conn->user_sign_up($nome, $password) !== true) {
    // Questo caso non dovrebbe mai succedere
    throw new Exception("Errore del database.");
  }
  $conn->close();

  $_POST = ['name' => $nome, 'password' => $password];
  require_once('accedi.php'); // TODO: cos'è sta roba?
  exit();
} catch (Exception $e) {
  $errori = $e->getMessage();
}

// ========================================================================================================================
GET:
// ========================================================================================================================

echo (new HTMLBuilder('../components/layout.html'))
  ->set('title', 'Registrati')
  ->set('description', 'Pagina di registrazione di Orchestra')
  ->set('keywords', 'Orchestra, musica classica, registrazione, sign up')
  ->set('menu', navbar())
  ->set('breadcrumbs', (new BreadcrumbsBuilder())
    ->addBreadcrumb(new BreadcrumbItem("Home"))
    ->addBreadcrumb(new BreadcrumbItem("Registrati", isCurrent: true))
    ->build()
    ->getBreadcrumbsHtml())
  ->set('content', (new HTMLBuilder('../components/registrati.html'))
    ->set('nome', $nome)
    ->set('errori', $errori, HTMLBuilder::ERROR_P)
    ->build())
  ->build();
