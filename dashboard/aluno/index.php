<?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
verificarTipo('aluno');

$usuario = getUsuarioInfo();

// Funções auxiliares para formatação
function formatarData($data) {
    return date('d/m/Y', strtotime($data));
}

function formatarDataHora($dataHora, $formato = 'd/m/Y \à\s H:i') {
    return date($formato, strtotime($dataHora));
}

// Buscar matrícula ativa do aluno
$stmt = $pdo->prepare("
    SELECT m.*, p.nome as plano_nome, p.inclui_personal, p.descricao as plano_descricao
    FROM matriculas m 
    LEFT JOIN planos p ON m.plano_id = p.id 
    WHERE m.aluno_id = ? AND m.status = 'ativa'
    ORDER BY m.data_inicio DESC 
    LIMIT 1
");
$stmt->execute([$usuario['id']]);
$matricula = $stmt->fetch();

// Buscar próximos agendamentos
$stmt = $pdo->prepare("
    SELECT a.*, u.nome as personal_nome
    FROM agenda a 
    LEFT JOIN usuarios u ON a.personal_id = u.id 
    WHERE a.aluno_id = ? AND a.data_hora >= NOW() 
    ORDER BY a.data_hora ASC 
    LIMIT 5
");
$stmt->execute([$usuario['id']]);
$agendamentos = $stmt->fetchAll();

// Buscar estatísticas do aluno
try {
    // Aulas concluídas
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM agenda WHERE aluno_id = ? AND status = 'concluido'");
    $stmt->execute([$usuario['id']]);
    $aulas_concluidas = $stmt->fetchColumn();
    
    // Aulas hoje
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM agenda WHERE aluno_id = ? AND DATE(data_hora) = CURDATE()");
    $stmt->execute([$usuario['id']]);
    $aulas_hoje = $stmt->fetchColumn();
    
    $estatisticas = [
        'aulas_concluidas' => $aulas_concluidas ?: 0,
        'aulas_hoje' => $aulas_hoje ?: 0,
        'avaliacoes' => 0
    ];
    
} catch (PDOException $e) {
    $estatisticas = [
        'aulas_concluidas' => 0,
        'aulas_hoje' => 0,
        'avaliacoes' => 0
    ];
}

// Calcular dias restantes da matrícula
$dias_restantes = 0;
if ($matricula && $matricula['data_fim']) {
    $data_fim = new DateTime($matricula['data_fim']);
    $hoje = new DateTime();
    $diferenca = $hoje->diff($data_fim);
    $dias_restantes = $diferenca->days;
    
    if ($diferenca->invert) {
        $dias_restantes = 0;
    }
}
?>
<?php 
$page_title = "Dashboard Aluno";
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
    --verde-sucesso: #10b981;
    --gradiente-azul: linear-gradient(135deg, var(--azul-profundo) 0%, var(--azul-vibrante) 100%);
    --gradiente-laranja: linear-gradient(135deg, var(--laranja-queimado) 0%, var(--laranja-vibrante) 100%);
    --gradiente-dourado: linear-gradient(135deg, var(--dourado-brilhante) 0%, var(--dourado-vibrante) 100%);
    --gradiente-misto: linear-gradient(135deg, var(--azul-vibrante) 0%, var(--laranja-vibrante) 100%);
}

body {
    background-color: var(--branco-suave);
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
}

/* Cards Principais */
.dashboard-card {
    border: none;
    border-radius: 20px;
    background: var(--branco-puro);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
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
    width: 4px;
    height: 100%;
}

.dashboard-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
}

.card-primary::before {
    background: var(--azul-vibrante);
}

.card-success::before {
    background: var(--laranja-vibrante);
}

.card-warning::before {
    background: var(--dourado-brilhante);
}

.card-info::before {
    background: var(--verde-sucesso);
}

/* Cards de Estatísticas */
.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, var(--azul-vibrante) 0%, #374151 100%);
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
    border: 2px solid;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
}

.icon-primary {
    border-color: var(--azul-vibrante);
    color: var(--azul-vibrante);
    background: rgba(37, 99, 235, 0.1);
}



.icon-warning {
    border-color: var(--dourado-brilhante);
    color: var(--dourado-brilhante);
    background: rgba(217, 119, 6, 0.1);
}

.icon-info {
    border-color: var(--verde-sucesso);
    color: var(--verde-sucesso);
    background: rgba(16, 185, 129, 0.1);
}

/* Botões */
.btn-modern {
    background: var(--branco-puro);
    border: 2px solid var(--azul-vibrante);
    color: var(--azul-vibrante);
    font-weight: 600;
    padding: 10px 20px;
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

.btn-success-modern {
    background: var(--laranja-vibrante);
    border: 2px solid var(--laranja-vibrante);
    color: white;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
}

.btn-success-modern:hover {
    background: var(--laranja-queimado);
    border-color: var(--laranja-queimado);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.4);
}

/* Cards de Seção */
.section-card {
    border: none;
    border-radius: 20px;
    background: var(--branco-puro);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.section-card .card-header {
    background: var(--branco-puro);
    border-bottom: 2px solid #e5e7eb;
    padding: 1.5rem;
}

.section-card .card-header h5 {
    margin: 0;
    font-weight: 700;
    color: var(--preto-elegante);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Cards de Plano Modernos */
.plan-card {
    border: none;
    border-radius: 16px;
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
    font-size: 2rem;
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

/* Listas Modernas */
.list-group-item-modern {
    border: none;
    border-bottom: 1px solid #f3f4f6;
    padding: 1.25rem;
    transition: all 0.3s ease;
    background: var(--branco-puro);
}

.list-group-item-modern:hover {
    background: #f8fafc;
    border-left: 4px solid var(--azul-vibrante);
    margin-left: -4px;
}

.list-group-item-modern:last-child {
    border-bottom: none;
}

/* Badges */
.badge-modern {
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.75rem;
    border: 1px solid;
}

.badge-success {
    background: rgba(16, 185, 129, 0.1);
    border-color: var(--verde-sucesso);
    color: var(--verde-sucesso);
}

.badge-warning {
    background: rgba(217, 119, 6, 0.1);
    border-color: var(--dourado-brilhante);
    color: var(--dourado-brilhante);
}

.badge-secondary {
    background: rgba(107, 114, 128, 0.1);
    border-color: #6b7280;
    color: #6b7280;
}

/* Títulos */
.page-title {
    color: var(--preto-elegante);
    font-weight: 800;
    margin-bottom: 0.5rem;
    font-size: 2.5rem;
}

.page-subtitle {
    color: #6b7280;
    font-weight: 500;
    font-size: 1.1rem;
}

/* Avatares */
.avatar-sm {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.avatar-personal {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #e5e7eb;
}

/* Grids */
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

/* Elementos Flutuantes */
.floating-shape {
    position: absolute;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(37, 99, 235, 0.05) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
    z-index: -1;
}

/* Progress Bars */
.progress-modern {
    height: 8px;
    border-radius: 10px;
    background: #f3f4f6;
    overflow: hidden;
}

.progress-bar-modern {
    background: linear-gradient(90deg, var(--azul-vibrante), var(--laranja-vibrante));
    border-radius: 10px;
    transition: width 0.6s ease;
}

/* Status Indicators */
.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}

.status-active {
    background: var(--verde-sucesso);
}

.status-pending {
    background: var(--dourado-brilhante);
}

.status-inactive {
    background: #6b7280;
}

/* Animações */
@keyframes float {

    0%,
    100% {
        transform: translateY(0px) scale(1);
    }

    50% {
        transform: translateY(-10px) scale(1.05);
    }
}

/* Estado Vazio */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* Progress Container para Matrícula */
.progress-container {
    position: relative;
    z-index: 2;
}

/* Botões de Ação Rápida */
.btn-outline-primary,
.btn-outline-success,
.btn-outline-warning,
.btn-outline-info {
    border-width: 2px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-primary:hover {
    background: var(--azul-vibrante);
    color: white;
}

.btn-outline-success:hover {
    color: white;
}

.btn-outline-warning:hover {
    background: var(--dourado-vibrante);
    color: white;
}

.btn-outline-info:hover {
    background: var(--azul-suave);
    color: white;
}
</style>

<div class="container-fluid py-4">
    <!-- Floating Background Elements -->
    <div class="floating-shape" style="top: 10%; left: 5%; animation-delay: 0s;"></div>
    <div class="floating-shape" style="top: 60%; right: 8%; animation-delay: 2s;"></div>
    <div class="floating-shape" style="bottom: 20%; left: 15%; animation-delay: 4s;"></div>

    <!-- Header -->
    <div class="row mb-5">
        <div class="col-md-8">
            <h1 class="page-title">Olá, <?php echo htmlspecialchars(explode(' ', $usuario['nome'])[0]); ?>! 👋</h1>
            <p class="page-subtitle">Acompanhe sua jornada fitness e agendamentos</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="d-flex gap-2 justify-content-end">
                <?php if ($matricula): ?>
                <span class="badge badge-success px-3 py-2">
                    <i class="fas fa-check-circle me-1"></i>Plano Ativo
                </span>
                <?php else: ?>
                <a href="planos.php" class="btn btn-success-modern">
                    <i class="fas fa-crown me-2"></i>Assinar Plano
                </a>
                <?php endif; ?>
                <a href="agenda.php" class="btn btn-modern">
                    <i class="fas fa-calendar-plus me-2"></i>Agendar Aula
                </a>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas Pessoais -->
    <div class="stats-grid">
        <div class="dashboard-card card-primary">
            <div class="card-body p-4">
                <div class="stat-icon icon-primary">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <div class="stat-number"><?php echo $estatisticas['aulas_concluidas']; ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Aulas Concluídas</p>
                <small class="text-muted">Total de treinos realizados</small>
            </div>
        </div>

        <div class="dashboard-card card-success">
            <div class="card-body p-4">
                <div class="stat-icon icon-success">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-number"><?php echo $estatisticas['aulas_hoje']; ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Aulas Hoje</p>
                <small class="text-muted">Agendadas para hoje</small>
            </div>
        </div>

        <div class="dashboard-card card-warning">
            <div class="card-body p-4">
                <div class="stat-icon icon-warning">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-number"><?php echo count($agendamentos); ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Próximas Aulas</p>
                <small class="text-muted">Agendamentos futuros</small>
            </div>
        </div>

        <?php if ($matricula): ?>
        <div class="dashboard-card card-info">
            <div class="card-body p-4">
                <div class="stat-icon icon-info">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?php echo $dias_restantes; ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Dias Restantes</p>
                <small class="text-muted">Da sua matrícula</small>
                <div class="progress-modern mt-2">
                    <?php 
                    $total_dias = 60;
                    $percentual = min(100, ($dias_restantes / $total_dias) * 100);
                    ?>
                    <div class="progress-bar-modern" style="width: <?php echo $percentual; ?>%"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="row">
        <!-- Informações da Matrícula - VERSÃO MELHORADA -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="section-card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-id-card text-primary me-2"></i>Minha Matrícula</h5>
                </div>
                <div class="card-body p-0">
                    <?php if ($matricula): ?>
                    <div class="plan-card h-100 border-0">
                        <div class="plan-header" style="background: var(--gradiente-azul);">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h4 class="plan-title"><?php echo htmlspecialchars($matricula['plano_nome']); ?>
                                    </h4>
                                    <span class="plan-status status-active">
                                        <i class="fas fa-check-circle me-1 small"></i>
                                        Plano Ativo
                                    </span>
                                </div>
                                <div class="price-tag">
                                    <?php echo $dias_restantes . ' Dias'; ?>
                                </div>
                            </div>

                            <!-- Barra de Progresso -->
                            <div class="progress-container mt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-white-50">Progresso do Plano</small>
                                    <small class="text-white-50">
                                        <?php 
                                        $total_dias = 60;
                                        $dias_decorridos = $total_dias - $dias_restantes;
                                        $percentual = min(100, ($dias_decorridos / $total_dias) * 100);
                                        echo number_format($percentual, 1); ?>%
                                    </small>
                                </div>
                                <div class="progress" style="height: 6px; background: rgba(255,255,255,0.3);">
                                    <div class="progress-bar bg-warning" style="width: <?php echo $percentual; ?>%">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <?php if (!empty($matricula['plano_descricao'])): ?>
                            <p class="text-muted mb-4"><?php echo htmlspecialchars($matricula['plano_descricao']); ?>
                            </p>
                            <?php endif; ?>

                            <!-- Informações do Plano -->
                            <div class="row text-center mb-4">
                                <div class="col-4">
                                    <div class="border-end">
                                        <div class="feature-icon mx-auto mb-2 bg-success">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">
                                            <?php echo $matricula['duracao_dias'] ?? 60; ?> dias</h6>
                                        <small class="text-muted">Duração</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-end">
                                        <div
                                            class="feature-icon mx-auto mb-2 <?php echo $matricula['inclui_personal'] ? 'bg-warning' : 'bg-secondary'; ?>">
                                            <i class="fas fa-dumbbell"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">
                                            <?php echo $matricula['inclui_personal'] ? 'Incluído' : 'Não Inclui'; ?>
                                        </h6>
                                        <small class="text-muted">Personal</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="feature-icon mx-auto mb-2 bg-info">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1"><?php echo $dias_restantes; ?> dias</h6>
                                    <small class="text-muted">Restantes</small>
                                </div>
                            </div>

                            <!-- Datas -->
                            <div class="row mb-4">
                                <div class="col-6">
                                    <div class="border-end pe-3">
                                        <small class="text-muted d-block">Data de Início</small>
                                        <strong
                                            class="text-dark"><?php echo formatarData($matricula['data_inicio']); ?></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="ps-3">
                                        <small class="text-muted d-block">Data de Término</small>
                                        <strong
                                            class="text-dark"><?php echo formatarData($matricula['data_fim']); ?></strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Benefícios -->
                            <div class="benefits mb-4">
                                <h6 class="fw-bold text-dark mb-3">Benefícios Ativos:</h6>
                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <small>Acesso ilimitado à academia</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <small>Avaliação física completa</small>
                                    </div>
                                    <?php if ($matricula['inclui_personal']): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <small>Personal trainer dedicado</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <small>Treino personalizado</small>
                                    </div>
                                    <?php else: ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <small>App de treinos</small>
                                    </div>
                                    <?php endif; ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <small>Suporte nutricional</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Ações -->
                            <div class="d-grid gap-2">
                                <a href="planos.php" class="btn btn-outline-primary py-2">
                                    <i class="fas fa-sync-alt me-2"></i>Renovar Plano
                                </a>
                                <a href="historico.php" class="btn btn-outline-secondary py-2">
                                    <i class="fas fa-history me-2"></i>Ver Histórico
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-id-card"></i>
                        <h5 class="text-dark mb-2">Nenhuma Matrícula Ativa</h5>
                        <p class="text-muted mb-3">Assine um plano para começar sua jornada fitness</p>
                        <a href="planos.php" class="btn btn-success-modern">
                            <i class="fas fa-crown me-2"></i>Ver Planos Disponíveis
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Próximos Agendamentos -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="section-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt text-success me-2"></i>Próximos Agendamentos</h5>
                    <a href="agenda.php" class="btn btn-sm btn-modern">Ver Todos</a>
                </div>
                <div class="card-body p-0">
                    <?php if ($agendamentos): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($agendamentos as $agenda): ?>
                        <div class="list-group-item list-group-item-modern">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-center">
                                    <div
                                        class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="fas fa-user-tie text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 text-dark fw-semibold">
                                            <?php echo htmlspecialchars($agenda['personal_nome']); ?></h6>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo formatarDataHora($agenda['data_hora'], 'd/m/Y \à\s H:i'); ?>
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-stopwatch me-1"></i>
                                            <?php echo $agenda['duracao_minutos']; ?> minutos
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-<?php 
                                        echo $agenda['status'] == 'agendado' ? 'success' : 
                                             ($agenda['status'] == 'pendente' ? 'warning' : 'secondary'); 
                                    ?>">
                                        <span class="status-indicator status-<?php echo $agenda['status']; ?>"></span>
                                        <?php echo ucfirst($agenda['status']); ?>
                                    </span>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <?php 
                                            $data_agenda = new DateTime($agenda['data_hora']);
                                            $hoje = new DateTime();
                                            $diferenca = $hoje->diff($data_agenda);
                                            
                                            if ($diferenca->days == 0) {
                                                echo 'Hoje';
                                            } elseif ($diferenca->days == 1) {
                                                echo 'Amanhã';
                                            } else {
                                                echo "Em {$diferenca->days} dias";
                                            }
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h5 class="text-dark mb-2">Nenhum Agendamento</h5>
                        <p class="text-muted mb-3">Você não tem aulas agendadas</p>
                        <a href="agenda.php" class="btn btn-success-modern">
                            <i class="fas fa-calendar-plus me-2"></i>Agendar Primeira Aula
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="row mt-2">
        <div class="col-12">
            <div class="section-card rounded-2 ">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt text-warning me-2"></i>Ações Rápidas</h5>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <a href="agenda.php" class="btn btn-outline-primary w-100 h-100 py-3">
                                <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                                <div class="fw-bold">Agendar Aula</div>
                                <small class="text-muted">Marcar treino</small>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="perfil.php" class="btn btn-outline-success w-100 h-100 py-3">
                                <i class="fas fa-user fa-2x mb-2"></i>
                                <div class="fw-bold">Meu Perfil</div>
                                <small class="text-muted">Editar dados</small>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="historico.php" class="btn btn-outline-warning w-100 h-100 py-3">
                                <i class="fas fa-history fa-2x mb-2"></i>
                                <div class="fw-bold">Histórico</div>
                                <small class="text-muted">Aulas anteriores</small>
                            </a>
                        </div>
                        <div class="col-md-3 col-6">
                            <a href="planos.php" class="btn btn-outline-info w-100 h-100 py-3">
                                <i class="fas fa-crown fa-2x mb-2"></i>
                                <div class="fw-bold">Meu Plano</div>
                                <small class="text-muted">Ver detalhes</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Animação para os cards ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.dashboard-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';

        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

// Efeitos interativos para os cards de plano
document.addEventListener('DOMContentLoaded', function() {
    const planCards = document.querySelectorAll('.plan-card');

    planCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<?php include '../../includes/footer.php'; ?>