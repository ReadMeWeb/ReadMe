<?php

namespace Pangine\utils;

use Pangine\Pangine;

require_once(__DIR__ . "/../../Utils/Stream.php");

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
        $this->tag_istant_replace('pangine_path',Pangine::path());
    }

    public function build(): string
    {
        if (isset($_SESSION["parameters"]) && (new \Stream($_SESSION["parameters"]))->filter(fn($parameter) => isset($parameter["message"]))->count() != 0) {
            foreach ($_SESSION["parameters"] as $parameter_name => $parameter_metadata) {
                if (isset($parameter_metadata["value"])) {
                    $this->base_layout = str_replace("{{" . $parameter_name . "-value}}", $parameter_metadata["value"], $this->base_layout);
                }
                if (isset($parameter_metadata["message"])) {
                    $this->base_layout = str_replace("{{" . $parameter_name . "-message}}", "<p class='errorMessage'>" . $parameter_metadata["message"] . "</p>", $this->base_layout);
                }
            }
        }
        foreach ($this->replacers as $replacer) {
            $replacer();
        }
        if (isset($_SESSION["general-message"])) {
            $this->base_layout = str_replace("{{general-message}}", $_SESSION["general-message"], $this->base_layout);
            unset($_SESSION["general-message"]);
        }
        $this->base_layout = str_replace("{{general-message}}", "", $this->base_layout);
        $this->base_layout = str_replace("{{page_path}}", $_SERVER['REQUEST_URI'], $this->base_layout);
        if (strpos($this->base_layout, "{{")) {
            $misses = [];
            preg_match_all('/{{(.*?)}}/', $this->base_layout, $misses);
            $misses = '(' . implode(", ", $misses[1]) . ')';
            throw new Exception500("Non tutti i tag sono stati consumati dalla pagina precedente $misses.");
        }
        return $this->base_layout;
    }

    public function tag_lazy_replace(string $tag, string $content): LayoutBuilder
    {
        $this->replacers[] = function () use ($tag, $content): void {
            if ($tag == "title") {
                $content .= " - ReadMe";
            }
            $this->base_layout = str_replace("{{" . $tag . "}}", $content, $this->base_layout);
        };
        return $this;
    }

    public function tag_istant_replace(string $tag, string $content): LayoutBuilder
    {
        if ($tag == "title") {
            $content .= " - ReadMe";
        }
        $this->base_layout = str_replace("{{" . $tag . "}}", $content, $this->base_layout);
        return $this;
    }

    public function plain_lazy_replace(string $plain_text, string $content): LayoutBuilder
    {
        $this->replacers[] = function () use ($plain_text, $content): void {
            $this->base_layout = str_replace($plain_text, $content, $this->base_layout);
        };
        return $this;
    }

    public function plain_instant_replace(string $plain_text, string $content): LayoutBuilder
    {
        $this->base_layout = str_replace($plain_text, $content, $this->base_layout);
        return $this;
    }


}
