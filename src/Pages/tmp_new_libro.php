<?php

require_once __DIR__ . "/../Utils/Database.php";
require_once __DIR__ . "/../Pangine/Pangine.php";

use Utils\Database;
use Pangine\utils\Validator;

$renderer_get_new = function (Database $db) {
    echo "Hello, world!";
};

$validator_get_new = new Validator("/Pages/404.php");
