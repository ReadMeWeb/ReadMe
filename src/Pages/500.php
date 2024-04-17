<?php
 
 require_once(__DIR__ . "/../Pangine/Pangine.php");
 require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");
 
 use \Pangine\Pangine;
 use \Pangine\utils\LayoutBuilder;
 
 (new Pangine())
     ->add_renderer_GET(function () {
         $content = file_get_contents(__DIR__ . "/../templates/500_content.html");
         $layout_builder = (new LayoutBuilder())
             ->tag_lazy_replace("title", "500")
             ->tag_lazy_replace("description", "Errore dei server interni")
             ->tag_lazy_replace("keywords", "ReadMe, biblioteca, 500, errore, malfunzionamento server")
             ->tag_lazy_replace("menu", Pangine::navbar_list())
             ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("500")))
             ->tag_istant_replace("content", $content);
         if(isset($_SESSION["error500message"])){
             $layout_builder->plain_lazy_replace("<p>Qualcosa è andato storto nel nostro <span lang=\"en\">backend</span>. Riprova più tardi.</p>", "<p>".$_SESSION["error500message"]."</p>");
             unset($_SESSION["error500message"]);
         }
         echo $layout_builder->build();
     }
     )->execute();
 