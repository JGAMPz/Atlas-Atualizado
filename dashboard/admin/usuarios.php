 <?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
verificarTipo('admin');

// Processar ações
if ($_POST['action'] ?? '' === 'alterar_status') {
    $usuario_id = intval($_POST['usuario_id']);
    $novo_status = $_POST['novo_status'];
    
    $stmt = $pdo->prepare("UPDATE usuarios SET status = ? WHERE id = ?");
    $stmt->execute([$novo_status, $usuario_id]);
    
    header('Location: usuarios.php?success=1');
    exit;
}

// Buscar todos os usuários
$stmt = $pdo->query("
    SELECT id, nome, email, tipo, telefone, data_cadastro, ultimo_login, status 
    FROM usuarios 
    ORDER BY data_cadastro DESC
");
$usuarios = $stmt->fetchAll();

// Estatísticas por tipo
$stmt = $pdo->query("
    SELECT tipo, COUNT(*) as total, 
           SUM(CASE WHEN status = 'ativo' THEN 1 ELSE 0 END) as ativos
    FROM usuarios 
    GROUP BY tipo
");
$estatisticas_tipo = $stmt->fetchAll();
?>
 <?php 
$page_title = "Gerenciar Usuários - Admin";
include '../../includes/header.php'; 
?>

 <div class="d-flex justify-content-between align-items-center mb-4">
     <h2>Gerenciar Usuários</h2>
     <div>
         <span class="badge bg-primary me-2">
             Total: <?php echo count($usuarios); ?>
         </span>
     </div>
 </div>

 <?php if (isset($_GET['success'])): ?>
 <div class="alert alert-success">
     Status do usuário atualizado com sucesso!
 </div>
 <?php endif; ?>

 <!-- Estatísticas por Tipo -->
 <div class="row mb-4">
     <?php foreach ($estatisticas_tipo as $estat): ?>
     <div class="col-md-4 mb-3">
         <div class="card">
             <div class="card-body text-center">
                 <h3 class="text-<?php 
                        echo $estat['tipo'] === 'admin' ? 'danger' : 
                             ($estat['tipo'] === 'personal' ? 'success' : 'primary');
                    ?>">
                     <?php echo $estat['total']; ?>
                 </h3>
                 <p class="card-text">
                     <?php echo ucfirst($estat['tipo']) . 's'; ?><br>
                     <small class="text-muted"><?php echo $estat['ativos']; ?> ativos</small>
                 </p>
             </div>
         </div>
     </div>
     <?php endforeach; ?>
 </div>

 <!-- Filtros -->
 <div class="card mb-4">
     <div class="card-body">
         <div class="row">
             <div class="col-md-4">
                 <select class="form-select" id="filterTipo">
                     <option value="">Todos os tipos</option>
                     <option value="aluno">Alunos</option>
                     <option value="personal">Personal Trainers</option>
                     <option value="admin">Administradores</option>
                 </select>
             </div>
             <div class="col-md-4">
                 <select class="form-select" id="filterStatus">
                     <option value="">Todos os status</option>
                     <option value="ativo">Ativos</option>
                     <option value="inativo">Inativos</option>
                     <option value="suspenso">Suspensos</option>
                 </select>
             </div>
             <div class="col-md-4">
                 <input type="text" class="form-control" id="searchUsers" placeholder="Buscar usuário...">
             </div>
         </div>
     </div>
 </div>

 <!-- Lista de Usuários -->
 <div class="card">
     <div class="card-header">
         <h5 class="card-title mb-0">Todos os Usuários</h5>
     </div>
     <div class="card-body">
         <div class="table-responsive">
             <table class="table table-striped" id="usersTable">
                 <thead>
                     <tr>
                         <th>Usuário</th>
                         <th>Tipo</th>
                         <th>Contato</th>
                         <th>Cadastro</th>
                         <th>Último Login</th>
                         <th>Status</th>
                         <th>Ações</th>
                     </tr>
                 </thead>
                 <tbody>
                     <?php foreach ($usuarios as $user): ?>
                     <tr>
                         <td>
                             <div class="d-flex align-items-center">
                                 <div class="rounded-circle bg-<?php 
                                        echo $user['tipo'] === 'admin' ? 'danger' : 
                                             ($user['tipo'] === 'personal' ? 'success' : 'primary');
                                    ?> text-white d-flex align-items-center justify-content-center me-3"
                                     style="width: 40px; height: 40px;">
                                     <?php echo strtoupper(substr($user['nome'], 0, 1)); ?>
                                 </div>
                                 <div>
                                     <h6 class="mb-0"><?php echo $user['nome']; ?></h6>
                                     <small class="text-muted"><?php echo $user['email']; ?></small>
                                 </div>
                             </div>
                         </td>
                         <td>
                             <span class="badge bg-<?php 
                                    echo $user['tipo'] === 'admin' ? 'danger' : 
                                         ($user['tipo'] === 'personal' ? 'success' : 'primary');
                                ?>">
                                 <?php echo ucfirst($user['tipo']); ?>
                             </span>
                         </td>
                         <td><?php echo $user['telefone'] ?: 'Não informado'; ?></td>
                         <td>
                             <small><?php echo formatDate($user['data_cadastro'], 'd/m/Y'); ?></small>
                         </td>
                         <td>
                             <?php if ($user['ultimo_login']): ?>
                             <small><?php echo formatDateTime($user['ultimo_login'], 'd/m H:i'); ?></small>
                             <?php else: ?>
                             <small class="text-muted">Nunca acessou</small>
                             <?php endif; ?>
                         </td>
                         <td>
                             <span class="badge bg-<?php 
                                    echo $user['status'] === 'ativo' ? 'success' : 
                                         ($user['status'] === 'inativo' ? 'secondary' : 'warning');
                                ?>">
                                 <?php echo ucfirst($user['status']); ?>
                             </span>
                         </td>
                         <td>
                             <div class="btn-group">
                                 <button class="btn btn-sm btn-outline-primary"
                                     onclick="verDetalhesUsuario(<?php echo $user['id']; ?>)">
                                     <i class="fas fa-eye"></i>
                                 </button>
                                 <button class="btn btn-sm btn-outline-<?php 
                                        echo $user['status'] === 'ativo' ? 'warning' : 'success';
                                    ?>"
                                     onclick="alterarStatusUsuario(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')">
                                     <i class="fas fa-<?php 
                                            echo $user['status'] === 'ativo' ? 'pause' : 'play';
                                        ?>"></i>
                                 </button>
                             </div>
                         </td>
                     </tr>
                     <?php endforeach; ?>
                 </tbody>
             </table>
         </div>
     </div>
 </div>

 <!-- Modal Detalhes do Usuário -->
 <div class="modal fade" id="modalDetalhesUsuario" tabindex="-1">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title">Detalhes do Usuário</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
             </div>
             <div class="modal-body" id="detalhesUsuarioContent">
                 <!-- Conteúdo carregado via AJAX -->
             </div>
         </div>
     </div>
 </div>

 <script>
// Filtros
document.getElementById('filterTipo').addEventListener('change', filtrarUsuarios);
document.getElementById('filterStatus').addEventListener('change', filtrarUsuarios);
document.getElementById('searchUsers').addEventListener('input', filtrarUsuarios);

function filtrarUsuarios() {
    const tipo = document.getElementById('filterTipo').value;
    const status = document.getElementById('filterStatus').value;
    const search = document.getElementById('searchUsers').value.toLowerCase();

    const rows = document.querySelectorAll('#usersTable tbody tr');

    rows.forEach(row => {
        const rowTipo = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const rowStatus = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
        const rowText = row.textContent.toLowerCase();

        const tipoMatch = !tipo || rowTipo.includes(tipo);
        const statusMatch = !status || rowStatus.includes(status);
        const searchMatch = !search || rowText.includes(search);

        row.style.display = (tipoMatch && statusMatch && searchMatch) ? '' : 'none';
    });
}

function verDetalhesUsuario(usuarioId) {
    fetch(`../../includes/functions.php?action=detalhes_usuario&id=${usuarioId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detalhesUsuarioContent').innerHTML = html;
            const modal = new bootstrap.Modal(document.getElementById('modalDetalhesUsuario'));
            modal.show();
        });
}

function alterarStatusUsuario(usuarioId, statusAtual) {
    const novoStatus = statusAtual === 'ativo' ? 'inativo' : 'ativo';
    const confirmMessage = `Deseja ${novoStatus === 'ativo' ? 'ativar' : 'inativar'} este usuário?`;

    if (confirm(confirmMessage)) {
        const formData = new FormData();
        formData.append('action', 'alterar_status');
        formData.append('usuario_id', usuarioId);
        formData.append('novo_status', novoStatus);

        fetch('usuarios.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    location.reload();
                }
            });
    }
}
 </script>

 <?php include '../../includes/footer.php'; ?>