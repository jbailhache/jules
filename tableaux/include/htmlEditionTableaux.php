<?php
/**
 * Estime la largeur d'une colonne en tenant compte des retours à la ligne
 */
function estimateColumnWidth(array $columnValues, int $min = 80, int $max = 400, float $charWidth = 8.2): int
{
    $maxLineLength = 0;

    foreach ($columnValues as $value) {
        $text = strip_tags((string)$value);
        // On découpe sur les retours à la ligne (réels ou <br>)
        $lines = preg_split('/\r\n|\r|\n/i', $text);

        foreach ($lines as $line) {
            $length = mb_strlen(trim($line));
            if ($length > $maxLineLength) {
                $maxLineLength = $length;
            }
        }
    }

    $estimated = (int) round($maxLineLength * $charWidth + 24); // +24 pour le padding
    return max($min, min($max, $estimated));
}


/**
 * Récupère toutes les valeurs textuelles d'une colonne à partir du DOM
 */
function getColumnTextsFromDom(DOMElement $table, int $colIndex): array
{
    $texts = [];
    $rows = $table->getElementsByTagName('tr');

    foreach ($rows as $row) {
        $cells = [];

        // On récupère th + td dans l'ordre
        foreach ($row->childNodes as $node) {
            if ($node->nodeName === 'th' || $node->nodeName === 'td') {
                $cells[] = $node;
            }
        }

        if (isset($cells[$colIndex])) {
            $texts[] = $cells[$colIndex]->textContent;
        }
    }

    return $texts;
}


function htmlEditionTableaux($index, $htmlTable) {
    global $json;

    if (preg_match ('#^(.*?)<table.*?</table>(.*)$#is', $htmlTable, $matches)) {
        $titre = $matches[1];
        $note = $matches[2];
    }

    $htmlTable = htmlToCustomTags($htmlTable);

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $doc->loadHTML('<?xml encoding="UTF-8">' . $htmlTable);
    libxml_clear_errors();

    $tables = $doc->getElementsByTagName('table');
    if ($tables->length === 0) {
        http_response_code(500);
        echo json_encode(['message' => 'Aucun tableau trouvé dans le HTML', 'code' => 500], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $table = $tables->item(0);

    $rows = $table->getElementsByTagName('tr');
    $data = [];
    $attributes = [];

    // Récupération des largeurs des colonnes
    $colWidths = [];
    if ($rows->length > 0) {
        $firstRow = $rows->item(0);
        $cells = $firstRow->getElementsByTagName('th');
        // $nbCols = $cells->length;
        if ($cells->length === 0) {
            $cells = $firstRow->getElementsByTagName('td');
        }
        foreach ($cells as $cell) {
            $width = null;
            // 1. Attribut width="..."
            if ($cell->hasAttribute('width')) {
                $width = (int) $cell->getAttribute('width');
            }
            // 2. Style inline style="width: ..."
            if (!$width && $cell->hasAttribute('style')) {
                if (preg_match('/width\s*:\s*(\d+)px?/i', $cell->getAttribute('style'), $matches)) {
                    $width = (int) $matches[1];
                }
            }
            // Fallback
            $colWidths[] = $width ?: null;
        }
    }

    $nbCols = count($colWidths);

    // 2. Estimation uniquement pour les colonnes sans largeur explicite

    for ($col = 0; $col < $nbCols; $col++) {
        if (!empty($colWidths[$col])) {
            continue; // largeur déjà connue
        }

        $columnTexts = getColumnTextsFromDom($table, $col);
        $colWidths[$col] = estimateColumnWidth($columnTexts);
    }

    // Sécurité : s'assurer qu'on a bien une largeur pour chaque colonne
    for ($col = 0; $col < $nbCols; $col++) {
        if (empty($colWidths[$col])) {
            $colWidths[$col] = 250;
        }
    }

    // Helper pour convertir index colonne -> lettres (ex: 0 -> A, 1 -> B, ...)
    $colToLetterFunc = function(int $col): string {
        $s = '';
        while ($col >= 0) {
            $s = chr(65 + ($col % 26)) . $s;
            $col = (int)($col / 26) - 1;
        }
        return $s;
    };

    $mergeCells = [];
    $data = [];
    $attributes = [];

    // Détermination de la grille et des fusions
    for ($rowIndex = 0; $rowIndex < $rows->length; $rowIndex++) {
        $row = $rows->item($rowIndex);
        $colIndex = 0;

        foreach ($row->childNodes as $node) {
            if ($node->nodeName !== 'th' && $node->nodeName !== 'td') {
                continue;
            }

            // Avancer jusqu'à la prochaine cellule non occupée par un rowspan/colspan précédent
            while (isset($data[$rowIndex][$colIndex])) {
                $colIndex++;
            }

            $colspan = $node->hasAttribute('colspan') ? max(1, (int)$node->getAttribute('colspan')) : 1;
            $rowspan = $node->hasAttribute('rowspan') ? max(1, (int)$node->getAttribute('rowspan')) : 1;

            // Extraire le texte
            $text = trim(preg_replace('/\s+/', ' ', $node->nodeValue));
            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $data[$rowIndex][$colIndex] = $text;

            // Capturer tous les attributs
            $cellAttributes = [];
            foreach ($node->attributes as $attr) {
                $cellAttributes[$attr->name] = $attr->value;
            }
            $attributes[$rowIndex][$colIndex] = $cellAttributes;

            if ($colspan > 1 || $rowspan > 1) {
                $cellRef = $colToLetterFunc($colIndex) . ($rowIndex + 1);
                $mergeCells[$cellRef] = [$colspan, $rowspan];

                for ($dr = 0; $dr < $rowspan; $dr++) {
                    for ($dc = 0; $dc < $colspan; $dc++) {
                        if ($dr > 0 || $dc > 0) {
                            $data[$rowIndex + $dr][$colIndex + $dc] = '';
                            $attributes[$rowIndex + $dr][$colIndex + $dc] = [];
                        }
                    }
                }
            }

            $colIndex += $colspan;
        }
    }

    // Normaliser la grille (remplir les trous éventuels et aligner le nombre de colonnes)
    $maxCols = $nbCols;
    foreach ($data as $rowIndex => $rowCells) {
        $maxCols = max($maxCols, count($rowCells));
    }
    for ($rowIndex = 0; $rowIndex < $rows->length; $rowIndex++) {
        for ($colIndex = 0; $colIndex < $maxCols; $colIndex++) {
            if (!isset($data[$rowIndex][$colIndex])) {
                $data[$rowIndex][$colIndex] = null;
            }
            if (!isset($attributes[$rowIndex][$colIndex])) {
                $attributes[$rowIndex][$colIndex] = [];
            }
        }
        ksort($data[$rowIndex]);
        ksort($attributes[$rowIndex]);
        $data[$rowIndex] = array_values($data[$rowIndex]);
        $attributes[$rowIndex] = array_values($attributes[$rowIndex]);
    }

    $json[$index] =  json_encode([
        'data' => $data,
        'attributes' => $attributes,
        'colWidths' => $colWidths,
        'mergeCells' => $mergeCells,
        // 'htmlTable' => $htmlTable,
    ], JSON_UNESCAPED_UNICODE);

    if(str_contains($titre, "text-align: left")){
        $alignementTitre = "G";
        $styleTitre = "text-align: left";
    } else if(str_contains($titre, "text-align: right")){
        $alignementTitre = "D";
        $styleTitre = "text-align: right";
    } else if(str_contains($titre, "text-align: center")){
        $alignementTitre = "C";
        $styleTitre = "text-align: center";
    } else if(!str_contains($titre, "text-align")){
        $alignementTitre = "";
        $styleTitre = "";
    }

    if(str_contains($note, "text-align: left")){
        $alignementNote = "G";
        $styleNote = "text-align: left";
    } else if(str_contains($note, "text-align: right")){
        $alignementNote = "D";
        $styleNote = "text-align: right";
    } else if(str_contains($note, "text-align: center")){
        $alignementNote = "C";
        $styleNote = "text-align: center";
    } else if(!str_contains($note, "text-align")){
        $alignementNote = "";
        $styleNote = "";
    }

    $output = '
    <input id="titre" name="titre" size="120" value="' . strip_tags(htmlToCustomTags($titre)) . '" style="' . $styleTitre . '">
    <select name="alignement_titre" id="alignement_titre">
        <option value="">--Veuillez choisir une option--</option>
        <option value="G" ';

    if($alignementTitre == "G"){ $output .= "selected"; }

        $output .= '>Aligner à gauche</option>
        <option value="D" ';

    if($alignementTitre == "D"){ $output .= "selected"; }

        $output .= '>Aligner à droite</option>
        <option value="C" ';

    if($alignementTitre == "C"){ $output .= "selected"; }

        $output .= '>Aligner au centre</option>

    </select>
    <br>
    <div style="table-container" id="table-container-' . $index . '"></div>
    <br>
    <input id="note" name="note" size="120" value="' . strip_tags(htmlToCustomTags($note)) . '" style="' . $styleNote . '">
    <select name="alignement_note" id="alignement_note">
        <option value="">--Veuillez choisir une option--</option>
        <option value="G" ';

    if($alignementNote == "G"){ $output .= "selected"; }

        $output .= '>Aligner à gauche</option>
        <option value="D" ';

    if($alignementNote == "D"){ $output .= "selected"; }

        $output .= '>Aligner à droite</option>
        <option value="C" ';

    if($alignementNote == "C"){ $output .= "selected"; }

        $output .= '>Aligner au centre</option>

    </select>
    <p>
    <button class="save-button" id="save-button-' . $index . '">Enregistrer sur le serveur</button>
    <div id="html-output"></div>
    ';

    return $output;

}
