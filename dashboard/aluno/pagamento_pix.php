<?php
// dashboard/aluno/pagamento_pix.php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
verificarTipo('aluno');

if (!isset($_SESSION['pagamento_pix'])) {
    header('Location: planos.php');
    exit;
}

$pagamento = $_SESSION['pagamento_pix'];
$page_title = "Pagamento PIX";
include '../../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0"><i class="fas fa-qrcode me-2"></i>Pagamento PIX</h4>
                </div>
                <div class="card-body text-center">

                    <?php if (isset($pagamento['mensagem'])): ?>
                    <div class="alert alert-info">
                        <?php echo $pagamento['mensagem']; ?>
                    </div>
                    <?php endif; ?>

                    <h5>Plano: <?php echo htmlspecialchars($pagamento['plano_nome']); ?></h5>
                    <h3 class="text-success">R$ <?php echo number_format($pagamento['valor'], 2, ',', '.'); ?></h3>

                    <div class="my-4">
                        <img src="<?php echo $pagamento['qr_code']; ?>" alt="QR Code PIX"
                            class="img-fluid border rounded" style="max-width: 250px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">PIX Copia e Cola:</label>
                        <div class="input-group">
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($pagamento['pix_copia_cola']); ?>" id="pixCode"
                                readonly>
                            <button class="btn btn-outline-primary" type="button" onclick="copiarPix()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <small class="text-muted">Copie o código e cole no seu app do banco</small>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Atenção:</strong> Este é um modo de simulação.
                        Em produção, o QR Code seria gerado pelo Mercado Pago.
                    </div>

                    <div class="d-grid gap-2">
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Voltar ao Dashboard
                        </a>
                        <a href="planos.php" class="btn btn-outline-secondary">
                            <i class="fas fa-sync-alt me-2"></i>Ver outros planos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copiarPix() {
    const pixCode = document.getElementById('pixCode');
    pixCode.select();
    pixCode.setSelectionRange(0, 99999);
    document.execCommand('copy');

    // Feedback visual
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.classList.remove('btn-outline-primary');
    btn.classList.add('btn-success');

    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-primary');
    }, 2000);
}
</script>

<?php include '../../includes/footer.php'; ?>