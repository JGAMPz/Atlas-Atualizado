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

<style>
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

/* Cards Modernos */
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

/* Botões Modernos */
.btn-modern {
    background: var(--azul-vibrante);
    border: none;
    color: white;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-modern:hover {
    background: var(--azul-profundo);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
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

/* Tabela Moderna */
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

.table-modern tbody td {
    padding: 1rem;
    border-color: #f3f4f6;
    vertical-align: middle;
}

/* Badges Modernos */
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

.badge-danger {
    background: rgba(220, 38, 38, 0.1);
    border-color: #dc2626;
    color: #dc2626;
}

.badge-secondary {
    background: rgba(107, 114, 128, 0.1);
    border-color: #6b7280;
    color: #6b7280;
}

/* Modal Moderno */
.modal-modern .modal-content {
    border-radius: 20px;
    border: none;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.modal-modern .modal-header {
    background: var(--azul-profundo);
    color: white;
    border-radius: 20px 20px 0 0;
    border: none;
    padding: 1.5rem;
}

.modal-modern .modal-header .btn-close {
    filter: invert(1);
}

/* Cards de Personal */
.personal-card {
    border: none;
    border-radius: 16px;
    background: var(--branco-puro);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    cursor: pointer;
}

.personal-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.personal-card.selected {
    border: 2px solid var(--azul-vibrante);
    background: rgba(37, 99, 235, 0.05);
}

/* Agenda do Personal */
.agenda-slot {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px;
    margin: 4px 0;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.agenda-slot:hover {
    border-color: var(--azul-vibrante);
    background: rgba(37, 99, 235, 0.05);
}

.agenda-slot.disponivel {
    border-color: var(--verde-sucesso);
    background: rgba(16, 185, 129, 0.05);
}

.agenda-slot.agendado {
    border-color: #dc2626;
    background: rgba(220, 38, 38, 0.05);
    cursor: not-allowed;
    opacity: 0.6;
}

.agenda-slot.selecionado {
    border-color: var(--azul-vibrante);
    background: var(--azul-vibrante);
    color: white;
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

/* Formulários Modernos */
.form-control-modern {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px 16px;
    transition: all 0.3s ease;
}

.form-control-modern:focus {
    border-color: var(--azul-vibrante);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Status Indicator */
.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}

.status-agendado {
    background: var(--verde-sucesso);
}

.status-pendente {
    background: var(--dourado-brilhante);
}

.status-cancelado {
    background: #dc2626;
}

.status-disponivel {
    background: #6b7280;
}

/* Loading */
.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f4f6;
    border-radius: 50%;
    border-top-color: var(--azul-vibrante);
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-md-8">
            <h1 class="page-title">Minha Agenda 📅</h1>
            <p class="page-subtitle">Gerencie seus agendamentos com personal trainers</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-success-modern" data-bs-toggle="modal" data-bs-target="#modalEscolherPersonal">
                <i class="fas fa-plus me-2"></i>Novo Agendamento
            </button>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="card-body text-center p-4">
                    <div class="text-primary mb-2">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <h3 class="fw-bold text-dark">
                        <?php echo count(array_filter($agendamentos, fn($a) => $a['status'] == 'agendado')); ?>
                    </h3>
                    <p class="text-muted mb-0">Agendados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="card-body text-center p-4">
                    <div class="text-warning mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h3 class="fw-bold text-dark">
                        <?php echo count(array_filter($agendamentos, fn($a) => $a['status'] == 'pendente')); ?>
                    </h3>
                    <p class="text-muted mb-0">Pendentes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="card-body text-center p-4">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h3 class="fw-bold text-dark">
                        <?php echo count(array_filter($agendamentos, fn($a) => $a['status'] == 'concluido')); ?>
                    </h3>
                    <p class="text-muted mb-0">Concluídos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="dashboard-card">
                <div class="card-body text-center p-4">
                    <div class="text-danger mb-2">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                    <h3 class="fw-bold text-dark">
                        <?php echo count(array_filter($agendamentos, fn($a) => $a['status'] == 'cancelado')); ?>
                    </h3>
                    <p class="text-muted mb-0">Cancelados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Agendamentos -->
    <div class="dashboard-card">
        <div class="card-header bg-transparent border-0 py-4">
            <h4 class="card-title mb-0 text-dark">
                <i class="fas fa-list me-2 text-primary ps-3"></i>Meus Agendamentos
            </h4>
        </div>
        <div class="card-body p-0">
            <?php if ($agendamentos): ?>
            <div class="table-responsive">
                <table class="table table-modern table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Personal Trainer</th>
                            <th>Duração</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agendamentos as $agenda): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">
                                    <?php echo date('d/m/Y', strtotime($agenda['data_hora'])); ?>
                                </div>
                                <small class="text-muted">
                                    <?php echo date('H:i', strtotime($agenda['data_hora'])); ?>
                                </small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <i class="fas fa-user-tie text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark"><?php echo $agenda['personal_nome']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark"><?php echo $agenda['duracao_minutos']; ?> min</span>
                            </td>
                            <td>
                                <span class="badge-modern badge-<?php 
                                    echo $agenda['status'] == 'agendado' ? 'success' : 
                                         ($agenda['status'] == 'pendente' ? 'warning' : 
                                         ($agenda['status'] == 'cancelado' ? 'danger' : 'secondary')); 
                                ?>">
                                    <span class="status-indicator status-<?php echo $agenda['status']; ?>"></span>
                                    <?php echo ucfirst($agenda['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($agenda['status'] == 'agendado'): ?>
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="cancelarAgendamento(<?php echo $agenda['id']; ?>)">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </button>
                                <?php elseif ($agenda['status'] == 'pendente'): ?>
                                <span class="text-muted">Aguardando confirmação</span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h5 class="text-dark mb-2">Nenhum agendamento encontrado</h5>
                <p class="text-muted mb-4">Comece agendando sua primeira aula!</p>
                <button class="btn btn-success-modern" data-bs-toggle="modal" data-bs-target="#modalEscolherPersonal">
                    <i class="fas fa-plus me-2"></i>Fazer Primeiro Agendamento
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Escolher Personal -->
<div class="modal fade modal-modern" id="modalEscolherPersonal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-dumbbell me-2"></i>Escolha um Personal Trainer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="listaPersonais">
                    <?php foreach ($personais as $personal): ?>
                    <div class="col-md-6">
                        <div class="personal-card p-3" data-personal-id="<?php echo $personal['id']; ?>"
                            data-personal-nome="<?php echo htmlspecialchars($personal['nome']); ?>">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-user-tie text-primary fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1"><?php echo $personal['nome']; ?></h6>
                                    <small class="text-muted">Personal Trainer</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Agenda do Personal -->
<div class="modal fade modal-modern" id="modalVerAgenda" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-alt me-2"></i>Agenda do <span id="nomePersonal"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="dataConsulta" class="form-label fw-semibold">Selecione a Data:</label>
                        <input type="date" class="form-control form-control-modern" id="dataConsulta"
                            onchange="carregarAgendaPersonal()">
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-end h-100">
                            <button class="btn btn-modern w-100" onclick="carregarAgendaPersonal()">
                                <i class="fas fa-sync me-2"></i>Atualizar Agenda
                            </button>
                        </div>
                    </div>
                </div>

                <div id="loadingAgenda" class="text-center py-4" style="display: none;">
                    <div class="loading mx-auto mb-2"></div>
                    <p class="text-muted">Carregando agenda...</p>
                </div>

                <div id="agendaPersonal" class="row">
                    <!-- Agenda será carregada aqui -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Agendamento -->
<div class="modal fade modal-modern" id="modalConfirmarAgendamento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-check me-2"></i>Confirmar Agendamento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalhesAgendamento">
                    <!-- Detalhes do agendamento serão preenchidos aqui -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success-modern" onclick="confirmarAgendamento()">
                    <i class="fas fa-check me-2"></i>Confirmar Agendamento
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let personalSelecionado = null;
let personalNomeSelecionado = null;
let slotSelecionado = null;

// Função para selecionar personal
function selecionarPersonal(personalId, personalNome) {
    personalSelecionado = personalId;
    personalNomeSelecionado = personalNome;

    console.log('Personal selecionado:', personalId, personalNome);

    // Remover seleção anterior
    document.querySelectorAll('.personal-card').forEach(card => {
        card.classList.remove('selected');
    });

    // Adicionar seleção atual
    event.currentTarget.classList.add('selected');

    // Fechar modal atual
    const modalEscolher = bootstrap.Modal.getInstance(document.getElementById('modalEscolherPersonal'));
    modalEscolher.hide();

    // Abrir modal da agenda após um pequeno delay
    setTimeout(() => {
        const modalAgenda = new bootstrap.Modal(document.getElementById('modalVerAgenda'));
        modalAgenda.show();

        // Configurar data atual e nome do personal
        document.getElementById('nomePersonal').textContent = personalNome;
        const hoje = new Date().toISOString().split('T')[0];
        document.getElementById('dataConsulta').value = hoje;

        // Carregar agenda
        carregarAgendaPersonal();
    }, 300);
}

function carregarAgendaPersonal() {
    const data = document.getElementById('dataConsulta').value;
    if (!data || !personalSelecionado) {
        console.log('Dados faltando:', {
            data,
            personalSelecionado
        });
        return;
    }

    const loading = document.getElementById('loadingAgenda');
    const agenda = document.getElementById('agendaPersonal');

    loading.style.display = 'block';
    agenda.innerHTML = '';

    const formData = new FormData();
    formData.append('personal_id', personalSelecionado);
    formData.append('data', data);
    formData.append('action', 'consultar_agenda_personal');

    console.log('Carregando agenda para personal:', personalSelecionado, 'data:', data);

    fetch('../../includes/functions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na rede');
            }
            return response.json();
        })
        .then(data => {
            console.log('Resposta da agenda:', data);
            loading.style.display = 'none';

            if (data.success) {
                exibirAgendaPersonal(data.agenda);
            } else {
                agenda.innerHTML = `
                    <div class="col-12 text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                        <p class="text-muted">${data.message || 'Erro ao carregar agenda'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            loading.style.display = 'none';
            agenda.innerHTML = `
                <div class="col-12 text-center py-4">
                    <i class="fas fa-exclamation-circle fa-2x text-danger mb-3"></i>
                    <p class="text-muted">Erro ao carregar agenda</p>
                </div>
            `;
        });
}

function exibirAgendaPersonal(agenda) {
    const container = document.getElementById('agendaPersonal');

    if (!agenda || agenda.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-4">
                <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                <h6 class="text-dark mb-2">Nenhum horário disponível</h6>
                <p class="text-muted">Não há horários disponíveis para esta data.</p>
            </div>
        `;
        return;
    }

    let html = '';
    agenda.forEach(slot => {
        // Converter para horário local
        const dataHora = new Date(slot.data_hora);
        const hora = dataHora.toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit'
        });

        const classe = slot.status === 'disponivel' ? 'disponivel' : 'agendado';
        const texto = slot.status === 'disponivel' ? 'Disponível' : 'Ocupado';

        html += `
            <div class="col-md-4 mb-3">
                <div class="agenda-slot ${classe}" 
                     onclick="${slot.status === 'disponivel' ? `selecionarSlot('${slot.data_hora}', '${hora}')` : ''}">
                    <div class="text-center">
                        <div class="fw-bold ${slot.status === 'disponivel' ? 'text-success' : 'text-danger'}">
                            ${hora}
                        </div>
                        <small class="${slot.status === 'disponivel' ? 'text-success' : 'text-danger'}">
                            ${texto}
                        </small>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function selecionarSlot(dataHora, hora) {
    slotSelecionado = dataHora;

    console.log('Slot selecionado:', dataHora, hora);

    // Remover seleção anterior
    document.querySelectorAll('.agenda-slot').forEach(slot => {
        slot.classList.remove('selecionado');
    });

    // Adicionar seleção atual
    event.currentTarget.classList.add('selecionado');

    // Fechar modal da agenda e abrir modal de confirmação
    const modalAgenda = bootstrap.Modal.getInstance(document.getElementById('modalVerAgenda'));
    modalAgenda.hide();

    setTimeout(() => {
        const modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmarAgendamento'));
        modalConfirmar.show();

        const dataFormatada = new Date(slotSelecionado).toLocaleDateString('pt-BR');

        document.getElementById('detalhesAgendamento').innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Confirme os detalhes do seu agendamento
            </div>
            <div class="row">
                <div class="col-12 mb-3">
                    <strong>Personal Trainer:</strong>
                    <p class="mb-0">${personalNomeSelecionado}</p>
                </div>
                <div class="col-6">
                    <strong>Data:</strong>
                    <p class="mb-0">${dataFormatada}</p>
                </div>
                <div class="col-6">
                    <strong>Horário:</strong>
                    <p class="mb-0">${hora}</p>
                </div>
                <div class="col-12 mt-2">
                    <strong>Duração:</strong>
                    <p class="mb-0">60 minutos</p>
                </div>
            </div>
        `;
    }, 300);
}

function confirmarAgendamento() {
    if (!personalSelecionado || !slotSelecionado) {
        alert('Erro: Dados do agendamento incompletos');
        return;
    }

    console.log('Confirmando agendamento:', {
        personalSelecionado,
        slotSelecionado
    });

    const formData = new FormData();
    formData.append('personal_id', personalSelecionado);
    formData.append('data_hora', slotSelecionado);
    formData.append('duracao_minutos', 60);
    formData.append('aluno_id', <?php echo $usuario['id']; ?>);
    formData.append('action', 'agendar');

    fetch('../../includes/functions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Resposta do agendamento:', data);
            if (data.success) {
                alert('Agendamento realizado com sucesso!');
                const modalConfirmar = bootstrap.Modal.getInstance(document.getElementById(
                    'modalConfirmarAgendamento'));
                modalConfirmar.hide();
                location.reload();
            } else {
                alert('Erro: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao realizar agendamento');
        });
}

function cancelarAgendamento(agendaId) {
    if (confirm('Tem certeza que deseja cancelar este agendamento?')) {
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
                    alert('Agendamento cancelado com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                alert('Erro ao cancelar agendamento');
            });
    }
}

// Inicialização quando o documento estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Configurar data mínima como hoje
    const hoje = new Date().toISOString().split('T')[0];
    if (document.getElementById('dataConsulta')) {
        document.getElementById('dataConsulta').min = hoje;
        document.getElementById('dataConsulta').value = hoje;
    }

    // Adicionar event listeners para os cards de personal
    document.querySelectorAll('.personal-card').forEach(card => {
        card.addEventListener('click', function() {
            const personalId = this.getAttribute('data-personal-id');
            const personalNome = this.getAttribute('data-personal-nome');
            selecionarPersonal(personalId, personalNome);
        });
    });

    // Adicionar event listener para o botão de atualizar agenda
    document.getElementById('dataConsulta')?.addEventListener('change', carregarAgendaPersonal);
});

// Função auxiliar para debug - pode remover depois
function debugModal() {
    console.log('Modal Escolher Personal:', document.getElementById('modalEscolherPersonal'));
    console.log('Modal Ver Agenda:', document.getElementById('modalVerAgenda'));
    console.log('Personal cards:', document.querySelectorAll('.personal-card').length);
}
</script>
<?php include '../../includes/footer.php'; ?>