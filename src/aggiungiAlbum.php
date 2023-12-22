<?php
require_once 'data/database.php';
require_once 'components/navbar.php';
require_once 'components/sessionEstablisher.php';
require_once 'components/breadcrumbs/breadcrumbItem.php';
require_once 'components/breadcrumbs/breadcrumbsBuilder.php';

function gethandlererror($name)
{
  if (array_key_exists($name, $_SESSION)) {
    $e = $_SESSION[$name];
    unset($_SESSION[$name]);
    return $e;
  }
  return false;
}

if (try_session()) {
  //TODO pagina accessibile solo da utenti admin
  if (array_key_exists('mail', $_SESSION["user"])) {
    header("Location: /");
  }

  //TODO riutilizzare un layout differente
  $page = file_get_contents("./components/layout.html");
  $content = file_get_contents("./components/aggiungiAlbum.html");

  $conn = new Database();
  $aristi = implode(
    "\n",
    array_map(
      function ($coll) {
        [ "id" => $id, "name" => $nome] = $coll;
        $nome = strip_tags($nome);
        return "<option value=\"$id\">$nome</option>";
      },
      $conn->artisti()
    )
  );
  $conn->close();
  $content = str_replace("{{artisti}}", $aristi, $content);

  // TODO generalizzare il nome degli errori
  $errori = "";
  if ($e = gethandlererror('addAlbumErrors')) {
    $errori = "<h1>Errore</h1>
      <p class='error'>" . (strip_tags($e->getmessage())) . "</p>";
  }
  if ($e = gethandlererror('addAlbumSuccess')) {
    $errori = "<h1>Successo</h1>
      <p class='error'>" . (strip_tags($e->getmessage())) . "</p>";
  }

  //TODO ripristinare i valori immessi in seguito a un errore

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
}
