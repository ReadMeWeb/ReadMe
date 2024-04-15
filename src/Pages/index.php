<?php
require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");
require_once(__DIR__ . "/../Pangine/utils/Validator.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;
use \Pangine\utils\Validator;

(new Pangine())
    ->add_renderer_GET(function () {
        echo (new LayoutBuilder())
            ->tag_lazy_replace("title", "Home")
            ->tag_lazy_replace("description", "Pagina principale della biblioteca ReadMe")
            ->tag_lazy_replace("keywords", "ReadMe, biblioteca, libri, narrativa, prenotazioni")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Accedi")))
            ->tag_istant_replace("content", "")
            ->build();
    })->execute();