<?php

require_once "components/navbar.php";
require_once "components/sessionEstablisher.php";
require_once "components/artist.php";
require_once "components/album.php";
require_once "components/song.php";
require_once "components/breadcrumbs/breadcrumbItem.php";
require_once "components/breadcrumbs/breadcrumbsBuilder.php";
require_once "data/database.php";

try_session();

$title = "Catalogo";
$description = "Pagina di catalogo musicale di musica classica di Orchestra";
$keywords = implode(", ", array("Orchestra", "Catalogo musicale", "Musica", "Album", "Artisti", "Canzoni"));

$layout = file_get_contents("components/layout.html");
$layout = str_replace("{{menu}}", navbar(), $layout);
$layout = str_replace("{{breadcrumbs}}",
    (new BreadcrumbsBuilder())
        ->addBreadcrumb(new BreadcrumbItem("Home"))
        ->addBreadcrumb(new BreadcrumbItem("Catalogo", true))
        ->build()
        ->getBreadcrumbsHtml(),
    $layout);
$layout = str_replace("{{title}}",$title,$layout);
$layout = str_replace("{{description}}",$description,$layout);
$layout = str_replace("{{keywords}}",$keywords,$layout);
$content = file_get_contents("components/catalogo.html");

// CREAZIONE CONTENT
$db = new Database();

$artists = [];
$albums = [];
$songs = [];

if ($db->status()) {
    $artists = $db->fetch_artist_info();
    $albums = $db->fetch_albums_info();
    $songs = $db->fetch_songs_info();
}
$db->close();

if ($_SESSION["user"]["status"] == "UNREGISTERED") {
    $lista_artisti = [];
    foreach ($artists as $artist){
        $lista_artisti[] = (new artist(
            $artist["id"],
            $artist["name"],
            $artist["biography"],
            $artist["file_name"]
        ))->toHtml();
    }
    $lista_album = [];
    foreach ($albums as $album){
        $lista_album[] = (new album(
            $album["id"],
            $album["name"],
            $album["file_name"]
        ))->toHtml();
    }
    $lista_songs= [];
    foreach ($songs as $song){
        $lista_songs[] = (new song(
            $song["producer"],
            $song["producer_name"],
            $song["name"],
            $song["audio_file_name"],
            $song["graphic_file_name"],
        ))->toHtml();
    }
    $lista_artisti = implode("\n",$lista_artisti);
    $lista_album = implode("\n",$lista_album);
    $lista_songs = implode("\n",$lista_songs);
    $content = str_replace("{{lista-artisti}}", $lista_artisti, $content);
    $content = str_replace("{{lista-album}}", $lista_album, $content);
    $content = str_replace("{{lista-canzoni}}", $lista_songs, $content);
    $layout = str_replace("{{content}}",$content,$layout);
} elseif ($_SESSION["user"]["status"] == "USER") {
    /*TODO: da implementare la vista USER*/
} else {
    /*TODO: da implementare la vista ADMIN*/
}

echo $layout;