<?php
require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;

# TODO: QUESTA PAGINA È DA ELIMINARE O MODIFICARE (è stata creata solamente per sperimentare con le features di validazione)

(new Pangine())
    ->add_renderer_GET(function() {
        echo (new LayoutBuilder())
            ->replace("title", "Home")
            ->replace("description", "Pagina principale della biblioteca ReadMe")
            ->replace("keywords", "ReadMe, biblioteca, libri, narrativa, prenotazioni")
            ->replace("content", "")
            ->replace("menu", Pangine::navbar_list())
            ->replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Admin")))
            ->build();
    })
    ->execute();
