<!DOCTYPE html>
    <head>
        <link rel="stylesheet" href="assets/style.css">
    </head>
<body>
<?php
require_once 'PHP.php';
include 'header.php';

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

if (!Sessao::VerificaSessao())
{
    header('Location: login');
    exit;
}

$user = Sessao::ObterUtilizador();
$feedback = '';
global $BaseDeDados;
$espacos = $BaseDeDados->ObterEspacos();

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $espaco = '';
    if (isset($_POST['espaco']))
        $espaco = $_POST['espaco'];

    $data = '';
    if (isset($_POST['data']))
        $data = $_POST['data'];

    $hora_inicio = '';
    if (isset($_POST['hora_inicio']))
        $hora_inicio = $_POST['hora_inicio'];

    $hora_fim = '';
    if (isset($_POST['hora_fim']))
        $hora_fim = $_POST['hora_fim'];

    $motivo = '';
    if (isset($_POST['motivo']))
        $motivo = trim($_POST['motivo']);

    if ($motivo === null)
        $motivo = ' ';

    $agendamento = new Agendamento();
    if ($agendamento->DefinirInformacoes($espaco, $data, $hora_inicio, $hora_fim, $motivo))
    {
        if ($user->AdicionarAgendamento($agendamento))
            $feedback = '<span class="success">Agendamento realizado!</span>';
        else
            $feedback = 'Conflito ou erro ao agendar.';
    }
    else
        $feedback = 'Preencha todos os campos corretamente.';
}
?>

<div class="container" id="container-marcar">
  <h2>Marcar Agendamento</h2>
  <form method="post" class="form-agendamento">
    <select name="espaco" required>
      <option value="">Selecione o espaço</option>
      <?php

      foreach ($espacos as $esp)
      {
          echo '<option value="'.htmlspecialchars($esp['Id']).'">'.htmlspecialchars($esp['Nome']).'</option>';
      }

      ?>
    </select>
    <input type="date" name="data" required>
    <div class="flex-row">
      <input type="time" name="hora_inicio" required>
      <span>até</span>
      <input type="time" name="hora_fim" required>
    </div>
    <textarea name="motivo" placeholder="Motivo (opcional)" rows="2"></textarea>
    <button type="submit">Agendar</button>
    <?php

    if ($feedback !== '')
        echo '<div class="feedback">'.$feedback.'</div>';
    
    ?>
  </form>
</div>
</body>
