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

function getSongInsertion(array $selectedArtist, Database $db): string
{
    $content = file_get_contents("components/addSong/addSong.html");
    $albums_fetch_array = $db->fetch_albums_info_by_artist_id($selectedArtist["id"]);
    $albums_list = "";
    foreach ($albums_fetch_array as $album) {
        $albums_list .= "<option value=\"" . $album["id"] . "\">" . $album["name"] . "</option>";
    }
    $errors = getAndClearErrorStringFromSession("new_song_error");
    $insertSongTemplate = array("{{artist_name}}", "{{song_title}}","{{artist_id}}","{{albums}}", "{{errors}}");

    $insertSongValues = array($selectedArtist["name"], "", $selectedArtist["id"], $albums_list, $errors);
    $content = str_replace($insertSongTemplate, $insertSongValues, $content);
    return $content;
}

function getSongInsertionSession(array $selectedArtist, string $selectedAlbum, $songTitle, Database $db): string
{
    $content = file_get_contents("components/addSong/addSong.html");
    $albums_fetch_array = $db->fetch_albums_info_by_artist_id($selectedArtist["id"]);
    $albums_list = "";
    foreach ($albums_fetch_array as $album) {
        if($selectedAlbum == $album["id"]){
            $albums_list .= "<option value=\"" . $album["id"] . "\" selected>" . $album["name"] . "</option>";
        }else{
            $albums_list .= "<option value=\"" . $album["id"] . "\">" . $album["name"] . "</option>";
        }
    }
    $errors = getAndClearErrorStringFromSession("new_song_error");
    $insertSongTemplate = array("{{artist_name}}", "{{song_title}}","{{artist_id}}","{{albums}}", "{{errors}}");

    $insertSongValues = array($selectedArtist["name"], $songTitle, $selectedArtist["id"], $albums_list, $errors);
    $content = str_replace($insertSongTemplate, $insertSongValues, $content);
    return $content;
}

try_session();

$title = "Informazioni Brano - Aggiungi Canzone - Orchestra";
$description = "Pagina per l'inserimento di una nuova canzone all'interno di Orchestra";
$keywords = implode(", ", array("Orchestra", "Aggiungi Canzone", "Nuova Canzone", "Canzone", "Inserimento"));
$breadcrumbs = (new BreadcrumbsBuilder())
    ->addBreadcrumb(new BreadcrumbItem("Home"))
    ->addBreadcrumb(new BreadcrumbItem("Aggiungi Canzone"))
    ->addBreadcrumb(new BreadcrumbItem("Informazioni Brano", true))
    ->build();

if (!sessionUserIsAdmin()) {
    echo getPermissionDeniedPage($title, $description, $keywords, $breadcrumbs);
    return;
}

$db = new Database();

$artists = $db->fetch_artist_info();

if(isset($_SESSION["saved_data"])){
    $sd = $_SESSION["saved_data"];
    $artist_id = $sd["artist_id"] ?? 0;
    $selectedAlbum = $sd["album_id"] ?? 0;
    $songTitle = $sd["song_title"] ?? "";
    unset($_SESSION["saved_data"]);

    $selectedArtist = $db->fetch_artist_info_by_id($artist_id);
    if ($selectedArtist != null) {
        $content = getSongInsertionSession($selectedArtist, $selectedAlbum, $songTitle, $db);
        $db->close();

        $layout = file_get_contents("components/layoutLogged.html");
        $placeholdersTemplates = array("{{title}}", "{{menu}}", "{{breadcrumbs}}", "{{content}}");
        $placeholdersValues = array($title, navbar(), $breadcrumbs->getBreadcrumbsHtml(), $content);
        echo str_replace($placeholdersTemplates, $placeholdersValues, $layout);

        return;
    }
}

if (isset($_POST["artist"])) {
    $selectedArtist = $db->fetch_artist_info_by_id($_POST["artist"]);
    if ($selectedArtist != null) {
        $content = getSongInsertion($selectedArtist, $db);
        $db->close();

        $layout = file_get_contents("components/layoutLogged.html");
        $placeholdersTemplates = array("{{title}}", "{{menu}}", "{{breadcrumbs}}", "{{content}}");
        $placeholdersValues = array($title, navbar(), $breadcrumbs->getBreadcrumbsHtml(), $content);
        echo str_replace($placeholdersTemplates, $placeholdersValues, $layout);

        return;
    }
}

setErrorStringToSession("sel_artist", "L'artista selezionato non è stato trovato. Riprova, selezionandolo dal menu sottostante.");
$db->close();
header("Location: " . UrlUtils::getUrl("selectartist.php"));
die();
