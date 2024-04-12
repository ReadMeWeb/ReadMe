<?php

function _map($function) {
  return fn ($collection) => array_map($function, $collection);
}

function _filter($function) {
  return fn ($collection) => array_filter($collection, $function);
}

function _implode($delimiter) {
  return fn ($collection) => implode($delimiter, $collection);
}

function stream($collection, ...$pipeline) {
  foreach ($pipeline as $function) {
    $collection = $function($collection);
  }
  return $collection;
}


// $numbers = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
// 
// $a = stream(
//   $numbers,
//   _filter(fn ($a) => ($a % 2) == 1),  // filtra per i dispari
//   _map(fn ($a) => 2 ** $a),           // eleva a potenza
//   _implode("\n\n") 
// );
// 
// echo $a . "\n\n";
