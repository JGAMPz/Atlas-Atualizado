<?php
// includes/mercadopago_config.php - VERSÃO SIMPLIFICADA

class MercadoPagoIntegration {
    private $public_key;
    private $access_token;
    
    public function __construct() {
        // Credenciais de TESTE - Funcionam sem SDK
        $this->public_key = 'TEST-a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $this->access_token = 'TEST-12345678901234567890123456789012-123456';
    }
    
    /**
     * Criar pagamento - VERSÃO SIMULADA
     */
    public function criarPagamento($dados) {
        // Simula criação de preferência no Mercado Pago
        $preference_id = 'TEST-' . uniqid();
        
        return [
            'success' => true,
            'init_point' => 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=' . $preference_id,
            'preference_id' => $preference_id,
            'message' => '✅ Pagamento simulado criado com sucesso! (Modo teste)'
        ];
    }
    
    /**
     * Criar pagamento PIX - VERSÃO SIMULADA
     */
    public function criarPagamentoPix($dados) {
        // Simula pagamento PIX
        $payment_id = 'PIX-' . uniqid();
        
        // QR Code fake (imagem em base64 de um QR code simples)
        $qr_code_fake = 'data:image/svg+xml;base64,' . base64_encode('
            <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
                <rect width="200" height="200" fill="#f0f0f0"/>
                <text x="100" y="100" text-anchor="middle" fill="#333">QR CODE PIX</text>
                <text x="100" y="120" text-anchor="middle" fill="#666" font-size="12">Modo Simulação</text>
            </svg>
        ');
        
        // PIX copia e cola fake
        $pix_copia_cola = '00020126580014br.gov.bcb.pix0136' . uniqid() . 
                         '5204000053039865406' . number_format($dados['valor'] * 100, 0, '', '') . 
                         '5802BR5913' . substr($dados['nome'], 0, 25) . '6008BRASILIA62070503***6304' . 
                         strtoupper(dechex(crc32($payment_id)));
        
        return [
            'success' => true,
            'payment_id' => $payment_id,
            'status' => 'pending',
            'qr_code' => $qr_code_fake,
            'qr_code_text' => $pix_copia_cola,
            'message' => '✅ PIX simulado criado com sucesso! (Modo teste)'
        ];
    }
    
    /**
     * Verificar status do pagamento - VERSÃO SIMULADA
     */
    public function verificarPagamento($payment_id) {
        // Simula verificação de status
        $statuses = ['pending', 'approved', 'rejected'];
        $random_status = $statuses[array_rand($statuses)];
        
        return [
            'success' => true,
            'status' => $random_status,
            'message' => 'Status simulado: ' . $random_status
        ];
    }
}
?>