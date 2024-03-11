<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'include/sessionEstablisher.php';
require_once 'include/include.php';
require_once 'include/pages.php';

try_session();
$status = $_SESSION['user']['status'];
if($status == "USER" || $status == "ADMIN"){
    unset($_SESSION['user']);
    session_destroy();
}

redirect(pages['Home']);
