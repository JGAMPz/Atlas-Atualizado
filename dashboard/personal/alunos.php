 <?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
verificarTipo('personal');

$usuario = getUsuarioInfo();

// Buscar alunos do personal
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.nome, u.email, u.telefone, u.data_nascimento,
           COUNT(a.id) as total_aulas,
           MAX(a.data_hora) as ultima_aula
    FROM agenda a
    JOIN usuarios u ON a.aluno_id = u.id
    WHERE a.personal_id = ? AND a.status = 'agendado'
    GROUP BY u.id
    ORDER BY u.nome
");
$stmt->execute([$usuario['id']]);
$alunos = $stmt->fetchAll();
?>
 <?php 
$page_title = "Meus Alunos - Personal";
include '../../includes/header.php'; 
?>

 <div class="d-flex justify-content-between align-items-center mb-4">
     <h2>Meus Alunos</h2>
     <span class="badge bg-primary"><?php echo count($alunos); ?> alunos</span>
 </div>

 <!-- Filtros e Busca -->
 <div class="card mb-4">
     <div class="card-body">
         <div class="row">
             <div class="col-md-6">
                 <input type="text" class="form-control" id="searchInput" placeholder="Buscar aluno...">
             </div>
             <div class="col-md-6">
                 <select class="form-select" id="filterSelect">
                     <option value="">Todos os alunos</option>
                     <option value="ativos">Alunos ativos</option>
                     <option value="recentes">Aulas recentes</option>
                 </select>
             </div>
         </div>
     </div>
 </div>

 <!-- Lista de Alunos -->
 <div class="card">
     <div class="card-header">
         <h5 class="card-title mb-0">Lista de Alunos</h5>
     </div>
     <div class="card-body">
         <?php if ($alunos): ?>
         <div class="table-responsive">
             <table class="table table-striped" id="alunosTable">
                 <thead>
                     <tr>
                         <th>Aluno</th>
                         <th>Contato</th>
                         <th>Idade</th>
                         <th>Total de Aulas</th>
                         <th>Última Aula</th>
                         <th>Ações</th>
                     </tr>
                 </thead>
                 <tbody>
                     <?php foreach ($alunos as $aluno): 
                            $idade = calculateAge($aluno['data_nascimento']);
                        ?>
                     <tr>
                         <td>
                             <div class="d-flex align-items-center">
                                 <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                                     style="width: 40px; height: 40px;">
                                     <?php echo strtoupper(substr($aluno['nome'], 0, 1)); ?>
                                 </div>
                                 <div>
                                     <h6 class="mb-0"><?php echo $aluno['nome']; ?></h6>
                                     <small class="text-muted"><?php echo $aluno['email']; ?></small>
                                 </div>
                             </div>
                         </td>
                         <td><?php echo $aluno['telefone']; ?></td>
                         <td><?php echo $idade; ?> anos</td>
                         <td>
                             <span class="badge bg-primary"><?php echo $aluno['total_aulas']; ?> aulas</span>
                         </td>
                         <td>
                             <?php if ($aluno['ultima_aula']): ?>
                             <?php echo formatDate($aluno['ultima_aula'], 'd/m/Y'); ?>
                             <?php else: ?>
                             <span class="text-muted">Nenhuma</span>
                             <?php endif; ?>
                         </td>
                         <td>
                             <div class="btn-group">
                                 <button class="btn btn-sm btn-outline-primary"
                                     onclick="verDetalhesAluno(<?php echo $aluno['id']; ?>)">
                                     <i class="fas fa-eye"></i>
                                 </button>
                                 <button class="btn btn-sm btn-outline-success"
                                     onclick="criarAvaliacao(<?php echo $aluno['id']; ?>)">
                                     <i class="fas fa-clipboard-check"></i>
                                 </button>
                                 <button class="btn btn-sm btn-outline-warning"
                                     onclick="criarTreino(<?php echo $aluno['id']; ?>)">
                                     <i class="fas fa-dumbbell"></i>
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
             <i class="fas fa-users fa-3x text-muted mb-3"></i>
             <h5>Nenhum aluno encontrado</h5>
             <p class="text-muted">Você ainda não tem alunos agendados.</p>
         </div>
         <?php endif; ?>
     </div>
 </div>

 <!-- Modal Detalhes do Aluno -->
 <div class="modal fade" id="modalDetalhesAluno" tabindex="-1">
     <div class="modal-dialog modal-lg">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title">Detalhes do Aluno</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
             </div>
             <div class="modal-body" id="detalhesAlunoContent">
                 <!-- Conteúdo carregado via AJAX -->
             </div>
         </div>
     </div>
 </div>

 <script>
// Busca em tempo real
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#alunosTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Filtros
document.getElementById('filterSelect').addEventListener('change', function(e) {
    // Implementar filtros específicos
});

function verDetalhesAluno(alunoId) {
    // Carregar detalhes do aluno via AJAX
    fetch(`../../includes/functions.php?action=detalhes_aluno&aluno_id=${alunoId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detalhesAlunoContent').innerHTML = html;
            const modal = new bootstrap.Modal(document.getElementById('modalDetalhesAluno'));
            modal.show();
        });
}

function criarAvaliacao(alunoId) {
    alert('Funcionalidade: Criar avaliação física para aluno ' + alunoId);
    // Implementar criação de avaliação física
}

function criarTreino(alunoId) {
    alert('Funcionalidade: Criar treino personalizado para aluno ' + alunoId);
    // Implementar criação de treino
}
 </script>

 <?php include '../../includes/footer.php'; ?>