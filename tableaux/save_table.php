<?php

include_once('include/fonction.php');

header('Content-Type: application/json; charset=UTF-8');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['data']) || !isset($input['tableIndex'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Données invalides', 'code' => 400], JSON_UNESCAPED_UNICODE);
    exit;
}

// Récupération des paramètres
$tableIndex = $input['tableIndex'];
$titre = $input['titre'];
$note = $input['note'];
$alignementTitre = $input["alignementTitre"];
$alignementNote = $input["alignementNote"];
$data = $input['data'];
$attributes = $input['attributes'];
$stylesArray = $input['stylesArray'];
$colWidths = $input['colWidths'];
$sannee = $input['annee'];
$art = $input['art'];
$sdocdeb = $input['doc'];
$agrandissement = $input['agrandissement'];

// $tableWidth = array_sum($colWidths) / $agrandissement + 4 * count($colWidths);

require_once("include/save_table_html.php");

$filename = "tableau.html";
file_put_contents($filename, $html);

// Retour du résultat
echo json_encode(['message' => "Tableau " . $tableIndex+1 . " enregistré avec succès"], JSON_UNESCAPED_UNICODE);

