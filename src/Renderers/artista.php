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
    $db->close();
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

  [$name, $biography, $id] = array_values($_POST);

  $db = new Database();
  if(!$db->status()){
    $db_>close();
    throw new Pangine\PangineError500(
      pages['Modifica Artista'] . "&id={$id}", 
      "Errore durante la connessione con il databse."
    );
  }

  $artist = $db->fetch_artist_info_by_id($id);
  
  if($artist == null) {
    redirect(pages['404']);
  }

  $tmp_name = $_FILES['immagine']['tmp_name'];

  if ($tmp_name != '') {

    if(!is_dir(BASE_DIR_IMAGES)) 
      mkdir(BASE_DIR_IMAGES);

    $upload = move_uploaded_file($tmp_name, BASE_DIR_IMAGES . '/' . $id);

    if(!$upload) {
      $db->close();
      throw new Pangine\PangineError500(
        pages['Modifica Artista'] . "&id={$id}", 
        "Errore durante il salvataggio dell'immagine dell'artista"
      );
    }
  }


  $res = $db->update_artist($id, $name, $biography);
  $db->close();

  if(!$res) {
    throw new Pangine\PangineError500(
      pages['Modifica Artista'] . "&id={$id}", 
      "Errore durante la modifica dell'artista nel database"
    );
  }

  redirect(pages['Catalogo']);
};

$get_edit_artist = function () use ($validator_edit, $validator_view) {
  (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));

  $validator_view->validate(pages['404'], $_GET);
  $id = $_GET['id'];

  $db = new Database();

  if(!$db->status()){
    $db->close();
    throw new Pangine\PangineError500(
      pages['Modifica Artista'] . "&id={$id}", 
      "Errore durante la connessione con il databse."
    );
  }

  $artist = $db->fetch_artist_info_by_id($id);
  $db->close();

  if (empty($artist)) {
    redirect(pages['404']);
  }

  [$_, $name, $biography] = array_values($artist);

  echo (new HTMLBuilder('../components/layout.html'))
    ->set('keywords', 'Orchestra, modifica artista')
    ->set('title', 'Modifica artista')
    ->set('menu', navbar())
    ->set('breadcrumbs', arraybreadcrumb(['Home', 'Catalogo', 'Modifica Artista']))
    ->set('description', 'Modifica artista dal catalogo di Orchestra')
    ->set("content", (($validator_edit->setformdata((new HTMLBuilderCleaner('../components/modificaArtista.html'))
      ->set('alt', "Immagine artista $name")
      ->set("src", BASE_DIR_IMAGES . $id)
      ->set("page-form", pages['Modifica Artista'])
      ->set("biografia-value", $biography)
      ->set("nome-value", $name)
      ->set("id-value", $id)
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
  
  [$artist_name, $biography] = array_values($_POST);

  $db = new Database();

  if(!$db->status()){

    $db->close();

    throw new Pangine\PangineError500(
      pages['Aggiungi Artista'], 
      "Errore durante la connessione con il databse."
    );
  }

  $id = $db->insert_artist($artist_name, $biography);

  if(!$id) {

    $db->close();
    throw new Pangine\PangineError500(
      pages['Aggiungi Artista'], 
      "Errore durante l'inserimento dell'artista nel database"
    );
  }

  if(!is_dir(BASE_DIR_IMAGES))
    mkdir(BASE_DIR_IMAGES);

  $res = move_uploaded_file($_FILES['immagine']['tmp_name'], BASE_DIR_IMAGES . '/' . $id);

  if(!$res) {

    $db->delete_artist($id);
    $db->close();

    throw new Pangine\PangineError500(
      pages['Aggiungi Artista'], 
      "Errore durante il salvataggio dell'immagine"
    );
  }

  $db->close();
  redirect(pages['Catalogo']);
};

$get_create_artist = function () use ($validator_create) {
  (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));

  echo (new HTMLBuilder('../components/layout.html'))
    ->set('title', 'Aggiungi artista')
    ->set('keywords', 'Orchestra, aggiungi artista')
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

$post_delete_artist = function () use ($validator_view) {
  (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));
  $validator_view->validate(pages['404'], $_POST);

  $id = $_POST['id'];

  $db = new Database();

  if(!$db->status()){
    $db->close();
    throw new Pangine\PangineError500(
      pages['Catalogo'], 
      "Errore durante la connessione con il database."
    );
  }

  $artist = $db->fetch_artist_info_by_id($id);
  
  if($artist == null) {
    $db->close();
    redirect(pages['404']);
  }
  
  $db->delete_artist($id);
  $db->close();

  $img = BASE_DIR_IMAGES . $id;

  if (file_exists($img))
    unlink($img);

  redirect(pages['Catalogo']);
};
