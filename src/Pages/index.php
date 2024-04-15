<?php
require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");
require_once(__DIR__ . "/../Pangine/utils/Validator.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;
use \Pangine\utils\Validator;

(new Pangine())
    ->add_renderer_GET(function() {
        echo (new LayoutBuilder())
            ->lazy_replace("title", "Home")
            ->lazy_replace("description", "Pagina principale della biblioteca ReadMe")
            ->lazy_replace("keywords", "ReadMe, biblioteca, libri, narrativa, prenotazioni")
            ->lazy_replace("menu", Pangine::navbar_list())
            ->lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Accedi")))
            ->istant_replace("content", "")
            ->build();
    }
)->execute();