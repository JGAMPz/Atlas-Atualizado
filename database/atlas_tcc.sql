-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17/10/2025 às 02:47
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `atlas_tcc`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agenda`
--

CREATE TABLE `agenda` (
  `id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `aluno_id` int(11) DEFAULT NULL,
  `data_hora` datetime NOT NULL,
  `duracao_minutos` int(11) DEFAULT 60,
  `status` enum('disponivel','agendado','cancelado') DEFAULT 'disponivel',
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agenda`
--

INSERT INTO `agenda` (`id`, `personal_id`, `aluno_id`, `data_hora`, `duracao_minutos`, `status`, `data_criacao`) VALUES
(1, 2, NULL, '2025-10-06 08:00:00', 60, 'disponivel', '2025-10-06 22:30:15'),
(2, 2, NULL, '2025-10-06 10:00:00', 60, 'disponivel', '2025-10-06 22:30:15'),
(3, 2, NULL, '2025-10-06 14:00:00', 60, 'disponivel', '2025-10-06 22:30:15'),
(4, 2, NULL, '2025-10-06 16:00:00', 60, 'disponivel', '2025-10-06 22:30:15'),
(5, 2, 1, '2025-10-06 17:00:00', 60, 'agendado', '2025-10-06 22:30:15'),
(6, 2, NULL, '2025-10-06 19:00:00', 60, 'disponivel', '2025-10-06 22:30:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `matriculas`
--

CREATE TABLE `matriculas` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `plano_id` int(11) NOT NULL,
  `personal_id` int(11) DEFAULT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `valor_contratado` decimal(10,2) NOT NULL,
  `status` enum('ativa','trancada','cancelada') DEFAULT 'ativa',
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `matriculas`
--

INSERT INTO `matriculas` (`id`, `aluno_id`, `plano_id`, `personal_id`, `data_inicio`, `data_fim`, `valor_contratado`, `status`, `data_criacao`) VALUES
(1, 1, 1, NULL, '2025-10-16', '2025-12-15', 0.00, 'ativa', '2025-10-16 21:42:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `plano_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `metodo_pagamento` enum('pix','cartao','boleto') NOT NULL,
  `status` enum('pendente','aprovado','recusado','cancelado') DEFAULT 'pendente',
  `id_mp` varchar(255) DEFAULT NULL,
  `qr_code` text DEFAULT NULL,
  `pix_copia_cola` text DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `link_boleto` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pagamentos`
--

INSERT INTO `pagamentos` (`id`, `usuario_id`, `plano_id`, `valor`, `metodo_pagamento`, `status`, `id_mp`, `qr_code`, `pix_copia_cola`, `data_vencimento`, `link_boleto`, `created_at`, `updated_at`) VALUES
(2, 1, 1, 129.00, 'pix', 'pendente', 'PIX-68e8309cdc4da', 'data:image/svg+xml;base64,DQogICAgICAgICAgICA8c3ZnIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPg0KICAgICAgICAgICAgICAgIDxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjZjBmMGYwIi8+DQogICAgICAgICAgICAgICAgPHRleHQgeD0iMTAwIiB5PSIxMDAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiMzMzMiPlFSIENPREUgUElYPC90ZXh0Pg0KICAgICAgICAgICAgICAgIDx0ZXh0IHg9IjEwMCIgeT0iMTIwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjNjY2IiBmb250LXNpemU9IjEyIj5Nb2RvIFNpbXVsYcOnw6NvPC90ZXh0Pg0KICAgICAgICAgICAgPC9zdmc+DQogICAgICAgIA==', '00020126580014br.gov.bcb.pix013668e8309cdc4dd5204000053039865406129005802BR5913Gabriel Porto6008BRASILIA62070503***6304D8E056C6', NULL, NULL, '2025-10-09 22:01:00', '2025-10-09 22:01:00'),
(3, 2, 1, 129.00, 'cartao', 'pendente', 'TEST-68e87a909bf9f', NULL, NULL, NULL, NULL, '2025-10-10 03:16:32', '2025-10-10 03:16:32'),
(4, 2, 1, 129.00, 'boleto', 'pendente', 'TEST-68e87a949f195', NULL, NULL, NULL, NULL, '2025-10-10 03:16:36', '2025-10-10 03:16:36'),
(5, 2, 1, 129.00, 'pix', 'pendente', 'PIX-68e87a96cb352', 'data:image/svg+xml;base64,DQogICAgICAgICAgICA8c3ZnIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPg0KICAgICAgICAgICAgICAgIDxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjZjBmMGYwIi8+DQogICAgICAgICAgICAgICAgPHRleHQgeD0iMTAwIiB5PSIxMDAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiMzMzMiPlFSIENPREUgUElYPC90ZXh0Pg0KICAgICAgICAgICAgICAgIDx0ZXh0IHg9IjEwMCIgeT0iMTIwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjNjY2IiBmb250LXNpemU9IjEyIj5Nb2RvIFNpbXVsYcOnw6NvPC90ZXh0Pg0KICAgICAgICAgICAgPC9zdmc+DQogICAgICAgIA==', '00020126580014br.gov.bcb.pix013668e87a96cb3695204000053039865406129005802BR5913Fabiana Porto6008BRASILIA62070503***63041ED2AC7D', NULL, NULL, '2025-10-10 03:16:38', '2025-10-10 03:16:38'),
(6, 2, 1, 129.00, 'pix', 'pendente', 'PIX-68e87ac23575e', 'data:image/svg+xml;base64,DQogICAgICAgICAgICA8c3ZnIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPg0KICAgICAgICAgICAgICAgIDxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjZjBmMGYwIi8+DQogICAgICAgICAgICAgICAgPHRleHQgeD0iMTAwIiB5PSIxMDAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiMzMzMiPlFSIENPREUgUElYPC90ZXh0Pg0KICAgICAgICAgICAgICAgIDx0ZXh0IHg9IjEwMCIgeT0iMTIwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjNjY2IiBmb250LXNpemU9IjEyIj5Nb2RvIFNpbXVsYcOnw6NvPC90ZXh0Pg0KICAgICAgICAgICAgPC9zdmc+DQogICAgICAgIA==', '00020126580014br.gov.bcb.pix013668e87ac2357645204000053039865406129005802BR5913Fabiana Porto6008BRASILIA62070503***63049E29CCC9', NULL, NULL, '2025-10-10 03:17:22', '2025-10-10 03:17:22'),
(7, 6, 1, 129.00, 'pix', 'pendente', 'PIX-68ef06d115cf5', 'data:image/svg+xml;base64,DQogICAgICAgICAgICA8c3ZnIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPg0KICAgICAgICAgICAgICAgIDxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiBmaWxsPSIjZjBmMGYwIi8+DQogICAgICAgICAgICAgICAgPHRleHQgeD0iMTAwIiB5PSIxMDAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiMzMzMiPlFSIENPREUgUElYPC90ZXh0Pg0KICAgICAgICAgICAgICAgIDx0ZXh0IHg9IjEwMCIgeT0iMTIwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjNjY2IiBmb250LXNpemU9IjEyIj5Nb2RvIFNpbXVsYcOnw6NvPC90ZXh0Pg0KICAgICAgICAgICAgPC9zdmc+DQogICAgICAgIA==', '00020126580014br.gov.bcb.pix013668ef06d115d045204000053039865406129005802BR5913Black6008BRASILIA62070503***6304AACD2B6B', NULL, NULL, '2025-10-15 02:28:33', '2025-10-15 02:28:33'),
(8, 1, 1, 129.00, 'pix', 'aprovado', NULL, NULL, NULL, NULL, NULL, '2025-10-17 00:42:42', '2025-10-17 00:42:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos`
--

CREATE TABLE `planos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `duracao_dias` int(11) NOT NULL,
  `inclui_personal` tinyint(1) DEFAULT 0,
  `status` enum('ativo','inativo') DEFAULT 'ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `planos`
--

INSERT INTO `planos` (`id`, `nome`, `descricao`, `preco`, `duracao_dias`, `inclui_personal`, `status`) VALUES
(1, 'Black', 'Treine em qualquer academia da Smart Fit, seja no Brasil ou na América Latina. São +1.700 unidades em 15 países!', 129.00, 60, 1, 'ativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('aluno','personal','admin') NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `ultimo_login` datetime DEFAULT NULL,
  `is_super_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `telefone`, `data_nascimento`, `endereco`, `data_cadastro`, `status`, `ultimo_login`, `is_super_admin`) VALUES
(1, 'Gabriel Porto', 'madaraeobito541@gmail.com', '$2y$12$.I0iSzaSoz893jOlZ/HuMePaJmra1L0gfSecXp.adqqsGyMMDGFpe', 'aluno', '(21) 98051-6442', '2008-04-30', 'Estrada Joao Paulo 991', '2025-10-06 21:21:33', 'ativo', '2025-10-06 21:34:31', 0),
(2, 'Fabiana Porto', 'fabiporto@gmail.com', '$2y$12$VppJrz/DTrjtBsjYRTHP2eem30Kfy/xmvfhhSE4JxglnW8an7u3DW', 'personal', '(21) 98590-0078', '1980-03-22', 'Estrada João Paulo 991', '2025-10-06 21:58:06', 'ativo', NULL, 0),
(3, 'Fabiana Porto', 'fabiporto2203@gmail.com', '$2y$12$JaDpNH4gr6SAhIbMN1FiQOtWvpDR0slCZ2iGb4AabBwndL0jkvnh.', 'aluno', '(21) 98590-0078', '1980-03-22', 'Estrada João Paulo 991', '2025-10-06 22:39:38', 'ativo', NULL, 0),
(4, 'Administrador Principal', 'admin@atlas.com', '$2y$10$/yW6jlcxH3By4H1H5VzWB.OQz8WFNfN1W0CIGaVq9iL7E/AbWzq2a', 'admin', '(11) 99999-9999', '1990-01-01', 'Endereço administrativo', '2025-10-09 17:27:46', 'ativo', NULL, 1),
(5, 'macaco', 'macacaca@gmail.com', '$2y$12$lp9r679cCXlCPWxcmpa1..vkOaY9Dj8T61PWjAYRB9hwZ2pKqfLAm', 'aluno', '(21) 98051-6442', '2006-06-06', 'macaca', '2025-10-09 19:04:19', 'ativo', NULL, 0),
(6, 'Black', 'exemplo@gmail.com', '$2y$12$7d3CCTZEQQwL92ZNNHADsOrbKg0loahAJHfjE924Y5M80zNg4.Q42', 'aluno', '(21) 98590-0078', '2005-06-12', '123ga', '2025-10-14 23:28:13', 'ativo', NULL, 0);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agenda`
--
ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_id` (`personal_id`),
  ADD KEY `aluno_id` (`aluno_id`);

--
-- Índices de tabela `matriculas`
--
ALTER TABLE `matriculas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aluno_id` (`aluno_id`),
  ADD KEY `plano_id` (`plano_id`),
  ADD KEY `personal_id` (`personal_id`);

--
-- Índices de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `plano_id` (`plano_id`);

--
-- Índices de tabela `planos`
--
ALTER TABLE `planos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agenda`
--
ALTER TABLE `agenda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `matriculas`
--
ALTER TABLE `matriculas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `planos`
--
ALTER TABLE `planos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agenda`
--
ALTER TABLE `agenda`
  ADD CONSTRAINT `agenda_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `agenda_ibfk_2` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `matriculas`
--
ALTER TABLE `matriculas`
  ADD CONSTRAINT `matriculas_ibfk_1` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `matriculas_ibfk_2` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`),
  ADD CONSTRAINT `matriculas_ibfk_3` FOREIGN KEY (`personal_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD CONSTRAINT `pagamentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pagamentos_ibfk_2` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
