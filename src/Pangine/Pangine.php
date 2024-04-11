<?php

namespace Pangine;
require_once(__DIR__ . "/../Utils/Database.php");
require_once(__DIR__ . "/utils/Exception500.php");

use \Utils\Database;
use \Pangine\utils\Exception500;

class Pangine
{
    // renderers that require a specific field to be set
    private array $controlled_renderers_unpure = [];
    // renderers that don't require any specific field to be set
    private array $controlled_renderers_pure = [];

    static string $status_UNREGISTERED = "UNREGISTERED";
    static string $status_USER = "USER";
    static string $status_ADMIN= "ADMIN";

    public function __construct()
    {
        $this->try_session();
    }

    public function execute(): void
    {
        try {
            $db = new Database();
            foreach ($this->controlled_renderers_unpure as $renderer) {
                $renderer($db);
            }
            foreach ($this->controlled_renderers_pure as $renderer) {
                $renderer($db);
            }
            $db->close();
        } catch (Exception500 $e) {

        }
    }

    public function add_renderer_POST(callable $renderer, string $caller_parameter_name = ""): Pangine
    {
        if ($caller_parameter_name == "") {
            $this->controlled_renderers_pure[] = function (Database $db) use ($renderer, $caller_parameter_name) {
                if ($_SERVER['REQUEST_METHOD'] == "POST" && ($caller_parameter_name == "" || isset($_POST[$caller_parameter_name]))) {
                    $renderer($db);
                    exit();
                }
            };
        } else {
            $this->controlled_renderers_unpure[] = function (Database $db) use ($renderer, $caller_parameter_name) {
                if ($_SERVER['REQUEST_METHOD'] == "POST" && ($caller_parameter_name == "" || isset($_POST[$caller_parameter_name]))) {
                    $renderer($db);
                    exit();
                }
            };
        }
        return $this;
    }

    public function add_renderer_GET(callable $renderer, string $caller_parameter_name = ""): Pangine
    {
        if ($caller_parameter_name == "") {
            $this->controlled_renderers_pure[] = function (Database $db) use ($renderer, $caller_parameter_name) {
                if ($_SERVER['REQUEST_METHOD'] == "GET" && ($caller_parameter_name == "" || isset($_GET[$caller_parameter_name]))) {
                    $renderer($db);
                    exit();
                }
            };
        } else {
            $this->controlled_renderers_unpure[] = function (Database $db) use ($renderer, $caller_parameter_name) {
                if ($_SERVER['REQUEST_METHOD'] == "GET" && ($caller_parameter_name == "" || isset($_GET[$caller_parameter_name]))) {
                    $renderer($db);
                    exit();
                }
            };
        }
        return $this;
    }

    private function try_session(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if(!isset($_SESSION["user"]["status"])){
            $_SESSION["user"]["status"] = self::$status_UNREGISTERED;
        }
    }
}