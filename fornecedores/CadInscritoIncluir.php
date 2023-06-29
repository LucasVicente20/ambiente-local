<?php

# -----------------------------------------------------------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadInscritoIncluir.php
# Autor:    Roberta Costa / Luciano Mauro
# Data:     01/06/2004
# Objetivo: Programa de Inclusão de Inscritos
# Data:     01/06/2004
# OBS.:     Tabulação 2 espaços
# OBS.:     Alterações deste documento php podem precisar ser replicados em CadGestaoFornecedorIncluir.php,
#			CadGestaoFornecedor.php, CadInscritoAlterar.php, e CadInscritoIncluir.php
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data:     14/05/2007 - Mudança da mensagem de inscrição do fornecedor
#                      - Correção da chamada do recibo para evitar redirecinamento
#                      - Retirada de $_SESSION['GetUrl']=array(), pois já está em funcoes.php;
#           29/05/2007 - Receber novos campos (índice Endividamento e Microempresa ou EPP)
#           07/06/2007 - Complementação da mensagem de texto da tela e email
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     18/06/2007 - Receber novo campo (índice de solvência)
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     06/06/2008 	- novo campo: Email 2
# 											- Checagem de erros no preenchimento do CEP, DDD e Número
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     03/09/2008 	- Usuário agora tem 2 tipos: Licitação e Compra direta
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     09/09/2008 	- Novas alterações para compra direta se comportar mais como especificado pelo cliente (DLC)
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     21/10/2008 	- Correção para limpar CEP (variavel de sessão 'CEPInformado')
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     21/07/2010 - Alteração de Validar CEP (CEP VÁLIDO PARA MUDAR DE ABA)
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Ariston
# Data:     09/08/2010	- Adicionado opção para incluir sócios
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     16/08/2010
# Objetivo: Permitir exclusão de todas classes serviços na inclusão e alteração de fornecedor e pré-fornecedor
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     26/08/2010
# Objetivo: Data de última alteração de contrato ou estatuto
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     24/05/2011	- Tarefa Redmine: 2245 - Em Inscrição de Fornecedores obrigar a digitação do e-mail
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     01/06/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
#                      - Alteração do nome do arquivo de "CadIncluirClasses.php" para "CadIncluirGrupos.php"
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     02/06/2011 - Tarefa Redmine: 2727 - Incluir novos campos para preenchimento no SICREF - Dados do Representante Legal
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     16/09/2011 - Tarefa Redmine: 3718 - Remoção de campos de Representante Legal no módulo de fornecedores (SICREF)
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Luiz Alves
# Data: 	08/03/2012 - Tarefa Redmine: 4092 - Manutenção na caital social subscrito e patrimônio líquido.
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     25/07/2018
# Objetivo: Tarefa Redmine 80154
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		14/09/2018
# Objetivo: Tarefa Redmine 202539
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Ernesto Ferreira
# Data:		26/10/2018
# Objetivo: Tarefa Redmine 201223
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Lucas André e Daniel Augusto
# Data:		16/05/2023
# Objetivo: Tarefa Redmine 282899
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Daniel Augusto
# Data:		16/05/2023
# Objetivo: Tarefa Redmine 282903
# -----------------------------------------------------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "funcoesFornecedores.php";
require_once("funcoesDocumento.php");

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/fornecedores/CadIncluirCertidaoComplementar.php');
AddMenuAcesso ('/fornecedores/CadIncluirGrupos.php');
AddMenuAcesso ('/fornecedores/CadIncluirAutorizacao.php');
AddMenuAcesso ('/fornecedores/RotVerificaEmail.php');
AddMenuAcesso ('/fornecedores/RelReciboInscritoPdf.php');
AddMenuAcesso ('/oracle/fornecedores/RotDebitoCredorConsulta.php');
AddMenuAcesso ('/oracle/fornecedores/RotConsultaInscricaoMercantil.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Origem	           = $_POST['Origem'];
	$Destino           = $_POST['Destino'];
	$_SESSION['Botao'] = $_POST['Botao'];

	if ($Origem == "A") {
		# Variáveis do Formulário A #
		if ($_SESSION['CPF_CNPJ'] != $_POST['CPF_CNPJ']) {
			$_SESSION['Irregularidade'] = "";
		}
		$_SESSION['CPF_CNPJ']		  = $_POST['CPF_CNPJ'];
		$_SESSION['TipoCnpjCpf']	  = $_POST['TipoCnpjCpf'];
		$_SESSION['Critica']		  = $_POST['Critica'];
		$_SESSION['MicroEmpresa']     = $_POST['MicroEmpresa'];
		$_SESSION['Identidade']		  = strtoupper2(trim($_POST['Identidade']));
		$_SESSION['OrgaoUF']    	  = strtoupper2(trim($_POST['OrgaoUF']));
		$_SESSION['RazaoSocial']	  = strtoupper2(trim($_POST['RazaoSocial']));
		$_SESSION['NomeFantasia']     = strtoupper2(trim($_POST['NomeFantasia']));
		$_SESSION['CEP']       		  = $_POST['CEP'];
		$_SESSION['CEPInformado']     = $_POST['CEP'];
		$_SESSION['CEPAntes']  		  = $_POST['CEPAntes'];
		$_SESSION['Logradouro']		  = strtoupper2(trim($_POST['Logradouro']));
		$_SESSION['Numero']			  = $_POST['Numero'];
		$_SESSION['Complemento']	  = strtoupper2(trim($_POST['Complemento']));
		$_SESSION['Bairro']			  = strtoupper2(trim($_POST['Bairro']));
		$_SESSION['Cidade']			  = strtoupper2(trim($_POST['Cidade']));
		$_SESSION['UF']  			  = $_POST['UF'];
		$_SESSION['DDD']          	  = $_POST['DDD'];
		$_SESSION['Telefone']     	  = $_POST['Telefone'];
		$_SESSION['Email']		      = trim($_POST['Email']);
		$_SESSION['Email2']		      = trim($_POST['Email2']);
		$_SESSION['EmailPopup']		  = $_SESSION['Email'];
		$_SESSION['Fax'] 			  = $_POST['Fax'];
		$_SESSION['RegistroJunta']    = $_POST['RegistroJunta'];
		$_SESSION['DataRegistro']     = $_POST['DataRegistro'];
		$_SESSION['NomeContato']      = strtoupper2(trim($_POST['NomeContato']));
		$_SESSION['CPFContato']       = $_POST['CPFContato'];
		$_SESSION['CargoContato']     = strtoupper2(trim($_POST['CargoContato']));
		$_SESSION['DDDContato']       = $_POST['DDDContato'];
		$_SESSION['TelefoneContato']  = $_POST['TelefoneContato'];
		$_SESSION['TipoHabilitacao']  = $_POST['TipoHabilitacao'];
		$_SESSION['NoSocios']         = $_POST['NoSocios']; //noSocios- conta o número de sócios, incluindo os sócios deletados
		$_SESSION['SociosCPF_CNPJ']   = $_POST['SociosCPF_CNPJ']; //SociosCPF_CNPJ- Possui o CPF/CNPJ dos sócios. Também inclui os CPF/CNPJs deletados
		$_SESSION['SociosNome']       = $_POST['SociosNome']; //SociosNome- Possui os nomes dos sócios. Note que o array inclui os nomes dos sócios deletados, que não serão adicionados
		$_SESSION['SocioNovoNome']    = $_POST['SocioNovoNome']; //SocioNovoNome- Nome do novo sócio a ser adicionado
		$_SESSION['SocioNovoCPF']     = $_POST['SocioNovoCPF']; //SocioNovoCPF- CPF do sócio a ser adicionado
		$_SESSION['SocioSelecionado'] = $_POST['SocioSelecionado']; //SocioSelecionado- Sócio selecionado para algum comando (apenas usado no comando de remover sócio)
		$_SESSION['MostrarNovoSocio'] = false; // MostrarNovoSocio- Informa que o nome e o CPF do sócio devem ser mostrados nas caixas de texto. Usado para o caso de correção do campo
	}

	if ($Origem == "B") {
		# Variáveis do Formulário B #
		if ($_SESSION['InscMercantil'] != $_POST['InscMercantil']) {
			$_SESSION['InscricaoValida'] = "";
		}
		$_SESSION['InscEstadual']	     = $_POST['InscEstadual'];
		$_SESSION['InscMercantil']       = $_POST['InscMercantil'];
		$_SESSION['InscOMunic']   	     = $_POST['InscOMunic'];
		$_SESSION['Certidao']   		 = $_POST['Certidao'];
		$_SESSION['DataCertidaoComp']    = $_POST['DataCertidaoComp'];
		$_SESSION['CertidaoObrigatoria'] = $_POST['CertidaoObrigatoria'];
		$_SESSION['DataCertidaoOb']      = $_POST['DataCertidaoOb'];
		$_SESSION['CheckComplementar']   = $_POST['CheckComplementar'];
	}

	if ($Origem == "C") {
		# Variáveis do Formulário C #
		$_SESSION['CapSocial']      	  = $_POST['CapSocial'];
		$_SESSION['CapIntegralizado']     = $_POST['CapIntegralizado'];
		$_SESSION['Patrimonio']    	      = $_POST['Patrimonio'];
		$_SESSION['IndLiqCorrente']       = $_POST['IndLiqCorrente'];
		$_SESSION['IndLiqGeral']    	  = $_POST['IndLiqGeral'];
		$_SESSION['IndEndividamento']	  = $_POST['IndEndividamento'];
		$_SESSION['IndSolvencia']         = $_POST['IndSolvencia'];
		$_SESSION['Banco1']      		  = strtoupper2(trim($_POST['Banco1']));
		$_SESSION['Agencia1']      	      = strtoupper2(trim($_POST['Agencia1']));
		$_SESSION['ContaCorrente1']       = strtoupper2(trim($_POST['ContaCorrente1']));
		$_SESSION['Banco2']     		  = strtoupper2(trim($_POST['Banco2']));
		$_SESSION['Agencia2']    		  = strtoupper2(trim($_POST['Agencia2']));
		$_SESSION['ContaCorrente2']       = strtoupper2(trim($_POST['ContaCorrente2']));
		$_SESSION['DataBalanco']          = $_POST['DataBalanco'];
		$_SESSION['DataNegativa']         = $_POST['DataNegativa'];
		$_SESSION['DataContratoEstatuto'] = $_POST['DataContratoEstatuto'];
	}

	if ($Origem == "D") {
		# Variáveis do Formulário D #
		$_SESSION['RegistroEntidade'] = $_POST['RegistroEntidade'];
		$_SESSION['NomeEntidade']     = strtoupper2(trim($_POST['NomeEntidade']));
		$_SESSION['DataVigencia']     = $_POST['DataVigencia'];
		$_SESSION['RegistroTecnico']  = $_POST['RegistroTecnico'];
		$_SESSION['AutorizaNome']	  = strtoupper2(trim($_POST['AutorizaNome']));
		$_SESSION['AutorizaRegistro'] = $_POST['AutorizaRegistro'];
		$_SESSION['AutorizaData']	  = $_POST['AutorizaData'];
		$_SESSION['CheckAutorizacao'] = $_POST['CheckAutorizacao'];
		$_SESSION['CheckMateriais']   = $_POST['CheckMateriais'];
		$_SESSION['CheckServicos']    = $_POST['CheckServicos'];
		$_SESSION['Cumprimento']	  = $_POST['Cumprimento'];
		$_SESSION['EmailPopup']       = $_POST['EmailPopup'];
		//$_SESSION['Email']            = $_SESSION['EmailPopup'];
	}

	if ($Origem == "E") {
		# Variáveis do Formulário E #

		$_SESSION['DDocumento']  = $_POST['DDocumento'];
		$_SESSION['tipoDoc'] = $_POST['tipoDoc'];
		$_SESSION['tipoDocDesc'] = $_POST['tipoDocDesc'];
		$_SESSION['obsDocumento'] = $_POST['obsDocumento'];

		// dados para retorno em caso de erro
		


	}

} else {
	$_SESSION['Irregularidade']  = $_GET['Irregularidade'];
	$_SESSION['InscricaoValida'] = $_GET['InscricaoValida'];
	$_SESSION['Mensagem']        = html_entity_decode(urldecode($_GET['Mensagem']));
	$_SESSION['Mens']   	     = $_GET['Mens'];
	$_SESSION['Tipo']            = $_GET['Tipo'];
	$Origem	                     = $_GET['Origem'];
	$Destino	                 = $_GET['Destino'];
}

# Reseta variáveis que não são usadas quando não é licitação
if ($_SESSION['TipoHabilitacao'] != 'L') {
	// Origem C
	$_SESSION['CapSocial']            = null;
	$_SESSION['CapIntegralizado']     = null;
	$_SESSION['Patrimonio']           = null;
	$_SESSION['IndLiqCorrente']       = null;
	$_SESSION['IndLiqGeral']          = null;
	$_SESSION['IndEndividamento']     = null;
	$_SESSION['IndSolvencia']         = null;
	$_SESSION['DataBalanco']          = null;
	$_SESSION['DataNegativa']         = null;
	$_SESSION['DataContratoEstatuto'] = null;

	// Origem D
	$_SESSION['Cumprimento']      = null;
	$_SESSION['RegistroEntidade'] = null;
	$_SESSION['NomeEntidade']     = null;
	$_SESSION['DataVigencia']     = null;
	$_SESSION['RegistroTecnico']  = null;
	$_SESSION['AutorizaNome']     = null;
	$_SESSION['AutorizaRegistro'] = null;
	$_SESSION['AutorizaData']     = null;
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Limpa as variaveis que estão na sessão #
if ($Origem == "" or is_null($_SESSION['CPF_CNPJ'])) {
	LimparSessao();
	$_SESSION['TipoHabilitacao'] = "L";
}


# Chamada das Criticas das Abas #
if ($Origem == "A" or $Origem == "") {
	$_SESSION['DestinoCons'] = $Destino;
	CriticaAbaHabilitacao();
} elseif ($Origem == "B") {
	$_SESSION['DestinoInsc'] = $Destino;
	CriticaAbaRegularidadeFiscal();
} elseif ($Origem == "C") {
	CriticaAbaQualificEconFinanceira();
} elseif ($Origem == "D") {
	CriticaAbaQualificTecnica();
} elseif ($Origem == "E" && $_SESSION['Botao'] != "Incluir") {
	CriticaAbaDocumentos();
}

# Aba de Habilitação Jurídica  - Formulário A #
if (($Origem == "A" or $Origem == "")) {
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
	if ($_SESSION['Botao'] == "Incluir") {
		//CriticaAbaRegularidadeFiscal(); // Verifica Critica do Formulário B
		//CriticaAbaQualificEconFinanceira(); // Verifica Critica do Formulário C
		//CriticaAbaQualificTecnica(); // Verifica Critica do Formulário D
		//CriticaAbaDocumentos();//Verifica Critica do Formulário E

		if (strlen($_SESSION['CPF_CNPJ']) != 11 and strlen($_SESSION['CPF_CNPJ']) != 14) {
			$_SESSION['Mens']     = 1;
			$_SESSION['Tipo']     = 1;
			$_SESSION['Mensagem'] = "CPF ou CNPJ inválido";
		} else {
			# Verifica se o Fornecedor já foi Cadastrado #
			$db = Conexao();
			$db->query("BEGIN TRANSACTION");
			
			$sql = "SELECT COUNT(AFORCRSEQU) FROM SFPC.TBFORNECEDORCREDENCIADO WHERE ";
				
				if (strlen($_SESSION['CPF_CNPJ']) == 11) {
					$sql .= "AFORCRCCPF = '".$_SESSION['CPF_CNPJ']."' ";
				} elseif (strlen($_SESSION['CPF_CNPJ']) == 14) {
					$sql .= "AFORCRCCGC = '".$_SESSION['CPF_CNPJ']."' ";
				}
				
			$result = $db->query($sql);
			
			if (PEAR::isError($result)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$rows = $result->numRows();
				
				if ($rows != 0) {
					$Linha = $result->fetchRow();
					$ExisteFornecedor = $Linha[0];
					
					if ($ExisteFornecedor == 0) {
						# Verifica se o Fornecedor Já foi Inscrito #
						$sqlpre = "SELECT CPREFSCODI,EPREFOMOTI FROM SFPC.TBPREFORNECEDOR WHERE ";

							# Colocar CPF/CGC para o Inscrito #
							if (strlen($_SESSION['CPF_CNPJ']) == 11) {
								$PreForCPF = "'".$_SESSION['CPF_CNPJ']."'";
								$PreForCGC = "NULL";
								$sqlpre   .= "APREFOCCPF = $PreForCPF ";
							} elseif (strlen($_SESSION['CPF_CNPJ']) == 14) {
								$PreForCGC = "'".$_SESSION['CPF_CNPJ']."'";
								$PreForCPF = "NULL";
								$sqlpre   .= "APREFOCCGC = $PreForCGC ";
							}
						
						$respre = $db->query($sqlpre);
						
						if (PEAR::isError($respre)) {
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						} else {
							$Linha    = $respre->fetchRow();
							$Situacao = $Linha[0];
							$Motivo   = $Linha[1];

							if ($Situacao == "") {
								if ($_SESSION['Cumprimento'] == "S" or $_SESSION['TipoHabilitacao'] != "L") {
									# Recupera a último sequencial e incrementa mais um #
									$sqlpre = "SELECT MAX(APREFOSEQU) AS Maximo FROM SFPC.TBPREFORNECEDOR";
									
									$respre = $db->query($sqlpre);
									
									if (PEAR::isError($respre)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									} else {
										$_SESSION['cont']++;
										$Maximo    = $respre->fetchRow();
										$PreForSeq = $Maximo[0] + 1;
										
										$PreForSituacao = 1; // Coloca a Situação do Pré-Cadastro - EM ANÁLISE
										$DataAtual = date("Y-m-d H:i:s"); // Data de Geração do Pré-Cadastro

										# Atribuindo Valor NULL - Formulário A #
										if( $_SESSION['MicroEmpresa']    == "" ){ $MicroEmpresa    = "NULL"; }else{ $MicroEmpresa    = "'".$_SESSION['MicroEmpresa']."'"; }
										if( $_SESSION['Identidade']      == "" ){ $Identidade      = "NULL"; }else{ $Identidade      = "'".$_SESSION['Identidade']."'"; }
										if( $_SESSION['OrgaoUF']         == "" ){ $OrgaoUF         = "NULL"; }else{ $OrgaoUF         = "'".$_SESSION['OrgaoUF']."'"; }
										if( $_SESSION['NomeFantasia']    == "" ){ $NomeFantasia    = "NULL"; }else{ $NomeFantasia    = "'".$_SESSION['NomeFantasia']."'"; }
										if( $_SESSION['DDD']             == "" ){ $DDD             = "NULL"; }else{ $DDD             = $_SESSION['DDD']; }
										if( $_SESSION['Numero']          == "" ){ $Numero          = "NULL"; }else{ $Numero          = $_SESSION['Numero']; }
										if( $_SESSION['Complemento']     == "" ){ $Complemento     = "NULL"; }else{ $Complemento     = "'".$_SESSION['Complemento']."'"; }
										if( $_SESSION['Telefone']        == "" ){ $Telefone        = "NULL"; }else{ $Telefone        = "'".$_SESSION['Telefone']."'"; }
										if( $_SESSION['Fax']             == "" ){ $Fax             = "NULL"; }else{ $Fax             = "'".$_SESSION['Fax']."'"; }
										if( $_SESSION['NomeContato']     == "" ){ $NomeContato     = "NULL"; }else{ $NomeContato     = "'".$_SESSION['NomeContato']."'"; }
										if( $_SESSION['CPFContato']      == "" ){ $CPFContato      = "NULL"; }else{ $CPFContato      = "'".$_SESSION['CPFContato']."'"; }
										if( $_SESSION['CargoContato']    == "" ){ $CargoContato    = "NULL"; }else{ $CargoContato    = "'".$_SESSION['CargoContato']."'"; }
										if( $_SESSION['DDDContato']      == "" ){ $DDDContato      = "NULL"; }else{ $DDDContato      = $_SESSION['DDDContato']; }
										if( $_SESSION['TelefoneContato'] == "" ){ $TelefoneContato = "NULL"; }else{ $TelefoneContato = "'".$_SESSION['TelefoneContato']."'"; }
										if( $_SESSION['RegistroJunta']	 == "" ){ $RegistroJunta	 = "NULL"; }else{ $RegistroJunta	 = "'".$_SESSION['RegistroJunta']."'"; }
										if( $_SESSION['DataRegistro']	 == "" ){ $DataRegistroInv	 = "NULL"; }else{ $DataRegistroInv	= "'".substr($_SESSION['DataRegistro'],6,4)."-".substr($_SESSION['DataRegistro'],3,2)."-".substr($_SESSION['DataRegistro'],0,2)."'"; }
										if( $_SESSION['Email2'] == "" or $_SESSION['Email2'] == "NULL" ){ $Email2 = "NULL"; }else{ $Email2 = "'".$_SESSION['Email2']."'"; }
										if( $_SESSION['Email'] == "" or $_SESSION['Email'] == "NULL" ){ $Email = "NULL"; }else{ $Email = "'".$_SESSION['Email']."'"; }
																			
										# Atribuindo Valor NULL - Formulário B #
										if( $_SESSION['InscMercantil'] == "" ){ $InscMercantil = "NULL"; }else{ $InscMercantil = $_SESSION['InscMercantil']; }
										if( $_SESSION['InscEstadual']  == "" ){ $InscEstadual  = "NULL"; }else{ $InscEstadual  = $_SESSION['InscEstadual']; }
										if( $_SESSION['InscOMunic']    == "" ){ $InscOMunic    = "NULL"; }else{ $InscOMunic    = $_SESSION['InscOMunic']; }

										# Atribuindo Valor NULL - Formulário C #
										if( $_SESSION['CapSocial']        == "" ){ $CapSocial        = "NULL"; }else{ $CapSocial        = str_replace(",", ".",$_SESSION['CapSocial']); }
										if( $_SESSION['Patrimonio']       == "" ){ $Patrimonio       = "NULL"; }else{ $Patrimonio       = str_replace(",", ".",$_SESSION['Patrimonio']); }
										if( $_SESSION['CapIntegralizado'] == "" ){ $CapIntegralizado = "NULL"; }else{ $CapIntegralizado = str_replace(",", ".",$_SESSION['CapIntegralizado']); }
										if( $_SESSION['IndLiqCorrente']   == "" ){ $IndLiqCorrente   = "NULL"; }else{ $IndLiqCorrente   = str_replace(",", ".",$_SESSION['IndLiqCorrente']); }
										if( $_SESSION['IndLiqGeral']      == "" ){ $IndLiqGeral      = "NULL"; }else{ $IndLiqGeral      = str_replace(",", ".",$_SESSION['IndLiqGeral']); }
										if( $_SESSION['IndEndividamento'] == "" ){ $IndEndividamento = "NULL"; }else{ $IndEndividamento = str_replace(",",".",$_SESSION['IndEndividamento']); }
										if( $_SESSION['IndSolvencia']     == "" ){ $IndSolvencia     = "NULL"; }else{ $IndSolvencia     = str_replace(",",".",$_SESSION['IndSolvencia']); }
										if( $_SESSION['DataBalanco']      == "" or $_SESSION['DataBalanco']  == "//" ){ $DataBalanco      = "NULL"; }else{ $DataBalanco      = "'".substr($_SESSION['DataBalanco'],6,4)."-".substr($_SESSION['DataBalanco'],3,2)."-".substr($_SESSION['DataBalanco'],0,2)."'"; }
										if( $_SESSION['DataNegativa']     == "" or $_SESSION['DataBalanco']  == "//" ){ $DataNegativa     = "NULL"; }else{ $DataNegativa     = "'".substr($_SESSION['DataNegativa'],6,4)."-".substr($_SESSION['DataNegativa'],3,2)."-".substr($_SESSION['DataNegativa'],0,2)."'"; }
										if( $_SESSION['DataContratoEstatuto']     == "" or $_SESSION['DataContratoEstatuto']  == "//" ){ $DataContratoEstatuto     = "NULL"; }else{ $DataContratoEstatuto     = "'".substr($_SESSION['DataContratoEstatuto'],6,4)."-".substr($_SESSION['DataContratoEstatuto'],3,2)."-".substr($_SESSION['DataContratoEstatuto'],0,2)."'"; }

										# Atribuindo Valor NULL - Formulário D #
										if( $_SESSION['NomeEntidade']     == "" ){ $NomeEntidade     = "NULL"; }else{ $NomeEntidade     = "'".$_SESSION['NomeEntidade']."'"; }
										if( $_SESSION['RegistroEntidade'] == "" ){ $RegistroEntidade = "NULL"; }else{ $RegistroEntidade = $_SESSION['RegistroEntidade']; }
										if( $_SESSION['DataVigencia']     == "" or $_SESSION['DataBalanco']  == "//" ){ $DataVigencia     = "NULL"; }else{ $DataVigencia     = "'".substr($_SESSION['DataVigencia'],6,4)."-".substr($_SESSION['DataVigencia'],3,2)."-".substr($_SESSION['DataVigencia'],0,2)."'"; }
										if( $_SESSION['RegistroTecnico']  == "" ){ $RegistroTecnico  = "NULL"; }else{ $RegistroTecnico  = $_SESSION['RegistroTecnico']; }
										//if( $_SESSION['TipoHabilitacao']  == "" ){ $TipoHabilitacao  = "NULL"; }else{ $TipoHabilitacao  = "'".$_SESSION['TipoHabilitacao']."'"; }

										# Colocando o CEP de Logragouro ou Material #
										if( $_SESSION['Localidade'] == "S" ){
												$Cep           = "NULL";
												$CepLocalidade = $_SESSION['CEP'];
										}else{
												$Cep           = $_SESSION['CEP'];
												$CepLocalidade = "NULL";
										}

										# Insere Pré-Fornecedor #  erro- data contrato ou estatuto da tabela fornecedor credenciado
										$sql    = "INSERT INTO SFPC.TBPREFORNECEDOR ( ";
										$sql   .= "APREFOSEQU, APREFOCCGC, APREFOCCPF, APREFOIDEN, NPREFOORGU, CPREFSCODI, ";
										$sql   .= "NPREFOSENH, EPREFOMOTI, NPREFORAZS, NPREFOFANT, CCEPPOCODI, CCELOCCODI, ";
										$sql   .= "EPREFOLOGR, APREFONUME, EPREFOCOMP, EPREFOBAIR, NPREFOCIDA, CPREFOESTA, ";
										$sql   .= "APREFOCDDD, APREFOTELS, APREFONFAX, NPREFOMAIL, APREFOCPFC, NPREFOCONT, ";
										$sql   .= "NPREFOCARG, APREFODDDC, APREFOTELC, APREFOREGJ, DPREFOREGJ, APREFOINES, ";
										$sql   .= "APREFOINME, APREFOINSM, VPREFOCAPS, VPREFOCAPI, VPREFOPATL, VPREFOINLC, ";
										$sql   .= "VPREFOINLG, DPREFOULTB, DPREFOCNFC, DPREFOCONT, NPREFOENTP, APREFOENTR, ";
										$sql   .= "DPREFOVIGE, APREFOENTT, DPREFOGERA, CGREMPCODI, CUSUPOCODI, APREFONTEN, ";
										$sql   .= "DPREFOEXPS, TPREFOULAT, FPREFOMEPP, VPREFOINDI, VPREFOINSO, NPREFOMAI2, ";
										$sql   .= "FPREFOTIPO  ";
										$sql   .= ") VALUES ( ";
										$sql   .= "$PreForSeq, $PreForCGC, $PreForCPF, $Identidade, $OrgaoUF, $PreForSituacao, ";
										$sql   .= "NULL, NULL, '".$_SESSION['RazaoSocial']."', $NomeFantasia, $Cep, $CepLocalidade, ";
										$sql   .= "'".$_SESSION['Logradouro']."', $Numero, $Complemento, '".$_SESSION['Bairro']."', '".$_SESSION['Cidade']."', '".$_SESSION['UF']."', ";
										$sql   .= "$DDD, $Telefone, $Fax, $Email, $CPFContato, $NomeContato, ";
										$sql   .= "$CargoContato, $DDDContato, $TelefoneContato, $RegistroJunta, $DataRegistroInv, $InscEstadual, ";
										$sql   .= "$InscMercantil, $InscOMunic, $CapSocial, $CapIntegralizado, $Patrimonio, $IndLiqCorrente, ";
										$sql   .= "$IndLiqGeral, $DataBalanco, $DataNegativa, $DataContratoEstatuto, $NomeEntidade, $RegistroEntidade, $DataVigencia, ";
										$sql   .= "$RegistroTecnico, '".substr($DataAtual,0,10)."', ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", NULL, NULL, ";
										$sql   .= "'$DataAtual', $MicroEmpresa, $IndEndividamento, $IndSolvencia, ".$Email2.", '".$_SESSION['TipoHabilitacao']."') ";
										
										$result = $db->query($sql);

										if (PEAR::isError($result)) {
											$db->query("ROLLBACK");
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										} else {
											# Inserindo Contas Bancárias #
											if ($_SESSION['Banco1'] != "" and $_SESSION['Agencia1'] != "" and $_SESSION['ContaCorrente1'] != "") {
												$sql  = "INSERT INTO SFPC.TBPREFORNCONTABANCARIA ( ";
												$sql .= "APREFOSEQU, CPRECOBANC, CPRECOAGEN, CPRECOCONT, TPRECOULAT ";
												$sql .= ") VALUES ( ";
												$sql .= "$PreForSeq, '".$_SESSION['Banco1']."', '".$_SESSION['Agencia1']."', '".$_SESSION['ContaCorrente1']."', '$DataAtual' ) ";
												
												$result = $db->query($sql);
												
												if (PEAR::isError($result)) {
													$db->query("ROLLBACK");
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}
											}
											
											if ($_SESSION['Banco2'] != "" and $_SESSION['Agencia2'] != "" and $_SESSION['ContaCorrente2'] != "") {
												$sql  = "INSERT INTO SFPC.TBPREFORNCONTABANCARIA ( ";
												$sql .= "APREFOSEQU, CPRECOBANC, CPRECOAGEN, CPRECOCONT, TPRECOULAT ";
												$sql .= ") VALUES ( ";
												$sql .= "$PreForSeq, '".$_SESSION['Banco2']."', '".$_SESSION['Agencia2']."', '".$_SESSION['ContaCorrente2']."', '$DataAtual' ) ";
												
												$result = $db->query($sql);
												
												if (PEAR::isError($result)) {
													$db->query("ROLLBACK");
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}
											}

											# Inserindo Sócios #
											if ($_SESSION['TipoCnpjCpf'] == "CNPJ") {
												for ($i=0; $i< $_SESSION['NoSocios']; $i++) {
													if (!is_null($_SESSION['SociosNome'][$i])) {
														$cpfnocaracteres = strlen($_SESSION['SociosCPF_CNPJ'][$i]);
														$tipocadastro    = NULL;
														
														if ($cpfnocaracteres==11) {
															$tipocadastro = "F";
														}elseif ($cpfnocaracteres==14) {
															$tipocadastro = "J";
														}
														
														if (is_null($tipocadastro)) {
															EmailErro("Erro na inclusão de sócios", __FILE__, __LINE__, "Erro na inclusão de sócios de fornecedores. CPF/CNPJ não está em um tamanho válido");
														} else {
															$sql = "INSERT INTO SFPC.TBSOCIOPREFORNECEDOR (APREFOSEQU, nsoprenome, asoprecada, fsopretcad, tsopreulat)
																	VALUES (".$PreForSeq.", '".$_SESSION['SociosNome'][$i]."', '".$_SESSION['SociosCPF_CNPJ'][$i]."', '".$tipocadastro."', '$DataAtual') ";
															
															$result = $db->query($sql);
															
															if (PEAR::isError($result)) {
																$db->query("ROLLBACK");
																ExibeErroBD($_SESSION['ErroPrograma']."\nLinha: ".__LINE__."\nSql: $sql");
															}
														}
													}
												}
											}

											# Inserindo Certidões #
											if (count($_SESSION['CertidaoObrigatoria']) != 0) {
												for ($i=0; $i< count($_SESSION['CertidaoObrigatoria']); $i++) {
													if (!is_null($_SESSION['CertidaoObrigatoria'][$i]) and $_SESSION['CertidaoObrigatoria'][$i]!="" and !is_null($_SESSION['DataCertidaoOb'][$i]) and $_SESSION['DataCertidaoOb'][$i]!="") {
														$DataCertidaoObInv = substr($_SESSION['DataCertidaoOb'][$i],6,4)."-".substr($_SESSION['DataCertidaoOb'][$i],3,2)."-".substr($_SESSION['DataCertidaoOb'][$i],0,2);
														
														$sql = "INSERT INTO SFPC.TBPREFORNCERTIDAO (APREFOSEQU, CTIPCECODI, DPREFCVALI, TPREFCULAT)
																VALUES ($PreForSeq, ".$_SESSION['CertidaoObrigatoria'][$i].", '$DataCertidaoObInv', '$DataAtual') ";
														
														$result = $db->query($sql);
														
														if (PEAR::isError($result)) {
															$db->query("ROLLBACK");
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}
													}
												}
											}

											if (count($_SESSION['CertidaoComplementar']) != 0) {
												for ($i=0; $i< count($_SESSION['CertidaoComplementar']); $i++) {
													if (!is_null($_SESSION['CertidaoComplementar'][$i]) and $_SESSION['CertidaoComplementar'][$i]!="" and !is_null($_SESSION['DataCertidaoComp'][$i]) and $_SESSION['DataCertidaoComp'][$i]!="") {
														$DataCertidaoCompInv = substr($_SESSION['DataCertidaoComp'][$i],6,4)."-".substr($_SESSION['DataCertidaoComp'][$i],3,2)."-".substr($_SESSION['DataCertidaoComp'][$i],0,2);
														
														$sql = "INSERT INTO SFPC.TBPREFORNCERTIDAO (APREFOSEQU, CTIPCECODI, DPREFCVALI, TPREFCULAT)
																VALUES ($PreForSeq, ".$_SESSION['CertidaoComplementar'][$i].", '$DataCertidaoCompInv', '$DataAtual') ";
														
														$result = $db->query($sql);
														
														if (PEAR::isError($result)) {
															$db->query("ROLLBACK");
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}
													}
												}
											}

											# Inserindo Autorização Específica #
											if (count($_SESSION['AutorizacaoNome']) != 0) {
												for ($i=0; $i< count($_SESSION['AutorizacaoNome']); $i++) {
													if (!is_null($_SESSION['AutorizacaoNome'][$i]) and $_SESSION['AutorizacaoNome'][$i]!="") {
														$AutorizacaoDataInv = substr($_SESSION['AutorizacaoData'][$i],6,4)."-".substr($_SESSION['AutorizacaoData'][$i],3,2)."-".substr($_SESSION['AutorizacaoData'][$i],0,2);
														
														$sql = "INSERT INTO SFPC.TBPREFORNAUTORIZACAOESPECIFICA (APREFOSEQU, NPREFANOMA, APREFANUMA, DPREFAVIGE, TPREFAULAT)
																VALUES ($PreForSeq, '".$_SESSION['AutorizacaoNome'][$i]."', ".$_SESSION['AutorizacaoRegistro'][$i].", '$AutorizacaoDataInv', '$DataAtual') ";
														
														$result = $db->query($sql);
														
														if (PEAR::isError($result)) {
															$db->query("ROLLBACK");
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}
													}
												}
											}

											# Incluindo Grupos de Fornecimento #
											if (count($_SESSION['Materiais']) != 0) {
												for ($i=0; $i< count($_SESSION['Materiais']); $i++) {
													$GrupoMaterial = explode("#",$_SESSION['Materiais'][$i]);
													
													$sql = "INSERT INTO SFPC.TBGRUPOPREFORNECEDOR (CGRUMSCODI, APREFOSEQU, TGRUPFULAT)
															VALUES (".$GrupoMaterial[1].", $PreForSeq, '$DataAtual') ";
																									
													$result = $db->query($sql);
													
													if (PEAR::isError($result)) {
														$db->query("ROLLBACK");
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}
												}
											}

											if (count($_SESSION['Servicos']) != 0) {
												for ($i=0; $i< count($_SESSION['Servicos']); $i++) {
													$GrupoServico = explode("#",$_SESSION['Servicos'][$i]);
													
													$sql = "INSERT INTO SFPC.TBGRUPOPREFORNECEDOR (CGRUMSCODI, APREFOSEQU, TGRUPFULAT)
															VALUES (".$GrupoServico[1].", $PreForSeq, '$DataAtual' ) ";

													$result = $db->query($sql);
													
													if (PEAR::isError($result)) {
														$db->query("ROLLBACK");
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}
												}
											}
											
											if (count($_SESSION['Arquivos_Upload_Insc']) != 0) {
												for ($i=0; $i< count($_SESSION['Arquivos_Upload_Insc']); $i++) {

													$arquivo = $_SESSION['Arquivos_Upload_Insc'];
													if($arquivo['situacao'][$i] == 'novo'){
														// fazer sql para trazer o sequencial
														$sql = ' SELECT cfdocusequ FROM SFPC.tbfornecedordocumento WHERE  1=1 ORDER BY cfdocusequ DESC limit 1';
														$seqDocumento = resultValorUnico(executarTransacao($db, $sql)) + 1;
								
														$anexo =  bin2hex($arquivo['conteudo'][$i]);

														$sqlAnexo = "INSERT INTO sfpc.tbfornecedordocumento
														(cfdocusequ, aprefosequ, aforcrsequ, afdocuanoa, cfdoctcodi, efdocunome, ifdocuarqu, ffdocuforn, tfdocuanex, ffdocusitu, cusupocodi, tfdoctulat)
														VALUES(".$seqDocumento.", ".$PreForSeq.", NULL, DATE_PART('YEAR', CURRENT_TIMESTAMP), ".$arquivo['tipoCod'][$i].", '".$arquivo['nome'][$i]."', decode('".$anexo."','hex'), 'N', now(), 'A', ".$_SESSION['_cusupocodi_'].", now());
														";

														$resultAnexo = $db->query($sqlAnexo);
														
														if (PEAR::isError($resultAnexo)) {
															$db->query("ROLLBACK");
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlAnexo");
														}else{
															//insere a fase do documento
															$observacaoTratada = str_replace("'","''",$arquivo['observacao'][$i]);
															//CR235018 KIM ALTERAÇÃO DA SITUAÇÃO DO ARQUIVO ANEXADO. SITUAÇÃO FOI DE 1 PARA 2
															$sqlHist = "INSERT INTO sfpc.tbfornecedordocumentohistorico
																	(cfdocusequ, cfdocscodi, efdochobse, tfdochcada, cusupocodi, tfdochulat)
																	VALUES(".$seqDocumento.", 2, '".$observacaoTratada."', now(), ".$_SESSION['_cusupocodi_'].", now());

															";
		
															$resultHist = $db->query($sqlHist);
															
															if (PEAR::isError($resultHist)) {
																$db->query("ROLLBACK");
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlHist");
															}

														}

													}
												}
											}

											# Busca E-mail do Fornecedor #
											$db  = Conexao();
											
											$sql = "SELECT NPREFOMAIL, NPREFOMAI2 FROM SFPC.TBPREFORNECEDOR WHERE APREFOSEQU = $PreForSeq";
											
											$result = $db->query($sql);
											
											if (PEAR::isError($result)) {
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											} else {
												$Linha = $result->fetchRow();
												$Email = $Linha[0];
												$Email2 = $Linha[1];
											}

											# Cria a senha do Usuário #
											$Senha             = CriaSenha();
											$_SESSION['Senha'] = $Senha;
											$SenhaCript        = crypt ($Senha,"P");
											$DataExpSenha      = SubtraiData("1",date("d/m/Y"));
											$DataExpSenhaInv   = substr($DataExpSenha,6,4)."-".substr($DataExpSenha,3,2)."-".substr($DataExpSenha,0,2);

											# Atualiza a senha do Usuário #
											$sql = "UPDATE SFPC.TBPREFORNECEDOR
													SET NPREFOSENH = '$SenhaCript', DPREFOEXPS = '$DataExpSenhaInv', APREFONTEN = 0, TPREFOULAT = '$DataAtual' 
													WHERE APREFOSEQU = $PreForSeq";
											
											$result = $db->query($sql);

											if (PEAR::isError($result)) {
												$db->query("ROLLBACK");
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											} else {
												$_SESSION['Mens']     = 1;
												$_SESSION['Tipo']     = 1;
												$_SESSION['Mensagem'] = "Inscrição Incluída com Sucesso";

												# Envia a senha pelo e-mail do usuário #
												if ($Email != "") {
													if (strlen($_SESSION['CPF_CNPJ']) == 11) {
														$TipoForn     = "Nome do Fornecedor";
														$CpfCgcMail   = "CPF";
														$CpfCgcNumero = substr($_SESSION['CPF_CNPJ'],0,3).".".substr($_SESSION['CPF_CNPJ'],3,3).".".substr($_SESSION['CPF_CNPJ'],6,3)."-".substr($_SESSION['CPF_CNPJ'],9,2);
													} elseif (strlen($_SESSION['CPF_CNPJ']) == 14) {
														$TipoForn     = "Razão Social do Fornecedor";
														$CpfCgcMail   = "CNPJ";
														$CpfCgcNumero = substr($_SESSION['CPF_CNPJ'],0,2).".".substr($_SESSION['CPF_CNPJ'],2,3).".".substr($_SESSION['CPF_CNPJ'],5,3)."/".substr($_SESSION['CPF_CNPJ'],8,4)."-".substr($_SESSION['CPF_CNPJ'],12,2);
													}
													EnviaEmail("$Email","Senha Temporária de Inscrição no Portal de Compras da Prefeitura do Recife","\n A Inscrição de fornecedor da Prefeitura do Recife foi efetuada com sucesso. \n A documentação relativa à habilitação jurídica, qualificação técnica, qualificação econômico-financeira e regularidade fiscal, deverá ser enviada ao Protocolo da Gerência Geral de Licitações e Compras - GGLIC (Cais do Apolo, 925, 2.º andar, CEP:500030-903, Bairro do Recife, Recife-PE -> telefone: 3355-8235).  \n\t $TipoForn: ".$_SESSION['RazaoSocial']."\n\t $CpfCgcMail: $CpfCgcNumero \n\t Senha temporária: $Senha ","from: portalcompras@recife.pe.gov.br");
												
													if ($Email2!="" and !is_null($Email2)) {
														EnviaEmail("$Email2","Senha Temporária de Inscrição no Portal de Compras da Prefeitura do Recife","\n A Inscrição de fornecedor da Prefeitura do Recife foi efetuada com sucesso. \n A documentação relativa à habilitação jurídica, qualificação técnica, qualificação econômico-financeira e regularidade fiscal, deverá ser enviada ao Protocolo da Gerência Geral de Licitações e Compras - GGLIC (Cais do Apolo, 925, 2.º andar, CEP:500030-903, Bairro do Recife, Recife-PE -> telefone: 3355-8235).  \n\t $TipoForn: ".$_SESSION['RazaoSocial']."\n\t $CpfCgcMail: $CpfCgcNumero \n\t Senha temporária: $Senha ","from: portalcompras@recife.pe.gov.br");
													}
													$_SESSION['Mensagem'] .= ". A senha temporária foi enviada p/o e-mail do Fornecedor";
												}

												# Coloca na mensagem o caminho para Recibo #
												$Url = "RelReciboInscritoPdf.php?CodigoInsc=$PreForSeq";
												
												if (!in_array($Url,$_SESSION['GetUrl'])) {
													$_SESSION['GetUrl'][] = $Url;
												}
												$_SESSION['Mensagem'] .= ". A documentação relativa à habilitação jurídica, qualificação técnica, qualificação econômico-financeira e regularidade fiscal, deverá ser enviada ao Protocolo da Gerência Geral de Licitações e Compras - GGLIC (Cais do Apolo, 925, 2.º andar, CEP:500030-903, Bairro do Recife, Recife-PE -> telefone: 3355-8235). Para visualizar o Recibo clique <a href=\"#\" class=\"titulo1\"  onClick=\"window.open('RelReciboInscritoPdf.php?CodigoInsc=$PreForSeq','popup', 'width=800px,height=600px') \" >AQUI</a>";
												$Origem = "";

												# Limpa Variáveis #
												if (isset($_SESSION['Botao'])) {
													unset($_SESSION['Botao']);
												}
												LimparSessao();
											
												# Redireciona o programa de acordo com o botão voltar #
												if ($_SESSION['Mens'] == 0) {
													$_SESSION['Botao']    = "";
													$_SESSION['Mensagem'] = "Fornecedor Inscrito Alterado com Sucesso";

													

													$Url = "CadAvaliacaoInscritoManter.php?ProgramaSelecao=".urlencode($_SESSION['ProgramaSelecao'])."&Sequencial=".$_SESSION['Sequencial']."&Mensagem=".urlencode($_SESSION['Mensagem'])."&Mens=1&Tipo=1";
												
													if (!in_array($Url,$_SESSION['GetUrl'])) {
														$_SESSION['GetUrl'][] = $Url;
													}
													header("location: ".$Url);
													exit;
												}
											}
										} 
									}
								}
							} else {
								if ($Situacao == 5) {
									$_SESSION['Mens']     = 1;
									$_SESSION['Tipo']     = 1;
									$_SESSION['Mensagem'] = "A Inscrição do Fornecedor foi excluída. Motivo: $Motivo. Para regularizar o seu cadastro, procurar a Divisão de Credenciamento - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo, 925 - Bairro do Recife - Recife/PE";
								} else {
									$_SESSION['Mens']     = 1;
									$_SESSION['Tipo']     = 1;
									$_SESSION['Mensagem'] = "Fornecedor Já Inscrito";
								}
							}
						}
					} else {
						$_SESSION['Mens']     = 1;
						$_SESSION['Tipo']     = 1;
						$_SESSION['Mensagem'] = "Fornecedor Já Cadastrado. Acesse a Consulta de Acompanhamento de Fornecedor para visualizar o seu cadastro. Caso não possua a senha de acesso procurar a Divisão de Credenciamento - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo, 925 - Bairro do Recife - Recife/PE";
					}
				}
				$db->query("COMMIT");
				$db->query("END");
				$db->disconnect();
				unset($_SESSION['Arquivos_Upload_Insc']);
				$Destino = "A";
			}
		}
	}
	$_SESSION['Des']= $Destino;
	$_SESSION['Ori']= $Origem;
	ExibeAbas($Destino);
}

# Função para Chamada do Formulário de cada Aba #
function ExibeAbas($Destino) {
	if ($Destino == "A" or $Destino == "") {
		ExibeAbaHabilitacao();
	} elseif ($Destino == "B") {
		ExibeAbaRegularidadeFiscal();
	} elseif ($Destino == "C") {
		ExibeAbaQualificEconFinanceira();
	} elseif ($Destino == "D") {
		ExibeAbaQualificTecnica();
	} elseif ($Destino == "E") {
		ExibeAbaDocumentos();
	}
	
}

# Critica aos Campos - Formulário A #
function CriticaAbaHabilitacao() {
	if ($_SESSION['Botao'] == "Limpar") {
		
		$_SESSION['Mens']		     = "";
		$_SESSION['CPF_CNPJ']		 = "";
		$_SESSION['TipoCnpjCpf']	 = "";
		$_SESSION['MicroEmpresa']	 = "";
		$_SESSION['Identidade']		 = "";
		$_SESSION['OrgaoUF']	     = "";
		$_SESSION['RazaoSocial']	 = "";
		$_SESSION['NomeFantasia']    = "";
		$_SESSION['CEP']       		 = "";
		$_SESSION['CEPInformado']    = "";
		$_SESSION['Logradouro']		 = "";
		$_SESSION['Numero']		     = "";
		$_SESSION['Complemento']	 = "";
		$_SESSION['Bairro']		     = "";
		$_SESSION['Cidade']		     = "";
		$_SESSION['UF']  		     = "";
		$_SESSION['DDD']          	 = "";
		$_SESSION['Telefone']     	 = "";
		$_SESSION['Email']		     = "";
		$_SESSION['Email2']		     = "";
		$_SESSION['Fax'] 		     = "";
		$_SESSION['RegistroJunta']	 = "";
		$_SESSION['DataRegistro']    = "";
		$_SESSION['CPFContato']      = "";
		$_SESSION['NomeContato']     = "";
		$_SESSION['CargoContato']    = "";
		$_SESSION['DDDContato']      = "";
		$_SESSION['TelefoneContato'] = "";
		$_SESSION['NoSocios']        = 0;
		$_SESSION['SociosCPF_CNPJ']  = array();
		$_SESSION['SociosNome']      = array();
		$_SESSION['SocioNovoNome']   = "";
		$_SESSION['SocioNovoCPF']    = "";
	} else {
		$_SESSION['Mens']			= 0;
		$_SESSION['Mensagem'] = "Informe: ";
		
		if ($_SESSION['CPF_CNPJ'] != "") {
			$_SESSION['CPF_CNPJ'] = FormataCPF_CNPJ($_SESSION['CPF_CNPJ'],$_SESSION['TipoCnpjCpf']);
		}
		
		if ($_SESSION['TipoCnpjCpf'] == "CPF" and $_SESSION['Critica'] != 1) {
			$Qtd = strlen($_SESSION['CPF_CNPJ']);
			$_SESSION['TipoDoc'] = 2;
			
			if (($Qtd != 11) and ($Qtd != 0)) {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'].=", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CPF com 11 números</a>";
			} elseif ($_SESSION['CPF_CNPJ'] == "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'].=", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CPF</a>";
			} else {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'].=", ";
				}
				$cpfcnpj = valida_CPF($_SESSION['CPF_CNPJ']);
				
				if ($cpfcnpj === false) {
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CPF Válido</a>";
		  		}
			}
		} elseif ($_SESSION['TipoCnpjCpf'] == "CNPJ" and $_SESSION['Critica'] != 1) {
			$Qtd = strlen($_SESSION['CPF_CNPJ']);
			$_SESSION['TipoDoc'] = 1;
			
			if (($Qtd != 14) and ($Qtd != 0)) {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'].=", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ com 14 números</a>";
			} elseif ($_SESSION['CPF_CNPJ'] == "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'].=", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ</a>";
			} else {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'].=", ";
				}
				$cpfcnpj = valida_CNPJ($_SESSION['CPF_CNPJ']);
				
				if ($cpfcnpj === false) {
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ Válido</a>";
				}
			}
		}
		
		if (($_SESSION['Mens'] != 1) && (($_SESSION['Botao']=="A") || ($_SESSION['Botao']=="Preencher"))) {
			if ($_SESSION['CEP']!="") {
				if (!is_numeric($_SESSION['CEP'])) {
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CEP.focus();\" class=\"titulo2\">Campo 'CEP' deve ser número</a>";
				} elseif (strlen($_SESSION['CEP'])!=8) {
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CEP.focus();\" class=\"titulo2\">Campo 'CEP' deve conter 8 dígitos</a>";
				} else {
					$db = Conexao();
					
					$sql = "SELECT	CCEPPOCODI, NCEPPOLOGR, NCEPPOBAIR, NCEPPOTIPO, CCEPPOESTA, NCEPPOCOMP, NCEPPOCIDA, CCEPPOREFE, CCEPPOTIPL 
							FROM	PPDV.TBCEPLOGRADOUROBR
							WHERE	CCEPPOCODI = ".$_SESSION['CEP'];
					
					$res = $db->query($sql);
					
					if (PEAR::isError($res)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					} else {
						$rows = $res->numRows();
						
						if ($rows == 0) {
							$sqlloc  = "SELECT	CCELOCCODI, NCELOCLOCA, CCELOCESTA, CCELOCTIPO
										FROM	PPDV.TBCEPLOCALIDADEBR
										WHERE	CCELOCCODI = ".$_SESSION['CEP'];
							
							$resloc = $db->query($sqlloc);

							if (PEAR::isError($resloc)) {
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlloc");
							} else {
								$rowloc = $resloc->numRows();
								
								if ($rowloc == 0 && $_SESSION['Logradouro'] = "") {
									if ($_SESSION['Mens']== 1) {
										$_SESSION['Mensagem'] .= ", ";
									}
									$_SESSION['Mens']       = 1;
									$_SESSION['Tipo']       = 2;
									$_SESSION['Mensagem']  .= "<a href=\"javascript:document.CadInscritoIncluir.CEP.focus();\" class=\"titulo2\">O CEP informado não está cadastrado em nosso sistema. Confira novamente o número informado e, caso esteja correto, insira manualmente o logradouro, bairro, cidade e UF correspondente</a>";
								}
							}
						}
					}
				}
			}
		}
		
		if ($_SESSION['Numero'] != "") {
			if (!is_numeric($_SESSION['Numero'])) {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Numero.focus();\" class=\"titulo2\">Campo 'Número' deve conter apenas números</a>";
			}
		}
		
		if ($_SESSION['DDD'] != "") {
			if (!is_numeric($_SESSION['DDD'])) {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DDD.focus();\" class=\"titulo2\">Campo 'DDD' deve conter apenas números</a>";
			} elseif(strlen($_SESSION['DDD']) != 3) {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'].=", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DDD.focus();\" class=\"titulo2\">Campo 'DDD' deve conter 3 dígitos</a>";
			}
		}
			
		if ($cpfcnpj === true) {
			# Verifica se o Fornecedor já foi Cadastrado #
			$db = Conexao();
			$db->query("BEGIN TRANSACTION");
			
			$sql = "SELECT COUNT(AFORCRSEQU) FROM SFPC.TBFORNECEDORCREDENCIADO WHERE ";
				
				if (strlen($_SESSION['CPF_CNPJ']) == 11) {
		    		$sql .= "AFORCRCCPF = '".$_SESSION['CPF_CNPJ']."' ";
		    	} elseif (strlen($_SESSION['CPF_CNPJ']) == 14) {
		    		$sql .= "AFORCRCCGC = '".$_SESSION['CPF_CNPJ']."' ";
		    	}
				
			$result = $db->query($sql);
			
			if (PEAR::isError($result)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			} else {
				$Linha = $result->fetchRow();
		  		$ExisteFornecedor = $Linha[0];
				  
				if ($ExisteFornecedor == 0) {
					# Verifica se o Fornecedor Já foi Inscrito #
					$sqlpre = "SELECT CPREFSCODI,EPREFOMOTI FROM SFPC.TBPREFORNECEDOR WHERE ";

						# Colocar CPF/CGC para o Pré-Cadastro #
						if (strlen($_SESSION['CPF_CNPJ']) == 11) {
						    $sqlpre   .= "APREFOCCPF = '".$_SESSION['CPF_CNPJ']."'";
						} elseif (strlen($_SESSION['CPF_CNPJ']) == 14) {
						    $sqlpre   .= "APREFOCCGC = '".$_SESSION['CPF_CNPJ']."'";
						}

					$respre = $db->query($sqlpre);
					
					if (PEAR::isError($respre)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					} else {
						$rows = $respre->numRows();
						
						if ($rows != 0) {
							$Linha = $respre->fetchRow();

							$Situacao = $Linha[0];
							$Motivo   = $Linha[1];

							if ($Situacao == 5) {
								$_SESSION['Mens']     = 1;
								$_SESSION['Tipo']     = 1;
								$_SESSION['Mensagem'] = "A Inscrição do Fornecedor foi excluída. Motivo: $Motivo. Para regularizar o seu cadastro, procurar a Divisão de Credenciamento - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo, 925 - Bairro do Recife - Recife/PE";
							} else {
								$_SESSION['Mens']     = 1;
								$_SESSION['Tipo']     = 1;
								$_SESSION['Mensagem'] = "Fornecedor Já Inscrito";
							}
						}
					}
				} else {
					$_SESSION['Mens']     = 1;
					$_SESSION['Tipo']     = 1;
					$_SESSION['Mensagem'] = "Fornecedor Já Cadastrado. Acesse a Consulta de Acompanhamento de Fornecedor para visualizar o seu cadastro. Caso não possua a senha de acesso procurar a Divisão de Credenciamento - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo, 925 - Bairro do Recife - Recife/PE";
				}
			}
			
			if ($_SESSION['Mens'] == 0) {
				# Verifica a existência do CPF/CNPJ no Cadastro da Prefeitura #
				if ($_SESSION['Irregularidade'] == "") {
					$NomePrograma = urlencode("CadInscritoIncluir.php");
					
					$Url = "fornecedores/RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&TipoDoc=".$_SESSION['TipoDoc']."&Destino=".$_SESSION['DestinoCons']."&CPF_CNPJ=".$_SESSION['CPF_CNPJ']."";
					
					if (!in_array($Url,$_SESSION['GetUrl'])) {
						$_SESSION['GetUrl'][] = $Url;
					}
					//Redireciona($Url);
					//exit;
				} else {
					if ($_SESSION['Irregularidade'] == "S") {
						$_SESSION['Mens']     = 1;
						$_SESSION['Tipo']     = 1;
						$_SESSION['Mensagem'] = "Fornecedor possui alguma irregularidade com a Prefeitura, deve procurar o Centro de Atendimento ao Contribuinte - CAC da Prefeitura - térreo - no Cais do Apolo, 925 - Bairro do Recife/PE e após a solução das pendências tentar executar a Inscrição novamente";
					} else {
						if ($_SESSION['CEP'] == "") {
							if ($_SESSION['Mens']== 1) {
								$_SESSION['Mensagem'].=", ";
							}
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CEP.focus();\" class=\"titulo2\">CEP</a>";
						} else {
							if ($_SESSION['Botao'] == "Preencher") {
								if ($_SESSION['Mens'] == 0) {
									# Seleciona o Endereço de acordo com o CEP informado #
									$_SESSION['CEPAntes'] = $_SESSION['CEP'];
									 
									$sql = "SELECT	CCEPPOCODI, NCEPPOLOGR, NCEPPOBAIR, NCEPPOTIPO, CCEPPOESTA, NCEPPOCOMP, NCEPPOCIDA, CCEPPOREFE, CCEPPOTIPL
											FROM	PPDV.TBCEPLOGRADOUROBR
											WHERE	CCEPPOCODI = ".$_SESSION['CEP'];
									
									$res = $db->query($sql);
									
									if (PEAR::isError($res)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									} else {
										$rows = $res->numRows();
										
										if ($rows == 0) {
											$sqlloc  = "SELECT	CCELOCCODI, NCELOCLOCA, CCELOCESTA, CCELOCTIPO
														FROM	PPDV.TBCEPLOCALIDADEBR
														WHERE	CCELOCCODI = ".$_SESSION['CEP'];
																				
											$resloc  = $db->query($sqlloc);
											
											if (PEAR::isError($resloc)) {
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlloc");
											} else {
												$rowloc  = $resloc->numRows();
												
												if ($rowloc == 0) {
													if ($_SESSION['Mens']== 1) {
														$_SESSION['Mensagem'].=", ";
													}
													$_SESSION['Mens']       = 1;
													$_SESSION['Tipo']       = 2;
													$_SESSION['Mensagem']  .= "<a href=\"javascript:document.CadInscritoIncluir.CEP.focus();\" class=\"titulo2\">O CEP informado não está cadastrado em nosso sistema. Confira novamente o número informado e, caso esteja correto, insira manualmente o logradouro, bairro, cidade e UF correspondente</a>";
													$_SESSION['Logradouro'] = "";
													$_SESSION['Bairro']     = "";
													$_SESSION['Cidade']     = "";
													$_SESSION['UF']         = "";
													$_SESSION['Localidade'] = "";
												} else {
													$LinhaLoc               = $resloc->fetchRow();
													$_SESSION['CEP']        = $LinhaLoc[0];
													$_SESSION['Cidade']     = $LinhaLoc[1];
													$_SESSION['UF']         = $LinhaLoc[2];
													$_SESSION['Localidade'] = "S";
												}
											}
										} else {
											$linha           = $res->fetchRow();
											$_SESSION['CEP'] = $linha[0];
											
											if ($linha[3] != "" and $linha[5] != "") {
												$_SESSION['Logradouro'] = $linha[3]." ".$linha[1]." ".$linha[5];
											} else {
												if ($linha[3] != "" and  $linha[5] == "") {
													$_SESSION['Logradouro'] = $linha[3]." ".$linha[1];
												} elseif ($linha[3] == "" and  $linha[5] != "") {
													$_SESSION['Logradouro'] = $linha[1]." ".$linha[5];
												} else {
													$_SESSION['Logradouro'] = $linha[1];
												}
											}
											$_SESSION['Bairro']     = $linha[2];
											$_SESSION['Cidade']     = $linha[6];
											$_SESSION['UF']         = $linha[4];
											$_SESSION['Localidade'] = "";
										}
									}
									while (strlen($_SESSION['CEP'])<8) { //colocar 8 digitos no CEP
										$_SESSION['CEP'] = "0".$_SESSION['CEP'];
									}
								}
							} else {
								if ($_SESSION['CEPAntes'] != $_SESSION['CEP']) {
									if ($_SESSION['CEPAntes'] != "") {
										if ($_SESSION['Mens']== 1) {
											$_SESSION['Mensagem'].=", ";
										}
										$_SESSION['Mens']      = 1;
										$_SESSION['Tipo']      = 2;
										$_SESSION['Virgula']   = 2;
										$_SESSION['Mensagem']  = "<a href=\"javascript:document.CadInscritoIncluir.CEP.focus();\" class=\"titulo2\">O CEP Informado não corresponde ao Endereço, clique no Botão \"Preencher Endereço\" para atualizar o Endereço</a>";
										$_SESSION['Botao']     = "";
									
									}
								}
							}
						}
						
						if ($_SESSION['Mens'] == 0 and $_SESSION['Botao'] != "Preencher") {
							if ($_SESSION['Identidade'] == "" and $_SESSION['TipoCnpjCpf'] == "CPF") {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens']      = 1;
								$_SESSION['Tipo']      = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Identidade.focus();\" class=\"titulo2\">Identidade</a>";
							}
							
							if ($_SESSION['OrgaoUF'] == "" and $_SESSION['TipoCnpjCpf'] == "CPF") {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens']      = 1;
								$_SESSION['Tipo']      = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.OrgaoUF.focus();\" class=\"titulo2\">Órgao Emissor/UF</a>";
							}
												
							if ($_SESSION['RazaoSocial'] == "") {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'].=", ";
								}
								$_SESSION['Mens']      = 1;
								$_SESSION['Tipo']      = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RazaoSocial.focus();\" class=\"titulo2\">Razão Social</a>";
							} else {
								$sql = "SELECT	COUNT(*) AS QTD
										FROM	SFPC.TBPREFORNECEDOR
										WHERE	NPREFORAZS = '".$_SESSION['RazaoSocial']."' ";
								
								$result = executarSQL($db, $sql);
								
								$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
								
								if ($row->qtd > 0) {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'].=", ";
									}
									$_SESSION['Mens']      = 1;
									$_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RazaoSocial.focus();\" class=\"titulo2\">Razão Social já existe </a>";
								}		
							}

							if (!empty($_SESSION['NomeFantasia'])) {
								$sql = "SELECT	COUNT(*) AS QTD
										FROM	SFPC.TBPREFORNECEDOR
										WHERE	NPREFOFANT = '".$_SESSION['NomeFantasia']."' ";
													
								$result = executarSQL($db, $sql);
								
								$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
								
								if ($row->qtd > 0) {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'].=", ";
									}
									$_SESSION['Mens']      = 1;
									$_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.NomeFantasia.focus();\" class=\"titulo2\">Nome Fantasia já existe</a>";
								}
							}
															
							if ($_SESSION['Localidade'] == "S") {
								if ($_SESSION['Logradouro'] == "") {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'].=", ";
									}
									$_SESSION['Mens']      = 1;
									$_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Logradouro.focus();\" class=\"titulo2\">Logradouro</a>";
								}
							}
							
							if ($_SESSION['Numero'] != "" and ! SoNumeros($_SESSION['Numero'])){
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens']      = 1;
								$_SESSION['Tipo']      = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Numero.focus();\" class=\"titulo2\">Número Válido</a>";
							}
							
							if ($_SESSION['Localidade'] == "S") {
								if ($_SESSION['Bairro'] == "") {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'].=", ";
									}
									$_SESSION['Mens']      = 1;
									$_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Bairro.focus();\" class=\"titulo2\">Bairro</a>";
								}
							}

							if (!empty($_SESSION['CEP'])) {
								$sql = "SELECT	COUNT(*) AS QTD
										FROM	SFPC.TBPREFORNECEDOR
										WHERE 	CCEPPOCODI = ".$_SESSION['CEP'] ;
													
									if (empty($_SESSION['Numero'])) {
										$sql .= " AND APREFONUME IS NULL ";  
									} else {
										$sql .= " AND APREFONUME = '".$_SESSION['Numero']."' ";
									}
											
									if (empty($_SESSION['Complemento'])) {
										$sql .= " AND (EPREFOCOMP IS NULL OR EPREFOCOMP = '') ";
									} else {
										$sql .= " AND EPREFOCOMP = '".$_SESSION['Complemento']."' ";
									}
													
								$result = executarSQL($db, $sql);
								
								$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
 
								if ($row->qtd > 0) {
									if ($_SESSION['Mens'] == 1){
										$_SESSION['Mensagem'] .= ", ";
									}
									$_SESSION['Mens']      = 1;
									$_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CEP.focus();\" class=\"titulo2\">CEP/Número/Complemento já existe</a>";
								}
							}
											
							if ($_SESSION['RegistroJunta'] == "") {
								if ($_SESSION['TipoCnpjCpf'] == "CNPJ") {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'] .= ", ";
									}
									$_SESSION['Mens']      = 1;
									$_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RegistroJunta.focus();\" class=\"titulo2\">Registro na Junta Comercial ou Cartório</a>";
								}
							} else {
								if (!SoNumeros($_SESSION['RegistroJunta'])) {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'] .= ", ";
									}
									$_SESSION['Mens']      = 1;
									$_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RegistroJunta.focus();\" class=\"titulo2\">Registro na Junta Comercial ou Cartório Válido</a>";
								} else {
									if ($_SESSION['RegistroJunta'] > 9223372036854775807) {
										if ($_SESSION['Mens'] == 1) {
											$_SESSION['Mensagem'] .= ", ";
										}
										$_SESSION['Mens']      = 1;
										$_SESSION['Tipo']      = 2;
										$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RegistroJunta.focus();\" class=\"titulo2\">Registro na Junta Comercial maior que 0 e menor que 9.223.372.036.854.775.807</a>";
									}
								}
							}
							
							if ($_SESSION['DataRegistro'] == "") {
								if ($_SESSION['TipoCnpjCpf'] == "CNPJ") {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'] .= ", ";
									}
									$_SESSION['Mens']      = 1;
									$_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataRegistro.focus();\" class=\"titulo2\">Data de Registro na Junta Comercial ou Cartório</a>";
								}
							} else {
								$MensErro = ValidaData($_SESSION['DataRegistro']);
								
								if ($MensErro != "") {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'] .= ", ";
									}
									$_SESSION['Mens']      = 1;
									$_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataRegistro.focus();\" class=\"titulo2\">Data Válida</a>";
								} else {
									$Hoje = date("Ymd");
									$Data = substr($_SESSION['DataRegistro'],-4).substr($_SESSION['DataRegistro'],3,2).substr($_SESSION['DataRegistro'],0,2);
									
									if ($Data > $Hoje) {
										if ($_SESSION['Mens'] == 1) {
											$_SESSION['Mensagem'] .= ", ";
										}
										$_SESSION['Mens']      = 1;
										$_SESSION['Tipo']      = 2;
										$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataRegistro.focus();\" class=\"titulo2\">Data Inferior ou Igual a Data Atual</a>";	
									}
								}
							}
							
							if ($_SESSION['Email'] == "") {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens']      = 1;
								$_SESSION['Tipo']      = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Email.focus();\" class=\"titulo2\">E-mail 1</a>";
							} else {
								if (!strchr($_SESSION['Email'], "@")) {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'].=", ";
									}
									$_SESSION['Mens']      = 1;
									$_SESSION['Tipo']      = 2;
								    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Email.focus();\" class=\"titulo2\">E-mail válido no campo 'E-mail 1'</a>";
								}
							}
							
							if ($_SESSION['Email2'] != "") {
								if (!strchr($_SESSION['Email2'], "@")) {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'].=", ";
									}
									$_SESSION['Mens']      = 1;
									$_SESSION['Tipo']      = 2;
								    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Email2.focus();\" class=\"titulo2\">E-mail válido no campo 'E-mail 2'</a>";
								}
							}
							
							if ($_SESSION['CPFContato'] != "") {
								$Qtd = strlen($_SESSION['CPFContato']);
								
								if (($Qtd != 11) and ($Qtd != 0)) {
									if ($_SESSION['Mens'] == 1) {
										$_SESSION['Mensagem'].=", ";
									}
									$_SESSION['Mens']      = 1;
									$_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPFContato.focus();\" class=\"titulo2\">CPF do Contato com 11 números</a>";
								} else {
									$cpfcnpj = valida_CPF($_SESSION['CPFContato']);
									
									if ($cpfcnpj === false) {
										if ($_SESSION['Mens'] == 1) {
											$_SESSION['Mensagem'].=", ";
										}
										$_SESSION['Mens']      = 1;
										$_SESSION['Tipo']      = 2;
										$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CPFContato.focus();\" class=\"titulo2\">CPF do Contato Válido</a>";
									}
								}
							}
						}
					}
				}
			}
			$db->disconnect();
		}
	}
		
	if ($_SESSION['Botao'] == "AdicionarSocio") { // Sócio - início
		$_SESSION['MostrarNovoSocio'] = true; // no caso de erro devem ser mostrados de novo os dados

		if ($_SESSION['NoSocios'] == "") {
			$_SESSION['NoSocios'] = 0;
		}
	
		if ($_SESSION['SocioNovoNome'] == "") {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'].=", ";
			}
			$_SESSION['Mens']     = 1;
			$_SESSION['Tipo']     = 2;
			$_SESSION['Mensagem'].= "<a href='javascript:document.CadInscritoIncluir.SocioNovoNome.focus();' class='titulo2'>Nome do sócio</a>";
		} elseif ($_SESSION['SocioNovoCPF'] == "") {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'].=", ";
			}
			$_SESSION['Mens']     = 1;
			$_SESSION['Tipo']     = 2;
			$_SESSION['Mensagem'].= "<a href='javascript:document.CadInscritoIncluir.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ do sócio</a>";
		} elseif (strlen($_SESSION['SocioNovoCPF']) != 11 and strlen($_SESSION['SocioNovoCPF']) != 14) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'].=", ";
			}
			$_SESSION['Mens']     = 1;
			$_SESSION['Tipo']     = 2;
			$_SESSION['Mensagem'].= "<a href='javascript:document.CadInscritoIncluir.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ de sócio válido</a>";
		} elseif ($_SESSION['SociosCPF_CNPJ'] != 0 and in_array($_SESSION['SocioNovoCPF'], $_SESSION['SociosCPF_CNPJ'])) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'].=", ";
			}
			$_SESSION['Mens']     = 1;
			$_SESSION['Tipo']     = 2;
			$_SESSION['Mensagem'].= "<a href='javascript:document.CadInscritoIncluir.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ de sócio não pode ser repetido</a>";
		} elseif ((strlen($_SESSION['SocioNovoCPF']) == 11 and !valida_CPF($_SESSION['SocioNovoCPF'])) or (strlen($_SESSION['SocioNovoCPF']) == 14 and !valida_CNPJ($_SESSION['SocioNovoCPF']))) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'].=", ";
			}
			$_SESSION['Mens']     = 1;
			$_SESSION['Tipo']     = 2;
			$_SESSION['Mensagem'].= "<a href='javascript:document.CadInscritoIncluir.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ de sócio válido</a>";
		} else {
			$_SESSION['SociosCPF_CNPJ'][ $_SESSION['NoSocios'] ] = $_SESSION['SocioNovoCPF'];
			$_SESSION['SociosNome'][ $_SESSION['NoSocios'] ] = $_SESSION['SocioNovoNome'];
			$_SESSION['NoSocios'] ++;
			$_SESSION['MostrarNovoSocio'] = false;
		}
	} elseif ($_SESSION['Botao'] == "RemoverSocio") {
		$_SESSION['SociosNome'][ $_SESSION['SocioSelecionado'] ] = NULL;
		$_SESSION['SociosCPF_CNPJ'][ $_SESSION['SocioSelecionado'] ] = NULL;
	} // Sócio - fim

	if (($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Preencher") or ($_SESSION['Botao'] == "Limpar")) {
		$_SESSION['Botao'] = "";
		ExibeAbaHabilitacao();
	}
}

function ExibeAbaHabilitacao() { // Formulário A - Habilitação Jurídica
?>
<html>
	<?php	# Carrega o layout padrão #
			layout();
	?>
	<script language="JavaScript" src="../janela.js" type="text/javascript"></script>
	<script language="JavaScript" type="">
		<!--
		function Submete(Destino){
	 		document.CadInscritoIncluir.Destino.value = Destino;
	 		document.CadInscritoIncluir.submit();
		}
		function removerSocio(valor){
			document.CadInscritoIncluir.SocioSelecionado.value=valor;
			enviar('RemoverSocio');
		}
		function enviar(valor){
				if(valor == 'Limpar'){
					if ( confirm("Deseja limpar os dados preenchidos desta aba?") ){
						document.CadInscritoIncluir.Botao.value = valor;
						document.CadInscritoIncluir.submit();
					}
				}else{
					document.CadInscritoIncluir.Botao.value = valor;
					document.CadInscritoIncluir.submit();
				}


			}
		<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="CadInscritoIncluir.php" method="post" name="CadInscritoIncluir">
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
				   								INCLUIR - INSCRIÇÃO DE FORNECEDOR
				         					</td>
				       					</tr>
			        					<tr>
			   	      						<td class="textonormal">
												<p align="justify">
													Para fazer a Inscrição de um Fornecedor, informe os dados abaixo. Os itens obrigatórios estão com *.<br>
			       	    							Para preencher o endereço digite o CEP e clique no botão "Preencher Endereço".<br><br>
	        	    								Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
	        	    								Para este rotina funcionar bem, desabilite o bloqueador de janelas suspensas (Pop up) do seu navegador para este endereço.
			         	   						</p>
			         						</td>
			   	      					</tr>
				        				<tr>
											<td align="left">
												<?php echo NavegacaoAbas(on,off,off,off, off); ?>
												<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr bgcolor="#bfdaf2">
														<td colspan="4">
								          					<table class="textonormal" border="0" align="left" summary="">
																<tr>
																	<td class="textonormal">
																		<input type="radio" name="TipoCnpjCpf" value="CPF" <?php if ($_SESSION['TipoCnpjCpf'] == "" or $_SESSION['TipoCnpjCpf'] == "CPF") { echo "checked"; } ?> onclick="document.CadInscritoIncluir.Critica.value=1;javascript:submit();"> CPF<span style="color: red;">*</span>
																		<input type="radio" name="TipoCnpjCpf" value="CNPJ" <?php if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { echo "checked"; } ?> onclick="document.CadInscritoIncluir.Critica.value=1;javascript:submit();">CNPJ
					          	    								</td>
					          	    								<td class="textonormal">
					          	    									<input type="text" name="CPF_CNPJ" size="15" maxlength="14" value="<?php echo $_SESSION['CPF_CNPJ'];?>" class="textonormal">
					          	    								</td>
					            								</tr>
																<tr>
																	<td class="textonormal">Tipo de Habilitação<span style="color: red;">*</span></td>
																	<td class="textonormal">
																		<select name="TipoHabilitacao" class="textonormal" onchange="document.CadInscritoIncluir.submit()">
																			<option value="D" <?php if ($_SESSION['TipoHabilitacao'] == "D") { echo "selected"; } ?> >COMPRA DIRETA</option>
																			<option value="L" <?php if ($_SESSION['TipoHabilitacao'] != "D") { echo "selected"; } ?> >LICITAÇÃO</option>
																		</select>
																	</td>
																</tr>
																<?php if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { ?>
																<tr>
											                 			<td class="textonormal"><?php echo getDescPorteEmpresaTitulo() ?></td>
											                 			<td class="textonormal"><?php echo comboBoxDescPorteEmpresa($_SESSION['MicroEmpresa']) ?> </td> 
																</tr>
																<?php } ?>
								            					<tr>
								              						<td class="textonormal">
								              							<?php if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { echo "Identidade Repres.Legal(Empr.Individual)\n"; } else { echo "Identidade<span style=\"color: red;\">*</span>\n"; } ?>
								              						</td>
								              						<td class="textonormal">
								              							<input type="text" name="Identidade" size="17" maxlength="15" value="<?php echo $_SESSION['Identidade'];?>" class="textonormal">
																	</td>
								            					</tr>
								            					<tr>
								              						<td class="textonormal">
								              							<?php if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { echo "Órgão Emissor/UF\n"; } else { echo "Órgão Emissor/UF<span style=\"color: red;\">*</span>\n"; } ?>
								              						</td>
								              						<td class="textonormal">
								              							<input type="text" name="OrgaoUF" size="17" maxlength="15" value="<?php echo $_SESSION['OrgaoUF'];?>" class="textonormal">
																	</td>
								            					</tr>
								            					<tr>
																	<td class="textonormal" height="20">
								              							<?php if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { echo "Razão Social*\n"; } else { echo "Nome<span style=\"color: red;\">*</span>\n"; } ?>
								              						</td>
								              						<td class="textonormal">
								              							<input type="text" name="RazaoSocial" size="45" maxlength="120" value="<?php echo $_SESSION['RazaoSocial'];?>" class="textonormal">
								              							<input type="hidden" name="Critica" size="1" value="2">
					            	  									<input type="hidden" name="Origem" value="A">
																		<input type="hidden" name="Destino">
																	</td>
								            					</tr>
								            					<tr>
								              						<td class="textonormal">Nome Fantasia </td>
								              						<td class="textonormal">
								              							<input type="text" name="NomeFantasia" size="45" maxlength="80" value="<?php echo $_SESSION['NomeFantasia'] ?>" class="textonormal">
								              						</td>
								            					</tr>
								            					<tr>
								              						<td class="textonormal">CEP<span style="color: red;">*</span></td>
																	<td class="textonormal">
																		<input type="text" name="CEP" size="8" maxlength="8" value="<?php echo $_SESSION['CEPInformado'] ?>" class="textonormal">
								            							<input type="hidden" name="CEPAntes" size="8" maxlength="8" value="<?php echo $_SESSION['CEPAntes']?>" class="textonormal">
								            							<input type="button" value="Preencher Endereço" class="botao" onclick="javascript:enviar('Preencher')">
								            						</td>
								            					</tr>
																<tr>
								              						<td class="textonormal">Logradouro<span style="color: red;">*</span></td>
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
								              						<td class="textonormal">Bairro<span style="color: red;">*</span></td>
								              						<td class="textonormal">
								              							<input type="text" name="Bairro" size="33" maxlength="30" value="<?php echo $_SESSION['Bairro'] ?>" class="textonormal">
								              						</td>
								            					</tr>
																<tr>
								              						<td class="textonormal">Cidade<span style="color: red;">*</span></td>
								              						<td class="textonormal">
								              							<input type="text" name="Cidade" size="33" maxlength="30" value="<?php echo $_SESSION['Cidade'] ?>" class="textonormal">
								              						</td>
								            					</tr>
								            					<tr>
								              						<td class="textonormal">UF<span style="color: red;">*</span></td>
					    	      									<td class="textonormal">
					    	      										<input type="text" name="UF" size="2" maxlength="2" value="<?php echo $_SESSION['UF'] ?>" class="textonormal">
					    	      									</td>
								            					</tr>
								            					<tr>
								              						<td class="textonormal">DDD </td>
								              						<td class="textonormal">
								              							<input type="text" name="DDD" size="2" maxlength="3" value="<?php echo $_SESSION['DDD'];?>" class="textonormal">
								              						</td>
								            					</tr>
								            					<tr>
								              						<td class="textonormal">Telefone(s) </td>
																	<td class="textonormal">
																		<input type="text" name="Telefone" size="33" maxlength="30" value="<?php echo $_SESSION['Telefone'];?>" class="textonormal">
																	</td>
								            					</tr>
								            					<tr>
								              						<td class="textonormal">E-mail 1<span style="color: red;">*</span></td>
																	<td class="textonormal">
																		<input type="text" name="Email" size="45" maxlength="60" value="<?php echo $_SESSION['Email'];?>" class="textonormal">
																	</td>
								            					</tr>
								            					<tr>
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
																	<td class="textonormal">Registro Junta Comercial ou Cartório<?php if($_SESSION['TipoCnpjCpf'] == "CNPJ" ){echo "*"; } ?></td>
																	<td class="textonormal">
																		<input type="text" name="RegistroJunta" size="12" maxlength="11" value="<?php echo $_SESSION['RegistroJunta'];?>" class="textonormal">
																	</td>
								            					</tr>
								            					<tr>
								              						<td class="textonormal" width="45%">Data Reg. Junta Comercial ou Cartório<?php if($_SESSION['TipoCnpjCpf'] == "CNPJ"){echo "*"; } ?>
																</td>
																<td class="textonormal">
                                                                    <?php
                                                                    $DataMes = DataMes();

                                                                    if ($DataIni == "" || is_null($DataIni)) {
                                                                        //$DataIni = $DataMes[0];
                                                                        $DataIni = "";
                                                                    }

                                                                    if ($DataFim == "" || is_null($DataFim)) {
                                                                        //$DataFim = $DataMes[1];
                                                                        $DataFim = "";
                                                                    }

                                                                    $URLIni = "../calendario.php?Formulario=ConsPesquisarDFD&Campo=DataIni";
                                                                    $URLFim = "../calendario.php?Formulario=ConsPesquisarDFD&Campo=DataFim";
                                                                    ?>

                                                                    <input class="textonormal" type="date"
                                                                        name="dataIni" id="dataIni" size="10"
                                                                        maxlength="10" value="<?php echo $DataIni; ?>">
                                                                    
                                                                    &nbsp;a&nbsp;
                                                                    <input class="textonormal" type="date"
                                                                        name="dataFim" id="dataFim" size="10"
                                                                        maxlength="10" value="<?php echo $DataFim; ?>">
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
								              						<td class="textonormal" >Telefone do Contato</td>
																	<td class="textonormal">
																		<input type="text" name="TelefoneContato" size="27" maxlength="25" value="<?php echo $_SESSION['TelefoneContato'];?>" class="textonormal">
																	</td>
								            					</tr>
								          					</table>
														</td>
													</tr>
												</table>
												<?php if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { // Sócios - início ?>	
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
																	<?php	for ($itr=0; $itr<$_SESSION['NoSocios']; $itr++) {
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
																	<?php		}
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
																</table>
															</td>
														</tr>
														
													</table>		
												<?php } //Sócios - fim ?>
											</td>
										</tr>
										
										<tr>
											<td class="textonormal" align="right">
											
												<input type="button" value="Próxima Aba" class="botao" onclick="javascript:enviar('A');">
												<input type="button" value="Limpar Tela" class="botao" onclick="javascript:enviar('Limpar');">
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
		<script language="JavaScript" type="">
			<!--
				document.CadInscritoIncluir.CPF_CNPJ.focus()
			//-->
		</script>
	</body>
</html>
<?php	exit;
}

function CriticaAbaRegularidadeFiscal() { // Formulário B - Regularidade Fiscal 
	if ($_SESSION['Botao'] == "Limpar") {
		$_SESSION['Botao']         = "";
		$_SESSION['InscEstadual']  = "";
		$_SESSION['InscMercantil'] = "";
		$_SESSION['InscOMunic']    = "";
		
		if ($_SESSION['DataCertidaoOb'] != 0) {
			$_SESSION['DataCertidaoOb'] = array_fill(5, count($_SESSION['DataCertidaoOb']), '');
		}
		
		if ($_SESSION['DataCertidaoComp'] != 0) {
			$_SESSION['DataCertidaoComp'] = array_fill(5, count($_SESSION['DataCertidaoComp']), '');
		}
		ExibeAbaRegularidadeFiscal();
	} elseif ($_SESSION['Botao'] == "RetirarComplementar") {
		if (count($_SESSION['CertidaoComplementar']) != 0) {
			for ($i=0; $i< count($_SESSION['CertidaoComplementar']); $i++) {
				if ($_SESSION['CheckComplementar'][$i] == "") {
					$QtdComplementares++;
					$_SESSION['CheckComplementar'][$i] = "";
					$_SESSION['CertidaoComplementar'][$QtdComplementares-1] = $_SESSION['CertidaoComplementar'][$i];
					$_SESSION['DataCertidaoComp'][$QtdComplementares-1]   = $_SESSION['DataCertidaoComp'][$i];
				}
			}
			$_SESSION['CertidaoComplementar'] = array_slice($_SESSION['CertidaoComplementar'],0,$QtdComplementares);
			$_SESSION['DataCertidaoComp']       = array_slice($_SESSION['DataCertidaoComp'],0,$QtdComplementares);

			if (count($_SESSION['CertidaoComplementar']) == 1 and count($_SESSION['CertidaoComplementar']) == "") {
				unset($_SESSION['CertidaoComplementar']);
			}
			
			if (count($_SESSION['DataCertidaoComp']) == 1 and count($_SESSION['DataCertidaoComp']) == "") {
				unset($_SESSION['DataCertidaoComp']);
			}
			$_SESSION['Certidao'] = "";
		}
		ExibeAbas("B");
	} else {
		$_SESSION['Mens']     = "";
		$_SESSION['Mensagem'] = "Informe: ";

		# Verifica se as Inscrições estão vazias #
		if ($_SESSION['InscMercantil'] == "" and $_SESSION['InscOMunic'] == "" and $_SESSION['InscEstadual'] == "" and $_SESSION['TipoHabilitacao'] == "L") {
			if ( $_SESSION['Mens'] == 1 ) { $_SESSION['Mensagem'] .= ", "; }
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.InscMercantil.focus();\" class=\"titulo2\">Inscrição Municipal Recife ou Inscrição de Outro Munícipio ou Inscrição Estadual</a>";
		} else {
			# Verifica se as duas Inscrições estão preenchidas #
			if ($_SESSION['InscMercantil'] != "" and $_SESSION['InscOMunic'] != "" and $_SESSION['TipoHabilitacao'] == "L") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.InscMercantil.focus();\" class=\"titulo2\">Inscrição Municipal Recife ou Inscrição de Outro Munícipio</a>";
			} else {
				# Verifica se a Inscrição Municipal é Númerica #
				if (($_SESSION['InscOMunic'] != "") and (! SoNumeros($_SESSION['InscOMunic']))) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.InscOMunic.focus();\" class=\"titulo2\">Inscrição de Outro Município Válida</a>";
				}
				
				if ($_SESSION['InscMercantil'] != "") {
					# Verifica se a Inscrição Municipal Recife é Númerica #
					if (($_SESSION['InscMercantil'] != "") && (! SoNumeros($_SESSION['InscMercantil']))) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens']      = 1;
						$_SESSION['Tipo']      = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.InscMercantil.focus();\" class=\"titulo2\"> Inscrição Municipal Recife Válida</a>";
					} else {
						# Pesquisa se Inscrição Municipal Recife é Válida no Banco de Dados #
						if ($_SESSION['InscricaoValida'] == "") {
							$NomePrograma = urlencode("CadInscritoIncluir.php");
							$Url = "fornecedores/RotConsultaInscricaoMercantil.php?NomePrograma=$NomePrograma&InscricaoMercantil=".$_SESSION['InscMercantil']."&Destino=".$_SESSION['DestinoInsc']."";
							
							if (!in_array($Url,$_SESSION['GetUrl'])) {
								$_SESSION['GetUrl'][] = $Url;
							}
							//Redireciona($Url);
							//exit;
						} else {
							if ($_SESSION['InscricaoValida'] == "N") {
								if ($_SESSION['Mens'] == 1) {
									$_SESSION['Mensagem'] .= ", ";
								}
								$_SESSION['Mens']      = 1;
								$_SESSION['Tipo']      = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.InscMercantil.focus();\" class=\"titulo2\"> Inscrição Municipal Recife Válida</a>";
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
			$_SESSION['Mens']      = 1;
			$_SESSION['Tipo']      = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.InscEstadual.focus();\" class=\"titulo2\"> Inscrição Estadual Válida</a>";
		}

		# Criando o Array de Certidões Complementares #
		if ($_SESSION['Certidao'] != "") {
			if (!session_is_registered('CertidaoComplementar')) {
				$_SESSION['CertidaoComplementar'] = array();
			}
			
			if ($_SESSION['CertidaoComplementar'] == "" || !in_array($_SESSION['Certidao'], $_SESSION['CertidaoComplementar'])) {
				$_SESSION['CertidaoComplementar'][ count($_SESSION['CertidaoComplementar']) ] = $_SESSION['Certidao'];
			}
		}

		# Verifica se as Data de Certidão Obrigatória estão vazias #
		if ($_SESSION['DataCertidaoOb'] != 0) {
			for ($i=0;$i < count($_SESSION['DataCertidaoOb']);$i++) {
				if ($_SESSION['DataCertidaoOb'][$i] == "") {
					$cont++;
					
					if ($cont == 1) {
						$PosOb = $i;
					}
								
					if ($i < 7) $ExisteDataOb = "N"; // Não verificar se for data do último balanço não informada
					if (($i == 7) and (empty($_SESSION['MicroEmpresa']))) $ExisteDataOb = "N";																				
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
			$PosOb = ( $PosOb * 2 ) + 5;
			
			if (($ExisteDataOb == "N") and ($_SESSION['TipoHabilitacao'] == "L")) {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.elements[$PosOb].focus();\" class=\"titulo2\"> Data(s) de Validade da(s) Certidão(ões) Obrigatória(s)</a>";
			} elseif ($DataValidaOb == "N") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.elements[$PosOb].focus();\" class=\"titulo2\"> Data(s) de Validade da(s) Certidão(ões) Obrigatória(s) Válida</a>";
			}
		}
		# Verifica se as Data de Certdão Complementar estão vazias #
		if ($_SESSION['DataCertidaoComp'] != 0) {
			for ($i=0;$i< count($_SESSION['DataCertidaoComp']);$i++) {
				if ($_SESSION['DataCertidaoComp'][$i] == "") {
					$cont++;
					
					if ($cont == 1) {
						$PosComp = $i + 1;
					}
					$ExisteDataOp = "N";
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
			$PosOb   = count($_SESSION['DataCertidaoOb']);
			
			if ($ExisteDataOp == "N") {
				$PosComp = ($PosComp * 2) + $PosOb + 10;
				
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.elements[$PosComp].focus();\" class=\"titulo2\"> Data de Validade da Certidão Complementar</a>";
			} elseif ($DataValidaComp == "N") {
				$PosComp = ($PosComp * 2) + $PosOb + 12;
				
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.elements[$PosComp].focus();\" class=\"titulo2\"> Data(s) de Validade da(s) Certidão(ões) Complementar(es) Válida</a>";
			}
		}
	}
	
	if( ($_SESSION['Mens'] != "" ) or ($_SESSION['Botao'] == "Limpar" ) ){
		$_SESSION['Botao'] = "";
		ExibeAbaRegularidadeFiscal();
	}
}

# Exibe Aba Regularidade Fiscal - Formulário B #
function ExibeAbaRegularidadeFiscal() {
?>
<html>
	<?php	# Carrega o layout padrão #
			layout();
	?>
	<script language="JavaScript" src="../janela.js" type="text/javascript"></script>
	<script language="JavaScript" type="">
		<!--
		function Submete(Destino) {
	 		document.CadInscritoIncluir.Destino.value = Destino;
	 		document.CadInscritoIncluir.submit();
		}
		function enviar(valor){
				if(valor == 'Limpar'){
					if ( confirm("Deseja limpar os dados preenchidos desta aba?") ){
						document.CadInscritoIncluir.Botao.value = valor;
						document.CadInscritoIncluir.submit();
					}
				}else{
					document.CadInscritoIncluir.Botao.value = valor;
					document.CadInscritoIncluir.submit();
				}


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
		<form action="CadInscritoIncluir.php" method="post" name="CadInscritoIncluir">
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
	  					<?php if ($_SESSION['Mens'] != 0) { ExibeMens($_SESSION['Mensagem'], $_SESSION['Tipo'],1); } ?>
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
			    								INCLUIR - INSCRIÇÃO DE FORNECEDOR
			          						</td>
			        					</tr> 
		  	      						<tr>
		    	      						<td class="textonormal">
		      	    							<p align="justify">
		        	    							Para continuar a Inscrição, informe os dados abaixo, as datas de todas as certidões fiscais obrigatórias e informe as certidões Complementares, se existirem com suas respectivas datas. Os itens obrigatórios estão com *. Deverá ser informada a Inscrição Municipal Recife de Recife ou a Inscrição de Outro Município.<br>
		        	    							Parar retirar uma ou mais certidão fiscal complementar selecione a(s) certidão(ões) e clique no botão "Retirar Complementar".
		          	   							</p>
		          							</td>
			        					</tr>
			        					<tr>
											<td align="left">
												<?php echo NavegacaoAbas(off,on,off,off,off); ?>
												<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr bgcolor="#bfdaf2">
														<td colspan="4">
								          					<table class="textonormal" border="0" align="left" width="100%" summary="">
																<tr>
																	<td class="textonormal" height="20">
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
								              							<?php if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { echo "Razão Social\n"; } else { echo "Nome\n"; } ?>
								              						</td>
								              						<td class="textonormal">
								              							<?php echo $_SESSION['RazaoSocial']; ?>
					            	  									<input type="hidden" name="Origem" value="B">
																		<input type="hidden" name="Destino">
																	</td>
								            					</tr>
																<tr>
								              						<td class="textonormal">
																		Inscrição Municipal Recife
																		<?php if ($_SESSION['TipoHabilitacao']!="D") { echo "<span style=\"color: red;\">*</span>"; } ?>
																	</td>
								              						<td class="textonormal">
								              							<input type="text" name="InscMercantil" size="20" maxlength="7" value="<?php echo $_SESSION['InscMercantil'];?>" class="textonormal">
								              						</td>
								            					</tr>
								            					<tr>
								              						<td class="textonormal" width="45%">Inscrição Outro Município<?php if($_SESSION['TipoHabilitacao']!="D"){echo "<span style=\"color: red;\">*</span>";} ?></td>
																	<td class="textonormal">
																		<input type="text" name="InscOMunic" size="20" maxlength="19" value="<?php echo $_SESSION['InscOMunic'];?>" class="textonormal">
																	</td>
								            					</tr>
								            					<tr>
								              						<td class="textonormal">Inscrição Estadual</td>
								              						<td class="textonormal">
								              							<input type="text" name="InscEstadual" size="20" maxlength="14" value="<?php echo $_SESSION['InscEstadual'];?>" class="textonormal">
								              						</td>
								            					</tr>
																<?php if ($_SESSION['TipoHabilitacao'] == "L") { ?>
								            					<tr>
								              						<td class="textonormal" colspan="2">
																		<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
		          			          										<tr>
								              									<td bgcolor="#75ADE6" class="textoabasoff" colspan="2" align="center">CERTIDÃO FISCAL</td>
								              								</tr>
								              								<tr>
								              									<td bgcolor="#DDECF9" class="textoabason" colspan="2" align="center">OBRIGATÓRIAS</td>
								              								</tr>
								              								<tr>
																				<td class="textonormal" colspan="2">
								              										<table class="textonormal" border="1" align="left" width="100%" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="">
								              											<tr>
								              												<td bgcolor="#75ADE6" class="textoabasoff">NOME DA CERTIDÃO</td>
								              												<td bgcolor="#75ADE6" class="textoabasoff">DATA DE VALIDADE</td>
								              											</tr>
									              										<?php	$db = Conexao();
																								  
																								$sql = "SELECT	CTIPCECODI, ETIPCEDESC
																										FROM	SFPC.TBTIPOCERTIDAO
																										WHERE	FTIPCEOBRI = 'S'
																										ORDER BY CTIPCECODI ";
																								  
																								$res = $db->query($sql);
																			  
																								if (PEAR::isError($res)) {
																					  				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																								} else {
																									while ($Linha = $res->fetchRow()) {
												          	      										$ob++;
																											
																										$Descricao  = substr($Linha[1],0,75);
												          	      										$CertidaoOb = $Linha[0];
																											
																										echo "<tr>\n";
												              											echo "	<td class=\"textonormal\" width=\"*\">$Descricao</td>\n";
											              												echo "	<td class=\"textonormal\" width=\"22%\" align=\"center\">\n";
																										  
																										$ElementoOb = (2 * $ob) + 3;
											              												$URL = "../calendario.php?Formulario=CadInscritoIncluir&Campo=elements[$ElementoOb]";
																										  
																										echo "		<input class=\"textonormal\" type=\"date\" name=\"DataCertidaoOb[$i]\" size=\"10\" maxlength=\"10\" value=\"".$_SESSION['DataCertidaoOb'][$ob-1]."\">\n";
																										echo "		<input type=\"hidden\" name=\"CertidaoObrigatoria[$i]\" value=\"".$CertidaoOb."\">\n";
																										echo "	</td>\n";
											              												echo "</tr>\n";
													                								}
																								}
										  	              										$db->disconnect();
										  	              								?>
								              										</table>
								              									</td>
								              								</tr>
								              								<tr>
								              									<td bgcolor="#DDECF9" class="textoabason" colspan="2" align="center">COMPLEMENTARES</td>
								              								</tr>
								              								<tr>
								              									<td class="textonormal" width="50%">
								              										<?php if (count($_SESSION['CertidaoComplementar']) > 0) { ?>
								              										<table class="textonormal" border="1" align="left" width="100%" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="">
								              											<tr>
								              												<td bgcolor="#75ADE6" class="textoabasoff">&nbsp;</td>
								              												<td bgcolor="#75ADE6" class="textoabasoff">NOME DA CERTIDÃO</td>
								              												<td bgcolor="#75ADE6" class="textoabasoff">DATA DE VALIDADE</td>
								              											</tr>
											              								<?php	for ($i=0; $i< count($_SESSION['CertidaoComplementar']);$i++) {
													              								$db  = Conexao();
																								  
																								$sql = "SELECT	CTIPCECODI, ETIPCEDESC
																										FROM	SFPC.TBTIPOCERTIDAO
																										WHERE	CTIPCECODI = ".$_SESSION['CertidaoComplementar'][$i]."
																										ORDER BY CTIPCECODI ";
																					  
																								$res = $db->query($sql);
																					
																								if (PEAR::isError($res)) {
																							 			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																								} else {
																									while ($Linha = $res->fetchRow()) {
														          	      								$op++;
																											
																										$CertidaoOpCodigo = $Linha[0];
														          	      								$Descricao        = substr($Linha[1],0,75);
																						  
																										echo "<tr>\n";
														              									echo "	<td class=\"textonormal\" width=\"5%\">\n";
														              									echo "		<input type=\"checkbox\" name=\"CheckComplementar[$i]\" value=\"$CertidaoOpCodigo\">\n";
														              									echo "	</td>\n";
														              									echo "	<td class=\"textonormal\" width=\"*\">$Descricao</td>\n";
														              									echo "	<td class=\"textonormal\" width=\"22%\" align=\"center\">\n";
																										  
																										$ElementoOp = $ElementoOb + 1 + ( 2 * $op );
														              									$URL = "../calendario.php?Formulario=CadInscritoIncluir&Campo=elements[$ElementoOp]";
																										
																										echo "  	<input class=\"textonormal\" type=\"date\" name=\"DataCertidaoComp[$i]\" size=\"10\" maxlength=\"10\" value=\"".$_SESSION['DataCertidaoComp'][$op-1]."\">&nbsp;\n";
																										echo "		<a href=\"javascript:janela('$URL','Calendario',220,170,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>\n";
														              									echo "	</td>\n";
														              									echo "</tr>\n";
															                						}
																								}
												  	              								$db->disconnect();
											              									}
											              								?>
											              							</table>
								              										<?php } ?>
								              									</td>
								              								</tr>
								              								<tr>
								              									<td class="textonormal" colspan="2" align="center">
								              										<?php	$Url = "CadIncluirCertidaoComplementar.php?ProgramaOrigem=CadInscritoIncluir";
																						if (!in_array($Url,$_SESSION['GetUrl'])) { $_SESSION['GetUrl'][] = $Url; }
																					?>
								              										<input class="botao" type="button" value="Incluir Complementar" onclick="javascript:AbreJanela('<?=$Url;?>',750,170);">
								              										<input class="botao" type="button" value="Retirar Complementar" onclick="javascript:enviar('RetirarComplementar');">
								              										<input type="hidden" name="Certidao" value="<?php echo $_SESSION['Certidao'];?>">
								              									</td>
								              								</tr>
								              							</table>
								              						</td>
								            					</tr>
																<?php } ?>
								          					</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
					      				<tr>
					        				<td colspan="4" align="right">
			            						<input type="button" value="Próxima Aba" class="botao" onclick="javascript:enviar('B');">
			            						<input type="button" value="Limpar Tela" class="botao" onclick="javascript:enviar('Limpar');">
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
	 		document.CadInscritoIncluir.InscMercantil.focus();
		//-->
		</script>
	</body>
</html>
<?php exit;
}

function CriticaAbaQualificEconFinanceira() { // Formulário C - Qualificação Econômica e Financeira
	if ($_SESSION['Botao'] == "Limpar") {

		$_SESSION['CapSocial']            = "";
		$_SESSION['CapIntegralizado']     = "";
		$_SESSION['Patrimonio']           = "";
		$_SESSION['IndLiqCorrente']       = "";
		$_SESSION['IndLiqGeral']          = "";
		$_SESSION['IndEndividamento']     = "";
		$_SESSION['IndSolvencia']         = "";
		$_SESSION['DataBalanco']          = "";
		$_SESSION['DataNegativa']         = "";
		$_SESSION['DataContratoEstatuto'] = "";
		$_SESSION['Banco1']               = "";
		$_SESSION['Banco2']               = "";
		$_SESSION['Agencia1']             = "";
		$_SESSION['Agencia2']             = "";
		$_SESSION['ContaCorrente1']       = "";
		$_SESSION['ContaCorrente2']       = "";

	} else {
		$_SESSION['Mens']     = "";
		$_SESSION['Mensagem'] = "Informe: ";
		
		if (($_SESSION['TipoCnpjCpf'] == "CNPJ") && ($_SESSION['TipoHabilitacao'] == 'L')) { // Compra direta não mostra os campos de qualificação econômica financeira (excessão de banco)
			if ($_SESSION['CapSocial'] == "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CapSocial.focus();\" class=\"titulo2\">Capital Social</a>";
			} else {
				if ($_SESSION['CapSocial'] == 0) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CapSocial.focus();\" class=\"titulo2\">A Capital Social deve ser númerico e diferente de zero</a>";
				}
				$_SESSION['CapSocial'] = str_replace(".","",$_SESSION['CapSocial']);
				
				if (!SoNumVirg($_SESSION['CapSocial'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CapSocial.focus();\" class=\"titulo2\">Capital Social Válido</a>";
				} else {
					$Numero = Decimal($_SESSION['CapSocial']);
					
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens']      = 1;
						$_SESSION['Tipo']      = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CapSocial.focus();\" class=\"titulo2\">Capital Social Válido</a>";
					} else {
						$_SESSION['CapSocial'] = $Numero;
					}
				}
			}
			
			if ($_SESSION['CapIntegralizado'] != "") {
				$_SESSION['CapIntegralizado'] = str_replace(".","",$_SESSION['CapIntegralizado']);
				
				if (!SoNumVirg($_SESSION['CapIntegralizado'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CapIntegralizado.focus();\" class=\"titulo2\">Capital Integralizado Válido</a>";
				} else {
					$Numero = Decimal($_SESSION['CapIntegralizado']);
					
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens']      = 1;
						$_SESSION['Tipo']      = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.CapIntegralizado.focus();\" class=\"titulo2\">Capital Integralizado Válido</a>";
					} else {
					 	$_SESSION['CapIntegralizado'] = $Numero;
					}
				}
			}
			
			if ($_SESSION['Patrimonio'] == "") {
				if (empty($_SESSION['MicroEmpresa'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Patrimonio.focus();\" class=\"titulo2\">Patrimônio Líquido</a>";
				}					
			} else {
				if ($_SESSION['Patrimonio'] == 0) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Patrimonio.focus();\" class=\"titulo2\">O Patrimônio Líquido deve ser númerico e diferente de zero</a>";
				}
				$_SESSION['Patrimonio'] = str_replace(".","",$_SESSION['Patrimonio']);
				
				if (!SoNumVirg($_SESSION['Patrimonio'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Patrimonio.focus();\" class=\"titulo2\">Patrimônio Líquido Válido</a>";
				} else {
					$Numero = Decimal($_SESSION['Patrimonio']);
					
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens']      = 1;
						$_SESSION['Tipo']      = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Patrimonio.focus();\" class=\"titulo2\">Patrimônio Líquido Válido</a>";
					} else {
						$_SESSION['Patrimonio'] = $Numero;
					}
				}
			}
			
			if ($_SESSION['IndLiqCorrente'] != "") {
				$_SESSION['IndLiqCorrente'] = str_replace(".","",$_SESSION['IndLiqCorrente']);
				
				if (!SoNumVirg($_SESSION['IndLiqCorrente'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.IndLiqCorrente.focus();\" class=\"titulo2\">Índice de Liquidez Corrente Válido</a>";
				} else {
					$Numero = Decimal($_SESSION['IndLiqCorrente']);
					
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens']      = 1;
						$_SESSION['Tipo']      = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.IndLiqCorrente.focus();\" class=\"titulo2\">Índice de Liquidez Corrente Válido</a>";
					} else {
						$_SESSION['IndLiqCorrente'] = $Numero;
					}
				}
			}
			
			if ($_SESSION['IndLiqGeral'] != "") {
				$_SESSION['IndLiqGeral'] = str_replace(".","",$_SESSION['IndLiqGeral']);
				
				if (!SoNumVirg($_SESSION['IndLiqGeral'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.IndLiqGeral.focus();\" class=\"titulo2\">Índice de Liquidez Geral Válido</a>";
				} else {
					$Numero = Decimal($_SESSION['IndLiqGeral']);
					
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens']      = 1;
						$_SESSION['Tipo']      = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.IndLiqGeral.focus();\" class=\"titulo2\">Índice de Liquidez Geral Válido</a>";
					} else {
						$_SESSION['IndLiqGeral'] = $Numero;
					}
				}
			}
			
			if ($_SESSION['IndEndividamento'] != "") {
				$_SESSION['IndEndividamento'] = str_replace(".","",$_SESSION['IndEndividamento']);
				
				if (!SoNumVirg($_SESSION['IndEndividamento'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.IndEndividamento.focus();\" class=\"titulo2\">Índice de Endividamento Válido</a>";
				} else {
					$Numero = Decimal($_SESSION['IndEndividamento']);
					
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens']      = 1;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.IndEndividamento.focus();\" class=\"titulo2\">Índice de Endividamento Válido</a>";
					} else {
						$_SESSION['IndEndividamento'] = $Numero;
					}
				}
			}
			
			if ($_SESSION['IndSolvencia'] != "") {
				$_SESSION['IndSolvencia'] = str_replace(".","",$_SESSION['IndSolvencia']);
				
				if (!SoNumVirg($_SESSION['IndSolvencia'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.IndSolvencia.focus();\" class=\"titulo2\">Índice de Solvência Geral Válido</a>";
				} else {
					$Numero = Decimal($_SESSION['IndSolvencia']);
					
					if (!$Numero) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens']      = 1;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.IndSolvencia.focus();\" class=\"titulo2\">Índice de Solvência Geral Válido</a>";
					} else {
						$_SESSION['IndSolvencia'] = $Numero;
					}
				}
			}
			
			if ($_SESSION['DataBalanco'] == "") {
				if (empty($_SESSION['MicroEmpresa'])) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataBalanco.focus();\" class=\"titulo2\">Data de validade do balanço</a>";
				} 
			} else {
				$MensErro = ValidaData($_SESSION['DataBalanco']);
				
				if ($MensErro != "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataBalanco.focus();\" class=\"titulo2\">Data de validade do balanço Válida</a>";
				} else {
					$DataBalancoInv = substr($_SESSION['DataBalanco'],6,4)."-".substr($_SESSION['DataBalanco'],3,2)."-".substr($_SESSION['DataBalanco'],0,2);
					
					if ($DataBalancoInv <= date("Y-m-d")) {
						if ($_SESSION['Mens'] == 1) {
							$_SESSION['Mensagem'] .= ", ";
						}
						$_SESSION['Mens']      = 1;
						$_SESSION['Tipo']      = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataBalanco.focus();\" class=\"titulo2\">Data de validade do balanço menor que data atual</a>";
					}
				}
			}
			
			if ($_SESSION['DataNegativa'] == "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataNegativa.focus();\" class=\"titulo2\">Data de Certidão Negativa de Falência ou Concordata</a>";
			} else {
				$MensErro = ValidaData($_SESSION['DataNegativa']);
				
				if ($MensErro != "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataNegativa.focus();\" class=\"titulo2\">Data de Certidão Negativa de Falência ou Concordata Válida</a>";
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
		
		if (($_SESSION['ContaCorrente1'] == $_SESSION['ContaCorrente2']) and ($_SESSION['ContaCorrente1'] != "" and $_SESSION['ContaCorrente2'] != "") ){
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens']      = 1;
			$_SESSION['Tipo']      = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.ContaCorrente1.focus();\" class=\"titulo2\">Contas Correntes Diferentes</a>";
		}
		
		if ($_SESSION['Banco1'] != "") {
			if (strlen($_SESSION['Banco1'])!=3) {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Banco1.focus();\" class=\"titulo2\">Código do banco 1 deve possuir 3 dígitos</a>";
			} else {
				if ($_SESSION['Agencia1'] == "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Agencia1.focus();\" class=\"titulo2\">Agência do Banco ".$_SESSION['Banco1']."</a>";
				} elseif (strlen($_SESSION['Agencia1']) != 5) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Agencia1.focus();\" class=\"titulo2\">Agência do Banco ".$_SESSION['Banco1']." deve possuir 5 dígitos</a>";
				}
				
				if ($_SESSION['ContaCorrente1'] == "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.ContaCorrente1.focus();\" class=\"titulo2\">Conta Corrente do Banco ".$_SESSION['Banco1']."</a>";
				}
			}
		} else {
			if ($_SESSION['Agencia1'] != "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Banco1.focus();\" class=\"titulo2\">Banco da Agência ".$_SESSION['Agencia1']."</a>";
			} elseif ($_SESSION['ContaCorrente1'] != "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Banco1.focus();\" class=\"titulo2\">Banco da Conta Corrente ".$_SESSION['ContaCorrente1']."</a>";
			} else {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.Banco1.focus();\" class=\"titulo2\">1ª conta de banco é requerida</a>";
			}
		}
		
		if ($_SESSION['Banco2'] != "") {
			if (strlen($_SESSION['Banco2']) != 3) {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Banco2.focus();\" class=\"titulo2\">Código do banco 2 deve possuir 3 dígitos</a>";
			} else {
				if ($_SESSION['Agencia2'] == "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 1;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Agencia2.focus();\" class=\"titulo2\">Agência do Banco ".$_SESSION['Banco2']."</a>";
				} elseif (strlen($_SESSION['Agencia2']) != 5) {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Agencia2.focus();\" class=\"titulo2\">Agência do Banco ".$_SESSION['Banco2']." deve possuir 5 dígitos</a>";
				}
				
				if ($_SESSION['ContaCorrente2'] == "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 1;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.ContaCorrente2.focus();\" class=\"titulo2\">Conta Corrente do Banco ".$_SESSION['Banco2']."</a>";
				}
			}
		} else {
			if ($_SESSION['Agencia2'] != "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 1;
				$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Banco2.focus();\" class=\"titulo2\">Banco da Agência ".$_SESSION['Agencia2']."</a>";
			} else {
				if ($_SESSION['ContaCorrente2'] != "") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 1;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.ContaCorrente2.focus();\" class=\"titulo2\">Banco da Conta Corrente ".$_SESSION['ContaCorrente2']."</a>";
				}
			}
		}
	}

	if (($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Limpar")) {
		$_SESSION['Botao'] = "";
		ExibeAbaQualificEconFinanceira();
	}
} //lucas

function ExibeAbaQualificEconFinanceira() { // Formulário C - Qualificação Econômica e Financeira
?>
<html>
	<?php	# Carrega o layout padrão #
			layout();
	?>
	<script language="javascript" src="../janela.js" type="text/javascript"></script>
	<script language="javascript" type="">
		<!--
		function Submete(Destino) {
		 	document.CadInscritoIncluir.Destino.value = Destino;
		 	document.CadInscritoIncluir.submit();
		}
		function enviar(valor){
				if(valor == 'Limpar'){
					if ( confirm("Deseja limpar os dados preenchidos desta aba?") ){
						document.CadInscritoIncluir.Botao.value = valor;
						document.CadInscritoIncluir.submit();
					}
				}else{
					document.CadInscritoIncluir.Botao.value = valor;
					document.CadInscritoIncluir.submit();
				}


			}
		<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="CadInscritoIncluir.php" method="post" name="CadInscritoIncluir">
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
	  					<?php if ($_SESSION['Mens'] != 0) { ExibeMens($_SESSION['Mensagem'], $_SESSION['Tipo'],1); } ?>
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
			    								INCLUIR - INSCRIÇÃO DE FORNECEDOR
			          						</td>
			        					</tr>
		  	      						<tr>
		    	      						<td class="textonormal">
		      	    							<p align="justify">
		        	    							Informe os itens obrigatórios que estão marcados com *. O Índice de Liquidez Corrente e Liquidez Geral não pode ser menor que 1.
		          	   							</p>
		          							</td>
			        					</tr>
			        					<tr>
											<td align="left">
												<?php echo NavegacaoAbas(off,off,on,off,off); ?>
												<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr bgcolor="#bfdaf2">
														<td colspan="4">
								          					<table class="textonormal" border="0" align="left" width="100%" summary="">
																<tr>
																	<td class="textonormal" height="20">
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
								              						<td class="textonormal" height="20">Razão Social</td>
								              						<td class="textonormal">
								              							<?php echo $_SESSION['RazaoSocial']; ?>
								              							<input type="hidden" name="Origem" value="C">
																		<input type="hidden" name="Destino">
								              						</td>
								            					</tr>
								            					
																<?php if (($_SESSION['TipoCnpjCpf'] == "CNPJ") && ($_SESSION['TipoHabilitacao']=='L')) { ?>
																	<tr>
								              							<td class="textonormal">Capital Social Subscrito<span style="color: red;">*</span></td>
								              							<td class="textonormal">
								              								<input type="text" name="CapSocial" size="20" maxlength="19" value="<?php echo $_SESSION['CapSocial'];?>" class="textonormal">
								              							</td>
								            						</tr>
																	<tr>
								              							<td class="textonormal">Capital Integralizado</td>
								              							<td class="textonormal">
								              								<input type="text" name="CapIntegralizado" size="20" maxlength="19" value="<?php echo $_SESSION['CapIntegralizado'];?>" class="textonormal">
								              							</td>
								            						</tr>
								            						<tr>
								              							<?php if (empty($_SESSION['MicroEmpresa'])) { ?>
								             		 					<td class="textonormal">Patrimônio Líquido<span style="color: red;">*</span></td>
								              							<?php } else { ?>
								              		 					<td class="textonormal">Patrimônio Líquido</td>
								              							<?php } ?>
								            							<td class="textonormal">
								              								<input type="text" name="Patrimonio" size="20" maxlength="19" value="<?php echo $_SESSION['Patrimonio'];?>" class="textonormal">
								              							</td>
								            						</tr>
																	<tr>
								              							<td class="textonormal" width="45%">Índice de Liquidez Corrente</td>
								              							<td class="textonormal">
								              								<input type="text" name="IndLiqCorrente" size="20" maxlength="19" value="<?php echo $_SESSION['IndLiqCorrente'];?>" class="textonormal">
								              							</td>
								            						</tr>
								            						<tr>
								              							<td class="textonormal">Índice de Liquidez Geral</td>
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
								               							<?php if (empty($_SESSION['MicroEmpresa'])) { ?>
										              					<td class="textonormal">Data de validade do balanço<span style="color: red;">*</span></td>
   								              							<?php } else { ?>
										              					<td class="textonormal">Data de validade do balanço</td>
										       							<?php } ?>										             
								            							<td class="textonormal">
					              											<?php $URL = "../calendario.php?Formulario=CadInscritoIncluir&Campo=DataBalanco" ?>
										          							<input type="text" name="DataBalanco" size="10" maxlength="10" value="<?php echo $_SESSION['DataBalanco'];?>" class="textonormal">
																			<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a> dd/mm/aaaa
																		</td>
								            						</tr>
								            						<tr>
								              							<td class="textonormal">Data de Certidão Negativa de Falência ou Concordata<span style="color: red;">*</span></td>
																		<td class="textonormal">
						              										<?php $URL = "../calendario.php?Formulario=CadInscritoIncluir&Campo=DataNegativa" ?>
										          							<input type="text" name="DataNegativa" size="10" maxlength="10" value="<?php echo $_SESSION['DataNegativa'];?>" class="textonormal">
																			<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a> dd/mm/aaaa
																		</td>
								            						</tr>
								            						<tr>
								              							<td class="textonormal">Data de última alteração de contrato ou estatuto</td>
																		<td class="textonormal">
					              											<?php $URL = "../calendario.php?Formulario=CadInscritoIncluir&Campo=DataContratoEstatuto" ?>
										          							<input type="text" name="DataContratoEstatuto" size="10" maxlength="10" value="<?php echo $_SESSION['DataContratoEstatuto'];?>" class="textonormal">
																			<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a> dd/mm/aaaa
																		</td>
								            						</tr>
																<?php } ?>
															
																<tr>
								            						<td colspan="2">
									            						<table align="center" border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
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
					        				<td colspan="4" align="right">
												<input type="button" value="Próxima Aba" class="botao" onclick="javascript:enviar('C');">
												<input type="button" value="Limpar Tela" class="botao" onclick="javascript:enviar('Limpar');">
												<input type="hidden" name="Origem" value="C">
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
 				<?php if( $_SESSION['TipoCnpjCpf'] == "CNPJ" ){ ?>
 				document.CadInscritoIncluir.CapSocial.focus();
 				<?php } ?>
			//-->
		</script>
	</body>
</html>
<?php exit;
}

function CriticaAbaQualificTecnica() { // Formulário D - Qualificação técnica
	if ($_SESSION['Botao'] == "Limpar") {
		//$_SESSION['Botao']            = "";
		$_SESSION['RegistroEntidade'] = "";
		$_SESSION['NomeEntidade']     = "";
		$_SESSION['DataVigencia']     = "";
		$_SESSION['RegistroTecnico']  = "";
	} elseif ($_SESSION['Botao'] == "RetirarAutorizacao") {
		if (count($_SESSION['AutorizacaoNome']) != 0) {
			for ($i=0; $i< count($_SESSION['AutorizacaoNome']); $i++) {
				if ($_SESSION['CheckAutorizacao'][$i] == "") {
					$QtdAutorizacao++;
					$_SESSION['CheckAutorizacao'][$i] = "";
					$_SESSION['AutorizacaoNome'][$QtdAutorizacao-1]     = $_SESSION['AutorizacaoNome'][$i];
					$_SESSION['AutorizacaoRegistro'][$QtdAutorizacao-1] = $_SESSION['AutorizacaoRegistro'][$i];
					$_SESSION['AutorizacaoData'][$QtdAutorizacao-1]     = $_SESSION['AutorizacaoData'][$i];
					$_SESSION['AutoEspecifica'][$QtdAutorizacao-1]      = $_SESSION['AutoEspecifica'][$i];
				}
			}
			$_SESSION['AutorizacaoNome']     = array_slice($_SESSION['AutorizacaoNome'],0,$QtdAutorizacao);
			$_SESSION['AutorizacaoRegistro'] = array_slice($_SESSION['AutorizacaoRegistro'],0,$QtdAutorizacao);
			$_SESSION['AutorizacaoData']     = array_slice($_SESSION['AutorizacaoData'],0,$QtdAutorizacao);
			$_SESSION['AutoEspecifica']      = array_slice($_SESSION['AutoEspecifica'],0,$QtdAutorizacao);
			
			if ($_SESSION['AutorizacaoNome'][0] == "" or count($_SESSION['AutorizacaoNome']) == 0) {
				unset($_SESSION['AutorizacaoNome']);
			}
			
			if ($_SESSION['AutorizacaoRegistro'][0] == "" or count($_SESSION['AutorizacaoRegistro']) == 0) {
				unset($_SESSION['AutorizacaoRegistro']);
			}
			
			if ($_SESSION['AutorizacaoData'][0] == "" or count($_SESSION['AutorizacaoData']) == 0) {
				unset($_SESSION['AutorizacaoData']);
			}
			
			if ($_SESSION['AutoEspecifica'][0] == "" or count($_SESSION['AutoEspecifica']) == 0) {
				unset($_SESSION['AutoEspecifica']);
			}
			$_SESSION['AutorizaNome']     = "";
			$_SESSION['AutorizaRegistro'] = "";
			$_SESSION['AutorizaData']     = "";
			$_SESSION['AutoEspecifica']   = "";
		}
		ExibeAbas("D");
	} elseif ($_SESSION['Botao'] == "RetirarGrupos") {
		$QtdMateriais=0;
		
		if (count($_SESSION['Materiais']) != 0) {
			for ($i=0; $i< count($_SESSION['Materiais']); $i++) {
				if ($_SESSION['CheckMateriais'][$i] == "") {
					$QtdMateriais++;
					$_SESSION['CheckMateriais'][$i] = "";
					$_SESSION['Materiais'][$QtdMateriais-1] = $_SESSION['Materiais'][$i];
				}
			}
		  	$_SESSION['Materiais'] = array_slice($_SESSION['Materiais'],0,$QtdMateriais);

			if (count($_SESSION['Materiais']) == 1 and count($_SESSION['Materiais']) == "") {
				unset($_SESSION['Materiais']);
			}
		}
		$QtdServicos=0;
		
		if (count($_SESSION['Servicos']) != 0) {
			for ($i=0; $i< count($_SESSION['Servicos']); $i++) {
				if ($_SESSION['CheckServicos'][$i] == "") {
					$QtdServicos++;
					$_SESSION['CheckServicos'][$i] = "";
					$_SESSION['Servicos'][$QtdServicos-1] = $_SESSION['Servicos'][$i];
				}
			}
			$_SESSION['Servicos'] = array_slice($_SESSION['Servicos'],0,$QtdServicos);

			if (count($_SESSION['Servicos']) == 1 and count($_SESSION['Servicos']) == "") {
				unset($_SESSION['Servicos']);
			}
		}
		ExibeAbas("D");
	} else {
		$_SESSION['Mens']     = "";
		$_SESSION['Mensagem'] = "Informe: ";
		
		if ($_SESSION['RegistroEntidade'] != "" and !SoNumeros($_SESSION['RegistroEntidade'])) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens']      = 1;
			$_SESSION['Tipo']      = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RegistroEntidade.focus();\" class=\"titulo2\">Registro da Entidade Válido</a>";
		}
		$MensErro = ValidaData($_SESSION['DataVigencia']);
		
		if ($_SESSION['DataVigencia'] != "" and $MensErro != "") {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens']      = 1;
			$_SESSION['Tipo']      = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.DataVigencia.focus();\" class=\"titulo2\">$MensErro</a>";
		}
		
		if (($_SESSION['RegistroTecnico'] != "") and (!SoNumeros($_SESSION['RegistroTecnico'])) ){
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens']      = 1;
			$_SESSION['Tipo']      = 2;
			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadInscritoIncluir.RegistroTecnico.focus();\" class=\"titulo2\">Registro ou Inscrição do Técinco Válida</a>";
		}

		# Constuindo o array de Autorização Específica #
		if ($_SESSION['AutorizaNome'] != "" and $_SESSION['AutorizaRegistro'] != "" and $_SESSION['AutorizaData'] != "") {
			if (!session_is_registered('AutorizacaoNome')) {
				$_SESSION['AutorizacaoNome'] = array();
			}
			
			if (!session_is_registered('AutorizacaoRegistro')) {
				$_SESSION['AutorizacaoRegistro'] = array();
			}
			
			if (!session_is_registered('AutorizacaoData')) {
				$_SESSION['AutorizacaoData'] = array();
			}
			
			if (!session_is_registered('AutoEspecifica')) {
				$_SESSION['AutoEspecifica'] = array();
			}
			$AutorizacaoEspecifica = $_SESSION['AutorizaNome']."#".$_SESSION['AutorizaRegistro'];
			
			if ($_SESSION['AutoEspecifica'] == "" || !in_array($AutorizacaoEspecifica, $_SESSION['AutoEspecifica'])) {
				$_SESSION['AutorizacaoNome'][ count($_SESSION['AutorizacaoNome']) ] = $_SESSION['AutorizaNome'];
				$_SESSION['AutorizacaoRegistro'][ count($_SESSION['AutorizacaoRegistro']) ] = $_SESSION['AutorizaRegistro'];
				$_SESSION['AutorizacaoData'][ count($_SESSION['AutorizacaoData']) ] = $_SESSION['AutorizaData'];
				$_SESSION['AutoEspecifica'][ count($_SESSION['AutoEspecifica']) ] = $AutorizacaoEspecifica;
			}
		}
		
		if (count($_SESSION['Materiais']) == 0 and count($_SESSION['Servicos']) == 0) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens']      = 1;
			$_SESSION['Tipo']      = 2;
			$_SESSION['Mensagem'] .= "Pelo menos um Grupo de Fornecimento deve ser Incluído";
		}
		
		if ($_SESSION['TipoHabilitacao'] == 'L') {
			if ($_SESSION['Cumprimento'] == "") {
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] .= "Resposta para o Cumprimento da Lei";
			} else {
				if ($_SESSION['Cumprimento'] == "N") {
					if ($_SESSION['Mens'] == 1) {
						$_SESSION['Mensagem'] .= ", ";
					}
					$_SESSION['Mens']        = 1;
					$_SESSION['Tipo']        = 2;
					$_SESSION['Mensagem']    = "A Inscrição só será efetivada se o fornecedor cumprir o que está disposto no Inc. XXXIII do Art. 7º da Constituição Federal";
					$_SESSION['Cumprimento'] = "S";
				}
			}
		}
	}
	
	if (($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Limpar") or ($_SESSION['Email'] == "" and $_SESSION['Botao'] == "Incluir")) {
		$_SESSION['Botao'] = "";
		ExibeAbaQualificTecnica();
	}
}

function ExibeAbaQualificTecnica() { // Formulário D - Qualificação técnica
?>
<html>
	<?php	# Carrega o layout padrão #
			layout();
	?>
	<script language="javascript" src="../janela.js" type="text/javascript"></script>
	<script language="javascript" type="">
		<!--
			function Submete(Destino) {
	 			document.CadInscritoIncluir.Destino.value = Destino;
	 			document.CadInscritoIncluir.submit();
			}
			function enviar(valor){
				if(valor == 'Limpar'){
					if ( confirm("Deseja limpar os dados preenchidos desta aba?") ){
						document.CadInscritoIncluir.Botao.value = valor;
						document.CadInscritoIncluir.submit();
					}
				}else{
					document.CadInscritoIncluir.Botao.value = valor;
					document.CadInscritoIncluir.submit();
				}


			}
			function AbreJanela(url,largura,altura) {
				window.open(url,'pagina','status=no,scrollbars=no,left=20,top=130,width='+largura+',height='+altura);
			}
			<?php MenuAcesso(); ?>
		//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
		<script language="JavaScript" src="../menu.js"></script>
		<script language="JavaScript">Init();</script>
		<form action="CadInscritoIncluir.php" method="post" name="CadInscritoIncluir">
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
	  					<?php if ($_SESSION['Mens'] != 0) { ExibeMens($_SESSION['Mensagem'], $_SESSION['Tipo'], 1); } ?>
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
			    								INCLUIR - INSCRIÇÃO DE FORNECEDOR
			          						</td>
			        					</tr>
		  	      						<tr>
		    	      						<td class="textonormal">
		      	    							<p align="justify">
		        	    							Informe os dados abaixo e clique no botão "Incluir".<br>
		        	    							Para informar a(s) autorização(ões) específica clique no botão "Incluir Autorização". Se desejar eliminar uma autorização específica já informada, marque a(s) autorização(ões) desejada(s) e clique no botão "Retirar Autorizações Marcadas".<br>
		        	    							Para informar os grupos de fornecimento clique no botão "Incluir Grupos". Se desejar eliminar um grupo de fornecimento já informado, marque o(s) grupo(s) desejado(s) e clique no botão "Retirar Grupos".
	        	    								Para este rotina funcionar bem, desabilite o bloqueador de janelas suspensas (Pop up) do seu navegador para este endereço.
		          	   							</p>
		          							</td>
			        					</tr>
			        					<tr>
											<td align="left">
												<?php echo NavegacaoAbas(off,off,off,on,off); ?>
												<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr bgcolor="#bfdaf2">
														<td colspan="4">
								          					<table class="textonormal" border="0" align="left" width="100%" summary="">
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
																	<td class="textonormal" height="20" width="45%">
								              							<?php if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { echo "Razão Social\n"; } else { echo "Nome\n"; } ?>
								              						</td>
								              						<td class="textonormal">
								              							<?php echo $_SESSION['RazaoSocial']; ?>
					            	  									<input type="hidden" name="Origem" value="D">
																		<input type="hidden" name="Destino">
																	</td>
								            					</tr>
								          					</table>
								      					</td>
								      				</tr>
													<?php if ($_SESSION['TipoHabilitacao'] == "L") { ?>
														<tr bgcolor="#bfdaf2">
											  				<td colspan="4">
								          						<table class="textonormal" border="0" align="left" width="100%" summary="">
								            						<tr>
								            							<td class="textonormal" colspan="">
																			<table border="0" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
			        			          										<tr>
								              										<td bgcolor="#75ADE6" class="textoabasoff" colspan="4" align="center">ENTIDADE PROFISSIONAL COMPETENTE</td>
								              									</tr>
											        	    					<tr>
											        	      						<td class="textonormal">Nome da Entidade</td>
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
								              											<?php $URL = "../calendario.php?Formulario=CadInscritoIncluir&Campo=DataVigencia" ?>
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
					            						<tr>
															<td bgcolor="#bfdaf2" colspan="4"></td>
														</tr>
														<tr>
					              							<td class="textonormal" colspan="4">
																<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
        			          										<tr>
					              										<td bgcolor="#75ADE6" class="textoabasoff" colspan="4" align="center">AUTORIZAÇÃO ESPECÍFICA</td>
					              									</tr>
					              									<?php	if (count($_SESSION['AutorizacaoNome']) != 0) {
							              										echo "<tr>\n";
							              										echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\" width=\"5%\">&nbsp;</td>\n";
							              										echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\">NOME DA ENTIDADE EMISSORA</td>\n";
							              										echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\">REGISTRO OU INSCRIÇÃO</td>\n";
							              										echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\" align=\"center\">DATA DE VIGÊNCIA</td>\n";
							              										echo "</tr>\n";

																				for ($i=0; $i< count($_SESSION['AutorizacaoNome']);$i++) {
							              											echo "			<tr>\n";
							              											echo "				<td class=\"textonormal\" width=\"5%\">\n";
							              											echo "					<input type=\"checkbox\" name=\"CheckAutorizacao[$i]\" value=\"$i\">\n";
							              											echo "				</td>\n";
							              											echo "				<td class=\"textonormal\" width=\"*\">".$_SESSION['AutorizacaoNome'][$i]."</td>\n";
							              											echo "				<td class=\"textonormal\" width=\"20%\">".$_SESSION['AutorizacaoRegistro'][$i]."</td>\n";
							              											echo "				<td class=\"textonormal\" width=\"15%\">".$_SESSION['AutorizacaoData'][$i]."</td>\n";
									    	      									echo "			</tr>\n";
					              												}
					              											}
					              									?>
					              									<tr>
					              										<td class="textonormal" colspan="4" align="center">
					              											<?php $Url = "CadIncluirAutorizacao.php?ProgramaOrigem=CadInscritoIncluir";
																			   if (!in_array($Url, $_SESSION['GetUrl'])) { $_SESSION['GetUrl'][] = $Url; } 
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
					            						<tr>
															<td bgcolor="#bfdaf2" colspan="4"></td>
														</tr>
													<?php } ?>
													<tr>
					              						<td class="textonormal" colspan="4">
															<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
        			          									<tr>
					              									<td bgcolor="#75ADE6" class="textoabasoff" colspan="2" align="center">GRUPOS DE FORNECIMENTO (OBJETO SOCIAL)</td>
					              								</tr>
					              								<?php 	if (count($_SESSION['Materiais']) != 0) {
							              									echo "<tr>\n";
							              									echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\" colspan=\"2\" align=\"center\">MATERIAIS</td>\n";
							              									echo "</tr>\n";

																			$DescricaoGrupoAntes = "";
																	
																			for ($i=0; $i< count($_SESSION['Materiais']);$i++) {
								              									$GrupoMaterial = explode("#",$_SESSION['Materiais'][$i]);

																				$db = Conexao();

																				$sql = "SELECT	A.CGRUMSCODI, A.EGRUMSDESC
																						FROM	SFPC.TBGRUPOMATERIALSERVICO A
																						WHERE	A.CGRUMSCODI = ".$GrupoMaterial[1]." 
																						ORDER BY 2 ";
																			
																				$res = $db->query($sql);
																			
																				if (PEAR::isError($res)) {
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				} else {
																					while ($Linha = $res->fetchRow()) {
								          	      										$Rows ++;
								          	      										$DescricaoGrupo = substr($Linha[1],0,75);

								              											if ($DescricaoGrupo != $DescricaoGrupoAntes) {
									              											echo "			<tr>\n";
																							echo "				<td class=\"textonormal\" width=\"5%\">\n";
																							echo "					<input type=\"checkbox\" name=\"CheckMateriais[$i]\" value=\"" . $_SESSION['Materiais'][$i] . "\">\n";
																							echo "				</td>\n";
																							echo "				<td class=\"textonormal\" width=\"*\">$DescricaoGrupo</td>\n";
																							echo "			</tr>\n";
								              											}
								              										}
																				}
					  	              											$DescricaoGrupoAntes = $DescricaoGrupo;
					              											}
					              											$db->disconnect();
					              									 	}
										  
																	   	if (count($_SESSION['Servicos']) != 0) {
							              									echo "<tr>\n";
							              									echo "	<td bgcolor=\"#DDECF9\" class=\"textoabason\" colspan=\"2\" align=\"center\">SERVIÇOS</td>\n";
							              									echo "</tr>\n";
																			  
																			$DescricaoGrupoAntes = "";
													  
																			for ($i=0; $i < count($_SESSION['Servicos']); $i++) {
								              									$ClasseServico = explode("#",$_SESSION['Servicos'][$i]);
																				  
																				$db = Conexao();
																				  
																				$sql = "SELECT	A.CGRUMSCODI, A.EGRUMSDESC
																						FROM	SFPC.TBGRUPOMATERIALSERVICO A
																						WHERE	A.CGRUMSCODI = ".$ClasseServico[1]."
																						ORDER BY 2 ";
													  
																				$res = $db->query($sql);
													
																				if (PEAR::isError($res)) {
													  								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				} else {
																					while ($Linha = $res->fetchRow()) {
						          	      												$Rows ++;
						          	      												$DescricaoGrupo = substr($Linha[1],0,75);

							              												if ($DescricaoGrupo != $DescricaoGrupoAntes) {
																							echo "			<tr>\n";
																							echo "				<td class=\"textonormal\" width=\"5%\">\n";
																							echo "					<input type=\"checkbox\" name=\"CheckServicos[$i]\" value=\"" . $_SESSION['Servicos'][$i] . "\">\n";
																							echo "				</td>\n";
																							echo "				<td class=\"textonormal\" width=\"*\">$DescricaoGrupo</td>\n";
																							echo "			</tr>\n";
																						}
							              											}
																				}
							  	            									$DescricaoGrupoAntes = $DescricaoGrupo;
					              											}
					              											$db->disconnect();
					              										}
					              								?>
					              								<tr>
					              									<td class="textonormal" colspan="2" align="center">
					              										<?php $Url = "CadIncluirGrupos.php?ProgramaOrigem=CadInscritoIncluir";
																		   if (!in_array($Url, $_SESSION['GetUrl'])) { $_SESSION['GetUrl'][] = $Url; }
																		?>
					              										<input class="botao" type="button" value="Incluir Grupos" onclick="javascript:AbreJanela('<?=$Url?>',750,370);">
																		<input class="botao" type="button" value="Retirar Grupos Marcados" onclick="javascript:enviar('RetirarGrupos');">
					              									</td>
					              								</tr>
					              							</table>
					              						</td>
					            					</tr>
													<?php if ($_SESSION['TipoHabilitacao'] == "L") { ?>
								      				<tr>
														<td bgcolor="#bfdaf2" colspan="4"></td>
													</tr>
													<tr>
					              						<td class="textonormal" colspan="4">
															<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
        			          									<tr>
					              									<td bgcolor="#75ADE6" colspan="2" class="textoabasoff" align="center">CUMPRIMENTO DA LEI</td>
					              								</tr>
					              								<tr>
					              									<td class="normal" align="left" width="80%">
					              										O fornecedor declara que cumpre o disposto no Inc. XXXIII do Art. 7º da Constituição Federal. "Sim" ou "Não"?
					              									</td>
					              									<td class="normal" align="left">
					              										<input type="radio" name="Cumprimento" value="S" <?php if( $_SESSION['Cumprimento'] == "S" ){ echo "checked"; }?>>Sim
					              										<input type="radio" name="Cumprimento" value="N" <?php if( $_SESSION['Cumprimento'] == "N" ){ echo "checked"; }?>>Não
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
												<input type="button" value="Próxima Aba" class="botao" onclick="javascript:enviar('D');">
												<input type="button" value="Limpar Tela" class="botao" onclick="javascript:enviar('Limpar');">
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
			<?php	if ($_SESSION['TipoHabilitacao'] == 'L') { ?>
			document.CadInscritoIncluir.NomeEntidade.focus();
			<?php } ?>
			
			<?php	if ($_SESSION['Email'] == "" and $_SESSION['Botao'] == "Incluir") {
						$Url = "RotVerificaEmail.php?ProgramaOrigem=CadInscritoIncluir";
			   			if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; } ?>
			window.open('<?=$Url;?>','pagina','status=no,scrollbars=no,left=200,top=150,width=400,height=225');
				<?php  } ?>
		</script>
	</body>
</html>
<?php exit;
}


function CriticaAbaDocumentos() { // Formulário E - Documentos

	$DDocumento = $_SESSION['DDocumento'];


	if ($_SESSION['Botao'] == "Limpar") {

		//$_SESSION['Botao'] = "";
		$_SESSION['tipoDoc'] = 0;
		$_SESSION['obsDocumento'] = "";

	} elseif  ($_SESSION['Botao'] == 'IncluirDocumento') {
		//var_dump($_FILES['Documentacao']);
		//die();
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
		
		
				$extensoes = explode(',', strtolower2($extensoesArquivo));
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
					if (! ($_SESSION['Arquivos_Upload_Insc']['conteudo'][] = file_get_contents($_FILES['Documentacao']['tmp_name']))) {
						$_SESSION['Mens']= 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] = 'Caminho da Documentação Inválido';
					} else {
						$_SESSION['Arquivos_Upload_Insc']['nome'][] = $_FILES['Documentacao']['name'];
						$_SESSION['Arquivos_Upload_Insc']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
						$_SESSION['Arquivos_Upload_Insc']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
						$_SESSION['Arquivos_Upload_Insc']['tipoCod'][] = $_POST['tipoDoc']; 
						$_SESSION['Arquivos_Upload_Insc']['tipoDocumentoDesc'][] = $_POST['tipoDocDesc']; 
						$_SESSION['Arquivos_Upload_Insc']['observacao'][] = strtoupper2($_POST['obsDocumento']); 
						$_SESSION['Arquivos_Upload_Insc']['dataHora'][] = date('d/m/Y H:i'); 

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
	} elseif ($_SESSION['Botao'] == 'RetirarDocumento') {
		//$Critica = 0;

		if ($DDocumento){
			foreach ($DDocumento as $valor) {
	
				if ($_SESSION['Arquivos_Upload_Insc']['situacao'][$valor] == 'novo') {
					$_SESSION['Arquivos_Upload_Insc']['situacao'][$valor] = 'cancelado'; // cancelado- quando o usuário incluiu um arquivo novo mas desistiu
				} elseif ($_SESSION['Arquivos_Upload_Insc']['situacao'][$valor] == 'existente') {
					$_SESSION['Arquivos_Upload_Insc']['situacao'][$valor] = 'excluido'; // excluído- quando o arquivo já existe e deve ser excluido no sistema
				}
	
			}

		}else{
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] = 'Selecione um anexo para ser retirado';
		}
	}
	
	if (($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Limpar") or ($_SESSION['Email'] == "" and $_SESSION['Botao'] == "Incluir") or ($_SESSION['Botao'] == "IncluirDocumento") or ($_SESSION['Botao'] == "RetirarDocumento")) {
		$_SESSION['Botao'] = "";
		ExibeAbaDocumentos();
	}
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
			$( document ).ready(function() {
				$('#Documentacao').change(function() {
					if(this.files[0].size > 10000000){
						$('#IncDoc').attr('disabled','disabled');
						$('#alertTam').show();
					}else{
						$('#IncDoc').removeAttr("disabled");
						$('#alertTam').hide();
					}
				});
			});


			function Submete(Destino){
				 document.CadInscritoIncluir.Destino.value = Destino;
				 document.CadInscritoIncluir.submit();
			}
			function enviar(valor){
				if(valor == 'Limpar'){
					if ( confirm("Deseja limpar os dados preenchidos desta aba?") ){
						document.CadInscritoIncluir.Botao.value = valor;
						document.CadInscritoIncluir.submit();
					}
				}else{
					document.CadInscritoIncluir.Botao.value = valor;
					document.CadInscritoIncluir.submit();
				}


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
			<form action="CadInscritoIncluir.php" method="post" name="CadInscritoIncluir" enctype="multipart/form-data">
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
							<span id="alertTam" style="display:none">
								<table border="0" width="100%">
								<tbody><tr>
									<td bgcolor="DCEDF7" class="titulo1">
										<blink><font class="titulo1">Erro!</font></blink>
									</td>
								</tr>
								<tr>
									<td class="titulo2">Informe: Tamanho do arquivo desse ser menor que 10Mb.</td>
								</tr>
								</tbody></table><br>


							
							</span>
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
													   INCLUIR - INSCRIÇÃO DE FORNECEDOR
												 </td>
											   </tr>
											<tr>
													 <td class="textonormal">
													<p align="justify">
														Para fazer a Inscrição de um Fornecedor, informe os dados abaixo. Os itens obrigatórios estão com *.<br>
														   Para preencher o endereço digite o CEP e clique no botão "Preencher Endereço".<br><br>
														Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
														Para este rotina funcionar bem, desabilite o bloqueador de janelas suspensas (Pop up) do seu navegador para este endereço.<br><br>
														Tamanho máximo para upload de arquivo: 5 MB.
														</p>
												 </td>
												 </tr>
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
																			  <?php if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { echo "Razão Social<span style=\"color: red;\">*</span>\n"; } else { echo "Nome<span style=\"color: red;\">*</span>\n"; } ?>
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
																		  <td class="textonormal">Documento<span style="color: red;">*</span></td>
																		<td class="textonormal">
																			<input type="file" name="Documentacao" id="Documentacao" class="textonormal" />
																			
																		</td>
																	</tr>
																	<tr>
																		  <td class="textonormal">Tipo de Documento<span style="color: red;">*</span></td>
																		<td class="textonormal">
																		<select name="tipoDoc" id="tipoDoc" class="tamanho_campo textonormal" onchange="preencheTipoDocDesc()">
																			<option value="0">Selecione um tipo de documento...</option>
																			<?php 
																				$db = Conexao();
																																								
																				$sql = "SELECT CFDOCTCODI, EFDOCTDESC, ffdoctobri FROM 
																						SFPC.TBFORNECEDORDOCUMENTOTIPO
																						WHERE FFDOCTSITU = 'A' ORDER BY afdoctorde, EFDOCTDESC";

																				$res = $db->query($sql);

																				if (PEAR::isError($res)) {
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
																			<textarea id="obsDocumento" name="obsDocumento" maxlength="500" cols="50" rows="4" onkeyup="javascript:CaracteresObservacao(1)" onblur="javascript:CaracteresObservacao(0)" onselect="javascript:CaracteresObservacao(1)" class="textonormal"><?php echo $_SESSION['obsDocumento'] ?></textarea>
																			<script language="javascript" type="">
																			function CaracteresObservacao(valor){
																				CadInscritoIncluir.NCaracteres.value = '' +  CadInscritoIncluir.obsDocumento.value.length;
																			}
																			</script>

																		</td>
																		<td valign="bottom">
																		<input type="button" value="Incluir Documento" id="IncDoc" class="botao" onclick="javascript:enviar('IncluirDocumento');">		
																		</td>
																	</tr>
																  </table>
															</td>
														</tr>
														<?php 	if (count($_SESSION['Arquivos_Upload_Insc']) > 0) {?>
														<tr>
					              						<td class="textonormal" colspan="4">
														 
															<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
																<tr>
					              									<td bgcolor="#75ADE6" class="textoabasoff" colspan="7" align="center">DOCUMENTOS ANEXADOS</td>
					              								</tr>
        			          									<tr>
					              									<td bgcolor="#bfdaf2" align="center"><b>  </b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Tipo do documento</b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Nome</b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Responsável anexação</b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Data/Hora Anexação</b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Situação</b></td>
																	  <td bgcolor="#bfdaf2" align="center"><b> Observação</b></td>
					              								</tr> 
																<?php																						$sql = "SELECT EUSUPORESP FROM 
																				SFPC.TBUSUARIOPORTAL
																				WHERE CUSUPOCODI = ".$_SESSION['_cusupocodi_']." limit 1";

																				$nome_usuario = resultValorUnico(executarTransacao($db, $sql));

																	$DTotal = count($_SESSION['Arquivos_Upload_Insc']['conteudo']);
																	for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {
																		if ($_SESSION['Arquivos_Upload_Insc']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload_Insc']['situacao'][$Dcont] == 'existente') {
																		?>	<tr>
																				<td align='center' width='5%' bgcolor="#ffffff"><input type='checkbox' name='DDocumento[<?php echo $Dcont?>]' value='<?php echo $Dcont?>' ></td>
																			
																				<td class='textonormal' bgcolor='#ffffff'>
																					<?php echo $_SESSION['Arquivos_Upload_Insc']['tipoDocumentoDesc'][$Dcont] ?>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<?php echo $_SESSION['Arquivos_Upload_Insc']['nome'][$Dcont] ?>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<?php 


																					if($nome_usuario){
																						echo $nome_usuario;
																					}else{
																						echo '-';
																					}
																					

																					?>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<?php echo $_SESSION['Arquivos_Upload_Insc']['dataHora'][$Dcont] ?>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<?php echo 'EM ANÁLISE' ?>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<?php echo $_SESSION['Arquivos_Upload_Insc']['observacao'][$Dcont] ?>
																				</td>
																			</tr>
																		<?php 

																			//$arquivo =  $_SESSION['Arquivos_Upload_Insc']['nome'][$Dcont];
																			//addArquivoAcesso($arquivo);
																		}
																	}

					              								?>
					              								<tr>
					              									<td class="textonormal" colspan="7" align="right">
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
										<input type="hidden" name="Destino">
										<input type="hidden" name="EmailPopup" value="<?php echo $_SESSION['EmailPopup'];?>">
										<input type="button" value="Limpar Tela" class="botao" onclick="javascript:enviar('Limpar');">
										<input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
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
					document.CadInscritoIncluir.CPF_CNPJ.focus()
				//-->
			</script>
		</body>
	</html>
	<?php	exit;
	}


function NavegacaoAbas($Pri,$Seg,$Ter,$Qua, $Qui) { // Navegação das abas

	$htm  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bordercolor=\"#75ADE6\" summary=\"\">";
	$htm .= "	<tr>\n";
	$htm .= "		<td valign=\"bottom\">\n";
	$htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
	$htm .=	"				<tr>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Pri."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Pri.".gif\" valign=\"center\" class=\"textoabason\"><a href=\"javascript:Submete('A');\" class=\"textoabas".$Pri."\">&nbsp;HABILITAÇÃO&nbsp;JURÍDICA&nbsp; </a></td>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Pri."_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
	$htm .=	"				</tr>\n";
	$htm .=	"			</table>\n";
	$htm .=	"		</td>\n";
	$htm .=	"		<td valign=\"bottom\">\n";
	$htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
	$htm .=	"		   	<tr>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Seg."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
	$htm .=	"			   	<td background=\"../midia/aba_".$Seg.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('B');\" class=\"textoabas".$Seg."\">&nbsp;REGULARIDADE&nbsp;FISCAL&nbsp; </a></td>\n";
	$htm .=	"			   	<td background=\"../midia/aba_".$Seg."_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
	$htm .=	"		   	</tr>\n";
	$htm .=	"			</table>\n";
	$htm .=	"		</td>\n";
	$htm .=	"		<td valign=\"bottom\">\n";
	$htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
	$htm .=	"				<tr>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Ter."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Ter.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('C');\" class=\"textoabas".$Ter."\">&nbsp;QUALIFICAÇÃO&nbsp;ECONÔMICA&nbsp;FINANCEIRA&nbsp;</a></td>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Ter."_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
	$htm .=	"				</tr>\n";
	$htm .=	"			</table>\n";
	$htm .=	"		</td>\n";
	$htm .=	"		<td valign=\"bottom\">\n";
	$htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
	$htm .=	"				<tr>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Qua."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Qua.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('D');\" class=\"textoabas".$Qua."\">&nbsp;QUALIFICAÇÃO&nbsp;TÉCNICA&nbsp;</a></td>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Qua."_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
	$htm .=	"				</tr>\n";
	$htm .=	"			</table>\n";
	$htm .=	"		</td>\n";
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
} ?>