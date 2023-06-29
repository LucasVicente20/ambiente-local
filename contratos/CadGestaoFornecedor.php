<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadGestaoFornecedor.php
# Autor:    Roberta Costa
# Data:     23/08/2004
# Objetivo: Programa de Gestão de Fornecedores
#-------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data:     14/05/2007 - Verifica se a data de balanço expirou
#                      - Retirada do $_SESSION['GetUrl'] = array();
#                      - Exibir data de última alteração
#                      - Colocação de "/oracle/" antes de $Url
#-------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data:     25/05/2007 - Receber comissão e data análise documentação
#           28/05/2007 - Receber novos campos (índice Endividamento e Microempresa ou EPP)
#                      - Permissão de Alteração da comissão e data análise documentação
#           30/05/2007 - Alteração para exibir todas as certidões obrigatórias para o
#                        caso de inclusão de uma nova certidão
#-------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     31/05/2007 - Correção para não perder dados quando teclado F5 na aba de regularida fiscal
#-------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data:     07/06/2007 - Correção para não informar comissão responsável sem data de análise e vice-versa
#-------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     18/06/2007 - Receber novo campo (índice de solvência)
#-------------------------------------------------------------------------
# Alterado: Ariston
# Data:     XX/06/2008 - Tratamento de erros em DDD, email, CPF e CEP
#-------------------------------------------------------------------------
# Alterado: Ariston
# Data:     20/06/2008	- Removido IF que não permitia alterar situacao para 1 (cadastrado)
#												- Consertado bug em que não atualiza a situacao quando a data da Situação é nula (O que ocorre sempre quando se altera pra cadastrado) Agora a data de cadastrado sempre será a data atual
#												- Situação de fornacedor era ordenado por data informada da situacao. Agora é ordenado situações por data de registro
#-------------------------------------------------------------------------
# Alterado: Ariston
# Data:     30/06/2008	- Colocado de novo para ordenar situação por data informada da situacao (tbfornsituacao.dforsisitu).
#												OBS.: Isto pode causar um bug que seria resolvido na correção dia 20
#												(o da situação não aparecer mudada quando voce muda para uma situação com data anterior à ultima situação do registro)
#												Porém isso é necessário, pois a situação a ser considerada como atual deve ser a situação que a pessoa está atualmente (dforsisitu)
#												e não a do registro (tforsiulat)
#-------------------------------------------------------------------------
# Alterado: Ariston
# Data:     11/07/2008	- Consertado bug em que tentava enviar data inválida para  SFPC.TBfornsituacao.dforsisitu quando o cadastro estava habilitado e nada era alterado
#												- Removi a checagem de erro quando o DDD for 2 dígitos. O problema é que, como o campo de DDD é número ao invés de caracteres no banco, o banco remove o 3o dígito (que é zero), deixando todos DDDs com 2 dígitos.
#-------------------------------------------------------------------------
# Alterado: Ariston
# Data:     28/08/2008	- Permitir que fornecedores inativos possuam data expirada de CHF
#-------------------------------------------------------------------------
# Alterado: Ariston
# Data:     01/09/2008	- Cadastrar fornecedores de compra direta, que não precisam autencicação das abas 'Regularidade Fiscal' e 'Qualificação Técnica'
#-------------------------------------------------------------------------
# Alterado: Ariston
# Data:     23/03/2009	- Em "Regularização Fiscal", lembrar alteração de datas quando mudar de aba e voltar para aba de regularização
#-------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     05/08/2010 - Verificação de data de balanço anual se está no prazo
#-------------------------------------------------------------------------
# Alterado: Ariston
# Data:     05/08/2010	- Adicionado opção para incluir sócios
#-------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     16/08/2010 - Permitir exclusão de todas classes serviços na inclusão e alteração de fornecedor e pré-fornecedor
#-------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     25/08/2010 - Data de última alteração de contrato ou estatuto
#-------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     27/09/2010 - Correção CEP
#-------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     14/10/2010 - Correção
#-------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     05/11/2010 - Alterando prazos de balanço anual e certidão negativa
#-------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     24/05/2011 - Tarefa Redmine: 2245 - Em Inscrição de Fornecedores obrigar a digitação do e-mail
#-------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     25/05/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
#                      - Alteração do nome do arquivo de "CadIncluirClasses.php" para "CadIncluirGrupos.php"
#-------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     27/05/2011 - Tarefa Redmine: 2727 - Incluir novos campos para preenchimento no SICREF - Dados do Representante Legal
#-------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     16/09/2011 - Tarefa Redmine: 3718 - Remoção de campos de Representante Legal no módulo de fornecedores (SICREF)
#-------------------------------------------------------------------------
# Alterado: Luiz Alves
# Data: 	08/03/2012 - Tarefa Redmine: 4092 - Manutenção na caital social subscrito e patrimônio líquido.
#-------------------------------------------------------------------------
# Alterado: José Almir <jose.almir@pitang.com>
# Data: 02/10/2014
# Objetivo: [CR125344]: REDMINE 71
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     25/07/2018
# Objetivo: Tarefa Redmine 80154
#-------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
#           Toda vez que incluir um novo campo ou dado de sessão ir em funções e acrescentar o campo em limpasessao()
#           Toda vez que aumentar ou diminuir o nº campos da primeira parte da tela, aumentar ou diminuir a variável $NoCamposGerais
# OBS.:     Alterações deste documento php podem precisar ser replicados em CadGestaoFornecedorIncluir.php, CadGestaoFornecedor.php, CadInscritoAlterar.php, e CadInscritoIncluir.php
#-------------------------------------------------------------------------
# Alterado: Ernesto Ferreira
# Data:		31/10/2018
# Objetivo: Tarefa Redmine 205883
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     07/12/2019
# Objetivo: Tarefa Redmine 206101
#-------------------------------------------------------------------------
# Alterado: Eliakim Ramos
# Data:     10/12/2019
# Objetivo: Tarefa Redmine 222833
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
require_once( "funcoesFornecedores.php");
require_once( "funcoesDocumento.php");

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/fornecedores/CadIncluirCertidaoComplementar.php');
AddMenuAcesso('/fornecedores/CadIncluirGrupos.php');
AddMenuAcesso('/fornecedores/CadIncluirAutorizacao.php');
AddMenuAcesso('/fornecedores/RotVerificaEmail.php');
AddMenuAcesso('/fornecedores/CadGestaoFornecedorOcorrencias.php');
AddMenuAcesso('/fornecedores/CadGestaoFornecedorExcluir.php');
AddMenuAcesso('/fornecedores/CadGestaoFornecedorHistorico.php');
AddMenuAcesso('/fornecedores/CadGestaoFornecedorSuspenso.php');
AddMenuAcesso('/fornecedores/CadGestaoFornecedorSelecionar.php');
AddMenuAcesso('/oracle/fornecedores/RotDebitoCredorConsulta.php');
AddMenuAcesso('/oracle/fornecedores/RotConsultaInscricaoMercantil.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $Origem = $_POST['Origem'];
  $Destino = $_POST['Destino'];
  $_SESSION['Botao'] = $_POST['Botao'];

# Tabela Inicial de Todos os Formulários #
	$_SESSION['Cumprimento']		= $_POST['Cumprimento'];
	$_SESSION['Situacao']				= $_POST['Situacao'];
	$_SESSION['DataSituacao']		= $_POST['DataSituacao'];
	$_SESSION['Motivo']					= strtoupper2(trim($_POST['Motivo']));
	$_SESSION['DataExpiracao']	= $_POST['DataExpiracao'];
	$_SESSION['SituacaoAntes']	= $_POST['SituacaoAntes'];
	$_SESSION['DataGeracaoCHF'] = $_POST['DataGeracaoCHF'];
	$_SESSION['DataValidadeCHF']= $_POST['DataValidadeCHF'];
	$_SESSION['ComissaoResp']		= $_POST['ComissaoResp'];
	$_SESSION['DataAnaliseDoc']	= $_POST['DataAnaliseDoc'];
	$_SESSION['CarregaPagina']	= $_POST['CarregaPagina'];
	$_SESSION['TipoHabilitacao']= $_POST['TipoHabilitacao'];

# Variáveis do Formulário A #
if ($Origem == "A") {
	$_SESSION['CPF_CNPJ']				= $_POST['CPF_CNPJ'];
	$_SESSION['TipoCnpjCpf']		= $_POST['TipoCnpjCpf'];
	$_SESSION['MicroEmpresa'] 	= $_POST['MicroEmpresa'];
	$_SESSION['Identidade']			= strtoupper2(trim($_POST['Identidade']));
	$_SESSION['OrgaoUF']				= strtoupper2(trim($_POST['OrgaoUF']));
	$_SESSION['RazaoSocial']		= strtoupper2(trim($_POST['RazaoSocial']));
	$_SESSION['NomeFantasia'] 	= strtoupper2(trim($_POST['NomeFantasia']));
	$_SESSION['CEP']						= $_POST['CEP'];
	$_SESSION['CEPAntes']				= $_POST['CEPAntes'];
	$_SESSION['Logradouro']			= strtoupper2(trim($_POST['Logradouro']));
	$_SESSION['Numero']					=	$_POST['Numero'];
	$_SESSION['Complemento']		= strtoupper2(trim($_POST['Complemento']));
	$_SESSION['Bairro']					= strtoupper2(trim($_POST['Bairro']));
	$_SESSION['Cidade']					= strtoupper2(trim($_POST['Cidade']));
	$_SESSION['UF']							= $_POST['UF'];
	$_SESSION['DDD']						= $_POST['DDD'];
	$_SESSION['Telefone']				= $_POST['Telefone'];
	$_SESSION['Email']					= trim($_POST['Email']);
	$_SESSION['Email2']					= trim($_POST['Email2']);
	$_SESSION['EmailPopup']			= $_SESSION['Email'];
	$_SESSION['Fax']						= $_POST['Fax'];
	$_SESSION['RegistroJunta']	= $_POST['RegistroJunta'];
	$_SESSION['DataRegistro'] 	= $_POST['DataRegistro'];
	$_SESSION['NomeContato']		= strtoupper2(trim($_POST['NomeContato']));
	$_SESSION['CPFContato']			= $_POST['CPFContato'];
	$_SESSION['CargoContato'] 	= strtoupper2(trim($_POST['CargoContato']));
	$_SESSION['DDDContato']			= $_POST['DDDContato'];
	$_SESSION['TelefoneContato']= $_POST['TelefoneContato'];

# Sócios
	/* SociosNome- Possui os nomes dos sócios.
	 * Note que o array inclui os nomes dos sócios deletados, que não serão adicionados
	 * SociosCPF_CNPJ- Possui o CPF/CNPJ dos sócios. Também inclui os CPF/CNPJs deletados
	 * noSocios- conta o número de sócios, incluindo os sócios deletados
	 * SocioNovoNome- Nome do novo sócio a ser adicionado
	 * SocioNovoCPF- CPF do sócio a ser adicionado
	 * SocioSelecionado- Sócio selecionado para algum comando (apenas usado no comando de remover sócio)
	 * MostrarNovoSocio- Informa que o nome e o CPF do sócio devem ser mostrados nas caixas de texto.
	 * Usado para o caso de correção do campo
	 * */

	$_SESSION['NoSocios']					= $_POST['NoSocios'];
	$_SESSION['SociosCPF_CNPJ'] 	= $_POST['SociosCPF_CNPJ'];
	$_SESSION['SociosNome']				= $_POST['SociosNome'];
	$_SESSION['SocioNovoNome']		= $_POST['SocioNovoNome'];
	$_SESSION['SocioNovoCPF']			= $_POST['SocioNovoCPF'];
	$_SESSION['SocioSelecionado'] = $_POST['SocioSelecionado'];
	$_SESSION['MostrarNovoSocio'] = false;
}

# Variáveis do Formulário B #
	if ($Origem == "B") {
		if ($_SESSION['InscMercantil'] != $_POST['InscMercantil']) {
			$_SESSION['InscricaoValida'] = "";
		 }
			$_SESSION['InscEstadual']						= $_POST['InscEstadual'];
			$_SESSION['InscMercantil']					= $_POST['InscMercantil'];
			$_SESSION['InscOMunic']							= $_POST['InscOMunic'];
			$_SESSION['CertidaoObrigatoria']		= $_POST['CertidaoObrigatoria'];
			$_SESSION['DataCertidaoOb']					= $_POST['DataCertidaoOb'];
			$_SESSION['DescCertidaoObrigatoria']= $_POST['DescCertidaoObrigatoria'];
			$_SESSION['Certidao']								= $_POST['Certidao'];
			$_SESSION['DataCertidaoComp']				= $_POST['DataCertidaoComp'];
			$_SESSION['CheckComplementar']			= $_POST['CheckComplementar'];
			$_SESSION['CertidaoComplementar']		= $_POST['CertidaoComplementar'];
	}

# Variáveis do Formulário C #
	if ($Origem == "C") {
		$_SESSION['CapSocial']					 = $_POST['CapSocial'];
		$_SESSION['CapIntegralizado']		 = $_POST['CapIntegralizado'];
		$_SESSION['Patrimonio']					 = $_POST['Patrimonio'];
		$_SESSION['IndLiqCorrente']			 = $_POST['IndLiqCorrente'];
		$_SESSION['IndLiqGeral']				 = $_POST['IndLiqGeral'];
		$_SESSION['IndEndividamento']		 = $_POST['IndEndividamento'];
		$_SESSION['IndSolvencia']				 = $_POST['IndSolvencia'];
		$_SESSION['Banco1']							 = strtoupper2(trim($_POST['Banco1']));
		$_SESSION['Agencia1']						 = strtoupper2(trim($_POST['Agencia1']));
		$_SESSION['ContaCorrente1']			 = strtoupper2(trim($_POST['ContaCorrente1']));
		$_SESSION['Banco2']							 = strtoupper2(trim($_POST['Banco2']));
		$_SESSION['Agencia2']						 = strtoupper2(trim($_POST['Agencia2']));
		$_SESSION['ContaCorrente2']			 = strtoupper2(trim($_POST['ContaCorrente2']));
		$_SESSION['DataBalanco']				 = $_POST['DataBalanco'];
		$_SESSION['DataNegativa']				 = $_POST['DataNegativa'];
		$_SESSION['DataContratoEstatuto']= $_POST['DataContratoEstatuto'];

	}

# Variáveis do Formulário D #
	if ($Origem == "D") {
		$_SESSION['RegistroEntidade']= $_POST['RegistroEntidade'];
		$_SESSION['NomeEntidade']		 = strtoupper2(trim($_POST['NomeEntidade']));
		$_SESSION['DataVigencia']		 = $_POST['DataVigencia'];
		$_SESSION['RegistroTecnico'] = $_POST['RegistroTecnico'];
		$_SESSION['AutorizaNome']		 = strtoupper2(trim($_POST['AutorizaNome']));
		$_SESSION['AutorizaRegistro']= $_POST['AutorizaRegistro'];
		$_SESSION['AutorizaData']		 = $_POST['AutorizaData'];
		$_SESSION['CheckAutorizacao']= $_POST['CheckAutorizacao'];
		$_SESSION['CheckMateriais']	 = $_POST['CheckMateriais'];
		$_SESSION['CheckServicos']	 = $_POST['CheckServicos'];
		$_SESSION['EmailPopup']		   = $_POST['EmailPopup'];
		$_SESSION['Email']					 = $_SESSION['EmailPopup'];
	}

	if ($Origem == "E") {
		# Variáveis do Formulário E #

		$_SESSION['DDocumento']  = $_POST['DDocumento'];
		$_SESSION['tipoDoc'] = $_POST['tipoDoc'];
		$_SESSION['tipoDocDesc'] = $_POST['tipoDocDesc'];
		$_SESSION['obsDocumento'] = $_POST['obsDocumento'];
		$_SESSION['pesqAnoDoc'] = $_POST['pesqAnoDoc'];
		$_SESSION['CodDownload']= $_POST['CodDownload'];

	}

	} else {
		$_SESSION['Sequencial'] 		 = $_GET['Sequencial'];
		$_SESSION['InscricaoValida'] = $_GET['InscricaoValida'];
		$_SESSION['Irregularidade']	 = $_GET['Irregularidade'];
		$Origem											 = $_GET['Origem'];
		$Destino										 = $_GET['Destino'];
	}

	if (strlen($_SESSION['CPF_CNPJ']) == 11) {
	    $_SESSION['TipoCnpjCpf'] = "CPF";
	}
	elseif (strlen($_SESSION['CPF_CNPJ']) == 14) {
	    $_SESSION['TipoCnpjCpf'] = "CNPJ";
}

# Reseta variáveis que não são usadas quando não é licitação
	if ($_SESSION['TipoHabilitacao'] != 'L') {

// Origem C

		$_SESSION['CapSocial'] 					 = null;
		$_SESSION['CapIntegralizado'] 	 = null;
		$_SESSION['Patrimonio'] 				 = null;
		$_SESSION['IndLiqCorrente'] 		 = null;
		$_SESSION['IndLiqGeral'] 				 = null;
		$_SESSION['IndEndividamento'] 	 = null;
		$_SESSION['IndSolvencia'] 			 = null;
		$_SESSION['DataBalanco'] 				 = null;
		$_SESSION['DataNegativa'] 			 = null;
		$_SESSION['DataContratoEstatuto']= null;

// Origem D

		$_SESSION['Cumprimento']		 = null;
		$_SESSION['RegistroEntidade']= null;
		$_SESSION['NomeEntidade'] 	 = null;
		$_SESSION['DataVigencia'] 	 = null;
		$_SESSION['RegistroTecnico'] = null;
		$_SESSION['AutorizaNome'] 	 = null;
		$_SESSION['AutorizaRegistro']= null;
		$_SESSION['AutorizaData'] 	 = null;
}
		$NoCamposGerais = 9; //número de campos de ítens de formulário fora das abas (items gerais)

# Identifica o Programa para Erro de Banco de Dados #
		$_SESSION['ErroPrograma'] = __FILE__;

# Redireciona o programa de acordo com o botão #

if ($_SESSION['Botao'] == "Voltar") {
		$_SESSION['Botao'] = "";
		$Url = "CadGestaoFornecedorSelecionar.php?Sequencial=" . $_SESSION['Sequencial'] . "&Programa=C";
	if (!in_array($Url, $_SESSION['GetUrl'])) {
			$_SESSION['GetUrl'][] = $Url;
	 }
			header("location: " . $Url);
exit;
}

elseif ($_SESSION['Botao'] == "Excluir") {
		$_SESSION['Botao'] = "";
		$Url = "CadGestaoFornecedorExcluir.php?Sequencial=" . $_SESSION['Sequencial'] . "";
	if (!in_array($Url, $_SESSION['GetUrl'])) {
			$_SESSION['GetUrl'][] = $Url;
			 }
		header("location: " . $Url);
	exit;
}

elseif ($_SESSION['Botao'] == "Historico") {
		$_SESSION['Botao'] = "";
		$Url = "CadGestaoFornecedorHistorico.php?Sequencial=" . $_SESSION['Sequencial'] . "&NomePrograma=" . urlencode("CadGestaoFornecedor.php") . "";
	if (!in_array($Url, $_SESSION['GetUrl'])) {
			$_SESSION['GetUrl'][] = $Url;
		 }
		header("location: " . $Url);
	exit;
}


# Carrega as variáveis dos formulários #

if ($_SESSION['Critica'] == "") {

# Busca os Dados da Tabela de fornecedor de Acordo com o sequencial do fornecedor #

	$db = Conexao();
	$sql = " SELECT AFORCRCCGC, AFORCRCCPF, AFORCRIDEN, NFORCRORGU, NFORCRRAZS,
									NFORCRFANT, CCEPPOCODI, CCELOCCODI, EFORCRLOGR, AFORCRNUME,
									EFORCRCOMP, EFORCRBAIR, NFORCRCIDA, CFORCRESTA, AFORCRCDDD,
									AFORCRTELS, AFORCRNFAX, NFORCRMAIL, AFORCRCPFC, NFORCRCONT,
									NFORCRCARG, AFORCRDDDC, AFORCRTELC, AFORCRREGJ, DFORCRREGJ,
									AFORCRINES, AFORCRINME, AFORCRINSM, VFORCRCAPS, VFORCRCAPI,
									VFORCRPATL, VFORCRINLC, VFORCRINLG, DFORCRULTB, DFORCRCNFC,
									NFORCRENTP, AFORCRENTR, DFORCRVIGE, AFORCRENTT, DFORCRGERA,
									FFORCRCUMP, TFORCRULAT, FORN.CCOMLICODI, DFORCRANAL, FFORCRMEPP,
									VFORCRINDI, VFORCRINSO, NFORCRMAI2, FFORCRTIPO, DFORCRCONT, APREFOSEQU
						 FROM SFPC.TBFORNECEDORCREDENCIADO FORN
						 LEFT OUTER JOIN SFPC.TBCOMISSAOLICITACAO COM ON FORN.CCOMLICODI = COM.CCOMLICODI
						WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . "";

	$result = $db->query($sql);
	if (db :: isError($result)) {
		ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();

# Variáveis Formulário A #
	$_SESSION['CNPJ']				   = $Linha[0];
	$_SESSION['CPF']				   = $Linha[1];
	$_SESSION['MicroEmpresa']  = $Linha[44];
	$_SESSION['Identidade']	   = $Linha[2];
	$_SESSION['OrgaoUF']		   = $Linha[3];
	$_SESSION['RazaoSocial']   = $Linha[4];
	$_SESSION['NomeFantasia']  = $Linha[5];
	if ($Linha[6] != "") {
			$_SESSION['CEP']		   = $Linha[6];
	} else {
			$_SESSION['CEP']		   = $Linha[7];
			$_SESSION['Localidade']= "S";
	}

//colocar 8 digitos no CEP
if($_SESSION['CEP']!="" and !is_null($_SESSION['CEP'])){
	while (strlen($_SESSION['CEP']) < 8) {
		$_SESSION['CEP'] = "0" . $_SESSION['CEP'];
		}
}
		$_SESSION['CEPAntes']				= $_SESSION['CEP'];
		$_SESSION['Logradouro']			= $Linha[8];
		$_SESSION['Numero']					= $Linha[9];
		$_SESSION['Complemento']		= $Linha[10];
		$_SESSION['Bairro']					= $Linha[11];
		$_SESSION['Cidade']					= $Linha[12];
		$_SESSION['UF']							= $Linha[13];
		$_SESSION['DDD']						= $Linha[14];
		$_SESSION['Telefone']				= $Linha[15];
		$_SESSION['Fax']						= $Linha[16];
		$_SESSION['Email']					= $Linha[17];
		$_SESSION['Email2']					= $Linha[47];
		$_SESSION['CPFContato']			= $Linha[18];
		$_SESSION['NomeContato'] 		= $Linha[19];
		$_SESSION['CargoContato']		= $Linha[20];
		$_SESSION['DDDContato']			= $Linha[21];
		$_SESSION['TelefoneContato']= $Linha[22];
		$_SESSION['RegistroJunta'] 	= $Linha[23];
		$_SESSION['DataRegistro']		= $Linha[24];

	if (!is_null($_SESSION['DataRegistro']) and $_SESSION['DataRegistro'] != "") {
			$_SESSION['DataRegistro'] = substr($_SESSION['DataRegistro'], 8, 2) . "/" . substr($_SESSION['DataRegistro'], 5, 2) . "/" . substr($_SESSION['DataRegistro'], 0, 4);
	} else {
			$_SESSION['DataRegistro'] = "";
	}

# Dados dos sócios- início
		$_SESSION['NoSocios'] = 0;
		$_SESSION['SociosCPF_CNPJ'] = array ();
		$_SESSION['SociosNome'] = array ();
		$_SESSION['SocioNovoNome'] = "";
		$_SESSION['SocioNovoCPF'] = "";

	if ($_SESSION['CNPJ'] != 0 AND $_SESSION['CNPJ'] != "") {

			$sqlsocio = "	SELECT asoforcada, fsofortcad, nsofornome
											FROM SFPC.tbsociofornecedor sf
										 WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . "
								";
			$resultsocio = $db->query($sqlsocio);
	if (db :: isError($resultsocio)) {
			EmailErroSQL("Erro na consulta de sócios de fornecedores", __FILE__, __LINE__, "Erro na consulta de sócios de fornecedores", $sqlsocio, $resultsocio);
	}

			$rows = $resultsocio->numRows();
			$_SESSION['NoSocios'] = $rows;

			for ($itr = 0; $itr < $rows; $itr++) {
				$linhasocio = $resultsocio->fetchRow();
				$_SESSION['SociosCPF_CNPJ'][$itr] = $linhasocio[0];
				$_SESSION['SociosNome'][$itr] = $linhasocio[2];
			}
		}

# Dados dos sócios- fim

# Variáveis Formulário B #

	$_SESSION['InscEstadual'] = $Linha[25];
	$_SESSION['InscMercantil']= $Linha[26];
	$_SESSION['InscOMunic'] 	= $Linha[27];

# Variáveis Formulário C #

if ($_SESSION['CNPJ'] != 0) {
		$_SESSION['CapSocial'] 					= str_replace(".", ",", $Linha[28]);
		$_SESSION['CapIntegralizado'] 	= str_replace(".", ",", $Linha[29]);
		$_SESSION['Patrimonio'] 				= str_replace(".", ",", $Linha[30]);
		$_SESSION['IndLiqCorrente'] 		= str_replace(".", ",", $Linha[31]);
		$_SESSION['IndLiqGeral'] 				= str_replace(".", ",", $Linha[32]);
		$_SESSION['IndEndividamento'] 	= str_replace(".", ",", $Linha[45]);
		$_SESSION['IndSolvencia'] 			= str_replace(".", ",", $Linha[46]);
		$_SESSION['DataBalanco'] 			  = $Linha[33];

	if ($_SESSION['DataBalanco'] 		 != "") {
			$GLOBALS["DataNovaUltBalanco"]= $_SESSION['DataBalanco']; // guarda valor no formato do banco para melhor comparação de datas
			$_SESSION['DataBalanco'] 			= substr($_SESSION['DataBalanco'], 8, 2) . "/" . substr($_SESSION['DataBalanco'], 5, 2) . "/" . substr($_SESSION['DataBalanco'], 0, 4);
	}
			$_SESSION['DataNegativa'] 		= $Linha[34];
	if ($_SESSION['DataNegativa']!= "" and !is_null($_SESSION['DataNegativa'])) {
			$GLOBALS["DataNovaCertidaoNeg"] = $_SESSION['DataNegativa']; // guarda valor no formato do banco para melhor comparação de datas
			$_SESSION['DataNegativa'] = substr($_SESSION['DataNegativa'], 8, 2) . "/" . substr($_SESSION['DataNegativa'], 5, 2) . "/" . substr($_SESSION['DataNegativa'], 0, 4);
	}
			$_SESSION['DataContratoEstatuto'] = $Linha[49];
	if ($_SESSION['DataContratoEstatuto'] != "" and !is_null($_SESSION['DataContratoEstatuto'])) {
			$_SESSION['DataContratoEstatuto'] = substr($_SESSION['DataContratoEstatuto'], 8, 2) . "/" . substr($_SESSION['DataContratoEstatuto'], 5, 2) . "/" . substr($_SESSION['DataContratoEstatuto'], 0, 4);
	}
}

# Variáveis Formulário D #

	$_SESSION['NomeEntidade'] 	  = $Linha[35];
	$_SESSION['RegistroEntidade'] = $Linha[36];
	$_SESSION['DataVigencia'] 	  = $Linha[37];
	if ($_SESSION['DataVigencia']!= "") {
			$_SESSION['DataVigencia'] = substr($_SESSION['DataVigencia'], 8, 2) . "/" . substr($_SESSION['DataVigencia'], 5, 2) . "/" . substr($_SESSION['DataVigencia'], 0, 4);
	}
	$_SESSION['RegistroTecnico'] 	= $Linha[38];
	$_SESSION['DataInscricao']		= substr($Linha[39], 8, 2) . "/" . substr($Linha[39], 5, 2) . "/" . substr($Linha[39], 0, 4);
	$_SESSION['Cumprimento']			= $Linha[40];
	$_SESSION['DataAlteracao']		= substr($Linha[41], 8, 2) . "/" . substr($Linha[41], 5, 2) . "/" . substr($Linha[41], 0, 4);
	$_SESSION['ComissaoResp']			= $Linha[42];

		if ($Linha[43] <> "") {
				$_SESSION['DataAnaliseDoc'] = substr($Linha[43], 8, 2) . "/" . substr($Linha[43], 5, 2) . "/" . substr($Linha[43], 0, 4);
		} else {
				$_SESSION['DataAnaliseDoc'] = "";
		}
	$_SESSION['TipoHabilitacao']  = $Linha[48];

# Variáveis Formulário E #
	$_SESSION['SequencialPre']	= $Linha[50];
	// Kim #222833
	// Add uma sql na linha 541;
	// carrega arquivos cadastrados
	$db = Conexao();
	$sql = "  SELECT doc.cfdocusequ, doc.aprefosequ, doc.aforcrsequ, doc.afdocuanoa, 
				doc.cfdoctcodi, doc.efdocunome, doc.ifdocuarqu, doc.ffdocuforn, 
				doc.tfdocuanex, doc.ffdocusitu, doc.cusupocodi, doc.tfdoctulat,
				(SELECT h.cfdocscodi
				FROM sfpc.tbfornecedordocumentohistorico h
				where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as situacao, 
				(SELECT h.efdochobse
				FROM sfpc.tbfornecedordocumentohistorico h
				where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as observacao, 

				t.efdoctdesc, 

				(SELECT h.cusupocodi
				FROM sfpc.tbfornecedordocumentohistorico h
				where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as usuarioUltimaAlt, 
				(SELECT u.eusuporesp
				FROM sfpc.tbfornecedordocumentohistorico h
				join sfpc.tbusuarioportal u on h.cusupocodi = u.cusupocodi
				where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as nomeUsuUltimaAlt, 
				
				(SELECT u.eusuporesp FROM sfpc.tbusuarioportal u where u.cusupocodi = doc.cusupocodi) as nomeUsuAnex, 
				(SELECT h.tfdochulat
				FROM sfpc.tbfornecedordocumentohistorico h
				where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as datahoraUltimaAlt 
			FROM sfpc.tbfornecedordocumento doc
			join sfpc.tbfornecedordocumentotipo t ON t.cfdoctcodi = doc.cfdoctcodi
			WHERE aforcrsequ = " . $_SESSION['Sequencial'];
			if($_SESSION['SequencialPre']){
				$sql .= " OR aprefosequ = " . $_SESSION['SequencialPre'];
			}
			$sql .= " AND ffdocusitu = 'A' order by tfdoctulat DESC";
	
	$result = $db->query($sql);
	if (db :: isError($result)) {
		ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
	} else {
		unset($_SESSION['Arquivos_Upload']);
		//$resultado = $result->fetchRow();
		while ($linha = $result->fetchRow()) {
				//verifica se quem cadastrou foi PCR ou o próprio fornecedor
				$nomeUsuAnex = '';
				$nomeUsuUltAlt = '';
				if($linha[7] == 'S'){

					if ($_SESSION['CPF_CNPJ'] != "") {
						if (strlen($_SESSION['CPF_CNPJ']) == 14) {
							$nomeUsuAnex = substr($_SESSION['CPF_CNPJ'],0,2).".".substr($_SESSION['CPF_CNPJ'],2,3).".".substr($_SESSION['CPF_CNPJ'],5,3)."/".substr($_SESSION['CPF_CNPJ'],8,4)."-".substr($_SESSION['CPF_CNPJ'],12,2);
						} else {
							$nomeUsuAnex = substr($_SESSION['CPF_CNPJ'],0,3).".".substr($_SESSION['CPF_CNPJ'],3,3).".".substr($_SESSION['CPF_CNPJ'],6,3)."-".substr($_SESSION['CPF_CNPJ'],9,2);
						}
					}

					//Usuário que fez a última alteração
					if($linha[15]>0){
						$nomeUsuUltAlt = 'PCR - '.$linha[16];
					}else{
						$nomeUsuUltAlt = $nomeUsuAnex;
					}

				}else{
					// KIM #222833
					// pega o usuario que faz o upload.
					$nomeUsuAnex = 'PCR - '.$linha[17];

					//Usuário que fez a última alteração
					$nomeUsuUltAlt = 'PCR - '.$linha[16];

				}
			
			$_SESSION['Arquivos_Upload']['nome'][] = $linha[5];
			$_SESSION['Arquivos_Upload']['situacao'][] = 'existente'; // situacao pode ser: novo, existente, cancelado e excluido
			$_SESSION['Arquivos_Upload']['codigo'][] = $linha[0]; // como é um arquivo novo, ainda nao possui código
			$_SESSION['Arquivos_Upload']['tipoCod'][] = $linha[4]; 
			$_SESSION['Arquivos_Upload']['tipoDocumentoDesc'][] = $linha[14]; 
			$_SESSION['Arquivos_Upload']['observacao'][] = $linha[13]; 
			$_SESSION['Arquivos_Upload']['conteudo'][] = $linha[6]; 
			$_SESSION['Arquivos_Upload']['anoAnex'][] = $linha[3];
			$_SESSION['Arquivos_Upload']['dataHora'][] = formatarDataHora($linha[8]); 

			$_SESSION['Arquivos_Upload']['codUsuarioUltAlt'][] = $linha[15];
			$_SESSION['Arquivos_Upload']['usuarioUltAlt'][] = $nomeUsuUltAlt;
			$_SESSION['Arquivos_Upload']['usuarioAnex'][] = $nomeUsuAnex;
			$_SESSION['Arquivos_Upload']['externo'][] = $linha[7];

			$_SESSION['Arquivos_Upload']['dataHoraUltAlt'][] = formatarDataHora($linha[18]); 
			$_SESSION['Arquivos_Upload']['situacaoHist'][] = $linha[12]; 

		}
	}


	}






# Busca os Dados da Tabela de Situação de acordo com o sequencial do Fornecedor #

	$sql = "SELECT A.DFORSISITU, B.CFORTSCODI, A.EFORSIMOTI, A.DFORSIEXPI
					  FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B
					 WHERE A.AFORCRSEQU = " . $_SESSION['Sequencial'] . "
					   AND A.CFORTSCODI = B.CFORTSCODI ";
	$sql .= " ORDER BY A.DFORSISITU DESC"; //ordenar por datas em que a pessoa ficou com a situação. Usa a situação mais recente como atual
	//$sql   .= " ORDER BY A.TFORSIULAT DESC"; //ordenar por datas em que os registros de situações foram inseridos. Usa o registro mais recente como atual
	$result = $db->query($sql);

	if (db :: isError($result)) {
		ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
	} else {
		$Linha = $result->fetchRow();

		$_SESSION['DataSituacao'] 		= substr($Linha[0], 8, 2) . "/" . substr($Linha[0], 5, 2) . "/" . substr($Linha[0], 0, 4);
		$_SESSION['DataSituacaoAntes']= $_SESSION['DataSituacao'];
		$_SESSION['Situacao']				  = $Linha[1];
		$_SESSION['SituacaoAntes']		= $Linha[1];
		$_SESSION['Motivo']						= strtoupper2($Linha[2]);
		$_SESSION['DataExpiracao']		= $Linha[3];

		if (($_SESSION['Situacao'] == 3) and ($_SESSION['DataExpiracao'] >= date("Y-m-d"))) {
				$_SESSION['Botao'] = "";
				$Url = "CadGestaoFornecedorSuspenso.php?Sequencial=" . $_SESSION['Sequencial'] . "";
				if (!in_array($Url, $_SESSION['GetUrl'])) {
					$_SESSION['GetUrl'][] = $Url;
		}
			  header("location: " . $Url);
			  exit;
				} else {

		if (($_SESSION['Situacao'] == 3) and ($_SESSION['DataExpiracao'] != "" and $_SESSION['DataExpiracao'] < date("Y-m-d"))) {

# Insere outra Situação do Fornecedor Cadastrado #

				$sql = "INSERT INTO SFPC.TBFORNSITUACAO ( ";
				$sql .= "AFORCRSEQU, CFORTSCODI, DFORSISITU,  EFORSIMOTI, ";
				$sql .= "DFORSIEXPI, CGREMPCODI, CUSUPOCODI, TFORSIULAT";
				$sql .= ") VALUES ( ";
				$sql .= "" . $_SESSION['Sequencial'] . ", 1, '" . date("Y-m-d") . "', NULL, ";
				$sql .= "NULL, " . $_SESSION['_cgrempcodi_'] . ", " . $_SESSION['_cusupocodi_'] . ", '" . date("Y-m-d H:i:s") . "' )";

				$result = $db->query($sql);
				if (db :: isError($result)) {
					$db->query("ROLLBACK");
					ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
					$_SESSION['Critica'] == "";
					$Url = "CadGestaoFornecedor.php?Sequencial=" . $_SESSION['Sequencial'] . "";
					if (!in_array($Url, $_SESSION['GetUrl'])) {
						$_SESSION['GetUrl'][] = $Url;
					}
					header("location: " . $Url);
					exit;
				}
			}

			if ($_SESSION['DataExpiracao'] != "") {
				$_SESSION['DataExpiracao'] = substr($Linha[3], 8, 2) . "/" . substr($Linha[3], 5, 2) . "/" . substr($Linha[3], 0, 4);
			}

# Busca os Dados da Tabela de Conta Bancária de acordo com o sequencial do Fornecedor #

			$sql =  "SELECT CFORCBBANC, CFORCBAGEN, CFORCBCONT, TFORCBULAT ";
			$sql .= "  FROM SFPC.TBFORNCONTABANCARIA ";
			$sql .= " WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . " ";
			$sql .= " ORDER BY TFORCBULAT";

			$result = $db->query($sql);
			if (db :: isError($result)) {
				ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
			} else {
				$Rows = $result->numRows();
				for ($i = 0; $i < $Rows; $i++) {
					$Linha = $result->fetchRow();
					if ($i == 0) {
						$_SESSION['Banco1'] = $Linha[0];
						$_SESSION['Agencia1'] = $Linha[1];
						$_SESSION['ContaCorrente1'] = $Linha[2];
						//colocar 3 digitos no Banco1
						while (strlen($_SESSION['Banco1']) < 3) {
							$_SESSION['Banco1'] = "0" . $_SESSION['Banco1'];
						}
						//colocar 5 digitos no Agencia1
						while (strlen($_SESSION['Agencia1']) < 5) {
							$_SESSION['Agencia1'] = "0" . $_SESSION['Agencia1'];
						}
					} else {
						$_SESSION['Banco2'] = $Linha[0];
						$_SESSION['Agencia2'] = $Linha[1];
						$_SESSION['ContaCorrente2'] = $Linha[2];
						//colocar 3 digitos no Banco2
						while (strlen($_SESSION['Banco2']) < 3) {
							$_SESSION['Banco2'] = "0" . $_SESSION['Banco2'];
						}
						//colocar 5 digitos no Agencia2
						while (strlen($_SESSION['Agencia2']) < 5) {
							$_SESSION['Agencia2'] = "0" . $_SESSION['Agencia2'];
						}
					}
				}
			}

			$Cadastrado = "HABILITADO";
// Variáveis informando os motivos de inabilitação

			$GLOBALS["InabilitacaoCertidaoObrigatoria"] = false;
			$GLOBALS["InabilitacaoUltBalanco"] = false;
			$GLOBALS["InabilitacaoCertidaoNeg"] = false;
			//(Heraldo)
			$GLOBALS["SemDemonstracoesContaveis"] = false;
				
			If ($_SESSION['TipoHabilitacao'] == "L") {
				# Verifica a Validação das Certidões do Fornecedor #
				$sql =  "SELECT A.CTIPCECODI, A.ETIPCEDESC, B.DFORCEVALI ";
				$sql .= "  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBFORNECEDORCERTIDAO B ";
				$sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI ";
				$sql .= "   AND B.AFORCRSEQU = " . $_SESSION['Sequencial'] . " ";
				$sql .= " ORDER BY B.DFORCEVALI DESC";

				$result = $db->query($sql);
				if (db :: isError($result)) {
					ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
					$Rows = $result->numRows();
					for ($i = 0; $i < $Rows; $i++) {
						$Linha = $result->fetchRow();
						if ($Linha[2] < date("Y-m-d")) {
	                            
							$_SESSION['Cadastrado'] = "INABILITADO";
							$GLOBALS["InabilitacaoCertidaoObrigatoria"] = true;
						} else {
							$_SESSION['Cadastrado'] = "HABILITADO";
						}
					}
				}

				# Verifica também se a data de balanço anual está no prazo # 
				if ( !empty($GLOBALS["DataNovaUltBalanco"]) and !empty($_SESSION['MicroEmpresa'] ) ) {
					if ($GLOBALS["DataNovaUltBalanco"] < prazoUltimoBalanço()->format('Y-m-d')) {
						//$_SESSION['Cadastrado'] = "INABILITADO";
						$GLOBALS["InabilitacaoUltBalanco"] = true;
	  	    		}
				}
  	    	

				$data_menos_prazo = prazoCertidaoNegDeFalencia()->format('Y-m-d');
				
				//if ($GLOBALS["DataNovaCertidaoNeg"] < $data_menos_prazo) {
				if ($GLOBALS["DataNovaCertidaoNeg"] != "" && $GLOBALS["DataNovaCertidaoNeg"] < $data_menos_prazo) {
					$_SESSION['Cadastrado'] = "INABILITADO";
					$GLOBALS["InabilitacaoCertidaoNeg"] = true;
				}
			} else {
				$_SESSION['Cadastrado'] = "HABILITADO";
			}

# Verifica se já Existe Data de CHF #

			$sql =  "SELECT DFORCHGERA, DFORCHVALI FROM SFPC.TBFORNECEDORCHF ";
			$sql .= " WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . " ";
			$result = $db->query($sql);

			if (db :: isError($result)) {
				ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
			} else {
				$Rows = $result->numRows();
				if ($Rows != 0) {
					$Linha = $result->fetchRow();
					$_SESSION['DataGeracaoCHF'] = DataBarra($Linha[0]);
					$_SESSION['DataGeracaoCHFAntes'] = $_SESSION['DataGeracaoCHF'];
					$_SESSION['DataValidadeCHF'] = DataBarra($Linha[1]);
				} else {
					$_SESSION['DataGeracaoCHF'] = "";
					$_SESSION['DataValidadeCHF'] = "";
				}
			}

			
# Verifica de CHF simplificado sem demonstrações contáveis (Heraldo)
		  	if ( !empty( $_SESSION['MicroEmpresa']) and empty( $_SESSION['DataBalanco']) ) {
				$GLOBALS["SemDemonstracoesContaveis"] = true;
		      }
				
			
			
			
			
# Verifica se Existe Ocorrência #

			$sql  = "SELECT COUNT(*) FROM SFPC.TBFORNECEDOROCORRENCIA ";
			$sql .= " WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . " ";

			$result = $db->query($sql);

			if (db :: isError($result)) {
				ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
			} else {
				$Ocorrencias = $result->fetchRow();
				$_SESSION['Ocorrencias'] = $Ocorrencias[0];
			}

			$db->disconnect();



			
			
			
			

# Verifica se o Fornecedor está Regular na Prefeitura #

			if ($_SESSION['Irregularidade'] == "") {
				if ($_SESSION['CNPJ'] != "") {
					$_SESSION['TipoDoc'] = 1;
					$_SESSION['CPF_CNPJ'] = $_SESSION['CNPJ'];
				}
				elseif ($_SESSION['CPF'] != "") {
					$_SESSION['TipoDoc'] = 2;
					$_SESSION['CPF_CNPJ'] = $_SESSION['CPF'];
				}
				$NomePrograma = urlencode("CadGestaoFornecedor.php");
				$Url = "fornecedores/RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&TipoDoc=" . $_SESSION['TipoDoc'] . "&Sequencial=" . $_SESSION['Sequencial'] . "&Destino=&CPF_CNPJ=" . $_SESSION['CPF_CNPJ'] . "";
				if (!in_array("/oracle/" . $Url, $_SESSION['GetUrl'])) {
					$_SESSION['GetUrl'][] = $Url;
				}
				Redireciona($Url);
				exit;
			}
			$_SESSION['Critica'] = 1;
		}
	}
}

# Chamada das Criticas das Abas #

if ($Origem == "A" or $Origem == "") {
		$_SESSION['DestinoCons'] = $Destino;
		CriticaAbaHabilitacao();
}

elseif ($Origem == "C") {
				CriticaAbaQualificEconFinanceira();
}

elseif ($Origem == "B") {
				$_SESSION['DestinoInsc'] = $Destino;
				CriticaAbaRegularidadeFiscal();
}
elseif ($Origem == "D") {
				CriticaAbaQualificTecnica();
}
elseif ($Origem == "E" ) {

				CriticaAbaDocumentos();
}

# Aba de Habilitação Jurídica  - Formulário A #

if ($Origem == "A" or $Origem == "") {
	if ($_SESSION['Botao'] == "A") {
			$Destino = "B";
	}
	ExibeAbas($Destino);
}

# Aba de Regularidade Fiscal - Formulário B #

if ($Origem == "B") {
	if ($_SESSION['Botao'] == "B") {
			$Destino = "C";
	}
			ExibeAbas($Destino);
}

# Aba de Qualificação Econômica e Financeira - Formulário C #

if ($Origem == "C") {
	if ($_SESSION['Botao'] == "C") {
			$Destino = "D";
	}
			ExibeAbas($Destino);
}

# Aba de Qualificação Técnica - Formulário D #
if ($Origem == "D") {
	if ($_SESSION['Botao'] == "D") {
		$Destino = "E";
	}
 	ExibeAbas($Destino);
}


# Salva Dados nas Tabelas #
if ($Origem == "E") {
	
	if ($_SESSION['Botao'] == "Alterar") {
		
# Verifica Critica do Formulário B #

	//CriticaAbaRegularidadeFiscal();

# Verifica Critica do Formulário C #

	//CriticaAbaQualificEconFinanceira();

# Verifica Critica do Formulário D #

	//CriticaAbaQualificTecnica();

//Verifica Critica do Formulário E

	//CriticaAbaDocumentos();

# Efetua as alterações dos dados do Fornecedor #

	$db = Conexao();
	$db->query("BEGIN TRANSACTION");
	$DataAtual = date("Y-m-d H:i:s");

# Coloca null para o valores não obrigatórios e que não foram digitados #

	if ($_SESSION['ComissaoResp'] == "") {
			$ComissaoResp = "NULL";
	} else {
			$ComissaoResp = "'" . $_SESSION['ComissaoResp'] . "'";
		}
		if ($_SESSION['DataAnaliseDoc'] == "") {
				$DataAnaliseDocInv = "NULL";
		} else {
				$DataAnaliseDocInv = "'" . substr($_SESSION['DataAnaliseDoc'], 6, 4) . "-" . substr($_SESSION['DataAnaliseDoc'], 3, 2) . "-" . substr($_SESSION['DataAnaliseDoc'], 0, 2) . "'";
			}

# Variáveis do Formulário A #

		if ($_SESSION['MicroEmpresa'] == "") {
				$MicroEmpresa = "NULL";
		} else {
				$MicroEmpresa = "'" . $_SESSION['MicroEmpresa'] . "'";
			}
		if ($_SESSION['Identidade'] == "") {
				$Identidade = "NULL";
		} else {
				$Identidade = "'" . $_SESSION['Identidade'] . "'";
			}
		if ($_SESSION['OrgaoUF'] == "") {
				$OrgaoUF = "NULL";
		} else {
				$OrgaoUF = "'" . $_SESSION['OrgaoUF'] . "'";
			}
		if ($_SESSION['NomeFantasia'] == "") {
				$NomeFantasia = "NULL";
		} else {
				$NomeFantasia = "'" . $_SESSION['NomeFantasia'] . "'";
			}
		if ($_SESSION['DDD'] == "") {
				$DDD = "NULL";
		} else {
				$DDD = "'" . $_SESSION['DDD'] . "'";
			}
		if ($_SESSION['Numero'] == "") {
				$Numero = "NULL";
		} else {
				$Numero = $_SESSION['Numero'];
			}
		if ($_SESSION['Complemento'] == "") {
				$Complemento = "NULL";
		} else {
				$Complemento = "'" . $_SESSION['Complemento'] . "'";
			}
		if ($_SESSION['Telefone'] == "") {
				$Telefone = "NULL";
		} else {
				$Telefone = "'" . $_SESSION['Telefone'] . "'";
			}

//if( $_SESSION['Email']           == "" ){ $Email           = "NULL"; }else{ $Email           = "'".$_SESSION['Email']."'"; }
//if( $_SESSION['Email2']           == "" ){ $Email2           = "NULL"; }else{ $Email2           = "'".$_SESSION['Email2']."'"; }

		if ($_SESSION['Fax'] == "") {
				$Fax = "NULL";
		} else {
				$Fax = "'" . $_SESSION['Fax'] . "'";
			}
		if ($_SESSION['NomeContato'] == "") {
				$NomeContato = "NULL";
		} else {
				$NomeContato = "'" . $_SESSION['NomeContato'] . "'";
			}
		if ($_SESSION['CPFContato'] == "") {
				$CPFContato = "NULL";
		} else {
				$CPFContato = "'" . $_SESSION['CPFContato'] . "'";
			}
		if ($_SESSION['CargoContato'] == "") {
				$CargoContato = "NULL";
		} else {
				$CargoContato = "'" . $_SESSION['CargoContato'] . "'";
			}
		if ($_SESSION['DDDContato'] == "") {
				$DDDContato = "NULL";
		} else {
				$DDDContato = $_SESSION['DDDContato'];
			}
		if ($_SESSION['TelefoneContato'] == "") {
				$TelefoneContato = "NULL";
		} else {
				$TelefoneContato = "'" . $_SESSION['TelefoneContato'] . "'";
			}
		if ($_SESSION['Situacao'] == "") {
				$Situacao = "NULL";
		} else {
				$Situacao = "'" . $_SESSION['Situacao'] . "'";
			}
		if ($_SESSION['DataRegistro'] == "" or is_null($_SESSION['DataRegistro'])) {
				$DataRegistroInv = "NULL";
		} else {
				$DataRegistroInv = "'" . substr($_SESSION['DataRegistro'], 6, 4) . "-" . substr($_SESSION['DataRegistro'], 3, 2) . "-" . substr($_SESSION['DataRegistro'], 0, 2) . "'";
			}
		if ($_SESSION['RegistroJunta'] == "" or is_null($_SESSION['RegistroJunta'])) {
				$RegistroJunta = "NULL";
		} else {
				$RegistroJunta = "'" . $_SESSION['RegistroJunta'] . "'";
			}
		if ($_SESSION['Email'] == "" or $_SESSION['Email'] == "NULL") {
				$Email = "NULL";
		} else {
				$Email = "'" . $_SESSION['Email'] . "'";
			}
		if ($_SESSION['Email2'] == "" or $_SESSION['Email2'] == "NULL") {
				$Email2 = "NULL";
		} else {
				$Email2 = "'" . $_SESSION['Email2'] . "'";
			}

# Variáveis do Formulário B #

		if ($_SESSION['InscMercantil'] == "") {
				$InscMercantil = "NULL";
		} else {
				$InscMercantil = $_SESSION['InscMercantil'];
			}
		if ($_SESSION['InscEstadual'] == "") {
				$InscEstadual = "NULL";
		} else {
				$InscEstadual = $_SESSION['InscEstadual'];
			}
		if ($_SESSION['InscOMunic'] == "") {
				$InscOMunic = "NULL";
		} else {
				$InscOMunic = $_SESSION['InscOMunic'];
			}

# Variáveis do Formulário C #

		if ($_SESSION['CapSocial'] == "") {
				$CapSocial = "NULL";
		} else {
				$CapSocial = str_replace(",", ".", $_SESSION['CapSocial']);
			}
		if ($_SESSION['Patrimonio'] == "") {
				$Patrimonio = "NULL";
		} else {
				$Patrimonio = str_replace(",", ".", $_SESSION['Patrimonio']);
			}
		if ($_SESSION['CapIntegralizado'] == "") {
				$CapIntegralizado = "NULL";
		} else {
				$CapIntegralizado = str_replace(",", ".", $_SESSION['CapIntegralizado']);
			}
		if ($_SESSION['IndLiqCorrente'] == "") {
				$IndLiqCorrente = "NULL";
		} else {
				$IndLiqCorrente = str_replace(",", ".", $_SESSION['IndLiqCorrente']);
			}
		if ($_SESSION['IndLiqGeral'] == "") {
				$IndLiqGeral = "NULL";
		} else {
				$IndLiqGeral = str_replace(",", ".", $_SESSION['IndLiqGeral']);
			}
		if ($_SESSION['IndEndividamento'] == "") {
				$IndEndividamento = "NULL";
		} else {
				$IndEndividamento = str_replace(",", ".", $_SESSION['IndEndividamento']);
			}
		if ($_SESSION['IndSolvencia'] == "") {
				$IndSolvencia = "NULL";
		} else {
				$IndSolvencia = str_replace(",", ".", $_SESSION['IndSolvencia']);
			}
		if ($_SESSION['DataBalanco'] == "" or $_SESSION['DataBalanco'] == "//") {
				$DataBalanco = "NULL";
		} else {
				$DataBalanco = "'" . substr($_SESSION['DataBalanco'], 6, 4) . "-" . substr($_SESSION['DataBalanco'], 3, 2) . "-" . substr($_SESSION['DataBalanco'], 0, 2) . "'";
			}
		if ($_SESSION['DataNegativa'] == "" or $_SESSION['DataNegativa'] == "//") {
				$DataNegativa = "NULL";
		} else {
				$DataNegativa = "'" . substr($_SESSION['DataNegativa'], 6, 4) . "-" . substr($_SESSION['DataNegativa'], 3, 2) . "-" . substr($_SESSION['DataNegativa'], 0, 2) . "'";
			}
		if ($_SESSION['DataContratoEstatuto'] == "" or $_SESSION['DataContratoEstatuto'] == "//") {
				$DataContratoEstatuto = "NULL";
		} else {
				$DataContratoEstatuto = "'" . substr($_SESSION['DataContratoEstatuto'], 6, 4) . "-" . substr($_SESSION['DataContratoEstatuto'], 3, 2) . "-" . substr($_SESSION['DataContratoEstatuto'], 0, 2) . "'";
			}

# Variáveis do Formulário D #

		if ($_SESSION['NomeEntidade'] == "") {
				$NomeEntidade = "NULL";
		} else {
				$NomeEntidade = "'" . $_SESSION['NomeEntidade'] . "'";
			}
		if ($_SESSION['RegistroEntidade'] == "") {
				$RegistroEntidade = "NULL";
		} else {
				$RegistroEntidade = $_SESSION['RegistroEntidade'];
			}
		if ($_SESSION['DataVigencia'] == "" or $_SESSION['DataVigencia'] == "//") {
				$DataVigencia = "NULL";
		} else {
				$DataVigencia = "'" . substr($_SESSION['DataVigencia'], 6, 4) . "-" . substr($_SESSION['DataVigencia'], 3, 2) . "-" . substr($_SESSION['DataVigencia'], 0, 2) . "'";
			}
		if ($_SESSION['RegistroTecnico'] == "") {
				$RegistroTecnico = "NULL";
		} else {
				$RegistroTecnico = $_SESSION['RegistroTecnico'];
			}
		if ($_SESSION['Cumprimento'] == "" or is_null($_SESSION['Cumprimento'])) {
				$Cumprimento = "NULL";
		} else {
				$Cumprimento = "'" . $_SESSION['Cumprimento'] . "'";
		}
		if ($_SESSION['CEP'] == "" or is_null($_SESSION['CEP'])) {
			$CEP = "NULL";
		} else {
			$CEP = "'" . $_SESSION['CEP'] . "'";
		}		

# Atualiza a tabela de Fornecedor #

		$sql = " UPDATE SFPC.TBFORNECEDORCREDENCIADO
							  SET AFORCRIDEN = $Identidade, NFORCRORGU = $OrgaoUF,
										NFORCRRAZS = '" . $_SESSION['RazaoSocial'] . "',
										NFORCRFANT = $NomeFantasia, ";
			if ($_SESSION['Localidade'] == "S") {
					$sql .= " CCEPPOCODI =  NULL , CCELOCCODI = " . $CEP . ", ";
			} else {
					$sql .= " CCEPPOCODI =  " . $CEP . " , CCELOCCODI = NULL, ";
				}
		$sql .= " EFORCRLOGR = '" . $_SESSION['Logradouro'] . "',
							AFORCRNUME = $Numero,
							EFORCRCOMP = $Complemento,
							EFORCRBAIR = '" . $_SESSION['Bairro'] . "',
							NFORCRCIDA = '" . $_SESSION['Cidade'] . "',
							CFORCRESTA = '" . $_SESSION['UF'] . "',
							AFORCRCDDD = " . $DDD . ",
							AFORCRTELS = $Telefone,
							AFORCRNFAX = $Fax,
							NFORCRMAIL = $Email,
							NFORCRMAI2 = $Email2,
							AFORCRCPFC = $CPFContato,
							NFORCRCONT = $NomeContato,
							NFORCRCARG = $CargoContato,
							AFORCRDDDC = $DDDContato,
							AFORCRTELC = $TelefoneContato,
							AFORCRREGJ = $RegistroJunta,
							DFORCRREGJ = $DataRegistroInv,
							AFORCRINES = $InscEstadual,
							AFORCRINME = $InscMercantil,
							AFORCRINSM = $InscOMunic,
							VFORCRCAPS = $CapSocial,
							VFORCRCAPI = $CapIntegralizado,
							VFORCRINLG = $IndLiqGeral,
							DFORCRULTB = $DataBalanco,
							VFORCRPATL = $Patrimonio,
							VFORCRINLC = $IndLiqCorrente,
							DFORCRCNFC = $DataNegativa,
							NFORCRENTP = $NomeEntidade,
							DFORCRCONT = $DataContratoEstatuto,
							AFORCRENTR = $RegistroEntidade,
							AFORCRENTT = $RegistroTecnico,
							DFORCRVIGE = $DataVigencia,
							CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . ",
							CUSUPOCODI = " . $_SESSION['_cusupocodi_'] . ",
							FFORCRCUMP = " . $Cumprimento . ",
							TFORCRULAT = '$DataAtual' ,
							CCOMLICODI = $ComissaoResp,
							DFORCRANAL = $DataAnaliseDocInv,
							FFORCRMEPP = $MicroEmpresa,
							VFORCRINDI = $IndEndividamento,
							VFORCRINSO = $IndSolvencia,
							FFORCRTIPO = '" . $_SESSION['TipoHabilitacao'] . "'
							WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . " ";
		

		$result = $db->query($sql);
		if (db :: isError($result)) {
				$db->query("ROLLBACK");
				ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
		} else {
			
# Excluindo e Inserindo Contas Bancárias #

		$sql = "DELETE FROM SFPC.TBFORNCONTABANCARIA WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . "  ";

		$result = $db->query($sql);
		if (db :: isError($result)) {
				$db->query("ROLLBACK");
				ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
		} else {
				if ($_SESSION['Banco1'] != "" and $_SESSION['Agencia1'] != "" and $_SESSION['ContaCorrente1'] != "") {
					$sql = "INSERT INTO SFPC.TBFORNCONTABANCARIA ( ";
					$sql .= "AFORCRSEQU, CFORCBBANC, CFORCBAGEN, CFORCBCONT, TFORCBULAT ";
					$sql .= ") VALUES ( ";
					$sql .= "" . $_SESSION['Sequencial'] . ", '" . $_SESSION['Banco1'] . "', '" . $_SESSION['Agencia1'] . "', '" . $_SESSION['ContaCorrente1'] . "', '$DataAtual' ) ";
					$result = $db->query($sql);
					if (db :: isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
					}
				}
				if ($_SESSION['Banco2'] != "" and $_SESSION['Agencia2'] != "" and $_SESSION['ContaCorrente2'] != "") {
					$sql = "INSERT INTO SFPC.TBFORNCONTABANCARIA ( ";
					$sql .= "AFORCRSEQU, CFORCBBANC, CFORCBAGEN, CFORCBCONT, TFORCBULAT ";
					$sql .= ") VALUES ( ";
					$sql .= "" . $_SESSION['Sequencial'] . ", '" . $_SESSION['Banco2'] . "', '" . $_SESSION['Agencia2'] . "', '" . $_SESSION['ContaCorrente2'] . "', '$DataAtual' ) ";
					$result = $db->query($sql);
					if (db :: isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
					}
				}
		}

# Excluindo e  Inserindo Sócios #

			if ($_SESSION['TipoCnpjCpf'] == "CNPJ") {
					$sql = "DELETE FROM SFPC.TBSOCIOFORNECEDOR WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . "";
					$result = $db->query($sql);
				if (db :: isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
					for ($i = 0; $i < $_SESSION['NoSocios']; $i++) {
						if (!is_null($_SESSION['SociosNome'][$i])) {
								$cpfnocaracteres = strlen($_SESSION['SociosCPF_CNPJ'][$i]);
								$tipocadastro = NULL;
							if ($cpfnocaracteres == 11) {
									$tipocadastro = "F";
							} else
								if ($cpfnocaracteres == 14) {
										$tipocadastro = "J";
								}
							if (is_null($tipocadastro)) {
									EmailErro("Erro na inclusão de sócios", __FILE__, __LINE__, "Erro na inclusão de sócios de fornecedores. CPF/CNPJ não está em um tamanho válido");
							} else {
									$sql = " INSERT INTO SFPC.TBSOCIOFORNECEDOR (
													 AFORCRSEQU, nsofornome, asoforcada, fsofortcad, tsoforulat
													 ) VALUES (
														 " . $_SESSION['Sequencial'] . ", '" . $_SESSION['SociosNome'][$i] . "', '" . $_SESSION['SociosCPF_CNPJ'][$i] . "', '" . $tipocadastro . "', '$DataAtual'
													 )
													   ";
									$result = $db->query($sql);
								if (db :: isError($result)) {
										$db->query("ROLLBACK");
										ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
								}
							}
						}
					}
				}
			}

# Excluindo e  Inserindo Certidões OBRIGATÓRIAS #

			if (count($_SESSION['CertidaoObrigatoria']) != 0) {
					$sql = "DELETE FROM SFPC.TBFORNECEDORCERTIDAO WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . "";
					$sql .= "AND CTIPCECODI IN (SELECT CTIPCECODI FROM SFPC.TBTIPOCERTIDAO WHERE FTIPCEOBRI = 'S')";

					$result = $db->query($sql);
				if (db :: isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
					for ($i = 0; $i < count($_SESSION['CertidaoObrigatoria']); $i++) {
						if ((!is_null($_SESSION['DataCertidaoOb'][$i])) && ($_SESSION['DataCertidaoOb'][$i] != "")) {
							$_SESSION['DataCertidaoOb'][$i] = substr($_SESSION['DataCertidaoOb'][$i], 6, 4) . "-" . substr($_SESSION['DataCertidaoOb'][$i], 3, 2) . "-" . substr($_SESSION['DataCertidaoOb'][$i], 0, 2);
							$sql = "INSERT INTO SFPC.TBFORNECEDORCERTIDAO ( ";
							$sql .= "AFORCRSEQU, CTIPCECODI, DFORCEVALI, TFORCEULAT ";
							$sql .= ") VALUES ( ";
							$sql .= "" . $_SESSION['Sequencial'] . ", " . $_SESSION['CertidaoObrigatoria'][$i] . ", '" . $_SESSION['DataCertidaoOb'][$i] . "', '$DataAtual' ) ";
							$result = $db->query($sql);
							if (db :: isError($result)) {
								$db->query("ROLLBACK");
								ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
							}
						}
					}
				}
			}

# Excluindo e  Inserindo Certidões COMPLEMENTARES #

			if (count($_SESSION['CertidaoComplementar']) != 0) {
					$sql = "DELETE FROM SFPC.TBFORNECEDORCERTIDAO WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . "";
					$sql .= "AND CTIPCECODI IN (SELECT CTIPCECODI FROM SFPC.TBTIPOCERTIDAO WHERE FTIPCEOBRI = 'N')";
					$result = $db->query($sql);
				if (db :: isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
					for ($i = 0; $i < count($_SESSION['CertidaoComplementar']); $i++) {
						if ((!is_null($_SESSION['DataCertidaoComp'][$i])) && ($_SESSION['DataCertidaoComp'][$i] != "")) {
							$_SESSION['DataCertidaoComp'][$i] = substr($_SESSION['DataCertidaoComp'][$i], 6, 4) . "-" . substr($_SESSION['DataCertidaoComp'][$i], 3, 2) . "-" . substr($_SESSION['DataCertidaoComp'][$i], 0, 2);
							$sql = "INSERT INTO SFPC.TBFORNECEDORCERTIDAO ( ";
							$sql .= "AFORCRSEQU, CTIPCECODI, DFORCEVALI, TFORCEULAT ";
							$sql .= ") VALUES ( ";
							$sql .= "" . $_SESSION['Sequencial'] . ", " . $_SESSION['CertidaoComplementar'][$i] . ", '" . $_SESSION['DataCertidaoComp'][$i] . "', '$DataAtual' ) ";
							$result = $db->query($sql);
							if (db :: isError($result)) {
									$db->query("ROLLBACK");
									ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
							}
						}
					}
				}
			}

# Inserindo Autorização #

			if (count($_SESSION['AutorizacaoNome']) != 0) {
					$sql = "DELETE FROM SFPC.TBFORNAUTORIZACAOESPECIFICA WHERE AFORCRSEQU = " . $_SESSION['Sequencial'];
					$result = $db->query($sql);
				if (db :: isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
					for ($i = 0; $i < count($_SESSION['AutorizacaoNome']); $i++) {
						if ((!is_null($_SESSION['AutorizacaoNome'][$i])) && ($_SESSION['AutorizacaoNome'][$i] != "")) {

							$AutorizacaoDataInv = substr($_SESSION['AutorizacaoData'][$i], 6, 4) . "-" . substr($_SESSION['AutorizacaoData'][$i], 3, 2) . "-" . substr($_SESSION['AutorizacaoData'][$i], 0, 2);
							$sql = "INSERT INTO SFPC.TBFORNAUTORIZACAOESPECIFICA ( ";
							$sql .= "AFORCRSEQU, NFORAENOMA, AFORAENUMA, DFORAEVIGE, TFORAEULAT ";
							$sql .= ") VALUES ( ";
							$sql .= "" . $_SESSION['Sequencial'] . ", '" . $_SESSION['AutorizacaoNome'][$i] . "', " . $_SESSION['AutorizacaoRegistro'][$i] . ", '$AutorizacaoDataInv', '$DataAtual' ) ";
							$result = $db->query($sql);
							if (db :: isError($result)) {
								$db->query("ROLLBACK");
								ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
							}
						}
					}
				}
			}

# Excluindo e Incluindo Grupos de Fornecimento #

			if (count($_SESSION['Materiais']) != 0 or count($_SESSION['Servicos']) != 0) {
					$sql = "DELETE FROM SFPC.TBGRUPOFORNECEDOR WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . " ";
					$result = $db->query($sql);
				if (db :: isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
					for ($i = 0; $i < count($_SESSION['Materiais']); $i++) {
						$GrupoMaterial = split("#", $_SESSION['Materiais'][$i]);
						$sql = "INSERT INTO SFPC.TBGRUPOFORNECEDOR ( ";
						$sql .= "CGRUMSCODI, AFORCRSEQU, TGRUFOULAT ";
						$sql .= ") VALUES ( ";
						$sql .= $GrupoMaterial[1] . ", " . $_SESSION['Sequencial'] . ", '$DataAtual' ) ";
						$result = $db->query($sql);
						if (db :: isError($result)) {
								$db->query("ROLLBACK");
								ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
						}
					}

					for ($i = 0; $i < count($_SESSION['Servicos']); $i++) {
							$GrupoServico = split("#", $_SESSION['Servicos'][$i]);
							$sql = "INSERT INTO SFPC.TBGRUPOFORNECEDOR ( ";
							$sql .= "CGRUMSCODI, AFORCRSEQU, TGRUFOULAT ";
							$sql .= ") VALUES ( ";
							$sql .= $GrupoServico[1] . ", " . $_SESSION['Sequencial'] . ", '$DataAtual' ) ";
							$result = $db->query($sql);
						if (db :: isError($result)) {
								$db->query("ROLLBACK");
								ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
						}
					}
				}
			}

			// DOCUMENTOS

			if (count($_SESSION['Arquivos_Upload']) != 0) {
				for ($i=0; $i< count($_SESSION['Arquivos_Upload']); $i++) {

					$arquivo = $_SESSION['Arquivos_Upload'];
					if($arquivo['situacao'][$i] == 'novo'){
						// fazer sql para trazer o sequencial
						$sql = ' SELECT cfdocusequ FROM SFPC.tbfornecedordocumento WHERE  1=1 ORDER BY cfdocusequ DESC limit 1';
						$seqDocumento = resultValorUnico(executarTransacao($db, $sql)) + 1;

						$anexo =  bin2hex($arquivo['conteudo'][$i]);

						$sqlAnexo = "INSERT INTO sfpc.tbfornecedordocumento
						(cfdocusequ, aprefosequ, aforcrsequ, afdocuanoa, cfdoctcodi, efdocunome, ifdocuarqu, ffdocuforn, tfdocuanex, ffdocusitu, cusupocodi, tfdoctulat)
						VALUES(".$seqDocumento.", NULL, ".$_SESSION['Sequencial'].",".$arquivo['anoAnex'][$i].", ".$arquivo['tipoCod'][$i].", '".$arquivo['nome'][$i]."', decode('".$anexo."','hex'), 'N', now(), 'A', ".$_SESSION['_cusupocodi_'].", now());
						";
						$resultAnexo = $db->query($sqlAnexo);
						
						if (db::isError($resultAnexo)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlAnexo");
						}else{
							//insere a fase do documento
							$sqlHist = "INSERT INTO sfpc.tbfornecedordocumentohistorico
									(cfdocusequ, cfdocscodi, efdochobse, tfdochcada, cusupocodi, tfdochulat)
									VALUES(".$seqDocumento.", 1, '".$arquivo['observacao'][$i]."', now(), ".$_SESSION['_cusupocodi_'].", now());

							";

							$resultHist = $db->query($sqlHist);
							
							if (db::isError($resultHist)) {
								$db->query("ROLLBACK");
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist");
							}

						}

					}elseif($arquivo['situacao'][$i] == 'excluido'){

						// Exclui todos os documentos antes de inserir os novos
						$sqlDelete = 'Delete FROM SFPC.tbfornecedordocumento FD ';
						$sqlDelete .= ' where FD.cfdocusequ = '.$arquivo['codigo'][$i];
						$resultDel= $db->query($sqlDelete);
							
						if (db::isError($resultDel)) {
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlDelete");
						}
						
					}elseif($arquivo['situacao'][$i] == 'existente'){

						if(($_POST['situacaoDoc'.$i] != $arquivo['situacaoDoc'][$i]) ){

							//insere a fase do documento
							$sqlHist = "INSERT INTO sfpc.tbfornecedordocumentohistorico
									(cfdocusequ, cfdocscodi, efdochobse, tfdochcada, cusupocodi, tfdochulat)
									VALUES(".$arquivo['codigo'][$i].", '".$_POST['situacaoDoc'.$i]."', '".$_POST['obsDocumento'.$i]."', now(), ".$_SESSION['_cusupocodi_'].", now());

							";

							$resultHist = $db->query($sqlHist);
							
							if (db::isError($resultHist)) {
								$db->query("ROLLBACK");
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist");
							}

						}


					}
				}
			}


# Altera a Situação do Fornecedor #
//if( $_SESSION['Situacao'] != 1 ){
# Atribui null as variáveis não obrigatórias #

			if ($_SESSION['Motivo'] == "") {
					$Motivo = "NULL";
			} else {
					$Motivo = "'" . $_SESSION['Motivo'] . "'";
				}
			if ($_SESSION['DataExpiracao'] == "") {
					$DataExpiracaoInv = "NULL";
			} else {
					$DataExpiracaoInv = "'" . substr($_SESSION['DataExpiracao'], 6, 4) . "-" . substr($_SESSION['DataExpiracao'], 3, 2) . "-" . substr($_SESSION['DataExpiracao'], 0, 2) . "'";
				}
					$dataAtual = date("Y-m-d");
			if ($_SESSION['Situacao'] == 1) {
					$DataSituacaoInv = substr($dataAtual, 0, 4) . "-" . substr($dataAtual, 5, 2) . "-" . substr($dataAtual, 8, 2);
			} else {
					$DataSituacaoInv = substr($_SESSION['DataSituacao'], 6, 4) . "-" . substr($_SESSION['DataSituacao'], 3, 2) . "-" . substr($_SESSION['DataSituacao'], 0, 2);
				}

			if ($_SESSION['SituacaoAntes'] != $_SESSION['Situacao']) {

# Verifica se a situação já esta cadastrada #

				$sql =  "SELECT AFORCRSEQU, CFORTSCODI, DFORSISITU FROM SFPC.TBFORNSITUACAO ";
				$sql .= " WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . " ";
				$sql .= "   AND DFORSISITU = '$DataSituacaoInv'";
				$res = $db->query($sql);
				if (db :: isError($res)) {
					ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
					$Rows = $res->numRows();
					if ($Rows == 0) {
						if ($_SESSION['DataSituacao'] == "") {
							$_SESSION['DataSituacao'] = date("Y-m-d");
						}

# Insere outra Situação do Fornecedor Cadastrado #

				$sql = "INSERT INTO SFPC.TBFORNSITUACAO ( ";
				$sql .= "AFORCRSEQU, CFORTSCODI, DFORSISITU,  EFORSIMOTI, ";
				$sql .= "DFORSIEXPI, CGREMPCODI, CUSUPOCODI, TFORSIULAT";
				$sql .= ") VALUES ( ";
				$sql .= "" . $_SESSION['Sequencial'] . ", " . $_SESSION['Situacao'] . ", '" . $DataSituacaoInv . "', $Motivo, ";
				$sql .= "$DataExpiracaoInv , " . $_SESSION['_cgrempcodi_'] . ", " . $_SESSION['_cusupocodi_'] . ", '$DataAtual' )";

				$result = $db->query($sql);
						if (db :: isError($result)) {
								$db->query("ROLLBACK");
								ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
						} else {
								$_SESSION['SituacaoAntes'] = $_SESSION['Situacao'];
							}
							} else {
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 1;
								$_SESSION['Mensagem'] = "Já foi informada Situação para esta data";
								ExibeAbas("A");
							}
						}
							} else {
								$DataSituacaoInv = substr($_SESSION['DataSituacao'], 6, 4) . "-" . substr($_SESSION['DataSituacao'], 3, 2) . "-" . substr($_SESSION['DataSituacao'], 0, 2);
								$DataSituacaoAntesInv = substr($_SESSION['DataSituacaoAntes'], 6, 4) . "-" . substr($_SESSION['DataSituacaoAntes'], 3, 2) . "-" . substr($_SESSION['DataSituacaoAntes'], 0, 2);

# Atualiza a Situação do Fornecedor Cadastrado #

			$sql = "UPDATE SFPC.TBFORNSITUACAO ";
			$sql .= "   SET EFORSIMOTI = $Motivo, DFORSIEXPI = $DataExpiracaoInv, ";
			$sql .= "       CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . ", CUSUPOCODI = " . $_SESSION['_cusupocodi_'] . ", ";
			$sql .= "       TFORSIULAT = '$DataAtual' ";
				if (($_SESSION['Situacao'] != 1) && ($_SESSION['DataSituacao'] != $_SESSION['DataSituacaoAntes'])) {
						 $sql .= ", DFORSISITU = '" . $DataSituacaoInv . "' ";
				}
			$sql .= " WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . " AND CFORTSCODI = " . $_SESSION['Situacao'] . " ";
			$sql .= "   AND DFORSISITU = '" . $DataSituacaoAntesInv . "'";

			$result = $db->query($sql);
				if (db :: isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				}
			}
			//}

				if ($_SESSION['DataGeracaoCHF'] != "") {

# Verifica se a data de CHF já esta cadastrada #

				$sql  = "SELECT * FROM SFPC.TBFORNECEDORCHF ";
				$sql .= " WHERE AFORCRSEQU = " . $_SESSION['Sequencial'] . " ";
				$res = $db->query($sql);
				if (db :: isError($res)) {
						ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
						$Rows = $res->numRows();
					if ($Rows == 0) {
							$_SESSION['DataValidadeCHF'] = SomaData(365, $_SESSION['DataGeracaoCHF']);

# Inserindo Situação Diferente de Cadastrado #

				$sql  = "INSERT INTO SFPC.TBFORNECEDORCHF ( ";
				$sql .= "AFORCRSEQU, CGREMPCODI, CUSUPOCODI, DFORCHGERA, DFORCHVALI, ";
				$sql .= "AFORCHNEMF, DFORCHULEF, AFORCHNEMU, CGREMPCOD1, CUSUPOCOD1, ";
				$sql .= "DFORCHULEU, TFORCHULAT ";
				$sql .= ") VALUES ( ";
				$sql .= "" . $_SESSION['Sequencial'] . ", " . $_SESSION['_cgrempcodi_'] . ", " . $_SESSION['_cusupocodi_'] . ", '" . DataInvertida($_SESSION['DataGeracaoCHF']) . "', '" . DataInvertida($_SESSION['DataValidadeCHF']) . "', ";
				$sql .= "null, null, null, null, null, ";
				$sql .= "null, '$DataAtual') ";

				$result = $db->query($sql);
						if (db :: isError($result)) {
								$db->query("ROLLBACK");
								ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
						}
					}
				}
			}
				$db->query("COMMIT");

# Limpando as variáveis usadas no programa #

			LimparSessao();

# Redireciona o programa de acordo com o botão voltar #

			if ($_SESSION['Mens'] == 0) {
					$_SESSION['Botao'] = "";
					$_SESSION['Critica'] = "";
					$_SESSION['Mensagem'] = "Atualização Realizada com Sucesso";
					$Url = "CadGestaoFornecedorSelecionar.php?Mensagem=" . urlencode($_SESSION['Mensagem']) . "&Mens=1&Tipo=1&Programa=C";
					if (!in_array($Url, $_SESSION['GetUrl'])) {
							$_SESSION['GetUrl'][] = $Url;
					}
					$Destino = "A";
					header("location: " . $Url);

					
			}
			
		}
					
					$db->query("END TRANSACTION");
					$db->disconnect();
		}
					ExibeAbas($Destino);
}

# Função para Chamada do Formulário de cada Aba #
function ExibeAbas($Destino) {
	if ($Destino == "A" or $Destino == "") {
		ExibeAbaHabilitacao();
	}
	elseif ($Destino == "B") {
		ExibeAbaRegularidadeFiscal();
	}
	elseif ($Destino == "C") {
		ExibeAbaQualificEconFinanceira();
	}
	elseif ($Destino == "D") {
		ExibeAbaQualificTecnica();
	}
	elseif ($Destino == "E") {
		ExibeAbaDocumentos();
	}
}

# Critica aos Campos - Formulário A #

function CriticaAbaHabilitacao() {
	
	
	if ($_SESSION['Botao'] == "Limpar") {
		$_SESSION['Botao'] = "";
		$_SESSION['Mens'] = "";
		$_SESSION['MicroEmpresa'] = "";
		$_SESSION['Identidade'] = "";
		$_SESSION['OrgaoUF'] = "";
		$_SESSION['RazaoSocial'] = "";
		$_SESSION['NomeFantasia'] = "";
		$_SESSION['CEP'] = "";
		$_SESSION['Logradouro'] = "";
		$_SESSION['Numero'] = "";
		$_SESSION['Complemento'] = "";
		$_SESSION['Bairro'] = "";
		$_SESSION['Cidade'] = "";
		$_SESSION['UF'] = "";
		$_SESSION['DDD'] = "";
		$_SESSION['Telefone'] = "";
		$_SESSION['Email'] = "";
		$_SESSION['Email2'] = "";
		$_SESSION['Fax'] = "";
		$_SESSION['RegistroJunta'] = "";
		$_SESSION['DataRegistro'] = "";
		$_SESSION['CPFContato'] = "";
		$_SESSION['NomeContato'] = "";
		$_SESSION['CargoContato'] = "";
		$_SESSION['DDDContato'] = "";
		$_SESSION['TelefoneContato'] = "";
		$_SESSION['EmailVazio'] = "";
	} else {
		$_SESSION['Mens'] = 0;
		$_SESSION['Mensagem'] = "Informe: ";
		$_SESSION['MensagemPrincipal'] = "";

		# Verifica a existência do CPF/CNPJ no Cadastro da Prefeitura #
		if ($_SESSION['Irregularidade'] == "") {
			$NomePrograma = urlencode("CadGestaoFornecedor.php");
			$Url = "fornecedores/RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&TipoDoc=" . $_SESSION['TipoDoc'] . "&Destino=" . $_SESSION['DestinoCons'] . "&Sequencial=" . $_SESSION['Sequencial'] . "&CPF_CNPJ=" . $_SESSION['CPF_CNPJ'] . "";
			if (!in_array("/oracle/" . $Url, $_SESSION['GetUrl'])) {
				$_SESSION['GetUrl'][] = $Url;
			}
			Redireciona($Url);
			exit;
		} else {
			if ($_SESSION['Irregularidade'] == "S") { //Irregularidade
				//$_SESSION['Mens'] = 1;
				//$_SESSION['Tipo'] = 1;
				//$_SESSION['Mensagem'] .= " Fornecedor possui alguma irregularidade com a Prefeitura, deve procurar o Centro de Atendimento ao Contribuinte - CAC da Prefeitura - térreo - no Cais do Apolo, 925 - Bairro do Recife/PE e após a solução das pendências tentar executar a Inscrição novamente.<br/>";
				$_SESSION['MensagemPrincipal'] .= " Fornecedor possui alguma irregularidade com a Prefeitura, deve procurar o Centro de Atendimento ao Contribuinte - CAC da Prefeitura - térreo - no Cais do Apolo, 925 - Bairro do Recife/PE e após a solução das pendências tentar executar a Inscrição novamente. ";
			} else {

				if ($_SESSION['Mens'] != 1 and ($_SESSION['Botao'] == "A" or $_SESSION['Botao'] == "Preencher")) {
					if ($_SESSION['CEP'] == "" and $_SESSION['TipoHabilitacao']=="L") {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CEP.focus();\" class=\"titulo2\">CEP</a>";
					} else {
						//		if( $_SESSION['Botao'] == "Preencher" ){
						if ($_SESSION['Mens'] == 0) {

# Seleciona o Endereço de acordo com o CEP informado #

				$_SESSION['CEPAntes'] = $_SESSION['CEP'];
				$cepStr = $_SESSION['CEP'];
				if($_SESSION['CEP']=="") $cepStr="null";
				$db = Conexao();
				$sql =  " SELECT CCEPPOCODI, NCEPPOLOGR, NCEPPOBAIR, NCEPPOTIPO, CCEPPOESTA, ";
				$sql .= "        NCEPPOCOMP, NCEPPOCIDA, CCEPPOREFE, CCEPPOTIPL ";
				$sql .= "   FROM PPDV.TBCEPLOGRADOUROBR ";
				$sql .= "  WHERE CCEPPOCODI = " . $cepStr."";

				$res = $db->query($sql);
				if (db :: isError($res)) {
						ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
						$rows = $res->numRows();
						if ($rows == 0) {
								$sqlloc = " SELECT CCELOCCODI, NCELOCLOCA, CCELOCESTA, CCELOCTIPO ";
								$sqlloc .= "  FROM PPDV.TBCEPLOCALIDADEBR ";
								$sqlloc .= " WHERE CCELOCCODI = " . $cepStr;

								$resloc = $db->query($sqlloc);
								if (db :: isError($resloc)) {
										ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlloc");
								} else {
										$rowloc = $resloc->numRows();
										if ($rowloc == 0) {
											if($_SESSION['TipoHabilitacao']=="L"){
												if ($_SESSION['Mens'] == 1) {
													$_SESSION['Mensagem'] .= ", ";
												}
												$_SESSION['Mens'] = 1;
												$_SESSION['Tipo'] = 2;
												$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CEP.focus();\" class=\"titulo2\">CEP Válido</a>";
											}
											$_SESSION['Logradouro'] = "";
											$_SESSION['Bairro'] = "";
											$_SESSION['Cidade'] = "";
											$_SESSION['UF'] = "";
											$_SESSION['Localidade'] = "";
										} else {
											$LinhaLoc = $resloc->fetchRow();
											$_SESSION['CEP'] = $LinhaLoc[0];
											$_SESSION['Cidade'] = $LinhaLoc[1];
											$_SESSION['UF'] = $LinhaLoc[2];
											$_SESSION['Localidade'] = "S";
										}
									}
											} else {
									$linha = $res->fetchRow();
									$_SESSION['CEP'] = $linha[0];
									if ($linha[3] != "" and $linha[5] != "") {
										$_SESSION['Logradouro'] = $linha[3] . " " . $linha[1] . " " . $linha[5];
									} else {
										if ($linha[3] != "" and $linha[5] == "") {
											$_SESSION['Logradouro'] = $linha[3] . " " . $linha[1];
										}
										elseif ($linha[3] == "" and $linha[5] != "") {
											$_SESSION['Logradouro'] = $linha[1] . " " . $linha[5];
										} else {
											$_SESSION['Logradouro'] = $linha[1];
										}
									}
									$_SESSION['Bairro'] = $linha[2];
									$_SESSION['Cidade'] = $linha[6];
									$_SESSION['UF'] = $linha[4];
									$_SESSION['Localidade'] = "";
								}
							}
							//colocar 8 digitos no CEP
							if(!is_null($_SESSION['CEP']) and trim($_SESSION['CEP'])!=""){
								while (strlen($_SESSION['CEP']) < 8) {
									$_SESSION['CEP'] = "0" . $_SESSION['CEP'];
								}
							}

							$db->disconnect();
							//	}
						} else {
							if ($_SESSION['CEPAntes'] != $_SESSION['CEP']) {
								if ($_SESSION['CEPAntes'] != "") {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'] .= ", ";
									}
									$_SESSION['Mens'] = 1;
									$_SESSION['Tipo'] = 2;
									$_SESSION['Virgula'] = 2;
									$_SESSION['Mensagem'] = "<a href=\"javascript:document.CadGestaoFornecedor.CEP.focus();\" class=\"titulo2\">O CEP Informado não corresponde ao Endereço, clique no Botão \"Preencher Endereço\" para atualizar o Endereço</a>";
									$_SESSION['Botao'] = "";
								} else {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'] .= ", ";
									}
									$_SESSION['Mens'] = 1;
									$_SESSION['Tipo'] = 2;
									$_SESSION['Mensagem'] = "<font class=\"titulo2\">Clique no Botão \"Preencher Endereço\" para atualizar os campos do Endereço</font>";
								}
							}
						}
					}
					if ($_SESSION['Mens'] != 1) {
						if ($_SESSION['TipoHabilitacao'] == 'E') {
							//if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens'] = 1;
							$_SESSION['Tipo'] = 2;
							$_SESSION['Mensagem'] = "<a href=\"javascript:document.CadGestaoFornecedor.TipoHabilitacao.focus();\" class=\"titulo2\">Usuário é do tipo 'Estoques', e deve ser atualizado para um dos tipos: 'Compra direta' ou 'Licitação'</a>";
						}
					}
					if ($_SESSION['Mens'] != 1) {
						if ($_SESSION['CEP'] == "" and $_SESSION['TipoHabilitacao']=="L") {
							//if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens'] = 1;
							$_SESSION['Tipo'] = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CEP.focus();\" class=\"titulo2\">Campo 'CEP' deve ser preenchido</a>";
						}
						elseif (!is_numeric($_SESSION['CEP']) and $_SESSION['CEP'] != "") {
							//if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens'] = 1;
							$_SESSION['Tipo'] = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CEP.focus();\" class=\"titulo2\">Campo 'CEP' deve ser número</a>";
						}
						elseif (strlen($_SESSION['CEP']) != 8 and $_SESSION['CEP'] != "") {
							//if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens'] = 1;
							$_SESSION['Tipo'] = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CEP.focus();\" class=\"titulo2\">Campo 'CEP' deve conter 8 dígitos</a>";
						}
					}
					if ($_SESSION['Numero'] != "") {
						if (!is_numeric($_SESSION['Numero'])) {
							if ($_SESSION['Mens'] == 1) {
								$_SESSION['Mensagem'] .= ", ";
							}
							$_SESSION['Mens'] = 1;
							$_SESSION['Tipo'] = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Numero.focus();\" class=\"titulo2\">Campo 'Número' deve conter apenas números</a>";
						}
					}
					$lengthDDD = strlen($_SESSION['DDD']);
					if ($_SESSION['DDD'] != "") {
						if (!is_numeric($_SESSION['DDD'])) {
							if ($_SESSION['Mens'] == 1) {
								$_SESSION['Mensagem'] .= ", ";
							}
							$_SESSION['Mens'] = 1;
							$_SESSION['Tipo'] = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DDD.focus();\" class=\"titulo2\">Campo 'DDD' deve conter apenas números</a>";
						}
						elseif (($lengthDDD == 1) || ($lengthDDD > 3)) {
							if ($_SESSION['Mens'] == 1) {
								$_SESSION['Mensagem'] .= ", ";
							}
							$_SESSION['Mens'] = 1;
							$_SESSION['Tipo'] = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DDD.focus();\" class=\"titulo2\">Campo 'DDD' deve conter 3 dígitos</a>";
						}
					}

					if ($_SESSION['Mens'] == 0 and $_SESSION['Botao'] != "Preencher") {
						if($_SESSION['TipoHabilitacao']=="L"){
							if ($_SESSION['Identidade'] == "" and $_SESSION['CPF'] != 0) {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Identidade.focus();\" class=\"titulo2\">Identidade</a>";
							}
							if ($_SESSION['OrgaoUF'] == "" and $_SESSION['CPF'] != 0) {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.OrgaoUF.focus();\" class=\"titulo2\">Órgao Emissor/UF</a>";
							}
							if ($_SESSION['Localidade'] == "S") {
								if ($_SESSION['Logradouro'] == "") {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'] .= ", ";
									}
									$_SESSION['Mens'] = 1;
									$_SESSION['Tipo'] = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Logradouro.focus();\" class=\"titulo2\">Logradouro</a>";
								}
							}
							if ($_SESSION['Numero'] != "" and !SoNumeros($_SESSION['Numero'])) {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Numero.focus();\" class=\"titulo2\">Número Válido</a>";
							}
							if ($_SESSION['Localidade'] == "S") {
								if ($_SESSION['Bairro'] == "") {

									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'] .= ", ";
									}
									$_SESSION['Mens'] = 1;
									$_SESSION['Tipo'] = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Bairro.focus();\" class=\"titulo2\">Bairro</a>";
								}
							}							
						}

						// ---------------------------------- 
						//  Crítica Razão Social
						//----------------------------------- -
						if ($_SESSION['RazaoSocial'] == "") {
							if ($_SESSION['Mens'] == 1) {
								$_SESSION['Mensagem'] .= ", ";
							}
							$_SESSION['Mens'] = 1;
							$_SESSION['Tipo'] = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.RazaoSocial.focus();\" class=\"titulo2\">Razão Social</a>";
						} else {
                            // Trecho inserido por Heraldo (12/01/2014)
							$sql  = " select count(*) as qtd from sfpc.tbfornecedorcredenciado ";
							$sql .= " where nforcrrazs = '$_SESSION[RazaoSocial]' ";
							$sql .= " and   aforcrsequ <> " . $_SESSION['Sequencial'];

							$db = Conexao();
							$result = executarSQL($db, $sql);
							$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
							$db->disconnect();
							
							if( $row->qtd > 0  ){
								if( $_SESSION['Mens'] == 1){
									$_SESSION['Mensagem'].=", ";
								}
								$_SESSION['Mens']      = 1;
								$_SESSION['Tipo']      = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.RazaoSocial.focus();\" class=\"titulo2\">Razão Social já existe </a>";
							}
							
						}
						
						// -----------------------------
						// Criticar Nome Fantasia
						// ------------------------------
						if( !empty($_SESSION['NomeFantasia'])   ){
							$nomeFantasiaFiltrado = stripslashes($_SESSION['NomeFantasia']);
							$nomeFantasiaFiltrado = preg_replace('/(\'|")/', "", $nomeFantasiaFiltrado);
							
							$sql  = " select count(*) as qtd from sfpc.tbfornecedorcredenciado";
							$sql .= " where nforcrfant = '$nomeFantasiaFiltrado' ";
							$sql .= " and   aforcrsequ <> " . $_SESSION['Sequencial'];

							$db = Conexao();
							$result = executarSQL($db, $sql);
							$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
							$db->disconnect();
							
							if ( $row->qtd > 0   ) {
								if( $_SESSION['Mens'] == 1){
									$_SESSION['Mensagem'].=", ";
								}
								$_SESSION['Mens']      = 1;
								$_SESSION['Tipo']      = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.NomeFantasia.focus();\" class=\"titulo2\">Nome Fantasia já existe</a>";
							}
						}
						
						// ------------------ 
						// Criticar CEP
						// ------------------ 
						if( !empty($_SESSION['CEP'])  ){
							
							$sql = "select count(*) as qtd from sfpc.tbfornecedorcredenciado";
							$sql .= " where ";
							$sql .= " cceppocodi = ".$_SESSION['CEP'] ;
							
							if ( empty( $_SESSION['Numero'] ))
								$sql .= " and aforcrnume is null";
							else
								$sql .= " and aforcrnume = ".$_SESSION['Numero'];

							if ( empty( $_SESSION['Complemento'] ))	 {						
								$sql .= " and ( eforcrcomp is null ";
								$sql .= " or eforcrcomp = '' )";
							}
							else {
								$sql .= "  and eforcrcomp = '".$_SESSION['Complemento']."'";
							} 
							
							
							$sql .= " and aforcrsequ <> " . $_SESSION['Sequencial'];
							 
							
							$db = Conexao();
							$result = executarSQL($db, $sql);
							$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
							$db->disconnect();
								
						
							if( $row->qtd > 0  ){
								if( $_SESSION['Mens'] == 1){
									$_SESSION['Mensagem'].=", ";
								}
								$_SESSION['Mens']      = 1;
								$_SESSION['Tipo']      = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CEP.focus();\" class=\"titulo2\">CEP/Número/Complemento já existe</a>";
							}
						
						}
						
						
						 
						

						if ($_SESSION['RegistroJunta'] == "") {
							if ($_SESSION['TipoCnpjCpf'] == "CNPJ") {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.RegistroJunta.focus();\" class=\"titulo2\">Registro na Junta Comercial ou Cartório</a>";
							}
						} else {
							if (!SoNumeros($_SESSION['RegistroJunta'])) {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.RegistroJunta.focus();\" class=\"titulo2\">Registro na Junta Comercial ou Cartório Válido</a>";
							} else {
								if ($_SESSION['RegistroJunta'] > 9223372036854775807) {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'] .= ", ";
									}
									$_SESSION['Mens'] = 1;
									$_SESSION['Tipo'] = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.RegistroJunta.focus();\" class=\"titulo2\">Registro na Junta Comercial maior que 0 e menor que 9.223.372.036.854.775.807</a>";
								}
							}
						}
						if ($_SESSION['DataRegistro'] == "") {
							if ($_SESSION['TipoCnpjCpf'] == "CNPJ") {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataRegistro.focus();\" class=\"titulo2\">Data de Registro na Junta Comercial ou Cartório</a>";
							}
						} else {
							$MensErro = ValidaData($_SESSION['DataRegistro']);
							if ($MensErro != "") {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataRegistro.focus();\" class=\"titulo2\">Data Válida</a>";
							} else {
								if (DataInvertida($_SESSION['DataRegistro']) > date("Y-m-d")) {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'] .= ", ";
									}
									$_SESSION['Mens'] = 1;
									$_SESSION['Tipo'] = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataRegistro.focus();\" class=\"titulo2\">Data Inferior ou Igual a Data Atual</a>";
								}
							}
						}
						if( $_SESSION['Email'] == ""){
							if( $_SESSION['TipoHabilitacao']=="L"){
							
								if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
								$_SESSION['Mens']      = 1;
								$_SESSION['Tipo']      = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Email.focus();\" class=\"titulo2\">E-mail 1</a>";
							}
						} else {
							if (!strchr($_SESSION['Email'], "@")) {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Email.focus();\" class=\"titulo2\">E-mail Válido no campo 'E-mail 1'</a>";
							}
						}
						if ($_SESSION['Email2'] != "") {
							if (!strchr($_SESSION['Email2'], "@")) {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Email2.focus();\" class=\"titulo2\">E-mail Válido no campo 'E-mail 2'</a>";
							}
						}
						if ($_SESSION['CPFContato'] != "") {
							$Qtd = strlen($_SESSION['CPFContato']);
							if (($Qtd != 11) and ($Qtd != 0)) {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CPFContato.focus();\" class=\"titulo2\">CPF do Contato com 11 números</a>";
							} else {
								$cpfcnpj = valida_CPF($_SESSION['CPFContato']);
								if ($cpfcnpj === false) {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'] .= ", ";
									}
									$_SESSION['Mens'] = 1;
									$_SESSION['Tipo'] = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CPFContato.focus();\" class=\"titulo2\">CPF do Contato Válido</a>";
								}
							}
						}
					}
				}
			}
		}

		if ($_SESSION['CarregaPagina'] != 0) {

# Função que critica a parte da Gestão #

			CriticaGestao();
		}
	}
	if (($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Preencher") or ($_SESSION['Botao'] == "Limpar")) {
		ExibeAbaHabilitacao();
	}
	# Sócio- INICIO
	// echo "[" . $_SESSION['SociosCPF_CNPJ'] . "]";
	if ($_SESSION['Botao'] == "AdicionarSocio") {
		$_SESSION['MostrarNovoSocio'] = true; // no caso de erro devem ser mostrados de novo os dados

		if ($_SESSION['NoSocios'] == "") {
			$_SESSION['NoSocios'] = 0;
		}
		if ($_SESSION['SocioNovoNome'] == "") {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href='javascript:document.CadGestaoFornecedor.SocioNovoNome.focus();' class='titulo2'>Nome do sócio</a>";
		}
		elseif ($_SESSION['SocioNovoCPF'] == "") {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href='javascript:document.CadGestaoFornecedor.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ do sócio</a>";
		}
		elseif (strlen($_SESSION['SocioNovoCPF']) != 11 and strlen($_SESSION['SocioNovoCPF']) != 14) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href='javascript:document.CadGestaoFornecedor.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ de sócio válido</a>";
		}
		elseif ($_SESSION['SociosCPF_CNPJ'] != 0 and in_array($_SESSION['SocioNovoCPF'], $_SESSION['SociosCPF_CNPJ'])) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href='javascript:document.CadGestaoFornecedor.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ de sócio não pode ser repetido</a>";
		}
		elseif ((strlen($_SESSION['SocioNovoCPF']) == 11 and !valida_CPF($_SESSION['SocioNovoCPF'])) or (strlen($_SESSION['SocioNovoCPF']) == 14 and !valida_CNPJ($_SESSION['SocioNovoCPF']))) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href='javascript:document.CadGestaoFornecedor.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ de sócio válido</a>";
		} else {
			$_SESSION['SociosCPF_CNPJ'][$_SESSION['NoSocios']] = $_SESSION['SocioNovoCPF'];
			$_SESSION['SociosNome'][$_SESSION['NoSocios']] = $_SESSION['SocioNovoNome'];
			$_SESSION['NoSocios']++;
			$_SESSION['MostrarNovoSocio'] = false;
		}
	}
	elseif ($_SESSION['Botao'] == "RemoverSocio") {
		$_SESSION['SociosNome'][$_SESSION['SocioSelecionado']] = NULL;
		$_SESSION['SociosCPF_CNPJ'][$_SESSION['SocioSelecionado']] = NULL;
	}
	# Sócio- FIM
}

# Exibe Aba Habilitacao - Formulário A #

function ExibeAbaHabilitacao() {

# Mensagens para Irregularidade e Certidões Fora do Prazo de Validade #
	//if ($_SESSION['Mens'] == 0) {
		if (($_SESSION['TipoHabilitacao'] == 'L') && ($_SESSION['Situacao'] == 1)) { //compra direta e não-'CADASTRADO' pode possuir certidoes inválidas
			$inabilitarFornecedor = false; //imprimir mensagem de fornecedor inabilitado?
			if ( $_SESSION['Cadastrado'] == "INABILITADO" and $GLOBALS["InabilitacaoCertidaoObrigatoria"]){

				if ($_SESSION['Irregularidade'] == "S") {
					if ($_SESSION['Cadastrado'] == "INABILITADO") {
						//$_SESSION['Mens'] = 1;
						//$_SESSION['Tipo'] = 1;
						$Mensagem = "<a href=\"$Url\" class=\"titulo2\">";
						if(!$inabilitarFornecedor){
							$inabilitarFornecedor = true;
							$_SESSION['MensagemPrincipal'] .= " Fornecedor INABILITADO ";
						}
						$Url = "CadGestaoFornecedor.php?Destino=B&Origem=A&Sequencial=" . $_SESSION['Sequencial'] . "";
						if (!in_array($Url, $_SESSION['GetUrl'])) {
							$_SESSION['GetUrl'][] = $Url;
						}
						$_SESSION['MensagemPrincipal'] .= " Certidão(ões) fora do prazo de validade e com situação irregular na Prefeitura. </a>";
					} else {
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 1;
						$Mensagem = "<a href=\"$Url\" class=\"titulo2\">";
						if(!$inabilitarFornecedor){
							$inabilitarFornecedor = true;
							$_SESSION['Mensagem'] .= "<a href=\"$Url\" class=\"titulo2\">Fornecedor inabilitado ";
						}
						$_SESSION['Mensagem'] .= " situação irregular na Prefeitura";
					}
				}
				if ($_SESSION['Irregularidade'] == "N") {
					if ($_SESSION['Cadastrado'] == "INABILITADO" and $_SESSION['ValCertidao'] != 1) {
						//$_SESSION['Mens'] = 1;
						//$_SESSION['Tipo'] = 1;
						$Mensagem = "<a href=\"$Url\" class=\"titulo2\">";
						if(!$inabilitarFornecedor){
							$inabilitarFornecedor = true;
							$_SESSION['MensagemPrincipal'] .= " Fornecedor inabilitado ";
							//$_SESSION['Mensagem'] .= "<a href=\"$Url\" class=\"titulo2\">Fornecedor inabilitado ";
						}
						$Url = "CadGestaoFornecedor.php?Destino=B&Origem=A&Sequencial=" . $_SESSION['Sequencial'] . "";
						if (!in_array($Url, $_SESSION['GetUrl'])) {
							$_SESSION['GetUrl'][] = $Url;
						}
						$_SESSION['MensagemPrincipal'] .= " Certidão(ões) fora do prazo de validade. ";
						//$_SESSION['Mensagem'] .= " Certidão(ões) fora do prazo de validade</a>";
					}
				}
			}
			if( $_SESSION['Cadastrado'] == "INABILITADO" and $GLOBALS["InabilitacaoUltBalanco"]){
				$Url = "CadGestaoFornecedor.php?Destino=C&Origem=A&Sequencial=" . $_SESSION['Sequencial'] . "";
				if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .=", "; }
				//$_SESSION['Mens'] = 1;
				//$_SESSION['Tipo'] = 1;
				$Mensagem = "<a href=\"$Url\" class=\"titulo2\">";
				if(!$inabilitarFornecedor){
					$inabilitarFornecedor = true;
					$_SESSION['MensagemPrincipal'] .= " Fornecedor inabilitado ";
					//$_SESSION['Mensagem'] .= "<a href=\"$Url\" class=\"titulo2\">Fornecedor inabilitado ";
				}
				$_SESSION['MensagemPrincipal'].= " Data de Validade do Balanço expirada. ";
				//$_SESSION['Mensagem'].= " Data de Validade do Balanço expirada</a>";
			}
			if ( $_SESSION['Cadastrado'] == "INABILITADO" and $GLOBALS["InabilitacaoCertidaoNeg"])
			{
				$Url = "CadGestaoFornecedor.php?Destino=C&Origem=A&Sequencial=" . $_SESSION['Sequencial'] . "";
				if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .=", "; }
				//$_SESSION['Mens'] = 1;
				//$_SESSION['Tipo'] = 1;
				$Mensagem = "<a href=\"$Url\" class=\"titulo2\">";
				if(!$inabilitarFornecedor){
					$inabilitarFornecedor = true;
					$_SESSION['MensagemPrincipal'] .= " Fornecedor inabilitado ";
					//$_SESSION['Mensagem'] = "<a href=\"$Url\" class=\"titulo2\">Fornecedor inabilitado ";
				}
				$_SESSION['MensagemPrincipal'].= " Data de Certidão Negativa expirada.";
				//$_SESSION['Mensagem'].= " Data de Certidão Negativa expirada</a>";
			}
			
			 // adicionado por heraldo para exibor mensagem 23/01/2014
			 //echo "SemDemonstracoesContabeis". $GLOBALS["SemDemonstracoesContaveis"];
			 //echo "<BR>Cadastro". $_SESSION['Cadastrado'];
			 //exit;
			 
			 
			 if (   $GLOBALS["SemDemonstracoesContaveis"]  ) {
				$Url = "CadGestaoFornecedor.php?Destino=C&Origem=A&Sequencial=" . $_SESSION['Sequencial'] . "";
				if( $_SESSION['Mens'] == 1 ){	$_SESSION['Mensagem'] .=", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 1;
				$Mensagem = "<a href=\"$Url\" class=\"titulo2\">";
				//if(!$inabilitarFornecedor){
				// 	$inabilitarFornecedor = true;
				// 	$_SESSION['Mensagem'] = "<a href=\"$Url\" class=\"titulo2\">Fornecedor inabilitado ";
				//}
				$_SESSION['Mensagem'].= " CHF simplificado sem demonstrações contábeis</a>";
				
			} 

			#
			if ($_SESSION['DataValidadeCHF'] != "" and (DataInvertida($_SESSION['DataValidadeCHF']) < date("Y-m-d")) and ($_SESSION['Situacao'] == 1) and ($_SESSION['Cadastrado'] == "HABILITADO")     ) {
				$_SESSION['Cadastrado'] = "INABILITADO";
				$_SESSION['ValCertidao'] = 1;
				if(!$inabilitarFornecedor){
					$inabilitarFornecedor = true;
					$_SESSION['Mensagem'] .= "Fornecedor INABILITADO ";
				}
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 1;
				$_SESSION['Virgula'] = 1;
				$_SESSION['Mensagem'] .= " CHF fora do prazo de validade";
			}
		}
	//}
?>
	<html>
	<?php

	# Carrega o layout padrão #
	layout();
?>
	<script language="javascript" src="../janela.js" type="text/javascript"></script>
	<script language="javascript" type="">
	<!--
	function Submete(Destino){
	 	document.CadGestaoFornecedor.Destino.value = Destino;
	 	document.CadGestaoFornecedor.submit();

	}
	function enviar(valor){
		document.CadGestaoFornecedor.Botao.value = valor;
		document.CadGestaoFornecedor.submit();
	}
	function removerSocio(valor){
		document.CadGestaoFornecedor.SocioSelecionado.value=valor;
		enviar('RemoverSocio');
	}
	function remeter(valor){
	 	document.CadGestaoFornecedor.Destino.value = 'A';
		document.CadGestaoFornecedor.Botao.value   = valor;
		document.CadGestaoFornecedor.submit();
	}
	function AbreJanela(url,largura,altura) {
		window.open(url,'pagina','status=no,scrollbars=no,left=20,top=150,width='+largura+',height='+altura);
	}
	<?php MenuAcesso(); ?>
	//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadGestaoFornecedor.php" method="post" name="CadGestaoFornecedor">
	<br><br><br><br><br>
	<table cellpadding="3" border="0" summary="">
		<!-- Caminho -->
		<tr>
			<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
			<td align="left" class="textonormal" colspan="2">
  			<font class="titulo2">|</font>
  			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedor > Cadastro e Gestão
			</td>
		</tr>
		<!-- Fim do Caminho-->

		<!-- Erro -->
		<tr>
			<td width="100"></td>
			<td align="left" colspan="2">
				<?php if ($_SESSION['Mens'] != 0): ?>
					<?php
						$msgCritica = ExibeMensStr($_SESSION['Mensagem'],$_SESSION['Tipo'],$_SESSION['Virgula']);
						echo str_replace("<br>", "", $msgCritica);
					?>
					<table border="0" width="100%">
						<tr>
							<td class="titulo2"><?php echo $_SESSION['MensagemPrincipal']; ?></td>
						</tr>
					</table>
				<?php elseif (!empty($_SESSION['MensagemPrincipal'])): ?>
					<table border="0" width="100%">
			        	<tr>
			        		<td bgcolor='DCEDF7' class='titulo2'>
			        			<blink>Atenção!</blink>
			        		</td>
			        	</tr>
			        	<tr>
			        		<td class=titulo2><?php echo $_SESSION['MensagemPrincipal']; ?></td>
			        	</tr>
			        </table>
				<?php endif; ?>
			</td>
		</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
			<td width="100"></td>
			<td class="textonormal">
				<table  border="0" cellspacing="0" cellpadding="3" summary="">
					<tr>
			     	<td class="textonormal">
			       	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" bgcolor="#FFFFFF" summary="" class="textonormal" >
			         	<tr>
			           	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
			    					ALTERAR - CADASTRO E GESTÃO DE FORNECEDOR
			          	</td>
			        	</tr>
			 	      	<tr>
			   	      	<td class="textonormal">
										<p align="justify">
											Para atualizar os dados da Inscrição de um Fornecedor, informe os dados abaixo e clique no botão "Alterar" da última aba(Qualificação Técnica). Os itens obrigatórios estão com *.<br>
			       	    		Para alterar o endereço digite um novo CEP e clique no botão "Preencher Endereço".<br><br>
		        	    		Para efetivar a alteração, clique no botão "Alterar" da aba Qualificação Técnica.<br><br>
			       	    		Para excluir o fornecedor do cadastro clique no botão "Excluir" da aba Habilitação Jurídica.
			         	   	</p>
			         		</td>
			        	</tr>
								<?php ExibeGestao(); ?>
								<tr>
									<td align="left">
										<?php echo NavegacaoAbas(on,off,off,off,off); ?>
										<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr bgcolor="#bfdaf2">
												<td colspan="4">
							          	<table class="textonormal" border="0" align="left" summary="">
														<tr>
															<td class="textonormal" height="20">
																<?php  if( $_SESSION['CNPJ'] != 0 ){ echo "CNPJ\n"; }else{ echo "CPF\n"; } ?>
				          	    			</td>
					          	    		<td class="textonormal">
						          	    		<?php

	if ($_SESSION['CNPJ'] != 0) {
		echo FormataCNPJ($_SESSION['CNPJ']);
	} else {
		echo FormataCPF($_SESSION['CPF']);
	}
?>
				          	    				<input type="hidden" name="CPF_CNPJ" value="<?php echo $_SESSION['CPF_CNPJ'] ?>">
					          	    			<input type="hidden" name="CNPJ" value="<?php echo $_SESSION['CNPJ'] ?>">
					          	    			<input type="hidden" name="CPF" value="<?php echo $_SESSION['CPF'] ?>">
					          	    		</td>
					            			</tr>
														<?php 
														$asteriscoParaLicitacao="*";
														if($_SESSION['TipoHabilitacao']!="L"){
															$asteriscoParaLicitacao = "";
														}
														
														if( $_SESSION['CNPJ'] != 0 ){?>
															<tr>
											                  <td class="textonormal"><?php echo getDescPorteEmpresaTitulo() ?></td>
											                  <td class="textonormal"><?php echo comboBoxDescPorteEmpresa($_SESSION['MicroEmpresa']) ?> </td> 
											                </tr>
														<?php } ?>
														<tr>
								              <td class="textonormal">
								              	<?php if( $_SESSION['CNPJ'] != 0 ){ echo "Identidade do Representante Legal\n"; }else{ echo "Identidade".$asteriscoParaLicitacao."\n"; } ?>
								              </td>
								              <td class="textonormal">
								              	<input type="text" name="Identidade" size="17" maxlength="14" value="<?php echo $_SESSION['Identidade'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">
								              	<?php if( $_SESSION['CNPJ'] != 0 ){ echo "Órgão Emissor/UF\n"; }else{ echo "Órgão Emissor/UF".$asteriscoParaLicitacao."\n"; } ?>
								              </td>
								              <td class="textonormal">
								              	<input type="text" name="OrgaoUF" size="17" maxlength="14" value="<?php echo $_SESSION['OrgaoUF'];?>" class="textonormal">
															</td>
								            </tr>

								            <tr>
								              <td class="textonormal">
								              	<?php if( $_SESSION['CNPJ'] != 0 ){ echo "Razão Social*\n"; }else{ echo "Nome*\n"; } ?>
								              </td>
								              <td class="textonormal">
								              	<input type="text" name="RazaoSocial" size="45" maxlength="120" value="<?php echo $_SESSION['RazaoSocial'];?>" class="textonormal">
						           	  			<input type="hidden" name="Origem" value="A">
																<input type="hidden" name="Destino" value="A">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Nome Fantasia</td>
								              <td class="textonormal">
								              	<input type="text" name="NomeFantasia" size="45" maxlength="80" value="<?php echo $_SESSION['NomeFantasia'] ?>" class="textonormal">
								              </td>
								            </tr>
								            <tr>
								              <td class="textonormal">CEP<?=$asteriscoParaLicitacao?></td>
															<td class="textonormal">
																<input type="text" name="CEP" size="8" maxlength="8" value="<?php echo $_SESSION['CEP'] ?>" class="textonormal">
																<input type="hidden" name="CEPAntes" size="8" maxlength="8" value="<?php echo $_SESSION['CEPAntes']?>" class="textonormal">
								            		<input type="button" value="Preencher Endereço" class="botao" onclick="javascript:enviar('Preencher')">
								            	</td>
								            </tr>
														<tr>
								              <td class="textonormal">Logradouro<?=$asteriscoParaLicitacao?></td>
								              <td class="textonormal">
								              	<input type="text" name="Logradouro" size="45" maxlength="100" value="<?php echo $_SESSION['Logradouro']; ?>" class="textonormal">
								              </td>
								            </tr>
														<tr>
								              <td class="textonormal">Número</td>
								              <td class="textonormal">
								              	<input type="text" name="Numero" size="5" maxlength="5" value="<?php echo $_SESSION['Numero']; ?>" class="textonormal">
								              </td>
								            </tr>
														<tr>
								              <td class="textonormal">Complemento</td>
								              <td class="textonormal">
								              	<input type="text" name="Complemento" size="33" maxlength="20" value="<?php echo $_SESSION['Complemento']; ?>" class="textonormal">
								              </td>
								            </tr>
														<tr>
								              <td class="textonormal">Bairro<?=$asteriscoParaLicitacao?></td>
								              <td class="textonormal">
								              	<input type="text" name="Bairro" size="33" maxlength="30" value="<?php echo $_SESSION['Bairro'] ?>" class="textonormal">
								              </td>
								            </tr>
														<tr>
								              <td class="textonormal">Cidade<?=$asteriscoParaLicitacao?></td>
								              <td class="textonormal">
								              	<input type="text" name="Cidade" size="33" maxlength="30" value="<?php echo $_SESSION['Cidade'] ?>" class="textonormal">
								              </td>
								            </tr>
								            <tr>
								              <td class="textonormal">UF<?=$asteriscoParaLicitacao?></td>
						    	      			<td class="textonormal">
						    	      				<input type="text" name="UF" size="2" maxlength="2" value="<?php echo $_SESSION['UF'] ?>" class="textonormal">
						    	      			</td>
								            </tr>
								            <tr>
								              <td class="textonormal">DDD</td>
								              <td class="textonormal">
								              	<input type="text" name="DDD" size="2" maxlength="3" value="<?php echo $_SESSION['DDD'];?>" class="textonormal">
								              </td>
								            </tr>
								            <tr>
								              <td class="textonormal">Telefone(s)</td>
															<td class="textonormal">
																<input type="text" name="Telefone" size="33" maxlength="30" value="<?php echo $_SESSION['Telefone'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">E-mail 1<?=$asteriscoParaLicitacao?></td>
											<td class="textonormal">
												<?

	/*php if( $_SESSION['Email'] == "NULL" ){ $_SESSION['Email'] = "";*/
?>
												<input type="text" name="Email" size="45" maxlength="60" value="<?php echo $_SESSION['Email'];?>" class="textonormal">
												<?
 /*php }else{ ?>
												<input type="text" name="Email" size="45" maxlength="60" value="<?php echo $_SESSION['Email'];?>" class="textonormal">
												<?php } */
?>
											</td>
								            </tr>
								              <td class="textonormal">E-mail 2</td>
											<td class="textonormal">
												<input type="text" name="Email2" size="45" maxlength="60" value="<?php echo $_SESSION['Email2'];?>" class="textonormal">
											</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Fax</td>
															<td class="textonormal">
																<input type="text" name="Fax" size="27" maxlength="25" value="<?php echo $_SESSION['Fax'];?>" class="textonormal">
															</td>
								            </tr>
														<tr>
														<td class="textonormal">Registro Junta Comercial ou Cartório<?php If($_SESSION['TipoCnpjCpf'] == "CNPJ") {echo "*";} ?></td>
															<td class="textonormal">
																<input type="text" name="RegistroJunta" size="12" maxlength="11" value="<?php echo $_SESSION['RegistroJunta'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal" width="45%">Data Reg. Junta Comercial ou Cartório<?php If($_SESSION['TipoCnpjCpf'] == "CNPJ") {echo "*";} ?></td>
								              <td class="textonormal">
																<?php $URL = "../calendario.php?Formulario=CadGestaoFornecedor&Campo=DataRegistro";?>
																<input type="text" name="DataRegistro" size="10" maxlength="10" value="<?php echo $_SESSION['DataRegistro'];?>" class="textonormal">
																<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0);"><img src="../midia/calendario.gif" border="0" alt=""></a>
									      			</td>
								            </tr>
														<tr>
								              <td class="textonormal">Nome do Contato</td>
															<td class="textonormal">
																<input type="text" name="NomeContato" size="45" maxlength="60" value="<?php echo $_SESSION['NomeContato'];?>" class="textonormal">
															</td>
								            </tr>
														<tr>
								              <td class="textonormal">CPF do Contato</td>
															<td class="textonormal">
																<input type="text" name="CPFContato" size="12" maxlength="11" value="<?php echo $_SESSION['CPFContato'];?>" class="textonormal">
															</td>
								            </tr>
														<tr>
								              <td class="textonormal">Cargo do Contato</td>
															<td class="textonormal">
																<input type="text" name="CargoContato" size="45" maxlength="60" value="<?php echo $_SESSION['CargoContato'];?>" class="textonormal">
															</td>
								            </tr>
														<tr>
								              <td class="textonormal">DDD do Contato</td>
															<td class="textonormal">
																<input type="text" name="DDDContato" size="2" maxlength="3" value="<?php echo $_SESSION['DDDContato'];?>" class="textonormal">
															</td>
								            </tr>
														<tr>
								              <td class="textonormal">Telefone do Contato</td>
															<td class="textonormal">
																<input type="text" name="TelefoneContato" size="27" maxlength="25" value="<?php echo $_SESSION['TelefoneContato'];?>" class="textonormal">
															</td>
								            </tr>
								          </table>
												</td>
											</tr>
										</table>
									</td>
								</tr>

								<?

	# SÓCIOS- INÍCIO
	if ($_SESSION['TipoCnpjCpf'] == "CNPJ") {
?>

											<tr  bgcolor="#bfdaf2" >
												<td class="textonormal">
													<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
		          			      	<tr>
								            	<td bgcolor="#75ADE6" class="textoabasoff" align="center">SÓCIOS</td>
								            </tr>
								            <tr>
																<td class="textonormal">
						              				<table class="textonormal" border="1" width="100%" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="">
						              					<tr>
						              						<td bgcolor="#75ADE6" class="textoabasoff">NOME</td>
						              						<td bgcolor="#75ADE6" class="textoabasoff" width="22%">CPF/CNPJ</td>
						              						<td bgcolor="#75ADE6" class="textoabasoff" width="5%">OPÇÕES</td>
						              					</tr>

																		<?php

		for ($itr = 0; $itr < $_SESSION['NoSocios']; $itr++) {
			if (!is_null($_SESSION['SociosCPF_CNPJ'][$itr])) {
?>

						              					<tr>
						              						<td class="textonormal"><?php echo $_SESSION['SociosNome'][$itr];?></td>
						              						<td class="textonormal"><?php echo $_SESSION['SociosCPF_CNPJ'][$itr];?></td>
						              						<td class="textonormal">
						              							<input type="hidden" name="SociosNome[<?php echo $itr; ?>]" value="<?php echo $_SESSION['SociosNome'][$itr]; ?>" >
						              							<input type="hidden" name="SociosCPF_CNPJ[<?php echo $itr; ?>]" value="<?php echo $_SESSION['SociosCPF_CNPJ'][$itr]; ?>" >
						              							<input type="button" name="RemoverSocio"  class="botao" value="Remover" onClick="javascript:removerSocio(<?php echo $itr; ?>);">
						              						</td>
						              					</tr>

						              					<?php

			}
		}
?>

						              					<tr>
						              						<td class="textonormal"><input type="text" name="SocioNovoNome" size="81" maxlength="80" value="<?php if($_SESSION['MostrarNovoSocio']) echo $_SESSION['SocioNovoNome']; ?>" class="textonormal"></td>
						              						<td class="textonormal"><input type="text" name="SocioNovoCPF" size="15" maxlength="14" value="<?php if($_SESSION['MostrarNovoSocio']) echo $_SESSION['SocioNovoCPF']; ?>" class="textonormal"></td>
						              						<td class="textonormal">
						              							<input type="hidden" name="SocioSelecionado" value="-1">
						              							<input type="hidden" name="NoSocios" value="<?php echo $_SESSION['NoSocios']; ?>" >
						              							<input type="button" name="AdicionarSocio" class="botao" value="Adicionar" onClick="javascript:enviar('AdicionarSocio');">
						              						</td>
						              					</tr>
						              		</td>

									          	</tr>
														</table>
													</td>
												</tr>

								       </table>



								<?

	}
	# SÓCIOS- FIM
?>


					      <tr>
					        <td colspan="4" align="right">
		            		<input type="button" value="Próxima Aba" class="botao" onclick="javascript:enviar('A');">
		            		<input type="button" value="Limpar Tela" class="botao" onclick="javascript:enviar('Limpar');">
		            		<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
		            		<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
		            		<input type="hidden" name="Botao" value="">
					        </td>
	        			</tr>
	     				</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<!-- Fim do Corpo -->
	</form>
	<script language="javascript" type="">
	<!--
		<?

	if ($_SESSION['Botao'] == "AdicionarSocio" and $_SESSION['Mens'] == 0) {
		echo "document.CadGestaoFornecedor.SocioNovoNome.focus();";
	} else {
		echo "document.CadGestaoFornecedor.Identidade.focus();";
	}
?>

	-->
	</script>
	</body>
	</html>
	<?php

	exit;
}

# Critica aos Campos - Formulário B #
function CriticaAbaRegularidadeFiscal() {
	global $NoCamposGerais;
	if ($_SESSION['Botao'] == "Limpar") {
		$_SESSION['Botao'] = "";
		$_SESSION['InscEstadual'] = "";
		$_SESSION['InscMercantil'] = "";
		$_SESSION['InscOMunic'] = "";
		if ($_SESSION['DataCertidaoOb'] != 0) {
			$_SESSION['DataCertidaoOb'] = array_fill(5, count($_SESSION['DataCertidaoOb']), '');
		}
		if ($_SESSION['DataCertidaoComp'] != 0) {
			$_SESSION['DataCertidaoComp'] = array_fill(5, count($_SESSION['DataCertidaoComp']), '');
		}
		ExibeAbaRegularidadeFiscal();
	}
	elseif ($_SESSION['Botao'] == "RetirarComplementar") {
		if (count($_SESSION['CertidaoComplementar']) != 0) {
			for ($i = 0; $i < count($_SESSION['CertidaoComplementar']); $i++) {
				if ($_SESSION['CheckComplementar'][$i] == "") {
					$QtdComp++;
					$_SESSION['CheckComplementar'][$i] = "";
					$_SESSION['CertidaoComplementar'][$QtdComp -1] = $_SESSION['CertidaoComplementar'][$i];
					$_SESSION['DataCertidaoComp'][$QtdComp -1] = $_SESSION['DataCertidaoComp'][$i];
				}
			}
			$_SESSION['CertidaoComplementar'] = array_slice($_SESSION['CertidaoComplementar'], 0, $QtdComp);
			$_SESSION['DataCertidaoComp'] = array_slice($_SESSION['DataCertidaoComp'], 0, $QtdComp);
			if (count($_SESSION['CertidaoComplementar']) == 1 and count($_SESSION['CertidaoComplementar']) == "") {
				session_unregister('CertidaoComplementar');
			}
			if (count($_SESSION['DataCertidaoComp']) == 1 and count($_SESSION['DataCertidaoComp']) == "") {
				session_unregister('DataCertidaoComp');
			}
			$_SESSION['Certidao'] = "";
		}
		ExibeAbas("B");
	} else {
		$_SESSION['Mens'] = "";
		$_SESSION['Mensagem'] = "Informe: ";

		# Verifica se as Inscrições estão vazias #
		if ($_SESSION['InscMercantil'] == "" and $_SESSION['InscOMunic'] == "" and $_SESSION['InscEstadual'] == "" AND $_SESSION['TipoHabilitacao'] == 'L') {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.InscMercantil.focus();\" class=\"titulo2\">Inscrição Municipal Recife ou Inscrição de Outro Munícipio ou Inscrição Estadual</a>";
		} else {
			# Verifica se as duas Inscrições estão preenchidas #
			if ($_SESSION['InscMercantil'] != "" and $_SESSION['InscOMunic'] != "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.InscMercantil.focus();\" class=\"titulo2\">Inscrição Municipal Recife ou Inscrição de Outro Munícipio</a>";
			} else {
				# Verifica se a Inscrição Municipal é Númerica #
				if (($_SESSION['InscOMunic'] != "") and (!SoNumeros($_SESSION['InscOMunic']))) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.InscOMunic.focus();\" class=\"titulo2\">Inscrição de Outro Município Válida</a>";
				}
				if ($_SESSION['InscMercantil'] != "") {
					# Verifica se a Inscrição Municipal Recife é Númerica #
					if (!SoNumeros($_SESSION['InscMercantil'])) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.InscMercantil.focus();\" class=\"titulo2\"> Inscrição Municipal Recife Válida</a>";
					} else {
						# Pesquisa se Inscrição Municipal Recife é Válida no Banco de Dados #
						if ($_SESSION['InscricaoValida'] == "") {
							$NomePrograma = urlencode("CadGestaoFornecedor.php");
							$Url = "fornecedores/RotConsultaInscricaoMercantil.php?NomePrograma=$NomePrograma&InscricaoMercantil=" . $_SESSION['InscMercantil'] . "&Destino=" . $_SESSION['DestinoInsc'] . "&Sequencial=" . $_SESSION['Sequencial'] . "";
							if (!in_array($Url, $_SESSION['GetUrl'])) {
								$_SESSION['GetUrl'][] = $Url;
							}
							Redireciona($Url);
							exit;
						} else {
							if ($_SESSION['InscricaoValida'] == "N") {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens'] = 1;
								$_SESSION['Tipo'] = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.InscMercantil.focus();\" class=\"titulo2\"> Inscrição Municipal Recife Válida</a>";
							}
						}
					}
				}
			}
		}
		if ($_SESSION['InscEstadual'] != "" and (!SoNumeros($_SESSION['InscEstadual']))) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.InscEstadual.focus();\" class=\"titulo2\"> Inscrição Estadual Válida</a>";
		}

		# Criando o Array de Certidões COMPLEMENTARES #
		if ($_SESSION['Certidao'] != "") {
			if (!session_is_registered('CertidaoComplementar')) {
				$_SESSION['CertidaoComplementar'] = array ();
			}
			if ($_SESSION['CertidaoComplementar'] == "" || !in_array($_SESSION['Certidao'], $_SESSION['CertidaoComplementar'])) {
				$_SESSION['CertidaoComplementar'][count($_SESSION['CertidaoComplementar'])] = $_SESSION['Certidao'];
			}
		}

		# Verifica se as certidões Obrigatórias estão válidas #
		if ($_SESSION['DataCertidaoOb'] != 0 AND $_SESSION['TipoHabilitacao'] == 'L') {
			for ($i = 0; $i < count($_SESSION['DataCertidaoOb']); $i++) {
				if ($_SESSION['DataCertidaoOb'][$i] == "") {
					$cont++;
					if ($cont == 1) {
						$PosOb = $i;
					}
					
					//não verificar se for data do último balanço não informada (Heraldo) 23/01/2014
					if   ($i < 7) 	$ExisteDataOb = "N";	
				    if   ( ( $i == 7) and (empty($_SESSION['MicroEmpresa']))  ) 	$ExisteDataOb = "N";
										
					
				} else {
					if (ValidaData($_SESSION['DataCertidaoOb'][$i])) {
						$con++;
						if ($con == 1) {
							$PosOb = $i;
						}
						$DataValidaOb = "N";
					}
				}
			}
			if ($ExisteDataOb == "N") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$PosOb = ($PosOb * 2) + $_SESSION['Elementos'] + $NoCamposGerais; # Quando aumentar ou diminuir o nº campos, aumentar ou diminuir o nºelementos
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.elements[$PosOb].focus();\" class=\"titulo2\"> Data(s) de Validade da(s) Certidão(ões) Obrigatória(s)</a>";
			}
			elseif ($DataValidaOb == "N") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$PosOb = ($PosOb * 2) + $_SESSION['Elementos'] + $NoCamposGerais; # Quando aumentar ou diminuir o nº campos, aumentar ou diminuir o nºelementos
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.elements[$PosOb].focus();\" class=\"titulo2\"> Data(s) de Validade da(s) Certidão(ões) Obrigatória(s) Válida</a>";
			}
		}

		# Verifica se as certidões Complementares estão válidas #
		if ($_SESSION['DataCertidaoComp'] != 0) {
			for ($i = 0; $i < count($_SESSION['DataCertidaoComp']); $i++) {
				if ($_SESSION['DataCertidaoComp'][$i] == "") {
					$cont++;
					if ($cont == 1) {
						$PosComp = $i +1;
					}
					$ExisteDataComp = "N";
				} else {
					if (ValidaData($_SESSION['DataCertidaoComp'][$i])) {
						$con++;
						if ($con == 1) {
							$PosComp = $i;
						}
						$DataValidaComp = "N";
					}
				}
			}
			$PosOb = count($_SESSION['DataCertidaoOb']);
			if ($ExisteDataComp == "N") {
				$PosComp = ($PosComp * 3) + $PosOb + $_SESSION['Elementos'] +10;
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.elements[$PosComp].focus();\" class=\"titulo2\"> Data de Validade da Certidão Complementar</a>";
			}
			elseif ($DataValidaComp == "N") {
				$PosComp = ($PosComp * 3) + $PosOb + $_SESSION['Elementos'] +13;
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.elements[$PosComp].focus();\" class=\"titulo2\"> Data(s) de Validade da(s) Certidão(ões) Complementar(es) Válida</a>";
			}
		}

		# Função que critica a parte da Gestão #
		CriticaGestao();
	}
	if (($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Limpar") ) {
		ExibeAbaRegularidadeFiscal();
	}
}

# Exibe Aba Regularidade Fiscal - Formulário B #
function ExibeAbaRegularidadeFiscal() {
	global $NoCamposGerais;
?>
	<html>
	<?php

	# Carrega o layout padrão #
	layout();
?>
	<script language="javascript" src="../janela.js" type="text/javascript"></script>
	<script language="javascript" type="">
	<!--
	function Submete(Destino) {
	 	document.CadGestaoFornecedor.Destino.value = Destino;
	 	document.CadGestaoFornecedor.submit();
	}
	function enviar(valor){
		document.CadGestaoFornecedor.Botao.value = valor;
		document.CadGestaoFornecedor.submit();
	}
	function remeter(valor){
	 	document.CadGestaoFornecedor.Destino.value = 'B';
		document.CadGestaoFornecedor.Botao.value   = valor;
		document.CadGestaoFornecedor.submit();
	}
	function AbreJanela(url,largura,altura) {
		window.open(url,'pagina','status=no,scrollbars=no,left=20,top=150,width='+largura+',height='+altura);
	}
	<?php MenuAcesso(); ?>
	//-->
	</script>
	<link rel="Stylesheet" type="Text/Css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadGestaoFornecedor.php" method="post" name="CadGestaoFornecedor">
	<br><br><br><br><br>
	<table cellpadding="3" border="0" summary="">
		<!-- Caminho -->
  	<tr>
	    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
	    <td align="left" class="textonormal" colspan="2">
	      <font class="titulo2">|</font>
	      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedor > Cadastro e Gestão
	    </td>
	  </tr>
	  <!-- Fim do Caminho-->

		<!-- Erro -->
		<tr>
		  <td width="100"></td>
		  <td align="left" colspan="2">
	  		<?php if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],1);}?>
	    </td>
		</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
			<td width="100"></td>
			<td class="textonormal">
				<table  border="0" cellspacing="0" cellpadding="3" summary="">
					<tr>
		      	<td class="textonormal">
		        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
		          	<tr>
		            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
			    					ALTERAR - CADASTRO E GESTÃO DE FORNECEDOR
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal">
		      	    		<p align="justify">
		        	    		Para continuar a Inscrição, informe os dados abaixo, as datas de todas as certidões fiscais obrigatórias e informe as certidões opcionais, se existirem com suas respectivas datas. Os itens obrigatórios estão com *.<br>
		        	    		Parar retirar uma ou mais certidão fiscal Complementar selecione a(s) certidão(ões) e clique no botão "Retirar Complementar".<br><br>
		        	    		Para efetivar a alteração, clique no botão "Alterar" da aba Qualificação Técnica.<br><br>
			       	    		Para excluir o fornecedor do cadastro clique no botão "Excluir" da aba Habilitação Jurídica.
		          	   	</p>
		          		</td>
			        	</tr>
								<?php ExibeGestao();?>
			        	<tr>
									<td align="left">
										<?php echo NavegacaoAbas(off,on,off,off,off); ?>
										<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr bgcolor="#bfdaf2">
												<td colspan="4">
								          <table class="textonormal" border="0" align="left"  width="100%" summary="">
														<tr>
															<td class="textonormal" height="20">
																<?php if( $_SESSION['CNPJ'] != 0 ){ echo "CNPJ\n"; }else{ echo "CPF\n"; } ?>
					          	    		</td>
					          	    		<td class="textonormal">
															<?php

	if ($_SESSION['CNPJ'] != 0) {
		echo "<input type=\"hidden\" name=\"CPF_CNPJ\" value=\"" . $_SESSION['CNPJ'] . "\" >" . FormataCNPJ($_SESSION['CNPJ']) . "\n";
	} else {
		echo "<input type=\"hidden\" name=\"CPF_CNPJ\" value=\"" . $_SESSION['CPF'] . "\" >" . FormataCPF($_SESSION['CPF']) . "\n";
	}
?>
					          	    		</td>
					            			</tr>
								            <tr>
								              <td class="textonormal" height="20">
								              	<?php if( $_SESSION['CNPJ'] != 0 ){ echo "Razão Social\n"; }else{ echo "Nome\n"; } ?>
								              </td>
								              <td class="textonormal">
								              	<?php echo $_SESSION['RazaoSocial']; ?>
					            	  			<input type="hidden" name="Origem" value="B">
																<input type="hidden" name="Destino" value="B">
															</td>
								            </tr>
														<tr>
														<td class="textonormal">Inscrição Municipal Recife<?php if($_SESSION['TipoHabilitacao']=="L"){echo "*";} ?> </td>
								              <td class="textonormal">
								              	<input type="text" name="InscMercantil" size="20" maxlength="7" value="<?php echo $_SESSION['InscMercantil'];?>" class="textonormal">
								              </td>
								            </tr>
								            <tr>
								              <td class="textonormal">Inscrição Outro Município<?php if($_SESSION['TipoHabilitacao']=="L"){echo "*";} ?> </td>
															<td class="textonormal">
																<input type="text" name="InscOMunic" size="20" maxlength="19" value="<?php echo $_SESSION['InscOMunic'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Inscrição Estadual </td>
								              <td class="textonormal">
								              	<input type="text" name="InscEstadual" size="20" maxlength="14" value="<?php echo $_SESSION['InscEstadual'];?>" class="textonormal">
								              </td>
								            </tr>
														<tr><td><br></td></tr>
														<?php

	if ($_SESSION['TipoHabilitacao'] == "L") {
?>
								            <tr>
								              <td class="textonormal" colspan="2">
																<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
		          			          		<tr>
								              	<td bgcolor="#75ADE6" class="textoabasoff" colspan="3" align="center">CERTIDÃO FISCAL</td>
								            </tr>
								              		<tr>
								              			<td bgcolor="#DDECF9" class="textonormal" colspan="3" align="center">OBRIGATÓRIA</td>
								              		</tr>
								              		<tr>
																		<td class="textonormal" colspan="3">
								              				<table class="textonormal" border="1" width="100%" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="">
								              					<tr>
								              						<td bgcolor="#75ADE6" class="textoabasoff">NOME DA CERTIDÃO</td>
								              						<td bgcolor="#75ADE6" class="textoabasoff" width="22%">DATA DE VALIDADE</td>
								              					</tr>
									              				<?php

		//if( !is_array($_SESSION['CertidaoComplementar']) ){
		# Elementos é quantidade de objetos acima da tabela certidões obrigatórias #
		$Elementos = $_SESSION['Elementos'] + $NoCamposGerais;
		if (is_null($_SESSION['DataCertidaoOb']) or !is_array($_SESSION['DataCertidaoOb'])) {
			# Mostra a lista de certidões obrigatórias #
			$db = Conexao();
			$sql = "
																										SELECT
																											A.CTIPCECODI, A.ETIPCEDESC, B.DFORCEVALI ";
			$sql .= "   FROM SFPC.TBTIPOCERTIDAO A ";
			$sql .= "   LEFT OUTER JOIN SFPC.TBFORNECEDORCERTIDAO B ON A.CTIPCECODI = B.CTIPCECODI ";
			$sql .= "   AND  B.AFORCRSEQU = " . $_SESSION['Sequencial'] . " ";
			$sql .= " WHERE  FTIPCEOBRI = 'S'";
			$sql .= " ORDER  BY A.CTIPCECODI";
			$res = $db->query($sql);

			if (db :: isError($res)) {
				ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
			} else {
				$Rows = $res->numRows();
				for ($i = 0; $i < $Rows; $i++) {

					$Linha = $res->fetchRow();
					$CertidaoOb = $Linha[0];
					$DescricaoOb = substr($Linha[1], 0, 75);
					if ($Linha[2] != 0) {
						# DataBarra() - monta a data com Barras(dd/mm/aaaa) quando a Data vem invertida(aaaa-mm-dd) #
						$_SESSION['DataCertidaoOb'][$i] = DataBarra($Linha[2]);
					}
					if (DataInvertida($_SESSION['DataCertidaoOb'][$i]) < date("Y-m-d")  ) {
						$Validade = "titulo1";
					} else {
						$Validade = "textonormal";
					}

					//Id do input
					$ElementoOb = $Elementos + (3 * $i);
					//echo "[".$ElementoOb."]";
					echo "<tr>\n";
					echo "	<td class=\"$Validade\" width=\"*\">$DescricaoOb </td>\n";
					echo "	<td class=\"textonormal\" width=\"22%\" align=\"center\">\n";
					$URL = "../calendario.php?Formulario=CadGestaoFornecedor&Campo=elements[$ElementoOb]";
					echo "		<input class=\"textonormal\" type=\"text\" name=\"DataCertidaoOb[$i]\" size=\"10\" maxlength=\"10\" value=\"" . $_SESSION['DataCertidaoOb'][$i] . "\">\n";
					echo "		<a href=\"javascript:janela('$URL','Calendario',220,170,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>\n";
					echo "		<input type=\"hidden\" name=\"CertidaoObrigatoria[$i]\" value=\"" . $CertidaoOb . "\">\n";
					echo "		<input type=\"hidden\" name=\"DescCertidaoObrigatoria[$i]\" value=\"" . $DescricaoOb . "\">\n";
					echo "	</td>\n";
					echo "</tr>\n";
				}
			}
		} else {
			$i = -1;
			foreach ($_SESSION['DataCertidaoOb'] as $CertidaoObrigatoria) {
				$i++;

				if (DataInvertida($_SESSION['DataCertidaoOb'][$i]) < date("Y-m-d")   ) {
					$Validade = "titulo1";
				} else {
					$Validade = "textonormal";
				}
				//Id do input
				$ElementoOb = $Elementos + (3 * $i);

				$DescricaoOb = $_SESSION["DescCertidaoObrigatoria"][$i];
				
				// Inserido por Heraldo;
				if (   $DescricaoOb == "DATA ÚLTIMO BALANÇO" )  $Validade = "textonormal";

				echo "<tr>\n";
				echo "	<td class=\"$Validade\" width=\"*\">$DescricaoOb  </td>\n";
				echo "	<td class=\"textonormal\" width=\"22%\" align=\"center\">\n";
				$URL = "../calendario.php?Formulario=CadGestaoFornecedor&Campo=elements[$ElementoOb]";
				echo "		<input class=\"textonormal\" type=\"text\" name=\"DataCertidaoOb[$i]\" size=\"10\" maxlength=\"10\" value=\"" . $_SESSION['DataCertidaoOb'][$i] . "\">\n";
				echo "		<a href=\"javascript:janela('$URL','Calendario',220,170,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>\n";
				echo "		<input type=\"hidden\" name=\"CertidaoObrigatoria[$i]\" value=\"" . $_SESSION['CertidaoObrigatoria'][$i] . "\">\n";
				echo "		<input type=\"hidden\" name=\"DescCertidaoObrigatoria[$i]\" value=\"" . $DescricaoOb . "\">\n";
				echo "	</td>\n";
				echo "</tr>\n";
			}
		}

?>
									  	              	</table>
									  	              </td>
								              		</tr>
								              		<tr>
								              			<td bgcolor="#DDECF9" class="textonormal" colspan="3" align="center">COMPLEMENTAR</td>
								              		</tr>
								              		<tr>
								              			<td class="textonormal" width="50%" colspan="3">
										              		<?php


		# quantidade de objetos acima da tabela certidões complementares #
		$Elementos += (count($_SESSION['DataCertidaoOb']) * 3) + 1;

		$db = Conexao();
		if (!is_array($_SESSION['CertidaoComplementar'])) {
			# Verifica se existem certidões opcionais cadastradas para o Fornecedor #
			$sql = "SELECT A.DFORCEVALI, B.CTIPCECODI, B.ETIPCEDESC  ";
			$sql .= "  FROM SFPC.TBFORNECEDORCERTIDAO A, SFPC.TBTIPOCERTIDAO B ";
			$sql .= " WHERE A.AFORCRSEQU = " . $_SESSION['Sequencial'] . " AND A.CTIPCECODI = B.CTIPCECODI";
			$sql .= "   AND B.FTIPCEOBRI = 'N' ORDER BY B.CTIPCECODI";
			$res = $db->query($sql);
			if (db :: isError($res)) {
				ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
			} else {
				$Rows = $res->numRows();
				if ($Rows != 0) {
					echo "<table class=\"textonormal\" border=\"1\" width=\"100%\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\">\n";
					echo "	<tr>\n";
					echo "		<td bgcolor=\"#75ADE6\" class=\"textoabasoff\">&nbsp;</td>\n";
					echo "		<td bgcolor=\"#75ADE6\" class=\"textoabasoff\">NOME DA CERTIDÃO</td>\n";
					echo "		<td bgcolor=\"#75ADE6\" class=\"textoabasoff\">DATA DE VALIDADE</td>\n";
					echo "	</tr>\n";
					# Mostra as certidões complementares cadastradas #
					for ($i = 0; $i < $Rows; $i++) {
						$ElementoComp = $Elementos + (2 * $i);
						//echo "[!".$ElementoOb."]";
						$Linha = $res->fetchRow();
						$_SESSION['DataCertidaoComp'][$i] = DataBarra($Linha[0]);
						$CertidaoComp = $Linha[1];
						$DescricaoComp = substr($Linha[2], 0, 75);
						echo  $DescricaoComp;
						exit;
						
						if (  DataInvertida($_SESSION['DataCertidaoComp'][$i] ) < date("Y-m-d")    ) {
							$Validade = "titulo1";
						} else {
							$Validade = "textonormal";
						}
						echo "	<tr>\n";
						echo "		<td class=\"textonormal\" width=\"5%\">\n";
						echo "			<input type=\"checkbox\" name=\"CheckComplementar[$i]\" value=\"$CertidaoComp\">\n";
						echo "		</td>\n";
						echo "		<td class=\"$Validade\" width=\"*\">$DescricaoComp</td>\n";
						echo "		<td class=\"textonormal\" width=\"22%\" align=\"center\">\n";
						$URL = "../calendario.php?Formulario=CadGestaoFornecedor&Campo=elements[$ElementoComp]";
						echo "			<input class=\"textonormal\" type=\"text\" name=\"DataCertidaoComp[$i]\" size=\"10\" maxlength=\"10\" value=\"" . $_SESSION['DataCertidaoComp'][$i] . "\">\n";
						echo "			<a href=\"javascript:janela('$URL','Calendario',220,170,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>\n";
						echo "			<input type=\"hidden\" name=\"CertidaoComplementar[$i]\" value=\"" . $CertidaoComp . "\">\n";
						echo "		</td>\n";
						echo "	</tr>\n";
					}
					echo "</table>\n";
				}
			}
		} else {
			echo "<table class=\"textonormal\" border=\"1\" width=\"100%\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\">\n";
			echo "	<tr>\n";
			echo "		<td bgcolor=\"#75ADE6\" class=\"textoabasoff\">&nbsp;</td>\n";
			echo "		<td bgcolor=\"#75ADE6\" class=\"textoabasoff\">NOME DA CERTIDÃO</td>\n";
			echo "		<td bgcolor=\"#75ADE6\" class=\"textoabasoff\">DATA DE VALIDADE</td>\n";
			echo "	</tr>\n";
			$db = Conexao();
			for ($i = 0; $i < count($_SESSION['CertidaoComplementar']); $i++) {
				$ElementoComp = $Elementos + (3 * $i);
				$sql = "SELECT CTIPCECODI, ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO ";
				$sql .= " WHERE CTIPCECODI = " . $_SESSION['CertidaoComplementar'][$i] . " ORDER BY CTIPCECODI";
				$res = $db->query($sql);
				if (db :: isError($res)) {
					ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
					$Linha = $res->fetchRow();
					$CertidaoComp = $Linha[0];
					$DescricaoComp = substr($Linha[1], 0, 75);
					if (DataInvertida($_SESSION['DataCertidaoComp'][$i]) < date("Y-m-d")) {
						$Validade = "titulo1";
					} else {
						$Validade = "textonormal";
					}
					echo "<tr>\n";
					echo "	<td class=\"textonormal\" width=\"5%\">\n";
					echo "		<input type=\"checkbox\" name=\"CheckComplementar[$i]\" value=\"$CertidaoComp\">\n";
					echo "	</td>\n";
					echo "	<td class=\"$Validade\">$DescricaoComp </td>\n";
					echo "	<td class=\"textonormal\" width=\"22%\" align=\"center\">\n";
					$URL = "../calendario.php?Formulario=CadGestaoFornecedor&Campo=elements[$ElementoComp]";
					echo "  	<input class=\"textonormal\" type=\"text\" name=\"DataCertidaoComp[$i]\" size=\"10\" maxlength=\"10\" value=\"" . $_SESSION['DataCertidaoComp'][$i] . "\">\n";
					echo "		<a href=\"javascript:janela('$URL','Calendario',220,170,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\"></a>\n";
					echo "		<input type=\"hidden\" name=\"CertidaoComplementar[$i]\" value=\"" . $_SESSION['CertidaoComplementar'][$i] . "\">\n";
					echo "	</td>\n";
					echo "</tr>\n";
				}
			}
			echo "	</table>\n";
		}
		$db->disconnect();
?>
								              			</td>
								              		</tr>
								              		<tr>
								              			<td class="textonormal" colspan="2" align="center">
								              				<?

		$Url = "CadIncluirCertidaoComplementar.php?ProgramaOrigem=CadGestaoFornecedor";
		if (!in_array($Url, $_SESSION['GetUrl'])) {
			$_SESSION['GetUrl'][] = $Url;
		}
?>
								              				<input class="botao" type="button" value="Incluir Complementar" onclick="javascript:AbreJanela('<?=$Url;?>',750,170);">
																			<input type="hidden" name="Certidao" value="<?php echo $_SESSION['Certidao'];?>">
								              				<input class="botao" type="button" value="Retirar Complementar" onclick="javascript:enviar('RetirarComplementar');">
								              			</td>
								              		</tr>
								              	</table>
								              </td>
								            </tr>
														<?php

	}
?>
								          </table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
				       	<tr>
		            	<td align="right">
		            		<input type="button" value="Próxima Aba" class="botao" onclick="javascript:enviar('B');">
		            		<input type="button" value="Limpar Tela" class="botao" onclick="javascript:enviar('Limpar');">
		            		<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
		            		<input type="hidden" name="Botao" value="">
		            	</td>
		            </tr>
	     				</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<!-- Fim do Corpo -->
	</table>
	</form>
	<script language="javascript" type="">
	<!--
	document.CadGestaoFornecedor.InscMercantil.focus();
	-->
	</script>
	</body>
	</html>
	<?php

	exit;
}
# Critica aos Campos - Formulário C #
function CriticaAbaQualificEconFinanceira() {
	if ($_SESSION['Botao'] == "Limpar") {
		$_SESSION['Botao'] = "";
		$_SESSION['CapSocial'] = "";
		$_SESSION['CapIntegralizado'] = "";
		$_SESSION['Patrimonio'] = "";
		$_SESSION['IndLiqCorrente'] = "";
		$_SESSION['IndLiqGeral'] = "";
		$_SESSION['IndEndividamento'] = "";
		$_SESSION['IndSolvencia'] = "";
		$_SESSION['DataBalanco'] = "";
		$_SESSION['DataNegativa'] = "";
		$_SESSION['DataContratoEstatuto'] = "";
		$_SESSION['Banco1'] = "";
		$_SESSION['Banco2'] = "";
		$_SESSION['Agencia1'] = "";
		$_SESSION['Agencia2'] = "";
		$_SESSION['ContaCorrente1'] = "";
		$_SESSION['ContaCorrente2'] = "";
	} else {
		$_SESSION['Mens'] = 0;
		$_SESSION['Mensagem'] = "Informe: ";
		if ($_SESSION['CNPJ'] != "" and $_SESSION['TipoHabilitacao'] == "L") {
			if ($_SESSION['CapSocial'] == "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CapSocial.focus();\" class=\"titulo2\">Capital Social </a>";
			} else {
				if( $_SESSION['CapSocial'] == 0 ) {
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						  $_SESSION['Mens']      = 1;
						  $_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CapSocial.focus();\" class=\"titulo2\">A Capital Social deve ser númerico e diferente de zero</a>";
					}
				$_SESSION['CapSocial'] = str_replace(".", "", $_SESSION['CapSocial']);
				if (!SoNumVirg($_SESSION['CapSocial'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CapSocial.focus();\" class=\"titulo2\">Capital Social Válido</a>";
				} else {
					$Numero = Decimal($_SESSION['CapSocial']);
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens'] = 1;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CapSocial.focus();\" class=\"titulo2\">Capital Social Válido</a>";
					} else {
						$_SESSION['CapSocial'] = $Numero;
					}
				}
			}
			if ($_SESSION['CapIntegralizado'] != "") {
				$_SESSION['CapIntegralizado'] = str_replace(".", "", $_SESSION['CapIntegralizado']);
				if (!SoNumVirg($_SESSION['CapIntegralizado'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CapIntegralizado.focus();\" class=\"titulo2\">Capital Integralizado Válido</a>";
				} else {
					$Numero = Decimal($_SESSION['CapIntegralizado']);
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens'] = 1;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.CapIntegralizado.focus();\" class=\"titulo2\">Capital Integralizado Válido</a>";
					} else {
						$_SESSION['CapIntegralizado'] = $Numero;
					}
				}
			}

			if ($_SESSION['Patrimonio'] == "") {
				
				if  ( empty( $_SESSION['MicroEmpresa'] )  )  {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Patrimonio.focus();\" class=\"titulo2\">Patrimônio Líquido</a>";
				}
				
			} else{
				if( $_SESSION['Patrimonio'] == 0 ) {
					if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						$_SESSION['Mens']      = 1;
						$_SESSION['Tipo']      = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Patrimonio.focus();\" class=\"titulo2\">O Patrimônio Líquido deve ser númerico e diferente de zero</a>";
				} else {
					$Numero = Decimal($_SESSION['Patrimonio']);
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens'] = 1;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Patrimonio.focus();\" class=\"titulo2\">Patrimônio Líquido Válido</a>";
					} else {
						$_SESSION['Patrimonio'] = $Numero;
					}
				}
			}

			if ($_SESSION['IndLiqCorrente'] != "") {
				$_SESSION['IndLiqCorrente'] = str_replace(".", "", $_SESSION['IndLiqCorrente']);
				if (!SoNumVirg($_SESSION['IndLiqCorrente'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.IndLiqCorrente.focus();\" class=\"titulo2\">Índice de Liquidez Corrente Válido</a>";
				} else {
					$Numero = Decimal($_SESSION['IndLiqCorrente']);
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens'] = 1;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.IndLiqCorrente.focus();\" class=\"titulo2\">Índice de Liquidez Corrente Válido</a>";
					} else {
						$_SESSION['IndLiqCorrente'] = $Numero;
					}
				}
			}
			if ($_SESSION['IndLiqGeral'] != "") {
				$_SESSION['IndLiqGeral'] = str_replace(".", "", $_SESSION['IndLiqGeral']);
				if (!SoNumVirg($_SESSION['IndLiqGeral'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.IndLiqGeral.focus();\" class=\"titulo2\">Índice de Liquidez Geral Válido</a>";
				} else {
					$Numero = Decimal($_SESSION['IndLiqGeral']);
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens'] = 1;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.IndLiqGeral.focus();\" class=\"titulo2\">Índice de Liquidez Geral Válido</a>";
					} else {
						$_SESSION['IndLiqGeral'] = $Numero;
					}
				}
			}
			if ($_SESSION['IndEndividamento'] != "") {
				$_SESSION['IndEndividamento'] = str_replace(".", "", $_SESSION['IndEndividamento']);
				if (!SoNumVirg($_SESSION['IndEndividamento'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.IndEndividamento.focus();\" class=\"titulo2\">Índice de Endividamento Válido</a>";
				} else {
					$Numero = Decimal($_SESSION['IndEndividamento']);
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens'] = 1;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.IndEndividamento.focus();\" class=\"titulo2\">Índice de Endividamento Válido</a>";
					} else {
						$_SESSION['IndEndividamento'] = $Numero;
					}
				}
			}
			if ($_SESSION['IndSolvencia'] != "") {
				$_SESSION['IndSolvencia'] = str_replace(".", "", $_SESSION['IndSolvencia']);
				if (!SoNumVirg($_SESSION['IndSolvencia'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.IndSolvencia.focus();\" class=\"titulo2\">Índice de Solvência Geral Válido</a>";
				} else {
					$Numero = Decimal($_SESSION['IndSolvencia']);
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens'] = 1;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.IndSolvencia.focus();\" class=\"titulo2\">Índice de Solvência Geral Válido</a>";
					} else {
						$_SESSION['IndSolvencia'] = $Numero;
					}
				}
			}
			
			//echo "Mirco Empresa".$_SESSION['MicroEmpresa'];
			//exit;
			 
			if ($_SESSION['DataBalanco'] == "" )  {
					
				if  ( empty( $_SESSION['MicroEmpresa'] )  )  {					
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataBalanco.focus();\" class=\"titulo2\">Data de validade do balanço</a>";
				} 
				
				
			} else {
				$MensErro = ValidaData($_SESSION['DataBalanco']);
				if ($MensErro != "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataBalanco.focus();\" class=\"titulo2\">Data de validade do balanço Válida</a>";
				}/* else {
					$DataBalancoInv = substr($_SESSION['DataBalanco'], 6, 4) . "-" . substr($_SESSION['DataBalanco'], 3, 2) . "-" . substr($_SESSION['DataBalanco'], 0, 2);
					if ($DataBalancoInv < date("Y-m-d")) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataBalanco.focus();\" class=\"titulo2\">Data de validade do balanço menor que data atual</a>";
					}
				}*/
			}
			if ($_SESSION['DataNegativa'] == "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataNegativa.focus();\" class=\"titulo2\">Data de Certidão Negativa de Falência ou Concordata</a>";
			} else {
				$MensErro = ValidaData($_SESSION['DataNegativa']);
				if ($MensErro != "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataNegativa.focus();\" class=\"titulo2\">Data de Certidão Negativa de Falência ou Concordata Válida</a>";
				}
			}

			if ($_SESSION['DataContratoEstatuto'] != "") {
				$MensErro = ValidaData($_SESSION['DataContratoEstatuto']);
				if ($MensErro != "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataContratoEstatuto.focus();\" class=\"titulo2\">Data de Contrato ou Estatuto Válida</a>";
				}
			}

		}
		if (($_SESSION['ContaCorrente1'] == $_SESSION['ContaCorrente2']) and ($_SESSION['ContaCorrente1'] != "" and $_SESSION['ContaCorrente2'] != "")) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.ContaCorrente1.focus();\" class=\"titulo2\">Contas Correntes Diferentes</a>";
		}
		if ($_SESSION['Banco1'] != "") {
			if (strlen($_SESSION['Banco1']) != 3) {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Banco1.focus();\" class=\"titulo2\">Código do banco 1 deve possuir 3 dígitos</a>";
			} else {
				if ($_SESSION['Agencia1'] == "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Agencia1.focus();\" class=\"titulo2\">Agência do Banco " . $_SESSION['Banco1'] . "</a>";
				} else
					if (strlen($_SESSION['Agencia1']) != 5) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Agencia1.focus();\" class=\"titulo2\">Agência do Banco " . $_SESSION['Banco1'] . " deve possuir 5 dígitos</a>";

					}
				if ($_SESSION['ContaCorrente1'] == "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.ContaCorrente1.focus();\" class=\"titulo2\">Conta Corrente do Banco " . $_SESSION['Banco1'] . "</a>";
				}
			}
		} else {
			if ($_SESSION['Agencia1'] != "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Banco1.focus();\" class=\"titulo2\">Banco da Agência " . $_SESSION['Agencia1'] . "</a>";
			} else
				if ($_SESSION['ContaCorrente1'] != "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Banco1.focus();\" class=\"titulo2\">Banco da Conta Corrente " . $_SESSION['ContaCorrente1'] . "</a>";
				} else {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Banco1.focus();\" class=\"titulo2\">1ª conta de banco é requerida</a>";
				}
		}
		if ($_SESSION['Banco2'] != "") {
			if ($_SESSION['Agencia2'] == "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 1;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Agencia2.focus();\" class=\"titulo2\">Agência do Banco " . $_SESSION['Banco2'] . "</a>";
			}
			if ($_SESSION['ContaCorrente2'] == "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 1;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.ContaCorrente2.focus();\" class=\"titulo2\">Conta Corrente do Banco " . $_SESSION['Banco2'] . "</a>";
			}
		} else {
			if ($_SESSION['Agencia2'] != "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 1;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Banco2.focus();\" class=\"titulo2\">Banco da Agência " . $_SESSION['Agencia2'] . "</a>";
			} else {
				if ($_SESSION['ContaCorrente2'] != "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 1;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.ContaCorrente2.focus();\" class=\"titulo2\">Banco da Conta Corrente " . $_SESSION['ContaCorrente2'] . "</a>";
				}
			}
		}

		# Função que critica a parte da Gestão #
		CriticaGestao();
	}


	if (($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Limpar")) {
		ExibeAbaQualificEconFinanceira();
	}
}

# Exibe Aba Qualificação Econômica e Financeira - Formulário C #
function ExibeAbaQualificEconFinanceira() {
?>
	<html>
	<?php

	# Carrega o layout padrão #
	layout();
?>
	<script language="javascript" src="../janela.js" type="text/javascript"></script>
	<script language="javascript" type="">
	<!--
	function Submete(Destino) {
		 	document.CadGestaoFornecedor.Destino.value = Destino;
		 	document.CadGestaoFornecedor.submit();
	}
	function enviar(valor){
		document.CadGestaoFornecedor.Botao.value = valor;
		document.CadGestaoFornecedor.submit();
	}
	function remeter(valor){
	 	document.CadGestaoFornecedor.Destino.value = 'C';
		document.CadGestaoFornecedor.Botao.value   = valor;
		document.CadGestaoFornecedor.submit();
	}
	function AbreJanela(url,largura,altura) {
		window.open(url,'pagina','status=no,scrollbars=no,left=20,top=150,width='+largura+',height='+altura);
	}
	<?php MenuAcesso(); ?>
	//-->
	</script>
	<link rel="Stylesheet" type="Text/Css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadGestaoFornecedor.php" method="post" name="CadGestaoFornecedor">
	<br><br><br><br><br>
	<table cellpadding="3" border="0" summary="">
		<!-- Caminho -->
	  <tr>
	    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
	    <td align="left" class="textonormal" colspan="2">
	      <font class="titulo2">|</font>
	      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedor > Cadastro e Gestão
	    </td>
	  </tr>
	  <!-- Fim do Caminho-->

		<!-- Erro -->
		<tr>
		  <td width="100"></td>
		  <td align="left" colspan="2">
	  		<?php if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],1);}?>
	    </td>
		</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
			<td width="100"></td>
			<td class="textonormal">
				<table  border="0" cellspacing="0" cellpadding="3" summary="">
					<tr>
		      	<td class="textonormal">
		        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
		          	<tr>
		            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
			    					ALTERAR - CADASTRO E GESTÃO DE FORNECEDOR
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal">
		      	    		<p align="justify">
		        	    		Informe os itens obrigatórios que estão marcados com *. O Índice de Liquidez Corrente e Liquidez Geral não pode ser menor que 1.<br><br>
		        	    		Para efetivar a alteração, clique no botão "Alterar" da aba Qualificação Técnica.<br><br>
			       	    		Para excluir o fornecedor do cadastro clique no botão "Excluir" da aba Habilitação Jurídica.
		          	   	</p>
		          		</td>
			        	</tr>
								<?php ExibeGestao(); ?>
 			        	<tr>
									<td align="left">
										<?php echo NavegacaoAbas(off,off,on,off,off); ?>
										<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr bgcolor="#bfdaf2">
												<td colspan="4">
								          <table class="textonormal" border="0" width="100%" summary="">
														<tr>
															<td class="textonormal" height="20">
																<?php if( $_SESSION['CNPJ'] != 0 ){ echo "CNPJ\n"; }else{ echo "CPF\n"; } ?>
					          	    		</td>
					          	    		<td class="textonormal">
																<?php

	if ($_SESSION['CNPJ'] != 0) {
		echo substr($_SESSION['CNPJ'], 0, 2) . "." . substr($_SESSION['CNPJ'], 2, 3) . "." . substr($_SESSION['CNPJ'], 5, 3) . "/" . substr($_SESSION['CNPJ'], 8, 4) . "-" . substr($_SESSION['CNPJ'], 12, 2);
	} else {
		echo substr($_SESSION['CPF'], 0, 3) . "." . substr($_SESSION['CPF'], 3, 3) . "." . substr($_SESSION['CPF'], 6, 3) . "/" . substr($_SESSION['CPF'], 9, 2);
	}
?>
					          	    			<input type="hidden" name="CPF_CNPJ" value="<?php echo $_SESSION['CPF_CNPJ'] ?>">
					          	    			<input type="hidden" name="CNPJ" value="<?php echo $_SESSION['CNPJ'] ?>">
					          	    			<input type="hidden" name="CPF" value="<?php echo $_SESSION['CPF'] ?>">
					          	    		</td>
					            			</tr>
								            <tr>
								              <td class="textonormal" height="20">
								              	<?php if( $_SESSION['CNPJ'] != 0 ){ echo "Razão Social\n"; }else{ echo "Nome\n"; } ?>
								              </td>
								              <td class="textonormal">
								              	<?php echo $_SESSION['RazaoSocial'];?>
					            	  			<input type="hidden" name="Origem" value="C">
																<input type="hidden" name="Destino" value="C">
															</td>
								            </tr>
														<?php if( $_SESSION['CNPJ'] != 0 and $_SESSION['TipoHabilitacao'] == "L" ){ ?>
														<tr>
								              <td class="textonormal">Capital Social Subscrito* </td>
								              <td class="textonormal">
								              	<input type="text" name="CapSocial" size="20" maxlength="19" value="<?php echo $_SESSION['CapSocial'];?>" class="textonormal">
								              </td>
								            </tr>
														<tr>
								              <td class="textonormal">Capital Integralizado </td>
								              <td class="textonormal">
								              	<input type="text" name="CapIntegralizado" size="20" maxlength="19" value="<?php echo $_SESSION['CapIntegralizado'];?>" class="textonormal">
								              </td>
								            </tr>
								            <tr>
								              <?php   if ( empty( $_SESSION['MicroEmpresa']) ) { ?>
								             		 <td class="textonormal">Patrimônio Líquido*</td>
								              <?php } else  { ?>
								              		 <td class="textonormal">Patrimônio Líquido</td>
								              <?php } ?>
								              <td class="textonormal">
								              	<input type="text" name="Patrimonio" size="20" maxlength="19" value="<?php echo $_SESSION['Patrimonio'];?>" class="textonormal">
								              </td>
								            </tr>
														<tr>
								              <td class="textonormal" width="45%">Índice de Liquidez Corrente </td>
								              <td class="textonormal">
								              	<input type="text" name="IndLiqCorrente" size="20" maxlength="19" value="<?php echo $_SESSION['IndLiqCorrente'];?>" class="textonormal">
								              </td>
								            </tr>
								            <tr>
								              <td class="textonormal">Índice de Liquidez Geral </td>
															<td class="textonormal">
																<input type="text" name="IndLiqGeral" size="20" maxlength="19" value="<?php echo $_SESSION['IndLiqGeral'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Índice de Endividamento</td>
															<td class="textonormal">
																<input type="text" name="IndEndividamento" size="20" maxlength="19" value="<?php echo $_SESSION['IndEndividamento'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Índice de Solvência Geral</td>
															<td class="textonormal">
																<input type="text" name="IndSolvencia" size="20" maxlength="19" value="<?php echo $_SESSION['IndSolvencia'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								            <?php   if ( empty( $_SESSION['MicroEmpresa']) ) { ?>
										              <td class="textonormal">Data de validade do balanço*</td>
   								            <?php } else  { ?>
										              <td class="textonormal">Data de validade do balanço</td>
										    <?php  } ?>      
										              
													<td class="textonormal">
					              				<?php $URL = "../calendario.php?Formulario=CadGestaoFornecedor&Campo=DataBalanco" ?>
										          	<input type="text" name="DataBalanco" size="10" maxlength="10" value="<?php echo $_SESSION['DataBalanco'];?>" class="textonormal">
																<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a> dd/mm/aaaa
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Data de Certidão Negativa de Falência ou Concordata*</td>
															<td class="textonormal">
					              				<?php $URL = "../calendario.php?Formulario=CadGestaoFornecedor&Campo=DataNegativa" ?>
										          	<input type="text" name="DataNegativa" size="10" maxlength="10" value="<?php echo $_SESSION['DataNegativa'];?>" class="textonormal">
																<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a> dd/mm/aaaa
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Data de última alteração de contrato ou estatuto</td>
															<td class="textonormal">
					              				<?php $URL = "../calendario.php?Formulario=CadGestaoFornecedor&Campo=DataContratoEstatuto" ?>
										          	<input type="text" name="DataContratoEstatuto" size="10" maxlength="10" value="<?php echo $_SESSION['DataContratoEstatuto'];?>" class="textonormal">
																<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a> dd/mm/aaaa
															</td>
								            </tr>
														<tr><td><br></td></tr>
														<?php } ?>
														<tr>
								            	<td colspan="2">
									            	<table  align="center" border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
									              	<tr>
									              		<td class="textoabasoff" bgcolor="#75ADE6" align="center">BANCO</td>
									              		<td class="textoabasoff" bgcolor="#75ADE6" align="center">AGÊNCIA </td>
									              		<td class="textoabasoff" bgcolor="#75ADE6" align="center">CONTA CORRENTE</td>
									              	</tr>
									              	<tr>
									              		<td class="textonormal" align="center">
									              			<input type="text" name="Banco1" size="3" maxlength="3" value="<?php echo $_SESSION['Banco1'];?>" class="textonormal">
									              		</td>
									              		<td class="textonormal" align="center">
									              			<input type="text" name="Agencia1" size="11" maxlength="8" value="<?php echo $_SESSION['Agencia1'];?>" class="textonormal">
									              		</td>
											              <td class="textonormal" align="center">
											              	<input type="text" name="ContaCorrente1" size="13" maxlength="10" value="<?php echo $_SESSION['ContaCorrente1'];?>" class="textonormal">
											              </td>
											          	</tr>
											            <tr>
											          	  <td class="textonormal" align="center">
											          	  	<input type="text" name="Banco2" size="3" maxlength="3" value="<?php echo $_SESSION['Banco2'];?>" class="textonormal">
											          	  </td>
											              <td class="textonormal" align="center">
											              	<input type="text" name="Agencia2" size="11" maxlength="8" value="<?php echo $_SESSION['Agencia2']; ?>" class="textonormal">
											              </td>
											              <td class="textonormal" align="center">
											              	<input type="text" name="ContaCorrente2" size="13" maxlength="10" value="<?php echo $_SESSION['ContaCorrente2'];?>" class="textonormal">
											              </td>
									            		</tr>
									            	</table>
									            </td>
								            </tr>
								          </table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
		          	<tr>
									<td align="right">
		            		<input type="button" value="Próxima Aba" class="botao" onclick="javascript:enviar('C');">
										<input type="button" value="Limpar Tela" class="botao" onclick="javascript:enviar('Limpar');">
			            	<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
										<input type="hidden" name="Botao" value="">
									</td>
		            </tr>
	     				</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<!-- Fim do Corpo -->
	</table>
	</form>
	<script language="javascript" type="">
	<!--
	<?php if( $_SESSION['CNPJ'] != 0 ){ ?>
	document.CadGestaoFornecedor.CapSocial.focus();
	<?php } ?>
	-->
	</script>
	</body>
	</html>
	<?php

	exit;
}

# Critica aos Campos - Formulário D #
function CriticaAbaQualificTecnica() {
	if ($_SESSION['Botao'] == "Limpar") {
		$_SESSION['Botao'] = "";
		$_SESSION['RegistroEntidade'] = "";
		$_SESSION['NomeEntidade'] = "";
		$_SESSION['DataVigencia'] = "";
		$_SESSION['RegistroTecnico'] = "";
	}
	elseif ($_SESSION['Botao'] == "RetirarAutorizacao") {
		if (count($_SESSION['AutorizacaoNome']) != 0) {
			for ($i = 0; $i < count($_SESSION['AutorizacaoNome']); $i++) {
				if ($_SESSION['CheckAutorizacao'][$i] == "") {
					$QtdAutorizacao++;
					$_SESSION['CheckAutorizacao'][$i] = "";
					$_SESSION['AutorizacaoNome'][$QtdAutorizacao -1] = $_SESSION['AutorizacaoNome'][$i];
					$_SESSION['AutorizacaoRegistro'][$QtdAutorizacao -1] = $_SESSION['AutorizacaoRegistro'][$i];
					$_SESSION['AutorizacaoData'][$QtdAutorizacao -1] = $_SESSION['AutorizacaoData'][$i];
					$_SESSION['AutoEspecifica'][$QtdAutorizacao -1] = $_SESSION['AutoEspecifica'][$i];
				}
			}
			$_SESSION['AutorizacaoNome'] = array_slice($_SESSION['AutorizacaoNome'], 0, $QtdAutorizacao);
			$_SESSION['AutorizacaoRegistro'] = array_slice($_SESSION['AutorizacaoRegistro'], 0, $QtdAutorizacao);
			$_SESSION['AutorizacaoData'] = array_slice($_SESSION['AutorizacaoData'], 0, $QtdAutorizacao);
			$_SESSION['AutoEspecifica'] = array_slice($_SESSION['AutoEspecifica'], 0, $QtdAutorizacao);
			if ($_SESSION['AutorizacaoNome'][0] == "" or count($_SESSION['AutorizacaoNome']) == 0) {
				unset ($_SESSION['AutorizacaoNome']);
			}
			if ($_SESSION['AutorizacaoRegistro'][0] == "" or count($_SESSION['AutorizacaoRegistro']) == 0) {
				unset ($_SESSION['AutorizacaoRegistro']);
			}
			if ($_SESSION['AutorizacaoData'][0] == "" or count($_SESSION['AutorizacaoData']) == 0) {
				unset ($_SESSION['AutorizacaoData']);
			}
			if ($_SESSION['AutoEspecifica'][0] == "" or count($_SESSION['AutoEspecifica']) == 0) {
				unset ($_SESSION['AutoEspecifica']);
			}
			$_SESSION['AutorizaNome'] = "";
			$_SESSION['AutorizaRegistro'] = "";
			$_SESSION['AutorizaData'] = "";
			$_SESSION['AutoEspecifica'] = "";
		}
		ExibeAbas("D");
	}
	elseif ($_SESSION['Botao'] == "RetirarGrupos") {
		$QtdMateriais=0;
		if (count($_SESSION['Materiais']) != 0) {
			for ($i = 0; $i < count($_SESSION['Materiais']); $i++) {
				if ($_SESSION['CheckMateriais'][$i] == "") {
					$QtdMateriais++;
					$_SESSION['CheckMateriais'][$i] = "";
					$_SESSION['Materiais'][$QtdMateriais-1] = $_SESSION['Materiais'][$i];
				}
			}
			$_SESSION['Materiais'] = array_slice($_SESSION['Materiais'],0,$QtdMateriais); //alterado de array_slice pra array_splice - Everton
		}
		$QtdServicos=0;
		if (count($_SESSION['Servicos']) != 0) {
			for ($i = 0; $i < count($_SESSION['Servicos']); $i++) {
				if ($_SESSION['CheckServicos'][$i] == "") {
					$QtdServicos++;
					$_SESSION['CheckServicos'][$i] = "";
					$_SESSION['Servicos'][$QtdServicos-1] = $_SESSION['Servicos'][$i];
				}
			}
			$_SESSION['Servicos'] = array_slice($_SESSION['Servicos'],0,$QtdServicos); //alterado de array_slice pra array_splice - Everton
		}
		ExibeAbas("D");
	} else {
		$_SESSION['Mens'] = "";
		$_SESSION['Mensagem'] = "Informe: ";
		if (($_SESSION['RegistroEntidade'] != "") and (!SoNumeros($_SESSION['RegistroEntidade']))) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.RegistroEntidade.focus();\" class=\"titulo2\">Registro da Entidade Válido</a>";
		}
		$MensErro = ValidaData($_SESSION['DataVigencia']);
		if (($_SESSION['DataVigencia'] != "") and ($MensErro != "")) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataVigencia.focus();\" class=\"titulo2\">$MensErro</a>";
		}
		if (($_SESSION['RegistroTecnico'] != "") and (!SoNumeros($_SESSION['RegistroTecnico']))) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.RegistroTecnico.focus();\" class=\"titulo2\">Registro ou Inscrição do Técnico Válida</a>";
		}

		# Constuindo o array de Autorização Específica #
		if ($_SESSION['AutorizaNome'] != "" and $_SESSION['AutorizaRegistro'] != "" and $_SESSION['AutorizaData'] != "") {
			if (!session_is_registered('AutorizacaoNome')) {
				$_SESSION['AutorizacaoNome'] = array ();
			}
			if (!session_is_registered('AutorizacaoRegistro')) {
				$_SESSION['AutorizacaoRegistro'] = array ();
			}
			if (!session_is_registered('AutorizacaoData')) {
				$_SESSION['AutorizacaoData'] = array ();
			}
			if (!session_is_registered('AutoEspecifica')) {
				$_SESSION['AutoEspecifica'] = array ();
			}
			$AutorizacaoEspecifica = $_SESSION['AutorizaNome'] . "#" . $_SESSION['AutorizaRegistro'];
			if ($_SESSION['AutoEspecifica'] == "" || !in_array($AutorizacaoEspecifica, $_SESSION['AutoEspecifica'])) {
				$_SESSION['AutorizacaoNome'][count($_SESSION['AutorizacaoNome'])] = $_SESSION['AutorizaNome'];
				$_SESSION['AutorizacaoRegistro'][count($_SESSION['AutorizacaoRegistro'])] = $_SESSION['AutorizaRegistro'];
				$_SESSION['AutorizacaoData'][count($_SESSION['AutorizacaoData'])] = $_SESSION['AutorizaData'];
				$_SESSION['AutoEspecifica'][count($_SESSION['AutoEspecifica'])] = $AutorizacaoEspecifica;
			}
		}

		# Constuindo os arrays de Grupos de Fornecimento #
		if ($Mens == 0) {
			if ($_SESSION['Materiais'][0] == "" and $_SESSION['Servicos'][0] == "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] = "Pelo menos um Grupo de Fornecimento deve ser Incluído";
			}

			# Função que critica a parte da Gestão #
			CriticaGestao();
		}
	}
	if (($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Limpar") or ($_SESSION['Email'] == "" and $_SESSION['Botao'] == "Alterar")) {
		ExibeAbaQualificTecnica();
	}
}

# Exibe Aba Qualificação Técnica - Formulário D #
function ExibeAbaQualificTecnica() {
?>
	<html>
	<?php

	# Carrega o layout padrão #
	layout();
?>
	<script language="javascript" src="../janela.js" type="text/javascript"></script>
	<script language="javascript" type="">
	<!--
	function Submete(Destino) {
	 	document.CadGestaoFornecedor.Destino.value = Destino;
	 	document.CadGestaoFornecedor.submit();
	}
	function enviar(valor){
		document.CadGestaoFornecedor.Botao.value = valor;
		document.CadGestaoFornecedor.submit();
	}
	function remeter(valor){
	 	document.CadGestaoFornecedor.Destino.value = 'D';
		document.CadGestaoFornecedor.Botao.value = valor;
		document.CadGestaoFornecedor.submit();
	}
	function AbreJanela(url,largura,altura) {
		window.open(url,'pagina','status=no,scrollbars=no,left=20,top=130,width='+largura+',height='+altura);
	}
	<?php MenuAcesso(); ?>
	//-->
	</script>
	<link rel="Stylesheet" type="Text/Css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadGestaoFornecedor.php" method="post" name="CadGestaoFornecedor">
	<br><br><br><br><br>
	<table cellpadding="3" border="0" summary="">
		<!-- Caminho -->
	  <tr>
	    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
	    <td align="left" class="textonormal" colspan="2">
	      <font class="titulo2">|</font>
	      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedor > Cadastro e Gestão
	    </td>
	  </tr>
	  <!-- Fim do Caminho-->

		<!-- Erro -->
		<tr>
		  <td width="100"></td>
		  <td align="left" colspan="2">
	  		<?php if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],1);}?>
	    </td>
		</tr>
		<!-- Fim do Erro -->

		<!-- Corpo -->
		<tr>
			<td width="100"></td>
			<td class="textonormal">
				<table  border="0" cellspacing="0" cellpadding="3" summary="">
					<tr>
		      	<td class="textonormal">
		        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
		          	<tr>
		            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
			    					ALTERAR - CADASTRO E GESTÃO DE FORNECEDOR
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal">
		      	    		<p align="justify">
		        	    		Informe os dados abaixo e clique no botão "Alterar".<br>
		        	    		Para informar as grupos de fornecimento clique no botão "Incluir Grupos". Se desejar eliminar um grupo de fornecimento já informada, marque o(s) grupo(s) desejado(s) e clique no botão "Retirar Grupos".<br><br>
		        	    		Para efetivar a alteração, clique no botão "Alterar" da aba Qualificação Técnica.<br><br>
			       	    		Para excluir o fornecedor do cadastro clique no botão "Excluir" da aba Habilitação Jurídica.
		          	   	</p>
		          		</td>
			        	</tr>
			        	<?php ExibeGestao(); ?>
			        	<tr>
									<td align="left">
										<?php echo NavegacaoAbas(off,off,off,on,off); ?>
										<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr bgcolor="#bfdaf2">
												<td colspan="4">
								          <table class="textonormal" border="0" align="left" width="100%" summary="">
														<tr>
															<td class="textonormal" height="20">
																<?php if( $_SESSION['CNPJ'] != 0 ){ echo "CNPJ\n"; }else{ echo "CPF\n"; } ?>
					          	    		</td>
					          	    		<td class="textonormal">
																<?php

	if ($_SESSION['CNPJ'] != 0) {
		echo substr($_SESSION['CNPJ'], 0, 2) . "." . substr($_SESSION['CNPJ'], 2, 3) . "." . substr($_SESSION['CNPJ'], 5, 3) . "/" . substr($_SESSION['CNPJ'], 8, 4) . "-" . substr($_SESSION['CNPJ'], 12, 2);
	} else {
		echo substr($_SESSION['CPF'], 0, 3) . "." . substr($_SESSION['CPF'], 3, 3) . "." . substr($_SESSION['CPF'], 6, 3) . "/" . substr($_SESSION['CPF'], 9, 2);
	}
?>
					          	    			<input type="hidden" name="CPF_CNPJ" value="<?php echo $_SESSION['CPF_CNPJ'] ?>">
					          	    			<input type="hidden" name="CNPJ" value="<?php echo $_SESSION['CNPJ'] ?>">
					          	    			<input type="hidden" name="CPF" value="<?php echo $_SESSION['CPF'] ?>">
					          	    		</td>
					            			</tr>
								            <tr>
								              <td class="textonormal" height="20" width="45%">
								              	<?php if( $_SESSION['CNPJ'] != 0 ){ echo "Razão Social\n"; }else{ echo "Nome\n"; } ?>
								              </td>
								              <td class="textonormal">
								              	<?php echo $_SESSION['RazaoSocial']; ?>
					            	  			<input type="hidden" name="Origem" value="D">
																<input type="hidden" name="Destino" value="D">
															</td>
								            </tr>
								          </table>
								      	</td>
								      </tr>

											<?php

	if ($_SESSION['TipoHabilitacao'] == "L") {
?>

											<tr bgcolor="#bfdaf2">
											  <td colspan="4">
								          <table class="textonormal" border="0" align="left" width="100%" summary="">
														<tr><td bgcolor="#bfdaf2" colspan="4"><br></td></tr>
								            <tr>
								            	<td class="textonormal" colspan="">
																<table border="0" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
			        			          		<tr>
								              			<td bgcolor="#75ADE6" class="textoabasoff" colspan="4" align="center">ENTIDADE PROFISSIONAL COMPETENTE</td>
								              		</tr>
											            <tr>
											              <td class="textonormal">Nome da Entidade </td>
											              <td class="textonormal">
											              	<input type="text" name="NomeEntidade" size="25" maxlength="18" value="<?php echo $_SESSION['NomeEntidade'];?>" class="textonormal">
											              </td>
											            </tr>
											            <tr>
											              <td class="textonormal" width="45%">Registro ou Inscrição </td>
											              <td class="textonormal">
											              	<input type="text" name="RegistroEntidade" size="10" maxlength="10" value="<?php echo $_SESSION['RegistroEntidade'];?>" class="textonormal">
											              </td>
											            </tr>
																	<tr>
											              <td class="textonormal">Data da Vigência </td>
											              <td class="textonormal">
								              				<?php $URL = "../calendario.php?Formulario=CadGestaoFornecedor&Campo=DataVigencia" ?>
													          	<input type="text" name="DataVigencia" size="10" maxlength="10" value="<?php echo $_SESSION['DataVigencia'];?>" class="textonormal">
																			<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
																		</td>
											           	</tr>
											            <tr>
											              <td class="textonormal" width="45%">Registro ou Inscrição do Técnico</td>
											              <td class="textonormal">
											              	<input type="text" name="RegistroTecnico" size="10" maxlength="10" value="<?php echo $_SESSION['RegistroTecnico'];?>" class="textonormal">
											              </td>
											            </tr>
										          </table>
								              </td>
								            </tr>
								          </table>
												</td>
											</tr>
					            <tr><td bgcolor="#bfdaf2" colspan="4"><br></td></tr>
											<tr>
					              <td class="textonormal" colspan="4">
													<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
        			          		<tr>
					              			<td bgcolor="#75ADE6" class="textoabasoff" colspan="4" align="center">AUTORIZAÇÃO ESPECÍFICA</td>
					              		</tr>
					              		<?php

		if ($_SESSION['CarregaAutorizacao'] == 0) {
			# Mostra as autorizações específicas já cadastradas do Inscrito #
			$db = Conexao();
			$sql = "SELECT NFORAENOMA, AFORAENUMA, DFORAEVIGE ";
			$sql .= "  FROM SFPC.TBFORNAUTORIZACAOESPECIFICA ";
			$sql .= " WHERE AFORCRSEQU = " . $_SESSION['Sequencial'];
			$res = $db->query($sql);
			if (db :: isError($res)) {
				ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
			} else {
				$Rows = $res->numRows();
				if ($Rows != 0) {
					echo "<tr>\n";
					echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\" width=\"5%\">&nbsp;</td>\n";
					echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\">NOME DA ENTIDADE EMISSORA</td>\n";
					echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\">REGISTRO OU INSCRIÇÃO</td>\n";
					echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\">DATA DE VIGÊNCIA</td>\n";
					echo "</tr>\n";
					for ($i = 0; $i < $Rows; $i++) {
						$Linha = $res->fetchRow();
						$_SESSION['AutorizacaoNome'][$i] = $Linha[0];
						$_SESSION['AutorizacaoRegistro'][$i] = $Linha[1];
						$_SESSION['AutorizacaoData'][$i] = substr($Linha[2], 8, 2) . "/" . substr($Linha[2], 5, 2) . "/" . substr($Linha[2], 0, 4);
						$_SESSION['AutoEspecifica'][$i] = $_SESSION['AutorizacaoNome'][$i] . "#" . $_SESSION['AutorizacaoRegistro'][$i];
						echo "			<tr>\n";
						echo "				<td class=\"textonormal\" width=\"5%\">\n";
						echo "					<input type=\"checkbox\" name=\"CheckAutorizacao[$i]\" value=\"$i\">\n";
						echo "				</td>\n";
						echo "				<td class=\"textonormal\" width=\"*\">" . $_SESSION['AutorizacaoNome'][$i] . "</td>\n";
						echo "				<td class=\"textonormal\" width=\"20%\">" . $_SESSION['AutorizacaoRegistro'][$i] . "</td>\n";
						echo "				<td class=\"textonormal\" width=\"15%\" align=\"center\">" . $_SESSION['AutorizacaoData'][$i] . "</td>\n";
						echo "			</tr>\n";
					}
				}
			}
			$db->disconnect();
			$_SESSION['CarregaAutorizacao'] = 1;
		} else {
			if (count($_SESSION['AutorizacaoNome']) != 0) {
				echo "<tr>\n";
				echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\" width=\"5%\">&nbsp;</td>\n";
				echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\">NOME DA ENTIDADE EMISSORA</td>\n";
				echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\">REGISTRO OU INSCRIÇÃO</td>\n";
				echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\">DATA DE VIGÊNCIA</td>\n";
				echo "</tr>\n";
				for ($i = 0; $i < count($_SESSION['AutorizacaoNome']); $i++) {
					$_SESSION['AutoEspecifica'][$i] = $_SESSION['AutorizacaoNome'][$i] . "#" . $_SESSION['AutorizacaoRegistro'][$i];
					echo "			<tr>\n";
					echo "				<td class=\"textonormal\" width=\"5%\">\n";
					echo "					<input type=\"checkbox\" name=\"CheckAutorizacao[$i]\" value=\"$i\">\n";
					echo "				</td>\n";
					echo "				<td class=\"textonormal\" width=\"*\">" . $_SESSION['AutorizacaoNome'][$i] . "</td>\n";
					echo "				<td class=\"textonormal\" width=\"20%\">" . $_SESSION['AutorizacaoRegistro'][$i] . "</td>\n";
					echo "				<td class=\"textonormal\" width=\"15%\" align=\"center\">" . $_SESSION['AutorizacaoData'][$i] . "</td>\n";
					echo "			</tr>\n";
				}
			}
		}
?>
					              		<tr>
					              			<td class="textonormal" colspan="4" align="center">
					              				<?

		$Url = "CadIncluirAutorizacao.php?ProgramaOrigem=CadGestaoFornecedor";
		if (!in_array($Url, $_SESSION['GetUrl'])) {
			$_SESSION['GetUrl'][] = $Url;
		}
?>
					              				<input class="botao" type="button" value="Incluir Autorização" onclick="javascript:AbreJanela('<?=$Url;?>',750,270);">
																<input class="botao" type="button" value="Retirar Autorizações Marcadas" onclick="javascript:enviar('RetirarAutorizacao');">
																<input type="hidden" name="AutorizaNome" value="<?php echo $_SESSION['AutorizaNome'];?>">
																<input type="hidden" name="AutorizaRegistro" value="<?php echo $_SESSION['AutorizaRegistro'];?>">
					            	  			<input type="hidden" name="AutorizaData" value="<?php echo $_SESSION['AutorizaData'];?>">
					              			</td>
					              		</tr>
					              	</table>
					              </td>
					            </tr>
											<tr><td bgcolor="#bfdaf2" colspan="4"><br></td></tr>
											<?php

	}
?>

											<tr>
					              <td class="textonormal" colspan="4">
													<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
        			          		<tr>
					              			<td bgcolor="#75ADE6" class="textoabasoff" colspan="2" align="center">GRUPOS DE FORNECIMENTO (OBJETO SOCIAL)</td>
					              		</tr>
			              				<?php

	$db = Conexao();
	if ($_SESSION['CarregaGrupos'] == 0) {
		# Mostra os grupos de materiais já cadastrados do Fornecedor #
		$sql = "SELECT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
		$sql .= "  FROM SFPC.TBGRUPOFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B  ";
		$sql .= " WHERE A.AFORCRSEQU = " . $_SESSION['Sequencial'] . " AND A.CGRUMSCODI = B.CGRUMSCODI ";
	    $sql .= "   AND B.FGRUMSTIPO = 'M' ORDER BY 1,3";
		$res = $db->query($sql);
		if (db :: isError($res)) {
			ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
		} else {
			$Rows = $res->numRows();
			if ($Rows != 0) {
				# Mostra os grupos de materiais cadastrados #
				echo "<tr>\n";
				echo "	<td bgcolor=\"#DDECF9\" class=\"textonormal\" colspan=\"2\" align=\"center\">MATERIAIS</td>\n";
				echo "</tr>\n";
				$DescricaoGrupoAntes = "";
				for ($i = 0; $i < $Rows; $i++) {
					$Linha = $res->fetchRow();
					$DescricaoGrupo = substr($Linha[2], 0, 75);
					$_SESSION['Materiais'][$i] = "M#" . $Linha[1];
					if ($DescricaoGrupoAntes != $DescricaoGrupo) {
						echo "			<tr>\n";
						echo "				<td class=\"textonormal\" width=\"5%\">\n";
						echo "					<input type=\"checkbox\" name=\"CheckMateriais[$i]\" value=\"" . $_SESSION['Materiais'][$i] . "\">\n";
						echo "				</td>\n";
						echo "				<td class=\"textonormal\" width=\"*\">$DescricaoGrupo</td>\n";
						echo "			</tr>\n";
					}
					$DescricaoGrupoAntes = $DescricaoGrupo;
				}
			}
		}

		# Mostra os grupos de serviços já cadastrados do Fornecedor #
		$sql = "SELECT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
		$sql .= "  FROM SFPC.TBGRUPOFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B  ";
		$sql .= " WHERE A.AFORCRSEQU = " . $_SESSION['Sequencial'] . " AND A.CGRUMSCODI = B.CGRUMSCODI ";
		$sql .= "   AND B.FGRUMSTIPO = 'S' ORDER BY 1,3";
		$res = $db->query($sql);
		if (db :: isError($res)) {
			ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
		} else {
			$Rows = $res->numRows();
			if ($Rows != 0) {
				# Mostra os grupos de serviços cadastrados
				echo "<tr>\n";
				echo "	<td bgcolor=\"#DDECF9\" class=\"textonormal\" colspan=\"2\" align=\"center\">SERVIÇOS</td>\n";
				echo "</tr>\n";
				$DescricaoGrupoAntes = "";
				for ($i = 0; $i < $Rows; $i++) {
					$Linha = $res->fetchRow();
					$DescricaoGrupo = substr($Linha[2], 0, 75);
					$_SESSION['Servicos'][$i] = "S#" . $Linha[1];
					if ($DescricaoGrupo != $DescricaoGrupoAntes) {
						echo "			<tr>\n";
						echo "				<td class=\"textonormal\" width=\"5%\">\n";
						echo "					<input type=\"checkbox\" name=\"CheckServicos[$i]\" value=\"" . $_SESSION['Servicos'][$i] . "\">\n";
						echo "				</td>\n";
						echo "				<td class=\"textonormal\" width=\"*\">$DescricaoGrupo</td>\n";
						echo "			</tr>\n";
					}
					$DescricaoGrupoAntes = $DescricaoGrupo;
				}
			}
		}
		$_SESSION['CarregaGrupos'] = 1;
	} else {
		if (count($_SESSION['Materiais']) != 0) {
			sort($_SESSION['Materiais']);
			echo "<tr>\n";
			echo "	<td bgcolor=\"#DDECF9\" class=\"textonormal\" colspan=\"2\" align=\"center\">MATERIAIS</td>\n";
			echo "</tr>\n";
			$DescricaoGrupoAntes = "";
			for ($i = 0; $i < count($_SESSION['Materiais']); $i++) {
				$GrupoMaterial = split("#", $_SESSION['Materiais'][$i]);
				$db = Conexao();
				$sql = "SELECT A.CGRUMSCODI, A.EGRUMSDESC ";
				$sql .= "  FROM SFPC.TBGRUPOMATERIALSERVICO A ";
				$sql .= " WHERE A.CGRUMSCODI = " . $GrupoMaterial[1] . " ";
				$sql .= " ORDER BY 2";
				$res = $db->query($sql);
				if (db :: isError($res)) {
					ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
					$Linha = $res->fetchRow();
					$Rows++;
					$DescricaoGrupo = substr($Linha[1], 0, 75);
					if ($DescricaoGrupo != $DescricaoGrupoAntes) {
						echo "			<tr>\n";
						echo "				<td class=\"textonormal\" width=\"5%\">\n";
						echo "					<input type=\"checkbox\" name=\"CheckMateriais[$i]\" value=\"" . $_SESSION['Materiais'][$i] . "\">\n";
						echo "				</td>\n";
						echo "				<td class=\"textonormal\" width=\"*\">$DescricaoGrupo</td>\n";
						echo "			</tr>\n";
					}
				}
				$DescricaoGrupoAntes = $DescricaoGrupo;
			}
			$db->disconnect();
		}
		if (count($_SESSION['Servicos']) != 0) {
			sort($_SESSION['Servicos']);
			echo "<tr>\n";
			echo "	<td bgcolor=\"#DDECF9\" class=\"textonormal\" colspan=\"2\" align=\"center\">SERVIÇOS</td>\n";
			echo "</tr>\n";
			$DescricaoGrupoAntes = "";
			for ($i = 0; $i < count($_SESSION['Servicos']); $i++) {
				$GrupoServico = split("#", $_SESSION['Servicos'][$i]);
				$db = Conexao();
				$sql = "SELECT A.CGRUMSCODI, A.EGRUMSDESC ";
				$sql .= "  FROM SFPC.TBGRUPOMATERIALSERVICO A ";
				$sql .= " WHERE A.CGRUMSCODI = " . $GrupoServico[1] . " ";
				$sql .= " ORDER BY 2";
				$res = $db->query($sql);
				if (db :: isError($res)) {
					ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
				} else {
					$Linha = $res->fetchRow();
					$Rows++;
					$DescricaoGrupo = substr($Linha[1], 0, 75);
					if ($DescricaoGrupo != $DescricaoGrupoAntes) {
						echo "			<tr>\n";
						echo "				<td class=\"textonormal\" width=\"5%\">\n";
						echo "					<input type=\"checkbox\" name=\"CheckServicos[$i]\" value=\"" . $_SESSION['Servicos'][$i] . "\">\n";
						echo "				</td>\n";
						echo "				<td class=\"textonormal\" width=\"*\">$DescricaoGrupo</td>\n";
						echo "			</tr>\n";
					}
				}
				$DescricaoGrupoAntes = $DescricaoGrupo;
			}
			$db->disconnect();
		}
	}
?>
					              		<tr>
					              			<td class="textonormal" colspan="2" align="center">
					              				<?

	$Url = "CadIncluirGrupos.php?ProgramaOrigem=CadGestaoFornecedor";
	if (!in_array($Url, $_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
?>
					              				<input type="button" class="botao" value="Incluir Grupos" onclick="javascript:AbreJanela('<?=$Url;?>',750,370);">
																<input type="button" class="botao" value="Retirar Grupos Marcados" onclick="javascript:enviar('RetirarGrupos');">
					              			</td>
					              		</tr>
					              	</table>
					              </td>
					            </tr>
										</table>
									</td>
								</tr>
		          	<tr>
									<td align="right">
										<input type="hidden" name="EmailPopup" value="<?php echo $_SESSION['EmailPopup'];?>">

										<input type="button" value="Próxima Aba" class="botao" onclick="javascript:enviar('D');">
										<input type="button" value="Limpar Tela" class="botao" onclick="javascript:enviar('Limpar');">
			            				<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
										<input type="hidden" name="Botao" value="">
									</td>
		            </tr>
	     				</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<!-- Fim do Corpo -->
	</table>
	</form>
	<script language="javascript" type="">
	<!--
	<?php

	if ($_SESSION['TipoHabilitacao'] == "L") {
?>
		document.CadGestaoFornecedor.NomeEntidade.focus();
	<?php

	}
	if ($_SESSION['Email'] == "" and $_SESSION['Botao'] == "Alterar") {
		$Url = "RotVerificaEmail.php?ProgramaOrigem=CadGestaoFornecedor";
		if (!in_array($Url, $_SESSION['GetUrl'])) {
			$_SESSION['GetUrl'][] = $Url;
		}
?>
		window.open('<?=$Url;?>','pagina','status=no,scrollbars=no,left=200,top=150,width=400,height=225');
	<?php

	}
?>
	-->
	</script>
	</body>
	</html>
	<?php

	exit;
}


function CriticaAbaDocumentos() { // Formulário E - Documentos

	$_SESSION['Mens']      = 0;
	$_SESSION['Tipo']      = 0;
	$_SESSION['Mensagem'] .= "";

	$Origem = $_POST['Origem'];
	$Destino = $_POST['Destino'];		
	//die($Origem.' - '.$Destino.' - '.$_SESSION['Botao']);

	$DDocumento = $_SESSION['DDocumento'];


	if ($_SESSION['Botao'] == "Limpar") {

		$_SESSION['Botao'] = "";
		$_SESSION['tipoDoc'] = 0;
		$_SESSION['obsDocumento'] = "";


		ExibeAbaDocumentos();
		

	} elseif  ($_SESSION['Botao'] == 'IncluirDocumento') {

		$_SESSION['Mens']     = "";
		$_SESSION['Mensagem'] = "Informe: ";


					
		if ($_POST['tipoDoc'] == '0' ) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens']      = 1;
			$_SESSION['Tipo']      = 2;
			$_SESSION['Mensagem'] .= "Tipo do documento deve ser preenchido";
		}else{

			$db = Conexao();
			$parametrosGerais = dadosParametrosGerais($db);
		
			$tamanhoArquivo = $parametrosGerais[4];
			$tamanhoNomeArquivo = $parametrosGerais[5];
			$extensoesArquivo = $parametrosGerais[6];
			

			//$Critica = 0;

			if ($_FILES['Documentacao']['tmp_name']) {
				$_FILES['Documentacao']['name'] = tratarNomeArquivo($_FILES['Documentacao']['name']);
		
				$extensoesArquivo .= ', .zip, .xlsm, .xls, .ods, .pdf';
		
		
				$extensoes = split(',', strtolower2($extensoesArquivo));
				array_push($extensoes, '.zip', '.xlsm');
		
				$noExtensoes = count($extensoes);
				$isExtensaoValida = false;
				for ($itr = 0; $itr < $noExtensoes; ++ $itr) {
					if (preg_match('/\\' . trim($extensoes[$itr]) . '$/', strtolower2($_FILES['Documentacao']['name']))) {
						$isExtensaoValida = true;
					}
				}
				if (! $isExtensaoValida) {
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= 'Selecione somente documento com a(s) extensão(ões) ' . $extensoesArquivo;
				}
				if (strlen($_FILES['Documentacao']['name']) > $tamanhoNomeArquivo) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'].= ', ';
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= 'Nome do Arquivo com até ' . $tamanhoNomeArquivo . ' Caracateres ( atualmente com ' . strlen($_FILES['Documentacao']['name']) . ' )';
				}
				$Tamanho = 5120*1000;
				if (($_FILES['Documentacao']['size'] > $Tamanho) || ($_FILES['Documentacao']['size'] == 0)) {
					if ($_SESSION['Mens']  == 1) {
						$_SESSION['Mensagem'] .= ', ';
					}
					$Kbytes = $Tamanho;
					$Kbytes = (int) $Kbytes;
					$_SESSION['Mens']= 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: 5 MB";
				}
				if ($_SESSION['Mens'] == '') {
					if (! ($_SESSION['Arquivos_Upload']['conteudo'][] = file_get_contents($_FILES['Documentacao']['tmp_name']))) {
						$_SESSION['Mens']= 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] = 'Caminho da Documentação Inválido';
					} else {
						$_SESSION['Arquivos_Upload']['nome'][] = $_FILES['Documentacao']['name'];
						$_SESSION['Arquivos_Upload']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
						$_SESSION['Arquivos_Upload']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
						$_SESSION['Arquivos_Upload']['tipoCod'][] = $_POST['tipoDoc']; 
						$_SESSION['Arquivos_Upload']['anoAnex'][] = $_POST['anoDoc'];
						$_SESSION['Arquivos_Upload']['tipoDocumentoDesc'][] = $_POST['tipoDocDesc']; 
						$_SESSION['Arquivos_Upload']['observacao'][] = strtoupper2($_POST['obsDocumento']); 
						$_SESSION['Arquivos_Upload']['dataHora'][] = date('d/m/Y H:i'); 
						$_SESSION['Arquivos_Upload']['codUsuarioUltAlt'][] = $_SESSION['_cusupocodi_'];
						$_SESSION['Arquivos_Upload']['usuarioUltAlt'][] = '';
						$_SESSION['Arquivos_Upload']['dataHoraUltAlt'][] = date('d/m/Y H:i');
						$_SESSION['Arquivos_Upload']['situacaoHist'][] = 1; 

						$_SESSION['tipoDoc'] = 0;
						$_SESSION['obsDocumento'] = "";
					}
				}

			} else {
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] = 'Falta anexar o documento';
			}
		}

		if ($_SESSION['Mens'] != 0 ) {
			ExibeAbaDocumentos();
		}

	} elseif ($_SESSION['Botao'] == 'RetirarDocumento') {
		//$Critica = 0;

		if ($DDocumento){
			foreach ($DDocumento as $valor) {
	
				if ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'novo') {
					$_SESSION['Arquivos_Upload']['situacao'][$valor] = 'cancelado'; // cancelado- quando o usuário incluiu um arquivo novo mas desistiu
				} elseif ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'existente') {
					$_SESSION['Arquivos_Upload']['situacao'][$valor] = 'excluido'; // excluído- quando o arquivo já existe e deve ser excluido no sistema
				}
	
			}

		}else{
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] = 'Selecione um anexo para ser retirado';
		}

		if ($_SESSION['Mens'] != 0 ) {
			ExibeAbaDocumentos();
		}

	} elseif ($_SESSION['Botao'] == 'PesquisaAnoDoc'){
		
		//Espaço para futuras críticas, caso existam.
		ExibeAbaDocumentos();

	}elseif ($_SESSION['Botao'] == 'Download'){
    
			//$docDown = getTramitacaoLicitacaoAnexos($licDown, $protDown, $seqDown);
			$docDown = $_SESSION['Arquivos_Upload'];

			$qtdup = count($docDown['conteudo']);
			for ($arqC = 0; $arqC < $qtdup; ++ $arqC) {

				if($_SESSION['CodDownload'] == $arqC){

					$arrNome = explode('.',$docDown['nome'][$arqC]);
					$extensao = $arrNome[1];
				
					$mimetype = 'application/octet-stream';
					
					header( 'Content-type: '.$mimetype ); 
					header( 'Content-Disposition: attachment; filename='.$docDown['nome'][$arqC] );   
					header( 'Content-Transfer-Encoding: binary' );
					header( 'Pragma: no-cache');
					
					echo pg_unescape_bytea($docDown['conteudo'][$arqC]);
				
					die();

				}

			}

			if ($_SESSION['Mens'] != 0 ) {
				ExibeAbaDocumentos();
			}
		
		
	}


		# Caso resolva alterar o fornoecedor #
		//if ($Mens == 0 and $_SESSION['Botao'] == "Alterar") {

			# Função que critica a parte da Gestão #
			//CriticaGestao();

		//}		

}

function ExibeAbaDocumentos() { // Formulário E - Documentos
	?>
	<html>
		<?php	# Carrega o layout padrão #
				layout();
		?>
		<script language="JavaScript" src="../janela.js" type="text/javascript"></script>
		<script language="JavaScript" type="">
			<!--
			function Submete(Destino){
				 document.CadGestaoFornecedor.Destino.value = Destino;
				 document.CadGestaoFornecedor.Botao.value = "";
				 document.CadGestaoFornecedor.submit();
			}
			function enviar(valor){
				if(valor == 'AnoDoc'){
					$('#pesqAnoDoc').val($('#anoDoc').val());
				}
				document.CadGestaoFornecedor.Botao.value = valor;
				document.CadGestaoFornecedor.submit();
			}

			function baixarArquivo(cod){
				document.CadGestaoFornecedor.CodDownload.value = cod;
				enviar('Download');
			}

			function remeter(valor){
				document.CadGestaoFornecedor.Destino.value = 'E';
				document.CadGestaoFornecedor.Botao.value = valor;
				document.CadGestaoFornecedor.submit();
			}
			function preencheTipoDocDesc(){
				$('#tipoDocDesc').val($('#tipoDoc option:selected').text());
			}


			<?php MenuAcesso(); ?>
			//-->
		</script>
		<link rel="stylesheet" type="text/css" href="../estilo.css">
		<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
			<script language="JavaScript" src="../menu.js"></script>
			<script language="JavaScript">Init();</script>
			<form action="CadGestaoFornecedor.php" method="post" name="CadGestaoFornecedor" enctype="multipart/form-data">
				<br><br><br><br><br>
				<table cellpadding="3" border="0" summary="">
					<!-- Caminho -->
					<tr>
						<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
						<td align="left" class="textonormal" colspan="2">
							  <font class="titulo2">|</font>
							  <a href="../index.php">
								<font color="#000000">Página Principal</font>
							</a> > Fornecedores > Inscrição > Cadastro
						</td>
					</tr>
					<!-- Fim do Caminho-->
					<!-- Erro -->
					<tr>
						  <td width="100"></td>
						  <td align="left" colspan="2">
							<?php	if ($_SESSION['Mens'] != 0) {
										ExibeMens($_SESSION['Mensagem'], $_SESSION['Tipo'], $_SESSION['Virgula']);
									} 
							?>
						 </td>
					</tr>
					<!-- Fim do Erro -->
					<!-- Corpo -->
					<tr>
						<td width="100"></td>
						<td class="textonormal">
							<table border="0" cellspacing="0" cellpadding="3" summary="">
								<tr>
									 <td class="textonormal">
									 <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
										<tr>
											<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
														ALTERAR - CADASTRO E GESTÃO DE FORNECEDOR
											</td>
											</tr>
										<tr>
											<td class="textonormal">
												<p align="justify">
													Informe os dados abaixo e clique no botão "Alterar".<br>
													Para informar as grupos de fornecimento clique no botão "Incluir Grupos". Se desejar eliminar um grupo de fornecimento já informada, marque o(s) grupo(s) desejado(s) e clique no botão "Retirar Grupos".<br><br>
													Para efetivar a alteração, clique no botão "Alterar" da aba Qualificação Técnica.<br><br>
													Para excluir o fornecedor do cadastro clique no botão "Excluir" da aba Habilitação Jurídica.
													<br><br>Tamanho máximo para upload de arquivo: 5 MB.
											</p>
											</td>
											</tr>
											<?php ExibeGestao(); ?>
											<tr>
												<td align="left">
													<?php echo NavegacaoAbas(off,off,off,off,on); ?>
													<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
														<tr bgcolor="#bfdaf2">
															<td colspan="4">
																  <table class="textonormal" border="0" align="left" summary="">
																	<tr>
																		<td class="textonormal" height="20" width="40%">
																			<?php if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { echo "CNPJ"; } else { echo "CPF"; } ?>
																		</td>
																		<td class="textonormal">
																			<?php	if ($_SESSION['CPF_CNPJ'] != "") {
																						if (strlen($_SESSION['CPF_CNPJ']) == 14) {
																							echo substr($_SESSION['CPF_CNPJ'],0,2).".".substr($_SESSION['CPF_CNPJ'],2,3).".".substr($_SESSION['CPF_CNPJ'],5,3)."/".substr($_SESSION['CPF_CNPJ'],8,4)."-".substr($_SESSION['CPF_CNPJ'],12,2);
																						} else {
																							echo substr($_SESSION['CPF_CNPJ'],0,3).".".substr($_SESSION['CPF_CNPJ'],3,3).".".substr($_SESSION['CPF_CNPJ'],6,3)."-".substr($_SESSION['CPF_CNPJ'],9,2);
																						}
																					}
																			?>
																		</td>
																	</tr>

																	<tr>
																		<td class="textonormal" height="20">
																			  <?php if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { echo "Razão Social*\n"; } else { echo "Nome*\n"; } ?>
																		</td>
																		<td class="textonormal">
																		  	<?php echo $_SESSION['RazaoSocial'] ?>
																		</td>
																	</tr>
																	<tr>
																		  <td class="textonormal">Nome Fantasia </td>
																		  <td class="textonormal">
																		  	<?php echo $_SESSION['NomeFantasia'] ?>
																		  </td>
																	</tr>
																	<tr>
																		<td class="textonormal">Ano de anexação*</td>
																		<td class="textonormal">
																		<select name="anoDoc" id="anoDoc" class="tamanho_campo textonormal" >
																			<?php //onchange="enviar('AnoDoc');"
																			$arr = array();

																	
																			for($j = date(Y); $j > 2015; $j--){
																				$arr[] = $j;
																			}
																			
																			foreach ($arr as $value) {
																				if( $value == $_SESSION['pesqAnoDoc']){
																					echo '<option value="'.$value.'" selected>'.$value.'</option>';
																				}else{
																					echo '<option value="'.$value.'">'.$value.'</option>';
																				}
																			}
																			?>
																		</select>
																		</td>
																	</tr>
																	<tr>
																		  <td class="textonormal">Documento*</td>
																		<td class="textonormal">
																			<input type="file" name="Documentacao" class="textonormal" />
																		</td>
																	</tr>
																	<tr>
																		<td class="textonormal">Tipo de Documento*</td>
																		<td class="textonormal">
																		<select name="tipoDoc" id="tipoDoc" class="tamanho_campo textonormal" onchange="preencheTipoDocDesc()">
																			<option value="0">Selecione um tipo de documento...</option>
																			<?php 
																				$db = Conexao();
																																								
																				$sql = "SELECT CFDOCTCODI, EFDOCTDESC, ffdoctobri FROM 
																						SFPC.TBFORNECEDORDOCUMENTOTIPO
																						WHERE FFDOCTSITU = 'A' ORDER BY EFDOCTDESC";

																				$res = $db->query($sql);

																				if (db::isError($res)) {
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				} else {
																					
																					while ($tipoDoc = $res->fetchRow()) {
																						
																						$docObrigatorio = '';
																						if($tipoDoc[2] == 'S'){
																							$docObrigatorio = ' (Obrigatório)';
																						}

																						if($tipoDoc[0] == $_SESSION['tipoDoc'] ){
																							?>
																							<option value="<?php echo $tipoDoc[0]; ?>" selected><?php echo $tipoDoc[1].$docObrigatorio; ?></option>
																							<?php
																						}else{
																							?>
																							<option value="<?php echo $tipoDoc[0]; ?>"><?php echo $tipoDoc[1].$docObrigatorio; ?></option>
																							<?php
																						}	


																					}
																					
																				}
																			?>
																		</select>
																		<input type="hidden" id="tipoDocDesc" name="tipoDocDesc" value="<?php echo $_SESSION['tipoDocDesc'] ?>">
																		</td>
																	</tr>
																	<tr>
																		  <td class="textonormal">Observação</td>
																		<td class="textonormal">
																			<font class="textonormal">máximo de 500 caracteres</font>
																			<input type="text" name="NCaracteres" disabled="" readonly="" size="3" value="0" class="textonormal"><br>
																			<textarea id="obsDocumento" name="obsDocumento" maxlength="500" cols="50" rows="4" onkeyup="javascript:CaracteresObservacao(1)" onblur="javascript:CaracteresObservacao(0)" onselect="javascript:CaracteresObservacao(1)" class="textonormal">
																			<?php echo $_SESSION['obsDocumento'] ?></textarea>
																			<script language="javascript" type="">
																			function CaracteresObservacao(valor){
																				CadGestaoFornecedor.NCaracteres.value = '' +  CadGestaoFornecedor.obsDocumento.value.length;
																			}
																			</script>

																		</td>
																		<td valign="bottom">
																		<input type="button" value="Incluir Documento" class="botao" onclick="javascript:enviar('IncluirDocumento');">		
																		</td>
																	</tr>
																  </table>
															</td>
														</tr>
														<?php 	if (count($_SESSION['Arquivos_Upload']) > 0) {?>
														<tr>
					              						<td class="textonormal" colspan="4">
														 
															<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
																<tr>
					              									<td bgcolor="#75ADE6" class="textoabasoff" colspan="9" align="center">DOCUMENTOS ANEXADOS</td>
					              								</tr>
																<tr>															<tr>
																	<td class="textonormal" colspan="9">
																	Ano da anexação: <select name="pesqAnoDoc" id="pesqAnoDoc" class="tamanho_campo textonormal" onChange="javascript:enviar('PesquisaAnoDoc');">
																		<?php 
																			$arr = array();
																			$anos = array();

																			$total_doc = count($_SESSION['Arquivos_Upload']['conteudo']);
																			for ($c = 0; $c < $total_doc; ++ $c) {
																			
																				$anoDoDocumento = $_SESSION['Arquivos_Upload']['anoAnex'][$c];
																				$anos[] = $anoDoDocumento;
																			
																			}

																			for($j = date(Y); $j > 2000; $j--){
																				if(in_array($j, $anos)){
																					$arr[] = $j;
																				}
																			}
																			
																			foreach ($arr as $value) {
																				if( $value == $_SESSION['pesqAnoDoc']){
																					echo '<option value="'.$value.'" selected>'.$value.'</option>';
																				}else{
																					echo '<option value="'.$value.'">'.$value.'</option>';
																				}
																			}
																		?>
																	</select>

																	</td>
																</tr>  
        			          									<tr>
					              									<td bgcolor="#bfdaf2" align="center"><b>  </b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Tipo do documento</b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Nome</b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Responsável anexação</b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Data/Hora Anexação</b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Situação</b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Observação</b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Responsável última alteração</b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Data/Hora última alteração</b></td>
					              								</tr> 
																<?php		/*  kim #222833
																				$sql = "SELECT EUSUPORESP FROM 
																				SFPC.TBUSUARIOPORTAL
																				WHERE CUSUPOCODI = ".$_SESSION['_cusupocodi_']." limit 1";

																				$nome_usuario = resultValorUnico(executarTransacao($db, $sql)); */
																		
																	$DTotal = count($_SESSION['Arquivos_Upload']['conteudo']);
																	
																	for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {

																		if($_SESSION['pesqAnoDoc']){
																			$pesqAnoDoc = $_SESSION['pesqAnoDoc'];
																		}else{
																			if($arr[0]){
																				$pesqAnoDoc = $arr[0];
																			}else{
																				$pesqAnoDoc = date(Y);
																			}
																		}

																

																		if (($_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'existente') && ($_SESSION['Arquivos_Upload']['anoAnex'][$Dcont] == $pesqAnoDoc)) {
																		?>	<tr>
																				<td align='center' width='5%' bgcolor="#ffffff"><input type='checkbox' name='DDocumento[<?php echo $Dcont?>]' value='<?php echo $Dcont?>' ></td>
																			
																				<td class='textonormal' bgcolor='#ffffff'>
																					<?php echo $_SESSION['Arquivos_Upload']['tipoDocumentoDesc'][$Dcont] ?>
																				</td>
																				<!--nome-->
																				<td class='textonormal' bgcolor="#ffffff">
																					<a href='javascript: baixarArquivo(<?php echo $Dcont?>);'><?php echo $_SESSION['Arquivos_Upload']['nome'][$Dcont] ?></a>
																					
																				</td>
																				<td class='textonormal' bgcolor="#ffffff" hint="<?php echo $_SESSION['Arquivos_Upload']['codigo'][$Dcont] ?>">
																					<?php 
																					
																					if($_SESSION['Arquivos_Upload']['situacao'][$Dcont]=='existente'){
																					
																						if($_SESSION['Arquivos_Upload']['externo'][$Dcont]=='S'){
																							// Kim CR#222833
																							 echo $_SESSION['Arquivos_Upload']['usuarioAnex'][$Dcont];
																						 
																						 
																						}else{
																							// echo 'PCR - '.$nome_usuario;
																							// kim CR#222833
																							// echo 'PCR - '.$nomeUsuAnex;
																							echo $_SESSION['Arquivos_Upload']['usuarioAnex'][$Dcont];
																						}

																					}else{
																						if($_SESSION['Arquivos_Upload']['usuarioAnex'][$Dcont]){
																							// echo 'PCR - '.$nomeUsuAnex;
																							echo $_SESSION['Arquivos_Upload']['usuarioAnex'][$Dcont];
																						}else{
																							echo '-';
																						}
																					}

																					

																					?>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<?php echo $_SESSION['Arquivos_Upload']['dataHora'][$Dcont] ?>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<select name="situacaoDoc<?php echo $Dcont?>" id="situacaoDoc<?php echo $Dcont?>" class="tamanho_campo textonormal" >
																						<?php 
																							$db = Conexao();
																																											
																							$sql = "SELECT CFDOCSCODI, EFDOCSDESC FROM 
																								SFPC.TBFORNECEDORDOCUMENTOSITUACAO
																								WHERE FFDOCSSITU = 'A' ORDER BY CFDOCSCODI";

																							$res = $db->query($sql);

																							if (db::isError($res)) {
																								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																							} else {
																								
																								while ($arrsitu = $res->fetchRow()) {
																									
																									if($arrsitu[0] == $_SESSION['Arquivos_Upload']['situacaoHist'][$Dcont] ){
																										?>
																										<option value="<?php echo $arrsitu[0]; ?>" selected><?php echo $arrsitu[1]; ?></option>
																										<?php
																									}else{
																										?>
																										<option value="<?php echo $arrsitu[0]; ?>"><?php echo $arrsitu[1]; ?></option>
																										<?php
																									}	


																								}
																								
																							}
																						?>
																					</select>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<input type='text' name='obsDocumento<?php echo $Dcont?>' id='obsDocumento<?php echo $Dcont?>' value='<?php echo $_SESSION['Arquivos_Upload']['observacao'][$Dcont] ?>'>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<?php 
																						if($_SESSION['Arquivos_Upload']['situacao'][$Dcont]=='existente'){
																							echo $_SESSION['Arquivos_Upload']['usuarioUltAlt'][$Dcont]; 
																						}else{
																							echo 'PCR - '.$nomeUsuUltAlt;
																						}
																					
																					?>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<?php echo $_SESSION['Arquivos_Upload']['dataHoraUltAlt'][$Dcont] ?>
																				</td>
																			</tr>
																		<?php 

																			//$arquivo =  $_SESSION['Arquivos_Upload']['nome'][$Dcont];
																			//addArquivoAcesso($arquivo);
																		}
																	}

					              								?>
					              								<tr>
					              									<td class="textonormal" colspan="9" align="right">
																		<input class="botao" type="button" value="Retirar Documento" onclick="javascript:enviar('RetirarDocumento');">
					              									</td>
					              								</tr>
					              							</table>
					              						</td>
					            						</tr>
													<?php } ?>
													</table>
												</td>
											</tr>
											
								  <tr>
								  	<td colspan="4" align="right">
									  	<input type="hidden" name="Critica" size="1" value="2">
										<input type="hidden" name="Origem" value="E">
										<input type="hidden" name="CodDownload" id="CodDownload" >
										<input type="hidden" name="Destino" value="E">

										<input type="hidden" name="EmailPopup" value="<?php echo $_SESSION['EmailPopup'];?>">
		            					<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
										<input type="button" value="Limpar Tela" class="botao" onclick="javascript:enviar('Limpar');">
			            				<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
										<input type="hidden" name="Botao" value="">
									</td>

								</tr>
							</table>
						</td>
					</tr>
					<!-- Fim do Corpo -->
				</table>
			</form>
			<script language="JavaScript" type="">
				<!--
					document.CadGestaoFornecedor.CPF_CNPJ.focus()
				//-->
			</script>
		</body>
	</html>
	<?php	exit;
	}


# Função Critica Gestão #
function CriticaGestao() {
	if ($_SESSION['Botao'] == "TipoSituacao") {
		$_SESSION['DataSituacao'] = "";
		$_SESSION['Motivo'] = "";
		$_SESSION['DataExpiracao'] = "";
	} else {
		if ($_SESSION['Cumprimento'] == "" and $_SESSION['TipoHabilitacao'] == "L") {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "Cumprimento";
		}
		if ($_SESSION['Situacao'] != 1) {
			if ($_SESSION['DataSituacao'] == "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataSituacao.focus();\" class=\"titulo2\">Data da Situação</a>";
			} else {
				$MensErro = ValidaData($_SESSION['DataSituacao']);
				if ($MensErro != "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataSituacao.focus();\" class=\"titulo2\">Data Válida</a>";
				} else {
					$DataSituacaoInv = substr($_SESSION['DataSituacao'], 6, 4) . "-" . substr($_SESSION['DataSituacao'], 3, 2) . "-" . substr($_SESSION['DataSituacao'], 0, 2);
					$DataInscricaoInv = substr($_SESSION['DataInscricao'], 6, 4) . "-" . substr($_SESSION['DataInscricao'], 3, 2) . "-" . substr($_SESSION['DataInscricao'], 0, 2);
					if ($DataSituacaoInv <= $DataInscricaoInv) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataSituacao.focus();\" class=\"titulo2\">Data da Situação maior que a data do Cadastro</a>";
					}
				}
			}
			if ($_SESSION['DataExpiracao'] == "" and $_SESSION['Situacao'] == 3) {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataExpiracao.focus();\" class=\"titulo2\">Data de Expiração da Situação</a>";
			}
			elseif ($_SESSION['DataExpiracao'] != "") {
				$MensErro = ValidaData($_SESSION['DataExpiracao']);
				if ($MensErro != "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataExpiracao.focus();\" class=\"titulo2\">Data de Expiração da Situação Válida</a>";
				} else {
					if (DataInvertida($_SESSION['DataExpiracao']) <= DataInvertida($_SESSION['DataSituacao'])) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataExpiracao.focus();\" class=\"titulo2\">Data de Expiração da Situação maior que a Data da Situação</a>";
					}
				}
			}
			if ($_SESSION['Motivo'] == "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.Motivo.focus();\" class=\"titulo2\">Motivo</a>";
			}
		}
		if ($_SESSION['DataGeracaoCHF'] == "") {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataGeracaoCHF.focus();\" class=\"titulo2\">Data de Geração do CHF</a>";
		}
		elseif ($_SESSION['DataGeracaoCHF'] != "") {
			$MensErro = ValidaData($_SESSION['DataGeracaoCHF']);
			if ($MensErro != "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataGeracaoCHF.focus();\" class=\"titulo2\">Data de Geração do CHF Válida</a>";
			} else {
				if (DataInvertida($_SESSION['DataGeracaoCHF']) > date("Y-m-d")) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataGeracaoCHF.focus();\" class=\"titulo2\">Data de Geração do CHF menor ou igual que a data atual</a>";
				}
			}
		}
		if (($_SESSION['ComissaoResp'] == "") and ($_SESSION['DataAnaliseDoc'] <> "")) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.ComissaoResp.focus();\" class=\"titulo2\">Comissão Responsável </a>";
		}
		elseif (($_SESSION['ComissaoResp'] <> "") and ($_SESSION['DataAnaliseDoc'] == "")) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedor.DataAnaliseDoc.focus();\" class=\"titulo2\">Data da Análise da Documentação </a>";
		}
	}
}

# Função que mostar a parte html da Gestão #
function ExibeGestao() {
	echo "<tr>\n";
	echo "	<td>\n";
	echo "		<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bordercolor=\"#75ADE6\" width=\"100%\" summary=\"\">\n";
	echo "			<tr>\n";
	echo "				<td>\n";
	echo "					<table class=\"textonormal\" border=\"0\" cellpadding=\"0\" cellspacing=\"2\" width=\"100%\" summary=\"\">\n";
	if ($_SESSION['TipoHabilitacao'] == "L") {
		echo "						<tr>\n";
		echo "							<td class=\"textonormal\" bgcolor=\"#DCEDF7\" width=\"45%\">Cumprimento Inc. XXXIII Art. 7º Cons. Fed.*</td>\n";
		echo "							<td class=\"textonormal\" colspan=\"2\">\n";
		echo "        				<input type=\"radio\" name=\"Cumprimento\" value=\"S\"\n";
		if ($_SESSION['Cumprimento'] == "S") {
			echo "checked";
		}
		echo "        				>Sim\n";
		echo "        				<input type=\"radio\" name=\"Cumprimento\" value=\"N\"\n";
		if ($_SESSION['Cumprimento'] == "N") {
			echo "checked";
		}
		echo "        				>Não\n";
		echo "        				<input type=\"hidden\" name=\"CarregaPagina\" value=\"1\">\n";
		echo "							</td>\n";
		echo "		  			</tr>\n";
	}
	echo "						<tr>\n";
	echo "							<td class=\"textonormal\" bgcolor=\"#DCEDF7\">Situação*</td>\n";
	echo "							<td class=\"textonormal\" colspan=\"2\"	>\n";
	echo "								<select name=\"Situacao\" class=\"textonormal\" onChange=\"javascript:remeter('TipoSituacao');\">\n";
	# Mostra Tabela de Situação #
	$db = Conexao();
	$sql = "SELECT CFORTSCODI, EFORTSDESC FROM SFPC.TBFORNECEDORTIPOSITUACAO WHERE CFORTSCODI <> 5 ORDER BY EFORTSDESC";
	$result = $db->query($sql);
	if (db :: isError($result)) {
		ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
	} else {
		$Rows = $result->numRows();
		for ($i = 0; $i < $Rows; $i++) {
			$Linha = $result->fetchRow();
			if ($Linha[0] == 1) {
				if ($_SESSION['TipoHabilitacao'] == "L") {
					$Linha[1] = $Linha[1] . " " . $_SESSION['Cadastrado'];
				}
			}
			if ($Linha[0] == $_SESSION['Situacao']) {
				echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
			} else {
				echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
			}
		}
	}
	$db->disconnect();
	echo "					  		</select>\n";
	echo "					  		<input type=\"hidden\" name=\"SituacaoAntes\" value=\"" . $_SESSION['SituacaoAntes'] . "\" class=\"textonormal\">\n";
	echo "							</td>\n";
	echo "		  			</tr>\n";
?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">Habilitação de fornecedor*</td>
											<td class="textonormal" colspan="2"	>
												<select name="TipoHabilitacao" class="textonormal" onchange="document.CadGestaoFornecedor.submit()">
													<?php

	if ($_SESSION['TipoHabilitacao'] == "E") {
?>
														<option value="E" selected >ESTOQUES</option>
													<?php

	}
?>
													<option value="D" <?php if( $_SESSION['TipoHabilitacao'] == "D" ){ echo "selected"; }?>>COMPRA DIRETA</option>
													<option value="L" <?php if( $_SESSION['TipoHabilitacao'] == "L" ){ echo "selected"; }?>>LICITAÇÃO</option>
												</select>

											</td>
										</tr>
	<?php

	$Elementos = 5;
	if ($_SESSION['Situacao'] != 1) {
		echo "						<tr>\n";
		echo "							<td class=\"textonormal\" bgcolor=\"#DCEDF7\">Data da Situação*</td>\n";
		echo "							<td class=\"textonormal\" colspan=\"2\">\n";
		$URL = "../calendario.php?Formulario=CadGestaoFornecedor&Campo=DataSituacao";
		echo "								<input type=\"text\" name=\"DataSituacao\" size=\"10\" maxlength=\"10\" value=\"" . $_SESSION['DataSituacao'] . "\" class=\"textonormal\">\n";
		echo "								<a href=\"javascript:janela('$URL','Calendario',220,170,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>\n";
		echo "							</td>\n";
		echo "		  			</tr>\n";
		echo "						<tr>\n";
		echo "							<td class=\"textonormal\" bgcolor=\"#DCEDF7\">Motivo*</td>\n";
		echo "							<td class=\"textonormal\" colspan=\"2\">\n";
		echo "        				<input type=\"text\" name=\"Motivo\" value=\"" . $_SESSION['Motivo'] . "\" class=\"textonormal\" size=\"50\" maxlength=\"200\">\n";
		echo "        			</td>\n";
		echo "		  			</tr>\n";
		$Elementos = $Elementos +2;
	}
	if ($_SESSION['Situacao'] == 3 or $_SESSION['Situacao'] == 6) {
		echo "		  			<tr>\n";
		echo "							<td class=\"textonormal\" bgcolor=\"#DCEDF7\">Data de Expiração da Situação</td>\n";
		echo "							<td class=\"textonormal\" height=\"20\" colspan=\"2\">\n";
		$URL = "../calendario.php?Formulario=CadGestaoFornecedor&Campo=DataExpiracao";
		echo "								<input type=\"text\" name=\"DataExpiracao\" size=\"10\" maxlength=\"10\" value=\"" . $_SESSION['DataExpiracao'] . "\" class=\"textonormal\">\n";
		echo "								<a href=\"javascript:janela('$URL','Calendario',220,170,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>\n";
		echo "							</td>\n";
		echo "		  			</tr>\n";
		$Elementos = $Elementos +1;
	}
	echo "		  			<tr>\n";
	echo "							<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Data de Geração do CHF*</td>\n";
	echo "							<td class=\"textonormal\" colspan=\"2\">\n";
	if ($_SESSION['DataGeracaoCHFAntes'] != "") {
		echo $_SESSION['DataGeracaoCHFAntes'];
		echo "						<input type=\"hidden\" name=\"DataGeracaoCHF\" size=\"10\" maxlength=\"10\" value=\"" . $_SESSION['DataGeracaoCHFAntes'] . "\" class=\"textonormal\">\n";
	} else {
		$URL = "../calendario.php?Formulario=CadGestaoFornecedor&Campo=DataGeracaoCHF";
		echo "								<input type=\"text\" name=\"DataGeracaoCHF\" size=\"10\" maxlength=\"10\" value=\"" . $_SESSION['DataGeracaoCHF'] . "\" class=\"textonormal\">\n";
		echo "								<a href=\"javascript:janela('$URL','Calendario',220,170,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>\n";
	}
	echo "		  				</td>\n";
	echo "		  			</tr>\n";
	echo "		  			<tr>\n";
	echo "							<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Data de Validade do CHF</td>\n";
	echo "							<td class=\"textonormal\" colspan=\"2\">\n";
	if ($_SESSION['DataValidadeCHF'] == "") {
		echo "-";
	} else {
		echo $_SESSION['DataValidadeCHF'];
		echo "								<input type=\"hidden\" name=\"DataValidadeCHF\" size=\"10\" maxlength=\"10\" value=\"" . $_SESSION['DataValidadeCHF'] . "\" class=\"textonormal\">\n";
		$Elementos = $Elementos +1;
	}
	echo "		  				</td>\n";
	echo "		  			</tr>\n";
	echo "		  			<tr>\n";
	echo "							<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Data de Cadastramento</td>\n";
	echo "							<td class=\"textonormal\">\n";
	echo $_SESSION['DataInscricao'];
	echo "		  				</td>\n";
	echo "		  			</tr>\n";
	echo "		  			<tr>\n";
	echo "							<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Data da Última Alteração</td>\n";
	echo "							<td class=\"textonormal\">\n";
	echo $_SESSION['DataAlteracao'];
	echo "		  				</td>\n";
	echo "		  			</tr>\n";
	echo "		  			<tr>\n";
	echo "		  				<td class=\"textonormal\" bgcolor=\"#DCEDF7\">Comissão de Licitação Responsável pela Análise da Documentação</td>\n";
	echo "		  			  <td class=\"textonormal\">\n";
	echo "		  			 		<select name=\"ComissaoResp\" class=\"textonormal\">\n";
	echo "		  				  <option value=\"\">Selecione a Comissão...</option>\n";
	$db = Conexao();
	$Grupo = $_SESSION['_cgrempcodi_'];
	$sql = "SELECT CCOMLICODI, ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO ";
	if ($Grupo <> 0) {
		$sql .= "WHERE  CGREMPCODI = $Grupo AND   FCOMLISTAT = 'A' ORDER BY ECOMLIDESC";
	} else {
		$sql .= " WHERE FCOMLISTAT = 'A' ORDER BY ECOMLIDESC";
	}
	$result = $db->query($sql);
	if (db :: isError($result)) {
		ExibeErroBD($_SESSION['ErroPrograma'] . "\nLinha: " . __LINE__ . "\nSql: $sql");
	}
	while ($Linha = $result->fetchRow()) {
		if ($Linha[0] == $_SESSION['ComissaoResp']) {
			echo "		  				  					<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
		} else {
			echo "		  				  					<option value=\"$Linha[0]\">$Linha[1]</option>\n";
		}
	}
	$db->disconnect();
	echo "		  					</select>\n";
	echo "		  				</td>\n";
	echo "		  			</tr>\n";
	echo "		  			<tr>\n";
	echo "							<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Data da Análise Documentação</td>\n";
	echo "							<td class=\"textonormal\">\n";
	$URL = "../calendario.php?Formulario=CadGestaoFornecedor&Campo=DataAnaliseDoc";
	echo "							   	<input type=\"text\" class=\"textonormal\" name=\"DataAnaliseDoc\" size=\"10\" maxlength=\"10\" value=\"" . $_SESSION['DataAnaliseDoc'] . "\" >\n";
	echo "									<a href=\"javascript:janela('$URL','Calendario',220,170,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>\n";
	echo "		  				</td>\n";
	echo "							<td class=\"textonormal\" align=\"right\">\n";
	echo "								<input type=\"button\" name=\"Historico\" value=\"Histórico\" class=\"botao\" onClick=\"javascript:enviar('Historico')\">\n";
	echo "		  				</td>\n";
	echo "		  			</tr>\n";

	$Elementos = $Elementos +2;
	$_SESSION['Elementos'] = $Elementos;
	if ($_SESSION['Ocorrencias'] != 0) {
		echo "		  			<tr>\n";
		echo "							<td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Ocorrências</td>\n";
		echo "							<td class=\"textonormal\" colspan=\"2\">\n";
		$Url = "CadGestaoFornecedorOcorrencias.php?ProgramaOrigem=CadGestaoFornecedor&Sequencial=" . $_SESSION['Sequencial'] . "&Origem=A&Destino=A";
		if (!in_array($Url, $_SESSION['GetUrl'])) {
			$_SESSION['GetUrl'][] = $Url;
		}
		echo "								<a href=\"javascript:AbreJanela('$Url',750,210);\"> Clique AQUI para visualizar as Ocorrências.</a>\n";
		echo "							</td>\n";
		echo "		  			</tr>\n";
	}
	echo "					</table>\n";
	echo "				</td>\n";
	echo "			</tr>\n";
	echo "		</table>\n";
	echo "	</td>\n";
	echo "</tr>\n";
}

# Função de Navegação das Abas #
function NavegacaoAbas($Pri, $Seg, $Ter, $Qua, $Qui) {
	$htm = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bordercolor=\"#75ADE6\" summary=\"\">";
	$htm .= "	<tr>\n";
	$htm .= "		<td valign=\"bottom\">\n";
	$htm .= "			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
	$htm .= "				<tr>\n";
	$htm .= "					<td background=\"../midia/aba_" . $Pri . "_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
	$htm .= "					<td background=\"../midia/aba_" . $Pri . ".gif\" valign=\"center\" class=\"textoabason\"><a href=\"javascript:Submete('A');\" class=\"textoabas" . $Pri . "\">&nbsp;HABILITAÇÃO&nbsp;JURÍDICA&nbsp; </a></td>\n";
	$htm .= "					<td background=\"../midia/aba_" . $Pri . "_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
	$htm .= "				</tr>\n";
	$htm .= "			</table>\n";
	$htm .= "		</td>\n";
	$htm .= "		<td valign=\"bottom\">\n";
	$htm .= "			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
	$htm .= "		   	<tr>\n";
	$htm .= "					<td background=\"../midia/aba_" . $Seg . "_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
	$htm .= "			   	<td background=\"../midia/aba_" . $Seg . ".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('B');\" class=\"textoabas" . $Seg . "\">&nbsp;REGULARIDADE&nbsp;FISCAL&nbsp; </a></td>\n";
	$htm .= "			   	<td background=\"../midia/aba_" . $Seg . "_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
	$htm .= "		   	</tr>\n";
	$htm .= "			</table>\n";
	$htm .= "		</td>\n";
	$htm .= "		<td valign=\"bottom\">\n";
	$htm .= "			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
	$htm .= "				<tr>\n";
	$htm .= "					<td background=\"../midia/aba_" . $Ter . "_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
	$htm .= "					<td background=\"../midia/aba_" . $Ter . ".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('C');\" class=\"textoabas" . $Ter . "\">&nbsp;QUALIF.&nbsp;ECON.&nbsp;FINAN.&nbsp;</a></td>\n";
	$htm .= "					<td background=\"../midia/aba_" . $Ter . "_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
	$htm .= "				</tr>\n";
	$htm .= "			</table>\n";
	$htm .= "		</td>\n";
	$htm .= "		<td valign=\"bottom\">\n";
	$htm .= "			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
	$htm .= "				<tr>\n";
	$htm .= "					<td background=\"../midia/aba_" . $Qua . "_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
	$htm .= "					<td background=\"../midia/aba_" . $Qua . ".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('D');\" class=\"textoabas" . $Qua . "\">&nbsp;QUALIF.&nbsp;TÉCNICA&nbsp;</a></td>\n";
	$htm .= "					<td background=\"../midia/aba_" . $Qua . "_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
	$htm .= "				</tr>\n";
	$htm .= "			</table>\n";
	$htm .= "		</td>\n";
	$htm .=	"		<td valign=\"bottom\">\n";
	$htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
	$htm .=	"				<tr>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Qui."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Qui.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('E');\" class=\"textoabas".$Qui."\">&nbsp;DOCUMENTOS&nbsp;</a></td>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Qui."_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
	$htm .=	"				</tr>\n";
	$htm .=	"			</table>\n";
	$htm .=	"		</td>\n";
	$htm .= "	</tr>\n";
	$htm .= "</table>\n";
	return $htm;
}
?>
