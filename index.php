<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATLAS - Seu Portal Fitness Premium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root {
        --azul-primario: #1e40af;
        --azul-secundario: #3b82f6;
        --laranja: #f97316;
        --laranja-claro: #fdba74;
        --dourado: #f59e0b;
        --preto: #1f2937;
        --cinza-escuro: #374151;
        --cinza-claro: #f3f4f6;
    }

    .gradient-bg {
        background: linear-gradient(135deg, var(--azul-primario) 0%, var(--azul-secundario) 100%);
    }

    .gradient-orange {
        background: linear-gradient(135deg, var(--laranja) 0%, var(--dourado) 100%);
    }

    .btn-gold {
        background: linear-gradient(135deg, var(--dourado) 0%, var(--laranja) 100%);
        border: none;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-gold:hover {
        background: linear-gradient(135deg, var(--laranja) 0%, var(--dourado) 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.3);
        color: white;
    }

    .btn-orange {
        background: var(--laranja);
        border: none;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-orange:hover {
        background: #ea580c;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(249, 115, 22, 0.3);
        color: white;
    }

    .btn-outline-gold {
        border: 2px solid var(--dourado);
        color: var(--dourado);
        background: transparent;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-outline-gold:hover {
        background: var(--dourado);
        color: white;
        transform: translateY(-2px);
    }

    .navbar-brand {
        font-weight: 800;
        font-size: 1.8rem;
        background: linear-gradient(135deg, var(--dourado) 0%, var(--laranja) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-section {
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" opacity="0.05"><path fill="%23ffffff" d="M500,100C276.1,100,100,276.1,100,500s176.1,400,400,400s400-176.1,400-400S723.9,100,500,100z M500,850c-192.8,0-350-157.2-350-350S307.2,150,500,150s350,157.2,350,350S692.8,850,500,850z"/></svg>') center/cover;
    }

    .card-hover {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .feature-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.75rem;
    }

    .stats-section {
        background: linear-gradient(135deg, var(--preto) 0%, var(--cinza-escuro) 100%);
    }

    .testimonial-card {
        border-left: 4px solid var(--dourado);
    }

    .footer {
        background: var(--preto);
    }

    .pulse-animation {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .floating {
        animation: floating 3s ease-in-out infinite;
    }

    @keyframes floating {
        0% {
            transform: translate(0, 0px);
        }

        50% {
            transform: translate(0, 15px);
        }

        100% {
            transform: translate(0, -0px);
        }
    }

    /* Centralização dos cards */
    .cards-center-container {
        display: flex;
        justify-content: center;
    }
    </style>
</head>

<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--preto);">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-dumbbell me-2"></i>ATLAS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-gold ms-2 px-3" href="cadastro.php">
                            <i class="fas fa-user-plus me-1"></i> Cadastre-se
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section gradient-bg text-white py-5">
        <div class="container position-relative">
            <div class="row align-items-center min-vh-70 py-5">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Transforme seu <span class="text-warning">corpo</span>,
                        transforme sua <span class="text-warning">vida</span></h1>
                    <p class="lead mb-4">O ATLAS é seu portal completo para fitness, bem-estar e resultados reais.
                        Junte-se a milhares de pessoas que já transformaram suas vidas conosco.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="cadastro.php" class="btn btn-gold btn-lg px-4 py-3 pulse-animation">
                            <i class="fas fa-rocket me-2"></i> Comece Agora
                        </a>
                        <a href="#planos" class="btn btn-outline-light btn-lg px-4 py-3">
                            <i class="fas fa-crown me-2"></i> Conhecer Planos
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center mt-5 mt-lg-0">
                    <div class="floating">
                        <img src="assets/img/undraw_personal-trainer_bqkg.svg" alt="Fitness"
                            class="img-fluid rounded-3 shadow-lg" style="max-width: 400px;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cards de Usuário -->
    <section class="py-5 bg-light" id="planos">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark mb-3">Escolha seu Perfil</h2>
                <p class="lead text-muted">Selecione o tipo de conta que melhor se adapta às suas necessidades</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-5">
                    <div class="card card-hover border-0 h-100 text-center">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-3">Para Alunos</h4>
                            <p class="text-muted mb-4">Acesso completo à academia, agendamento de aulas, acompanhamento
                                de progresso e muito mais.</p>
                            <div class="mt-auto">
                                <a href="cadastro.php?tipo=aluno" class="btn btn-primary px-4">
                                    <i class="fas fa-user-plus me-2"></i> Cadastrar como Aluno
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card card-hover border-0 h-100 text-center">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-3">Personal Trainers</h4>
                            <p class="text-muted mb-4">Gerencie sua agenda, acompanhe alunos, crie planos de treino e
                                expanda seu negócio.</p>
                            <div class="mt-auto">
                                <a href="cadastro.php?tipo=personal" class="btn btn-orange px-4">
                                    <i class="fas fa-chart-line me-2"></i> Cadastrar como Personal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recursos -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark mb-3">Recursos Exclusivos</h2>
                <p class="lead text-muted">Tudo que você precisa para sua jornada fitness em um só lugar</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3 text-center">
                    <div class="feature-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3">Agendamento Inteligente</h5>
                    <p class="text-muted">Marque horários com personal trainers de forma rápida e intuitiva</p>
                </div>
                <div class="col-md-3 text-center">
                    <div class="feature-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3">Pagamentos Seguros</h5>
                    <p class="text-muted">Sistema de pagamento digital com total segurança e praticidade</p>
                </div>
                <div class="col-md-3 text-center">
                    <div class="feature-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3">Acompanhamento Detalhado</h5>
                    <p class="text-muted">Monitore seu progresso com relatórios e métricas detalhadas</p>
                </div>
                <div class="col-md-3 text-center">
                    <div class="feature-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3">Experiência Mobile</h5>
                    <p class="text-muted">Acesse de qualquer dispositivo com nossa interface responsiva</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Estatísticas -->
    <section class="stats-section text-white py-5">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <h2 class="fw-bold text-warning display-4">2K+</h2>
                    <p class="lead">Alunos Ativos</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h2 class="fw-bold text-warning display-4">150+</h2>
                    <p class="lead">Personal Trainers</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h2 class="fw-bold text-warning display-4">15K+</h2>
                    <p class="lead">Aulas Realizadas</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h2 class="fw-bold text-warning display-4">98%</h2>
                    <p class="lead">Satisfação dos Clientes</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Depoimentos -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-dark mb-3">O que dizem sobre nós</h2>
                <p class="lead text-muted">Histórias reais de transformação e sucesso</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card card-hover border-0 h-100">
                        <div class="card-body p-4 testimonial-card">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0">Carlos Silva</h5>
                                    <small class="text-muted">Aluno há 1 ano</small>
                                </div>
                            </div>
                            <p class="text-muted">"Perdi 15kg com o acompanhamento dos personal trainers do ATLAS. A
                                plataforma facilita todo o processo!"</p>
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-hover border-0 h-100">
                        <div class="card-body p-4 testimonial-card">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0">Mariana Costa</h5>
                                    <small class="text-muted">Personal Trainer</small>
                                </div>
                            </div>
                            <p class="text-muted">"O ATLAS revolucionou minha forma de trabalhar. Consigo gerenciar meus
                                alunos e agenda com muita facilidade."</p>
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-hover border-0 h-100">
                        <div class="card-body p-4 testimonial-card">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 me-3">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0">Roberto Alves</h5>
                                    <small class="text-muted">Aluno há 6 meses</small>
                                </div>
                            </div>
                            <p class="text-muted">"A experiência mobile é incrível! Consigo agendar aulas e acompanhar
                                meu progresso de qualquer lugar."</p>
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-5 gradient-bg text-white">
        <div class="container text-center">
            <h2 class="fw-bold mb-4 display-5">Pronto para começar sua transformação?</h2>
            <p class="lead mb-5">Junte-se à comunidade ATLAS e dê o primeiro passo para uma vida mais saudável e ativa
            </p>
            <a href="cadastro.php" class="btn btn-gold btn-lg px-5 py-3">
                <i class="fas fa-rocket me-2"></i> Comece Agora Gratuitamente
            </a>
        </div>
    </section>

    <?php include './includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>