<?php
include("./data/db_creation.php");
[$conn,$status] = db_create();

$result = null;
if($status){
    $result = $conn->query("SELECT * FROM users_to_delete");
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
    if($status){
        if ($result->num_rows > 0) {
        // Stampa i dati di ogni utente
        while($row = $result->fetch_assoc()) {
            echo "ID: " . $row["id"] . " - Username: " . $row["username"] . " - Password: " . $row["password"] . "<br>";
        }
        } else {
            echo "Nessun utente trovato";
        }
    }else{
        echo "Connessione fallita al database";
    }
    $conn->close();
    ?>
    <audio controls>
        <source src="assets/Audio/0112_release_state.mp3" type="audio/mpeg">
        Your browser does not support the audio tag.
    </audio></body>
</html>