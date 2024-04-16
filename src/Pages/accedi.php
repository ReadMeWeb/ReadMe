<?php

use Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;
use Utils\Database;

require_once __DIR__ . '/../Utils/ErroriMigliori.php';
require_once __DIR__ . '/../Utils/Stream.php';
require_once __DIR__ . '/../Utils/Database.php';
require_once __DIR__ . '/../Pangine/Pangine.php';
require_once __DIR__ . '/../Pangine/utils/LayoutBuilder.php';

const registrati = 'registrati';

(new Pangine())
  //accedi
  ->add_renderer_POST($accedi = function (Database $conn) {
    $profilo = $conn->execute_query(
      'SELECT username, status FROM Users WHERE username = ? AND password = ?;',
      $_POST['nome'],
      $_POST['password']
    );
    if (count($profilo) == 1) {
      $_SESSION['user'] = $profilo;
      echo "loggato B) ";
    } else {
      echo "account non trovato lmao";
    }
  }, needs_database: true)
  ->add_renderer_GET(function () {
    echo (new LayoutBuilder())
      ->tag_lazy_replace('title', 'Accedi')
      ->tag_lazy_replace('description', 'Pagina di accesso alla biblioteca di ReadMe')
      ->tag_lazy_replace('keywords', 'ReadMe, biblioteca, libri, narrativa, prenotazioni, accedi')
      ->tag_lazy_replace('menu', Pangine::navbar_list())
      ->tag_lazy_replace('breadcrumbs', Pangine::breadcrumbs_generator(array('Home', 'Accedi')))
      ->tag_lazy_replace('content', file_get_contents(__DIR__ . '/../templates/accedi.html'))
      ->tag_lazy_replace('page-form', '?')
      ->tag_lazy_replace('legenda', 'Accedi')
      ->tag_lazy_replace('nome-value', '')
      ->tag_lazy_replace('nome-autocomplete', '')
      ->tag_lazy_replace('nome-message', '')
      ->tag_lazy_replace('password-value', '')
      ->tag_lazy_replace('password-autocomplete', '')
      ->tag_lazy_replace('password-message', '')
      ->tag_lazy_replace('crud-name', '')
      ->tag_lazy_replace('crud-innerhtml', 'Accedi')
      ->tag_lazy_replace('sign-in-up-url', '?' . registrati . '=1')
      ->tag_lazy_replace('sign-in-up-url-innerhtml', 'Sei nuovo ? Clicca qui per registrarti')
      ->build();
  })
  //registrati
  ->add_renderer_POST(function () use ($accedi) {
  }, registrati, true)
  ->add_renderer_GET(function () {
    echo (new LayoutBuilder())
      ->tag_lazy_replace('title', 'Accedi')
      ->tag_lazy_replace('description', 'Pagina di accesso alla biblioteca di ReadMe')
      ->tag_lazy_replace('keywords', 'ReadMe, biblioteca, libri, narrativa, prenotazioni, accedi')
      ->tag_lazy_replace('menu', Pangine::navbar_list())
      ->tag_lazy_replace('breadcrumbs', Pangine::breadcrumbs_generator(array('Home', 'Accedi')))
      ->tag_lazy_replace('content', file_get_contents(__DIR__ . '/../templates/accedi.html'))
      ->tag_lazy_replace('page-form', '?' . registrati)
      ->tag_lazy_replace('legenda', 'Accedi')
      ->tag_lazy_replace('nome-value', '')
      ->tag_lazy_replace('nome-autocomplete', '')
      ->tag_lazy_replace('nome-message', '')
      ->tag_lazy_replace('password-value', '')
      ->tag_lazy_replace('password-autocomplete', '')
      ->tag_lazy_replace('password-message', '')
      ->tag_lazy_replace('crud-name', registrati)
      ->tag_lazy_replace('crud-innerhtml', 'Registrati')
      ->tag_lazy_replace('sign-in-up-url', '?')
      ->tag_lazy_replace('sign-in-up-url-innerhtml', 'Hai già un profilo ? Clicca qui per accedere')
      ->build();
  }, registrati)
  ->execute();
