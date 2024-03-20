<?php
function try_session(): bool{
    if(!isset($_SESSION)){
        $session_return = session_start();
        if(!isset($_SESSION["user"])){
            $_SESSION["user"]["status"] = "UNREGISTERED";
        }
        return $session_return;
    }
    return true;
}