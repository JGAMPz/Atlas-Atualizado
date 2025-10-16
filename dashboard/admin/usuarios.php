<?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
verificarTipo('admin');

$usuario = getUsuarioInfo();

// Iniciar sessão se não estiver ativa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Processar ações AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'alterar_status':
                if (isset($_POST['usuario_id']) && isset($_POST['novo_status'])) {
                    $usuario_id = intval($_POST['usuario_id']);
                    $novo_status = $_POST['novo_status'];
                    
                    $stmt = $pdo->prepare("UPDATE usuarios SET status = ? WHERE id = ?");
                    $stmt->execute([$novo_status, $usuario_id]);
                    
                    echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Dados insuficientes.']);
                }
                exit;
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Buscar todos os usuários
try {
    $stmt = $pdo->query("
        SELECT id, nome, email, tipo, telefone, data_cadastro, ultimo_login, status 
        FROM usuarios 
        ORDER BY data_cadastro DESC
    ");
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $usuarios = [];
    $erro = "Erro ao carregar usuários: " . $e->getMessage();
    error_log($erro);
}

// Estatísticas por tipo
try {
    $stmt = $pdo->query("
        SELECT tipo, COUNT(*) as total, 
               SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) as ativos
        FROM usuarios 
        GROUP BY tipo
    ");
    $estatisticas_tipo = $stmt->fetchAll();
} catch (PDOException $e) {
    $estatisticas_tipo = [];
}

$page_title = "Gerenciar Usuários - Admin";
include '../../includes/header.php';
?>

<style>
:root {
    --azul-profundo: #1e3a8a;
    --azul-vibrante: #2563eb;
    --azul-suave: #3b82f6;
    --laranja-queimado: #ea580c;
    --laranja-vibrante: #f97316;
    --laranja-suave: #fb923c;
    --dourado-brilhante: #d97706;
    --dourado-vibrante: #f59e0b;
    --dourado-suave: #fbbf24;
    --preto-elegante: #111827;
    --preto-suave: #1f2937;
    --branco-puro: #ffffff;
    --branco-suave: #f8fafc;
    --cinza-suave: #f3f4f6;
    --cinza-medio: #9ca3af;
    --gradiente-azul: linear-gradient(135deg, var(--azul-profundo) 0%, var(--azul-vibrante) 100%);
    --gradiente-laranja: linear-gradient(135deg, var(--laranja-queimado) 0%, var(--laranja-vibrante) 100%);
    --gradiente-dourado: linear-gradient(135deg, var(--dourado-brilhante) 0%, var(--dourado-vibrante) 100%);
    --gradiente-misto: linear-gradient(135deg, var(--azul-vibrante) 0%, var(--laranja-vibrante) 100%);
}

body {
    background-color: var(--branco-suave);
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
}

/* Cards e Layout */
.dashboard-card {
    border: none;
    border-radius: 16px;
    background: var(--branco-puro);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    background: var(--azul-vibrante);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
    background: var(--azul-profundo);
    color: white;
}

/* Botões */
.btn-modern {
    background: var(--branco-puro);
    border: 2px solid var(--azul-vibrante);
    color: var(--azul-vibrante);
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
}

.btn-modern:hover {
    background: var(--azul-vibrante);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

.btn-gold {
    background: var(--gradiente-azul);
    border: none;
    color: white;
    font-weight: 700;
    padding: 14px 28px;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 6px 15px rgba(6, 27, 217, 0.3);
}

.btn-gold:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(6, 80, 217, 0.4);
    color: white;
}

/* Cards de Usuário */
.user-card {
    border: none;
    border-radius: 12px;
    background: var(--branco-puro);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    position: relative;
}

.user-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    color: white;
    margin-right: 1rem;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

/* Modais */
.modal-header-gradient {
    background: var(--gradiente-azul);
    color: white;
}

.modal-content {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

/* Títulos */
.page-title {
    color: var(--preto-elegante);
    font-weight: 800;
    margin-bottom: 0.5rem;
    font-size: 2.5rem;
}

.page-subtitle {
    color: var(--cinza-medio);
    font-weight: 500;
    font-size: 1.1rem;
}

/* Grid de Estatísticas */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .page-title {
        font-size: 2rem;
    }
}

/* Badges */
.badge-modern {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
    border: 1px solid;
}

.badge-active {
    background: rgba(34, 197, 94, 0.1);
    border-color: #16a34a;
    color: #16a34a;
}

.badge-inactive {
    background: rgba(107, 114, 128, 0.1);
    border-color: #6b7280;
    color: #6b7280;
}

.badge-suspenso {
    background: rgba(239, 68, 68, 0.1);
    border-color: #dc2626;
    color: #dc2626;
}

.badge-admin {
    background: rgba(220, 38, 38, 0.1);
    border-color: #dc2626;
    color: #dc2626;
}

.badge-personal {
    background: rgba(34, 197, 94, 0.1);
    border-color: #16a34a;
    color: #16a34a;
}

.badge-aluno {
    background: rgba(37, 99, 235, 0.1);
    border-color: #2563eb;
    color: #2563eb;
}

/* Estado Vazio */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state i {
    font-size: 4rem;
    background: var(--gradiente-misto);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
}

/* Formulários */
.form-control,
.form-select {
    border: 2px solid var(--cinza-suave);
    border-radius: 12px;
    padding: 12px 16px;
    transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--azul-vibrante);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Alertas */
.alert {
    border-radius: 12px;
    border: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
    border-left: 4px solid #16a34a;
}

.alert-danger {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    border-left: 4px solid #dc2626;
}

/* Botões de ação */
.btn-action {
    border-radius: 10px;
    padding: 8px 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 0.875rem;
}

.btn-edit {
    background: rgba(37, 99, 235, 0.1);
    color: var(--azul-vibrante);
    border: 1px solid rgba(37, 99, 235, 0.2);
}

.btn-edit:hover {
    background: var(--azul-vibrante);
    color: white;
    transform: translateY(-2px);
}

.btn-status {
    background: rgba(249, 115, 22, 0.1);
    color: #f97316;
    border: 1px solid rgba(249, 115, 22, 0.2);
}

.btn-status:hover {
    background: #f97316;
    color: white;
    transform: translateY(-2px);
}

/* Animações */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeIn 0.5s ease-out;
}

/* Filtros */
.filter-card {
    border: none;
    border-radius: 16px;
    background: var(--branco-puro);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.filter-section {
    padding: 1.5rem;
    border-bottom: 1px solid var(--cinza-suave);
}

.filter-section:last-child {
    border-bottom: none;
}

/* Tabela */
.table-modern {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.table-modern thead th {
    background: var(--azul-profundo);
    color: white;
    font-weight: 600;
    padding: 1rem;
    border: none;
}

.table-modern tbody td {
    padding: 1rem;
    border-bottom: 1px solid var(--cinza-suave);
    vertical-align: middle;
}

.table-modern tbody tr:last-child td {
    border-bottom: none;
}

.table-modern tbody tr:hover {
    background-color: rgba(59, 130, 246, 0.05);
}
</style>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-5 fade-in">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Gerenciar Usuários</h1>
                    <p class="page-subtitle">Visualize e gerencie todos os usuários do sistema</p>
                </div>
                <div class="d-flex gap-3">
                    <button class="btn btn-modern px-4 fw-bold" onclick="exportarUsuarios()">
                        <i class="fas fa-file-export me-2"></i>Exportar
                    </button>
                    <button class="btn btn-gold px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalNovoUsuario">
                        <i class="fas fa-user-plus me-2"></i>Novo Usuário
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid fade-in">
        <div class="dashboard-card">
            <div class="card-body p-4">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo count($usuarios ?? []); ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Total de Usuários</p>
                <small class="text-muted">Todos os usuários cadastrados</small>
            </div>
        </div>

        <?php foreach ($estatisticas_tipo as $estat): ?>
        <div class="dashboard-card">
            <div class="card-body p-4">
                <div class="stat-icon" style="background: <?php 
                    echo $estat['tipo'] === 'admin' ? '#dc2626' : 
                         ($estat['tipo'] === 'personal' ? '#16a34a' : '#2563eb');
                ?>;">
                    <i class="fas <?php 
                        echo $estat['tipo'] === 'admin' ? 'fa-crown' : 
                             ($estat['tipo'] === 'personal' ? 'fa-dumbbell' : 'fa-user-graduate');
                    ?>"></i>
                </div>
                <div class="stat-number"><?php echo $estat['total']; ?></div>
                <p class="card-text fw-semibold text-dark mb-1"><?php echo ucfirst($estat['tipo']) . 's'; ?></p>
                <small class="text-muted"><?php echo $estat['ativos']; ?> ativos</small>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($erro)): ?>
    <div class="row mb-4 fade-in">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-3 fs-5"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">Erro!</h6>
                        <p class="mb-0"><?php echo $erro; ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="row mb-4 fade-in">
        <div class="col-12">
            <div class="filter-card">
                <div class="filter-section">
                    <h5 class="fw-bold text-dark mb-3">Filtrar Usuários</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="filterTipo" class="form-label fw-semibold text-dark">Tipo de Usuário</label>
                            <select class="form-select" id="filterTipo">
                                <option value="">Todos os tipos</option>
                                <option value="aluno">Alunos</option>
                                <option value="personal">Personal Trainers</option>
                                <option value="admin">Administradores</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filterStatus" class="form-label fw-semibold text-dark">Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">Todos os status</option>
                                <option value="ativo">Ativos</option>
                                <option value="inativo">Inativos</option>
                                <option value="suspenso">Suspensos</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="searchUsers" class="form-label fw-semibold text-dark">Buscar</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchUsers" placeholder="Nome, email...">
                                <button class="btn btn-outline-secondary" type="button" onclick="filtrarUsuarios()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Usuários -->
    <div class="row fade-in">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header bg-transparent border-0 py-3 px-4">
                    <h5 class="card-title fw-bold text-dark mb-0 ">
                        Todos os Usuários
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($usuarios)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <h3 class="text-dark mb-3">Nenhum usuário cadastrado</h3>
                        <p class="text-muted mb-4">Não há usuários no sistema ainda</p>
                        <button class="btn btn-gold btn-lg px-5" data-bs-toggle="modal"
                            data-bs-target="#modalNovoUsuario">
                            <i class="fas fa-user-plus me-2"></i>Adicionar Primeiro Usuário
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>Tipo</th>
                                    <th>Contato</th>
                                    <th>Cadastro</th>
                                    <th>Último Login</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar" style="background: <?php 
                                                echo $user['tipo'] === 'admin' ? '#dc2626' : 
                                                     ($user['tipo'] === 'personal' ? '#16a34a' : '#2563eb');
                                            ?>;">
                                                <?php echo strtoupper(substr($user['nome'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($user['nome']); ?>
                                                </h6>
                                                <small
                                                    class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-modern <?php 
                                            echo $user['tipo'] === 'admin' ? 'badge-admin' : 
                                                 ($user['tipo'] === 'personal' ? 'badge-personal' : 'badge-aluno');
                                        ?>">
                                            <i class="fas <?php 
                                                echo $user['tipo'] === 'admin' ? 'fa-crown' : 
                                                     ($user['tipo'] === 'personal' ? 'fa-dumbbell' : 'fa-user-graduate');
                                            ?> me-1"></i>
                                            <?php echo ucfirst($user['tipo']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <small
                                                class="text-muted"><?php echo $user['telefone'] ?: 'Não informado'; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <small
                                            class="text-dark fw-semibold"><?php echo formatDate($user['data_cadastro'], 'd/m/Y'); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($user['ultimo_login']): ?>
                                        <small
                                            class="text-dark fw-semibold"><?php echo formatDateTime($user['ultimo_login'], 'd/m H:i'); ?></small>
                                        <?php else: ?>
                                        <small class="text-muted">Nunca acessou</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge-modern <?php 
                                            echo $user['status'] === 'ativo' ? 'badge-active' : 
                                                 ($user['status'] === 'inativo' ? 'badge-inactive' : 'badge-suspenso');
                                        ?>">
                                            <i class="fas <?php 
                                                echo $user['status'] === 'ativo' ? 'fa-check-circle' : 
                                                     ($user['status'] === 'inativo' ? 'fa-pause-circle' : 'fa-exclamation-triangle');
                                            ?> me-1"></i>
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-action btn-edit"
                                                onclick="verDetalhesUsuario(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-eye me-1"></i> Ver
                                            </button>
                                            <button class="btn btn-action btn-status"
                                                onclick="alterarStatusUsuario(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')">
                                                <i class="fas <?php 
                                                    echo $user['status'] === 'ativo' ? 'fa-pause' : 'fa-play';
                                                ?> me-1"></i>
                                                <?php echo $user['status'] === 'ativo' ? 'Inativar' : 'Ativar'; ?>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalhes do Usuário -->
    <div class="modal fade" id="modalDetalhesUsuario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header modal-header-gradient py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-user me-2"></i>Detalhes do Usuário
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="detalhesUsuarioContent">
                    <!-- Conteúdo carregado via AJAX -->
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom">
                    <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i> Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Novo Usuário -->
    <div class="modal fade" id="modalNovoUsuario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header modal-header-gradient py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-user-plus me-2"></i>Adicionar Novo Usuário
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="usuarios.php">
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="novo_nome" class="form-label fw-bold text-dark">Nome Completo <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control py-3" id="novo_nome" name="nome" required
                                    placeholder="Nome do usuário">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="novo_email" class="form-label fw-bold text-dark">E-mail <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control py-3" id="novo_email" name="email" required
                                    placeholder="email@exemplo.com">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="novo_tipo" class="form-label fw-bold text-dark">Tipo de Usuário <span
                                        class="text-danger">*</span></label>
                                <select class="form-select py-3" id="novo_tipo" name="tipo" required>
                                    <option value="aluno">Aluno</option>
                                    <option value="personal">Personal Trainer</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="novo_telefone" class="form-label fw-bold text-dark">Telefone</label>
                                <input type="text" class="form-control py-3" id="novo_telefone" name="telefone"
                                    placeholder="(11) 99999-9999">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nova_senha" class="form-label fw-bold text-dark">Senha <span
                                        class="text-danger">*</span></label>
                                <input type="password" class="form-control py-3" id="nova_senha" name="senha" required
                                    placeholder="Senha segura">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="novo_status" class="form-label fw-bold text-dark">Status <span
                                        class="text-danger">*</span></label>
                                <select class="form-select py-3" id="novo_status" name="status" required>
                                    <option value="ativo" selected>Ativo</option>
                                    <option value="inativo">Inativo</option>
                                    <option value="suspenso">Suspenso</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light rounded-bottom">
                        <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-gold px-4 py-2 fw-bold">
                            <i class="fas fa-save me-2"></i> Criar Usuário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Filtros
    document.getElementById('filterTipo').addEventListener('change', filtrarUsuarios);
    document.getElementById('filterStatus').addEventListener('change', filtrarUsuarios);
    document.getElementById('searchUsers').addEventListener('input', filtrarUsuarios);

    function filtrarUsuarios() {
        const tipo = document.getElementById('filterTipo').value;
        const status = document.getElementById('filterStatus').value;
        const search = document.getElementById('searchUsers').value.toLowerCase();

        const rows = document.querySelectorAll('#usersTableBody tr');

        rows.forEach(row => {
            const rowTipo = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const rowStatus = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
            const rowText = row.textContent.toLowerCase();

            const tipoMatch = !tipo || rowTipo.includes(tipo);
            const statusMatch = !status || rowStatus.includes(status);
            const searchMatch = !search || rowText.includes(search);

            row.style.display = (tipoMatch && statusMatch && searchMatch) ? '' : 'none';
        });
    }

    function verDetalhesUsuario(usuarioId) {
        fetch(`../../includes/functions.php?action=detalhes_usuario&id=${usuarioId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('detalhesUsuarioContent').innerHTML = html;
                const modal = new bootstrap.Modal(document.getElementById('modalDetalhesUsuario'));
                modal.show();
            })
            .catch(error => {
                console.error('Erro ao carregar detalhes:', error);
                document.getElementById('detalhesUsuarioContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Erro ao carregar detalhes do usuário.
                    </div>
                `;
                const modal = new bootstrap.Modal(document.getElementById('modalDetalhesUsuario'));
                modal.show();
            });
    }

    function alterarStatusUsuario(usuarioId, statusAtual) {
        const novoStatus = statusAtual === 'ativo' ? 'inativo' : 'ativo';
        const confirmMessage = `Deseja ${novoStatus === 'ativo' ? 'ativar' : 'inativar'} este usuário?`;

        if (confirm(confirmMessage)) {
            const formData = new FormData();
            formData.append('action', 'alterar_status');
            formData.append('usuario_id', usuarioId);
            formData.append('novo_status', novoStatus);

            fetch('usuarios.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Recarregar a página para refletir as mudanças
                        window.location.reload();
                    } else {
                        alert('Erro: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro na comunicação com o servidor');
                });
        }
    }

    function exportarUsuarios() {
        alert('Funcionalidade de exportação será implementada em breve!');
        // Implementar lógica de exportação aqui
    }

    // Inicializar filtros
    document.addEventListener('DOMContentLoaded', function() {
        filtrarUsuarios();
    });
    </script>

    <?php include '../../includes/footer.php'; ?>