<?php

use Pangine\Pangine;
use Pangine\utils\LayoutBuilder;

require_once __DIR__ . '/../Utils/Stream.php';
require_once __DIR__ . '/../Utils/Database.php';
require_once __DIR__ . '/../Utils/Accedi.php';
require_once __DIR__ . '/../Pangine/Pangine.php';
require_once __DIR__ . '/../Pangine/utils/LayoutBuilder.php';


function _username() {
  return $_SESSION['user']['username'];
}

function a($a, $f, $e) {
  return _add_parametre($a, _string(string_parser: fn ($i) => $f($i) ? '' : $e));
}

(new Pangine())
  ->add_renderer_POST(
    function ($conn) {

      stream(
        _new_validator('/marango/Pages/404.php'),
        _add_parametre('id', _string(string_parser: fn ($i) => $conn->execute_query('select count(*) = 1 as b from Books where id = ?', $i)[0]['b'] == 1 ? '' : 'Il libro non è stato trovato'))
      )->validate();

      stream(
        _new_validator('/marango/Pages/loan.php?id=' . $_POST['id']),
        a('inizio', fn ($i) => $i >= date('Y-m-d', time()), 'La data di inizio non può essere prima di oggi.'),
        a('fine',   fn ($i) => $i >= date('Y-m-d', time()), 'La data di fine non può essere prima di oggi.'),
        a('fine', fn ($i) => $_POST['inizio'] < 'i', 'La data di fine deve essere dopo la data di inizio.'),
      )->validate();

      $book_name = $conn->execute_query('select title from Books where id = ?', $_POST["id"])[0]['title'];

      $conn->execute_query('insert into Loans(book_id,user_username,loan_start_date,loan_expiration_date) values(?,?,?,?);', $_POST['id'], _username(), $_POST['inizio'], $_POST['fine']);

      Pangine::set_general_message("Noleggio di '". $book_name ."' avvenuto con successo!","succ");
      Pangine::redirect('Prestiti');
      exit();
    },
    needs_database: true
  )
  ->add_renderer_GET(
    function ($conn) {

      stream(
        _new_validator('/marango/Pages/404.php'),
        _add_parametre('id', _string(string_parser: fn ($i) => $conn->execute_query('select count(*) = 1 as b from Books where id = ?', $i)[0]['b'] == 1 ? '' : 'Il libro non è stato trovato'))
      )->validate();

      echo (new LayoutBuilder())
        ->tag_lazy_replace('title', 'Prestito libri')
        ->tag_lazy_replace('description', 'Pagina di noleggio di un libro della biblioteca di ReadMe')
        ->tag_lazy_replace('keywords', 'ReadMe, biblioteca, libri, noleggi')
        ->tag_lazy_replace('menu', Pangine::navbar_list())
        // TODO sarebbe ideale avere un modo per passare le chiamate get ai link delle breadcrumbs per tornare alla pagina del libro
        ->tag_istant_replace('breadcrumbs', Pangine::breadcrumbs_generator(array('Home', 'Catalogo', 'Libro', 'Noleggio')))
        ->plain_instant_replace('Pages/libro.php', 'Pages/libro.php?id=' . $_GET['id'])
        ->tag_istant_replace('content', file_get_contents(__DIR__ . '/../templates/make_loan_content.html'))

        ->tag_lazy_replace('libro-value',   $_GET['id'])
        ->tag_lazy_replace('libro-titolo',  $conn->execute_query('select title as t from Books where id = ?', $_GET['id'])[0]['t'])
        ->tag_lazy_replace('user-value', _username())

        ->tag_lazy_replace('inizio-value', $today = date('Y-m-d', time()))
        ->tag_lazy_replace('inizio-message', '')
        ->tag_lazy_replace('fine-value', $today)
        ->tag_lazy_replace('fine-message', '')
        ->build();
    },
    caller_parameter_name: 'id',
    needs_database: true
  )
  ->execute();
