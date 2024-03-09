<?php

use Pangine\Pangine;

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'Pangine/Pangine.php';
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

if (is_not_signed_in()) {
  $_SESSION['redirection'] = "albums.php";
  redirect('accedi.php');
}

(new Pangine())
  ->GET_read(function () {
    echo (new HTMLBuilder('../components/layout.html'))
      ->set('title', 'Albums')
      ->set('description', 'Pagina admin di Orchestra per vedere l\'elenco tutti gli album')
      ->set('keywords', '')
      ->set('menu', navbar())
      ->set('breadcrumbs', (new BreadcrumbsBuilder())
        ->addBreadcrumb(new BreadcrumbItem("Home"))
        ->addBreadcrumb(new BreadcrumbItem("Albums", isCurrent: true))
        ->build()
        ->getBreadcrumbsHtml())
      ->set('content', (new HTMLBuilder('../components/albums.html'))
        ->set('albums', implode(
          "\n",
          array_map(function ($album) {
            [ 'id' => $id, 'nome' => $nome] = $album;
            return "<a href='./album.php?id=$id&read=true'>$nome</a>";
          }, dbcall(fn ($db) => $db->albums()))
        ))
        ->build())
      ->build();
  })
  ->execute();
