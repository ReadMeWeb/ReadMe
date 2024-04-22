<?php 

use Pangine\Pangine;
use Pangine\utils\LayoutBuilder;
use Pangine\utils\Validator;
use Pangine\utils\ValidatorMe;
use Utils\Database;

require_once __DIR__ . '/../Utils/Stream.php';
require_once __DIR__ . '/../Utils/Database.php';
require_once __DIR__ . '/../Utils/Accedi.php';
require_once __DIR__ . '/../Pangine/Pangine.php';
require_once __DIR__ . '/../Pangine/utils/LayoutBuilder.php';


(new Pangine())
  ->add_renderer_POST('accedi', needs_database: true)
  ->add_renderer_GET(function () {
    echo (new LayoutBuilder())
      ->tag_lazy_replace('title', 'Accedi')
      ->tag_lazy_replace('description', 'Pagina di accesso alla biblioteca di ReadMe')
      ->tag_lazy_replace('keywords', 'ReadMe, biblioteca, libri, narrativa, prenotazioni, accedi')
      ->tag_lazy_replace('menu', Pangine::navbar_list())
      ->tag_lazy_replace('breadcrumbs', Pangine::breadcrumbs_generator(array('Home', 'Accedi')))
      ->tag_istant_replace('content', file_get_contents(__DIR__ . '/../templates/accedi_registrati_content.html'))
      ->tag_lazy_replace('legenda', 'Accedi')
      ->tag_lazy_replace('nome-autocomplete', 'username')
      ->tag_lazy_replace('password-autocomplete', 'current-password')
      ->tag_lazy_replace('crud-name', '')
      ->tag_lazy_replace('crud-innerhtml', 'Accedi')
      ->tag_lazy_replace('sign-in-up-url', '/marango/Pages/registrati.php')
      ->tag_lazy_replace('sign-in-up-url-innerhtml', 'Sei nuovo ? Clicca qui per registrarti')

      ->tag_lazy_replace('nome-value', '')
      ->tag_lazy_replace('nome-message', '')
      ->tag_lazy_replace('password-value', '')
      ->tag_lazy_replace('password-message', '')
      ->build();
  })
  ->execute();
