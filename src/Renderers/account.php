<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'components/breadcrumbs.php';
require_once 'components/navbar.php';
require_once 'Pangine/Pangine.php';
require_once 'include/database.php';
require_once 'include/utils.php';
require_once 'include/pages.php';

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
    $content = str_replace("<a href=\"{{pages-account}}\">Informazioni</a>","Informazioni",$content);
    $layout = str_replace("{{content}}",$content,$layout);

    $htmlBuilder = (new \Pangine\PangineUnvalidFormManager(new HTMLBuilderCleaner(layout: $layout)))->getHTMLBuilder();

    $layout =  $htmlBuilder->set("title",$title)
    ->set("menu",$navbar)
    ->set("breadcrumbs",$breadcrumbs)
    ->set("username-value",$_SESSION["user"]["username"])
    ->set("password-value",$_SESSION["user"]["password"])
    ->set('pages-account-update',pages['Account (Modifica)'])
    ->set('pages-exit',pages['Esci'])
    ->set('pages-form',pages['Account'])
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
    $content = str_replace("<a href=\"{{pages-account-update}}\">Modifica</a>","Modifica",$content);
    $layout = str_replace("{{content}}",$content,$layout);
    $htmlBuilder = (new \Pangine\PangineUnvalidFormManager(new HTMLBuilderCleaner(layout: $layout)))->getHTMLBuilder();
    $layout = $htmlBuilder->set("title",$title)
        ->set("menu",$navbar)
        ->set("breadcrumbs",$breadcrumbs)
        ->set("username-value",$_SESSION["user"]["username"])
        ->set("password-value",$_SESSION["user"]["password"])
        ->set('pages-account',pages['Account'])
        ->set('pages-exit',pages['Esci'])
        ->set('pages-form',pages['Account'])
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
    $validator->validate(pages['Account (Modifica)']);
    $database = new Database();
    $result = $database->update_user_info($_SESSION["user"]["username"],$_POST["username"],$_POST["password"]);
    $database->close();
    if($result){
        $_SESSION["user"]["username"] = $_POST["username"];
        $_SESSION["user"]["password"] = $_POST["password"];
        redirect(pages['Account']);
    }
};
