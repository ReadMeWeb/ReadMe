<?php

use Pangine\Pangine;

require_once __DIR__ . '/../Utils/Stream.php';
require_once __DIR__ . '/../Utils/Database.php';
require_once __DIR__ . '/../Pangine/Pangine.php';

(new Pangine())
  //accedi
  ->add_renderer_POST(function () {
  })
  ->add_renderer_GET(function () {
  })
  //registrati
  ->add_renderer_POST(function () {
  })
  ->add_renderer_GET(function () {
  })
  ->execute();
