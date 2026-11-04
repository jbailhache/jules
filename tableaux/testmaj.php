<?php
    require_once("include/fonction.php");
    require_once("include/htmlEditionTableaux.php");
    $majauto = false;
    $tableau = "<table border=\"1\"><tr><th>Nouveau tableau</th></tr><tr><td>Première ligne</td></tr></table>";
    $html = htmlEditionTableaux(1, $tableau);

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
            <?php echo $html; ?>

<?php
    require_once("include/visutxt_tableaux.php");
?>

    </body>
</html>

