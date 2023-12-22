<?php
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
  if (array_key_exists('mail', $_SESSION["user"])) {
    header("Location: /");
  }

  $page = file_get_contents("./components/layout.html");
  $content = file_get_contents("./components/accedi.html");
  $errori = "";
  if ($e = gethandlererror('loginErrors')) {
    $errori = "<h1>Errore</h1>
      <p class='error'>" . (strip_tags($e->getmessage())) . "</p>";
  }

  //TODO ripristinare la mail / password all'interno degli input

  $breadcrumbs = (new BreadcrumbsBuilder())
    ->addBreadcrumb(new BreadcrumbItem("Home"))
    ->addBreadcrumb(new BreadcrumbItem("Accedi", isCurrent: true))
    ->build()
    ->getBreadcrumbsHtml();

  $page = str_replace("{{title}}", "Accedi", $page);
  $page = str_replace("{{description}}", "Pagina di accesso di Orchestra", $page);
  $page = str_replace("{{keywords}}", "Orchestra, musica classica, accesso, log in, sign in", $page);
  $page = str_replace("{{menu}}", navbar(), $page);
  $page = str_replace("{{breadcrumbs}}", $breadcrumbs, $page);

  $page = str_replace("{{content}}", $content, $page);
  $page = str_replace("{{errori}}", $errori, $page);
  echo $page;
}
