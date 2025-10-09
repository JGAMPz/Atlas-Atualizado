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

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Planos Disponíveis</h1>

            <!-- Mensagens de feedback -->
            <?php if (isset($_SESSION['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['erro']; ?>
                <?php unset($_SESSION['erro']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['sucesso']; ?>
                <?php unset($_SESSION['sucesso']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (empty($planos)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Nenhum plano disponível no momento.
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($planos as $plano): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header text-center bg-primary text-white">
                            <h4 class="card-title mb-0"><?php echo htmlspecialchars($plano['nome']); ?></h4>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="text-primary">R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?></h3>
                            <p class="text-muted"><?php echo htmlspecialchars($plano['descricao']); ?></p>

                            <div class="features mb-3">
                                <p><i class="fas fa-calendar-day text-success me-2"></i>
                                    <strong>Duração:</strong> <?php echo $plano['duracao_dias']; ?> dias
                                </p>

                                <?php if ($plano['inclui_personal']): ?>
                                <p><i class="fas fa-dumbbell text-warning me-2"></i>
                                    <strong>Inclui Personal Trainer</strong>
                                </p>
                                <?php else: ?>
                                <p><i class="fas fa-users text-secondary me-2"></i>
                                    <strong>Acesso à academia</strong>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer text-center bg-light">
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                data-bs-target="#modalPagamento<?php echo $plano['id']; ?>">
                                <i class="fas fa-shopping-cart me-2"></i>Assinar Plano
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal de Pagamento SIMPLIFICADO -->
                <div class="modal fade" id="modalPagamento<?php echo $plano['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-dumbbell me-2"></i>
                                    Assinar <?php echo htmlspecialchars($plano['nome']); ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <h5><?php echo htmlspecialchars($plano['nome']); ?></h5>
                                <h3 class="text-success">R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?>
                                </h3>
                                <p class="text-muted"><?php echo htmlspecialchars($plano['descricao']); ?></p>

                                <form method="POST" action="processar_pagamento.php" class="mt-4">
                                    <input type="hidden" name="plano_id" value="<?php echo $plano['id']; ?>">

                                    <div class="d-grid gap-3">
                                        <button type="submit" class="btn btn-success btn-lg py-3"
                                            name="metodo_pagamento" value="pix">
                                            <i class="fas fa-qrcode me-2"></i>
                                            PAGAR COM PIX
                                        </button>

                                        <button type="submit" class="btn btn-primary btn-lg py-3"
                                            name="metodo_pagamento" value="cartao">
                                            <i class="fas fa-credit-card me-2"></i>
                                            PAGAR COM CARTÃO
                                        </button>

                                        <button type="submit" class="btn btn-info btn-lg py-3" name="metodo_pagamento"
                                            value="boleto">
                                            <i class="fas fa-barcode me-2"></i>
                                            PAGAR COM BOLETO
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>