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
            ->replace("title", "Home")
            ->replace("description", "Pagina principale della biblioteca ReadMe")
            ->replace("keywords", "ReadMe, biblioteca, libri, narrativa, prenotazioni")
            ->replace("content", "")
            ->replace("menu", Pangine::navbar_list())
            ->replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Accedi")))
            ->build();
    }
)->execute();