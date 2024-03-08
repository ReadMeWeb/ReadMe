<?php
set_include_path($_SERVER["DOCUMENT_ROOT"]);
require_once '../components/sessionEstablisher.php'
try_session();
$status = $_SESSION['user']['status'];
if($status = "USER" || $status == "ADMIN"){
    unset($_SESSION['user']);
    session_destroy();
}
header("Location: /index.php");