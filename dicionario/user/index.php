<?php
require_once '../config/db.php';
require_once 'header.php';

// Configuração da paginação
$palavrasPorPagina = 30; // 5 colunas × 4 linhas
$paginaAtual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $palavrasPorPagina;

// Busca o total de palavras no banco de dados
$totalPalavras = $pdo->query("SELECT COUNT(*) FROM DICIONARIO")->fetchColumn();
$totalPaginas = ceil($totalPalavras / $palavrasPorPagina);

// Consulta as palavras da página atual
$stmt = $pdo->prepare("SELECT * FROM DICIONARIO ORDER BY PALAVRA LIMIT :offset, :limit");
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $palavrasPorPagina, PDO::PARAM_INT);
$stmt->execute();
$palavras = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <a href="?pagina=<?= $i ?>" class="<?= $i == $paginaAtual ? 'active' : '' ?>">
             <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</main>

<?php require_once 'footer.php'; ?>