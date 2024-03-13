<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'components/navbar.php';
require_once 'include/sessionEstablisher.php';
require_once 'components/artist.php';
require_once 'components/album.php';
require_once 'components/song.php';
require_once 'components/breadcrumbs.php';
require_once 'include/database.php';

function isSequencePresent(string $haystack, string $sequence) {
    $regex = implode('.*?', array_map(fn ($c) => '['.preg_quote($c).']',str_split($sequence)));
    $regex = '/'.$regex.'/i';
    return preg_match($regex, $haystack) === 1;
}

$get_catalogo = function () {
    try_session();

    $title = "Catalogo";
    $description = "Pagina di catalogo musicale di musica classica di Orchestra";
    $keywords = implode(", ", array("Orchestra", "Catalogo musicale", "Musica", "Album", "Artisti", "Canzoni"));

    $layout = "";
    $content = file_get_contents("../components/catalogo.html");

// CREAZIONE CONTENT

    if ($_SESSION["user"]["status"] == "UNREGISTERED") {
        $layout = file_get_contents("../components/layout.html");
        $layout = str_replace("{{description}}",$description,$layout);
        $layout = str_replace("{{keywords}}",$keywords,$layout);
    } else {
        $layout = file_get_contents("../components/layoutLogged.html");
    }

    $layout = str_replace("{{menu}}", navbar(), $layout);
    $layout = str_replace("{{breadcrumbs}}",arraybreadcrumb(['Home','Catalogo']),$layout);
    $layout = str_replace("{{title}}",$title,$layout);

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

    if(isset($_GET["searched"])){
        $artists_tmp = [];
        $albums_tmp = [];
        $songs_tmp = [];
        foreach ($artists as $artist){
            if(isSequencePresent($artist["name"],$_GET["searched"])){
                $artists_tmp[] = $artist;
            }
        }
        foreach ($albums as $album){
            if(isSequencePresent($album["name"],$_GET["searched"])){
                $albums_tmp[] = $album;
            }
        }
        foreach ($songs as $song){
            if(isSequencePresent($song["name"],$_GET["searched"])){
                $songs_tmp[] = $song;
            }
        }
        $artists = $artists_tmp;
        $albums = $albums_tmp;
        $songs = $songs_tmp;
        $content = str_replace("{{searched}}", $_GET["searched"], $content);
    }
    $content = str_replace("{{searched}}", "", $content);

    $lista_artists = [];
    foreach ($artists as $artist){
        $lista_artists[] = (new artist(
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
            $album["id"]
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
    $lista_artists = implode("\n",$lista_artists);
    $lista_album = implode("\n",$lista_album);
    $lista_songs = implode("\n",$lista_songs);
    if(count($artists) == 0){
        $content = str_replace("{{lista-artisti}}", "<p>Nessun artista trovato.</p>", $content);
    }else{
        $content = str_replace("{{lista-artisti}}", $lista_artists, $content);
    }
    if(count($albums) == 0){
        $content = str_replace("{{lista-album}}", "<p>Nessun album trovato.</p>", $content);
    }else{
        $content = str_replace("{{lista-album}}", $lista_album, $content);
    }
    if(count($songs) == 0){
        $content = str_replace("{{lista-canzoni}}", "<p>Nessuna canzone trovata.</p>", $content);
    }else{
        $content = str_replace("{{lista-canzoni}}", $lista_songs, $content);
    }
    $content = str_replace("{{page-form}}", pages['Catalogo'], $content);
    $layout = str_replace("{{content}}",$content,$layout);

    echo $layout;

};
