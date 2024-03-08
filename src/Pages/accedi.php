<?php

use Pangine\Pangine;

set_include_path($_SERVER["DOCUMENT_ROOT"]);
require_once '../Pangine/Pangine.php';
require_once '../Pangine/HTMLBuilder.php';
require_once '../components/breadcrumbs.php';
require_once '../components/navbar.php';
require_once '../components/sessionEstablisher.php';
require_once '../include/database.php';
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

const logerr = 'logerr';

(new Pangine())
  ->POST_read(function () {
    $nome = '';

    try {
      [
        'nome' => $nome,
        'password' => $password,
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
      $_SESSION[logerr] = ['nome' => $nome, 'val' => $e->getMessage(), 'typ' => HTMLBuilder::ERROR_P];
      redirect('accedi.php');
    }
  })
  ->GET_read(function () {

    if (!array_key_exists(logerr, $_SESSION)) {
      $_SESSION[logerr] = ['nome' => '', 'val' => '', 'typ' => HTMLBuilder::UNSAFE];
    }

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
        ->set('nome', $_SESSION[logerr]['nome'])
        ->set('errori', $_SESSION[logerr]['val'], $_SESSION[logerr]['typ'])
        ->build())
      ->build();

    unset($_SESSION[logerr]);
  })
  ->execute();
