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

$get_artist= function () {
    echo 'GET artist';
};

$get_edit_artist = function () {
    (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));

    (new Pangine\PangineValidator("GET",
      [ "id" => new Pangine\PangineValidatorConfig(
            notEmpty: true,
            minVal: 0)
      ]))->validate(pages['Catalogo']);

    $artist_id = $_GET['id'];

    $layout = (new HTMLBuilder('../components/layout.html'))
    ->set('keywords','Orchestra, modifica artista')
    ->set('title','Modifica artista')
    ->set('menu',navbar())
    ->set('breadcrumbs',arraybreadcrumb(['Home','Modifica Artista']))
    ->set('description','Modifica artista dal catalogo di Orchestra');

    $db = new Database(); 
    $artist = $db->fetch_artist_info_by_id($artist_id);
    $db->close();

    if(empty($artist)) {
        echo "Id invalido!";
        exit;
    }

    [$_, $artist_name, $biography, $artist_image] = array_values($artist);
    $alt_image = "Immagine artista $artist_name";
    
    $layout->set("content",file_get_contents('../components/modificaArtista.html'));

    $htmlBuilder = (new \Pangine\PangineUnvalidFormManager(new HTMLBuilderCleaner(layout: $layout->build())))->getHTMLBuilder();

    $layout = $htmlBuilder
        ->set('alt','Modifica artista dal catalogo di Orchestra')
        ->set("src", BASE_DIR_IMAGES . $artist_image)
        ->set("nome-value", $artist_name)
        ->set("page-form", pages['Modifica Artista'])
        ->set("biografia-value", $biography)
        ->set("id-value", $artist_id)
        ->clean("-message")
        ->clean("-value")
        ->build();

    echo $layout;

};

$get_create_artist = function () {
    (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));

    $keywords = implode(', ', array('Orchestra', 'modifica artista'));
    $title = 'Aggiungi artista';
    $menu = navbar();
    $breadcrumbs = (new BreadcrumbsBuilder())
        ->addBreadcrumb(new BreadcrumbItem('Home'))
        ->addBreadcrumb(new BreadcrumbItem('Aggiungi Artista', isCurrent: true))
        ->build()
        ->getBreadcrumbsHtml();
    $description = 'Aggiungi un artista dal catalogo di Orchestra';
    $layout = file_get_contents('../components/layout.html');
    $content = file_get_contents('../components/aggiungiArtista.html');
    $layout = str_replace("{{content}}",$content,$layout);

    $htmlBuilder = (new \Pangine\PangineUnvalidFormManager(new HTMLBuilderCleaner(layout: $layout)))->getHTMLBuilder();
    $layout = $htmlBuilder->set("title",$title)
        ->set("menu",$menu)
        ->set("breadcrumbs",$breadcrumbs)
        ->set("description", $description)
        ->set("keywords", $keywords)
        ->set("page-form", pages['Aggiungi Artista'])
        ->clean("-message")
        ->clean("-value")
        ->build();
    echo $layout;
};

$post_edit_artist = function () {

    (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));
    $expectedParameters = array(
        "id"=> (new Pangine\PangineValidatorConfig(
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
    );
    $validator = new Pangine\PangineValidator("POST",$expectedParameters);
    $validator->validate(pages['Modifica Artista']."&id={$_POST['id']}");

    [$artist_name, $biography, $id] = array_values($_POST);

    $db = new Database();
    $img = $db->fetch_artist_info_by_id($id)['file_name'];

    if($_FILES['immagine']['tmp_name'] != '') {
        $old_img = $img;
        $tmp_name = $_FILES['immagine']['tmp_name'];
        $name = $_FILES['immagine']['name'];
        $img = upload_file(BASE_DIR_IMAGES, $tmp_name, $name);
        if(file_exists(BASE_DIR_IMAGES . $old_img))
            unlink(BASE_DIR_IMAGES . $old_img);
    }

    $db->update_artist($id, $artist_name, $biography, $img);
    $db->close();
    redirect(pages['Catalogo']);

};

$post_add_artist = function () {
    (new Pangine\PangineAuthenticator())->authenticate(array("ADMIN"));
    $expectedParameters = array(
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
    );
    $validator = new Pangine\PangineValidator("POST",$expectedParameters);
    $validator->validate(pages['Aggiungi Artista']);

    $tmp_name = $_FILES['immagine']['tmp_name'];
    $name = $_FILES['immagine']['name'];
    $img = upload_file(BASE_DIR_IMAGES, $tmp_name, $name);
    [$artist_name, $biograph] = array_values($_POST);

    $db = new Database();
    $db->insert_artist($artist_name, $biograph, $img);
    $db->close();

    redirect(pages['Catalogo']);


};

$get_delete_artist = function() {
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

    if(file_exists(BASE_DIR_IMAGES . $img))
        unlink(BASE_DIR_IMAGES . $img);

    redirect(pages['Catalogo']);
};
