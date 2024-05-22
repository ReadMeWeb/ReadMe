<?php

require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");
require_once(__DIR__ . "/../Utils/Database.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;
use Pangine\utils\Validator;
use \Utils\Database;

function get_book_card(string $cover_file_name, string $title, int $copies, string $name_surname, int $id): string
{
    return "<div>
                    <dt>{$title}</dt>
                    <dd><img src='assets/book_covers/{$cover_file_name}' alt='' width='200' height='200'></dd>
                    <dd>Autore: {$name_surname}</dd>
                    <dd>Disponibilità: {$copies}</dd>
                    <dd><a href='Pages/libro.php?id={$id}'>Visualizza</a></dd>
            </div>";
}

(new Pangine())
    ->add_renderer_GET(function (Database $db) {

        $step_skip = 6;

        $content = file_get_contents(__DIR__ . "/../templates/catalogo_content.html");

        $query = trim($_GET['query']);

        if (empty($query)) {
            Pangine::redirect("Pages/catalogo.php");
        }

        $escaped_query = addcslashes($query, "%_\\");
        $final_query = "%";

        foreach (str_split($escaped_query) as $char) {
            $final_query .= ($char . "%");
        }

        $offset = $_GET['page_num'] ?? 1;
        $res = $db->execute_query(
            "SELECT Books.id, title, cover_file_name, name_surname, (number_of_copies - COALESCE(loans, 0)) AS number_of_copies
            FROM 
            (
                SELECT Books.id, title, cover_file_name, name_surname, number_of_copies
                FROM Authors 
                JOIN Books 
                ON Authors.id = Books.author_id 
                WHERE name_surname LIKE ? OR title LIKE ?
            ) AS Books 
            LEFT JOIN 
            (
                SELECT book_id, count(book_id) AS loans 
                FROM Loans 
                WHERE loan_expiration_date >= CURRENT_DATE() AND loan_start_date <= CURRENT_DATE()
                GROUP BY book_id
            ) AS Loans
            ON Books.id = book_id
            LIMIT ?, ?",
            $final_query,
            $final_query,
            (($offset - 1) * $step_skip),
            $step_skip
        );

        $books = "";
        $res_number = count($res) . " libri per la ricerca: '$query'";
        if (!empty($res)) {
            foreach ($res as $book) {
                $books .= get_book_card(
                    $book["cover_file_name"],
                    $book["title"],
                    $book["number_of_copies"],
                    $book["name_surname"],
                    $book["id"]
                );
            }
        } else {
            $res_number = "Nessun libro trovato per la ricerca: '{$query}'";
        }

        $book_count = $db->execute_query("SELECT count(*) as book_count FROM Books")[0]["book_count"];
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
            ->tag_lazy_replace("page-selector", Pangine::page_selector_generator(ceil(floatval($book_count) / floatval($step_skip))))
            ->build();
    },
        caller_parameter_name: "query",
        needs_database: true,
        validator: (new Validator("Pages/404.php"))
            ->add_parameter("query")
            ->is_string(0, 200)
    )
    ->add_renderer_GET(function (Database $db) {

        $step_skip = 6;

        $content = file_get_contents(__DIR__ . "/../templates/catalogo_content.html");

        $offset = $_GET['page_num'] ?? 1;

        $res = $db->execute_query(
            "SELECT Books.id, title, cover_file_name, name_surname, (number_of_copies - COALESCE(loans, 0)) AS number_of_copies FROM 
            (SELECT Books.id, title, cover_file_name, name_surname, number_of_copies  FROM Authors JOIN Books ON Authors.id = Books.author_id) AS Books
            LEFT JOIN 
            (SELECT book_id, count(book_id) AS loans FROM Loans WHERE loan_expiration_date >= CURRENT_DATE() AND loan_start_date <= CURRENT_DATE() GROUP BY book_id) AS Loans
            ON Books.id = book_id
            LIMIT ?, ?",
            (($offset - 1) * $step_skip),
            $step_skip
        );

        $books = "";
        $res_number = "Tutti i libri";

        if (!empty($res)) {
            foreach ($res as $book) {
                $books .= get_book_card(
                    $book["cover_file_name"],
                    $book["title"],
                    $book["number_of_copies"],
                    $book["name_surname"],
                    $book["id"]
                );
            }
            $books = '<dl id="books-container">' . $books . '</dl>';
        } else {
            $books = "<p>Il catalogo al momento è vuoto.</p>";
        }

        $book_count = $db->execute_query("SELECT count(*) as book_count FROM Books")[0]["book_count"];

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
            ->tag_lazy_replace("page-selector", Pangine::page_selector_generator(ceil(floatval($book_count) / floatval($step_skip))))
            ->build();
    },
        needs_database: true
    )->execute();
