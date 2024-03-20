<?php

require_once "./Pangine/Pangine.php";
require_once "./components/navbar.php";

(new \Pangine\Pangine())->GET_read(function () {
    (new \Pangine\PangineAuthenticator())->authenticate(array('UNREGISTERED', 'USER', 'ADMIN'));

    $htmlBuilder = (new HTMLBuilder('./components/500.html'))
        ->set('menu', navbar());
    if (isset($_SESSION['error500data'])) {
        $htmlBuilder->set(
            'link',
            '<a href="' . $_SESSION['error500data']['link'] . '">' . $_SESSION['error500data']['message'] . '</a>'
        );
        unset($_SESSION['error500data']);
    } else {
        $htmlBuilder->set('link', '<a href="/index.php">Ritorna alla home</a>');
    }
    echo $htmlBuilder->build();
})->execute();
