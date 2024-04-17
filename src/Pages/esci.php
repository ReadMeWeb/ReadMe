<?php

use Pangine\Pangine;

require_once __DIR__ . '/../Pangine/Pangine.php';

(new Pangine())
->add_renderer_GET(function () {
  $_SESSION['user'] = null;
  Pangine::redirect('Home');
})
  ->execute();
