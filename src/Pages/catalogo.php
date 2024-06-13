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
    $cover_file_name = str_replace("'","",$cover_file_name);
    return "<li>
                <article class='book-card'>
                    <h2>{$title}</h2>
                    <img src='assets/book_covers/{$cover_file_name}' alt='' width='200' height='200'/>
                    <dl>
                        <div>
                            <dt>Autore:</dt>
                            <dd>{$name_surname}</dd>
                        </div>

                        <div>
                            <dt>Disponibilità: </dt>
                            <dd>{$copies}</dd>
                        </div>
                    </dl>
                    <a href='Pages/libro.php?id={$id}'>Visualizza</a>
                </article>
            </li>";
}


(new Pangine())
    ->add_renderer_GET(function (Database $db) {

        $content = file_get_contents(__DIR__ . "/../templates/catalogo_content.html");

        $query = isset($_GET["query"])? $_GET["query"] : "";
        $page_n = isset($_GET["page"]) ? $_GET["page"] : 1;

        if($page_n < 1) 
            Pangine::redirect("Pages/404.php");
        

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
            Pangine::redirect("Pages/404.php");

        $books = "";
        $page_selector = "";
        $res_number = "Tutti i libri.";
        $books_count = $books_count[0]['books'];

        if(!empty($query))
            $res_number = $books_count . " libri per la ricerca: '$query'";
        
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
            $books = '<ul id="books-container">' . $books . '</ul>';

            $forward_links = $back_links = "";
            $last_page = ceil($books_count / BOOKS_PER_PAGE);

            if($page_n != $last_page) {
                $next_page = $page_n + 1;

                $forward_links .= "<a href='Pages/catalogo.php?page={$next_page}&query={$query}'><abbr title='Successivo'>Succ</abbr></a>";
                $forward_links .= "<a href='Pages/catalogo.php?page={$last_page}&query={$query}'>Fine</a>";
                
            }

            if($page_n > 1) {
                $prev_page = $page_n - 1;

                $back_links .= "<a href='Pages/catalogo.php?page=1&query={$query}'>Inizio</a>";
                $back_links .= "<a href='Pages/catalogo.php?page={$prev_page}&query={$query}'><abbr title='Precedente'>Prec</abbr></a>";
            }

            $page_selector = "
                <nav class='pages-nav' aria-label='Navigazione tramite pagine'>
                    {$back_links}
                    <p><abbr title='Corrente:'>Corr: </abbr>{$page_n}</p>
                    {$forward_links}
                </nav>
            ";
        }
    

        echo (new LayoutBuilder())
            ->tag_lazy_replace("title", "Catalogo")
            ->tag_lazy_replace("description", "Catalogo della libreria ReadMe")
            ->tag_lazy_replace("keywords", "ReadMe, Catalogo, Libreria, Libri, Autori, Copertine")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Catalogo")))
            ->tag_istant_replace("content", $content)
            ->tag_lazy_replace("searched", $query)
            ->tag_lazy_replace("results-number", $res_number)
            ->tag_lazy_replace("books", $books)
            ->tag_lazy_replace("page-selector", $page_selector)
            ->build();
    },
        needs_database: true
)->execute();
