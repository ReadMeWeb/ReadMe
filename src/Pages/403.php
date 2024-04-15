<?php

require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;

(new Pangine())
    ->add_renderer_GET(function () {
        $content = file_get_contents(__DIR__ . "/../templates/403_content.html");
        echo (new LayoutBuilder())
            ->tag_lazy_replace("title", "403")
            ->tag_lazy_replace("description", "Permessi insufficienti per poter accedere alla pagina")
            ->tag_lazy_replace("keywords", "ReadMe, biblioteca, 403, errore, permessi insufficienti")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("403")))
            ->tag_istant_replace("content", $content)
            ->build();
    }
    )->execute();
