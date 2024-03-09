<?php
set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'include/sessionEstablisher.php';
function navbar(): string
{
    try_session();

    // 1. Path pagina .php partendo dalla directory src;
    // 2. Nome da mostrare per tale pagina;
    // 3. Stati alla quale deve essere mostrata:
    //      - UNREGISTERED;
    //      - USER;
    //      - ADMIN.
    $links = array(
        array("/Pages/catalogo.php","Catalogo",array("UNREGISTERED","USER","ADMIN")),
        array("/chisiamo.php","Chi Siamo",array("UNREGISTERED")),
        array("/Pages/accedi.php","Accedi",array("UNREGISTERED")),
        array("/Pages/registrati.php","Registrati",array("UNREGISTERED")),
        array("/Pages/artista.php?create=true", "Aggiungi Artista", array("ADMIN")),
        array("/Pages/albums.php", "Albums", array("ADMIN")),
        array("/selectartist.php", "Aggiungi Canzone", array("ADMIN")),
        array("/Pages/account.php", "Account", array("USER","ADMIN")),
    );
    $selectedLink = strtok($_SERVER['REQUEST_URI'],'?');
    $navLinks = "";
    foreach ($links as $linkTriple) {
        $link = $linkTriple[0];
        $pageName = $linkTriple[1];
        $allowedStatus = $linkTriple[2];
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
