<?php

require_once __DIR__ . "/../handlers/url_utils.php";
require_once __DIR__ . "/../handlers/pages.php";

function getErrorePermessi(): string
{
    $html = file_get_contents(__DIR__ . "/errorePermessi.html");
    return str_replace("{{url}}", UrlUtils::getUrl(Pages::$pages["Home"]), $html);
}
