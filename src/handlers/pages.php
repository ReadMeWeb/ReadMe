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
        "Aggiungi Canzone" => "/Pages/addSong.php",
        "Informazioni Canzone" => "/Pages/addSong.php?create=true",
        "Account" => "/Pages/account.php",
        "Account (Modifica)" => "/Pages/account?update=true.php",
        "Permessi insufficienti" => "/Pages/unallowed.php",
        "Modifica Canzone" => "/Pages/addSong.php?update=true",
    ];
}
