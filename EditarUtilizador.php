<?php
require_once 'PHP.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Utilizador - Sistema de Agendamentos</title>
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

$utilizador = Sessao::ObterUtilizador();
$feedback = '';
global $BaseDeDados;
$espacos = $BaseDeDados->ObterEspacos();

if (isset($_GET['id']) === false)
{
    header('Location: admin');
    exit;
}

$id = intval($_GET['id']);

$isAdmin = $BaseDeDados->VerificarAdmin($utilizador->Id);
if ($id !== $utilizador->Id && $isAdmin !== true)
{
    header('Location: admin');
    exit;
}

$stmt = $BaseDeDados->getConexao()->prepare('SELECT * FROM tbl_utilizadores WHERE Id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0)
{
    header('Location: admin');
    exit;
}

$user = $result->fetch_assoc();

$msg = '';
$adminAtual = 0;
if (isset($user['Admin']) === true)
    $adminAtual = (int)$user['Admin'];

if (array_key_exists('REQUEST_METHOD', $_SERVER) === true && $_SERVER['REQUEST_METHOD'] === 'POST')
{
    $nome = '';
    if (isset($_POST['nome']) === true)
        $nome = $_POST['nome'];

    $email = '';
    if (isset($_POST['email']) === true)
        $email = $_POST['email'];

    $novoAdmin = $adminAtual;
    if (isset($_POST['admin']) === true)
        $novoAdmin = (int)$_POST['admin'];

    if ($nome === '' || $email === '')
    {
        $msg = 'Por favor, preencha todos os campos obrigatórios.';
    }

    if ($msg === '')
    {
        $userObj = new Utilizador();
        $userObj->Id = $id;
        $userObj->Nome = $user['Nome'];
        $userObj->Email = $user['Email'];
        $userObj->Senha = isset($user['Senha']) === true ? $user['Senha'] : '';
        $atualizou = $userObj->EditarDados($nome, $email, $novoAdmin);

        if ($atualizou !== true)
        {
            $msg = 'Erro ao editar utilizador.';
            if ($nome === '' || $email === '')
                $msg .= ' [Campos obrigatórios em branco]';
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
                $msg .= ' [Email inválido]';
            if ($novoAdmin !== 0 && $novoAdmin !== 1)
                $msg .= ' [Admin inválido]';
        }

        if ($atualizou === true)
        {
            header('Location: admin');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Utilizador</title>
    <link rel="stylesheet" href="assets/style.css">
    <script>
    function confirmarAdminChange(form) {
        var adminAtual = <?= json_encode($adminAtual) ?>;
        var adminNovo = form.admin.value;
        if (adminAtual != adminNovo) {
            return confirm('Tem certeza que deseja alterar o estado de admin deste utilizador?');
        }
        return true;
    }
    </script>
</head>
<body>
    <div class="container">
    <h2>Editar Utilizador</h2>
    <?php
    if ($msg !== '')
        echo '<div class="feedback mensagem-erro">' . $msg . '</div>';
    ?>
    <form method="post" onsubmit="return confirmarAdminChange(this);">
        <label>Nome:<br><input type="text" name="nome" value="<?php echo htmlspecialchars($user['Nome']); ?>" required></label><br>
        <label>Email:<br><input type="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required></label><br>
        <?php if ($isAdmin === true)
        {
        ?>
        <label>Admin:</label>
        <div class="radio-group">
            <label class="radio-label">
                <input type="radio" name="admin" value="1" <?php if ($adminAtual === 1) echo 'checked'; ?>>
                <span>Sim</span>
            </label>
            <label class="radio-label">
                <input type="radio" name="admin" value="0" <?php if ($adminAtual === 0) echo 'checked'; ?>>
                <span>Não</span>
            </label>
        </div>
        <?php
        }
        ?>
        <div class="form-actions">
            <button type="submit" class="btn-salvar">Salvar</button>
            <a href="agendamentos" class="btn-cancelar-link">Cancelar</a>
        </div>
    </form>
    </div>
</body>
</html>
