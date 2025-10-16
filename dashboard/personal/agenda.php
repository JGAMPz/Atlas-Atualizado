 <?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
verificarTipo('personal');

$usuario = getUsuarioInfo();

// Processar criação de horários
if ($_POST['action'] ?? '' === 'criar_horarios') {
    $data = $_POST['data'];
    $horarios = $_POST['horarios'] ?? [];
    
    foreach ($horarios as $horario) {
        $data_hora = $data . ' ' . $horario . ':00';
        
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO agenda (personal_id, data_hora, status) 
            VALUES (?, ?, 'disponivel')
        ");
        $stmt->execute([$usuario['id'], $data_hora]);
    }
    
    header('Location: agenda.php?success=1');
    exit;
}

// Buscar agenda da semana
$semana_inicio = date('Y-m-d', strtotime('monday this week'));
$semana_fim = date('Y-m-d', strtotime('sunday this week'));

$stmt = $pdo->prepare("
    SELECT a.*, u.nome as aluno_nome, u.telefone as aluno_telefone
    FROM agenda a
    LEFT JOIN usuarios u ON a.aluno_id = u.id
    WHERE a.personal_id = ? AND DATE(a.data_hora) BETWEEN ? AND ?
    ORDER BY a.data_hora
");
$stmt->execute([$usuario['id'], $semana_inicio, $semana_fim]);
$agenda_semana = $stmt->fetchAll();

// Agrupar por dia
$agenda_por_dia = [];
foreach ($agenda_semana as $agenda) {
    $dia = date('Y-m-d', strtotime($agenda['data_hora']));
    $agenda_por_dia[$dia][] = $agenda;
}
?>
 <?php 
$page_title = "Minha Agenda - Personal";
include '../../includes/header.php'; 
?>

 <div class="d-flex justify-content-between align-items-center mb-4">
     <h2>Minha Agenda</h2>
     <div>
         <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCriarHorarios">
             <i class="fas fa-plus"></i> Criar Horários
         </button>
     </div>
 </div>

 <?php if (isset($_GET['success'])): ?>
 <div class="alert alert-success">
     Horários criados com sucesso!
 </div>
 <?php endif; ?>

 <!-- Navegação da Semana -->
 <div class="card mb-4">
     <div class="card-body">
         <div class="d-flex justify-content-between align-items-center">
             <h5 class="mb-0">Semana de <?php echo date('d/m', strtotime($semana_inicio)); ?> a
                 <?php echo date('d/m', strtotime($semana_fim)); ?></h5>
             <div>
                 <a href="agenda.php?week=prev" class="btn btn-outline-primary btn-sm">← Semana Anterior</a>
                 <a href="agenda.php?week=current" class="btn btn-outline-secondary btn-sm">Esta Semana</a>
                 <a href="agenda.php?week=next" class="btn btn-outline-primary btn-sm">Próxima Semana →</a>
             </div>
         </div>
     </div>
 </div>

 <!-- Agenda Semanal -->
 <div class="row">
     <?php 
    $dias_semana = [
        'Segunda' => date('Y-m-d', strtotime('monday this week')),
        'Terça' => date('Y-m-d', strtotime('tuesday this week')),
        'Quarta' => date('Y-m-d', strtotime('wednesday this week')),
        'Quinta' => date('Y-m-d', strtotime('thursday this week')),
        'Sexta' => date('Y-m-d', strtotime('friday this week')),
        'Sábado' => date('Y-m-d', strtotime('saturday this week')),
        'Domingo' => date('Y-m-d', strtotime('sunday this week'))
    ];
    
    foreach ($dias_semana as $dia_nome => $dia_data): 
        $agenda_dia = $agenda_por_dia[$dia_data] ?? [];
    ?>
     <div class="col-md-4 col-lg-3 mb-4">
         <div class="card h-100">
             <div class="card-header">
                 <h6 class="card-title mb-0">
                     <?php echo $dia_nome; ?><br>
                     <small><?php echo date('d/m', strtotime($dia_data)); ?></small>
                 </h6>
             </div>
             <div class="card-body">
                 <?php if ($agenda_dia): ?>
                 <?php foreach ($agenda_dia as $horario): ?>
                 <div
                     class="mb-3 p-2 border rounded <?php echo $horario['status'] === 'agendado' ? 'bg-light' : ''; ?>">
                     <div class="d-flex justify-content-between align-items-start">
                         <div>
                             <strong><?php echo date('H:i', strtotime($horario['data_hora'])); ?></strong>
                             <small class="d-block"><?php echo $horario['duracao_minutos']; ?>min</small>
                         </div>
                         <span class="badge bg-<?php 
                                        echo $horario['status'] === 'agendado' ? 'success' : 
                                             ($horario['status'] === 'disponivel' ? 'primary' : 'secondary');
                                    ?>">
                             <?php echo ucfirst($horario['status']); ?>
                         </span>
                     </div>

                     <?php if ($horario['status'] === 'agendado' && $horario['aluno_nome']): ?>
                     <div class="mt-2">
                         <small>
                             <strong>Aluno:</strong> <?php echo $horario['aluno_nome']; ?><br>
                             <strong>Tel:</strong> <?php echo $horario['aluno_telefone']; ?>
                         </small>
                     </div>
                     <div class="mt-2">
                         <button class="btn btn-sm btn-outline-danger"
                             onclick="cancelarAgendamento(<?php echo $horario['id']; ?>)">
                             Cancelar
                         </button>
                     </div>
                     <?php elseif ($horario['status'] === 'disponivel'): ?>
                     <div class="mt-2">
                         <small class="text-muted">Disponível</small>
                     </div>
                     <?php endif; ?>
                 </div>
                 <?php endforeach; ?>
                 <?php else: ?>
                 <p class="text-muted text-center">Nenhum horário</p>
                 <?php endif; ?>
             </div>
         </div>
     </div>
     <?php endforeach; ?>
 </div>

 <!-- Modal Criar Horários -->
 <div class="modal fade" id="modalCriarHorarios" tabindex="-1">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title">Criar Horários Disponíveis</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
             </div>
             <form method="POST">
                 <input type="hidden" name="action" value="criar_horarios">

                 <div class="modal-body">
                     <div class="mb-3">
                         <label for="data" class="form-label">Data</label>
                         <input type="date" class="form-control" id="data" name="data"
                             value="<?php echo date('Y-m-d'); ?>" required>
                     </div>

                     <div class="mb-3">
                         <label class="form-label">Horários Disponíveis</label>
                         <div class="row">
                             <?php 
                            $horarios_disponiveis = [
                                '08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'
                            ];
                            
                            foreach ($horarios_disponiveis as $horario): 
                            ?>
                             <div class="col-6 mb-2">
                                 <div class="form-check">
                                     <input class="form-check-input" type="checkbox" name="horarios[]"
                                         value="<?php echo $horario; ?>"
                                         id="horario_<?php echo str_replace(':', '', $horario); ?>">
                                     <label class="form-check-label"
                                         for="horario_<?php echo str_replace(':', '', $horario); ?>">
                                         <?php echo $horario; ?>
                                     </label>
                                 </div>
                             </div>
                             <?php endforeach; ?>
                         </div>
                     </div>
                 </div>

                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                     <button type="submit" class="btn btn-primary">Criar Horários</button>
                 </div>
             </form>
         </div>
     </div>
 </div>

 <script>
function cancelarAgendamento(agendaId) {
    if (confirm('Deseja cancelar este agendamento?')) {
        const formData = new FormData();
        formData.append('agenda_id', agendaId);
        formData.append('action', 'cancelar_agendamento_personal');

        fetch('../../includes/functions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Agendamento cancelado!');
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            });
    }
}
 </script>

 <?php include '../../includes/footer.php'; ?>