<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stadt Land Fluss</title>
    <script>
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
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <?php
        $categories = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'categoryName') === 0) {
                $index = str_replace('categoryName', '', $key);
                $categories[$value] = htmlspecialchars($_POST['categoryValue' . $index]);
            }
        }
        ?>
        <div id="gameArea">
            <h2>Spielbereich</h2>
            <?php foreach ($categories as $name => $value): ?>
                <p><?php echo htmlspecialchars($name); ?>: <?php echo $value; ?></p>
            <?php endforeach; ?>
            <!-- Weitere Spiel-Logik kann hier hinzugefügt werden -->
        </div>
    <?php else: ?>
        <form method="post" action="">
            <div id="categories">
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
            <button type="button" onclick="addCategory()">Kategorie hinzufügen</button>
            <button type="submit">Spiel starten</button>
        </form>
    <?php endif; ?>
</body>
</html>