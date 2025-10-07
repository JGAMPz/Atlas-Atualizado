 <?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
verificarTipo('admin');

// Estatísticas para relatórios
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_usuarios,
        SUM(CASE WHEN tipo = 'aluno' THEN 1 ELSE 0 END) as total_alunos,
        SUM(CASE WHEN tipo = 'personal' THEN 1 ELSE 0 END) as total_personais,
        (SELECT COUNT(*) FROM matriculas WHERE status = 'ativa') as matriculas_ativas,
        (SELECT SUM(valor_contratado) FROM matriculas WHERE status = 'ativa') as receita_total,
        (SELECT COUNT(*) FROM agenda WHERE status = 'agendado') as aulas_agendadas
    FROM usuarios
    WHERE status = 'ativo'
");
$estatisticas = $stmt->fetch();

// Receita mensal (últimos 6 meses)
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(data_criacao, '%Y-%m') as mes,
        SUM(valor_contratado) as receita,
        COUNT(*) as matriculas
    FROM matriculas 
    WHERE data_criacao >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(data_criacao, '%Y-%m')
    ORDER BY mes
");
$receita_mensal = $stmt->fetchAll();

// Novos usuários (últimos 30 dias)
$stmt = $pdo->query("
    SELECT 
        DATE(data_cadastro) as data,
        COUNT(*) as novos_usuarios,
        SUM(CASE WHEN tipo = 'aluno' THEN 1 ELSE 0 END) as novos_alunos
    FROM usuarios 
    WHERE data_cadastro >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
    GROUP BY DATE(data_cadastro)
    ORDER BY data
");
$novos_usuarios = $stmt->fetchAll();
?>
 <?php 
$page_title = "Relatórios - Admin";
include '../../includes/header.php'; 
?>

 <div class="d-flex justify-content-between align-items-center mb-4">
     <h2>Relatórios e Estatísticas</h2>
     <div>
         <button class="btn btn-success" onclick="exportarRelatorio()">
             <i class="fas fa-download"></i> Exportar Relatório
         </button>
     </div>
 </div>

 <!-- Cards de Resumo -->
 <div class="row mb-4">
     <div class="col-md-2 mb-3">
         <div class="card text-center">
             <div class="card-body">
                 <h3 class="text-primary"><?php echo $estatisticas['total_usuarios']; ?></h3>
                 <p class="card-text">Total Usuários</p>
             </div>
         </div>
     </div>
     <div class="col-md-2 mb-3">
         <div class="card text-center">
             <div class="card-body">
                 <h3 class="text-success"><?php echo $estatisticas['total_alunos']; ?></h3>
                 <p class="card-text">Alunos</p>
             </div>
         </div>
     </div>
     <div class="col-md-2 mb-3">
         <div class="card text-center">
             <div class="card-body">
                 <h3 class="text-warning"><?php echo $estatisticas['total_personais']; ?></h3>
                 <p class="card-text">Personais</p>
             </div>
         </div>
     </div>
     <div class="col-md-2 mb-3">
         <div class="card text-center">
             <div class="card-body">
                 <h3 class="text-info"><?php echo $estatisticas['matriculas_ativas']; ?></h3>
                 <p class="card-text">Matrículas Ativas</p>
             </div>
         </div>
     </div>
     <div class="col-md-2 mb-3">
         <div class="card text-center">
             <div class="card-body">
                 <h3 class="text-danger">R$ <?php echo number_format($estatisticas['receita_total'], 2, ',', '.'); ?>
                 </h3>
                 <p class="card-text">Receita Total</p>
             </div>
         </div>
     </div>
     <div class="col-md-2 mb-3">
         <div class="card text-center">
             <div class="card-body">
                 <h3 class="text-secondary"><?php echo $estatisticas['aulas_agendadas']; ?></h3>
                 <p class="card-text">Aulas Agendadas</p>
             </div>
         </div>
     </div>
 </div>

 <div class="row">
     <!-- Receita Mensal -->
     <div class="col-md-6 mb-4">
         <div class="card">
             <div class="card-header">
                 <h5 class="card-title mb-0">Receita Mensal (Últimos 6 meses)</h5>
             </div>
             <div class="card-body">
                 <?php if ($receita_mensal): ?>
                 <div class="table-responsive">
                     <table class="table table-sm">
                         <thead>
                             <tr>
                                 <th>Mês</th>
                                 <th>Matrículas</th>
                                 <th>Receita</th>
                             </tr>
                         </thead>
                         <tbody>
                             <?php foreach ($receita_mensal as $mes): ?>
                             <tr>
                                 <td><?php echo date('m/Y', strtotime($mes['mes'] . '-01')); ?></td>
                                 <td><?php echo $mes['matriculas']; ?></td>
                                 <td>R$ <?php echo number_format($mes['receita'], 2, ',', '.'); ?></td>
                             </tr>
                             <?php endforeach; ?>
                         </tbody>
                     </table>
                 </div>
                 <?php else: ?>
                 <p class="text-muted">Nenhuma receita nos últimos 6 meses.</p>
                 <?php endif; ?>
             </div>
         </div>
     </div>

     <!-- Novos Usuários -->
     <div class="col-md-6 mb-4">
         <div class="card">
             <div class="card-header">
                 <h5 class="card-title mb-0">Novos Usuários (Últimos 30 dias)</h5>
             </div>
             <div class="card-body">
                 <?php if ($novos_usuarios): ?>
                 <div class="table-responsive">
                     <table class="table table-sm">
                         <thead>
                             <tr>
                                 <th>Data</th>
                                 <th>Novos Usuários</th>
                                 <th>Novos Alunos</th>
                             </tr>
                         </thead>
                         <tbody>
                             <?php foreach ($novos_usuarios as $dia): ?>
                             <tr>
                                 <td><?php echo formatDate($dia['data'], 'd/m/Y'); ?></td>
                                 <td><?php echo $dia['novos_usuarios']; ?></td>
                                 <td><?php echo $dia['novos_alunos']; ?></td>
                             </tr>
                             <?php endforeach; ?>
                         </tbody>
                     </table>
                 </div>
                 <?php else: ?>
                 <p class="text-muted">Nenhum novo usuário nos últimos 30 dias.</p>
                 <?php endif; ?>
             </div>
         </div>
     </div>
 </div>

 <!-- Relatório Detalhado -->
 <div class="card">
     <div class="card-header">
         <h5 class="card-title mb-0">Relatório Detalhado</h5>
     </div>
     <div class="card-body">
         <div class="row mb-3">
             <div class="col-md-4">
                 <label for="data_inicio" class="form-label">Data Início</label>
                 <input type="date" class="form-control" id="data_inicio" value="<?php echo date('Y-m-01'); ?>">
             </div>
             <div class="col-md-4">
                 <label for="data_fim" class="form-label">Data Fim</label>
                 <input type="date" class="form-control" id="data_fim" value="<?php echo date('Y-m-d'); ?>">
             </div>
             <div class="col-md-4">
                 <label class="form-label">&nbsp;</label>
                 <button class="btn btn-primary w-100" onclick="gerarRelatorio()">
                     <i class="fas fa-chart-bar"></i> Gerar Relatório
                 </button>
             </div>
         </div>

         <div id="relatorioResultado">
             <!-- Resultado do relatório será carregado aqui -->
         </div>
     </div>
 </div>

 <script>
function gerarRelatorio() {
    const dataInicio = document.getElementById('data_inicio').value;
    const dataFim = document.getElementById('data_fim').value;

    if (!dataInicio || !dataFim) {
        alert('Por favor, selecione as datas de início e fim.');
        return;
    }

    showLoading(document.querySelector('#relatorioResultado').parentElement.querySelector('button'));

    fetch(`../../includes/functions.php?action=gerar_relatorio&data_inicio=${dataInicio}&data_fim=${dataFim}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('relatorioResultado').innerHTML = html;
            hideLoading();
        })
        .catch(error => {
            hideLoading();
            alert('Erro ao gerar relatório.');
            console.error('Error:', error);
        });
}

function exportarRelatorio() {
    // Simular exportação (em produção, integrar com biblioteca de exportação)
    alert('Funcionalidade de exportação será implementada aqui!');

    // Exemplo de implementação:
    // window.location.href = `../../includes/export.php?type=excel&data_inicio=...&data_fim=...`;
}

// Gerar relatório automático para o mês atual
document.addEventListener('DOMContentLoaded', function() {
    gerarRelatorio();
});
 </script>

 <?php include '../../includes/footer.php'; ?>