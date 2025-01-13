<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['password'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (loginUser($username, $password)) {
            header('Location: game.php');
            exit();
        } else {
            echo 'Ungültige Anmeldedaten!';
        }
    } else {
        echo 'Bitte füllen Sie alle Felder aus!';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Login - Stadt Land Fluss</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <h2>Login</h2>
    <form method="post">
        Benutzername: <input type="text" name="username" required><br>
        Passwort: <input type="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>Noch keinen Account? <a href="register.php">Registrieren</a></p>
    <p>Oder ohne Anmeldung <a href="game.php">spielen</a></p>
</body>
</html>
