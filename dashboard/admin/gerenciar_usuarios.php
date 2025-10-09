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
    // Verificar se todos os campos necessários estão presentes
    if (isset($_POST['usuario_id'], $_POST['novo_tipo'])) {
        $usuario_id = $_POST['usuario_id'];
        $novo_tipo = $_POST['novo_tipo'];
        
        $resultado = alterarTipoUsuario($usuario_id, $novo_tipo, $usuario['id']);
    } else {
        $resultado = ['success' => false, 'message' => 'Dados incompletos para alteração.'];
    }
}

// Buscar todos os usuários - COM VERIFICAÇÃO DE COLUNA
try {
    // Primeiro verificar se a coluna is_super_admin existe
    $check_column = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'is_super_admin'");
    $column_exists = $check_column->fetch();
    
    if ($column_exists) {
        // Se a coluna existe, buscar com ela
        $stmt = $pdo->query("
            SELECT id, nome, email, senha, tipo, telefone, data_nascimento, endereco, data_cadastro, status, ultimo_login, is_super_admin
            FROM usuarios 
            ORDER BY tipo, nome
        ");
    } else {
        // Se a coluna não existe, buscar sem ela e adicionar valor padrão
        $stmt = $pdo->query("
            SELECT id, nome, email, senha, tipo, telefone, data_nascimento, endereco, data_cadastro, status, ultimo_login
            FROM usuarios 
            ORDER BY tipo, nome
        ");
    }
    
    $usuarios = $stmt->fetchAll();
    
    // Adicionar is_super_admin se não existir para TODOS os usuários
    foreach ($usuarios as &$user) {
        if (!isset($user['is_super_admin'])) {
            $user['is_super_admin'] = 0; // Valor padrão
        }
    }
    unset($user); // Quebrar a referência
    
} catch (PDOException $e) {
    $usuarios = [];
    $erro_busca = "Erro ao carregar lista de usuários: " . $e->getMessage();
    error_log("Erro na query de usuários: " . $e->getMessage());
}

// GARANTIR que o usuário atual também tenha is_super_admin
if (!isset($usuario['is_super_admin'])) {
    try {
        $stmt = $pdo->prepare("SELECT is_super_admin FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario['id']]);
        $user_data = $stmt->fetch();
        $usuario['is_super_admin'] = $user_data['is_super_admin'] ?? 0;
    } catch (Exception $e) {
        $usuario['is_super_admin'] = 0;
    }
}

$page_title = "Gerenciar Usuários";
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Gerenciar Usuários</h1>

            <?php if (isset($resultado)): ?>
            <div
                class="alert alert-<?php echo $resultado['success'] ? 'success' : 'danger'; ?> alert-dismissible fade show">
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
                                            switch($user['tipo']) {
                                                case 'admin': echo 'Administrador'; break;
                                                case 'personal': echo 'Personal Trainer'; break;
                                                default: echo 'Aluno';
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-<?php echo $user['status'] == 'ativo' ? 'success' : 'danger'; ?>">
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
                                        <?php
                                            // Verificar se é o Super Admin - AGORA SEGURO
                                            $is_super_admin = (isset($user['is_super_admin']) && $user['is_super_admin'] == 1);
                                            ?>

                                        <?php if ($is_super_admin): ?>
                                        <span class="text-warning">
                                            <i class="fas fa-crown me-1"></i>
                                            Admin Principal
                                        </span>
                                        <br>
                                        <small class="text-muted">Não pode ser alterado</small>
                                        <?php else: ?>
                                        <form method="POST" class="d-flex align-items-center gap-2">
                                            <input type="hidden" name="usuario_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="acao" value="alterar_tipo">

                                            <select name="novo_tipo" class="form-select form-select-sm"
                                                style="width: auto;"
                                                onchange="if(confirm('Alterar <?php echo htmlspecialchars($user['nome']); ?> para ' + this.options[this.selectedIndex].text + '?')) { this.form.submit(); } else { this.value='<?php echo $user['tipo']; ?>'; }">
                                                <option value="aluno"
                                                    <?php echo $user['tipo'] == 'aluno' ? 'selected' : ''; ?>>Aluno
                                                </option>
                                                <option value="personal"
                                                    <?php echo $user['tipo'] == 'personal' ? 'selected' : ''; ?>>
                                                    Personal</option>
                                                <option value="admin"
                                                    <?php echo $user['tipo'] == 'admin' ? 'selected' : ''; ?>>Admin
                                                </option>
                                            </select>

                                            <button type="submit" class="btn btn-primary btn-sm" style="display: none;">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>

                                        <?php if ($user['tipo'] === 'admin'): ?>
                                        <small class="text-warning d-block mt-1">
                                            <i class="fas fa-info-circle"></i>
                                            Apenas o admin principal pode rebaixar
                                        </small>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                        <?php else: ?>
                                        <?php
                                            // Verificar se o usuário atual é o Super Admin - AGORA SEGURO
                                            $is_current_user_super_admin = (isset($usuario['is_super_admin']) && $usuario['is_super_admin'] == 1);
                                            ?>

                                        <span class="text-muted">Seu usuário</span>
                                        <br>
                                        <small
                                            class="<?php echo $is_current_user_super_admin ? 'text-warning fw-bold' : 'text-muted'; ?>">
                                            <i class="fas fa-user me-1"></i>
                                            <?php 
                                                switch($user['tipo']) {
                                                    case 'admin': 
                                                        echo $is_current_user_super_admin ? 'Admin Principal 👑' : 'Administrador'; 
                                                        break;
                                                    case 'personal': echo 'Personal Trainer'; break;
                                                    default: echo 'Aluno';
                                                }
                                                ?>
                                        </small>
                                        <?php if ($is_current_user_super_admin): ?>
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