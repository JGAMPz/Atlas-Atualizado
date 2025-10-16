 <?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
verificarTipo('personal');

$usuario = getUsuarioInfo();

// Estatísticas do personal
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT aluno_id) as total_alunos,
        COUNT(*) as total_agendamentos,
        SUM(CASE WHEN status = 'agendado' THEN 1 ELSE 0 END) as agendamentos_ativos,
        SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as aulas_concluidas
    FROM agenda 
    WHERE personal_id = ? AND DATE(data_hora) >= CURDATE() - INTERVAL 30 DAY
");
$stmt->execute([$usuario['id']]);
$estatisticas = $stmt->fetch();

// Próximos agendamentos
$stmt = $pdo->prepare("
    SELECT a.*, u.nome as aluno_nome, u.telefone as aluno_telefone
    FROM agenda a
    LEFT JOIN usuarios u ON a.aluno_id = u.id
    WHERE a.personal_id = ? AND a.data_hora >= NOW() AND a.status = 'agendado'
    ORDER BY a.data_hora ASC
    LIMIT 5
");
$stmt->execute([$usuario['id']]);
$proximos_agendamentos = $stmt->fetchAll();

// Alunos ativos
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.nome, u.email, u.telefone
    FROM agenda a
    JOIN usuarios u ON a.aluno_id = u.id
    WHERE a.personal_id = ? AND a.status = 'agendado' AND a.data_hora >= NOW() - INTERVAL 7 DAY
    ORDER BY u.nome
    LIMIT 8
");
$stmt->execute([$usuario['id']]);
$alunos_ativos = $stmt->fetchAll();
?>
 <?php 
$page_title = "Dashboard Personal";
include '../../includes/header.php'; 
?>

 <div class="row">
     <div class="col-md-8">
         <h2>Bem-vindo, <?php echo $usuario['nome']; ?>!</h2>
         <p class="text-muted">Gerencie sua agenda e acompanhe seus alunos</p>
     </div>
     <div class="col-md-4 text-end">
         <a href="agenda.php" class="btn btn-primary me-2">
             <i class="fas fa-calendar-plus"></i> Nova Agenda
         </a>
         <a href="alunos.php" class="btn btn-success">
             <i class="fas fa-users"></i> Meus Alunos
         </a>
     </div>
 </div>

 <!-- Cards de Estatísticas -->
 <div class="row mt-4">
     <div class="col-md-3 mb-4">
         <div class="card dashboard-card success">
             <div class="card-body">
                 <div class="d-flex justify-content-between">
                     <div>
                         <h3 class="text-success"><?php echo $estatisticas['total_alunos']; ?></h3>
                         <p class="card-text">Alunos Ativos</p>
                     </div>
                     <div class="align-self-center">
                         <i class="fas fa-users fa-2x text-success"></i>
                     </div>
                 </div>
             </div>
         </div>
     </div>

     <div class="col-md-3 mb-4">
         <div class="card dashboard-card info">
             <div class="card-body">
                 <div class="d-flex justify-content-between">
                     <div>
                         <h3 class="text-info"><?php echo $estatisticas['agendamentos_ativos']; ?></h3>
                         <p class="card-text">Agendamentos Ativos</p>
                     </div>
                     <div class="align-self-center">
                         <i class="fas fa-calendar-check fa-2x text-info"></i>
                     </div>
                 </div>
             </div>
         </div>
     </div>

     <div class="col-md-3 mb-4">
         <div class="card dashboard-card warning">
             <div class="card-body">
                 <div class="d-flex justify-content-between">
                     <div>
                         <h3 class="text-warning"><?php echo $estatisticas['total_agendamentos']; ?></h3>
                         <p class="card-text">Total de Aulas (30 dias)</p>
                     </div>
                     <div class="align-self-center">
                         <i class="fas fa-chart-line fa-2x text-warning"></i>
                     </div>
                 </div>
             </div>
         </div>
     </div>

     <div class="col-md-3 mb-4">
         <div class="card dashboard-card primary">
             <div class="card-body">
                 <div class="d-flex justify-content-between">
                     <div>
                         <h3 class="text-primary"><?php echo $estatisticas['aulas_concluidas']; ?></h3>
                         <p class="card-text">Aulas Concluídas</p>
                     </div>
                     <div class="align-self-center">
                         <i class="fas fa-check-circle fa-2x text-primary"></i>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>

 <div class="row mt-4">
     <!-- Próximos Agendamentos -->
     <div class="col-md-6">
         <div class="card">
             <div class="card-header d-flex justify-content-between align-items-center">
                 <h5 class="card-title mb-0">Próximos Agendamentos</h5>
                 <a href="agenda.php" class="btn btn-sm btn-outline-primary">Ver Todos</a>
             </div>
             <div class="card-body">
                 <?php if ($proximos_agendamentos): ?>
                 <div class="list-group list-group-flush">
                     <?php foreach ($proximos_agendamentos as $agenda): ?>
                     <div class="list-group-item d-flex justify-content-between align-items-center">
                         <div>
                             <h6 class="mb-1"><?php echo $agenda['aluno_nome']; ?></h6>
                             <small class="text-muted">
                                 <i class="fas fa-clock"></i>
                                 <?php echo date('d/m H:i', strtotime($agenda['data_hora'])); ?>
                                 (<?php echo $agenda['duracao_minutos']; ?>min)
                             </small>
                         </div>
                         <span class="badge bg-success">Agendado</span>
                     </div>
                     <?php endforeach; ?>
                 </div>
                 <?php else: ?>
                 <p class="text-muted">Nenhum agendamento futuro.</p>
                 <?php endif; ?>
             </div>
         </div>
     </div>

     <!-- Meus Alunos -->
     <div class="col-md-6">
         <div class="card">
             <div class="card-header d-flex justify-content-between align-items-center">
                 <h5 class="card-title mb-0">Meus Alunos</h5>
                 <a href="alunos.php" class="btn btn-sm btn-outline-success">Ver Todos</a>
             </div>
             <div class="card-body">
                 <?php if ($alunos_ativos): ?>
                 <div class="row">
                     <?php foreach ($alunos_ativos as $aluno): ?>
                     <div class="col-6 mb-3">
                         <div class="d-flex align-items-center">
                             <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                 style="width: 40px; height: 40px;">
                                 <?php echo strtoupper(substr($aluno['nome'], 0, 1)); ?>
                             </div>
                             <div>
                                 <h6 class="mb-0"><?php echo $aluno['nome']; ?></h6>
                                 <small class="text-muted"><?php echo $aluno['telefone']; ?></small>
                             </div>
                         </div>
                     </div>
                     <?php endforeach; ?>
                 </div>
                 <?php else: ?>
                 <p class="text-muted">Nenhum aluno ativo no momento.</p>
                 <?php endif; ?>
             </div>
         </div>
     </div>
 </div>

 <!-- Ações Rápidas -->
 <div class="row mt-4">
     <div class="col-12">
         <div class="card">
             <div class="card-header">
                 <h5 class="card-title mb-0">Ações Rápidas</h5>
             </div>
             <div class="card-body">
                 <div class="row">
                     <div class="col-md-3 text-center">
                         <a href="agenda.php?action=create" class="btn btn-outline-primary btn-lg w-100 mb-2">
                             <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                             Criar Horários
                         </a>
                     </div>
                     <div class="col-md-3 text-center">
                         <a href="alunos.php" class="btn btn-outline-success btn-lg w-100 mb-2">
                             <i class="fas fa-user-plus fa-2x mb-2"></i><br>
                             Gerenciar Alunos
                         </a>
                     </div>
                     <div class="col-md-3 text-center">
                         <button class="btn btn-outline-info btn-lg w-100 mb-2" onclick="abrirModalAvaliacao()">
                             <i class="fas fa-clipboard-check fa-2x mb-2"></i><br>
                             Nova Avaliação
                         </button>
                     </div>
                     <div class="col-md-3 text-center">
                         <button class="btn btn-outline-warning btn-lg w-100 mb-2" onclick="abrirModalTreino()">
                             <i class="fas fa-dumbbell fa-2x mb-2"></i><br>
                             Criar Treino
                         </button>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>

 <?php include '../../includes/footer.php'; ?>