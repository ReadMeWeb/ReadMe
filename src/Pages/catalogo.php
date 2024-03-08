<?php
use Pangine\Pangine;

set_include_path($_SERVER["DOCUMENT_ROOT"]);
require_once 'Pangine/Pangine.php';
require_once "Renderers/catalogo.php";

/**
 * @var callable $get_catalogo per la pagina di visualizzazione del catalogo
 */


$app = new Pangine();

$app->GET_read($get_catalogo)
    ->execute();
