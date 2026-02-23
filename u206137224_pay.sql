-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 24/02/2026 às 00:19
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
-- Banco de dados: `u206137224_pay`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `admlogin`
--

CREATE TABLE `admlogin` (
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `app`
--

CREATE TABLE `app` (
  `token` varchar(255) NOT NULL,
  `numero_usuarios` int(11) DEFAULT NULL,
  `faturamento_total` decimal(10,2) DEFAULT NULL,
  `total_transacoes` int(11) DEFAULT NULL,
  `visitantes` int(11) DEFAULT NULL,
  `manutencao` tinyint(1) DEFAULT NULL,
  `sms_url_cadastro_pendente` varchar(255) DEFAULT NULL,
  `sms_url_cadastro_ativo` varchar(255) DEFAULT NULL,
  `sms_url_notificacao_user` varchar(255) DEFAULT NULL,
  `sms_url_redefinir_senha` varchar(255) DEFAULT NULL,
  `sms_url_autenticar_admin` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `app`
--

INSERT INTO `app` (`token`, `numero_usuarios`, `faturamento_total`, `total_transacoes`, `visitantes`, `manutencao`, `sms_url_cadastro_pendente`, `sms_url_cadastro_ativo`, `sms_url_notificacao_user`, `sms_url_redefinir_senha`, `sms_url_autenticar_admin`) VALUES
('', NULL, NULL, NULL, NULL, NULL, 'https://v1.smsfunnel.com.br/integrations/lists/450db410-9890-470d-af69-ea94f00273a4/add-lead', 'ok', 'ok', 'ok', 'ok');

-- --------------------------------------------------------

--
-- Estrutura para tabela `checkout_avaliacoes`
--

CREATE TABLE `checkout_avaliacoes` (
  `id` int(11) NOT NULL,
  `checkout_id` int(11) NOT NULL,
  `nome_avaliador` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `comentario` text NOT NULL,
  `estrelas` int(1) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `checkout_avaliacoes`
--

INSERT INTO `checkout_avaliacoes` (`id`, `checkout_id`, `nome_avaliador`, `avatar`, `comentario`, `estrelas`, `data_criacao`) VALUES
(10, 14, 'Lucas Medeiros', '../uploads/avatares/68515ab2687ff_Screenshot_20250617-090307~2.png', 'Acabei de assinar aqui , é top d+ ,ainda comprei os vídeos da Mayara junto 🥵🫣🤤', 5, '2025-06-17 12:08:20'),
(11, 14, 'João Silva ', '../uploads/avatares/68515af07c94d_Screenshot_20250617-090442~2.png', 'Muito bom ,melhores vídeos 🫣', 5, '2025-06-17 12:09:21'),
(12, 14, 'Mateus ', '../uploads/avatares/68515b2e3d1cc_Screenshot_20250617-090338~2.png', 'Rapaz ,já tinha assinado vários grupos  e nunca vi um igual esse ,todos os vídeos são de qualidade e bem raros de se achar ', 5, '2025-06-17 12:10:24'),
(13, 14, 'Cauã santos ', '../uploads/avatares/68515c9285503_Screenshot_20250617-090357~2.png', 'Qui grupo top 🔝 gostei mia linda', 5, '2025-06-17 12:16:20'),
(14, 14, 'Everton ', '../uploads/avatares/68515cc09a8f0_Screenshot_20250617-090419~2.png', 'Aprovado 🤤🤤🤤🤤🤤,nem sabia que a Kamilinha tinha fotos e vídeos ,amei ', 5, '2025-06-17 12:17:05'),
(15, 14, 'Victor Hugo ', '../uploads/avatares/68515cfddb1b6_Screenshot_20250617-090425~2.png', 'Rapaz ,esse vídeo da Kamilinha dando pro nino é o melhor ,vídeo top da porra ,até o nino veyyy,comeu a Kamilinha ', 5, '2025-06-17 12:18:06'),
(23, 18, 'joao silva', '../uploads/avatares/68688f90590fe_carlos.jpg', 'Confesso que fiquei com medo, mas assim que paguei a taxa o dinheiro entrou certinho. Empresa séria de verdade!', 5, '2025-07-05 02:36:00'),
(24, 18, 'alex almeida', '../uploads/avatares/68688f906003f_opaaja.jpg', 'Fiz o pagamento da taxa e deu tudo certo, recebi o valor rapidinho. Muito confiável, vou pagar a conta de água e luuzzz, obrigadoo.', 5, '2025-07-05 02:36:00'),
(25, 18, 'marcos junior', '../uploads/avatares/68688f907c133_favelaod.jfif', 'P****, esse site me ajudou a apagar tudo parabes de verdade, ja indiquei pra minha familia toda, obrigaodooo.', 5, '2025-07-05 02:36:00'),
(26, 18, 'mariana B.', '../uploads/avatares/68688f907c2ae_2e4c8ea71b690f8b23c401a98c1308ae.jpg', 'nÃO ACREDITEI NISSO, SLCC, MAIS O NEGOCIO FUNCIONOU PODEM FICAR tranquilos com isso, nota 1000000', 5, '2025-07-05 02:36:00'),
(27, 19, 'joao silva', '../uploads/avatares/6877cc5f940e1_1].jpeg', 'Achei que era golpe, mas depois que paguei a taxa o dinheiro entrou certinho.', 5, '2025-07-16 15:59:27'),
(28, 19, 'marcos antonio', '../uploads/avatares/6877cc5f94a2b_2.jpeg', 'Funcionou direitinho! Paguei a taxa e recebi na hora no Pix.', 5, '2025-07-16 15:59:27'),
(29, 19, 'emanuel gomes', '../uploads/avatares/6877cc5f94d72_3.jpeg', 'Desconfiado no começo, mas fiz o pagamento da taxa e o valor caiu rapidinho.', 5, '2025-07-16 15:59:27'),
(30, 19, 'gabriel victor', '../uploads/avatares/6877cc5fac6f7_4.jpeg', 'Muito rápido fiz o pagamento da taxa e logo em seguida recebi.', 5, '2025-07-16 15:59:27'),
(31, 19, 'larissa muniz', '../uploads/avatares/6877cc5fbacce_7.jpeg', 'Pode confiar, meninas! Paguei a taxa e recebi o valor certinho.', 5, '2025-07-16 15:59:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `checkout_build`
--

CREATE TABLE `checkout_build` (
  `id` int(11) NOT NULL,
  `name_produto` varchar(255) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `referencia` varchar(255) NOT NULL,
  `logo_produto` varchar(255) DEFAULT NULL,
  `obrigado_page` text DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `email` varchar(255) NOT NULL,
  `url_checkout` varchar(255) DEFAULT NULL,
  `banner_produto` varchar(255) NOT NULL,
  `arquivo_digital` varchar(255) DEFAULT NULL COMMENT 'Caminho para o arquivo digital enviado',
  `link_externo` varchar(500) DEFAULT NULL COMMENT 'Link externo para download (Google Drive, Mega, etc.)',
  `tipo_entrega` enum('arquivo','link','nenhum') NOT NULL DEFAULT 'nenhum' COMMENT 'Tipo de entrega digital',
  `exibir_cpf` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Define se o campo de CPF será exibido no checkout (1=sim, 0=não)',
  `user_provider_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `checkout_build`
--

INSERT INTO `checkout_build` (`id`, `name_produto`, `valor`, `referencia`, `logo_produto`, `obrigado_page`, `ativo`, `email`, `url_checkout`, `banner_produto`, `arquivo_digital`, `link_externo`, `tipo_entrega`, `exibir_cpf`, `user_provider_id`) VALUES
(1, 'visagra', '12,30', '', '../uploads/comnbobanner.jpg', 'https://web.uranopay.com', 1, 'cauacavalcante38@gmail.com', 'https://web.uranopay.com/checkout/v1/?id=l0MDnPe5nkCiiPsAOOZAIMXB', '../uploads/COMBOINEJECT.jpg', NULL, NULL, 'nenhum', 1, NULL),
(5, 'Igamimg', '19.9', '', '../uploads/ChatGPT Image 22 de abr. de 2025, 13_06_57 (1).png', 'jogar.win', 1, 'contatosx@icloud.com', 'https://web.uranopay.com/checkout/v1/?id=pxb7AV1HjoRpE9SGxrGCfNJC', '../uploads/ChatGPT Image 22 de abr. de 2025, 13_06_57 (1).png', NULL, NULL, 'nenhum', 1, NULL),
(7, 'Tênis', '10', '', '../uploads/user_rank.png', 'https://uranopay.com', 1, 'stivevendas@catgroup.uk', 'https://web.uranopay.com/checkout/v1/?id=c0pA51EJ4F10n1SztE1TT2tX', '../uploads/user_rank.png', NULL, NULL, 'nenhum', 1, NULL),
(8, 'teste', '100', '', '../uploads/1745506450646..webp', 'teste', 1, 'nidsijs@dqdqwk.com', 'https://web.uranopay.com/checkout/v1/?id=ssnnF6jt6o84mKaRmshzkmOe', '../uploads/1745508074456..webp', NULL, NULL, 'nenhum', 1, NULL),
(14, 'GRUPO DE VAZADOS', '20', '', '../uploads/IMG-20250501-WA0133.jpg', NULL, 1, 'diegoteste@gmail.com', 'https://web.uranopay.com/checkout/v1/?id=sBV1tu5o0ZXXouTg8cuznqu3', '../uploads/IMG-20250501-WA0164.jpg', '', 'https://t.me/+ZHUhmGVL4u8yNGUx', 'link', 0, NULL),
(18, 'Taxa anti-fraude Spotify', '20', '', '../uploads/foto.jpeg', NULL, 1, 'caua77652@gmail.com', 'https://web.uranopay.com/checkout/v1/?id=cwF8Q6kdzU49E9DywDGEcdZB', '../uploads/fotu1.jfif', '', '', 'nenhum', 0, NULL),
(19, 'Taxa anti-fraude Shein', '1', '', '../uploads/sheinn9.png', NULL, 1, 'caua77652@gmail.com', 'https://web.uranopay.com/checkout/v1/?id=DKiU1UDfvWWGi4ipF0s6oSJF', '../uploads/sheinnpay.webp', '', '', 'nenhum', 0, NULL),
(20, 'teste', '100', '', '../uploads/WhatsApp_Image_2026-02-15_at_14.57.13-removebg-preview.png', 'https://www.youtube.com/watch?v=ocvWEi3AFU0&list=RDocvWEi3AFU0&start_radio=1', 1, 'mathgoldyoficial@gmail.com', 'https://localhost/checkout/v1/?id=hnfdr8CvD4KzOa7S3iyb1BYt', '../uploads/WhatsApp Image 2026-02-15 at 14.57.13.jpeg', NULL, NULL, 'nenhum', 1, NULL),
(21, 'teste 23', '100', '', '../uploads/WhatsApp Image 2026-02-15 at 14.57.13.jpeg', 'https://hpanel.hostinger.com/domain/zyrocheckout.com.br/domain-overview', 1, 'mathgoldyoficial@gmail.com', 'http://localhost/uranoPAY/web/checkout/v1/?id=ZX4XOklHPZkiTe91RPehC1p1', '../uploads/WhatsApp Image 2026-02-15 at 14.57.13.jpeg', NULL, NULL, 'nenhum', 1, NULL),
(22, 'teste 2312', '100', '', '../uploads/WhatsApp_Image_2026-02-15_at_14.57.13-removebg-preview.png', 'https://hpanel.hostinger.com/domain/zyrocheckout.com.br/domain-overview', 1, 'mathgoldyoficial@gmail.com', 'http://localhost/uranoPAY/web/checkout/v1/?id=n12jHghhQ0CFo1AjU4YTm9UJ', '../uploads/WhatsApp Image 2026-02-15 at 14.57.13.jpeg', NULL, NULL, 'nenhum', 1, NULL),
(23, 'teste 231211', '50', '', '../uploads/Logo Ametista [AMYX] NÃO VETORIZADA.png', 'https://hpanel.hostinger.com/domain/zyrocheckout.com.br/domain-overview', 1, 'mathgoldyoficial@gmail.com', 'http://localhost/uranoPAY/web/checkout/v1/?id=a3MVvEbJWe87ebrzbiU9rqlL', '../uploads/Logo Ametista [AMYX] NÃO VETORIZADA.png', NULL, NULL, 'nenhum', 1, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `checkout_upsells`
--

CREATE TABLE `checkout_upsells` (
  `id` int(11) NOT NULL,
  `checkout_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `checkout_upsells`
--

INSERT INTO `checkout_upsells` (`id`, `checkout_id`, `nome`, `descricao`, `valor`, `imagem`, `ordem`, `ativo`, `data_criacao`) VALUES
(7, 14, 'FOTO MINHA (MAYARA) ', 'Se quiser me ver pelada ,compre esse conteúdo extra por 7 reais e veja minhas fotos e vídeos pelada e dando pro meu primo .', 7.00, '../uploads/upsells/685158d3e7b8d_IMG_20250617_085943_595.jpg', 0, 1, '2025-06-17 12:00:20'),
(12, 18, 'Saque em dobro', 'Seu pix em dobro, com uso maximo de 1 vez.', 4.99, '../uploads/upsells/68688f908b011_foto.jpeg', 0, 1, '2025-07-05 02:36:00'),
(13, 19, 'Saque em dobro', 'O dinheiro entra dobrado.', 7.00, '../uploads/upsells/6877cc5fe1af0_sheinnpay.webp', 0, 1, '2025-07-16 15:59:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `confirmar_deposito`
--

CREATE TABLE `confirmar_deposito` (
  `email` varchar(255) NOT NULL,
  `externalreference` varchar(255) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `data` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `digital_content_access`
--

CREATE TABLE `digital_content_access` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL COMMENT 'ID da transação/referência externa',
  `checkout_id` int(11) NOT NULL COMMENT 'ID do produto na tabela checkout_build',
  `client_email` varchar(255) NOT NULL COMMENT 'Email do cliente',
  `access_type` enum('link','download') NOT NULL COMMENT 'Tipo de acesso (link ou download)',
  `access_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Número de acessos realizados',
  `max_access` int(11) NOT NULL DEFAULT 3 COMMENT 'Número máximo de acessos permitidos',
  `last_access` datetime DEFAULT NULL COMMENT 'Data e hora do último acesso',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Data de criação do registro',
  `access_token` varchar(64) NOT NULL COMMENT 'Token único para acesso seguro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `digital_content_access`
--

INSERT INTO `digital_content_access` (`id`, `transaction_id`, `checkout_id`, `client_email`, `access_type`, `access_count`, `max_access`, `last_access`, `created_at`, `access_token`) VALUES
(1, 'TEMP_XpMLInGLpa53LP4h8R1lp2UD_1746123919', 10, 'cliente@exemplo.com', 'link', 4, 3, '2025-05-01 18:34:16', '2025-05-01 18:25:19', '639cc00ad68033b6a24cd68712c86ddba8c1205c73bd73e57dd63e545da2254c'),
(2, 'TEMP_XpMLInGLpa53LP4h8R1lp2UD_1746124856', 10, 'cliente@exemplo.com', 'link', 3, 3, '2025-05-01 18:41:07', '2025-05-01 18:40:56', '83959bdf65d8d8c9b27522f64d12cdb90c82b13094803cf9d7cea9352e2fb045'),
(3, 'TEMP_XpMLInGLpa53LP4h8R1lp2UD_1746128009', 10, 'cliente@exemplo.com', 'link', 0, 3, NULL, '2025-05-01 19:33:29', '02caa247e3b3a87dff110647a6417dd44766b9ddc8f37cd16d4432514b424e41'),
(4, 'TEMP_XpMLInGLpa53LP4h8R1lp2UD_1746128566', 10, 'cliente@exemplo.com', 'link', 1, 3, '2025-05-10 19:08:25', '2025-05-01 19:42:46', '59bc1b2c41f84aa5af073e994b43705f142a8014060f9ed34a756da9b73ec76b'),
(5, 'TEMP_XpMLInGLpa53LP4h8R1lp2UD_1746130245', 10, 'cliente@exemplo.com', 'link', 1, 3, '2025-05-01 20:11:18', '2025-05-01 20:10:45', '64441996972619d522910a6133fa080d260745784ddb37bb7c74abdde52b0f9a'),
(6, 'TEST_1746186451', 10, 'teste@exemplo.com', 'link', 0, 3, NULL, '2025-05-02 18:56:43', 'cb719abe802d4d8fb1327b29b4dfa77ecbe9231487026495b3f4e19bd319dfe7'),
(7, 'DEBUG_1746905353', 15, 'teseete@uranopay.com', 'link', 3, 3, '2025-06-17 12:24:05', '2025-06-17 12:23:41', 'f15279516a2f8a5fb260a3e3679345f6f5c6667742d1e3c8906f2c1a0b0ed11f'),
(8, 'DEBUG_1746905353', 14, 'teseete@uranopay.com', 'link', 3, 3, '2025-06-17 12:34:24', '2025-06-17 12:29:27', '7c22f0d06b060f428608c7150f73b290b8d55395b9dc6c9713aebb756096ab31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `domains`
--

CREATE TABLE `domains` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `type` enum('custom','subdomain','system_subdomain') NOT NULL DEFAULT 'custom',
  `status` enum('pending','active','suspended') NOT NULL DEFAULT 'pending',
  `ssl_status` enum('none','pending','active','failed') NOT NULL DEFAULT 'none',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `prefix` varchar(32) DEFAULT NULL,
  `slug` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_transacoes`
--

CREATE TABLE `historico_transacoes` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL COMMENT 'ID da transação/referência externa',
  `checkout_id` int(11) NOT NULL COMMENT 'ID do produto na tabela checkout_build',
  `vendedor_id` varchar(255) NOT NULL COMMENT 'user_id do vendedor',
  `comprador_id` varchar(255) DEFAULT NULL COMMENT 'user_id do comprador',
  `valor_total` decimal(10,2) NOT NULL COMMENT 'Valor total da transação',
  `valor_liquido` decimal(10,2) NOT NULL COMMENT 'Valor líquido após taxas',
  `data_transacao` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Data e hora da transação',
  `tipo_transacao` enum('venda','saque','estorno') NOT NULL DEFAULT 'venda' COMMENT 'Tipo de transação',
  `descricao` varchar(255) DEFAULT NULL COMMENT 'Descrição adicional'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `historico_transacoes`
--

INSERT INTO `historico_transacoes` (`id`, `transaction_id`, `checkout_id`, `vendedor_id`, `comprador_id`, `valor_total`, `valor_liquido`, `data_transacao`, `tipo_transacao`, `descricao`) VALUES
(1, 'TEST_1746222182', 0, 'caua77652@gmail.com', NULL, 105.00, 100.00, '2025-05-02 21:43:02', 'venda', 'Teste manual'),
(2, 'DEBUG_1746903475', 10, 'caua77652@gmail.com', NULL, 10.00, 9.50, '2025-05-10 18:57:55', 'venda', 'Venda de teste do produto #10 (Profelar)'),
(3, 'DEBUG_1746905339', 1, 'cauacavalcante38@gmail.com', NULL, 10.00, 9.50, '2025-05-10 19:28:59', 'venda', 'Venda de teste do produto #1 (visagra)'),
(4, 'DEBUG_1746905353', 1, 'cauacavalcante38@gmail.com', NULL, 10.00, 9.50, '2025-05-10 19:29:13', 'venda', 'Venda de teste do produto #1 (visagra)');

-- --------------------------------------------------------

--
-- Estrutura para tabela `payment_links`
--

CREATE TABLE `payment_links` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `editable_amount` tinyint(1) DEFAULT 0,
  `provider_id` int(11) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `max_payments` int(11) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `thank_you_url` varchar(500) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `payment_links`
--

INSERT INTO `payment_links` (`id`, `user_id`, `name`, `description`, `amount`, `editable_amount`, `provider_id`, `slug`, `max_payments`, `expires_at`, `thank_you_url`, `status`, `created_at`) VALUES
(1, 0, 'teste', '', 100.00, 0, 2, 'teste', 1, NULL, '', 1, '2026-02-23 05:58:02');

-- --------------------------------------------------------

--
-- Estrutura para tabela `plans`
--

CREATE TABLE `plans` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `plans`
--

INSERT INTO `plans` (`id`, `name`, `price`, `created_at`) VALUES
(1, 'STARTER', 0.00, '2026-02-23 06:16:10'),
(2, 'PRO', 49.90, '2026-02-23 06:16:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `seguranca`
--

CREATE TABLE `seguranca` (
  `keyseguranca` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `seguranca`
--

INSERT INTO `seguranca` (`keyseguranca`) VALUES
('24142414dertinhoiakdsj4847jdks92m');

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacoes`
--

CREATE TABLE `solicitacoes` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) DEFAULT NULL,
  `externalreference` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_document` varchar(255) NOT NULL,
  `client_email` varchar(255) NOT NULL,
  `real_data` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT '0',
  `qrcode_pix` varchar(500) NOT NULL DEFAULT '0',
  `paymentcode` varchar(500) NOT NULL DEFAULT '0',
  `idtransaction` varchar(255) NOT NULL DEFAULT '0',
  `paymentCodeBase64` text DEFAULT NULL,
  `provider_ref` varchar(255) DEFAULT NULL,
  `client_telefone` varchar(15) DEFAULT NULL,
  `executor_ordem` varchar(255) DEFAULT NULL,
  `descricao_transacao` varchar(255) DEFAULT NULL,
  `postback` varchar(500) DEFAULT NULL COMMENT 'URL de webhook do usuário para notificações'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `solicitacoes`
--

INSERT INTO `solicitacoes` (`id`, `user_id`, `externalreference`, `amount`, `client_name`, `client_document`, `client_email`, `real_data`, `status`, `qrcode_pix`, `paymentcode`, `idtransaction`, `paymentCodeBase64`, `provider_ref`, `client_telefone`, `executor_ordem`, `descricao_transacao`, `postback`) VALUES
(161, 'severino64', 'IlMR1Kmovkd49Q3CZMDaW87tQaIDCYkJ', 30.00, 'Maria Oliveira', '98765432100', 'maria.oliveira@email.com', '2024-10-04', 'paid', '0', '00020126850014br.gov.bcb.pix2563pix.voluti.com.br/qr/v3/at/767f2ab7-3d2c-47a0-9126-51a03e316a935204000053039865802BR5925PAGPIX SOLUCAO EM PAGAMEN6014SAO BERNARDO D62070503***63040A9F', 'a27c989e-f57d-49fe-852d-a7608988f745', 'MDAwMjAxMjY4NTAwMTRici5nb3YuYmNiLnBpeDI1NjNwaXgudm9sdXRpLmNvbS5ici9xci92My9hdC83NjdmMmFiNy0zZDJjLTQ3YTAtOTEyNi01MWEwM2UzMTZhOTM1MjA0MDAwMDUzMDM5ODY1ODAyQlI1OTI1UEFHUElYIFNPTFVDQU8gRU0gUEFHQU1FTjYwMTRTQU8gQkVSTkFSRE8gRDYyMDcwNTAzKioqNjMwNDBBOUY=', 'pagpix', '81994298684', NULL, NULL, NULL),
(162, 'sistema', 'TEST_1746186440', 10.00, 'Cliente Teste', '11999999999', 'teste@exemplo.com', '2025-05-02', 'paid', '0', 'TESTE_CONTENT_1746186440', '0', NULL, 'primepag', '11999999999', NULL, NULL, NULL),
(163, 'sistema', 'TEST_1746186451', 10.00, 'Cliente Teste', '11999999999', 'teste@exemplo.com', '2025-05-02', 'paid', '0', 'TESTE_CONTENT_1746186451', '0', NULL, 'primepag', '11999999999', NULL, NULL, NULL),
(164, 'caua77652@gmail.com', 'TEST_1746902917', 10.00, 'Cliente Teste', '11999999999', 'dasasd@w0dqw.com', '2025-05-10', 'paid', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(165, 'cauacavalcante38@gmail.com', 'TEST_1746902967', 100.00, 'Cliente Teste', '11999999999', 'erwe@w0kdqwo0.com', '2025-05-10', 'paid', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(166, 'caua77652@gmail.com', 'TEST_1746903173', 1.00, 'Cliente Teste', '11999999999', 'dasds@dlpqow.com', '2025-05-10', 'paid', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(167, 'cauacavalcante38@gmail.com', 'TEST_1746903185', 1.00, 'Cliente Teste', '11999999999', 'dsds@dlw.com', '2025-05-10', 'paid', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(168, 'caua77652@gmail.com', 'DEBUG_1746903475', 10.00, 'Cliente Teste', '11999999999', 'teste@uranopay.com', '2025-05-10', 'paid', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(169, 'cauacavalcante38@gmail.com', 'DEBUG_1746905339', 10.00, 'Cliente Teste', '11999999999', 'teste@uranopay.com', '2025-05-10', 'paid', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(170, 'cauacavalcante38@gmail.com', 'DEBUG_1746905353', 10.00, 'Cliente Teste', '11999999999', 'teseete@uranopay.com', '2025-05-10', 'paid', '0', '0', '0', NULL, NULL, NULL, NULL, NULL, NULL),
(171, NULL, 'pay_699b8a0a0b6df_23', 5.00, 'Teste Usuario', '12345678901', 'mathgoldyoficial@gmail.com', '2026-02-22', 'pending', '0', '00020101021226810014br.gov.bcb.pix2559qr.woovi.com/qr/v2/cob/354a8b84-1145-44fe-a307-272a6910ec2b52040000530398654045.005802BR5909PUSHINPAY6011HORTOLANDIA62290525265985e8aaa3451fbea8beb4e6304B6EC', 'a124f583-cc4c-4b02-92cb-8d51fe987a79', 'data:image/png;base64,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', 'Pushin Pay', NULL, NULL, NULL, NULL),
(172, NULL, 'pay_699b96edb62cc_23', 50.00, 'matheus vitor', '84921593966', 'mathgoldyoficial@gmail.com', '2026-02-23', 'pending', '0', '00020101021226810014br.gov.bcb.pix2559qr.woovi.com/qr/v2/cob/218ff4aa-fc53-48e6-a5b8-1b0cbe223077520400005303986540550.005802BR5909PUSHINPAY6011HORTOLANDIA62290525f1c880bf3e934730a953222d563046716', 'a125092e-aa54-4ebd-8de9-339ab8fd5a37', 'data:image/png;base64,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', 'Pushin Pay', NULL, NULL, NULL, NULL),
(173, NULL, 'link_699bec84dbe50_1', 100.00, 'matheus vitor', '01786039451', 'waveagc@gmail.com', '2026-02-23', 'pending', '0', '00020101021226810014br.gov.bcb.pix2559qr.woovi.com/qr/v2/cob/70407851-7569-4868-9bf3-fba154a217395204000053039865406100.005802BR5909PUSHINPAY6011HORTOLANDIA6229052518fdbecea7a944f8801978ccb63044547', 'a1258bc6-a51f-4610-936a-3fb5e61b60f5', 'data:image/png;base64,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', 'Pushin Pay', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_transaction` varchar(100) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `price` decimal(20,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(4) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `reference` varchar(191) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `2fa_secret` varchar(32) DEFAULT NULL,
  `2fa_active` tinyint(4) NOT NULL DEFAULT 0,
  `username` varchar(255) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `cpf_cnpj` varchar(255) DEFAULT '',
  `data_nascimento` date DEFAULT '1900-01-01',
  `telefone` varchar(20) DEFAULT NULL,
  `total_transacoes` int(11) DEFAULT 0,
  `permission` tinyint(4) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `ip_user` varchar(45) DEFAULT NULL,
  `transacoes_aproved` int(11) DEFAULT 0,
  `transacoes_recused` int(11) DEFAULT 0,
  `token` varchar(255) NOT NULL DEFAULT uuid(),
  `banido` tinyint(4) DEFAULT 0,
  `cliente_id` varchar(255) DEFAULT NULL,
  `user_id` varchar(255) NOT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `numero_residencia` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `foto_rg_frente` varchar(255) DEFAULT NULL,
  `foto_rg_verso` varchar(255) DEFAULT NULL,
  `selfie_rg` varchar(255) DEFAULT NULL,
  `media_faturamento` varchar(50) DEFAULT NULL,
  `rua` varchar(255) DEFAULT NULL,
  `plan_id` int(11) DEFAULT 1,
  `plan_expires_at` datetime DEFAULT NULL,
  `subscription_status` varchar(20) DEFAULT 'active',
  `taxa_cash_in_padrao_padrao` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `nome`, `email`, `2fa_secret`, `2fa_active`, `username`, `senha`, `cpf_cnpj`, `data_nascimento`, `telefone`, `total_transacoes`, `permission`, `avatar`, `status`, `data_cadastro`, `ip_user`, `transacoes_aproved`, `transacoes_recused`, `token`, `banido`, `cliente_id`, `user_id`, `cep`, `estado`, `cidade`, `bairro`, `numero_residencia`, `complemento`, `foto_rg_frente`, `foto_rg_verso`, `selfie_rg`, `media_faturamento`, `rua`, `plan_id`, `plan_expires_at`, `subscription_status`, `taxa_cash_in_padrao_padrao`) VALUES
(11323042, 'Luiz', 'yazesalinha12@gmail.com', NULL, 0, NULL, '$2y$10$CZAbdgbQmpuIwWfV9IV28OSuaElzEFcmluYppoGHtgLQa1/9wK.RW', '', '1900-01-01', '81985081547', 0, 1, NULL, '1', '2025-04-23 11:06:56', NULL, 0, 0, '376a32d5-204c-11f0-b1bd-3cd7830cb258', 0, '823add6b1bb4af2e16177bf2', 'luizbless7', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'active', 0.00),
(11374890, 'Diogo', 'diegoteste@gmail.com', NULL, 0, NULL, '$2y$10$zx92KrUvAifrQrMgQlK2muiRNTNtZmZnsW6mKD9Pu4cPi9..F0NJy', '', '1900-01-01', '71998656865', 0, 1, NULL, '1', '2025-06-17 08:48:51', NULL, 0, 0, '09d5e3cf-4b71-11f0-a5d9-1940f6116ffc', 0, 'd539d7c899934195198331e2', 'diogolima12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'active', 0.00),
(31589421, 'Jocimario', 'gabrieelsilvaaa63@gmail.com', NULL, 0, NULL, '$2y$10$Di4W6x4H8Q/FuGu18jkE5OXI814/8IN6qeV8O/XvdDXMopVixdq9.', '', '1900-01-01', '62999783522', 0, 1, NULL, '1', '2025-05-06 15:18:26', NULL, 0, 0, '80bcae85-2aa6-11f0-b1bd-3cd7830cb258', 0, 'c4f9d6423a9c40704d91dea2', 'lomfx44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'active', 0.00),
(42488839, 'dsadsadssad', 'nidsijs@dqdqwk.com', NULL, 0, NULL, '$2y$10$NHfWmGLOQ.dkhHmdgEQZMOGn5vqNMZX7ATsAamwJmjdLpq4luuiRO', '', '1900-01-01', '43243234432432', 0, 4, NULL, '1', '2025-04-26 13:12:32', NULL, 0, 0, '423743ad-22b9-11f0-b1bd-3cd7830cb258', 0, 'f0cebc1614b0032e49cc17b7', 'dsadasdsaddsa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'active', 0.00),
(46858453, 'pedro souza', 'mathgoldyoficial@gmail.com', NULL, 0, NULL, '$2y$10$9VSGbVlA4gvRahjLCFjFJuyRgPCgR4RYm/1bdADUN0lStMN9q0pGm', '', '1900-01-01', '84991106133', 0, 1, NULL, '1', '2026-02-15 02:12:17', NULL, 0, 0, 'e5d32789-0a2c-11f1-83ae-e4a8dff49cea', 0, '712ea7d427bafce25c2df9c7', 'mathvtr12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'active', 0.00),
(57998134, 'Paulo Reinan Oliveira', 'contatosx@icloud.com', NULL, 0, NULL, '$2y$10$3jwOlDyaLnxoBM3wYhyUgeGKw0NN6.Jq6dkZidla//JGgX5kFQ06u', '', '1900-01-01', '19993249965', 0, 1, NULL, '1', '2025-04-22 19:08:15', NULL, 0, 0, '4a4f3371-1fc6-11f0-b1bd-3cd7830cb258', 0, '886a27306cbfd33fb04900db', 'contatosx@icloud.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'active', 0.00),
(60806755, 'Eraldo', 'eraldofilhox70@gmail.com', NULL, 0, NULL, '$2y$10$CePAfNeUonefuyl2A0ua8uK6BdyHafTsGcOVoWE/PMWM5/UTW7FLm', '067.995.121-04 ', '1900-01-01', '64999846180', 0, 1, NULL, '1', '2025-04-16 17:14:05', NULL, 0, 0, '589f84f8-1aff-11f0-b1bd-3cd7830cb258', 0, '4d7bee14c59dad96cb770ebc', 'Eraldo', '75713-035', 'GO', 'Catalão', 'Margon II', '310', 'Casa', 'rg_frente_680010dcc3778.jpg', 'rg_verso_680010dcc3780.jpg', 'selfie_680010dcc3781.jpg', '10000-30000', 'Rua Sebastião Alves da Costa', 1, NULL, 'active', 0.00),
(65172020, 'matheus vitor', 'waveagc@gmail.com', NULL, 0, NULL, '$2y$10$9GQWhn0Np1mniWSpe6BG2OQARYI3vcvt7NQ6Kd3Ej4hVqJuLYLBeu', '', '1900-01-01', '84921593966', 0, 3, NULL, '0', '2026-02-15 02:03:40', NULL, 0, 0, 'b1c79000-0a2b-11f1-83ae-e4a8dff49cea', 0, 'a4aa49619679fa3cd83628e8', 'mathvtr', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'active', 0.00),
(73937336, 'Mfuehudwj hiwjswdwidjwidji jdiwjswihdfeufhiwj ijdiwjwihdiwkdoq jiwjdwidjwifjei jwdodkwofjiehiehgiejdiw jifjeifjeifwkfijrghis kwoskowfiejifefefefe uranopay.com', 'nomin.momin+445r0@mail.ru', NULL, 0, NULL, '$2y$10$92UDrQZ4r.mihX1OnuhAe.jjUn/iWv6UCiaurSV01421B.9jpsye.', '', '1900-01-01', '86913457331', 0, 1, NULL, '0', '2025-07-23 20:54:38', NULL, 0, 0, '64eb2122-6820-11f0-b3d1-41f1f4f1dc79', 0, 'b3c6eeb0d8b58d50d44739a7', 'Mfuehudwj hiwjswdwidjwidji jdiwjswihdfeufhiwj ijdiwjwihdiwkdoq jiwjdwidjwifjei jwdodkwofjiehiehgiejdiw jifjeifjeifwkfijrghis kwoskowfiejifefefefe uranopay.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'active', 0.00),
(74175399, 'CIPRIANO BAIRRAL', 'ciprianobairralcima@reborn.com', NULL, 0, NULL, '$2y$10$pVsFik4x3cBOoy.4Qlg1Tue.ouypJBYd2KTLV1KhQH0TtXMxSw9EK', '03872916000120', '1900-01-01', '22999765555', 0, 1, NULL, '5', '2025-07-23 13:33:14', NULL, 0, 0, 'bac78101-67e2-11f0-b3d1-41f1f4f1dc79', 0, '6628706e2b2170aeb253e045', 'cipriano01', '28495-000', 'RJ', 'APERIBE', 'CENTRO', '153', 'frente', 'rg_frente_68810f10ebacb.jpg', 'rg_verso_68810f10ebae1.jpg', 'selfie_68810f10ebae3.jpg', '30000-100000', 'CIDONIO BAIRRAL', 1, NULL, 'active', 0.00),
(80317961, 'caua jorge cavalcante da silva', 'caua77652@gmail.com', NULL, 0, NULL, '$2y$10$pLNkrJZzmw/2i51ySIA6XulTx8YSPergH2BzpexjoRCW1NmniIGAO', '149638384727', '1900-01-01', '81992866683', 1, 5, NULL, '1', '2025-04-22 21:02:31', NULL, 1, 0, '405f591f-1fd6-11f0-b1bd-3cd7830cb258', 0, '34930dca1d39fb7e4a9d7845', 'caua77652@gmail.com', '53370815', 'PE', 'olinda', 'ouro preto', '148', 'casa', 'rg_frente_68082eb0a55e0.jpg', 'rg_verso_68082eb0a55ea.jpg', 'selfie_68082eb0a55eb.jpg', '100000-400000', 'rua josue joaquim da silva ', 1, NULL, 'active', 0.00),
(91393696, 'pai88273017', 'cauacavalcante38@gmail.com', NULL, 0, NULL, '$2y$10$nU0dC8ApELIxEq6gS0c3dODqNCqQ39H1LmtyneO9g.PSAOQCTiojK', '03950648488', '1900-01-01', '81992866683', 2, 1, NULL, '1', '2025-04-16 13:48:14', NULL, 2, 0, '972395f7-1ae2-11f0-b1bd-3cd7830cb258', 0, 'add4056ace99cb965588a0e8', 'caua88', '53370815', 'pernambuco', 'olinda', 'ouro preto', '148', 'casa', 'rg_frente_67ffe0bd8fe04.jpg', 'rg_verso_67ffe0bd8fe0c.jpg', '', '', 'rua joaquim da silva', 1, NULL, 'active', 0.00),
(94818453, 'Stive Sousa de Paiva', 'stivevendas@catgroup.uk', NULL, 0, NULL, '$2y$10$KY2T4m5uNC3ZceQiES3aL.DgY3b3CMoD.B2dldLsaU.HeA3Bp87We', '', '1900-01-01', '1231970990', 0, 1, NULL, '1', '2025-04-24 15:56:32', NULL, 0, 0, 'd672413e-213d-11f0-b1bd-3cd7830cb258', 0, '786085883ab04da16e279559', 'stive02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'active', 0.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `users_key`
--

CREATE TABLE `users_key` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) DEFAULT NULL,
  `api_key` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `users_key`
--

INSERT INTO `users_key` (`id`, `user_id`, `api_key`, `status`) VALUES
(18, 'severino64', '81bb141a-1746-49a8-bb4a-c3b8aa0d2259', 'ativo'),
(19, 'loureirop81@gmail.com', '3b38c2960bd4d03fb25bd647', 'ativo'),
(20, 'testandoo', 'ccffca48927d457646912bc7', 'ativo'),
(21, 'thigas', 'f4186ba6a0f0bb2f9d39ce9a', 'ativo'),
(22, 'caua88', 'add4056ace99cb965588a0e8', 'ativo'),
(23, 'Eraldo', '4d7bee14c59dad96cb770ebc', 'ativo'),
(24, 'contatosx@icloud.com', '886a27306cbfd33fb04900db', 'ativo'),
(25, 'caua77652@gmail.com', '34930dca1d39fb7e4a9d7845', 'ativo'),
(26, 'luizbless7', '823add6b1bb4af2e16177bf2', 'ativo'),
(27, 'stive02', '786085883ab04da16e279559', 'ativo'),
(28, 'dsadasdsaddsa', 'f0cebc1614b0032e49cc17b7', 'ativo'),
(29, 'mathvtr12', '712ea7d427bafce25c2df9c7', 'ativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_providers`
--

CREATE TABLE `user_providers` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `provider_name` varchar(255) NOT NULL,
  `api_key` text DEFAULT NULL,
  `api_token` text DEFAULT NULL,
  `client_id` text DEFAULT NULL,
  `client_secret` text DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `user_providers`
--

INSERT INTO `user_providers` (`id`, `user_id`, `provider_name`, `api_key`, `api_token`, `client_id`, `client_secret`, `status`, `created_at`) VALUES
(2, 'mathvtr12', 'Pushin Pay', '', '62352|FmyiiE7ESX4PrHFKNPCX8DoDreuZqIeYW1Uon45Y91217ab0', '', '', 1, '2026-02-19 03:49:13');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `app`
--
ALTER TABLE `app`
  ADD PRIMARY KEY (`token`);

--
-- Índices de tabela `checkout_avaliacoes`
--
ALTER TABLE `checkout_avaliacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checkout_id` (`checkout_id`);

--
-- Índices de tabela `checkout_build`
--
ALTER TABLE `checkout_build`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_checkout_provider` (`user_provider_id`);

--
-- Índices de tabela `checkout_upsells`
--
ALTER TABLE `checkout_upsells`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checkout_id` (`checkout_id`);

--
-- Índices de tabela `digital_content_access`
--
ALTER TABLE `digital_content_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_checkout` (`transaction_id`,`checkout_id`),
  ADD KEY `checkout_id` (`checkout_id`),
  ADD KEY `client_email` (`client_email`),
  ADD KEY `access_token` (`access_token`);

--
-- Índices de tabela `domains`
--
ALTER TABLE `domains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `domain` (`domain`),
  ADD KEY `domain_2` (`domain`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `historico_transacoes`
--
ALTER TABLE `historico_transacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `vendedor_id` (`vendedor_id`),
  ADD KEY `comprador_id` (`comprador_id`),
  ADD KEY `data_transacao` (`data_transacao`);

--
-- Índices de tabela `payment_links`
--
ALTER TABLE `payment_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices de tabela `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `seguranca`
--
ALTER TABLE `seguranca`
  ADD PRIMARY KEY (`keyseguranca`);

--
-- Índices de tabela `solicitacoes`
--
ALTER TABLE `solicitacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_data` (`user_id`,`real_data`),
  ADD KEY `idx_status` (`status`);

--
-- Índices de tabela `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `transactions_user_id_index` (`user_id`) USING BTREE;

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Índices de tabela `users_key`
--
ALTER TABLE `users_key`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `user_providers`
--
ALTER TABLE `user_providers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `checkout_avaliacoes`
--
ALTER TABLE `checkout_avaliacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de tabela `checkout_build`
--
ALTER TABLE `checkout_build`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de tabela `checkout_upsells`
--
ALTER TABLE `checkout_upsells`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `digital_content_access`
--
ALTER TABLE `digital_content_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `domains`
--
ALTER TABLE `domains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `historico_transacoes`
--
ALTER TABLE `historico_transacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `payment_links`
--
ALTER TABLE `payment_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `solicitacoes`
--
ALTER TABLE `solicitacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=174;

--
-- AUTO_INCREMENT de tabela `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94818454;

--
-- AUTO_INCREMENT de tabela `users_key`
--
ALTER TABLE `users_key`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `user_providers`
--
ALTER TABLE `user_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `checkout_avaliacoes`
--
ALTER TABLE `checkout_avaliacoes`
  ADD CONSTRAINT `fk_checkout_avaliacoes` FOREIGN KEY (`checkout_id`) REFERENCES `checkout_build` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `checkout_build`
--
ALTER TABLE `checkout_build`
  ADD CONSTRAINT `fk_checkout_provider` FOREIGN KEY (`user_provider_id`) REFERENCES `user_providers` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `checkout_upsells`
--
ALTER TABLE `checkout_upsells`
  ADD CONSTRAINT `checkout_upsells_ibfk_1` FOREIGN KEY (`checkout_id`) REFERENCES `checkout_build` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
