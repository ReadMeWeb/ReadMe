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

        $content = file_get_contents(__DIR__ . "/../templates/libro_edit_content.html");

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
            ->tag_lazy_replace("no_copies", "1")
            ->tag_lazy_replace("authors_options", $authors_options)
            ->tag_lazy_replace("image-hidden", "image-hidden")
            ->tag_lazy_replace("submit-value", "Aggiungi")
            ->tag_lazy_replace("submit-name", "create")
            ->tag_lazy_replace("book_id_field", "")
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
        $current_author_option = "<option selected value='{$book_data["author_id"]}'>{$book_data["name_surname"]}</option>";
        $authors_options = "";
        foreach ($authors as $author) {
            $authors_options .= "<option value='{$author["id"]}'>{$author["name_surname"]}</option>";
        }

        $content = file_get_contents(__DIR__ . "/../templates/libro_edit_content.html");

        echo (new LayoutBuilder("priv"))
            ->tag_istant_replace("content", $content)
            ->tag_lazy_replace("title", "Modifica Libro")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace(
                "breadcrumbs",
                Pangine::breadcrumbs_generator([
                    "Home",
                    "Catalogo",
                    "Libro (Modifica)",
                ])
            )
            ->plain_instant_replace(
                "<main id=\"content\">",
                "<main class=\"book-page\">"
            )
            ->tag_lazy_replace("book_title", $book_data["title"])
            ->tag_lazy_replace("description", $book_data["description"])
            ->tag_lazy_replace("current_cover", $book_data["cover_file_name"])
            ->tag_lazy_replace("no_copies", $book_data["number_of_copies"])
            ->tag_lazy_replace("current_author_option", $current_author_option)
            ->tag_lazy_replace("authors_options", $authors_options)
            ->tag_lazy_replace("image-hidden", "")
            ->tag_lazy_replace("submit-value", "Aggiorna")
            ->tag_lazy_replace("submit-name", "update")
            ->tag_lazy_replace("book_id_field", "<input type='hidden' name='book_id' value='{$book_data["id"]}'>")
            ->build();
    },
    "modifica",
    needs_database: true
)
->add_renderer_POST(function(Database $db){
    (new Validator("/marango/Pages/500.php"))
        ->add_parameter("title")
        ->is_string()
        ->add_parameter("description")
        ->is_string()
        ->add_parameter("book_id")
        ->is_numeric(value_parser:function(int $book_id) use ($db) {
            $book_query =
                "SELECT * FROM Books WHERE id = ?";
            $book_data = $db->execute_query($book_query, $book_id);
            return count($book_data) == 0 ? "Il libro selezionato non esiste." : "";
        })
        ->add_parameter("no_copies")
        ->is_numeric(min_val: 1)
        ->add_parameter("cover")
        ->is_file([
            "image/jpeg",
            "image/jpg",
            "image/png",
        ])
        ->add_parameter("author")
        ->is_string(string_parser: function(string $author_id) use ($db) {
            $author_query =
                "SELECT name_surname FROM Authors WHERE id = ?";
            $author_data = $db->execute_query($author_query, $author_id);
            return count($author_data) == 0 ? "L'autore o l'autrice selezionato/a non esiste." : "";
        }
    );

    if($_FILES["cover"]["tmp_name"] != ""){
        $new_name = $_POST["book_id"] .".". pathinfo($_FILES["cover"]["name"], PATHINFO_EXTENSION);
        $saved = move_uploaded_file($_FILES["cover"]["tmp_name"], "../assets/book_covers/" . $new_name);
        if(!$saved){
            header("Location: /marango/Pages/500.php");
            exit();
        }
    }

    $result = $db->execute_query(
        "UPDATE Books SET title = ?, description = ?, author_id = ?, number_of_copies = ? WHERE id = ?",
        $_POST["title"],
        $_POST["description"],
        $_POST["author"],
        $_POST["no_copies"],
        $_POST["book_id"]
    );
    if(!$result){
        header("Location: /marango/Pages/500.php");
        exit();
    }

    header("Location: /marango/Pages/libro.php?id={$_POST["book_id"]}");
    exit();
}, "update", needs_database: true)
->add_renderer_POST(
    function(Database $db){

        (new Validator("/marango/Pages/500.php"))
            ->add_parameter("title")
            ->is_string()
            ->add_parameter("description")
            ->is_string()
            ->add_parameter("no_copies")
            ->is_numeric(min_val: 1)
            ->add_parameter("cover")
            ->is_file([
                "image/jpeg",
                "image/jpg",
                "image/png",
            ])
            ->add_parameter("author")
            ->is_string(string_parser: function(string $author_id) use ($db) {
                $author_query =
                    "SELECT name_surname FROM Authors WHERE id = ?";
                $author_data = $db->execute_query($author_query, $author_id);
                return count($author_data) == 0 ? "L'autore o l'autrice selezionato/a non esiste." : "";
            }
        );

        $db->get_connection()->begin_transaction();
        $tmp_name = "tmp.jpeg";
        $stmt = $db->get_connection()->prepare("INSERT INTO Books (title, description, cover_file_name, author_id, number_of_copies) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssii', $_POST['title'], $_POST['description'], $tmp_name, $_POST['author'], $_POST['no_copies']);
        if(!$stmt->execute()){
            header("Location: /marango/Pages/500.php");
            exit();
        }
        $last_id = $db->get_connection()->insert_id;

        $new_name = $last_id .".". pathinfo($_FILES["cover"]["name"], PATHINFO_EXTENSION);
        $saved = move_uploaded_file($_FILES["cover"]["tmp_name"], "../assets/book_covers/" . $new_name);
        if(!$saved){
            $db->get_connection()->rollback();
            header("Location: /marango/Pages/500.php");
            exit();
        }

        $stmt = $db->get_connection()->prepare("UPDATE Books SET cover_file_name = ? WHERE id = ?");
        $stmt->bind_param('si', $new_name, $last_id);
        if(!$stmt->execute()){
            $db->get_connection()->rollback();
            header("Location: /marango/Pages/500.php");
            exit();
        }

        $db->get_connection()->commit();

        header("Location: /marango/Pages/libro.php?id=$last_id");
        exit();
    },
    "create",
    needs_database: true
)
->add_renderer_GET(function(Database $db){
    $book_query =
        "SELECT * FROM Books WHERE id = ?";
    $book_data = $db->execute_query($book_query, $_GET['id']);
    if(count($book_data) != 1){
        header("Location: /marango/Pages/500.php");
        exit();
    }
    $book = $book_data[0];

    $content = file_get_contents(__DIR__ . "/../templates/libro_delete_content.html");

    echo (new LayoutBuilder("priv"))
        ->tag_istant_replace("content", $content)
        ->tag_lazy_replace("title", "Elimina Libro")
        ->tag_lazy_replace("menu", Pangine::navbar_list())
        ->tag_lazy_replace(
            "breadcrumbs",
            Pangine::breadcrumbs_generator([
                "Home",
                "Catalogo",
                "Elimina",
            ])
        )
        ->plain_instant_replace(
            "<main id=\"content\">",
            "<main id=\"book-page-delete\" class=\"book-page\">"
        )
        ->tag_lazy_replace("book_title", $book["title"])
        ->tag_lazy_replace("book_id", $book["id"])
        ->build();
}, "elimina", needs_database: true)
->add_renderer_POST(function(Database $db){
    $book_query =
        "SELECT cover_file_name FROM Books WHERE id = ?";
    $book_data = $db->execute_query($book_query, $_POST['id']);
    if(count($book_data) != 1){
        header("Location: /marango/Pages/500.php");
        exit();
    }
    $file_name = $book_data[0]["cover_file_name"];

    $db->get_connection()->begin_transaction();
    $stmt = $db->get_connection()->prepare("DELETE FROM Books WHERE id = ?");
    $stmt->bind_param('i', $_POST['id']);
    if(!$stmt->execute()){
        header("Location: /marango/Pages/500.php");
        exit();
    }

    if(file_exists("../assets/book_covers/" . $file_name)){
        $deleted = unlink("../assets/book_covers/" . $file_name);
        if(!$deleted){
            $db->get_connection()->rollback();
            header("Location: /marango/Pages/500.php");
            exit();
        }
    }

    $db->get_connection()->commit();
    header("Location: /marango/Pages/catalogo.php");
    exit();
}, "elimina", needs_database: true)
->execute();
