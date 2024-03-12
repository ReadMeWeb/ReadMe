<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'include/pages.php';

class album
{
    private string $id;
    private string $name;
    private string $file_name;

    public function __construct($id,$name,$file_name)
    {
        $this->name = $name;
        $this->id = $id;
        $this->file_name = $file_name;
    }

    public function toHtml(): string{
        $additional_elements = "";
        if($_SESSION["user"]["status"] == "ADMIN"){
            $additional_elements = "
                <form action='".pages['Modifica album']."' method='get'>
                    <fieldset>
                        <legend>Azioni possibili</legend>
                        <input type='hidden' name='id' value='".$this->id."'>
                        <!-- TODO rimozione tramite get potrebbe essere un rischio -->
                        <button type='submit' name='delete' value='true'>Rimuovi</button>
                        <button type='submit' name='update' value='true'>Modifica</button>
                    </fieldset>
                </form>
            ";
        }
        return "
            <li>
                <img src='".assets['albumPhotos'].$this->file_name."' alt='Copertina album ".$this->name."'>
                <a href='".pages['Ispeziona album']."&id=".$this->id."' aria-label='Visualizza canzoni appartenenti a ".$this->name."'>".$this->name."</a>
                ".$additional_elements."
            </li>
        ";
    }

}
