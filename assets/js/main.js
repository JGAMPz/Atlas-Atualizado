// =============================================
// JAVASCRIPT GLOBAL - ACADEMIA FIT
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    initializeSystem();
});

/**
 * Valida data de nascimento
 */
function validarDataNascimento(dataNascimento) {
    const nascimento = new Date(dataNascimento);
    const hoje = new Date();
    const idade = hoje.getFullYear() - nascimento.getFullYear();
    
    // Ajuste para o mês/dia
    const mesAtual = hoje.getMonth();
    const diaAtual = hoje.getDate();
    const mesNascimento = nascimento.getMonth();
    const diaNascimento = nascimento.getDate();
    
    let idadeCorrigida = idade;
    
    if (mesAtual < mesNascimento || (mesAtual === mesNascimento && diaAtual < diaNascimento)) {
        idadeCorrigida--;
    }
    
    return {
        valida: idadeCorrigida >= 1 && idadeCorrigida <= 120,
        idade: idadeCorrigida,
        mensagem: idadeCorrigida < 1 ? 
            'Você deve ter pelo menos 1 ano de idade' : 
            'Idade máxima permitida é 120 anos'
    };
}

/**
 * Adiciona validação em tempo real para data de nascimento
 */
function inicializarValidacaoDataNascimento() {
    const dataNascimentoInput = document.getElementById('data_nascimento');
    
    if (dataNascimentoInput) {
        // Validação em tempo real
        dataNascimentoInput.addEventListener('change', function() {
            const validacao = validarDataNascimento(this.value);
            
            if (!validacao.valida) {
                this.setCustomValidity(validacao.mensagem);
                this.reportValidity();
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Também validar no submit do formulário
        const form = dataNascimentoInput.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const validacao = validarDataNascimento(dataNascimentoInput.value);
                
                if (!validacao.valida) {
                    e.preventDefault();
                    alert(validacao.mensagem);
                    dataNascimentoInput.focus();
                }
            });
        }
    }
}

/**
 * Inicializa funcionalidades do sistema
 */
function initializeSystem() {
    // Inicializar tooltips do Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializar formulários
    initializeForms();
    
    // Inicializar máscaras
    initializeMasks();
    
    // Inicializar formulário multi-step
    initializeMultiStepForm();
    
    // Configurar CSRF token para requisições AJAX
    setupCSRF();
}

/**
 * Inicializa máscaras para campos de formulário
 */
function initializeMasks() {
    // Máscara para telefone
    const telefoneInputs = document.querySelectorAll('input[type="tel"]');
    telefoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else {
                value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
            }
            
            e.target.value = value;
        });
    });

    // Máscara para CPF (se necessário)
    const cpfInputs = document.querySelectorAll('input[data-mask="cpf"]');
    cpfInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            e.target.value = value;
        });
    });
}

/**
 * Inicializa formulário multi-step
 */
function initializeMultiStepForm() {
    const cadastroForm = document.getElementById('cadastroForm');
    if (!cadastroForm) return;

    let currentStep = 1;
    const totalSteps = 3;

    // Inicializar progresso
    updateProgress();

    // Seleção de tipo de usuário
    document.querySelectorAll('.type-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.type-option').forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            document.getElementById('tipo_usuario').value = this.dataset.type;
        });
    });

    // Navegação entre steps
    window.nextStep = function(step) {
        if (validateStep(currentStep)) {
            document.getElementById(`step${currentStep}`).classList.remove('active');
            document.getElementById(`step${step}`).classList.add('active');
            document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
            document.querySelector(`.step[data-step="${step}"]`).classList.add('active');
            currentStep = step;
            updateProgress();
        }
    };

    window.prevStep = function(step) {
        document.getElementById(`step${currentStep}`).classList.remove('active');
        document.getElementById(`step${step}`).classList.add('active');
        document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
        document.querySelector(`.step[data-step="${step}"]`).classList.add('active');
        currentStep = step;
        updateProgress();
    };

    function updateProgress() {
        const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
        const progressFill = document.getElementById('progressFill');
        if (progressFill) {
            progressFill.style.width = `${progress}%`;
        }
    }

    // Validação de steps
    function validateStep(step) {
        switch(step) {
            case 1:
                const tipo = document.getElementById('tipo_usuario').value;
                if (!tipo) {
                    alert('Por favor, selecione um tipo de usuário');
                    return false;
                }
                return true;
            case 2:
                const nome = document.getElementById('nome').value;
                const email = document.getElementById('email').value;
                const telefone = document.getElementById('telefone').value;
                
                if (!nome || !email || !telefone) {
                    alert('Por favor, preencha todos os campos obrigatórios');
                    return false;
                }
                return true;
            default:
                return true;
        }
    }

    // Verificação de força da senha
    const senhaInput = document.getElementById('senha');
    if (senhaInput) {
        senhaInput.addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('passwordStrength');
            const feedback = document.getElementById('passwordFeedback');
            
            if (!strengthBar || !feedback) return;
            
            let strength = 0;
            let feedbackText = '';

            if (password.length >= 6) strength += 25;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 25;
            if (password.match(/\d/)) strength += 25;
            if (password.match(/[^a-zA-Z\d]/)) strength += 25;

            strengthBar.style.width = `${strength}%`;
            
            if (strength < 50) {
                strengthBar.className = 'strength-fill strength-weak';
                feedbackText = 'Senha fraca';
            } else if (strength < 75) {
                strengthBar.className = 'strength-fill strength-medium';
                feedbackText = 'Senha média';
            } else {
                strengthBar.className = 'strength-fill strength-strong';
                feedbackText = 'Senha forte';
            }
            
            feedback.textContent = feedbackText;
        });
    }

    // Verificação de confirmação de senha
    const confirmarSenhaInput = document.getElementById('confirmar_senha');
    if (confirmarSenhaInput) {
        confirmarSenhaInput.addEventListener('input', function(e) {
            const senha = document.getElementById('senha').value;
            const confirmar = e.target.value;
            const matchText = document.getElementById('passwordMatch');
            
            if (!matchText) return;
            
            if (confirmar) {
                if (senha === confirmar) {
                    matchText.textContent = 'Senhas coincidem';
                    matchText.style.color = '#10b981';
                } else {
                    matchText.textContent = 'Senhas não coincidem';
                    matchText.style.color = '#ef4444';
                }
            } else {
                matchText.textContent = '';
            }
        });
    }

    // Auto-selecionar tipo baseado na URL
    const urlParams = new URLSearchParams(window.location.search);
    const tipo = urlParams.get('tipo');
    
    if (tipo) {
        const option = document.querySelector(`.type-option[data-type="${tipo}"]`);
        if (option) {
            option.click();
        }
    }
}

/**
 * Configura CSRF token para requisições AJAX
 */
function setupCSRF() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (csrfToken) {
        // Adicionar token a todas as requisições AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
    }
}

/**
 * Inicializa validação de formulários
 */
function initializeForms() {
    // Formulário de login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    // Formulário de cadastro
    const cadastroForm = document.getElementById('cadastroForm');
    if (cadastroForm) {
        cadastroForm.addEventListener('submit', handleCadastro);
    }

    // Formulário de recuperação de senha
    const recuperarForm = document.getElementById('recuperarSenhaForm');
    if (recuperarForm) {
        recuperarForm.addEventListener('submit', handleRecuperarSenha);
    }
}

/**
 * Manipula envio do formulário de login
 */
function handleLogin(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    formData.append('action', 'login');
    
    showLoading(form.querySelector('button[type="submit"]'));
    
    fetch('includes/functions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => {
                window.location.href = 'dashboard/' + form.tipo_usuario.value + '/index.php';
            }, 1500);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('error', 'Erro de conexão. Tente novamente.');
        console.error('Error:', error);
    });
}

/**
 * Manipula envio do formulário de cadastro
 */
function handleCadastro(e) {
    e.preventDefault();
    
    const form = e.target;
    const senha = form.senha.value;
    const confirmarSenha = form.confirmar_senha.value;
    
    // Validação de senha
    if (senha !== confirmarSenha) {
        showAlert('error', 'As senhas não coincidem');
        return;
    }
    
    if (senha.length < 6) {
        showAlert('error', 'A senha deve ter pelo menos 6 caracteres');
        return;
    }
    
    const formData = new FormData(form);
    formData.append('action', 'cadastrar_usuario');
    
    showLoading(form.querySelector('button[type="submit"]'));
    
    fetch('includes/functions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('error', 'Erro de conexão. Tente novamente.');
        console.error('Error:', error);
    });
}

/**
 * Manipula recuperação de senha
 */
function handleRecuperarSenha(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    showLoading(form.querySelector('button[type="submit"]'));
    
    // Simular envio de email (em produção, integrar com serviço de email)
    setTimeout(() => {
        hideLoading();
        showAlert('success', 'Instruções de recuperação enviadas para seu e-mail!');
        form.reset();
    }, 2000);
}

/**
 * Exibe alerta para o usuário
 */
function showAlert(type, message) {
    // Remover alertas existentes
    const existingAlerts = document.querySelectorAll('.custom-alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const alertHtml = `
        <div class="alert ${alertClass} custom-alert alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Inserir no topo da página
    const container = document.querySelector('.container') || document.body;
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-remover após 5 segundos
    setTimeout(() => {
        const alert = document.querySelector('.custom-alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

/**
 * Mostra loading no botão
 */
function showLoading(button) {
    if (!button) return;
    
    button.disabled = true;
    button.innerHTML = '<span class="loading-spinner"></span> Processando...';
}

/**
 * Esconde loading do botão
 */
function hideLoading(button = null) {
    if (!button) {
        button = document.querySelector('button[type="submit"]');
    }
    
    if (button) {
        button.disabled = false;
        const originalText = button.getAttribute('data-original-text') || 'Enviar';
        button.innerHTML = originalText;
    }
}

/**
 * Formata data para exibição
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

/**
 * Formata data e hora para exibição
 */
function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleString('pt-BR');
}

/**
 * Formata valor monetário
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

/**
 * Valida email
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Debounce function para otimizar performance
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Modal de confirmação personalizado
 */
function showConfirmModal(title, message, confirmCallback, cancelCallback) {
    // Criar modal dinamicamente
    const modalHtml = `
        <div class="modal fade" id="confirmModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="confirmButton">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remover modal existente
    const existingModal = document.getElementById('confirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Adicionar novo modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
    
    // Configurar eventos
    document.getElementById('confirmButton').addEventListener('click', function() {
        modal.hide();
        if (confirmCallback) confirmCallback();
    });
    
    document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function() {
        if (cancelCallback) cancelCallback();
        this.remove();
    });
}

// Exportar funções para uso global
window.showAlert = showAlert;
window.showConfirmModal = showConfirmModal;
window.formatDate = formatDate;
window.formatDateTime = formatDateTime;
window.formatCurrency = formatCurrency;