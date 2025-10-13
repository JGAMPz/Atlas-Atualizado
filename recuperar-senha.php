 <!DOCTYPE html>
 <html lang="pt-BR">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Recuperar Senha - Academia Fit</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <link href="assets/css/style.css" rel="stylesheet">
 </head>

 <body class="login-body">
     <div class="container">
         <div class="row justify-content-center min-vh-100 align-items-center">
             <div class="col-md-6 col-lg-4">
                 <div class="card shadow">
                     <div class="card-body p-5">
                         <div class="text-center mb-4">
                             <i class="fas fa-key fa-2x text-primary mb-3"></i>
                             <h3 class="card-title">Recuperar Senha</h3>
                             <p class="text-muted">Informe seu e-mail para recuperação</p>
                         </div>

                         <form id="recuperarSenhaForm">
                             <div class="mb-3">
                                 <label for="email" class="form-label">E-mail</label>
                                 <input type="email" class="form-control" id="email" name="email" required>
                             </div>

                             <button type="submit" class="btn btn-primary w-100 mb-3">Recuperar Senha</button>

                             <div class="text-center">
                                 <a href="login.php" class="text-decoration-none">Voltar ao Login</a>
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