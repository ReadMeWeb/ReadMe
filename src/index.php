<?php
require_once 'data/database.php';

// generazione contenuto statico della pagina
$keywords = implode(', ', array('Orchestra', 'storia della musica classica', 'musica classica', 'player musicale', 'player gratuito'));
$title = 'Orchestra';
$description = 'Orchestra è un player musicale online gratuito che ti permette di ascoltare tutta la musica classica dal 1800 fino ad oggi';

// ottenimento contenuto dinamico della pagina pagina dal database
$db = new Database();

$song_count = 0;
$artist_count = 0;
$latest_music = [];

// TODO: implementare la tabella Album
$album_count = 0;


if($db->status()) {
    $song_count = $db->song_count();
    $artist_count = $db->artist_count();
    $latest_music = $db->latest_releases(3);
}
$db->close();


// generazione delle cards delle ultime canzoni uscite
$song = file_get_contents('components/songCard.html');
$place_holders = array('{{card_class}}', '{{artist}}', '{{song}}', '{{added_date}}');
$songs = '';
foreach($latest_music as $music) {
    $values = array('ultime_uscite', $music['artist'], $music['song'], $music['added_date']);
    $songs .= str_replace($place_holders, $values, $song);
}


// generazione contenuto principale della pagina
$content = file_get_contents('components/index.html');
$place_holders = array('{{artist_count}}', '{{album_count}}', '{{song_count}}', '{{songs}}');
$values = array($artist_count, $album_count, $song_count, $songs);
$content = str_replace($place_holders, $values, $content);

// generazione pagina di risposta
$place_holders = array('{{title}}', '{{keywords}}', '{{description}}', '{{content}}');
$values = array($title, $keywords, $description, $content);
$template = file_get_contents('components/layout.html');
$template = str_replace($place_holders, $values, $template);

echo $template;
