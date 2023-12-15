<?php
require_once "./components/navbar.php";
require_once "./components/member.php";

setlocale(LC_ALL, 'it_IT');

/* VARIABILI */

$title = "Chi Siamo - Orchestra";
$description = "In questa pagina troverai la storia dei creatori del progetto, la nostra vision e la nostra mission!";
$keywords = implode(', ', array('Orchestra', 'storia della musica classica', 'musica classica', 'player musicale', 'player gratuito', 'chi siamo', 'creatori'));

$member_list = array(
    new Member(
        "Alex",
        "Appassionato di algoritmi e logica sin dall'infanzia, Alex trasforma ogni problema in un'opportunità di
         risolvere enigmi. Con un debole per l'intelligenza artificiale, ama sviluppare algoritmi che rendano il
         mondo un posto più efficiente. Quando non è immerso nel codice, lo troverai scalando montagne o
         perdendosi in un buon romanzo fantasy.",
        "../assets/images/person_placeholder.jpeg"
    ),
    new Member(
        "Matteo",
        "Matteo è un creativo instancabile, sempre alla ricerca di nuove idee e soluzioni fuori dagli schemi. Il
        suo amore per il design si mescola perfettamente con la sua passione per il coding, creando esperienze
        utente coinvolgenti e innovative. Fuori dall'università, lo troverai in un laboratorio di arte
        contemporanea o sperimentando in cucina con nuove ricette.",
        "../assets/images/person_placeholder.jpeg"
    ),
    new Member(
        "Pedro",
        "Pedro è il guru della sicurezza informatica del gruppo, con una mente acuta per individuare e risolvere
        le vulnerabilità. La sua passione per l'etica hacker si combina con il suo impegno nel garantire la
        protezione dei dati. Quando non è dietro lo schermo, lo troverai immergersi in giochi da tavolo
        strategici o esplorando nuovi percorsi in bicicletta.",
        "../assets/images/person_placeholder.jpeg"
    ),
    new Member(
        "Riccardo",
        "Riccardo è il poliedrico del gruppo, con una passione sia per il backend che per il frontend dello
        sviluppo web. È un appassionato di clean code e architettura scalabile. Fuori dall'ambiente informatico,
        si dedica alla fotografia di paesaggi naturali o alle lunghe passeggiate ascoltando podcast di storia.",
        "../assets/images/person_placeholder.jpeg"
    )
);
$vision = "<h2>Visioneeeee</h2>";
$mission = "<h2>Missioneeeee</h2>";

/* GENERAZIONE HTML */

$html = file_get_contents("./components/layout.html");
$content = file_get_contents("./components/chiSiamo.html");

$membri_html = "";
foreach ($member_list as $member){
   $membri_html .= $member->generateMemberCard();
}

$placeholdersContentTemplates = array("{{membri}}", "{{vision}}", "{{mission}}");
$placeholdersContentValues = array($membri_html, $vision, $mission);
$content = str_replace($placeholdersContentTemplates, $placeholdersContentValues, $content);

$placeholdersTemplates = array("{{title}}", "{{description}}", "{{keywords}}", "{{menu}}", "{{breadcrumbs}}", "{{content}}");
$placeholdersValues = array($title, $description, $keywords, "{{menu}}", "{{breadcrumbs}}", $content);

$html = str_replace($placeholdersTemplates, $placeholdersValues, $html);

echo $html;