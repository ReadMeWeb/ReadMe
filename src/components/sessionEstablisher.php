<?php
function try_session(){
    if(!isset($_SESSION)){
        session_start();
    }
}