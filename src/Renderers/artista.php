<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'components/navbar.php';
require_once 'components/breadcrumbs.php';
require_once 'include/sessionEstablisher.php';
require_once 'components/validator.php';
require_once 'include/utils.php';
require_once 'include/pages.php';
require_once 'include/database.php';

const BASE_DIR_IMAGES = '../assets/artistPhotos/';

// ==================================================================================================
// READ ARTIST
// ==================================================================================================

$get_artist = function () {
  echo 'GET artist';
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

  (new Pangine\PangineValidator(
    "GET",
    [
      "id" => new Pangine\PangineValidatorConfig(
        notEmpty: true,
        minVal: 0
      )
    ]
  ))->validate(pages['Catalogo']);

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
      ->set("nome-value", $artist_name)
      ->set("page-form", pages['Modifica Artista'])
      ->set("biografia-value", $biography)
      ->set("id-value", $artist_id)
      ->clean("-message")
      ->clean("-value")))
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
  $expectedParameters = array(
    "id" => (new Pangine\PangineValidatorConfig(
      notEmpty: true,
      minVal: 0
    ))
  );
  $validator = new Pangine\PangineValidator("GET", $expectedParameters);
  $validator->validate(pages['Catalogo']);
  $id = $_GET['id'];

  $db = new Database();
  $img = $db->fetch_artist_info_by_id($id)['file_name'];
  $db->delete_artist($id);
  $db->close();

  if (file_exists(BASE_DIR_IMAGES . $img))
    unlink(BASE_DIR_IMAGES . $img);

  redirect(pages['Catalogo']);
};
