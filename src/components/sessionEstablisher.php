<?php
function try_session(): void{
    if(!isset($_SESSION)){
        session_start();
    }
}