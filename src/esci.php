<?php
set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'include/sessionEstablisher.php';

$cleanup_results = [
  try_session(),
  session_unset(),
  session_destroy(),
  // session_write_close(),
  setcookie(session_name(), '', 0, '/'),
  // session_regenerate_id(true),
];

$bool_compare = function ($a, $b) {
  return $a && $b;
};

if (array_reduce($cleanup_results, $bool_compare, true)) {
  header("Location: /index.php");
} else {
  // TODO sostituire con il reindirazzamento a una pagina di errore
  echo "<p>Errore durante il sign out</p>";
  echo "<p>dump di quale operazione ha fallito</p>";
  echo "<textarea style='width:90%; height:90%;' readonly>";
  var_dump($cleanup_results);
  echo "</textarea>";
}
