<?php
require_once 'PHP.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Agendamento - Sistema de Agendamentos</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <?php
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

                // Se for array com chave 'agendamento' (novo formato)
                if (is_array($ag) === true && array_key_exists('agendamento', $ag) === true)
                {
                    if (array_key_exists('Id', $ag) === true)
                        $idAg = $ag['Id'];
                    
                    if ($idAg !== null && $idAg == $id)
                        return $ag; // Retorna o array inteiro com 'agendamento' e 'Id'
                }
                // Se for array simples (formato antigo)
                else if (is_array($ag) === true && array_key_exists('Id', $ag) === true)
                {
                    $idAg = $ag['Id'];
                    if ($idAg !== null && $idAg == $id)
                        return $ag;
                }
                // Se for objeto
                else if (is_object($ag) === true)
                {
                    $idAg = BaseDeDados_Aceder_Com_Classes::ObterIdDeAgendamento($ag);
                    if ($idAg !== null && $idAg == $id)
                        return $ag;
                }
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

            // Se é array com chave 'agendamento' (novo formato)
            if (is_array($agendamento) === true && array_key_exists('agendamento', $agendamento) === true)
            {
                if (array_key_exists('Utilizador_Id', $agendamento) === true)
                    $agendamentoUid = $agendamento['Utilizador_Id'];
            }
            // Se é array simples (formato antigo)
            else if (is_array($agendamento) === true && array_key_exists('Utilizador_Id', $agendamento) === true)
            {
                $agendamentoUid = $agendamento['Utilizador_Id'];
            }
            // Se é objeto
            else if (is_object($agendamento) === true && property_exists($agendamento, 'Utilizador_Id'))
            {
                $agendamentoUid = $agendamento->Utilizador_Id;
            }

            if ($agendamentoUid === null)
                $msg = 'Erro ao identificar o dono do agendamento.';

            if ($agendamentoUid !== null && $agendamentoUid != $utilizador->Id && $isAdmin !== true)
                $msg = 'Você não tem permissão para editar este agendamento.';
        }
    }
}

if ($msg === '' && array_key_exists('REQUEST_METHOD', $_SERVER) === true && $_SERVER['REQUEST_METHOD'] === 'POST')
{
    $id = intval($_GET['id']);
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

if ($msg === '' && (is_object($agendamento) === true || is_array($agendamento) === true))
{
    // Se $agendamento é um array com chave 'agendamento', extrair o objeto
    if (is_array($agendamento) === true && array_key_exists('agendamento', $agendamento) === true)
    {
        $temp = $agendamento['agendamento'];
        $agendamento = array();

        if (is_object($temp) === true)
        {
            if (property_exists($temp, 'Motivo') === true && $temp->Motivo !== null)
                $agendamento['Motivo'] = $temp->Motivo;
            else
                $agendamento['Motivo'] = '';

            if (property_exists($temp, 'Espaco_Id') === true && $temp->Espaco_Id !== null)
                $agendamento['Espaco_Id'] = $temp->Espaco_Id;
            else
                $agendamento['Espaco_Id'] = '';

            if (property_exists($temp, 'Data') === true && $temp->Data !== null)
                $agendamento['Data'] = $temp->Data;
            else
                $agendamento['Data'] = '';

            if (property_exists($temp, 'Hora_Inicio') === true && $temp->Hora_Inicio !== null)
                $agendamento['Hora_Inicio'] = $temp->Hora_Inicio;
            else
                $agendamento['Hora_Inicio'] = '';

            if (property_exists($temp, 'Hora_Fim') === true && $temp->Hora_Fim !== null)
                $agendamento['Hora_Fim'] = $temp->Hora_Fim;
            else
                $agendamento['Hora_Fim'] = '';

            if (property_exists($temp, 'Utilizador_Id') === true && $temp->Utilizador_Id !== null)
                $agendamento['Utilizador_Id'] = $temp->Utilizador_Id;
            else
                $agendamento['Utilizador_Id'] = '';

            $agendamento['Id'] = BaseDeDados_Aceder_Com_Classes::ObterIdDeAgendamento($temp);
        }
    }
    else if (is_object($agendamento) === true)
    {
        $temp = $agendamento;
        $agendamento = array();

        if (property_exists($temp, 'Motivo') === true && $temp->Motivo !== null)
            $agendamento['Motivo'] = $temp->Motivo;
        else
            $agendamento['Motivo'] = '';

        if (property_exists($temp, 'Espaco_Id') === true && $temp->Espaco_Id !== null)
            $agendamento['Espaco_Id'] = $temp->Espaco_Id;
        else
            $agendamento['Espaco_Id'] = '';

        if (property_exists($temp, 'Data') === true && $temp->Data !== null)
            $agendamento['Data'] = $temp->Data;
        else
            $agendamento['Data'] = '';

        if (property_exists($temp, 'Hora_Inicio') === true && $temp->Hora_Inicio !== null)
            $agendamento['Hora_Inicio'] = $temp->Hora_Inicio;
        else
            $agendamento['Hora_Inicio'] = '';

        if (property_exists($temp, 'Hora_Fim') === true && $temp->Hora_Fim !== null)
            $agendamento['Hora_Fim'] = $temp->Hora_Fim;
        else
            $agendamento['Hora_Fim'] = '';

        if (property_exists($temp, 'Utilizador_Id') === true && $temp->Utilizador_Id !== null)
            $agendamento['Utilizador_Id'] = $temp->Utilizador_Id;
        else
            $agendamento['Utilizador_Id'] = '';

        $agendamento['Id'] = BaseDeDados_Aceder_Com_Classes::ObterIdDeAgendamento($temp);
    }
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
    <div class="container" id="container-editar-agendamento">
        <h2>Editar Agendamento</h2>
        <?php
    if ($msg !== '')
        echo '<div class="mensagem-erro">' . htmlspecialchars($msg) . '</div>';

    if ($msg === '')
    {
    ?>
        <form method="post" class="form-agendamento">
            <label>Espaço:
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
            </label>

            <label>Data:
                <input type="date" name="data" value="<?php echo htmlspecialchars($agendamento['Data']); ?>" required>
            </label>

            <div class="flex-row">
                <label style="flex: 1;">Hora Início:
                    <input type="time" name="hora_inicio"
                        value="<?php echo htmlspecialchars($agendamento['Hora_Inicio']); ?>" required>
                </label>

                <span>até</span>

                <label style="flex: 1;">Hora Fim:
                    <input type="time" name="hora_fim" value="<?php echo htmlspecialchars($agendamento['Hora_Fim']); ?>"
                        required>
                </label>
            </div>

            <label>Motivo (opcional):
                <textarea name="motivo" rows="3"><?php echo htmlspecialchars($agendamento['Motivo']); ?></textarea>
            </label>

            <div class="flex-row" style="justify-content: flex-end; margin-top: 16px;">
                <a href="agendamentos" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-edit">Salvar Alterações</button>
            </div>
        </form>
        <?php
    }
    ?>
    </div>
    </div>
</body>

</html>