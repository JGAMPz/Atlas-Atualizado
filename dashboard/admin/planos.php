 <?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
verificarTipo('admin');

// Processar ações
if ($_POST['action'] ?? '' === 'criar_plano') {
    $nome = sanitizeInput($_POST['nome']);
    $descricao = sanitizeInput($_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $duracao_dias = intval($_POST['duracao_dias']);
    $inclui_personal = isset($_POST['inclui_personal']) ? 1 : 0;
    
    $stmt = $pdo->prepare("
        INSERT INTO planos (nome, descricao, preco, duracao_dias, inclui_personal) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nome, $descricao, $preco, $duracao_dias, $inclui_personal]);
    
    header('Location: planos.php?success=1');
    exit;
}

if ($_POST['action'] ?? '' === 'editar_plano') {
    $id = intval($_POST['id']);
    $nome = sanitizeInput($_POST['nome']);
    $descricao = sanitizeInput($_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $duracao_dias = intval($_POST['duracao_dias']);
    $inclui_personal = isset($_POST['inclui_personal']) ? 1 : 0;
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("
        UPDATE planos 
        SET nome = ?, descricao = ?, preco = ?, duracao_dias = ?, inclui_personal = ?, status = ?
        WHERE id = ?
    ");
    $stmt->execute([$nome, $descricao, $preco, $duracao_dias, $inclui_personal, $status, $id]);
    
    header('Location: planos.php?success=2');
    exit;
}

// Buscar todos os planos
$stmt = $pdo->query("SELECT * FROM planos ORDER BY preco");
$planos = $stmt->fetchAll();
?>
 <?php 
$page_title = "Gerenciar Planos - Admin";
include '../../includes/header.php'; 
?>

 <div class="d-flex justify-content-between align-items-center mb-4">
     <h2>Gerenciar Planos</h2>
     <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCriarPlano">
         <i class="fas fa-plus"></i> Novo Plano
     </button>
 </div>

 <?php if (isset($_GET['success'])): ?>
 <div class="alert alert-success">
     <?php echo $_GET['success'] == 1 ? 'Plano criado com sucesso!' : 'Plano atualizado com sucesso!'; ?>
 </div>
 <?php endif; ?>

 <!-- Lista de Planos -->
 <div class="card">
     <div class="card-header">
         <h5 class="card-title mb-0">Planos Cadastrados</h5>
     </div>
     <div class="card-body">
         <?php if ($planos): ?>
         <div class="table-responsive">
             <table class="table table-striped">
                 <thead>
                     <tr>
                         <th>Nome</th>
                         <th>Descrição</th>
                         <th>Preço</th>
                         <th>Duração</th>
                         <th>Personal</th>
                         <th>Status</th>
                         <th>Ações</th>
                     </tr>
                 </thead>
                 <tbody>
                     <?php foreach ($planos as $plano): ?>
                     <tr>
                         <td>
                             <strong><?php echo $plano['nome']; ?></strong>
                         </td>
                         <td><?php echo $plano['descricao']; ?></td>
                         <td>R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?></td>
                         <td><?php echo $plano['duracao_dias']; ?> dias</td>
                         <td>
                             <?php if ($plano['inclui_personal']): ?>
                             <span class="badge bg-success">Incluso</span>
                             <?php else: ?>
                             <span class="badge bg-secondary">Não</span>
                             <?php endif; ?>
                         </td>
                         <td>
                             <span class="badge bg-<?php echo $plano['status'] === 'ativo' ? 'success' : 'danger'; ?>">
                                 <?php echo ucfirst($plano['status']); ?>
                             </span>
                         </td>
                         <td>
                             <div class="btn-group">
                                 <button class="btn btn-sm btn-outline-primary"
                                     onclick="editarPlano(<?php echo $plano['id']; ?>)">
                                     <i class="fas fa-edit"></i>
                                 </button>
                                 <button
                                     class="btn btn-sm btn-outline-<?php echo $plano['status'] === 'ativo' ? 'warning' : 'success'; ?>"
                                     onclick="togglePlanoStatus(<?php echo $plano['id']; ?>, '<?php echo $plano['status']; ?>')">
                                     <i
                                         class="fas fa-<?php echo $plano['status'] === 'ativo' ? 'pause' : 'play'; ?>"></i>
                                 </button>
                             </div>
                         </td>
                     </tr>
                     <?php endforeach; ?>
                 </tbody>
             </table>
         </div>
         <?php else: ?>
         <div class="text-center py-4">
             <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
             <h5>Nenhum plano cadastrado</h5>
             <p class="text-muted">Crie seu primeiro plano para começar.</p>
         </div>
         <?php endif; ?>
     </div>
 </div>

 <!-- Modal Criar Plano -->
 <div class="modal fade" id="modalCriarPlano" tabindex="-1">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title">Criar Novo Plano</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
             </div>
             <form method="POST">
                 <input type="hidden" name="action" value="criar_plano">

                 <div class="modal-body">
                     <div class="mb-3">
                         <label for="nome" class="form-label">Nome do Plano</label>
                         <input type="text" class="form-control" id="nome" name="nome" required>
                     </div>

                     <div class="mb-3">
                         <label for="descricao" class="form-label">Descrição</label>
                         <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                     </div>

                     <div class="row">
                         <div class="col-md-6">
                             <div class="mb-3">
                                 <label for="preco" class="form-label">Preço (R$)</label>
                                 <input type="number" class="form-control" id="preco" name="preco" step="0.01" min="0"
                                     required>
                             </div>
                         </div>
                         <div class="col-md-6">
                             <div class="mb-3">
                                 <label for="duracao_dias" class="form-label">Duração (dias)</label>
                                 <input type="number" class="form-control" id="duracao_dias" name="duracao_dias"
                                     value="30" required>
                             </div>
                         </div>
                     </div>

                     <div class="mb-3">
                         <div class="form-check">
                             <input class="form-check-input" type="checkbox" id="inclui_personal"
                                 name="inclui_personal">
                             <label class="form-check-label" for="inclui_personal">
                                 Inclui Personal Trainer
                             </label>
                         </div>
                     </div>
                 </div>

                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                     <button type="submit" class="btn btn-primary">Criar Plano</button>
                 </div>
             </form>
         </div>
     </div>
 </div>

 <!-- Modal Editar Plano -->
 <div class="modal fade" id="modalEditarPlano" tabindex="-1">
     <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title">Editar Plano</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
             </div>
             <form method="POST">
                 <input type="hidden" name="action" value="editar_plano">
                 <input type="hidden" name="id" id="editar_id">

                 <div class="modal-body">
                     <div class="mb-3">
                         <label for="editar_nome" class="form-label">Nome do Plano</label>
                         <input type="text" class="form-control" id="editar_nome" name="nome" required>
                     </div>

                     <div class="mb-3">
                         <label for="editar_descricao" class="form-label">Descrição</label>
                         <textarea class="form-control" id="editar_descricao" name="descricao" rows="3"></textarea>
                     </div>

                     <div class="row">
                         <div class="col-md-6">
                             <div class="mb-3">
                                 <label for="editar_preco" class="form-label">Preço (R$)</label>
                                 <input type="number" class="form-control" id="editar_preco" name="preco" step="0.01"
                                     min="0" required>
                             </div>
                         </div>
                         <div class="col-md-6">
                             <div class="mb-3">
                                 <label for="editar_duracao_dias" class="form-label">Duração (dias)</label>
                                 <input type="number" class="form-control" id="editar_duracao_dias" name="duracao_dias"
                                     required>
                             </div>
                         </div>
                     </div>

                     <div class="mb-3">
                         <div class="form-check">
                             <input class="form-check-input" type="checkbox" id="editar_inclui_personal"
                                 name="inclui_personal">
                             <label class="form-check-label" for="editar_inclui_personal">
                                 Inclui Personal Trainer
                             </label>
                         </div>
                     </div>

                     <div class="mb-3">
                         <label for="editar_status" class="form-label">Status</label>
                         <select class="form-select" id="editar_status" name="status" required>
                             <option value="ativo">Ativo</option>
                             <option value="inativo">Inativo</option>
                         </select>
                     </div>
                 </div>

                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                     <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                 </div>
             </form>
         </div>
     </div>
 </div>

 <script>
function editarPlano(planoId) {
    // Buscar dados do plano via AJAX
    fetch(`../../includes/functions.php?action=get_plano&id=${planoId}`)
        .then(response => response.json())
        .then(plano => {
            document.getElementById('editar_id').value = plano.id;
            document.getElementById('editar_nome').value = plano.nome;
            document.getElementById('editar_descricao').value = plano.descricao;
            document.getElementById('editar_preco').value = plano.preco;
            document.getElementById('editar_duracao_dias').value = plano.duracao_dias;
            document.getElementById('editar_inclui_personal').checked = plano.inclui_personal == 1;
            document.getElementById('editar_status').value = plano.status;

            const modal = new bootstrap.Modal(document.getElementById('modalEditarPlano'));
            modal.show();
        });
}

function togglePlanoStatus(planoId, statusAtual) {
    const novoStatus = statusAtual === 'ativo' ? 'inativo' : 'ativo';
    const confirmMessage = `Deseja ${novoStatus === 'ativo' ? 'ativar' : 'inativar'} este plano?`;

    if (confirm(confirmMessage)) {
        const formData = new FormData();
        formData.append('action', 'toggle_plano_status');
        formData.append('plano_id', planoId);
        formData.append('novo_status', novoStatus);

        fetch('../../includes/functions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Status do plano atualizado!');
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            });
    }
}
 </script>

 <?php include '../../includes/footer.php'; ?>