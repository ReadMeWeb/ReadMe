<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'components/navbar.php';
require_once 'include/sessionEstablisher.php';
require_once 'components/artist.php';
require_once 'components/album.php';
require_once 'components/song.php';
require_once 'components/breadcrumbs.php';
require_once 'include/database.php';
require_once 'include/utils.php';

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

    [ $artists , $albums, $songs ] = dbcall(fn ($db) => [ 
      $db->fetch_artist_info(),
      $db->fetch_albums_info(),
      $db->fetch_songs_info(),
    ]);

    if($query = extract_from_array_else("searched", $_GET, false)){
        $artists = array_filter($artists, fn ($a) => isSequencePresent($a['name'], $query));
        $albums = array_filter($albums, fn ($a) => isSequencePresent($a['name'], $query));
        $songs = array_filter($songs, fn ($a) => isSequencePresent($a['name'], $query));
        $content->set("searched", $query);
    }
    $content->set("searched", "");

    $lista_artists = [];
    foreach ($artists as $artist){
        $lista_artists[] = (new artist(
            $artist["id"],
            $artist["name"],
            $artist["biography"],
            $artist["id"]
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
            $song["id"],
            $song["producer"],
            $song["producer_name"],
            $song["name"],
            $song["audio_file_name"],
            $song["graphic_file_name"],
        ))->toHtml();
    }

    $implode_else = fn ($list, $else) => count($list) > 0 ? implode("\n", $list) : $else ;

    echo $layout->set('content',$content
      ->set('lista-artisti',  $implode_else($lista_artists, "<p>Nessun artista trovato.</p>"))
      ->set('lista-album',    $implode_else($lista_album, "<p>Nessun album trovato.</p>"))
      ->set('lista-canzoni',  $implode_else($lista_songs, "<p>Nessuna canzone trovata.</p>"))
      ->set('page-form', pages['Catalogo'])
    ->build())
    ->build();

};
