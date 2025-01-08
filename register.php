<?php
session_start();
require 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['password'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (registerUser($username, $password)) {
            session_regenerate_id();
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $username;
            header('Location: login.php');
            exit();
        } else {
            echo 'Registrierung fehlgeschlagen!';
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
    <title>Registrieren - Stadt Land Fluss</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <h2>Registrieren</h2>
    <form method="post">
        Benutzername: <input type="text" name="username" required><br>
        Passwort: <input type="password" name="password" required><br>
        <button type="submit">Registrieren</button>
    </form>
    <p>Schon registriert? <a href="login.php">Login</a></p>
</body>
</html>
