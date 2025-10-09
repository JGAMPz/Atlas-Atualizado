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
        (SELECT SUM(valor_contratado) FROM matriculas WHERE status = 'ativa' AND MONTH(data_criacao) = MONTH(CURRENT_DATE())) as receita_mensal,
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

<div class="row">
    <div class="col-md-8">
        <h2>Painel Administrativo</h2>
        <p class="text-muted">Visão geral do sistema</p>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <a href="planos.php" class="btn btn-primary">Planos</a>
            <a href="usuarios.php" class="btn btn-success">Usuários</a>
            <a href="relatorios.php" class="btn btn-info">Relatórios</a>
        </div>
    </div>
</div>

<!-- Cards de Estatísticas -->
<div class="row mt-4">
    <div class="col-md-2 mb-4">
        <div class="card dashboard-card primary">
            <div class="card-body text-center">
                <h3 class="text-primary"><?php echo $estatisticas['total_alunos']; ?></h3>
                <p class="card-text">Alunos</p>
                <i class="fas fa-users fa-2x text-primary"></i>
            </div>
        </div>
    </div>

    <div class="col-md-2 mb-4">
        <div class="card dashboard-card success">
            <div class="card-body text-center">
                <h3 class="text-success"><?php echo $estatisticas['total_personais']; ?></h3>
                <p class="card-text">Personais</p>
                <i class="fas fa-dumbbell fa-2x text-success"></i>
            </div>
        </div>
    </div>

    <div class="col-md-2 mb-4">
        <div class="card dashboard-card warning">
            <div class="card-body text-center">
                <h3 class="text-warning"><?php echo $estatisticas['matriculas_ativas']; ?></h3>
                <p class="card-text">Matrículas Ativas</p>
                <i class="fas fa-id-card fa-2x text-warning"></i>
            </div>
        </div>
    </div>

    <!-- Receita Mensal -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Receita Mensal</h5>
                        <p class="card-text h4">
                            R$ <?php echo number_format(($receita_mensal ?? 0), 2, ',', '.'); ?>
                        </p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                    </div>
                </div>
                <small class="text-muted"><?php echo date('F Y'); ?></small>
            </div>
        </div>
    </div>

    <div class="col-md-2 mb-4">
        <div class="card dashboard-card danger">
            <div class="card-body text-center">
                <h3 class="text-danger"><?php echo $estatisticas['aulas_hoje']; ?></h3>
                <p class="card-text">Aulas Hoje</p>
                <i class="fas fa-calendar-day fa-2x text-danger"></i>
            </div>
        </div>
    </div>

    <div class="col-md-2 mb-4">
        <div class="card dashboard-card secondary">
            <div class="card-body text-center">
                <h3 class="text-secondary"><?php echo $estatisticas['novos_cadastros']; ?></h3>
                <p class="card-text">Novos Hoje</p>
                <i class="fas fa-user-plus fa-2x text-secondary"></i>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Últimos Cadastros -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Últimos Cadastros</h5>
            </div>
            <div class="card-body">
                <?php if ($ultimos_cadastros): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($ultimos_cadastros as $cadastro): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0"><?php echo $cadastro['nome']; ?></h6>
                            <small class="text-muted"><?php echo $cadastro['email']; ?></small>
                        </div>
                        <div>
                            <span class="badge bg-<?php 
                                        echo $cadastro['tipo'] === 'admin' ? 'danger' : 
                                             ($cadastro['tipo'] === 'personal' ? 'success' : 'primary');
                                    ?>">
                                <?php echo ucfirst($cadastro['tipo']); ?>
                            </span>
                            <small class="text-muted d-block">
                                <?php echo formatDate($cadastro['data_cadastro'], 'd/m H:i'); ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">Nenhum cadastro recente.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Próximas Aulas -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Próximas Aulas</h5>
            </div>
            <div class="card-body">
                <?php if ($proximas_aulas): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($proximas_aulas as $aula): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1"><?php echo $aula['aluno_nome']; ?></h6>
                                <small class="text-muted">com <?php echo $aula['personal_nome']; ?></small>
                            </div>
                            <small class="text-muted">
                                <?php echo formatDateTime($aula['data_hora'], 'd/m H:i'); ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">Nenhuma aula agendada.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>