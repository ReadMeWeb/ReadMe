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
    try {
      foreach ($this->indexer as $key => $renderer) {
        if (($_SERVER['REQUEST_METHOD'] === 'GET' && str_contains($key, "GET")) || ($_SERVER['REQUEST_METHOD'] === 'POST' && str_contains($key, "POST"))) {
          $renderer();
        }
      }
    } catch (PangineValidationError $e) {
      $_SESSION["err_data"] = $e->get_errors();
      redirect($e->getCallbackPage());
    } catch (PangineAuthError $e) {
      redirect(pages['Permessi insufficienti']);
    }
  }


  public function GET_create(callable $renderer): Pangine {
    $wrapper = function () use ($renderer): void {
      if (isset($_GET["create"])) {
        $renderer();
        exit(0);
      }
    };
    $this->indexer["1GET_0"] = $wrapper;
    return $this;
  }

  public function GET_read(callable $renderer): Pangine {
    $wrapper = function () use ($renderer): void {
      if (isset($_GET)) {
        $renderer();
        exit(0);
      }
    };
    $this->indexer["1GET_3"] = $wrapper;
    return $this;
  }

  public function GET_update(callable $renderer): Pangine {
    $wrapper = function () use ($renderer): void {
      if (isset($_GET["update"])) {
        $renderer();
        exit(0);
      }
    };
    $this->indexer["1GET_1"] = $wrapper;
    return $this;
  }

  public function GET_delete(callable $renderer): Pangine {
    $wrapper = function () use ($renderer): void {
      if (isset($_GET["delete"])) {
        $renderer();
        exit(0);
      }
    };
    $this->indexer["1GET_2"] = $wrapper;
    return $this;
  }

  public function POST_create(callable $renderer): Pangine {
    $wrapper = function () use ($renderer): void {
      if (isset($_POST["create"])) {
        $renderer();
        exit(0);
      }
    };
    $this->indexer["0POST_0"] = $wrapper;
    return $this;
  }

  public function POST_read(callable $renderer): Pangine {
    $wrapper = function () use ($renderer): void {
      if (isset($_POST)) {
        $renderer();
        exit(0);
      }
    };
    $this->indexer["0POST_3"] = $wrapper;
    return $this;
  }

  public function POST_update(callable $renderer): Pangine {
    $wrapper = function () use ($renderer): void {
      if (isset($_POST["update"])) {
        $renderer();
        exit(0);
      }
    };
    $this->indexer["0POST_1"] = $wrapper;
    return $this;
  }

  public function POST_delete(callable $renderer): Pangine {
    $wrapper = function () use ($renderer): void {
      if (isset($_POST["delete"])) {
        $renderer();
        exit(0);
      }
    };
    $this->indexer["0POST_2"] = $wrapper;
    return $this;
  }
}

class PangineValidationError extends \Exception {
  private array $fieldsWithErrors;
  private string $callbackPage;

  public function __construct(string $callbackPage) {
    $this->fieldsWithErrors = array();
    $this->callbackPage = $callbackPage;
    parent::__construct(json_encode($this->fieldsWithErrors));
  }

  public function add_unvalidated_field(string $fieldName, string $unvalidMessage, $value): void {
    $this->fieldsWithErrors[$fieldName] = array("value" => $value, "message" => $unvalidMessage);
  }

  public function found_errors(): bool {
    return count($this->fieldsWithErrors) > 0;
  }

  public function get_errors(): array {
    return $this->fieldsWithErrors;
  }

  public function getCallbackPage(): string {
    return $this->callbackPage;
  }
}

class PangineAuthError extends \Exception {
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
    $error = new PangineValidationError($callbackPage);
    if ($this->method == "GET") {
      $method = $_GET;
    } else {
      $method = $_POST;
    }
    foreach ($this->configs as $field => $config) {
      if ($config->isImg()) {
          $validationResponse = $config->validate($field, $_FILES);
          if ($validationResponse != "") {
            $error->add_unvalidated_field($field, $validationResponse, $field);
          }
        $_SESSION["data"][$field] = $_FILES[$field]['name'];
      } else {
          $validationResponse = $config->validate($method[$field], $method);
          if ($validationResponse != "") {
            $error->add_unvalidated_field($field, $validationResponse, $method[$field]);
          }
        $_SESSION["data"][$field] = $method[$field];
      }
    }
    if ($error->found_errors()) {
      throw $error;
    }
  }
}

class PangineValidatorConfig {
  private bool $notZero;
  private bool $notEmpty;
  private int $minLength;
  private int $maxLength;
  private int $minVal;
  private int $maxVal;
  private bool $isImage;

  public function __construct(bool $notEmpty = false, bool $notZero = false, int $minLength = 0, int $maxLength = -1, int $minVal = 0, int $maxVal = -1, bool $isImage = false) {
    $this->notZero = $notZero;
    $this->notEmpty = $notEmpty;
    $this->isImage = $isImage;
    $this->minLength = $minLength;
    $this->maxLength = $maxLength;
    $this->minVal = $minVal;
    $this->maxVal = $maxVal;
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


class PangineUnvalidFormManager {

  // Dependency injection
  public function __construct(private \HTMLBuilder $htmlBuilder) {
    try_session();
    foreach (extract_from_array_else("err_data", $_SESSION, []) as $field => $data) {
      $this->htmlBuilder->set("" . $field . "-value", $data["value"], \HTMLBuilder::UNSAFE)
        ->set("" . $field . "-message", $data["message"], \HTMLBuilder::ERROR_P);
    }
    foreach (extract_from_array_else("data", $_SESSION, []) as $field => $value) {
      $this->htmlBuilder->set("" . $field . "-value", $value, \HTMLBuilder::UNSAFE)
        ->set("" . $field . "-message", "", \HTMLBuilder::UNSAFE);
    }
  }

  public function getHTMLBuilder() {
    return $this->htmlBuilder;
  }
}


class PangineAuthenticator {
  public function authenticate(array $allowedStatuses): bool {
    $session_status = try_session();
    if (!in_array($_SESSION["user"]["status"], $allowedStatuses)) {
      throw new PangineAuthError("Non hai i permessi per accedere alla pagina richiesta.");
    }
    return $session_status;
  }
}
