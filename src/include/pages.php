<?php

const path = '/';

const pages = [
    'Home' => path . 'index.php',
    'Chi Siamo' => path . 'chisiamo.php',
    'Accedi' => path . 'Pages/accedi.php',
    'Registrati' => path . 'Pages/registrati.php',
    'Catalogo' => path . 'Pages/catalogo.php',
    'Aggiungi Artista' => path . 'Pages/artista.php?create=true',
    'Modifica Artista' => path . 'Pages/artista.php?update=true',
    'Albums' => path . 'Pages/albums.php',
    'Crea album' => path . 'Pages/album.php?create=true',
    'Ispeziona album' => path . '',
    'Modifica album' => path . '',
    'Aggiungi Canzone' => path . 'selectartist.php',
    'Informazioni Brano' => path . 'addsong.php',
    'Canzone Aggiunta' => path . 'successaddsong.php',
    'Account' => path . 'Pages/account.php',
    'Account (Modifica)' => path . 'Pages/account?update=true.php',
    'Permessi insufficienti' => path . 'Pages/unallowed.php'
  ];
