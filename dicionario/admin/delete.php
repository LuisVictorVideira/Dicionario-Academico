<?php
require_once '../config/session.php';
require_once '../config/db.php';
checkLogin();

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: dashboard.php");
    exit();
}

// Verifica se a palavra existe
$stmt = $pdo->prepare("SELECT * FROM DICIONARIO WHERE ID = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$palavra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$palavra) {
    echo "Palavra não encontrada.";
    exit();
}

// Deleta a palavra
$stmt = $pdo->prepare("DELETE FROM DICIONARIO WHERE ID = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

// Redireciona de volta para o painel
header("Location: dashboard.php");
exit();
