<?php

class HTMLBuilderMissingPlaceholderException extends Exception {
  public function __construct($placeholder, $code = 0, Throwable $previous = null) {
    parent::__construct("'$placeholder' non è presente nell'html.", $code, $previous);
  }
}

class HTMLBuilderUndefinedPlaceholderException extends Exception {
  public function __construct($placeholder, $code = 0, Throwable $previous = null) {
    parent::__construct("'$placeholder' non è stato assegnato un valore valido.", $code, $previous);
  }
}

class HTMLBuilderMultiplePlacehoderException extends Exception {
  public function __construct($filename, $code = 0, Throwable $previous = null) {
    parent::__construct("'$filename' contiene duplicati dello stesso marcatore.", $code, $previous);
  }
}

class HTMLBuilder {
  private $content;
  private $placeholders;

  function __construct(string $htmlfile) {
    $this->content = file_get_contents($htmlfile);
    preg_match_all('/{{(.*)}}/', $this->content, $matches, PREG_OFFSET_CAPTURE);

    $nomi = array_column($matches[1], 0);
    if (count($nomi) != count(array_unique($nomi))) {
      throw new HTMLBuilderMultiplePlacehoderException($htmlfile);
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

  function set($placeholder, $data, $type = 'text'): HTMLBuilder {
    if (!array_key_exists($placeholder, $this->placeholders)) {
      throw new HTMLBuilderMissingPlaceholderException($placeholder);
    }

    // TODO da estendere qual'ora fossero richiesti magheggi
    $this->placeholders[$placeholder][1] = match ($type) {
      'text' => $data,
    };

    return $this;
  }

  function build(): string {
    foreach ($this->placeholders as $placeholder => $line) {
      [$offset, $replace] = $line;

      if ($replace === null) {
        throw new HTMLBuilderUndefinedPlaceholderException($placeholder);
      }

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
