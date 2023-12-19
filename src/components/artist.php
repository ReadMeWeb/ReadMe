<?php

class artist
{
    private string $id;
    private string $name;
    private string $biography;
    private string $file_name;

    static string $template;

    public function __construct($id,$name,$biography,$file_name)
    {
        $this->name = $name;
        $this->id = $id;
        $this->biography = $biography;
        $this->file_name = $file_name;
    }

    public function toHtml(): string{
        return "
            <dt>
                <a href='artist.php?id=".$this->id."' aria-label='Vai alla pagina personale di ".$this->name."'>".$this->name."</a>
            </dt>
            <dd>
                <img src='assets/artistPhotos/".$this->file_name."' alt='Immagine profilo di ".$this->name."'>
            </dd>
        ";
    }

}