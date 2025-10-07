 <?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
verificarTipo('aluno');

$usuario = getUsuarioInfo();

// Buscar planos disponíveis
$stmt = $pdo->query("SELECT * FROM planos WHERE status = 'ativo' ORDER BY preco");
$planos = $stmt->fetchAll();

// Verificar se já tem matrícula ativa
$stmt = $pdo->prepare("SELECT * FROM matriculas WHERE aluno_id = ? AND status = 'ativa'");
$stmt->execute([$usuario['id']]);
$matricula_ativa = $stmt->fetch();
?>
 <?php 
$page_title = "Planos - Aluno";
include '../../includes/header.php'; 
?>

 <div class="d-flex justify-content-between align-items-center mb-4">
     <h2>Planos Disponíveis</h2>
     <?php if ($matricula_ativa): ?>
     <div class="alert alert-info mb-0">
         <i class="fas fa-info-circle"></i> Você já possui uma matrícula ativa.
     </div>
     <?php endif; ?>
 </div>

 <div class="row">
     <?php foreach ($planos as $plano): ?>
     <div class="col-md-4 mb-4">
         <div class="card h-100 <?php echo $plano['inclui_personal'] ? 'border-warning' : ''; ?>">
             <?php if ($plano['inclui_personal']): ?>
             <div class="card-header bg-warning text-dark">
                 <strong><i class="fas fa-crown"></i> COM PERSONAL</strong>
             </div>
             <?php endif; ?>
             <div class="card-body d-flex flex-column">
                 <h5 class="card-title"><?php echo $plano['nome']; ?></h5>
                 <p class="card-text flex-grow-1"><?php echo $plano['descricao']; ?></p>

                 <div class="mt-auto">
                     <h3 class="text-primary">R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?></h3>
                     <small class="text-muted"><?php echo $plano['duracao_dias']; ?> dias</small>

                     <div class="mt-3">
                         <?php if ($plano['inclui_personal']): ?>
                         <p class="text-success"><i class="fas fa-check"></i> Personal Trainer Incluso</p>
                         <?php else: ?>
                         <p class="text-muted"><i class="fas fa-times"></i> Personal Trainer não incluso</p>
                         <?php endif; ?>
                     </div>

                     <?php if (!$matricula_ativa): ?>
                     <button class="btn btn-primary w-100 mt-3" onclick="assinarPlano(<?php echo $plano['id']; ?>)">
                         Assinar Plano
                     </button>
                     <?php else: ?>
                     <button class="btn btn-outline-secondary w-100 mt-3" disabled>
                         Matrícula Ativa
                     </button>
                     <?php endif; ?>
                 </div>
             </div>
         </div>
     </div>
     <?php endforeach; ?>
 </div>

 <!-- Modal Confirmação -->
 <div class="modal fade" id="modalConfirmacao" tabindex="-1">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title">Confirmar Assinatura</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
             </div>
             <div class="modal-body">
                 <p>Deseja assinar o plano <strong id="planoNome"></strong>?</p>
                 <p>Valor: R$ <span id="planoPreco"></span></p>
             </div>
             <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                 <button type="button" class="btn btn-primary" id="btnConfirmarAssinatura">Confirmar</button>
             </div>
         </div>
     </div>
 </div>

 <script>
let planoSelecionado = null;

function assinarPlano(planoId) {
    // Buscar dados do plano (em uma implementação real, isso viria do backend)
    const planos = <?php echo json_encode($planos); ?>;
    const plano = planos.find(p => p.id == planoId);

    if (plano) {
        planoSelecionado = plano;
        document.getElementById('planoNome').textContent = plano.nome;
        document.getElementById('planoPreco').textContent = plano.preco.toFixed(2).replace('.', ',');

        const modal = new bootstrap.Modal(document.getElementById('modalConfirmacao'));
        modal.show();
    }
}

document.getElementById('btnConfirmarAssinatura').addEventListener('click', function() {
    if (planoSelecionado) {
        // Redirecionar para página de pagamento
        window.location.href = `pagamento.php?plano_id=${planoSelecionado.id}`;
    }
});
 </script>

 <?php include '../../includes/footer.php'; ?>