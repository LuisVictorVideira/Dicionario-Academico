<?php
require_once '../config/session.php';
require_once '../config/db.php';
checkLogin();

// Buscar disciplinas disponíveis
$disciplinas = $pdo->query("SELECT ID, DISCIPLINA FROM DISCIPLINA ORDER BY DISCIPLINA")->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
$erro = '';
$sucesso = '';

if (!$id) {
    header("Location: dashboard.php");
    exit();
}

// Carrega os dados da palavra atual
$stmt = $pdo->prepare("SELECT d.*, dis.DISCIPLINA as DISCIPLINA_NOME 
                      FROM DICIONARIO d
                      LEFT JOIN DISCIPLINA dis ON d.ID_DISCIPLINA = dis.ID
                      WHERE d.ID = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$palavra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$palavra) {
    echo "Palavra não encontrada.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_palavra = $_POST['palavra'] ?? '';
    $significado = $_POST['significado'] ?? '';
    $id_disciplina = $_POST['disciplina'] ?? '';
    $contexto = $_POST['contexto'] ?? '';

    if (empty($nova_palavra) || empty($significado) || empty($id_disciplina)) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } else {
        $stmt = $pdo->prepare("UPDATE DICIONARIO 
                              SET PALAVRA = :palavra, 
                                  SIGNIFICADO = :significado, 
                                  ID_DISCIPLINA = :id_disciplina, 
                                  CONTEXTO = :contexto
                              WHERE ID = :id");
        
        $stmt->bindParam(':palavra', $nova_palavra);
        $stmt->bindParam(':significado', $significado);
        $stmt->bindParam(':id_disciplina', $id_disciplina);
        $stmt->bindParam(':contexto', $contexto);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            $sucesso = "Palavra atualizada com sucesso!";
            // Atualiza os dados locais
            $palavra['PALAVRA'] = $nova_palavra;
            $palavra['SIGNIFICADO'] = $significado;
            $palavra['ID_DISCIPLINA'] = $id_disciplina;
            $palavra['CONTEXTO'] = $contexto;
            // Atualiza também o nome da disciplina para exibição
            foreach ($disciplinas as $disciplina) {
                if ($disciplina['ID'] == $id_disciplina) {
                    $palavra['DISCIPLINA_NOME'] = $disciplina['DISCIPLINA'];
                    break;
                }
            }
        } else {
            $erro = 'Erro ao atualizar a palavra. ' . implode(" ", $stmt->errorInfo());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Palavra</title>
    <link rel="stylesheet" href="../assets/css/edit.css">
</head>
<body>
    <div class="container">
        <h1>Editar Palavra</h1>
        <a href="dashboard.php" class="back-btn">← Voltar</a>

        <?php if ($erro): ?>
            <div class="mensagem erro"><?= $erro ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="mensagem sucesso"><?= $sucesso ?></div>
        <?php endif; ?>

        <form method="POST" class="edit-form">
            <div class="form-group">
                <label class="required-field">Palavra (Inglês)</label>
                <input type="text" name="palavra" required value="<?= htmlspecialchars($palavra['PALAVRA']) ?>">
            </div>
            
            <div class="form-group">
                <label class="required-field">Significado</label>
                <textarea name="significado" required><?= htmlspecialchars($palavra['SIGNIFICADO']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="required-field">Disciplina</label>
                <select name="disciplina" required>
                    <option value="">Selecione uma disciplina</option>
                    <?php foreach ($disciplinas as $disciplina): ?>
                        <option value="<?= htmlspecialchars($disciplina['ID']) ?>" <?= $palavra['ID_DISCIPLINA'] == $disciplina['ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($disciplina['DISCIPLINA']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <a href="disciplinas.php" class="add-disciplina-link">+ Adicionar nova disciplina</a>
            </div>
            
            <div class="form-group">
                <label>Contexto</label>
                <textarea name="contexto"><?= htmlspecialchars($palavra['CONTEXTO'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" class="submit-btn">Salvar Alterações</button>
        </form>
    </div>
</body>
</html>