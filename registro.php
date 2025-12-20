<?php
require_once 'PHP.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema de Agendamentos</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <?php include 'header.php';

if (Sessao::VerificaSessao())
{
    header('Location: home');
    exit;
}

$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $nome = '';
    if (isset($_POST['nome']))
        $nome = $_POST['nome'];

    $email = '';
    if (isset($_POST['email']))
        $email = $_POST['email'];

    $senha = '';
    if (isset($_POST['senha']))
        $senha = $_POST['senha'];

    $confirmar = '';
    if (isset($_POST['confirmar']))
        $confirmar = $_POST['confirmar'];

    $cadastro = new Cadastro();
    if ($cadastro->DefinirInformacoes($nome, $email, $senha, $confirmar))
    {
        if ($cadastro->Cadastrar())
            $feedback = '<span class="success">Cadastro realizado! Faça login.</span>';
        else
            $feedback = 'Email já cadastrado.';
    }
    else
        $feedback = 'Preencha todos os campos corretamente.';
}
?>
    <div class="container" id="container-registro">
        <h2>Registro</h2>
        <form method="post">
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <input type="password" name="confirmar" placeholder="Confirmar senha" required>
            <button type="submit">Registrar</button>
            <?php

    if ($feedback !== '')
        echo '<div class="feedback">'.$feedback.'</div>';
    
    ?>
        </form>
        <p>Já tem conta? <a href="login">Entrar</a></p>
    </div>
</body>

</html>