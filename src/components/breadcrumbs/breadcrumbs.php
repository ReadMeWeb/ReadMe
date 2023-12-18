<?php
require_once "breadcrumbItem.php";

class Breadcrumbs
{
    private array $items;
    private const DIVIDER = "&gt&gt";

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function getBreadcrumbsHtml(): string
    {
        $html = "<p>Ti trovi in: ";
        for($i = 0; $i < sizeof($this->items); $i++){
            $html .= $this->items[$i]->getBreadcrumbItemString();
            if($i < sizeof($this->items) - 1){
                $html .= " " . self::DIVIDER . " ";
            }
        }
        return $html . "</p>";
    }
}
