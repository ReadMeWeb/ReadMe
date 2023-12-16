<?php
function try_session(): bool{
    if(!isset($_SESSION)){
        return session_start();
    }
    return true;
}