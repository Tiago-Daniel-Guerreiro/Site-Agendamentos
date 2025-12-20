<?php
require_once 'PHP.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Espaços - Sistema de Agendamentos</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <?php include 'header.php';

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
$admin = new Admin();

if (!$admin->DefinirInformacoes($user))
{
    header('Location: home.');
    exit;
}

$feedback = '';
global $BaseDeDados;

if (isset($_POST['editar_id']) && isset($_POST['novo_nome']))
{
    $id = (int)$_POST['editar_id'];
    $novo_nome = trim($_POST['novo_nome']);
  
    if ($novo_nome !== '')
    {
        $resultado = $BaseDeDados->EditarEspaco($id, $novo_nome);
        if ($resultado)
        {
            $feedback = '<span class="success">Nome alterado!</span>';
            header('Location: espacos?feedback=' . urlencode($feedback));
            exit;
        }
        else
            $feedback = 'Erro: nome já existe ou inválido.';
    }
}

if (isset($_POST['remover_id']))
{
    $id = (int)$_POST['remover_id'];
    $apagarAgendamentos = false;

    if (isset($_POST['apagar_agendamentos']) && $_POST['apagar_agendamentos'] === '1')
        $apagarAgendamentos = true;

    if ($BaseDeDados->EspacoTemAgendamentos($id) === true && $apagarAgendamentos === false)
    {
        $feedback = 'Este espaço possui agendamentos. Deseja apagar todos os agendamentos vinculados? <form method="post" class="inline-form"><input type="hidden" name="remover_id" value="'.$id.'"><input type="hidden" name="apagar_agendamentos" value="1"><button type="submit">Sim, apagar tudo</button></form> <a href="espacos">Cancelar</a>';
        header('Location: espacos?feedback=' . urlencode($feedback));
        exit;
    }

    if ($BaseDeDados->RemoverEspaco($id, $apagarAgendamentos))
        $feedback = '<span class="success">Espaço removido!</span>';
    else
        $feedback = 'Erro ao remover espaço.';
    
    header('Location: espacos?feedback=' . urlencode($feedback));
    exit;
}

if (isset($_POST['nome']) && !isset($_POST['editar_id']))
{
    $nome = trim($_POST['nome']);
    if ($nome !== '')
    {
        if ($BaseDeDados->AdicionarEspacos($nome))
        {
            $feedback = '<span class="success">Espaço adicionado!</span>';
            header('Location: espacos?feedback=' . urlencode($feedback));
            exit;
        }
        else
        {
            $feedback = 'Erro ao adicionar espaço.';
            header('Location: espacos?feedback=' . urlencode($feedback));
            exit;
        }
    }
}

if (isset($_POST['remover_multiplos']) && isset($_POST['remover_ids']) && is_array($_POST['remover_ids']))
{
    $ids = array_map('intval', $_POST['remover_ids']);
    $espacosComAgendamentos = array();
    foreach ($ids as $id)
    {
        if ($BaseDeDados->EspacoTemAgendamentos($id))
            $espacosComAgendamentos[] = $id;
    }
    if (count($espacosComAgendamentos) > 0 && !isset($_POST['apagar_agendamentos_multiplos']))
    {
        $idsInputs = '';

        foreach ($ids as $id)
        {
            $idsInputs .= '<input type="hidden" name="remover_ids[]" value="'.$id.'">';
        }

        $feedback = '<div class="confirm-modal"><div class="confirm-modal-content">'
            .'<h3>Confirmação necessária</h3>'
            .'<p>Alguns espaços selecionados possuem agendamentos.<br>Deseja apagar todos os agendamentos vinculados a eles?</p>'
            .'<div class="modal-actions">'
            .'<form method="post" class="inline-form">'
            .$idsInputs
            .'<input type="hidden" name="remover_multiplos" value="1">'
            .'<input type="hidden" name="apagar_agendamentos_multiplos" value="1">'
            .'<button type="submit" class="btn btn-danger">Sim, apagar tudo</button>'
            .'</form>'
            .'<form method="get" class="inline-form">'
            .'<button type="submit" class="btn btn-secondary">Cancelar</button>'
            .'</form>'
            .'</div>'
            .'</div></div>';
        return;
    }
    $ok = true;

    foreach ($ids as $id)
    {
        $apagarAg = false;

        if (count($espacosComAgendamentos) > 0 && isset($_POST['apagar_agendamentos_multiplos']))
            $apagarAg = true;

        if (!$BaseDeDados->RemoverEspaco($id, $apagarAg))
            $ok = false;
    }

    if ($ok === true)
        $feedback = '<span class="success">Espaços removidos!</span>';
    else
        $feedback = 'Erro ao remover um ou mais espaços.';
    
    if ($feedback !== '')
    {
        header('Location: espacos?feedback=' . urlencode($feedback));
        exit;
    }
}

$espacos = $BaseDeDados->ObterEspacos();
$editando = null;

if (isset($_GET['edit']))
    $editando = (int)$_GET['edit'];

if (isset($_GET['feedback']))
    $feedback = $_GET['feedback'];

?>
    <link rel="stylesheet" href="assets/style.css">
    <div class="container" id="container-espacos">
        <h2>Gerenciar Espaços</h2>
        <div class="card-section">
            <h3 class="card-title">Novo Espaço</h3>
            <form method="post" class="form-add-espaco">
                <div class="form-add-espaco-content">
                    <input type="text" name="nome" placeholder="Nome do espaço" required>
                    <button type="submit">Adicionar</button>
                </div>
            </form>
        </div>

        <div class="card-section">
            <h3 class="card-title">Espaços Existentes</h3>
            <form method="post" id="form-remover-multiplos">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Selecionar</th>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
        foreach ($espacos as $esp)
        {
            echo '<tr>';
            echo '<td><input type="checkbox" name="remover_ids[]" value="'.htmlspecialchars($esp['Id']).'"></td>';
            echo '<td>'.htmlspecialchars($esp['Id']).'</td>';
            
            if ($editando != (int)$esp['Id'])
            {
                echo '<td>' . htmlspecialchars($esp['Nome']) . '</td>';
                echo '<td><a href="espacos?edit='.htmlspecialchars($esp['Id']).'" class="btn-edit">Editar</a></td>';
            }
            else
            {
                echo '<td>
                    <form method="post" class="form-input-edit">
                        <input type="hidden" name="editar_id" value="' . htmlspecialchars($esp['Id']) . '">
                        <input type="text" name="novo_nome" value="' . htmlspecialchars($esp['Nome']) . '" required>
                    </form>
                </td>
                <td>
                    <form method="post" class="form-edit-espaco-guardar">
                        <input type="hidden" name="editar_id" value="' . htmlspecialchars($esp['Id']) . '">
                        <input type="hidden" name="novo_nome" value="' . htmlspecialchars($esp['Nome']) . '">
                        <button type="submit" class="btn-guardar">Guardar</button>
                    </form>
                    <a href="espacos" class="btn-cancelar-link-inline">Cancelar</a>
                </td>';
            }
            
            echo '</tr>';
        }

        if (count($espacos) === 0)
            echo '<tr><td colspan="4">Nenhum espaço cadastrado.</td></tr>';
        
        ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="remover_multiplos" class="btn-remover-multiplos">Remover espaços
                    selecionados</button>
            </form>
        </div>
    </div>
    <?php
    if ($feedback !== '')
    {
        if (strpos($feedback, 'confirm-modal') !== false)
        {
            echo $feedback;
        }
        else
        {
            $feedbackClass = 'feedback';
            if (strpos($feedback, 'removidos') !== false || strpos($feedback, 'sucesso') !== false || strpos($feedback, 'alterado') !== false || strpos($feedback, 'adicionado') !== false)
                $feedbackClass = 'feedback success';
            echo '<div class="container '.$feedbackClass.'">'.$feedback.'</div>';
        }
    }
    ?>
</body>

</html>