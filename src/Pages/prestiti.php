<?php

require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");
require_once(__DIR__ . "/../Utils/Database.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;
use Pangine\utils\Validator;
use \Utils\Database;

function get_loan_card(string $title, string $start_date, string $end_date, string $status): string {
    return "<li> 
            <article class='loan-card'>
                <h2>{$title}</h2>
                <dl>
                    <div>
                        <dt>Data inizio prestito:</dt>
                        <dd>{$start_date}</dd>
                    </div>

                    <div>
                        <dt>Data fine prestito:</dt>
                        <dd>{$end_date}</dd>
                    </div>
                    <div>
                        <dt>Stato prestito:</dt>
                        <dd>{$status}</dd>
                    </div>
                </dl>
            </article>
        </li>";
}

(new Pangine())
    ->add_renderer_GET(function(Database $db) {

        $content = file_get_contents(__DIR__ . "/../templates/prestiti_content.html");
        $where_cond = "";
        $order_field = "";

        if( $_GET["order"] == "start") {
            $order_field = "loan_start_date";
        }
        else {
            $order_field = "loan_expiration_date";
        }

        if($_GET["status"] != "all") {
            if($_GET["status"] == "expired") {
                $where_cond = "AND loan_expiration_date < CURRENT_DATE()";
            }
            else {
                $where_cond = "AND loan_expiration_date > CURRENT_DATE()";
            }
        } 
                
        $query = " SELECT *, IF(loan_expiration_date < CURRENT_DATE(), 'scaduto', 'attivo') AS status FROM Loans JOIN Books on Books.id = book_id
            WHERE user_username = ? {$where_cond}
            ORDER BY  {$order_field} DESC";
        
        $res = $db->execute_query($query, $_SESSION["user"]["username"]);
       


        $loans = "";
        if(!empty($res)) {
            $loans .= "<ol>";
            foreach($res as $loan)  {
                    $loans .= get_loan_card(
                    $loan["title"], 
                    $loan["loan_start_date"],  
                    $loan["loan_expiration_date"],
                    $loan["status"]);                
            }
            $loans .= "</ol>";
        }
        else {
            $loans = "<p>Non hai ancora effettuato alcun prestito.</p>";
        }

        echo (new LayoutBuilder())
            ->tag_lazy_replace("title", "Prestiti")
            ->tag_lazy_replace("description", "I tuoi prestiti")
            ->tag_lazy_replace("keywords", "Prestiti")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Prestiti")))
            ->tag_istant_replace("content", $content)
            ->tag_istant_replace("start", $_GET["order"] == "start" ? "selected" : "")
            ->tag_istant_replace("end", $_GET["order"] == "end" ? "selected" : "")
            ->tag_istant_replace("expired", $_GET["status"] == "expired" ? "selected" : "")
            ->tag_istant_replace("active", $_GET["status"] == "active" ? "selected" : "")
            ->tag_istant_replace("all", $_GET["status"] == "all" ? "selected" : "")
            ->tag_istant_replace("loans", $loans)
            ->build();
        
    },
    needs_database: true,
    validator: (new Validator("Pages/404.php"))
        ->add_parameter("status")->is_string(string_parser: function (string $status) {
            if($status == "active" || $status == "expired" || $status = "all") 
                return "";
            return "Filtro dei prestiti non valido";
        })
        ->add_parameter("order")->is_string(string_parser: function(string $order) {
            if($order == "start" || $order="end") 
                return "";
            return "Ordinamento dei prestiti non valido";
        }))
->execute();