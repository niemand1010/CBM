<!DOCTYPE html>
<html lang="de">
<head>
    <!-- Meta-Daten und Titel der Seite -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stadt Land Fluss</title>
    <script>
        // Diese Funktion erzeugt bei Bedarf weitere Eingabefelder für Kategorien
        function addCategory() {
            const container = document.getElementById('categories');
            const categoryCount = container.children.length / 2 + 1;
            const div = document.createElement('div');
            div.className = 'category-input';
            div.innerHTML = `
                <label for="categoryName${categoryCount}">Kategoriename ${categoryCount}:</label>
                <input type="text" id="categoryName${categoryCount}" name="categoryName${categoryCount}" required>
                <label for="categoryValue${categoryCount}">Wert ${categoryCount}:</label>
                <input type="text" id="categoryValue${categoryCount}" name="categoryValue${categoryCount}" required>
            `;
            container.appendChild(div);
        }
    </script>
</head>
<body>
    <?php
    // Session starten, um den Login-Status zu speichern
    session_start();

    // Wenn Login-Daten geschickt wurden, einfache Prüfung (nur als Beispiel)
    if (isset($_POST['username']) && isset($_POST['password'])) {
        // Kurze Beispielprüfung - ersetzen Sie diese Logik mit richtiger Authentifizierung
        if ($_POST['username'] === 'test' && $_POST['password'] === 'secret') {
            $_SESSION['logged_in'] = true;
        }
    }
    ?>

    <?php if (!isset($_SESSION['logged_in'])): ?>
        <!-- Zeige das Login-Formular an, wenn nicht eingeloggt -->
        <form method="post">
            <label>Nutzername:</label>
            <input type="text" name="username" required>
            <label>Passwort:</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
    <?php else: ?>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <?php
            // Hier wird die Datenbankverbindung (PDO) eingerichtet (derzeit auskommentiert)
            // $pdo = new PDO('mysql:host=localhost;dbname=MYDB', 'USER', 'PASS');

            // Kategorien sammeln, indem wir alle POST-Einträge durchgehen
            $categories = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'categoryName') === 0) {
                    $index = str_replace('categoryName', '', $key);
                    $categories[$value] = htmlspecialchars($_POST['categoryValue' . $index]);
                }
            }

            // ChatGPT-Funktion (derzeit auskommentiert), um die gesammelten Daten zu verarbeiten
            /*
            function sendToChatGPT($prompt) {
                $apiKey = 'YOUR_API_KEY';
                $data = [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [['role' => 'user','content' => $prompt]],
                ];
                $options = [
                    CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        "Authorization: Bearer $apiKey"
                    ],
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_RETURNTRANSFER => true
                ];
                $ch = curl_init();
                curl_setopt_array($ch, $options);
                $response = curl_exec($ch);
                curl_close($ch);
                return json_decode($response, true)['choices'][0]['message']['content'] ?? '';
            }

            // Beispielhafte Prompt-Zusammenstellung
            // $prompt = "Die Kategorien sind: ...";
            // $chatGPTResponse = sendToChatGPT($prompt);

            // Platzhalter zum Speichern von Spielergebnissen in einer Datenbank
            // $stmt = $pdo->prepare("INSERT INTO ergebnisse (nutzer, kategorie, wert) VALUES (?, ?, ?)");

            // $stmt->execute([...]);

            // Hier können Sie die ChatGPT-Antwort ausgeben
            // echo '<p>ChatGPT sagt: ' . htmlspecialchars($chatGPTResponse) . '</p>';
            ?>
            <div id="gameArea">
                <h2>Spielbereich</h2>
                <!-- Ausgabe der eingegebenen Kategorien und Werte -->
                <?php foreach ($categories as $name => $value): ?>
                    <p><?php echo htmlspecialchars($name); ?>: <?php echo $value; ?></p>
                <?php endforeach; ?>
                <!-- Hier kann weitere Spiel-Logik folgen -->
            </div>
        <?php else: ?>
            <!-- Formular zur Erfassung der Kategorien -->
            <form method="post" action="">
                <div id="categories">
                    <!-- Hier sind bereits drei Kategoriefelder vorhanden -->
                    <div class="category-input">
                        <label for="categoryName1">Kategoriename 1:</label>
                        <input type="text" id="categoryName1" name="categoryName1" required>
                        <label for="categoryValue1">Wert 1:</label>
                        <input type="text" id="categoryValue1" name="categoryValue1" required>
                    </div>
                    <div class="category-input">
                        <label for="categoryName2">Kategoriename 2:</label>
                        <input type="text" id="categoryName2" name="categoryName2" required>
                        <label for="categoryValue2">Wert 2:</label>
                        <input type="text" id="categoryValue2" name="categoryValue2" required>
                    </div>
                    <div class="category-input">
                        <label for="categoryName3">Kategoriename 3:</label>
                        <input type="text" id="categoryName3" name="categoryName3" required>
                        <label for="categoryValue3">Wert 3:</label>
                        <input type="text" id="categoryValue3" name="categoryValue3" required>
                    </div>
                </div>
                <!-- Button zum Hinzufügen weiterer Kategorien -->
                <button type="button" onclick="addCategory()">Kategorie hinzufügen</button>
                <button type="submit">Spiel starten</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>