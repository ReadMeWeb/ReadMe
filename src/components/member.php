<?php

class Member {
    private string $name;
    private string $description;
    private string $photo_url;

    public function __construct($name, $description, $photo_url) {
        $this->name = $name;
        $this->description = $description;
        $this->photo_url = $photo_url;
    }

    function getName(): string {
        return $this->name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getPhotoUrl(): string {
        return $this->photo_url;
    }

    public function generateMemberCard(): string {
        return "
            <dt>$this->name</dt>
            <dd>
                <img src=\"$this->photo_url\" class=\"ritratto\" alt=\"\">
                <dl class=\"membro\">
                    <dt>Storia</dt>
                    <dd>$this->description</dd>
                </dl>
            </dd>
        ";
    }
}