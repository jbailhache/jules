<?php
    require_once("include/fonction.php");
    require_once("include/htmlEditionTableaux.php");

    global $json;

    $majauto = false;
    $agrandissement = 1.5;
    $sannee = 2026;
    $sartdeb = '';
    $sartfin = '';
    $sdocdeb = '';
    $filename = "tableau.html";

    $tableau = file_get_contents($filename);
    if (empty($tableau)) {
        $tableau = "<table border=\"1\"><tr><th>Nouveau tableau</th></tr><tr><td>Première ligne</td></tr></table>";
    }
    $html = htmlEditionTableaux(0, $tableau);
    echo "<p>json = ".print_r($json, true)."</p>";
?>
<html>
    <head>
        <title>Test mise à jour tableau</title>
    </head>
    <body>
        <h2>Test mise à jour tableaux</h2>
        <p>
            <br>Tableau : <br>
            <?php echo $tableau; ?>
            <br>HTML : <br>
            <table>
                <tr>
                    <td class="textecourant" valign="top">
                        <?php echo $html; ?>
                    </td>
                </tr>
            </table>

  <!-- Inclure le CSS de jSuites (optionnel pour certains composants comme le calendrier) -->
  <link rel="stylesheet" href="node_modules/jsuites/dist/jsuites.css">
  <!-- Inclure le CSS de Jspreadsheet -->
  <link rel="stylesheet" href="node_modules/jspreadsheet-ce/dist/jspreadsheet.css">

  <!-- Inclure jSuites AVANT Jspreadsheet -->
  <script src="node_modules/jsuites/dist/jsuites.js"></script>
  <!-- Inclure Jspreadsheet -->
  <script src="node_modules/jspreadsheet-ce/dist/index.js"></script>

<?php
    require_once("include/visutxt_tableaux.php");
?>

    </body>
</html>
