<!DOCTYPE html>
<?php
require_once 'PHP.php';
include 'header.php';

if (!isset($_SESSION['utilizador']))
{
    header('Location: login');
    exit;
}

$user = $_SESSION['utilizador'];
$admin = new Admin();

if ($admin->DefinirInformacoes($user) === false)
{
    header('Location: home');
    exit;
}

$utilizadores = array();
$agendamentos = array();
$espacosArr = array();
$espacosNomes = array();

if (isset($BaseDeDados))
{
    $utilizadores = $BaseDeDados->ObterTodosOsIdUtilizadores();
    $agendamentos = $admin->VisualizarAgendamentos();
    $espacosArr = $BaseDeDados->ObterEspacos();
}

foreach ($espacosArr as $esp)
{
    if (isset($esp['Id']) && isset($esp['Nome']))
        $espacosNomes[$esp['Id']] = $esp['Nome'];
}

$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_POST['cancelar_agendamento']))
    {
        $ids = array();
        if (isset($_POST['agendamento_id']))
        {
            if (is_array($_POST['agendamento_id']))
                $ids = $_POST['agendamento_id'];
            else
                $ids[] = $_POST['agendamento_id'];
        }
        if (count($ids) === 0)
        {
            $feedback = 'Nenhum agendamento selecionado.';
            header('Location: admin?feedback=' . urlencode($feedback));
            exit;
        }
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
        {
            $feedback = '<span class="success">' . $sucesso . ' agendamento(s) cancelado(s) com sucesso.</span>';
            header('Location: admin?feedback=' . urlencode($feedback));
            exit;
        }

        $feedback = 'Erro ao cancelar agendamento(s).';
        header('Location: admin?feedback=' . urlencode($feedback));
        exit;
    }
}
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Administração</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h2>Painel de Administração</h2>
    <?php 
    if (isset($_GET['feedback']))
        $feedback = $_GET['feedback'];
    if ($feedback !== '')
        echo '<p>' . $feedback . '</p>';
    ?>
    <h3>Agendamentos</h3>
    <form method="post" id="form-cancelar-agendamentos">
    <table>
        <thead>
            <tr>
                <th><input type="checkbox" id="checkAll" onclick="marcarTodos(this)"></th>
                <th>ID</th>
                <th>Espaço</th>
                <th>Usuário</th>
                <th>Data</th>
                <th>Hora Início</th>
                <th>Hora Fim</th>
                <th>Motivo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (is_array($agendamentos))
            {
                foreach ($agendamentos as $agendamento)
                {
                    echo '<tr>';
                    echo '<td><input type="checkbox" name="agendamento_id[]" value="' . htmlspecialchars($agendamento['Id']) . '"></td>';
                    echo '<td>' . htmlspecialchars($agendamento['Id']) . '</td>';

                    if (isset($espacosNomes[$agendamento['Espaco_Id']]))
                        echo '<td>' . htmlspecialchars($espacosNomes[$agendamento['Espaco_Id']]) . '</td>';
                    else
                        echo '<td>' . htmlspecialchars($agendamento['Espaco_Id']) . '</td>';
                    
                    echo '<td>' . htmlspecialchars(BaseDeDados::ObterUsernamePorId($agendamento['Utilizador_Id'])) . '</td>';
                    echo '<td>' . htmlspecialchars($agendamento['Data']) . '</td>';
                    echo '<td>' . htmlspecialchars($agendamento['Hora_Inicio']) . '</td>';
                    echo '<td>' . htmlspecialchars($agendamento['Hora_Fim']) . '</td>';

                    if (isset($agendamento['Motivo']) && $agendamento['Motivo'] !== '')
                        echo '<td>' . htmlspecialchars($agendamento['Motivo']) . '</td>';
                    else
                        echo '<td>-</td>';

                    echo '<td>';
                    echo '<a class="btn-edit" href="EditarAgendamento?id=' . urlencode($agendamento['Id']) . '">Editar</a>';
                    echo '<form method="post" style="display:inline" onsubmit="return confirm(\'Tem certeza que deseja cancelar este agendamento?\');">';
                    echo '<input type="hidden" name="agendamento_id[]" value="' . htmlspecialchars($agendamento['Id']) . '">';
                    echo '<button type="submit" name="cancelar_agendamento" class="btn-cancel" style="margin:3;">Cancelar</button>';
                    echo '</form>';
                    echo '</td>';
                    echo '</tr>';
                }
            }
            ?>
        </tbody>
    </table>
    <button type="submit" name="cancelar_agendamento" class="btn-cancel" onclick="return confirm('Tem certeza que deseja cancelar os agendamentos selecionados?');">Cancelar Selecionados</button>
    </form>
    <script>
    function marcarTodos(box)
    {
        var checkboxes = document.querySelectorAll('input[name="agendamento_id[]"]');
        var i = 0;

        for (i = 0; i < checkboxes.length; i++)
        {
            checkboxes[i].checked = box.checked;
        }
    }
    </script>
    <h3>Utilizadores</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Admin</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (is_array($utilizadores))
            {
                foreach ($utilizadores as $id)
                {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($id) . '</td>';
                    echo '<td>' . htmlspecialchars(BaseDeDados::ObterUsernamePorId($id)) . '</td>';
                    echo '<td>' . htmlspecialchars($BaseDeDados->ObterEmailPorUsername(BaseDeDados::ObterUsernamePorId($id))) . '</td>';
                    
                    if (BaseDeDados::ObterEstadoAdminPorId($id) === true)
                        echo '<td>Sim</td>';
                    else
                        echo '<td>Não</td>';

                    echo '<td>';
                    echo '<a class="btn-edit" href="EditarUtilizador?id=' . urlencode($id) . '">Editar</a>';
                    echo '</td>';
                    echo '</tr>';
                }
            }
            ?>
        </tbody>
    </table>
</body>
</html>
