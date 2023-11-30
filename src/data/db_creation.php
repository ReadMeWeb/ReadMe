<?php
function db_create() {
    $host = "mysql"; // Può essere diverso a seconda della configurazione del tuo ambiente Docker
    $username = "root";
    $password = "admin";
    $database = "Orchestra";

    // Connessione al database
    $conn = mysqli_connect($host, $username, $password, $database);

    $status = true; # status a true per connessione riuscita

    // Verifica la connessione
    if ($conn->connect_error) {
        $status = false; # status a false per connessione fallita
    }

    return [$conn, $status];
}