<?php

namespace Pangine;
require_once(__DIR__ . "/../Utils/Database.php");
require_once(__DIR__ . "/utils/Exception500.php");

use \Utils\Database;
use \Pangine\utils\Exception500;

define("status_UNREGISTERED", "UNREGISTERED");

class Pangine
{

    // renderers that require a specific field to be set
    private array $controlled_renderers_unpure = [];
    // renderers that don't require any specific field to be set
    private array $controlled_renderers_pure = [];

    private static string $status_UNREGISTERED = "UNREGISTERED";
    private static string $status_USER = "USER";
    private static string $status_ADMIN = "ADMIN";

    private static array $pages = [];

    public function __construct()
    {
        $this->try_session();
        self::$pages = array(
            "Chi siamo" => array(
                "path" => "/Pages/chi_siamo.php",
                "privileges" => array(self::$status_UNREGISTERED)
            ),
            "Catalogo" => array(
                "path" => "/Pages/catalogo.php",
                "privileges" => array(self::$status_UNREGISTERED, self::$status_USER, self::$status_ADMIN)
            ),
            "Accedi" => array(
                "path" => "/Pages/accedi.php",
                "privileges" => array(self::$status_UNREGISTERED)
            ),
            "Registrati" => array(
                "path" => "/Pages/registrati.php",
                "privileges" => array(self::$status_UNREGISTERED)
            ),
            "Home" => array(
                "path" => "/Pages/index.php",
                "privileges" => array() // Rimane vuoto in quanto non si vuole che venga visualizzato nella navbar
            ),
        );
    }

    public function execute(): void
    {
        try {
            foreach ($this->controlled_renderers_unpure as $renderer) {
                $renderer();
            }
            foreach ($this->controlled_renderers_pure as $renderer) {
                $renderer();
            }
        } catch (Exception500 $e) {

        }
    }

    public function add_renderer_POST(callable $renderer, string $caller_parameter_name = "", bool $needs_database = false): Pangine
    {
        $renderer_wrapper = function () use ($renderer, $caller_parameter_name, $needs_database) {
            if ($_SERVER['REQUEST_METHOD'] == "POST" && ($caller_parameter_name == "" || isset($_POST[$caller_parameter_name]))) {
                if ($needs_database) {
                    $db = new Database();
                    $renderer($db);
                    $db->close();
                } else {
                    $renderer();
                }
                exit();
            }
        };
        if ($caller_parameter_name == "") {
            $this->controlled_renderers_pure[] = $renderer_wrapper;
        } else {
            $this->controlled_renderers_unpure[] = $renderer_wrapper;
        }
        return $this;
    }

    public function add_renderer_GET(callable $renderer, string $caller_parameter_name = "", bool $needs_database = false): Pangine
    {
        $renderer_wrapper = function () use ($renderer, $caller_parameter_name, $needs_database) {
            if ($_SERVER['REQUEST_METHOD'] == "GET" && ($caller_parameter_name == "" || isset($_GET[$caller_parameter_name]))) {
                if ($needs_database) {
                    $db = new Database();
                    $renderer($db);
                    $db->close();
                } else {
                    $renderer();
                }
                exit();
            }
        };
        if ($caller_parameter_name == "") {
            $this->controlled_renderers_pure[] = $renderer_wrapper;
        } else {
            $this->controlled_renderers_unpure[] = $renderer_wrapper;
        }
        return $this;
    }

    private function try_session(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION["user"]["status"])) {
            $_SESSION["user"]["status"] = self::$status_UNREGISTERED;
        }
    }

    public static function navbar_list(): string
    {
        $selectedLink = strtok($_SERVER['REQUEST_URI'], '?');
        $navLinks = "";
        foreach (self::$pages as $page_title => $page_metadata) {
            $link = $page_metadata["path"];
            $pageName = $page_title;
            $allowedStatus = $page_metadata["privileges"];
            if (in_array($_SESSION["user"]["status"], $allowedStatus)) {
                if ($selectedLink == $link) {
                    $navLinks .= "<li class='selectedNavLink'>" . $pageName . "</li>";
                } else {
                    $navLinks .= "<li><a href='" . $link . "'>" . $pageName . "</a></li>";
                }
            }
        }
        return $navLinks;
    }

    /**
     * @throws Exception500
     */
    public static function breadcrumbs_generator(array $breadcrumbs_array): string
    {
        if (count($breadcrumbs_array) == 0) {
            throw new Exception500("Fornire una breadcrumb con almeno una pagina.");
        }
        $breadcrumb_str = "<p>Ti trovi in: ";
        $last_breadcrumb = end($breadcrumbs_array);
        foreach ($breadcrumbs_array as $breadcrumb) {
            if (strcmp($breadcrumb, $last_breadcrumb) == 0) {
                $breadcrumb_str .= $breadcrumb;
            } else {
                if (!isset(self::$pages[$breadcrumb])) {
                    throw new Exception500("Fornire una breadcrumb riferita ad una pagina realmente esistente.");
                }
                $breadcrumb_str .= "<a href='" . self::$pages[$breadcrumb]["path"] . "'>";
                if ($breadcrumb == "Home") {
                    $breadcrumb = "<span lang='en'>Home</span>";
                }
                $breadcrumb_str .= $breadcrumb . "</a> &gt;&gt; ";
            }
        }
        return $breadcrumb_str . "</p>";
    }
}