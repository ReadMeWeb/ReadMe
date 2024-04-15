<?php
require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;

(new Pangine())
    ->add_renderer_GET(function () {
        $content = file_get_contents(__DIR__ . "/../templates/404_content.html");
        echo (new LayoutBuilder())
            ->tag_lazy_replace("title", "404")
            ->tag_lazy_replace("description", "La pagina cercata non è stata trovata")
            ->tag_lazy_replace("keywords", "ReadMe, biblioteca, 404, errore, pagina mancante, pagina non trovata")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("404")))
            ->tag_istant_replace("content", $content)
            ->build();
    }
    )->execute();
