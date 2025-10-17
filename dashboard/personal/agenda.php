<?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
verificarTipo('personal');

$usuario = getUsuarioInfo();

// Buscar agenda do personal
$stmt = $pdo->prepare("
    SELECT * FROM agenda 
    WHERE personal_id = ? 
    ORDER BY data_hora DESC
");
$stmt->execute([$usuario['id']]);
$agenda = $stmt->fetchAll();

$page_title = "Minha Agenda - Personal";
include '../../includes/header.php';
?>

<style>
/* (Use os mesmos estilos modernos da agenda do aluno) */
:root {
    --azul-profundo: #1e3a8a;
    --azul-vibrante: #2563eb;
    --laranja-vibrante: #f97316;
    --verde-sucesso: #10b981;
    --dourado-brilhante: #d97706;
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
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
}

.btn-success-modern {
    background: var(--laranja-vibrante);
    border: none;
    color: white;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
}

.btn-success-modern:hover {
    background: #ea580c;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.4);
}

.table-modern {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.table-modern thead th {
    background: var(--azul-profundo);
    color: white;
    border: none;
    padding: 1rem;
    font-weight: 600;
}

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
</style>

<div class="container-fluid py-4">
    <div class="row mb-5">
        <div class="col-md-8">
            <h1 class="page-title">Minha Agenda 📅</h1>
            <p class="page-subtitle">Gerencie seus horários disponíveis para alunos</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-success-modern" data-bs-toggle="modal" data-bs-target="#modalCriarHorarios">
                <i class="fas fa-plus me-2"></i>Adicionar Horários
            </button>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header bg-transparent border-0 py-4">
            <h4 class="card-title mb-0 text-dark">
                <i class="fas fa-calendar-alt me-2 text-primary"></i>Horários Disponíveis
            </h4>
        </div>
        <div class="card-body p-0">
            <?php if ($agenda): ?>
            <div class="table-responsive">
                <table class="table table-modern table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Status</th>
                            <th>Aluno</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agenda as $slot): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">
                                    <?php echo date('d/m/Y', strtotime($slot['data_hora'])); ?>
                                </div>
                                <small class="text-muted">
                                    <?php echo date('H:i', strtotime($slot['data_hora'])); ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge-modern badge-<?php 
                                    echo $slot['status'] == 'disponivel' ? 'success' : 
                                         ($slot['status'] == 'agendado' ? 'warning' : 'secondary'); 
                                ?>">
                                    <?php echo ucfirst($slot['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($slot['aluno_id']): ?>
                                <?php 
                                $stmt_aluno = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
                                $stmt_aluno->execute([$slot['aluno_id']]);
                                $aluno = $stmt_aluno->fetch();
                                echo $aluno ? $aluno['nome'] : 'Aluno não encontrado';
                                ?>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($slot['status'] == 'disponivel'): ?>
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="excluirHorario(<?php echo $slot['id']; ?>)">
                                    <i class="fas fa-trash me-1"></i>Excluir
                                </button>
                                <?php elseif ($slot['status'] == 'agendado'): ?>
                                <span class="text-muted">Aula agendada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                <h5 class="text-dark mb-2">Nenhum horário cadastrado</h5>
                <p class="text-muted mb-4">Adicione seus horários disponíveis para começar a receber agendamentos!</p>
                <button class="btn btn-success-modern" data-bs-toggle="modal" data-bs-target="#modalCriarHorarios">
                    <i class="fas fa-plus me-2"></i>Adicionar Primeiros Horários
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Criar Horários -->
<div class="modal fade" id="modalCriarHorarios" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-clock me-2"></i>Adicionar Horários Disponíveis
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCriarHorarios">
                    <div class="mb-3">
                        <label for="data_horarios" class="form-label">Data</label>
                        <input type="date" class="form-control" id="data_horarios" name="data_horarios" required
                            min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Horários Disponíveis</label>
                        <div class="row g-2">
                            <?php 
                            $horarios_padrao = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
                            foreach ($horarios_padrao as $horario): ?>
                            <div class="col-md-6">
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success-modern" onclick="criarHorarios()">
                    <i class="fas fa-save me-2"></i>Salvar Horários
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cancelar Agendamento -->
<div class="modal fade" id="modalCancelarAgendamento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle me-2 text-danger"></i>Cancelar Agendamento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCancelarAgendamento">
                    <input type="hidden" id="agendamento_id_cancelar" name="agendamento_id">

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Atenção:</strong> Esta ação notificará o aluno sobre o cancelamento.
                    </div>

                    <div class="mb-3">
                        <label for="motivo_cancelamento" class="form-label">
                            <strong>Motivo do Cancelamento *</strong>
                        </label>
                        <textarea class="form-control" id="motivo_cancelamento" name="motivo" rows="4"
                            placeholder="Explique o motivo do cancelamento para o aluno..." required></textarea>
                        <small class="text-muted">Ex: Problemas de saúde, emergência familiar, etc.</small>
                    </div>

                    <div id="detalhes_agendamento">
                        <!-- Detalhes do agendamento serão preenchidos aqui -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarCancelamentoPersonal()">
                    <i class="fas fa-times me-2"></i>Cancelar Agendamento
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function criarHorarios() {
    const form = document.getElementById('formCriarHorarios');
    const formData = new FormData(form);

    // Adicionar personal_id e action
    formData.append('personal_id', <?php echo $usuario['id']; ?>);
    formData.append('action', 'criar_horarios_personal');

    fetch('../../includes/functions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Horários criados com sucesso!');
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            alert('Erro ao criar horários');
        });
}

function excluirHorario(horario_id) {
    if (confirm('Deseja excluir este horário?')) {
        const formData = new FormData();
        formData.append('horario_id', horario_id);
        formData.append('action', 'excluir_horario_personal');

        fetch('../../includes/functions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Horário excluído com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            });
    }
}
// Função para abrir modal de cancelamento
function abrirCancelamento(agendamento_id, aluno_nome, data_hora) {
    document.getElementById('agendamento_id_cancelar').value = agendamento_id;

    // Preencher detalhes do agendamento
    document.getElementById('detalhes_agendamento').innerHTML = `
        <div class="mt-3 p-3 bg-light rounded">
            <h6>Detalhes do Agendamento:</h6>
            <p class="mb-1"><strong>Aluno:</strong> ${aluno_nome}</p>
            <p class="mb-1"><strong>Data/Hora:</strong> ${data_hora}</p>
        </div>
    `;

    // Limpar motivo anterior
    document.getElementById('motivo_cancelamento').value = '';

    const modal = new bootstrap.Modal(document.getElementById('modalCancelarAgendamento'));
    modal.show();
}

// Função para confirmar cancelamento
function confirmarCancelamentoPersonal() {
    const agendamento_id = document.getElementById('agendamento_id_cancelar').value;
    const motivo = document.getElementById('motivo_cancelamento').value;

    if (!motivo.trim()) {
        alert('Por favor, informe o motivo do cancelamento.');
        return;
    }

    const formData = new FormData();
    formData.append('agenda_id', agendamento_id);
    formData.append('personal_id', <?php echo $usuario['id']; ?>);
    formData.append('motivo', motivo);
    formData.append('action', 'personal_cancelar_agendamento');

    fetch('../../includes/functions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            alert('Erro ao cancelar agendamento');
        });
}
</script>

<?php include '../../includes/footer.php'; ?>