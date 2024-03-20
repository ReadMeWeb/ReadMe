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

  echo (new HTMLBuilder('../components/addSong/selectArtist.html'))
    ->set('title', "Aggiungi Canzone - Selezione Artista")
    ->set('menu', navbar())
    ->set('breadcrumbs', arraybreadcrumb(['Home', 'Aggiungi Canzone']))
    ->set('content', (new HTMLBuilder('../components/addSong/selectArtist.html'))
      ->set('artists', getArtistSelectionContent(dbcall(fn ($db) => $db->fetch_artist_info())))
      ->build())
    ->build();
};

$post_create_song = function () {
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

  // custom validation functions
  $check_album_belongs_to_artist = function () use ( $artist_id, $album_id): string {
    $db = new Database();
    if ( $album_id == "NULL" || $db->check_album_belong_to_artist($artist_id, $album_id)) {
      $db->close();
      return "";
    } else {
      $db->close();
      return "L'album richiesto non appartiene all'artista selezionato.";
    }
  };

  $expectedParameters = [
    "title" => new Pangine\PangineValidatorConfig(
      notEmpty: true,
      minLength: 4,
      maxLength: 40
    ),
    "artist_id" => new Pangine\PangineValidatorConfig(notEmpty: true),
    "album" => new Pangine\PangineValidatorConfig(
      notEmpty: true,
      customFunction: $check_album_belongs_to_artist
    ),
  ];

  $validator = new Pangine\PangineValidator("POST", $expectedParameters);
  $validator->validate(
    "/Pages/addSong.php?create=true&artist_id=" . $artist_id
  );

  /* Try update file */
  $result_graphic = move_uploaded_file(
    $tmp_graphic["tmp_name"],
    $uploadFileG
  );
  if ($result_graphic) {
    $result_audio = move_uploaded_file(
      $tmp_audio["tmp_name"],
      $uploadFileA
    );
    if (!$result_audio) {
      unlink($uploadFileG);
      redirect(pages['Catalogo']);
    }
  } else {
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

$get_create_song = function () {
  (new Pangine\PangineAuthenticator())->authenticate(["ADMIN"]);

  $layout = file_get_contents("../components/layoutLogged.html");
  $title = "Aggiungi Canzone - Informazioni Canzone";
  $navbar = navbar();
  $breadcrumbs = arraybreadcrumb(['Home', 'Aggiungi Canzone', 'Informazioni Canzone']);
  $content = file_get_contents("../components/addSong/addSong.html");

  $artist_id = $_GET["artist_id"];

  $db = new Database();
  $artist_fetch_array = $db->fetch_artist_info_by_id($artist_id);
  $albums_fetch_array = $db->fetch_albums_info_by_artist_id($artist_id);
  $db->close();

  $albums = getAlbumsSelectionContent($albums_fetch_array);

  $layout = str_replace("{{content}}", $content, $layout);

  $html_builder = (new Pangine\PangineUnvalidFormManager(
    $layout
  ))->getHTMLBuilder();
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

$post_update_song = function () {
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

  $expectedParameters = [
    "title" => new Pangine\PangineValidatorConfig(
      notEmpty: true,
      minLength: 4,
      maxLength: 40
    ),
    "artist_id" => new Pangine\PangineValidatorConfig(notEmpty: true),
    "song_id" => new Pangine\PangineValidatorConfig(notEmpty: true),
  ];

  $validator = new Pangine\PangineValidator("POST", $expectedParameters);
  $validator->validate(
    "/Pages/addSong.php?producer=" .
      $artist_id .
      "&name=" .
      $song_title .
      "&update=Modifica"
  );

  $db = new Database();
  $song_old = $db->fetch_song_info_by_id($song_id);
  $db->close();

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
      header("Location: /Pages/catalogo.php");
    } else {
      //TODO error 500
    }
  }
};

$get_update_song = function () {
  (new Pangine\PangineAuthenticator())->authenticate(["ADMIN"]);

  $layout = file_get_contents("../components/layoutLogged.html");
  $title = "Modifica Canzone";
  $navbar = navbar();
  $breadcrumbs = (new BreadcrumbsBuilder())
    ->addBreadcrumb(new BreadcrumbItem("Home"))
    ->addBreadcrumb(new BreadcrumbItem("Modifica Canzone", isCurrent: true))
    ->build()
    ->getBreadcrumbsHtml();
  $content = file_get_contents("../components/addSong/editSong.html");

  $song_id = $_GET["id"];
  $artist_id = $_GET["producer"];

  $db = new Database();
  $artist_fetch_array = $db->fetch_artist_info_by_id($artist_id);
  $all_artists_except_selected = $db->fetch_all_artists_except_the_following(
    $artist_id
  );
  $song_fetch_array = $db->fetch_song_info_by_id($song_id);
  $db->close();

  $artist_options = getArtistSelectionContent($all_artists_except_selected);
  $current_artist_option =
    "<option selected value=\"" .
    $artist_fetch_array["id"] .
    "\">" .
    $artist_fetch_array["name"] .
    "</option>";

  $layout = str_replace("{{content}}", $content, $layout);

  $html_builder = (new Pangine\PangineUnvalidFormManager(
    $layout
  ))->getHTMLBuilder();
  $html = $html_builder
    ->set("title", $title)
    ->set("menu", $navbar)
    ->set("breadcrumbs", $breadcrumbs)
    ->set("current-artist", $current_artist_option)
    ->set("artists", $artist_options)
    ->set("song_id-value", $song_fetch_array["id"])
    ->set("title-value", $song_fetch_array["name"])
    ->set("title-value2", $song_fetch_array["name"])
    ->set("current_audio_file", $song_fetch_array["audio_file_name"])
    ->set("current_graphic_file", $song_fetch_array["graphic_file_name"])
    ->clean("-message")
    ->clean("-value")
    ->build();
  echo $html;
};


$get_delete_song = function () {
  (new Pangine\PangineAuthenticator())->authenticate(["ADMIN"]);

  $layout = file_get_contents("../components/layoutLogged.html");
  $title = "Rimuovi Canzone";
  $navbar = navbar();
  $breadcrumbs = (new BreadcrumbsBuilder())
    ->addBreadcrumb(new BreadcrumbItem("Home"))
    ->addBreadcrumb(new BreadcrumbItem("Rimuovi Canzone", true))
    ->build()
    ->getBreadcrumbsHtml();
  $content = file_get_contents("../components/addSong/removeSong.html");

  $song_id = $_GET["id"];

  $db = new Database();
  $song = $db->fetch_song_info_by_id($song_id);
  $db->close();

  $layout = str_replace("{{content}}", $content, $layout);

  $html_builder = (new Pangine\PangineUnvalidFormManager(
    $layout
  ))->getHTMLBuilder();
  $html = $html_builder
    ->set("title", $title)
    ->set("menu", $navbar)
    ->set("breadcrumbs", $breadcrumbs)
    ->set("song-title", $song["name"])
    ->set("song-id", $song_id)
    ->build();
  echo $html;
};

$post_delete_song = function () {
  (new Pangine\PangineAuthenticator())->authenticate(["ADMIN"]);

  $song_id = $_POST["id"];

  $expectedParameters = [
    "id" => new Pangine\PangineValidatorConfig(notEmpty: true),
  ];

  $validator = new Pangine\PangineValidator("POST", $expectedParameters);
  $validator->validate("/Pages/addSong.php?id=" . $song_id . "&delete=true");

  $db = new Database();
  $song = $db->fetch_song_info_by_id($song_id);

  if ($song == null) {
    //TODO error 404
  } else {
    $result = $db->delete_song($song_id);
    $db->close();

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

      header("Location: /Pages/catalogo.php");
    } else {
      //TODO error 500
    }
  }
};
