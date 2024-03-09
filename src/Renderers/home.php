<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'include/database.php';
require_once 'components/navbar.php';
require_once 'components/breadcrumbs.php';

$get_home = function() {

    $title = 'Orchestra';
    $keywords = implode(', ', array('Orchestra', 'storia della musica classica', 'musica classica', 'player musicale', 'player gratuito'));
    $description = 'Orchestra è un player musicale online gratuito che ti permette di ascoltare tutta la musica classica dal 1800 fino ad oggi';
    $menu = navbar();
    $breadcrumbs = (new BreadcrumbsBuilder())
        ->addBreadcrumb(new BreadcrumbItem("Home", isCurrent: true))
        ->build()
        ->getBreadcrumbsHtml();

    $db = new Database();

    $song_count = 0;
    $artist_count = 0;
    $latest_music = [];
    $album_count = 0;

    if($db->status()) {
        $song_count = $db->song_count();
        $artist_count = $db->artist_count();
        $latest_music = $db->latest_releases(4);
        $album_count = $db->album_count();
    }
    $db->close();

    $song = file_get_contents('../components/songCard.html');
    $place_holders = array('{{card_class}}', '{{img}}', '{{artist}}', '{{song}}', '{{added_date}}');
    $songs = '';

    foreach($latest_music as $music) {
        $values = array('ultime_uscite',  $music['img'], $music['artist'], $music['song'], $music['added_date']);
        $songs .= str_replace($place_holders, $values, $song);
    }

    $content = file_get_contents('../components/index.html');
    $place_holders = array('{{artist_count}}', '{{album_count}}', '{{song_count}}', '{{songs}}');
    $values = array($artist_count, $album_count, $song_count, $songs);
    $content = str_replace($place_holders, $values, $content);

    $place_holders = array('{{title}}', '{{keywords}}', '{{description}}', '{{content}}','{{menu}}','{{breadcrumbs}}');
    $values = array($title, $keywords, $description, $content,$menu,$breadcrumbs);
    $template = file_get_contents('../components/layout.html');
    $template = str_replace($place_holders, $values, $template);

    echo $template;
};
