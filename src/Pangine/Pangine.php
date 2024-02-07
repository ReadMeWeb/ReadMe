<?php

namespace Pangine;

class Pangine{
    private array $indexer;

    public function __construct(){
        $this->indexer = [];
    }
    public function execute(): void{
        ksort($this->indexer);
        try {
            foreach ($this->indexer as $renderer){
                $renderer();
            }
        }catch (PangineValidationError $e){

        }
    }
    public function GET_create(callable $renderer): Pangine{
        $wrapper = function () use($renderer): void{
            if(isset($_GET["create"])){
                $renderer();
                exit(0);
            }
        };
        $this->indexer["GET_0"] = $wrapper;
        return $this;
    }
    public function GET_read(callable $renderer): Pangine{
        $wrapper = function () use($renderer): void{
            if(isset($_GET)){
                $renderer();
                exit(0);
            }
        };
        $this->indexer["GET_3"] = $wrapper;
        return $this;
    }
    public function GET_update(callable $renderer): Pangine{
        $wrapper = function () use($renderer): void{
            if(isset($_GET["update"])){
                $renderer();
                exit(0);
            }
        };
        $this->indexer["GET_1"] = $wrapper;
        return $this;
    }
    public function GET_delete(callable $renderer): Pangine{
        $wrapper = function () use($renderer): void{
            if(isset($_GET["delete"])){
                $renderer();
                exit(0);
            }
        };
        $this->indexer["GET_2"] = $wrapper;
        return $this;
    }
    public function POST_create(callable $renderer): Pangine{
        $wrapper = function () use($renderer): void{
            if(isset($_POST["create"])){
                $renderer();
                exit(0);
            }
        };
        $this->indexer["POST_0"] = $wrapper;
        return $this;
    }
    public function POST_read(callable $renderer): Pangine{
        $wrapper = function () use($renderer): void{
            if(isset($_POST)){
                $renderer();
                exit(0);
            }
        };
        $this->indexer["POST_3"] = $wrapper;
        return $this;
    }
    public function POST_update(callable $renderer): Pangine{
        $wrapper = function () use($renderer): void{
            if(isset($_POST["update"])){
                $renderer();
                exit(0);
            }
        };
        $this->indexer["POST_1"] = $wrapper;
        return $this;
    }
    public function POST_delete(callable $renderer): Pangine{
        $wrapper = function () use($renderer): void{
            if(isset($_POST["delete"])){
                $renderer();
                exit(0);
            }
        };
        $this->indexer["POST_2"] = $wrapper;
        return $this;
    }
}

class PangineValidationError extends \Exception {
    private array $fieldsWithErrors;

    public function getFieldsWithErrors(): array{
        return $this->fieldsWithErrors;
    }

    public function __construct(){
        $this->fieldsWithErrors = array();
        parent::__construct(json_encode($this->fieldsWithErrors));
    }

    public function add_unvalidated_field(string $fieldName, string $unvalidMessage): void{
        $this->fieldsWithErrors[$fieldName] = $unvalidMessage;
    }
    public function found_errors(): bool{
        return count($this->fieldsWithErrors) > 0;
    }

}

class PangineAuthError extends \Exception {
}

class PangineValidator{
    private string $method;
    private array $configs;
    public function __constrct(string $method,array $configs){
       $this->configs = $configs;
       $this->method = $method;
    }

    public function validate(): void{
        $error = new PangineValidationError();
        $method = null;
        if($this->method == "GET"){
            $method = $_GET;
        }else{
            $method = $_POST;
        }
        foreach($this->configs as $field => $config){
            if(isset($method[$field])){
                //TODO: inserire codice che prendendo la configurazione, valida il campo, e aggiunge eventuali errori
            }else{
                $error->add_unvalidated_field($field,"Questo campo è da riempire.");
            }
        }
        if($error->found_errors()){
            throw $error;
        }
    }
}

class PangineAuthenticator{
    private function try_session(): bool{
        if(!isset($_SESSION)){
            $session_return = session_start();
            if(!isset($_SESSION["user"])){
                $_SESSION["user"]["status"] = "UNREGISTERED";
            }
            return $session_return;
        }
        return true;
    }
    public function authenticate(array $allowedStatuses): bool{
        $session_status = $this->try_session();
        if (!in_array($_SESSION["user"]["status"],$allowedStatuses)){
            throw new PangineAuthError("Non hai i permessi per accedere alla pagina richiesta.");
        }
        return $session_status;
    }
}
