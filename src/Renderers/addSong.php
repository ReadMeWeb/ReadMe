<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'components/breadcrumbs.php';
require_once 'components/navbar.php';
require_once 'Pangine/Pangine.php';
require_once 'include/database.php';
require_once 'include/HTMLBuilder.php';
require_once 'include/utils.php';
require_once 'include/pages.php';

function escape_string($input): string {
  return htmlspecialchars($input, ENT_QUOTES, "UTF-8");
}

function getArtistSelectionContent(array $artists): string {
  return implode('', array_map(
    fn ($artist) => "<option value=\"" . $artist["id"] . "\">" . $artist["name"] . "</option>",
    $artists
  ));
}

function getAlbumsSelectionContent(array $albums): string {
  return implode('', array_map(
    fn ($album) => "<option value=\"" . $album["id"] . "\">" . $album["name"] . "</option>",
    $albums
  ));
}

$get_select_artist = function () {
  (new Pangine\PangineAuthenticator())->authenticate(["ADMIN"]);

  echo (new HTMLBuilder('../components/layoutLogged.html'))
    ->set('title', "Aggiungi Canzone - Selezione Artista")
    ->set('menu', navbar())
    ->set('breadcrumbs', arraybreadcrumb(['Home', 'Aggiungi Canzone']))
    ->set('content', (new HTMLBuilder('../components/addSong/selectArtist.html'))
      ->set('artists', getArtistSelectionContent(dbcall(fn ($db) => $db->fetch_artist_info())))
      ->build())
    ->build();
};

$validator_create = new Pangine\PangineValidator([
  "title" => new Pangine\PangineValidatorConfig(
    notEmpty: true,
    minLength: 4,
    maxLength: 40
  ),
  "artist_id" => new Pangine\PangineValidatorConfig(notEmpty: true),
  "album" => new Pangine\PangineValidatorConfig(
    notEmpty: true,
    //customFunction: function () use ($artist_id, $album_id): string {
    //  return dbcall(fn ($db) => ($album_id == "NULL" || $db->check_album_belong_to_artist($artist_id, $album_id))
    //    ? "" : "L'album richiesto non appartiene all'artista selezionato.");
    //}
  ),
]);

$post_create_song = function () use ($validator_create) {
  (new Pangine\PangineAuthenticator())->authenticate(["ADMIN"]);

  $artist_id = $_POST["artist_id"];
  $album_id = $_POST["album"];
  $song_title = $_POST["title"];
  $tmp_audio = $_FILES["audio_file"];
  $tmp_graphic = $_FILES["graphic_file"];

  // set up audio file
  $uploadDirA = dirname(__FILE__) . "/../assets/songAudios/";
  $fileNameA = str_replace(" ", "-", $song_title) . "_" . $artist_id . ".mp3";
  $uploadFileA = $uploadDirA . $fileNameA;

  // set up graphic file
  $uploadDirG = dirname(__FILE__) . "/../assets/songPhotos/";
  $fileNameG = str_replace(" ", "-", $song_title) . "_" . $artist_id . ".png";
  $uploadFileG = $uploadDirG . $fileNameG;

  // TODO usare pages
  $validator_create->validate(
    "/Pages/addSong.php?create=true&artist_id=" . $artist_id,
    $_POST
  );

  /* Try update file */
  $result_graphic = move_uploaded_file($tmp_graphic["tmp_name"], $uploadFileG);
  if (!$result_graphic) {
    redirect(pages['Catalogo']);
  }
  $result_audio = move_uploaded_file($tmp_audio["tmp_name"], $uploadFileA);
  if (!$result_audio) {
    unlink($uploadFileG);
    redirect(pages['Catalogo']);
  }

  $db = new Database();
  $result = $db->insert_song(
    $artist_id,
    $song_title,
    $fileNameA,
    $fileNameG,
    $album_id == "NULL" ? null : $album_id
  );
  $db->close();

  if ($result) {
    redirect(pages['Catalogo']);
  } else {
    unlink($uploadFileA);
    unlink($uploadFileG);
  }
};

$get_create_song = function () use ($validator_create) {
  (new Pangine\PangineAuthenticator())->authenticate(["ADMIN"]);

  $artist_id = $_GET["artist_id"];

  $db = new Database();
  $artist_fetch_array = $db->fetch_artist_info_by_id($artist_id);
  $albums_fetch_array = $db->fetch_albums_info_by_artist_id($artist_id);
  $db->close();

  $albums = getAlbumsSelectionContent($albums_fetch_array);

  echo (new HTMLBuilder("../components/layoutLogged.html"))
    ->set('title', "Aggiungi Canzone - Informazioni Canzone")
    ->set('menu', navbar())
    ->set('breadcrumbs', arraybreadcrumb(['Home', 'Aggiungi Canzone', 'Informazioni Canzone']))
    ->set('content', $validator_create->setformdata(
      (new HTMLBuilderCleaner("../components/addSong/addSong.html"))
        ->clean("-message")
        ->clean("-value")
        ->set("artist_name", $artist_fetch_array["name"])
        ->set("artist_id-value", $artist_id)
        ->set("albums", $albums)
    )->build())
    ->build();
};

$validator_update = new Pangine\PangineValidator([
  "title" => new Pangine\PangineValidatorConfig(
    notEmpty: true,
    minLength: 4,
    maxLength: 40
  ),
  //"artist_id" => new Pangine\PangineValidatorConfig(notEmpty: true),
  "song_id" => new Pangine\PangineValidatorConfig(notEmpty: true),
]);


$post_update_song = function () use ($validator_update) {
  (new Pangine\PangineAuthenticator())->authenticate(["ADMIN"]);

  $song_id = $_POST["song_id"];
  $artist_id = $_POST["artist_id"];
  $song_title = $_POST["title"];
  $tmp_audio = $_FILES["audio_file"];
  $tmp_graphic = $_FILES["graphic_file"];

  // set up audio file
  $uploadDirA = dirname(__FILE__) . "/../assets/songAudios/";
  $fileNameA = str_replace(" ", "-", $song_title) . "_" . $artist_id . ".mp3";
  $uploadFileA = $uploadDirA . $fileNameA;

  // set up graphic file
  $uploadDirG = dirname(__FILE__) . "/../assets/songPhotos/";
  $fileNameG = str_replace(" ", "-", $song_title) . "_" . $artist_id . ".png";
  $uploadFileG = $uploadDirG . $fileNameG;

  // TODO usare pages
  $validator_update->validate(
    "/Pages/addSong.php?update=true&id=" . $song_id,
    $_POST
  );

  $db = new Database();
  $song_old = $db->fetch_song_info_by_id($song_id);
  $db->close();

  // TODO aggiornare per utilizzare l'id
  /* Try update file */
  if ($tmp_graphic["size"] != 0) {
    unlink($song_old["graphic_file_name"]);
    $result_graphic = move_uploaded_file(
      $tmp_graphic["tmp_name"],
      $uploadFileG
    );
  }
  if ($tmp_graphic["size"] != 0) {
    unlink($song_old["audio_file_name"]);
    $result_audio = move_uploaded_file(
      $tmp_audio["tmp_name"],
      $uploadFileA
    );
  }

  if (
    ($tmp_graphic["size"] == 0 || $result_graphic) &&
    ($tmp_audio["size"] == 0 || $result_audio)
  ) {
    $db = new Database();
    $result = $db->update_song(
      $song_id,
      $artist_id,
      $song_title,
      $tmp_audio["size"] == 0 ? $song_old["audio_file_name"] : $fileNameA,
      $tmp_graphic["size"] == 0
        ? $song_old["graphic_file_name"]
        : $fileNameG,
      $artist_id == $song_old["producer"] ? $song_old["album"] : null
    );
    $db->close();

    if ($result) {
      redirect(pages['Catalogo']);
    } else {
      //TODO error 500
    }
  }
};

$get_update_song = function () use ($validator_update) {
  (new Pangine\PangineAuthenticator())->authenticate(["ADMIN"]);

  $db = new Database();
  $song_fetch_array = $db->fetch_song_info_by_id($_GET["id"]);
  $artist_options = $db->fetch_artists();
  $db->close();

  $artist_options = implode('', array_map(
    fn ($a) => $a['id'] == $song_fetch_array['producer']
      ? "<option selected value='" . $a['id'] . "'>" . $a['name'] . "</option>"
      : "<option value='" . $a['id'] . "'>" . $a['name'] . "</option>",
    $artist_options
  ));

  echo (new HTMLBuilder("../components/layoutLogged.html"))
    ->set('title', "Modifica Canzone")
    ->set('menu', navbar())
    ->set('breadcrumbs', arraybreadcrumb(['Home', 'Modifica Canzone']))
    ->set('content', $validator_update->setformdata(
      (new HTMLBuilderCleaner("../components/addSong/editSong.html"))
        ->clean("-message")
        ->clean("-value")
        ->set("artists", $artist_options)
        ->set("song_id-value", $song_fetch_array["id"])
        ->set("title-value", $song_fetch_array["name"])
        ->set("title-value2", $song_fetch_array["name"])
        ->set("current_audio_file", $song_fetch_array["audio_file_name"])
        ->set("current_graphic_file", $song_fetch_array["graphic_file_name"])
    )->build())
    ->build();
};

$validator_delete = new Pangine\PangineValidator([
  "id" => new Pangine\PangineValidatorConfig(notEmpty: true),
]);

$post_delete_song = function () use ($validator_delete) {
  (new Pangine\PangineAuthenticator())->authenticate(["ADMIN"]);

  $song_id = $_POST["id"];

  // TODO usare pages
  $validator_delete->validate("/Pages/addSong.php?delete=true&id=" . $song_id, $_POST);

  $db = new Database();
  $song = $db->fetch_song_info_by_id($song_id);

  if ($song == null) {
    //TODO error 404
  } else {
    $result = $db->delete_song($song_id);
    $db->close();

    // TODO aggiornare con gli id
    if ($result) {
      $audio_dir =
        dirname(__FILE__) .
        "/../assets/songAudios/" .
        $song["audio_file_name"];
      $graphic_dir =
        dirname(__FILE__) .
        "/../assets/songPhotos/" .
        $song["graphic_file_name"];
      if (file_exists($audio_dir)) {
        unlink($audio_dir);
      }
      if (file_exists($graphic_dir)) {
        unlink($graphic_dir);
      }

      redirect(pages['Catalogo']);
    } else {
      //TODO error 500
    }
  }
};

$get_delete_song = function () use ($validator_delete) {
  (new Pangine\PangineAuthenticator())->authenticate(["ADMIN"]);

  $song_id = $_GET["id"];

  $db = new Database();
  $song = $db->fetch_song_info_by_id($song_id);
  $db->close();

  echo (new HTMLBuilder("../components/layoutLogged.html"))
    ->set('title', 'Rimuovi Canzone')
    ->set('menu', navbar())
    ->set('breadcrumbs', arraybreadcrumb(['Home', 'Rimuovi Canzone']))
    ->set('content', $validator_delete->setformdata(
      (new HTMLBuilder("../components/addSong/removeSong.html"))
        ->set('song-title', $song['name'])
        ->set('song-id', $song_id)
    )->build())
    ->build();
};
