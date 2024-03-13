<?php

class HTMLBuilderMultiplePlacehoderException extends Exception {
  public function __construct($filename, $code = 0, Throwable $previous = null) {
    parent::__construct("'$filename' contiene duplicati dello stesso marcatore.", $code, $previous);
  }
}

class HTMLBuilder {

  protected string $content;
  protected array $placeholders;
  private array $invalidplaceholders = [];

  function __construct(private string $htmlfile) {
    $this->content = file_get_contents($htmlfile);
    preg_match_all('/{{(.*?)}}/', $this->content, $matches, PREG_OFFSET_CAPTURE);

    $nomi = array_column($matches[1], 0);
    $contavalori = array_filter(array_count_values($nomi), fn ($n) => $n > 1);
    if (count($contavalori) > 0) {
      die("ERRORE HTMLBUILDER  '" . $this->htmlfile . "' <br><br>\n\n"
        . 'Sono stati trovati duplicati dei seguenti marcatori :' . "   <br> \n"
        . implode('', array_map(fn ($line) => " - $line    <br> \n", array_keys($contavalori))) . "\n<br>\n\n"
        . str_replace("\n", "   <br> \n", (new Exception())->getTraceAsString()));
    }

    $this->placeholders = array_map(
      fn ($a) => [$a, null],
      array_combine(
        $nomi,
        array_column($matches[0], 1),
      )
    );
    uasort($this->placeholders, fn ($a, $b) => ($a[0] > $b[0]) ? -1 : 1);
  }


  const UNSAFE = 0;
  const ERROR_P = 1;
  const SUCCESS_P = 2;

  function set($placeholder, $data, $type = HTMLBuilder::UNSAFE): HTMLBuilder {
    if (!array_key_exists($placeholder, $this->placeholders)) {
      $this->invalidplaceholders[] = $placeholder;
      return $this;
    }

    // TODO da estendere qual'ora fossero richiesti magheggi
    $this->placeholders[$placeholder][1] = match ($type) {
      HTMLBuilder::UNSAFE => $data,
      HTMLBuilder::ERROR_P => '<p class="error">' . htmlspecialchars($data) . '</p>',
      HTMLBuilder::SUCCESS_P => '<p class="success">' . htmlspecialchars($data) . '</p>',
    };

    return $this;
  }

  function build(): string {
    $errormessage = '';
    $unsetted = array_filter($this->placeholders, fn ($line) => $line[1] === null);
    if (count($unsetted) > 0) {
       $errormessage .= ('Non sono stati settati i sequenti marcatori ' . ": <br> \n"
        . implode('', array_map(fn ($line) => "- $line   <br> \n", array_keys($unsetted)))) . "\n<br>\n\n";
    }
    if (count($this->invalidplaceholders) > 0) {
      $errormessage .= 'Il file non contiene i seguenti marcatori:' . " <br> \n"
        . implode('', array_map(fn ($line) => "- $line    <br> \n", $this->invalidplaceholders)) . "\n<br>\n\n";
    }
    if ($errormessage !== '') {
      die( "ERRORE HTMLBUILDER  '".$this->htmlfile."' <br><br>\n\n"
        . $errormessage
        . str_replace("\n","   <br> \n",(new Exception())->getTraceAsString())
      );
    }

    foreach ($this->placeholders as $placeholder => $line) {
      [$offset, $replace] = $line;
      $this->content = substr_replace(
        $this->content,
        $replace,
        $offset,
        strlen($placeholder) + 4,
      );
    }

    return $this->content;
  }
};

class HTMLBuilderCleaner extends HTMLBuilder {
  function clean(string $substring): HTMLBuilder {
    foreach ($this->placeholders as $placeholder => $line) {
      if (str_contains($placeholder, $substring)) {
        $this->set($placeholder, '');
      }
    }
    return $this;
  }
}
