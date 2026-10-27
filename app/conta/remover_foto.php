<?php
session_start();
header('Content-Type: application/json');
require_once('../ligacao_base_dados.php');

$userId = $_SESSION['userId'];
$userType = $_SESSION['userType'];

try {
    // Selecionar tabela correta baseado no tipo de usuário
    $table = ($userType === 'tecnico') ? 'suporte_tecnico' : 'comunidade_escolar';
    $idColumn = 'id'; // Ambas as tabelas usam 'id' como coluna de identificação
    
    // Buscar nome do arquivo atual
    $query = "SELECT foto_perfil FROM {$table} WHERE {$idColumn} = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if ($user && $user['foto_perfil'] && $user['foto_perfil'] !== 'default.jpg') {
        // Remover arquivo
        $filepath = '../assets/img/perfil/' . $user['foto_perfil'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    // Atualizar banco de dados para usar a foto default
    $query = "UPDATE {$table} SET foto_perfil = 'default.jpg' WHERE {$idColumn} = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $userId]);

    echo json_encode(['success' => true, 'message' => 'Foto removida com sucesso']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 