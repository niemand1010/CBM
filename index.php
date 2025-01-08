<?php
session_start();
require 'includes/config.php';

if (isset($_SESSION['loggedin'])) {
    header('Location: game.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
?><?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'], $_POST['password'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // Überprüfen, ob Benutzer bereits existiert
        if ($stmt = $con->prepare('SELECT id FROM users WHERE username = ?')) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                echo 'Benutzername bereits vergeben!';
            } else {
                if ($stmt = $con->prepare('INSERT INTO users (username, password) VALUES (?, ?)')) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt->bind_param('ss', $username, $hashed_password);
                    if ($stmt->execute()) {
                        session_regenerate_id();
                        $_SESSION['loggedin'] = TRUE;
                        $_SESSION['name'] = $username;
                        header('Location: index.php');
                    } else {
                        echo 'Registrierung fehlgeschlagen!';
                    }
                }
            }
            $stmt->close();
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
</html><?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'], $_POST['password'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if ($stmt = $con->prepare('SELECT id, password FROM users WHERE username = ?')) {
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $hashed_password);
                $stmt->fetch();
                if (password_verify($password, $hashed_password)) {
                    session_regenerate_id();
                    $_SESSION['loggedin'] = TRUE;
                    $_SESSION['name'] = $username;
                    $_SESSION['id'] = $id;
                    header('Location: index.php');
                } else {
                    echo 'Ungültige Anmeldedaten!';
                }
            } else {
                echo 'Ungültige Anmeldedaten!';
            }
            $stmt->close();
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
</head>
<body>
    <h2>Login</h2>
    <form method="post">
        Benutzername: <input type="text" name="username" required><br>
        Passwort: <input type="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>Noch keinen Account? <a href="register.php">Registrieren</a></p>
</body>
</html><?php
require 'db.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

$validation = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['kategorie'], $_POST['wort'])) {
        $kategorie = trim($_POST['kategorie']);
        $wort = trim($_POST['wort']);

        // Ergebnisse speichern
        if ($stmt = $con->prepare('INSERT INTO ergebnisse (user, kategorie, wort) VALUES (?, ?, ?)')) {
            $stmt->bind_param('sss', $_SESSION['name'], $kategorie, $wort);
            if ($stmt->execute()) {
                // ChatGPT-Validierung
                $apiKey = 'YOUR_CHATGPT_API_KEY'; // Ersetze mit deinem API-Schlüssel
                $prompt = "Überprüfe das Wort '$wort' für die Kategorie '$kategorie' im Spiel Stadt Land Fluss.";

                $ch = curl_init("https://api.openai.com/v1/chat/completions");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/json",
                    "Authorization: Bearer $apiKey"
                ]);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    "model" => "gpt-4",
                    "messages" => [
                        ["role" => "system", "content" => "Du validierst Antworten für das Spiel Stadt Land Fluss."],
                        ["role" => "user", "content" => $prompt]
                    ]
                ]));
                $response = curl_exec($ch);
                curl_close($ch);

                $responseData = json_decode($response, true);
                $validation = $responseData['choices'][0]['message']['content'] ?? 'Keine Antwort erhalten.';
            } else {
                $validation = 'Fehler beim Speichern der Ergebnisse.';
            }
            $stmt->close();
        }
    } else {
        $validation = 'Bitte füllen Sie alle Felder aus!';
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Stadt Land Fluss</title>
    <style>
        /* Bordered form */
        form {
            border: 3px solid #f1f1f1;
            padding: 20px;
            max-width: 400px;
            margin: auto;
        }

        /* Full-width inputs */
        input[type=text] {
            width: 100%;
            padding: 12px 20px;
            margin: 8px 0;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        /* Set a style for all buttons */
        button {
            background-color: #04AA6D;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        /* Add a hover effect for buttons */
        button:hover {
            opacity: 0.8;
        }

        /* Extra style for the cancel button (red) */
        .cancelbtn {
            width: auto;
            padding: 10px 18px;
            background-color: #f44336;
        }

        /* Center the avatar image inside this container */
        .imgcontainer {
            text-align: center;
            margin: 24px 0 12px 0;
        }

        .container {
            padding: 16px;
        }

        .validation {
            margin-top: 20px;
            padding: 10px;
            background-color: #e7f3fe;
            border-left: 6px solid #2196F3;
            max-width: 400px;
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <h1>Willkommen, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
    <form method="post">
        <div class="container">
            <label for="kategorie"><b>Kategorie</b></label>
            <input type="text" placeholder="Kategorie" name="kategorie" required>

            <label for="wort"><b>Wort</b></label>
            <input type="text" placeholder="Wort" name="wort" required>

            <button type="submit">Absenden</button>
        </div>
    </form>
    <div class="validation">
        <?php echo htmlspecialchars($validation); ?>
    </div>
    <a href="logout.php">Logout</a>
</body>
</html><?php
session_start();
session_unset();
session_destroy();
header('Location: login.php');
exit();
?>