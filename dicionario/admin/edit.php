<?php
require_once '../config/session.php'; // Inclui o arquivo de sessão para verificar login
require_once '../config/db.php'; // Inclui o arquivo de conexão com o banco de dados
checkLogin(); // Verifica se o usuário está logado

// Buscar disciplinas disponíveis para exibição no formulário
$disciplinas = $pdo->query("SELECT ID, DISCIPLINA FROM DISCIPLINA ORDER BY DISCIPLINA")->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null; // Obtém o ID da palavra a ser editada via parâmetro GET
$erro = ''; // Variável para armazenar mensagens de erro
$sucesso = ''; // Variável para armazenar mensagens de sucesso

// Redireciona para o dashboard se o ID não for fornecido
if (!$id) {
    header("Location: dashboard.php");
    exit();
}

// Carrega os dados da palavra atual com base no ID fornecido
$stmt = $pdo->prepare("SELECT d.*, dis.DISCIPLINA as DISCIPLINA_NOME 
                      FROM DICIONARIO d
                      LEFT JOIN DISCIPLINA dis ON d.ID_DISCIPLINA = dis.ID
                      WHERE d.ID = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT); // Associa o ID ao parâmetro da consulta
$stmt->execute();
$palavra = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados da palavra

// Verifica se a palavra existe no banco de dados
if (!$palavra) {
    echo "Palavra não encontrada."; // Exibe mensagem de erro se a palavra não for encontrada
    exit();
}

// Verifica se o formulário foi enviado via método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os valores enviados pelo formulário
    $nova_palavra = $_POST['palavra'] ?? ''; // Nova palavra
    $significado = $_POST['significado'] ?? ''; // Novo significado
    $id_disciplina = $_POST['disciplina'] ?? ''; // Nova disciplina
    $contexto = $_POST['contexto'] ?? ''; // Novo contexto (opcional)

    // Valida se os campos obrigatórios foram preenchidos
    if (empty($nova_palavra) || empty($significado) || empty($id_disciplina)) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } else {
        // Prepara a consulta para atualizar os dados da palavra
        $stmt = $pdo->prepare("UPDATE DICIONARIO 
                              SET PALAVRA = :palavra, 
                                  SIGNIFICADO = :significado, 
                                  ID_DISCIPLINA = :id_disciplina, 
                                  CONTEXTO = :contexto
                              WHERE ID = :id");
        
        // Associa os valores aos parâmetros da consulta
        $stmt->bindParam(':palavra', $nova_palavra);
        $stmt->bindParam(':significado', $significado);
        $stmt->bindParam(':id_disciplina', $id_disciplina);
        $stmt->bindParam(':contexto', $contexto);
        $stmt->bindParam(':id', $id);

        // Executa a consulta e verifica se foi bem-sucedida
        if ($stmt->execute()) {
            $sucesso = "Palavra atualizada com sucesso!"; // Mensagem de sucesso

            // Atualiza os dados locais para refletir as alterações
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
            // Mensagem de erro em caso de falha na atualização
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
    <link rel="stylesheet" href="../assets/css/edit.css"> <!-- Link para o arquivo CSS -->
</head>
<body>
    <div class="container">
        <h1>Editar Palavra</h1>
        <a href="dashboard.php" class="back-btn">← Voltar</a> <!-- Botão para voltar ao dashboard -->

        <!-- Exibe mensagem de erro, se houver -->
        <?php if ($erro): ?>
            <div class="mensagem erro"><?= $erro ?></div>
        <?php endif; ?>

        <!-- Exibe mensagem de sucesso, se houver -->
        <?php if ($sucesso): ?>
            <div class="mensagem sucesso"><?= $sucesso ?></div>
        <?php endif; ?>

        <!-- Formulário para editar a palavra -->
        <form method="POST" class="edit-form">
            <div class="form-group">
                <label class="required-field">Palavra (Inglês)</label>
                <input type="text" name="palavra" required value="<?= htmlspecialchars($palavra['PALAVRA']) ?>"> <!-- Campo para editar a palavra -->
            </div>
            
            <div class="form-group">
                <label class="required-field">Significado</label>
                <textarea name="significado" required><?= htmlspecialchars($palavra['SIGNIFICADO']) ?></textarea> <!-- Campo para editar o significado -->
            </div>
            
            <div class="form-group">
                <label class="required-field">Disciplina</label>
                <select name="disciplina" required>
                    <option value="">Selecione uma disciplina</option>
                    <!-- Preenche as opções do select com as disciplinas disponíveis -->
                    <?php foreach ($disciplinas as $disciplina): ?>
                        <option value="<?= htmlspecialchars($disciplina['ID']) ?>" <?= $palavra['ID_DISCIPLINA'] == $disciplina['ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($disciplina['DISCIPLINA']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <a href="disciplinas.php" class="add-disciplina-link">+ Adicionar nova disciplina</a> <!-- Link para adicionar nova disciplina -->
            </div>
            
            <div class="form-group">
                <label>Contexto</label>
                <textarea name="contexto"><?= htmlspecialchars($palavra['CONTEXTO'] ?? '') ?></textarea> <!-- Campo para editar o contexto -->
            </div>
            
            <button type="submit" class="submit-btn">Salvar Alterações</button> <!-- Botão para salvar as alterações -->
        </form>
    </div>
</body>
</html>