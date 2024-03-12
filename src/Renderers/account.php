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

    $content = (new HTMLBuilder("../components/layoutLogged.html"))
    ->set('title','Account')
    ->set('menu',navbar())
    ->set('breadcrumbs',arraybreadcrumb(['Home', 'Account']))
    ->set('content',(new \Pangine\PangineUnvalidFormManager((new HTMLBuilderCleaner("../components/account.html"))
    ->set('username-value',$_SESSION["user"]["username"])
    ->set('password-value',$_SESSION["user"]["password"])
    ->set('pages-account-update',pages['Account (Modifica)'])
    ->set('pages-account','{{pages-account}}')
    ->set('pages-exit',pages['Esci'])
    ->set('pages-form',pages['Account'])
    ->clean('-message')
    ->clean('-value')))
    ->getHTMLBuilder()
    ->build())
    ->build();

    $content = str_replace("<input type=\"submit\" name=\"update\" value=\"Modifica\">","",$content);
    $content = str_replace("<a href=\"{{pages-account}}\">Informazioni</a>","Informazioni",$content);

    echo $content;
};

$get_edit_account = function () {
    (new Pangine\PangineAuthenticator())->authenticate(array("USER","ADMIN"));

    $content = (new HTMLBuilderCleaner('../components/layoutLogged.html'))
        ->set('title','Account')
        ->set('menu',navbar())
        ->set('breadcrumbs',arraybreadcrumb(['Home', 'Account', 'Account (Modifica)']))
        ->set('content',(new \Pangine\PangineUnvalidFormManager((new HTMLBuilderCleaner('../components/account.html'))
        ->set('username-value',$_SESSION["user"]["username"])
        ->set('password-value',$_SESSION["user"]["password"])
        ->set('pages-account',pages['Account'])
        ->set('pages-account-update','{{pages-account-update}}')
        ->set('pages-exit',pages['Esci'])
        ->set('pages-form',pages['Account'])
        ->clean('-message')
        ->clean('-value')))
        ->getHTMLBuilder()
        ->build())
        ->build();

    $content = str_replace("disabled","",$content);
    $content = str_replace("<a href=\"{{pages-account-update}}\">Modifica</a>","Modifica",$content);
    echo $content;
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
