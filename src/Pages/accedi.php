<?php

use Pangine\Pangine;
use Pangine\PangineAuthenticator;
use Pangine\PangineUnvalidFormManager;
use Pangine\PangineValidator;
use Pangine\PangineValidatorConfig;

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'Pangine/Pangine.php';
require_once 'include/HTMLBuilder.php';
require_once 'components/breadcrumbs.php';
require_once 'components/navbar.php';
require_once 'include/sessionEstablisher.php';
require_once 'include/database.php';
require_once 'include/utils.php';
require_once 'include/pages.php';

set_error_handler(function ($severity, $message, $file, $line) {
  throw new \ErrorException($message, $severity, $severity, $file, $line);
});

if (!try_session()) {
  throw new ErrorException("try_session ha fallito");
}

(new PangineAuthenticator())->authenticate(['UNREGISTERED']);

const logerr = 'logerr';

(new Pangine())
  ->POST_read($accedi = function () {
    $nome = '';

    try {
      [
        'nome' => $nome,
        'password' => $password,
      ] = $_POST;

      $_SESSION['user'] = dbcall(
        fn ($conn) => count($res = $conn->user_with_mail_password($nome, $password)) > 0
          ? $res[0]
          : throw new Exception("Nessun utente trovato. Le credenziali potrebbero essere errate.")
      );

      redirect(extract_from_array_else('redirection', $_SESSION, pages['Home']));
    } catch (Exception $e) {
      $_SESSION[logerr] = [
        'nome' => $nome,
        'risultato' => $e->getMessage(),
        'tiporisultato' => HTMLBuilder::ERROR_P
      ];
      redirect(pages['Accedi']);
    }
  })
  ->GET_read(function () {
    [
      'nome' => $nome,
      'risultato' => $risultato,
      'tiporisultato' => $tiporisultato,
    ] = extract_from_array_else(logerr, $_SESSION, [
      'nome' => '',
      'risultato' => '',
      'tiporisultato' => HTMLBuilder::UNSAFE,
    ]);

    echo (new HTMLBuilder('../components/layout.html'))
      ->set('title', 'Accedi')
      ->set('description', 'Pagina di accesso di Orchestra')
      ->set('keywords', 'Orchestra, musica classica, accesso, log in, sign in')
      ->set('menu', navbar())
      ->set('breadcrumbs', arraybreadcrumb(['Home', 'Accedi']))
      ->set('content', (new HTMLBuilder('../components/accedi.html'))
        ->set('nome-value', $nome)
        ->set('nome-message', '')
        ->set('password-value', '')
        ->set('password-message', $risultato, $tiporisultato)
        ->set('legenda', 'Accedi')
        ->set('nome-autocomplete', 'username')
        ->set('autocomplete-password', 'current-password')
        ->set('page-form', pages['Accedi'])
        ->set('crud-name', 'read')
        ->set('crud-innerhtml', 'Accedi')
        ->set('urlsigninup', pages['Registrati'])
        ->set('innerhtmlsigninup', 'Sei nuovo ? Clicca qui per registrarti')
        ->build())
      ->build();
  })
  ->POST_create(function () use ($accedi) {
    (new PangineValidator($_SERVER['REQUEST_METHOD'], [
      'nome' => (new PangineValidatorConfig(
        notEmpty: true,
        minLength: 6,
        maxLength: 20
      )),
      'password' => (new PangineValidatorConfig(
        notEmpty: true,
        minLength: 8,
        maxLength: 20
      )),
    ]))->validate(pages['Registrati']);

    [
      'nome' => $nome,
      'password' => $password,
    ] = $_POST;

    dbcall(function ($conn) use ($nome, $password) {
      if ($conn->user_exists($nome)) {
        throw new Exception("Il nome utente fornito risulta già registrato.");
      }

      if ($conn->user_sign_up($nome, $password) !== true) {
        throw new Exception("Errore del database.");
      }
    });

    $accedi();
  })
  ->GET_create(function () {
    echo (new HTMLBuilder('../components/layout.html'))
      ->set('title', 'Registrati')
      ->set('description', 'Pagina di registrazione di Orchestra')
      ->set('keywords', 'Orchestra, musica classica, registrazione, sign up')
      ->set('menu', navbar())
      ->set('breadcrumbs', arraybreadcrumb(['Home', 'Registrati']))
      ->set('content', (new PangineUnvalidFormManager((new HTMLBuilder('../components/accedi.html'))
        ->set('password-value', '')
        ->set('password-message', '')
        ->set('nome-value', '')
        ->set('nome-message', '')
        ->set('legenda', 'Registrati')
        ->set('nome-autocomplete', 'off')
        ->set('autocomplete-password', 'new-password')
        ->set('page-form', pages['Accedi'])
        ->set('crud-name', 'create')
        ->set('crud-innerhtml', 'Registrati')
        ->set('urlsigninup', pages['Accedi'])
        ->set('innerhtmlsigninup', 'Hai già un profilo ? Clicca qui per accedere')))
        ->getHTMLBuilder()
        ->build())
      ->build();
  })
  ->execute();
