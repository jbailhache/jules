<?php

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

