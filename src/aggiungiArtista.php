<?php
require_once 'components/navbar.php';
require_once 'components/breadcrumbs/breadcrumbItem.php';
require_once 'components/breadcrumbs/breadcrumbsBuilder.php';
require_once 'components/sessionEstablisher.php';
require_once 'data/database.php';
require_once 'components/validator.php';

try_session();

if($_SESSION['user']['status'] != 'ADMIN') {
    // TODO: pagina 404
    echo '<h1>Pagina non trovata</h1>';
    exit;
}

$biography = '';
$artist_image = '';
$artist_name = '';
$errors = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['nome_artista']) && isset($_POST['biografia']) && isset($_FILES['immagine_artista'])){

        $artist_image = trim($_FILES['immagine_artista']['tmp_name']);
        $artist_name = trim($_POST['nome_artista']);
        $biography = trim($_POST['biografia']);

    }
    else {
        // TODO: pagina 400
        http_response_code(400);
        echo "<h1>Richiesta malformata o troppo grande per essere gestista.</h1>";
        exit;
    }

    $errors .= Validator::check_input_dim($artist_name, 'nome artista', 0, 100) . Validator::check_input_dim($biography, 'biografia', 0) . Validator::check_input_img($artist_image, $_FILES['immagine_artista']['error']);

    if(empty($errors)) {
        $artist_name = htmlentities($artist_name);
        $biography = htmlentities($biography);
        $image_name = uniqid() . image_type_to_extension(exif_imagetype($artist_image));
        $image_dir = "./assets/artistPhotos/";

        if(!is_dir($image_dir)) {
            mkdir($image_dir);
        }
        rename($artist_image, $image_dir . $image_name);

        $db = new Database(); 
        $res = false;
        if($db->status()){
            $res = $db->insert_artist($artist_name, $biography, $image_name);
        }
        $db->close();

        if($res) {
            header('Location: catalogo.php');
            exit;
        }
        else {
            // TODO: pagina 500
            http_response_code(500);
            echo "<h1>Errore durante l'aggiunta dell'artista riprova o contatta l'amministratore del sito.</h1>";
        }
    }
}

// generazione contenuto statico della pagina
$keywords = implode(', ', array('Orchestra', 'aggiungi artista'));
$title = 'Aggiungi artista';
$menu = navbar();
$breadcrumbs = (new BreadcrumbsBuilder())
    ->addBreadcrumb(new BreadcrumbItem('Home'))
    ->addBreadcrumb(new BreadcrumbItem('Aggiungi Artista', isCurrent: true))
    ->build()
    ->getBreadcrumbsHtml();
$description = 'Aggiungi un nuovo artista al catalogo di Orchestra';

// generazione pagina di risposta
$content = file_get_contents('components/aggiungiArtista.html');

$place_holders = array('{{title}}', '{{keywords}}', '{{description}}', '{{content}}','{{menu}}','{{breadcrumbs}}', '{{errors}}', '{{artist_name}}', '{{biography}}', '{{artist_image}}');

$values = array($title, $keywords, $description, $content,$menu, $breadcrumbs, $errors, $artist_name, $biography, $artist_image);

$template = file_get_contents('components/layout.html');
$template = str_replace($place_holders, $values, $template);

echo $template;