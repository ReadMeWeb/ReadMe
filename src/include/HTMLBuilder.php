<?php

class HTMLBuilderMissingPlaceholderException extends Exception
{
    public function __construct($placeholder,$content, $code = 0, Throwable $previous = null)
    {
        parent::__construct("'$placeholder' non è presente nell'html. Content: ".$content, $code, $previous);
    }
}

class HTMLBuilderUndefinedPlaceholderException extends Exception
{
    public function __construct($placeholder, $content, $code = 0, Throwable $previous = null)
    {
        parent::__construct("'$placeholder' non è stato assegnato un valore valido. Content: ".$content, $code, $previous);
    }
}

class HTMLBuilderMultiplePlacehoderException extends Exception
{
    public function __construct($filename, $code = 0, Throwable $previous = null)
    {
        parent::__construct("'$filename' contiene duplicati dello stesso marcatore.", $code, $previous);
    }
}

class HTMLBuilder {

  protected string $content;
  protected array $placeholders;

    function __construct(string $htmlfile = "", string $layout = "")
    {
        if ($htmlfile == "") {
            $this->content = $layout;
        }else if ($layout == ""){
            $this->content = file_get_contents($htmlfile);
        }else{
            throw new Exception("Non è stato fornito un metodo per raggiungere un layout da manipolare.");
        }
        preg_match_all('/{{(.*?)}}/', $this->content, $matches, PREG_OFFSET_CAPTURE);

        $nomi = array_column($matches[1], 0);
        if (count($nomi) != count(array_unique($nomi))) {
            throw new HTMLBuilderMultiplePlacehoderException($htmlfile);
        }

        $this->placeholders = array_map(
            fn($a) => [$a, null],
            array_combine(
                $nomi,
                array_column($matches[0], 1),
            )
        );
        uasort($this->placeholders, fn($a, $b) => ($a[0] > $b[0]) ? -1 : 1);
    }


  const UNSAFE = 0;
  const ERROR_P = 1;
  const SUCCESS_P = 2;

  function set($placeholder, $data, $type = HTMLBuilder::UNSAFE): HTMLBuilder {
    if (!array_key_exists($placeholder, $this->placeholders)) {
      throw new HTMLBuilderMissingPlaceholderException($placeholder,$this->content);
    }

    // TODO da estendere qual'ora fossero richiesti magheggi
      if($this->placeholders[$placeholder][1] == null){
          $this->placeholders[$placeholder][1] = match ($type) {
              HTMLBuilder::UNSAFE => $data,
              HTMLBuilder::ERROR_P => '<p class="error">' . htmlspecialchars($data) . '</p>',
              HTMLBuilder::SUCCESS_P => '<p class="success">' . htmlspecialchars($data) . '</p>',
          };
      }

        return $this;
    }

    function build(): string
    {
        foreach ($this->placeholders as $placeholder => $line) {
            [$offset, $replace] = $line;

            if ($replace === null) {
              throw new HTMLBuilderUndefinedPlaceholderException($placeholder,$this->content);
            }

            $this->content = substr_replace(
                $this->content,
                $replace,
                $offset,
                strlen($placeholder) + 4,
            );
            unset($this->placeholders[$placeholder]);

        }

        return $this->content;
    }
}

;

class HTMLBuilderCleaner extends HTMLBuilder {
    function clean(string $substring): HTMLBuilder
    {
        foreach ($this->placeholders as $placeholder => $line) {
            if (str_contains($placeholder, $substring)) {
              $this->set($placeholder,'');
            }
        }
        return $this;
    }
}