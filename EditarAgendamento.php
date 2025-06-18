<!DOCTYPE html>
<?php
require_once 'PHP.php';
include 'header.php';

$msg = '';

if (Sessao::VerificaSessao() !== true)
{
    if (isset($_COOKIE['login']) === true && isset($_COOKIE['senha']) === true)
    {
        $email = $_COOKIE['login'];
        $senha = $_COOKIE['senha'];

        global $BaseDeDados;
        $utilizador = $BaseDeDados->ObterUtilizadorComEmail($email, $senha);

        if ($utilizador !== null)
            Sessao::IniciarSessao($utilizador);
    }
}

if (Sessao::VerificaSessao() !== true)
{
    header('Location: login');
    exit;
}

if ($msg === '')
{
    $utilizador = Sessao::ObterUtilizador();
    global $BaseDeDados;
    $espacos = $BaseDeDados->ObterEspacos();

    if (array_key_exists('id', $_GET) === false)
        $msg = 'ID do agendamento não informado.';

    if ($msg === '')
    {
        $id = intval($_GET['id']);

        function buscarAgendamentoPorId($agendamentos, $id)
        {
            foreach ($agendamentos as $ag)
            {
                $idAg = null;

                if (is_array($ag) === true && array_key_exists('Id', $ag) === true)
                    $idAg = $ag['Id'];

                if (is_object($ag) === true)
                    $idAg = BaseDeDados_Aceder_Com_Classes::ObterIdDeAgendamento($ag);

                if ($idAg !== null && $idAg == $id)
                    return $ag;
            }

            return null;
        }

        $agendamento = buscarAgendamentoPorId($utilizador->VisualizarAgendamentos(), $id);
        $isAdmin = $utilizador->VerificarAdmin();

        if ($agendamento === null && $isAdmin === true)
        {
            $admin = new Admin();
            if ($admin->DefinirInformacoes($utilizador) === true)
                $agendamento = buscarAgendamentoPorId($admin->VisualizarAgendamentos(), $id);
        }

        if ($agendamento === null)
        {
            $msg = 'Agendamento não encontrado ou você não tem permissão para editá-lo.';
        }

        if ($msg === '')
        {
            $agendamentoUid = null;

            if (is_array($agendamento) === true && array_key_exists('Utilizador_Id', $agendamento) === true)
                $agendamentoUid = $agendamento['Utilizador_Id'];

            if (is_object($agendamento) === true && property_exists($agendamento, 'Utilizador_Id'))
                $agendamentoUid = $agendamento->Utilizador_Id;

            if ($agendamentoUid === null)
                $msg = 'Erro ao identificar o dono do agendamento.';

            if ($agendamentoUid !== null && $agendamentoUid != $utilizador->Id && $isAdmin !== true)
                $msg = 'Você não tem permissão para editar este agendamento.';
        }
    }
}

if ($msg === '' && array_key_exists('REQUEST_METHOD', $_SERVER) === true && $_SERVER['REQUEST_METHOD'] === 'POST')
{
    $motivo = '';
    if (array_key_exists('motivo', $_POST) === true)
        $motivo = $_POST['motivo'];

    $espaco = '';
    if (array_key_exists('espaco', $_POST) === true)
        $espaco = $_POST['espaco'];

    $data = '';
    if (array_key_exists('data', $_POST) === true)
        $data = $_POST['data'];

    $hora_inicio = '';
    if (array_key_exists('hora_inicio', $_POST) === true)
        $hora_inicio = $_POST['hora_inicio'];

    $hora_fim = '';
    if (array_key_exists('hora_fim', $_POST) === true)
        $hora_fim = $_POST['hora_fim'];

    if (BaseDeDados::EditarAgendamento($id, $utilizador->Id, $motivo, $espaco, $data, $hora_inicio, $hora_fim) === true)
    {
        header('Location: agendamentos?edit=success');
        exit;
    }

    $msg = 'Erro ao editar agendamento.';
}

if ($msg === '' && is_object($agendamento) === true)
{
    $agendamento = array();

    if (property_exists($agendamento, 'Motivo') === true && $agendamento->Motivo !== null)
        $agendamento['Motivo'] = $agendamento->Motivo;
    else
        $agendamento['Motivo'] = '';

    if (property_exists($agendamento, 'Espaco_Id') === true && $agendamento->Espaco_Id !== null)
        $agendamento['Espaco_Id'] = $agendamento->Espaco_Id;
    else
        $agendamento['Espaco_Id'] = '';

    if (property_exists($agendamento, 'Data') === true && $agendamento->Data !== null)
        $agendamento['Data'] = $agendamento->Data;
    else
        $agendamento['Data'] = '';

    if (property_exists($agendamento, 'Hora_Inicio') === true && $agendamento->Hora_Inicio !== null)
        $agendamento['Hora_Inicio'] = $agendamento->Hora_Inicio;
    else
        $agendamento['Hora_Inicio'] = '';

    if (property_exists($agendamento, 'Hora_Fim') === true && $agendamento->Hora_Fim !== null)
        $agendamento['Hora_Fim'] = $agendamento->Hora_Fim;
    else
        $agendamento['Hora_Fim'] = '';

    if (property_exists($agendamento, 'Utilizador_Id') === true && $agendamento->Utilizador_Id !== null)
        $agendamento['Utilizador_Id'] = $agendamento->Utilizador_Id;
    else
        $agendamento['Utilizador_Id'] = '';

    $agendamento['Id'] = BaseDeDados_Aceder_Com_Classes::ObterIdDeAgendamento($agendamento);
}

if (
    $msg === '' &&
    is_array($agendamento) === true &&
    array_key_exists('agendamento', $agendamento) === true &&
    is_object($agendamento['agendamento']) === true
)
{
    $obj = $agendamento['agendamento'];

    if (property_exists($obj, 'Motivo') === true && $obj->Motivo !== null)
        $agendamento['Motivo'] = $obj->Motivo;
    else
        $agendamento['Motivo'] = '';

    if (property_exists($obj, 'Espaco_Id') === true && $obj->Espaco_Id !== null)
        $agendamento['Espaco_Id'] = $obj->Espaco_Id;
    else
        $agendamento['Espaco_Id'] = '';

    if (property_exists($obj, 'Data') === true && $obj->Data !== null)
        $agendamento['Data'] = $obj->Data;
    else
        $agendamento['Data'] = '';

    if (property_exists($obj, 'Hora_Inicio') === true && $obj->Hora_Inicio !== null)
        $agendamento['Hora_Inicio'] = $obj->Hora_Inicio;
    else
        $agendamento['Hora_Inicio'] = '';

    if (property_exists($obj, 'Hora_Fim') === true && $obj->Hora_Fim !== null)
        $agendamento['Hora_Fim'] = $obj->Hora_Fim;
    else
        $agendamento['Hora_Fim'] = '';

    unset($agendamento['agendamento']);
}

if ($msg === '')
{
    foreach (array('Motivo', 'Espaco_Id', 'Data', 'Hora_Inicio', 'Hora_Fim', 'Utilizador_Id', 'Id') as $campo)
    {
        if (array_key_exists($campo, $agendamento) === false || $agendamento[$campo] === null)
            $agendamento[$campo] = '';
    }

    if (array_key_exists('REQUEST_METHOD', $_SERVER) === true && $_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (array_key_exists('motivo', $_POST) === true)
            $agendamento['Motivo'] = $_POST['motivo'];

        if (array_key_exists('espaco', $_POST) === true)
            $agendamento['Espaco_Id'] = $_POST['espaco'];

        if (array_key_exists('data', $_POST) === true)
            $agendamento['Data'] = $_POST['data'];

        if (array_key_exists('hora_inicio', $_POST) === true)
            $agendamento['Hora_Inicio'] = $_POST['hora_inicio'];

        if (array_key_exists('hora_fim', $_POST) === true)
            $agendamento['Hora_Fim'] = $_POST['hora_fim'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Agendamento</title>
</head>
<body>
    <h2>Editar Agendamento</h2>
    <?php
    if ($msg !== '')
        echo '<p class="mensagem-erro" style="color:red">' . htmlspecialchars($msg) . '</p>';

    if ($msg === '')
    {
    ?>
    <form method="post">
        <label>Espaço:<br>
            <select name="espaco" required>
                <?php
                foreach ($espacos as $espaco)
                {
                    $espacoId = '';

                    if (is_array($espaco) === true && array_key_exists('Id', $espaco) === true)
                        $espacoId = $espaco['Id'];

                    if (is_object($espaco) === true && property_exists($espaco, 'Id'))
                        $espacoId = $espaco->Id;

                    $espacoNome = '';

                    if (is_array($espaco) === true && array_key_exists('Nome', $espaco) === true)
                        $espacoNome = $espaco['Nome'];

                    if (is_object($espaco) === true && property_exists($espaco, 'Nome'))
                        $espacoNome = $espaco->Nome;

                    $selected = '';

                    if ($espacoId === $agendamento['Espaco_Id'])
                        $selected = 'selected';

                    echo '<option value="' . htmlspecialchars($espacoId) . '" ' . $selected . '>' . htmlspecialchars($espacoNome) . '</option>';
                }
                ?>
            </select>
        </label><br>
        <label>Data:<br><input type="date" name="data" value="<?php echo htmlspecialchars($agendamento['Data']); ?>" required></label><br>
        <label>Hora Início:<br><input type="time" name="hora_inicio" value="<?php echo htmlspecialchars($agendamento['Hora_Inicio']); ?>" required></label><br>
        <label>Hora Fim:<br><input type="time" name="hora_fim" value="<?php echo htmlspecialchars($agendamento['Hora_Fim']); ?>" required></label><br>
        <label>Motivo:<br><input type="text" name="motivo" value="<?php echo htmlspecialchars($agendamento['Motivo']); ?>"></label><br>
        <button type="submit">Salvar</button>
        <a href="agendamentos">Cancelar</a>
    </form>
    <?php
    }
    ?>
</body>
</html>
