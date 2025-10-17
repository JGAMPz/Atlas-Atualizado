<?php
// processar_pagamento.php

// Iniciar sessão apenas se não estiver ativa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Verificar autenticação
verificarTipo('aluno');
$usuario = getUsuarioInfo();

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit;
}

// Verificar dados
if (!isset($_POST['plano_id']) || !isset($_POST['metodo_pagamento'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    exit;
}

$plano_id = intval($_POST['plano_id']);
$metodo_pagamento = $_POST['metodo_pagamento'];

// Validar método de pagamento
$metodos_validos = ['pix', 'cartao', 'boleto'];
if (!in_array($metodo_pagamento, $metodos_validos)) {
    echo json_encode(['success' => false, 'message' => 'Método de pagamento inválido.']);
    exit;
}

try {
    // Buscar dados do plano
    $stmt = $pdo->prepare("SELECT * FROM planos WHERE id = ? AND status = 'ativo'");
    $stmt->execute([$plano_id]);
    $plano = $stmt->fetch();
    
    if (!$plano) {
        echo json_encode(['success' => false, 'message' => 'Plano não encontrado ou inativo.']);
        exit;
    }

    // Verificar se a tabela pagamentos existe
    $tabela_existe = false;
    try {
        $teste = $pdo->query("SELECT 1 FROM pagamentos LIMIT 1");
        $tabela_existe = true;
    } catch (Exception $e) {
        $tabela_existe = false;
    }

    $pagamento_id = null;

    if ($tabela_existe) {
        // Usar tabela pagamentos se existir
        $stmt = $pdo->prepare("
            INSERT INTO pagamentos (usuario_id, plano_id, valor, metodo_pagamento, status, data_criacao) 
            VALUES (?, ?, ?, ?, 'pendente', NOW())
        ");
        $stmt->execute([$usuario['id'], $plano_id, $plano['preco'], $metodo_pagamento]);
        $pagamento_id = $pdo->lastInsertId();
        
        error_log("Pagamento criado: ID $pagamento_id para usuário {$usuario['id']}");
    } else {
        // Se não existir tabela pagamentos, criar matrícula direto
        $stmt = $pdo->prepare("SELECT id FROM matriculas WHERE aluno_id = ? AND status = 'ativa'");
        $stmt->execute([$usuario['id']]);
        $matricula_existente = $stmt->fetch();
        
        if ($matricula_existente) {
            // Atualizar matrícula existente
            $stmt = $pdo->prepare("
                UPDATE matriculas 
                SET plano_id = ?, data_inicio = NOW(), data_fim = DATE_ADD(NOW(), INTERVAL ? DAY), status = 'ativa'
                WHERE aluno_id = ?
            ");
            $stmt->execute([$plano_id, $plano['duracao_dias'], $usuario['id']]);
            error_log("Matrícula atualizada para usuário {$usuario['id']}");
        } else {
            // Criar nova matrícula
            $stmt = $pdo->prepare("
                INSERT INTO matriculas (aluno_id, plano_id, status, data_inicio, data_fim) 
                VALUES (?, ?, 'ativa', NOW(), DATE_ADD(NOW(), INTERVAL ? DAY))
            ");
            $stmt->execute([$usuario['id'], $plano_id, $plano['duracao_dias']]);
            error_log("Nova matrícula criada para usuário {$usuario['id']}");
        }
        
        // Sucesso - matrícula criada/atualizada
        echo json_encode([
            'success' => true, 
            'message' => "Plano {$plano['nome']} contratado com sucesso!",
            'redirect' => 'index.php'
        ]);
        exit;
    }

    // Processar com Mercado Pago se disponível
    $mp_config_path = '../../includes/mercadopago_config.php';
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
                    // Atualizar pagamento com dados do PIX
                    if ($tabela_existe) {
                        $stmt = $pdo->prepare("
                            UPDATE pagamentos 
                            SET id_mp = ?, qr_code = ?, pix_copia_cola = ?, status = 'aguardando'
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $resultado['payment_id'],
                            $resultado['qr_code'],
                            $resultado['qr_code_text'],
                            $pagamento_id
                        ]);
                    }
                    
                    // Sucesso com PIX
                    echo json_encode([
                        'success' => true,
                        'message' => $resultado['message'] ?? 'Pagamento PIX criado com sucesso!',
                        'redirect' => 'pagamento_pix.php',
                        'pagamento_id' => $pagamento_id
                    ]);
                    exit;
                } else {
                    throw new Exception($resultado['message'] ?? 'Erro ao criar pagamento PIX');
                }
            } else {
                $resultado = $mp->criarPagamento($dados_pagamento);
                
                if ($resultado['success']) {
                    if ($tabela_existe) {
                        $stmt = $pdo->prepare("UPDATE pagamentos SET id_mp = ?, status = 'processando' WHERE id = ?");
                        $stmt->execute([$resultado['preference_id'], $pagamento_id]);
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => $resultado['message'] ?? 'Pagamento processado com sucesso!',
                        'redirect' => 'planos.php',
                        'preference_id' => $resultado['preference_id']
                    ]);
                    exit;
                } else {
                    throw new Exception($resultado['message'] ?? 'Erro ao criar pagamento');
                }
            }
            
        } catch (Exception $e) {
            error_log("Erro Mercado Pago: " . $e->getMessage());
            throw new Exception('Erro no gateway de pagamento: ' . $e->getMessage());
        }
        
    } else {
        // Modo simples sem Mercado Pago - apenas atualizar status para pago
        if ($tabela_existe) {
            $stmt = $pdo->prepare("UPDATE pagamentos SET status = 'pago' WHERE id = ?");
            $stmt->execute([$pagamento_id]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Plano {$plano['nome']} contratado com sucesso! (Modo simulação)",
            'redirect' => 'index.php'
        ]);
        exit;
    }
    
} catch (Exception $e) {
    error_log("Erro processar_pagamento: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao processar pagamento: ' . $e->getMessage()
    ]);
    exit;
}
?>