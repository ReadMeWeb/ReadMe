<?php

require_once "../components/sessionEstablisher.php";
require_once "../components/errorePermessi.php";
require_once "../components/breadcrumbs/breadcrumbsBuilder.php";
require_once "../components/breadcrumbs/breadcrumbItem.php";
require_once "../components/navbar.php";
require_once "../data/database.php";
require_once "errors_utils.php";
require_once "url_utils.php";

function sessionUserIsAdmin(): bool
{
    return $_SESSION["user"]["status"] == "ADMIN";
}

try_session();

$title = "Aggiungi Canzone";
$description = "Pagina per l'inserimento di una nuova canzone all'interno di Orchestra";
$keywords = implode(", ", array("Orchestra", "Aggiungi Canzone", "Nuova Canzone", "Canzone", "Inserimento"));
$breadcrumbs = (new BreadcrumbsBuilder())
    ->addBreadcrumb(new BreadcrumbItem("Home"))
    ->addBreadcrumb(new BreadcrumbItem("Aggiungi Canzone", true))
    ->build();

if (!sessionUserIsAdmin()) {
    echo getPermissionDeniedPage($title, $description, $keywords, $breadcrumbs);
    return;
}

$db = new Database();
$is_error = false;
$artist_id = 0;
$album_id = 0;
$song_title = "";
$sessionData = [];

if(!isset($_POST["artist_id"])){
    // solamente se qualcuno modifica l'input nascosto manualmente
    $is_error = true;
    setErrorStringToSession("sel_artist", "Non è stato selezionato alcun artista. Riprova, selezionandolo dal menu sottostante.");
}else{
    $artist_id = $_POST["artist_id"];
    $artist_info = $db->fetch_artist_info_by_id($artist_id);
    $sessionData["artist_id"] = $artist_id;
    if($artist_info == null){
        $is_error = true;
        setErrorStringToSession("sel_artist", "L'artista selezionato non è stato trovato. Riprova, selezionandolo dal menù sottostante.");
    }
}


if(!isset($_POST["album"])){
    $is_error = true;
    setErrorStringToSession("new_song_error", "Non è stato selezionato alcun artista. Riprova, selezionandolo dal menù sottostante.");
}else{
    $album_id = $_POST["album"];
    $sessionData["album_id"] = "";
    if($album_id != "NULL"){
        $album_belong_to_artist = $db->check_album_belong_to_artist($artist_id, $album_id);
        if(!$album_belong_to_artist){
            $is_error = true;
            setErrorStringToSession("new_song_error", "L'album non appartiene all'artista selezionato, per tanto non è possibile inserire la canzone.");
        }
    }
}

$uploadFileA = "";
$uploadFileG = "";
$fileNameA = "";
$fileNameG = "";

if(!isset($_POST["title"])){
    $is_error = true;
    setErrorStringToSession("new_song_error", "Il titolo della canzone è obbligatorio.");
}else{
    $song_title = trim($_POST["title"]);
    if(strlen($song_title) <= 3){
        $is_error = true;
        setErrorStringToSession("new_song_error", "Il titolo della canzone non può essere più corto di 4 caratteri.");
    }else{
        $sessionData["song_title"] = $song_title;
        if($album_id != 0 && $artist_id != 0){
            if($_FILES['audio_file']['type'] != "audio/mpeg"){
                $is_error = true;
                setErrorStringToSession("new_song_error", "Il file audio deve essere in formato mp3.");
            }else{
                $uploadDirA = dirname(__FILE__) . "/../assets/Audio/";
                $fileNameA = str_replace(" ", "-", $song_title) . "_" . $artist_id . ".mp3";
                $uploadFileA = $uploadDirA . $fileNameA;

                if (!move_uploaded_file($_FILES['audio_file']['tmp_name'], $uploadFileA)) {
                    $is_error = true;
                    setErrorStringToSession("new_song_error", "Il file audio non è stato caricato correttamente.");
                }
            }
            if($_FILES['graphic_file']['type'] != "image/png"){
                $is_error = true;
                setErrorStringToSession("new_song_error", "Il file grafico deve essere in formato png.");
            }else{
                $uploadDirG = dirname(__FILE__) . "/../assets/images/";
                $fileNameG = str_replace(" ", "-", $song_title) . "_" . $artist_id . ".png";
                $uploadFileG = $uploadDirG . $fileNameG;

                if (!move_uploaded_file($_FILES['graphic_file']['tmp_name'], $uploadFileG)) {
                    $is_error = true;
                    setErrorStringToSession("new_song_error", "Il file grafico non è stato caricato correttamente.");
                }
            }
        }
    }
}

$_SESSION["saved_data"] = $sessionData;

if($is_error){
    if(file_exists($uploadFileA)) unlink($uploadFileA);
    if(file_exists($uploadFileG)) unlink($uploadFileG);
    $db->close();
    header("Location: " . UrlUtils::getUrl("addsong.php"));
    die();
}else{
    if(!$db->insert_song($artist_id, $song_title, $fileNameA, $fileNameG, $album_id == "NULL" ? null : $album_id)){
        if(file_exists($uploadFileA)) unlink($uploadFileA);
        if(file_exists($uploadFileG)) unlink($uploadFileG);
        setErrorStringToSession("new_song_error", "Errore durante il salvataggio nel database della canzone. Riprovare.");
        header("Location: " . UrlUtils::getUrl("addsong.php"));
        $db->close();
        die();
    }else{
        header("Location: " . UrlUtils::getUrl("successAddSong.php"));
        $db->close();
        die();
    }
}