<?php

use Pangine\utils\Validator;
use Pangine\utils\ValidateMe;

require_once __DIR__ . '/../Pangine/utils/Validator.php';
require_once __DIR__ . '/../Pangine/utils/ValidateMe.php';

function _new_validator($url) {
  return new Validator(url_in_case_of_failure: $url);
}

function _add_parametre($name, $setting) {
  return fn (Validator $validator) => $setting($validator->add_parameter($name));
}

function _file(array $allowed_extensions = []) {
  return fn (ValidateMe $me) => $me->is_file($allowed_extensions);
}

function _string(int $min_length = 0, int $max_length = -1, callable $string_parser = null) {
  return fn (ValidateMe $me) => $me->is_string($min_length, $max_length, $string_parser);
}

function _numeric(int $min_val = PHP_INT_MIN, int $max_val = PHP_INT_MAX, callable $value_parser = null) {
  return fn (ValidateMe $me) => $me->is_numeric($min_val, $max_val, $value_parser);
}


// come usare
// 
// stream(
// _new_validator($url),
// _add_parametre($name,_string(4,20)),
// _add_parametre($age,_numeric()),
// _add_parametre($dossier,_file(['txt','tex','md']))
// )
// 
