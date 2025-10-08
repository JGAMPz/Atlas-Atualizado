<?php
// =============================================
// CONFIGURAÇÕES DO SISTEMA - ACADEMIA FIT
// =============================================

// Exibir erros (desativar em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'portal_academia');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configurações do sistema
define('SITE_NAME', 'ATLAS');
define('SITE_URL', 'http://localhost/portal-academia');
define('SITE_PATH', __DIR__ . '/..');
define('UPLOAD_PATH', SITE_PATH . '/assets/uploads');

// Configurações de sessão
define('SESSION_TIMEOUT', 3600); // 1 hora em segundos
define('SESSION_NAME', 'atlas');

// Configurações de segurança
define('PASSWORD_COST', 12);
define('TOKEN_EXPIRATION', 1800); // 30 minutos

// Configurações de pagamento (exemplo - configurar com dados reais)
define('PAGSEGURO_EMAIL', 'seu-email@pagseguro.com');
define('PAGSEGURO_TOKEN', 'seu-token-pagseguro');
define('PAGSEGURO_SANDBOX', true); // true para testes, false para produção

// Configurações de email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'seu-email@gmail.com');
define('SMTP_PASS', 'sua-senha-app');
define('SMTP_FROM', 'contato@academiafit.com');
define('SMTP_FROM_NAME', 'Academia Fit');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// =============================================
// CONEXÃO COM O BANCO DE DADOS
// =============================================
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => true
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch(PDOException $e) {
    // Log do erro (em produção)
    error_log("Erro de conexão: " . $e->getMessage());
    
    // Mensagem amigável para o usuário
    die("Erro de conexão com o banco de dados. Por favor, tente novamente mais tarde.");
}

// =============================================
// FUNÇÕES GLOBAIS DE CONFIGURAÇÃO
// =============================================

// Valida data de nascimento (idade entre 1 e 120 anos)
 
function validarDataNascimento($dataNascimento) {
    if (empty($dataNascimento)) {
        return ['valido' => false, 'mensagem' => 'Data de nascimento é obrigatória'];
    }
    
    $nascimento = new DateTime($dataNascimento);
    $hoje = new DateTime();
    $idade = $hoje->diff($nascimento)->y;
    
    if ($idade < 1) {
        return ['valido' => false, 'mensagem' => 'Você deve ter pelo menos 1 ano de idade'];
    }
    
    if ($idade > 120) {
        return ['valido' => false, 'mensagem' => 'Idade máxima permitida é 120 anos'];
    }
    
    // Verificar se a data não é futura
    if ($nascimento > $hoje) {
        return ['valido' => false, 'mensagem' => 'Data de nascimento não pode ser futura'];
    }
    
    return ['valido' => true, 'idade' => $idade];
}

// Inicializa a sessão com configurações de segurança
 
function initSession() {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    session_start();
    
    // Renovar ID da sessão periodicamente
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Verifica se a requisição é AJAX

function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Retorna a URL absoluta do sistema

function getBaseUrl() {
    return SITE_URL;
}

// Retorna o caminho absoluto do sistema

function getBasePath() {
    return SITE_PATH;
}

// Sanitiza dados de entrada

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Valida formato de email

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Valida formato de telefone brasileiro

function isValidPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 11;
}

// Gera token CSRF

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verifica token CSRF
 
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

// Redireciona para uma URL

function redirect($url, $permanent = false) {
    if ($permanent) {
        header('HTTP/1.1 301 Moved Permanently');
    }
    header('Location: ' . $url);
    exit();
}

// Formata valor monetário

function formatCurrency($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

//  Formata data para exibição

function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

// Formata data e hora para exibição
 
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

// Calcula idade a partir da data de nascimento

function calculateAge($birthdate) {
    $birth = new DateTime($birthdate);
    $today = new DateTime();
    $age = $today->diff($birth);
    return $age->y;
}

// Log de atividades do sistema

function logActivity($usuario_id, $acao, $detalhes = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO historico_acesso 
            (usuario_id, data_acesso, ip_address, user_agent) 
            VALUES (?, NOW(), ?, ?)
        ");
        
        $stmt->execute([
            $usuario_id,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        // Também loga em arquivo (para debug)
        $logMessage = date('Y-m-d H:i:s') . " - User: $usuario_id - Action: $acao - Details: $detalhes" . PHP_EOL;
        file_put_contents(SITE_PATH . '/logs/system.log', $logMessage, FILE_APPEND | LOCK_EX);
        
    } catch (Exception $e) {
        // Falha silenciosa no log
        error_log("Erro no log de atividade: " . $e->getMessage());
    }
}

/**
 * Envia notificação para usuário
 */
function sendNotification($usuario_id, $titulo, $mensagem, $tipo = 'info') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notificacoes 
            (usuario_id, titulo, mensagem, tipo) 
            VALUES (?, ?, ?, ?)
        ");
        
        return $stmt->execute([$usuario_id, $titulo, $mensagem, $tipo]);
        
    } catch (Exception $e) {
        error_log("Erro ao enviar notificação: " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica permissões de usuário
 */
function hasPermission($tipo_requerido, $tipo_usuario) {
    $hierarquia = [
        'admin' => 3,
        'personal' => 2, 
        'aluno' => 1
    ];
    
    return isset($hierarquia[$tipo_usuario]) && 
           $hierarquia[$tipo_usuario] >= $hierarquia[$tipo_requerido];
}

/**
 * Retorna array de tipos de usuário
 */
function getUserTypes() {
    return [
        'aluno' => 'Aluno',
        'personal' => 'Personal Trainer', 
        'admin' => 'Administrador'
    ];
}

/**
 * Retorna array de status de matrícula
 */
function getMatriculaStatus() {
    return [
        'ativa' => 'Ativa',
        'trancada' => 'Trancada',
        'cancelada' => 'Cancelada',
        'expirada' => 'Expirada'
    ];
}

/**
 * Retorna array de métodos de pagamento
 */
function getPaymentMethods() {
    return [
        'cartao_credito' => 'Cartão de Crédito',
        'pix' => 'PIX',
        'boleto' => 'Boleto Bancário',
        'dinheiro' => 'Dinheiro',
        'transferencia' => 'Transferência'
    ];
}

/**
 * Retorna array de tipos de aula
 */
function getClassTypes() {
    return [
        'musculacao' => 'Musculação',
        'pilates' => 'Pilates',
        'yoga' => 'Yoga', 
        'funcional' => 'Treino Funcional',
        'outros' => 'Outros'
    ];
}

// Inicializar sessão
//initSession();

// Gerar token CSRF se não existir
//if (empty($_SESSION['csrf_token'])) {
   // generateCsrfToken();
//}

// Verificar se precisa fazer manutenção do banco
register_shutdown_function(function() {
    // Limpar sessões expiradas, etc.
});

?>