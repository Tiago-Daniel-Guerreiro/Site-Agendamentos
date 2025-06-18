<?php
define('PROJECT_ROOT', __DIR__); 
require_once PROJECT_ROOT . '/vendor/autoload.php'; 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dotenv = Dotenv\Dotenv::createImmutable(PROJECT_ROOT); 
$dotenv->load();

$BaseDeDados = new BaseDeDados();
$BaseDeDados->Conectar();

class Cadastro {  // Usar Javascript para validar a $senha==$ConfirmacaoSenha no front-end, + php para validar no back-end
  private $Nome; 
  private $Email; 
  private $Senha;

  function DefinirInformacoes($nome,$email,$senha,$confirmacaoSenha) : bool
  { 
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmacaoSenha))
      return false; // Verifica se algum campo está vazio

    if ($senha != $confirmacaoSenha)
      return false; 

    $this->Nome = $nome; 
    $this->Email = $email; 
    $this->Senha = $senha; 

    return true;
  } 
  
  // Cadastra o usuário. Retorna true se o cadastro for bem-sucedido, false caso contrário
  function Cadastrar():bool {     
    if($this->VerificaEmailExiste($this->Email)) // Verifica se o email já existe
      return false; // Email já existe na base de dados

    if(!$this->InserirUtilizador())
      return false; // Falha ao inserir usuário na base de dados

    return true; // Cadastro bem-sucedido
  }

  // Verifica se o email já existe na base de dados, Retorna true se existir, false caso contrário
  function VerificaEmailExiste($email):bool { 
    global $BaseDeDados;

    if($BaseDeDados->EmailExiste($email)) // Verifica se o email já existe,
      return true;  // Retorna true se existir

    return false; // Retorna false se não existir
  }
  
  // Insere o usuário na base de dados, Retorna true se a inserção for bem-sucedida, false caso contrário
  function InserirUtilizador():bool {
    if($this->VerificaEmailExiste($this->Email))
      return false; // Retorna false se o email já existir

    global $BaseDeDados;

    if(!$BaseDeDados->InserirUtilizador($this->Nome, $this->Email, $this->Senha)) 
      return false; // Retorna false se a inserção falhar

    return true; // Retorna true se a inserção for bem-sucedida
  }
}
class Login { 
  public $Email;
  public $Senha; 

  function DefinirInformacoes($email,$senha) { 
    $this->Email = $email; 
    $this->Senha = $senha; 
  } 
  function Logar():bool { // Código para entrar?
    
    return self::VerificarLogin($this->Email, $this->Senha);
  } 
  public static function VerificarLogin($email,$senha):bool { 
    global $BaseDeDados;

    if (!$BaseDeDados->VerificarLogin($email, $senha)) {
      return false; // Login falhou
    }
    
    return true; // Retorna true se o login for bem-sucedido 
  }
  public static function VerificarLogin_Utilizador(Utilizador $utilizador):bool {
    return self::VerificarLogin($utilizador->Email, $utilizador->Senha);
  }
}
class Sessao {
  // Inicia a sessão apenas se ainda não estiver iniciada
  private static function iniciarSessaoSeNecessario() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
  }

  // Inicia a sessão e define o usuário
  public static function IniciarSessao(Utilizador $utilizador) {
    if (!self::VerificaSessao()) {
      $_SESSION['utilizador'] = $utilizador;
      return true;
    }
    // Já existe um usuário na sessão
    return false;
  }

  // Verifica se existe um usuário na sessão
  public static function VerificaSessao(): bool {
    self::iniciarSessaoSeNecessario();
    return isset($_SESSION['utilizador']);
  }

  // Finaliza a sessão
  public static function FinalizarSessao() {
    self::iniciarSessaoSeNecessario();
    $_SESSION = [];
    session_destroy();
  }

  // Obtém o usuário da sessão
  public static function ObterUtilizador() {
    self::iniciarSessaoSeNecessario();
    if($_SESSION['utilizador'])
      return $_SESSION['utilizador'];
    else
      return null; // Retorna null se não houver usuário na sessão
  }

  // Remove o usuário da sessão
  public static function RemoverUtilizador() {
    self::iniciarSessaoSeNecessario();
    unset($_SESSION['utilizador']);
  }

  // Define cookies de login e senha para manter sessão
  public static function DefinirCookiesLogin($email, $senha)
  {
    setcookie('login', $email, time() + 604800, "/");
    setcookie('senha', $senha, time() + 604800, "/");
  }

  // Remove cookies de login e senha
  public static function RemoverCookiesLogin()
  {
    setcookie('login', '', time() - 3600, "/");
    setcookie('senha', '', time() - 3600, "/");
  }
}
class Agendamento {
  public $Espaco_Id; 
  public $Data;
  public $Hora_Inicio;
  public $Hora_Fim;
  public $Motivo;
 
  // Define as informações do agendamento. Retorna false se algum campo estiver vazio ou se os dados forem inválidos
  function DefinirInformacoes($espaco,$data,$horaInicio,$horaFim,$motivo) {
    $this->Espaco_Id = $espaco;
    $this->Data = $data;
    $this->Hora_Inicio = $horaInicio;
    $this->Hora_Fim = $horaFim;
    $this->Motivo = $motivo;

    if(!$this->VerificarDados()) { // Verifica se os dados estão incorretos
      $this->LimparDados(); // Limpa os dados 
      return false; // retorna false
    }

    return true; // Se os dados estiverem corretos e não houver conflito, retorna true    
  }

  // Retorna true se houver conflito, false caso contrário  
  function VerificaConflito():bool { 
    return BaseDeDados_Aceder_Com_Classes::ExisteConflito($this);
  }

  //Verificar se os dados do agendamento estão corretos, Retorna true se os dados estiverem corretos, false caso contrário
  function VerificarDados():bool {
    if(empty($this->Espaco_Id) || empty($this->Data) || empty($this->Hora_Inicio) || empty($this->Hora_Fim) || $this->Motivo === null) 
      return false; // Verifica se algum campo está vazio
    
    if(!strtotime($this->Data) || !strtotime($this->Hora_Inicio) || !strtotime($this->Hora_Fim)) 
      return false; // Verifica se a data e horários são válidos
    
    if(strtotime($this->Hora_Inicio) >= strtotime($this->Hora_Fim))
      return false; // Verifica se a hora de início é anterior à hora de fim

    if(strtotime($this->Data) < strtotime(date('Y-m-d')))
      return false; // Verifica se a data e horários não estão no passado
    
    return true;
  }

  // Limpa os dados do agendamento
  function LimparDados() {
    $this->Espaco_Id = null;
    $this->Data = null;
    $this->Hora_Inicio = null;
    $this->Hora_Fim = null;
    $this->Motivo = null;
  }

  //Adiciona o agendamento à base de dados. Retorna true se for bem-sucedido, false caso contrário
  function GravarAgendamento($Utilizador):bool {
    if($this->VerificaConflito()) { // Verifica se há conflito com outro agendamento
      return false; // Retorna false se houver conflito
    }
    if(!$Utilizador->AdicionarAgendamento($this))
      return false;
    return true;
  }

  // Retorna as informações do agendamento em um array
  function ObterInformacoes(): array {
    if(!$this->VerificarDados())
      return []; // Retorna um array vazio se os dados não forem válidos

    return [
      'Espaco_Id' => $this->Espaco_Id,
      'Data' => $this->Data,
      'Hora_Inicio' => $this->Hora_Inicio,
      'Hora_Fim' => $this->Hora_Fim,
      'Motivo' => $this->Motivo
    ];
  }
}
class Utilizador {
  public int $Id;
  public string $Nome;
  public string $Email;
  public string $Senha;

  public function VerificarDados() {
    try {
      if (!isset($this->Id) || !is_numeric($this->Id) || $this->Id <= 0) return false;
      if (empty($this->Nome) || empty($this->Email)) return false;
      if (!filter_var($this->Email, FILTER_VALIDATE_EMAIL)) return false;
      // Senha pode ser vazia para operações administrativas
    } catch (Exception $e) {
      return false;
    }
    return true;
  }
  // Verifica se o usuário é um administrador na base de dados
  public function VerificarAdmin():bool {    
    if(!$this -> VerificarDados())
      return false;

    return Admin::VerificarAdminNaBaseDeDados($this); 
  }
  // Adiciona um agendamento à base de dados. Retorna true se for bem-sucedido, false caso contrário
  public function AdicionarAgendamento(Agendamento $agendamento):bool {
    if(!$this -> VerificarDados())
      return false;

    global $BaseDeDados;

    if($agendamento->VerificaConflito()) 
      return false;

    if(!$agendamento->VerificarDados()) 
      return false;

    if(!BaseDeDados_Aceder_Com_Classes::InserirAgendamento($this, $agendamento))
      return false;
    return true;
  }
  // Define as informações do usuário. Retorna false se algum campo estiver vazio, email inválido ou falha no login
  public function DefinirInformacoes($nome,$email, $senha) {
    if(empty($nome) || empty($email) || empty($senha))
      return false;

    $email = trim(strtolower($email));

    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
      return false;

    $this->Nome = $nome;
    $this->Email = $email;
    $this->Senha = $senha;

    $IdTemp = BaseDeDados_Aceder_Com_Classes::ObterIdDeUtilizador($this);

    if($IdTemp == null)
    {
      $this->DefinirInformacoesNull();
      return false;
    }
    $this->Id = $IdTemp;
    return true;
  }
  private function DefinirInformacoesNull(){
    $this->Nome = "";
    $this->Email = "";
    $this->Senha = "";
  }
  // Retorna um array de agendamentos válidos do usuário.
  public function VisualizarAgendamentos(): array {
    if(!$this -> VerificarDados())
      return [];

    $agendamentos = BaseDeDados_Aceder_Com_Classes::ObterAgendamentosUtilizador($this);

    $validos = [];
    foreach ($agendamentos as $item){
      // Se for array associativo (novo formato)
      if (is_array($item) && isset($item['agendamento']) && $item['agendamento'] instanceof Agendamento) {
        if ($item['agendamento']->VerificarDados()) {
          $validos[] = $item;
        }
      } elseif ($item instanceof Agendamento) { // fallback para formato antigo
        if ($item->VerificarDados()) {
          $validos[] = $item;
        }
      }
    }
    return $validos;
  }
  public function CancelarAgendamento($id):bool {
    if(!$this -> VerificarDados())
      return false;

    global $BaseDeDados;
    return $BaseDeDados->CancelarAgendamento($id, $this->Id);
  }
  public function AcederAdmin():bool {
    if(!$this -> VerificarDados())
      return false;

    return $this->VerificarAdmin();
  }
  public function ObterInformacoes(): array {
   if(!$this -> VerificarDados())
      return [];

    return [
        'Id'    => $this->Id,
        'Nome'  => $this->Nome,
        'Email' => $this->Email
    ];
  }
  public function EditarDados($novoNome, $novoEmail, $novoAdmin) : bool {
    if(empty($novoNome) || empty($novoEmail) || !in_array($novoAdmin, [0,1], true))
      return false;
    if(!filter_var($novoEmail, FILTER_VALIDATE_EMAIL))
      return false;
    // Chama o método estático que faz o update
    return BaseDeDados::EditarUtilizador($this->Id, $novoNome, $novoEmail, $novoAdmin);
  }
}
class Admin {
  public Utilizador $Utilizador;

  public function VerificarUtilizador(): bool {
    try{
      if($this->Utilizador == null || !$this->Utilizador->VerificarDados()) {
        return false; // Retorna false se o usuário for null ou os dados não forem válidos
      }
    } catch (Exception $e) {
      return false; // Retorna false se ocorrer alguma exceção
    }
    // Verifica se o usuário é admin na base de dados
    return $this->Utilizador->VerificarAdmin();
  }
  public static function VerificarAdminNaBaseDeDados(Utilizador $utilizador):bool {
    if($utilizador->VerificarDados() == false)
      return false; // Retorna false se o usuário for null

    return BaseDeDados_Aceder_Com_Classes::VerificarAdmin($utilizador); // Verifica se o usuário é um administrador na base de dados
  }
  public function DefinirInformacoes(Utilizador $utilizador) {
    if($utilizador->VerificarDados() == false)
      return false; // Retorna false se o usuário for null

    if(!self::VerificarAdminNaBaseDeDados($utilizador))
      return false;
   
    $this->Utilizador = $utilizador;
    return true;
  }
  public function VisualizarAgendamentos() {
    if($this->VerificarUtilizador() == false)
      return []; // Retorna um array vazio se o usuário não for admin

    if(!self::VerificarAdminNaBaseDeDados($this->Utilizador))
      return [];
    return BaseDeDados_Aceder_Com_Classes::ObterTodosOsAgendamentos($this); // Retorna todos os agendamentos
  }
  public function VisualizarAgendamentos_ComFiltros(string $Espaco = null, int $Id = null, string $DataInicial = null, string $DataFinal = null):array {
    if($this->VerificarUtilizador() == false)
      return []; // Retorna um array vazio se o usuário não for admin

    if(!self::VerificarAdminNaBaseDeDados($this->Utilizador))
      return [];
    // código para filtrar os agendamentos na base de dados
    return [];
  }
}
class BaseDeDados_Aceder_Com_Classes {
    public static function ObterIdDeAgendamento(Agendamento $agendamento): ?int {
      global $BaseDeDados;
      return $BaseDeDados->ObterIdDeAgendamento($agendamento->Espaco_Id, $agendamento->Data, $agendamento->Hora_Inicio, $agendamento->Hora_Fim);
    }
    public static function VerificarLogin(Utilizador $Utilizador): bool {
      global $BaseDeDados;
      return $BaseDeDados->VerificarLogin($Utilizador->Email, $Utilizador->Senha);
    }

    public static function InserirAgendamento(Utilizador $utilizador, Agendamento $agendamento): bool {
      global $BaseDeDados;
      return $BaseDeDados->InserirAgendamento(
        $utilizador->Id,
        $agendamento->Espaco_Id,
        $agendamento->Data,
        $agendamento->Hora_Inicio,
        $agendamento->Hora_Fim,
        $agendamento->Motivo
      );
    }

    public static function ExisteConflito(Agendamento $agendamento): bool {
      global $BaseDeDados;
      return $BaseDeDados->ExisteConflito(
          $agendamento->Espaco_Id,
          $agendamento->Data,
          $agendamento->Hora_Inicio,
          $agendamento->Hora_Fim
      );
    }

    public static function ObterAgendamentosUtilizador(Utilizador $utilizador): array
    {

        global $BaseDeDados;

        $agendamentos = [];

        if (!isset($BaseDeDados)) 
            return $agendamentos;

        $agendamentos_Array = $BaseDeDados->ObterAgendamentosUtilizador($utilizador->Id);

        if (!is_array($agendamentos_Array) || count($agendamentos_Array) === 0)
            return $agendamentos;

        foreach ($agendamentos_Array as $row) 
        {

            if (
                !is_array($row) ||
                !isset($row['Espaco_Id']) ||
                !isset($row['Data']) ||
                !isset($row['Hora_Inicio']) ||
                !isset($row['Hora_Fim']) ||
                !isset($row['Motivo'])
            )
                continue;

            $agendamento = new Agendamento();
            if ($agendamento->DefinirInformacoes($row['Espaco_Id'], $row['Data'], $row['Hora_Inicio'], $row['Hora_Fim'], $row['Motivo']))
            {

                $item = [];
                $item['agendamento'] = $agendamento;

                if (isset($row['Id']))
                    $item['Id'] = $row['Id'];
                else
                    $item['Id'] = null;

                if (isset($row['Utilizador_Id']))
                    $item['Utilizador_Id'] = $row['Utilizador_Id'];
                else
                    $item['Utilizador_Id'] = null;

                $agendamentos[] = $item;

            }

        }

        return $agendamentos;

    }

    public static function VerificarAdmin(Utilizador $utilizador): bool {

      global $BaseDeDados;
      return $BaseDeDados->VerificarAdmin($utilizador->Id);
    }
    public static function ObterTodosOsAgendamentos(Admin $admin): array {
      global $BaseDeDados;
      if(!BaseDeDados_Aceder_Com_Classes::VerificarAdmin($admin->Utilizador))
        return []; // Retorna um array vazio se o usuário não for um administrador

      return $BaseDeDados->ObterTodosOsAgendamentos($admin->Utilizador->Id);
    }
    public static function ObterIdDeUtilizador(Utilizador $utilizador): ?int {
      global $BaseDeDados;
      return $BaseDeDados->ObterIdPeloEmail($utilizador->Email); // Retorna o ID do usuário pelo email
    }
}
class BaseDeDados {
    private $conexao;

    public function Conectar(bool $tests = false) {
      if($tests == false)
        $DB = $_ENV['DB_NAME'];
      else
        $DB = $_ENV['DB_TESTS_NAME'];

      $user = $_ENV['DB_USER'];
      $passwd = $_ENV['DB_PASS'];
      $host = $_ENV['DB_HOST'];

      $this->conexao = new mysqli($host, $user, $passwd, $DB);

      if ($this->conexao->connect_error) {
          die("Conexão falhou: " . $this->conexao->connect_error);
      }
    }
    public function Desconectar() {
        if ($this->conexao) {
            $this->conexao->close();
        }
    }
    public function VerificarConexao() : bool {
        return $this->conexao && !$this->conexao->connect_error;
    }
    public function InserirUtilizador($nome, $email, $senha): bool {
        $senhaHash = $this->CodificarSenha($senha);
        $stmt = $this->conexao->prepare("INSERT INTO tbl_utilizadores (Nome, Email, Senha) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $email, $senhaHash);

        if ($stmt->execute()) {
            if($this->conexao->insert_id != null)
                return true;
            else
                return false;
        }
        return false; // Retorna false se a inserção falhar
    }
    private function CodificarSenha($senha): string {
        return password_hash($senha, PASSWORD_DEFAULT);
    }
    public function ObterEmailPorUsername($username) {
      if ($username === null) {
          return null; // Retorna null se o email for null
      }
      $stmt = $this->conexao->prepare("SELECT Email FROM tbl_utilizadores WHERE Nome = ?");
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $resultado = $stmt->get_result();
      $row = $resultado->fetch_assoc();
      return $row ? $row['Email'] : null; // Retorna o email do usuário ou null se não existir
    }
    public function EmailExiste($email): bool { 
        // Procura o ID de um usuário com o email informado
        $stmt = $this->conexao->prepare("SELECT Id FROM tbl_utilizadores WHERE Email = ?"); // Prepara a consulta SQL
        $stmt->bind_param("s", $email);  // Adiciona o email como parâmetro
        $stmt->execute(); // Executa a consulta
        $stmt->store_result(); // Armazena o resultado para contar as linhas retornadas

        // Retorna true se encontrou pelo menos um registro (ou seja, o email já existe)
        // Retorna false se não encontrou nenhum registro (ou seja, o email não existe)
        return $stmt->num_rows > 0;
    }
    public function VerificarLoginComEmail($email, $senha): bool {
        if($email === null || $senha === null) {
            return false; // Retorna false se email ou senha forem nulos
        }
        $stmt = $this->conexao->prepare("SELECT Senha FROM tbl_utilizadores WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($senhaHash);
        if ($stmt->fetch()) {
            return password_verify($senha, $senhaHash); // Corrigido: compara senha informada com hash
        }
        // Se não encontrar o usuário, retorna false
        return false;
    }
    public function VerificarLoginComUsername($username, $senha): bool {
      if($username === null) {
          return false; // Retorna false se email ou senha forem nulos
      }
      $email = $this->ObterEmailPorUsername($username);

      if(!$this->VerificarLoginComEmail($email, $senha))
        return false;

      return true;
    }
    public function VerificarLogin($email_username, $senha): bool {
      if($email_username === null || $senha === null) {
          return false; // Retorna false se email ou senha forem nulos
      }
      
      if($this->VerificarLoginComEmail($email_username, $senha)) {

        Sessao::IniciarSessao($this->ObterUtilizadorComEmail($email_username, $senha)); // Inicia a sessão com o usuário
        return true; // Verifica se é um email
      }

      if($this->VerificarLoginComUsername($email_username, $senha)){ // Se não for um email, tenta verificar como username
        
        Sessao::IniciarSessao($this->ObterUtilizadorComUsername($email_username, $senha)); // Inicia a sessão com o usuário
        return true; // Verifica se é um username
      }

      
      return false;
    }
    public function ObterUtilizadorComEmail($email, $senha): ?Utilizador {
        if ($email === null || $senha === null) {
            return null; // Retorna null se o email for null
        }
        $stmt = $this->conexao->prepare("SELECT * FROM tbl_utilizadores WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $utilizador = new Utilizador();
            if($utilizador->DefinirInformacoes($row['Nome'], $row['Email'], $senha)) { // Define as informações do usuário
                $utilizador->Id = $row['Id']; // Define o ID do usuário
                return $utilizador; // Retorna o objeto Utilizador
            }
        }
    }
    public function ObterUtilizadorComUsername($username, $senha): ?Utilizador {
        if ($username === null || $senha === null) {
            return null; // Retorna null se o username for null
        }
        $email = $this->ObterEmailPorUsername($username);
        if ($email === null) {
            return null; // Retorna null se o email não existir
        }
        return $this->ObterUtilizadorComEmail($email, $senha); // Busca o usuário pelo email
    }
    public function InserirAgendamento($utilizadorId, $espacoId, $data, $horaInicio, $horaFim, $motivo): bool {
      if($this->ExisteConflito($espacoId, $data, $horaInicio, $horaFim)) 
          return false; // Retorna false se houver conflito com outro agendamento

      $stmt = $this->conexao->prepare("INSERT INTO tbl_agendamentos (Utilizador_Id, Espaco_Id, Data, Hora_Inicio, Hora_Fim, Motivo) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("iissss", $utilizadorId, $espacoId, $data, $horaInicio, $horaFim, $motivo);
      return $stmt->execute();
    }
    public function ExisteConflito($espacoId, $data, $horaInicio, $horaFim): bool {
        $stmt = $this->conexao->prepare(
            "SELECT Id FROM tbl_agendamentos 
             WHERE Espaco_Id = ? AND Data = ? 
             AND ((Hora_Inicio < ? AND Hora_Fim > ?) OR (Hora_Inicio < ? AND Hora_Fim > ?))"
        );
        $stmt->bind_param("isssss", $espacoId, $data, $horaFim, $horaInicio, $horaInicio, $horaFim);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
    public function ObterAgendamentosUtilizador($utilizadorId): array {
        $stmt = $this->conexao->prepare("SELECT * FROM tbl_agendamentos WHERE Utilizador_Id = ?");
        $stmt->bind_param("i", $utilizadorId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    public function ObterTodosOsAgendamentos($utilizadorId): array {
      if(!$this->VerificarAdmin($utilizadorId))
        return false;

      $stmt = $this->conexao->prepare("SELECT * FROM tbl_agendamentos");
      $stmt->execute();
      $resultado = $stmt->get_result();
      return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    public function ObterAgendamentosPorId($agendamentoId) {
      $stmt = $this->getConexao()->prepare("SELECT * FROM tbl_agendamentos WHERE Id = ?");
      $stmt->bind_param("i", $agendamentoId);
      $stmt->execute();
      $resultado = $stmt->get_result();

      while ($row = $resultado->fetch_assoc()) {
        $agendamento = new Agendamento();

        $espacoId = $row['Espaco_Id'];
        $data = $row['Data'];
        $horaInicio = $row['Hora_Inicio'];
        $horaFim = $row['Hora_Fim'];
        $motivo = $row['Motivo'];

        if(!$agendamento->DefinirInformacoes($espacoId, $data, $horaInicio, $horaFim, $motivo)) 
          return []; // Se os dados não forem válidos, pula para o próximo

        return $agendamento;
      }
    }
    public function CancelarAgendamento($agendamentoId, $utilizadorId): bool {
      // Verifica se o utilizador é o dono do agendamento ou admin
      $stmt = $this->conexao->prepare("SELECT Utilizador_Id FROM tbl_agendamentos WHERE Id = ?");
      $stmt->bind_param("i", $agendamentoId);
      $stmt->execute();
      $resultado = $stmt->get_result();
      if ($resultado->num_rows === 0) {
        return false; // Agendamento não existe
      }
      $row = $resultado->fetch_assoc();
      $donoId = $row['Utilizador_Id'];

      // Verifica se é o dono ou admin
      $isAdmin = $this->VerificarAdmin($utilizadorId);
      if ($donoId != $utilizadorId && !$isAdmin) {
        return false; // Não é permitido cancelar
      }

      $stmt = $this->conexao->prepare("DELETE FROM tbl_agendamentos WHERE Id = ?");
      $stmt->bind_param("i", $agendamentoId);
      return $stmt->execute();
    }
    public function VerificarAdmin($utilizadorId): bool {
      $admin = 0; // Inicializa a variável para evitar erros
      $stmt = $this->conexao->prepare("SELECT Admin FROM tbl_utilizadores WHERE Id = ?");
      $stmt->bind_param("i", $utilizadorId);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($admin);
      if ($stmt->num_rows > 0 && $stmt->fetch()) {
          return intval($admin) === 1;
      }
      return false;
    }
    public function ObterEspacos(): array {
        $stmt = $this->conexao->prepare("SELECT * FROM tbl_espacos");
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    public function AdicionarEspacos($nome): bool {
        // Verifica se já existe um espaço com o mesmo nome (case insensitive)
        $stmt = $this->conexao->prepare("SELECT COUNT(*) FROM tbl_espacos WHERE LOWER(Nome) = LOWER(?)");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count > 0) {
            return false; // Já existe um espaço com esse nome
        }
        $stmt = $this->conexao->prepare("INSERT INTO tbl_espacos (Nome) VALUES (?)");
        $stmt->bind_param("s", $nome);
        return $stmt->execute();
    }
    public function EditarEspaco($id, $novoNome): bool {
        // Não permitir nome duplicado
        $stmt = $this->conexao->prepare("SELECT COUNT(*) FROM tbl_espacos WHERE LOWER(Nome) = LOWER(?) AND Id != ?");
        $stmt->bind_param("si", $novoNome, $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count > 0) {
            return false; // Nome já existe
        }
        $stmt = $this->conexao->prepare("UPDATE tbl_espacos SET Nome = ? WHERE Id = ?");
        $stmt->bind_param("si", $novoNome, $id);
        return $stmt->execute();
    }
    public function RemoverEspaco($id, $apagarAgendamentos = false): bool {
        if ($this->EspacoTemAgendamentos($id)) {
            if ($apagarAgendamentos) {
                $stmt = $this->conexao->prepare("DELETE FROM tbl_agendamentos WHERE Espaco_Id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
            } else {
                return false; // Não remove se houver agendamentos e não for para apagar
            }
        }
        $stmt = $this->conexao->prepare("DELETE FROM tbl_espacos WHERE Id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    public function EspacoTemAgendamentos($id): bool {
        $stmt = $this->conexao->prepare("SELECT COUNT(*) FROM tbl_agendamentos WHERE Espaco_Id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }
    public function getConexao() {
      return $this->conexao;
    }
    public function ObterIdPeloEmail($email): ?int {
        $stmt = $this->conexao->prepare("SELECT Id FROM tbl_utilizadores WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            return (int)$row['Id'];
        }
        return null; // Retorna null se não encontrar o usuário
    }
    public function LimparBaseDeDados() {
      // Corrigido: obter o nome do banco de dados atual
      $result = $this->conexao->query("SELECT DATABASE() as db");
      $dbName = $result->fetch_row()[0];   
      if ($dbName === 'schoolDBtests')  //verificar se a base de dados é a schoolDBtests
      { 
        $tabelas = [
            'tbl_agendamentos',
            'tbl_espacos',
            'tbl_tokens',
            'tbl_utilizadores'
        ];
        foreach ($tabelas as $tabela) {
            $this->conexao->query("TRUNCATE TABLE $tabela");
        }
      }
    }
    public static function AlterarAdmin($utilizadorId, $novoStatus): bool {
        global $BaseDeDados;
        $stmt = $BaseDeDados->getConexao()->prepare("UPDATE tbl_utilizadores SET Admin = ? WHERE Id = ?");
        $stmt->bind_param("ii", $novoStatus, $utilizadorId);
        return $stmt->execute();
    }
    public static function ObterTodosOsIdUtilizadores(): array {
        global $BaseDeDados;
        $stmt = $BaseDeDados->getConexao()->prepare("SELECT Id FROM tbl_utilizadores");
        $stmt->execute();
        $resultado = $stmt->get_result();
        $ids = [];
        while ($row = $resultado->fetch_assoc()) {
            $ids[] = (int)$row['Id'];
        }

        sort($ids); // Ordena os IDs em ordem crescente

        return $ids; // Retorna um array com todos os IDs dos usuários
    }
    public static function ObterUsernamePorId($id): ?string {
        global $BaseDeDados;
        $stmt = $BaseDeDados->getConexao()->prepare("SELECT Nome FROM tbl_utilizadores WHERE Id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            return $row['Nome']; // Retorna o nome do usuário
        }
        return null; // Retorna null se não encontrar o usuário
    }
    public static function ObterEstadoAdminPorId($id): ?bool {
        global $BaseDeDados;
        $stmt = $BaseDeDados->getConexao()->prepare("SELECT Admin FROM tbl_utilizadores WHERE Id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            return (bool)$row['Admin']; // Retorna o status de admin do usuário
        }
        return null; // Retorna null se não encontrar o usuário
    }
    public static function EditarAgendamento($Id, $IdUtilizador, $novoMotivo, $novoEspacoId, $novaData, $novaHoraInicio, $novaHoraFim): bool {
        global $BaseDeDados;
        // Busca o agendamento
        if(BaseDeDados::ExisteConflito($novoEspacoId, $novaData, $novaHoraInicio, $novaHoraFim)) 
          return false; // Retorna false se houver conflito com outro agendamento

        $stmt = $BaseDeDados->getConexao()->prepare("SELECT Utilizador_Id FROM tbl_agendamentos WHERE Id = ?");
        $stmt->bind_param("i", $Id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 0) {
            return false; // Agendamento não existe
        }
        $row = $resultado->fetch_assoc();
        $donoId = $row['Utilizador_Id'];
        // Só permite se for o dono ou admin
        if ($donoId != $IdUtilizador && !$BaseDeDados->VerificarAdmin($IdUtilizador)) {
            return false;
        }
        // Atualiza o agendamento
        $stmt = $BaseDeDados->getConexao()->prepare("UPDATE tbl_agendamentos SET Motivo = ?, Espaco_Id = ?, Data = ?, Hora_Inicio = ?, Hora_Fim = ? WHERE Id = ?");
        $stmt->bind_param("sisssi", $novoMotivo, $novoEspacoId, $novaData, $novaHoraInicio, $novaHoraFim, $Id);
        return $stmt->execute();
    }
    public static function ApagarUtilizador($id, $idUtilizador): bool {
        global $BaseDeDados;
        // Só permite se for o próprio usuário ou admin
        if (!$BaseDeDados->VerificarAdmin($idUtilizador)) {
            return false;
        }
        $stmt = $BaseDeDados->getConexao()->prepare("DELETE FROM tbl_utilizadores WHERE Id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    public static function EditarUtilizador($id, $novoNome, $novoEmail, $admin): bool {
        global $BaseDeDados;
        if (empty($novoNome) || empty($novoEmail) || ($admin !== 0 && $admin !== 1)) {
            return false; // Retorna false se algum campo estiver vazio ou admin inválido
        }
        if (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
            return false; // Retorna false se o email não for válido
        }
        // Se o admin for 0 ou 1, atualiza o status de admin
        $stmt = $BaseDeDados->getConexao()->prepare("UPDATE tbl_utilizadores SET Nome = ?, Email = ?, Admin = ? WHERE Id = ?");
        $stmt->bind_param("ssii", $novoNome, $novoEmail, $admin, $id);
        return $stmt->execute(); // Retorna true se a atualização for bem-sucedida
    }
    public function ObterIdDeAgendamento($espacoId, $data, $horaInicio, $horaFim) {
        $stmt = $this->conexao->prepare("SELECT Id FROM tbl_agendamentos WHERE Espaco_Id = ? AND Data = ? AND Hora_Inicio = ? AND Hora_Fim = ?");
        $stmt->bind_param("isss", $espacoId, $data, $horaInicio, $horaFim);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            return $row['Id'];
        }
        return null;
    }
  }
?>