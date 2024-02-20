<?php

use Pangine\Pangine;

require_once "../Pangine/Pangine.php";
include "../Renderers/artista.php";

/**
 * @var callable $get_artist per la pagina di visualizzazione delle informazioni dell'artista
 * @var callable $get_edit_artist per la pagina di modifica delle informazioni dell'artista
 * @var callable $get_add_artist per la pagina di inserimento di un nuovo atista
 * @var callable $post_edit_artist per la pagina di elaborazione e modifica delle nuove informazioni dell'artista
 * @var callable $post_add_artist per la pagina di elaborazione dell'inserimento di un nuovo artista
 * @var callable $get_delete_artist per la rimozione di un artista
 */


$app = new Pangine();

$app->GET_read($get_artist)
    ->GET_update($get_edit_artist)
    ->GET_create($get_create_artist)
    ->POST_update($post_edit_artist)
    ->POST_create($post_add_artist)
    ->GET_delete($get_delete_artist)
    ->execute();