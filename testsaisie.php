<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>test saisie</title>

  <!-- Inclure le CSS de jSuites (optionnel pour certains composants comme le calendrier) -->
  <link rel="stylesheet" href="node_modules/jsuites/dist/jsuites.css">
  <!-- Inclure le CSS de Jspreadsheet -->
  <link rel="stylesheet" href="node_modules/jspreadsheet-ce/dist/jspreadsheet.css">

  <!-- Inclure jSuites AVANT Jspreadsheet -->
  <script src="node_modules/jsuites/dist/jsuites.js"></script>
  <!-- Inclure Jspreadsheet -->
  <script src="node_modules/jspreadsheet-ce/dist/index.js"></script>

<script type="text/javascript">
var lastFocusedField = null;

document.addEventListener('focusin', function(event) {
    var tag = event.target.tagName ? event.target.tagName.toLowerCase() : '';
    if (tag === 'textarea' || (tag === 'input' && (event.target.type === 'text' || event.target.type === 'search' || !event.target.type))) {
        lastFocusedField = event.target;
    }
});

function getActiveField() {
    var jssEditorInput = document.querySelector('.jexcel td.editor input, .jexcel td.editor textarea, .jss td.editor input, .jss td.editor textarea');
    if (jssEditorInput) {
        return jssEditorInput;
    }
    if (document.activeElement) {
        var activeTag = document.activeElement.tagName ? document.activeElement.tagName.toLowerCase() : '';
        if (activeTag === 'textarea' || (activeTag === 'input' && (document.activeElement.type === 'text' || document.activeElement.type === 'search' || !document.activeElement.type))) {
            return document.activeElement;
        }
    }
    if (lastFocusedField && document.contains(lastFocusedField)) {
        return lastFocusedField;
    }
    return document.getElementById('texte');
}

function insertAtCursor(char) {
    var field = getActiveField();
    if (field && (field.tagName.toLowerCase() === 'textarea' || field.tagName.toLowerCase() === 'input')) {
        field.focus();
        if (typeof field.selectionStart === 'number' && typeof field.selectionEnd === 'number') {
            var startPos = field.selectionStart;
            var endPos = field.selectionEnd;
            var val = field.value;
            field.value = val.substring(0, startPos) + char + val.substring(endPos);
            field.selectionStart = field.selectionEnd = startPos + char.length;
        } else {
            field.value += char;
        }
        var event = new Event('input', { bubbles: true });
        field.dispatchEvent(event);
    }
}

function openCharTable() {
    var field = getActiveField();
    if (field) {
        lastFocusedField = field;
    }
    var popup = window.open('', 'CharTablePopup', 'width=300,height=200,resizable=yes,scrollbars=yes');
    if (popup) {
        var doc = popup.document;
        doc.open();
        doc.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Table de caractères</title>');
        doc.write('<style>table { border-collapse: collapse; } td { border: 1px solid #ccc; padding: 10px 15px; text-align: center; font-size: 20px; cursor: pointer; } td:hover { background-color: #e0e0e0; }</style>');
        doc.write('</head><body>');
        doc.write('<h3>Table de caractères</h3>');
        doc.write('<table><tr>');
        doc.write('<td onclick="window.opener.insertAtCursor(\'Σ\')">Σ</td>');
        doc.write('<td onclick="window.opener.insertAtCursor(\'α\')">α</td>');
        doc.write('<td onclick="window.opener.insertAtCursor(\'β\')">β</td>');
        doc.write('<td onclick="window.opener.insertAtCursor(\'γ\')">γ</td>');
        doc.write('</tr></table>');
        doc.write('</body></html>');
        doc.close();
        popup.focus();
    }
}
</script>
</head>
<body>
Test saisie
<p>
<?php
    $texte = isset($_POST['texte']) ? $_POST['texte'] : false;
    if ($texte) {
        echo "Vous avez saisi : " . htmlspecialchars($texte, ENT_QUOTES, 'UTF-8');
    } else {
        echo "Vous n'avez rien saisi";
    }
?>
<p>
<form method="POST" action="testsaisie.php">
<textarea name="texte" id="texte" rows="20" cols="100"><?php echo isset($_POST['texte']) ? htmlspecialchars($_POST['texte'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
<p>
<textarea name="texte2" id="texte2" rows="20" cols="100"><?php echo isset($_POST['texte']) ? htmlspecialchars($_POST['texte'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
<p>

<?php
// Exemple de données côté serveur (tableau PHP)
$data = [
    ["Nom" => "Dupont", "Âge" => 30, "Ville" => "Paris"],
    ["Nom" => "Martin", "Âge" => 25, "Ville" => "Lyon"],
    ["Nom" => "Durand", "Âge" => 40, "Ville" => "Marseille"],
];

// Encodage JSON pour JavaScript
$jsonData = json_encode(array_values($data));
?>



<div id="spreadsheet"></div>

<script>
// Récupération des données PHP dans JS
const data = <?php echo $jsonData; ?>;

// Initialisation de Jspreadsheet
jspreadsheet(document.getElementById('spreadsheet'), {
    data: data,
    columns: [
        { type: 'text', title: 'Nom', width: 120 },
        { type: 'numeric', title: 'Âge', width: 80 },
        { type: 'text', title: 'Ville', width: 150 },
    ],
    minDimensions: [3, 5], // colonnes, lignes
});
</script>

<p>
<input type="submit" value="OK">
<input type="button" value="Table de caractères" onclick="openCharTable()">
</form>
</body>
</html>
