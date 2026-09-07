<html>
<head>
<title>test saisie</title>
</head>
<body>
Test saisie
<p>
<?php 
    $texte = isset($_POST['texte']) ? $_POST['texte'] : false;
    if ($texte) {
        echo "Vous avez saisi : $texte"; 
    } else {
        echo "Vous n'avez rien saisi";
    }
?>
<p>
<form method="POST" action="testsaisie.php">
<textarea name="texte" id="texte" rows="20" cols="100">
</textarea>
<p>
<input type="submit" value="OK">
</form>
</body>
</html>

