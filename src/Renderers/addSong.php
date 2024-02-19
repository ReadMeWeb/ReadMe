<?php

require_once "../components/breadcrumbs/breadcrumbsBuilder.php";
require_once "../components/breadcrumbs/breadcrumbItem.php";
require_once "../components/navbar.php";
require_once "../Pangine/Pangine.php";
require_once "../data/database.php";

function getArtistSelectionContent(array $artists): string
{
    $artists_list = "";
    foreach ($artists as $artist) {
        $artists_list .= "<option value=\"" . $artist["id"] . "\">" . $artist["name"] . "</option>";
    }
    return $artists_list;
}

function getAlbumsSelectionContent(array $albums): string
{
    $albums_list = "";
    foreach ($albums as $album) {
        $albums_list .= "<option value=\"" . $album["id"] . "\">" . $album["name"] . "</option>";
    }
    return $albums_list;
}

$get_select_artist = function () {
    (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));

    $layout = file_get_contents("../components/layoutLogged.html");
    $title = "Aggiungi Canzone - Selezione Artista";
    $navbar = navbar();
    $breadcrumbs = (new BreadcrumbsBuilder())
        ->addBreadcrumb(new BreadcrumbItem("Home"))
        ->addBreadcrumb(new BreadcrumbItem("Aggiungi Canzone", true))
        ->build()
        ->getBreadcrumbsHtml();
    $content = file_get_contents("../components/addSong/selectArtist.html");

    $db = new Database();
    $artists_list = $db->fetch_artist_info();
    $db->close();

    $artists = getArtistSelectionContent($artists_list);

    $layout = str_replace("{{content}}", $content, $layout);

    $layout = str_replace(
        array(
            "{{title}}",
            "{{menu}}",
            "{{breadcrumbs}}",
            "{{artists}}"
        ),
        array(
            $title,
            $navbar,
            $breadcrumbs,
            $artists
        ),
        $layout);
    echo $layout;
};

$get_create_song = function () {
    (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));

    $layout = file_get_contents("../components/layoutLogged.html");
    $title = "Aggiungi Canzone - Informazioni Canzone";
    $navbar = navbar();
    $breadcrumbs = (new BreadcrumbsBuilder())
        ->addBreadcrumb(new BreadcrumbItem("Home"))
        ->addBreadcrumb(new BreadcrumbItem("Aggiungi Canzone"))
        ->addBreadcrumb(new BreadcrumbItem("Informazioni Canzone", true))
        ->build()
        ->getBreadcrumbsHtml();
    $content = file_get_contents("../components/addSong/addSong.html");

    $artist_id = $_GET["artist_id"];

    $db = new Database();
    $artist_fetch_array = $db->fetch_artist_info_by_id($artist_id);
    $albums_fetch_array = $db->fetch_albums_info_by_artist_id($artist_id);
    $db->close();

    $albums = getAlbumsSelectionContent($albums_fetch_array);

    $layout = str_replace("{{content}}", $content, $layout);

    $html_builder = (new Pangine\PangineUnvalidFormManager($layout))->getHTMLBuilder();
    $html = $html_builder
        ->set("title", $title)
        ->set("menu", $navbar)
        ->set("breadcrumbs", $breadcrumbs)
        ->set("artist_name", $artist_fetch_array["name"])
        ->set("artist_id-value", $artist_id)
        ->set("albums", $albums)
        ->clean("-message")
        ->clean("-value")
        ->build();
    echo $html;
};

$post_add_song = function () {
    (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));

    $artist_id = $_POST["artist_id"];
    $album_id = $_POST["album"];
    $song_title = $_POST["title"];
    $tmp_audio = $_FILES["audio_file"];
    $tmp_graphic = $_FILES["graphic_file"];

    // set up audio file
    $uploadDirA = dirname(__FILE__) . "/../assets/Audio/";
    $fileNameA = str_replace(" ", "-", $song_title) . "_" . $artist_id . ".mp3";
    $uploadFileA = $uploadDirA . $fileNameA;

    // set up graphic file
    $uploadDirG = dirname(__FILE__) . "/../assets/images/";
    $fileNameG = str_replace(" ", "-", $song_title) . "_" . $artist_id . ".png";
    $uploadFileG = $uploadDirG . $fileNameG;

    // custom validation functions
    $check_album_belongs_to_artist = function () use ($artist_id, $album_id): string {
        $db = new Database();
        if ($album_id == "NULL" || $db->check_album_belong_to_artist($artist_id, $album_id)) {
            $db->close();
            return "";
        } else {
            $db->close();
            return "L'album richiesto non appartiene all'artista selezionato.";
        }
    };

    $try_update_graphic = function () use ($uploadFileG, $tmp_graphic): string {
        $result_graphic = move_uploaded_file($tmp_graphic['tmp_name'], $uploadFileG);
        if ($result_graphic) {
            return "";
        } else {
            return "Non è stato possibile caricare il file grafico richiesto a causa di un errore lato server.";
        }
    };

    $try_update_audio = function () use ($uploadFileA, $tmp_audio): string {
        $result_audio = move_uploaded_file($tmp_audio['tmp_name'], $uploadFileA);
        if ($result_audio) {
            return "";
        } else {
            return "Non è stato possibile caricare il file audio richiesto a causa di un errore lato server.";
        }
    };

    $expectedParameters = array(
        "title" => (new Pangine\PangineValidatorConfig(
            notEmpty: true,
            minLength: 4,
            maxLength: 40
        )),
        "artist_id" => (new Pangine\PangineValidatorConfig(
            notEmpty: true,
        )),
        "album" => (new Pangine\PangineValidatorConfig(
            notEmpty: true,
            customFunction: $check_album_belongs_to_artist
        )),
        //TODO: fix this fields validation as they are in $_FILES and not in $_GET or $_POST
        /*"audio_file"=>(new Pangine\PangineValidatorConfig(
            notEmpty: true,
            customFunction: $try_update_audio
        )),
        "graphic_file"=>(new Pangine\PangineValidatorConfig(
            notEmpty: true,
            isImage: true,
            customFunction: $try_update_graphic
        )),*/
    );

    $validator = new Pangine\PangineValidator("POST", $expectedParameters);
    $validator->validate("/Pages/addSong.php?create=true&artist_id=".$artist_id);
    //TODO if validation goes wrong, delete files if they exists

    $db = new Database();
    $result = $db->insert_song($artist_id, $song_title, $fileNameA, $fileNameG, $album_id == "NULL" ? null : $album_id);
    $db->close();

    if ($result) {
        header("Location: /Pages/catalogo.php");
    }
};
