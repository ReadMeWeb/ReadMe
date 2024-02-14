<?php
include_once "handlers/manageSong.php";
include_once "components/sessionEstablisher.php";
include_once "data/database.php";
include_once "handlers/url_utils.php";
include_once "components/breadcrumbs/breadcrumbsBuilder.php";
include_once "components/breadcrumbs/breadcrumbs.php";
include_once "components/breadcrumbs/breadcrumbItem.php";
include_once "handlers/errors_utils.php";
include_once "components/navbar.php";

try_session();

// TODO testing
$_SESSION["user"]["status"] = "ADMIN";

if (!sessionUserIsAdmin()) {
    requestError();
}

$db = new database();

if($_SERVER["REQUEST_METHOD"] == "GET"){
    if(isset($_GET["artist_id"])){
        showDetailsFormOrGoBackToArtistSelection($db, $_GET["artist_id"]);
    }elseif(isset($_GET["song_id"])){
        showEditFormOrGoBackToCatalog($db, $_GET["song_id"]);
    }elseif(isset($_GET["success_add"]) && $_GET["success_add"] == "true"){
        showSuccessMessage("add");
    }elseif(isset($_GET["success_edit"]) && $_GET["success_edit"] == "true"){
        showSuccessMessage("edit");
    }elseif(isset($_GET["success_delete"]) && $_GET["success_delete"] == "true"){
        showSuccessMessage("delete");
    }else{
        showSelectArtistForm($db);
    }
}elseif($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST["add"]) && $_POST["add"] == "yes"){
        handleAddSong($db, $_POST);
    }elseif(isset($_POST["edit"]) && $_POST["edit"] == "yes"){
        handleEditSong($db, $_POST);
    }elseif(isset($_POST["song_id"])){
        // TODO eliminazione canzone a database
        echo "elimina canzone database";
    }else{
        requestError();
    }
}else{
    requestError();
}