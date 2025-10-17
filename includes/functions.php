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

            case 'consultar_agenda_personal':
                 echo json_encode(consultarAgendaPersonal($pdo, $_POST));
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

            case 'criar_horarios_personal':
                echo json_encode(criarHorariosPersonal($pdo, $_POST));
                break;
    
            case 'excluir_horario_personal':
                echo json_encode(excluirHorarioPersonal($pdo, $_POST));
                break;

            case 'personal_cancelar_agendamento':
                echo json_encode(personalCancelarAgendamento($pdo, $_POST));
                break;
    
            case 'marcar_notificacao_lida':
                echo json_encode(marcarNotificacaoLida($_POST['notificacao_id']));
                break;

            case 'processar_pagamento':
                if (isset($_POST['plano_id'], $_POST['metodo_pagamento'])) {
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
 * Processa agendamento de aula - VERSÃO CORRIGIDA
 */
function processAgendamento($data) {
    global $pdo;
    
    try {
        $personal_id = $data['personal_id'];
        $aluno_id = $data['aluno_id'];
        $data_hora = $data['data_hora'];
        
        // Verificar se o horário existe e está disponível
        $stmt = $pdo->prepare("
            SELECT id FROM agenda 
            WHERE personal_id = ? 
            AND data_hora = ? 
            AND status = 'disponivel'
            AND aluno_id IS NULL
        ");
        $stmt->execute([$personal_id, $data_hora]);
        $slot = $stmt->fetch();
        
        if (!$slot) {
            return ['success' => false, 'message' => 'Horário não disponível ou já foi agendado'];
        }
        
        // Agendar aula
        $stmt = $pdo->prepare("
            UPDATE agenda 
            SET aluno_id = ?, status = 'agendado' 
            WHERE id = ?
        ");
        $stmt->execute([$aluno_id, $slot['id']]);
        
        if ($stmt->rowCount() > 0) {
            logActivity($aluno_id, 'agendamento', 'Agendou aula com personal');
            return ['success' => true, 'message' => 'Aula agendada com sucesso!'];
        }
        
        return ['success' => false, 'message' => 'Erro ao agendar aula'];
        
    } catch (PDOException $e) {
        error_log("Erro no agendamento: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()];
    }
}
/**
 * Cria horários disponíveis para personal
 */
function criarHorariosPersonal($pdo, $dados) {
    try {
        $personal_id = $dados['personal_id'];
        $data = $dados['data_horarios'];
        $horarios = $dados['horarios'] ?? [];
        
        if (empty($horarios)) {
            return ['success' => false, 'message' => 'Selecione pelo menos um horário'];
        }
        
        foreach ($horarios as $horario) {
            $data_hora = $data . ' ' . $horario . ':00';
            
            // Verificar se já existe
            $stmt = $pdo->prepare("SELECT id FROM agenda WHERE personal_id = ? AND data_hora = ?");
            $stmt->execute([$personal_id, $data_hora]);
            
            if (!$stmt->fetch()) {
                // Criar novo horário
                $stmt = $pdo->prepare("
                    INSERT INTO agenda (personal_id, data_hora, status) 
                    VALUES (?, ?, 'disponivel')
                ");
                $stmt->execute([$personal_id, $data_hora]);
            }
        }
        
        return ['success' => true, 'message' => 'Horários criados com sucesso!'];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erro ao criar horários: ' . $e->getMessage()];
    }
}

/**
 * Exclui horário do personal
 */
function excluirHorarioPersonal($pdo, $dados) {
    try {
        $horario_id = $dados['horario_id'];
        
        // Verificar se o horário pode ser excluído (não está agendado)
        $stmt = $pdo->prepare("SELECT status FROM agenda WHERE id = ?");
        $stmt->execute([$horario_id]);
        $horario = $stmt->fetch();
        
        if (!$horario) {
            return ['success' => false, 'message' => 'Horário não encontrado'];
        }
        
        if ($horario['status'] == 'agendado') {
            return ['success' => false, 'message' => 'Não é possível excluir horário com agendamento ativo'];
        }
        
        $stmt = $pdo->prepare("DELETE FROM agenda WHERE id = ?");
        $stmt->execute([$horario_id]);
        
        return ['success' => true, 'message' => 'Horário excluído com sucesso!'];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erro ao excluir horário: ' . $e->getMessage()];
    }
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

function processarPagamento($plano_id, $usuario_id, $metodo_pagamento) {
    global $pdo;
    
    try {
        // Buscar dados do plano
        $stmt = $pdo->prepare("SELECT * FROM planos WHERE id = ? AND status = 'ativo'");
        $stmt->execute([$plano_id]);
        $plano = $stmt->fetch();
        
        if (!$plano) {
            return ['success' => false, 'message' => 'Plano não encontrado ou inativo.'];
        }

        // Buscar dados do usuário
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            return ['success' => false, 'message' => 'Usuário não encontrado.'];
        }

        // Verificar se a tabela pagamentos existe
        $tabela_existe = false;
        try {
            $teste = $pdo->query("SELECT 1 FROM pagamentos LIMIT 1");
            $tabela_existe = true;
        } catch (Exception $e) {
            $tabela_existe = false;
        }

        if ($tabela_existe) {
            // Usar as colunas CORRETAS baseado na estrutura da tabela
            $stmt = $pdo->prepare("
                INSERT INTO pagamentos (usuario_id, plano_id, valor, metodo_pagamento, status) 
                VALUES (?, ?, ?, ?, 'pendente')
            ");
            $stmt->execute([$usuario_id, $plano_id, $plano['preco'], $metodo_pagamento]);
            $pagamento_id = $pdo->lastInsertId();
            
            error_log("Pagamento criado: ID $pagamento_id para usuário $usuario_id");
        } else {
            // Se não existir tabela pagamentos, criar matrícula direto
            $stmt = $pdo->prepare("SELECT id FROM matriculas WHERE aluno_id = ? AND status = 'ativa'");
            $stmt->execute([$usuario_id]);
            $matricula_existente = $stmt->fetch();
            
            if ($matricula_existente) {
                // Atualizar matrícula existente
                $stmt = $pdo->prepare("
                    UPDATE matriculas 
                    SET plano_id = ?, data_inicio = NOW(), data_fim = DATE_ADD(NOW(), INTERVAL ? DAY), status = 'ativa'
                    WHERE aluno_id = ?
                ");
                $stmt->execute([$plano_id, $plano['duracao_dias'], $usuario_id]);
                error_log("Matrícula atualizada para usuário $usuario_id");
            } else {
                // Criar nova matrícula
                $stmt = $pdo->prepare("
                    INSERT INTO matriculas (aluno_id, plano_id, status, data_inicio, data_fim) 
                    VALUES (?, ?, 'ativa', NOW(), DATE_ADD(NOW(), INTERVAL ? DAY))
                ");
                $stmt->execute([$usuario_id, $plano_id, $plano['duracao_dias']]);
                error_log("Nova matrícula criada para usuário $usuario_id");
            }
            
            return [
                'success' => true, 
                'message' => "Plano {$plano['nome']} contratado com sucesso!",
                'redirect' => 'index.php'
            ];
        }

        // Processar com Mercado Pago se disponível
        $mp_config_path = '../includes/mercadopago_config.php';
        if (file_exists($mp_config_path)) {
            require_once $mp_config_path;
            
            try {
                $mp = new MercadoPagoIntegration();
                
                $dados_pagamento = [
                    'pagamento_id' => $pagamento_id,
                    'plano_nome' => $plano['nome'],
                    'valor' => (float)$plano['preco'],
                    'email' => $usuario['email'],
                    'nome' => $usuario['nome'],
                    'cpf' => $usuario['cpf'] ?? '00000000000'
                ];

                if ($metodo_pagamento === 'pix') {
                    $resultado = $mp->criarPagamentoPix($dados_pagamento);
                    
                    if ($resultado['success']) {
                        // Atualizar pagamento com dados do PIX usando as colunas CORRETAS
                        $stmt = $pdo->prepare("
                            UPDATE pagamentos 
                            SET id_mp = ?, qr_code = ?, pix_copia_cola = ?, status = 'pendente'
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $resultado['payment_id'],
                            $resultado['qr_code'],
                            $resultado['qr_code_text'],
                            $pagamento_id
                        ]);
                        
                        error_log("PIX criado com sucesso para pagamento $pagamento_id");
                        
                        return [
                            'success' => true,
                            'message' => $resultado['message'] ?? 'Pagamento PIX criado com sucesso!',
                            'redirect' => 'pagamento_pix.php',
                            'pagamento_id' => $pagamento_id
                        ];
                    } else {
                        error_log("Erro ao criar PIX: " . ($resultado['message'] ?? 'Erro desconhecido'));
                        return ['success' => false, 'message' => $resultado['message'] ?? 'Erro ao criar pagamento PIX'];
                    }
                } else {
                    $resultado = $mp->criarPagamento($dados_pagamento);
                    
                    if ($resultado['success']) {
                        $stmt = $pdo->prepare("UPDATE pagamentos SET id_mp = ?, status = 'pendente' WHERE id = ?");
                        $stmt->execute([$resultado['preference_id'], $pagamento_id]);
                        
                        error_log("Pagamento cartão/boleto criado para pagamento $pagamento_id");
                        
                        return [
                            'success' => true,
                            'message' => $resultado['message'] ?? 'Pagamento processado com sucesso!',
                            'redirect' => 'planos.php',
                            'preference_id' => $resultado['preference_id']
                        ];
                    } else {
                        error_log("Erro ao criar pagamento: " . ($resultado['message'] ?? 'Erro desconhecido'));
                        return ['success' => false, 'message' => $resultado['message'] ?? 'Erro ao criar pagamento'];
                    }
                }
                
            } catch (Exception $e) {
                error_log("Erro Mercado Pago: " . $e->getMessage());
                return ['success' => false, 'message' => 'Erro no gateway de pagamento: ' . $e->getMessage()];
            }
            
        } else {
            // Modo simples sem Mercado Pago - marcar como aprovado
            $stmt = $pdo->prepare("UPDATE pagamentos SET status = 'aprovado' WHERE id = ?");
            $stmt->execute([$pagamento_id]);
            
            // Criar/atualizar matrícula também
            $stmt = $pdo->prepare("SELECT id FROM matriculas WHERE aluno_id = ? AND status = 'ativa'");
            $stmt->execute([$usuario_id]);
            $matricula_existente = $stmt->fetch();
            
            if ($matricula_existente) {
                $stmt = $pdo->prepare("
                    UPDATE matriculas 
                    SET plano_id = ?, data_inicio = NOW(), data_fim = DATE_ADD(NOW(), INTERVAL ? DAY), status = 'ativa'
                    WHERE aluno_id = ?
                ");
                $stmt->execute([$plano_id, $plano['duracao_dias'], $usuario_id]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO matriculas (aluno_id, plano_id, status, data_inicio, data_fim) 
                    VALUES (?, ?, 'ativa', NOW(), DATE_ADD(NOW(), INTERVAL ? DAY))
                ");
                $stmt->execute([$usuario_id, $plano_id, $plano['duracao_dias']]);
            }
            
            error_log("Pagamento simulado aprovado: $pagamento_id");
            
            return [
                'success' => true,
                'message' => "Plano {$plano['nome']} contratado com sucesso! (Modo simulação)",
                'redirect' => 'index.php'
            ];
        }
        
    } catch (Exception $e) {
        error_log("Erro processarPagamento: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro ao processar pagamento: ' . $e->getMessage()];
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
// Adicione esta função junto com as outras funções de agenda
function consultarAgendaPersonal($pdo, $dados) {
    try {
        $personal_id = $dados['personal_id'];
        $data = $dados['data'];
        
        // Buscar apenas horários DISPONÍVEIS do personal para a data específica
        $stmt = $pdo->prepare("
            SELECT * FROM agenda 
            WHERE personal_id = ? 
            AND DATE(data_hora) = ?
            AND status = 'disponivel'
            AND aluno_id IS NULL
            ORDER BY data_hora ASC
        ");
        $stmt->execute([$personal_id, $data]);
        $agenda = $stmt->fetchAll();
        
        return [
            'success' => true,
            'agenda' => $agenda
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Erro ao consultar agenda: ' . $e->getMessage()
        ];
    }
}
// Função para criar slots padrão de horários
function criarSlotsPadrao($data, $personal_id) {
    $slots = [];
    $horarios = [
        '08:00:00', '09:00:00', '10:00:00', '11:00:00',
        '14:00:00', '15:00:00', '16:00:00', '17:00:00', '18:00:00'
    ];
    
    foreach ($horarios as $horario) {
        $slots[] = [
            'id' => null,
            'personal_id' => $personal_id,
            'aluno_id' => null,
            'data_hora' => $data . ' ' . $horario,
            'duracao_minutos' => 60,
            'status' => 'disponivel',
            'data_criacao' => date('Y-m-d H:i:s')
        ];
    }
    
    return $slots;
}

/**
 * Busca notificações do usuário
 */
function getNotificacoes($usuario_id) {
    global $pdo;
    
    try {
        // Verificar se a tabela notificacoes existe
        $tabela_existe = $pdo->query("SHOW TABLES LIKE 'notificacoes'")->rowCount() > 0;
        
        if (!$tabela_existe) {
            // Criar tabela se não existir
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS notificacoes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    usuario_id INT NOT NULL,
                    titulo VARCHAR(255) NOT NULL,
                    mensagem TEXT NOT NULL,
                    tipo VARCHAR(50) DEFAULT 'info',
                    lida TINYINT(1) DEFAULT 0,
                    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                    data_leitura DATETIME NULL,
                    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            
            return []; // Retorna array vazio na primeira execução
        }
        
        $stmt = $pdo->prepare("
            SELECT * FROM notificacoes 
            WHERE usuario_id = ? 
            ORDER BY data_criacao DESC 
            LIMIT 10
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Erro ao buscar notificações: " . $e->getMessage());
        return [];
    }
}

/**
 * Marca notificação como lida
 */
function marcarNotificacaoLida($notificacao_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE notificacoes 
            SET lida = 1, data_leitura = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$notificacao_id]);
        
        return ['success' => $stmt->rowCount() > 0];
        
    } catch (PDOException $e) {
        error_log("Erro ao marcar notificação como lida: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro ao marcar notificação como lida'];
    }
}



// ... (o restante das funções permanece igual)

/**
 * Personal cancela agendamento com justificativa
 */
function personalCancelarAgendamento($pdo, $dados) {
    try {
        $agenda_id = $dados['agenda_id'];
        $personal_id = $dados['personal_id'];
        $motivo = sanitizeInput($dados['motivo']);
        
        // Verificar se o agendamento existe e pertence ao personal
        $stmt = $pdo->prepare("
            SELECT a.*, u.nome as aluno_nome, u.email as aluno_email 
            FROM agenda a 
            LEFT JOIN usuarios u ON a.aluno_id = u.id 
            WHERE a.id = ? AND a.personal_id = ?
        ");
        $stmt->execute([$agenda_id, $personal_id]);
        $agendamento = $stmt->fetch();
        
        if (!$agendamento) {
            return ['success' => false, 'message' => 'Agendamento não encontrado'];
        }
        
        if ($agendamento['status'] != 'agendado') {
            return ['success' => false, 'message' => 'Este agendamento não está ativo'];
        }
        
        // Cancelar agendamento
        $stmt = $pdo->prepare("
            UPDATE agenda 
            SET status = 'cancelado_personal', motivo_cancelamento = ?, data_cancelamento = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$motivo, $agenda_id]);
        
        if ($stmt->rowCount() > 0) {
            // Notificar aluno
            notificarAlunoCancelamento($agendamento['aluno_id'], $agendamento, $motivo);
            
            logActivity($personal_id, 'cancelamento_personal', 
                "Cancelou agendamento do aluno " . $agendamento['aluno_nome'] . ". Motivo: " . $motivo);
            
            return ['success' => true, 'message' => 'Agendamento cancelado com sucesso! O aluno foi notificado.'];
        }
        
        return ['success' => false, 'message' => 'Erro ao cancelar agendamento'];
        
    } catch (PDOException $e) {
        error_log("Erro ao cancelar agendamento: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()];
    }
}

/**
 * Notifica aluno sobre cancelamento
 */
function notificarAlunoCancelamento($aluno_id, $agendamento, $motivo) {
    global $pdo;
    
    try {
        // Criar notificação para o aluno
        $stmt = $pdo->prepare("
            INSERT INTO notificacoes (usuario_id, titulo, mensagem, tipo, data_criacao) 
            VALUES (?, ?, ?, 'cancelamento', NOW())
        ");
        
        $personal_nome = getUserName($agendamento['personal_id']);
        $data_formatada = formatDateTime($agendamento['data_hora']);
        
        $titulo = "❌ Aula Cancelada";
        $mensagem = "Sua aula com {$personal_nome} em {$data_formatada} foi cancelada. Motivo: {$motivo}";
        
        $stmt->execute([$aluno_id, $titulo, $mensagem]);
        
        // Aqui você pode adicionar envio de email, push notification, etc.
        enviarEmailCancelamento($agendamento, $motivo);
        
    } catch (PDOException $e) {
        error_log("Erro ao criar notificação: " . $e->getMessage());
    }
}

/**
 * Envia email de cancelamento (placeholder - implemente conforme seu sistema de email)
 */
function enviarEmailCancelamento($agendamento, $motivo) {
    // Implemente o envio de email aqui
    error_log("Email de cancelamento para: " . ($agendamento['aluno_email'] ?? 'N/A') . 
              " - Motivo: " . $motivo);
}

/**
 * Busca nome do usuário
 */
function getUserName($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        return $user ? $user['nome'] : 'Usuário';
    } catch (PDOException $e) {
        error_log("Erro ao buscar nome do usuário: " . $e->getMessage());
        return 'Usuário';
    }
}

?>