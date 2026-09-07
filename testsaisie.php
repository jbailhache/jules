<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>test saisie</title>

  <!-- Inclure le CSS de jSuites -->
  <link rel="stylesheet" href="https://bossanova.uk/jspreadsheet/v4/jspreadsheet.css" type="text/css" />
  <link rel="stylesheet" href="https://jsuites.net/v4/jsuites.css" type="text/css" />

  <!-- Inclure jSuites AVANT Jspreadsheet -->
  <script src="https://jsuites.net/v4/jsuites.js"></script>
  <!-- Inclure Jspreadsheet v4 -->
  <script src="https://bossanova.uk/jspreadsheet/v4/jspreadsheet.js"></script>

<script type="text/javascript">
var lastFocusedField = null;
var activeJssState = null;

document.addEventListener('focusin', function(event) {
    var tag = event.target.tagName ? event.target.tagName.toLowerCase() : '';
    if (tag === 'textarea' || (tag === 'input' && (event.target.type === 'text' || event.target.type === 'search' || !event.target.type))) {
        lastFocusedField = event.target;
        if (event.target.closest && (event.target.closest('.jexcel td.editor, .jss td.editor') || event.target.closest('.jexcel, .jss'))) {
            captureJssState();
        }
    }
});

function captureJssState() {
    var editorInput = document.querySelector('.jexcel td.editor input, .jexcel td.editor textarea, .jss td.editor input, .jss td.editor textarea, .jexcel_editor input, .jexcel_editor textarea');
    if (editorInput) {
        var td = editorInput.closest ? editorInput.closest('td') : editorInput.parentElement;
        var container = editorInput.closest ? editorInput.closest('.jexcel_container, .jss_container, [id="spreadsheet"]') : null;
        var instance = null;
        if (container) {
            var spDiv = container.querySelector ? container.querySelector('.jexcel, .jss') : null;
            if (spDiv && (spDiv.jspreadsheet || spDiv.jexcel)) {
                instance = spDiv.jspreadsheet || spDiv.jexcel;
            } else if (container.jspreadsheet || container.jexcel) {
                instance = container.jspreadsheet || container.jexcel;
            }
        }
        if (!instance && (window.jspreadsheet || window.jexcel)) {
            var jssObj = window.jspreadsheet || window.jexcel;
            instance = jssObj.current;
        }

        activeJssState = {
            instance: instance,
            editorInput: editorInput,
            td: td,
            value: editorInput.value,
            selectionStart: typeof editorInput.selectionStart === 'number' ? editorInput.selectionStart : editorInput.value.length,
            selectionEnd: typeof editorInput.selectionEnd === 'number' ? editorInput.selectionEnd : editorInput.value.length
        };
        return;
    }

    var instance = null;
    if (window.jspreadsheet || window.jexcel) {
        var jssObj = window.jspreadsheet || window.jexcel;
        instance = jssObj.current;
    }
    if (instance && instance.edition) {
        var td = instance.edition[0];
        var inputEl = td ? (td.querySelector ? td.querySelector('input, textarea') : null) : null;
        var val = inputEl ? inputEl.value : (instance.edition[1] || '');
        activeJssState = {
            instance: instance,
            editorInput: inputEl,
            td: td,
            value: val,
            selectionStart: inputEl && typeof inputEl.selectionStart === 'number' ? inputEl.selectionStart : val.length,
            selectionEnd: inputEl && typeof inputEl.selectionEnd === 'number' ? inputEl.selectionEnd : val.length
        };
        return;
    }
}

function getActiveField() {
    var jssEditorInput = document.querySelector('.jexcel td.editor input, .jexcel td.editor textarea, .jss td.editor input, .jss td.editor textarea');
    if (jssEditorInput) {
        return jssEditorInput;
    }
    if (activeJssState && activeJssState.editorInput && document.contains(activeJssState.editorInput)) {
        return activeJssState.editorInput;
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
    if (activeJssState) {
        var state = activeJssState;
        var val = state.value;
        var startPos = state.selectionStart;
        var endPos = state.selectionEnd;
        var newVal = val.substring(0, startPos) + char + val.substring(endPos);
        var newCursorPos = startPos + char.length;

        state.value = newVal;
        state.selectionStart = newCursorPos;
        state.selectionEnd = newCursorPos;

        if (state.editorInput && document.contains(state.editorInput)) {
            state.editorInput.focus();
            state.editorInput.value = newVal;
            if (typeof state.editorInput.selectionStart === 'number') {
                state.editorInput.selectionStart = state.editorInput.selectionEnd = newCursorPos;
            }
            var inputEvent = new Event('input', { bubbles: true });
            state.editorInput.dispatchEvent(inputEvent);
        } else if (state.instance && state.td) {
            if (typeof state.instance.openEditor === 'function') {
                state.instance.openEditor(state.td, false);
                var newEditorInput = state.td.querySelector('input, textarea');
                if (newEditorInput) {
                    newEditorInput.focus();
                    newEditorInput.value = newVal;
                    if (typeof newEditorInput.selectionStart === 'number') {
                        newEditorInput.selectionStart = newEditorInput.selectionEnd = newCursorPos;
                    }
                    state.editorInput = newEditorInput;
                    var inputEvent = new Event('input', { bubbles: true });
                    newEditorInput.dispatchEvent(inputEvent);
                }
            } else if (typeof state.instance.setValue === 'function') {
                state.instance.setValue(state.td, newVal);
            }
        }
        return;
    }

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
        var inputEvent = new Event('input', { bubbles: true });
        field.dispatchEvent(inputEvent);
        var changeEvent = new Event('change', { bubbles: true });
        field.dispatchEvent(changeEvent);
    }
}

function openCharTable() {
    captureJssState();
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
<input type="button" value="Table de caractères" onclick="openCharTable()" onpointerdown="captureJssState()" onmousedown="captureJssState()">
</form>
</body>
</html>
