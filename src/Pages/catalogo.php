<?php
use Pangine\Pangine;

require_once '../Pangine/Pangine.php'
include "../Renderers/catalogo.php";

/**
 * @var callable $get_catalogo per la pagina di visualizzazione del catalogo
 */


$app = new Pangine();

$app->GET_read($get_catalogo)
    ->execute();
