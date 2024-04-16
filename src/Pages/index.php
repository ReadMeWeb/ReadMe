<?php
require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");
require_once(__DIR__ . "/../Utils/Database.php.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;
use \Utils\Database;

(new Pangine())
    ->add_renderer_GET(function (Database $db) {
        $content = file_get_contents(__DIR__ . "/../templates/index_content.html");

        $authors_counter = $db->

        echo (new LayoutBuilder())
            ->tag_lazy_replace("title", "Home")
            ->tag_lazy_replace("description", "Pagina principale della biblioteca ReadMe")
            ->tag_lazy_replace("keywords", "ReadMe, biblioteca, libri, narrativa, prenotazioni")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Accedi")))
            ->tag_lazy_replace("authors-counter","")
            ->tag_lazy_replace("books-counter","")
            ->tag_lazy_replace("loans-counter","")
            ->tag_istant_replace("content", $content)
            ->build();
    },needs_database: true)->execute();