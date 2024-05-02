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

function validator_loan() {
  return stream(
    _new_validator('/marango/Pages/loans.php'),
    _add_parametre('inizio', _string()),
    _add_parametre('fine', _string()),
  );
}

(new Pangine())
  ->add_renderer_POST(function ($conn) {
  }, needs_database: true)
  ->add_renderer_GET(function ($conn) {
    echo (new LayoutBuilder())
      ->tag_lazy_replace('title', 'Prestito libri')
      ->tag_lazy_replace('description', 'Pagina di prestito di un libro della biblioteca di ReadMe')
      ->tag_lazy_replace('keywords', 'ReadMe, biblioteca, libri, prestiti')
      ->tag_lazy_replace('menu', Pangine::navbar_list())
      ->tag_lazy_replace('breadcrumbs', Pangine::breadcrumbs_generator(array('Home', 'Libri', 'Prestito')))
      ->tag_istant_replace('content', file_get_contents(__DIR__ . '/../templates/make_loan_content.html'))

      ->tag_lazy_replace('libro-value',   $_GET['libro'])
      ->tag_lazy_replace('libro-titolo',    $conn->execute_query('select title as t from Books where id = ?', $_GET['libro'])[0]['t'])
      ->tag_lazy_replace('user-value', _username())

      ->tag_lazy_replace('inizio-value', '')
      ->tag_lazy_replace('inizio-message', '')
      ->tag_lazy_replace('fine-value', '')
      ->tag_lazy_replace('fine-message', '')
      ->build();
  }, caller_parameter_name: 'libro', needs_database: true)
  ->add_renderer_GET(function ($conn) {
    echo (new LayoutBuilder())
      ->tag_lazy_replace('title', 'Prestito libri')
      ->tag_lazy_replace('description', 'Pagina di prenotazione di un libro della biblioteca di ReadMe')
      ->tag_lazy_replace('keywords', 'ReadMe, biblioteca, libri, prestiti')
      ->tag_lazy_replace('menu', Pangine::navbar_list())
      ->tag_lazy_replace('breadcrumbs', Pangine::breadcrumbs_generator(array('Home', 'Libri')))
      ->tag_istant_replace('content', file_get_contents(__DIR__ . '/../templates/loanable_books_content.html'))

      ->tag_lazy_replace('libri', stream(
        $conn->execute_query('select b.id as id, b.title as title from Books as b inner join active_loans as a on b.id = a.book_id'),
        _map(fn ($libro) => sprintf('<a href="?libro=%d"><img src="" alt="">%s</a>',$libro['id'],$libro['title'])),
        _implode("\n"),
      ))
      ->build();
  }, needs_database: true)
  ->execute();
