<?php

require_once 'pages.php';

class Breadcrumbs {
  private array $items;
  private const DIVIDER = "&gt&gt";

  public function __construct(array $items) {
    $this->items = $items;
  }

  public function getBreadcrumbsHtml(): string {
    $html = "<p>Ti trovi in: ";
    for ($i = 0; $i < sizeof($this->items); $i++) {
      $html .= $this->items[$i]->getBreadcrumbItemString();
      if ($i < sizeof($this->items) - 1) {
        $html .= " " . self::DIVIDER . " ";
      }
    }
    return $html . "</p>";
  }
}

class BreadcrumbsBuilder {
  private array $items;

  public function __construct() {
  }

  public function addBreadcrumb(BreadcrumbItem $item): BreadcrumbsBuilder {
    $this->items[] = $item;
    return $this;
  }

  public function build(): Breadcrumbs {
    return new Breadcrumbs($this->items);
  }
}

class BreadcrumbItem {
  private string $name;
  private string $link;
  private bool $isCurrent;

  public function __construct($name, $isCurrent = false) {
    $this->name = $name;
    $this->link = pages[$name];
    $this->isCurrent = $isCurrent;
  }

  public function getBreadcrumbItemString(): string {
    if ($this->isCurrent) return $this->name;
    return "<a href=\"" . $this->link . "\">" . $this->name . "</a>";
  }
}
