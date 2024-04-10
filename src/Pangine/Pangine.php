<?php

namespace Pangine;

class Pangine
{
    private array $controlled_renderers = [];

    public function __construct()
    {
        self::try_session();
    }

    public function execute()
    {
        try {
            foreach ($this->controlled_renderers as $renderer) {
                $renderer();
            }
        }catch(\Exception $e){

        }
    }

    public function add_renderer_POST(callable $renderer, string $caller_parameter_name=""): Pangine{
        $this->controlled_renderers[] = function () use ($renderer,$caller_parameter_name){
            if($_SERVER['REQUEST_METHOD'] == "POST" && ($caller_parameter_name=="" || isset($_POST[$caller_parameter_name]))){
                $renderer();
            }
        };
        return $this;
    }

    public function add_renderer_GET(callable $renderer, string $caller_parameter_name=""): Pangine{
        $this->controlled_renderers[] = function () use ($renderer,$caller_parameter_name){
            if($_SERVER['REQUEST_METHOD'] == "GET" && ($caller_parameter_name=="" || isset($_GET[$caller_parameter_name]))){
                $renderer();
            }
        };
        return $this;
    }

    static function try_session(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}