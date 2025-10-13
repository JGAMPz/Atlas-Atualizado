<?php
// create_admin.php
// Script para inserir um usuário admin na tabela `usuarios`
// ATENÇÃO: ajuste as credenciais do DB e a senha padrão antes de usar.

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

// ----- CONFIGURE AQUI -----
const DB_HOST = '127.0.0.1';
const DB_NAME = 'atlas_tcc';
const DB_USER = 'root';
const DB_PASS = '@lablemos2019';
const DB_CHARSET = 'utf8mb4';
// --------------------------

$admin = [
    'nome' => 'Administrador do Sistema',
    'cpf' => null,
    'sexo' => null,
    // **IMPORTANTE**: aqui o nome da chave deve corresponder à coluna da tabela (emailIndex)
    'email' => 'admin@atlas.com',
    // senha em texto; será hasheada com password_hash()
    'senha' => 'admin123',   // troque para uma senha forte
    'tipo' => 'admin',
    'descricao' => 'Usuário com privilégios administrativos',
    'telefone' => null,
    'data_nascimento' => null,     // formato 'YYYY-MM-DD' ou null
    'status' => 'ativo',
    'endereco' => 'rondonia morro do fubá'
];

try {
    // CORREÇÃO: usar as constantes DB_HOST e DB_NAME no DSN (antes usava variáveis inexistentes)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // Verifica se já existe um admin com esse email (coluna emailIndex)
    $stmt = $pdo->prepare("SELECT id, tipo FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $admin['email']]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "Já existe um usuário com este email (id: {$existing['id']}, tipo: {$existing['tipo']}).\n";
        exit;
    }

    // Validações mínimas — usamos as chaves corretas ('emailIndex' e 'senha_plain')
    if (!filter_var($admin['email'], FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException("Email inválido: {$admin['email']}");
    }
    if (empty($admin['senha']) || strlen($admin['senha']) < 8) {
        throw new RuntimeException("Senha inválida: use pelo menos 8 caracteres.");
    }

    // Hash da senha (bcrypt)
    $senha_hash = password_hash($admin['senha'], PASSWORD_BCRYPT);
    if ($senha_hash === false) {
        throw new RuntimeException("Falha ao gerar hash da senha.");
    }

    // Campos que serão inseridos (não insira idPrimary porque é AUTO_INCREMENT)
    $fields = [
        'nome',
        'cpf',
        'sexo',
        'email',
        'senha',
        'tipo',
        'descricao',
        'telefone',
        'data_nascimento',
        'status',
        'endereco'
    ];

    // Monta placeholders e valores
    $placeholders = [];
    $values = [];
    foreach ($fields as $f) {
        $placeholders[] = ':' . $f;
        if ($f === 'senha') {
            $values[':senha'] = $senha_hash;
        } elseif (array_key_exists($f, $admin)) {
            $values[':' . $f] = $admin[$f];
        } else {
            $values[':' . $f] = null;
        }
    }

    $sql = "INSERT INTO usuarios (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";

    $pdo->beginTransaction();
    $insert = $pdo->prepare($sql);
    $insert->execute($values);
    $newId = (int)$pdo->lastInsertId();
    $pdo->commit();

    echo "Admin criado com sucesso! id = {$newId}\n";
    echo "Email: {$admin['email']}\n";
    echo "Senha (texto): {$admin['senha']}\n";
    echo ">>> TROQUE ESSA SENHA IMEDIATAMENTE APÓS LOGIN <<<\n";

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Erro no banco de dados: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}
