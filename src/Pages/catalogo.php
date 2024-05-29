<?php

require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");
require_once(__DIR__ . "/../Utils/Database.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;
use Pangine\utils\Validator;
use \Utils\Database;

define("BOOKS_PER_PAGE", 6);

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

        $content = file_get_contents(__DIR__ . "/../templates/catalogo_content.html");

        $query = trim($_GET['query']);

        (new Validator("Pages/404.php"))
        ->add_parameter("page")
        ->is_numeric(
            value_parser: function (int $page_n) use ($db, &$res, $query, &$books_count) {

                if($page_n < 1) 
                    return "Pagina richiesta inesistente";

                $escaped_query = addcslashes($query, "%_\\");
                $final_query = "%";

                foreach (str_split($escaped_query) as $char) {
                    $final_query .= ($char . "%");
                }

                $books_count = $db->execute_query(
                    "SELECT COUNT(*) AS books FROM Books 
                    JOIN Authors ON Authors.id = Books.author_id 
                    WHERE  name_surname LIKE ? OR title LIKE ?",
                    $final_query,
                    $final_query
                );

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
                    ($page_n-1)  * BOOKS_PER_PAGE,
                    BOOKS_PER_PAGE 
                );

                if(empty($res) && $page_n > 1)
                    return "Pagina richiesta inesistente";
                return "";
                
            }
        )->validate();

        $books = "";
        $page_selector = "";
        $res_number = "Tutti i libri.";

        if(!empty($query))
            $res_number = $books_count[0]["books"] . " libri per la ricerca: '$query'";
        
        if(!empty($res)) {
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

            $next_page = $prev_page = "";
            $page = $_GET["page"];

            if($books_count[0]["books"] > BOOKS_PER_PAGE * $page) {
                if($page == floor($books_count[0]["books"] / BOOKS_PER_PAGE)) {
                    $next_page = "<a href='Pages/catalogo.php?page=" . ($page+1) . "&query={$query}'>Fine</a>";
                }
                else {
                    $next_page = "<a href='Pages/catalogo.php?page=" . ($page+1) . "&query={$query}'><abbr title='Successivo'>Succ</abbr></a>";
                }
            }

            if($page > 1) {
                if($page == 2) {
                    $prev_page = "<a href='Pages/catalogo.php?page=" . ($page-1) . "&query={$query}'>Inizio</a>";
                }
                else {
                    $prev_page = "<a href='Pages/catalogo.php?page=" . ($page-1) . "&query={$query}'><abbr title='Precedente'>Prec</abbr></a>";
                }
            }

            $page_selector = "
                <nav class='pages-nav'>
                    {$prev_page}
                    <p><p><abbr title='Corrente:'>Corr: </abbr>{$page}</p>
                    {$next_page}
                </nav>
            ";
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
            ->tag_lazy_replace("page-selector", $page_selector)
            ->build();
    },
        needs_database: true,
        validator: (new Validator("Pages/404.php"))
            ->add_parameter("query")
            ->is_string(0, 200)
)->execute();
