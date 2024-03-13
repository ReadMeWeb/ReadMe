<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'include/database.php';
require_once 'components/navbar.php';
require_once 'components/breadcrumbs.php';
require_once 'include/utils.php';
require_once 'include/HTMLBuilder.php';

// pezzo copia incollato da refactor-home
$db = new Database();

$song_count = 0;
$artist_count = 0;
$latest_music = [];
$album_count = 0;

if ($db->status()) {
  $song_count = $db->song_count();
  $artist_count = $db->artist_count();
  $latest_music = $db->latest_releases(4);
  $album_count = $db->album_count();
}
$db->close();

$template = (new HTMLBuilder('components/layout.html'))
  ->set('title', 'Orchestra')
  ->set('keywords', 'Orchestra, storia della musica classica, musica classica, player musicale, player gratuito')
  ->set('description', 'Orchestra è un player musicale online gratuito che ti permette di ascoltare tutta la musica classica dal 1800 fino ad oggi')
  ->set('menu', navbar())
  ->set('breadcrumbs', arraybreadcrumb(['Home']))
  ->set('content', (new HTMLBuilder('components/index.html'))
    ->set('artist_count', $artist_count)
    ->set('album_count', $album_count)
    ->set('song_count', $song_count)
    ->set('songs', implode('', array_map(function ($music) {
      return (new HTMLBuilder('components/songCard.html'))
        ->set('card_class', 'ultime_uscite')
        ->set('img', $music['img'])
        ->set('artist', $music['artist'])
        ->set('song', $music['song'])
        ->set('added_date', $music['added_date'])
        ->build();
    }, $latest_music)))
    ->build())
  ->build();

// soluzione sloppy per il fix dei link
$link = [
  '<link rel="stylesheet" href="../styles/style.css" media="screen" />',
  '<link rel="stylesheet" href="../styles/print.css" media="print" />',
  '<link rel="shortcut icon" type="images/png" href="../assets/images/favicon.ico" />',
  '<a href="../index.php" aria-label="Ritorna alla pagina home"><h1><span>Orchestra</span></h1></a>'
];
$linkmeglio = [
  '<link rel="stylesheet" href="./styles/style.css" media="screen" />',
  '<link rel="stylesheet" href="./styles/print.css" media="print" />',
  '<link rel="shortcut icon" type="images/png" href="./assets/images/favicon.ico" />',
  '<h1><span>Orchestra</span>'
];

$template = str_replace($link, $linkmeglio, $template);

echo $template;
