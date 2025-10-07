 <?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
verificarTipo('aluno');

$usuario = getUsuarioInfo();

// Buscar matrícula do aluno
$stmt = $pdo->prepare("
    SELECT m.*, p.nome as plano_nome, p.preco, u.nome as personal_nome 
    FROM matriculas m 
    LEFT JOIN planos p ON m.plano_id = p.id 
    LEFT JOIN usuarios u ON m.personal_id = u.id 
    WHERE m.aluno_id = ?
    ORDER BY m.data_inicio DESC
");
$stmt->execute([$usuario['id']]);
$matriculas = $stmt->fetchAll();
?>
 <?php 
$page_title = "Minha Matrícula - Aluno";
include '../../includes/header.php'; 
?>

 <div class="row">
     <div class="col-md-8">
         <h2>Minha Matrícula</h2>
         <p class="text-muted">Gerencie sua matrícula na academia</p>
     </div>
     <div class="col-md-4 text-end">
         <?php 
        $matricula_ativa = array_filter($matriculas, function($m) {
            return $m['status'] == 'ativa';
        });
        
        if (empty($matricula_ativa)): ?>
         <a href="planos.php" class="btn btn-primary">Assinar Plano</a>
         <?php endif; ?>
     </div>
 </div>

 <?php if ($matriculas): ?>
 <?php foreach ($matriculas as $matricula): ?>
 <div class="card mt-4">
     <div class="card-header d-flex justify-content-between align-items-center">
         <h5 class="card-title mb-0">
             Plano: <?php echo $matricula['plano_nome']; ?>
             <span class="badge bg-<?php 
                        echo $matricula['status'] == 'ativa' ? 'success' : 
                             ($matricula['status'] == 'trancada' ? 'warning' : 'secondary'); 
                    ?> ms-2">
                 <?php echo ucfirst($matricula['status']); ?>
             </span>
         </h5>
         <div>
             <?php if ($matricula['status'] == 'ativa'): ?>
             <button class="btn btn-sm btn-outline-warning me-2"
                 onclick="trancarMatricula(<?php echo $matricula['id']; ?>)">
                 <i class="fas fa-pause"></i> Trancar
             </button>
             <button class="btn btn-sm btn-outline-danger" onclick="cancelarMatricula(<?php echo $matricula['id']; ?>)">
                 <i class="fas fa-times"></i> Cancelar
             </button>
             <?php elseif ($matricula['status'] == 'trancada'): ?>
             <button class="btn btn-sm btn-outline-success"
                 onclick="reativarMatricula(<?php echo $matricula['id']; ?>)">
                 <i class="fas fa-play"></i> Reativar
             </button>
             <?php endif; ?>
         </div>
     </div>
     <div class="card-body">
         <div class="row">
             <div class="col-md-6">
                 <p><strong>Data de Início:</strong> <?php echo date('d/m/Y', strtotime($matricula['data_inicio'])); ?>
                 </p>
                 <p><strong>Data de Término:</strong> <?php echo date('d/m/Y', strtotime($matricula['data_fim'])); ?>
                 </p>
                 <p><strong>Valor:</strong> R$ <?php echo number_format($matricula['preco'], 2, ',', '.'); ?></p>
             </div>
             <div class="col-md-6">
                 <p><strong>Personal Trainer:</strong>
                     <?php echo $matricula['personal_nome'] ?: 'Não atribuído'; ?>
                 </p>
                 <p><strong>Dias Restantes:</strong>
                     <?php 
                            $dias_restantes = floor((strtotime($matricula['data_fim']) - time()) / (60 * 60 * 24));
                            echo $dias_restantes > 0 ? $dias_restantes . ' dias' : 'Expirado';
                            ?>
                 </p>
             </div>
         </div>
     </div>
 </div>
 <?php endforeach; ?>
 <?php else: ?>
 <div class="alert alert-info mt-4">
     <h5>Nenhuma matrícula encontrada</h5>
     <p>Você ainda não possui uma matrícula ativa na academia.</p>
     <a href="planos.php" class="btn btn-primary">Ver Planos Disponíveis</a>
 </div>
 <?php endif; ?>

 <script>
function trancarMatricula(matriculaId) {
    if (confirm('Deseja trancar sua matrícula? Você não poderá usar a academia durante o período de trancamento.')) {
        const formData = new FormData();
        formData.append('matricula_id', matriculaId);
        formData.append('action', 'trancar_matricula');

        fetch('../../includes/functions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Matrícula trancada com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            });
    }
}

function cancelarMatricula(matriculaId) {
    if (confirm('ATENÇÃO: Deseja cancelar sua matrícula? Esta ação não pode ser desfeita.')) {
        const formData = new FormData();
        formData.append('matricula_id', matriculaId);
        formData.append('action', 'cancelar_matricula');

        fetch('../../includes/functions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Matrícula cancelada com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            });
    }
}

function reativarMatricula(matriculaId) {
    if (confirm('Deseja reativar sua matrícula?')) {
        const formData = new FormData();
        formData.append('matricula_id', matriculaId);
        formData.append('action', 'reativar_matricula');

        fetch('../../includes/functions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Matrícula reativada com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            });
    }
}
 </script>

 <?php include '../../includes/footer.php'; ?>