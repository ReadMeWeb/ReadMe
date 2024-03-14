<?php

namespace Pangine;

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'include/sessionEstablisher.php';
require_once 'include/HTMLBuilder.php';
require_once 'include/utils.php';
require_once 'include/pages.php';

class Pangine {
  private array $indexer;

  public function __construct() {
    $this->indexer = [];
  }

  public function execute(): void {
    try_session();
    try {
        ksort($this->indexer);
        foreach ($this->indexer as $key => $renderer) {
            if (str_contains($key, $_SERVER['REQUEST_METHOD'])) {
                $renderer();
            }
        }
        $this->indexer['1GET_3']();
    }catch (PangineError500 $e){
        redirect('/500.php');
    }
  }

  private function wrap($index, $array, $crud, $renderer) {
    $this->indexer[$index] = function () use ($array, $crud, $renderer) {
      if ($crud == 'read' || isset($array[$crud])) {
        $renderer();
        exit(0);
      }
    };
    return $this;
  }

  public function GET_create(callable $renderer): Pangine {
    return $this->wrap('1GET_0', $_GET, 'create', $renderer);
  }

  public function GET_read(callable $renderer): Pangine {
    return $this->wrap('1GET_3', $_GET, 'read', $renderer);
  }

  public function GET_update(callable $renderer): Pangine {
    return $this->wrap('1GET_1', $_GET, 'update', $renderer);
  }

  public function GET_delete(callable $renderer): Pangine {
    return $this->wrap('1GET_2', $_GET, 'delete', $renderer);
  }

  public function POST_create(callable $renderer): Pangine {
    return $this->wrap('0POST_0', $_POST, 'create', $renderer);
  }

  public function POST_read(callable $renderer): Pangine {
    return $this->wrap('0POST_3', $_POST, 'read', $renderer);
  }

  public function POST_update(callable $renderer): Pangine {
    return $this->wrap('0POST_1', $_POST, 'update', $renderer);
  }

  public function POST_delete(callable $renderer): Pangine {
    return $this->wrap('0POST_2', $_POST, 'delete', $renderer);
  }
}

class PangineValidator {

  public function __construct(private array $configs) {
  }

  public function validate(string $callbackPage, array $method): void {
    try_session();
    $keys = array_keys($this->configs);
    $_SESSION['err_data'] = array_filter(
      array_combine($keys, array_map(
        fn ($field, $config) => $config->validate($field, $method),
        $keys,
        $this->configs
      )),
      fn ($err) => $err['message'] !== ''
    );
    $_SESSION['data'] = array_combine($keys, array_map(
      fn ($field, $config) => $config->isImg() ? $_FILES[$field]['name'] : $method[$field],
      $keys,
      $this->configs
    ));
    if (count($_SESSION["err_data"]) > 0) {
      redirect($callbackPage);
    }
  }

  public function setformdata(\HTMLBuilder $htmlBuilder) {
    try_session();
    foreach (extract_from_array_else("err_data", $_SESSION, []) as $field => $data) {
      $htmlBuilder
        ->set("" . $field . "-value", $data["value"], \HTMLBuilder::UNSAFE)
        ->set("" . $field . "-message", $data["message"], \HTMLBuilder::ERROR_P);
    }
    foreach (extract_from_array_else("data", $_SESSION, []) as $field => $value) {
      $htmlBuilder
        ->set("" . $field . "-value", $value, \HTMLBuilder::UNSAFE);
      //->set("" . $field . "-message", "", \HTMLBuilder::UNSAFE);
    }

    return $htmlBuilder;
  }
}

class PangineValidatorConfig {

  public function __construct(
    private bool $notEmpty = false,
    private bool $notZero = false,
    private int $minLength = 0,
    private int $maxLength = -1,
    private int $minVal = 0,
    private int $maxVal = -1,
    private bool $isImage = false
  ) {
  }

  public function isImg(): bool {
    return $this->isImage;
  }

  public function validate(string $field, array $method): array {
    return $this->isImg()
      ? array('value' => $field, 'message' => $this->getmessage($field, $_FILES))
      : array('value' => $method[$field], 'message' => $this->getmessage($method[$field], $method));
  }

  private function getmessage(string $field): string {
    if ($this->isImage) {
      return $this->imgfield($field);
    } else if (is_numeric($field)) {
      return $this->numericfield($field);
    } else {
      return $this->textfield($field);
    }
    return "";
  }

  private function imgfield(string $field): string {
    $img_name = trim($_FILES[$field]['name']);
    $upload_code = $_FILES[$field]['error'];

    if ($this->notEmpty || $img_name != '') {
      if ($img_name == '') {
        return "Per procedere è necessario caricare un'immagine.";
      }
      if ($upload_code != UPLOAD_ERR_OK) {
        return "Errore durante il caricamento dell'immagine riprovare più tardi o contattare l'amministratore.";
      }
      $img_ext = pathinfo($img_name, PATHINFO_EXTENSION);
      $valid_ext = ['png', 'jpg', 'jpeg'];

      if (!$img_ext || !in_array($img_ext, $valid_ext)) {
        return "Il file caricato ha un formato non corretto (formati supportati png, jpg e jpeg).";
      }
    }
    return '';
  }

  private function numericfield(string $field): string {
    if ($this->notZero && intval($field) == 0) {
      return "Questo campo non può essere uguale a 0.";
    }
    if ($this->minVal > intval($field)) {
      return "Questo campo deve avere un valore minimo di " . $this->minVal . ".";
    }
    if ($this->maxVal >= 0 && $this->maxVal < intval($field)) {
      return "Questo campo deve avere un valore massimo di " . $this->maxVal . ".";
    }
    return '';
  }

  private function textfield(string $field): string {
    if ($this->notEmpty && $field == "") {
      return "Questo campo non può essere vuoto.";
    }
    if ($this->minLength > strlen($field)) {
      return "Questo campo deve almeno essere di " . $this->minLength . " caratteri.";
    }
    if ($this->maxLength >= 0 && $this->maxLength < strlen($field)) {
      return "Questo campo deve al massimo essere di " . $this->maxLength . " caratteri.";
    }
    return '';
  }
}


class PangineAuthenticator {
  public function authenticate(array $allowedStatuses): bool {
    $session_status = try_session();
    if (!in_array($_SESSION["user"]["status"], $allowedStatuses)) {
      redirect(pages['Permessi insufficienti']);
    }
    return $session_status;
  }
}

class PangineError500 extends \Exception {
    public function __construct($link = "", $message = ""){
        try_session();
        if($link != "" && $message != "") {
            $_SESSION['error500data']['link'] = $link;
            $_SESSION['error500data']['message'] = $message;
        }
        parent::__construct();
    }
}
