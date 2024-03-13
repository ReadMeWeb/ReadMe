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
    ksort($this->indexer);
    foreach ($this->indexer as $key => $renderer) {
      if (str_contains($key, $_SERVER['REQUEST_METHOD'])) {
        $renderer();
      }
    }
  }

  private function wrap($index, $array, $crud, $renderer) {
    $this->indexer[$index] = function () use ($array, $crud, $renderer) {
      if (isset($array[$crud])) {
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
  private string $method;
  private array $configs;

  public function __construct(string $method, array $configs) {
    $this->configs = $configs;
    $this->method = $method;
  }

  public function validate(string $callbackPage): void {
    try_session();
    $fieldsWithErrors =  [];
    if ($this->method == "GET") {
      $method = $_GET;
    } else {
      $method = $_POST;
    }
    foreach ($this->configs as $field => $config) {
      if ($config->isImg()) {
        $validationResponse = $config->validate($field, $_FILES);
        if ($validationResponse != "") {
          $fieldsWithErrors[$field] = array("value" => $field, "message" => $validationResponse);
        }
        $_SESSION["data"][$field] = $_FILES[$field]['name'];
      } else {
        $validationResponse = $config->validate($method[$field], $method);
        if ($validationResponse != "") {
          $fieldsWithErrors[$field] = array("value" => $method[$field], "message" => $validationResponse);
        }
        $_SESSION["data"][$field] = $method[$field];
      }
    }
    if (count($fieldsWithErrors) > 0) {
      $_SESSION["err_data"] = $fieldsWithErrors;
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
        ->set("" . $field . "-value", $value, \HTMLBuilder::UNSAFE)
        ->set("" . $field . "-message", "", \HTMLBuilder::UNSAFE);
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

  public function validate(string $field, array $method): string {
    if (!isset($method[$field])) {
      return "Questo campo è da fornire.";
    }
    if ($this->isImage) {

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
    } else if (is_numeric($field)) {
      if ($this->notZero && intval($field) == 0) {
        return "Questo campo non può essere uguale a 0.";
      }
      if ($this->minVal > intval($field)) {
        return "Questo campo deve avere un valore minimo di " . $this->minVal . ".";
      }
      if ($this->maxVal >= 0 && $this->maxVal < intval($field)) {
        return "Questo campo deve avere un valore massimo di " . $this->maxVal . ".";
      }
    } else {
      if ($this->notEmpty && $field == "") {
        return "Questo campo non può essere vuoto.";
      }
      if ($this->minLength > strlen($field)) {
        return "Questo campo deve almeno essere di " . $this->minLength . " caratteri.";
      }
      if ($this->maxLength >= 0 && $this->maxLength < strlen($field)) {
        return "Questo campo deve al massimo essere di " . $this->maxLength . " caratteri.";
      }
    }
    return "";
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
