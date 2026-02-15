-- Adminer 4.16.0 MySQL 10.11.10-MariaDB-log dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `admlogin`;
CREATE TABLE `admlogin` (
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `adquirentes`;
CREATE TABLE `adquirentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adquirente` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `adquirentes` (`id`, `adquirente`, `status`, `url`, `referencia`) VALUES
(1,	'bspay',	'0',	'https://api.bspay.co/',	'ad_bspay'),
(2,	'suitpay',	'0',	'https://t.me/notredamebra',	'ad_suitpay'),
(3,	'pagpix',	'0',	'https://t.me/notredamebra',	'ad_pagpix'),
(4,	'appmax',	'0',	'https://t.me/notredamebra',	'ad_appmax'),
(5,	'primepag',	'1',	'https://primepag.com.br/',	'ad_primepag');

DROP TABLE IF EXISTS `ad_appmax`;
CREATE TABLE `ad_appmax` (
  `id` int(11) NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `client_secret` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '0',
  `url_cash_out` varchar(255) NOT NULL DEFAULT '0',
  `taxa_pix_cash_in` decimal(10,2) NOT NULL DEFAULT 0.00,
  `taxa_pix_cash_out` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ad_appmax` (`id`, `client_id`, `client_secret`, `url`, `url_cash_out`, `taxa_pix_cash_in`, `taxa_pix_cash_out`) VALUES
(1,	'',	'',	'https://api.appmax.com.br',	'https://api.appmax.com.br/v1/payments/pix',	0.99,	3.67);

DROP TABLE IF EXISTS `ad_bspay`;
CREATE TABLE `ad_bspay` (
  `id` int(11) NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `client_secret` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '0',
  `url_cash_out` varchar(255) NOT NULL DEFAULT '0',
  `taxa_pix_cash_in` decimal(10,2) NOT NULL DEFAULT 0.00,
  `taxa_pix_cash_out` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ad_bspay` (`id`, `client_id`, `client_secret`, `url`, `url_cash_out`, `taxa_pix_cash_in`, `taxa_pix_cash_out`) VALUES
(1,	'severino64_1697651537117',	'e2fcc894d148f405c188d31f11f16039f7becca28cf01448cc297f450691b58b121d76a1ffc24376b84436cabbf6130f',	'https://ws.suitpay.app/api/v1/gateway/request-qrcode',	'0',	0.00,	0.00);

DROP TABLE IF EXISTS `ad_pagpix`;
CREATE TABLE `ad_pagpix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `secret_key` varchar(255) NOT NULL,
  `url_cash_in` varchar(255) NOT NULL,
  `url_cash_out` varchar(255) NOT NULL,
  `taxa_pix_cash_in` decimal(10,2) NOT NULL DEFAULT 0.70,
  `taxa_pix_cash_out` decimal(10,2) NOT NULL DEFAULT 0.70,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ad_pagpix` (`id`, `secret_key`, `url_cash_in`, `url_cash_out`, `taxa_pix_cash_in`, `taxa_pix_cash_out`) VALUES
(1,	'',	'https://api.pagpix.ai/api/user/transactions',	'https://api.pagpix.ai/api/user/cashout',	0.70,	0.70);

DROP TABLE IF EXISTS `ad_primepag`;
CREATE TABLE `ad_primepag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `client_id` varchar(255) NOT NULL,
  `client_secret` varchar(255) NOT NULL,
  `secret_key` varchar(255) DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `taxa_pix_cash_in` decimal(10,2) DEFAULT 4.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ad_primepag` (`id`, `status`, `client_id`, `client_secret`, `secret_key`, `webhook_url`, `taxa_pix_cash_in`, `created_at`, `updated_at`) VALUES
(1,	1,	'',	'',	'',	'https://api.uranopay.com/v1/adquirente/primepag/webhook/',	4.00,	'2025-04-22 22:37:17',	'2025-05-10 16:34:42');

DROP TABLE IF EXISTS `ad_suitpay`;
CREATE TABLE `ad_suitpay` (
  `id` int(11) NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `client_secret` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '0',
  `url_cash_out` varchar(255) NOT NULL DEFAULT '0',
  `taxa_pix_cash_in` decimal(10,2) NOT NULL DEFAULT 0.00,
  `taxa_pix_cash_out` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ad_suitpay` (`id`, `client_id`, `client_secret`, `url`, `url_cash_out`, `taxa_pix_cash_in`, `taxa_pix_cash_out`) VALUES
(1,	'severino64_1697651537117',	'e2fcc894d148f405c188d31f11f16039f7becca28cf01448cc297f450691b58b121d76a1ffc24376b84436cabbf6130f',	'https://ws.suitpay.app/api/v1/gateway/request-qrcode',	'0',	0.00,	0.00);

DROP TABLE IF EXISTS `app`;
CREATE TABLE `app` (
  `token` varchar(255) NOT NULL,
  `numero_usuarios` int(11) DEFAULT NULL,
  `faturamento_total` decimal(10,2) DEFAULT NULL,
  `total_transacoes` int(11) DEFAULT NULL,
  `visitantes` int(11) DEFAULT NULL,
  `manutencao` tinyint(1) DEFAULT NULL,
  `taxa_cash_in_padrao` decimal(5,2) DEFAULT NULL,
  `taxa_cash_out_padrao` decimal(5,2) DEFAULT NULL,
  `taxa_fixa_padrao` decimal(5,2) DEFAULT NULL,
  `sms_url_cadastro_pendente` varchar(255) DEFAULT NULL,
  `sms_url_cadastro_ativo` varchar(255) DEFAULT NULL,
  `sms_url_notificacao_user` varchar(255) DEFAULT NULL,
  `sms_url_redefinir_senha` varchar(255) DEFAULT NULL,
  `sms_url_autenticar_admin` varchar(255) DEFAULT NULL,
  `taxa_pix_valor_real_cash_in_padrao` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `app` (`token`, `numero_usuarios`, `faturamento_total`, `total_transacoes`, `visitantes`, `manutencao`, `taxa_cash_in_padrao`, `taxa_cash_out_padrao`, `taxa_fixa_padrao`, `sms_url_cadastro_pendente`, `sms_url_cadastro_ativo`, `sms_url_notificacao_user`, `sms_url_redefinir_senha`, `sms_url_autenticar_admin`, `taxa_pix_valor_real_cash_in_padrao`) VALUES
('',	NULL,	NULL,	NULL,	NULL,	NULL,	5.00,	2.00,	NULL,	'https://v1.smsfunnel.com.br/integrations/lists/450db410-9890-470d-af69-ea94f00273a4/add-lead',	'ok',	'ok',	'ok',	'ok',	1.00);

DROP TABLE IF EXISTS `checkout_avaliacoes`;
CREATE TABLE `checkout_avaliacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checkout_id` int(11) NOT NULL,
  `nome_avaliador` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `comentario` text NOT NULL,
  `estrelas` int(1) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `checkout_id` (`checkout_id`),
  CONSTRAINT `fk_checkout_avaliacoes` FOREIGN KEY (`checkout_id`) REFERENCES `checkout_build` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `checkout_avaliacoes` (`id`, `checkout_id`, `nome_avaliador`, `avatar`, `comentario`, `estrelas`, `data_criacao`) VALUES
(10,	14,	'Lucas Medeiros',	'../uploads/avatares/68515ab2687ff_Screenshot_20250617-090307~2.png',	'Acabei de assinar aqui , é top d+ ,ainda comprei os vídeos da Mayara junto 🥵🫣🤤',	5,	'2025-06-17 12:08:20'),
(11,	14,	'João Silva ',	'../uploads/avatares/68515af07c94d_Screenshot_20250617-090442~2.png',	'Muito bom ,melhores vídeos 🫣',	5,	'2025-06-17 12:09:21'),
(12,	14,	'Mateus ',	'../uploads/avatares/68515b2e3d1cc_Screenshot_20250617-090338~2.png',	'Rapaz ,já tinha assinado vários grupos  e nunca vi um igual esse ,todos os vídeos são de qualidade e bem raros de se achar ',	5,	'2025-06-17 12:10:24'),
(13,	14,	'Cauã santos ',	'../uploads/avatares/68515c9285503_Screenshot_20250617-090357~2.png',	'Qui grupo top 🔝 gostei mia linda',	5,	'2025-06-17 12:16:20'),
(14,	14,	'Everton ',	'../uploads/avatares/68515cc09a8f0_Screenshot_20250617-090419~2.png',	'Aprovado 🤤🤤🤤🤤🤤,nem sabia que a Kamilinha tinha fotos e vídeos ,amei ',	5,	'2025-06-17 12:17:05'),
(15,	14,	'Victor Hugo ',	'../uploads/avatares/68515cfddb1b6_Screenshot_20250617-090425~2.png',	'Rapaz ,esse vídeo da Kamilinha dando pro nino é o melhor ,vídeo top da porra ,até o nino veyyy,comeu a Kamilinha ',	5,	'2025-06-17 12:18:06'),
(23,	18,	'joao silva',	'../uploads/avatares/68688f90590fe_carlos.jpg',	'Confesso que fiquei com medo, mas assim que paguei a taxa o dinheiro entrou certinho. Empresa séria de verdade!',	5,	'2025-07-05 02:36:00'),
(24,	18,	'alex almeida',	'../uploads/avatares/68688f906003f_opaaja.jpg',	'Fiz o pagamento da taxa e deu tudo certo, recebi o valor rapidinho. Muito confiável, vou pagar a conta de água e luuzzz, obrigadoo.',	5,	'2025-07-05 02:36:00'),
(25,	18,	'marcos junior',	'../uploads/avatares/68688f907c133_favelaod.jfif',	'P****, esse site me ajudou a apagar tudo parabes de verdade, ja indiquei pra minha familia toda, obrigaodooo.',	5,	'2025-07-05 02:36:00'),
(26,	18,	'mariana B.',	'../uploads/avatares/68688f907c2ae_2e4c8ea71b690f8b23c401a98c1308ae.jpg',	'nÃO ACREDITEI NISSO, SLCC, MAIS O NEGOCIO FUNCIONOU PODEM FICAR tranquilos com isso, nota 1000000',	5,	'2025-07-05 02:36:00'),
(27,	19,	'joao silva',	'../uploads/avatares/6877cc5f940e1_1].jpeg',	'Achei que era golpe, mas depois que paguei a taxa o dinheiro entrou certinho.',	5,	'2025-07-16 15:59:27'),
(28,	19,	'marcos antonio',	'../uploads/avatares/6877cc5f94a2b_2.jpeg',	'Funcionou direitinho! Paguei a taxa e recebi na hora no Pix.',	5,	'2025-07-16 15:59:27'),
(29,	19,	'emanuel gomes',	'../uploads/avatares/6877cc5f94d72_3.jpeg',	'Desconfiado no começo, mas fiz o pagamento da taxa e o valor caiu rapidinho.',	5,	'2025-07-16 15:59:27'),
(30,	19,	'gabriel victor',	'../uploads/avatares/6877cc5fac6f7_4.jpeg',	'Muito rápido fiz o pagamento da taxa e logo em seguida recebi.',	5,	'2025-07-16 15:59:27'),
(31,	19,	'larissa muniz',	'../uploads/avatares/6877cc5fbacce_7.jpeg',	'Pode confiar, meninas! Paguei a taxa e recebi o valor certinho.',	5,	'2025-07-16 15:59:27');

DROP TABLE IF EXISTS `checkout_build`;
CREATE TABLE `checkout_build` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_produto` varchar(255) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `referencia` varchar(255) NOT NULL,
  `logo_produto` varchar(255) DEFAULT NULL,
  `obrigado_page` text DEFAULT NULL,
  `key_gateway` varchar(255) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `email` varchar(255) NOT NULL,
  `url_checkout` varchar(255) DEFAULT NULL,
  `banner_produto` varchar(255) NOT NULL,
  `primepag_client_id` varchar(255) DEFAULT NULL,
  `primepag_client_secret` varchar(255) DEFAULT NULL,
  `primepag_secret_key` varchar(255) DEFAULT NULL,
  `arquivo_digital` varchar(255) DEFAULT NULL COMMENT 'Caminho para o arquivo digital enviado',
  `link_externo` varchar(500) DEFAULT NULL COMMENT 'Link externo para download (Google Drive, Mega, etc.)',
  `tipo_entrega` enum('arquivo','link','nenhum') NOT NULL DEFAULT 'nenhum' COMMENT 'Tipo de entrega digital',
  `exibir_cpf` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Define se o campo de CPF será exibido no checkout (1=sim, 0=não)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `checkout_build` (`id`, `name_produto`, `valor`, `referencia`, `logo_produto`, `obrigado_page`, `key_gateway`, `ativo`, `email`, `url_checkout`, `banner_produto`, `primepag_client_id`, `primepag_client_secret`, `primepag_secret_key`, `arquivo_digital`, `link_externo`, `tipo_entrega`, `exibir_cpf`) VALUES
(1,	'visagra',	'12,30',	'',	'../uploads/comnbobanner.jpg',	'https://web.uranopay.com',	'add4056ace99cb965588a0e8',	1,	'cauacavalcante38@gmail.com',	'https://web.uranopay.com/checkout/v1/?id=l0MDnPe5nkCiiPsAOOZAIMXB',	'../uploads/COMBOINEJECT.jpg',	NULL,	NULL,	NULL,	NULL,	NULL,	'nenhum',	1),
(5,	'Igamimg',	'19.9',	'',	'../uploads/ChatGPT Image 22 de abr. de 2025, 13_06_57 (1).png',	'jogar.win',	'886a27306cbfd33fb04900db',	1,	'contatosx@icloud.com',	'https://web.uranopay.com/checkout/v1/?id=pxb7AV1HjoRpE9SGxrGCfNJC',	'../uploads/ChatGPT Image 22 de abr. de 2025, 13_06_57 (1).png',	NULL,	NULL,	NULL,	NULL,	NULL,	'nenhum',	1),
(7,	'Tênis',	'10',	'',	'../uploads/user_rank.png',	'https://uranopay.com',	'786085883ab04da16e279559',	1,	'stivevendas@catgroup.uk',	'https://web.uranopay.com/checkout/v1/?id=c0pA51EJ4F10n1SztE1TT2tX',	'../uploads/user_rank.png',	NULL,	NULL,	NULL,	NULL,	NULL,	'nenhum',	1),
(8,	'teste',	'100',	'',	'../uploads/1745506450646..webp',	'teste',	'f0cebc1614b0032e49cc17b7',	1,	'nidsijs@dqdqwk.com',	'https://web.uranopay.com/checkout/v1/?id=ssnnF6jt6o84mKaRmshzkmOe',	'../uploads/1745508074456..webp',	NULL,	NULL,	NULL,	NULL,	NULL,	'nenhum',	1),
(14,	'GRUPO DE VAZADOS',	'20',	'',	'../uploads/IMG-20250501-WA0133.jpg',	NULL,	'd539d7c899934195198331e2',	1,	'diegoteste@gmail.com',	'https://web.uranopay.com/checkout/v1/?id=sBV1tu5o0ZXXouTg8cuznqu3',	'../uploads/IMG-20250501-WA0164.jpg',	NULL,	NULL,	NULL,	'',	'https://t.me/+ZHUhmGVL4u8yNGUx',	'link',	0),
(18,	'Taxa anti-fraude Spotify',	'20',	'',	'../uploads/foto.jpeg',	NULL,	'34930dca1d39fb7e4a9d7845',	1,	'caua77652@gmail.com',	'https://web.uranopay.com/checkout/v1/?id=cwF8Q6kdzU49E9DywDGEcdZB',	'../uploads/fotu1.jfif',	NULL,	NULL,	NULL,	'',	'',	'nenhum',	0),
(19,	'Taxa anti-fraude Shein',	'1',	'',	'../uploads/sheinn9.png',	NULL,	'34930dca1d39fb7e4a9d7845',	1,	'caua77652@gmail.com',	'https://web.uranopay.com/checkout/v1/?id=DKiU1UDfvWWGi4ipF0s6oSJF',	'../uploads/sheinnpay.webp',	NULL,	NULL,	NULL,	'',	'',	'nenhum',	0);

DROP TABLE IF EXISTS `checkout_upsells`;
CREATE TABLE `checkout_upsells` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checkout_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `checkout_id` (`checkout_id`),
  CONSTRAINT `checkout_upsells_ibfk_1` FOREIGN KEY (`checkout_id`) REFERENCES `checkout_build` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `checkout_upsells` (`id`, `checkout_id`, `nome`, `descricao`, `valor`, `imagem`, `ordem`, `ativo`, `data_criacao`) VALUES
(7,	14,	'FOTO MINHA (MAYARA) ',	'Se quiser me ver pelada ,compre esse conteúdo extra por 7 reais e veja minhas fotos e vídeos pelada e dando pro meu primo .',	7.00,	'../uploads/upsells/685158d3e7b8d_IMG_20250617_085943_595.jpg',	0,	1,	'2025-06-17 12:00:20'),
(12,	18,	'Saque em dobro',	'Seu pix em dobro, com uso maximo de 1 vez.',	4.99,	'../uploads/upsells/68688f908b011_foto.jpeg',	0,	1,	'2025-07-05 02:36:00'),
(13,	19,	'Saque em dobro',	'O dinheiro entra dobrado.',	7.00,	'../uploads/upsells/6877cc5fe1af0_sheinnpay.webp',	0,	1,	'2025-07-16 15:59:27');

DROP TABLE IF EXISTS `confirmar_deposito`;
CREATE TABLE `confirmar_deposito` (
  `email` varchar(255) NOT NULL,
  `externalreference` varchar(255) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `data` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `digital_content_access`;
CREATE TABLE `digital_content_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(255) NOT NULL COMMENT 'ID da transação/referência externa',
  `checkout_id` int(11) NOT NULL COMMENT 'ID do produto na tabela checkout_build',
  `client_email` varchar(255) NOT NULL COMMENT 'Email do cliente',
  `access_type` enum('link','download') NOT NULL COMMENT 'Tipo de acesso (link ou download)',
  `access_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Número de acessos realizados',
  `max_access` int(11) NOT NULL DEFAULT 3 COMMENT 'Número máximo de acessos permitidos',
  `last_access` datetime DEFAULT NULL COMMENT 'Data e hora do último acesso',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Data de criação do registro',
  `access_token` varchar(64) NOT NULL COMMENT 'Token único para acesso seguro',
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_checkout` (`transaction_id`,`checkout_id`),
  KEY `checkout_id` (`checkout_id`),
  KEY `client_email` (`client_email`),
  KEY `access_token` (`access_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `digital_content_access` (`id`, `transaction_id`, `checkout_id`, `client_email`, `access_type`, `access_count`, `max_access`, `last_access`, `created_at`, `access_token`) VALUES
(1,	'TEMP_XpMLInGLpa53LP4h8R1lp2UD_1746123919',	10,	'cliente@exemplo.com',	'link',	4,	3,	'2025-05-01 18:34:16',	'2025-05-01 18:25:19',	'639cc00ad68033b6a24cd68712c86ddba8c1205c73bd73e57dd63e545da2254c'),
(2,	'TEMP_XpMLInGLpa53LP4h8R1lp2UD_1746124856',	10,	'cliente@exemplo.com',	'link',	3,	3,	'2025-05-01 18:41:07',	'2025-05-01 18:40:56',	'83959bdf65d8d8c9b27522f64d12cdb90c82b13094803cf9d7cea9352e2fb045'),
(3,	'TEMP_XpMLInGLpa53LP4h8R1lp2UD_1746128009',	10,	'cliente@exemplo.com',	'link',	0,	3,	NULL,	'2025-05-01 19:33:29',	'02caa247e3b3a87dff110647a6417dd44766b9ddc8f37cd16d4432514b424e41'),
(4,	'TEMP_XpMLInGLpa53LP4h8R1lp2UD_1746128566',	10,	'cliente@exemplo.com',	'link',	1,	3,	'2025-05-10 19:08:25',	'2025-05-01 19:42:46',	'59bc1b2c41f84aa5af073e994b43705f142a8014060f9ed34a756da9b73ec76b'),
(5,	'TEMP_XpMLInGLpa53LP4h8R1lp2UD_1746130245',	10,	'cliente@exemplo.com',	'link',	1,	3,	'2025-05-01 20:11:18',	'2025-05-01 20:10:45',	'64441996972619d522910a6133fa080d260745784ddb37bb7c74abdde52b0f9a'),
(6,	'TEST_1746186451',	10,	'teste@exemplo.com',	'link',	0,	3,	NULL,	'2025-05-02 18:56:43',	'cb719abe802d4d8fb1327b29b4dfa77ecbe9231487026495b3f4e19bd319dfe7'),
(7,	'DEBUG_1746905353',	15,	'teseete@uranopay.com',	'link',	3,	3,	'2025-06-17 12:24:05',	'2025-06-17 12:23:41',	'f15279516a2f8a5fb260a3e3679345f6f5c6667742d1e3c8906f2c1a0b0ed11f'),
(8,	'DEBUG_1746905353',	14,	'teseete@uranopay.com',	'link',	3,	3,	'2025-06-17 12:34:24',	'2025-06-17 12:29:27',	'7c22f0d06b060f428608c7150f73b290b8d55395b9dc6c9713aebb756096ab31');

DROP TABLE IF EXISTS `historico_transacoes`;
CREATE TABLE `historico_transacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(255) NOT NULL COMMENT 'ID da transação/referência externa',
  `checkout_id` int(11) NOT NULL COMMENT 'ID do produto na tabela checkout_build',
  `vendedor_id` varchar(255) NOT NULL COMMENT 'user_id do vendedor',
  `comprador_id` varchar(255) DEFAULT NULL COMMENT 'user_id do comprador',
  `valor_total` decimal(10,2) NOT NULL COMMENT 'Valor total da transação',
  `valor_liquido` decimal(10,2) NOT NULL COMMENT 'Valor líquido após taxas',
  `data_transacao` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Data e hora da transação',
  `tipo_transacao` enum('venda','saque','estorno') NOT NULL DEFAULT 'venda' COMMENT 'Tipo de transação',
  `descricao` varchar(255) DEFAULT NULL COMMENT 'Descrição adicional',
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `vendedor_id` (`vendedor_id`),
  KEY `comprador_id` (`comprador_id`),
  KEY `data_transacao` (`data_transacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `historico_transacoes` (`id`, `transaction_id`, `checkout_id`, `vendedor_id`, `comprador_id`, `valor_total`, `valor_liquido`, `data_transacao`, `tipo_transacao`, `descricao`) VALUES
(1,	'TEST_1746222182',	0,	'caua77652@gmail.com',	NULL,	105.00,	100.00,	'2025-05-02 21:43:02',	'venda',	'Teste manual'),
(2,	'DEBUG_1746903475',	10,	'caua77652@gmail.com',	NULL,	10.00,	9.50,	'2025-05-10 18:57:55',	'venda',	'Venda de teste do produto #10 (Profelar)'),
(3,	'DEBUG_1746905339',	1,	'cauacavalcante38@gmail.com',	NULL,	10.00,	9.50,	'2025-05-10 19:28:59',	'venda',	'Venda de teste do produto #1 (visagra)'),
(4,	'DEBUG_1746905353',	1,	'cauacavalcante38@gmail.com',	NULL,	10.00,	9.50,	'2025-05-10 19:29:13',	'venda',	'Venda de teste do produto #1 (visagra)');

DROP TABLE IF EXISTS `logs_ip_cash_out`;
CREATE TABLE `logs_ip_cash_out` (
  `ip` varchar(45) NOT NULL,
  `data` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ip`,`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `pix_deposito`;
CREATE TABLE `pix_deposito` (
  `id` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `data` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `pix_solicitacoes`;
CREATE TABLE `pix_solicitacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `externalreference` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `deposito_liquido` decimal(10,2) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_document` varchar(20) DEFAULT NULL,
  `client_email` varchar(255) NOT NULL,
  `real_data` datetime NOT NULL,
  `status` varchar(50) NOT NULL,
  `paymentcode` text DEFAULT NULL,
  `adquirente_ref` varchar(50) DEFAULT 'primepag',
  `client_telefone` varchar(20) DEFAULT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `taxa_cash_in` decimal(5,2) DEFAULT 4.00,
  `qrcode_image_url` varchar(255) DEFAULT NULL,
  `expiration_time` int(11) DEFAULT 1800,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_externalreference` (`externalreference`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_real_data` (`real_data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `retiradas`;
CREATE TABLE `retiradas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `valor_liquido` decimal(10,2) DEFAULT NULL,
  `tipo_chave` varchar(50) DEFAULT NULL,
  `chave` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `data_solicitacao` datetime DEFAULT current_timestamp(),
  `data_pagamento` datetime DEFAULT NULL,
  `taxa_cash_out` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `seguranca`;
CREATE TABLE `seguranca` (
  `keyseguranca` varchar(255) NOT NULL,
  PRIMARY KEY (`keyseguranca`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `seguranca` (`keyseguranca`) VALUES
('24142414dertinhoiakdsj4847jdks92m');

DROP TABLE IF EXISTS `solicitacoes`;
CREATE TABLE `solicitacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `adquirente_ref` varchar(255) DEFAULT NULL,
  `taxa_cash_in` decimal(10,2) DEFAULT NULL,
  `deposito_liquido` varchar(255) NOT NULL DEFAULT '0',
  `taxa_pix_cash_in_adquirente` decimal(10,2) NOT NULL DEFAULT 0.00,
  `taxa_pix_cash_in_valor_fixo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `client_telefone` varchar(15) DEFAULT NULL,
  `executor_ordem` varchar(255) DEFAULT NULL,
  `descricao_transacao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_data` (`user_id`,`real_data`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `solicitacoes` (`id`, `user_id`, `externalreference`, `amount`, `client_name`, `client_document`, `client_email`, `real_data`, `status`, `qrcode_pix`, `paymentcode`, `idtransaction`, `paymentCodeBase64`, `adquirente_ref`, `taxa_cash_in`, `deposito_liquido`, `taxa_pix_cash_in_adquirente`, `taxa_pix_cash_in_valor_fixo`, `client_telefone`, `executor_ordem`, `descricao_transacao`) VALUES
(161,	'severino64',	'IlMR1Kmovkd49Q3CZMDaW87tQaIDCYkJ',	30.00,	'Maria Oliveira',	'98765432100',	'maria.oliveira@email.com',	'2024-10-04',	'paid',	'0',	'00020126850014br.gov.bcb.pix2563pix.voluti.com.br/qr/v3/at/767f2ab7-3d2c-47a0-9126-51a03e316a935204000053039865802BR5925PAGPIX SOLUCAO EM PAGAMEN6014SAO BERNARDO D62070503***63040A9F',	'a27c989e-f57d-49fe-852d-a7608988f745',	'MDAwMjAxMjY4NTAwMTRici5nb3YuYmNiLnBpeDI1NjNwaXgudm9sdXRpLmNvbS5ici9xci92My9hdC83NjdmMmFiNy0zZDJjLTQ3YTAtOTEyNi01MWEwM2UzMTZhOTM1MjA0MDAwMDUzMDM5ODY1ODAyQlI1OTI1UEFHUElYIFNPTFVDQU8gRU0gUEFHQU1FTjYwMTRTQU8gQkVSTkFSRE8gRDYyMDcwNTAzKioqNjMwNDBBOUY=',	'pagpix',	2.00,	'29.4',	0.70,	1.00,	'81994298684',	NULL,	NULL),
(162,	'sistema',	'TEST_1746186440',	10.00,	'Cliente Teste',	'11999999999',	'teste@exemplo.com',	'2025-05-02',	'paid',	'0',	'TESTE_CONTENT_1746186440',	'0',	NULL,	'primepag',	4.00,	'9.6',	0.00,	0.00,	'11999999999',	NULL,	NULL),
(163,	'sistema',	'TEST_1746186451',	10.00,	'Cliente Teste',	'11999999999',	'teste@exemplo.com',	'2025-05-02',	'paid',	'0',	'TESTE_CONTENT_1746186451',	'0',	NULL,	'primepag',	4.00,	'9.6',	0.00,	0.00,	'11999999999',	NULL,	NULL),
(164,	'caua77652@gmail.com',	'TEST_1746902917',	10.00,	'Cliente Teste',	'11999999999',	'dasasd@w0dqw.com',	'2025-05-10',	'paid',	'0',	'0',	'0',	NULL,	NULL,	NULL,	'0',	0.00,	0.00,	NULL,	NULL,	NULL),
(165,	'cauacavalcante38@gmail.com',	'TEST_1746902967',	100.00,	'Cliente Teste',	'11999999999',	'erwe@w0kdqwo0.com',	'2025-05-10',	'paid',	'0',	'0',	'0',	NULL,	NULL,	NULL,	'0',	0.00,	0.00,	NULL,	NULL,	NULL),
(166,	'caua77652@gmail.com',	'TEST_1746903173',	1.00,	'Cliente Teste',	'11999999999',	'dasds@dlpqow.com',	'2025-05-10',	'paid',	'0',	'0',	'0',	NULL,	NULL,	NULL,	'0',	0.00,	0.00,	NULL,	NULL,	NULL),
(167,	'cauacavalcante38@gmail.com',	'TEST_1746903185',	1.00,	'Cliente Teste',	'11999999999',	'dsds@dlw.com',	'2025-05-10',	'paid',	'0',	'0',	'0',	NULL,	NULL,	NULL,	'0',	0.00,	0.00,	NULL,	NULL,	NULL),
(168,	'caua77652@gmail.com',	'DEBUG_1746903475',	10.00,	'Cliente Teste',	'11999999999',	'teste@uranopay.com',	'2025-05-10',	'paid',	'0',	'0',	'0',	NULL,	NULL,	NULL,	'0',	0.00,	0.00,	NULL,	NULL,	NULL),
(169,	'cauacavalcante38@gmail.com',	'DEBUG_1746905339',	10.00,	'Cliente Teste',	'11999999999',	'teste@uranopay.com',	'2025-05-10',	'paid',	'0',	'0',	'0',	NULL,	NULL,	NULL,	'0',	0.00,	0.00,	NULL,	NULL,	NULL),
(170,	'cauacavalcante38@gmail.com',	'DEBUG_1746905353',	10.00,	'Cliente Teste',	'11999999999',	'teseete@uranopay.com',	'2025-05-10',	'paid',	'0',	'0',	'0',	NULL,	NULL,	NULL,	'0',	0.00,	0.00,	NULL,	NULL,	NULL);

DROP TABLE IF EXISTS `solicitacoes_cash_out`;
CREATE TABLE `solicitacoes_cash_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) NOT NULL,
  `externalreference` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `beneficiaryname` varchar(255) NOT NULL,
  `beneficiarydocument` varchar(50) NOT NULL,
  `pix` varchar(255) NOT NULL,
  `type` enum('PIX','BOLETO','CREDIT_CARD') NOT NULL,
  `pixkey` varchar(255) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('PENDING','COMPLETED','CANCELLED') NOT NULL,
  `idtransaction` varchar(255) NOT NULL,
  `taxa_cash_out` decimal(10,2) NOT NULL,
  `cash_out_liquido` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_transaction` varchar(100) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `price` decimal(20,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(4) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `reference` varchar(191) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `transactions_user_id_index` (`user_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `2fa_secret` varchar(32) DEFAULT NULL,
  `2fa_active` tinyint(4) NOT NULL DEFAULT 0,
  `username` varchar(255) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `cpf_cnpj` varchar(255) DEFAULT '',
  `data_nascimento` date DEFAULT '1900-01-01',
  `telefone` varchar(20) DEFAULT NULL,
  `saldo` decimal(15,2) DEFAULT 0.00,
  `total_transacoes` int(11) DEFAULT 0,
  `permission` tinyint(4) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `ip_user` varchar(45) DEFAULT NULL,
  `transacoes_aproved` int(11) DEFAULT 0,
  `transacoes_recused` int(11) DEFAULT 0,
  `valor_sacado` decimal(15,2) DEFAULT 0.00,
  `valor_saque_pendente` decimal(15,2) DEFAULT 0.00,
  `taxa_cash_in` decimal(5,2) DEFAULT 0.00,
  `taxa_cash_out` decimal(5,2) DEFAULT 0.00,
  `token` varchar(255) NOT NULL DEFAULT uuid(),
  `banido` tinyint(4) DEFAULT 0,
  `cliente_id` varchar(255) DEFAULT NULL,
  `taxa_percentual` decimal(10,2) DEFAULT 5.00,
  `volume_transacionado` decimal(20,2) NOT NULL DEFAULT 0.00,
  `valor_pago_taxa` decimal(20,2) NOT NULL DEFAULT 0.00,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `nome`, `email`, `2fa_secret`, `2fa_active`, `username`, `senha`, `cpf_cnpj`, `data_nascimento`, `telefone`, `saldo`, `total_transacoes`, `permission`, `avatar`, `status`, `data_cadastro`, `ip_user`, `transacoes_aproved`, `transacoes_recused`, `valor_sacado`, `valor_saque_pendente`, `taxa_cash_in`, `taxa_cash_out`, `token`, `banido`, `cliente_id`, `taxa_percentual`, `volume_transacionado`, `valor_pago_taxa`, `user_id`, `cep`, `estado`, `cidade`, `bairro`, `numero_residencia`, `complemento`, `foto_rg_frente`, `foto_rg_verso`, `selfie_rg`, `media_faturamento`, `rua`) VALUES
(11323042,	'Luiz',	'yazesalinha12@gmail.com',	NULL,	0,	NULL,	'$2y$10$CZAbdgbQmpuIwWfV9IV28OSuaElzEFcmluYppoGHtgLQa1/9wK.RW',	'',	'1900-01-01',	'81985081547',	0.00,	0,	1,	NULL,	'1',	'2025-04-23 11:06:56',	NULL,	0,	0,	0.00,	0.00,	5.00,	2.00,	'376a32d5-204c-11f0-b1bd-3cd7830cb258',	0,	'823add6b1bb4af2e16177bf2',	5.00,	0.00,	0.00,	'luizbless7',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL),
(11374890,	'Diogo',	'diegoteste@gmail.com',	NULL,	0,	NULL,	'$2y$10$zx92KrUvAifrQrMgQlK2muiRNTNtZmZnsW6mKD9Pu4cPi9..F0NJy',	'',	'1900-01-01',	'71998656865',	0.00,	0,	1,	NULL,	'1',	'2025-06-17 08:48:51',	NULL,	0,	0,	0.00,	0.00,	0.00,	0.00,	'09d5e3cf-4b71-11f0-a5d9-1940f6116ffc',	0,	'd539d7c899934195198331e2',	0.00,	0.00,	0.00,	'diogolima12',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL),
(31589421,	'Jocimario',	'gabrieelsilvaaa63@gmail.com',	NULL,	0,	NULL,	'$2y$10$Di4W6x4H8Q/FuGu18jkE5OXI814/8IN6qeV8O/XvdDXMopVixdq9.',	'',	'1900-01-01',	'62999783522',	0.00,	0,	1,	NULL,	'1',	'2025-05-06 15:18:26',	NULL,	0,	0,	0.00,	0.00,	5.50,	5.00,	'80bcae85-2aa6-11f0-b1bd-3cd7830cb258',	0,	'c4f9d6423a9c40704d91dea2',	2.00,	0.00,	0.00,	'lomfx44',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL),
(42488839,	'dsadsadssad',	'nidsijs@dqdqwk.com',	NULL,	0,	NULL,	'$2y$10$NHfWmGLOQ.dkhHmdgEQZMOGn5vqNMZX7ATsAamwJmjdLpq4luuiRO',	'',	'1900-01-01',	'43243234432432',	0.00,	0,	4,	NULL,	'1',	'2025-04-26 13:12:32',	NULL,	0,	0,	0.00,	0.00,	5.00,	2.00,	'423743ad-22b9-11f0-b1bd-3cd7830cb258',	0,	'f0cebc1614b0032e49cc17b7',	5.00,	0.00,	0.00,	'dsadasdsaddsa',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL),
(57998134,	'Paulo Reinan Oliveira',	'contatosx@icloud.com',	NULL,	0,	NULL,	'$2y$10$3jwOlDyaLnxoBM3wYhyUgeGKw0NN6.Jq6dkZidla//JGgX5kFQ06u',	'',	'1900-01-01',	'19993249965',	0.00,	0,	1,	NULL,	'1',	'2025-04-22 19:08:15',	NULL,	0,	0,	0.00,	0.00,	5.00,	2.00,	'4a4f3371-1fc6-11f0-b1bd-3cd7830cb258',	0,	'886a27306cbfd33fb04900db',	5.00,	0.00,	0.00,	'contatosx@icloud.com',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL),
(60806755,	'Eraldo',	'eraldofilhox70@gmail.com',	NULL,	0,	NULL,	'$2y$10$CePAfNeUonefuyl2A0ua8uK6BdyHafTsGcOVoWE/PMWM5/UTW7FLm',	'067.995.121-04 ',	'1900-01-01',	'64999846180',	0.00,	0,	1,	NULL,	'1',	'2025-04-16 17:14:05',	NULL,	0,	0,	0.00,	0.00,	5.00,	2.00,	'589f84f8-1aff-11f0-b1bd-3cd7830cb258',	0,	'4d7bee14c59dad96cb770ebc',	5.00,	0.00,	0.00,	'Eraldo',	'75713-035',	'GO',	'Catalão',	'Margon II',	'310',	'Casa',	'rg_frente_680010dcc3778.jpg',	'rg_verso_680010dcc3780.jpg',	'selfie_680010dcc3781.jpg',	'10000-30000',	'Rua Sebastião Alves da Costa'),
(73937336,	'Mfuehudwj hiwjswdwidjwidji jdiwjswihdfeufhiwj ijdiwjwihdiwkdoq jiwjdwidjwifjei jwdodkwofjiehiehgiejdiw jifjeifjeifwkfijrghis kwoskowfiejifefefefe uranopay.com',	'nomin.momin+445r0@mail.ru',	NULL,	0,	NULL,	'$2y$10$92UDrQZ4r.mihX1OnuhAe.jjUn/iWv6UCiaurSV01421B.9jpsye.',	'',	'1900-01-01',	'86913457331',	0.00,	0,	1,	NULL,	'0',	'2025-07-23 20:54:38',	NULL,	0,	0,	0.00,	0.00,	5.00,	2.00,	'64eb2122-6820-11f0-b3d1-41f1f4f1dc79',	0,	'b3c6eeb0d8b58d50d44739a7',	5.00,	0.00,	0.00,	'Mfuehudwj hiwjswdwidjwidji jdiwjswihdfeufhiwj ijdiwjwihdiwkdoq jiwjdwidjwifjei jwdodkwofjiehiehgiejdiw jifjeifjeifwkfijrghis kwoskowfiejifefefefe uranopay.com',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL),
(74175399,	'CIPRIANO BAIRRAL',	'ciprianobairralcima@reborn.com',	NULL,	0,	NULL,	'$2y$10$pVsFik4x3cBOoy.4Qlg1Tue.ouypJBYd2KTLV1KhQH0TtXMxSw9EK',	'03872916000120',	'1900-01-01',	'22999765555',	0.00,	0,	1,	NULL,	'5',	'2025-07-23 13:33:14',	NULL,	0,	0,	0.00,	0.00,	5.00,	2.00,	'bac78101-67e2-11f0-b3d1-41f1f4f1dc79',	0,	'6628706e2b2170aeb253e045',	5.00,	0.00,	0.00,	'cipriano01',	'28495-000',	'RJ',	'APERIBE',	'CENTRO',	'153',	'frente',	'rg_frente_68810f10ebacb.jpg',	'rg_verso_68810f10ebae1.jpg',	'selfie_68810f10ebae3.jpg',	'30000-100000',	'CIDONIO BAIRRAL'),
(80317961,	'caua jorge cavalcante da silva',	'caua77652@gmail.com',	NULL,	0,	NULL,	'$2y$10$pLNkrJZzmw/2i51ySIA6XulTx8YSPergH2BzpexjoRCW1NmniIGAO',	'149638384727',	'1900-01-01',	'81992866683',	9.50,	1,	5,	NULL,	'1',	'2025-04-22 21:02:31',	NULL,	1,	0,	0.00,	0.00,	5.00,	2.00,	'405f591f-1fd6-11f0-b1bd-3cd7830cb258',	0,	'34930dca1d39fb7e4a9d7845',	5.00,	10.00,	0.00,	'caua77652@gmail.com',	'53370815',	'PE',	'olinda',	'ouro preto',	'148',	'casa',	'rg_frente_68082eb0a55e0.jpg',	'rg_verso_68082eb0a55ea.jpg',	'selfie_68082eb0a55eb.jpg',	'100000-400000',	'rua josue joaquim da silva '),
(91393696,	'pai88273017',	'cauacavalcante38@gmail.com',	NULL,	0,	NULL,	'$2y$10$nU0dC8ApELIxEq6gS0c3dODqNCqQ39H1LmtyneO9g.PSAOQCTiojK',	'03950648488',	'1900-01-01',	'81992866683',	19.00,	2,	1,	NULL,	'1',	'2025-04-16 13:48:14',	NULL,	2,	0,	0.00,	0.00,	5.00,	2.00,	'972395f7-1ae2-11f0-b1bd-3cd7830cb258',	0,	'add4056ace99cb965588a0e8',	5.00,	20.00,	0.00,	'caua88',	'53370815',	'pernambuco',	'olinda',	'ouro preto',	'148',	'casa',	'rg_frente_67ffe0bd8fe04.jpg',	'rg_verso_67ffe0bd8fe0c.jpg',	'',	'',	'rua joaquim da silva'),
(94818453,	'Stive Sousa de Paiva',	'stivevendas@catgroup.uk',	NULL,	0,	NULL,	'$2y$10$KY2T4m5uNC3ZceQiES3aL.DgY3b3CMoD.B2dldLsaU.HeA3Bp87We',	'',	'1900-01-01',	'1231970990',	0.00,	0,	1,	NULL,	'1',	'2025-04-24 15:56:32',	NULL,	0,	0,	0.00,	0.00,	5.00,	1.00,	'd672413e-213d-11f0-b1bd-3cd7830cb258',	0,	'786085883ab04da16e279559',	2.00,	0.00,	0.00,	'stive02',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL);

DROP TABLE IF EXISTS `users_key`;
CREATE TABLE `users_key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(100) DEFAULT NULL,
  `api_key` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users_key` (`id`, `user_id`, `api_key`, `status`) VALUES
(18,	'severino64',	'81bb141a-1746-49a8-bb4a-c3b8aa0d2259',	'ativo'),
(19,	'loureirop81@gmail.com',	'3b38c2960bd4d03fb25bd647',	'ativo'),
(20,	'testandoo',	'ccffca48927d457646912bc7',	'ativo'),
(21,	'thigas',	'f4186ba6a0f0bb2f9d39ce9a',	'ativo'),
(22,	'caua88',	'add4056ace99cb965588a0e8',	'ativo'),
(23,	'Eraldo',	'4d7bee14c59dad96cb770ebc',	'ativo'),
(24,	'contatosx@icloud.com',	'886a27306cbfd33fb04900db',	'ativo'),
(25,	'caua77652@gmail.com',	'34930dca1d39fb7e4a9d7845',	'ativo'),
(26,	'luizbless7',	'823add6b1bb4af2e16177bf2',	'ativo'),
(27,	'stive02',	'786085883ab04da16e279559',	'ativo'),
(28,	'dsadasdsaddsa',	'f0cebc1614b0032e49cc17b7',	'ativo');

-- 2025-07-26 17:12:56
