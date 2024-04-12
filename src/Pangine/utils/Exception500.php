<?php

namespace Pangine\utils;

use Throwable;

class Exception500 extends \Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}