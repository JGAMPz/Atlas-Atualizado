<?php
require_once '../../includes/auth.php';
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$usuario = getUsuarioInfo();

// Processar alteração de tipo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'alterar_tipo') {
    if (isset($_POST['usuario_id'], $_POST['novo_tipo'])) {
        $usuario_id = $_POST['usuario_id'];
        $novo_tipo = $_POST['novo_tipo'];
        $resultado = alterarTipoUsuario($usuario_id, $novo_tipo, $usuario['id']);
    } else {
        $resultado = ['success' => false, 'message' => 'Dados incompletos para alteração.'];
    }
}

// Buscar todos os usuários - VERSÃO SEGURA
try {
    // Verificar se a coluna is_super_admin existe de forma segura
    $column_exists = false;
    try {
        $check_column = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'is_super_admin'");
        $column_exists = (bool)$check_column->fetch();
    } catch (Exception $e) {
        $column_exists = false;
    }
    
    if ($column_exists) {
        $stmt = $pdo->query("
            SELECT id, nome, email, tipo, telefone, data_nascimento, endereco, 
                   data_cadastro, status, ultimo_login, is_super_admin
            FROM usuarios 
            ORDER BY tipo, nome
        ");
    } else {
        $stmt = $pdo->query("
            SELECT id, nome, email, tipo, telefone, data_nascimento, endereco, 
                   data_cadastro, status, ultimo_login, 0 as is_super_admin
            FROM usuarios 
            ORDER BY tipo, nome
        ");
    }
    
    $usuarios = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $usuarios = [];
    $erro_busca = "Erro ao carregar lista de usuários: " . $e->getMessage();
    error_log("Erro na query de usuários: " . $e->getMessage());
}

// Garantir que o usuário atual tenha is_super_admin definido
if (!isset($usuario['is_super_admin'])) {
    try {
        $check_column = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'is_super_admin'");
        if ($check_column->fetch()) {
            $stmt = $pdo->prepare("SELECT is_super_admin FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario['id']]);
            $user_data = $stmt->fetch();
            $usuario['is_super_admin'] = $user_data['is_super_admin'] ?? 0;
        } else {
            $usuario['is_super_admin'] = 0;
        }
    } catch (Exception $e) {
        $usuario['is_super_admin'] = 0;
    }
}

// Garantir que todos os usuários tenham is_super_admin
foreach ($usuarios as &$user) {
    if (!isset($user['is_super_admin'])) {
        $user['is_super_admin'] = 0;
    }
}
unset($user);

$page_title = "Gerenciar Usuários";
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Gerenciar Usuários</h1>

            <?php if (isset($resultado)): ?>
            <div class="alert alert-<?php echo $resultado['success'] ? 'success' : 'danger'; ?> alert-dismissible fade show">
                <?php echo $resultado['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (isset($erro_busca)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $erro_busca; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Lista de Usuários</h5>
                    <small class="text-muted">Total: <?php echo count($usuarios); ?> usuários</small>
                </div>
                <div class="card-body">
                    <?php if (empty($usuarios)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Nenhum usuário encontrado no sistema.
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Data de Cadastro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($user['nome']); ?>
                                        <?php if ($user['id'] == $usuario['id']): ?>
                                        <span class="badge bg-info">Você</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $user['tipo'] == 'admin' ? 'danger' : 
                                                 ($user['tipo'] == 'personal' ? 'warning' : 'secondary'); 
                                        ?>">
                                            <?php 
                                            $is_super_admin = $user['is_super_admin'] ?? 0;
                                            switch($user['tipo']) {
                                                case 'admin': 
                                                    echo ($is_super_admin == 1) ? 'Admin Principal' : 'Administrador'; 
                                                    break;
                                                case 'personal': echo 'Personal Trainer'; break;
                                                default: echo 'Aluno';
                                            }
                                            ?>
                                        </span>
                                        <?php if (($user['is_super_admin'] ?? 0) == 1): ?>
                                        <i class="fas fa-crown text-warning ms-1" title="Administrador Principal"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['status'] == 'ativo' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($user['data_cadastro'])) {
                                            echo formatDate($user['data_cadastro']);
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($user['id'] != $usuario['id']): ?>
                                        
                                        <?php if (($user['is_super_admin'] ?? 0) == 1): ?>
                                        <span class="text-warning">
                                            <i class="fas fa-crown me-1"></i>
                                            Admin Principal
                                        </span>
                                        <br>
                                        <small class="text-muted">Não pode ser alterado</small>
                                        <?php else: ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="usuario_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="acao" value="alterar_tipo">
                                            
                                            <select name="novo_tipo" class="form-select form-select-sm" 
                                                    onchange="if(confirm('Tem certeza que deseja alterar <?php echo htmlspecialchars($user['nome']); ?> para ' + this.options[this.selectedIndex].text + '?')) { this.form.submit(); } else { this.value='<?php echo $user['tipo']; ?>'; }"
                                                    style="width: auto; min-width: 140px;">
                                                <option value="aluno" <?php echo $user['tipo'] == 'aluno' ? 'selected' : ''; ?>>Aluno</option>
                                                <option value="personal" <?php echo $user['tipo'] == 'personal' ? 'selected' : ''; ?>>Personal Trainer</option>
                                                <option value="admin" <?php echo $user['tipo'] == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                            </select>
                                        </form>

                                        <?php if ($user['tipo'] === 'admin'): ?>
                                        <small class="text-warning d-block mt-1">
                                            <i class="fas fa-info-circle"></i>
                                            Apenas o admin principal pode rebaixar
                                        </small>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php else: ?>
                                        <span class="text-muted">Seu usuário</span>
                                        <br>
                                        <?php
                                        $current_user_super_admin = $usuario['is_super_admin'] ?? 0;
                                        ?>
                                        <small class="<?php echo $current_user_super_admin == 1 ? 'text-warning fw-bold' : 'text-muted'; ?>">
                                            <i class="fas fa-user me-1"></i>
                                            <?php 
                                                switch($user['tipo']) {
                                                    case 'admin': 
                                                        echo $current_user_super_admin == 1 ? 'Admin Principal 👑' : 'Administrador'; 
                                                        break;
                                                    case 'personal': echo 'Personal Trainer'; break;
                                                    default: echo 'Aluno';
                                                }
                                            ?>
                                        </small>
                                        <?php if ($current_user_super_admin == 1): ?>
                                        <br>
                                        <small class="text-success">
                                            <i class="fas fa-shield-alt me-1"></i>
                                            Você tem permissões totais
                                        </small>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>