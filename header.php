<?php
// PHP.php já foi incluído antes no arquivo principal

$user = null;

if (!Sessao::VerificaSessao())
{
    if (isset($_COOKIE['login']) && isset($_COOKIE['senha']))
    {
        $email = $_COOKIE['login'];
        $senha = $_COOKIE['senha'];

        global $BaseDeDados;
        $utilizador = $BaseDeDados->ObterUtilizadorComEmail($email, $senha);
        
        if ($utilizador !== null)
            Sessao::IniciarSessao($utilizador);
    }
}

if (Sessao::VerificaSessao())
    $user = Sessao::ObterUtilizador();

$isAdmin = false;
global $BaseDeDados;

if ($user !== null && isset($user->Id))
    $isAdmin = $BaseDeDados->VerificarAdmin($user->Id);

?>

<header class="header-nav">
  <nav>
    <?php
    if ($user !== null)
    {
        echo '<a href="index">Sistema de Agendamentos</a>';
        echo '<a href="agendamentos">Meus Agendamentos</a>';
        echo '<a href="marcar">Marcar Agendamento</a>';

        if ($isAdmin === true)
        {
            echo '<a href="admin">Painel Admin</a>';
            echo '<a href="espacos">Gerenciar Espaços</a>';
        }

        echo '<a href="logout">Terminar sessão</a>';
    }
    else
    {
        echo '<a href="login">Login</a>';
        echo '<a href="registro">Registrar</a>';
    }
    ?>
  </nav>
</header>
