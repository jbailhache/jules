<?php

function customTagsToHtml($cell) {
        $escapedCell = $cell ? htmlspecialchars($cell, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $cell;
    
        $escapedCell = str_replace("\n", "<br>", $escapedCell);
        $escapedCell = str_replace("[b]", "<b>", $escapedCell);
        $escapedCell = str_replace("[/b]", "</b>", $escapedCell);
        $escapedCell = str_replace("[i]", "<i>", $escapedCell);
        $escapedCell = str_replace("[/i]", "</i>", $escapedCell);
        $escapedCell = str_replace("[sup]", "<sup>", $escapedCell);
        $escapedCell = str_replace("[/sup]", "</sup>", $escapedCell);
        $escapedCell = str_replace("[sub]", "<sub>", $escapedCell);
        $escapedCell = str_replace("[/sub]", "</sub>", $escapedCell);

        return $escapedCell;
}

function htmlToCustomTags($str) {
    $str = str_replace("<br>", "\\n", $str);
    $str = str_replace("<b>", "[b]", $str);
    $str = str_replace("</b>", "[/b]", $str);
    $str = str_replace("<i>", "[i]", $str);
    $str = str_replace("</i>", "[/i]", $str);
    $str = str_replace("<sup>", "[sup]", $str);
    $str = str_replace("</sup>", "[/sup]", $str);
    $str = str_replace("<sub>", "[sub]", $str);
    $str = str_replace("</sub>", "[/sub]", $str);
    return $str;
}

