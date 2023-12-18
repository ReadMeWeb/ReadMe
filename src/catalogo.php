<?php

require_once "components/navbar.php";
require_once "components/sessionEstablisher.php";
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
    $lista_artisti = "";
    $content = str_replace("{{lista-artisti}}", $lista_artisti, $content);
} elseif ($_SESSION["user"]["status"] == "USER") {

} else {

}

echo $layout;