<?php
set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'components/navbar.php';
require_once 'components/breadcrumbs.php';
require_once 'include/pages.php';
require_once 'include/utils.php';

$get_unallowed = function () {
  echo (new HTMLBuilder("../components/layout.html"))
    ->set('title', "Permessi insufficienti")
    ->set('description', "Non possiedi i permessi sufficienti al fine di visualizzare la pagina richiesta in precedenza.")
    ->set('keywords', "Orchestra, Permessi insufficienti, Musica")
    ->set('menu', navbar())
    ->set('breadcrumbs', arraybreadcrumb(['Home', 'Permessi insufficienti']))
    ->set('content', (new HTMLBuilder("../components/unallowed.html"))
      ->set('pages-catalogo', pages['Catalogo'])
      ->build())
    ->build();
};
