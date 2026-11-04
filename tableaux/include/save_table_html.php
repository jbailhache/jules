<?php

// Créer une grille pour suivre les cellules occupées par rowspan/colspan
$occupied = [];
foreach ($data as $rowIndex => $row) {
    $occupied[$rowIndex] = array_fill(0, count($row), false);
}

// Marquer les cellules occupées par les fusions
foreach ($mergeCells as $merge) {
    $row = $merge['row'];
    $col = $merge['col'];
    $rowspan = $merge['rowspan'];
    $colspan = $merge['colspan'];
    for ($r = 0; $r < $rowspan; $r++) {
        for ($c = 0; $c < $colspan; $c++) {
            if ($row + $r < count($data) && $col + $c < count($data[0])) {
                $occupied[$row + $r][$col + $c] = true;
            }
        }
    }
}

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

$escapedTitre = customTagsToHtml($titre);
$html = $pDeb.$escapedTitre.$pFin . '<table border="1">' . "\n";
$rowNum = 0;
foreach ($data as $rowIndex => $row) {
    // echo " $rowIndex:".print_r($row,true);
    $rowNum++;
    
    // Tableaux : Titres des colonnes traités comme des cellules ordinaires
    if ($rowNum == 1) {
        continue;
    }

    $html .= '  <tr>' . "\n";
    foreach ($row as $colIndex => $cell) {
        // echo " ($rowIndex,$colIndex):$cell ";
        // echo " row:$rowIndex col:$colIndex ";
        $cellStyle = $styles[$rowIndex][$colIndex];
        $correctif = "overflow: hidden;";
        if (!strpos($cellStyle, $correctif)) {
            $cellStyle .= " $correctif";
        }
        $correctif = "padding-right: 1px;";
        if (!strpos($cellStyle, $correctif)) {
            $cellStyle .= " $correctif";
        }

        // Ignorer les cellules occupées par une fusion (sauf la cellule de départ)
        // if ($occupied[$rowIndex][$colIndex] && !($cell !== null)) {
        if ($occupied[$rowIndex][$colIndex] && empty($cell)) {
            // echo "Cellule occupée"; exit();
            continue;
        }

        $cellAttributes = '';
        // Ajouter tous les attributs sauf rowspan/colspan (gérés par mergeCells)
        /*foreach ($attributes[$rowIndex][$colIndex] as $attrName => $attrValue) {
            if ($attrName !== 'rowspan' && $attrName !== 'colspan') {
                $cellAttributes .= ' ' . htmlspecialchars($attrName) . '="' . htmlspecialchars($attrValue) . '"';
            }
        }*/
        /*if ($rowIndex == 0) {
            $rowIndex1 = 0;
        } else {
            $rowIndex1 = 1;
        }
        if ($colIndex == 0) {
            $colIndex1 = 0;
        } else {
            $colIndex1 = 1;
        }*/
        $rowIndex1 = $rowIndex - 1;
        $colIndex1 = $colIndex;
        // echo " attributes=<pre>".print_r($attributes,true); exit();
        if (is_array($attributes) 
            && array_key_exists($rowIndex1, $attributes) && is_array($attributes[$rowIndex1]) 
            && array_key_exists($colIndex1, $attributes[$rowIndex1]) && is_array($attributes[$rowIndex1][$colIndex1])) {
            foreach ($attributes[$rowIndex1][$colIndex1] as $attrName => $attrValue) {
                if ($attrName !== 'rowspan' && $attrName !== 'colspan') {
                    $cellAttributes .= ' ' . htmlspecialchars($attrName) . '="' . htmlspecialchars($attrValue) . '"';
                }
            }
        }
        // Ajouter rowspan/colspan depuis mergeCells
        foreach ($mergeCells as $merge) {
            if ($merge['row'] === $rowIndex && $merge['col'] === $colIndex) {
                if ($merge['rowspan'] > 1) $cellAttributes .= ' rowspan="' . $merge['rowspan'] . '"';
                if ($merge['colspan'] > 1) $cellAttributes .= ' colspan="' . $merge['colspan'] . '"';
            }
        }

        // echo " initialement cellAttributes='$cellAttributes' "; 

        // Enlever le style
        $cellAttributes = preg_replace('/style="[^"]*"\s*/', '', $cellAttributes);

        // Enlever width
        $cellAttributes = preg_replace('/\s*width="[^"]*\s*"/', '', $cellAttributes);

        $escapedCell = customTagsToHtml($cell);

        // echo " cellStyle='$cellStyle' cellAttributes='$cellAttributes' "; 

        if ($rowNum == 1) {
        } elseif ($rowNum == 2) { // Tableaux : Titres des colonnes traités comme des cellules ordinaires
            // $html .= '    <th style="' . $cellStyle . '" ' . $cellAttributes . '>' . $escapedCell . '</th>' . "\n";
            $html .= '    <th style="' . $cellStyle . '" ' . $cellAttributes . ' width="' . $colWidths[$colIndex] / $agrandissement . '">' . $escapedCell . '</th>' . "\n";
        } else {
            // $html .= '    <td' . $cellAttributes . '>' . $escapedCell . '</td>' . "\n";
            $html .= '    <td style="' . $cellStyle . '" ' . $cellAttributes . '>' . $escapedCell . '</td>' . "\n";
        }
        // echo " html='$html' "; exit();
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

