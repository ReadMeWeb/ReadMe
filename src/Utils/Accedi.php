<?php

use Pangine\Pangine;
use Pangine\utils\LayoutBuilder;
use Pangine\utils\Validator;
use Pangine\utils\ValidatorMe;
use Utils\Database;

require_once __DIR__ . '/../Utils/Stream.php';
require_once __DIR__ . '/../Utils/Database.php';
require_once __DIR__ . '/../Utils/Accedi.php';
require_once __DIR__ . '/../Pangine/Pangine.php';
require_once __DIR__ . '/../Pangine/utils/LayoutBuilder.php';

function accedi(Database $conn) {
    $profilo = $conn->execute_query('
      SELECT username, status FROM Users WHERE username = ? AND password = ?;
    ', $_POST['nome'], $_POST['password']);
    if (count($profilo) == 1) {
      $_SESSION['user'] = $profilo[0];
      // TODO sarebbe da reindirizzare alla pagina da cui proveniamo
      //      che non necessariamente è la home
      Pangine::redirect('Home');
    }
    Pangine::redirect();
  };
