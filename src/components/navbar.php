<?php
require_once "sessionEstablisher.php";
function navbar(): string{
    try_session();

    // 1. Path pagina .php partendo dalla directory src;
    // 2. Nome da mostrare per tale pagina;
    // 3. Stati alla quale deve essere mostrata:
    //      - UNREGISTERED;
    //      - USER;
    //      - ADMIN.
    $links = array(
        array("index.php","Home",array("UNREGISTERED")),
        array("chisiamo.php","Chi Siamo",array("UNREGISTERED")),
        array("accedi.php","Accedi",array("UNREGISTERED")),
        array("registrati.php","Registrati",array("UNREGISTERED"))
    );
    $selectedLink = basename($_SERVER['PHP_SELF']);
    $navLinks = "";
    foreach ($links as $linkTriple){
        $classToApply = "";
        $link = $linkTriple[0];
        $pageName = $linkTriple[1];
        $allowedStatus = $linkTriple[2];
        if(!isset($_SESSION["user"])){
            $_SESSION["user"]["status"] = "UNREGISTERED";
        }
        if(in_array($_SESSION["user"]["status"],$allowedStatus)){
            if($selectedLink == $link){
                $classToApply = "class='selectedNavLink' ";
            }
            $navLinks .= "<li><a ". $classToApply ."href='". $link ."'>" . $pageName . "</a></li>";
        }
    }
    return $navLinks;
}