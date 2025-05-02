<?php
require_once '../config/session.php';
require_once '../config/db.php';
checkLogin();

// Configuração de paginação
$itens_por_pagina = 30;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

$busca = $_GET['busca'] ?? '';

// Consulta principal com JOIN
$sql = "SELECT SQL_CALC_FOUND_ROWS DI.ID, DI.PALAVRA, DI.SIGNIFICADO, DI.CONTEXTO, D.DISCIPLINA
        FROM DICIONARIO DI
        LEFT JOIN DISCIPLINA D ON DI.ID_DISCIPLINA = D.ID";

$sql_count = "SELECT COUNT(*) as total
              FROM DICIONARIO DI
              LEFT JOIN DISCIPLINA D ON DI.ID_DISCIPLINA = D.ID";

if (!empty($busca)) {
    $sql .= " WHERE DI.PALAVRA LIKE :busca 
              OR DI.SIGNIFICADO LIKE :busca 
              OR DI.CONTEXTO LIKE :busca 
              OR D.DISCIPLINA LIKE :busca";

    $sql_count .= " WHERE DI.PALAVRA LIKE :busca 
                    OR DI.SIGNIFICADO LIKE :busca 
                    OR DI.CONTEXTO LIKE :busca 
                    OR D.DISCIPLINA LIKE :busca";
}

$sql .= " ORDER BY DI.ID DESC LIMIT :offset, :itens_por_pagina";

// Buscar palavras
$stmt = $pdo->prepare($sql);
if (!empty($busca)) {
    $busca_param = '%' . $busca . '%';
    $stmt->bindParam(':busca', $busca_param, PDO::PARAM_STR);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':itens_por_pagina', $itens_por_pagina, PDO::PARAM_INT);
$stmt->execute();
$palavras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter total de registros
$total_registros = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$total_paginas = ceil($total_registros / $itens_por_pagina);

// Buscar disciplinas disponíveis
$disciplinas = $pdo->query("SELECT DISCIPLINA FROM DISCIPLINA WHERE DISCIPLINA IS NOT NULL AND DISCIPLINA != '' ORDER BY DISCIPLINA")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Dashboard - Admin</h1>
        <a href="logout.php" class="logout-btn">Sair</a>

        <div class="busca-container">
    <form method="GET" style="flex: 1; display: flex; gap: 10px; flex-wrap: wrap; position: relative;">
        <input type="text" name="busca" placeholder="Buscar palavra, significado, disciplina ou contexto..." 
               value="<?= htmlspecialchars($busca) ?>" id="campoBusca">
        <button type="submit">Buscar</button>
        <?php if(!empty($busca)): ?>
            <button type="button" class="limpar-busca" onclick="limparBusca()">×</button>
        <?php endif; ?>
        <input type="hidden" name="pagina" value="1">
    </form>
    <div class="acoes-container">
        <a href="add.php" class="add-btn">+ Adicionar Palavra</a>
        <a href="disciplinas.php" class="add-disciplina-btn">Gerenciar Disciplinas</a>
    </div>
</div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Palavra</th>
                    <th>Significado</th>
                    <th>Contexto</th>
                    <th>Disciplina</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($palavras as $p): ?>
                    <tr>
                        <td><?= $p['ID'] ?></td>
                        <td><?= htmlspecialchars($p['PALAVRA']) ?></td>
                        <td class="<?= empty($p['SIGNIFICADO']) ? 'null-value' : '' ?>">
                            <?= !empty($p['SIGNIFICADO']) ? htmlspecialchars($p['SIGNIFICADO']) : '(não definido)' ?>
                        </td>
                        <td class="contexto <?= empty($p['CONTEXTO']) ? 'null-value' : '' ?>" title="<?= htmlspecialchars($p['CONTEXTO'] ?? '') ?>">
                            <?= !empty($p['CONTEXTO']) ? htmlspecialchars($p['CONTEXTO']) : '(sem contexto)' ?>
                        </td>
                        <td><?= htmlspecialchars($p['DISCIPLINA'] ?? '(sem disciplina)') ?></td>
                        <td class="acoes">
                            <a href="edit.php?id=<?= $p['ID'] ?>">Editar</a>
                            <a href="delete.php?id=<?= $p['ID'] ?>" onclick="return confirm('Tem certeza que deseja excluir esta palavra?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_paginas > 1): ?>
        <div class="paginacao">
            <?php if ($pagina_atual > 1): ?>
                <a href="?pagina=1&busca=<?= urlencode($busca) ?>">«</a>
                <a href="?pagina=<?= $pagina_atual - 1 ?>&busca=<?= urlencode($busca) ?>">‹</a>
            <?php endif; ?>

            <?php 
            $inicio = max(1, $pagina_atual - 2);
            $fim = min($total_paginas, $pagina_atual + 2);

            for ($i = $inicio; $i <= $fim; $i++): ?>
                <?php if ($i == $pagina_atual): ?>
                    <span><?= $i ?></span>
                <?php else: ?>
                    <a href="?pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($pagina_atual < $total_paginas): ?>
                <a href="?pagina=<?= $pagina_atual + 1 ?>&busca=<?= urlencode($busca) ?>">›</a>
                <a href="?pagina=<?= $total_paginas ?>&busca=<?= urlencode($busca) ?>">»</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <script>
function limparBusca() {
    // Remove o parâmetro de busca da URL
    const url = new URL(window.location.href);
    url.searchParams.delete('busca');
    url.searchParams.set('pagina', '1');
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    const campoBusca = document.getElementById('campoBusca');
    const limparBtn = document.querySelector('.limpar-busca');
    
    if (campoBusca) {
        // Mostrar/ocultar o botão conforme digitação
        campoBusca.addEventListener('input', function() {
            if (limparBtn) {
                limparBtn.style.display = this.value.trim() !== '' ? 'block' : 'none';
            }
        });
    }
});
</script>
</body>
</html>
