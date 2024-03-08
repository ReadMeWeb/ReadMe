<?php
set_include_path($_SERVER["DOCUMENT_ROOT"]);
require_once 'components/navbar.php';
require_once 'components/breadcrumbs.php';

$get_unallowed = function (){
    $layout = file_get_contents("../components/layout.html");
    $title = "Permessi insufficienti";
    $description = "Non possiedi i permessi sufficienti al fine di visualizzare la pagina richiesta in precedenza.";
    $keywords = implode(", ", array("Orchestra", "Permessi insufficienti", "Musica"));
    $navbar = navbar();
    $breadcrumbs = (new BreadcrumbsBuilder())
        ->addBreadcrumb(new BreadcrumbItem("Home"))
        ->addBreadcrumb(new BreadcrumbItem("Permessi insufficienti", true))
        ->build()
        ->getBreadcrumbsHtml();
    $content = file_get_contents("../components/unallowed.html");
    $layout = str_replace(
        array("{{title}}",
            "{{menu}}",
            "{{description}}",
            "{{keywords}}",
            "{{breadcrumbs}}",
            "{{content}}",
            )
        ,array(
        $title,
        $navbar,
        $description,
        $keywords,
        $breadcrumbs,
        $content),
        $layout);
    echo $layout;
};
