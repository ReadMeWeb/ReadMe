<?php
require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;

# TODO: QUESTA PAGINA È DA ELIMINARE O MODIFICARE (è stata creata solamente per sperimentare con le features di validazione)

(new Pangine())
    ->add_renderer_GET(function() {
        echo (new LayoutBuilder())
            ->tag_lazy_replace("title", "Home")
            ->tag_lazy_replace("description", "Pagina principale della biblioteca ReadMe")
            ->tag_lazy_replace("keywords", "ReadMe, biblioteca, libri, narrativa, prenotazioni")
            ->tag_lazy_replace("smtg-value","smtg-value")
            ->tag_lazy_replace("smtg-message","smtg-message")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Admin")))
            ->tag_istant_replace("content", "{{smtg-value}} --- {{smtg-message}}")
            ->build();
    })
    ->execute();
