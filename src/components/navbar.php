<?php
set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'include/sessionEstablisher.php';
require_once 'include/pages.php';
function navbar(): string
{
    try_session();

    // 1. Path pagina .php partendo dalla directory src;
    // 2. Nome da mostrare per tale pagina;
    // 3. Stati alla quale deve essere mostrata:
    //      - UNREGISTERED;
    //      - USER;
    //      - ADMIN.
    function associa_pagina_permessi($nome, $permessi) {
      return array(pages[$nome], $nome, $permessi);
    }
    $links = array(
        associa_pagina_permessi("Catalogo",array("UNREGISTERED","USER","ADMIN")),
        associa_pagina_permessi("Chi Siamo",array("UNREGISTERED")),
        associa_pagina_permessi("Accedi",array("UNREGISTERED")),
        associa_pagina_permessi("Registrati",array("UNREGISTERED")),
        associa_pagina_permessi("Aggiungi Artista", array("ADMIN")),
        associa_pagina_permessi("Albums", array("ADMIN")),
        associa_pagina_permessi("Aggiungi Canzone", array("ADMIN")),
        associa_pagina_permessi("Account", array("USER","ADMIN")),
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
