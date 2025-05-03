<?php
require_once '../config/session.php'; // Inclui o arquivo de sessão
require_once '../config/db.php'; // Inclui o arquivo de conexão com o banco de dados
checkLogin(); // Verifica se o usuário está logado

// Configuração de paginação
$itens_por_pagina = 30; // Número de itens por página
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Página atual (obtida via GET)
$offset = ($pagina_atual - 1) * $itens_por_pagina; // Calcula o deslocamento para a consulta SQL

$busca = $_GET['busca'] ?? ''; // Obtém o termo de busca, se fornecido

// Consulta principal com JOIN para buscar palavras e suas disciplinas
$sql = "SELECT SQL_CALC_FOUND_ROWS DI.ID, DI.PALAVRA, DI.SIGNIFICADO, DI.CONTEXTO, D.DISCIPLINA
        FROM DICIONARIO DI
        LEFT JOIN DISCIPLINA D ON DI.ID_DISCIPLINA = D.ID";

$sql_count = "SELECT COUNT(*) as total
              FROM DICIONARIO DI
              LEFT JOIN DISCIPLINA D ON DI.ID_DISCIPLINA = D.ID";

// Adiciona condições de busca com filtro selecionado
$filtro = $_GET['filtro'] ?? 'palavra'; // Padrão: filtrar por palavra

if (!empty($busca)) {
    $busca_param = '%' . $busca . '%';
    
    // Aplica o filtro selecionado
    switch ($filtro) {
        case 'palavra':
            $sql .= " WHERE DI.PALAVRA LIKE :busca";
            $sql_count .= " WHERE DI.PALAVRA LIKE :busca";
            break;
        case 'contexto':
            $sql .= " WHERE DI.CONTEXTO LIKE :busca";
            $sql_count .= " WHERE DI.CONTEXTO LIKE :busca";
            break;
        case 'disciplina':
            $sql .= " WHERE D.DISCIPLINA LIKE :busca";
            $sql_count .= " WHERE D.DISCIPLINA LIKE :busca";
            break;
        default:
            $sql .= " WHERE DI.PALAVRA LIKE :busca";
            $sql_count .= " WHERE DI.PALAVRA LIKE :busca";
    }
}

// Ordena os resultados e aplica a paginação
$sql .= " ORDER BY DI.ID DESC LIMIT :offset, :itens_por_pagina";

// Prepara a consulta para buscar palavras
$stmt = $pdo->prepare($sql);
if (!empty($busca)) {
    $busca_param = '%' . $busca . '%'; // Adiciona os curingas para busca parcial
    $stmt->bindParam(':busca', $busca_param, PDO::PARAM_STR); // Associa o parâmetro de busca
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT); // Define o deslocamento
$stmt->bindParam(':itens_por_pagina', $itens_por_pagina, PDO::PARAM_INT); // Define o limite de itens por página
$stmt->execute();
$palavras = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtém os resultados

// Obter o total de registros encontrados
$total_registros = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$total_paginas = ceil($total_registros / $itens_por_pagina); // Calcula o total de páginas

// Buscar disciplinas disponíveis para exibição
$disciplinas = $pdo->query("SELECT DISCIPLINA FROM DISCIPLINA WHERE DISCIPLINA IS NOT NULL AND DISCIPLINA != '' ORDER BY DISCIPLINA")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Admin - Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css"> <!-- Link para o CSS -->
</head>
<body>
    <div class="container">
        <h1>Dashboard - Admin</h1>
        <a href="logout.php" class="logout-btn">Sair</a> <!-- Botão de logout -->

        <!-- Formulário de busca -->
        <!-- Formulário de busca -->
<div class="busca-container">
    <form method="GET" style="flex: 1; display: flex; gap: 10px; flex-wrap: wrap; position: relative;">
        <input type="text" name="busca" placeholder="Digite sua busca..." 
               value="<?= htmlspecialchars($busca) ?>" id="campoBusca">
        
        <!-- Seleção de filtro -->
        <select name="filtro" id="filtro">
            <option value="palavra" <?= ($_GET['filtro'] ?? '') === 'palavra' ? 'selected' : '' ?>>Filtrar por Palavra</option>
            <option value="contexto" <?= ($_GET['filtro'] ?? '') === 'contexto' ? 'selected' : '' ?>>Filtrar por Contexto</option>
            <option value="disciplina" <?= ($_GET['filtro'] ?? '') === 'disciplina' ? 'selected' : '' ?>>Filtrar por Disciplina</option>
        </select>
        
        <button type="submit">Buscar</button>
        <?php if (!empty($busca)): ?>
            <button type="button" class="limpar-busca" onclick="limparBusca()">×</button>
        <?php endif; ?>
        <input type="hidden" name="pagina" value="1">
    </form>
    <div class="acoes-container">
        <a href="add.php" class="add-btn">+ Adicionar Palavra</a>
        <a href="disciplinas.php" class="add-disciplina-btn">Gerenciar Disciplinas</a>
    </div>
</div>

        <!-- Tabela de palavras -->
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
                        <td><?= $p['ID'] ?></td> <!-- ID da palavra -->
                        <td><?= htmlspecialchars($p['PALAVRA']) ?></td> <!-- Palavra -->
                        <td class="<?= empty($p['SIGNIFICADO']) ? 'null-value' : '' ?>">
                            <?= !empty($p['SIGNIFICADO']) ? htmlspecialchars($p['SIGNIFICADO']) : '(não definido)' ?> <!-- Significado -->
                        </td>
                        <td class="contexto <?= empty($p['CONTEXTO']) ? 'null-value' : '' ?>" title="<?= htmlspecialchars($p['CONTEXTO'] ?? '') ?>">
                            <?= !empty($p['CONTEXTO']) ? htmlspecialchars($p['CONTEXTO']) : '(sem contexto)' ?> <!-- Contexto -->
                        </td>
                        <td><?= htmlspecialchars($p['DISCIPLINA'] ?? '(sem disciplina)') ?></td> <!-- Disciplina -->
                        <td class="acoes">
                            <a href="edit.php?id=<?= $p['ID'] ?>">Editar</a> <!-- Link para editar -->
                            <a href="delete.php?id=<?= $p['ID'] ?>" onclick="return confirm('Tem certeza que deseja excluir esta palavra?')">Excluir</a> <!-- Link para excluir -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginação -->
        <?php if ($total_paginas > 1): ?>
        <div class="paginacao">
            <?php if ($pagina_atual > 1): ?>
                <a href="?pagina=1&busca=<?= urlencode($busca) ?>">«</a> <!-- Link para a primeira página -->
                <a href="?pagina=<?= $pagina_atual - 1 ?>&busca=<?= urlencode($busca) ?>">‹</a> <!-- Link para a página anterior -->
            <?php endif; ?>

            <?php 
            $inicio = max(1, $pagina_atual - 2); // Define o início da paginação
            $fim = min($total_paginas, $pagina_atual + 2); // Define o fim da paginação

            for ($i = $inicio; $i <= $fim; $i++): ?>
                <?php if ($i == $pagina_atual): ?>
                    <span><?= $i ?></span> <!-- Página atual -->
                <?php else: ?>
                    <a href="?pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>"><?= $i ?></a> <!-- Link para outras páginas -->
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($pagina_atual < $total_paginas): ?>
                <a href="?pagina=<?= $pagina_atual + 1 ?>&busca=<?= urlencode($busca) ?>">›</a> <!-- Link para a próxima página -->
                <a href="?pagina=<?= $total_paginas ?>&busca=<?= urlencode($busca) ?>">»</a> <!-- Link para a última página -->
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Script para limpar busca -->
    <script>
    function limparBusca() {
    // Remove os parâmetros de busca e filtro da URL
    const url = new URL(window.location.href);
    url.searchParams.delete('busca');
    url.searchParams.delete('filtro');
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
