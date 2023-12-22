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
        return "
            <li>
                <img src='assets/songPhotos/".$this->graphic_file_name."' alt='Copertina della canzone ".$this->name."'>
                <p>".$this->name."</p>
                <a href='artist.php?id=".$this->producer."' aria-label='Vai alla pagina personale di ".$this->producer_name."'>".$this->producer_name."</a>
                <audio controls>
                    <source src='songAudios/".$this->audio_file_name."' type='audio/mpeg'>
                    Attenzione: il tuo browser non supporta i tag audio (la preghiamo di cambiare browser).
                </audio>
            </li>
        ";
    }

}