 <?php
session_start();
$tipo = $_GET['tipo'] ?? '';
?>
 <!DOCTYPE html>
 <html lang="pt-BR">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Cadastro - Academia Fit</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <link href="assets/css/style.css" rel="stylesheet">
 </head>

 <body class="login-body">
     <div class="container">
         <div class="row justify-content-center min-vh-100 align-items-center">
             <div class="col-md-8 col-lg-6">
                 <div class="card shadow">
                     <div class="card-body p-5">
                         <div class="text-center mb-4">
                             <i class="fas fa-user-plus fa-2x text-primary mb-3"></i>
                             <h3 class="card-title">Cadastro</h3>
                             <p class="text-muted">Crie sua conta</p>
                         </div>

                         <form id="cadastroForm">
                             <div class="mb-3">
                                 <label for="tipo_usuario" class="form-label">Tipo de Usuário</label>
                                 <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                                     <option value="">Selecione...</option>
                                     <option value="aluno" <?= $tipo == 'aluno' ? 'selected' : '' ?>>Aluno</option>
                                     <option value="personal" <?= $tipo == 'personal' ? 'selected' : '' ?>>Personal
                                         Trainer</option>
                                 </select>
                             </div>

                             <div class="row">
                                 <div class="col-md-6">
                                     <div class="mb-3">
                                         <label for="nome" class="form-label">Nome Completo</label>
                                         <input type="text" class="form-control" id="nome" name="nome" required>
                                     </div>
                                 </div>
                                 <div class="col-md-6">
                                     <div class="mb-3">
                                         <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                         <input type="date" class="form-control" id="data_nascimento"
                                             name="data_nascimento" required>
                                     </div>
                                 </div>
                             </div>

                             <div class="mb-3">
                                 <label for="email" class="form-label">E-mail</label>
                                 <input type="email" class="form-control" id="email" name="email" required>
                             </div>

                             <div class="mb-3">
                                 <label for="telefone" class="form-label">Telefone</label>
                                 <input type="tel" class="form-control" id="telefone" name="telefone" required>
                             </div>

                             <div class="mb-3">
                                 <label for="endereco" class="form-label">Endereço</label>
                                 <textarea class="form-control" id="endereco" name="endereco" rows="2"></textarea>
                             </div>

                             <div class="row">
                                 <div class="col-md-6">
                                     <div class="mb-3">
                                         <label for="senha" class="form-label">Senha</label>
                                         <input type="password" class="form-control" id="senha" name="senha" required>
                                     </div>
                                 </div>
                                 <div class="col-md-6">
                                     <div class="mb-3">
                                         <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                                         <input type="password" class="form-control" id="confirmar_senha"
                                             name="confirmar_senha" required>
                                     </div>
                                 </div>
                             </div>

                             <button type="submit" class="btn btn-primary w-100 mb-3">Cadastrar</button>

                             <div class="text-center">
                                 <p class="mb-0">Já tem uma conta? <a href="login.php" class="text-decoration-none">Faça
                                         login</a></p>
                             </div>
                         </form>
                     </div>
                 </div>
             </div>
         </div>
     </div>

     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
     <script src="assets/js/main.js"></script>
 </body>

 </html>