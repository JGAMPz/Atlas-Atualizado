<?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
verificarTipo('admin');

$usuario = getUsuarioInfo();

// Processar ações (apenas criação e edição via POST normal)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'criar_plano':
                $resultado = criarPlano($_POST);
                break;
                
            case 'editar_plano':
                $resultado = editarPlano($_POST);
                break;
                
            default:
                $resultado = ['success' => false, 'message' => 'Ação não reconhecida'];
        }
        
        if ($resultado['success']) {
            $_SESSION['sucesso'] = $resultado['message'];
        } else {
            $_SESSION['erro'] = $resultado['message'];
        }
        
        header('Location: ' . BASE_URL . '/dashboard/admin/planos.php');
        exit;
    }
}

// Buscar planos do banco
try {
    $stmt = $pdo->query("SELECT * FROM planos ORDER BY id");
    $planos = $stmt->fetchAll();
} catch (PDOException $e) {
    $planos = [];
    $erro = "Erro ao carregar planos: " . $e->getMessage();
}

$page_title = "Gerenciar Planos - Admin";
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Gerenciar Planos</h1>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPlano">
                    <i class="fas fa-plus me-2"></i>Novo Plano
                </button>
            </div>

            <?php if (isset($erro)): ?>
            <div class="alert alert-danger">
                <?php echo $erro; ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['sucesso']; ?>
                <?php unset($_SESSION['sucesso']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['erro'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['erro']; ?>
                <?php unset($_SESSION['erro']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (empty($planos)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Nenhum plano cadastrado no sistema.
            </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($planos as $plano): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-<?php echo $plano['status'] == 'ativo' ? 'success' : 'secondary'; ?> text-white">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($plano['nome']); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h3 class="text-primary">R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?></h3>
                                <small class="text-muted">por mês</small>
                            </div>
                            
                            <?php if (!empty($plano['descricao'])): ?>
                            <p class="card-text"><?php echo htmlspecialchars($plano['descricao']); ?></p>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <strong>Duração:</strong> 
                                    <?php echo $plano['duracao'] ?? 30; ?> dias
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <strong>Status:</strong> 
                                    <span class="badge bg-<?php echo $plano['status'] == 'ativo' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($plano['status']); ?>
                                    </span>
                                </small>
                            </div>

                            <?php if (isset($plano['inclui_personal'])): ?>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <strong>Personal Trainer:</strong> 
                                    <?php echo $plano['inclui_personal'] ? 'Sim' : 'Não'; ?>
                                </small>
                            </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <small class="text-muted">
                                    <strong>ID:</strong> <?php echo $plano['id']; ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="btn-group w-100">
                                <button class="btn btn-outline-primary btn-sm" 
                                        onclick="abrirEditarPlano(<?php echo $plano['id']; ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-outline-danger btn-sm" 
                                        onclick="excluirPlano(<?php echo $plano['id']; ?>, '<?php echo htmlspecialchars($plano['nome']); ?>')">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
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

<!-- Modal para Novo Plano -->
<div class="modal fade" id="modalPlano" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Novo Plano</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="planos.php">
                <input type="hidden" name="action" value="criar_plano">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Plano <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="nome" required 
                               placeholder="Ex: Plano Básico, Plano Premium">
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"
                                  placeholder="Descreva os benefícios do plano..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="preco" class="form-label">Valor Mensal (R$) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="preco" name="preco" 
                               step="0.01" min="0" required placeholder="99.90">
                    </div>
                    <div class="mb-3">
                        <label for="duracao_dias" class="form-label">Duração (dias) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="duracao_dias" name="duracao_dias" 
                               value="30" min="1" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="inclui_personal" name="inclui_personal" value="1">
                            <label class="form-check-label" for="inclui_personal">
                                <i class="fas fa-dumbbell me-1"></i> Inclui Personal Trainer
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="ativo" selected>Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Criar Plano
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Plano -->
<div class="modal fade" id="modalEditarPlano" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Plano</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="planos.php" id="formEditarPlano">
                <input type="hidden" name="action" value="editar_plano">
                <input type="hidden" name="plano_id" id="editar_plano_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editar_nome" class="form-label">Nome do Plano</label>
                        <input type="text" class="form-control" id="editar_nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="editar_descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="editar_descricao" name="descricao" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editar_preco" class="form-label">Valor Mensal (R$)</label>
                        <input type="number" class="form-control" id="editar_preco" name="preco" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="editar_duracao" class="form-label">Duração (dias)</label>
                        <input type="number" class="form-control" id="editar_duracao" name="duracao_dias" min="1" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editar_inclui_personal" name="inclui_personal" value="1">
                            <label class="form-check-label" for="editar_inclui_personal">
                                Inclui Personal Trainer
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editar_status" class="form-label">Status</label>
                        <select class="form-select" id="editar_status" name="status">
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

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o plano <strong id="nome_plano_excluir"></strong>?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarExclusao()">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script>
// Variável global para armazenar dados dos planos
const planosData = <?php echo json_encode($planos); ?>;
let planoParaExcluir = null;

function abrirEditarPlano(planoId) {
    const plano = planosData.find(p => p.id == planoId);
    
    if (plano) {
        document.getElementById('editar_plano_id').value = plano.id;
        document.getElementById('editar_nome').value = plano.nome;
        document.getElementById('editar_descricao').value = plano.descricao || '';
        document.getElementById('editar_preco').value = plano.preco;
        document.getElementById('editar_duracao').value = plano.duracao;
        document.getElementById('editar_inclui_personal').checked = plano.inclui_personal == 1;
        document.getElementById('editar_status').value = plano.status;
        
        const modal = new bootstrap.Modal(document.getElementById('modalEditarPlano'));
        modal.show();
    } else {
        alert('Plano não encontrado!');
    }
}

function excluirPlano(planoId, planoNome) {
    planoParaExcluir = planoId;
    document.getElementById('nome_plano_excluir').textContent = planoNome;
    
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmarExclusao'));
    modal.show();
}

function confirmarExclusao() {
    if (!planoParaExcluir) return;
    
    // Fechar o modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarExclusao'));
    if (modal) modal.hide();
    
    // Mostrar loading
    const btnExcluir = document.querySelector('.btn-danger');
    const originalText = btnExcluir.innerHTML;
    btnExcluir.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Excluindo...';
    btnExcluir.disabled = true;
    
    // Fazer requisição AJAX para excluir
    fetch('../../includes/functions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=excluir_plano&plano_id=' + planoParaExcluir
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recarregar a página para atualizar a lista
            location.reload();
        } else {
            alert('Erro: ' + data.message);
            // Restaurar botão
            btnExcluir.innerHTML = originalText;
            btnExcluir.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro na comunicação com o servidor.');
        // Restaurar botão
        btnExcluir.innerHTML = originalText;
        btnExcluir.disabled = false;
    });
}

// Fechar modais após envio dos formulários
document.addEventListener('DOMContentLoaded', function() {
    // Fechar modal de criação
    const formCriar = document.querySelector('form[action="planos.php"] input[name="action"][value="criar_plano"]');
    if (formCriar) {
        formCriar.closest('form').addEventListener('submit', function() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalPlano'));
            if (modal) modal.hide();
        });
    }
    
    // Fechar modal de edição
    const formEditar = document.getElementById('formEditarPlano');
    if (formEditar) {
        formEditar.addEventListener('submit', function() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarPlano'));
            if (modal) modal.hide();
        });
    }
});
</script>

<?php include '../../includes/footer.php'; ?>