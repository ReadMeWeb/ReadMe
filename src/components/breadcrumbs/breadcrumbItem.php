<?php
require_once __DIR__ . "/../../handlers/url_utils.php";
require_once __DIR__ . "/../../handlers/pages.php";
class BreadcrumbItem
{
    private string $name;
    private string $link;
    private bool $isCurrent;

    public function __construct($name, $isCurrent = false)
    {
        $this->name = $name;
        $this->link = UrlUtils::getUrl(Pages::$pages[$name]);
        $this->isCurrent = $isCurrent;
    }

    public function getBreadcrumbItemString(): string
    {
        if ($this->isCurrent) return $this->name;
        return "<a href=\"" . $this->link . "\">" . $this->name . "</a>";
    }
}
