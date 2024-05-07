<?php
require_once __DIR__ . "/../Pangine/Pangine.php";
require_once __DIR__ . "/../Pangine/utils/LayoutBuilder.php";
require_once __DIR__ . "/../Utils/Database.php";

use Pangine\Pangine;
use Pangine\utils\LayoutBuilder;
use Utils\Database;
use Pangine\utils\Validator;

(new Pangine())
->add_renderer_GET(
    function(Database $db){

        $authors_query =
            "SELECT * FROM Authors";
        $authors = $db->execute_query($authors_query);
        $authors_options = "";
        foreach ($authors as $author) {
            $authors_options .= "<option value='{$author["id"]}'>{$author["name_surname"]}</option>";
        }

        $content = file_get_contents(__DIR__ . "/../templates/libro_edit.html");

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
            ->plain_instant_replace(
                "<main id=\"content\">",
                "<main class=\"book-page image-hidden\">"
            )
            ->tag_lazy_replace("book_title", "")
            ->tag_lazy_replace("description", "")
            ->tag_lazy_replace("current_cover", "")
            ->tag_lazy_replace("current_author_option", "")
            ->tag_lazy_replace("authors_options", $authors_options)
            ->tag_lazy_replace("image-hidden", "image-hidden")
            ->tag_lazy_replace("submit-value", "Aggiungi")
            ->tag_lazy_replace("submit-name", "create")
            ->build();
    }, needs_database: true
)
->add_renderer_GET(
    function(Database $db){

    $book_data = [];
    (new Validator("/marango/Pages/404.php"))
        ->add_parameter("id")
        ->is_numeric(
            value_parser: function (int $book_id) use ($db, &$book_data) {
                $book_query =
                    "SELECT B.id, B.title, B.description, B.cover_file_name, B.number_of_copies, A.name_surname, B.author_id FROM Books B INNER JOIN Authors A ON A.id = B.author_id WHERE B.id= ?";
                $book_data = $db->execute_query($book_query, $book_id);
                if (count($book_data) == 0) {
                    return "Il libro richiesto non esiste.";
                }
                return "";
            }
        )
        ->validate();

        $book_data = $book_data[0];

        $authors_query =
            "SELECT * FROM Authors WHERE name_surname != ?";
        $authors = $db->execute_query($authors_query, $book_data["name_surname"]);
        $current_author_option = "<option disabled selected value='{$book_data["author_id"]}'>{$book_data["name_surname"]}</option>";
        $authors_options = "";
        foreach ($authors as $author) {
            $authors_options .= "<option value='{$author["id"]}'>{$author["name_surname"]}</option>";
        }

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
            ->plain_instant_replace(
                "<main id=\"content\">",
                "<main class=\"book-page\">"
            )
            ->tag_lazy_replace("book_title", $book_data["title"])
            ->tag_lazy_replace("description", $book_data["description"])
            ->tag_lazy_replace("current_cover", $book_data["cover_file_name"])
            ->tag_lazy_replace("current_author_option", $current_author_option)
            ->tag_lazy_replace("authors_options", $authors_options)
            ->tag_lazy_replace("image-hidden", "")
            ->tag_lazy_replace("submit-value", "Aggiorna")
            ->tag_lazy_replace("submit-name", "update")
            ->build();
    },
    "modifica",
    needs_database: true
)
->add_renderer_POST(function(){
    echo "Hello Update!";
}, "update")
->add_renderer_POST(function(){
    echo "Hello Create!";
}, "create")
->execute();
