<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'include/sessionEstablisher.php';
require_once 'components/errorePermessi.php';
require_once 'components/breadcrumbs.php';
require_once 'components/navbar.php';
require_once 'include/database.php';
require_once 'include/errors_utils.php';

function sessionUserIsAdmin(): bool
{
    return $_SESSION["user"]["status"] == "ADMIN";
}

function getArtistSelectionContent(array $artists, string|null $errors): string
{
    $content = file_get_contents("components/addSong/selectArtist.html");
    $artists_list = "";
    foreach ($artists as $artist) {
        $artists_list .= "<option value=\"" . $artist["id"] . "\">" . $artist["name"] . "</option>";
    }
    $content = str_replace("{{artists}}", $artists_list, $content);
    if ($errors != null) $content = str_replace("{{errors}}", $errors, $content);
    else $content = str_replace("{{errors}}", "", $content);

    return $content;
}

$title = "Selezione Artista - Aggiungi Canzone - Orchestra";
$description = "Pagina per l'inserimento di una nuova canzone all'interno di Orchestra";
$keywords = implode(", ", array("Orchestra", "Aggiungi Canzone", "Nuova Canzone", "Canzone", "Inserimento"));
$breadcrumbs = (new BreadcrumbsBuilder())
    ->addBreadcrumb(new BreadcrumbItem("Home"))
    ->addBreadcrumb(new BreadcrumbItem("Catalogo"))
    ->addBreadcrumb(new BreadcrumbItem("Aggiungi Canzone", true))
    ->build();

if (!sessionUserIsAdmin()) {
    echo getPermissionDeniedPage($title, $description, $keywords, $breadcrumbs);
    return;
}

$db = new Database();
$artists = $db->fetch_artist_info();
$db->close();

$content = getArtistSelectionContent($artists, getAndClearErrorStringFromSession("sel_artist"));

$layout = file_get_contents("components/layoutLogged.html");
$placeholdersTemplates = array("{{title}}", "{{menu}}", "{{breadcrumbs}}", "{{content}}");
$placeholdersValues = array($title, navbar(), $breadcrumbs->getBreadcrumbsHtml(), $content);

$layout = str_replace($placeholdersTemplates, $placeholdersValues, $layout);

echo $layout;
