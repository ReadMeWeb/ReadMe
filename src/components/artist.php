<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'include/pages.php';

class artist
{
    private string $id;
    private string $name;
    private string $biography;
    private string $file_name;

    public function __construct($id,$name,$biography,$file_name)
    {
        $this->name = $name;
        $this->id = $id;
        $this->biography = $biography;
        $this->file_name = $file_name;
    }

    public function toHtml(): string{
        // TODO: COME AGGIORNARE POSSIBILI AZIONI
        $additional_elements = "";
        if($_SESSION["user"]["status"] == "ADMIN"){
            $additional_elements = "
            <a href='" . pages['Artista'] . "?id=" . $this->id . "&update=true' > Modifica </a>
            <form action='".pages['Artista']."' method='post'>
                <fieldset>
                    <legend>Azioni possibili</legend>
                    <input type='hidden' name='id' value='".$this->id."'>
                    <input type='submit' name='delete' value='Rimuovi'>
                </fieldset>
            </form>
            ";
        }
        return "
            <li>
                <img src='".assets['artistPhotos'].$this->file_name."' alt='Immagine profilo di ".$this->name."'>
                <a href='".pages['Artista']."?id=".$this->id."' aria-label='Vai alla pagina personale di ".$this->name."'>".$this->name."</a>
                ".$additional_elements."
            </li>
        ";
    }

}
