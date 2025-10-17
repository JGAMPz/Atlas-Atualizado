<?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
verificarTipo('aluno');

$usuario = getUsuarioInfo();

// Buscar planos disponíveis
try {
    $stmt = $pdo->query("SELECT * FROM planos WHERE status = 'ativo' ORDER BY preco");
    $planos = $stmt->fetchAll();
} catch (PDOException $e) {
    $planos = [];
}

$page_title = "Planos Disponíveis";
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
    --gradiente-azul: linear-gradient(135deg, var(--azul-profundo) 0%, var(--azul-vibrante) 100%);
    --gradiente-laranja: linear-gradient(135deg, var(--laranja-queimado) 0%, var(--laranja-vibrante) 100%);
    --gradiente-dourado: linear-gradient(135deg, var(--dourado-brilhante) 0%, var(--dourado-vibrante) 100%);
    --gradiente-misto: linear-gradient(135deg, var(--azul-vibrante) 0%, var(--laranja-vibrante) 100%);
}

body {
    background-color: var(--branco-suave);
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
}

/* Cards e Layout */
.dashboard-card {
    border: none;
    border-radius: 20px;
    background: var(--branco-puro);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
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
    width: 5px;
    height: 100%;
    background: var(--gradiente-dourado);
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    background: var(--azul-vibrante);
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
    background: var(--azul-profundo);
    color: white;
}

/* Botões */
.btn-gold {
    background: var(--gradiente-azul);
    border: none;
    color: white;
    font-weight: 700;
    padding: 14px 28px;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 6px 15px rgba(6, 27, 217, 0.3);
}

.btn-gold:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(6, 80, 217, 0.4);
    color: white;
}

.btn-success-modern {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    color: white;
    font-weight: 700;
    padding: 16px 24px;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
}

.btn-success-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
    color: white;
}

.btn-primary-modern {
    background: linear-gradient(135deg, var(--azul-vibrante) 0%, var(--azul-profundo) 100%);
    border: none;
    color: white;
    font-weight: 700;
    padding: 16px 24px;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
}

.btn-primary-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
    color: white;
}

.btn-info-modern {
    background: linear-gradient(135deg, #06b6d4 0%, #0ea5e9 100%);
    border: none;
    color: white;
    font-weight: 700;
    padding: 16px 24px;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 6px 15px rgba(6, 182, 212, 0.3);
}

.btn-info-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(6, 182, 212, 0.4);
    color: white;
}

/* Cards de Plano */
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
    font-size: 2.5rem;
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

/* Modais */
.modal-header-gradient {
    background: var(--gradiente-azul);
    color: white;
}

.modal-content {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

/* Títulos */
.page-title {
    color: var(--preto-elegante);
    font-weight: 800;
    margin-bottom: 0.5rem;
    font-size: 2.5rem;
    background: var(--preto-elegante);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-subtitle {
    color: var(--cinza-medio);
    font-weight: 500;
    font-size: 1.1rem;
}

/* Grid de Planos */
.plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .plans-grid {
        grid-template-columns: 1fr;
    }

    .page-title {
        font-size: 2rem;
    }
}

/* Badges */
.badge-modern {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
    border: 1px solid;
}

.badge-popular {
    background: linear-gradient(135deg, var(--dourado-vibrante) 0%, var(--laranja-vibrante) 100%);
    border-color: var(--dourado-vibrante);
    color: white;
}

/* Estado Vazio */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state i {
    font-size: 4rem;
    background: var(--gradiente-misto);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
}

/* Formulários */
.form-control,
.form-select {
    border: 2px solid var(--cinza-suave);
    border-radius: 12px;
    padding: 12px 16px;
    transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--azul-vibrante);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Alertas */
.alert {
    border-radius: 12px;
    border: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
    border-left: 4px solid #16a34a;
}

.alert-danger {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
    border-left: 4px solid #dc2626;
}

.alert-info {
    background: rgba(59, 130, 246, 0.1);
    color: #2563eb;
    border-left: 4px solid #2563eb;
}

/* Animações */
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

.fade-in {
    animation: fadeIn 0.5s ease-out;
}

/* Destaque para plano popular */
.popular-plan {
    border: 2px solid var(--dourado-vibrante);
    transform: scale(1.02);
}

.popular-plan .plan-header {
    background: var(--gradiente-dourado);
}

.popular-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 3;
}
</style>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-5 fade-in">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Planos Disponíveis</h1>
                    <p class="page-subtitle">Escolha o plano perfeito para seus objetivos</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge badge-popular px-3 py-2">
                        <i class="fas fa-crown me-2"></i>Mais Popular
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['erro'])): ?>
    <div class="row mb-4 fade-in">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-3 fs-5"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">Erro!</h6>
                        <p class="mb-0"><?php echo $_SESSION['erro']; ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['erro']); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['sucesso'])): ?>
    <div class="row mb-4 fade-in">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-3 fs-5"></i>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading mb-1">Sucesso!</h6>
                        <p class="mb-0"><?php echo $_SESSION['sucesso']; ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['sucesso']); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Planos Grid -->
    <div class="plans-grid fade-in">
        <?php if (empty($planos)): ?>
        <div class="col-12">
            <div class="dashboard-card text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3 class="text-dark mb-3">Nenhum plano disponível</h3>
                    <p class="text-muted mb-4">No momento não há planos disponíveis para contratação</p>
                </div>
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($planos as $index => $plano): ?>
        <?php 
        // Marcar o plano do meio como popular (ou outro critério)
        $is_popular = $index === 1; // Segundo plano como popular
        ?>
        <div class="plan-card h-100 <?php echo $is_popular ? 'popular-plan' : ''; ?>">
            <?php if ($is_popular): ?>
            <div class="popular-badge">
                <span class="badge badge-popular px-3 py-2">
                    <i class="fas fa-crown me-1"></i>Mais Popular
                </span>
            </div>
            <?php endif; ?>

            <div class="plan-header">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="plan-title"><?php echo htmlspecialchars($plano['nome']); ?></h4>
                        <span class="plan-status status-active">
                            <i class="fas fa-check-circle me-1 small"></i>
                            Disponível
                        </span>
                    </div>
                    <div class="price-tag">
                        R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <?php if (!empty($plano['descricao'])): ?>
                <p class="text-muted mb-4"><?php echo htmlspecialchars($plano['descricao']); ?></p>
                <?php endif; ?>

                <div class="row text-center mb-4">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="feature-icon mx-auto mb-2">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1"><?php echo $plano['duracao_dias'] ?? 30; ?> dias</h6>
                            <small class="text-muted">Duração</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div
                            class="feature-icon mx-auto mb-2 <?php echo $plano['inclui_personal'] ? 'bg-warning' : 'bg-secondary'; ?>">
                            <i class="fas fa-dumbbell"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">
                            <?php echo $plano['inclui_personal'] ? 'Incluído' : 'Não Inclui'; ?>
                        </h6>
                        <small class="text-muted">Personal Trainer</small>
                    </div>
                </div>

                <!-- Benefícios -->
                <div class="benefits mb-4">
                    <h6 class="fw-bold text-dark mb-3">Benefícios Incluídos:</h6>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Acesso ilimitado à academia</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Avaliação física completa</small>
                        </div>
                        <?php if ($plano['inclui_personal']): ?>
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
            </div>

            <div class="card-footer bg-transparent border-0 pt-0">
                <button type="button" class="btn btn-gold w-100 py-3 fw-bold" data-bs-toggle="modal"
                    data-bs-target="#modalPagamento<?php echo $plano['id']; ?>">
                    <i class="fas fa-shopping-cart me-2"></i>
                    <?php echo $is_popular ? 'ESCOLHER ESTE PLANO' : 'ASSINAR AGORA'; ?>
                </button>
            </div>
        </div>

        <!-- Modal de Pagamento Moderno -->
        <div class="modal fade" id="modalPagamento<?php echo $plano['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header modal-header-gradient py-3">
                        <h5 class="modal-title fw-bold">
                            <i class="fas fa-credit-card me-2"></i>
                            Finalizar Assinatura
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="dashboard-card h-100">
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold text-dark mb-3">Resumo do Pedido</h5>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="fw-semibold">Plano:</span>
                                            <span><?php echo htmlspecialchars($plano['nome']); ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="fw-semibold">Duração:</span>
                                            <span><?php echo $plano['duracao_dias'] ?? 30; ?> dias</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="fw-semibold">Personal Trainer:</span>
                                            <span><?php echo $plano['inclui_personal'] ? 'Incluído' : 'Não Incluído'; ?></span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold fs-5">Total:</span>
                                            <span class="fw-bold fs-5 text-success">R$
                                                <?php echo number_format($plano['preco'], 2, ',', '.'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5 class="fw-bold text-dark mb-3">Escolha a Forma de Pagamento</h5>
                                <form method="POST" action="../../includes/functions.php">
                                    <input type="hidden" name="plano_id" value="<?php echo $plano['id']; ?>">
                                    <input type="hidden" name="action" value="processar_pagamento">

                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-success-modern py-3"
                                            name="metodo_pagamento" value="pix">
                                            <i class="fas fa-qrcode me-2 fa-lg"></i>
                                            <div class="text-start">
                                                <div class="fw-bold">PAGAR COM PIX</div>
                                                <small class="opacity-90">Pagamento instantâneo</small>
                                            </div>
                                        </button>

                                        <button type="submit" class="btn btn-primary-modern py-3"
                                            name="metodo_pagamento" value="cartao">
                                            <i class="fas fa-credit-card me-2 fa-lg"></i>
                                            <div class="text-start">
                                                <div class="fw-bold">CARTÃO DE CRÉDITO</div>
                                                <small class="opacity-90">Até 12x sem juros</small>
                                            </div>
                                        </button>

                                        <button type="submit" class="btn btn-info-modern py-3" name="metodo_pagamento"
                                            value="boleto">
                                            <i class="fas fa-barcode me-2 fa-lg"></i>
                                            <div class="text-start">
                                                <div class="fw-bold">BOLETO BANCÁRIO</div>
                                                <small class="opacity-90">Pague em qualquer banco</small>
                                            </div>
                                        </button>
                                    </div>

                                    <div class="mt-4 text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-shield-alt me-1"></i>
                                            Pagamento 100% seguro via Mercado Pago
                                        </small>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Informações Adicionais -->
    <div class="row fade-in">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="card-body p-4">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="feature-icon mx-auto mb-3 bg-success">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h6 class="fw-bold">Pagamento Seguro</h6>
                            <small class="text-muted">Transações protegidas e criptografadas</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="feature-icon mx-auto mb-3 bg-warning">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <h6 class="fw-bold">Cancelamento Flexível</h6>
                            <small class="text-muted">Cancele quando quiser sem multas</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="feature-icon mx-auto mb-3 bg-info">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h6 class="fw-bold">Suporte 24/7</h6>
                            <small class="text-muted">Nossa equipe está sempre disponível</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Efeitos interativos para os cards
document.addEventListener('DOMContentLoaded', function() {
    const planCards = document.querySelectorAll('.plan-card');

    planCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });

        card.addEventListener('mouseleave', function() {
            if (!this.classList.contains('popular-plan')) {
                this.style.transform = 'translateY(0)';
            } else {
                this.style.transform = 'scale(1.02)';
            }
        });
    });
});

// Confirmação antes do pagamento
document.addEventListener('DOMContentLoaded', function() {
    const paymentButtons = document.querySelectorAll('button[name="metodo_pagamento"]');

    paymentButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const planoNome = this.closest('.modal-content').querySelector('.fw-bold')
                .textContent;
            const metodo = this.textContent.trim();

            if (!confirm(`Confirmar pagamento do ${planoNome} via ${metodo}?`)) {
                e.preventDefault();
            }
        });
    });
});
// Substitua o evento de submit dos formulários de pagamento
document.addEventListener('DOMContentLoaded', function() {
    const paymentForms = document.querySelectorAll('form[method="POST"]');

    paymentForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitButton = this.querySelector('button[type="submit"]:focus');
            if (!submitButton) return;

            const metodoPagamento = submitButton.value;
            const formData = new FormData(this);
            formData.append('metodo_pagamento', metodoPagamento);

            // Desabilitar botões durante o processamento
            const buttons = this.querySelectorAll('button');
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML =
                    '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';
            });

            try {
                const response = await fetch('processar_pagamento.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Mostrar mensagem de sucesso
                    showAlert('success', result.message);

                    // Redirecionar se especificado
                    if (result.redirect) {
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 2000);
                    } else {
                        // Recarregar a página após 2 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                } else {
                    // Mostrar mensagem de erro
                    showAlert('error', result.message);

                    // Reabilitar botões
                    buttons.forEach(btn => {
                        btn.disabled = false;
                        resetButtonText(btn, metodoPagamento);
                    });
                }

            } catch (error) {
                console.error('Erro:', error);
                showAlert('error', 'Erro de conexão. Tente novamente.');

                // Reabilitar botões
                buttons.forEach(btn => {
                    btn.disabled = false;
                    resetButtonText(btn, metodoPagamento);
                });
            }
        });
    });

    function resetButtonText(button, metodo) {
        const textos = {
            'pix': '<i class="fas fa-qrcode me-2 fa-lg"></i><div class="text-start"><div class="fw-bold">PAGAR COM PIX</div><small class="opacity-90">Pagamento instantâneo</small></div>',
            'cartao': '<i class="fas fa-credit-card me-2 fa-lg"></i><div class="text-start"><div class="fw-bold">CARTÃO DE CRÉDITO</div><small class="opacity-90">Até 12x sem juros</small></div>',
            'boleto': '<i class="fas fa-barcode me-2 fa-lg"></i><div class="text-start"><div class="fw-bold">BOLETO BANCÁRIO</div><small class="opacity-90">Pague em qualquer banco</small></div>'
        };

        button.innerHTML = textos[metodo] || 'PAGAR';
    }

    function showAlert(type, message) {
        // Remover alertas existentes
        const existingAlerts = document.querySelectorAll('.custom-alert');
        existingAlerts.forEach(alert => alert.remove());

        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

        const alertDiv = document.createElement('div');
        alertDiv.className = `custom-alert alert ${alertClass} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas ${icon} me-3 fs-5"></i>
                <div class="flex-grow-1">
                    <h6 class="alert-heading mb-1">${type === 'success' ? 'Sucesso!' : 'Erro!'}</h6>
                    <p class="mb-0">${message}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alertDiv);

        // Auto-remover após 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});
</script>

<?php include '../../includes/footer.php'; ?>