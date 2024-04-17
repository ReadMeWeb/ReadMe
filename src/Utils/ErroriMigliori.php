<?php
 
 require_once __DIR__ . '/Stream.php';
 
 set_error_handler(function ($severity, $message, $file, $line) {
   throw new \ErrorException($message, $severity, $severity, $file, $line);
 });
 
 set_exception_handler(function (Throwable $exception) {
   $a = explode("\n", "{$exception->getMessage()}\n\n{$exception->getTraceAsString()}");
   $format = '%-' . max(array_map('strlen', $a)) . 's';
   die(stream($a, _map(fn ($a) => sprintf($format, $a)), _implode("        <br>\n")));
 });
 