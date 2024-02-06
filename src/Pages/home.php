<?php

require_once "../Pangine/Pangine.php";
include "../Renderers/home.php";
/**
 * @var callable $get_home per il read
 * @var callable $get_create_home per il create
 * @var callable $get_delete_home per la deletion
 * @var callable $get_update_home per l'updating
 */


$app = new \Pangine\Pangine();

$app->GET_read($get_home)
    ->GET_create($get_create_home)
    ->GET_delete($get_delete_home)
    ->GET_update($get_update_home)
    ->execute();