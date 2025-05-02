<?php
require_once '../config/session.php';
require_once '../config/db.php';
checkLogin();

// CONSTANTES
define('DISCIPLINA_PADRAO', 'Sem Disciplina');

$erro = '';
$sucesso = '';

// Verificar e criar disciplina padrão se não existir
$stmt = $pdo->prepare("SELECT ID FROM DISCIPLINA WHERE DISCIPLINA = :padrao");
$stmt->bindValue(':padrao', DISCIPLINA_PADRAO);
$stmt->execute();
$disciplina_padrao_id = $stmt->fetchColumn();

if (!$disciplina_padrao_id) {
    $stmt = $pdo->prepare("INSERT INTO DISCIPLINA (DISCIPLINA) VALUES (:padrao)");
    $stmt->bindValue(':padrao', DISCIPLINA_PADRAO);
    $stmt->execute();
    $disciplina_padrao_id = $pdo->lastInsertId();
}

// Adicionar nova disciplina
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nova_disciplina'])) {
        $nova_disciplina = trim($_POST['nova_disciplina']);
        
        if (empty($nova_disciplina)) {
            $erro = 'Digite o nome da disciplina';
        } else if (strcasecmp($nova_disciplina, DISCIPLINA_PADRAO) === 0) {
            $erro = 'Este nome de disciplina é reservado';
        } else {
            try {
                // Verifica se a disciplina já existe
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM DISCIPLINA WHERE DISCIPLINA = :disciplina");
                $stmt->bindParam(':disciplina', $nova_disciplina);
                $stmt->execute();
                
                if ($stmt->fetchColumn() > 0) {
                    $erro = 'Esta disciplina já existe';
                } else {
                    // Adiciona a nova disciplina
                    $stmt = $pdo->prepare("INSERT INTO DISCIPLINA (DISCIPLINA) VALUES (:disciplina)");
                    $stmt->bindParam(':disciplina', $nova_disciplina);
                    
                    if ($stmt->execute()) {
                        $sucesso = 'Disciplina adicionada com sucesso!';
                    }
                }
            } catch (PDOException $e) {
                $erro = 'Erro ao adicionar disciplina: ' . $e->getMessage();
            }
        }
    }
    
    // Editar disciplina (renomear)
    if (isset($_POST['editar_disciplina'])) {
        $id_disciplina = $_POST['id_disciplina'];
        $disciplina_nova = trim($_POST['disciplina_nova']);
        
        if (empty($disciplina_nova)) {
            $erro = 'Digite o novo nome da disciplina';
        } else if ($disciplina_nova === $_POST['disciplina_antiga']) {
            $erro = 'O novo nome deve ser diferente do atual';
        } else if (strcasecmp($disciplina_nova, DISCIPLINA_PADRAO) === 0) {
            $erro = 'Este nome de disciplina é reservado';
        } else {
            $pdo->beginTransaction();
            
            try {
                // Verifica se a nova disciplina já existe
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM DISCIPLINA WHERE DISCIPLINA = :disciplina AND ID != :id");
                $stmt->bindParam(':disciplina', $disciplina_nova);
                $stmt->bindParam(':id', $id_disciplina);
                $stmt->execute();
                
                if ($stmt->fetchColumn() > 0) {
                    $erro = 'Já existe uma disciplina com este nome';
                    $pdo->rollBack();
                } else {
                    // Atualiza o nome da disciplina
                    $stmt = $pdo->prepare("UPDATE DISCIPLINA SET DISCIPLINA = :nova WHERE ID = :id");
                    $stmt->bindParam(':nova', $disciplina_nova);
                    $stmt->bindParam(':id', $id_disciplina);
                    $stmt->execute();
                    
                    $pdo->commit();
                    $sucesso = 'Disciplina renomeada com sucesso!';
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $erro = 'Erro ao renomear disciplina: ' . $e->getMessage();
            }
        }
    }
}

// Remover disciplina (mover palavras para disciplina padrão)
if (isset($_GET['remover'])) {
    $id_disciplina = $_GET['remover'];
    
    try {
        $pdo->beginTransaction();
        
        // 1. Verifica se é a disciplina padrão
        $stmt = $pdo->prepare("SELECT DISCIPLINA FROM DISCIPLINA WHERE ID = :id");
        $stmt->bindParam(':id', $id_disciplina);
        $stmt->execute();
        $nome_disciplina = $stmt->fetchColumn();
        
        if (strcasecmp($nome_disciplina, DISCIPLINA_PADRAO) === 0) {
            $erro = 'Não é possível remover a disciplina padrão';
            $pdo->rollBack();
        } else {
            // 2. Move todas as palavras para a disciplina padrão
            $stmt = $pdo->prepare("UPDATE DICIONARIO SET ID_DISCIPLINA = :padrao WHERE ID_DISCIPLINA = :id");
            $stmt->bindValue(':padrao', $disciplina_padrao_id);
            $stmt->bindParam(':id', $id_disciplina);
            $stmt->execute();
            
            // 3. Remove a disciplina
            $stmt = $pdo->prepare("DELETE FROM DISCIPLINA WHERE ID = :id");
            $stmt->bindParam(':id', $id_disciplina);
            $stmt->execute();
            
            $pdo->commit();
            $sucesso = "Disciplina removida. Palavras movidas para '" . DISCIPLINA_PADRAO . "'.";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $erro = 'Erro ao remover disciplina: ' . $e->getMessage();
    }
}

// Buscar disciplinas existentes (excluindo a padrão)
$disciplinas = $pdo->query("
    SELECT d.ID, d.DISCIPLINA, COUNT(dic.ID) as total_palavras
    FROM DISCIPLINA d
    LEFT JOIN DICIONARIO dic ON d.ID = dic.ID_DISCIPLINA
    WHERE d.DISCIPLINA != '" . DISCIPLINA_PADRAO . "'
    GROUP BY d.ID, d.DISCIPLINA
    ORDER BY d.DISCIPLINA
")->fetchAll(PDO::FETCH_ASSOC);

// Buscar total de palavras na disciplina padrão
$stmt = $pdo->prepare("SELECT COUNT(*) FROM DICIONARIO WHERE ID_DISCIPLINA = :padrao");
$stmt->bindValue(':padrao', $disciplina_padrao_id);
$stmt->execute();
$total_padrao = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Disciplinas</title>
    <link rel="stylesheet" href="../assets/css/disciplinas.css">
    <script>
        function showEditForm(id, disciplina) {
            // Esconde todos os forms de edição primeiro
            document.querySelectorAll('.edit-disciplina-form').forEach(form => {
                form.style.display = 'none';
            });
            
            // Mostra o form específico
            const form = document.getElementById('edit-form-' + id);
            form.style.display = 'block';
            
            // Preenche o campo com o nome atual
            form.querySelector('input[name="disciplina_nova"]').value = disciplina;
            form.querySelector('input[name="disciplina_antiga"]').value = disciplina;
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Gerenciar Disciplinas</h1>
        <a href="dashboard.php" class="back-btn">← Voltar</a>

        <?php if ($erro): ?>
            <div class="mensagem erro"><?= $erro ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="mensagem sucesso"><?= $sucesso ?></div>
        <?php endif; ?>

        <div class="holder-warning">
            <strong>Disciplina padrão:</strong> <?= DISCIPLINA_PADRAO ?> (<?= $total_padrao ?> palavras)<br>
            Ao remover uma disciplina, todas as suas palavras serão movidas para "<?= DISCIPLINA_PADRAO ?>".
        </div>

        <div class="disciplina-form">
            <form method="POST">
                <div class="form-group">
                    <label>Nova Disciplina</label>
                    <input type="text" name="nova_disciplina" placeholder="Digite o nome da nova disciplina" required>
                </div>
                <button type="submit" class="submit-btn">Adicionar Disciplina</button>
            </form>
        </div>

        <h2>Disciplinas Existentes</h2>
        <ul class="disciplina-list">
            <?php foreach ($disciplinas as $disciplina): ?>
                <li class="disciplina-item">
                    <div class="disciplina-info">
                        <div class="disciplina-nome"><?= htmlspecialchars($disciplina['DISCIPLINA']) ?></div>
                        <div class="disciplina-contador">(<?= $disciplina['total_palavras'] ?> palavras)</div>
                    </div>
                    <div class="acoes">
                        <a href="#" class="edit-btn" onclick="showEditForm('<?= $disciplina['ID'] ?>', '<?= htmlspecialchars($disciplina['DISCIPLINA']) ?>')">Editar</a>
                        <a href="?remover=<?= $disciplina['ID'] ?>" class="remove-btn" onclick="return confirm('Tem certeza que deseja remover a disciplina \'<?= htmlspecialchars($disciplina['DISCIPLINA']) ?>\'?\nAs <?= $disciplina['total_palavras'] ?> palavras associadas serão movidas para \'<?= DISCIPLINA_PADRAO ?>\'.')">Remover</a>
                    </div>
                    <div id="edit-form-<?= $disciplina['ID'] ?>" class="edit-disciplina-form">
                        <form method="POST">
                            <input type="hidden" name="id_disciplina" value="<?= $disciplina['ID'] ?>">
                            <input type="hidden" name="disciplina_antiga" value="<?= htmlspecialchars($disciplina['DISCIPLINA']) ?>">
                            <input type="text" name="disciplina_nova" placeholder="Novo nome" required>
                            <div class="edit-form-actions">
                                <button type="submit" name="editar_disciplina" class="save-btn">Salvar</button>
                                <button type="button" class="cancel-btn" onclick="this.closest('.edit-disciplina-form').style.display='none'">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>