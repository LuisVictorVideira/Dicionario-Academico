<?php
require_once '../config/db.php';
require_once 'header.php';

// Configuração de paginação
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$palavrasPorPagina = 10; // Número de palavras por página
$offset = ($paginaAtual - 1) * $palavrasPorPagina;

// Verifica se há um termo de busca
$busca = $_GET['busca'] ?? '';

// Ajusta a consulta SQL para incluir a busca, se necessário
if (!empty($busca)) {
    $stmt = $pdo->prepare("
        SELECT * 
        FROM DICIONARIO 
        WHERE PALAVRA LIKE :busca 
        ORDER BY PALAVRA 
        LIMIT :offset, :limit
    ");
    $buscaParam = '%' . $busca . '%';
    $stmt->bindParam(':busca', $buscaParam, PDO::PARAM_STR);
} else {
    $stmt = $pdo->prepare("
        SELECT * 
        FROM DICIONARIO 
        ORDER BY PALAVRA 
        LIMIT :offset, :limit
    ");
}

$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $palavrasPorPagina, PDO::PARAM_INT);
$stmt->execute();
$palavras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca o total de palavras no banco de dados para a paginação
if (!empty($busca)) {
    $stmtTotal = $pdo->prepare("
        SELECT COUNT(*) 
        FROM DICIONARIO 
        WHERE PALAVRA LIKE :busca
    ");
    $stmtTotal->bindParam(':busca', $buscaParam, PDO::PARAM_STR);
    $stmtTotal->execute();
    $totalPalavras = $stmtTotal->fetchColumn();
} else {
    $totalPalavras = $pdo->query("SELECT COUNT(*) FROM DICIONARIO")->fetchColumn();
}

$totalPaginas = ceil($totalPalavras / $palavrasPorPagina);
?>

<main>
    <div class="grade-wrapper">
        <ul class="grade-palavras">
            <?php foreach ($palavras as $p): ?>
                <li>
                    <a href="palavra.php?id=<?= $p['ID'] ?>">
                        <?= htmlspecialchars($p['PALAVRA']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Navegação entre páginas -->
    <div class="paginacao">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <a href="?pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>" class="<?= $i == $paginaAtual ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</main>

<?php require_once 'footer.php'; ?>