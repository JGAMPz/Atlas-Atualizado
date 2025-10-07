 <?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
verificarTipo('aluno');

$usuario = getUsuarioInfo();
$plano_id = $_GET['plano_id'] ?? null;

if (!$plano_id) {
    header('Location: planos.php');
    exit;
}

// Buscar dados do plano
$stmt = $pdo->prepare("SELECT * FROM planos WHERE id = ?");
$stmt->execute([$plano_id]);
$plano = $stmt->fetch();

if (!$plano) {
    header('Location: planos.php');
    exit;
}
?>
 <?php 
$page_title = "Pagamento - Aluno";
include '../../includes/header.php'; 
?>

 <div class="row justify-content-center">
     <div class="col-md-8">
         <div class="card">
             <div class="card-header">
                 <h4 class="card-title mb-0">Finalizar Assinatura</h4>
             </div>
             <div class="card-body">
                 <!-- Resumo do Plano -->
                 <div class="alert alert-info">
                     <h5>Resumo do Plano</h5>
                     <p><strong>Plano:</strong> <?php echo $plano['nome']; ?></p>
                     <p><strong>Descrição:</strong> <?php echo $plano['descricao']; ?></p>
                     <p><strong>Valor:</strong> R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?></p>
                     <?php if ($plano['inclui_personal']): ?>
                     <p><i class="fas fa-check text-success"></i> Personal Trainer Incluso</p>
                     <?php endif; ?>
                 </div>

                 <!-- Formulário de Pagamento -->
                 <form id="formPagamento">
                     <input type="hidden" name="plano_id" value="<?php echo $plano['id']; ?>">
                     <input type="hidden" name="valor" value="<?php echo $plano['preco']; ?>">

                     <h5 class="mb-3">Dados de Pagamento</h5>

                     <div class="mb-3">
                         <label for="metodo_pagamento" class="form-label">Método de Pagamento</label>
                         <select class="form-select" id="metodo_pagamento" name="metodo_pagamento" required>
                             <option value="">Selecione...</option>
                             <option value="cartao_credito">Cartão de Crédito</option>
                             <option value="pix">PIX</option>
                             <option value="boleto">Boleto Bancário</option>
                         </select>
                     </div>

                     <!-- Campos para Cartão de Crédito -->
                     <div id="camposCartao" style="display: none;">
                         <div class="row">
                             <div class="col-md-6">
                                 <div class="mb-3">
                                     <label for="numero_cartao" class="form-label">Número do Cartão</label>
                                     <input type="text" class="form-control" id="numero_cartao" name="numero_cartao"
                                         placeholder="1234 5678 9012 3456">
                                 </div>
                             </div>
                             <div class="col-md-6">
                                 <div class="mb-3">
                                     <label for="nome_cartao" class="form-label">Nome no Cartão</label>
                                     <input type="text" class="form-control" id="nome_cartao" name="nome_cartao">
                                 </div>
                             </div>
                         </div>
                         <div class="row">
                             <div class="col-md-4">
                                 <div class="mb-3">
                                     <label for="validade_mes" class="form-label">Mês/Ano</label>
                                     <input type="text" class="form-control" id="validade_mes" name="validade_mes"
                                         placeholder="MM/AA">
                                 </div>
                             </div>
                             <div class="col-md-4">
                                 <div class="mb-3">
                                     <label for="cvv" class="form-label">CVV</label>
                                     <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123">
                                 </div>
                             </div>
                             <div class="col-md-4">
                                 <div class="mb-3">
                                     <label for="parcelas" class="form-label">Parcelas</label>
                                     <select class="form-select" id="parcelas" name="parcelas">
                                         <?php for ($i = 1; $i <= 12; $i++): ?>
                                         <option value="<?php echo $i; ?>"><?php echo $i; ?>x</option>
                                         <?php endfor; ?>
                                     </select>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- Campos para PIX -->
                     <div id="camposPix" style="display: none;">
                         <div class="alert alert-warning">
                             <p>Após confirmar o pagamento, será gerado um QR Code para pagamento via PIX.</p>
                             <p>O prazo para confirmação do pagamento é de 30 minutos.</p>
                         </div>
                     </div>

                     <!-- Campos para Boleto -->
                     <div id="camposBoleto" style="display: none;">
                         <div class="alert alert-warning">
                             <p>Após confirmar a compra, será gerado um boleto bancário.</p>
                             <p>O prazo para pagamento é de 3 dias úteis.</p>
                         </div>
                     </div>

                     <div class="mt-4">
                         <button type="submit" class="btn btn-success btn-lg w-100">
                             <i class="fas fa-lock"></i> Finalizar Pagamento
                         </button>
                     </div>
                 </form>
             </div>
         </div>
     </div>
 </div>

 <script>
document.getElementById('metodo_pagamento').addEventListener('change', function() {
    // Esconder todos os campos
    document.getElementById('camposCartao').style.display = 'none';
    document.getElementById('camposPix').style.display = 'none';
    document.getElementById('camposBoleto').style.display = 'none';

    // Mostrar campos específicos
    if (this.value === 'cartao_credito') {
        document.getElementById('camposCartao').style.display = 'block';
    } else if (this.value === 'pix') {
        document.getElementById('camposPix').style.display = 'block';
    } else if (this.value === 'boleto') {
        document.getElementById('camposBoleto').style.display = 'block';
    }
});

document.getElementById('formPagamento').addEventListener('submit', function(e) {
    e.preventDefault();

    if (confirm('Confirmar pagamento?')) {
        const formData = new FormData(this);
        formData.append('action', 'processar_pagamento');
        formData.append('aluno_id', <?php echo $usuario['id']; ?>);

        // Simular processamento de pagamento
        alert('Pagamento processado com sucesso! Redirecionando...');

        // Em uma implementação real, aqui seria a integração com gateway de pagamento
        setTimeout(() => {
            window.location.href = 'index.php?pagamento=sucesso';
        }, 2000);
    }
});
 </script>

 <?php include '../../includes/footer.php'; ?>