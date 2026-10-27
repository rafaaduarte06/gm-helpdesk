<?php

require_once __DIR__ . '/config.php';

// Credenciais lidas da configuração (variáveis de ambiente ou config.local.php)
$host = obterConfig('DB_HOST') . ':' . obterConfig('DB_PORT'); // Endereço do servidor da base de dados
$dbname = obterConfig('DB_NAME'); // Nome da base de dados
$username = obterConfig('DB_USER'); // Nome de utilizador
$password = obterConfig('DB_PASS'); // Palavra-passe

try {
    // Criação da ligação à base de dados
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Configurações adicionais para tratamento de erros
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log the error
    error_log("Database connection error: " . $e->getMessage());
    
    // Em caso de erro, retornar JSON ou mensagem de erro
    if (!headers_sent()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro na conexão com a base de dados: ' . $e->getMessage()]);
    } else {
        echo "Erro na conexão com a base de dados: " . $e->getMessage();
    }
    exit;
}

?>