<?php

use Pangine\Pangine;

require_once "../Pangine/Pangine.php";
include "../Renderers/addSong.php";

/**
 * @var callable $get_select_artist per la pagine della selezione dell'artista della canzone
 * @var callable $get_create_song per la pagina di inserimento delle informazioni della canzone
 * @var callable $post_add_song per la pagina di elaborazione e inserimento delle nuove informazioni della canzone
 */


$app = new Pangine();

$app->GET_read($get_select_artist)
    ->GET_create($get_create_song)
    ->POST_update($post_add_song)
    ->execute();
