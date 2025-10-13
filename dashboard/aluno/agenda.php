 <?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
verificarTipo('aluno');

$usuario = getUsuarioInfo();

// Buscar personais disponíveis
$stmt = $pdo->query("SELECT * FROM usuarios WHERE tipo = 'personal' AND status = 'ativo'");
$personais = $stmt->fetchAll();

// Buscar agendamentos do aluno
$stmt = $pdo->prepare("
    SELECT a.*, u.nome as personal_nome 
    FROM agenda a 
    LEFT JOIN usuarios u ON a.personal_id = u.id 
    WHERE a.aluno_id = ? 
    ORDER BY a.data_hora DESC
");
$stmt->execute([$usuario['id']]);
$agendamentos = $stmt->fetchAll();
?>
 <?php 
$page_title = "Agenda - Aluno";
include '../../includes/header.php'; 
?>

 <div class="row">
     <div class="col-md-8">
         <h2>Minha Agenda</h2>
         <p class="text-muted">Agende horários com personal trainers</p>
     </div>
     <div class="col-md-4 text-end">
         <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgendar">
             <i class="fas fa-plus"></i> Novo Agendamento
         </button>
     </div>
 </div>

 <!-- Lista de Agendamentos -->
 <div class="card mt-4">
     <div class="card-header">
         <h5 class="card-title mb-0">Meus Agendamentos</h5>
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
                         <th>Ações</th>
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
                                             ($agenda['status'] == 'pendente' ? 'warning' : 
                                             ($agenda['status'] == 'cancelado' ? 'danger' : 'secondary')); 
                                    ?>">
                                 <?php echo ucfirst($agenda['status']); ?>
                             </span>
                         </td>
                         <td>
                             <?php if ($agenda['status'] == 'agendado'): ?>
                             <button class="btn btn-sm btn-outline-danger"
                                 onclick="cancelarAgendamento(<?php echo $agenda['id']; ?>)">
                                 Cancelar
                             </button>
                             <?php endif; ?>
                         </td>
                     </tr>
                     <?php endforeach; ?>
                 </tbody>
             </table>
         </div>
         <?php else: ?>
         <p class="text-muted">Nenhum agendamento encontrado.</p>
         <?php endif; ?>
     </div>
 </div>

 <!-- Modal Agendamento -->
 <div class="modal fade" id="modalAgendar" tabindex="-1">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title">Novo Agendamento</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
             </div>
             <div class="modal-body">
                 <form id="formAgendar">
                     <div class="mb-3">
                         <label for="personal_id" class="form-label">Personal Trainer</label>
                         <select class="form-select" id="personal_id" name="personal_id" required>
                             <option value="">Selecione um personal...</option>
                             <?php foreach ($personais as $personal): ?>
                             <option value="<?php echo $personal['id']; ?>">
                                 <?php echo $personal['nome']; ?>
                             </option>
                             <?php endforeach; ?>
                         </select>
                     </div>
                     <div class="mb-3">
                         <label for="data_agendamento" class="form-label">Data</label>
                         <input type="date" class="form-control" id="data_agendamento" name="data_agendamento" required>
                     </div>
                     <div class="mb-3">
                         <label for="hora_agendamento" class="form-label">Hora</label>
                         <input type="time" class="form-control" id="hora_agendamento" name="hora_agendamento" required>
                     </div>
                     <div class="mb-3">
                         <label for="duracao" class="form-label">Duração (minutos)</label>
                         <select class="form-select" id="duracao" name="duracao">
                             <option value="60">60 minutos</option>
                             <option value="90">90 minutos</option>
                             <option value="120">120 minutos</option>
                         </select>
                     </div>
                 </form>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                 <button type="button" class="btn btn-primary" onclick="agendarAula()">Agendar</button>
             </div>
         </div>
     </div>
 </div>

 <script>
function agendarAula() {
    const form = document.getElementById('formAgendar');
    const formData = new FormData(form);

    // Adicionar aluno_id
    formData.append('aluno_id', <?php echo $usuario['id']; ?>);
    formData.append('action', 'agendar');

    fetch('../../includes/functions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Aula agendada com sucesso!');
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao agendar aula');
        });
}

function cancelarAgendamento(agendaId) {
    if (confirm('Deseja cancelar este agendamento?')) {
        const formData = new FormData();
        formData.append('agenda_id', agendaId);
        formData.append('action', 'cancelar_agendamento');

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