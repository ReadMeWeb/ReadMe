<?php

require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");
require_once(__DIR__ . "/../Utils/Database.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;
use Pangine\utils\Validator;
use \Utils\Database;

define("LOANS_PER_PAGE", 6);

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

        $page_n = isset($_GET["page"])? $_GET["page"] : 1;
        
        if($page_n < 1) 
            Pangine::redirect("Pages/404.php"); 

        if( !isset($_GET["order"]) || $_GET["order"] == "start") {
            $_GET["order"] = "start";
            $order_field = "loan_start_date DESC";
        }
        else {
            $order_field = "loan_expiration_date";
        }

        if(isset($_GET["status"]) && $_GET["status"] != "all") {
            if($_GET["status"] == "expired") {
                $where_cond = "AND loan_expiration_date < CURRENT_DATE()";
            }
            else {
                $where_cond = "AND loan_expiration_date > CURRENT_DATE()";
            }
        }
        else {
            $_GET["status"] = "all";
        }
                
        $query = " SELECT *, IF(loan_expiration_date < CURRENT_DATE(), 'scaduto', 'attivo') AS status FROM Loans JOIN Books on Books.id = book_id
            WHERE user_username = ? {$where_cond}
            ORDER BY  {$order_field} 
            LIMIT ?, ?";
        $res = $db->execute_query($query, $_SESSION["user"]["username"], LOANS_PER_PAGE*($page_n-1), LOANS_PER_PAGE);
       
        $query = "SELECT count(*) AS loans FROM Loans WHERE user_username = ? {$where_cond}";
        $loans_count = $db->execute_query($query, $_SESSION["user"]["username"]);

        if(empty($res) and $page_n > 1)
            Pangine::redirect("Pages/404.php");

        $loans = $page_selector = "";

        if(!empty($res)) {
            $loans .= "<ol id='loans-list'>";
            foreach($res as $loan)  {
                    $loans .= get_loan_card(
                    $loan["title"], 
                    $loan["loan_start_date"],  
                    $loan["loan_expiration_date"],
                    $loan["status"]);                
            }
            $loans .= "</ol>";

            $forward_links = $back_links = "";
            $loans_count = $loans_count[0]['loans'];
            $last_page = ceil($loans_count / LOANS_PER_PAGE);

            if($page_n != $last_page) {

                $next_page = $page_n +1;

                $forward_links .= "<a href='Pages/prestiti.php?page={$next_page}&order={$_GET['order']}&status={$_GET['status']}'><abbr title='Successivo'>Succ</abbr></a>";
                $forward_links .= "<a href='Pages/prestiti.php?page={$last_page}&order={$_GET['order']}&status={$_GET['status']}'>Fine</a>";;
                
            }

            if($page_n > 1) {
                $prev_page = $page_n -1;
                
                $back_links = "<a href='Pages/prestiti.php?page=1&order={$_GET['order']}&status={$_GET['status']}'>Inizio</a>";
                $back_links .= "<a href='Pages/prestiti.php?page={$prev_page}&order={$_GET['order']}&status={$_GET['status']}'><abbr title='Precedente'>Prec</abbr></a>";
            }

            $page_selector = "
                <nav class='pages-nav' aria-label='Navigazione tramite pagine'>
                    {$back_links}
                    <p><abbr title='Corrente:'>Corr: </abbr>{$page_n}</p>
                    {$forward_links}
                </nav>";
        }
        else {
            switch($_GET["status"]) {
                case "all":
                    $loans = "<p>Non hai ancora effettuato alcun prestito.</p>";
                    break;
                case "expired":
                    $loans = "<p>Non possiedi alcun prestito scaduto.</p>";
                    break;
                case "active":
                    $loans = "<p>Non possiedi alcun prestito attivo.</p>";
                    break;
            }
        }

        echo (new LayoutBuilder("priv"))
            ->tag_lazy_replace("title", "Prestiti")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Prestiti")))
            ->tag_istant_replace("content", $content)
            ->tag_lazy_replace("start", $_GET["order"] == "start" ? "selected='selected'" : "")
            ->tag_lazy_replace("end", $_GET["order"] == "end" ? "selected='selected'" : "")
            ->tag_lazy_replace("expired", $_GET["status"] == "expired" ? "selected='selected'" : "")
            ->tag_lazy_replace("active", $_GET["status"] == "active" ? "selected='selected'" : "")
            ->tag_lazy_replace("all", $_GET["status"] == "all" ? "selected='selected'" : "")
            ->tag_lazy_replace("loans", $loans)
            ->tag_lazy_replace("page-selector", $page_selector)
            ->build();
        
    },
    needs_database: true
    )->execute();