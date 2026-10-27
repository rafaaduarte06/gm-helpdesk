<?php
// Verificar a sessão
session_start();
if (!isset($_SESSION['usuario_id'])) {
    die("Acesso não autorizado");
}

// Verificar se foi fornecido um ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de anexo inválido");
}

// Conectar ao banco de dados
try {
    require_once '../../../ligacao_base_dados.php';
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados");
}

$anexo_id = (int)$_GET['id'];

// Buscar informações do anexo
$stmt = $pdo->prepare("SELECT a.* FROM ticket_anexos a WHERE a.id = ?");
$stmt->execute([$anexo_id]);
$anexo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anexo) {
    die("Anexo não encontrado na base de dados (ID: {$anexo_id})");
}

// Verificar permissão do usuário para acessar este anexo
$user_id = $_SESSION['usuario_id'];
$user_type = $_SESSION['usuario_tipo'];

// Permitir acesso para técnicos, admin ou o próprio usuário que criou o ticket
if ($user_type != 'tecnico' && $user_type != 'admin') {
    // Verificar se o usuário é o criador do ticket
    $stmt = $pdo->prepare("SELECT id_usuario FROM tickets WHERE id = ?");
    $stmt->execute([$anexo['ticket_id']]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ticket || $ticket['id_usuario'] != $user_id) {
        die("Você não tem permissão para acessar este arquivo");
    }
}

// Definir caminhos possíveis para tentar encontrar o arquivo
$root_path = realpath($_SERVER['DOCUMENT_ROOT']);
$wamp_path = realpath(dirname(dirname(dirname(__DIR__))));
$arquivo_path = $anexo['caminho'];

// Verificar se o caminho já inclui 'uploads/tickets/'
$path_has_uploads = (strpos($arquivo_path, 'uploads/tickets/') === 0);
$file_path = $path_has_uploads ? $arquivo_path : 'uploads/tickets/' . $arquivo_path;
$file_name = basename($arquivo_path);

$possible_paths = [
    // Caminho direto do banco de dados (como está)
    $arquivo_path,
    
    // Caminho ajustado (se necessário)
    $file_path,
    
    // Caminho relativo da raiz do projeto
    dirname(dirname(dirname(__DIR__))) . '/' . $arquivo_path,
    
    // Caminhos absolutos
    $wamp_path . '/' . $arquivo_path,
    $_SERVER['DOCUMENT_ROOT'] . '/' . $arquivo_path,
    
    // Caminhos usando apenas o nome do arquivo
    dirname(dirname(dirname(__DIR__))) . '/uploads/tickets/' . $file_name,
    $wamp_path . '/uploads/tickets/' . $file_name,
    $_SERVER['DOCUMENT_ROOT'] . '/uploads/tickets/' . $file_name,
    
    // Versão mais simples (arquivo na pasta uploads)
    'uploads/tickets/' . $file_name,
    '../../../uploads/tickets/' . $file_name
];

// Encontrar o primeiro caminho válido
$valid_path = null;
foreach ($possible_paths as $path) {
    if (file_exists($path) && is_readable($path)) {
        $valid_path = $path;
        // Descomentar a linha abaixo para debug
        // die("Arquivo encontrado em: " . $valid_path);
        break;
    }
}

if (!$valid_path) {
    // Último recurso - procurar em toda a pasta uploads e suas subpastas
    $search_dirs = [
        dirname(dirname(dirname(__DIR__))) . '/uploads',
        $wamp_path . '/uploads',
        $_SERVER['DOCUMENT_ROOT'] . '/uploads',
        'uploads'
    ];
    
    foreach ($search_dirs as $base_dir) {
        if (file_exists($base_dir)) {
            $valid_path = findFileRecursive($base_dir, $file_name);
            if ($valid_path) {
                // Descomentar a linha abaixo para debug
                // die("Arquivo encontrado (busca recursiva) em: " . $valid_path);
                break;
            }
        }
    }
}

if (!$valid_path) {
    $error_msg = "<h3>Arquivo não encontrado</h3>";
    $error_msg .= "<p>O arquivo '{$anexo['nome_arquivo']}' não foi encontrado no servidor.</p>";
    $error_msg .= "<p>Caminho na base de dados: {$anexo['caminho']}</p>";
    $error_msg .= "<p>Nome do arquivo: {$file_name}</p>";
    $error_msg .= "<p>Informações do servidor:</p>";
    $error_msg .= "<ul>";
    $error_msg .= "<li>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
    $error_msg .= "<li>Script atual: " . __FILE__ . "</li>";
    $error_msg .= "<li>Diretório atual: " . getcwd() . "</li>";
    $error_msg .= "</ul>";
    $error_msg .= "<p>Caminhos verificados:</p><ul>";
    
    foreach ($possible_paths as $path) {
        $status = file_exists($path) ? (is_readable($path) ? "Existe e é legível" : "Existe mas não é legível") : "Não existe";
        $error_msg .= "<li>" . htmlspecialchars($path) . " - " . $status . "</li>";
    }
    
    $error_msg .= "</ul>";
    $error_msg .= "<p>Se o arquivo estiver no servidor, verifique os caminhos e permissões ou contate o administrador.</p>";
    die($error_msg);
}

// Agora temos um caminho válido, então podemos servir o arquivo
$file_size = filesize($valid_path);
$file_name = $anexo['nome_arquivo'];

// Definir o tipo de conteúdo com base na extensão
$extension = pathinfo($file_name, PATHINFO_EXTENSION);
$content_type = 'application/octet-stream'; // Padrão para qualquer arquivo

// Tipos MIME comuns
$mime_types = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'zip' => 'application/zip',
    'txt' => 'text/plain'
];

if (isset($mime_types[strtolower($extension)])) {
    $content_type = $mime_types[strtolower($extension)];
}

// Definir headers para download
header('Content-Description: File Transfer');
header('Content-Type: ' . $content_type);
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $file_size);

// Limpar buffer de saída
if (ob_get_level()) {
    ob_end_clean();
}

// Enviar arquivo
readfile($valid_path);
exit;

// Função para buscar arquivo recursivamente
function findFileRecursive($dir, $filename) {
    if (!file_exists($dir) || !is_dir($dir)) {
        return null;
    }
    
    try {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            
            $path = $dir . '/' . $file;
            
            // Se encontrou o arquivo, retorna o caminho
            if (is_file($path) && $file == $filename) {
                return $path;
            }
            
            // Se for diretório, busca recursivamente
            if (is_dir($path)) {
                $result = findFileRecursive($path, $filename);
                if ($result) {
                    return $result;
                }
            }
        }
    } catch (Exception $e) {
        // Ignora erros de permissão
    }
    
    return null;
} 