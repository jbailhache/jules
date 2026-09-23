<?php

require_once __DIR__ . '/htmlTableToCals.php';

function testTableWithPrefixAndSuffix() {
    $input = 'foo<table><tr><td>Cell 1</td></tr></table>bar';
    $result = htmlTableToCals($input);

    $expectedPrefix = '<omnibook>' . "\n" . 'foo' . "\n" . '<table';
    $expectedSuffix = '</table>' . "\n" . 'bar' . "\n" . '</omnibook>';

    assert(str_contains($result, '<omnibook>'), "Output should start omnibook root");
    assert(str_contains($result, 'foo'), "Output should contain prefix 'foo'");
    assert(str_contains($result, 'bar'), "Output should contain suffix 'bar'");

    // Check ordering: foo comes before <table and bar comes after </table>
    $posFoo = strpos($result, 'foo');
    $posTableStart = strpos($result, '<table');
    $posTableEnd = strpos($result, '</table>');
    $posBar = strpos($result, 'bar');

    if ($posFoo < $posTableStart && $posTableStart < $posTableEnd && $posTableEnd < $posBar) {
        echo "PASS: testTableWithPrefixAndSuffix\n";
    } else {
        echo "FAIL: testTableWithPrefixAndSuffix\nGot:\n$result\n";
        exit(1);
    }
}

function testTableOnly() {
    $input = '<table><tr><td>Cell 1</td></tr></table>';
    $result = htmlTableToCals($input);

    if (str_starts_with($result, "<omnibook>\n<table") && str_ends_with($result, "</table>\n</omnibook>")) {
        echo "PASS: testTableOnly\n";
    } else {
        echo "FAIL: testTableOnly\nGot:\n$result\n";
        exit(1);
    }
}

function testTableWithHtmlPrefixAndSuffix() {
    $input = '<div>Header text</div><table border="1"><tr><td>Cell 1</td></tr></table><p>Footer text</p>';
    $result = htmlTableToCals($input);

    assert(str_contains($result, '<div>Header text</div>'));
    assert(str_contains($result, '<p>Footer text</p>'));

    $posHeader = strpos($result, '<div>Header text</div>');
    $posTableStart = strpos($result, '<table');
    $posTableEnd = strpos($result, '</table>');
    $posFooter = strpos($result, '<p>Footer text</p>');

    if ($posHeader < $posTableStart && $posTableStart < $posTableEnd && $posTableEnd < $posFooter) {
        echo "PASS: testTableWithHtmlPrefixAndSuffix\n";
    } else {
        echo "FAIL: testTableWithHtmlPrefixAndSuffix\nGot:\n$result\n";
        exit(1);
    }
}

testTableWithPrefixAndSuffix();
testTableOnly();
testTableWithHtmlPrefixAndSuffix();

echo "All tests passed successfully.\n";
