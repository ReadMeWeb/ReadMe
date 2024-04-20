<?php


namespace Pangine\utils;

use Pangine\Pangine;

require_once(__DIR__ . "/ValidateMe.php");
require_once(__DIR__ . "/Exception500.php");

class Validator
{
    private array $to_be_validated = [];
    private string $url_in_case_of_failure = "";
    private bool $validation_failed = false;

    public function __construct(string $url_in_case_of_failure){
        $this->url_in_case_of_failure = $url_in_case_of_failure;
    }
    public function add_parameter(string $parameter_name): ValidateMe{
        $this->to_be_validated[] = new ValidateMe($parameter_name,$this);
        return end($this->to_be_validated);
    }

    public function something_went_off(): void{
       $this->validation_failed = true;
    }

    public function validate(): void{
        foreach ($this->to_be_validated as $validator){
            $validator->validate();
        }
        if($this->validation_failed){
            if ($this->url_in_case_of_failure === '') {
              Pangine::redirect();
            }
            header("Location: ".$this->url_in_case_of_failure);
            exit();
        }
    }

    public static function clear_session_parameters(): void{
        unset($_SESSION["parameters"]);
    }

}

