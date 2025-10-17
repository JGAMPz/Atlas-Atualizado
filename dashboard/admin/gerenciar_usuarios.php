<?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$usuario = getUsuarioInfo();

// Processar alteração de tipo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'alterar_tipo') {
    if (isset($_POST['usuario_id'], $_POST['novo_tipo'])) {
        $usuario_id = $_POST['usuario_id'];
        $novo_tipo = $_POST['novo_tipo'];
        $resultado = alterarTipoUsuario($usuario_id, $novo_tipo, $usuario['id']);
    } else {
        $resultado = ['success' => false, 'message' => 'Dados incompletos para alteração.'];
    }
}

// Buscar todos os usuários - VERSÃO SEGURA
try {
    // Verificar se a coluna is_super_admin existe de forma segura
    $column_exists = false;
    try {
        $check_column = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'is_super_admin'");
        $column_exists = (bool)$check_column->fetch();
    } catch (Exception $e) {
        $column_exists = false;
    }
    
    if ($column_exists) {
        $stmt = $pdo->query("
            SELECT id, nome, email, tipo, telefone, data_nascimento, endereco, 
                   data_cadastro, status, ultimo_login, is_super_admin
            FROM usuarios 
            ORDER BY tipo, nome
        ");
    } else {
        $stmt = $pdo->query("
            SELECT id, nome, email, tipo, telefone, data_nascimento, endereco, 
                   data_cadastro, status, ultimo_login, 0 as is_super_admin
            FROM usuarios 
            ORDER BY tipo, nome
        ");
    }
    
    $usuarios = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $usuarios = [];
    $erro_busca = "Erro ao carregar lista de usuários: " . $e->getMessage();
    error_log("Erro na query de usuários: " . $e->getMessage());
}

// Garantir que o usuário atual tenha is_super_admin definido
if (!isset($usuario['is_super_admin'])) {
    try {
        $check_column = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'is_super_admin'");
        if ($check_column->fetch()) {
            $stmt = $pdo->prepare("SELECT is_super_admin FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario['id']]);
            $user_data = $stmt->fetch();
            $usuario['is_super_admin'] = $user_data['is_super_admin'] ?? 0;
        } else {
            $usuario['is_super_admin'] = 0;
        }
    } catch (Exception $e) {
        $usuario['is_super_admin'] = 0;
    }
}

// Garantir que todos os usuários tenham is_super_admin
foreach ($usuarios as &$user) {
    if (!isset($user['is_super_admin'])) {
        $user['is_super_admin'] = 0;
    }
}
unset($user);

$page_title = "Gerenciar Usuários";
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
    border-radius: 20px;
    background: var(--branco-puro);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.dashboard-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 5px;
    height: 100%;

}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
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

/* Títulos */
.page-title {
    color: var(--preto-elegante);
    font-weight: 800;
    margin-bottom: 0.5rem;
    font-size: 2.5rem;
    background: var(--preto-elegante);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
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

.badge-admin {
    background: rgba(220, 38, 38, 0.1);
    border-color: #dc2626;
    color: #dc2626;
}

.badge-personal {
    background: rgba(245, 158, 11, 0.1);
    border-color: #d97706;
    color: #d97706;
}

.badge-aluno {
    background: rgba(37, 99, 235, 0.1);
    border-color: #2563eb;
    color: #2563eb;
}

.badge-super-admin {
    background: linear-gradient(135deg, rgba(217, 119, 6, 0.2) 0%, rgba(245, 158, 11, 0.2) 100%);
    border-color: #d97706;
    color: #d97706;
    border-width: 2px;
}

/* Tabela Moderna */
.table-modern {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    background: var(--branco-puro);
}

.table-modern thead th {
    background: var(--azul-profundo);
    color: white;
    font-weight: 600;
    padding: 1.25rem 1rem;
    border: none;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-modern tbody td {
    padding: 1.25rem 1rem;
    border-bottom: 1px solid var(--cinza-suave);
    vertical-align: middle;
    transition: all 0.3s ease;
}

.table-modern tbody tr:last-child td {
    border-bottom: none;
}

.table-modern tbody tr:hover {
    background-color: rgba(59, 130, 246, 0.05);
    transform: translateX(4px);
}

.table-modern tbody tr:hover td {
    background-color: transparent;
}

/* Formulários */
.form-control,
.form-select {
    border: 2px solid var(--cinza-suave);
    border-radius: 12px;
    padding: 12px 16px;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--azul-vibrante);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-select-sm {
    min-width: 160px;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 12px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding-right: 40px;
}

/* Alertas */
.alert {
    border-radius: 12px;
    border: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    padding: 1.25rem 1.5rem;
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

.alert-info {
    background: rgba(59, 130, 246, 0.1);
    color: #2563eb;
    border-left: 4px solid #2563eb;
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

/* Cards de Estatísticas */
.stats-card {
    border: none;
    border-radius: 16px;
    background: var(--branco-puro);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--gradiente-azul);
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

/* Ícones Especiais */
.icon-crown {
    color: var(--dourado-vibrante);
    text-shadow: 0 2px 4px rgba(217, 119, 6, 0.3);
}

.icon-user {
    color: var(--azul-vibrante);
}

.icon-shield {
    color: var(--laranja-vibrante);
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

/* Filtros */
.filter-card {
    border: none;
    border-radius: 16px;
    background: var(--branco-puro);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 2rem;
}

.filter-section {
    padding: 1.5rem;
    border-bottom: 1px solid var(--cinza-suave);
}

.filter-section:last-child {
    border-bottom: none;
}
</style>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-5 fade-in">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Gerenciar Usuários</h1>
                    <p class="page-subtitle">Gerencie tipos e permissões dos usuários do sistema</p>
                </div>
                <div class="d-flex gap-3">
                    <button class="btn btn-modern">
                        <i class="fas fa-download me-2"></i>Exportar
                    </button>
                    <button class="btn btn-gold">
                        <i class="fas fa-sync-alt me-2"></i>Atualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid fade-in">
        <div class="stats-card">
            <div class="card-body p-4">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo count($usuarios); ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Total de Usuários</p>
                <small class="text-muted">Todos os usuários cadastrados</small>
            </div>
        </div>

        <?php
        $total_ativos = array_sum(array_map(fn($u) => $u['status'] === 'ativo' ? 1 : 0, $usuarios));
        $total_admins = array_sum(array_map(fn($u) => $u['tipo'] === 'admin' ? 1 : 0, $usuarios));
        $total_personais = array_sum(array_map(fn($u) => $u['tipo'] === 'personal' ? 1 : 0, $usuarios));
        ?>

        <div class="stats-card">
            <div class="card-body p-4">
                <div class="stat-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo $total_ativos; ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Usuários Ativos</p>
                <small class="text-muted">Contas ativas no sistema</small>
            </div>
        </div>

        <div class="stats-card">
            <div class="card-body p-4">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="stat-number"><?php echo $total_admins; ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Administradores</p>
                <small class="text-muted">Usuários com acesso total</small>
            </div>
        </div>

        <div class="stats-card">
            <div class="card-body p-4">
                <div class="stat-icon bg-info">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <div class="stat-number"><?php echo $total_personais; ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Personal Trainers</p>
                <small class="text-muted">Profissionais cadastrados</small>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($resultado)): ?>
    <div class="row mb-4 fade-in">
        <div class="col-12">
            <div
                class="alert alert-<?php echo $resultado['success'] ? 'success' : 'danger'; ?> alert-dismissible fade show">
                <div class="d-flex align-items-center">
                    <i
                        class="fas <?php echo $resultado['success'] ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> me-3 fs-5"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1"><?php echo $resultado['success'] ? 'Sucesso!' : 'Erro!'; ?></h6>
                        <p class="mb-0"><?php echo $resultado['message']; ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($erro_busca)): ?>
    <div class="row mb-4 fade-in">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-3 fs-5"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">Erro!</h6>
                        <p class="mb-0"><?php echo $erro_busca; ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lista de Usuários -->
    <div class="row fade-in">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-header bg-transparent border-0 py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title fw-bold text-dark mb-2 ps-3">
                                Lista de Usuários
                            </h5>
                            <small class="text-muted ps-3 mb-2">Total: <?php echo count($usuarios); ?> usuários
                                cadastrados</small>
                        </div>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control ps-2" placeholder="Buscar usuário..."
                                style="min-width: 250px;">
                            <select class="form-select me-3" style="min-width: 150px;">
                                <option value="">Todos os tipos</option>
                                <option value="aluno">Alunos</option>
                                <option value="personal">Personais</option>
                                <option value="admin">Administradores</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($usuarios)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <h3 class="text-dark mb-3">Nenhum usuário encontrado</h3>
                        <p class="text-muted mb-4">Não há usuários cadastrados no sistema</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>Contato</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Cadastro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                                style="width: 45px; height: 45px; font-weight: 600;">
                                                <?php echo strtoupper(substr($user['nome'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($user['nome']); ?>
                                                </h6>
                                                <small
                                                    class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                                <?php if ($user['id'] == $usuario['id']): ?>
                                                <span class="badge bg-info ms-2">Você</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <small
                                                class="text-muted"><?php echo $user['telefone'] ?: 'Não informado'; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $is_super_admin = $user['is_super_admin'] ?? 0;
                                        $tipo_classes = [
                                            'admin' => ['badge-admin', ($is_super_admin == 1) ? 'Admin Principal' : 'Administrador'],
                                            'personal' => ['badge-personal', 'Personal Trainer'],
                                            'aluno' => ['badge-aluno', 'Aluno']
                                        ];
                                        $tipo_info = $tipo_classes[$user['tipo']] ?? ['badge-secondary', 'Usuário'];
                                        ?>
                                        <span class="badge-modern <?php echo $tipo_info[0]; ?>">
                                            <?php echo $tipo_info[1]; ?>
                                            <?php if ($is_super_admin == 1): ?>
                                            <i class="fas fa-crown icon-crown ms-1"></i>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge-modern <?php echo $user['status'] == 'ativo' ? 'badge-active' : 'badge-inactive'; ?>">
                                            <i class="fas fa-circle me-1 small"></i>
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-dark fw-semibold">
                                            <?php echo !empty($user['data_cadastro']) ? formatDate($user['data_cadastro']) : 'N/A'; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($user['id'] != $usuario['id']): ?>

                                        <?php if (($user['is_super_admin'] ?? 0) == 1): ?>
                                        <div class="d-flex align-items-center text-warning">
                                            <i class="fas fa-crown icon-crown me-2"></i>
                                            <div>
                                                <div class="fw-bold">Admin Principal</div>
                                                <small class="text-muted">Não pode ser alterado</small>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="usuario_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="acao" value="alterar_tipo">

                                            <select name="novo_tipo" class="form-select form-select-sm"
                                                onchange="if(confirm('Tem certeza que deseja alterar <?php echo htmlspecialchars($user['nome']); ?> para ' + this.options[this.selectedIndex].text + '?')) { this.form.submit(); } else { this.value='<?php echo $user['tipo']; ?>'; }"
                                                style="min-width: 160px;">
                                                <option value="aluno"
                                                    <?php echo $user['tipo'] == 'aluno' ? 'selected' : ''; ?>>Aluno
                                                </option>
                                                <option value="personal"
                                                    <?php echo $user['tipo'] == 'personal' ? 'selected' : ''; ?>>
                                                    Personal Trainer</option>
                                                <option value="admin"
                                                    <?php echo $user['tipo'] == 'admin' ? 'selected' : ''; ?>>
                                                    Administrador</option>
                                            </select>
                                        </form>

                                        <?php if ($user['tipo'] === 'admin'): ?>
                                        <small class="text-warning d-block mt-1">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Apenas o admin principal pode rebaixar
                                        </small>
                                        <?php endif; ?>
                                        <?php endif; ?>

                                        <?php else: ?>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user icon-user me-2"></i>
                                            <div>
                                                <div class="fw-bold text-dark">Seu usuário</div>
                                                <?php
                                                $current_user_super_admin = $usuario['is_super_admin'] ?? 0;
                                                $user_role = $current_user_super_admin == 1 ? 'Admin Principal 👑' : 
                                                            ($user['tipo'] === 'personal' ? 'Personal Trainer' : 'Aluno');
                                                ?>
                                                <small
                                                    class="<?php echo $current_user_super_admin == 1 ? 'text-warning fw-bold' : 'text-muted'; ?>">
                                                    <?php echo $user_role; ?>
                                                </small>
                                                <?php if ($current_user_super_admin == 1): ?>
                                                <br>
                                                <small class="text-success">
                                                    <i class="fas fa-shield-alt icon-shield me-1"></i>
                                                    Permissões totais
                                                </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
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
</div>

<script>
// Filtro de busca em tempo real
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[placeholder="Buscar usuário..."]');
    const typeFilter = document.querySelector('select');
    const tableRows = document.querySelectorAll('.table-modern tbody tr');

    function filterUsers() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedType = typeFilter.value;

        tableRows.forEach(row => {
            const userName = row.querySelector('h6').textContent.toLowerCase();
            const userEmail = row.querySelector('small.text-muted').textContent.toLowerCase();
            const userType = row.querySelector('.badge-modern').textContent.toLowerCase();

            const matchesSearch = userName.includes(searchTerm) || userEmail.includes(searchTerm);
            const matchesType = !selectedType || userType.includes(selectedType);

            row.style.display = (matchesSearch && matchesType) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterUsers);
    typeFilter.addEventListener('change', filterUsers);
});

// Tooltips para ícones
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Confirmação antes de alterações
function confirmChange(userName, newType) {
    return confirm(`Tem certeza que deseja alterar ${userName} para ${newType}?`);
}
</script>

<?php include '../../includes/footer.php'; ?>