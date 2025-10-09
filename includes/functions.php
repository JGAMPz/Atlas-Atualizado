<?php
require_once 'config.php';

/**
 * Processa ações via AJAX
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'login':
                echo json_encode(processLogin($_POST));
                break;
                
            case 'cadastrar_usuario':
                echo json_encode(processCadastro($_POST));
                break;
                
            case 'agendar':
                echo json_encode(processAgendamento($_POST));
                break;
                
            case 'cancelar_agendamento':
                echo json_encode(cancelarAgendamento($_POST['agenda_id']));
                break;
                
            case 'trancar_matricula':
                echo json_encode(trancarMatricula($_POST['matricula_id']));
                break;
                
            case 'cancelar_matricula':
                echo json_encode(cancelarMatricula($_POST['matricula_id']));
                break;
                
            case 'reativar_matricula':
                echo json_encode(reativarMatricula($_POST['matricula_id']));
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

/**
 * Processa login do usuário com detecção automática do tipo
 */
function processLogin($data) {
    global $pdo;
    
    $email = sanitizeInput($data['email']);
    $senha = $data['senha'];
    
    // Buscar usuário (SEM filtrar por tipo - busca em todos os tipos)
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND status = 'ativo'");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        return ['success' => false, 'message' => 'Usuário não encontrado ou inativo'];
    }
    
    // Verificar senha
    if (!password_verify($senha, $usuario['senha'])) {
        return ['success' => false, 'message' => 'Senha incorreta'];
    }
    
    // Iniciar sessão se não estiver ativa
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Criar sessão com dados do usuário
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_tipo'] = $usuario['tipo'];
    $_SESSION['usuario_tipo_nome'] = getTipoUsuarioNome($usuario['tipo']);
    
    // Log de atividade
    logActivity($usuario['id'], 'login', 'Login no sistema como ' . $usuario['tipo']);
    
    return [
        'success' => true, 
        'message' => 'Login realizado com sucesso!',
        'redirect' => 'dashboard/' . $usuario['tipo'] . '/index.php',
        'tipo_usuario' => $usuario['tipo']
    ];
}

/**
 * Retorna o nome amigável do tipo de usuário
 */
function getTipoUsuarioNome($tipo) {
    $tipos = [
        'aluno' => 'Aluno',
        'personal' => 'Personal Trainer',
        'admin' => 'Administrador'
    ];
    
    return $tipos[$tipo] ?? 'Usuário';
}

/**
 * Processa cadastro de usuário
 */
function processCadastro($data) {
    global $pdo;
    
    $nome = sanitizeInput($data['nome']);
    $email = sanitizeInput($data['email']);
    $senha = $data['senha'];
    $confirmar_senha = $data['confirmar_senha'];
    $tipo_usuario = $data['tipo_usuario'];
    $telefone = sanitizeInput($data['telefone']);
    $data_nascimento = $data['data_nascimento'];
    $endereco = sanitizeInput($data['endereco']);
    
    // Validações básicas
    if ($senha !== $confirmar_senha) {
        return ['success' => false, 'message' => 'As senhas não coincidem'];
    }
    
    if (!isValidEmail($email)) {
        return ['success' => false, 'message' => 'E-mail inválido'];
    }
    
    // VALIDAÇÃO DA DATA DE NASCIMENTO
    $validacaoData = validarDataNascimento($data_nascimento);
    if (!$validacaoData['valido']) {
        return ['success' => false, 'message' => $validacaoData['mensagem']];
    }
    
    // Verificar se email já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Este e-mail já está cadastrado'];
    }
    
    // Hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT, ['cost' => PASSWORD_COST]);
    
    // Inserir usuário
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nome, email, senha, tipo, telefone, data_nascimento, endereco) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$nome, $email, $senha_hash, $tipo_usuario, $telefone, $data_nascimento, $endereco]);
    
    // Log de atividade
    logActivity($pdo->lastInsertId(), 'cadastro', 'Novo cadastro no sistema');
    
    return ['success' => true, 'message' => 'Cadastro realizado com sucesso!'];
}

/**
 * Processa agendamento de aula
 */
function processAgendamento($data) {
    global $pdo;
    
    $personal_id = $data['personal_id'];
    $aluno_id = $data['aluno_id'];
    $data_agendamento = $data['data_agendamento'];
    $hora_agendamento = $data['hora_agendamento'];
    $duracao = $data['duracao'];
    
    $data_hora = $data_agendamento . ' ' . $hora_agendamento . ':00';
    
    // Verificar se horário está disponível
    $stmt = $pdo->prepare("
        SELECT id FROM agenda 
        WHERE personal_id = ? AND data_hora = ? AND status = 'disponivel'
    ");
    $stmt->execute([$personal_id, $data_hora]);
    
    if (!$stmt->fetch()) {
        return ['success' => false, 'message' => 'Horário não disponível'];
    }
    
    // Agendar aula
    $stmt = $pdo->prepare("
        UPDATE agenda 
        SET aluno_id = ?, status = 'agendado' 
        WHERE personal_id = ? AND data_hora = ? AND status = 'disponivel'
    ");
    
    $stmt->execute([$aluno_id, $personal_id, $data_hora]);
    
    if ($stmt->rowCount() > 0) {
        // Notificar personal
        sendNotification($personal_id, 'Novo Agendamento', 
            "O aluno agendou uma aula para " . formatDateTime($data_hora));
        
        logActivity($aluno_id, 'agendamento', 'Agendou aula com personal');
        return ['success' => true, 'message' => 'Aula agendada com sucesso!'];
    }
    
    return ['success' => false, 'message' => 'Erro ao agendar aula'];
}

/**
 * Cancela agendamento
 */
function cancelarAgendamento($agenda_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        UPDATE agenda 
        SET aluno_id = NULL, status = 'disponivel' 
        WHERE id = ? AND status = 'agendado'
    ");
    
    $stmt->execute([$agenda_id]);
    
    if ($stmt->rowCount() > 0) {
        logActivity($_SESSION['usuario_id'], 'cancelamento', 'Cancelou agendamento');
        return ['success' => true, 'message' => 'Agendamento cancelado!'];
    }
    
    return ['success' => false, 'message' => 'Erro ao cancelar agendamento'];
}

/**
 * Tranca matrícula
 */
function trancarMatricula($matricula_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        UPDATE matriculas 
        SET status = 'trancada' 
        WHERE id = ? AND aluno_id = ? AND status = 'ativa'
    ");
    
    $stmt->execute([$matricula_id, $_SESSION['usuario_id']]);
    
    if ($stmt->rowCount() > 0) {
        logActivity($_SESSION['usuario_id'], 'trancamento', 'Trancou matrícula');
        return ['success' => true, 'message' => 'Matrícula trancada com sucesso!'];
    }
    
    return ['success' => false, 'message' => 'Erro ao trancar matrícula'];
}

/**
 * Cancela matrícula
 */
function cancelarMatricula($matricula_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        UPDATE matriculas 
        SET status = 'cancelada', data_cancelamento = NOW() 
        WHERE id = ? AND aluno_id = ? AND status = 'ativa'
    ");
    
    $stmt->execute([$matricula_id, $_SESSION['usuario_id']]);
    
    if ($stmt->rowCount() > 0) {
        logActivity($_SESSION['usuario_id'], 'cancelamento', 'Cancelou matrícula');
        return ['success' => true, 'message' => 'Matrícula cancelada com sucesso!'];
    }
    
    return ['success' => false, 'message' => 'Erro ao cancelar matrícula'];
}

/**
 * Reativa matrícula
 */
function reativarMatricula($matricula_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        UPDATE matriculas 
        SET status = 'ativa', data_cancelamento = NULL 
        WHERE id = ? AND aluno_id = ? AND status = 'trancada'
    ");
    
    $stmt->execute([$matricula_id, $_SESSION['usuario_id']]);
    
    if ($stmt->rowCount() > 0) {
        logActivity($_SESSION['usuario_id'], 'reativacao', 'Reativou matrícula');
        return ['success' => true, 'message' => 'Matrícula reativada com sucesso!'];
    }
    
    return ['success' => false, 'message' => 'Erro ao reativar matrícula'];
}

/**
 * Busca horários disponíveis do personal
 */
function getHorariosDisponiveis($personal_id, $data) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM agenda 
        WHERE personal_id = ? AND DATE(data_hora) = ? AND status = 'disponivel'
        ORDER BY data_hora
    ");
    
    $stmt->execute([$personal_id, $data]);
    return $stmt->fetchAll();
}

/**
 * Cria horários disponíveis para personal
 */
function criarHorariosDisponiveis($personal_id, $data, $horarios) {
    global $pdo;
    
    foreach ($horarios as $horario) {
        $data_hora = $data . ' ' . $horario . ':00';
        
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO agenda (personal_id, data_hora, status) 
            VALUES (?, ?, 'disponivel')
        ");
        
        $stmt->execute([$personal_id, $data_hora]);
    }
    
    return true;
}

/**
 * Altera o tipo de um usuário com proteção do Super Admin (usando is_super_admin)
 */
function alterarTipoUsuario($usuario_id, $novo_tipo, $admin_id) {
    global $pdo;
    
    try {
        // Verificar se quem está alterando é admin
        $stmt = $pdo->prepare("SELECT tipo, is_super_admin FROM usuarios WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
        
        if (!$admin || $admin['tipo'] !== 'admin') {
            return ['success' => false, 'message' => 'Apenas administradores podem alterar tipos de usuário.'];
        }
        
        // Impedir que o admin altere a si mesmo
        if ($usuario_id == $admin_id) {
            return ['success' => false, 'message' => 'Você não pode alterar seu próprio tipo.'];
        }
        
        // Verificar se o usuário alvo é Super Admin
        $stmt = $pdo->prepare("SELECT id, nome, tipo, is_super_admin FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            return ['success' => false, 'message' => 'Usuário não encontrado.'];
        }
        
        // Proteger o Super Admin de ser rebaixado
        if ($usuario['is_super_admin'] == 1 && $novo_tipo !== 'admin') {
            return ['success' => false, 'message' => 'Não é possível rebaixar o administrador principal do sistema.'];
        }
        
        // Verificar se o tipo é válido
        $tipos_validos = ['aluno', 'personal', 'admin'];
        if (!in_array($novo_tipo, $tipos_validos)) {
            return ['success' => false, 'message' => 'Tipo de usuário inválido.'];
        }
        
        // Verificar se já é do tipo desejado
        if ($usuario['tipo'] === $novo_tipo) {
            return ['success' => false, 'message' => "Este usuário já é " . getTipoUsuarioNome($novo_tipo) . "."];
        }
        
        // Apenas o Super Admin pode rebaixar outros administradores
        $is_current_user_super_admin = ($admin['is_super_admin'] == 1);
        
        if ($usuario['tipo'] === 'admin' && $novo_tipo !== 'admin' && !$is_current_user_super_admin) {
            return ['success' => false, 'message' => 'Apenas o administrador principal pode rebaixar outros administradores.'];
        }
        
        // Alterar tipo
        $stmt = $pdo->prepare("UPDATE usuarios SET tipo = ? WHERE id = ?");
        $stmt->execute([$novo_tipo, $usuario_id]);
        
        // Log da ação
        $tipos_nomes = [
            'aluno' => 'aluno',
            'personal' => 'personal trainer', 
            'admin' => 'administrador'
        ];
        
        logActivity($admin_id, 'alteracao_tipo', 
            "Alterou {$usuario['nome']} de {$tipos_nomes[$usuario['tipo']]} para {$tipos_nomes[$novo_tipo]}");
        
        return [
            'success' => true, 
            'message' => "{$usuario['nome']} foi alterado para " . getTipoUsuarioNome($novo_tipo) . " com sucesso!"
        ];
        
    } catch (PDOException $e) {
        error_log("Erro ao alterar tipo de usuário: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro ao alterar tipo de usuário.'];
    }
}
?>