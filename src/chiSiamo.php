<?php
require_once "./components/navbar.php";

setlocale(LC_ALL, 'it_IT');

$title = "Chi Siamo - Orchestra";
$description = "In questa pagina troverai la storia dei creatori del progetto, la nostra vision e la nostra mission!";
$keywords = implode(', ', array('Orchestra', 'storia della musica classica', 'musica classica', 'player musicale', 'player gratuito', 'chi siamo', 'creatori'));

$html = file_get_contents("./components/layout.html");
$content = file_get_contents("./components/chiSiamo.html");

$placeholdersTemplates = array("{{title}}", "{{description}}", "{{keywords}}", "{{menu}}", "{{breadcrumbs}}", "{{content}}");
$placeholdersValues = array($title, $description, $keywords, "{{menu}}", "{{breadcrumbs}}", $content);

$html = str_replace($placeholdersTemplates, $placeholdersValues, $html);

echo $html;