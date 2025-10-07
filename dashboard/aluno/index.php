<?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
verificarTipo('aluno');

$usuario = getUsuarioInfo();

// Buscar matrícula ativa do aluno
$stmt = $pdo->prepare("
    SELECT m.*, p.nome as plano_nome, p.inclui_personal 
    FROM matriculas m 
    LEFT JOIN planos p ON m.plano_id = p.id 
    WHERE m.aluno_id = ? AND m.status = 'ativa'
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
?>
<?php 
$page_title = "Dashboard Aluno";
include '../../includes/header.php'; 
?>

<div class="row">
    <div class="col-md-8">
        <h2>Bem-vindo, <?php echo $usuario['nome']; ?>!</h2>
        <p class="text-muted">Acompanhe suas atividades e agendamentos</p>
    </div>
    <div class="col-md-4 text-end">
        <?php if ($matricula): ?>
        <span class="badge bg-success">Matrícula Ativa - <?php echo $matricula['plano_nome']; ?></span>
        <?php else: ?>
        <a href="planos.php" class="btn btn-primary">Assinar Plano</a>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-4">
    <!-- Card Status Matrícula -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Status da Matrícula</h5>
                        <p class="card-text">
                            <?php if ($matricula): ?>
                            <span class="text-success">Ativa</span>
                            <?php else: ?>
                            <span class="text-danger">Inativa</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-id-card fa-2x text-primary"></i>
                    </div>
                </div>
                <?php if ($matricula): ?>
                <small class="text-muted">Plano: <?php echo $matricula['plano_nome']; ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Card Próximas Aulas -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Próximas Aulas</h5>
                        <p class="card-text"><?php echo count($agendamentos); ?> agendadas</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-alt fa-2x text-success"></i>
                    </div>
                </div>
                <a href="agenda.php" class="btn btn-sm btn-outline-success">Ver Agenda</a>
            </div>
        </div>
    </div>

    <!-- Card Personal Trainer -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Personal Trainer</h5>
                        <p class="card-text">
                            <?php if ($matricula && $matricula['personal_id']): ?>
                            <span class="text-success">Atribuído</span>
                            <?php elseif ($matricula && $matricula['inclui_personal']): ?>
                            <span class="text-warning">A definir</span>
                            <?php else: ?>
                            <span class="text-muted">Não incluso</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-dumbbell fa-2x text-warning"></i>
                    </div>
                </div>
                <?php if ($matricula && !$matricula['personal_id'] && $matricula['inclui_personal']): ?>
                <a href="agenda.php" class="btn btn-sm btn-outline-warning">Escolher Personal</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Próximos Agendamentos -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Próximos Agendamentos</h5>
            </div>
            <div class="card-body">
                <?php if ($agendamentos): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Data/Hora</th>
                                <th>Personal</th>
                                <th>Duração</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agendamentos as $agenda): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($agenda['data_hora'])); ?></td>
                                <td><?php echo $agenda['personal_nome']; ?></td>
                                <td><?php echo $agenda['duracao_minutos']; ?> min</td>
                                <td>
                                    <span class="badge bg-<?php 
                                                echo $agenda['status'] == 'agendado' ? 'success' : 
                                                     ($agenda['status'] == 'pendente' ? 'warning' : 'secondary'); 
                                            ?>">
                                        <?php echo ucfirst($agenda['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">Nenhum agendamento encontrado.</p>
                <a href="agenda.php" class="btn btn-primary">Agendar Aula</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>