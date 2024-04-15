<?php

namespace Pangine\utils;

class LayoutBuilder
{
    private string $base_layout;
    private array $replacers;

    public function __construct(string $layout_type = "pub")
    {
        if ($layout_type == "priv") {
            $this->base_layout = file_get_contents(__DIR__ . "/../../templates/private_layout.html");
        } else {
            $this->base_layout = file_get_contents(__DIR__ . "/../../templates/public_layout.html");
        }
    }

    public function build(): string
    {
        if (isset($_SESSION["parameters"])) {
            foreach ($_SESSION["parameters"] as $parameter_name => $parameter_metadata) {
                if (isset($parameter_metadata["value"])) {
                    $this->base_layout = str_replace("{{" . $parameter_name . "-value}}", $parameter_metadata["value"], $this->base_layout);
                }
                if (isset($parameter_metadata["message"])) {
                    $this->base_layout = str_replace("{{" . $parameter_name . "-message}}", $parameter_metadata["message"], $this->base_layout);
                }
            }
        }
        foreach ($this->replacers as $replacer) {
            $replacer();
        }
        if (strpos($this->base_layout, "{{")) {
            throw new Exception500("Non tutti i tag sono stati consumati dalla pagina precedente.");
        }
        return $this->base_layout;
    }

    public function lazy_replace(string $tag, string $content): LayoutBuilder
    {
        $this->replacers[] = function () use ($tag, $content): void {
            if ($tag == "title") {
                $content .= " - ReadMe";
            }
            $this->base_layout = str_replace("{{" . $tag . "}}", $content, $this->base_layout);
        };
        return $this;
    }

    public function istant_replace(string $tag, string $content): LayoutBuilder
    {
        if ($tag == "title") {
            $content .= " - ReadMe";
        }
        $this->base_layout = str_replace("{{" . $tag . "}}", $content, $this->base_layout);
        return $this;
    }


}