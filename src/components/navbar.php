<?php
function navbar(array $links, string $selectedLink){
    $navLinks = "";
    foreach ($links as $link){
        $classToApply = "";
        $pageName = str_replace(".html","",$link);
        $pageName = str_replace(".php","",$pageName);
        if($selectedLink == $link || $selectedLink == $pageName){
            $classToApply = "class='selectedNavLink'";
        }
        $navLinks .= "<li><a ". $classToApply ." href='". $link ."'>" . $pageName . "</a></li>";
    }
    return $navLinks;
}