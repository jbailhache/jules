<?php

/**
 * Ajoute les balises de modification autour de chaque groupe de mots entiers
 * ajoutés ou modifiés dans $apres par rapport à $avant.
 *
 * @param string $avant Code HTML initial (ex: table HTML).
 * @param string $apres Code HTML modifié.
 * @param string $debut Balise(s) marquant le début d'une modification (ex: "<u>").
 * @param string $fin Balise(s) marquant la fin d'une modification (ex: "</u>").
 * @return string Code HTML $apres avec les balises de modification ajoutées.
 */
function ajouterBalisesModifications($avant, $apres, $debut, $fin) {
    if ($avant === $apres || $apres === '') {
        return $apres;
    }

    $tokensA = tokenizeHtmlForDiff($avant);
    $tokensB = tokenizeHtmlForDiff($apres);

    $insertedFlags = computeLcsInsertedFlags($tokensA, $tokensB);

    $countB = count($tokensB);
    for ($i = 0; $i < $countB; $i++) {
        $tokensB[$i]['inserted'] = $insertedFlags[$i];
    }

    $result = '';
    $inGroup = false;

    for ($k = 0; $k < $countB; $k++) {
        $tok = $tokensB[$k];

        if ($tok['type'] === 'tag') {
            if ($inGroup) {
                $result .= $fin;
                $inGroup = false;
            }
            $result .= $tok['text'];
            continue;
        }

        if ($tok['type'] === 'word') {
            if ($tok['inserted']) {
                if (!$inGroup) {
                    $result .= $debut;
                    $inGroup = true;
                }
                $result .= $tok['text'];
            } else {
                if ($inGroup) {
                    $result .= $fin;
                    $inGroup = false;
                }
                $result .= $tok['text'];
            }
            continue;
        }

        // Token type is 'sep' (whitespace, punctuation, etc.)
        if ($inGroup) {
            $hasFutureInsertedWord = false;
            for ($next = $k + 1; $next < $countB; $next++) {
                if ($tokensB[$next]['type'] === 'tag') {
                    break;
                }
                if ($tokensB[$next]['type'] === 'word') {
                    if ($tokensB[$next]['inserted']) {
                        $hasFutureInsertedWord = true;
                    }
                    break;
                }
            }

            if ($hasFutureInsertedWord) {
                $result .= $tok['text'];
            } else {
                $result .= $fin;
                $inGroup = false;
                $result .= $tok['text'];
            }
        } else {
            $result .= $tok['text'];
        }
    }

    if ($inGroup) {
        $result .= $fin;
    }

    return $result;
}

/**
 * Découpe un code HTML en un tableau de tokens (tags, mots, séparateurs).
 */
function tokenizeHtmlForDiff($html) {
    // Expression régulière pour capturer les balises HTML <...>
    $pattern = '/(<(?:"[^"]*"|\'[^\']*\'|[^>])+>)/s';
    $parts = preg_split($pattern, $html, -1, PREG_SPLIT_DELIM_CAPTURE);

    $tokens = [];
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }

        if (str_starts_with($part, '<') && str_ends_with($part, '>')) {
            $tokens[] = [
                'type' => 'tag',
                'text' => $part,
            ];
        } else {
            // Découpage du texte en mots (lettres/chiffres Unicode) et séparateurs
            $subParts = preg_split('/([\p{L}\p{N}]+)/u', $part, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            foreach ($subParts as $sub) {
                if (preg_match('/^[\p{L}\p{N}]+$/u', $sub)) {
                    $tokens[] = [
                        'type' => 'word',
                        'text' => $sub,
                    ];
                } else {
                    $tokens[] = [
                        'type' => 'sep',
                        'text' => $sub,
                    ];
                }
            }
        }
    }
    return $tokens;
}

/**
 * Calcule pour chaque token de B s'il est inséré/modifié par rapport à A via LCS.
 */
function computeLcsInsertedFlags($a, $b) {
    $n = count($a);
    $m = count($b);

    if ($m === 0) {
        return [];
    }
    if ($n === 0) {
        return array_fill(0, $m, true);
    }

    // Calcul de la matrice LCS DP
    $dp = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));

    for ($i = 1; $i <= $n; $i++) {
        for ($j = 1; $j <= $m; $j++) {
            if ($a[$i - 1]['text'] === $b[$j - 1]['text']) {
                $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
            } else {
                $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
            }
        }
    }

    $bInserted = array_fill(0, $m, true);
    $i = $n;
    $j = $m;

    while ($i > 0 && $j > 0) {
        if ($a[$i - 1]['text'] === $b[$j - 1]['text']) {
            $bInserted[$j - 1] = false;
            $i--;
            $j--;
        } elseif ($dp[$i - 1][$j] >= $dp[$i][$j - 1]) {
            $i--;
        } else {
            $j--;
        }
    }

    return $bInserted;
}
