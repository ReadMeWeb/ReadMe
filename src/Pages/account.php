<?php

use Pangine\Pangine;

require_once "../Pangine/Pangine.php";
include "../Renderers/account.php";

/**
 * @var callable $get_account per la pagina di visualizzazione delle informazioni dell'account
 * @var callable $edit_account per la pagina di visualizzazione delle informazioni dell'account
 */


$app = new Pangine();

$app->GET_read($get_account)
    ->GET_update($edit_account)
    ->execute();
