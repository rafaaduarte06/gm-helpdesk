<?php
/**
 * Script para obter o ID correto de um computador com base na referência
 */

// Header para retornar JSON
header('Content-Type: application/json');

// Incluir arquivo de conexão
require_once '../../ligacao_base_dados.php';

// Verificar se a referência foi fornecida
if (!isset($_GET['ref']) || empty($_GET['ref'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Referência do computador não fornecida'
    ]);
    exit;
}

$ref = trim($_GET['ref']);

try {
    // Tentar encontrar o computador pela referência
    $stmt = $pdo->prepare("SELECT COMP_ID FROM computadores WHERE COMP_REF = ?");
    $stmt->execute([$ref]);
    $computer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($computer) {
        echo json_encode([
            'success' => true,
            'id' => $computer['COMP_ID']
        ]);
    } else {
        // Se não encontrar pelo campo COMP_REF, tenta buscar diretamente pelo ID
        // Este é um fallback caso o componente ID já seja o ID do computador
        if (is_numeric($ref)) {
            $stmt = $pdo->prepare("SELECT COMP_ID FROM computadores WHERE COMP_ID = ?");
            $stmt->execute([$ref]);
            $computer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($computer) {
                echo json_encode([
                    'success' => true,
                    'id' => $computer['COMP_ID']
                ]);
                exit;
            }
        }
        
        // Se não encontrar de nenhuma forma
        echo json_encode([
            'success' => false,
            'message' => 'Computador não encontrado'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar computador: ' . $e->getMessage()
    ]);
}
?> 