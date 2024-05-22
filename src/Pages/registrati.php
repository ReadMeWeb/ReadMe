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
  ->add_renderer_POST(
    function (Database $conn) {
      (new Validator(Pangine::path() . 'Pages/registrati.php'))->add_parameter('nome')->is_string(
        string_parser: fn () =>
        $conn->execute_query('select count(*) = 0 as c from Users where username = ?;', $_POST['nome'])[0]['c']
          ? ''
          : 'Nome utente già registrato'
      )->validate();

      $conn->execute_query('insert into Users values(?,?,\'USER\');', $_POST['nome'], $_POST['password']);
      Pangine::set_general_message("Creazione dell'account avvenuta con successo!","success");
      Pangine::redirect("Accedi");
    },
    needs_database: true,
    validator: (new Validator(url_in_case_of_failure: Pangine::path() . 'Pages/registrati.php'))
      ->add_parameter('nome')->is_string(4, 20)
      ->add_parameter('password')->is_string(4, 128)
  )
  ->add_renderer_GET(
    function () {
      echo (new LayoutBuilder())
        ->tag_lazy_replace('title', 'Registrati')
        ->tag_lazy_replace('description', 'Pagina di registrazione alla biblioteca di ReadMe')
        ->tag_lazy_replace('keywords', 'ReadMe, biblioteca, libri, narrativa, prenotazioni, registrazione')
        ->tag_lazy_replace('menu', Pangine::navbar_list())
        ->tag_lazy_replace('breadcrumbs', Pangine::breadcrumbs_generator(array('Home', 'Registrati')))
        ->tag_istant_replace('content', file_get_contents(__DIR__ . '/../templates/accedi_registrati_content.html'))
        ->tag_lazy_replace('form_action', 'Pages/registrati.php')
        ->tag_lazy_replace('legenda', 'Registrati')
        ->tag_lazy_replace('nome-autocomplete', 'off')
        ->tag_lazy_replace('password-autocomplete', 'new-password')
        ->tag_lazy_replace('crud-name', 'registrati')
        ->tag_lazy_replace('crud-innerhtml', 'Registrati')
        ->tag_lazy_replace('sign-in-up-url', Pangine::path() . 'Pages/accedi.php')
        ->tag_lazy_replace('sign-in-up-url-innerhtml', 'Hai già un profilo ? Clicca qui per accedere')

        ->tag_lazy_replace('nome-value', '')
        ->tag_lazy_replace('nome-message', '')
        ->tag_lazy_replace('password-value', '')
        ->tag_lazy_replace('password-message', '')

        ->build();
    }
  )
  ->execute();
