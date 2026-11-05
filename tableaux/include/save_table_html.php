<?php

$styles = [];
foreach ($data as $rowIndex => $row) {
    $styles[$rowIndex] = array_fill(0, count($row), false);
}

foreach ($stylesArray as $item) {
    $row = $item['row'];
    $col = $item['col'];
    $style = $item['style'];
    $styles[$row][$col] = $style;
}

if($alignementTitre == 'G'){

    $pDeb = '<p style="width: ' . $tableWidth . 'px; text-align: left;">';
    $pFin = '</p>';

} else if($alignementTitre == 'D'){

    $pDeb = '<p style="width: ' . $tableWidth . 'px; text-align: right;">';
    $pFin = '</p>';

} else if($alignementTitre == 'C'){

    $pDeb = '<p style="width: ' . $tableWidth . 'px; text-align: center;">';
    $pFin = '</p>';

} else {

    $pDeb = '<p>';
    $pFin = '</p>';
}

// Traitement des cellules fusionnées
$cellRefToCoords = function(string $ref): ?array {
    if (!preg_match('/^([A-Z]+)([0-9]+)$/', $ref, $matches)) return null;
    $colLetters = $matches[1];
    $rowNumber = (int)$matches[2];
    $col = 0;
    for ($i = 0; $i < strlen($colLetters); $i++) {
        $col = $col * 26 + (ord($colLetters[$i]) - 64);
    }
    $col -= 1;
    return [$col, $rowNumber - 1]; // 0-based data1 row
};

$mergedAnchors = []; // [rowIndex => [colIndex => [colspan, rowspan]]]
$coveredCells = [];  // [rowIndex => [colIndex => true]]

if (!empty($mergeCells) && is_array($mergeCells)) {
    foreach ($mergeCells as $ref => $dims) {
        $coords = $cellRefToCoords($ref);
        if (!$coords) continue;
        $c = $coords[0];
        $r = $coords[1]; // index dans data1 (soit index - 1 dans $data qui contient headers en ligne 0)
        $dataRowIndex = $r + 1; // index correspondant dans $data
        $colspan = (int)$dims[0];
        $rowspan = (int)$dims[1];

        $mergedAnchors[$dataRowIndex][$c] = [$colspan, $rowspan];

        for ($dr = 0; $dr < $rowspan; $dr++) {
            for ($dc = 0; $dc < $colspan; $dc++) {
                if ($dr > 0 || $dc > 0) {
                    $coveredCells[$dataRowIndex + $dr][$c + $dc] = true;
                }
            }
        }
    }
}

$escapedTitre = customTagsToHtml($titre);
$html = $pDeb.$escapedTitre.$pFin . '<table border="1">' . "\n";
$rowNum = 0;
foreach ($data as $rowIndex => $row) {

    $rowNum++;

    // Tableaux : Titres des colonnes traités comme des cellules ordinaires
    if ($rowNum == 1) {
        continue;
    }

    $html .= '  <tr>' . "\n";
    foreach ($row as $colIndex => $cell) {
        // Ignorer les cellules recouvertes par une fusion
        if (!empty($coveredCells[$rowIndex][$colIndex])) {
            continue;
        }
        $cellStyle = $styles[$rowIndex][$colIndex];
        $correctif = "overflow: hidden;";
        if (!strpos($cellStyle, $correctif)) {
            $cellStyle .= " $correctif";
        }
        $correctif = "padding-right: 1px;";
        if (!strpos($cellStyle, $correctif)) {
            $cellStyle .= " $correctif";
        }

        $cellAttributes = '';
        $rowIndex1 = $rowIndex - 1;
        $colIndex1 = $colIndex;
        if (is_array($attributes)
            && array_key_exists($rowIndex1, $attributes) && is_array($attributes[$rowIndex1])
            && array_key_exists($colIndex1, $attributes[$rowIndex1]) && is_array($attributes[$rowIndex1][$colIndex1])) {
            foreach ($attributes[$rowIndex1][$colIndex1] as $attrName => $attrValue) {
                if ($attrName !== 'rowspan' && $attrName !== 'colspan') {
                    $cellAttributes .= ' ' . htmlspecialchars($attrName) . '="' . htmlspecialchars($attrValue) . '"';
                }
            }
        }

        // Enlever le style
        $cellAttributes = preg_replace('/style="[^"]*"\s*/', '', $cellAttributes);

        // Enlever width
        $cellAttributes = preg_replace('/\s*width="[^"]*\s*"/', '', $cellAttributes);

        // Ajouter colspan / rowspan si cellule ancre d'une fusion
        $cellColspan = 1;
        if (isset($mergedAnchors[$rowIndex][$colIndex])) {
            $mergeDim = $mergedAnchors[$rowIndex][$colIndex];
            $cellColspan = $mergeDim[0];
            if ($mergeDim[0] > 1) {
                $cellAttributes .= ' colspan="' . $mergeDim[0] . '"';
            }
            if ($mergeDim[1] > 1) {
                $cellAttributes .= ' rowspan="' . $mergeDim[1] . '"';
            }
        }

        $escapedCell = customTagsToHtml($cell);

        if ($rowNum == 1) {
        } elseif ($rowNum == 2) { // Tableaux : Titres des colonnes traités comme des cellules ordinaires
            $thWidth = 0;
            for ($c = 0; $c < $cellColspan; $c++) {
                if (isset($colWidths[$colIndex + $c])) {
                    $thWidth += $colWidths[$colIndex + $c];
                }
            }
            $html .= '    <th style="' . $cellStyle . '" ' . $cellAttributes . ' width="' . $thWidth / $agrandissement . '">' . $escapedCell . '</th>' . "\n";
        } else {
            $html .= '    <td style="' . $cellStyle . '" ' . $cellAttributes . '>' . $escapedCell . '</td>' . "\n";
        }
    }
    $html .= '  </tr>' . "\n";
}

if($alignementNote == 'G'){

    $pDeb = '<p style="width: ' . $tableWidth . 'px; text-align: left;">';
    $pFin = '</p>';

} else if($alignementNote == 'D'){

    $pDeb = '<p style="width: ' . $tableWidth . 'px; text-align: right;">';
    $pFin = '</p>';

} else if($alignementNote == 'C'){

    $pDeb = '<p style="width: ' . $tableWidth . 'px; text-align: center;">';
    $pFin = '</p>';

} else {

    $pDeb = '<p>';
    $pFin = '</p>';
}

$escapedNote = customTagsToHtml($note);
$html .= '</table>' . $pDeb.$escapedNote.$pFin;
