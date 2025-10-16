<?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
verificarTipo('admin');

$usuario = getUsuarioInfo();

// Estatísticas gerais
$stmt = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM usuarios WHERE tipo = 'aluno' AND status = 'ativo') as total_alunos,
        (SELECT COUNT(*) FROM usuarios WHERE tipo = 'personal' AND status = 'ativo') as total_personais,
        (SELECT COUNT(*) FROM matriculas WHERE status = 'ativa') as matriculas_ativas,
        (SELECT SUM(p.preco)  -- Usando a coluna 'preco' da tabela 'planos'
         FROM matriculas m
         JOIN planos p ON m.plano_id = p.id
         WHERE m.status = 'ativa' 
           AND MONTH(m.data_inicio) = MONTH(CURRENT_DATE())) as receita_mensal,
        (SELECT COUNT(*) FROM agenda WHERE DATE(data_hora) = CURDATE() AND status = 'agendado') as aulas_hoje,
        (SELECT COUNT(*) FROM usuarios WHERE DATE(data_cadastro) = CURDATE()) as novos_cadastros
");
$estatisticas = $stmt->fetch();


// Últimos cadastros
$stmt = $pdo->query("
    SELECT nome, email, tipo, data_cadastro 
    FROM usuarios 
    ORDER BY data_cadastro DESC 
    LIMIT 5
");
$ultimos_cadastros = $stmt->fetchAll();

// Próximas aulas
$stmt = $pdo->query("
    SELECT a.data_hora, u1.nome as aluno_nome, u2.nome as personal_nome
    FROM agenda a
    JOIN usuarios u1 ON a.aluno_id = u1.id
    JOIN usuarios u2 ON a.personal_id = u2.id
    WHERE a.data_hora >= NOW() AND a.status = 'agendado'
    ORDER BY a.data_hora ASC
    LIMIT 5
");
$proximas_aulas = $stmt->fetchAll();
?>
<?php 
$page_title = "Dashboard Admin";
include '../../includes/header.php'; 
?>

<style>
:root {
    --azul-profundo: #1e3a8a;
    --azul-vibrante: #2563eb;
    --laranja-queimado: #ea580c;
    --laranja-vibrante: #f97316;
    --dourado-brilhante: #d97706;
    --dourado-suave: #f59e0b;
    --preto-elegante: #111827;
    --branco-puro: #ffffff;
    --cinza-suave: #f8fafc;
}

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

.card-danger::before {
    background: #dc2626;
}

.card-secondary::before {
    background: #6b7280;
}

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

.icon-success {
    border-color: var(--laranja-vibrante);
    color: var(--laranja-vibrante);
    background: rgba(249, 115, 22, 0.1);
}

.icon-warning {
    border-color: var(--dourado-brilhante);
    color: var(--dourado-brilhante);
    background: rgba(217, 119, 6, 0.1);
}

.icon-danger {
    border-color: #dc2626;
    color: #dc2626;
    background: rgba(220, 38, 38, 0.1);
}

.icon-secondary {
    border-color: #6b7280;
    color: #6b7280;
    background: rgba(107, 114, 128, 0.1);
}

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

.badge-modern {
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.75rem;
    border: 1px solid;
}

.badge-aluno {
    background: rgba(37, 99, 235, 0.1);
    border-color: var(--azul-vibrante);
    color: var(--azul-vibrante);
}

.badge-personal {
    background: rgba(249, 115, 22, 0.1);
    border-color: var(--laranja-vibrante);
    color: var(--laranja-vibrante);
}

.badge-admin {
    background: rgba(217, 119, 6, 0.1);
    border-color: var(--dourado-brilhante);
    color: var(--dourado-brilhante);
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

.avatar-sm {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

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

.floating-shape {
    position: absolute;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(37, 99, 235, 0.05) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
    z-index: -1;
}

@keyframes float {

    0%,
    100% {
        transform: translateY(0px) scale(1);
    }

    50% {
        transform: translateY(-10px) scale(1.05);
    }
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
            <h1 class="page-title">Painel Administrativo</h1>
            <p class="page-subtitle">Visão geral do sistema ATLAS em tempo real</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="d-flex gap-2 justify-content-end">
                <a href="planos.php" class="btn btn-modern">
                    <i class="fas fa-cubes me-2"></i>Planos
                </a>
                <a href="usuarios.php" class="btn btn-modern">
                    <i class="fas fa-users me-2"></i>Usuários
                </a>
                <a href="relatorios.php" class="btn btn-modern">
                    <i class="fas fa-chart-bar me-2"></i>Relatórios
                </a>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="stats-grid">
        <div class="dashboard-card card-primary">
            <div class="card-body p-4">
                <div class="stat-icon icon-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $estatisticas['total_alunos']; ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Alunos Ativos</p>
                <small class="text-muted">Total no sistema</small>
            </div>
        </div>

        <div class="dashboard-card card-success">
            <div class="card-body p-4">
                <div class="stat-icon icon-success">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <div class="stat-number"><?php echo $estatisticas['total_personais']; ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Personais</p>
                <small class="text-muted">Treinadores ativos</small>
            </div>
        </div>

        <div class="dashboard-card card-warning">
            <div class="card-body p-4">
                <div class="stat-icon icon-warning">
                    <i class="fas fa-id-card"></i>
                </div>
                <div class="stat-number"><?php echo $estatisticas['matriculas_ativas']; ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Matrículas</p>
                <small class="text-muted">Ativas no momento</small>
            </div>
        </div>

        <div class="dashboard-card card-primary">
            <div class="card-body p-4">
                <div class="stat-icon icon-primary">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-number">
                    R$ <?php echo number_format(($estatisticas['receita_mensal'] ?? 0), 0, ',', '.'); ?>
                </div>
                <p class="card-text fw-semibold text-dark mb-1">Receita Mensal</p>
                <small class="text-muted"><?php echo date('F Y'); ?></small>
            </div>
        </div>

        <div class="dashboard-card card-danger">
            <div class="card-body p-4">
                <div class="stat-icon icon-danger">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-number"><?php echo $estatisticas['aulas_hoje']; ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Aulas Hoje</p>
                <small class="text-muted">Agendadas</small>
            </div>
        </div>

        <div class="dashboard-card card-secondary">
            <div class="card-body p-4">
                <div class="stat-icon icon-secondary">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-number"><?php echo $estatisticas['novos_cadastros']; ?></div>
                <p class="card-text fw-semibold text-dark mb-1">Novos Hoje</p>
                <small class="text-muted">Cadastros do dia</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Últimos Cadastros -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="section-card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-clock text-primary"></i>Últimos Cadastros</h5>
                </div>
                <div class="card-body p-0">
                    <?php if ($ultimos_cadastros): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($ultimos_cadastros as $cadastro): ?>
                        <div class="list-group-item list-group-item-modern">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div
                                        class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-dark fw-semibold"><?php echo $cadastro['nome']; ?></h6>
                                        <small class="text-muted"><?php echo $cadastro['email']; ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-modern badge-<?php 
                                        echo $cadastro['tipo'] === 'admin' ? 'admin' : 
                                             ($cadastro['tipo'] === 'personal' ? 'personal' : 'aluno');
                                    ?>">
                                        <?php echo ucfirst($cadastro['tipo']); ?>
                                    </span>
                                    <div class="mt-1">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo formatDate($cadastro['data_cadastro'], 'd/m H:i'); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Nenhum cadastro recente.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Próximas Aulas -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="section-card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt text-success"></i>Próximas Aulas</h5>
                </div>
                <div class="card-body p-0">
                    <?php if ($proximas_aulas): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($proximas_aulas as $aula): ?>
                        <div class="list-group-item list-group-item-modern">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-center">
                                    <div
                                        class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="fas fa-dumbbell text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 text-dark fw-semibold"><?php echo $aula['aluno_nome']; ?></h6>
                                        <small class="text-muted">
                                            <i class="fas fa-user-tie me-1"></i>
                                            com <?php echo $aula['personal_nome']; ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold text-dark">
                                        <?php echo formatDateTime($aula['data_hora'], 'd/m'); ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo formatDateTime($aula['data_hora'], 'H:i'); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Nenhuma aula agendada.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>