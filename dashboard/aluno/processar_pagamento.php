<?php
// Incluir os arquivos necessários PRIMEIRO
require_once '../../includes/config.php'; // Este define $pdo
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Agora verificar autenticação
verificarTipo('aluno');

$usuario = getUsuarioInfo();

// DEBUG - Verificar se está funcionando
error_log("=== PROCESSAR PAGAMENTO INICIADO ===");
error_log("Usuário: " . $usuario['nome']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("❌ Não é POST - Redirecionando");
    $_SESSION['erro'] = 'Método inválido.';
    header('Location: planos.php');
    exit;
}

// Verificar se os dados foram enviados
if (!isset($_POST['plano_id']) || !isset($_POST['metodo_pagamento'])) {
    error_log("❌ Dados incompletos");
    $_SESSION['erro'] = 'Dados incompletos.';
    header('Location: planos.php');
    exit;
}

$plano_id = $_POST['plano_id'];
$metodo_pagamento = $_POST['metodo_pagamento'];

error_log("Plano ID: $plano_id, Método: $metodo_pagamento");

try {
    // Buscar dados do plano
    $stmt = $pdo->prepare("SELECT * FROM planos WHERE id = ? AND status = 'ativo'");
    $stmt->execute([$plano_id]);
    $plano = $stmt->fetch();
    
    if (!$plano) {
        error_log("❌ Plano não encontrado: $plano_id");
        $_SESSION['erro'] = 'Plano não encontrado.';
        header('Location: planos.php');
        exit;
    }

    error_log("✅ Plano encontrado: " . $plano['nome']);

    // Verificar se a tabela pagamentos existe
    $tabela_existe = false;
    try {
        $teste = $pdo->query("SELECT 1 FROM pagamentos LIMIT 1");
        $tabela_existe = true;
    } catch (Exception $e) {
        $tabela_existe = false;
    }

    if ($tabela_existe) {
        // Usar tabela pagamentos se existir
        $stmt = $pdo->prepare("
            INSERT INTO pagamentos (usuario_id, plano_id, valor, metodo_pagamento, status) 
            VALUES (?, ?, ?, ?, 'pendente')
        ");
        $stmt->execute([$usuario['id'], $plano_id, $plano['preco'], $metodo_pagamento]);
        $pagamento_id = $pdo->lastInsertId();
        error_log("✅ Pagamento criado na tabela pagamentos: $pagamento_id");
    } else {
        // Se não existir tabela pagamentos, criar matrícula direto
        error_log("⚠️ Tabela pagamentos não existe, criando matrícula direto");
        
        // Verificar se já tem matrícula ativa
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
            error_log("✅ Matrícula atualizada");
        } else {
            // Criar nova matrícula
            $stmt = $pdo->prepare("
                INSERT INTO matriculas (aluno_id, plano_id, status, data_inicio, data_fim) 
                VALUES (?, ?, 'ativa', NOW(), DATE_ADD(NOW(), INTERVAL ? DAY))
            ");
            $stmt->execute([$usuario['id'], $plano_id, $plano['duracao_dias']]);
            error_log("✅ Nova matrícula criada");
        }
        
        $_SESSION['sucesso'] = "Plano <strong>{$plano['nome']}</strong> contratado com sucesso!";
        header('Location: index.php');
        exit;
    }

    // Tentar carregar Mercado Pago (modo simulação)
    $mp_config_path = '../../includes/mercadopago_config.php';
    if (file_exists($mp_config_path)) {
        require_once $mp_config_path;
        $mp = new MercadoPagoIntegration();
        error_log("✅ MercadoPagoIntegration carregado");
        
        $dados_pagamento = [
            'pagamento_id' => $pagamento_id,
            'plano_nome' => $plano['nome'],
            'valor' => (float)$plano['preco'],
            'email' => $usuario['email'],
            'nome' => $usuario['nome'],
            'cpf' => '00000000000'
        ];

        if ($metodo_pagamento === 'pix') {
            // Pagamento via PIX (simulado)
            $resultado = $mp->criarPagamentoPix($dados_pagamento);
            error_log("✅ PIX criado: " . print_r($resultado, true));
            
            if ($resultado['success']) {
                // Atualizar pagamento com dados do PIX
                if ($tabela_existe) {
                    $stmt = $pdo->prepare("
                        UPDATE pagamentos 
                        SET id_mp = ?, qr_code = ?, pix_copia_cola = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $resultado['payment_id'],
                        $resultado['qr_code'],
                        $resultado['qr_code_text'],
                        $pagamento_id
                    ]);
                }
                
                // Redirecionar para página do PIX
                $_SESSION['pagamento_pix'] = [
                    'pagamento_id' => $pagamento_id,
                    'qr_code' => $resultado['qr_code'],
                    'pix_copia_cola' => $resultado['qr_code_text'],
                    'valor' => $plano['preco'],
                    'plano_nome' => $plano['nome'],
                    'mensagem' => $resultado['message'] ?? ''
                ];
                
                header('Location: pagamento_pix.php');
                exit;
            }
            
        } else {
            // Pagamento via cartão ou boleto (simulado)
            $resultado = $mp->criarPagamento($dados_pagamento);
            error_log("✅ Pagamento criado: " . print_r($resultado, true));
            
            if ($resultado['success']) {
                // Atualizar pagamento com ID da preferência
                if ($tabela_existe) {
                    $stmt = $pdo->prepare("UPDATE pagamentos SET id_mp = ? WHERE id = ?");
                    $stmt->execute([$resultado['preference_id'], $pagamento_id]);
                }
                
                // Em modo simulação: mostrar mensagem
                $_SESSION['sucesso'] = ($resultado['message'] ?? 'Pagamento processado com sucesso!') . 
                                     ' Preference ID: ' . $resultado['preference_id'];
                header('Location: planos.php');
                exit;
            }
        }
        
        // Se chegou aqui, houve erro no Mercado Pago
        $_SESSION['erro'] = 'Erro ao processar pagamento: ' . ($resultado['message'] ?? 'Erro desconhecido');
        header('Location: planos.php');
        exit;
        
    } else {
        error_log("⚠️ Arquivo Mercado Pago não encontrado, usando modo simples");
        // Modo simples sem Mercado Pago
        $_SESSION['sucesso'] = "Plano <strong>{$plano['nome']}</strong> contratado com sucesso! (Modo simulação)";
        header('Location: index.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("❌ Erro processar pagamento: " . $e->getMessage());
    $_SESSION['erro'] = 'Erro ao processar pagamento: ' . $e->getMessage();
    header('Location: planos.php');
    exit;
}
?>