<?php

use Pangine\Pangine;
use \Pangine\utils\LayoutBuilder;

require_once __DIR__ . '/../Utils/ErroriMigliori.php';
require_once __DIR__ . '/../Utils/Stream.php';
require_once __DIR__ . '/../Utils/Database.php';
require_once __DIR__ . '/../Pangine/Pangine.php';
require_once __DIR__ . '/../Pangine/utils/LayoutBuilder.php';

const registrati = 'signup';

(new Pangine())
  //accedi
  ->add_renderer_POST(function () {
  }, needs_database: true)
  ->add_renderer_GET(function () {
    echo (new LayoutBuilder())
      ->tag_lazy_replace('title', 'Accedi')
      ->tag_lazy_replace('description', 'Pagina di accesso alla biblioteca di ReadMe')
      ->tag_lazy_replace('keywords', 'ReadMe, biblioteca, libri, narrativa, prenotazioni, accedi')
      ->tag_lazy_replace('menu', Pangine::navbar_list())
      ->tag_lazy_replace('breadcrumbs', Pangine::breadcrumbs_generator(array('Home', 'Accedi')))
      ->tag_istant_replace('content', '')
      ->build();
  })
  //registrati
  ->add_renderer_POST(function () {
  }, registrati, true)
  ->add_renderer_GET(function () {
  }, registrati, true)
  ->execute();
