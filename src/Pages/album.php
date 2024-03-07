<?php

require_once '../Pangine/Pangine.php';
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

if (is_user_signed_in()) {
  redirect('/');
}

if (is_not_signed_in()) {
  $_SESSION['redirection'] = "album-create.php";
  redirect('accedi.php');
}

const session_err = 'ALBUM_CREATE_ERR';

function arraybreadcrumb($array) {
  $arrayitems = array_map(fn ($item) => new BreadcrumbItem($item), $array);
  $arrayitems[$last = array_key_last($array)] = new BreadcrumbItem($array[$last], isCurrent: true);
  $builder = new BreadcrumbsBuilder();
  foreach ($arrayitems as $i) {
    $builder->addBreadcrumb($i);
  }
  return $builder->build()->getBreadcrumbsHtml();
}

function artistihtmlioptions($artista) {
  return implode("\n", array_map(
    function ($coll) use ($artista) {
      ["id" => $id, "name" => $nome] = $coll;
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
        "artista" => $artista,
        "nome" => $nome,
      ] = $_POST;

      $conn = new Database();
      if ($conn->album_exists($nome, $artista)) {
        throw new Exception("L'album risulta già essere registrato");
      }

      $dir = "../assets/albumPhotos";
      if ($e = file_exists($dir)) {
        if ($d = is_dir($dir) === false) {
          throw new Exception("'$dir' esiste ma non è una directory");
        }
      } else {
        if ($m = mkdir($dir, 0777, true) === false) {
          throw new Exception("Directory '$dir' mancante e non può essere creata : $dir");
        }
      };

      if ($_FILES["copertina"]["size"] > 524288) {
        throw new Exception("Copertina tropppo grande");
      }

      if (!move_uploaded_file($_FILES["copertina"]["tmp_name"], "$dir/$artista-$nome")) {
        throw new Exception("Errore nel salvataggio della copertina");
      }

      if (!$conn->album_add($nome, $artista, "$artista-$nome")) {
        throw new Exception("Errore di inserimento nel database");
      }
      $conn->close();


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

    echo (new HTMLBuilder('../components/layout.html'))
      ->set('title', 'Aggiungi Album')
      ->set('description', 'Pagina admin di Orchestra per aggiungere album')
      ->set('keywords', '')
      ->set('menu', navbar())
      ->set('breadcrumbs', (new BreadcrumbsBuilder())
        ->addBreadcrumb(new BreadcrumbItem("Home"))
        ->addBreadcrumb(new BreadcrumbItem("Aggiungi Album", isCurrent: true))
        ->build()
        ->getBreadcrumbsHtml())
      ->set('content', (new HTMLBuilder('../components/aggiungiAlbum.html'))
        ->set('legenda', 'Creazione album')
        ->set('artisti', implode("\n", array_map(
          function ($coll) use ($artista) {
            ["id" => $id, "name" => $nome] = $coll;
            $nome = strip_tags($nome);
            $selection = ($id == $artista) ? 'selected' : '';
            return "<option $selection value=\"$id\">$nome</option>";
          },
          dbcall(fn ($conn) => $conn->artisti())
        )))
        ->set('nomealbum', $nome)
        ->set('action', 'album.php?create=true')
        ->set('risultato', $risultato, $tiporisultato)
        ->set('nascondidelete', 'hidden')
        ->set('nomecomando', '')
        ->set('valorecomando', 'Crea')
        ->build())
      ->build();
  })
  ->GET_read(function () {
    [
      'nome' => $nome,
      'artista' => $artista
    ] = dbcall(fn ($conn) => $conn->album($_GET['id'])[0]);

    echo (new HTMLBuilder('../components/layout.html'))
      ->set('title', 'Aggiungi Album')
      ->set('description', 'Pagina admin di Orchestra per aggiungere album')
      ->set('keywords', '')
      ->set('menu', navbar())
      ->set('breadcrumbs', arraybreadcrumb(["Home", "Aggiungi Album"]))
      ->set('content', (new HTMLBuilder('../components/aggiungiAlbum.html'))
        ->set('legenda', 'Creazione album')
        ->set('artisti', artistihtmlioptions($artista))
        ->set('nomealbum', $nome)
        ->set('action', 'album.php?id=' . ($_GET['id']) . '&update=true')
        ->set('risultato', '')
        ->set('nascondidelete', 'hidden')
        ->set('nomecomando', '')
        ->set('valorecomando', 'Modifica')
        ->build())
      ->build();
  })
->GET_update(function (){
    [
      'nome' => $nome,
      'artista' => $artista
    ] = dbcall(fn ($conn) => $conn->album($_GET['id'])[0]);

    echo (new HTMLBuilder('../components/layout.html'))
      ->set('title', 'Aggiungi Album')
      ->set('description', 'Pagina admin di Orchestra per aggiungere album')
      ->set('keywords', '')
      ->set('menu', navbar())
      ->set('breadcrumbs', arraybreadcrumb(["Home", "Aggiungi Album"]))
      ->set('content', (new HTMLBuilder('../components/aggiungiAlbum.html'))
        ->set('legenda', 'Creazione album')
        ->set('artisti', artistihtmlioptions($artista))
        ->set('nomealbum', $nome)
        ->set('action', 'album.php?id=' . ($_GET['id']) . 'update=true')
        ->set('risultato', '')
        ->set('nascondidelete', '')
        ->set('nomecomando', '')
        ->set('valorecomando', 'Salva')
        ->build())
      ->build();
})
  ->execute();