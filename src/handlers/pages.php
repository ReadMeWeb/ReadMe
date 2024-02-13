<?php

class Pages
{
    static array $pages = [
        "Home" => "index.php", //in questa non ci va lo '/' all'inizio
        "Chi Siamo" => "/chisiamo.php",
        "Accedi" => "/accedi.php",
        "Registrati" => "/registrati.php",
        "Catalogo" => "/Pages/catalogo.php",
        "Aggiungi Artista" => "/aggiungiArtista.php",
        "Aggiungi Album" => "/aggiungiAlbum.php",
        "Aggiungi Canzone" => "/selectartist.php",
        "Informazioni Brano" => "/addsong.php",
        "Canzone Aggiunta" => "/successaddsong.php",
        "Account" => "/Pages/account.php",
        "Account (Modifica)" => "/Pages/account?update=true.php",
        "Permessi insufficienti" => "/Pages/unallowed.php"
    ];
}
