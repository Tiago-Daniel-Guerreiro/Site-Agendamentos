<?php
require_once 'PHP.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Agendamentos</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'header.php';

if (Sessao::VerificaSessao())
{
    header('Location: agendamentos');
    exit;
}

$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $email = '';
    if (isset($_POST['email']))
        $email = $_POST['email'];

    $senha = '';
    if (isset($_POST['senha']))
        $senha = $_POST['senha'];

    $manter = false;
    if (isset($_POST['manter']))
        $manter = true;

    $login = new Login();
    $login->DefinirInformacoes($email, $senha);
    if ($login->Logar())
    {
        session_start();

        $utilizador = $_SESSION['utilizador'];

        if ($utilizador !== null)
        {
            if ($manter)
                Sessao::DefinirCookiesLogin($email, $senha);

            header('Location: home');
            exit;
        }
    }
    $feedback = 'Email ou senha incorretos.';
}
?>
<div class="container" id="container-login">
  <h2>Login</h2>
  <form method="post">
    <input type="text" name="email" placeholder="Email ou usuário" required autofocus>
    <input type="password" name="senha" placeholder="Senha" required>
    <div class="manter-login-row">
      <input type="checkbox" name="manter" id="manter" class="manter-login-checkbox">
      <label for="manter" class="manter-login-label">
        Manter login por 7 dias
      </label>
    </div>
    <button type="submit">Entrar</button>
    <?php
    
    if ($feedback !== '')
        echo '<div class="feedback">'.$feedback.'</div>';

    ?>
    
  </form>
  <p>Não tem conta? <a href="registro">Registre-se</a></p>
</div>
</body>
</html>
