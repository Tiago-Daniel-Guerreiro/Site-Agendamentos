<?php
require_once 'PHP.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Agendamentos - Sistema de Agendamentos</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <?php include 'header.php';

if (Sessao::VerificaSessao() !== true)
{

    if (isset($_COOKIE['login']) === true && isset($_COOKIE['senha']) === true)
    {

        $email = $_COOKIE['login'];
        $senha = $_COOKIE['senha'];

        global $BaseDeDados;

        if (isset($BaseDeDados) === true)
        {
            $utilizador = Sessao::ObterUtilizador();

            if ($utilizador !== null)
                Sessao::IniciarSessao($utilizador);
        }

    }

}

if (Sessao::VerificaSessao() !== true)
{
    header('Location: login');
    exit;
}

$user = Sessao::ObterUtilizador();

if (isset($user) !== true)
{
    header('Location: login');
    exit;
}

$agendamentos = $user->VisualizarAgendamentos();

if (is_array($agendamentos) !== true)
    $agendamentos = array();

$espacosArr = array();
global $BaseDeDados;

if (isset($BaseDeDados) === true)
    $espacosArr = $BaseDeDados->ObterEspacos();

$espacosNomes = array();

foreach ($espacosArr as $esp)
{
    if (isset($esp['Id']) === true && isset($esp['Nome']) === true)
        $espacosNomes[$esp['Id']] = $esp['Nome'];

}

$resposta = '';

if (isset($_SERVER['REQUEST_METHOD']) === true && $_SERVER['REQUEST_METHOD'] === 'POST')
{

    if (isset($_POST['cancelar_agendamento']) === true)
    {
        $ids = array();

        if (isset($_POST['agendamento_id']) === true)
        {
            if (is_array($_POST['agendamento_id']) === true)
                $ids = $_POST['agendamento_id'];
            else
                $ids[] = $_POST['agendamento_id'];
        }

        if (count($ids) === 0)
            $resposta = 'Nenhum agendamento selecionado.';
        else
        {
            $sucesso = 0;
            $falha = 0;

            foreach ($ids as $idAgendamento)
            {
                $cancelado = $user->CancelarAgendamento($idAgendamento);

                if ($cancelado === true)
                    $sucesso++;
                else
                    $falha++;
            }

            if ($sucesso > 0 && $falha === 0)
                $resposta = '<span class="success">' . $sucesso . ' agendamento(s) cancelado(s) com sucesso.</span>';
            else if ($sucesso > 0 && $falha > 0)
                $resposta = '<span class="warning">Alguns agendamentos foram cancelados, outros não.</span>';
            else
                $resposta = 'Erro ao cancelar agendamento(s).';

        }
    }

    // Recarrega os agendamentos após cancelar
    $agendamentos = $user->VisualizarAgendamentos();

    if (is_array($agendamentos) !== true)
        $agendamentos = array();

}

?>

    <link rel="stylesheet" href="assets/style.css">
    <div class="container">
        <h2>Meus Agendamentos</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Espaço</th>
                        <th>Data</th>
                        <th>Hora Início</th>
                        <th>Hora Fim</th>
                        <th>Motivo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

        if (is_array($agendamentos) !== true || count($agendamentos) === 0)
            echo '<tr><td colspan="6">Nenhum agendamento encontrado.</td></tr>';

        else
        {
            foreach ($agendamentos as $ag)
            {

                if (is_array($ag) === true && isset($ag['agendamento']) === true)
                {

                    $agObj = $ag['agendamento'];
                    $idAg = null;

                    if (isset($ag['Id']) === true)
                        $idAg = $ag['Id'];
                    else
                        $idAg = BaseDeDados_Aceder_Com_Classes::ObterIdDeAgendamento($agObj);

                    $utilizadorId = null;
                    if (isset($ag['Utilizador_Id']) === true)
                        $utilizadorId = $ag['Utilizador_Id'];
                }
                else
                {
                    $agObj = $ag;
                    $idAg = BaseDeDados_Aceder_Com_Classes::ObterIdDeAgendamento($agObj);
                    $utilizadorId = null;

                    if (isset($agObj->Utilizador_Id) === true)
                        $utilizadorId = $agObj->Utilizador_Id;

                }

                $espacoNome = '';
                
                if (isset($agObj->Espaco_Id) === true && isset($espacosNomes[$agObj->Espaco_Id]) === true)
                    $espacoNome = $espacosNomes[$agObj->Espaco_Id];
                else if (isset($agObj->Espaco_Id) === true)
                    $espacoNome = $agObj->Espaco_Id;

                $data = '';
                if (isset($agObj->Data) === true)
                    $data = $agObj->Data;

                $horaInicio = '';
                if (isset($agObj->Hora_Inicio) === true)
                    $horaInicio = $agObj->Hora_Inicio;

                $horaFim = '';
                if (isset($agObj->Hora_Fim) === true)
                    $horaFim = $agObj->Hora_Fim;

                $motivo = '-';
                if (isset($agObj->Motivo) === true && $agObj->Motivo !== '')
                    $motivo = htmlspecialchars($agObj->Motivo);

                $idAgStr = '';
                if ($idAg !== null)
                    $idAgStr = (string)$idAg;

                echo '<tr>';
                echo '<td>' . htmlspecialchars($espacoNome) . '</td>';
                echo '<td>' . htmlspecialchars($data) . '</td>';
                echo '<td>' . htmlspecialchars($horaInicio) . '</td>';
                echo '<td>' . htmlspecialchars($horaFim) . '</td>';
                echo '<td>' . $motivo . '</td>';
                echo '<td style="display: flex; align-items: center;">';

                if ($idAgStr !== '')
                    echo '<a class="btn-edit" href="EditarAgendamento?id=' . urlencode($idAgStr) . '">Editar</a>';
                else
                    echo '<span style="color:gray">Editar</span>';

                echo '<form method="post" style="box-shadow: none; padding: none;" onsubmit="return confirm(\'Tem certeza que deseja cancelar este agendamento?\');">';
                echo '<input type="hidden" name="agendamento_id[]" value="' . htmlspecialchars($idAgStr) . '">';
                echo '<button type="submit" name="cancelar_agendamento" class="btn-cancel">Cancelar</button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
        }
        ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php 
if ($resposta !== '' )
    echo '<div class="container feedback-container">' . $resposta . '</div>';
?>
</body>

</html>