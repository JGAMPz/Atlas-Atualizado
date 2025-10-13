        </div> <!-- Fecha container -->
        </main> <!-- Fecha main -->

        <!-- Footer SEMPRE no rodapé -->
        <footer class="bg-dark text-light py-4 mt-auto">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-dumbbell"></i> ATLAS</h5>
                        <p>Seu portal completo para fitness e bem-estar</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p>&copy; <?php echo date('Y'); ?> ATLAS. Todos os direitos reservados.</p>
                        <p>Desenvolvido com ❤️ para sua saúde</p>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
        <script src="../assets/js/main.js"></script>

        <?php if (isset($page_js)): ?>
        <script src="../assets/js/<?php echo $page_js; ?>"></script>
        <?php endif; ?>
        </body>

        </html>