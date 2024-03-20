<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'components/navbar.php';
require_once 'components/breadcrumbs.php';
require_once 'include/sessionEstablisher.php';
require_once 'components/validator.php';
require_once 'components/album.php';
require_once 'components/song.php';
require_once 'include/utils.php';
require_once 'include/pages.php';
require_once 'include/database.php';

const BASE_DIR_IMAGES = '../assets/artistPhotos/';
// ==================================================================================================
// READ ARTIST
// ==================================================================================================
$validator_view = new Pangine\PangineValidator(array(
  "id"=> (new Pangine\PangineValidatorConfig(
      notEmpty: true,
      minVal: 0
  ))
));

$get_artist = function ()  use ($validator_view){
  (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN", "USER"));

  $validator_view->validate(pages["404"], $_GET);
  $id = $_GET['id'];

  $db = new Database();

  if(!$db->status()){
    throw new Pangine\PangineError500(
      pages['Artista'] . "&id={$id}", 
      "Errore durante la connessione con il databse."
    );
  }

  $artist = $db->fetch_artist_info_by_id($id);

  if($artist == null) {
    $db->close();
    redirect(pages['404']);
  }

  $albums = $db->fetch_albums_info_by_artist_id($id);
  $songs = $db->fetch_songs_info_by_artist_id($id); 
  $db->close();

  [$_, $name, $biography] = array_values($artist);

  $albums_list = "";
    foreach ($albums as $album){
        $albums_list .= (new album(
            $album["id"],
            $album["name"],
            $id
        ))->toHtml();
    }

    $songs_list = "";
    foreach ($songs as $song){
        $songs_list .= (new song(
            $id,
            $name,
            $song["name"],
            $song["audio_file_name"],
            $song["graphic_file_name"],
        ))->toHtml(false);
    }

    echo (new HTMLBuilder('../components/layout.html'))
    ->set('keywords', 'Orchestra, visualizza artista')
    ->set('title', 'Visualizza artista')
    ->set('menu', navbar())
    ->set('breadcrumbs', arraybreadcrumb(['Home', 'Catalogo', 'Artista']))
    ->set('description', "Pagina artista $name")
    ->set("content", 
        (new HTMLBuilder('../components/getArtista.html'))
        ->set('nome', $name)
        ->set('src', BASE_DIR_IMAGES . $id)
        ->set('alt', "Immagine artista $name")
        ->set('singoli', empty($songs_list) ? "$name deve ancora pubblicare dei singoli." : $songs_list)
        ->set('album', empty($albums_list) ? "$name deve ancora pubblicare un album." : $albums_list)
        ->set('biografia', $biography)
        ->build()
    )
    ->build();
};

// ==================================================================================================
// UPDATE ARTIST
// ==================================================================================================

$validator_edit = new Pangine\PangineValidator(array(
  "id" => (new Pangine\PangineValidatorConfig(
    notEmpty: true,
    minVal: 0
  )),
  "nome" => (new Pangine\PangineValidatorConfig(
    notEmpty: true,
    minLength: 5,
    maxLength: 150
  )),
  "biografia" => (new Pangine\PangineValidatorConfig(
    notEmpty: true,
    minLength: 20
  )),
  "immagine" => (new Pangine\PangineValidatorConfig(
    isImage: true
  ))
));

$post_edit_artist = function () use ($validator_edit) {

  (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));

  $validator_edit->validate(pages['Modifica Artista'] . '&id=' . $_POST['id'], $_POST);

  [$artist_name, $biography, $id] = array_values($_POST);

  $db = new Database();
  $img = $db->fetch_artist_info_by_id($id)['file_name'];

  if ($_FILES['immagine']['tmp_name'] != '') {
    $old_img = $img;
    $tmp_name = $_FILES['immagine']['tmp_name'];
    $name = $_FILES['immagine']['name'];
    $img = upload_file(BASE_DIR_IMAGES, $tmp_name, $name);
    if (file_exists(BASE_DIR_IMAGES . $old_img))
      unlink(BASE_DIR_IMAGES . $old_img);
  }

  $db->update_artist($id, $artist_name, $biography, $img);
  $db->close();
  redirect(pages['Catalogo']);
};

$get_edit_artist = function () use ($validator_edit) {
  (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));

  if (!array_key_exists('id', $_GET)) {
    redirect(pages['Catalogo']);
  }

  $artist_id = $_GET['id'];

  $artist = dbcall(fn ($db) => $db->fetch_artist_info_by_id($artist_id));

  if (empty($artist)) {
    echo "Id invalido!";
    exit;
  }

  [$_, $artist_name, $biography, $artist_image] = array_values($artist);

  echo (new HTMLBuilder('../components/layout.html'))
    ->set('keywords', 'Orchestra, modifica artista')
    ->set('title', 'Modifica artista')
    ->set('menu', navbar())
    ->set('breadcrumbs', arraybreadcrumb(['Home', 'Modifica Artista']))
    ->set('description', 'Modifica artista dal catalogo di Orchestra')
    ->set("content", (($validator_edit->setformdata((new HTMLBuilderCleaner('../components/modificaArtista.html'))
      ->set('alt', "Immagine artista $artist_name")
      ->set("src", BASE_DIR_IMAGES . $artist_image)
      ->set("page-form", pages['Modifica Artista'])
      ->set("biografia-value", $biography)
      ->set("nome-value", $artist_name)
      ->set("id-value", $artist_id)
      ->set("immagine-value", '')
      ->clean("-message")))
      ->build()))
    ->build();
};

// ==================================================================================================
// CREATE ARTIST
// ==================================================================================================

$validator_create = new Pangine\PangineValidator(array(
  "nome" => (new Pangine\PangineValidatorConfig(
    notEmpty: true,
    minLength: 5,
    maxLength: 150
  )),
  "biografia" => (new Pangine\PangineValidatorConfig(
    notEmpty: true,
    minLength: 20
  )),
  "immagine" => (new Pangine\PangineValidatorConfig(
    notEmpty: true,
    isImage: true
  ))
));

$post_add_artist = function () use ($validator_create) {
  (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));
  $validator_create->validate(pages['Aggiungi Artista'], $_POST);

  $tmp_name = $_FILES['immagine']['tmp_name'];
  $name = $_FILES['immagine']['name'];
  $img = upload_file(BASE_DIR_IMAGES, $tmp_name, $name);
  [$artist_name, $biograph] = array_values($_POST);

  $db = new Database();
  $db->insert_artist($artist_name, $biograph, $img);
  $db->close();

  redirect(pages['Catalogo']);
};

$get_create_artist = function () use ($validator_create) {
  (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));

  echo (new HTMLBuilder('../components/layout.html'))
    ->set('title', 'Aggiungi artista')
    ->set('keywords', 'Orchestra, modifica artista')
    ->set('menu', navbar())
    ->set('breadcrumbs', arraybreadcrumb(['Home', 'Aggiungi Artista']))
    ->set('description', 'Aggiungi un artista dal catalogo di Orchestra')
    ->set('content', ($validator_create->setformdata((new HTMLBuilderCleaner('../components/aggiungiArtista.html'))
      ->set("page-form", pages['Aggiungi Artista'])
      ->clean('-value')
      ->clean('-message')))
      ->build())
    ->build();
};

// ==================================================================================================
// DELETE ARTIST
// ==================================================================================================

$get_delete_artist = function () {
  (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));

  if (!array_key_exists('id', $_GET)) {
    redirect(pages['Catalogo']);
  }

  $id = $_GET['id'];

  $db = new Database();
  $img = $db->fetch_artist_info_by_id($id)['file_name'];
  $db->delete_artist($id);
  $db->close();

  if (file_exists(BASE_DIR_IMAGES . $img))
    unlink(BASE_DIR_IMAGES . $img);

  redirect(pages['Catalogo']);
};
