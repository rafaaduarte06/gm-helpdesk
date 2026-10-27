<?php
session_start();
require_once('../ligacao_base_dados.php');

header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$table = $_SESSION['userType'] === 'tecnico' ? 'suporte_tecnico' : 'comunidade_escolar';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($_FILES['foto'])) {
            // Upload de nova foto
            $file = $_FILES['foto'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];

            if (!in_array($ext, $allowed)) {
                throw new Exception('Formato de arquivo não permitido');
            }

            $filename = uniqid() . '.' . $ext;
            $path = '../assets/img/perfil/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $path)) {
                // Apagar foto antiga se existir
                $stmt = $pdo->prepare("SELECT foto_perfil FROM $table WHERE id = ?");
                $stmt->execute([$_SESSION['userId']]);
                $oldFile = $stmt->fetchColumn();

                if ($oldFile && $oldFile !== 'default.jpg') {
                    @unlink('../assets/img/perfil/' . $oldFile);
                }

                // Atualizar no banco
                $stmt = $pdo->prepare("UPDATE $table SET foto_perfil = ? WHERE id = ?");
                $stmt->execute([$filename, $_SESSION['userId']]);

                echo json_encode(['success' => true, 'filename' => $filename]);
                exit;
            }
        } elseif (isset($data['action']) && $data['action'] === 'delete') {
            // Apagar foto
            $stmt = $pdo->prepare("SELECT foto_perfil FROM $table WHERE id = ?");
            $stmt->execute([$_SESSION['userId']]);
            $oldFile = $stmt->fetchColumn();

            if ($oldFile && $oldFile !== 'default.jpg') {
                @unlink('../assets/img/perfil/' . $oldFile);
            }

            $stmt = $pdo->prepare("UPDATE $table SET foto_perfil = 'default.jpg' WHERE id = ?");
            $stmt->execute([$_SESSION['userId']]);

            echo json_encode(['success' => true]);
            exit;
        }
    }

    throw new Exception('Requisição inválida');
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 