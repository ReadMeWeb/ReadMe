<?php
$get_home = function () {
    echo "sono in get home";
    echo "
        <form action='/Pages/home.php' method='get'><input type='submit' name='create' value='create'></form>
    ";
    echo "
        <form action='/Pages/home.php' method='get'><input type='submit' name='delete' value='delete'></form>
    ";
    echo "
        <form action='/Pages/home.php' method='get'><input type='submit' name='update' value='update'></form>
    ";
};
$get_create_home = function () {
    echo "sono in get create home";
};
$get_update_home = function () {
    echo "sono in get update home";
};
$get_delete_home = function () {
    echo "sono in get delete home";
};
