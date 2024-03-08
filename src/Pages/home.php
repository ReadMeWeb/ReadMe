<?php
use Pangine\Pangine;

set_include_path($_SERVER["DOCUMENT_ROOT"]);
require_once '../Pangine/Pangine.php';
include "../Renderers/home.php";

/**
 * @var callable $get_home per la pagina di home
 */


$app = new Pangine();

$app->GET_read($get_home)
    ->execute();