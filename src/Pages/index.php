<?php
 require_once(__DIR__ . "/../Pangine/Pangine.php");
 require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");
 require_once(__DIR__ . "/../Utils/Database.php");
 
 use \Pangine\Pangine;
 use \Pangine\utils\LayoutBuilder;
 use \Utils\Database;
 
 (new Pangine())
     ->add_renderer_GET(function (Database $db) {
         $content = file_get_contents(__DIR__ . "/../templates/index_content.html");
 
         $authors_counter = $db->execute_query("SELECT COUNT(*) as count FROM Authors")[0]["count"] | 0;
         $books_counter = $db->execute_query("SELECT SUM(number_of_copies) as sum FROM Books")[0]["sum"] | 0;
         $loans_counter = $db->execute_query("SELECT COUNT(*) as count FROM Books")[0]["count"] | 0;
 
         echo (new LayoutBuilder())
             ->tag_lazy_replace("title", "Home")
             ->tag_lazy_replace("description", "Pagina principale della biblioteca ReadMe")
             ->tag_lazy_replace("keywords", "ReadMe, biblioteca, libri, narrativa, prenotazioni")
             ->tag_lazy_replace("menu", Pangine::navbar_list())
             ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Accedi")))
             ->tag_lazy_replace("authors-counter",strval($authors_counter))
             ->tag_lazy_replace("books-counter",strval($books_counter))
             ->tag_lazy_replace("loans-counter",strval($loans_counter))
             ->tag_istant_replace("content", $content)
             ->build();
     },needs_database: true)->execute();