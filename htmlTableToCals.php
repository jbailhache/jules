<?php

function htmlTableToCals(string $html): string
{
    // Charger HTML en mode tolérant
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $table = $xpath->query('//table')->item(0);
    if (!$table) return "";

    // 1) récupérer toutes les lignes <tr>
    $rowsAll = $xpath->query('.//tr', $table);
    if ($rowsAll->length === 0) return "";

    // 2) déterminer le nombre réel de colonnes (en tenant compte des colspan)
    $numCols = 0;
    foreach ($rowsAll as $tr) {
        $count = 0;
        foreach ($xpath->query('./th|./td', $tr) as $cell) {
            $count += max(1, intval($cell->getAttribute('colspan')));
        }
        if ($count > $numCols) $numCols = $count;
    }
    if ($numCols === 0) return "";

    // 3) lire le colgroup (s'il existe) et préparer les pourcentages
    $colNodes = $xpath->query('.//colgroup/col', $table);
    $htmlPercentsRaw = [];
    foreach ($colNodes as $col) {
        $w = trim($col->getAttribute('width'));
        if ($w !== "" && preg_match('/^(\d+(\.\d+)?)\s*%$/', $w, $m)) {
            $htmlPercentsRaw[] = floatval($m[1]);
        } else {
            $htmlPercentsRaw[] = null;
        }
    }
    // compléter jusqu'au nombre réel de colonnes
    while (count($htmlPercentsRaw) < $numCols) $htmlPercentsRaw[] = null;

    // 4) répartir les pourcentages manquants
    $definedTotal = 0.0;
    $undefinedIdx = [];
    foreach ($htmlPercentsRaw as $i => $p) {
        if ($p === null) $undefinedIdx[] = $i;
        else $definedTotal += $p;
    }

    if ($definedTotal <= 0.0) {
        // aucune définition → répartition uniforme 100%
        $each = 100.0 / $numCols;
        for ($i = 0; $i < $numCols; $i++) $htmlPercentsRaw[$i] = $each;
    } else {
        $remaining = max(0.0, 100.0 - $definedTotal);
        $nUndefined = count($undefinedIdx);
        $auto = $nUndefined > 0 ? ($remaining / $nUndefined) : 0.0;
        foreach ($undefinedIdx as $idx) $htmlPercentsRaw[$idx] = $auto;
    }

    // 5) calculer les colwidth proportionnels (somme W)
    $TOTAL_WIDTH = 17.568;
    $sumPct = array_sum($htmlPercentsRaw);
    $colWidths = [];
    for ($i = 0; $i < $numCols; $i++) {
        $pct = $htmlPercentsRaw[$i];
        $colWidths[$i] = ($sumPct > 0.0) ? ($TOTAL_WIDTH * ($pct / $sumPct)) : ($TOTAL_WIDTH / $numCols);
    }

    // 6) sérialiseur strict des nœuds (préserve self-closing)
    $serializeNode = function (DOMNode $node) use (&$serializeNode): string {
        switch ($node->nodeType) {
            case XML_ELEMENT_NODE:
                $name = $node->nodeName;
                $attrs = "";
                if ($node->attributes && $node->attributes->length) {
                    foreach ($node->attributes as $a) {
                        $attrs .= ' ' . $a->nodeName . '="' . htmlspecialchars($a->nodeValue, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '"';
                    }
                }
                if (!$node->hasChildNodes()) {
                    return "<{$name}{$attrs}/>";
                }
                $inner = "";
                foreach ($node->childNodes as $child) $inner .= $serializeNode($child);
                return "<{$name}{$attrs}>{$inner}</{$name}>";
            case XML_TEXT_NODE:
                return htmlspecialchars($node->nodeValue, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            case XML_CDATA_SECTION_NODE:
                return "<![CDATA[" . $node->nodeValue . "]]>";
            case XML_COMMENT_NODE:
                return "<!--" . $node->nodeValue . "-->";
            default:
                return $node->ownerDocument->saveHTML($node);
        }
    };

    // 7) construire une <entry> à partir d'une cellule (valign seulement si présent)
    $buildCalsEntry = function (DOMElement $cell, int $colStart, int $colspan, int $rowspan) use ($serializeNode): string {
        $namest = "col" . ($colStart + 1);
        $nameend = "col" . ($colStart + $colspan);
        $attrs = ' namest="' . $namest . '" nameend="' . $nameend . '"';
        if ($rowspan > 1) $attrs .= ' morerows="' . ($rowspan - 1) . '"';

        $colsep = "1";
        $rowsep = "1";
        $style = $cell->getAttribute('style') ?: '';
        if (stripos($style, 'border-bottom:none') !== false) $rowsep = "0";
        $attrs .= ' colsep="' . $colsep . '" rowsep="' . $rowsep . '"';

        // align horizontal
        $align = $cell->getAttribute('align');
        if (!$align) {
            if (preg_match('/text-align\s*:\s*(left|right|center|justify)/i', $style, $m)) $align = strtolower($m[1]);
            else $align = "left";
        }
        $attrs .= ' align="' . $align . '"';

        // valign uniquement si présent
        $valign = $cell->getAttribute('valign');
        if ($valign !== '') {
            $attrs .= ' valign="' . $valign . '"';
        }

        // contenu interne intact
        $inner = "";
        foreach ($cell->childNodes as $child) $inner .= $serializeNode($child);

        return "      <entry{$attrs}>\n" . $inner . "\n      </entry>";
    };

    // 8) génération finale en utilisant spanMatrix (compteurs de rowspan)
    $out = [];
    // $out[] = '<?xml version="1.0" encoding="UTF-8"?'.'>';
    $out[] = '<omnibook>';
    $out[] = '<table frame="all" orient="port" pgwide="1">';
    $out[] = '  <tgroup cols="' . $numCols . '">';

    // colspec
    for ($i = 0; $i < $numCols; $i++) {
        $w = rtrim(rtrim(number_format($colWidths[$i], 6, '.', ''), '0'), '.');
        $out[] = '    <colspec colnum="' . ($i + 1) . '" colname="col' . ($i + 1) . '" colwidth="' . $w . '*"/>';
    }

    $out[] = '    <tbody>';

    // spanMatrix init: nombre de colonnes, compte de lignes restantes pour chaque colonne
    $spanMatrix = array_fill(0, $numCols, 0);

    foreach ($rowsAll as $tr) {
        $out[] = '      <row>';
        $currentCol = 0;

        // sauter colonnes occupées par rowspan en cours
        while (isset($spanMatrix[$currentCol]) && $spanMatrix[$currentCol] > 0) {
            $spanMatrix[$currentCol]--;
            $currentCol++;
        }

        foreach ($xpath->query('./th|./td', $tr) as $cell) {
            // sauter si occupé
            while (isset($spanMatrix[$currentCol]) && $spanMatrix[$currentCol] > 0) {
                $spanMatrix[$currentCol]--;
                $currentCol++;
            }

            $colspan = max(1, intval($cell->getAttribute('colspan')));
            $rowspan = max(1, intval($cell->getAttribute('rowspan')));

            if ($rowspan > 1) {
                for ($c = 0; $c < $colspan; $c++) {
                    // incrémenter compteur de lignes restantes pour cette colonne
                    $spanMatrix[$currentCol + $c] = ($spanMatrix[$currentCol + $c] ?? 0) + ($rowspan - 1);
                }
            }

            $out[] = $buildCalsEntry($cell, $currentCol, $colspan, $rowspan);

            $currentCol += $colspan;
        }

        $out[] = '      </row>';
    }

    $out[] = '    </tbody>';
    $out[] = '  </tgroup>';
    $out[] = '</table>';
    $out[] = '</omnibook>';

    return implode("\n", $out);
}

