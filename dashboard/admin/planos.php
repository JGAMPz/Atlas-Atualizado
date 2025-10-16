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

// Buscar planos do banco ANTES de qualquer processamento
try {
    $stmt = $pdo->query("SELECT * FROM planos ORDER BY id");
    $planos = $stmt->fetchAll();
} catch (PDOException $e) {
    $planos = [];
    $erro = "Erro ao carregar planos: " . $e->getMessage();
    error_log($erro);
}

// Processar ações AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'excluir_plano':
                if (isset($_POST['plano_id'])) {
                    $resultado = excluirPlano($_POST['plano_id']);
                    echo json_encode($resultado);
                } else {
                    echo json_encode(['success' => false, 'message' => 'ID do plano não especificado.']);
                }
                exit;
                break;
                
            case 'editar_plano':
                if (isset($_POST['plano_id'])) {
                    $resultado = editarPlano($_POST);
                    echo json_encode($resultado);
                } else {
                    echo json_encode(['success' => false, 'message' => 'ID do plano não especificado.']);
                }
                exit;
                break;
                
            case 'criar_plano':
                $resultado = criarPlano($_POST);
                if ($resultado['success']) {
                    $_SESSION['sucesso'] = $resultado['message'];
                } else {
                    $_SESSION['erro'] = $resultado['message'];
                }
                header('Location: ' . $_SERVER['PHP_SELF']);
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

$page_title = "Gerenciar Planos - Admin";
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
    background: var(--gradiente-dourado);
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

.btn-orange {
    background: var(--gradiente-laranja);
    border: none;
    color: white;
    font-weight: 700;
    padding: 12px 24px;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 6px 15px rgba(249, 115, 22, 0.3);
}

.btn-orange:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(249, 115, 22, 0.4);
    color: white;
}

/* Cards de Plano */
.plan-card {
    border: none;
    border-radius: 10px;
    background: var(--branco-puro);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    position: relative;
}

.plan-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
}

.plan-header {
    background: var(--gradiente-azul);
    color: white;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.plan-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: rgba(255, 255, 255, 0.1);
    transform: rotate(30deg);
}

.price-tag {
    font-size: 2.5rem;
    font-weight: 800;
    color: white;
    position: relative;
    z-index: 2;
}

.plan-title {
    color: white;
    font-weight: 800;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 2;
}

.plan-status {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
    border: 1px solid;
    position: relative;
    z-index: 2;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

.status-active {
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
}

.status-inactive {
    border-color: rgba(255, 255, 255, 0.3);
    color: rgba(255, 255, 255, 0.8);
}

.feature-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    background: var(--azul-profundo);
    color: white;
}

/* Modais */
.modal-header-gradient {
    background: var(--gradiente-azul);
    color: white;
}

.modal-content {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
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
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.75rem;
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

.form-check-input:checked {
    background-color: var(--laranja-vibrante);
    border-color: var(--laranja-vibrante);
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

/* Botões de ação nos cards */
.btn-action {
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-edit {
    background: rgba(22, 92, 243, 0.34);
    color: var(--azul-vibrante);
    border: 1px solid rgba(37, 99, 235, 0.2);
}

.btn-edit:hover {
    background: var(--azul-vibrante);
    color: white;
    transform: translateY(-2px);
}

.btn-delete {
    background: rgba(236, 135, 20, 0.1);
    color: #eea010ff;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.btn-delete:hover {
    background: #ca710cff;
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
</style>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-5 fade-in">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Gerenciar Planos</h1>
                    <p class="page-subtitle">Crie e gerencie os planos disponíveis para seus alunos</p>
                </div>
                <button class="btn btn-gold btn-lg px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalPlano">
                    <i class="fas fa-plus-circle me-2"></i>Novo Plano
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid fade-in">
        <div class="dashboard-card">
            <div class="card-body p-4">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-number"><?php echo count($planos ?? []); ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Total de Planos</p>
                <small class="text-muted">Todos os planos cadastrados</small>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-body p-4">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number">
                    <?php 
                    $planos_ativos = array_filter($planos ?? [], fn($p) => $p['status'] === 'ativo');
                    echo count($planos_ativos); 
                    ?>
                </div>
                <p class="card-text fw-semibold text-dark mb-1">Planos Ativos</p>
                <small class="text-muted">Disponíveis para contratação</small>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-body p-4">
                <div class="stat-icon">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <div class="stat-number">
                    <?php 
                    $planos_personal = array_filter($planos ?? [], fn($p) => $p['inclui_personal'] == 1);
                    echo count($planos_personal); 
                    ?>
                </div>
                <p class="card-text fw-semibold text-dark mb-1">Com Personal</p>
                <small class="text-muted">Incluem treinador</small>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-body p-4">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-number">
                    R$ <?php 
                    if (!empty($planos)) {
                        $soma_precos = array_sum(array_column($planos, 'preco'));
                        echo number_format($soma_precos / count($planos), 0, ',', '.');
                    } else {
                        echo '0';
                    }
                    ?>
                </div>
                <p class="card-text fw-semibold text-dark mb-1">Valor Médio</p>
                <small class="text-muted">Preço médio mensal</small>
            </div>
        </div>
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

    <?php if (isset($_SESSION['sucesso'])): ?>
    <div class="row mb-4 fade-in">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-3 fs-5"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">Sucesso!</h6>
                        <p class="mb-0"><?php echo $_SESSION['sucesso']; ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['sucesso']); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['erro'])): ?>
    <div class="row mb-4 fade-in">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-3 fs-5"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">Erro!</h6>
                        <p class="mb-0"><?php echo $_SESSION['erro']; ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['erro']); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Planos Grid -->
    <div class="row fade-in">
        <?php if (empty($planos)): ?>
        <div class="col-12">
            <div class="plan-card text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3 class="text-dark mb-3">Nenhum plano cadastrado</h3>
                    <p class="text-muted mb-4">Comece criando seu primeiro plano para oferecer aos alunos</p>
                    <button class="btn btn-gold btn-lg px-5" data-bs-toggle="modal" data-bs-target="#modalPlano">
                        <i class="fas fa-plus me-2"></i>Criar Primeiro Plano
                    </button>
                </div>
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($planos as $plano): ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="plan-card h-100">
                <div class="plan-header">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="plan-title"><?php echo htmlspecialchars($plano['nome']); ?></h4>
                            <span
                                class="plan-status <?php echo $plano['status'] == 'ativo' ? 'status-active' : 'status-inactive'; ?>">
                                <i class="fas fa-circle me-1 small"></i>
                                <?php echo ucfirst($plano['status']); ?>
                            </span>
                        </div>
                        <div class="price-tag">
                            R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <?php if (!empty($plano['descricao'])): ?>
                    <p class="text-muted mb-4"><?php echo htmlspecialchars($plano['descricao']); ?></p>
                    <?php endif; ?>

                    <div class="row text-center mb-4">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="feature-icon mx-auto mb-2">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1"><?php echo $plano['duracao_dias'] ?? 30; ?> dias</h6>
                                <small class="text-muted">Duração</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="feature-icon mx-auto mb-2">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">
                                <?php echo $plano['inclui_personal'] ? 'Incluído' : 'Não'; ?>
                            </h6>
                            <small class="text-muted">Personal</small>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-transparent border-0 pt-0">
                    <div class="d-flex gap-2">
                        <button class="btn btn-action btn-edit flex-fill py-2"
                            onclick="abrirEditarPlano(<?php echo $plano['id']; ?>)">
                            <i class="fas fa-edit me-2"></i>Editar
                        </button>
                        <button class="btn btn-action btn-delete flex-fill py-2"
                            onclick="excluirPlano(<?php echo $plano['id']; ?>, '<?php echo htmlspecialchars($plano['nome']); ?>')">
                            <i class="fas fa-trash me-2"></i>Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Modal para Novo Plano -->
    <div class="modal fade" id="modalPlano" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header modal-header-gradient py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-plus-circle me-2"></i>Adicionar Novo Plano
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="planos.php">
                    <input type="hidden" name="action" value="criar_plano">
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label fw-bold text-dark">Nome do Plano <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control py-3" id="nome" name="nome" required
                                    placeholder="Ex: Plano Básico, Plano Premium">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="preco" class="form-label fw-bold text-dark">Valor Mensal (R$) <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control py-3" id="preco" name="preco" step="0.01"
                                    min="0" required placeholder="99.90">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label fw-bold text-dark">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3"
                                placeholder="Descreva os benefícios do plano..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="duracao_dias" class="form-label fw-bold text-dark">Duração (dias) <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control py-3" id="duracao_dias" name="duracao_dias"
                                    value="30" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label fw-bold text-dark">Status <span
                                        class="text-danger">*</span></label>
                                <select class="form-select py-3" id="status" name="status" required>
                                    <option value="ativo" selected>Ativo</option>
                                    <option value="inativo">Inativo</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="inclui_personal"
                                name="inclui_personal" value="1">
                            <label class="form-check-label fw-bold text-dark" for="inclui_personal">
                                <i class="fas fa-dumbbell me-2 text-warning"></i> Inclui Personal Trainer
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light rounded-bottom">
                        <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-gold px-4 py-2 fw-bold">
                            <i class="fas fa-save me-2"></i> Criar Plano
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Plano -->
    <div class="modal fade" id="modalEditarPlano" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header modal-header-gradient py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-edit me-2"></i>Editar Plano
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="planos.php" id="formEditarPlano">
                    <input type="hidden" name="action" value="editar_plano">
                    <input type="hidden" name="plano_id" id="editar_plano_id">
                    <input type="hidden" name="inclui_personal" id="editar_inclui_personal_hidden" value="0">
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editar_nome" class="form-label fw-bold text-dark">Nome do Plano</label>
                                <input type="text" class="form-control py-3" id="editar_nome" name="nome" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editar_preco" class="form-label fw-bold text-dark">Valor Mensal (R$)</label>
                                <input type="number" class="form-control py-3" id="editar_preco" name="preco"
                                    step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="editar_descricao" class="form-label fw-bold text-dark">Descrição</label>
                            <textarea class="form-control" id="editar_descricao" name="descricao" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editar_duracao" class="form-label fw-bold text-dark">Duração (dias)</label>
                                <input type="number" class="form-control py-3" id="editar_duracao" name="duracao_dias"
                                    min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editar_status" class="form-label fw-bold text-dark">Status</label>
                                <select class="form-select py-3" id="editar_status" name="status">
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="editar_inclui_personal"
                                value="1">
                            <label class="form-check-label fw-bold text-dark" for="editar_inclui_personal">
                                <i class="fas fa-dumbbell me-2 text-warning"></i> Inclui Personal Trainer
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-light rounded-bottom">
                        <button type="button" class="btn btn-secondary px-4 py-2"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-gold px-4 py-2 fw-bold">
                            <i class="fas fa-save me-2"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="modalConfirmarExclusao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold text-dark mb-3">Tem certeza que deseja excluir?</h5>
                    <p class="text-muted">O plano <strong id="nome_plano_excluir" class="text-danger"></strong> será
                        permanentemente removido.</p>
                    <p class="text-danger small"><i class="fas fa-info-circle me-1"></i>Esta ação não pode ser desfeita.
                    </p>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom">
                    <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger px-4 py-2 fw-bold" onclick="confirmarExclusao()">
                        <i class="fas fa-trash me-2"></i> Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Variável global para armazenar dados dos planos
    const planosData = <?php echo json_encode($planos ?? []); ?>;
    let planoParaExcluir = null;

    function abrirEditarPlano(planoId) {
        const plano = planosData.find(p => p.id == planoId);

        if (plano) {
            // Preencher os campos do formulário
            document.getElementById('editar_plano_id').value = plano.id;
            document.getElementById('editar_nome').value = plano.nome;
            document.getElementById('editar_descricao').value = plano.descricao || '';
            document.getElementById('editar_preco').value = plano.preco;
            document.getElementById('editar_duracao').value = plano.duracao_dias;

            // Configurar o checkbox e o hidden
            const checkbox = document.getElementById('editar_inclui_personal');
            const hidden = document.getElementById('editar_inclui_personal_hidden');
            checkbox.checked = plano.inclui_personal == 1;
            hidden.value = plano.inclui_personal == 1 ? '1' : '0';

            document.getElementById('editar_status').value = plano.status;

            // Abrir o modal
            const modal = new bootstrap.Modal(document.getElementById('modalEditarPlano'));
            modal.show();
        } else {
            alert('Plano não encontrado!');
        }
    }

    function excluirPlano(planoId, planoNome) {
        planoParaExcluir = planoId;
        document.getElementById('nome_plano_excluir').textContent = planoNome;

        const modal = new bootstrap.Modal(document.getElementById('modalConfirmarExclusao'));
        modal.show();
    }

    function confirmarExclusao() {
        if (!planoParaExcluir) return;

        const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarExclusao'));
        if (modal) modal.hide();

        const btnExcluir = document.querySelector('#modalConfirmarExclusao .btn-danger');
        const originalText = btnExcluir.innerHTML;
        btnExcluir.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Excluindo...';
        btnExcluir.disabled = true;

        fetch('planos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=excluir_plano&plano_id=' + planoParaExcluir
            })
            .then(response => response.text())
            .then(text => {
                const jsonMatch = text.match(/\{.*\}/s);
                if (jsonMatch) {
                    try {
                        const data = JSON.parse(jsonMatch[0]);
                        if (data.success) {
                            window.location.href = window.location.href.split('?')[0] + '?t=' + new Date()
                                .getTime();
                        } else {
                            alert('Erro: ' + data.message);
                            btnExcluir.innerHTML = originalText;
                            btnExcluir.disabled = false;
                        }
                    } catch (e) {
                        alert('Erro ao processar resposta do servidor');
                        btnExcluir.innerHTML = originalText;
                        btnExcluir.disabled = false;
                    }
                } else {
                    alert('Resposta inválida do servidor');
                    btnExcluir.innerHTML = originalText;
                    btnExcluir.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                alert('Erro na comunicação com o servidor');
                btnExcluir.innerHTML = originalText;
                btnExcluir.disabled = false;
            });
    }

    // Configurar evento para o formulário de edição
    document.addEventListener('DOMContentLoaded', function() {
        const formEditar = document.getElementById('formEditarPlano');
        const checkboxPersonal = document.getElementById('editar_inclui_personal');
        const hiddenPersonal = document.getElementById('editar_inclui_personal_hidden');

        if (formEditar && checkboxPersonal && hiddenPersonal) {
            // Atualizar o campo hidden quando o checkbox mudar
            checkboxPersonal.addEventListener('change', function() {
                hiddenPersonal.value = this.checked ? '1' : '0';
            });

            formEditar.addEventListener('submit', function(e) {
                e.preventDefault();

                // Garantir que o valor do hidden está atualizado
                hiddenPersonal.value = checkboxPersonal.checked ? '1' : '0';

                const formData = new URLSearchParams();
                formData.append('action', 'editar_plano');
                formData.append('plano_id', document.getElementById('editar_plano_id').value);
                formData.append('nome', document.getElementById('editar_nome').value);
                formData.append('descricao', document.getElementById('editar_descricao').value);
                formData.append('preco', document.getElementById('editar_preco').value);
                formData.append('duracao_dias', document.getElementById('editar_duracao').value);
                formData.append('status', document.getElementById('editar_status').value);
                formData.append('inclui_personal', hiddenPersonal.value);

                const btnSalvar = this.querySelector('button[type="submit"]');
                const originalText = btnSalvar.innerHTML;
                btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Salvando...';
                btnSalvar.disabled = true;

                fetch('planos.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: formData.toString()
                    })
                    .then(response => response.text())
                    .then(text => {
                        const jsonMatch = text.match(/\{.*\}/s);
                        if (jsonMatch) {
                            try {
                                const data = JSON.parse(jsonMatch[0]);
                                if (data.success) {
                                    const modal = bootstrap.Modal.getInstance(document
                                        .getElementById(
                                            'modalEditarPlano'));
                                    if (modal) modal.hide();
                                    setTimeout(() => {
                                        window.location.href = window.location.href.split(
                                            '?')[
                                            0] + '?t=' + new Date().getTime();
                                    }, 500);
                                } else {
                                    alert('Erro: ' + data.message);
                                    btnSalvar.innerHTML = originalText;
                                    btnSalvar.disabled = false;
                                }
                            } catch (e) {
                                alert('Erro ao processar resposta do servidor');
                                btnSalvar.innerHTML = originalText;
                                btnSalvar.disabled = false;
                            }
                        } else {
                            alert('Resposta inválida do servidor');
                            btnSalvar.innerHTML = originalText;
                            btnSalvar.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Erro na requisição:', error);
                        alert('Erro na comunicação com o servidor');
                        btnSalvar.innerHTML = originalText;
                        btnSalvar.disabled = false;
                    });
            });
        }
    });
    </script>

    <?php include '../../includes/footer.php'; ?>