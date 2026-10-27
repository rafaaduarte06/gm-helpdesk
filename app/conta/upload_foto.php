<?php
session_start();
header('Content-Type: application/json');
require_once('../ligacao_base_dados.php');

if (!isset($_FILES['foto'])) {
    echo json_encode(['success' => false, 'message' => 'Nenhuma foto enviada']);
    exit;
}

$file = $_FILES['foto'];
$userId = $_SESSION['userId'];
$userType = $_SESSION['userType'];

// Verificar tipo de arquivo
$allowed = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido']);
    exit;
}

// Gerar nome único para o arquivo
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '.' . $extension;
$uploadPath = '../assets/img/perfil/' . $filename;

try {
    // Selecionar tabela correta baseado no tipo de usuário
    $table = ($userType === 'tecnico') ? 'suporte_tecnico' : 'comunidade_escolar';
    $idColumn = 'id'; // Ambas as tabelas usam 'id' como coluna de identificação

    // Buscar foto antiga para remover
    $stmt = $pdo->prepare("SELECT foto_perfil FROM {$table} WHERE {$idColumn} = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    // Remover foto antiga se existir
    if ($user && $user['foto_perfil'] && $user['foto_perfil'] !== 'default.jpg') {
        $oldFile = '../assets/img/perfil/' . $user['foto_perfil'];
        if (file_exists($oldFile)) {
            unlink($oldFile);
        }
    }

    // Mover arquivo novo
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Atualizar banco de dados
        $query = "UPDATE {$table} SET foto_perfil = :foto WHERE {$idColumn} = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':foto' => $filename,
            ':id' => $userId
        ]);

        echo json_encode([
            'success' => true, 
            'message' => 'Foto atualizada com sucesso',
            'foto' => $filename
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar arquivo']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 