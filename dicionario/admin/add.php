<?php
// Inclui os arquivos de configuração para sessão e banco de dados
require_once '../config/session.php';
require_once '../config/db.php';

// Verifica se o usuário está logado
checkLogin();

// Busca as disciplinas disponíveis no banco de dados
$disciplinas = $pdo->query("SELECT ID, DISCIPLINA FROM DISCIPLINA ORDER BY DISCIPLINA")->fetchAll(PDO::FETCH_ASSOC);

// Inicializa variáveis para mensagens e campos do formulário
$erro = '';
$sucesso = '';
$palavra = $significado = $contexto = '';
$disciplina_selecionada = '';

// Verifica se o formulário foi enviado via método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os valores enviados pelo formulário
    $palavra = $_POST['palavra'] ?? '';
    $significado = $_POST['significado'] ?? '';
    $disciplina_selecionada = $_POST['disciplina'] ?? '';
    $contexto = $_POST['contexto'] ?? '';

    // Valida se os campos obrigatórios foram preenchidos
    if (empty($palavra) || empty($significado) || empty($disciplina_selecionada)) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } else {
        // Prepara a query para inserir os dados no banco de dados
        $stmt = $pdo->prepare("INSERT INTO DICIONARIO (PALAVRA, SIGNIFICADO, ID_DISCIPLINA, CONTEXTO) 
                               VALUES (:palavra, :significado, :id_disciplina, :contexto)");
        // Associa os valores às variáveis da query
        $stmt->bindParam(':palavra', $palavra);
        $stmt->bindParam(':significado', $significado);
        $stmt->bindParam(':id_disciplina', $disciplina_selecionada);
        $stmt->bindParam(':contexto', $contexto);
        
        // Executa a query e verifica se foi bem-sucedida
        if ($stmt->execute()) {
            $sucesso = 'Palavra adicionada com sucesso!';
            // Limpa os campos do formulário após o sucesso
            $palavra = $significado = $contexto = '';
            $disciplina_selecionada = '';
        } else {
            // Exibe mensagem de erro caso a inserção falhe
            $erro = 'Erro ao adicionar a palavra. ' . implode(" ", $stmt->errorInfo());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Palavra</title>
    <link rel="stylesheet" href="../assets/css/add.css">
</head>
<body>
    <div class="container">
        <h1>Adicionar Nova Palavra</h1>
        <!-- Botão para voltar ao dashboard -->
        <a href="dashboard.php" class="back-btn">← Voltar</a>

        <!-- Exibe mensagem de erro, se houver -->
        <?php if ($erro): ?>
            <div class="mensagem erro"><?= $erro ?></div>
        <?php endif; ?>

        <!-- Exibe mensagem de sucesso, se houver -->
        <?php if ($sucesso): ?>
            <div class="mensagem sucesso"><?= $sucesso ?></div>
        <?php endif; ?>

        <!-- Formulário para adicionar nova palavra -->
        <form method="POST" class="add-form">
            <div class="form-group">
                <label class="required-field">Palavra </label>
                <input type="text" name="palavra" required value="<?= htmlspecialchars($palavra) ?>">
            </div>
            
            <div class="form-group">
                <label class="required-field">Significado</label>
                <textarea name="significado" required><?= htmlspecialchars($significado) ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="required-field">Disciplina</label>
                <select name="disciplina" required>
                    <option value="">Selecione uma disciplina</option>
                    <!-- Preenche as opções do select com as disciplinas disponíveis -->
                    <?php foreach ($disciplinas as $disciplina): ?>
                        <option value="<?= htmlspecialchars($disciplina['ID']) ?>" <?= $disciplina_selecionada == $disciplina['ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($disciplina['DISCIPLINA']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <!-- Link para adicionar nova disciplina -->
                <a href="disciplinas.php" class="add-disciplina-link">+ Adicionar nova disciplina</a>
            </div>
            
            <div class="form-group">
                <label>Contexto</label>
                <textarea name="contexto"><?= htmlspecialchars($contexto) ?></textarea>
            </div>
            
            <!-- Botão para enviar o formulário -->
            <button type="submit" class="submit-btn">Adicionar Palavra</button>
        </form>
    </div>
</body>
</html>