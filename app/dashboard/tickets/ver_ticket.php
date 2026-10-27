<?php
/**
 * Página de visualização de ticket específico
 */

// Verificar a sessão
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Incluir as funções de tickets
require_once 'funcoes/tickets_funcoes.php';

// Incluir as funções de manutenção
include_once "../../manutencao/funcoes/manutencao_funcoes.php";

// Conectar ao banco de dados
try {
    require_once '../../ligacao_base_dados.php';
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Obter informações do usuário atual
// Identificar qual formato de sessão está sendo usado
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : (isset($_SESSION['userId']) ? $_SESSION['userId'] : '');
$usuario_nome = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : (isset($_SESSION['userName']) ? $_SESSION['userName'] : 'Usuário');
$usuario_tipo = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : (isset($_SESSION['userType']) ? $_SESSION['userType'] : '');

// Guardar em formato padrão para uso consistente
$_SESSION['usuario_id'] = $usuario_id;
$_SESSION['usuario_nome'] = $usuario_nome;
$_SESSION['usuario_tipo'] = $usuario_tipo;

// Buscar foto de perfil
try {
    $stmt = $pdo->prepare("SELECT foto_perfil FROM suporte_tecnico WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $foto_result = $stmt->fetch();
    $foto_perfil = ($foto_result && $foto_result['foto_perfil']) ? $foto_result['foto_perfil'] : 'default.jpg';
} catch (Exception $e) {
    $foto_perfil = 'default.jpg';
}

$usuario = [
    'id' => $usuario_id,
    'nome' => $usuario_nome,
    'tipo' => $usuario_tipo,
    'foto_perfil' => $foto_perfil
];

// Verificar se o ID do ticket foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirecionar para a página de tickets se o ID não for válido
    header('Location: dashboard.php');
    exit;
}

$ticket_id = (int)$_GET['id'];

// Obter informações do ticket
$stmt = $pdo->prepare("SELECT t.*, 
                      COALESCE(s.sala, t.sala) as sala,
                      COALESCE(ce.nome_completo, st.nome_completo, t.solicitante) as solicitante_nome
                      FROM tickets t
                      LEFT JOIN salas s ON t.sala = s.id_sala OR t.sala = s.sala
                      LEFT JOIN comunidade_escolar ce ON t.solicitante = ce.numero_processo OR (ce.id = t.solicitante AND t.solicitante REGEXP '^[0-9]+$')
                      LEFT JOIN suporte_tecnico st ON t.solicitante = st.numero_processo OR (st.id = t.solicitante AND t.solicitante REGEXP '^[0-9]+$')
                      WHERE t.id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se o ticket existe
if (!$ticket) {
    $_SESSION['erro'] = "Ticket não encontrado.";
    header('Location: dashboard.php');
    exit;
}

// Verificar permissão para visualizar este ticket
if (!verificarPermissaoTicket($pdo, $ticket_id, $usuario)) {
    $_SESSION['erro'] = "Você não tem permissão para visualizar este ticket.";
    header('Location: dashboard.php');
    exit;
}

// Obter comentários e anexos do ticket
$comentarios = obterComentariosTicket($pdo, $ticket_id);
$anexos = obterAnexosTicket($pdo, $ticket_id);

// Obtém os componentes associados ao ticket
$componentes = obterComponentesTicket($ticket_id);

// Processar ações (adicionar comentário, anexo, mudar status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['acao']) ? $_POST['acao'] : '';
    
    if ($acao === 'comentario') {
        // Processar novo comentário
        $conteudo = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
        
        if (!empty($conteudo)) {
            if (adicionarComentario($pdo, $ticket_id, $usuario_id, $conteudo)) {
                // Definir variável de sucesso
                $sucesso = "comentario";
                
                // Recarregar dados do ticket com a mesma query original
                $stmt = $pdo->prepare("SELECT t.*, 
                      COALESCE(s.sala, t.sala) as sala,
                      COALESCE(ce.nome_completo, st.nome_completo, t.solicitante) as solicitante_nome
                      FROM tickets t
                      LEFT JOIN salas s ON t.sala = s.id_sala OR t.sala = s.sala
                      LEFT JOIN comunidade_escolar ce ON t.solicitante = ce.numero_processo OR (ce.id = t.solicitante AND t.solicitante REGEXP '^[0-9]+$')
                      LEFT JOIN suporte_tecnico st ON t.solicitante = st.numero_processo OR (st.id = t.solicitante AND t.solicitante REGEXP '^[0-9]+$')
                      WHERE t.id = ?");
                $stmt->execute([$ticket_id]);
                $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Recarregar comentários e anexos
                $comentarios = obterComentariosTicket($pdo, $ticket_id);
                $anexos = obterAnexosTicket($pdo, $ticket_id);
            } else {
                $erro = "Erro ao adicionar comentário.";
            }
        } else {
            $erro = "O comentário não pode estar vazio.";
        }
    } elseif ($acao === 'anexo') {
        // Processar upload de anexo
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES['arquivo'];
            $nome_arquivo = $arquivo['name'];
            $tamanho = $arquivo['size'];
            $tipo = $arquivo['type'];
            $tmp_path = $arquivo['tmp_name'];
            
            // Verificar se o tipo e tamanho são permitidos
            if (!verificarTipoArquivo($tipo)) {
                $erro = "Tipo de arquivo não permitido.";
            } elseif (!verificarTamanhoArquivo($tamanho)) {
                $erro = "O arquivo é muito grande. Tamanho máximo: 5MB.";
            } else {
                // Criar diretório de uploads se não existir
                $upload_dir = '../../uploads/tickets/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Gerar nome único para o arquivo
                $nome_unico = time() . '_' . $ticket_id . '_' . $usuario_id . '_' . $nome_arquivo;
                $caminho_completo = $upload_dir . $nome_unico;
                
                // Mover o arquivo para o diretório de uploads
                if (move_uploaded_file($tmp_path, $caminho_completo)) {
                    // Adicionar anexo ao banco de dados
                    if (adicionarAnexo($pdo, $ticket_id, $usuario_id, $nome_arquivo, $tamanho, $tipo, $nome_unico)) {
                        // Adicionar comentário do sistema sobre o novo anexo
                        $comentario_sistema = "Anexo adicionado: {$nome_arquivo}";
                        adicionarComentario($pdo, $ticket_id, $usuario_id, $comentario_sistema, true);
                        
                        // Definir variável de sucesso
                        $sucesso = "anexo";
                        
                        // Recarregar dados do ticket com a mesma query original
                        $stmt = $pdo->prepare("SELECT t.*, 
                              COALESCE(s.sala, t.sala) as sala,
                              COALESCE(ce.nome_completo, st.nome_completo, t.solicitante) as solicitante_nome
                              FROM tickets t
                              LEFT JOIN salas s ON t.sala = s.id_sala OR t.sala = s.sala
                              LEFT JOIN comunidade_escolar ce ON t.solicitante = ce.numero_processo OR (ce.id = t.solicitante AND t.solicitante REGEXP '^[0-9]+$')
                              LEFT JOIN suporte_tecnico st ON t.solicitante = st.numero_processo OR (st.id = t.solicitante AND t.solicitante REGEXP '^[0-9]+$')
                              WHERE t.id = ?");
                        $stmt->execute([$ticket_id]);
                        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        // Recarregar comentários e anexos
                        $comentarios = obterComentariosTicket($pdo, $ticket_id);
                        $anexos = obterAnexosTicket($pdo, $ticket_id);
                    } else {
                        // Remover o arquivo se não foi possível registrar no banco
                        unlink($caminho_completo);
                        $erro = "Erro ao registrar o anexo.";
                    }
                } else {
                    $erro = "Erro ao fazer upload do arquivo.";
                }
            }
        } else {
            $erro = "Nenhum arquivo enviado ou erro no upload.";
        }
    }
}

// Atualizar informações após processamento, se necessário
if (isset($_GET['sucesso'])) {
    $ticket = obterTicketPorId($pdo, $ticket_id);
    $comentarios = obterComentariosTicket($pdo, $ticket_id);
    $anexos = obterAnexosTicket($pdo, $ticket_id);
}

// Incluir o cabeçalho
$titulo_pagina = "Ticket #" . $ticket_id;
include_once '../../includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket <?php echo $ticket_id; ?> - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/variables.css">
    <link rel="stylesheet" href="../../assets/css/custom.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        /* Estilo de fundo gradiente como na página de login */
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* Estilo para sidebar e conteúdo principal */
        .sidebar {
            width: 300px;
            min-height: 100vh;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 999;
            background: linear-gradient(150deg,rgb(227, 238, 252),rgb(235, 241, 250));
            border-right: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            padding: 0;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .sidebar::before {
            display: none;
        }

        .escola-logo {
            position: relative;
            z-index: 20;
            margin-top: auto;
            padding: 25px 0;
            text-align: center;
        }

        .escola-logo img {
            max-width: 150px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .sidebar-header, .sidebar .user-info, .sidebar .nav {
            position: relative;
            z-index: 10;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .sidebar-header h5 {
            font-weight: 700;
            color: #1a3c5e;
        }

        .sidebar .user-info {
            padding: 20px;
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            border-radius: 12px;
            color: white;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .sidebar .user-info:hover {
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        .sidebar .user-info .user-details h6 {
            color: white;
            font-weight: 700;
        }

        .sidebar .user-info .user-details small {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
        }

        .sidebar .user-info .avatar img {
            border: 2px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar .nav {
            padding: 0 15px;
        }

        .sidebar .nav-item {
            margin-bottom: 8px;
        }

        .sidebar .nav-link {
            padding: 10px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
            color: #1a3c5e;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            color: #0d6efd;
            width: 20px;
            text-align: center;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #ffffff;
            color: #0d6efd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .sidebar .nav-link.active i {
            color: #0d6efd;
        }

        /* Botão de sidebar toggle */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1000;
            background-color: rgba(75, 134, 180, 0.15);
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
            width: 45px;
            height: 45px;
            text-align: center;
            line-height: 45px;
            border: 2px solid #4b86b4;
            transition: all 0.3s ease;
            padding: 0;
            cursor: pointer;
        }

        .sidebar-toggle:hover {
            background-color: rgba(75, 134, 180, 0.25);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        .sidebar-toggle i {
            font-size: 1.3rem;
            color: #2a4d69;
            line-height: 42px;
            padding-left: 7.8px;
        }

        .sidebar-toggle:active {
            transform: translateY(1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar.active + .main-content .sidebar-toggle {
            display: none;
        }

        /* Conteúdo principal */
        .main-content {
            margin-left: 300px;
            width: calc(100% - 300px);
            transition: all 0.3s ease;
        }
        
        /* Responsividade */
        @media (max-width: 992px) {
            .sidebar {
                width: 250px;
            }
            
            .main-content {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                left: -300px;
                transition: all 0.3s ease;
                width: 280px;
                z-index: 1001;
            }
            
            .sidebar.active {
                left: 0;
                box-shadow: 0 0 25px rgba(0, 0, 0, 0.3);
            }
            
            body.menu-active .main-content {
                transform: translateX(280px);
                opacity: 0.2;
                pointer-events: none;
                position: relative;
                z-index: 1;
            }
            
            /* Hide the back button when menu is active */
            body.menu-active .back-to-tickets {
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }
            
            /* Ensure the overlay has a higher z-index than any content */
            .sidebar-overlay {
                z-index: 1000 !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                padding-top: 60px;
                transition: all 0.3s ease;
            }
            
            .sidebar-toggle {
                display: block !important;
            }
        }
        
        /* Cabeçalho do Ticket */
        .ticket-header {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            padding: 1.8rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .ticket-header:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            transform: translateY(-3px);
        }
        
        .ticket-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .ticket-title i {
            font-size: 2.2rem;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .ticket-title i:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }
        
        .ticket-status {
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 140px;
        }
        
        .ticket-status:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
        }
        
        .status-aberto {
            background: linear-gradient(to right, #ffc107, #e0a800);
            border: 2px solid rgba(224, 168, 0, 0.2);
        }
        
        .status-aberto::before {
            content: "";
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #fff7db;
            border-radius: 50%;
            margin-right: 8px;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
            animation: pulse 1.5s infinite;
        }
        
        .status-em-andamento {
            background: linear-gradient(to right, #36b9cc, #1a96b8);
            border: 2px solid rgba(26, 150, 184, 0.2);
        }
        
        .status-em-andamento::before {
            content: "";
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #dbf5ff;
            border-radius: 50%;
            margin-right: 8px;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
            animation: pulse 1.5s infinite;
        }
        
        .status-resolvido, .status-concluido {
            background: linear-gradient(to right, #28a745, #20c997);
            border: 2px solid rgba(32, 201, 151, 0.2);
        }
        
        .status-resolvido::before, .status-concluido::before {
            content: "";
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #dbffec;
            border-radius: 50%;
            margin-right: 8px;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.3);
        }
        
        .status-fechado {
            background: linear-gradient(to right, #6c757d, #495057);
            border: 2px solid rgba(73, 80, 87, 0.2);
        }
        
        .status-cancelado {
            background: linear-gradient(to right, #dc3545, #c82333);
            border: 2px solid rgba(200, 35, 51, 0.2);
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
            }
            70% {
                box-shadow: 0 0 0 5px rgba(255, 255, 255, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }
        
        @media (max-width: 768px) {
            .ticket-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 1.5rem;
            }
            
            .ticket-title {
                margin-bottom: 0.5rem;
                width: 100%;
            }
            
            .ticket-status {
                display: none; /* Esconde o status em dispositivos móveis */
            }
            
            .ticket-title i {
                font-size: 1.8rem;
                padding: 10px;
            }
            
            .ticket-content.neon-border-aberto,
            .ticket-content.neon-border-em-andamento,
            .ticket-content.neon-border-resolvido,
            .ticket-content.neon-border-concluido,
            .ticket-content.neon-border-fechado,
            .ticket-content.neon-border-cancelado {
                padding: 1.5rem;
                border-width: 3px;
                animation: glowEffect 2s infinite alternate;
            }
            
            @keyframes glowEffect {
                0% {
                    box-shadow: 0 0 10px var(--glow-color), 0 0 20px rgba(var(--glow-color-rgb), 0.5);
                }
                100% {
                    box-shadow: 0 0 15px var(--glow-color), 0 0 30px rgba(var(--glow-color-rgb), 0.7);
                }
            }
            
            .ticket-content.neon-border-aberto {
                border-color: #ffc107;
                --glow-color: #ffc107;
                --glow-color-rgb: 255, 193, 7;
            }
            
            .ticket-content.neon-border-em-andamento {
                border-color: #36b9cc;
                --glow-color: #36b9cc;
                --glow-color-rgb: 54, 185, 204;
            }
            
            .ticket-content.neon-border-resolvido,
            .ticket-content.neon-border-concluido {
                border-color: #28a745;
                --glow-color: #28a745;
                --glow-color-rgb: 40, 167, 69;
            }
            
            .ticket-content.neon-border-fechado {
                border-color: #6c757d;
                --glow-color: #6c757d;
                --glow-color-rgb: 108, 117, 125;
            }
            
            .ticket-content.neon-border-cancelado {
                border-color: #dc3545;
                --glow-color: #dc3545;
                --glow-color-rgb: 220, 53, 69;
            }
        }
        
        @media (max-width: 576px) {
            .ticket-header {
                padding: 1.2rem;
            }
            
            .ticket-title {
                gap: 10px;
            }
            
            .ticket-title i {
                font-size: 1.5rem;
                padding: 8px;
            }
        }
        
        /* Botão de voltar para tickets */
        .back-to-tickets {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 1500;
            display: inline-flex;
            align-items: center;
            padding: 0.7rem 1.2rem;
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            border-radius: 10px;
            text-decoration: none;
            border: none;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease, opacity 0.3s ease, visibility 0.3s ease;
            overflow: hidden;
            opacity: 1;
            visibility: visible;
        }
        
        .back-to-tickets.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .back-to-tickets::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #3a6186, #4ca1af);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 10px;
        }

        .back-to-tickets:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .back-to-tickets:hover::before {
            opacity: 1;
        }

        .back-to-tickets .back-icon {
            margin-right: 10px;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .back-to-tickets:hover .back-icon {
            transform: translateX(-4px);
        }

        .back-to-tickets .back-text {
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .back-to-tickets {
                padding: 0.6rem 1rem;
            }
        }
        
        @media (max-width: 375px) {
            .back-to-tickets .back-text {
                display: none;
            }
            
            .back-to-tickets {
                padding: 0.5rem;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .back-to-tickets .back-icon {
                margin-right: 0;
            }
        }

        .ticket-content {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        
        .ticket-content:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .ticket-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .ticket-section:hover {
            transform: translateY(-2px);
        }
        
        .ticket-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .ticket-section h5 {
            font-weight: 700;
            color: #2a4d69;
            margin-bottom: 1.2rem;
            font-size: 1.25rem;
            letter-spacing: 0.5px;
        }
        
        .ticket-section h5 i {
            color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.1);
            padding: 8px;
            border-radius: 8px;
            margin-right: 10px;
        }
        
        .comment-container {
            margin-bottom: 1.5rem;
        }
        
        .comment {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 1.2rem;
            margin-bottom: 1.2rem;
            border-left: 4px solid #007bff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .comment:hover {
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .comment-system {
            background-color: #e9f5ff;
            border-left: 4px solid #17a2b8;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            align-items: center;
        }
        
        .comment-author {
            font-weight: 700;
            color: #0d6efd;
            display: flex;
            align-items: center;
            font-size: 1.05rem;
        }
        
        .comment-author i {
            background-color: rgba(13, 110, 253, 0.1);
            padding: 8px;
            border-radius: 50%;
            margin-right: 8px;
            color: #0d6efd;
            font-size: 0.9rem;
        }
        
        .comment-date {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .comment-content {
            white-space: pre-line;
            line-height: 1.6;
            color: #495057;
        }
        
        .add-comment-form {
            background: linear-gradient(to bottom right, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .add-comment-form:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .add-comment-form h5 {
            color: #2a4d69;
            font-weight: 700;
            margin-bottom: 1.2rem;
            font-size: 1.2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #2a4d69;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .form-control {
            border: 1px solid #e1ecff;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            padding: 0.7rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background-color: #ffffff;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.2);
            border-color: #a9c6ff;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #0d6efd, #0b5ed7);
            border: none;
            padding: 0.8rem 1.8rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #0b5ed7, #094bac);
            box-shadow: 0 6px 15px rgba(13, 110, 253, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: linear-gradient(to right, #6c757d, #5a6268);
            border: none;
            padding: 0.8rem 1.8rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(108, 117, 125, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(to right, #5a6268, #495057);
            box-shadow: 0 6px 15px rgba(108, 117, 125, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: linear-gradient(to right, #28a745, #20c997);
            border: none;
            padding: 0.8rem 1.8rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-success:hover {
            background: linear-gradient(to right, #20c997, #198754);
            box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: linear-gradient(to right, #dc3545, #c82333);
            border: none;
            padding: 0.8rem 1.8rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-danger:hover {
            background: linear-gradient(to right, #c82333, #bd2130);
            box-shadow: 0 6px 15px rgba(220, 53, 69, 0.4);
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .list-group {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .list-group-item {
            padding: 1rem 1.25rem;
            border-color: rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .list-group-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table th {
            font-weight: 600;
            padding: 0.85rem 0.75rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
            font-size: 0.9rem;
            vertical-align: middle;
            color: #2a4d69;
        }
        
        .table td {
            padding: 0.85rem 0.75rem;
            vertical-align: middle;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
            color: #212529;
        }
        
        .p-3.bg-light.rounded {
            background-color: #f8f9fa !important;
            border-radius: 10px !important;
            padding: 1.25rem !important;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.03);
        }
        
        .badge {
            padding: 0.5em 0.8em;
            font-weight: 600;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .alert {
            border-radius: 10px;
            padding: 1rem 1.5rem;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            animation: alertFadeIn 0.5s ease forwards;
        }
        
        @keyframes alertFadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background: linear-gradient(to right, #d1e7dd, #c3e6cb);
            color: #0f5132;
        }
        
        .alert-danger {
            background: linear-gradient(to right, #f8d7da, #f5c2c7);
            color: #842029;
        }
    </style>
</head>
<body>
    <!-- Botão de voltar para tickets -->
    <a href="dashboard.php" class="back-to-tickets">
        <i class="bi bi-arrow-left back-icon"></i>
        <span class="back-text">Voltar para Tickets</span>
    </a>

    <!-- Botão toggle para sidebar em dispositivos móveis -->
    <button class="sidebar-toggle btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar e conteúdo principal -->
    <div class="d-flex">
        <!-- Incluir o sidebar -->
        <?php renderSidebar('dashboard', $usuario_tipo, $usuario_nome, $foto_perfil); ?>
        
        <!-- Conteúdo principal -->
        <main class="main-content">
            <div class="container-fluid p-4">
                <?php if (isset($erro)): ?>
                <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>
                
                <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success">
                    <?php 
                    $sucesso = $_GET['sucesso'];
                    if ($sucesso === 'comentario') {
                        echo "Comentário adicionado com sucesso.";
                    } elseif ($sucesso === 'anexo') {
                        echo "Anexo adicionado com sucesso.";
                    }
                    ?>
                </div>
                <?php endif; ?>
                
                <!-- Cabeçalho do Ticket -->
                <div class="ticket-header">
                    <div class="ticket-title">
                        <i class="bi bi-ticket-perforated"></i>
                        <div>
                            <h4>Ticket <?php echo $ticket_id; ?></h4>
                            <p class="mb-0 mt-2"><?php echo htmlspecialchars($ticket['assunto']); ?></p>
                        </div>
                    </div>
                    <div class="ticket-status status-<?php echo str_replace('_', '-', $ticket['status']); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                    </div>
                </div>
                
                <!-- Conteúdo do Ticket -->
                <div class="ticket-content">
                    <!-- Detalhes do Ticket -->
                    <div class="ticket-section">
                        <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Detalhes do Ticket</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Sala:</strong> <?php echo htmlspecialchars($ticket['sala']); ?></p>
                                <p><strong>Utilizador:</strong> <?php echo $ticket['solicitante_nome']; ?></p>
                                <p><strong>Prioridade:</strong> 
                                    <span style="font-weight: 600; <?php 
                                        echo $ticket['prioridade'] == '1' ? 'color: #0d6efd;' : 
                                            ($ticket['prioridade'] == '2' ? 'color: #ffc107;' : 'color: #dc3545;'); 
                                    ?>">
                                        <?php 
                                        echo $ticket['prioridade'] == '1' ? 'Baixa' : 
                                            ($ticket['prioridade'] == '2' ? 'Média' : 'Alta'); 
                                        ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Data de Criação:</strong> <?php echo date('d/m/Y H:i', strtotime($ticket['criado_em'])); ?></p>
                                <p><strong>Última Atualização:</strong> <?php echo date('d/m/Y H:i', strtotime($ticket['ultima_atualizacao'])); ?></p>
                                <p><strong>Técnico Responsável:</strong> 
                                    <?php 
                                    if (empty($ticket['id_tecnico'])) {
                                        echo '<span class="text-muted">Não atribuído</span>';
                                    } else {
                                        // Buscar nome do técnico
                                        try {
                                            $stmt = $pdo->prepare("SELECT nome_completo FROM suporte_tecnico WHERE id = ?");
                                            $stmt->execute([$ticket['id_tecnico']]);
                                            $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
                                            echo htmlspecialchars($tecnico['nome_completo']);
                                        } catch (Exception $e) {
                                            echo '<span class="text-muted">Erro ao carregar técnico</span>';
                                        }
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Descrição do Ticket -->
                    <div class="ticket-section">
                        <h5 class="mb-3"><i class="bi bi-file-text me-2"></i>Descrição</h5>
                        <div class="p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($ticket['descricao'])); ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($ticket['mensagem_erro'])): ?>
                    <!-- Mensagem de Erro -->
                    <div class="ticket-section">
                        <h5 class="mb-3"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Mensagem de Erro</h5>
                        <div class="p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($ticket['mensagem_erro'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($ticket['observacoes_adicionais'])): ?>
                    <!-- Observações Adicionais -->
                    <div class="ticket-section">
                        <h5 class="mb-3"><i class="bi bi-pencil me-2"></i>Observações Adicionais</h5>
                        <div class="p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($ticket['observacoes_adicionais'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Comentários -->
                    <div class="ticket-section">
                        <h5 class="mb-3"><i class="bi bi-chat-dots me-2"></i>Comentários (<?php echo count($comentarios); ?>)</h5>
                        
                        <?php if (empty($comentarios)): ?>
                            <div class="p-4 text-center bg-light rounded">
                                <i class="bi bi-chat-square text-muted" style="font-size: 2rem;"></i>
                                <p class="mt-3 mb-0 text-muted">Nenhum comentário disponível para este ticket.</p>
                                <small class="text-muted">Utilize o formulário abaixo para adicionar o primeiro comentário.</small>
                            </div>
                        <?php else: ?>
                            <div class="comment-container">
                                <?php foreach ($comentarios as $comentario): ?>
                                    <div class="comment">
                                        <div class="comment-header">
                                            <span class="comment-author">
                                                <i class="bi bi-person me-1"></i> 
                                                <?php 
                                                echo htmlspecialchars($comentario['nome_autor'] ?? 'Técnico'); 
                                                ?>
                                            </span>
                                            <span class="comment-date">
                                                <?php echo date('d/m/Y H:i', strtotime($comentario['data_comentario'])); ?>
                                            </span>
                                        </div>
                                        <div class="comment-content mt-2">
                                            <?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Formulário para adicionar comentário -->
                        <div class="add-comment-form mt-4">
                            <h5 class="mb-3">Adicionar Comentário</h5>
                            <form method="POST" action="ver_ticket.php?id=<?php echo $ticket_id; ?>">
                                <input type="hidden" name="acao" value="comentario">
                                
                                <div class="mb-3">
                                    <label for="comentario" class="form-label">Escreve aqui</label>
                                    <textarea class="form-control" id="comentario" name="comentario" rows="3" required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i>Enviar Comentário
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Equipamentos Associados -->
                    <div class="ticket-section">
                        <h5 class="mb-3"><i class="bi bi-pc-display me-2"></i>Equipamentos</h5>
                        <?php
                        // Obter equipamentos associados ao ticket
                        $stmt = $pdo->prepare("SELECT * FROM ticket_componentes WHERE ticket_id = ?");
                        $stmt->execute([$ticket_id]);
                        $equipamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($equipamentos)) {
                            echo '<p class="text-muted">Nenhum equipamento associado a este ticket.</p>';
                        } else {
                            echo '<ul class="list-group">';
                            foreach ($equipamentos as $equip) {
                                $equipamento_tipo = strtolower($equip['equipamento']);
                                $ref_id = $equip['ref_equipamento'];
                                
                                // Referência a ser exibida (padrão é o valor original)
                                $ref_texto = $ref_id;
                                
                                // Verificar se a referência é um ID numérico
                                if (is_numeric($ref_id) || strpos($ref_id, 'COMP_') === 0) {
                                    // Mapeamento de tipos de equipamento para suas tabelas e campos
                                    $mapeamento = [
                                        'computador' => ['tabela' => 'computadores', 'campo_id' => 'COMP_ID', 'campo_ref' => 'COMP_REF'],
                                        'rato' => ['tabela' => 'ratos', 'campo_id' => 'RATOS_ID', 'campo_ref' => 'RATOS_REF'],
                                        'teclado' => ['tabela' => 'teclados', 'campo_id' => 'TECLADO_ID', 'campo_ref' => 'TECLADO_REF'],
                                        'monitor' => ['tabela' => 'monitor', 'campo_id' => 'MONITOR_ID', 'campo_ref' => 'MONITOR_REF'],
                                        'cooler' => ['tabela' => 'cooler', 'campo_id' => 'COO_ID', 'campo_ref' => 'COO_REF'],
                                        'disco' => ['tabela' => 'disco', 'campo_id' => 'DISCO_ID', 'campo_ref' => 'DISCO_REF'],
                                        'fonte' => ['tabela' => 'fonte', 'campo_id' => 'FONTE_ID', 'campo_ref' => 'FONTE_REF'],
                                        'memoriaram' => ['tabela' => 'memoriaram', 'campo_id' => 'MR_ID', 'campo_ref' => 'MR_REF'],
                                        'placaderede' => ['tabela' => 'placaderede', 'campo_id' => 'PR_ID', 'campo_ref' => 'PR_REF'],
                                        'placagrafica' => ['tabela' => 'placagrafica', 'campo_id' => 'PG_ID', 'campo_ref' => 'PG_REF'],
                                        'placamae' => ['tabela' => 'placamae', 'campo_id' => 'PM_ID', 'campo_ref' => 'PM_REF'],
                                        'processador' => ['tabela' => 'processador', 'campo_id' => 'PROC_ID', 'campo_ref' => 'PROC_REF'],
                                        'caixa' => ['tabela' => 'caixa', 'campo_id' => 'CAIXA_ID', 'campo_ref' => 'CAIXA_REF'],
                                        'drivedvd' => ['tabela' => 'drivedvd', 'campo_id' => 'DRVD_ID', 'campo_ref' => 'DRVD_REF'],
                                        'ventoinha' => ['tabela' => 'ventoinha', 'campo_id' => 'VENT_ID', 'campo_ref' => 'VENT_REF']
                                    ];
                                    
                                    // Caso especial para computadores
                                    if ($equipamento_tipo == 'computador') {
                                        try {
                                            $query = "SELECT COMP_REF FROM computadores WHERE COMP_ID = ?";
                                            $stmt_ref = $pdo->prepare($query);
                                            $stmt_ref->execute([$ref_id]);
                                            $result = $stmt_ref->fetch(PDO::FETCH_ASSOC);
                                            
                                            if ($result && !empty($result['COMP_REF'])) {
                                                $ref_texto = $result['COMP_REF'];
                                            }
                                        } catch (Exception $e) {
                                            // Em caso de erro, manteremos o ID original
                                            error_log("Erro ao buscar referência de computador: " . $e->getMessage());
                                        }
                                    } else {
                                        // Para outros tipos de componentes
                                        // Percorrer o mapeamento e procurar uma correspondência
                                        foreach ($mapeamento as $tipo => $info) {
                                            if (strpos($equipamento_tipo, $tipo) !== false) {
                                                try {
                                                    // Verificar se a tabela existe
                                                    $checkTable = $pdo->query("SHOW TABLES LIKE '{$info['tabela']}'");
                                                    if ($checkTable->rowCount() > 0) {
                                                        // Verificar se a coluna de referência existe
                                                        $checkColumn = $pdo->query("SHOW COLUMNS FROM {$info['tabela']} LIKE '{$info['campo_ref']}'");
                                                        if ($checkColumn->rowCount() > 0) {
                                                            // Fazer a consulta usando o campo ID e buscando o campo REF
                                                            $query = "SELECT {$info['campo_ref']} FROM {$info['tabela']} WHERE {$info['campo_id']} = ?";
                                                            $stmt_ref = $pdo->prepare($query);
                                                            $stmt_ref->execute([$ref_id]);
                                                            $result = $stmt_ref->fetch(PDO::FETCH_ASSOC);
                                                            
                                                            if ($result && !empty($result[$info['campo_ref']])) {
                                                                $ref_texto = $result[$info['campo_ref']];
                                                                break;
                                                            }
                                                        }
                                                    }
                                                } catch (Exception $e) {
                                                    // Ignorar erros, manter o valor original
                                                    continue;
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                                echo '<div>';
                                echo '<strong>' . htmlspecialchars($equip['equipamento']) . '</strong>';
                                echo '<span class="d-block small text-muted">Ref: ' . htmlspecialchars($ref_texto) . '</span>';
                                echo '</div>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        }
                        ?>
                    </div>
                    
                    <!-- Anexos -->
                    <div class="ticket-section">
                        <h5 class="mb-3"><i class="bi bi-paperclip me-2"></i>Anexos (<?php echo count($anexos); ?>)</h5>
                        
                        <?php if (empty($anexos)): ?>
                            <p class="text-muted">Nenhum anexo disponível.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nome</th>
                                            <th>Tipo</th>
                                            <th>Tamanho</th>
                                            <th>Data</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($anexos as $anexo): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($anexo['nome_arquivo']); ?></td>
                                                <td><?php echo htmlspecialchars($anexo['tipo_arquivo']); ?></td>
                                                <td><?php echo formatarTamanhoArquivo($anexo['tamanho']); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($anexo['data_upload'])); ?></td>
                                                <td>
                                                    <a href="funcoes/download_anexo.php?id=<?php echo $anexo['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-download"></i> Download
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Ações do Ticket -->
                    <div class="d-flex justify-content-end">
                        <?php if ($usuario_tipo == 'tecnico' || $usuario_tipo == 'admin'): ?>
                            <?php if ($ticket['status'] != 'resolvido' && $ticket['status'] != 'fechado'): ?>
                                <a href="funcoes/cancelar_ticket.php?id=<?php echo $ticket_id; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja fechar este ticket?');">
                                    <i class="bi bi-x-circle me-1"></i>Fechar Ticket
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função que permite o toggle do sidebar
        window.toggleSidebar = function() {
            const sidebar = document.querySelector('.sidebar');
            const toggleButton = document.querySelector('.sidebar-toggle');
            const body = document.body;
            
            sidebar.classList.toggle('active');
            
            // Se o sidebar estiver ativo, esconde o botão de toggle e mostra o overlay
            if (sidebar.classList.contains('active')) {
                // Esconde imediatamente o botão de toggle
                toggleButton.style.display = 'none';
                
                // Adiciona classe ao body para controlar o conteúdo principal
                body.classList.add('menu-active');
                
                // Cria e adiciona o overlay
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.background = 'rgba(0,0,0,0.7)'; // Overlay mais escuro
                overlay.style.zIndex = '1000';
                overlay.style.transition = 'all 0.3s ease';
                document.body.appendChild(overlay);
                
                // Evento de clique para o overlay
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    body.classList.remove('menu-active');
                    this.remove();
                    // Mostra o botão de toggle novamente
                    setTimeout(() => {
                        toggleButton.style.display = 'block';
                    }, 50);
                });
            } else {
                // Remove classe do body
                body.classList.remove('menu-active');
                
                // Esconde o overlay se existir
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) overlay.remove();
                
                // Certifica-se de que o botão de toggle está visível
                toggleButton.style.display = 'block';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Controle do botão "Voltar para Tickets"
            const backButton = document.querySelector('.back-to-tickets');
            let lastScrollTop = 0;
            let scrollingUp = false;
            
            window.addEventListener('scroll', function() {
                const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
                
                // Determina a direção do scroll
                scrollingUp = currentScroll < lastScrollTop;
                
                // Se rolando para baixo e já passou do cabeçalho, esconde o botão
                if (!scrollingUp && currentScroll > 150) {
                    backButton.classList.add('hidden');
                }
                
                // Se rolando para cima e está próximo ao topo, mostra o botão
                if (scrollingUp && currentScroll < 150) {
                    backButton.classList.remove('hidden');
                }
                
                lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
            });
            
            // Aplicar borda neon baseada no status do ticket em dispositivos móveis
            function aplicarBordaNeon() {
                // Verificar status do ticket pela classe atual
                const statusElement = document.querySelector('.ticket-status');
                if (!statusElement) return;
                
                // Extrair o status da classe (ex: status-aberto -> aberto)
                const statusClasses = Array.from(statusElement.classList);
                let statusClass = '';
                
                for (let cls of statusClasses) {
                    if (cls.startsWith('status-')) {
                        statusClass = cls.replace('status-', '');
                        break;
                    }
                }
                
                if (!statusClass) return;
                
                // Aplicar classe de borda neon à caixa de conteúdo do ticket
                const ticketContent = document.querySelector('.ticket-content');
                if (ticketContent) {
                    // Remover classes de borda neon existentes
                    ticketContent.classList.remove(
                        'neon-border-aberto',
                        'neon-border-em-andamento',
                        'neon-border-resolvido',
                        'neon-border-concluido',
                        'neon-border-fechado',
                        'neon-border-cancelado'
                    );
                    
                    // Adicionar a classe apropriada
                    ticketContent.classList.add(`neon-border-${statusClass}`);
                }
            }
            
            // Aplicar borda neon inicialmente
            aplicarBordaNeon();
            
            // Reaplica ao redimensionar a janela
            window.addEventListener('resize', aplicarBordaNeon);
        });
    </script>
</body>
</html>

<?php
// Função auxiliar para formatar o tamanho do arquivo
function formatarTamanhoArquivo($bytes) {
    if ($bytes < 1024) return $bytes . " bytes";
    else if ($bytes < 1048576) return round($bytes / 1024, 1) . " KB";
    else return round($bytes / 1048576, 1) . " MB";
}
?> 