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

if (count($_POST) == 0) {
  goto get;
}

// ========================================================================================================================
// POST
// ========================================================================================================================

try {
  // TODO aggiornare quando la copertina verrà usata
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


  $_SESSION['addAlbumSuccess'] = new Exception("Album $nome aggiunto con successo");
  header("Location: /aggiungiAlbum.php");
} catch (Exception $e) {
  $_SESSION['addAlbumErrors'] = $e;
  header("Location: /aggiungiAlbum.php");
}

exit();

// ========================================================================================================================
// GET
// ========================================================================================================================

get:


function gethandlererror($name)
{
  if (array_key_exists($name, $_SESSION)) {
    $e = $_SESSION[$name];
    unset($_SESSION[$name]);
    return $e;
  }
  return false;
}

//TODO pagina accessibile solo da utenti admin

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

//TODO generalizzare il nome degli errori
//TODO ripristinare i valori immessi in seguito a un errore
$errori = "";
if ($e = gethandlererror('addAlbumErrors')) {
  $errori = "<h1>Errore</h1>
      <p class='error'>" . (strip_tags($e->getmessage())) . "</p>";
}
if ($e = gethandlererror('addAlbumSuccess')) {
  $errori = "<h1>Successo</h1>
      <p class='error'>" . (strip_tags($e->getmessage())) . "</p>";
}


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
echo $page;
