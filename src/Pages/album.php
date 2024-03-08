<?php

set_include_path($_SERVER["DOCUMENT_ROOT"]);
require_once '../Pangine/Pangine.php';
require_once '../include/HTMLBuilder.php';
require_once '../components/breadcrumbs.php';
require_once '../components/navbar.php';
require_once '../components/sessionEstablisher.php';
require_once '../include/database.php';
require_once '../include/utils.php';

const session_err = 'ALBUM_CREATE_ERR';
const layout = '../components/layout.html';
const content = '../components/album.html';
const dir = '../assets/albumPhotos';

set_error_handler(function ($severity, $message, $file, $line) {
  throw new \ErrorException($message, $severity, $severity, $file, $line);
});

if (!try_session()) {
  throw new ErrorException('try_session ha fallito');
}

if (is_user_signed_in()) {
  redirect('/');
}

if (is_not_signed_in()) {
  $_SESSION['redirection'] = 'album-create.php';
  redirect('accedi.php');
}

if (file_exists(dir)) {
  if (is_dir(dir) === false) {
    throw new Exception(sprintf("'%s' esiste ma non è una directory", dir));
  }
} else {
  if (mkdir(dir, 0777, true) === false) {
    throw new Exception(sprintf("Directory '%s' mancante e non può essere creata : %s", dir, dir));
  }
};

function assertcopertinamaxsize() {
  if ($_FILES['copertina']['size'] > 524288) {
    throw new Exception('Copertina tropppo grande');
  }
}

function savecopertina($id) {
  if (!move_uploaded_file($_FILES['copertina']['tmp_name'], dir . '/' . $id)) {
    throw new Exception('Errore nel salvataggio della copertina');
  }
}

function artistihtmlioptions($artista) {
  return implode("\n", array_map(
    function ($coll) use ($artista) {
      ['id' => $id, 'name' => $nome] = $coll;
      $nome = strip_tags($nome);
      $selection = ($id == $artista) ? 'selected' : '';
      return "<option $selection value=\"$id\">$nome</option>";
    },
    dbcall(fn ($conn) => $conn->artisti())
  ));
}

(new Pangine\Pangine())
  ->POST_create(function () {

    $artista = '';
    $nome = '';
    try {
      [
        'artista' => $artista,
        'nome' => $nome,
      ] = $_POST;

      dbcall(function ($conn) use ($nome, $artista) {
        // TODO riverificare l'ordine delle operazioni
        if ($conn->album_exists($nome, $artista)) {
          throw new Exception('L\'album risulta già essere registrato');
        }
        assertcopertinamaxsize();
        if (($id = $conn->album_add($nome, $artista)) === false) {
          throw new Exception('Errore di inserimento nel database');
        }
        savecopertina($id);
      });

      $_SESSION[session_err] = [
        'Risultato' => 'Album ' . $nome . ' è stato creato con successo',
        'TipoRisultato' => HTMLBuilder::SUCCESS_P,
        'Artista' => '',
        'Nome' => '',
      ];
    } catch (Exception $e) {
      $_SESSION[session_err] = [
        'Risultato' => $e->getMessage(),
        'TipoRisultato' => HTMLBuilder::ERROR_P,
        'Artista' => $artista,
        'Nome' => $nome,
      ];
    }
    redirect('album.php?create=true');
  })
  ->GET_create(function () {
    [
      'Risultato' => $risultato,
      'TipoRisultato' => $tiporisultato,
      'Artista' => $artista,
      'Nome' => $nome,
    ] = extract_from_array_else(session_err, $_SESSION, [
      'Risultato' => '',
      'TipoRisultato' => HTMLBuilder::UNSAFE,
      'Artista' => '',
      'Nome' => '',
    ]);

    echo (new HTMLBuilder(layout))
      ->set('title', 'Aggiungi Album')
      ->set('description', 'Pagina admin di Orchestra per aggiungere album')
      ->set('keywords', '')
      ->set('menu', navbar())
      ->set('breadcrumbs', arraybreadcrumb(['Home', 'Albums', 'Crea album']))
      ->set('content', (new HTMLBuilder(content))
        ->set('legenda', 'Creazione album')
        ->set('artisti', artistihtmlioptions($artista))
        ->set('valorenomealbum', $nome)
        ->set('action', 'album.php?create=true')
        ->set('risultato', $risultato, $tiporisultato)
        ->set('method', 'post')
        ->set('nascondidelete', 'hidden')
        ->set('nomecomando', 'create')
        ->set('valorecomando', 'true')
        ->set('innerhtmlcomando', 'Crea')
        ->set('valoreid', '')
        ->set('disabilitaid', 'disabled')
        ->set('disabilitaartista', '')
        ->set('disabilitanomealbum', '')
        ->set('disabilitacopertina', '')
        ->build())
      ->build();
  })
  ->POST_update(function () {
    $artista = '';
    $nome = '';
    $id = '';
    try {
      [
        'artista' => $artista,
        'nome' => $nome,
        'id' => $id,
      ] = $_POST;

      // TODO verificare l'ordine delle operazioni
      dbcall(function (Database $conn) use ($id, $nome, $artista) {
        $conn->album_update($id, $nome, $artista);
      });

      if ($_FILES['copertina']['name'] !== '') {
        assertcopertinamaxsize();
        savecopertina($id);
      }
    } catch (Exception $e) {
      $_SESSION[session_err] = [
        'Risultato' => $e->getMessage(),
        'TipoRisultato' => HTMLBuilder::ERROR_P,
        'Artista' => $artista,
        'Nome' => $nome,
      ];
    }
    redirect('./album.php?read=true&id=' . $id);
  })
  ->POST_delete(function () {
    // TODO
  })
  ->GET_read(function () {
    [
      'Risultato' => $risultato,
      'TipoRisultato' => $tiporisultato,
      'Artista' => $artista,
      'Nome' => $nome,
    ] = extract_from_array_else(session_err, $_SESSION, [
      'Risultato' => '',
      'TipoRisultato' => HTMLBuilder::UNSAFE,
      'Artista' => '',
      'Nome' => '',
    ]);

    [
      'nome' => $nome,
      'artista' => $artista,
    ] = dbcall(fn ($conn) => $conn->album($_GET['id'])[0]);

    echo (new HTMLBuilder(layout))
      ->set('title', 'Ispeziona Album')
      ->set('description', 'Pagina admin di Orchestra per ispezionare un album')
      ->set('keywords', '')
      ->set('menu', navbar())
      ->set('breadcrumbs', arraybreadcrumb(['Home', 'Albums', 'Ispeziona album']))
      ->set('content', (new HTMLBuilder(content))
        ->set('legenda', 'Creazione album')
        ->set('artisti', artistihtmlioptions($artista))
        ->set('valorenomealbum', $nome)
        ->set('method', 'get')
        ->set('action', 'album.php')
        ->set('risultato', $risultato, $tiporisultato)
        ->set('nascondidelete', 'hidden')
        ->set('nomecomando', 'update')
        ->set('valorecomando', 'true')
        ->set('innerhtmlcomando', 'Modifica')
        ->set('valoreid', $_GET['id'])
        ->set('disabilitaid', '')
        ->set('disabilitaartista', 'disabled')
        ->set('disabilitanomealbum', 'disabled')
        ->set('disabilitacopertina', 'disabled')
        ->build())
      ->build();
  })
  ->GET_update(function () {
    [
      'nome' => $nome,
      'artista' => $artista,
    ] = dbcall(fn ($conn) => $conn->album($_GET['id'])[0]);

    echo (new HTMLBuilder(layout))
      ->set('title', 'Modifica Album')
      ->set('description', 'Pagina admin di Orchestra per modificare album esistenti')
      ->set('keywords', '')
      ->set('menu', navbar())
      ->set('breadcrumbs', arraybreadcrumb(['Home', 'Albums', 'Modifica album']))
      ->set('content', (new HTMLBuilder(content))
        ->set('legenda', 'Creazione album')
        ->set('artisti', artistihtmlioptions($artista))
        ->set('valorenomealbum', $nome)
        ->set('method', 'post')
        ->set('action', 'album.php')
        ->set('risultato', '')
        ->set('nascondidelete', '')
        ->set('nomecomando', 'update')
        ->set('valorecomando', 'true')
        ->set('innerhtmlcomando', 'Salva')
        ->set('valoreid', $_GET['id'])
        ->set('disabilitaid', '')
        ->set('disabilitaartista', '')
        ->set('disabilitanomealbum', '')
        ->set('disabilitacopertina', '')
        ->build())
      ->build();
  })
  ->execute();
