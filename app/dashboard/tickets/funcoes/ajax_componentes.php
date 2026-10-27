<?php
// Incluir arquivos necessários
require_once('../../../ligacao_base_dados.php');
require_once('tickets_funcoes.php');

header('Content-Type: application/json');

// Verificar se foram enviados os parâmetros necessários
if (!isset($_GET['tipo'])) {
    echo json_encode(['erro' => 'Tipo de componente não especificado']);
    exit;
}

$tipo = $_GET['tipo'];
$sala_id = isset($_GET['sala_id']) ? $_GET['sala_id'] : null;

// Registro para depuração
error_log("AJAX - Tipo: " . $tipo . ", Sala ID: " . ($sala_id ?? 'null'));

try {
    $resultado = [];
    
    // Se o tipo for "computador", buscar computadores disponíveis
    if ($tipo == 'computador') {
        $resultado = obterComputadoresDisponiveis($pdo, $sala_id);
        error_log("Resultado para computadores: " . count($resultado) . " encontrados");
    } else {
        // Caso contrário, buscar componentes do tipo especificado
        $resultado = obterComponentesPorTipo($pdo, $tipo, $sala_id);
        error_log("Resultado para " . $tipo . ": " . count($resultado) . " encontrados");
    }
    
    echo json_encode($resultado);
} catch (Exception $e) {
    error_log("Erro ao processar requisição AJAX: " . $e->getMessage());
    echo json_encode(['erro' => 'Erro ao processar a requisição: ' . $e->getMessage()]);
}
exit; 