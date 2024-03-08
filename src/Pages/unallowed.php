<?php
set_include_path($_SERVER["DOCUMENT_ROOT"]);
require_once 'Pangine/Pangine.php';
require_once "../Renderers/unallowed.php";
/**
 * @var callable $get_unallowed per la pagina da visualizzare se non si hanno i permessi giusti
 */


$app = new \Pangine\Pangine();

$app->GET_read($get_unallowed)
    ->execute();
