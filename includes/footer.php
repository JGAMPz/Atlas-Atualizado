    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-3 mt-auto">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> ATLAS - Sistema de Academia. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para Notificações -->
    <script>
function marcarNotificacaoLida(notificacao_id, element) {
    // Marcar visualmente como lida
    element.classList.remove('bg-light');

    // Enviar para o servidor
    const formData = new FormData();
    formData.append('action', 'marcar_notificacao_lida');
    formData.append('notificacao_id', notificacao_id);

    fetch('../../includes/functions.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                // Atualizar contador
                const badge = document.querySelector('.nav-link .badge');
                if (badge) {
                    const currentCount = parseInt(badge.textContent);
                    if (currentCount > 1) {
                        badge.textContent = currentCount - 1;
                    } else {
                        badge.remove();
                    }
                }
            }
        });
}
    </script>
    </body>

    </html>