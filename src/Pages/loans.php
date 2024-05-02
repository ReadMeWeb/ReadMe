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
  ->add_renderer_GET(function () {
    echo (new LayoutBuilder())
      ->tag_lazy_replace('title', 'Prestito libri')
      ->tag_lazy_replace('menu', Pangine::navbar_list())
      ->tag_lazy_replace('breadcrumbs', Pangine::breadcrumbs_generator(array('Home', 'a')))
      ->tag_istant_replace('content', file_get_contents(__DIR__ . '/../templates/make_loan_content.html'))

      ->tag_lazy_replace('libro-nome', $_GET['libro-nome'])
      ->tag_lazy_replace('libro-value', $_GET['libro-value'])
      ->tag_lazy_replace('user-value', _username())

      ->tag_lazy_replace('inizio-value', '')
      ->tag_lazy_replace('inizio-message', '')
      ->tag_lazy_replace('fine-value', '')
      ->tag_lazy_replace('fine-message', '')
      ->build();
  })
  ->execute();
