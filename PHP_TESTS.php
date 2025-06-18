<?php
require "PHP.php";

// Executa os testes
global $BaseDeDados;
$BaseDeDados->Desconectar();
$BaseDeDados->Conectar(true);

class TestesUnitarios {
    private $resultados = [];
    private $utilizadores = [];
    private $admin = null;
    private $agendamentos = [];
    private $espacoId = null;

    public function rodar() {
        global $BaseDeDados;

        try {
            $BaseDeDados->LimparBaseDeDados();
            $this->testarCadastroUtilizadores();
            $this->testarLoginUtilizadores();
            $this->testarMetodosUtilizador();
            $this->testarAdmin();
            echo "<p>" . implode("<br>", $this->resultados) . "<p>";
        } catch (Exception $e) {
            echo "<pre>Erro fatal nos testes: ".$e->getMessage()."";
        } finally {
            $BaseDeDados->Desconectar();
        }
    }

    private function testarCadastroUtilizadores() {
        $this->resultados[] = "<br>Testando cadastro de usuários...";
        // Garante que existe pelo menos um espaço
        global $BaseDeDados;
        $espacos = $BaseDeDados->ObterEspacos();
        if (empty($espacos)) {
            $BaseDeDados->AdicionarEspacos("Espaço Teste");
            $espacos = $BaseDeDados->ObterEspacos();
        }
        if (!empty($espacos)) {
            $this->espacoId = $espacos[0]['Id'];
        } else {
            $this->resultados[] = "Falha ao criar espaço para agendamento.";
            return;
        }
        for ($i = 10; $i <= 20; $i++) {
            $cadastro = new Cadastro();
            $nome = "TesteUser$i";
            $email = "testeuser$i@exemplo.com";
            $senha = "senha$i";
            $ok = $cadastro->DefinirInformacoes($nome, $email, $senha, $senha);
            if (!$ok) {
                $this->resultados[] = "Falha ao definir informações do usuário $i";
                continue;
            }
            
            if($cadastro->Cadastrar() == true)
            {
              $this->resultados[] = "Usuário Cadastrad com sucesso: $nome ($email)";
            } else {
                $this->resultados[] = "Falha ao cadastrar usuário $i: Email já existe ou dados inválidos.";
              continue;
            }
 
            // Cria objeto Utilizador para testes seguintes
            $utilizador = new Utilizador();
            if ($utilizador->DefinirInformacoes($nome, $email, $senha)) {
                $utilizador->Senha = $senha;
                $this->utilizadores[] = $utilizador;
            } else {
                $this->resultados[] = "Falha ao definir informações do usuário $i após cadastro.";
            }
        }
        // Torna o primeiro admin
        if (count($this->utilizadores) > 0) {
            global $BaseDeDados;
            $email = $this->utilizadores[0]->Email;
            $stmt = $BaseDeDados->getConexao()->prepare("UPDATE tbl_utilizadores SET Admin = 1 WHERE Email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->close();

            // Atualiza o objeto em memória para refletir o novo status de admin
            $this->utilizadores[0]->DefinirInformacoes(
                $this->utilizadores[0]->Nome,
                $this->utilizadores[0]->Email,
                $this->utilizadores[0]->Senha
            );

            if($this->utilizadores[0]->VerificarAdmin())
              $this->resultados[] = "<br>Usuário 1 é admin.";
            else
              $this->resultados[] = "<br>Usuário 1 não é admin.";
        }
    }

    private function testarLoginUtilizadores() {
        $this->resultados[] = "<br>Testando login de usuários...";
        foreach ($this->utilizadores as $i => $utilizador) {
            $ok = Login::VerificarLogin($utilizador->Email, $utilizador->Senha);
            if (!$ok) {
                $this->resultados[] = "Falha no login do usuário ".($i+1).": Email ou senha inválidos.";
                continue;
            }
            else
                $this->resultados[] = "Usuário ".($i+1).": login bem-sucedido com Email {$utilizador->Email}.";
        }
    }

    private function testarMetodosUtilizador() {
        $this->resultados[] = "<br>Testando métodos de usuário...";
        foreach ($this->utilizadores as $i => $utilizador) {
            // Testa AdicionarAgendamento
            $agendamento = new Agendamento();
            $espacoId = $this->espacoId ?? 1;
            // Horários diferentes para cada usuário para evitar conflito
            $horaInicio = (10 + $i) . ':00:00';
            $horaFim = (11 + $i) . ':00:00';
            $ok = $agendamento->DefinirInformacoes($espacoId, date('Y-m-d', strtotime('+1 day')), $horaInicio, $horaFim, "Motivo Teste $i");
            if ($ok) {
                $ok2 = $utilizador->AdicionarAgendamento($agendamento);
                $this->resultados[] = $ok2 ? "Usuário ".($i+1).": agendamento adicionado." : "Usuário ".($i+1).": falha ao adicionar agendamento.";
                if ($ok2) $this->agendamentos[] = $agendamento;
            } else {
                $this->resultados[] = "Usuário ".($i+1).": dados inválidos para agendamento.";
            }
            // Testa VisualizarAgendamentos
            $agds = $utilizador->VisualizarAgendamentos();
            $this->resultados[] = "Usuário ".($i+1).": visualizou ".count($agds)." agendamentos.";
            /* Testa CancelarAgendamento
            if (!empty($agds)) {
                $ok = $utilizador->CancelarAgendamento($agds[0]->Id ?? 1); // Cancela o primeiro agendamento do usuário
                $this->resultados[] = $ok ? "Usuário ".($i+1).": cancelou agendamento ".($agds[0]->Id ?? 1)."." : "Usuário ".($i+1).": não conseguiu cancelar agendamento ".($agds[0]->Id ?? 1).".";
            }
                */
            // Testa AcederAdmin
            $ok = $utilizador->AcederAdmin();
            $this->resultados[] = $ok ? "Usuário ".($i+1).": acesso admin OK." : "Usuário ".($i+1).": acesso admin NEGADO.";
            // Testa ObterInformacoesComSenha~
            $info = $utilizador->ObterInformacoes();
            $this->resultados[] = "Usuário ".($i+1).": info: ".json_encode($info);
        }
    }

    private function testarAdmin() {
        $this->resultados[] = "<br>Testando métodos de admin...";
        // Usa o primeiro usuário como admin
        if (count($this->utilizadores) == 0) return;
        $admin = new Admin();
        $admin->DefinirInformacoes($this->utilizadores[0]);
        $this->admin = $admin;
        // VisualizarAgendamentos
        $agds = $admin->VisualizarAgendamentos();
        $this->resultados[] = "Admin: visualizou ".count($agds)." agendamentos.";
        // Aprovar/Rejeitar agendamento
        $agendamentoId = null;
        if (!empty($this->agendamentos)) {
            // Pega o id real do agendamento inserido
            global $BaseDeDados;
            $result = $BaseDeDados->getConexao()->query("SELECT Id FROM tbl_agendamentos ORDER BY Id ASC LIMIT 1");
            if ($result && $row = $result->fetch_assoc()) {
                $agendamentoId = $row['Id'];
            }
        }
        $ok = $admin->AprovarRejeitarAgendamento($agendamentoId);
        $this->resultados[] = $ok ? "Admin: aprovou/rejeitou agendamento $agendamentoId." : "Admin: não conseguiu aprovar/rejeitar agendamento $agendamentoId.";
        // Visualizar com filtros
        $filtros = $admin->VisualizarAgendamentos_ComFiltros();
        $this->resultados[] = "Admin: visualizou com filtros: ".json_encode($filtros);
    }
}

$testes = new TestesUnitarios();
$testes->rodar();
?>