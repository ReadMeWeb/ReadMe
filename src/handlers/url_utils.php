<?php
class UrlUtils {
    static function getUrl(string $page) : string {
        return "http://" . $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . "/" . $page;
    }
}