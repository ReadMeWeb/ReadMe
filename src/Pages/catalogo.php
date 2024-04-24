<?php

require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");
require_once(__DIR__ . "/../Utils/Database.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;
use Pangine\utils\Validator;
use \Utils\Database;

function get_book_card(string $cover_file_name, string $title, int $copies, string $name_surname, int $id): string {
    return "<li> 
            <article class='book-card'>
                <img src='{$cover_file_name}' alt='' width='200' height='200'>
                <dl>
                    <div>
                        <dt>Titolo:</dt>
                        <dd>{$title}</dd>
                    </div>

                    <div>
                        <dt>Autore:</dt>
                        <dd>{$name_surname}</dd>
                    </div>

                    <div>
                        <dt>Disponibilità:</dt>
                        <dd>{$copies}</dd>
                    </div>
                </dl>
                <a href='/marango/Pages/libro.php&id={$id}'>Visualizza</a>

            </article>
        </li>";
}

(new Pangine())
    ->add_renderer_GET(function(Database $db){

        $content = file_get_contents(__DIR__ . "/../templates/catalogo_content.html");

        $query =  htmlspecialchars(trim($_GET['query']));

        if(empty($query)) {
            Pangine::redirect('Catalogo');
        }

        $escaped_query = "%" . addcslashes($query, "%_\\") . "%";
        
        $res = $db->execute_query(
            "SELECT Books.id, title, cover_file_name, name_surname, (number_of_copies - COALESCE(loans, 0)) AS number_of_copies
            FROM 
            (
                SELECT Books.id, title, cover_file_name, name_surname, number_of_copies
                FROM Authors 
                JOIN Books 
                ON Authors.id = Books.author_id 
                WHERE name_surname LIKE ? OR title LIKE ?
            ) as Books 
            LEFT JOIN 
            (
                SELECT book_id, count(book_id) AS loans 
                FROM Loans 
                WHERE loan_expiration_date < CURRENT_DATE()
                GROUP BY book_id
            ) as Loans
            ON Books.id = book_id", 
            $escaped_query, 
            $escaped_query
        );

        $books = "";
        $res_number = count($res) . " libri per la ricerca: '$query'";
        if(!empty($res)) {
            foreach($res as $book) {
                $books .= get_book_card(
                    $book["cover_file_name"],
                    $book["title"], 
                    $book["number_of_copies"], 
                    $book["name_surname"],
                    $book["id"]
                );
            }
        }
        else {
            $res_number = "Nessun libro trovato per la ricerca: '{$query}'";
        }

        echo (new LayoutBuilder())
            ->tag_lazy_replace("title", "Catalogo")
            ->tag_lazy_replace("description", "Catalogo della libreria ReadMe")
            ->tag_lazy_replace("keywords", "ReadMe")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Catalogo")))
            ->tag_istant_replace("content", $content)
            ->tag_istant_replace("searched", $query)
            ->tag_istant_replace("results-number", $res_number)
            ->tag_istant_replace("books", $books)
            ->build();
    },
    caller_parameter_name: "query",
    needs_database: true, 
    validator: (new Validator("/marango/Pages/500.php"))
        ->add_parameter("query")
        ->is_string(0, 200)
    )
    ->add_renderer_GET(function(Database $db){
        $content = file_get_contents(__DIR__ . "/../templates/catalogo_content.html");

        $res = $db->execute_query(
            "SELECT Books.id, title, cover_file_name, name_surname, (number_of_copies - COALESCE(loans, 0)) AS number_of_copies FROM 
            (SELECT Books.id, title, cover_file_name, name_surname, number_of_copies  FROM Authors JOIN Books ON Authors.id = Books.author_id) as Books
            LEFT JOIN 
            (SELECT book_id, count(book_id) AS loans FROM Loans WHERE loan_expiration_date < CURRENT_DATE() GROUP BY book_id) as Loans
            ON Books.id = book_id"
        );

        $books = "";
        $res_number = "Tutti i libri";

        if(!empty($res)) {
            foreach($res as $book) {
                $books .= get_book_card(
                    $book["cover_file_name"],
                    $book["title"], 
                    $book["number_of_copies"], 
                    $book["name_surname"],
                    $book["id"]
                );
            }
        }
        else {
            $books = "Il catalogo al momento è vuoto.";
        }

        echo (new LayoutBuilder())
            ->tag_lazy_replace("title", "Catalogo")
            ->tag_lazy_replace("description", "Catalogo della libreria ReadMe")
            ->tag_lazy_replace("keywords", "ReadMe, ")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Catalogo")))
            ->tag_istant_replace("content", $content)
            ->tag_istant_replace("searched", '')
            ->tag_istant_replace("results-number", $res_number)
            ->tag_istant_replace("books", $books)
            ->build();
            
    },
    needs_database: true 
    )->execute();