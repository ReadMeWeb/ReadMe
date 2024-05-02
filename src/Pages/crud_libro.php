<?php
require_once __DIR__ . "/../Pangine/Pangine.php";
require_once __DIR__ . "/../Pangine/utils/LayoutBuilder.php";
require_once __DIR__ . "/../Utils/Database.php";
require_once __DIR__ . "/../Pages/tmp_new_libro.php";
require_once __DIR__ . "/../Pages/tmp_edit_libro.php";

use Pangine\Pangine;
use Pangine\utils\LayoutBuilder;

(new Pangine())
->add_renderer_GET(
    function(){

        $content = file_get_contents(__DIR__ . "/../templates/libro_new.html");

        echo (new LayoutBuilder("priv"))
            ->tag_istant_replace("content", $content)
            ->tag_lazy_replace("title", "Nuovo Libro")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace(
                "breadcrumbs",
                Pangine::breadcrumbs_generator([
                    "Home",
                    "Nuovo Libro",
                ])
            )
            ->build();
    },
    //needs_database: true,
    validator: $validator_get_new
)
->add_renderer_GET(
    function(){

        $content = file_get_contents(__DIR__ . "/../templates/libro_edit.html");

        echo (new LayoutBuilder("priv"))
            ->tag_istant_replace("content", $content)
            ->tag_lazy_replace("title", "Modifica Libro")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace(
                "breadcrumbs",
                Pangine::breadcrumbs_generator([
                    "Home",
                    "Catalogo",
                    "Modifica",
                ])
            )
            ->build();
    },
    "modifica",
    //needs_database: true,
    validator: $validator_get_edit
)
->execute();
