<!DOCTYPE html>
<?php
require_once 'PHP.php';
include 'header.php';

if (!Sessao::VerificaSessao())
{
    header('Location: login.php');
    exit;
}

$user = Sessao::ObterUtilizador();
global $BaseDeDados;
$espacos = $BaseDeDados->ObterEspacos();

$espacoSelecionado = null;
if (isset($_POST['espaco']))
    $espacoSelecionado = (int)$_POST['espaco'];
else
{
    if (isset($espacos[0]['Id']))
        $espacoSelecionado = $espacos[0]['Id'];
}

$dataBase = date('Y-m-d');
if (isset($_POST['data']))
    $dataBase = $_POST['data'];

$diaSemana = date('N', strtotime($dataBase));
$inicioSemana = date('Y-m-d', strtotime($dataBase . ' -' . ($diaSemana-1) . ' days'));
$diasSemana = array();

for ($i = 0; $i < 7; $i++)
{
    $diasSemana[] = date('Y-m-d', strtotime($inicioSemana . "+$i days"));
}

$agendamentosSemana = array();

if ($espacoSelecionado !== null)
{
    $stmt = $BaseDeDados->getConexao()->prepare("SELECT * FROM tbl_agendamentos WHERE Espaco_Id = ? AND Data BETWEEN ? AND ?");
    $stmt->bind_param("iss", $espacoSelecionado, $diasSemana[0], $diasSemana[6]);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc())
    {
        $agendamentosSemana[$row['Data']][] = $row;
    }
}

function obterIntervalos($inicio, $fim)
{
    $intervalos = array();
    $h = strtotime($inicio);
    $f = strtotime($fim);

    while ($h < $f)
    {
        $intervalos[] = date('H:i', $h);
        $h += 3600;
    }

    return $intervalos;
}

$horaPadraoInicio = '08:00';
$horaPadraoFim = '22:00';
$horaMin = $horaPadraoInicio;
$horaMax = $horaPadraoFim;

foreach ($agendamentosSemana as $agendamentosDia)
{
    foreach ($agendamentosDia as $ag)
    {
        $inicio = substr($ag['Hora_Inicio'], 0, 5);
        $fim = substr($ag['Hora_Fim'], 0, 5);

        if ($inicio < $horaMin)
            $horaMin = '00:00';

        $fimMinutos = (int)substr($fim, 3, 2);
        $fimHora = (int)substr($fim, 0, 2);

        if ($fimMinutos > 0)
        {
            $fimHora++;
            $fim = sprintf('%02d:00', $fimHora);
        }
        
        if ($fim > $horaMax)
            $horaMax = $fim;
    }
}

$horas = array();
$h = strtotime($horaMin);
$hFim = strtotime($horaMax);

while ($h < $hFim)
{
    $horas[] = date('H:i', $h);
    $h += 3600;
}

$tabela = '<div class="table-responsive">
<table class="tabela-semana">
  <thead>
    <tr>
      <th>Hora</th>';

foreach ($diasSemana as $dia)
{
    $tabela .= '<th>' . date('D', strtotime($dia)) . '<br>' . date('d/m', strtotime($dia)) . '</th>';
}

$tabela .= '</tr>
  </thead>
  <tbody>';

foreach ($horas as $hIdx => $hora)
{
    $tabela .= '<tr><td>' . $hora . '</td>';
    foreach ($diasSemana as $dIdx => $dia)
    {
        $celula = '';
        $classe = 'livre';
        $extraStyle = '';
        $ocupacoes = array();

        if (isset($agendamentosSemana[$dia]))
        {
            foreach ($agendamentosSemana[$dia] as $ag)
            {
                $inicioTimestamp = strtotime($ag['Hora_Inicio']);
                $fimTimestamp = strtotime($ag['Hora_Fim']);
                $horaTimestamp = strtotime($hora);
                $proxHoraTimestamp = $horaTimestamp + 3600;

                if ($inicioTimestamp < $proxHoraTimestamp && $fimTimestamp > $horaTimestamp)
                {
                    $ocupacoes[] = array(
                        'inicio' => $inicioTimestamp < $horaTimestamp ? $horaTimestamp : $inicioTimestamp,
                        'fim' => $fimTimestamp > $proxHoraTimestamp ? $proxHoraTimestamp : $fimTimestamp
                    );
                }
            }
        }

        if (count($ocupacoes) > 0)
        {
            usort($ocupacoes, function($a, $b) 
            { 
                return $a['inicio'] - $b['inicio']; 
            });

            $blocos = array();

            foreach ($ocupacoes as $oc)
            {
                if (empty($blocos))
                    $blocos[] = $oc;
                else
                {
                    $last = &$blocos[count($blocos)-1];

                    if ($oc['inicio'] <= $last['fim'])
                    {
                        if ($oc['fim'] > $last['fim'])
                            $last['fim'] = $oc['fim'];
                    }
                    else
                        $blocos[] = $oc;
                }
            }
            $celulasBloco = array();

            foreach ($blocos as $b)
            {
                $celulasBloco[] = date('H:i', $b['inicio']) . ' / ' . date('H:i', $b['fim']);
            }

            $celula = implode('<br>', $celulasBloco);
            $classe = 'ocupado';
        }
        $tabela .= '<td class="' . $classe . '" style="' . $extraStyle . '">' . $celula . '</td>';
    }
    $tabela .= '</tr>';
}
$tabela .= '</tbody></table></div>';

// Montar as opções do select em uma string
$selectOptions = '';
foreach ($espacos as $esp)
{
    $selected = '';
    if ($espacoSelecionado == $esp['Id'])
    {
        $selected = ' selected';
    }
    $selectOptions .= '<option value="' . $esp['Id'] . '"' . $selected . '>' . htmlspecialchars($esp['Nome']) . '</option>';
}

?>
<link rel="stylesheet" href="assets/style.css">
<div class="container" id="container-home">
  <h2>Bem-vindo, <?php echo htmlspecialchars($user->Nome); ?>!</h2>
  <form method="post" class="form-semana">
    <label>Espaço:
      <select name="espaco" onchange="this.form.submit()">
        <?php echo $selectOptions; ?>
      </select>
    </label>
    <label>Semana de:
      <input type="date" name="data" value="<?php echo htmlspecialchars($dataBase); ?>">
    </label>
    <button type="submit">Filtrar</button>
  </form>
  <?php echo $tabela; ?>
</div>
