<?php

namespace Pangine;

use mysql_xdevapi\Exception;

class Pangine{
    private array $indexer;

    public function __construct(){
        $this->indexer = [];
    }
    public function execute(): void{
        ksort($this->indexer);
        try {
            foreach ($this->indexer as $key => $renderer){
                $renderer();
            }
        }catch (Exception $e){

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

class PangineValidationError{
    
}

class PangineAuthError{

}

class PangineValidator{

}

class PangineAuthenticator{

}
