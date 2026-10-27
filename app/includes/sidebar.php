<?php
/**
 * Função para gerar o conteúdo da sidebar com base no tipo de usuário
 * @param string $userType - Tipo de usuário (tecnico, utilizador, etc)
 * @param string $userName - Nome do usuário
 * @param string $userPhoto - Foto do usuário
 * @param string $activePage - Página atual para destacar o link ativo
 * @return string HTML da sidebar
 */
function getSidebarContent($userType = 'tecnico', $userName = 'Usuário', $userPhoto = null, $activePage = '') {
    // Se não houver foto, usar a default
    if (!$userPhoto || $userPhoto == '') {
        $userPhoto = 'default.jpg';
    }

    // Usar caminho absoluto para as imagens
    $photoPath = '/assets/img/perfil/' . $userPhoto;
    
    // Determinar quais links estão ativos
    $dashboardActive = ($activePage == 'dashboard') ? 'active' : '';
    $inventarioActive = ($activePage == 'inventario') ? 'active' : '';
    $inventarioPCActive = ($activePage == 'inventariopc') ? 'active' : '';
    $salasActive = ($activePage == 'salas') ? 'active' : '';
    $contasActive = ($activePage == 'contas') ? 'active' : '';
    $perfilActive = ($activePage == 'perfil') ? 'active' : '';
    $manutencaoActive = ($activePage == 'manutencao') ? 'active' : '';

    // Definir caminhos base do projeto
    $basePath = '';
    // Verificar se a variável está definida, caso contrário usar detecção automática
    if(isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['SCRIPT_NAME'])) {
        // Obter o diretório base do projeto a partir do caminho do script
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $basePath = str_replace('\\', '/', $scriptDir);
        $basePath = preg_replace('/\/includes$|\/dashboard(\/.*)?$|\/computadores$|\/conta$|\/salas$|\/manutencao$/', '', $basePath);
        
        // Se estamos na raiz do servidor, basePath será vazio, então garantimos que ele tenha uma barra
        if(empty($basePath)) {
            $basePath = '/';
        }
        
        // Garantir que termina com uma barra
        if(substr($basePath, -1) != '/') {
            $basePath .= '/';
        }
    }

    // Definir o texto do tipo de usuário
    $userTypeText = ($userType == 'tecnico') ? 'Suporte Técnico' : 'Comunidade Escolar';

    $sidebarHTML = <<<HTML
    <div class="sidebar-header p-3 border-bottom">
        <div class="d-flex align-items-center">
            <i class="bi bi-tools fs-4 me-2 text-primary"></i>
            <h5 class="mb-0">GM HelpDesk</h5>
        </div>
    </div>
    <div class="p-3">
        <div class="user-info">
            <a href="{$basePath}conta/perfil.php" class="text-decoration-none">
                <div class="d-flex align-items-center">
                    <div class="avatar">
                        <img src="{$basePath}assets/img/perfil/{$userPhoto}" 
                             class="rounded-circle" 
                             style="width: 45px; height: 45px; object-fit: cover;">
                    </div>
                    <div class="user-details ms-2">
                        <h6 class="mb-0">{$userName}</h6>
                        <small>{$userTypeText}</small>
                    </div>
                </div>
            </a>
        </div>
        <ul class="nav flex-column mt-3">
            <li class="nav-item mb-2">
                <a href="{$basePath}dashboard/tickets/dashboard.php" class="nav-link {$dashboardActive}">
                    <i class="bi bi-house"></i>
                    Dashboard
                </a>
            </li>
HTML;

    // Adicionar links específicos para técnicos
    if ($userType == 'tecnico') {
        $sidebarHTML .= <<<HTML
            <li class="nav-item mb-2">
                <a href="{$basePath}manutencao/manutencao.php" class="nav-link {$manutencaoActive}">
                    <i class="bi bi-wrench-adjustable"></i>
                    Manutenção
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="{$basePath}dashboard/inventario.php" class="nav-link {$inventarioActive}">
                    <i class="bi bi-box"></i>
                    Inventário
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="{$basePath}computadores/inventariopc.php" class="nav-link {$inventarioPCActive}">
                    <i class="bi bi-pc-display"></i>
                    Inventário Computador
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="{$basePath}salas/salas.php" class="nav-link {$salasActive}">
                    <i class="bi bi-building"></i>
                    Salas
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="{$basePath}dashboard/utilizadores/contas.php" class="nav-link {$contasActive}">
                    <i class="bi bi-people"></i>
                    Contas
                </a>
            </li>
            
HTML;
    }

    $sidebarHTML .= <<<HTML
        </ul>
    </div>
    <div class="escola-logo">
        <img src="{$basePath}assets/img/menu/escola.png" alt="Logo da Escola">
    </div>
HTML;

    return $sidebarHTML;
}

/**
 * Função para renderizar a sidebar completa incluindo manipulação JS
 * @param string $activePage - Página atual para destacar o link ativo
 * @param string $userType - Tipo de usuário (opcional, se não fornecido, será obtido via AJAX)
 * @param string $userName - Nome do usuário (opcional, se não fornecido, será obtido via AJAX)
 * @param string $userPhoto - Foto do usuário (opcional, se não fornecido, será obtido via AJAX)
 */
function renderSidebar($activePage = '', $userType = null, $userName = null, $userPhoto = null) {
    // Se foram fornecidos dados do usuário, use-os diretamente
    if ($userType !== null && $userName !== null) {
        // Pré-renderiza a sidebar com os valores fornecidos
        $sidebarContent = getSidebarContent($userType, $userName, $userPhoto, $activePage);
        
        // Adicionar o botão de toggle para mobile
        echo <<<HTML
        <button class="sidebar-toggle btn" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        
        <nav class="sidebar">
            {$sidebarContent}
        </nav>

        <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            
            sidebar.classList.toggle('active');
            
            if (sidebar.classList.contains('active')) {
                body.classList.add('menu-active');
                
                // Cria e adiciona o overlay
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.background = 'rgba(0,0,0,0.7)';
                overlay.style.zIndex = '1000';
                overlay.style.transition = 'all 0.3s ease';
                document.body.appendChild(overlay);
                
                // Evento de clique para o overlay
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    body.classList.remove('menu-active');
                    this.remove();
                });
            } else {
                body.classList.remove('menu-active');
                
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) overlay.remove();
            }
        }
        
        // Adicionar event listeners para os links da navegação
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Verificar se estamos em viewport mobile
                    if (window.innerWidth <= 768) {
                        // Remover o overlay e restaurar o botão toggle
                        const overlay = document.querySelector('.sidebar-overlay');
                        if (overlay) overlay.remove();
                        
                        document.body.classList.remove('menu-active');
                    }
                });
            });
        });
        </script>
HTML;
    } else {
        // Pré-renderiza a sidebar com valores padrão
        $defaultSidebar = getSidebarContent('tecnico', 'Usuário', null, $activePage);
        
        // Adicionar o botão de toggle para mobile
        echo <<<HTML
        <button class="sidebar-toggle btn" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        
        <nav class="sidebar">
            <!-- Será preenchido pelo JavaScript -->
            {$defaultSidebar}
        </nav>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadSidebar('{$activePage}');
        });

        function loadSidebar(activePage) {
            // Determine basePath more dynamically
            let basePath = '';
            // Use window.location.pathname to get the full current path
            const currentPath = window.location.pathname;
            
            // Check if we're in a known subdirectory
            if (currentPath.includes('/dashboard/') || 
                currentPath.includes('/conta/') ||
                currentPath.includes('/computadores/') ||
                currentPath.includes('/salas/')) {
                // Extract the base path by going back the appropriate number of directories
                const pathParts = currentPath.split('/');
                // Remove the last part (current file) and the current directory
                pathParts.splice(-2, 2);
                basePath = pathParts.join('/') + '/';
            } else {
                // If we're at the root of the site or can't determine
                basePath = '/';
            }
            
            fetch(basePath + 'conta/get_user_data.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const userType = data.tipo_utilizador || 'tecnico';
                        const userName = data.nome_completo || 'Usuário';
                        const userPhoto = data.foto_perfil || null;
                        
                        // Fazer uma solicitação para obter o sidebar HTML atualizado
                        fetch(basePath + 'includes/get_sidebar.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `userType=\${userType}&userName=\${userName}&userPhoto=\${userPhoto}&activePage=\${activePage}`
                        })
                        .then(response => response.text())
                        .then(html => {
                            document.querySelector('.sidebar').innerHTML = html;
                            
                            // Adicionar event listeners para os links da navegação
                            setupSidebarEventListeners();
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar dados do usuário:', error);
                    setupSidebarEventListeners();
                });
        }
        
        function setupSidebarEventListeners() {
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Verificar se estamos em viewport mobile
                    if (window.innerWidth <= 768) {
                        // Remover o overlay e restaurar o botão toggle
                        const overlay = document.querySelector('.sidebar-overlay');
                        if (overlay) overlay.remove();
                        
                        document.body.classList.remove('menu-active');
                    }
                    // Permitir que a navegação continue normalmente
                });
            });
        }
        
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const toggleButton = document.querySelector('.sidebar-toggle');
            const body = document.body;
            
            sidebar.classList.toggle('active');
            
            // Se o sidebar estiver ativo, esconde o botão de toggle e mostra o overlay
            if (sidebar.classList.contains('active')) {
                // Esconde imediatamente o botão de toggle
                if (toggleButton) toggleButton.style.display = 'none';
                
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
                        if (toggleButton) toggleButton.style.display = 'block';
                    }, 50);
                });
            } else {
                // Remove classe do body
                body.classList.remove('menu-active');
                
                // Esconde o overlay se existir
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) overlay.remove();
                
                // Certifica-se de que o botão de toggle está visível
                if (toggleButton) toggleButton.style.display = 'block';
            }
        }
        </script>
HTML;
    }
}
?> 