-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+deb12u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 18-Jun-2025 às 09:46
-- Versão do servidor: 10.11.11-MariaDB-0+deb12u1
-- versão do PHP: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `schoolDB`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_agendamentos`
--

CREATE TABLE `tbl_agendamentos` (
  `Id` int(11) NOT NULL,
  `Utilizador_Id` int(11) NOT NULL,
  `Espaco_Id` int(11) NOT NULL,
  `Data` date NOT NULL,
  `Hora_Inicio` time NOT NULL,
  `Hora_Fim` time NOT NULL,
  `Motivo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_espacos`
--

CREATE TABLE `tbl_espacos` (
  `Id` int(11) NOT NULL,
  `Nome` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_tokens`
--

CREATE TABLE `tbl_tokens` (
  `Id` int(11) NOT NULL,
  `Utilizador_Id` int(11) NOT NULL,
  `Token` varchar(72) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_utilizadores`
--

CREATE TABLE `tbl_utilizadores` (
  `Id` int(11) NOT NULL,
  `Nome` varchar(30) NOT NULL,
  `Email` varchar(320) NOT NULL,
  `Senha` varchar(72) NOT NULL,
  `Admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `tbl_agendamentos`
--
ALTER TABLE `tbl_agendamentos`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Utilizador_Id` (`Utilizador_Id`),
  ADD KEY `Espaco_Id` (`Espaco_Id`),
  ADD KEY `Utilizador_Id_2` (`Utilizador_Id`);

--
-- Índices para tabela `tbl_espacos`
--
ALTER TABLE `tbl_espacos`
  ADD PRIMARY KEY (`Id`);

--
-- Índices para tabela `tbl_tokens`
--
ALTER TABLE `tbl_tokens`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Token` (`Token`),
  ADD UNIQUE KEY `Id_Usuario` (`Utilizador_Id`),
  ADD UNIQUE KEY `Utilizador_Id` (`Utilizador_Id`);

--
-- Índices para tabela `tbl_utilizadores`
--
ALTER TABLE `tbl_utilizadores`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Nome` (`Nome`,`Email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tbl_agendamentos`
--
ALTER TABLE `tbl_agendamentos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_espacos`
--
ALTER TABLE `tbl_espacos`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_tokens`
--
ALTER TABLE `tbl_tokens`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tbl_utilizadores`
--
ALTER TABLE `tbl_utilizadores`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `tbl_agendamentos`
--
ALTER TABLE `tbl_agendamentos`
  ADD CONSTRAINT `tbl_agendamentos_ibfk_1` FOREIGN KEY (`Utilizador_Id`) REFERENCES `tbl_utilizadores` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_agendamentos_ibfk_2` FOREIGN KEY (`Espaco_Id`) REFERENCES `tbl_espacos` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
