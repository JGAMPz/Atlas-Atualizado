 <?php
session_start();
?>
 <!DOCTYPE html>
 <html lang="pt-BR">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Academia Fit - Portal Online</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <link href="assets/css/style.css" rel="stylesheet">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
 </head>

 <body>
     <!-- Header -->
     <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
         <div class="container">
             <a class="navbar-brand" href="index.php">
                 <i class="fas fa-dumbbell"></i> ATLAS
             </a>
             <div class="navbar-nav ms-auto">
                 <a class="nav-link" href="login.php">Login</a>
                 <a class="nav-link" href="cadastro.php">Cadastre-se</a>
             </div>
         </div>
     </nav>

     <!-- Hero Section -->
     <section class="hero-section bg-primary text-white py-5">
         <div class="container text-center">
             <h1 class="display-4 fw-bold">Bem-vindo ao ATLAS</h1>
             <p class="lead">Seu portal completo para fitness e bem-estar</p>
             <div class="row mt-5">
                 <div class="col-md-4">
                     <div class="card text-dark mb-4">
                         <div class="card-body">
                             <i class="fas fa-user-graduate fa-3x text-primary mb-3"></i>
                             <h5>Para Alunos</h5>
                             <p>Gerencie sua matrícula, agenda e pagamentos</p>
                             <a href="cadastro.php?tipo=aluno" class="btn btn-primary">Cadastrar como Aluno</a>
                         </div>
                     </div>
                 </div>
                 <div class="col-md-4">
                     <div class="card text-dark mb-4">
                         <div class="card-body">
                             <i class="fas fa-dumbbell fa-3x text-success mb-3"></i>
                             <h5>Para Personal Trainers</h5>
                             <p>Gerencie sua agenda e alunos</p>
                             <a href="cadastro.php?tipo=personal" class="btn btn-success">Cadastrar como Personal</a>
                         </div>
                     </div>
                 </div>
                 <div class="col-md-4">
                     <div class="card text-dark mb-4">
                         <div class="card-body">
                             <i class="fas fa-cogs fa-3x text-warning mb-3"></i>
                             <h5>Administradores</h5>
                             <p>Gerencie o sistema completo</p>
                             <a href="login.php" class="btn btn-warning">Acessar Admin</a>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </section>

     <!-- Features -->
     <section class="py-5">
         <div class="container">
             <h2 class="text-center mb-5">Recursos do Portal</h2>
             <div class="row">
                 <div class="col-md-3 text-center">
                     <i class="fas fa-calendar-alt fa-2x text-primary mb-3"></i>
                     <h5>Agendamento Online</h5>
                     <p>Agende horários com personal trainers</p>
                 </div>
                 <div class="col-md-3 text-center">
                     <i class="fas fa-credit-card fa-2x text-success mb-3"></i>
                     <h5>Pagamento Digital</h5>
                     <p>Pague suas mensalidades online</p>
                 </div>
                 <div class="col-md-3 text-center">
                     <i class="fas fa-chart-line fa-2x text-info mb-3"></i>
                     <h5>Acompanhamento</h5>
                     <p>Acompanhe seu progresso</p>
                 </div>
                 <div class="col-md-3 text-center">
                     <i class="fas fa-mobile-alt fa-2x text-warning mb-3"></i>
                     <h5>Totalmente Online</h5>
                     <p>Acesse de qualquer dispositivo</p>
                 </div>
             </div>
         </div>
     </section>

     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
     <script src="assets/js/main.js"></script>
 </body>

 </html>