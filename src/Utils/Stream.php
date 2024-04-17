<?php
 
 class Stream
 {
     private array $collection = [];
 
     public function __construct(array $collection)
     {
         $this->collection = $collection;
     }
 
     public function map(callable $function): Stream
     {
         $this->collection = array_map($function, $this->collection);
         return $this;
     }
 
     public function filter(callable $function): Stream
     {
         $this->collection = array_filter($this->collection, $function);
         return $this;
     }
 
     public function implode(string $delimiter = ""): string
     {
         return implode($delimiter, $this->collection);
     }
 
     public function count(): int
     {
         return count($this->collection);
     }
 }
 
 function _map($function)
 {
     return fn($collection) => array_map($function, $collection);
 }
 
 function _filter($function)
 {
     return fn($collection) => array_filter($collection, $function);
 }
 
 function _implode($delimiter)
 {
     return fn($collection) => implode($delimiter, $collection);
 }
 
 function stream($collection, ...$pipeline)
 {
     foreach ($pipeline as $function) {
         $collection = $function($collection);
     }
     return $collection;
 }
 
 
 $numbers = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
 
 
 //FUNCTIONAL IMPLEMENTATION
 //$a = stream(
 //    $numbers,
 //    _filter(fn($a) => ($a % 2) == 1),  // filtra per i dispari
 //    _map(fn($a) => 2 ** $a),           // eleva a potenza
 //    _implode("\n\n")
 //);
 
 //OOP IMPLEMENTATION
 //$a = (new Stream($numbers))
 //    ->filter(fn($a) => ($a % 2) == 1)
 //    ->map(fn($a) => 2 ** $a)
 //    ->implode("\n\n");
 //
 //echo $a . "\n\n";
 