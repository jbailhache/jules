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

if ($allPassed) {
    echo "\nALL TESTS PASSED!\n";
    exit(0);
} else {
    echo "\nSOME TESTS FAILED!\n";
    exit(1);
}
