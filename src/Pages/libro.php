<?php
require_once __DIR__ . "/../Pangine/Pangine.php";
require_once __DIR__ . "/../Pangine/utils/LayoutBuilder.php";
require_once __DIR__ . "/../Utils/Database.php";
require_once __DIR__ . "/../Pages/tmp_new_libro.php";

use Pangine\Pangine;
use Pangine\utils\LayoutBuilder;
use Pangine\utils\Validator;
use Utils\Database;

(new Pangine())
    ->add_renderer_GET(function (Database $db) {
        $content = file_get_contents(
            __DIR__ . "/../templates/libro_content.html"
        );

        $book_data = [];

        (new Validator("/marango/Pages/404.php"))
            ->add_parameter("id")
            ->is_numeric(
                value_parser: function (int $book_id) use ($db, &$book_data) {
                    $book_query =
                        "SELECT * FROM Books B INNER JOIN Authors A ON A.id = B.author_id WHERE B.id= ?";
                    $book_data = $db->execute_query($book_query, $book_id);
                    if (count($book_data) == 0) {
                        return "Il libro richiesto non esiste.";
                    }
                    return "";
                }
            )
            ->validate();

        $book_data = $book_data[0];

        $layout = (new LayoutBuilder())
            ->plain_instant_replace(
                "<main id=\"content\">",
                "<main id=\"book-page\">"
            )
            ->tag_istant_replace("content", $content)
            ->tag_lazy_replace("title", $book_data["title"])
            ->tag_lazy_replace("description", $book_data["description"])
            ->tag_lazy_replace(
                "keywords",
                "ReadMe, biblioteca, libri, narrativa, prenotazioni"
            )
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace(
                "breadcrumbs",
                Pangine::breadcrumbs_generator(["Home", "Catalogo", "Libro"])
            )
            ->tag_lazy_replace("book-title", $book_data["title"])
            ->tag_lazy_replace("book-author", $book_data["name_surname"])
            ->tag_lazy_replace("book-description", $book_data["description"])
            ->tag_lazy_replace(
                "cover-image-src",
                $book_data["cover_file_name"]
            );
        $operations = "";
        if ($_SESSION["user"]["status"] != Pangine::UNREGISTERED()) {
            if (
                $_SESSION["user"]["status"] == Pangine::USER() ||
                $_SESSION["user"]["status"] == Pangine::ADMIN()
            ) {
                $remaining_query = "
                        select id, (number_of_copies - COALESCE(number_of_loans, 0)) as copies_remaining

                        from Books as b

                        left join (

                            select book_id, count(book_id) as number_of_loans
                            from Loans
                            where loan_start_date <= now() AND
                                  loan_expiration_date >= now()
                            group by book_id

                        ) as l

                        on l.book_id = b.id
                        where b.id = ?
                    ";
                $remaining = $db->execute_query(
                    $remaining_query,
                    $book_data["id"]
                )[0]["copies_remaining"];
                $disabled = $remaining == 0 ? "disabled" : "";
                $copies = $book_data["number_of_copies"];
                $operations .= "
                        <p>Numero di copie possedute: $copies</p>
                        <form id='book_user_op_form' method='POST' action='/marango/Pages/libro.php?id={$book_data["id"]}'>
                            <p>Copie rimanenti: $remaining</p>
                            <input type='submit' name='noleggia' value='Noleggia' $disabled/>
                        </form>
                    ";
            }
            if ($_SESSION["user"]["status"] == Pangine::ADMIN()) {
                $operations .= "
                            <form id='book_admin_op_form' method='POST' action='/marango/Pages/libro.php?id={$book_data["id"]}'>
                                <input type='submit' name='modifica' value='Modifica'/>
                                <input type='submit' name='elimina' value='Elimina'/>
                            </form>
                    ";
            }
        }

        echo $layout->tag_lazy_replace("operations", $operations)->build();
    }, needs_database: true)
    ->add_renderer_GET(
        $renderer_get_new,
        "nuovo",
        needs_database: true,
        validator: $validator_get_new
    )
    ->execute();
