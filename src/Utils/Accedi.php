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
  $profilo = null;
  (new Validator(''))->add_parameter('nome')->is_string(
    string_parser: function () use ($conn, &$profilo) {
      return count($profilo = $conn->execute_query('
      SELECT username, status, password FROM Users WHERE username = ? AND password = ?;
    ', $_POST['nome'], $_POST['password'])) == 1
        ? ''
        : 'Le credenziali non risultano registrate.';
    }
  )->validate();
  if ($profilo !== null && count($profilo) == 1) {
    $_SESSION['user'] = $profilo[0];
    // TODO sarebbe da reindirizzare alla pagina da cui proveniamo
    //      che non necessariamente è la home
    Pangine::redirect('Home');
  }
  Pangine::redirect();
};
