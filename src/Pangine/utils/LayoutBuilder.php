<?php

namespace Pangine\utils;

class LayoutBuilder
{
    private string $base_layout;

    public function __construct(string $layout_type = "pub")
    {
        if($layout_type == "priv"){
            $this->base_layout = file_get_contents(__DIR__ . "/../../templates/private_layout.html");
        }else{
            $this->base_layout = file_get_contents(__DIR__ . "/../../templates/public_layout.html");
        }
    }

    public function build(): string
    {
        if(strpos($this->base_layout,"{{") != false){
            throw new Exception500("Non tutti i tag sono stati consumati dalla pagina precedente.");
        }
        return $this->base_layout;
    }

    public function replace(string $tag, string $content): LayoutBuilder
    {
        if($tag == "title"){
            $content.= " - ReadMe";
        }
        $this->base_layout = str_replace("{{" . $tag . "}}", $content, $this->base_layout);
        return $this;
    }


}