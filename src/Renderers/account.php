<?php

require_once "../components/breadcrumbs.php";
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
    $layout = str_replace("{{content}}",$content,$layout);

    $htmlBuilder = (new \Pangine\PangineUnvalidFormManager(new HTMLBuilderCleaner(layout: $layout)))->getHTMLBuilder();

    $layout =  $htmlBuilder->set("title",$title)
    ->set("menu",$navbar)
    ->set("breadcrumbs",$breadcrumbs)
    ->set("username-value",$_SESSION["user"]["username"])
    ->set("password-value",$_SESSION["user"]["password"])
    ->clean("-message")
    ->clean("-value")
    ->build();

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
    $htmlBuilder = (new \Pangine\PangineUnvalidFormManager(new HTMLBuilderCleaner(layout: $layout)))->getHTMLBuilder();
    $layout = $htmlBuilder->set("title",$title)
        ->set("menu",$navbar)
        ->set("breadcrumbs",$breadcrumbs)
        ->set("username-value",$_SESSION["user"]["username"])
        ->set("password-value",$_SESSION["user"]["password"])
        ->clean("-message")
        ->clean("-value")
        ->build();
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
