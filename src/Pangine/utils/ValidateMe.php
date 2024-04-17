<?php
 
 namespace Pangine\utils;
 
 require_once(__DIR__ . "/Validator.php");
 
 class ValidateMe
 {
 
     private string $parameter_name;
     private Validator $validator;
 
     private array $validating_functions;
 
     public function __construct(string $parameter_name, Validator $validator)
     {
         $this->parameter_name = $parameter_name;
         $this->validator = $validator;
     }
 
     //Le estensioni vanno senza il punto
 
     /**
      * @throws Exception500
      */
     public function is_file(array $allowed_extensions = []): Validator
     {
         $this->validating_functions[] = function () use ($allowed_extensions) {
             if (count($allowed_extensions) == 0) {
                 throw new Exception500("Non è stata fornita alcuna estensione al validatore.");
             }
 
             //Controllo dell'esistenza del file
             if (!isset($_FILES[$this->parameter_name])) {
                 $_SESSION["parameters"][$this->parameter_name]["message"] = "Si prega di fornire un file.";
                 $this->validator->something_went_off();
                 return;
             }
 
             //Controllo dell'estensione del file
             $img_name = trim($_FILES[$this->parameter_name]['name']);
             $img_ext = pathinfo($img_name, PATHINFO_EXTENSION);
             if (!$img_ext || !in_array($img_ext, $allowed_extensions)) {
                 $_SESSION["parameters"][$this->parameter_name]["message"] = "Estensione del file non valida. L'estensione del file deve essere tra queste: ";
                 foreach ($allowed_extensions as $extension) {
                     $_SESSION["parameters"][$this->parameter_name]["message"] .= $extension;
                     if (end($allowed_extensions) == $extension) {
                         $_SESSION["parameters"][$this->parameter_name]["message"] .= ".";
                     } else {
                         $_SESSION["parameters"][$this->parameter_name]["message"] .= ", ";
                     }
                 }
                 $this->validator->something_went_off();
             }
         };
 
         return $this->validator;
     }
 
     //Lo string parser, deve ritornare la stringa che sarà il messaggio di validazione. NON INTERAGISCE DIRETTAMENTE CON LA SESSIONE.
     //Restituisce "" se va tutto bene.
     public function is_string(int $min_length = 0, int $max_length = -1, callable $string_parser = null): Validator
     {
         $this->validating_functions[] = function () use ($min_length, $max_length, $string_parser) {
             if ($min_length < 0 || $max_length < -1) {
                 throw new Exception500("Inserire lunghezze di validazione stringa valide.");
             }
 
             $method = null;
             if (isset($_GET[$this->parameter_name])) {
                 $method = $_GET;
             } elseif (isset($_POST[$this->parameter_name])) {
                 $method = $_POST;
             } else {
                 $_SESSION["parameters"][$this->parameter_name]["message"] = "Si prega di fornire un valore.";
                 $this->validator->something_went_off();
                 return;
             }
 
             $str_to_analyze = $method[$this->parameter_name];
             $_SESSION["parameters"][$this->parameter_name]["value"] = $str_to_analyze;
 
             if ($min_length != 0 && strlen($str_to_analyze) < $min_length) {
                 $_SESSION["parameters"][$this->parameter_name]["message"] = "Si prega di fornire un valore con un numero di caratteri maggiore di " . $min_length . ".";
                 $this->validator->something_went_off();
                 return;
             }
             if ($max_length != -1 && strlen($str_to_analyze) > $max_length) {
                 $_SESSION["parameters"][$this->parameter_name]["message"] = "Si prega di fornire un valore con un numero di caratteri minore di " . $max_length . ".";
                 $this->validator->something_went_off();
                 return;
             }
             if ($string_parser != null) {
                 $message_from_parser = $string_parser($str_to_analyze);
                 if ($message_from_parser != "") {
                     $_SESSION["parameters"][$this->parameter_name]["message"] = $message_from_parser;
                     $this->validator->something_went_off();
                 }
             }
         };
 
         return $this->validator;
     }
 
     //Lo string parser, deve ritornare la stringa che sarà il messaggio di validazione. NON INTERAGISCE DIRETTAMENTE CON LA SESSIONE.
     //Restituisce "" se va tutto bene.
     /**
      * @throws Exception500
      */
     public function is_numeric(int $min_val = PHP_INT_MIN, int $max_val = PHP_INT_MAX, callable $value_parser = null): Validator
     {
         $this->validating_functions[] = function () use ($min_val, $max_val, $value_parser) {
             if ($min_val > $max_val) {
                 throw new Exception500("Inserire un intervallo di validazione per valori numerici valido.");
             }
 
             $method = null;
             if (isset($_GET[$this->parameter_name])) {
                 $method = $_GET;
             } elseif (isset($_POST[$this->parameter_name])) {
                 $method = $_POST;
             } else {
                 $_SESSION["parameters"][$this->parameter_name]["message"] = "Si prega di fornire un valore.";
                 $this->validator->something_went_off();
                 return;
             }
 
             $val_to_analyze = $method[$this->parameter_name];
             $_SESSION["parameters"][$this->parameter_name]["value"] = $val_to_analyze;
 
             if ($min_val != PHP_INT_MIN && $val_to_analyze < $min_val) {
                 $_SESSION["parameters"][$this->parameter_name]["message"] = "Si prega di fornire un valore numerico maggiore di" . $min_val . ".";
                 $this->validator->something_went_off();
                 return;
             }
             if ($max_val != PHP_INT_MAX && $val_to_analyze > $max_val) {
                 $_SESSION["parameters"][$this->parameter_name]["message"] = "Si prega di fornire un valore numerico minore di " . $max_val . ".";
                 $this->validator->something_went_off();
                 return;
             }
             if ($value_parser != null) {
                 $message_from_parser = $value_parser($val_to_analyze);
                 if ($message_from_parser != "") {
                     $_SESSION["parameters"][$this->parameter_name]["message"] = $message_from_parser;
                     $this->validator->something_went_off();
                 }
             }
         };
 
         return $this->validator;
     }
 
     public function validate(): void
     {
         foreach ($this->validating_functions as $validating_function) {
             $validating_function();
         }
     }
 
 }