<?php

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
                <form action='RemoveEditAlbum.php' method='post'>
                    <fieldset>
                        <legend>Azioni possibili</legend>
                        <input type='hidden' name='id' value='".$this->id."'>
                        <input type='submit' value='Rimuovi'>
                        <input type='submit' value='Modifica'>
                    </fieldset>
                </form>
            ";
        }
        return "
            <li>
                <img src='assets/albumPhotos/".$this->file_name."' alt='Copertina album ".$this->name."'>
                <a href='album.php?id=".$this->id."' aria-label='Visualizza canzoni appartenenti a ".$this->name."'>".$this->name."</a>
                ".$additional_elements."
            </li>
        ";
    }

}