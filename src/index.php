<?php
require_once "./Pangine/Pangine.php";

use \Pangine\Pangine;

(new Pangine())->add_renderer_GET(function (){
    echo "GET senza parametri";
})
->add_renderer_GET(function(){
    echo "GET con parametro 'prova'";
},"prova")
->add_renderer_POST(function(){
    echo "POST senza parametri";
})
->add_renderer_POST(function(){
    echo "POST con parametro 'prova'";
},"prova")
->execute();