<?php
require_once __DIR__ . "/../Pangine/Pangine.php";
require_once __DIR__ . "/../Pangine/utils/LayoutBuilder.php";
require_once __DIR__ . "/../Pangine/utils/Exception500.php";
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

        $content = file_get_contents(__DIR__ . "/../templates/libro_new_content.html");

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
            ->plain_instant_replace('<main id="content">', '<main id="content" class="book-page">')
            ->tag_lazy_replace("book_title-value", "")
            ->tag_lazy_replace("description-value", "")
            ->tag_lazy_replace("current_cover", "")
            ->tag_lazy_replace("author-value", "")
            ->tag_lazy_replace("author-new-value", "")
            ->tag_lazy_replace("no_copies-value", "1")
            ->tag_lazy_replace("authors_options", $authors_options)
            ->tag_lazy_replace("submit-value", "Aggiungi")
            ->tag_lazy_replace("submit-name", "create")
            ->tag_lazy_replace("book_id_field", "")
            ->tag_lazy_replace("back_button", "")
            ->tag_lazy_replace("book_title-message", "")
            ->tag_lazy_replace("description-message", "")
            ->tag_lazy_replace("no_copies-message", "")
            ->tag_lazy_replace("author-message", "")
            ->tag_lazy_replace("author-new-message", "")
            ->tag_lazy_replace("cover-message", "")
            ->build();
    }, needs_database: true
)
->add_renderer_GET(
    function(Database $db){

    (new Validator("Pages/404.php"))
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
        $current_author_option = "<option selected='selected' value='{$book_data["author_id"]}'>{$book_data["name_surname"]}</option>";
        $authors_options = "";
        foreach ($authors as $author) {
            $authors_options .= "<option value='{$author["id"]}'>{$author["name_surname"]}</option>";
        }

        $content = file_get_contents(__DIR__ . "/../templates/libro_edit_content.html");

        echo (new LayoutBuilder("priv"))
            ->tag_istant_replace("content", $content)
            ->tag_lazy_replace("title", "Modifica Libro")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_istant_replace('breadcrumbs', Pangine::breadcrumbs_generator(array('Home', 'Catalogo', 'Libro', 'Modifica')))
            ->plain_instant_replace('Pages/libro.php', 'Pages/libro.php?id=' . $book_data['id'])
            ->tag_lazy_replace("book_title-value", $book_data["title"])
            ->tag_lazy_replace("description-value", $book_data["description"])
            ->tag_lazy_replace("current_cover", str_replace("'","",$book_data["cover_file_name"]))
            ->tag_lazy_replace("no_copies-value", $book_data["number_of_copies"])
            ->tag_lazy_replace("author-value", $current_author_option)
            ->tag_lazy_replace("author-new-value", "")
            ->tag_lazy_replace("authors_options", $authors_options)
            ->tag_lazy_replace("submit-value", "Aggiorna")
            ->tag_lazy_replace("submit-name", "update")
            ->tag_lazy_replace("book_id_field", "<input type='hidden' name='book_id' value='{$book_data['id']}'/>")
            ->tag_lazy_replace("back_button", "<a href='Pages/libro.php?id={$book_data['id']}'>Annulla operazione</a>")
            ->tag_lazy_replace("book_title-message", "")
            ->tag_lazy_replace("description-message", "")
            ->tag_lazy_replace("no_copies-message", "")
            ->tag_lazy_replace("author-message", "")
            ->tag_lazy_replace("author-new-message", "")
            ->tag_lazy_replace("cover-message", "")
            ->build();
    },
    "modifica",
    needs_database: true
)
->add_renderer_POST(function(Database $db){
    (new Validator("Pages/crud_libro.php?id={$_POST['book_id']}&modifica"))
        ->add_parameter("book_title")
        ->is_string(min_length: 4, max_length: 30)
        ->add_parameter("description")
        ->is_string(min_length: 20)
        ->add_parameter("book_id")
        ->is_numeric(value_parser:function(int $book_id) use ($db, &$book_data) {
            $book_query =
                "SELECT * FROM Books WHERE id = ?";
            $book_data = $db->execute_query($book_query, $book_id);
            return count($book_data) == 0 ? "Il libro selezionato non esiste." : "";
        })
        ->add_parameter("no_copies")
        ->is_numeric(min_val: 1, max_val: 1000000)
        ->add_parameter("author-new")
        ->is_string(string_parser: function(string $author) use ($db) {
            if($author != "") {
                if(strlen($author) < 4) {
                    return "Il nome dell'autore o dell'autrice deve essere lungo almeno 4 caratteri.";
                } 
                if(strlen($author) > 30 ) {
                    return "Il nome dell'autore o dell'autrice deve essere lungo meno di 30 caratteri.";
                }
                return "";
            }else{
                (new Validator("Pages/crud_libro.php?id={$_POST['book_id']}&modifica"))
                ->add_parameter("author")
                ->is_string(string_parser: function(string $author_id) use ($db) {
                    $author_query =
                        "SELECT name_surname FROM Authors WHERE id = ?";
                    $author_data = $db->execute_query($author_query, $author_id);
                    return count($author_data) == 0 ? "L'autore o l'autrice selezionato/a non esiste." : "";
                })->validate();
            }
        })
        ->validate();
    
    $old_author = $book_data[0]["author_id"];

    if($_FILES["cover"]["tmp_name"] != ""){
        $new_name = $_POST["book_id"] .".". pathinfo($_FILES["cover"]["name"], PATHINFO_EXTENSION);
        $saved = move_uploaded_file($_FILES["cover"]["tmp_name"], "../assets/book_covers/" . $new_name);
        if(!$saved){
            Pangine::set_general_message("Errore durante il caricamento del file per l'aggiornaento del libro, riprovare (ERR_BOOK_01)");
            		Pangine::redirect("Pages/crud_libro.php?id={$_POST['book_id']}&modifica");
        }
    }

    $last_id = 0;
    $new_author = false;

    if(isset($_POST['author-new']) && $_POST['author-new'] != ""){
        $new_author = true;
        $stmt = $db->get_connection()->prepare("INSERT INTO Authors (name_surname) VALUES (?)");
        $stmt->bind_param('s', $_POST['author-new']);
        if(!$stmt->execute()){
        $db->get_connection()->rollback();
            Pangine::set_general_message("Errore durante l'inserimento del libro, riprovare (ERR_BOOK_10)");
          		Pangine::redirect("Pages/crud_libro.php?id={$_POST['book_id']}&modifica");
        }
        $last_id = $db->get_connection()->insert_id;
    }

    $author = $new_author ? $last_id : $_POST['author'];
    $result = $db->execute_query(
        "UPDATE Books SET title = ?, description = ?, author_id = ?, number_of_copies = ? WHERE id = ?",
        $_POST["book_title"],
        $_POST["description"],
        $author,
        $_POST["no_copies"],
        $_POST["book_id"]
    );
    if(!$result){
        $db->get_connection()->rollback();
        Pangine::set_general_message("Errore durante l'aggiornamento del libro, riprovare (ERR_BOOK_02)");
        		Pangine::redirect("Pages/crud_libro.php?id={$_POST['book_id']}&modifica");
    }

    $db->get_connection()->commit();

    $db->execute_query(
        "DELETE FROM Authors 
            WHERE Authors.id=? AND 
            (SELECT COUNT(*) FROM Books WHERE Books.author_id = ?)=0", 
            $old_author, 
            $old_author
    );

    Pangine::set_general_message("Libro aggiornato con successo","success");
    		Pangine::redirect("Pages/libro.php?id={$_POST["book_id"]}");
}, "update", needs_database: true)
->add_renderer_POST(
    function(Database $db){

        (new Validator("Pages/crud_libro.php?create"))
            ->add_parameter("book_title")
            ->is_string(min_length: 4, max_length: 30)
            ->add_parameter("description")
            ->is_string(min_length: 20)
            ->add_parameter("no_copies")
            ->is_numeric(min_val: 1, max_val: 1000000)
            ->add_parameter("cover")
            ->is_file([
                "jpeg",
                "jpg",
                "png",
            ])
            ->add_parameter("author-new")
            ->is_string(string_parser: function(string $author) use ($db) {
                if($author != "") {

                    if(strlen($author) < 4) {
                        return "Il nome dell'autore o dell'autrice deve essere lungo almeno 4 caratteri.";
                    } 

                    if(strlen($author) > 30 ) {
                        return "Il nome dell'autore o dell'autrice deve essere lungo meno di 30 caratteri.";
                    }

                    return "";
                }else{
                    (new Validator("Pages/crud_libro.php?create"))
                    ->add_parameter("author")
                    ->is_string(string_parser: function(string $author_id) use ($db) {
                        $author_query =
                            "SELECT name_surname FROM Authors WHERE id = ?";
                        $author_data = $db->execute_query($author_query, $author_id);
                        return count($author_data) == 0 ? "L'autore o l'autrice selezionato/a non esiste." : "";
                    })->validate();
                }
            })->validate();

        $db->get_connection()->begin_transaction();
        $tmp_name = "tmp.jpeg";
        $last_id = 0;
        $new_author = false;

        if(isset($_POST['author-new']) && $_POST['author-new'] != ""){
            $new_author = true;
            $stmt = $db->get_connection()->prepare("INSERT INTO Authors (name_surname) VALUES (?)");
            $stmt->bind_param('s', $_POST['author-new']);
            if(!$stmt->execute()){
                $db->get_connection()->rollback();
                Pangine::set_general_message("Errore durante l'inserimento del libro, riprovare (ERR_BOOK_10)");
              		Pangine::redirect("Pages/crud_libro.php?create");
            }
            $last_id = $db->get_connection()->insert_id;
        }


        $author = $new_author ? $last_id : $_POST['author'];
        $stmt = $db->get_connection()->prepare("INSERT INTO Books (title, description, cover_file_name, author_id, number_of_copies) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssii', $_POST['book_title'], $_POST['description'], $tmp_name, $author, $_POST['no_copies']);
        if(!$stmt->execute()){
            $db->get_connection()->rollback();
            Pangine::set_general_message("Errore durante l'inserimento del libro, riprovare (ERR_BOOK_03)");
            		Pangine::redirect("Pages/crud_libro.php?create");
        }
        $last_id = $db->get_connection()->insert_id;

        $new_name = $last_id .".". pathinfo($_FILES["cover"]["name"], PATHINFO_EXTENSION);
        $saved = move_uploaded_file($_FILES["cover"]["tmp_name"], "../assets/book_covers/" . $new_name);
        if(!$saved){
            $db->get_connection()->rollback();
            Pangine::set_general_message("Errore durante il caricamento del file per l'inserimento del libro, riprovare (ERR_BOOK_04)");
            		Pangine::redirect("Pages/crud_libro.php?create");
        }

        $stmt = $db->get_connection()->prepare("UPDATE Books SET cover_file_name = ? WHERE id = ?");
        $stmt->bind_param('si', $new_name, $last_id);
        if(!$stmt->execute()){
            $db->get_connection()->rollback();
            Pangine::set_general_message("Errore durante l'inserimento del libro, riprovare (ERR_BOOK_05)");
            		Pangine::redirect("Pages/crud_libro.php?create");
        }

        $db->get_connection()->commit();

        Pangine::set_general_message("Libro inserito con successo","success");
        		Pangine::redirect("Pages/libro.php?id=$last_id");
    },
    "create",
    needs_database: true
)
->add_renderer_GET(function(Database $db){
    $book_query =
        "SELECT * FROM Books WHERE id = ?";
    $book_data = $db->execute_query($book_query, $_GET['id']);
    if(count($book_data) != 1){
        throw new \Pangine\utils\Exception500("Stai cercando di eliminare un libro che non esiste!");
        exit();
    }
    $book = $book_data[0];

    $content = file_get_contents(__DIR__ . "/../templates/libro_delete_content.html");

    echo (new LayoutBuilder("priv"))
        ->tag_istant_replace("content", $content)
        ->tag_lazy_replace("title", "Elimina Libro")
        ->tag_lazy_replace("menu", Pangine::navbar_list())
        ->tag_istant_replace('breadcrumbs', Pangine::breadcrumbs_generator(array('Home', 'Catalogo', 'Libro', 'Elimina')))
        ->plain_instant_replace("libro.php'", "libro.php?id=". $book["id"] ."'")
        ->plain_instant_replace(
            "<main id=\"content\">",
            "<main id=\"book-page-delete\" class=\"book-page\">"
        )
        ->tag_lazy_replace("book_title", $book["title"])
        ->tag_lazy_replace("book_id", $book["id"])
        ->plain_lazy_replace("#content","#book-page-delete")
        ->build();
}, "elimina", needs_database: true)
->add_renderer_POST(function(Database $db){
    $book_query =
        "SELECT cover_file_name, author_id FROM Books WHERE id = ?";
    $book_data = $db->execute_query($book_query, $_POST['id']);
    if(count($book_data) != 1){
        Pangine::set_general_message("Non è stato possbile trovare il libro richiesto, riprovare (ERR_BOOK_07)");
        		Pangine::redirect("Pages/catalogo.php");
    }
    $file_name = $book_data[0]["cover_file_name"];

    $db->get_connection()->begin_transaction();
    $stmt = $db->get_connection()->prepare("DELETE FROM Books WHERE id = ?");
    $stmt->bind_param('i', $_POST['id']);
    if(!$stmt->execute()){
        Pangine::set_general_message("Non è stato possbile eliminare il libro, riprovare (ERR_BOOK_08)");
        		Pangine::redirect("Pages/libro.php?id='{$_POST['id']}'");
    }

    $old_author = $book_data[0]["author_id"];

    $db->execute_query(
        "DELETE FROM Authors
            WHERE Authors.id = ? AND 
            (SELECT COUNT(*) FROM Books WHERE Books.author_id = ?) = 0", 
            $old_author , 
            $old_author 
    );


    if(file_exists("../assets/book_covers/" . $file_name)){
        $deleted = unlink("../assets/book_covers/" . $file_name);
        if(!$deleted){
            $db->get_connection()->rollback();
            Pangine::set_general_message("Non è stato possbile eliminare il file collegato al libro, riprovare (ERR_BOOK_09)");
            		Pangine::redirect("Pages/libro.php?id='{$_POST['id']}'");
        }
    }

    $db->get_connection()->commit();
    Pangine::set_general_message("Libro eliminato con successo","success");
    		Pangine::redirect("Pages/catalogo.php");
}, "elimina", needs_database: true)
->execute();
