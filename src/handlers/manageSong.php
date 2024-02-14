<?php

function sessionUserIsAdmin(): bool
{
    return $_SESSION["user"]["status"] == "ADMIN";
}
function requestError() : void {
    // TODO impossibile soddisfare la richiesta
    header("Location: " . UrlUtils::getUrl("catalogo.php"));
    die();
}

function showSelectArtistForm($db) : void {
    $title = "Artista - Aggiungi Brano - Orchestra";
    $breadcrumbs = (new BreadcrumbsBuilder())
        ->addBreadcrumb(new BreadcrumbItem("Home"))
        ->addBreadcrumb(new BreadcrumbItem("Aggiungi Brano", true))
        ->build();

    $artists = $db->fetch_artist_info();
    $db->close();

    $content = file_get_contents("components/manageSong/selectArtist.html");
    $artists_list = "";
    foreach ($artists as $artist) {
        $artists_list .= "<option value=\"" . $artist["id"] . "\">" . $artist["name"] . "</option>";
    }
    $content = str_replace("{{artists}}", $artists_list, $content);
    $errors = getAndClearErrorStringFromSession("sel_artist");
    if ($errors != null) $content = str_replace("{{errors}}", $errors, $content);
    else $content = str_replace("{{errors}}", "", $content);

    $layout = file_get_contents("components/layoutLogged.html");
    $placeholdersTemplates = array("{{title}}", "{{menu}}", "{{breadcrumbs}}", "{{content}}");
    $placeholdersValues = array($title, navbar(), $breadcrumbs->getBreadcrumbsHtml(), $content);

    $layout = str_replace($placeholdersTemplates, $placeholdersValues, $layout);

    echo $layout;
}

function showDetailsForm($db, $artist): void {
    $content = file_get_contents("components/manageSong/addSong.html");
    $albums_fetch_array = $db->fetch_albums_info_by_artist_id($artist["id"]);
    $albums_list = "";
    foreach ($albums_fetch_array as $album) {
        $albums_list .= "<option value=\"" . $album["id"] . "\">" . $album["name"] . "</option>";
    }
    $errors = getAndClearErrorStringFromSession("new_song_error");
    $insertSongTemplate = array("{{artist_name}}", "{{song_title}}","{{artist_id}}","{{albums}}", "{{errors}}");

    $title = "Dettagli - Aggiungi Brano - Orchestra";
    $breadcrumbs = (new BreadcrumbsBuilder())
        ->addBreadcrumb(new BreadcrumbItem("Home"))
        ->addBreadcrumb(new BreadcrumbItem("Aggiungi Brano"))
        ->addBreadcrumb(new BreadcrumbItem("Dettagli Brano", true))
        ->build();

    $insertSongValues = array($artist["name"], "", $artist["id"], $albums_list, $errors);
    $content = str_replace($insertSongTemplate, $insertSongValues, $content);
    $layout = file_get_contents("components/layoutLogged.html");
    $placeholdersTemplates = array("{{title}}", "{{menu}}", "{{breadcrumbs}}", "{{content}}");
    $placeholdersValues = array($title, navbar(), $breadcrumbs->getBreadcrumbsHtml(), $content);
    echo str_replace($placeholdersTemplates, $placeholdersValues, $layout);
}

function showDetailsFormOrGoBackToArtistSelection(Database $db, $artist_id): void {
    $artist = $db->fetch_artist_info_by_id($artist_id);
    if ($artist != null) {
        showDetailsForm($db, $artist);
    }else{
        setErrorStringToSession("sel_artist", "L'artista selezionato non è stato trovato. Riprova, selezionandolo dal menu sottostante.");
        $db->close();
        header("Location: " . UrlUtils::getUrl("managesong.php"));
        die();
    }
}
function showSuccessMessage(string $type): void {
    $content = file_get_contents("components/manageSong/success.html");
    $template = array("{{message}}");
    $values = array("Operazione effettuata con successo!");
    $title = "Successo - Gestione Brano - Orchestra";
    if($type == "add"){
        $breadcrumbs = (new BreadcrumbsBuilder())
            ->addBreadcrumb(new BreadcrumbItem("Home"))
            ->addBreadcrumb(new BreadcrumbItem("Aggiungi Brano", true))
            ->build();
    }elseif($type == "edit"){
        $breadcrumbs = (new BreadcrumbsBuilder())
            ->addBreadcrumb(new BreadcrumbItem("Home"))
            ->addBreadcrumb(new BreadcrumbItem("Modifica Brano", true))
            ->build();
    }else {
        $breadcrumbs = (new BreadcrumbsBuilder())
            ->addBreadcrumb(new BreadcrumbItem("Home"))
            ->addBreadcrumb(new BreadcrumbItem("Elimina Brano", true))
            ->build();
    }

    $content = str_replace($template, $values, $content);
    $layout = file_get_contents("components/layoutLogged.html");
    $placeholdersTemplates = array("{{title}}", "{{menu}}", "{{breadcrumbs}}", "{{content}}");
    $placeholdersValues = array($title, navbar(), $breadcrumbs->getBreadcrumbsHtml(), $content);
    echo str_replace($placeholdersTemplates, $placeholdersValues, $layout);
}

function handleAddSong($db, $post) {
    $is_error = false;
    $is_error_sel = false;
    $artist_id = 0;
    $album_id = 0;
    $song_title = "";

    if(!isset($post["artist_id"])){
        // solamente se qualcuno modifica l'input nascosto manualmente
        $is_error_sel = true;
        setErrorStringToSession("sel_artist", "Non è stato selezionato alcun artista. Riprova, selezionandolo dal menu sottostante.");
    }else{
        $artist_id = $post["artist_id"];
        $artist_info = $db->fetch_artist_info_by_id($artist_id);
        if($artist_info == null){
            $is_error_sel = true;
            setErrorStringToSession("sel_artist", "L'artista selezionato non è stato trovato. Riprova, selezionandolo dal menù sottostante.");
        }
    }


    if(!isset($post["album"])){
        $is_error = true;
        setErrorStringToSession("new_song_error", "Non è stato selezionato alcun artista. Riprova, selezionandolo dal menù sottostante.");
    }else{
        $album_id = $post["album"];
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

    if(!isset($post["title"])){
        $is_error = true;
        setErrorStringToSession("new_song_error", "Il titolo della canzone è obbligatorio.");
    }else{
        $song_title = trim($post["title"]);
        if(strlen($song_title) <= 3){
            $is_error = true;
            setErrorStringToSession("new_song_error", "Il titolo della canzone non può essere più corto di 4 caratteri.");
        }else{
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

    if($is_error_sel){
        if(file_exists($uploadFileA)) unlink($uploadFileA);
        if(file_exists($uploadFileG)) unlink($uploadFileG);
        $db->close();
        header("Location: " . UrlUtils::getUrl("managesong.php"));
    }elseif($is_error){
        if(file_exists($uploadFileA)) unlink($uploadFileA);
        if(file_exists($uploadFileG)) unlink($uploadFileG);
        $db->close();
        header("Location: " . UrlUtils::getUrl("managesong.php?artist_id=".$artist_id));
    }else{
        if(!$db->insert_song($artist_id, $song_title, $fileNameA, $fileNameG, $album_id == "NULL" ? null : $album_id)){
            if(file_exists($uploadFileA)) unlink($uploadFileA);
            if(file_exists($uploadFileG)) unlink($uploadFileG);
            setErrorStringToSession("new_song_error", "Errore durante il salvataggio nel database della canzone. Riprovare.");
            $db->close();
            header("Location: " . UrlUtils::getUrl("managesong.php?artist_id=".$artist_id));
        }else{
            $db->close();
            header("Location: " . UrlUtils::getUrl("managesong.php?success_add=true"));
        }
    }
}

function showEditFormOrGoBackToCatalog(Database $db, $song_id) {
    $song = $db->fetch_song_info_by_id($song_id);
    if ($song != null) {
        showEditForm($song);
    }else{
        setErrorStringToSession("sel_song", "La canzone selezionata per la modifica non è disponibile.");
        $db->close();
        header("Location: " . UrlUtils::getUrl("catalogo.php"));
        die();
    }
}

function showEditForm($song) {
    $content = file_get_contents("components/manageSong/editSong.html");
    $errors = getAndClearErrorStringFromSession("edit_song_error");
    $template = array("{{song_id}}","{{artist_name}}","{{song_title}}","{{album_name}}", "{{errors}}", "{{graphic_file_name}}", "{{audio_file_name}}");

    $title = "Modifica Brano - Orchestra";
    $breadcrumbs = (new BreadcrumbsBuilder())
        ->addBreadcrumb(new BreadcrumbItem("Home"))
        ->addBreadcrumb(new BreadcrumbItem("Catalogo"))
        ->addBreadcrumb(new BreadcrumbItem("Modifica Brano", true))
        ->build();

    $values = array($song["id"], $song["artist_name"], $song["song_name"], $song["album_name"], $errors, $song["graphic_file_name"], $song["audio_file_name"]);
    $content = str_replace($template, $values, $content);
    $layout = file_get_contents("components/layoutLogged.html");
    $placeholdersTemplates = array("{{title}}", "{{menu}}", "{{breadcrumbs}}", "{{content}}");
    $placeholdersValues = array($title, navbar(), $breadcrumbs->getBreadcrumbsHtml(), $content);
    echo str_replace($placeholdersTemplates, $placeholdersValues, $layout);
}