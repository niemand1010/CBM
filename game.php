<?php
session_start();
require 'includes/functions.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// DB-Verbindung annehmen
// Beispiel: $con = new mysqli('localhost', 'user', 'pass', 'database');
// Bitte selbst anpassen

$validation = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['kategorie']) && !empty($_POST['wort']) &&
        is_array($_POST['kategorie']) && is_array($_POST['wort'])) {

        foreach ($_POST['kategorie'] as $index => $kat) {
            $cat = trim($kat);
            $word = trim($_POST['wort'][$index]);

            if ($cat !== '' && $word !== '') {
                if ($stmt = $con->prepare('INSERT INTO ergebnisse (user, kategorie, wort) VALUES (?, ?, ?)')) {
                    $stmt->bind_param('sss', $_SESSION['username'], $cat, $word);
                    if ($stmt->execute()) {
                        // GPT-API-Abfrage
                        $apiKey = getenv('CHATGPT_API_KEY');
                        $prompt = "Überprüfe das Wort '$word' für die Kategorie '$cat' im Spiel Stadt Land Fluss.";

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

                        $data = json_decode($response, true);
                        $validation .= htmlspecialchars($data['choices'][0]['message']['content'] ?? 'Keine Antwort.', ENT_QUOTES) . "<br>";
                    } else {
                        $validation .= 'Fehler beim Speichern: ' . htmlspecialchars($cat, ENT_QUOTES) . '<br>';
                    }
                    $stmt->close();
                }
            }
        }
    } else {
        $validation = 'Bitte füllen Sie alle Felder korrekt aus.';
    }
}

// Bisherige Ergebnisse anzeigen
$ergebnisse = [];
if ($stmt = $con->prepare('SELECT kategorie, wort FROM ergebnisse WHERE user = ?')) {
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $ergebnisse[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Stadt Land Fluss</title>
    <link rel="stylesheet" href="assets/styles.css">
    <script>
        function addRow() {
            var table = document.getElementById('kategorie_table').getElementsByTagName('tbody')[0];
            var newRow = table.insertRow();
            var catCell = newRow.insertCell(0);
            var wordCell = newRow.insertCell(1);

            var select = document.createElement('select');
            select.name = 'kategorie[]';
            select.required = true;
            ['Stadt', 'Land', 'Fluss', 'Andere'].forEach(function(optValue) {
                var opt = document.createElement('option');
                opt.value = optValue;
                opt.textContent = optValue;
                select.appendChild(opt);
            });

            var input = document.createElement('input');
            input.type = 'text';
            input.name = 'wort[]';
            input.placeholder = 'Wort';
            input.required = true;

            catCell.appendChild(select);
            wordCell.appendChild(input);
        }
    </script>
</head>
<body>
    <h1>Willkommen, <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES); ?>!</h1>
    <a href="logout.php">Logout</a>

    <form method="post">
        <table id="kategorie_table" border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>Kategorie</th>
                    <th>Wort</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="kategorie[]" required>
                            <option value="Stadt">Stadt</option>
                            <option value="Land">Land</option>
                            <option value="Fluss">Fluss</option>
                            <option value="Andere">Andere</option>
                        </select>
                    </td>
                    <td><input type="text" name="wort[]" placeholder="Wort" required></td>
                </tr>
            </tbody>
        </table>
        <button type="button" onclick="addRow()">Weitere Kategorie hinzufügen</button>
        <button type="submit">Absenden</button>
    </form>

    <?php if ($validation): ?>
        <div class="validation"><?php echo $validation; ?></div>
    <?php endif; ?>

    <h2>Deine Ergebnisse</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Stadt</th>
                <th>Land</th>
                <th>Fluss</th>
                <th>Extra</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (empty($ergebnisse)) {
                echo '<tr><td colspan="4">Keine Einträge.</td></tr>';
            } else {
                foreach ($ergebnisse as $eintrag) {
                    $stadt = $eintrag['kategorie'] === 'Stadt' ? htmlspecialchars($eintrag['wort'], ENT_QUOTES) : '';
                    $land = $eintrag['kategorie'] === 'Land' ? htmlspecialchars($eintrag['wort'], ENT_QUOTES) : '';
                    $fluss = $eintrag['kategorie'] === 'Fluss' ? htmlspecialchars($eintrag['wort'], ENT_QUOTES) : '';
                    $extra = (!in_array($eintrag['kategorie'], ['Stadt','Land','Fluss'])) 
                        ? htmlspecialchars($eintrag['kategorie'] . ': ' . $eintrag['wort'], ENT_QUOTES) : '';
                    echo "<tr><td>$stadt</td><td>$land</td><td>$fluss</td><td>$extra</td></tr>";
                }
            }
            ?>
        </tbody>
    </table>
</body>
</html>
