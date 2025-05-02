<?php
require_once '../config/db.php';
require_once 'header.php';

// Verifica se o ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Palavra não encontrada.</p>";
    require_once 'footer.php';
    exit;
}

$id = (int) $_GET['id'];

// Consulta a palavra pelo ID
$stmt = $pdo->prepare("SELECT * FROM DICIONARIO WHERE ID = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$palavra = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se encontrou
if (!$palavra) {
    echo "<p>Palavra não encontrada.</p>";
    require_once 'footer.php';
    exit;
}
?>

<main>
    <h2><?= htmlspecialchars($palavra['PALAVRA']) ?></h2>

    <p><strong>Significado:</strong> <?= nl2br(htmlspecialchars($palavra['SIGNIFICADO'])) ?></p>
    <p><strong>Contexto:</strong> <?= nl2br(htmlspecialchars($palavra['CONTEXTO'])) ?></p>
    <p><strong>Disciplina:</strong> <?= htmlspecialchars($palavra['DISCIPLINA']) ?></p>

    <p><a href="index.php">← Voltar</a></p>
</main>

<?php require_once 'footer.php'; ?>
