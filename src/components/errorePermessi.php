<?php

set_include_path($_SERVER["DOCUMENT_ROOT"]);
require_once __DIR__ . "/../include/pages.php";

function getErrorePermessi(): string
{
    $html = file_get_contents(__DIR__ . "/errorePermessi.html");
    return str_replace("{{url}}", pages["Home"], $html);
}
