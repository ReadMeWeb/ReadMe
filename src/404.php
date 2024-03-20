<?php

require_once "./Pangine/Pangine.php";
require_once "./components/navbar.php";

(new \Pangine\Pangine())->GET_read(function (){
    (new \Pangine\PangineAuthenticator())->authenticate(array('UNREGISTERED','USER','ADMIN'));
    echo (new HTMLBuilder('./components/404.html'))->set('menu',navbar())->build();
})->execute();
