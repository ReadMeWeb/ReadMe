<?php

require_once 'components/breadcrumbs/breadcrumbItem.php';
require_once 'components/breadcrumbs/breadcrumbsBuilder.php';
require_once 'components/navbar.php';
require_once 'components/sessionEstablisher.php';
require_once 'data/database.php';
require_once 'handlers/utils.php';

set_error_handler(function ($severity, $message, $file, $line) {
  throw new \ErrorException($message, $severity, $severity, $file, $line);
});

if (!try_session()) {
  throw new ErrorException("try_session ha fallito");
}

// TODO pagina accessibile solo da utenti admin
// TODO - accesso con reindirizzamento - dovrebbe bastare aggiungere $_SESSION['redirection'] = <questa pagina>
// if (!is_user_signed_in()) {
//   redirect('/accedi.php');
// }

$errori = '';
$successo = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  goto GET;
}

// ========================================================================================================================
// POST
// ========================================================================================================================

try {
  [
    "artista" => $artista,
    "nome" => $nome,
  ] = $_POST;

  $conn = new Database();
  if ($conn->album_exists($nome, $artista)) {
    throw new Exception("L'album risulta già essere registrato");
  }

  $dir = "assets/albumPhotos";
  if ($e = file_exists($dir)) {
    if ($d = is_dir($dir) === false) {
      throw new Exception("'$dir' esiste ma non è una directory");
    }
  } else {
    if ($m = mkdir($dir, 0777, true) === false) {
      throw new Exception("Directory '$dir' mancante e non può essere creata : $dir");
    }
  };

  // Il numero scelto è arbitrario, può essere rimosso
  // se non da rimuovere:
  // TODO : feed forward - avviso dell'utente che il file può essere grande solo tot 
  if ($_FILES["copertina"]["size"] > 500000) {
    throw new Exception("File tropppo grande");
  }

  if (!move_uploaded_file($_FILES["copertina"]["tmp_name"], "$dir/$artista-$nome")) {
    throw new Exception("Errore nel salvataggio della copertina");
  }

  if (!$conn->album_add($nome, $artista, "$artista-$nome")) {
    throw new Exception("Errore di inserimento nel database");
  }
  $conn->close();

  $successo = '
    <h1>Successo</h1>
    <ul class="successo">
      <li>Album ' . $nome . ' aggiunto con successo</li>
    </ul>
  ';
  $errori = '';
} catch (Exception $e) {
  $successo = '';
  $errori = '
    <h1>Errore</h1>
    <ul class="error">
      <li>' . (strip_tags($e->getMessage())) . '</li>
    </ul>
  ';
}

// ========================================================================================================================
GET:
// ========================================================================================================================

//TODO utilizzare un layout differente (?)
$page = file_get_contents("./components/layout.html");
$content = file_get_contents("./components/aggiungiAlbum.html");

$conn = new Database();
$aristi = implode(
  "\n",
  array_map(
    function ($coll) {
      ["id" => $id, "name" => $nome] = $coll;
      $nome = strip_tags($nome);
      return "<option value=\"$id\">$nome</option>";
    },
    $conn->artisti()
  )
);
$conn->close();
$content = str_replace("{{artisti}}", $aristi, $content);

//TODO aggiornare le breadcrumbs
$breadcrumbs = (new BreadcrumbsBuilder())
  ->addBreadcrumb(new BreadcrumbItem("Home"))
  ->build()
  ->getBreadcrumbsHtml();

$page = str_replace("{{title}}", "Aggiungi Album", $page);
$page = str_replace("{{description}}", "Pagina admin di Orchestra per aggiungere album", $page);
$page = str_replace("{{keywords}}", "", $page);
$page = str_replace("{{menu}}", navbar(), $page);
$page = str_replace("{{breadcrumbs}}", $breadcrumbs, $page);

$page = str_replace("{{content}}", $content, $page);
$page = str_replace("{{errori}}", $errori, $page);
$page = str_replace("{{successo}}", $successo, $page);
echo $page;
