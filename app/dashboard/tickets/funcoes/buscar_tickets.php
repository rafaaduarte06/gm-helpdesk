// Iniciar sessão e verificar autenticação
session_start();

// Depuração da sessão
error_log("Sessão em buscar_tickets.php: " . print_r($_SESSION, true));

// Verificar se o usuário está logado - aceitar diferentes formatos de sessão
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

// Identificar qual formato está sendo usado
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : $_SESSION['userId'];
$usuario_tipo = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : (isset($_SESSION['userType']) ? $_SESSION['userType'] : '');
$usuario_nome = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : (isset($_SESSION['userName']) ? $_SESSION['userName'] : 'Usuário');

// Guardar em formato padrão para uso consistente
$_SESSION['usuario_id'] = $usuario_id;
$_SESSION['usuario_tipo'] = $usuario_tipo;
$_SESSION['usuario_nome'] = $usuario_nome;

// Conexão com banco de dados
// ... existing code ... 