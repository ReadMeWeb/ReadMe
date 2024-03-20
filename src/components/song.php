<?php

set_include_path($_SERVER['DOCUMENT_ROOT']);
require_once 'include/pages.php';

class song
{
    private string $id;
    private string $producer;
    private string $producer_name;
    private string $name;
    private string $audio_file_name;
    private string $graphic_file_name;

    public function __construct(
        $id,
        $producer,
        $producer_name,
        $name,
        $audio_file_name,
        $graphic_file_name
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->producer = $producer;
        $this->producer_name = $producer_name;
        $this->audio_file_name = $audio_file_name;
        $this->graphic_file_name = $graphic_file_name;
    }

    public function toHtml(): string
    {
        $additional_elements = "";
        if($_SESSION["user"]["status"] == "ADMIN"){
            // TODO CONFLITTO
            // <form action='/Pages/addSong.php' method='get'>
            $additional_elements = "
                <form action='".pages['RemoveUpdateSong']."' method='post'>
                    <fieldset>
                        <legend>Azione di Modifica</legend>
                        <input type='hidden' name='id' value='" .
                $this->id .
                "'>
                        <input type='hidden' name='producer' value='" .
                $this->producer .
                "'>
                        <input type='submit' name='update' value='Modifica'>
                    </fieldset>
                </form>
                <form action='/Pages/addSong.php' method='get'>
                    <fieldset>
                        <legend>Azione di Rimozione</legend>
                        <input type='hidden' name='id' value='" .
                $this->id .
                "'>
                        <input type='submit' name='delete' value='Rimuovi'>
                    </fieldset>
                </form>
            ";
        }
        if($_SESSION["user"]["status"] == "USER"){
            $additional_elements = "
                <form action='".pages['AddToPlaylist']."' method='post'>
                    <fieldset>
                        <legend>Azioni possibili</legend>
                        <input type='hidden' name='producer' value='" .
                $this->producer .
                "'>
                        <input type='hidden' name='name' value='" .
                $this->name .
                "'>
                        <input type='submit' value='Aggiungi alla playlist'>
                    </fieldset>
                </form>
            ";
        }
        return "
            <li>
                <img src='".assets['songPhotos'].$this->graphic_file_name."' alt='Copertina della canzone ".$this->name."'>
                <p>".$this->name."</p>
                <a href='".pages['Artista']."?id=".$this->producer."' aria-label='Vai alla pagina personale di ".$this->producer_name."'>".$this->producer_name."</a>
                <audio controls>
                    <source src='".assets['songAudios'].$this->audio_file_name."' type='audio/mpeg'>
                    Attenzione: il tuo browser non supporta i tag audio (la preghiamo di cambiare browser).
                </audio>
                " .
            $additional_elements .
            "
            </li>
        ";
    }
}
