<?php

class song
{
    private string $producer;
    private string $producer_name;
    private string $name;
    private string $audio_file_name;
    private string $graphic_file_name;

    public function __construct($producer,$producer_name,$name,$audio_file_name,$graphic_file_name)
    {
        $this->name = $name;
        $this->producer = $producer;
        $this->producer_name= $producer_name;
        $this->audio_file_name = $audio_file_name;
        $this->graphic_file_name = $graphic_file_name;
    }

    public function toHtml(): string{
        $additional_elements = "";
        if($_SESSION["user"]["status"] == "ADMIN"){
            $additional_elements = "
                <form action='RemoveUpdateSong.php' method='post'>
                    <fieldset>
                        <legend>Azioni possibili</legend>
                        <input type='hidden' name='producer' value='".$this->producer."'>
                        <input type='hidden' name='name' value='".$this->name."'>
                        <input type='submit' value='Rimuovi'>
                        <input type='submit' value='Modifica'>
                    </fieldset>
                </form>
            ";
        }
        if($_SESSION["user"]["status"] == "USER"){
            $additional_elements = "
                <form action='addToPlaylist.php' method='post'>
                    <fieldset>
                        <legend>Azioni possibili</legend>
                        <input type='hidden' name='producer' value='".$this->producer."'>
                        <input type='hidden' name='name' value='".$this->name."'>
                        <input type='submit' value='Aggiungi alla playlist'>
                    </fieldset>
                </form>
            ";
        }
        return "
            <li>
                <img src='assets/songPhotos/".$this->graphic_file_name."' alt='Copertina della canzone ".$this->name."'>
                <p>".$this->name."</p>
                <a href='artist.php?id=".$this->producer."' aria-label='Vai alla pagina personale di ".$this->producer_name."'>".$this->producer_name."</a>
                <audio controls>
                    <source src='songAudios/".$this->audio_file_name."' type='audio/mpeg'>
                    Attenzione: il tuo browser non supporta i tag audio (la preghiamo di cambiare browser).
                </audio>
                ".$additional_elements."
            </li>
        ";
    }

}