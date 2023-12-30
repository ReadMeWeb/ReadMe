<?php

function sessionUserIsAdmin(): bool
{
    return $_SESSION["user"]["status"] == "ADMIN";
}
