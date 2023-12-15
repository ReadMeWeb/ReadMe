<?php
require_once "sessionEstablisher.php";
function navbar(){
    try_session();

    // 1. Path pagina .php partendo dalla directory src;
    // 2. Nome da mostrare per tale pagina;
    // 3. Stati alla quale deve essere mostrata:
    //      - UN: Unregistered;
    //      - US: User;
    //      - AD: Admin.
    $links = array(
        array("index.php","Home",array("UN")),
        array("accedi.php","Accedi",array("UN")),
        array("registrati.php","Registrati",array("UN"))
    );
    $selectedLink = basename($_SERVER['PHP_SELF']);
    $navLinks = "";
    foreach ($links as $linkTriple){
        $classToApply = "";
        $link = $linkTriple[0];
        $pageName = $linkTriple[1];
        $allowedStatus = $linkTriple[2];
        if(!isset($_SESSION["Status"])){
            $_SESSION["Status"] = "UN";
        }
        if(in_array($_SESSION["Status"],$allowedStatus)){
            if($selectedLink == $link){
                $classToApply = " class='selectedNavLink' ";
            }
            $navLinks .= "<li><a". $classToApply ."href='". $link ."'>" . $pageName . "</a></li>";
        }
    }
    return $navLinks;
}