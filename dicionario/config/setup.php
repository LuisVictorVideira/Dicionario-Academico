<?php
$host = 'localhost'; // Host do banco de dados
$user = 'root';      // Usuário do banco de dados
$pass = '';          // Senha do banco de dados
$dbName = 'dicionario'; // Nome do banco de dados

try {
    // Conexão inicial sem selecionar o banco de dados
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica se o banco de dados já existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbName");
    $pdo->exec("USE $dbName");

    // Criação da tabela DISCIPLINA
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS DISCIPLINA (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            DISCIPLINA VARCHAR(30) NOT NULL
        )
    ");

    // Criação da tabela DICIONARIO
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS DICIONARIO (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            PALAVRA VARCHAR(30) NOT NULL,
            SIGNIFICADO VARCHAR(300) NOT NULL,
            CONTEXTO VARCHAR(1000),
            ID_DISCIPLINA INT,
            FOREIGN KEY (ID_DISCIPLINA) REFERENCES DISCIPLINA(ID)
        )
    ");

    echo "Banco de dados e tabelas criados com sucesso!";
} catch (PDOException $e) {
    die("Erro ao configurar o banco de dados: " . $e->getMessage());
}