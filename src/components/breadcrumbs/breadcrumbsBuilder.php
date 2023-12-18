<?php
require_once "breadcrumbs.php";
require_once "breadcrumbItem.php";
class BreadcrumbsBuilder
{
    private array $items;

    public function __construct()
    {
    }

    public function addBreadcrumb(BreadcrumbItem $item): BreadcrumbsBuilder
    {
        $this->items[] = $item;
        return $this;
    }

    public function build(): Breadcrumbs
    {
        return new Breadcrumbs($this->items);
    }
}
