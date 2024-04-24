<?php
require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;

(new Pangine())
->add_renderer_GET(function () {

    $content = file_get_contents(__DIR__ . "/../templates/chi-siamo_content.html");

    echo (new LayoutBuilder())
        ->tag_lazy_replace("title", "Chi siamo")
        ->tag_lazy_replace("description", "Pagina di informazioni su ReadMe")
        ->tag_lazy_replace("keywords", "ReadMe, Biblioteca Padova, Libri, Letteratura")
        ->tag_lazy_replace("menu", Pangine::navbar_list())
        ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Chi siamo")))
        ->tag_istant_replace("content", $content)
        ->build();
})->execute();