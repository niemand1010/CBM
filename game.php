<?php
session_start();
require 'includes/functions.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

$validation = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['kategorie'], $_POST['wort'])) {
        // Überprüfe, ob es sich um Arrays handelt
        if (is_array($_POST['kategorie']) && is_array($_POST['wort'])) {
            foreach ($_POST['kategorie'] as $index => $kategorie) {
                $kategorie = trim($kategorie);
                $wort = trim($_POST['wort'][$index]);

                if ($kategorie !== '' && $wort !== '') {
                    // Ergebnisse speichern
                    if ($stmt = $con->prepare('INSERT INTO ergebnisse (user, kategorie, wort) VALUES (?, ?, ?)')) {
                        $stmt->bind_param('sss', $_SESSION['username'], $kategorie, $wort);
                        if ($stmt->execute()) {
                            // ChatGPT-Validierung für jede Eingabe
                            $apiKey = getenv('CHATGPT_API_KEY'); // Verwende Umgebungsvariable
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
                            $validation .= htmlspecialchars($responseData['choices'][0]['message']['content'] ?? 'Keine Antwort erhalten.', ENT_QUOTES) . "<br>";
                        } else {
                            $validation .= 'Fehler beim Speichern der Ergebnisse für Kategorie ' . htmlspecialchars($kategorie, ENT_QUOTES) . '.<br>';
                        }
                        $stmt->close();
                    }
                }
            }
        } else {
            $validation = 'Ungültige Formulareingaben!';
        }
    } else {
        $validation = 'Bitte füllen Sie alle Felder aus!';
    }
}

// Ergebnisse des aktuellen Benutzers abrufen
$ergebnisse = [];
if ($stmt = $con->prepare('SELECT kategorie, wort FROM ergebnisse WHERE user = ?')) {
    $stmt->bind_param('s', $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
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
        function toggleNewCategoryInput() {
            var categorySelect = document.getElementById('kategorie_option');
            var newCategoryDiv = document.getElementById('new_category_div');
            if (categorySelect.value === 'Other') {
                newCategoryDiv.style.display = 'block';
            } else {
                newCategoryDiv.style.display = 'none';
            }
        }

        function addRow() {
            var table = document.getElementById('kategorie_table').getElementsByTagName('tbody')[0];
            var newRow = table.insertRow();

            var cell1 = newRow.insertCell(0);
            var categorySelect = document.createElement('select');
            categorySelect.name = 'kategorie[]';
            categorySelect.required = true;
            var options = ['Stadt', 'Land', 'Fluss', 'Other'];
            options.forEach(function(option) {
                var opt = document.createElement('option');
                opt.value = option;
                opt.innerHTML = option;
                categorySelect.appendChild(opt);
            });
            categorySelect.onchange = toggleNewCategoryInput;
            cell1.appendChild(categorySelect);

            var cell2 = newRow.insertCell(1);
            var input = document.createElement('input');
            input.type = 'text';
            input.name = 'wort[]';
            input.placeholder = 'Wort';
            input.required = true;
            cell2.appendChild(input);
        }
    </script>
</head>
<body>
    <h1>Willkommen, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Nutzer', ENT_QUOTES); ?>!</h1>
    <a href="logout.php">Logout</a>

    <form method="post">
        <div class="container">
            <!-- Tabelle zur Eingabe der Kategorien -->
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
                                <option value="Other">Andere</option>
                            </select>
                        </td>
                        <td><input type="text" name="wort[]" placeholder="Wort" required></td>
                    </tr>
                    <tr>
                        <td>
                            <select name="kategorie[]" required>
                                <option value="Stadt">Stadt</option>
                                <option value="Land">Land</option>
                                <option value="Fluss">Fluss</option>
                                <option value="Other">Andere</option>
                            </select>
                        </td>
                        <td><input type="text" name="wort[]" placeholder="Wort" required></td>
                    </tr>
                    <tr>
                        <td>
                            <select name="kategorie[]" required>
                                <option value="Stadt">Stadt</option>
                                <option value="Land">Land</option>
                                <option value="Fluss">Fluss</option>
                                <option value="Other">Andere</option>
                            </select>
                        </td>
                        <td><input type="text" name="wort[]" placeholder="Wort" required></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" onclick="addRow()">Weitere Kategorie hinzufügen</button>
            <br><br>
            <button type="submit">Absenden</button>
        </div>
    </form>
    <?php if ($validation): ?>
        <div class="validation">
            <?php echo $validation; ?>
        </div>
    <?php endif; ?>

    <!-- Tabelle zur Anzeige der Ergebnisse -->
    <h2>Deine Ergebnisse</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Stadt</th>
                <th>Land</th>
                <th>Fluss</th>
                <th>Extra Kategorie</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Initialisiere Spalteninhalte
            foreach ($ergebnisse as $ergebnis) {
                $stadt = '';
                $land = '';
                $fluss = '';
                $extra = '';

                switch ($ergebnis['kategorie']) {
                    case 'Stadt':
                        $stadt = htmlspecialchars($ergebnis['wort'], ENT_QUOTES);
                        break;
                    case 'Land':
                        $land = htmlspecialchars($ergebnis['wort'], ENT_QUOTES);
                        break;
                    case 'Fluss':
                        $fluss = htmlspecialchars($ergebnis['wort'], ENT_QUOTES);
                        break;
                    default:
                        $extra = htmlspecialchars($ergebnis['kategorie'] . ': ' . $ergebnis['wort'], ENT_QUOTES);
                        break;
                }

                echo '<tr>';
                echo '<td>' . $stadt . '</td>';
                echo '<td>' . $land . '</td>';
                echo '<td>' . $fluss . '</td>';
                echo '<td>' . $extra . '</td>';
                echo '</tr>';
            }

            // Falls keine Ergebnisse vorhanden sind
            if (empty($ergebnisse)) {
                echo '<tr><td colspan="4">Keine Ergebnisse vorhanden.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</body>
</html>
