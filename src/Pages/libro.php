<?php
require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");
require_once(__DIR__ . "/../Utils/Database.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;
use \Utils\Database;

(new Pangine())
    ->add_renderer_GET(function (Database $db) {
        $content = file_get_contents(__DIR__ . "/../templates/libro_content.html");

        // Assuming you have a book id to fetch the book's data
        $book_id = $_GET['id'];
        $book_data = $db->execute_query("SELECT * FROM Books B INNER JOIN Authors A ON A.id = B.author_id WHERE A.id= ?", $book_id)[0];

        echo (new LayoutBuilder())
            ->tag_lazy_replace("title", $book_data["title"])
            ->tag_lazy_replace("description", $book_data["description"])
            ->tag_lazy_replace("keywords", "ReadMe, biblioteca, libri, narrativa, prenotazioni")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Catalogo", "Libro")))
            ->tag_lazy_replace("book-title", $book_data["title"])
            ->tag_lazy_replace("book-author", $book_data["name_surname"])
            ->tag_lazy_replace("book-description", $book_data["description"])
            ->tag_lazy_replace("cover-image-src", $book_data["cover_file_name"])
            ->tag_lazy_replace("cover-image-src", $book_data["cover_file_name"])
            ->tag_lazy_replace("copies-remaining", $book_data["number_of_copies"])
            ->tag_istant_replace("content", $content)
            ->build();
    },needs_database: true)->execute();