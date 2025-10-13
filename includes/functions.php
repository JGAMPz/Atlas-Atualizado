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

            case 'processar_pagamento':
                if (isset($_POST['plano_id'], $_POST['metodo_pagamento'])) {
                    session_start();
                    if (!isset($_SESSION['usuario_id'])) {
                        echo json_encode(['success' => false, 'message' => 'Usuário não logado.']);
                        exit;
                    }
                    $resultado = processarPagamento($_POST['plano_id'], $_SESSION['usuario_id'], $_POST['metodo_pagamento']);
                    echo json_encode($resultado);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Dados incompletos para processar pagamento.']);
                }
                exit;
                break;
                
                case 'excluir_plano':
                    if (isset($_POST['plano_id'])) {
                        session_start();
                        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
                            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
                            exit;
                        }
                        $resultado = excluirPlano($_POST['plano_id']);
                        echo json_encode($resultado);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'ID do plano não especificado.']);
                    }
                    exit;
                    break;

                case 'criar_plano':
                    if (isset($_POST['nome'], $_POST['preco'])) {
                        session_start();
                        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
                            $_SESSION['erro'] = 'Acesso negado.';
                            header('Location: /dashboard/admin/planos.php');
                            exit;
                        }
                        
                        $resultado = criarPlano($_POST);
                        
                        if ($resultado['success']) {
                            $_SESSION['sucesso'] = $resultado['message'];
                        } else {
                            $_SESSION['erro'] = $resultado['message'];
                        }
                        
                        header('Location: /dashboard/admin/planos.php');
                        exit;
                    } else {
                        $_SESSION['erro'] = 'Dados incompletos.';
                        header('Location: /dashboard/admin/planos.php');
                        exit;
                    }
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
 * Processa pagamento de plano
 */
function processarPagamento($plano_id, $usuario_id, $metodo_pagamento) {
    global $pdo;
    
    try {
        // Buscar informações do plano
        $stmt = $pdo->prepare("SELECT * FROM planos WHERE id = ?");
        $stmt->execute([$plano_id]);
        $plano = $stmt->fetch();
        
        if (!$plano) {
            return ['success' => false, 'message' => 'Plano não encontrado.'];
        }
        
        // Verificar se já tem matrícula ativa
        $stmt = $pdo->prepare("SELECT id FROM matriculas WHERE user_id = ? AND status = 'ativa'");
        $stmt->execute([$usuario_id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Você já possui uma matrícula ativa.'];
        }
        
        // Criar matrícula
        $data_inicio = date('Y-m-d H:i:s');
        $data_fim = date('Y-m-d H:i:s', strtotime("+{$plano['duracao']} days"));
        $stmt = $pdo->prepare("INSERT INTO matriculas (user_id, plano_id, data_inicio, data_fim, status) VALUES (?, ?, ?, ?, 'ativa')");
        $stmt->execute([$usuario_id, $plano_id, $data_inicio, $data_fim]);
        $matricula_id = $pdo->lastInsertId();
        
        // Registrar pagamento
        $stmt = $pdo->prepare("INSERT INTO pagamentos (user_id, plano_id, valor, metodo_pagamento, status, data_pagamento) VALUES (?, ?, ?, ?, 'pago', NOW())");
        $stmt->execute([$usuario_id, $plano_id, $plano['preco'], $metodo_pagamento]);
        
        // Log da ação
        logActivity($usuario_id, 'pagamento', "Pagou plano {$plano['nome']} via {$metodo_pagamento}");
        
        return [
            'success' => true, 
            'message' => 'Pagamento processado com sucesso! Matrícula ativada.',
            'redirect' => 'dashboard/aluno/index.php'
        ];
        
    } catch (PDOException $e) {
        error_log("Erro ao processar pagamento: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro ao processar pagamento.'];
    }
}

/**
 * Cria novo plano
 */
function criarPlano($data) {
    global $pdo;
    
    try {
        $nome = sanitizeInput($data['nome']);
        $descricao = sanitizeInput($data['descricao'] ?? '');
        $preco = floatval($data['preco']);
        $duracao = intval($data['duracao_dias'] ?? 30);
        $inclui_personal = isset($data['inclui_personal']) ? 1 : 0;
        $status = sanitizeInput($data['status'] ?? 'ativo');
        
        $stmt = $pdo->prepare("
            INSERT INTO planos (nome, descricao, preco, duracao, inclui_personal, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([$nome, $descricao, $preco, $duracao, $inclui_personal, $status]);
        
        if ($result && $stmt->rowCount() > 0) {
            logActivity($_SESSION['usuario_id'], 'criar_plano', "Criou plano: {$nome}");
            return ['success' => true, 'message' => 'Plano criado com sucesso!'];
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log("Erro no INSERT: " . print_r($errorInfo, true));
            return ['success' => false, 'message' => 'Falha ao criar plano.'];
        }
        
    } catch (PDOException $e) {
        error_log("Erro PDO ao criar plano: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro no banco de dados.'];
    }
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
 * Editar plano existente
 */
function editarPlano($data) {
    global $pdo;
    
    try {
        if (!isset($data['plano_id'])) {
            return ['success' => false, 'message' => 'ID do plano não especificado.'];
        }
        
        $plano_id = intval($data['plano_id']);
        $nome = sanitizeInput($data['nome']);
        $descricao = sanitizeInput($data['descricao'] ?? '');
        $preco = floatval($data['preco']);
        $duracao = intval($data['duracao_dias'] ?? 30);
        $inclui_personal = isset($data['inclui_personal']) ? 1 : 0;
        $status = sanitizeInput($data['status'] ?? 'ativo');
        
        // Verificar se o plano existe
        $stmt = $pdo->prepare("SELECT id FROM planos WHERE id = ?");
        $stmt->execute([$plano_id]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Plano não encontrado.'];
        }
        
        // Atualizar o plano
        $stmt = $pdo->prepare("
            UPDATE planos 
            SET nome = ?, descricao = ?, preco = ?, duracao = ?, inclui_personal = ?, status = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([$nome, $descricao, $preco, $duracao, $inclui_personal, $status, $plano_id]);
        
        if ($result && $stmt->rowCount() > 0) {
            logActivity($_SESSION['usuario_id'], 'editar_plano', "Editou plano: {$nome} (ID: {$plano_id})");
            return ['success' => true, 'message' => 'Plano atualizado com sucesso!'];
        } else {
            return ['success' => false, 'message' => 'Nenhuma alteração foi realizada.'];
        }
        
    } catch (PDOException $e) {
        error_log("Erro PDO ao editar plano: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro ao editar plano.'];
    }
}

/**
 * Excluir plano
 */
function excluirPlano($plano_id) {
    global $pdo;
    
    try {
        $plano_id = intval($plano_id);
        
        // Verificar se o plano existe
        $stmt = $pdo->prepare("SELECT nome FROM planos WHERE id = ?");
        $stmt->execute([$plano_id]);
        $plano = $stmt->fetch();
        
        if (!$plano) {
            return ['success' => false, 'message' => 'Plano não encontrado.'];
        }
        
        // Verificar se há matrículas ativas com este plano
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM matriculas WHERE plano_id = ? AND status = 'ativa'");
        $stmt->execute([$plano_id]);
        $matriculas_ativas = $stmt->fetch()['total'];
        
        if ($matriculas_ativas > 0) {
            return ['success' => false, 'message' => 'Não é possível excluir este plano pois existem matrículas ativas vinculadas a ele.'];
        }
        
        // Excluir o plano
        $stmt = $pdo->prepare("DELETE FROM planos WHERE id = ?");
        $result = $stmt->execute([$plano_id]);
        
        if ($result && $stmt->rowCount() > 0) {
            logActivity($_SESSION['usuario_id'], 'excluir_plano', "Excluiu plano: {$plano['nome']} (ID: {$plano_id})");
            return ['success' => true, 'message' => 'Plano excluído com sucesso!'];
        } else {
            return ['success' => false, 'message' => 'Erro ao excluir plano.'];
        }
        
    } catch (PDOException $e) {
        error_log("Erro PDO ao excluir plano: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro ao excluir plano.'];
    }
}
?>