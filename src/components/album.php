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
        return "
            <li>
                <img src='assets/albumPhotos/".$this->file_name."' alt='Copertina album ".$this->name."'>
                <a href='album.php?id=".$this->id."' aria-label='Visualizza canzoni appartenenti a ".$this->name."'>".$this->name."</a>
            </li>
        ";
    }

}