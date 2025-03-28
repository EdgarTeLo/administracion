<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_text = $_POST['input_text'];
    $hashed_text = password_hash($input_text, PASSWORD_DEFAULT);
    echo "Texto en hash: " . $hashed_text;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hashing de Texto</title>
</head>
<body>
    <form method="post" action="1.php">
        <label for="input_text">Ingresa el texto:</label>
        <input type="text" id="input_text" name="input_text">
        <input type="submit" value="Hashear">
    </form>