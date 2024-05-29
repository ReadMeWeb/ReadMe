<?php
require_once(__DIR__ . "/../Pangine/Pangine.php");
require_once(__DIR__ . "/../Pangine/utils/LayoutBuilder.php");
require_once(__DIR__ . "/../Utils/Database.php");

use \Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;
use \Pangine\utils\Validator;
use \Utils\Database;

(new Pangine())
    ->add_renderer_GET(function () {
        $content = file_get_contents(__DIR__ . "/../templates/account_content.html");

        echo (new LayoutBuilder("priv"))
            ->tag_lazy_replace("title", "Account")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Account")))
            ->tag_istant_replace("content", $content)
            ->tag_lazy_replace("username-value", $_SESSION["user"]["username"])
            ->plain_instant_replace("<input type=\"password\" id=\"password\" name=\"password\" value=\"{{password-value}}\" onblur=\"validatePassword()\" disabled>","")
            ->plain_instant_replace("<label for=\"password\">Password</label>","")
            ->tag_lazy_replace("username-message", "")
            ->tag_lazy_replace("password-message", "")
            ->plain_instant_replace("<li><a href=\"{{pages-account}}\">Informazioni</a></li>", "<li>Informazioni</li>")
            ->tag_lazy_replace("pages-account-update", "Pages/account.php?update=true")
            ->tag_lazy_replace("pages-exit", "Pages/account.php?exit=true")
            ->tag_lazy_replace("pages-form", "")
            ->plain_instant_replace("<input type=\"submit\" name=\"update\" value=\"Modifica\">", "")
            ->build();
    })
    ->add_renderer_GET(function () {
        $content = file_get_contents(__DIR__ . "/../templates/account_content.html");

        echo (new LayoutBuilder("priv"))
            ->tag_lazy_replace("title", "Account")
            ->tag_lazy_replace("menu", Pangine::navbar_list())
            ->tag_lazy_replace("breadcrumbs", Pangine::breadcrumbs_generator(array("Home", "Account (Modifica)")))
            ->tag_istant_replace("content", $content)
            ->tag_lazy_replace("username-value", $_SESSION["user"]["username"])
            ->tag_lazy_replace("password-value", "")
            ->plain_lazy_replace(" disabled>", ">")
            ->plain_lazy_replace(" disabled>", ">")
            ->tag_lazy_replace("username-message", "")
            ->tag_lazy_replace("password-message", "")
            ->tag_lazy_replace("pages-account", "Pages/account.php")
            ->plain_instant_replace("<li><a href=\"{{pages-account-update}}\">Modifica</a></li>", "<li>Modifica</li>")
            ->tag_lazy_replace("pages-exit", "Pages/account.php?exit=true")
            ->tag_lazy_replace("pages-form", "Pages/account.php?update=true")
            ->build();
    }, caller_parameter_name: "update")
    ->add_renderer_POST(function (Database $db) {
        (new Validator("Pages/account.php?update=true"))
            ->add_parameter("username")
            ->is_string(string_parser: function () use ($db): string {
                $result = $db->execute_query("SELECT username FROM Users WHERE username = ?", $_POST["username"]);
                if (count($result) != 0 && $_POST["username"] != $_SESSION["user"]["username"]) {
                    return "<p><span lang='en'>Username</span> già utilizzato da un altro account.</p>";
                }
                return "";
            })->validate();
        $result = $db->execute_query("UPDATE Users SET username = ?, password = ? WHERE username = ?", $_POST["username"], $_POST["password"], $_SESSION["user"]["username"]);

        $_SESSION["user"]["username"] = $_POST["username"];
        $_SESSION["user"]["password"] = $_POST["password"];

        		Pangine::redirect("Pages/account.php");
    }, caller_parameter_name: "update", needs_database: true, validator: (new Validator("Pages/account.php?update=true"))
        ->add_parameter("username")
        ->is_string(4, 20)
        ->add_parameter("password")
        ->is_string(4, 128)
    )
    ->add_renderer_GET(function () {
        unset($_SESSION["user"]);
        $_SESSION["user"]["status"] = Pangine::UNREGISTERED();
        		Pangine::redirect("Pages/index.php");
    }, caller_parameter_name: "exit")
    ->execute();