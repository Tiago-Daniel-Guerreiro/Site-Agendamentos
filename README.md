# Site de Agendamentos em PHP

![Language](https://img.shields.io/badge/PHP-7%2B-blueviolet.svg)
![Database](https://img.shields.io/badge/Database-MySQL-orange.svg)
![Status](https://img.shields.io/badge/Status-Projeto%20Escolar-brightgreen)

Um projeto académico para criar um sistema de agendamentos funcional, desenvolvido com foco no **back-end em PHP** e na interação com uma **base de dados MySQL**. A aplicação permite que utilizadores submetam e visualizem agendamentos através de uma interface web simples.

## 🚀 Tecnologias Utilizadas
- **Back-end:** PHP
- **Base de Dados:** MySQL (gerida via phpMyAdmin)
- **Front-end:** HTML e CSS

A escolha destas tecnologias foi orientada por requisitos académicos e pelo objetivo de solidificar os conhecimentos fundamentais no desenvolvimento web do lado do servidor.

## 🎯 Objetivo Principal
O objetivo principal foi construir uma aplicação web "full-stack" básica, desde a interface até à base de dados. O projeto serviu como um exercício prático para aprender a:
- Processar dados de formulários HTML com PHP.
- Conectar e executar consultas (`queries`) numa base de dados MySQL.
- Estruturar uma aplicação PHP de forma modular.
- Compreender o ciclo de vida de uma requisição web num ambiente servidor-cliente.

## ❓ O Problema
A gestão manual de agendamentos (por telefone, papel ou email) é ineficiente e propensa a erros, como sobreposições de horários ou perda de informação. Este projeto aborda esse problema criando uma solução digital, centralizada e automatizada para a criação e consulta de agendamentos.

## ✔️ A Solução
Uma aplicação web simples, mas funcional, composta por três componentes principais:
1.  **Interface do Utilizador (Front-end):** Páginas dinâmicas onde o HTML é gerado diretamente pelos scripts PHP. Estas páginas incluem formulários para submeter dados e áreas para visualizar os agendamentos. O estilo é gerido com CSS básico.
2.  **Lógica de Negócio (Back-end):** Scripts PHP que recebem os dados dos formulários, validam a informação e comunicam com a base de dados para inserir, atualizar ou consultar agendamentos.
3.  **Persistência de Dados (Base de Dados):** Uma base de dados MySQL que armazena toda a informação de forma estruturada e persistente.

Uma versão de demonstração está disponível online em **[site-agendamentos.great-site.net](http://site-agendamentos.great-site.net)**.
**Nota:** A aplicação está totalmente funcional para o utilizador comum. No entanto, por razões de segurança, a área de administração não está publicamente acessível nesta demonstração.

## 👤 Meu Papel
Este projeto foi desenvolvido em colaboração, com uma forte divisão de especialidades. O meu papel focou-se principalmente no design da arquitetura e na implementação da lógica PHP:
- **Arquitetura da Aplicação:** Fui o principal responsável por desenhar a estrutura geral do projeto, definindo como os diferentes scripts PHP iriam interagir.
- **Desenvolvimento Back-end:** Implementei a maior parte da lógica de negócio em PHP, incluindo o processamento dos formulários e a criação dos objetos que representam os dados.
- **Modelo Inicial da Base de Dados:** Criei o modelo inicial da classe de interação com a base de dados, estabelecendo o "contrato" e a estrutura que seria posteriormente desenvolvida.

Embora tenha participado em várias fases, o meu colega teve um papel central na implementação final e na otimização da interação com a base de dados MySQL, uma área em que ele era mais experiente. Esta colaboração permitiu-nos entregar um projeto funcional e aprender um com o outro.

## ⚙️ Principais Desafios
- **Conexão Segura PHP-MySQL:** Aprender a gerir credenciais e estabelecer uma conexão estável e segura com a base de dados.
- **Depuração (Debugging):** O maior desafio foi identificar e corrigir bugs, tanto na lógica PHP como na interação com a base de dados.

## ✅ Resultados
- **Prova de Conceito Funcional:** O sistema é capaz de criar e listar agendamentos, cumprindo o seu objetivo principal.
- **Aprendizagem Prática:** O projeto proporcionou uma experiência valiosa e prática no desenvolvimento web do lado do servidor.
- **Base para Projetos Futuros:** A compreensão adquirida sobre PHP e MySQL serve como uma base sólida para projetos web mais complexos.

## 🔮 Próximos Passos
O projeto tem potencial para evoluir com as seguintes melhorias:
- **Melhorar a Interface (UI/UX):** Modernizar o design para uma experiência mais intuitiva e agradável.
- **Aumentar a Segurança:** Implementar medidas de segurança mais robustas, como a proteção contra injeção de SQL (`SQL Injection`) e ataques XSS (`Cross-Site Scripting`).
