<?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
verificarTipo('admin');

$usuario = getUsuarioInfo();

// Buscar dados para relatórios
try {
    // Total de usuários por tipo
    $stmt = $pdo->query("
        SELECT 
            tipo,
            COUNT(*) as total,
            SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) as ativos
        FROM usuarios 
        GROUP BY tipo
    ");
    $usuarios_por_tipo = $stmt->fetchAll();

    // Total de planos
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_planos,
            SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) as planos_ativos
        FROM planos
    ");
    $planos_info = $stmt->fetch();

    // Matrículas ativas
    $stmt = $pdo->query("
        SELECT COUNT(*) as total_matriculas 
        FROM matriculas 
        WHERE status = 'ativa'
    ");
    $matriculas_ativas = $stmt->fetchColumn();

    // Pagamentos recentes (usando data_inicio como referência)
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_pagamentos,
            COALESCE(SUM(p.valor), 0) as valor_total
        FROM pagamentos p
        WHERE p.status = 'pago'
    ");
    $pagamentos_info = $stmt->fetch();

    // Agendamentos do mês
    $stmt = $pdo->query("
        SELECT COUNT(*) as total_agendamentos
        FROM agenda 
        WHERE status = 'agendado' 
        AND MONTH(data_hora) = MONTH(CURRENT_DATE())
        AND YEAR(data_hora) = YEAR(CURRENT_DATE())
    ");
    $agendamentos_mes = $stmt->fetchColumn();

} catch (PDOException $e) {
    error_log("Erro ao buscar dados para relatórios: " . $e->getMessage());
    $usuarios_por_tipo = [];
    $planos_info = ['total_planos' => 0, 'planos_ativos' => 0];
    $matriculas_ativas = 0;
    $pagamentos_info = ['total_pagamentos' => 0, 'valor_total' => 0];
    $agendamentos_mes = 0;
}

$page_title = "Relatórios - Admin";
include '../../includes/header.php';
?>

<style>
:root {
    --azul-profundo: #1e3a8a;
    --azul-vibrante: #2563eb;
    --laranja-vibrante: #f97316;
    --dourado-brilhante: #d97706;
    --preto-elegante: #111827;
    --branco-puro: #ffffff;
    --cinza-suave: #f8fafc;
}

body {
    background-color: var(--cinza-suave);
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
}

.dashboard-card {
    border: none;
    border-radius: 16px;
    background: var(--branco-puro);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
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
    background: linear-gradient(135deg, var(--azul-vibrante) 0%, var(--laranja-vibrante) 100%);
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
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
    color: white;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, var(--azul-vibrante) 0%, var(--azul-profundo) 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, var(--laranja-vibrante) 0%, var(--dourado-brilhante) 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #06b6d4 0%, #0ea5e9 100%);
}

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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.chart-container {
    background: var(--branco-puro);
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 2rem;
}

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
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.table-modern tbody tr:last-child td {
    border-bottom: none;
}

.table-modern tbody tr:hover {
    background-color: rgba(59, 130, 246, 0.05);
}

.fade-in {
    animation: fadeIn 0.5s ease-out;
}

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
</style>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-5 fade-in">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Relatórios</h1>
                    <p class="page-subtitle">Estatísticas e insights do sistema</p>
                </div>
                <div class="d-flex gap-3">
                    <button class="btn btn-outline-primary">
                        <i class="fas fa-download me-2"></i>Exportar
                    </button>
                    <button class="btn btn-primary">
                        <i class="fas fa-sync-alt me-2"></i>Atualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid fade-in">
        <!-- Total de Usuários -->
        <div class="dashboard-card">
            <div class="card-body p-4">
                <div class="stat-icon bg-gradient-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number text-primary">
                    <?php 
                    $total_usuarios = array_sum(array_column($usuarios_por_tipo, 'total'));
                    echo $total_usuarios; 
                    ?>
                </div>
                <p class="card-text fw-semibold text-dark mb-1">Total de Usuários</p>
                <small class="text-muted">Todos os usuários cadastrados</small>
            </div>
        </div>

        <!-- Matrículas Ativas -->
        <div class="dashboard-card">
            <div class="card-body p-4">
                <div class="stat-icon bg-gradient-success">
                    <i class="fas fa-id-card"></i>
                </div>
                <div class="stat-number text-success">
                    <?php echo $matriculas_ativas; ?>
                </div>
                <p class="card-text fw-semibold text-dark mb-1">Matrículas Ativas</p>
                <small class="text-muted">Alunos ativos no sistema</small>
            </div>
        </div>

        <!-- Planos Ativos -->
        <div class="dashboard-card">
            <div class="card-body p-4">
                <div class="stat-icon bg-gradient-warning">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-number text-warning">
                    <?php echo $planos_info['planos_ativos'] ?? 0; ?>
                </div>
                <p class="card-text fw-semibold text-dark mb-1">Planos Ativos</p>
                <small class="text-muted">Disponíveis para contratação</small>
            </div>
        </div>

        <!-- Receita Total -->
        <div class="dashboard-card">
            <div class="card-body p-4">
                <div class="stat-icon bg-gradient-info">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-number text-info">
                    R$ <?php echo number_format($pagamentos_info['valor_total'] ?? 0, 2, ',', '.'); ?>
                </div>
                <p class="card-text fw-semibold text-dark mb-1">Receita Total</p>
                <small class="text-muted">Valor arrecadado</small>
            </div>
        </div>
    </div>

    <div class="row fade-in">
        <!-- Distribuição de Usuários por Tipo -->
        <div class="col-lg-6 mb-4">
            <div class="chart-container">
                <h4 class="fw-bold mb-4">
                    <i class="fas fa-chart-pie me-2 text-primary"></i>
                    Distribuição de Usuários por Tipo
                </h4>
                <?php if (!empty($usuarios_por_tipo)): ?>
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Total</th>
                                <th>Ativos</th>
                                <th>Percentual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_geral = array_sum(array_column($usuarios_por_tipo, 'total'));
                            foreach ($usuarios_por_tipo as $tipo): 
                                $percentual = $total_geral > 0 ? ($tipo['total'] / $total_geral) * 100 : 0;
                            ?>
                            <tr>
                                <td>
                                    <span class="fw-bold text-capitalize">
                                        <?php echo $tipo['tipo']; ?>
                                    </span>
                                </td>
                                <td><?php echo $tipo['total']; ?></td>
                                <td>
                                    <span class="badge bg-success">
                                        <?php echo $tipo['ativos']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                            <div class="progress-bar bg-primary"
                                                style="width: <?php echo $percentual; ?>%">
                                            </div>
                                        </div>
                                        <span class="text-muted small">
                                            <?php echo number_format($percentual, 1); ?>%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Nenhum dado disponível para exibir</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Estatísticas Gerais -->
        <div class="col-lg-6 mb-4">
            <div class="chart-container">
                <h4 class="fw-bold mb-4">
                    <i class="fas fa-chart-bar me-2 text-success"></i>
                    Estatísticas Gerais
                </h4>
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="card bg-light border-0">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                                <h5 class="fw-bold"><?php echo $agendamentos_mes; ?></h5>
                                <small class="text-muted">Agendamentos este mês</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card bg-light border-0">
                            <div class="card-body text-center">
                                <i class="fas fa-credit-card fa-2x text-success mb-2"></i>
                                <h5 class="fw-bold"><?php echo $pagamentos_info['total_pagamentos'] ?? 0; ?></h5>
                                <small class="text-muted">Pagamentos processados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card bg-light border-0">
                            <div class="card-body text-center">
                                <i class="fas fa-box fa-2x text-warning mb-2"></i>
                                <h5 class="fw-bold"><?php echo $planos_info['total_planos'] ?? 0; ?></h5>
                                <small class="text-muted">Total de planos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card bg-light border-0">
                            <div class="card-body text-center">
                                <i class="fas fa-percentage fa-2x text-info mb-2"></i>
                                <h5 class="fw-bold">
                                    <?php 
                                    $taxa_ativos = $total_usuarios > 0 ? 
                                        (array_sum(array_column($usuarios_por_tipo, 'ativos')) / $total_usuarios) * 100 : 0;
                                    echo number_format($taxa_ativos, 1); 
                                    ?>%
                                </h5>
                                <small class="text-muted">Taxa de usuários ativos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumo Financeiro -->
    <div class="row fade-in">
        <div class="col-12">
            <div class="chart-container">
                <h4 class="fw-bold mb-4">
                    <i class="fas fa-chart-line me-2 text-success"></i>
                    Resumo Financeiro
                </h4>
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 bg-success text-white">
                            <div class="card-body">
                                <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                                <h4 class="fw-bold">
                                    R$ <?php echo number_format($pagamentos_info['valor_total'] ?? 0, 2, ',', '.'); ?>
                                </h4>
                                <p class="mb-0">Receita Total</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 bg-primary text-white">
                            <div class="card-body">
                                <i class="fas fa-receipt fa-2x mb-2"></i>
                                <h4 class="fw-bold"><?php echo $pagamentos_info['total_pagamentos'] ?? 0; ?></h4>
                                <p class="mb-0">Transações</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 bg-info text-white">
                            <div class="card-body">
                                <i class="fas fa-calculator fa-2x mb-2"></i>
                                <h4 class="fw-bold">
                                    R$ <?php 
                                    $media = ($pagamentos_info['total_pagamentos'] ?? 0) > 0 ? 
                                        ($pagamentos_info['valor_total'] ?? 0) / ($pagamentos_info['total_pagamentos'] ?? 1) : 0;
                                    echo number_format($media, 2, ',', '.'); 
                                    ?>
                                </h4>
                                <p class="mb-0">Ticket Médio</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 bg-warning text-white">
                            <div class="card-body">
                                <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                <h4 class="fw-bold"><?php echo $matriculas_ativas; ?></h4>
                                <p class="mb-0">Clientes Ativos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>