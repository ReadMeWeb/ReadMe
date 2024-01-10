<?php

function getAndClearErrorStringFromSession(string $name): string | null
{
    $err = "SERROR_" . $name;
    if (array_key_exists($err, $_SESSION)) {
        $err_msg = $_SESSION[$err];
        unset($_SESSION[$err]);
        return $err_msg;
    }
    return null;
}

function setErrorStringToSession(string $name, string $err_msg): void
{
    $err = "SERROR_" . $name;
    if (array_key_exists($err, $_SESSION)) {
        $_SESSION[$err] .= "<p>" . $err_msg . "</p>";
    } else {
        $_SESSION[$err] = "<p>" . $err_msg . "</p>";
    }
}

function getPermissionDeniedPage(string $title, string $description, string $keywords, Breadcrumbs $breadcrumbs): string
{
    $layout = file_get_contents(__DIR__ . "/../components/layout.html");
    $placeholdersTemplates = array("{{title}}", "{{description}}", "{{keywords}}", "{{menu}}", "{{breadcrumbs}}", "{{content}}");
    $placeholdersValues = array($title, $description, $keywords, navbar(), $breadcrumbs->getBreadcrumbsHtml(), getErrorePermessi());
    return str_replace($placeholdersTemplates, $placeholdersValues, $layout);
}
