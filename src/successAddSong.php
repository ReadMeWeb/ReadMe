<?php
set_include_path($_SERVER["DOCUMENT_ROOT"]);
require_once './components/navbar.php'
require_once './components/member.php'
require_once './components/breadcrumbs.php'

setlocale(LC_ALL, 'it_IT');

/* VARIABILI */

$title = "Successo - Aggiungi Canzone - Orchestra";
$description = "Pagina di conferma dell'avvenuto inserimento di un nuovo brano nel sistema";
$keywords = implode(", ", array("Orchestra", "Aggiungi Canzone", "Nuova Canzone", "Canzone", "Inserimento"));
$breadcrumbs = (new BreadcrumbsBuilder())
    ->addBreadcrumb(new BreadcrumbItem("Home"))
    ->addBreadcrumb(new BreadcrumbItem("Aggiungi Canzone"))
    ->addBreadcrumb(new BreadcrumbItem("Canzone Aggiunta", true))
    ->build();
/* GENERAZIONE HTML */

$html = file_get_contents("./components/layoutLogged.html");
$content = file_get_contents("./components/addSong/successAddSong.html");

$layout = file_get_contents("components/layoutLogged.html");
$placeholdersTemplates = array("{{title}}", "{{menu}}", "{{breadcrumbs}}", "{{content}}");
$placeholdersValues = array($title, navbar(), $breadcrumbs->getBreadcrumbsHtml(), $content);
echo str_replace($placeholdersTemplates, $placeholdersValues, $layout);
