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
    $regex = implode('.*?', array_map(fn ($c) => preg_quote($c), str_split($sequence)));
    $regex = '/'.$regex.'/i';
    return preg_match($regex, $haystack) === 1;
}

$get_catalogo = function () {
    try_session();

    $layout = ((is_admin_signed_in() || is_user_signed_in())
        ? (new HTMLBuilder("../components/layoutLogged.html"))
        : ((new HTMLBuilder("../components/layout.html"))
        ->set('description', 'Pagina di catalogo musicale di musica classica di Orchestra')
        ->set('keywords', 'Orchestra, Catalogo musicale, Musica, Album, Artisti, Canzoni')))
    ->set('title', 'Catalogo')
    ->set('menu', navbar())
    ->set('breadcrumbs',arraybreadcrumb(['Home','Catalogo']));

// CREAZIONE CONTENT

    $content = new HTMLBuilder("../components/catalogo.html");
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
        $query = $_GET["searched"];
        $artists_tmp = array_filter($artists, fn ($a) => isSequencePresent($a['name'], $query));
        $albums_tmp = array_filter($albums, fn ($a) => isSequencePresent($a['name'], $query));
        $songs_tmp = array_filter($songs, fn ($a) => isSequencePresent($a['name'], $query));
        // foreach ($artists as $artist){
        //     if(isSequencePresent($artist["name"],$_GET["searched"])){
        //         $artists_tmp[] = $artist;
        //     }
        // }
        //foreach ($albums as $album){
        //    if(isSequencePresent($album["name"],$_GET["searched"])){
        //        $albums_tmp[] = $album;
        //    }
        //}
        //foreach ($songs as $song){
        //    if(isSequencePresent($song["name"],$_GET["searched"])){
        //        $songs_tmp[] = $song;
        //    }
        //}
        $artists = $artists_tmp;
        $albums = $albums_tmp;
        $songs = $songs_tmp;
        $content->set("searched", $_GET["searched"]);
    }
    $content->set("searched", "");

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
        $content->set('lista-artisti', "<p>Nessun artista trovato.</p>");
    }else{
        $content->set('lista-artisti', $lista_artists);
    }
    if(count($albums) == 0){
        $content->set('lista-album', "<p>Nessun album trovato.</p>");
    }else{
        $content->set('lista-album', $lista_album);
    }
    if(count($songs) == 0){
        $content->set('lista-canzoni', "<p>Nessuna canzone trovata.</p>");
    }else{
        $content->set('lista-canzoni', $lista_songs);
    }
    $content->set('page-form', pages['Catalogo']);

    $layout->set('content',$content->build());

    echo $layout->build();

};
