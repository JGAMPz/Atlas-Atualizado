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
 * Processa login do usuário
 */
function processLogin($data) {
    global $pdo;
    
    $email = sanitizeInput($data['email']);
    $senha = $data['senha'];
    $tipo_usuario = $data['tipo_usuario'];
    
    // Buscar usuário
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND tipo = ? AND status = 'ativo'");
    $stmt->execute([$email, $tipo_usuario]);
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
    
    // Criar sessão
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_tipo'] = $usuario['tipo'];
    
    // Log de atividade
    logActivity($usuario['id'], 'login', 'Login no sistema');
    
    return [
        'success' => true, 
        'message' => 'Login realizado com sucesso',
        'redirect' => 'dashboard/' . $usuario['tipo'] . '/index.php'
    ];
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
?>