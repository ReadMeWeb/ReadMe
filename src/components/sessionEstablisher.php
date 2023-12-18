<?php
function try_session(): bool{
    if(!isset($_SESSION)){
        if(!isset($_SESSION["user"])){
            $_SESSION["user"]["status"] = "UNREGISTERED";
        }
        return session_start();
    }
    return true;
}