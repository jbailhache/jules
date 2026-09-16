<?php

require_once __DIR__ . '/ajouterBalisesModifications.php';

function runTest($testName, $avant, $apres, $debut, $fin, $expected) {
    $actual = ajouterBalisesModifications($avant, $apres, $debut, $fin);
    if ($actual === $expected) {
        echo "PASS: $testName\n";
        return true;
    } else {
        echo "FAIL: $testName\n";
        echo "  Expected: " . var_export($expected, true) . "\n";
        echo "  Actual:   " . var_export($actual, true) . "\n";
        return false;
    }
}

$allPassed = true;

// Test Case 1: Prompt Example 1
$allPassed &= runTest(
    "Prompt Example 1 - word modifications in cell",
    "<table><tr><td>aaa bbb ccc ddd eee fff ggg</td></tr></table>",
    "<table><tr><td>aaa xxx ccc ddd yyy eee ggg</td></tr></table>",
    "<u>",
    "</u>",
    "<table><tr><td>aaa <u>xxx</u> ccc ddd <u>yyy</u> eee ggg</td></tr></table>"
);

// Test Case 2: Prompt Example 2 - unchanged
$allPassed &= runTest(
    "Prompt Example 2 - unchanged",
    "<table><tr><th width=20>aaa</th></tr><tr><td>bbb</td></tr></table>",
    "<table><tr><th width=20>aaa</th></tr><tr><td>bbb</td></tr></table>",
    "<u>",
    "</u>",
    "<table><tr><th width=20>aaa</th></tr><tr><td>bbb</td></tr></table>"
);

// Test Case 3: Tag attribute modification without text change
$allPassed &= runTest(
    "Tag attribute modification (width changed)",
    "<table><tr><th width=10>aaa</th></tr><tr><td>bbb</td></tr></table>",
    "<table><tr><th width=20>aaa</th></tr><tr><td>bbb</td></tr></table>",
    "<u>",
    "</u>",
    "<table><tr><th width=20>aaa</th></tr><tr><td>bbb</td></tr></table>"
);

// Test Case 4: Inserting a row
$allPassed &= runTest(
    "Inserting a row",
    "<table><tr><td>Ligne 1</td></tr></table>",
    "<table><tr><td>Ligne 1</td></tr><tr><td>Ligne 2</td></tr></table>",
    "<u>",
    "</u>",
    "<table><tr><td>Ligne 1</td></tr><tr><td><u>Ligne 2</u></td></tr></table>"
);

// Test Case 5: Deleting a row
$allPassed &= runTest(
    "Deleting a row",
    "<table><tr><td>Ligne 1</td></tr><tr><td>Ligne 2</td></tr></table>",
    "<table><tr><td>Ligne 1</td></tr></table>",
    "<u>",
    "</u>",
    "<table><tr><td>Ligne 1</td></tr></table>"
);

// Test Case 6: Inserting a column
$allPassed &= runTest(
    "Inserting a column",
    "<table><tr><td>A1</td></tr><tr><td>A2</td></tr></table>",
    "<table><tr><td>A1</td><td>B1</td></tr><tr><td>A2</td><td>B2</td></tr></table>",
    "<u>",
    "</u>",
    "<table><tr><td>A1</td><td><u>B1</u></td></tr><tr><td>A2</td><td><u>B2</u></td></tr></table>"
);

// Test Case 7: Deleting a column
$allPassed &= runTest(
    "Deleting a column",
    "<table><tr><td>A1</td><td>B1</td></tr><tr><td>A2</td><td>B2</td></tr></table>",
    "<table><tr><td>A1</td></tr><tr><td>A2</td></tr></table>",
    "<u>",
    "</u>",
    "<table><tr><td>A1</td></tr><tr><td>A2</td></tr></table>"
);

// Test Case 8: Custom multi-tag $debut and $fin
$allPassed &= runTest(
    'Custom multi-tag $debut and $fin',
    "<table><tr><td>alpha</td></tr></table>",
    "<table><tr><td>beta</td></tr></table>",
    "<font color=blue><u>",
    "</u></font>",
    "<table><tr><td><font color=blue><u>beta</u></font></td></tr></table>"
);

// Test Case 9: French accented characters
$allPassed &= runTest(
    "French accented characters",
    "<table><tr><td>élève présent</td></tr></table>",
    "<table><tr><td>élève absent et remplacé</td></tr></table>",
    "<u>",
    "</u>",
    "<table><tr><td>élève <u>absent et remplacé</u></td></tr></table>"
);

// Test Case 10: Multi-word insertion
$allPassed &= runTest(
    "Multi-word insertion",
    "<table><tr><td>hello world</td></tr></table>",
    "<table><tr><td>hello beautiful new world</td></tr></table>",
    "<u>",
    "</u>",
    "<table><tr><td>hello <u>beautiful new</u> world</td></tr></table>"
);

// Test Case 11: Identical strings
$allPassed &= runTest(
    "Identical inputs",
    "<table><tr><td>test</td></tr></table>",
    "<table><tr><td>test</td></tr></table>",
    "<u>",
    "</u>",
    "<table><tr><td>test</td></tr></table>"
);

// Test Case 12: Empty string/tables
$allPassed &= runTest(
    "Empty tables",
    "<table></table>",
    "<table></table>",
    "<u>",
    "</u>",
    "<table></table>"
);

// Test Case 13: Percentage addition (User feedback)
$allPassed &= runTest(
    "Percentage addition at end of cell",
    "<table><tr><td>10</td></tr></table>",
    "<table><tr><td>12%</td></tr></table>",
    "<font color=blue><u>",
    "</u></font>",
    "<table><tr><td><font color=blue><u>12%</u></font></td></tr></table>"
);

// Test Case 14: Percentage modification
$allPassed &= runTest(
    "Percentage modification",
    "<table><tr><td>10%</td></tr></table>",
    "<table><tr><td>12%</td></tr></table>",
    "<font color=blue><u>",
    "</u></font>",
    "<table><tr><td><font color=blue><u>12%</u></font></td></tr></table>"
);

// Test Case 15: Decimal percentage
$allPassed &= runTest(
    "Decimal percentage",
    "<table><tr><td>10%</td></tr></table>",
    "<table><tr><td>12,5%</td></tr></table>",
    "<font color=blue><u>",
    "</u></font>",
    "<table><tr><td><font color=blue><u>12,5%</u></font></td></tr></table>"
);

// Test Case 16: Currency symbols ($ and €)
$allPassed &= runTest(
    "Currency symbols ($ and €)",
    "<table><tr><td>Prix: 10€</td></tr></table>",
    "<table><tr><td>Prix: 12,50€</td></tr></table>",
    "<font color=blue><u>",
    "</u></font>",
    "<table><tr><td>Prix: <font color=blue><u>12,50€</u></font></td></tr></table>"
);

// Test Case 17: Complex table with row deletion and insertion
$tableAvant = '<table><tr><th>Première colonne</th><th width="200">Deuxième colonne</th><th>Troisième colonne</th></tr>
<tr><td>Lorem ipsum dolor sit amet</td><td>consectetur adipiscing elit</td><td>Sed non risus</td></tr>
<tr><td>Suspendisse lectus tortor</td><td>dignissim sit amet</td><td>adipiscing nec</td></tr>
<tr><td>ultricies sed, dolor</td><td>Cras elementum</td><td>ultrices diam</td></tr>
<tr><td>Proin porttitor</td><td>orci nec nonummy molestie</td><td>enim est eleifend</td></tr>
</table>';

$tableApres = '<table><tr><th>Première colonne</th><th width="300">Deuxième colonne</th><th>Troisième colonne</th></tr>
<tr><td>Lorem ipsum sit amet</td><td>consectetur additionem adipiscing elit</td><td>Sed non risus</td></tr>
<tr><td>ultricies sed, dolor</td><td>Cras elementum</td><td>ultrices diam</td></tr>
<tr><td>Maecenas ligula massa</td><td>varius a, semper congue</td><td>euismod non, mi.</td></tr>
<tr><td>Proin porttitor</td><td>orci nec nonummy novum molestie</td><td>enim est eleifend</td></tr>
</table>';

$tableExpected = '<table><tr><th>Première colonne</th><th width="300">Deuxième colonne</th><th>Troisième colonne</th></tr>
<tr><td>Lorem ipsum sit amet</td><td>consectetur <u>additionem</u> adipiscing elit</td><td>Sed non risus</td></tr>
<tr><td>ultricies sed, dolor</td><td>Cras elementum</td><td>ultrices diam</td></tr>
<tr><td><u>Maecenas ligula massa</u></td><td><u>varius a, semper congue</u></td><td><u>euismod non, mi.</u></td></tr>
<tr><td>Proin porttitor</td><td>orci nec nonummy <u>novum</u> molestie</td><td>enim est eleifend</td></tr>
</table>';

$allPassed &= runTest(
    "Complex table with row deletion, insertion, and word modifications",
    $tableAvant,
    $tableApres,
    "<u>",
    "</u>",
    $tableExpected
);

if ($allPassed) {
    echo "\nALL TESTS PASSED!\n";
    exit(0);
} else {
    echo "\nSOME TESTS FAILED!\n";
    exit(1);
}
