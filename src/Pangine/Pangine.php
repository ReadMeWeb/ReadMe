<?php

namespace Pangine;

require_once "../components/sessionEstablisher.php";

class Pangine {
  private array $indexer;

  public function __construct() {
    $this->indexer = [];
  }

    public function execute(): void
    {
        try_session();
        ksort($this->indexer);
        try {
            foreach ($this->indexer as $renderer) {
                $renderer();
            }
        } catch (PangineValidationError $e) {
            $_SESSION["err_data"] = $e->get_errors();
            header("Location: " . $e->getCallbackPage());
            //echo json_encode($e->get_errors());
            exit(0);
        } catch (PangineAuthError $e) {
            header("Location: /Pages/unallowed.php");
            exit(0);
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

class PangineValidationError extends \Exception
{
    private array $fieldsWithErrors;
    private string $callbackPage;

    public function __construct(string $callbackPage)
    {
        $this->fieldsWithErrors = array();
        $this->callbackPage = $callbackPage;
        parent::__construct(json_encode($this->fieldsWithErrors));
    }

    public function add_unvalidated_field(string $fieldName, string $unvalidMessage, $value): void
    {
        $this->fieldsWithErrors[$fieldName] = array("value" => $value, "message" => $unvalidMessage);
    }

  public function found_errors(): bool {
    return count($this->fieldsWithErrors) > 0;
  }

    public function get_errors(): array
    {
        return $this->fieldsWithErrors;
    }

    public function getCallbackPage(): string
    {
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

    public function validate(string $callbackPage): void
    {
        try_session();
        $error = new PangineValidationError($callbackPage);
        if ($this->method == "GET") {
            $method = $_GET;
        } else {
            $method = $_POST;
        }
        foreach ($this->configs as $field => $config) {
            if (isset($method[$field])) {
                $validationResponse = $config->validate($method[$field]);
                if ($validationResponse != "") {
                    $error->add_unvalidated_field($field, $validationResponse, $method[$field]);
                }
            } else {
                $error->add_unvalidated_field($field, "Questo campo è da fornire.", $method[$field]);
            }
            $_SESSION["data"][$field] = $method[$field];
        }
        if ($error->found_errors()) {
            throw $error;
        } else {
            unset($_SESSION["data"]);
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

    public function validate(string $field): string
    {
        if ($this->isImage) {
            $img_path = trim($_FILES[$field]['tmp_name']);
            $img_type = exif_imagetype($img_path);
            if (!$img_type) {
                return "Il file caricato non è un immagine.";
            } else if (!in_array($img_type, array(IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
                return "Il file caricato non è nel formato corretto (supportati .jpeg e .png).";
            }
        }
        if (is_numeric($field)) {
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

class PangineUnvalidFormManager
{
    private string $layout;

    public function __construct(string $layout){
        try_session();
        $this->layout = $layout;
        foreach ($_SESSION["err_data"] as $field => $data){
            $this->layout = str_replace("{{".$field."-value}}",$data["value"],$this->layout);
            $this->layout = str_replace("<p>{{".$field."-message}}</p>",$data["message"],$this->layout);
        }
        foreach ($_SESSION["data"] as $field => $value){
            $this->layout = str_replace("{{".$field."-value}}",$value,$this->layout);
            $this->layout = str_replace("<p>{{".$field."-message}}</p>","",$this->layout);
        }
   }

   public function getLayout(){
        return $this->layout;
   }
}


class PangineAuthenticator {
  private function try_session(): bool {
    if (!isset($_SESSION)) {
      $session_return = session_start();
      if (!isset($_SESSION["user"])) {
        $_SESSION["user"]["status"] = "UNREGISTERED";
      }
      return $session_return;
    }
    return true;
  }

  public function authenticate(array $allowedStatuses): bool {
    $session_status = $this->try_session();
    if (!in_array($_SESSION["user"]["status"], $allowedStatuses)) {
      throw new PangineAuthError("Non hai i permessi per accedere alla pagina richiesta.");
    }
    return $session_status;
  }
}

require_once 'HTMLBuilder.php';
