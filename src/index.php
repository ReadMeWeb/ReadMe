<?php
require_once(__DIR__."/Pangine/Pangine.php");

use \Pangine\Pangine;
use \Utils\Database;

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
    ->add_renderer_GET(function(Database $db){
        $db->add_user();
    },"add_user")
->execute();