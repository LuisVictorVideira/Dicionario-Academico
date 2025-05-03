<!DOCTYPE html>
<html lang="pt-br" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dicionário Acadêmico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Dicionário Acadêmico</h1>
        
        <!-- Barra de pesquisa -->
        <div class="header-busca">
            <form method="GET" action="index.php">
                <input type="text" name="busca" placeholder="Pesquise por palavras.." value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                <button type="submit">Pesquisar</button>
            </form>
        </div>
    </header>

    <!-- Botão de alternância de tema no canto inferior direito -->
    <button class="theme-toggle" id="theme-toggle" aria-label="Alternar tema">🌙</button>
</body>
</html>