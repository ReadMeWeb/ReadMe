<?php

use Pangine\Pangine;

require_once "../Pangine/Pangine.php";
include "../Renderers/account.php";

/**
 * @var callable $get_account per la pagina di visualizzazione delle informazioni dell'account
 * @var callable $get_edit_account per la pagina di modifica delle informazioni dell'account
 * @var callable $post_edit_account per la pagina di elaborazione e modifica delle nuove informazioni dell'account
 */


$app = new Pangine();

$app->GET_read($get_account)
    ->GET_update($get_edit_account)
    ->POST_update($post_edit_account)
    ->execute();