SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `tbl_agendamentos` (
  `Id` int(11) NOT NULL,
  `Utilizador_Id` int(11) NOT NULL,
  `Espaco_Id` int(11) NOT NULL,
  `Data` date NOT NULL,
  `Hora_Inicio` time NOT NULL,
  `Hora_Fim` time NOT NULL,
  `Motivo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_agendamentos` (`Id`, `Utilizador_Id`, `Espaco_Id`, `Data`, `Hora_Inicio`, `Hora_Fim`, `Motivo`) VALUES
(1, 1, 1, '2025-06-13', '10:00:00', '11:00:00', 'Motivo Teste 0'),
(2, 2, 1, '2025-06-13', '11:00:00', '12:00:00', 'Motivo Teste 1'),
(3, 3, 1, '2025-06-13', '12:00:00', '13:00:00', 'Motivo Teste 2'),
(4, 4, 1, '2025-06-13', '13:00:00', '14:00:00', 'Motivo Teste 3'),
(5, 5, 1, '2025-06-13', '14:00:00', '15:00:00', 'Motivo Teste 4'),
(6, 6, 1, '2025-06-13', '15:00:00', '16:00:00', 'Motivo Teste 5'),
(7, 7, 1, '2025-06-13', '16:00:00', '17:00:00', 'Motivo Teste 6'),
(8, 8, 1, '2025-06-13', '17:00:00', '18:00:00', 'Motivo Teste 7'),
(9, 9, 1, '2025-06-13', '18:00:00', '19:00:00', 'Motivo Teste 8'),
(10, 10, 1, '2025-06-13', '19:00:00', '20:00:00', 'Motivo Teste 9'),
(11, 11, 1, '2025-06-13', '20:00:00', '21:00:00', 'Motivo Teste 10');

CREATE TABLE `tbl_espacos` (
  `Id` int(11) NOT NULL,
  `Nome` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_espacos` (`Id`, `Nome`) VALUES
(1, 'Espaço Teste');

CREATE TABLE `tbl_tokens` (
  `Id` int(11) NOT NULL,
  `Utilizador_Id` int(11) NOT NULL,
  `Token` varchar(72) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_utilizadores` (
  `Id` int(11) NOT NULL,
  `Nome` varchar(30) NOT NULL,
  `Email` varchar(320) NOT NULL,
  `Senha` varchar(72) NOT NULL,
  `Admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_utilizadores` (`Id`, `Nome`, `Email`, `Senha`, `Admin`) VALUES
(1, 'TesteUser10', 'testeuser10@exemplo.com', '$2y$10$EH.OExi7P2jx.Vn1k57Jq.Qy0DOue4majl6zV9Gw0KcaOR88/YOFK', 1),
(2, 'TesteUser11', 'testeuser11@exemplo.com', '$2y$10$Y5rdq3QVo6rGLjPBMIrzYOufIi70mMInenXNqa4CqWhFFj.GKiPV2', 0),
(3, 'TesteUser12', 'testeuser12@exemplo.com', '$2y$10$R6ATSt9wrfiP5uYeWpOkuOTl9RTy3A1Yif0NH5qIaElsfgb4gYnr.', 0),
(4, 'TesteUser13', 'testeuser13@exemplo.com', '$2y$10$czgRMs3gYN2Qhzg/HTS6JO8Hn/yprRQh61JygROjv4cfFMYtzIbpG', 0),
(5, 'TesteUser14', 'testeuser14@exemplo.com', '$2y$10$sqC6J7zRmV803WChklRU1eHtOZoemu30ZQS4Mf8A0D4ftt36A2uPa', 0),
(6, 'TesteUser15', 'testeuser15@exemplo.com', '$2y$10$ngt1e1PrH4EEA3iT35ssvOgRNJhZQibqCImhHTiXwtz9/KqWPQu3K', 0),
(7, 'TesteUser16', 'testeuser16@exemplo.com', '$2y$10$KqN6m7MnCl/xy/ysAposGOwnwnekxM24q9ZhMLId66CCw1ZE/HWRa', 0),
(8, 'TesteUser17', 'testeuser17@exemplo.com', '$2y$10$8ZKc7Nv7ueMKC0P2X29v9utHSvsRKSdudboDWUy68/6bg1k45BRGW', 0),
(9, 'TesteUser18', 'testeuser18@exemplo.com', '$2y$10$dFeAArmvoO3JSHifQIh3aueesQ3Gk13.PSy/Ss.iGgY1E7RH1gPze', 0),
(10, 'TesteUser19', 'testeuser19@exemplo.com', '$2y$10$VlZ0zFosbaMEdbAReqrwz.cm6MvlDhi72S4q3jwwNhjb0MCwbvSl2', 0),
(11, 'TesteUser20', 'testeuser20@exemplo.com', '$2y$10$Q.pxGuoGWOWX8wj4jipUVeOcjyeNsSgRO5t41GoO6Y3e17f0vSDEm', 0);

ALTER TABLE `tbl_agendamentos`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Utilizador_Id` (`Utilizador_Id`),
  ADD KEY `Espaco_Id` (`Espaco_Id`);

ALTER TABLE `tbl_espacos`
  ADD PRIMARY KEY (`Id`);

ALTER TABLE `tbl_tokens`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Token` (`Token`),
  ADD UNIQUE KEY `Id_Usuario` (`Utilizador_Id`),
  ADD UNIQUE KEY `Utilizador_Id` (`Utilizador_Id`);

ALTER TABLE `tbl_utilizadores`
  ADD PRIMARY KEY (`Id`);

ALTER TABLE `tbl_agendamentos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `tbl_espacos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `tbl_tokens`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tbl_utilizadores`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

COMMIT;