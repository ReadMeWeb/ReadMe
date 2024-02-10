<?php
require_once "sessionEstablisher.php";
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
        array("/catalogo.php","Catalogo",array("UNREGISTERED","USER","ADMIN")),
        array("/chisiamo.php","Chi Siamo",array("UNREGISTERED")),
        array("/accedi.php","Accedi",array("UNREGISTERED")),
        array("/registrati.php","Registrati",array("UNREGISTERED")),
        array("/aggiungiArtista.php", "Aggiungi Artista", array("ADMIN")),
        array("/aggiungiAlbum.php", "Aggiungi Album", array("ADMIN")),
        array("/selectartist.php", "Aggiungi Canzone", array("ADMIN")),
        array("/Pages/account.php", "Account", array("USER","ADMIN")),
    );
    $selectedLink = basename($_SERVER['PHP_SELF']);
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
