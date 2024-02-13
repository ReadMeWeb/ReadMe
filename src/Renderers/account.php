<?php

require_once "../components/breadcrumbs/breadcrumbsBuilder.php";
require_once "../components/breadcrumbs/breadcrumbItem.php";
require_once "../components/navbar.php";
require_once "../Pangine/Pangine.php";
require_once "../data/database.php";

$get_account = function () {
    (new Pangine\PangineAuthenticator())->authenticate(array("USER","ADMIN"));

    $layout = file_get_contents("../components/layoutLogged.html");
    $title = "Account";
    $navbar = navbar();
    $breadcrumbs = (new BreadcrumbsBuilder())
        ->addBreadcrumb(new BreadcrumbItem("Home"))
        ->addBreadcrumb(new BreadcrumbItem("Account", true))
        ->build()
        ->getBreadcrumbsHtml();
    $content = file_get_contents("../components/account.html");
    $content = str_replace("<input type=\"submit\" name=\"update\" value=\"Modifica\">","",$content);
    $content = str_replace("<a href=\"/Pages/account.php\">Informazioni</a>","Informazioni",$content);

    $layout = str_replace(
        array("{{title}}",
            "{{menu}}",
            "{{breadcrumbs}}",
            "{{content}}",
            "{{username-value}}",
            "{{password-value}}",
            "{{username-message}}",
            "{{password-message}}")
        ,array(
            $title,
            $navbar,
            $breadcrumbs,
            $content,
            $_SESSION["user"]["username"],
            $_SESSION["user"]["password"],
            "",
            ""),
        $layout);
    echo $layout;
};

$get_edit_account = function () {
    $database = new Database();
    (new Pangine\PangineAuthenticator())->authenticate(array("USER","ADMIN"));

    $layout = file_get_contents("../components/layoutLogged.html");
    $title = "Account";
    $navbar = navbar();
    $breadcrumbs = (new BreadcrumbsBuilder())
        ->addBreadcrumb(new BreadcrumbItem("Home"))
        ->addBreadcrumb(new BreadcrumbItem("Account"))
        ->addBreadcrumb(new BreadcrumbItem("Account (Modifica)", true))
        ->build()
        ->getBreadcrumbsHtml();
    $content = file_get_contents("../components/account.html");
    $content = str_replace("disabled","",$content);
    $content = str_replace("<a href=\"/Pages/account.php?update=true\">Modifica</a>","Modifica",$content);
    $layout = str_replace("{{content}}",$content,$layout);
    $layout = (new \Pangine\PangineUnvalidFormManager($layout))->getLayout();
    $layout = str_replace(
        array("{{title}}",
            "{{menu}}",
            "{{breadcrumbs}}",
            "{{username-value}}",
            "{{password-value}}",
            "{{username-message}}",
            "{{password-message}}")
        ,array(
            $title,
            $navbar,
            $breadcrumbs,
            $_SESSION["user"]["username"],
            $_SESSION["user"]["password"],
            "",
            ""
        ),
        $layout);
    echo $layout;
    $database->close();
};

$post_edit_account = function (){
    (new Pangine\PangineAuthenticator())->authenticate(array("USER","ADMIN"));
    $expectedParameters = array(
        "username"=> (new Pangine\PangineValidatorConfig(
            notEmpty: true,
            minLength: 6,
            maxLength: 40
        )),
        "password"=>(new Pangine\PangineValidatorConfig(
            notEmpty: true,
            minLength: 8,
            maxLength: 20
        )),
    );
    $validator = new Pangine\PangineValidator("POST",$expectedParameters);
    $validator->validate("/Pages/account.php?update=true");
    $database = new Database();
    $result = $database->update_user_info($_SESSION["user"]["username"],$_POST["username"],$_POST["password"]);
    $database->close();
    if($result){
        $_SESSION["user"]["username"] = $_POST["username"];
        $_SESSION["user"]["password"] = $_POST["password"];
        header("Location: /Pages/account.php");
    }
};
