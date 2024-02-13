<?php


class HTMLBuilder {
  private $content;
  private $placeholders;

  function __construct(string $htmlfile) {
    $content = file_get_contents($htmlfile);
    preg_match_all('/{{(.*)}}/',$content,$placeholders);
  }

};


// print_r(new HTMLBuilder('../components/accedi.html'));
