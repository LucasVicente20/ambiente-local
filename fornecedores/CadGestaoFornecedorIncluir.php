<?php 
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadGestaoFornecedorIncluir.php
# Autor:    Roberta Costa
# Data:     23/08/2004
# Objetivo: Programa de Inclusão de Fornecedores
# Data:     28/05/2007 - Receber novos campos (índice Endividamento e MicroEmpresa ou EPP)
#                      - Correção do link "AQUI" para emissão do recibo
#-------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     18/06/2007 - Receber novo campo (índice de solvência)
#-------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     09/06/2008 	- novo campo: Email 2
# 											- Checagem de erros no preenchimento do CEP, DDD e Número
#-------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     09/09/2008 	- Alterações para incluir compra direta
#-------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     21/10/2008 	- Correção para limpar CEP (variavel de sessão 'CEPInformado')
#-------------------------------------------------------------------------
# Autor:    Everton Lino
# Data:     21/07/2010 - Alteração de Validar CEP (CEP VÁLIDO PARA MUDAR DE ABA)
#-------------------------------------------------------------------------
# Alterado: Ariston
# Data:     09/08/2010	- Adicionado opção para incluir sócios
#-------------------------------------------------------------------------
# Autor:    Everton Lino
# Data:     16/08/2010
# Objetivo: Permitir exclusão de todas classes serviços na inclusão e alteração de fornecedor e pré-fornecedor
#-------------------------------------------------------------------------
# Autor:    Everton Lino
# Data:     25/08/2010
#-------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     24/05/2011	- Tarefa Redmine: 2245 - Em Inscrição de Fornecedores obrigar a digitação do e-mail
#-------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     30/05/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
#                      - Alteração do nome do arquivo de "CadIncluirClasses.php" para "CadIncluirGrupos.php"
#-------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     30/05/2011 - Tarefa Redmine: 2727 - Incluir novos campos para preenchimento no SICREF - Dados do Representante Legal
#-------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     16/09/2011 - Tarefa Redmine: 3718 - Remoção de campos de Representante Legal no módulo de fornecedores (SICREF)
# Objetivo: Data de última alteração de contrato ou estatuto
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     25/07/2018
# Objetivo: Tarefa Redmine 80154
#-------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
# OBS.:     Alterações deste documento php podem precisar ser replicados em CadGestaoFornecedorIncluir.php, CadGestaoFornecedor.php, CadGestaoFornecedorIncluir.php, e CadGestaoFornecedorIncluir.php
#-------------------------------------------------------------------------
# Alterado: Ernesto Ferreira
# Data:		01/11/2018
# Objetivo: Tarefa Redmine 205883
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: João Madson
# Data:		12/08/2020
# Objetivo: Tarefa Redmine 221528
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Osmar Celestino
# Data:		11/01/2023
# Objetivo: Corrigir problema do Cep: Urgência em Produção.
# -----------------------------------------------------------------------------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include "../funcoes.php";
include "funcoesFornecedores.php";
require_once("funcoesDocumento.php");
date_default_timezone_set('America/Recife');
# Executa o controle de segurança	#
session_start();
Seguranca();
unset($_SESSION['Irregularidade']);
# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/CadIncluirCertidaoComplementar.php' );
AddMenuAcesso( '/fornecedores/CadIncluirGrupos.php' );
AddMenuAcesso( '/fornecedores/CadIncluirAutorizacao.php' );
AddMenuAcesso( '/fornecedores/RotVerificaEmail.php' );
AddMenuAcesso( '/fornecedores/CadGestaoFornecedorSelecionar.php' );
AddMenuAcesso( '/fornecedores/RelReciboFornecedorPdf.php' );


AddMenuAcesso( '/oracle/fornecedores/RotConsultaInscricaoMercantil.php' );
AddMenuAcesso( '/oracle/fornecedores/RotDebitoCredorConsulta.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "POST" ){
		$Origem            = $_POST['Origem'];
		$Destino           = $_POST['Destino'];
		$_SESSION['Botao'] = $_POST['Botao'];
		if( $Origem == "A" ){
				# Variáveis do Formulário A #
				if($_SESSION['CPF_CNPJ'] != $_POST['CPF_CNPJ'] ){
						$_SESSION['Irregularidade'] = "";

				}
				$_SESSION['CPF_CNPJ']        = $_POST['CPF_CNPJ'];
				$_SESSION['TipoCnpjCpf']     = $_POST['TipoCnpjCpf'];
				$_SESSION['Critica']         = $_POST['Critica'];
				$_SESSION['MicroEmpresa']    = $_POST['MicroEmpresa'];
				$_SESSION['Identidade']      = strtoupper2(trim($_POST['Identidade']));
				$_SESSION['OrgaoUF']         = strtoupper2(trim($_POST['OrgaoUF']));
				$_SESSION['RazaoSocial']     = strtoupper2(trim($_POST['RazaoSocial']));
				$_SESSION['NomeFantasia']    = strtoupper2(trim($_POST['NomeFantasia']));
				$_SESSION['CEP']             = $_POST['CEP'];
				$_SESSION['CEPInformado']    = $_POST['CEP'];
				$_SESSION['CEPAntes']        = $_POST['CEPAntes'];
				$_SESSION['Logradouro']      = strtoupper2(trim($_POST['Logradouro']));
				$_SESSION['Numero']          = $_POST['Numero'];
				$_SESSION['Complemento']     = strtoupper2(trim($_POST['Complemento']));
				$_SESSION['Bairro']          = strtoupper2(trim($_POST['Bairro']));
				$_SESSION['Cidade']          = strtoupper2(trim($_POST['Cidade']));
				$_SESSION['UF']              = $_POST['UF'];
				$_SESSION['DDD']             = $_POST['DDD'];
				$_SESSION['Telefone']        = $_POST['Telefone'];
				$_SESSION['Email']           = trim($_POST['Email']);
				$_SESSION['Email2']           = trim($_POST['Email2']);
				$_SESSION['EmailPopup']      = $_SESSION['Email'];
				$_SESSION['Fax']             = $_POST['Fax'];
				$_SESSION['RegistroJunta']   = $_POST['RegistroJunta'];
				$_SESSION['DataRegistro']    = $_POST['DataRegistro'];
				$_SESSION['NomeContato']     = strtoupper2(trim($_POST['NomeContato']));
				$_SESSION['CPFContato']      = $_POST['CPFContato'];
				$_SESSION['CargoContato']    = strtoupper2(trim($_POST['CargoContato']));
				$_SESSION['DDDContato']      = $_POST['DDDContato'];
				$_SESSION['TelefoneContato'] = $_POST['TelefoneContato'];
				$_SESSION['TipoHabilitacao'] = $_POST['TipoHabilitacao'];
				# Sócios
				/* SociosNome- Possui os nomes dos sócios.
				 * 		Note que o array inclui os nomes dos sócios deletados, que não serão adicionados
				 * SociosCPF_CNPJ- Possui o CPF/CNPJ dos sócios. Também inclui os CPF/CNPJs deletados
				 * noSocios- conta o número de sócios, incluindo os sócios deletados
				 * SocioNovoNome- Nome do novo sócio a ser adicionado
				 * SocioNovoCPF- CPF do sócio a ser adicionado
				 * SocioSelecionado- Sócio selecionado para algum comando (apenas usado no comando de remover sócio)
				 * MostrarNovoSocio- Informa que o nome e o CPF do sócio devem ser mostrados nas caixas de texto.
				 * 		Usado para o caso de correção do campo
				 * */
				$_SESSION['NoSocios'] = $_POST['NoSocios'];
				$_SESSION['SociosCPF_CNPJ'] = $_POST['SociosCPF_CNPJ'];
				$_SESSION['SociosNome'] = $_POST['SociosNome'];
				$_SESSION['SocioNovoNome'] = $_POST['SocioNovoNome'];
				$_SESSION['SocioNovoCPF'] = $_POST['SocioNovoCPF'];
				$_SESSION['SocioSelecionado'] = $_POST['SocioSelecionado'];
				$_SESSION['MostrarNovoSocio'] = false;
		}

		if( $Origem == "B" ){
				# Variáveis do Formulário B #
				if( $_SESSION['InscMercantil'] != $_POST['InscMercantil'] ){
						$_SESSION['InscricaoValida'] = "";
				}
				$_SESSION['InscEstadual']        = $_POST['InscEstadual'];
				$_SESSION['InscMercantil']       = $_POST['InscMercantil'];
				$_SESSION['InscOMunic']          = $_POST['InscOMunic'];
				$_SESSION['Certidao']            = $_POST['Certidao'];
				$_SESSION['DataCertidaoOp']      = $_POST['DataCertidaoOp'];
				$_SESSION['CertidaoObrigatoria'] = $_POST['CertidaoObrigatoria'];
				$_SESSION['DataCertidaoOb']      = $_POST['DataCertidaoOb'];
				$_SESSION['CheckComplementar']   = $_POST['CheckComplementar'];
		}

		if( $Origem == "C" ){
				# Variáveis do Formulário C #
				$_SESSION['CapSocial']        = $_POST['CapSocial'];
				$_SESSION['CapIntegralizado'] = $_POST['CapIntegralizado'];
				$_SESSION['Patrimonio']       = $_POST['Patrimonio'];
				$_SESSION['IndLiqCorrente']   = $_POST['IndLiqCorrente'];
				$_SESSION['IndLiqGeral']      = $_POST['IndLiqGeral'];
				$_SESSION['IndEndividamento'] = $_POST['IndEndividamento'];
				$_SESSION['IndSolvencia']     = $_POST['IndSolvencia'];
				$_SESSION['Banco1']           = strtoupper2(trim($_POST['Banco1']));
				$_SESSION['Agencia1']         = strtoupper2(trim($_POST['Agencia1']));
				$_SESSION['ContaCorrente1']   = strtoupper2(trim($_POST['ContaCorrente1']));
				$_SESSION['Banco2']           = strtoupper2(trim($_POST['Banco2']));
				$_SESSION['Agencia2']         = strtoupper2(trim($_POST['Agencia2']));
				$_SESSION['ContaCorrente2']   = strtoupper2(trim($_POST['ContaCorrente2']));
				$_SESSION['DataBalanco']      = $_POST['DataBalanco'];
				$_SESSION['DataNegativa']     = $_POST['DataNegativa'];
				$_SESSION['DataContratoEstatuto']     = $_POST['DataContratoEstatuto'];
		}

		if( $Origem == "D" ){
				# Variáveis do Formulário D #
				$_SESSION['RegistroEntidade']	= $_POST['RegistroEntidade'];
				$_SESSION['NomeEntidade']     = strtoupper2(trim($_POST['NomeEntidade']));
				$_SESSION['DataVigencia']     = $_POST['DataVigencia'];
				$_SESSION['RegistroTecnico']  = $_POST['RegistroTecnico'];
				$_SESSION['AutorizaNome']     = strtoupper2(trim($_POST['AutorizaNome']));
				$_SESSION['AutorizaRegistro'] = $_POST['AutorizaRegistro'];
				$_SESSION['AutorizaData']     = $_POST['AutorizaData'];
				$_SESSION['CheckAutorizacao'] = $_POST['CheckAutorizacao'];
				$_SESSION['CheckMateriais']   = $_POST['CheckMateriais'];
				$_SESSION['CheckServicos']    = $_POST['CheckServicos'];
				$_SESSION['Cumprimento']      = $_POST['Cumprimento'];
				//$_SESSION['EmailPopup']       = $_POST['EmailPopup'];
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
	
}else{
		$_SESSION['Irregularidade']  = $_GET['Irregularidade'];
		$_SESSION['InscricaoValida'] = $_GET['InscricaoValida'];
		$_SESSION['EmailVazio']      = $_GET['EmailVazio'];
		$Origem                      = $_GET['Origem'];
		$Destino                     = $_GET['Destino'];
}

# Reseta variáveis que não são usadas quando não é licitação
if($_SESSION['TipoHabilitacao'] != 'L'){
	// Origem C
	$_SESSION['CapSocial'] = null;
	$_SESSION['CapIntegralizado'] = null;
	$_SESSION['Patrimonio'] = null;
	$_SESSION['IndLiqCorrente'] = null;
	$_SESSION['IndLiqGeral'] = null;
	$_SESSION['IndEndividamento'] = null;
	$_SESSION['IndSolvencia'] = null;
	$_SESSION['DataBalanco'] = null;
	$_SESSION['DataNegativa'] = null;
	$_SESSION['DataContratoEstatuto'] = null;


	// Origem D
	$_SESSION['Cumprimento'] = null;
	$_SESSION['RegistroEntidade']	= null;
	$_SESSION['NomeEntidade']     = null;
	$_SESSION['DataVigencia']     = null;
	$_SESSION['RegistroTecnico'] = null;
	$_SESSION['AutorizaNome']     = null;
	$_SESSION['AutorizaRegistro'] = null;
	$_SESSION['AutorizaData']     = null;
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $_SESSION['Botao'] == "Voltar" ){
		$_SESSION['Botao'] = "";
		$Url = "CadGestaoFornecedorSelecionar.php?Programa=C";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}
if( $_SESSION['TipoHabilitacao']=='' or is_null($_SESSION['TipoHabilitacao']) ){
	$_SESSION['TipoHabilitacao']="L";
}

if( $Origem == "" ){
	unset($_SESSION['Arquivos_Upload_Inc']);
}

# Chamada das Criticas das Abas #
if( $Origem == "A" or $Origem == "" ){
		$_SESSION['DestinoCons'] = $Destino;
		CriticaAbaHabilitacao();
}elseif( $Origem == "B" ){
		$_SESSION['DestinoInsc'] = $Destino;
		CriticaAbaRegularidadeFiscal();
}elseif( $Origem == "C" ){
		CriticaAbaQualificEconFinanceira();
}elseif( $Origem == "D" ){
		CriticaAbaQualificTecnica();
}elseif ($Origem == "E" && $_SESSION['Botao'] != "Incluir") {
	CriticaAbaDocumentos();
}

# Aba de Habilitação Jurídica  - Formulário A #
if( $Origem == "A" or $Origem == "" ){
		if( $_SESSION['Botao'] == "A" ){
				$Destino = "B";
		}
		ExibeAbas($Destino);
}

# Aba de Regularidade Fiscal - Formulário B #
if( $Origem == "B" ){
		if( $_SESSION['Botao'] == "B"){
				$Destino = "C";
		}
		ExibeAbas($Destino);
}

# Aba de Qualificação Econômica e Financeira - Formulário C #
if( $Origem == "C" ){
		if( $_SESSION['Botao'] == "C" ){
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
if( $Origem == "E" ){
		if( $_SESSION['Botao'] == "Incluir" ){
				//CriticaAbaRegularidadeFiscal();
				//CriticaAbaQualificEconFinanceira();
				//CriticaAbaQualificTecnica(); // Verifica Critica do Formulário D
				CriticaAbaDocumentos();//Verifica Critica do Formulário E
				
				# Verifica se o Fornecedor já foi Cadastrado #
				$db = Conexao();
				$db->query("BEGIN TRANSACTION");
				$sql = "SELECT COUNT(AFORCRSEQU) FROM SFPC.TBFORNECEDORCREDENCIADO WHERE ";
				if( strlen($_SESSION['CPF_CNPJ']) == 11 ){
						$sql .= "AFORCRCCPF = '".$_SESSION['CPF_CNPJ']."' ";
				}elseif( strlen($_SESSION['CPF_CNPJ']) == 14 ){
						$sql .= "AFORCRCCGC = '".$_SESSION['CPF_CNPJ']."' ";
				}
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$rows = $result->numRows();
						if( $rows != 0 ){
								$Linha = $result->fetchRow();
								$ExisteFornecedor = $Linha[0];
								if( $ExisteFornecedor == 0 ){
										# Verifica se o Fornecedor Já foi Inscrito #
										$sqlpre = "SELECT CPREFSCODI, EPREFOMOTI FROM SFPC.TBPREFORNECEDOR WHERE ";

										# Colocar CPF/CGC para o Pré-Cadastro #
										if( strlen($_SESSION['CPF_CNPJ']) == 11 ){
												$ForCPF = "'".$_SESSION['CPF_CNPJ']."'";
												$ForCGC = "NULL";
												$sqlpre   .= "APREFOCCPF = $ForCPF ";
										}elseif( strlen($_SESSION['CPF_CNPJ']) == 14 ){
												$ForCGC = "'".$_SESSION['CPF_CNPJ']."'";
												$ForCPF = "NULL";
												$sqlpre   .= "APREFOCCGC = $ForCGC ";
										}
										$respre = $db->query($sqlpre);
										if( PEAR::isError($respre) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Linha    = $respre->fetchRow();
												$Situacao = $Linha[0];
												$Motivo   = $Linha[1];
												if( $Situacao == "" ){
														if( $_SESSION['Cumprimento'] == "S" or $_SESSION['TipoHabilitacao']!="L" ){
																# Recupera a último sequencial e incrementa mais um #
															  $sqlmax = "SELECT MAX(AFORCRSEQU) AS Maximo FROM SFPC.TBFORNECEDORCREDENCIADO";
															  $resmax = $db->query($sqlmax);
															  if( PEAR::isError($resmax) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlmax");
																}else{
																		$Maximo = $resmax->fetchRow();
																		$ForSeq = $Maximo[0] + 1;

																		# Data de Geração do Pré-Cadastro #
																		$DataAtual = date("Y-m-d H:i:s");

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
																		if( $_SESSION['DataRegistro'] == "" ){ $DataRegistroInv = "NULL"; }else{ $DataRegistroInv	= "'".substr($_SESSION['DataRegistro'],6,4)."-".substr($_SESSION['DataRegistro'],3,2)."-".substr($_SESSION['DataRegistro'],0,2)."'"; }
																		if( $_SESSION['Email'] == "" or $_SESSION['Email'] == "NULL" ){ $Email = "NULL"; }else{ $Email = "'".$_SESSION['Email']."'"; }
																		if( $_SESSION['Email2'] == "" or $_SESSION['Email2'] == "NULL" ){ $Email2 = "NULL"; }else{ $Email2 = "'".$_SESSION['Email2']."'"; }

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
																    if( $_SESSION['IndEndividamento'] == "" ){ $IndEndividamento = "NULL"; }else{ $IndEndividamento = str_replace(",", ".",$_SESSION['IndEndividamento']); }
																    if( $_SESSION['IndSolvencia']     == "" ){ $IndSolvencia     = "NULL"; }else{ $IndSolvencia     = str_replace(",", ".",$_SESSION['IndSolvencia']); }
																    if( $_SESSION['DataBalanco']      == "" or $_SESSION['DataBalanco']  == "//" ){ $DataBalanco      = "NULL"; }else{ $DataBalanco      = "'".substr($_SESSION['DataBalanco'],6,4)."-".substr($_SESSION['DataBalanco'],3,2)."-".substr($_SESSION['DataBalanco'],0,2)."'"; }
																    if( $_SESSION['DataNegativa']     == "" or $_SESSION['DataNegativa']  == "//" ){ $DataNegativa     = "NULL"; }else{ $DataNegativa     = "'".substr($_SESSION['DataNegativa'],6,4)."-".substr($_SESSION['DataNegativa'],3,2)."-".substr($_SESSION['DataNegativa'],0,2)."'"; }
																    if( $_SESSION['DataContratoEstatuto']     == "" or $_SESSION['DataContratoEstatuto']  == "//" ){ $DataContratoEstatuto  = "NULL"; }else{ $DataContratoEstatuto  = "'".substr($_SESSION['DataContratoEstatuto'],6,4)."-".substr($_SESSION['DataContratoEstatuto'],3,2)."-".substr($_SESSION['DataContratoEstatuto'],0,2)."'"; }


																    # Atribuindo Valor NULL - Formulário D #
																	if( $_SESSION['NomeEntidade']     == "" ){ $NomeEntidade     = "NULL"; }else{ $NomeEntidade     = "'".$_SESSION['NomeEntidade']."'"; }
																    if( $_SESSION['RegistroEntidade'] == "" ){ $RegistroEntidade = "NULL"; }else{ $RegistroEntidade = $_SESSION['RegistroEntidade']; }
																    if( $_SESSION['DataVigencia']     == "" or $_SESSION['DataVigencia']  == "//" ){ $DataVigencia     = "NULL"; }else{ $DataVigencia     = "'".substr($_SESSION['DataVigencia'],6,4)."-".substr($_SESSION['DataVigencia'],3,2)."-".substr($_SESSION['DataVigencia'],0,2)."'"; }
																    if( $_SESSION['RegistroTecnico']  == "" ){ $RegistroTecnico  = "NULL"; }else{ $RegistroTecnico  = $_SESSION['RegistroTecnico']; }
																    if( $_SESSION['RegistroJunta']  == "" ){ $RegistroJunta  = "NULL"; }else{ $RegistroJunta  = $_SESSION['RegistroJunta']; }

																	# Colocando o CEP de Logragouro ou Material #
												    		 		if( $_SESSION['Localidade'] == "S" ){
															 				$Cep           = "NULL";
															 				$CepLocalidade = $_SESSION['CEP'];
															 				if($_SESSION['CEP']=="" or is_null($_SESSION['CEP'])) $CepLocalidade = "NULL";
												    		 		}else{
															 				$Cep           = $_SESSION['CEP'];
															 				if($_SESSION['CEP']=="" or is_null($_SESSION['CEP'])) $Cep = "NULL";
															 				$CepLocalidade = "NULL";
															 		}







																    # Insere Fornecedor #
																		$sql    = "INSERT INTO SFPC.TBFORNECEDORCREDENCIADO ( ";
																		$sql   .= "AFORCRSEQU, APREFOSEQU, AFORCRCCGC, AFORCRCCPF, AFORCRIDEN, ";
																		$sql   .= "NFORCRORGU, NFORCRRAZS, NFORCRFANT, CCEPPOCODI, CCELOCCODI, ";
																		$sql   .= "EFORCRLOGR, AFORCRNUME, EFORCRCOMP, EFORCRBAIR, NFORCRCIDA, ";
																		$sql   .= "CFORCRESTA, AFORCRCDDD, AFORCRTELS, AFORCRNFAX, NFORCRMAIL, ";
																		$sql   .= "AFORCRCPFC, NFORCRCONT, NFORCRCARG, AFORCRDDDC, AFORCRTELC, ";
																		$sql   .= "AFORCRREGJ, DFORCRREGJ, AFORCRINES, AFORCRINME, AFORCRINSM, ";
																		$sql   .= "VFORCRCAPS, VFORCRCAPI, VFORCRPATL, VFORCRINLC, VFORCRINLG, ";
																		$sql   .= "DFORCRULTB, DFORCRCNFC, DFORCRCONT, NFORCRENTP, AFORCRENTR, ";
																		$sql   .= "DFORCRVIGE, AFORCRENTT, DFORCRGERA, CGREMPCODI, CUSUPOCODI, ";
																		$sql   .= "TFORCRULAT, FFORCRCUMP, VFORCRINDI, FFORCRMEPP, VFORCRINSO, ";
																		$sql   .= "NFORCRMAI2, FFORCRTIPO ";
																		$sql   .= " ) VALUES ( ";
																		$sql   .= "$ForSeq, NULL, $ForCGC, $ForCPF, $Identidade, ";
																		$sql   .= "$OrgaoUF, '".$_SESSION['RazaoSocial']."', $NomeFantasia, $Cep, $CepLocalidade, ";
																		$sql   .= "'".$_SESSION['Logradouro']."', $Numero, $Complemento, '".$_SESSION['Bairro']."', '".$_SESSION['Cidade']."', ";
																		$sql   .= "'".$_SESSION['UF']."', $DDD, $Telefone, $Fax, $Email, ";
																		$sql   .= "$CPFContato, $NomeContato, $CargoContato, $DDDContato, $TelefoneContato, ";
																		$sql   .= "$RegistroJunta, $DataRegistroInv, $InscEstadual, $InscMercantil, $InscOMunic, ";
																		$sql   .= "$CapSocial, $CapIntegralizado, $Patrimonio, $IndLiqCorrente, $IndLiqGeral, ";
																		$sql   .= "$DataBalanco, $DataNegativa, $DataContratoEstatuto, $NomeEntidade, $RegistroEntidade, $DataVigencia, ";
																		$sql   .= "$RegistroTecnico, '".substr($DataAtual,0,10)."', ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '$DataAtual', ";
																		$sql   .= "'".$_SESSION['Cumprimento']."',";
																		$sql   .= "$IndEndividamento, $MicroEmpresa, $IndSolvencia, $Email2, '".$_SESSION['TipoHabilitacao']."') ";
																		$result = $db->query($sql);
																		if( PEAR::isError($result) ){
																				$db->query("ROLLBACK");
										    								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				# Inserindo Situação - Cadastrado #
																				$sql    = "INSERT INTO SFPC.TBFORNSITUACAO ( ";
																				$sql   .= "AFORCRSEQU, CFORTSCODI, EFORSIMOTI, CGREMPCODI, ";
																				$sql   .= "CUSUPOCODI, DFORSISITU, TFORSIULAT ";
																				$sql   .= ") VALUES ( ";
																				$sql   .= "$ForSeq, 1, NULL, ".$_SESSION['_cgrempcodi_'].", ";
																				$sql   .= "".$_SESSION['_cusupocodi_'].", '".substr($DataAtual,0,10)."', '$DataAtual' ) ";
																				$result = $db->query($sql);
																				if( PEAR::isError($result) ){
																						$db->query("ROLLBACK");
												    								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				}

																				# Inserindo Contas Bancárias #
																				if( $_SESSION['Banco1'] != "" and $_SESSION['Agencia1'] != "" and $_SESSION['ContaCorrente1'] != "" ){
																						$sql    = "INSERT INTO SFPC.TBFORNCONTABANCARIA ( ";
																						$sql   .= "AFORCRSEQU, CFORCBBANC, CFORCBAGEN, CFORCBCONT, TFORCBULAT ";
																						$sql   .= ") VALUES ( ";
																						$sql   .= "$ForSeq, '".$_SESSION['Banco1']."', '".$_SESSION['Agencia1']."', '".$_SESSION['ContaCorrente1']."', '$DataAtual' ) ";
																						$result = $db->query($sql);
																						if( PEAR::isError($result) ){
																								$db->query("ROLLBACK");
														    								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																						}
																				}
																				if( $_SESSION['Banco2'] != "" and $_SESSION['Agencia2'] != "" and $_SESSION['ContaCorrente2'] != "" ){
																						$sql    = "INSERT INTO SFPC.TBFORNCONTABANCARIA ( ";
																						$sql   .= "AFORCRSEQU, CFORCBBANC, CFORCBAGEN, CFORCBCONT, TFORCBULAT ";
																						$sql   .= ") VALUES ( ";
																						$sql   .= "$ForSeq, '".$_SESSION['Banco2']."', '".$_SESSION['Agencia2']."', '".$_SESSION['ContaCorrente2']."', '$DataAtual' ) ";
																						$result = $db->query($sql);
																						if( PEAR::isError($result) ){
																								$db->query("ROLLBACK");
														    								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																						}
																				}


																				# Inserindo Sócios #
																				if( $_SESSION['TipoCnpjCpf'] == "CNPJ" ){
																								for( $i=0; $i< $_SESSION['NoSocios']; $i++ ){
																									if( !is_null($_SESSION['SociosNome'][$i]) ){
																										$cpfnocaracteres = strlen($_SESSION['SociosCPF_CNPJ'][$i]);
																										$tipocadastro = NULL;
																										if($cpfnocaracteres==11){
																											$tipocadastro = "F";
																										}else if($cpfnocaracteres==14){
																											$tipocadastro = "J";
																										}
																										if (is_null($tipocadastro)){
																											EmailErro("Erro na inclusão de sócios", __FILE__, __LINE__, "Erro na inclusão de sócios de fornecedores. CPF/CNPJ não está em um tamanho válido");
																										}else{
																											$sql = "
																												INSERT INTO SFPC.TBSOCIOFORNECEDOR (
																													AFORCRSEQU, nsofornome, asoforcada, fsofortcad, tsoforulat
																												) VALUES (
																													".$ForSeq.", '".$_SESSION['SociosNome'][$i]."', '".$_SESSION['SociosCPF_CNPJ'][$i]."', '".$tipocadastro."', '$DataAtual'
																												)
																											";
																											$result = $db->query($sql);
																											if( PEAR::isError($result) ){
																													$db->query("ROLLBACK");
																													ExibeErroBD($_SESSION['ErroPrograma']."\nLinha: ".__LINE__."\nSql: $sql");
																											}
																										}
																									}
																								}
																				}


																				# Inserindo Certidões #
																				if( count($_SESSION['CertidaoObrigatoria']) != 0 ){
																						for( $i=0; $i<count($_SESSION['CertidaoObrigatoria']); $i++ ){
																							if( !is_null($_SESSION['CertidaoObrigatoria'][$i]) and $_SESSION['CertidaoObrigatoria'][$i]!="" and !is_null($_SESSION['DataCertidaoOb'][$i]) and $_SESSION['DataCertidaoOb'][$i]!="" ){
																								$DataCertidaoObInv = substr($_SESSION['DataCertidaoOb'][$i],6,4)."-".substr($_SESSION['DataCertidaoOb'][$i],3,2)."-".substr($_SESSION['DataCertidaoOb'][$i],0,2);
		 																						$sql    = "INSERT INTO SFPC.TBFORNECEDORCERTIDAO ( ";
																								$sql   .= "AFORCRSEQU, CTIPCECODI, DFORCEVALI, TFORCEULAT ";
																								$sql   .= ") VALUES ( ";
																								$sql   .= "$ForSeq, ".$_SESSION['CertidaoObrigatoria'][$i].", '$DataCertidaoObInv', '$DataAtual' ) ";
																								$result = $db->query($sql);
																								if( PEAR::isError($result) ){
																										$db->query("ROLLBACK");
																    								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																								}
																							}
																						}
																				}

																				if( count($_SESSION['CertidaoComplementar']) != 0 ){
																						for( $i=0; $i<count($_SESSION['CertidaoComplementar']); $i++ ){
																							if(!is_null($_SESSION['CertidaoComplementar'][$i]) and $_SESSION['CertidaoComplementar'][$i]!="" ){
																								$DataCertidaoOpInv = substr($_SESSION['DataCertidaoOp'][$i],6,4)."-".substr($_SESSION['DataCertidaoOp'][$i],3,2)."-".substr($_SESSION['DataCertidaoOp'][$i],0,2);
																								$sql    = "INSERT INTO SFPC.TBFORNECEDORCERTIDAO ( ";
																								$sql   .= "AFORCRSEQU, CTIPCECODI, DFORCEVALI, TFORCEULAT ";
																								$sql   .= ") VALUES ( ";
																								$sql   .= "$ForSeq, ".$_SESSION['CertidaoComplementar'][$i].", '$DataCertidaoOpInv', '$DataAtual' ) ";
																								$result = $db->query($sql);
																								if( PEAR::isError($result) ){
																										$db->query("ROLLBACK");
																    								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																								}
																							}
																						}
																				}

																				# Inserindo Autorização Específica #
																				if( count($_SESSION['AutorizacaoNome']) != 0 ){
																						for( $i=0; $i< count($_SESSION['AutorizacaoNome']); $i++ ){
																							if(!is_null($_SESSION['AutorizacaoNome'][$i]) and $_SESSION['AutorizacaoNome'][$i]!="" ){
																								$AutorizacaoDataInv = substr($_SESSION['AutorizacaoData'][$i],6,4)."-".substr($_SESSION['AutorizacaoData'][$i],3,2)."-".substr($_SESSION['AutorizacaoData'][$i],0,2);
																								$sql    = "INSERT INTO SFPC.TBFORNAUTORIZACAOESPECIFICA ( ";
																								$sql   .= "AFORCRSEQU, NFORAENOMA, AFORAENUMA, DFORAEVIGE, TFORAEULAT ";
																								$sql   .= ") VALUES ( ";
																								$sql   .= "$ForSeq, '".$_SESSION['AutorizacaoNome'][$i]."', ".$_SESSION['AutorizacaoRegistro'][$i].", '$AutorizacaoDataInv', '$DataAtual' ) ";
																								$result = $db->query($sql);
																								if( PEAR::isError($result) ){
																										$db->query("ROLLBACK");
																										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																								}
																							}
																						}
																				}

																				# Incluindo Grupos de Fornecimento #
																				if( count($_SESSION['Materiais']) != 0 ){
																						for( $i=0; $i< count($_SESSION['Materiais']); $i++ ){
																								$GrupoMaterial = explode("#",$_SESSION['Materiais'][$i]);
																								$sql    = "INSERT INTO SFPC.TBGRUPOFORNECEDOR ( ";
																								$sql   .= "CGRUMSCODI, AFORCRSEQU, TGRUFOULAT ";
																								$sql   .= ") VALUES ( ";
																								$sql   .= $GrupoMaterial[1].", $ForSeq, '$DataAtual' ) ";
																								$result = $db->query($sql);
																								if( PEAR::isError($result) ){
																										$db->query("ROLLBACK");
																										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																								}
																						}
																				}

																				if( count($_SESSION['Servicos']) != 0 ){
																						for( $i=0; $i< count($_SESSION['Servicos']); $i++ ){
																								$GrupoServico = explode("#",$_SESSION['Servicos'][$i]);
																								$sql    = "INSERT INTO SFPC.TBGRUPOFORNECEDOR ( ";
																								$sql   .= "CGRUMSCODI, AFORCRSEQU, TGRUFOULAT ";
																								$sql   .= ") VALUES ( ";
																								$sql   .= $GrupoServico[1].", $ForSeq, '$DataAtual' ) ";
																								$result = $db->query($sql);
																								if( PEAR::isError($result) ){
																										$db->query("ROLLBACK");
																										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																								}
																						}
																				}

																				if (count($_SESSION['Arquivos_Upload_Inc']) != 0) {
																					for ($i=0; $i< count($_SESSION['Arquivos_Upload_Inc']); $i++) {
									
																						$arquivo = $_SESSION['Arquivos_Upload_Inc'];
																						if($arquivo['situacao'][$i] == 'novo'){
																							// fazer sql para trazer o sequencial
																							$sql = ' SELECT cfdocusequ FROM SFPC.tbfornecedordocumento WHERE  1=1 ORDER BY cfdocusequ DESC limit 1';
																							$seqDocumento = resultValorUnico(executarTransacao($db, $sql)) + 1;
																	
																							$anexo =  bin2hex($arquivo['conteudo'][$i]);
									
																							$sqlAnexo = "INSERT INTO sfpc.tbfornecedordocumento
																							(cfdocusequ, aprefosequ, aforcrsequ, afdocuanoa, cfdoctcodi, efdocunome, ifdocuarqu, ffdocuforn, tfdocuanex, ffdocusitu, cusupocodi, tfdoctulat)
																							VALUES(".$seqDocumento.", NULL, ".$ForSeq." , DATE_PART('YEAR', CURRENT_TIMESTAMP), ".$arquivo['tipoCod'][$i].", '".$arquivo['nome'][$i]."', decode('".$anexo."','hex'), 'N', '".$DataAtual."', 'A', ".$_SESSION['_cusupocodi_'].",  '".$DataAtual."');
																							";
																							
																							$resultAnexo = $db->query($sqlAnexo);
																							
																							if (PEAR::isError($resultAnexo)) {
																								$db->query("ROLLBACK");
																								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlAnexo");
																							}else{
																								//insere a fase do documento
																								$sqlHist = "INSERT INTO sfpc.tbfornecedordocumentohistorico
																										(cfdocusequ, cfdocscodi, efdochobse, tfdochcada, cusupocodi, tfdochulat)
																										VALUES(".$seqDocumento.", 1, '".$arquivo['observacao'][$i]."', now(), ".$_SESSION['_cusupocodi_'].", now());
									
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
																				$sql = "SELECT NFORCRMAIL, NFORCRMAI2 FROM SFPC.TBFORNECEDORCREDENCIADO WHERE AFORCRSEQU = $ForSeq";
																				$result = $db->query($sql);
																				if( PEAR::isError($result) ){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				}else{
																						$Linha = $result->fetchRow();
																						$Email = $Linha[0];
																						$Email2 = $Linha[1];
																						unset($_SESSION['Arquivos_Upload_Inc']);
																				}

																				# Cria a senha do Usuário #
																				$Senha             = CriaSenha();
																				$_SESSION['Senha'] = $Senha;
																				$SenhaCript        = crypt($Senha,"P");

																				# Atualiza a senha do Usuário #
																				$sql = "UPDATE SFPC.TBFORNECEDORCREDENCIADO SET NFORCRSENH = '$SenhaCript' WHERE AFORCRSEQU = $ForSeq";
																				$result = $db->query($sql);
																				if( PEAR::isError($result) ){
																						$db->query("ROLLBACK");
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				}else{
																						$_SESSION['Mens'] = 1;$_SESSION['Tipo'] = 1;
																						$_SESSION['Mensagem'] = "Inscrição Incluída com Sucesso";

																						# Envia a senha pelo e-mail do usuário #
																						if( $Email != "" ){
																								if( strlen($_SESSION['CPF_CNPJ']) == 11 ){
																										$TipoForn     = "Nome do Fornecedor";
																										$CpfCgcMail   = "CPF";
																										$CpfCgcNumero = substr($_SESSION['CPF_CNPJ'],0,3).".".substr($_SESSION['CPF_CNPJ'],3,3).".".substr($_SESSION['CPF_CNPJ'],6,3)."-".substr($_SESSION['CPF_CNPJ'],9,2);
																								}elseif( strlen($_SESSION['CPF_CNPJ']) == 14 ){
																										$TipoForn     = "Razão Social do Fornecedor";
																										$CpfCgcMail   = "CNPJ";
																										$CpfCgcNumero = substr($_SESSION['CPF_CNPJ'],0,2).".".substr($_SESSION['CPF_CNPJ'],2,3).".".substr($_SESSION['CPF_CNPJ'],5,3)."/".substr($_SESSION['CPF_CNPJ'],8,4)."-".substr($_SESSION['CPF_CNPJ'],12,2);
																								}
																								EnviaEmail("$Email","Senha Temporária de Inscrição no Portal de Compras ".$Email,"\t $TipoForn: ".$_SESSION['RazaoSocial']."\n\t $CpfCgcMail: $CpfCgcNumero \n\t Senha: $Senha ","from: portalcompras@recife.pe.gov.br");
																								if($Email2!="" and !is_null($Email2) ){
																									EnviaEmail("$Email2","Senha Temporária de Inscrição no Portal de Compras".$Email2,"\t $TipoForn: ".$_SESSION['RazaoSocial']."\n\t $CpfCgcMail: $CpfCgcNumero \n\t Senha: $Senha ","from: portalcompras@recife.pe.gov.br");
																								}
																								$_SESSION['Mensagem'] .= ". A senha Temporária foi enviada p/o e-mail do Fornecedor";
																						}

																						# Coloca na mensagem o caminho para Recibo #
																						$Url = "RelReciboFornecedorPdf.php?CodigoInsc=$ForSeq";
																						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																						$_SESSION['Mensagem'] .= ". Para visualizar o Recibo clique <a href=\"$Url\" class=\"titulo1\">AQUI</a>";

																						# Limpa Variáveis #
																						$Origem	 = "";
																						$Destino = "";
																						unset($_SESSION['Botao']);

																						# Limpa Variáveis de Sessão - Formulário A #
																						$Origem	           = "";
																						$Destino           = "";
																						$_SESSION['Botao'] = "";
																						unset($_SESSION['Arquivos_Upload_Inc']);
																						LimparSessao();
																				}
																		}
																}
														}
												}else{
														if( $Situacao == 5 ){
																$_SESSION['Mens'] = 1;$_SESSION['Tipo'] = 1;
																$_SESSION['Mensagem'] = "A Inscrição do Fornecedor foi excluída. Motivo: $Motivo. Para regularizar o seu cadastro, procurar a Divisão de Credenciamento - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo, 925 - Bairro do Recife - Recife/PE";
														}else{
																$_SESSION['Mens'] = 1;$_SESSION['Tipo'] = 1;
																$_SESSION['Mensagem'] = "Fornecedor Já Inscrito";
														}
												}
										}
								}else{
										$_SESSION['Mens'] = 1;$_SESSION['Tipo'] = 1;
										$_SESSION['Mensagem'] = "Fornecedor Já Cadastrado. Acesse a Consulta de Acompanhamento de Fornecedor para visualizar o seu cadastro. Caso não possua a senha de acesso procurar a Divisão de Credenciamento - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo, 925 - Bairro do Recife - Recife/PE";
								}
						}
						$db->query("COMMIT");
						$db->query("END");
						$db->disconnect();
						$Destino = "A";
				}
		}
		ExibeAbas($Destino);
}

# Função para Chamada do Formulário de cada Aba #
function ExibeAbas($Destino){
	if( $Destino == "A" or $Destino == "" ){
			ExibeAbaHabilitacao();
	}elseif( $Destino == "B" ){
			ExibeAbaRegularidadeFiscal();
	}elseif( $Destino == "C" ){
			ExibeAbaQualificEconFinanceira();
	}elseif( $Destino == "D" ){
			ExibeAbaQualificTecnica();
	}elseif ($Destino == "E") {
		ExibeAbaDocumentos();
	}
}

# Critica aos Campos - Formulário A #
function CriticaAbaHabilitacao(){
	if( $_SESSION['Botao'] == "Limpar" ){
			$_SESSION['Botao']           = "";
			$_SESSION['Mens']						 = "";
			$_SESSION['CPF_CNPJ']				 = "";
			$_SESSION['TipoCnpjCpf']		 = "";
			$_SESSION['MicroEmpresa']	   = "";
			$_SESSION['Identidade']		   = "";
			$_SESSION['OrgaoUF']	       = "";
			$_SESSION['RazaoSocial']		 = "";
			$_SESSION['NomeFantasia']    = "";
			$_SESSION['CEP']       			 = "";
			$_SESSION['CEPInformado']		=	"";
			$_SESSION['Logradouro']			 = "";
			$_SESSION['Numero']			     = "";
			$_SESSION['Complemento']	   = "";
			$_SESSION['Bairro']					 = "";
			$_SESSION['Cidade']					 = "";
			$_SESSION['UF']  						 = "";
			$_SESSION['DDD']          	 = "";
			$_SESSION['Telefone']     	 = "";
			$_SESSION['Email'] = "";
			$_SESSION['Email2'] = "";
			$_SESSION['Fax'] 						 = "";
			$_SESSION['RegistroJunta']   = "";
			$_SESSION['DataRegistro']    = "";
			$_SESSION['CPFContato']      = "";
			$_SESSION['NomeContato']     = "";
			$_SESSION['CargoContato']    = "";
			$_SESSION['DDDContato']      = "";
			$_SESSION['TelefoneContato'] = "";
			$_SESSION['EmailVazio']      = "";
			$_SESSION['TipoHabilitacao'] = "L";
			$_SESSION['NoSocios'] =0;
			$_SESSION['SociosCPF_CNPJ'] = array();
			$_SESSION['SociosNome'] = array();
			$_SESSION['SocioNovoNome'] = "";
			$_SESSION['SocioNovoCPF'] = "";
	}else{
			$_SESSION['Mens']			= 0;
			$_SESSION['Mensagem'] = "Informe: ";
			if( $_SESSION['CPF_CNPJ'] != "" ){
					$_SESSION['CPF_CNPJ'] = FormataCPF_CNPJ($_SESSION['CPF_CNPJ'],$_SESSION['TipoCnpjCpf']);
			}
			if( $_SESSION['TipoCnpjCpf'] == "CPF" and $_SESSION['Critica'] != 1 ){
			  	$Qtd = strlen($_SESSION['CPF_CNPJ']);
			  	$_SESSION['TipoDoc'] = 2;
			  	if( ($Qtd != 11) and ($Qtd != 0) ){
			      	if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CPF com 11 números</a>";
					}elseif( $_SESSION['CPF_CNPJ'] == "" ){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
					  	$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CPF</a>";
					}else{
					  	if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$cpfcnpj = valida_CPF($_SESSION['CPF_CNPJ']);
							if( $cpfcnpj === false ){
							  	$_SESSION['Mens']      = 1;
							  	$_SESSION['Tipo']      = 2;
		  						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CPF Válido</a>";
		  				}
			  	}
			}elseif( $_SESSION['TipoCnpjCpf'] == "CNPJ" and $_SESSION['Critica'] != 1 ){
					$Qtd = strlen($_SESSION['CPF_CNPJ']);
					$_SESSION['TipoDoc'] = 1;
			   	if( ($Qtd != 14) and ($Qtd != 0)  ){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
					  	$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ com 14 números</a>";
				 	}elseif( $_SESSION['CPF_CNPJ'] == "" ){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
					  	$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ</a>";
				 	}else{
					  	if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$cpfcnpj = valida_CNPJ($_SESSION['CPF_CNPJ']);
							if( $cpfcnpj === false ){
							  	$_SESSION['Mens']      = 1;
							  	$_SESSION['Tipo']      = 2;
		  						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CPF_CNPJ.focus();\" class=\"titulo2\">CNPJ Válido</a>";
		  				}
				 	}
			}
			if( $cpfcnpj === true ){
					# Verifica se o Fornecedor já foi Cadastrado #
					$db = Conexao();
					$db->query("BEGIN TRANSACTION");
					$sql = "SELECT COUNT(AFORCRSEQU) FROM SFPC.TBFORNECEDORCREDENCIADO WHERE ";
					if( strlen($_SESSION['CPF_CNPJ']) == 11 ){
			    		$sql .= "AFORCRCCPF = '".$_SESSION['CPF_CNPJ']."' ";
			    }elseif( strlen($_SESSION['CPF_CNPJ']) == 14 ){
			    		$sql .= "AFORCRCCGC = '".$_SESSION['CPF_CNPJ']."' ";
			    }
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
							$Linha = $result->fetchRow();
			      	$ExisteFornecedor = $Linha[0];
							if( $ExisteFornecedor == 0 ){
								  # Verifica se o Fornecedor Já foi Inscrito #
								  $sqlpre = "SELECT CPREFSCODI,EPREFOMOTI FROM SFPC.TBPREFORNECEDOR WHERE ";

							    # Colocar CPF/CGC para o Pré-Cadastro #
							    if( strlen($_SESSION['CPF_CNPJ']) == 11 ){
							    		$ForCPF  = "'".$_SESSION['CPF_CNPJ']."'";
							    		$ForCGC  = "NULL";
							    		$sqlpre .= "APREFOCCPF = $ForCPF ";
							    }elseif( strlen($_SESSION['CPF_CNPJ']) == 14 ){
							    		$ForCGC  = "'".$_SESSION['CPF_CNPJ']."'";
							    		$ForCPF  = "NULL";
							    		$sqlpre .= "APREFOCCGC = $ForCGC ";
							    }
									$respre = $db->query($sqlpre);
								  if( PEAR::isError($respre) ){
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											$ExistePreInscrito = $respre->numRows();
											if( $ExistePreInscrito != 0 ){
													$Linha = $respre->fetchRow();
													$Situacao = $Linha[0];
													$Motivo   = $Linha[1];
													if( $Situacao == 5 ){
															$_SESSION['Mens']     = 1;
															$_SESSION['Tipo']     = 1;
															$_SESSION['Mensagem'] = "A Inscrição do Fornecedor foi excluída. Motivo: $Motivo. Para regularizar o seu cadastro, procurar a Divisão de Credenciamento - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo, 925 - Bairro do Recife - Recife/PE";
													}else{
															$_SESSION['Mens']     = 1;
															$_SESSION['Tipo']     = 1;
															$_SESSION['Mensagem'] = "Fornecedor Já Inscrito";
													}
											}
							    }
							}else{
								$_SESSION['Mens']     = 1;
								$_SESSION['Tipo']     = 1;
								$_SESSION['Mensagem'] = "Fornecedor Já Cadastrado. Acesse a Consulta de Acompanhamento de Fornecedor para visualizar o seu cadastro. Caso não possua a senha de acesso procurar a Divisão de Credenciamento - DCF no 11º andar da Prefeitura do Recife, no Cais do Apolo, 925 - Bairro do Recife - Recife/PE";
							}
					}
					if($_SESSION['Mens'] == 0 and $_SESSION['TipoHabilitacao']=="L"){
						if($_SESSION['CEP']==""){
							//if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CEP.focus();\" class=\"titulo2\">Campo 'CEP' deve ser preenchido</a>";
						}elseif(!is_numeric($_SESSION['CEP'])){
							//if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CEP.focus();\" class=\"titulo2\">Campo 'CEP' deve ser número</a>";
						}elseif(strlen($_SESSION['CEP'])!=8){
							//if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CEP.focus();\" class=\"titulo2\">Campo 'CEP' deve conter 8 dígitos</a>";
						}else{
						  $db = Conexao();
							$sql  = "SELECT CCEPPOCODI, NCEPPOLOGR, NCEPPOBAIR, NCEPPOTIPO, CCEPPOESTA, ";
							$sql .= "       NCEPPOCOMP, NCEPPOCIDA, CCEPPOREFE, CCEPPOTIPL  ";
							$sql .= "  FROM PPDV.TBCEPLOGRADOUROBR ";
							$sql .= " WHERE CCEPPOCODI = ".$_SESSION['CEP'];
							$res  = $db->query($sql);
							if( PEAR::isError($res) ){
							    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}else{
										$rows = $res->numRows();
										if( $rows == 0 ){
												$sqlloc  = "SELECT CCELOCCODI, NCELOCLOCA, CCELOCESTA, CCELOCTIPO ";
												$sqlloc .= "  FROM PPDV.TBCEPLOCALIDADEBR ";
												$sqlloc .= " WHERE CCELOCCODI = ".$_SESSION['CEP'];
												$resloc  = $db->query($sqlloc);
												if( PEAR::isError($resloc) ){
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlloc");
												}else{
														$rowloc  = $resloc->numRows();
														if( $rowloc == 0 )
														{
																if ($_SESSION['Mens']== 1){
																	$_SESSION['Mensagem'].=", ";
																}
											  				$_SESSION['Mens']       = 1;
											  				$_SESSION['Tipo']       = 2;
									  						$_SESSION['Mensagem']  .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CEP.focus();\" class=\"titulo2\">CEP Válido</a>";

														}
												}
										}
							}
					}

					}
					if($_SESSION['Numero']!=""){
						if(!is_numeric($_SESSION['Numero'])){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Numero.focus();\" class=\"titulo2\">Campo 'Número' deve conter apenas números</a>";
						}
					}
					if($_SESSION['DDD']!=""){
						if(!is_numeric($_SESSION['DDD'])){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.DDD.focus();\" class=\"titulo2\">Campo 'DDD' deve conter apenas números</a>";
						}elseif(strlen($_SESSION['DDD'])!=3){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.DDD.focus();\" class=\"titulo2\">Campo 'DDD' deve conter 3 dígitos</a>";
						}
					}
					if( $_SESSION['Mens'] == 0 ){

							# Verifica a existência do CPF/CNPJ no Cadastro da Prefeitura #
							if( $_SESSION['Irregularidade'] == "" ){
									$NomePrograma = urlencode("CadGestaoFornecedorIncluir.php");
									$Url = "fornecedores/RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&TipoDoc=".$_SESSION['TipoDoc']."&Destino=".$_SESSION['DestinoCons']."&CPF_CNPJ=".$_SESSION['CPF_CNPJ']."";
									if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
									//Redireciona($Url);
									//exit;
                             
							}

									if( $_SESSION['Irregularidade'] == "S" ){
											$_SESSION['Mens']     = 1;
											$_SESSION['Tipo']     = 1;
											$_SESSION['Mensagem'] = "Fornecedor possui alguma irregularidade com a Prefeitura, deve procurar o Centro de Atendimento ao Contribuinte - CAC da Prefeitura - térreo - no Cais do Apolo, 925 - Bairro do Recife/PE e após a solução das pendências tentar executar a Inscrição novamente";
									}else{

                                                if( ($_SESSION['CEP'] == "" or is_null($_SESSION['CEP'])) and $_SESSION['TipoHabilitacao']=="L") {
                                                    if( $_SESSION['Mens']== 1 ){$_SESSION['Mensagem'].=", ";}
                                                    $_SESSION['Mens']      = 1;
                                                    $_SESSION['Tipo']      = 2;
                                                        $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CEP.focus();\" class=\"titulo2\">CEP</a>";
                                                }else{

													if( $_SESSION['Botao'] == "Preencher" and $_SESSION['CEP'] != "" ){
															if( $_SESSION['Mens'] == 0 ){
																	# Seleciona o Endereço de acordo com o CEP informado #
																	$_SESSION['CEPAntes'] = $_SESSION['CEP'];
								     							$sql  = "SELECT CCEPPOCODI, NCEPPOLOGR, NCEPPOBAIR, NCEPPOTIPO, CCEPPOESTA, ";
																	$sql .= "       NCEPPOCOMP, NCEPPOCIDA, CCEPPOREFE, CCEPPOTIPL ";
																	$sql .= "  FROM PPDV.TBCEPLOGRADOUROBR ";
																	$sql .= " WHERE CCEPPOCODI = ".$_SESSION['CEP'];
																	$res  = $db->query($sql);
																	if( PEAR::isError($res) ){
																	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	}else{

																			$rows = $res->numRows();
																			if( $rows == 0 ){
																					$sqlloc  = "SELECT CCELOCCODI, NCELOCLOCA, CCELOCESTA, CCELOCTIPO ";
																					$sqlloc .= "  FROM PPDV.TBCEPLOCALIDADEBR ";
																					$sqlloc .= " WHERE CCELOCCODI = ".$_SESSION['CEP'];
																					$resloc  = $db->query($sqlloc);
																					if( PEAR::isError($resloc) ){
																					    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlloc");
																					}else{
																							$rowloc  = $resloc->numRows();
																							if( $rowloc == 0 ){
																									if ($_SESSION['Mens']== 1){$_SESSION['Mensagem'].=", ";}
																					  			$_SESSION['Mens']       = 1;
																					  			$_SESSION['Tipo']       = 2;
																			  					$_SESSION['Mensagem']  .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CEP.focus();\" class=\"titulo2\">CEP Válido</a>";
																			  					$_SESSION['Logradouro'] = "";
																									$_SESSION['Bairro']     = "";
																									$_SESSION['Cidade']     = "";
																									$_SESSION['UF']         = "";
																									$_SESSION['Localidade'] = "";
																							}else{
																									$LinhaLoc               = $resloc->fetchRow();
																									$_SESSION['CEP']        = $LinhaLoc[0];
																									$_SESSION['Cidade']     = $LinhaLoc[1];
																									$_SESSION['UF']         = $LinhaLoc[2];
																									$_SESSION['Localidade'] = "S";
																							}
																					}
																			}else{
																					$linha           = $res->fetchRow();
																					$_SESSION['CEP'] = $linha[0];
																					if( $linha[3] != "" and $linha[5] != "" ){
																							$_SESSION['Logradouro'] = $linha[3]." ".$linha[1]." ".$linha[5];
																					}else{
																							if( $linha[3] != "" and  $linha[5] == ""  ){
																									$_SESSION['Logradouro'] = $linha[3]." ".$linha[1];
																							}elseif( $linha[3] == "" and  $linha[5] != ""  ){
																									$_SESSION['Logradouro'] = $linha[1]." ".$linha[5];
																							}else{
																									$_SESSION['Logradouro'] = $linha[1];
																							}
																					}
																					$_SESSION['Bairro']     = $linha[2];
																					$_SESSION['Cidade']     = $linha[6];
																					$_SESSION['UF']         = $linha[4];
																					$_SESSION['Localidade'] = "";
																			}
																	}
																	//colocar 8 digitos no CEP
																	while(strlen($_SESSION['CEP'])<8){
																		$_SESSION['CEP'] = "0".$_SESSION['CEP'];
																	}
															}
													}else{
															if( $_SESSION['CEPAntes'] != $_SESSION['CEP'] ){
																	if( $_SESSION['CEPAntes'] != "" ){
																			if ($_SESSION['Mens']== 1){$_SESSION['Mensagem'].=", ";}
															  			$_SESSION['Mens']      = 1;
															  			$_SESSION['Tipo']      = 2;
															  			$_SESSION['Virgula']   = 2;
																			$_SESSION['Mensagem']  = "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CEP.focus();\" class=\"titulo2\">O CEP Informado não corresponde ao Endereço, clique no Botão \"Preencher Endereço\" para atualizar o Endereço</a>";
																			$_SESSION['Botao']     = "";
																	}else{
																			if ($_SESSION['Mens']== 1){$_SESSION['Mensagem'].=", ";}
															  			$_SESSION['Mens']     = 1;
															  			$_SESSION['Tipo']     = 2;
															  			$_SESSION['Mensagem'] = "<font class=\"titulo2\">Clique no Botão \"Preencher Endereço\" para atualizar os campos do Endereço</font>";
																	}
															}
													}
											}
											if( $_SESSION['Mens'] == 0 and $_SESSION['Botao'] != "Preencher" ){
												if( $_SESSION['RazaoSocial'] == ""  ){
													if( $_SESSION['Mens'] == 1){$_SESSION['Mensagem'].=", ";}
													$_SESSION['Mens']      = 1;
													$_SESSION['Tipo']      = 2;
													if($_SESSION['TipoCnpjCpf'] == "CPF"){
														$nomeStr = "Nome";
													}else{
														$nomeStr = "Razão Social";
													}
													$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.RazaoSocial.focus();\" class=\"titulo2\">".$nomeStr."</a>";
												}
												
												if($_SESSION['TipoHabilitacao']=="L"){
													if($_SESSION['TipoCnpjCpf'] == "CPF"){ //é pessoa física...
														if( $_SESSION['Identidade'] == ""){
																if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
																$_SESSION['Mens']      = 1;
																$_SESSION['Tipo']      = 2;
																$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Identidade.focus();\" class=\"titulo2\">Identidade</a>";
														}else if ( !is_numeric($_SESSION['Identidade'])){
																if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
																$_SESSION['Mens']      = 1;
																$_SESSION['Tipo']      = 2;
																$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Identidade.focus();\" class=\"titulo2\">Identidade deve ser número</a>";
														}

														if( $_SESSION['OrgaoUF'] == ""){
																if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
																$_SESSION['Mens']      = 1;
																$_SESSION['Tipo']      = 2;
																$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.OrgaoUF.focus();\" class=\"titulo2\">Órgao Emissor/UF</a>";
														}
													}

													if( $_SESSION['Localidade'] == "S"  ){
															if( $_SESSION['Logradouro'] == ""  ){
																	if( $_SESSION['Mens'] == 1){$_SESSION['Mensagem'].=", ";}
														  		$_SESSION['Mens']      = 1;
														  		$_SESSION['Tipo']      = 2;
																	$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Logradouro.focus();\" class=\"titulo2\">Logradouro</a>";
															}
													}
													if( $_SESSION['Numero'] != "" and ! SoNumeros($_SESSION['Numero'])){
															if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
															$_SESSION['Mens']      = 1;
															$_SESSION['Tipo']      = 2;
															$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Numero.focus();\" class=\"titulo2\">Número Válido</a>";
													}
													if( $_SESSION['Localidade'] == "S"  ){
															if( $_SESSION['Bairro'] == ""  ){
																	if( $_SESSION['Mens'] == 1){$_SESSION['Mensagem'].=", ";}
														  		$_SESSION['Mens']      = 1;
														  		$_SESSION['Tipo']      = 2;
																	$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Bairro.focus();\" class=\"titulo2\">Bairro</a>";
															}
													}
												}

													if( $_SESSION['RegistroJunta'] == "" ){
														if($_SESSION['TipoCnpjCpf'] == "CNPJ"){
															if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
															$_SESSION['Mens']      = 1;
															$_SESSION['Tipo']      = 2;
															$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.RegistroJunta.focus();\" class=\"titulo2\">Registro na Junta Comercial ou Cartório</a>";
														}
													}else{
															if( ! SoNumeros($_SESSION['RegistroJunta']) ){
																	if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
																	$_SESSION['Mens']      = 1;
																	$_SESSION['Tipo']      = 2;
																	$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.RegistroJunta.focus();\" class=\"titulo2\">Registro na Junta Comercial ou Cartório Válido</a>";
															}else{
																	if( $_SESSION['RegistroJunta'] > 9223372036854775807 ){
																			if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
																			$_SESSION['Mens']      = 1;
																			$_SESSION['Tipo']      = 2;
																			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.RegistroJunta.focus();\" class=\"titulo2\">Registro na Junta Comercial maior que 0 e menor que 9.223.372.036.854.775.807</a>";
																	}
															}
													}
													if( $_SESSION['DataRegistro'] == "" ){
														if($_SESSION['TipoCnpjCpf'] == "CNPJ"){
															if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
															$_SESSION['Mens']      = 1;
															$_SESSION['Tipo']      = 2;
															$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.DataRegistro.focus();\" class=\"titulo2\">Data de Registro na Junta Comercial ou Cartório</a>";
														}
													}else{
															$MensErro = ValidaData($_SESSION['DataRegistro']);
															if( $MensErro != "" ){
																	if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
																	$_SESSION['Mens']      = 1;
																	$_SESSION['Tipo']      = 2;
																	$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.DataRegistro.focus();\" class=\"titulo2\">Data Válida</a>";
															}else{
																	$Hoje = date("Ymd");
																	$Data = substr($_SESSION['DataRegistro'],-4).substr($_SESSION['DataRegistro'],3,2).substr($_SESSION['DataRegistro'],0,2);
																	if( $Data > $Hoje ){
																			if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
																			$_SESSION['Mens']      = 1;
																			$_SESSION['Tipo']      = 2;
																			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.DataRegistro.focus();\" class=\"titulo2\">Data Inferior ou Igual a Data Atual</a>";
																	}
															}
													}

													if( $_SESSION['Email'] == "" and $_SESSION['TipoHabilitacao']=="L"){
															if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
															$_SESSION['Mens']      = 1;
															$_SESSION['Tipo']      = 2;
															$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Email.focus();\" class=\"titulo2\">E-mail 1</a>";
													} else {
														if( $_SESSION['Email'] != "" and ! strchr($_SESSION['Email'], "@") ){
											    				if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
											    				$_SESSION['Mens']      = 1;
											    				$_SESSION['Tipo']      = 2;
									    			 			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Email.focus();\" class=\"titulo2\">E-mail válido no campo 'E-mail 1'</a>";
														}
													}
													if(  $_SESSION['Email2'] != "" ){
														if( ! strchr($_SESSION['Email2'], "@") ){
											    				if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
											    				$_SESSION['Mens'] = 1;
											    				$_SESSION['Tipo'] = 2;
									    			 			$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Email2.focus();\" class=\"titulo2\">E-mail válido no campo 'E-mail 2'</a>";
														}
													}
													if( $_SESSION['CPFContato'] != "" ){
													  	$Qtd = strlen($_SESSION['CPFContato']);
													  	if( ($Qtd != 11) and ($Qtd != 0) ){
													      	if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
																	$_SESSION['Mens']      = 1;
																	$_SESSION['Tipo']      = 2;
																	$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CPFContato.focus();\" class=\"titulo2\">CPF do Contato com 11 números</a>";
															}else{
															  	$cpfcnpj = valida_CPF($_SESSION['CPFContato']);
																	if( $cpfcnpj === false ){
																			if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
																	  	$_SESSION['Mens']      = 1;
																	  	$_SESSION['Tipo']      = 2;
												  						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CPFContato.focus();\" class=\"titulo2\">CPF do Contato Válido</a>";
												  				}
													  	}
													}
											}
									}

					}
					$db->disconnect();
			}
	}
	# Sócio- INICIO
	if( $_SESSION['Botao'] == "AdicionarSocio" ){
		$_SESSION['MostrarNovoSocio'] = true; // no caso de erro devem ser mostrados de novo os dados

		if($_SESSION['NoSocios'] ==""){
			$_SESSION['NoSocios'] = 0;
		}
		if( $_SESSION['SocioNovoNome']=="" ){
			if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
			$_SESSION['Mens']     = 1;
			$_SESSION['Tipo']     = 2;
			$_SESSION['Mensagem'].= "<a href='javascript:document.CadGestaoFornecedorIncluir.SocioNovoNome.focus();' class='titulo2'>Nome do sócio</a>";
		}elseif ($_SESSION['SocioNovoCPF']==""){
			if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
			$_SESSION['Mens']     = 1;
			$_SESSION['Tipo']     = 2;
			$_SESSION['Mensagem'].= "<a href='javascript:document.CadGestaoFornecedorIncluir.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ do sócio</a>";
		}elseif (strlen($_SESSION['SocioNovoCPF'])!=11 and strlen($_SESSION['SocioNovoCPF'])!=14){
			if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
			$_SESSION['Mens']     = 1;
			$_SESSION['Tipo']     = 2;
			$_SESSION['Mensagem'].= "<a href='javascript:document.CadGestaoFornecedorIncluir.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ de sócio válido</a>";
		}elseif ($_SESSION['SociosCPF_CNPJ']!=0 and in_array($_SESSION['SocioNovoCPF'], $_SESSION['SociosCPF_CNPJ'] )){
			if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
			$_SESSION['Mens']     = 1;
			$_SESSION['Tipo']     = 2;
			$_SESSION['Mensagem'].= "<a href='javascript:document.CadGestaoFornecedorIncluir.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ de sócio não pode ser repetido</a>";
		}elseif ( (strlen($_SESSION['SocioNovoCPF'])==11 and !valida_CPF($_SESSION['SocioNovoCPF'])) or (strlen($_SESSION['SocioNovoCPF'])==14 and !valida_CNPJ($_SESSION['SocioNovoCPF'])) ){
			if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'].=", "; }
			$_SESSION['Mens']     = 1;
			$_SESSION['Tipo']     = 2;
			$_SESSION['Mensagem'].= "<a href='javascript:document.CadGestaoFornecedorIncluir.SocioNovoCPF.focus();' class='titulo2'>CPF/CNPJ de sócio válido</a>";
		}else{
			$_SESSION['SociosCPF_CNPJ'][ $_SESSION['NoSocios'] ] = $_SESSION['SocioNovoCPF'];
			$_SESSION['SociosNome'][ $_SESSION['NoSocios'] ] = $_SESSION['SocioNovoNome'];
			$_SESSION['NoSocios'] ++;
			$_SESSION['MostrarNovoSocio'] = false;
		}
	}elseif( $_SESSION['Botao'] == "RemoverSocio" ){
		$_SESSION['SociosNome'][ $_SESSION['SocioSelecionado'] ] = NULL;
		$_SESSION['SociosCPF_CNPJ'][ $_SESSION['SocioSelecionado'] ] = NULL;
	}
	# Sócio- FIM

	if( ($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Preencher" ) or ($_SESSION['Botao'] == "Limpar" ) ){
			ExibeAbaHabilitacao();
	}
}

# Exibe Aba Habilitacao - Formulário A #
function ExibeAbaHabilitacao(){
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
	 	document.CadGestaoFornecedorIncluir.Destino.value = Destino;
	 	document.CadGestaoFornecedorIncluir.submit();
	}
	function enviar(valor){
		document.CadGestaoFornecedorIncluir.Botao.value = valor;
		document.CadGestaoFornecedorIncluir.submit();
	}
	<?php  MenuAcesso(); ?>
	//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadGestaoFornecedorIncluir.php" method="post" name="CadGestaoFornecedorIncluir">
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
					<?php  if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],$_SESSION['Virgula']); }?>
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
				    					INCLUIR - CADASTRO E GESTÃO DE FORNECEDOR
				          	</td>
				        	</tr>
			  	      	<tr>
			    	      	<td class="textonormal" >
											<p align="justify">
												Para fazer o Cadastro de um Fornecedor, informe os dados abaixo e clique no botão "Incluir" da última aba(Qualificação Técnica). Os itens obrigatórios estão com *.<br>
			        	    		Para preencher o endereço digite o CEP e clique no botão "Preencher Endereço".<br><br>
	        	    		Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
			          	   	</p>
			          		</td>
				        	</tr>
				        	<tr>
									<td align="left">
										<?php  echo NavegacaoAbas(on,off,off,off, off); ?>
										<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr bgcolor="#bfdaf2">
												<td colspan="4">
								          <table class="textonormal" border="0" align="left" summary="">
														<tr>
															<td class="textonormal">
																<input type="radio" name="TipoCnpjCpf" value="CPF" <?php  if( $_SESSION['TipoCnpjCpf'] == "" or $_SESSION['TipoCnpjCpf'] == "CPF" ){ echo "checked"; }?> onclick="document.CadGestaoFornecedorIncluir.Critica.value=1;javascript:submit();"> CPF*
																<input type="radio" name="TipoCnpjCpf" value="CNPJ" <?php  if( $_SESSION['TipoCnpjCpf'] == "CNPJ" ){ echo "checked"; }?> onclick="document.CadGestaoFornecedorIncluir.Critica.value=1;javascript:submit();">CNPJ
					          	    		</td>
					          	    		<td class="textonormal">
					          	    			<input type="text" name="CPF_CNPJ" size="15" maxlength="14" value="<?php  echo $_SESSION['CPF_CNPJ'];?>" class="textonormal">
					          	    		</td>
					            			</tr>
														<tr>
															<td class="textonormal">Tipo de Habilitação*</td>
															<td class="textonormal">
																<select name="TipoHabilitacao" class="textonormal" onchange="document.CadGestaoFornecedorIncluir.submit()">
																	<option value="D" <?php  if( $_SESSION['TipoHabilitacao'] == "D" ){ echo "selected"; }?>>COMPRA DIRETA</option>
																	<option value="L" <?php  if( $_SESSION['TipoHabilitacao'] != "D" ){ echo "selected"; }?>>LICITAÇÃO</option>
																</select>
															</td>
														</tr>
														<?php 
														$asteriscoParaLicitacao="*"; 
														if($_SESSION['TipoHabilitacao']!="L"){
															$asteriscoParaLicitacao = "";
														}
														
														if( $_SESSION['TipoCnpjCpf'] == "CNPJ" ){?>
															  <tr>
											                  <td class="textonormal"><?php  echo getDescPorteEmpresaTitulo() ?></td>
											                  <td class="textonormal"><?php  echo comboBoxDescPorteEmpresa($_SESSION['MicroEmpresa']) ?> </td> 
															  </tr>
														<?php  } ?>
					            			<tr>
								              <td class="textonormal">
								              	<?php  if( $_SESSION['TipoCnpjCpf'] == "CNPJ" ){ echo "Identidade do Representante Legal\n"; }else{ echo "Identidade".$asteriscoParaLicitacao."\n"; } ?>
								              </td>
								              <td class="textonormal">
								              	<input type="text" name="Identidade" size="17" maxlength="14" value="<?php  echo $_SESSION['Identidade'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">
								              	<?php  if( $_SESSION['TipoCnpjCpf'] == "CNPJ" ){ echo "Órgão Emissor/UF\n"; }else{ echo "Órgão Emissor/UF".$asteriscoParaLicitacao."\n"; } ?>
								              </td>
								              <td class="textonormal">
								              	<input type="text" name="OrgaoUF" size="17" maxlength="14" value="<?php  echo $_SESSION['OrgaoUF'];?>" class="textonormal">
															</td>
								            </tr>

								            <tr>
								              <td class="textonormal">
								              	<?php  if(  $_SESSION['TipoCnpjCpf'] == "CNPJ" ){ echo "Razão Social*\n"; }else{ echo "Nome*\n"; } ?>
								              </td>
								              <td class="textonormal">
								              	<input type="text" name="RazaoSocial" size="45" maxlength="120" value="<?php  echo $_SESSION['RazaoSocial'];?>" class="textonormal">
								              	<input type="hidden" name="Critica" size="1" value="2">
					            	  			<input type="hidden" name="Origem" value="A">
																<input type="hidden" name="Destino">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Nome Fantasia </td>
								              <td class="textonormal">
								              	<input type="text" name="NomeFantasia" size="45" maxlength="80" value="<?php  echo $_SESSION['NomeFantasia'] ?>" class="textonormal">
								              </td>
								            </tr>
								            <tr>
								              <td class="textonormal">CEP<?php echo $asteriscoParaLicitacao?></td>
															<td class="textonormal">
																<input type="text" name="CEP" size="8" maxlength="8" value="<?php  echo $_SESSION['CEPInformado'] ?>" class="textonormal">
								            		<input type="hidden" name="CEPAntes" size="8" maxlength="8" value="<?php  echo $_SESSION['CEPAntes']?>" class="textonormal">
								            		<input type="button" value="Preencher Endereço" class="botao" onclick="javascript:enviar('Preencher')">
								            	</td>
								            </tr>
														<tr>
								              <td class="textonormal">Logradouro<?php echo $asteriscoParaLicitacao?></td>
								              <td class="textonormal">
								              	<input type="text" name="Logradouro" size="45" maxlength="100" value="<?php  echo $_SESSION['Logradouro']; ?>" <?php  if( $_SESSION['Localidade'] == "" ){ echo "onFocus=\"document.CadGestaoFornecedorIncluir.Numero.focus()\" class=\"endereco\""; }else{ echo "class=\"textonormal\""; }?>>
								              </td>
								            </tr>
														<tr>
								              <td class="textonormal">Número</td>
								              <td class="textonormal">
								              	<input type="text" name="Numero" size="5" maxlength="5" value="<?php  echo $_SESSION['Numero']; ?>" class="textonormal">
								              </td>
								            </tr>
														<tr>
								              <td class="textonormal">Complemento </td>
								              <td class="textonormal">
								              	<input type="text" name="Complemento" size="33" maxlength="20" value="<?php  echo $_SESSION['Complemento']; ?>" class="textonormal">
								              </td>
								            </tr>
														<tr>
								              <td class="textonormal">Bairro<?php echo $asteriscoParaLicitacao?></td>
								              <td class="textonormal">
								              	<input type="text" name="Bairro" size="33" maxlength="30" value="<?php  echo $_SESSION['Bairro'] ?>" <?php  if( $_SESSION['Localidade'] == "" ){ echo "onFocus=\"document.CadGestaoFornecedorIncluir.Numero.focus()\" class=\"endereco\""; }else{ echo "class=\"textonormal\""; }?>>
								              </td>
								            </tr>
														<tr>
								              <td class="textonormal">Cidade<?php echo $asteriscoParaLicitacao?></td>
								              <td class="textonormal">
								              	<input type="text" name="Cidade" size="33" maxlength="30" value="<?php  echo $_SESSION['Cidade'] ?>" onFocus="document.CadGestaoFornecedorIncluir.Numero.focus()" class="endereco">
								              </td>
								            </tr>
								            <tr>
								              <td class="textonormal">UF<?php echo $asteriscoParaLicitacao?></td>
						    	      				<td class="textonormal">
						    	      					<input type="text" name="UF" size="2" maxlength="2" value="<?php  echo $_SESSION['UF'] ?>" onFocus="document.CadGestaoFornecedorIncluir.Numero.focus()" class="endereco">
						    	      				</td>
								            </tr>
								            <tr>
								              <td class="textonormal">DDD </td>
								              <td class="textonormal">
								              	<input type="text" name="DDD" size="2" maxlength="3" value="<?php  echo $_SESSION['DDD'];?>" class="textonormal">
								              </td>
								            </tr>
								            <tr>
								              <td class="textonormal">Telefone(s) </td>
															<td class="textonormal">
																<input type="text" name="Telefone" size="33" maxlength="30" value="<?php  echo $_SESSION['Telefone'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">E-mail 1<?php echo $asteriscoParaLicitacao?></td>
										<td class="textonormal">
											<input type="text" name="Email" size="45" maxlength="60" value="<?php  echo $_SESSION['Email'];?>" class="textonormal">
										</td>
								            </tr>
								            <tr>
								              <td class="textonormal">E-mail 2</td>
										<td class="textonormal">
											<input type="text" name="Email2" size="45" maxlength="60" value="<?php  echo $_SESSION['Email2'];?>" class="textonormal">
										</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Fax</td>
															<td class="textonormal">
																<input type="text" name="Fax" size="27" maxlength="25" value="<?php  echo $_SESSION['Fax'];?>" class="textonormal">
															</td>
								            </tr>
														<tr>
														<td class="textonormal">Registro Junta Comercial ou Cartório<?php  if($_SESSION['TipoCnpjCpf'] == "CNPJ"){ echo "*"; } ?></td>
															<td class="textonormal">
																<input type="text" name="RegistroJunta" size="12" maxlength="11" value="<?php  echo $_SESSION['RegistroJunta'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal" width="45%">Data Reg. Junta Comercial ou Cartório aaaaaaaa<?php  if($_SESSION['TipoCnpjCpf'] == "CNPJ"){ echo "*"; } ?></td>
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
																<input type="text" name="NomeContato" size="45" maxlength="60" value="<?php  echo $_SESSION['NomeContato'];?>" class="textonormal">
															</td>
								            </tr>
 														<tr>
								              <td class="textonormal">CPF do Contato</td>
															<td class="textonormal">
																<input type="text" name="CPFContato" size="12" maxlength="11" value="<?php  echo $_SESSION['CPFContato'];?>" class="textonormal">
															</td>
								            </tr>
														<tr>
								              <td class="textonormal">Cargo do Contato</td>
															<td class="textonormal">
																<input type="text" name="CargoContato" size="45" maxlength="60" value="<?php  echo $_SESSION['CargoContato'];?>" class="textonormal">
															</td>
								            </tr>
														<tr>
								              <td class="textonormal">DDD do Contato</td>
															<td class="textonormal">
																<input type="text" name="DDDContato" size="2" maxlength="3" value="<?php  echo $_SESSION['DDDContato'];?>" class="textonormal">
															</td>
								            </tr>
														<tr>
								              <td class="textonormal" >Telefone do Contato</td>
															<td class="textonormal">
																<input type="text" name="TelefoneContato" size="27" maxlength="25" value="<?php  echo $_SESSION['TelefoneContato'];?>" class="textonormal">
															</td>
								            </tr>
								          </table>
												</td>
											</tr>
										</table>
									</td>
								</tr>


								<?php 
								# SÓCIOS- INÍCIO
								if($_SESSION['TipoCnpjCpf'] == "CNPJ"){
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
																		for($itr=0; $itr<$_SESSION['NoSocios']; $itr++){
																			if (!is_null($_SESSION['SociosCPF_CNPJ'][$itr])){
																		?>

						              					<tr>
						              						<td class="textonormal"><?php  echo $_SESSION['SociosNome'][$itr];?></td>
						              						<td class="textonormal"><?php  echo $_SESSION['SociosCPF_CNPJ'][$itr];?></td>
						              						<td class="textonormal">
						              							<input type="hidden" name="SociosNome[<?php  echo $itr; ?>]" value="<?php  echo $_SESSION['SociosNome'][$itr]; ?>" >
						              							<input type="hidden" name="SociosCPF_CNPJ[<?php  echo $itr; ?>]" value="<?php  echo $_SESSION['SociosCPF_CNPJ'][$itr]; ?>" >
						              							<input type="button" name="RemoverSocio"  class="botao" value="Remover" onClick="javascript:removerSocio(<?php  echo $itr; ?>);">
						              						</td>
						              					</tr>

						              					<?php 
																			}
																		}
						              					?>

						              					<tr>
						              						<td class="textonormal"><input type="text" name="SocioNovoNome" size="81" maxlength="80" value="<?php  if($_SESSION['MostrarNovoSocio']) echo $_SESSION['SocioNovoNome']; ?>" class="textonormal"></td>
						              						<td class="textonormal"><input type="text" name="SocioNovoCPF" size="15" maxlength="14" value="<?php  if($_SESSION['MostrarNovoSocio']) echo $_SESSION['SocioNovoCPF']; ?>" class="textonormal"></td>
						              						<td class="textonormal">
						              							<input type="hidden" name="SocioSelecionado" value="-1">
						              							<input type="hidden" name="NoSocios" value="<?php  echo $_SESSION['NoSocios']; ?>" >
						              							<input type="button" name="AdicionarSocio" class="botao" value="Adicionar" onClick="javascript:enviar('AdicionarSocio');">
						              						</td>
						              					</tr>
						              		</td>

									          	</tr>
														</table>
													</td>
												</tr>
												<tr bgcolor="#bfdaf2" >
													<td>

													</td>
												</tr>
								       </table>
												</td>
											</tr>
										</table>
									</td>
								</tr>

								<?php 
								}
								# SÓCIOS- FIM
								?>


					      <tr>
					        <td colspan="4" align="right">
			            	<input type="button" value="Próxima Aba" class="botao" onclick="javascript:enviar('A');">
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
	document.CadGestaoFornecedorIncluir.CPF_CNPJ.focus();
	//-->
	</script>
	</body>
	</html>
	<?php 
	exit;
}

# Critica aos Campos - Formulário B #
function CriticaAbaRegularidadeFiscal(){
	if( $_SESSION['Botao'] == "Limpar" ){
			$_SESSION['Botao']         = "";
			$_SESSION['InscEstadual']  = "";
			$_SESSION['InscMercantil'] = "";
			$_SESSION['InscOMunic']    = "";
			if( $_SESSION['DataCertidaoOb'] != 0 ){
					$_SESSION['DataCertidaoOb'] = array_fill(5, count($_SESSION['DataCertidaoOb']), '');
			}
			if( $_SESSION['DataCertidaoOb'] != 0 ){
					$_SESSION['DataCertidaoOp'] = array_fill(5, count($_SESSION['DataCertidaoOp']), '');
			}
			ExibeAbaRegularidadeFiscal();
	}elseif($_SESSION['Botao'] == "RetirarComplementar"){
			if( count($_SESSION['CertidaoComplementar']) != 0 ){
                $QtdOpcionais = 0;
					for( $i=0; $i<count($_SESSION['CertidaoComplementar']); $i++ ){
							if( $_SESSION['CheckComplementar'][$i] == "" ){
									$QtdOpcionais++;
									$_SESSION['CheckComplementar'][$i] = "";
									$_SESSION['CertidaoComplementar'][$QtdOpcionais-1] = $_SESSION['CertidaoComplementar'][$i];
									$_SESSION['DataCertidaoOp'][$QtdOpcionais-1]   = $_SESSION['DataCertidaoOp'][$i];
							}
					}
					$_SESSION['CertidaoComplementar'] = array_slice($_SESSION['CertidaoComplementar'],0,$QtdOpcionais);
					$_SESSION['DataCertidaoOp']       = array_slice($_SESSION['DataCertidaoOp'],0,$QtdOpcionais);
					if( count($_SESSION['CertidaoComplementar']) == 1 and count($_SESSION['CertidaoComplementar']) == "" ){
							unset($_SESSION['CertidaoComplementar']);
					}
					if( count($_SESSION['DataCertidaoOp']) == 1 and count($_SESSION['DataCertidaoOp']) == "" ){
							unset($_SESSION['DataCertidaoOp']);
					}
					$_SESSION['Certidao'] = "";
			}
			ExibeAbas("B");
	}else{
			$_SESSION['Mens']     = "";
			$_SESSION['Mensagem'] = "Informe: ";


			if( $_SESSION['TipoHabilitacao'] == "L" ){
				# Verifica se as duas Inscrições estão vazias #
				if( $_SESSION['InscMercantil'] == "" and $_SESSION['InscOMunic'] == "" and $_SESSION['InscEstadual'] == "" ){
						if ( $_SESSION['Mens'] == 1 ) { $_SESSION['Mensagem'] .= ", "; }
						$_SESSION['Mens']      = 1;
						$_SESSION['Tipo']      = 2;
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.InscMercantil.focus();\" class=\"titulo2\">Inscrição Municipal Recife ou Inscrição de Outro Munícipio ou Inscrição Estadual</a>";
				}else{
						# Verifica se as duas Inscrições estão preenchidas #
						if( $_SESSION['InscMercantil'] != "" and $_SESSION['InscOMunic'] != "" ){
								if ( $_SESSION['Mens'] == 1 ) { $_SESSION['Mensagem'] .= ", "; }
								$_SESSION['Mens']      = 1;
								$_SESSION['Tipo']      = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.InscMercantil.focus();\" class=\"titulo2\">Inscrição Municipal Recife ou Inscrição de Outro Munícipio</a>";
						}else{
								# Verifica se a Inscrição Municipal é Númerica #
								if( ($_SESSION['InscOMunic'] != "") and (! SoNumeros($_SESSION['InscOMunic'])) ){
										if ( $_SESSION['Mens'] == 1 ) { $_SESSION['Mensagem'] .= ", "; }
										$_SESSION['Mens']      = 1;
										$_SESSION['Tipo']      = 2;
										$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.InscOMunic.focus();\" class=\"titulo2\">Inscrição de Outro Município Válida</a>";
								}
								if( $_SESSION['InscMercantil'] != "" ){
										# Verifica se a Inscrição Municipal Recife é Númerica #
										if( ! SoNumeros($_SESSION['InscMercantil']) ){
												if ( $_SESSION['Mens'] == 1 ) { $_SESSION['Mensagem'] .= ", "; }
												$_SESSION['Mens']      = 1;
												$_SESSION['Tipo']      = 2;
												$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.InscMercantil.focus();\" class=\"titulo2\"> Inscrição Municipal Recife Válida</a>";
										}else{
												# Pesquisa se Inscrição Municipal Recife é Válida no Banco de Dados #
												if( $_SESSION['InscricaoValida'] == "" ){
														$NomePrograma = urlencode("CadGestaoFornecedorIncluir.php");
														$Url = "fornecedores/RotConsultaInscricaoMercantil.php?NomePrograma=$NomePrograma&InscricaoMercantil=".$_SESSION['InscMercantil']."&Destino=".$_SESSION['DestinoInsc']."";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														//Redireciona($Url);
														//exit;
												}else{
														if( $_SESSION['InscricaoValida'] == "N" ){
																if ( $_SESSION['Mens'] == 1 ) { $_SESSION['Mensagem'] .= ", "; }
																$_SESSION['Mens']      = 1;
																$_SESSION['Tipo']      = 2;
																$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.InscMercantil.focus();\" class=\"titulo2\"> Inscrição Municipal Recife Válida</a>";
														}
												}
										}
								}
						}
				}
			}
			if( $_SESSION['InscEstadual'] != "" and ( ! SoNumeros($_SESSION['InscEstadual']) ) ){
					if ( $_SESSION['Mens'] == 1 ) { $_SESSION['Mensagem'] .= ", "; }
					$_SESSION['Mens']      = 1;
					$_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.InscEstadual.focus();\" class=\"titulo2\"> Inscrição Estadual Válida</a>";
			}

			# Criando o Array de Certidões Opcionais #
			if( $_SESSION['Certidao'] != "" ){
					if( ! isset($_SESSION['CertidaoComplementar'])){
							$_SESSION['CertidaoComplementar'] = array();
					}
					if( $_SESSION['CertidaoComplementar'] == "" || ! in_array( $_SESSION['Certidao'],$_SESSION['CertidaoComplementar'] ) ){
							$_SESSION['CertidaoComplementar'][ count($_SESSION['CertidaoComplementar']) ] = $_SESSION['Certidao'];
					}
			}

			if( $_SESSION['TipoHabilitacao'] == "L" ){
				# Verifica se as Data de Certdão Obrigatória estão vazias #
				if( $_SESSION['DataCertidaoOb'] != 0 ){
                    $cont = 0;
						for( $i=0;$i < count($_SESSION['DataCertidaoOb']);$i++) {
								if( $_SESSION['DataCertidaoOb'][$i] == "" ){
										$cont++;
										if( $cont == 1 ){ $PosOb = $i; }
										//não verificar se for data do último balanço não informada (Heraldo) 23/01/2014
										if   ($i < 7) 	$ExisteDataOb = "N";
										if   ( ( $i == 7) and (empty($_SESSION['MicroEmpresa']))  ) 	$ExisteDataOb = "N";

								}
						}
						if( $ExisteDataOb == "N" ){
								if ( $_SESSION['Mens'] == 1 ) { $_SESSION['Mensagem'] .= ", "; }
								$PosOb = ( $PosOb * 2 ) + 5;
								$_SESSION['Mens']      = 1;
								$_SESSION['Tipo']      = 2;
								$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.elements[$PosOb].focus();\" class=\"titulo2\"> Data(s) de Validade da(s) Certidão(ões) Obrigatória(s)</a>";
						}
				}
			}

			# Verifica se as Data de Certdão Complementar estão vazias #
			if( $_SESSION['DataCertidaoOp'] != 0 ){
					for ( $i=0;$i< count($_SESSION['DataCertidaoOp']);$i++) {
							if( $_SESSION['DataCertidaoOp'][$i] == "" ){
									$cont++;
									if( $cont == 1 ){ $PosOp = $i + 1; }
									$ExisteDataOp = "N";
							}
					}
					if( $ExisteDataOp == "N" ){
							if ( $_SESSION['Mens'] == 1 ) { $_SESSION['Mensagem'] .= ", "; }
							$PosOb = count($_SESSION['DataCertidaoOb']);
							$PosOp = ( $PosOp * 2 ) + $PosOb + 8;
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.elements[$PosOp].focus();\" class=\"titulo2\"> Data de Validade da Certidão Complementar</a>";
					}
			}
	}
	if( ($_SESSION['Mens'] != "" ) or ($_SESSION['Botao'] == "Limpar" ) ){
			ExibeAbaRegularidadeFiscal();
	}
}

# Exibe Aba Regularidade Fiscal - Formulário B #
function ExibeAbaRegularidadeFiscal(){
?>
	<html>
	<?php 
	# Carrega o layout padrão #
	layout();
	?>
	<script language="JavaScript" src="../janela.js" type="text/javascript"></script>
	<script language="javascript" type="">
	<!--
	function Submete(Destino) {
	 	document.CadGestaoFornecedorIncluir.Destino.value = Destino;
	 	document.CadGestaoFornecedorIncluir.submit();
	}
	function enviar(valor){
		document.CadGestaoFornecedorIncluir.Botao.value = valor;
		document.CadGestaoFornecedorIncluir.submit();
	}
	function AbreJanela(url,largura,altura) {
		window.open(url,'pagina','status=no,scrollbars=no,left=20,top=150,width='+largura+',height='+altura);
	}
	<?php  MenuAcesso(); ?>
	//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadGestaoFornecedorIncluir.php" method="post" name="CadGestaoFornecedorIncluir">
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
	  		<?php  if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],1);}?>
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
			    					INCLUIR - CADASTRO E GESTÃO DE FORNECEDOR
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal" >
		      	    		<p align="justify">
		        	    		Para continuar a Inscrição, informe os dados abaixo, as datas de todas as certidões fiscais obrigatórias e informe as certidões opcionais, se existirem com suas respectivas datas. Os itens obrigatórios estão com *. Deverá ser informada a Inscrição Municipal Recife de Recife ou a Inscrição de Outro Município.<br>
		        	    		Parar retirar uma ou mais certidão fiscal Complementar selecione a(s) certidão(ões) e clique no botão "Retirar Certidão Complementar".
		          	   	</p>
		          		</td>
			        	</tr>
			        	<tr>
									<td align="left">
										<?php  echo NavegacaoAbas(off,on,off,off,off); ?>
										<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr bgcolor="#bfdaf2">
												<td colspan="4">
								          <table class="textonormal" border="0" align="left" width="100%" summary="">
														<tr>
															<td class="textonormal" height="20">
																<?php  if( strlen($_SESSION['CPF_CNPJ']) == 14 ){ echo "CNPJ"; }else{ echo "CPF";} ?>
															</td>
					          	    		<td class="textonormal">
						          	    		<?php 
						          	    		if( $_SESSION['CPF_CNPJ'] != "" ){
								          	    		if( strlen($_SESSION['CPF_CNPJ']) == 14 ){
								          	    				echo substr($_SESSION['CPF_CNPJ'],0,2).".".substr($_SESSION['CPF_CNPJ'],2,3).".".substr($_SESSION['CPF_CNPJ'],5,3)."/".substr($_SESSION['CPF_CNPJ'],8,4)."-".substr($_SESSION['CPF_CNPJ'],12,2);
								          	    		}else{
								          	    				echo substr($_SESSION['CPF_CNPJ'],0,3).".".substr($_SESSION['CPF_CNPJ'],3,3).".".substr($_SESSION['CPF_CNPJ'],6,3)."-".substr($_SESSION['CPF_CNPJ'],9,2);
								          	    		}
								          	    }
						          	    		?>
					          	    		</td>
					            			</tr>
								            <tr>
								              <td class="textonormal" height="20">
								              	<?php  if(  $_SESSION['TipoCnpjCpf'] == "CNPJ" ){ echo "Razão Social\n"; }else{ echo "Nome\n"; } ?>
								              </td>
								              <td class="textonormal">
								              	<?php  echo $_SESSION['RazaoSocial']; ?>
					            	  			<input type="hidden" name="Origem" value="B">
																<input type="hidden" name="Destino">
															</td>
								            </tr>
														<tr>
								              <td class="textonormal">Inscrição Municipal Recife<?php  if( $_SESSION['TipoHabilitacao'] == "L" ){ echo "*"; }?></td>
								              <td class="textonormal">
								              	<input type="text" name="InscMercantil" size="20" maxlength="7" value="<?php  echo $_SESSION['InscMercantil'];?>" class="textonormal">
								              </td>
								            </tr>
								            <tr>
								              <td class="textonormal" width="45%">Inscrição Outro Município<?php  if( $_SESSION['TipoHabilitacao'] == "L" ){ echo "*"; }?></td>
															<td class="textonormal">
																<input type="text" name="InscOMunic" size="20" maxlength="19" value="<?php  echo $_SESSION['InscOMunic'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Inscrição Estadual </td>
								              <td class="textonormal">
								              	<input type="text" name="InscEstadual" size="20" maxlength="14" value="<?php  echo $_SESSION['InscEstadual'];?>" class="textonormal">
								              </td>
								            </tr>
														<tr><td><br></td></tr>
														<?php 
														if($_SESSION['TipoHabilitacao']=="L"){
														?>
								            <tr>
								              <td class="textonormal" colspan="2">
																<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
		          			          		<tr>
								              			<td bgcolor="#75ADE6" class="textoabasoff" colspan="2" align="center">CERTIDÃO FISCAL</td>
								              		</tr>
								              		<tr>
								              			<td bgcolor="#DDECF9" class="textonormal" colspan="2" align="center">OBRIGATÓRIA</td>
								              		</tr>
								              		<tr>
																		<td class="textonormal" colspan="2">
								              				<table class="textonormal" border="1" align="left" width="100%" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="">
								              					<tr>
								              						<td bgcolor="#75ADE6" class="textoabasoff">NOME DA CERTIDÃO</td>
								              						<td bgcolor="#75ADE6" class="textoabasoff">DATA DE VALIDADE</td>
								              					</tr>
									              				<?php 
											              		$db  = Conexao();
							  												$sql = "SELECT CTIPCECODI,ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO WHERE FTIPCEOBRI = 'S' ORDER BY CTIPCECODI";
							  												$res = $db->query($sql);
																			  if( PEAR::isError($res) ){
																					  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				}else{
                                                                                  $ob = 0;
																						while( $Linha = $res->fetchRow() ){
												          	      			$ob++;
												          	      			$Descricao  = substr($Linha[1],0,75);
												          	      			$CertidaoOb = $Linha[0];
												          	      			echo "<tr>\n";
												              					echo "	<td class=\"textonormal\" width=\"*\">$Descricao</td>\n";
											              						echo "	<td class=\"textonormal\" width=\"22%\">\n";
											              						$ElementoOb = ( 2 * $ob ) + 3;
											              						$URL = "../calendario.php?Formulario=CadGestaoFornecedorIncluir&Campo=elements[$ElementoOb]";
																			          echo "		<input class=\"textonormal\" type=\"text\" name=\"DataCertidaoOb[$i]\" size=\"10\" maxlength=\"10\" value=\"".$_SESSION['DataCertidaoOb'][$ob-1]."\">\n";
																								echo "		<a href=\"javascript:janela('$URL','Calendario',220,170,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\"></a>\n";
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
								              			<td bgcolor="#DDECF9" class="textonormal" colspan="2" align="center">COMPLEMENTAR</td>
								              		</tr>
								              		<tr>
								              			<td class="textonormal" width="50%">
								              				<?php  if( count($_SESSION['CertidaoComplementar']) > 0 ){ ?>
								              				<table class="textonormal" border="1" align="left" width="100%" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="">
								              					<tr>
								              						<td bgcolor="#75ADE6" class="textoabasoff">&nbsp;</td>
								              						<td bgcolor="#75ADE6" class="textoabasoff">NOME DA CERTIDÃO</td>
								              						<td bgcolor="#75ADE6" class="textoabasoff">DATA DE VALIDADE</td>
								              					</tr>
											              		<?php 
											              		for( $i=0; $i<count($_SESSION['CertidaoComplementar']);$i++ ){
													              		$db  = Conexao();
									  												$sql = "SELECT CTIPCECODI,ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO WHERE CTIPCECODI = ".$_SESSION['CertidaoComplementar'][$i]." ORDER BY CTIPCECODI";
									  												$res = $db->query($sql);
																					  if( PEAR::isError($res) ){
																							  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																						}else{
                                                                                          $op = 0;
																								while( $Linha = $res->fetchRow() ){
														          	      			$op++;
														          	      			$CertidaoOpCodigo = $Linha[0];
														          	      			$Descricao        = substr($Linha[1],0,75);
													              						echo "<tr>\n";
														              					echo "	<td class=\"textonormal\" width=\"5%\">\n";
														              					echo "		<input type=\"checkbox\" name=\"CheckComplementar[$i]\" value=\"$CertidaoOpCodigo\">\n";
														              					echo "	</td>\n";
														              					echo "	<td class=\"textonormal\" width=\"*\">$Descricao</td>\n";
														              					echo "	<td class=\"textonormal\" width=\"22%\">\n";
														              					$ElementoOp = $ElementoOb + 1 + ( 2 * $op );
														              					$URL = "../calendario.php?Formulario=CadGestaoFornecedorIncluir&Campo=elements[$ElementoOp]";
																						        echo "  	<input class=\"textonormal\" type=\"text\" name=\"DataCertidaoOp[$i]\" size=\"10\" maxlength=\"10\" value=\"".$_SESSION['DataCertidaoOp'][$op-1]."\">\n";
																										echo "		<a href=\"javascript:janela('$URL','Calendario',220,170,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\"></a>\n";
														              					echo "	</td>\n";
														              					echo "</tr>\n";
															                	}
																						}
												  	              	$db->disconnect();
											              		}
											              		?>
											              	</table>
								              				<?php  } ?>
								              			</td>
								              		</tr>
								              		<tr>
								              			<td class="textonormal" colspan="2" align="center">
								              				<?php 
																			$Url = "CadIncluirCertidaoComplementar.php?ProgramaOrigem=CadGestaoFornecedorIncluir";
																			if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																			?>
								              				<input class="botao" type="button" value="Incluir Complementar" onclick="javascript:AbreJanela('<?php echo $Url;?>',750,170);">
								              				<input class="botao" type="button" value="Retirar Complementar" onclick="javascript:enviar('RetirarComplementar');">
								              				<input type="hidden" name="Certidao" value="<?php  echo $_SESSION['Certidao'];?>">
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
 	document.CadGestaoFornecedorIncluir.InscMercantil.focus();
	//-->
	</script>
	</body>
	</html>
	<?php 
	exit;
}

# Critica aos Campos - Formulário C #
function CriticaAbaQualificEconFinanceira(){
	if( $_SESSION['Botao'] == "Limpar" ){
			$_SESSION['Botao']            = "";
			$_SESSION['CapSocial']        = "";
			$_SESSION['CapIntegralizado'] = "";
			$_SESSION['Patrimonio']       = "";
			$_SESSION['IndLiqCorrente']   = "";
			$_SESSION['IndLiqGeral']      = "";
			$_SESSION['IndEndividamento'] = "";
			$_SESSION['IndSolvencia']     = "";
			$_SESSION['DataBalanco']      = "";
			$_SESSION['DataNegativa']     = "";
			$_SESSION['DataContratoEstatuto']     = "";
			$_SESSION['Banco1']           = "";
			$_SESSION['Banco2']           = "";
			$_SESSION['Agencia1']         = "";
			$_SESSION['Agencia2']         = "";
			$_SESSION['ContaCorrente1']   = "";
			$_SESSION['ContaCorrente2']   = "";

	}else{
			$_SESSION['Mens']     = "";
			$_SESSION['Mensagem'] = "Informe: ";
			if( $_SESSION['TipoCnpjCpf'] == "CNPJ" and $_SESSION['TipoHabilitacao'] == "L" ){
					if( $_SESSION['CapSocial'] == "" ) {
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						  $_SESSION['Mens']      = 1;
						  $_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CapSocial.focus();\" class=\"titulo2\">Capital Social</a>";
					}else{
							$_SESSION['CapSocial'] = str_replace(".","",$_SESSION['CapSocial']);
							if( ! SoNumVirg($_SESSION['CapSocial']) ){
									if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
								  $_SESSION['Mens']      = 1;
								  $_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CapSocial.focus();\" class=\"titulo2\">Capital Social Válido</a>";
							}else{
									$Numero = Decimal($_SESSION['CapSocial']);
									if( ! $Numero ){
											if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
										  $_SESSION['Mens']      = 1;
										  $_SESSION['Tipo']      = 2;
											$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CapSocial.focus();\" class=\"titulo2\">Capital Social Válido</a>";
									}else{
									 		$_SESSION['CapSocial'] = $Numero;
									}
							}
					}
					if( $_SESSION['CapIntegralizado'] != "" ) {
							$_SESSION['CapIntegralizado'] = str_replace(".","",$_SESSION['CapIntegralizado']);
							if( ! SoNumVirg($_SESSION['CapIntegralizado']) ){
									if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
								  $_SESSION['Mens']      = 1;
								  $_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CapIntegralizado.focus();\" class=\"titulo2\">Capital Integralizado Válido</a>";
							}else{
									$Numero = Decimal($_SESSION['CapIntegralizado']);
									if( ! $Numero ){
											if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
										  $_SESSION['Mens']      = 1;
										  $_SESSION['Tipo']      = 2;
											$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.CapIntegralizado.focus();\" class=\"titulo2\">Capital Integralizado Válido</a>";
									}else{
									 		$_SESSION['CapIntegralizado'] = $Numero;
									}
							}
					}
					if( $_SESSION['Patrimonio'] == "" ) {
						if ( empty( $_SESSION['MicroEmpresa'])  ) { 
						     if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						     $_SESSION['Mens']      = 1;
						     $_SESSION['Tipo']      = 2;
						     $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Patrimonio.focus();\" class=\"titulo2\">Patrimônio Líquido</a>";
						} 
					}else{
							$_SESSION['Patrimonio'] = str_replace(".","",$_SESSION['Patrimonio']);
							if( ! SoNumVirg($_SESSION['Patrimonio']) ){
									if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
								  $_SESSION['Mens']      = 1;
								  $_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Patrimonio.focus();\" class=\"titulo2\">Patrimônio Líquido Válido</a>";
							}else{
									$Numero = Decimal($_SESSION['Patrimonio']);
									if( ! $Numero ){
											if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
										  $_SESSION['Mens']      = 1;
										  $_SESSION['Tipo']      = 2;
											$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Patrimonio.focus();\" class=\"titulo2\">Patrimônio Líquido Válido</a>";
									}else{
									 		$_SESSION['Patrimonio'] = $Numero;
									}
							}
					}
					if( $_SESSION['IndLiqCorrente'] != "" ) {
							$_SESSION['IndLiqCorrente'] = str_replace(".","",$_SESSION['IndLiqCorrente']);
							if( ! SoNumVirg($_SESSION['IndLiqCorrente']) ){
									if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
								  $_SESSION['Mens']      = 1;
								  $_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.IndLiqCorrente.focus();\" class=\"titulo2\">Índice de Liquidez Corrente Válido</a>";
							}else{
									$Numero = Decimal($_SESSION['IndLiqCorrente']);
									if( ! $Numero ){
											if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
										  $_SESSION['Mens']      = 1;
										  $_SESSION['Tipo']      = 2;
											$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.IndLiqCorrente.focus();\" class=\"titulo2\">Índice de Liquidez Corrente Válido</a>";
									}else{
									 		$_SESSION['IndLiqCorrente'] = $Numero;
									}
							}
					}
					if( $_SESSION['IndLiqGeral'] != "" ){
							$_SESSION['IndLiqGeral'] = str_replace(".","",$_SESSION['IndLiqGeral']);
							if( ! SoNumVirg($_SESSION['IndLiqGeral']) ){
									if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
								  $_SESSION['Mens']      = 1;
								  $_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.IndLiqGeral.focus();\" class=\"titulo2\">Índice de Liquidez Geral Válido</a>";
							}else{
									$Numero = Decimal($_SESSION['IndLiqGeral']);
									if( ! $Numero ){
											if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
										  $_SESSION['Mens']      = 1;
										  $_SESSION['Tipo']      = 2;
											$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.IndLiqGeral.focus();\" class=\"titulo2\">Índice de Liquidez Geral Válido</a>";
									}else{
									 		$_SESSION['IndLiqGeral'] = $Numero;
									}
							}
					}

					if( $_SESSION['IndEndividamento'] != "" )  {
							$_SESSION['IndEndividamento'] = str_replace(".","",$_SESSION['IndEndividamento']);
							if( ! SoNumVirg($_SESSION['IndEndividamento']) ){
									if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
								  $_SESSION['Mens']      = 1;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.IndEndividamento.focus();\" class=\"titulo2\">Índice de Endividamento Válido</a>";
							}else{
									$Numero = Decimal($_SESSION['IndEndividamento']);
									if( ! $Numero ){
											if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
										  $_SESSION['Mens']      = 1;
											$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.IndEndividamento.focus();\" class=\"titulo2\">Índice de Endividamento Válido</a>";
									}else{
									 		$_SESSION['IndEndividamento'] = $Numero;
									}
							}
					}

					if( $_SESSION['IndSolvencia'] != "" )  {
							$_SESSION['IndSolvencia'] = str_replace(".","",$_SESSION['IndSolvencia']);
							if( ! SoNumVirg($_SESSION['IndSolvencia']) ){
									if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
								  $_SESSION['Mens']      = 1;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.IndSolvencia.focus();\" class=\"titulo2\">Índice de Solvência Geral Válido</a>";
							}else{
									$Numero = Decimal($_SESSION['IndSolvencia']);
									if( ! $Numero ){
											if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
										  $_SESSION['Mens']      = 1;
											$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.IndSolvencia.focus();\" class=\"titulo2\">Índice de Solvência Geral Válido</a>";
									}else{
									 		$_SESSION['IndSolvencia'] = $Numero;
									}
							}
					}
					
					
					// Alterado por heraldo , só criticar Data Balanço se MicroEmpresa informado
					 
					if( $_SESSION['DataBalanco'] == "" ){
						if (  empty( $_SESSION['MicroEmpresa'])  ) {
						    if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						    $_SESSION['Mens']      = 1;
						    $_SESSION['Tipo']      = 2;
						    $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.DataBalanco.focus();\" class=\"titulo2\">Data de validade do balanço</a>";
						} 
					}else{
							$MensErro = ValidaData($_SESSION['DataBalanco']);
							if( $MensErro != "" ){
									if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
								  $_SESSION['Mens']      = 1;
								  $_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.DataBalanco.focus();\" class=\"titulo2\">Data de validade do balanço Válida</a>";
							}else{
									$DataBalancoInv = substr($_SESSION['DataBalanco'],6,4)."-".substr($_SESSION['DataBalanco'],3,2)."-".substr($_SESSION['DataBalanco'],0,2);
									if( $DataBalancoInv >= date("Y-m-d") ){
											if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
										  $_SESSION['Mens']      = 1;
										  $_SESSION['Tipo']      = 2;
											$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.DataBalanco.focus();\" class=\"titulo2\">Data de validade do balanço menor que data atual</a>";
									}
							}
					}
					if( $_SESSION['DataNegativa'] == "" ){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						  $_SESSION['Mens']      = 1;
						  $_SESSION['Tipo']      = 2;
						  $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.DataNegativa.focus();\" class=\"titulo2\">Data de Certidão Negativa de Falência ou Concordata</a>";
					}else{
							$MensErro = ValidaData($_SESSION['DataNegativa']);
							if( $MensErro != ""  ){
									if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
								  $_SESSION['Mens']      = 1;
								  $_SESSION['Tipo']      = 2;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.DataNegativa.focus();\" class=\"titulo2\">Data de última alteração de contrato ou estatuto</a>";
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
						$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.DataContratoEstatuto.focus();\" class=\"titulo2\">Data de Contrato ou Estatuto Válida</a>";
					}
				}

			}
			if( ( $_SESSION['ContaCorrente1'] == $_SESSION['ContaCorrente2']  ) and ( $_SESSION['ContaCorrente1'] != "" and $_SESSION['ContaCorrente2'] != "" ) ){
					if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
				  $_SESSION['Mens']      = 1;
				  $_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.ContaCorrente1.focus();\" class=\"titulo2\">Contas Correntes Diferentes</a>";
			}
			if( $_SESSION['Banco1'] != "" ){
				if(strlen($_SESSION['Banco1'])!=3){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						  $_SESSION['Mens']      = 1;
						  $_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Banco1.focus();\" class=\"titulo2\">Código do banco 1 deve possuir 3 dígitos</a>";
				}else{
					if( $_SESSION['Agencia1'] == "" ){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						  $_SESSION['Mens']      = 1;
						  $_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Agencia1.focus();\" class=\"titulo2\">Agência do Banco ".$_SESSION['Banco1']."</a>";
					}else if(strlen($_SESSION['Agencia1'])!=5){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						  $_SESSION['Mens']      = 1;
						  $_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Agencia1.focus();\" class=\"titulo2\">Agência do Banco ".$_SESSION['Banco1']." deve possuir 5 dígitos</a>";

					}
					if( $_SESSION['ContaCorrente1'] == "" ){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						  $_SESSION['Mens']      = 1;
						  $_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.ContaCorrente1.focus();\" class=\"titulo2\">Conta Corrente do Banco ".$_SESSION['Banco1']."</a>";
					}
				}
			}else{
					if( $_SESSION['Agencia1'] != "" ){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						  $_SESSION['Mens']      = 1;
						  $_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Banco1.focus();\" class=\"titulo2\">Banco da Agência ".$_SESSION['Agencia1']."</a>";
					}else if( $_SESSION['ContaCorrente1'] != "" ){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Banco1.focus();\" class=\"titulo2\">Banco da Conta Corrente ".$_SESSION['ContaCorrente1']."</a>";
					} else if($_SESSION['TipoHabilitacao']=="L"){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
							$_SESSION['Mens']      = 1;
							$_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Banco1.focus();\" class=\"titulo2\">1ª conta de banco é requerida</a>";
					}
			}
			if( $_SESSION['Banco2'] != "" ){
				if(strlen($_SESSION['Banco2'])!=3){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						  $_SESSION['Mens']      = 1;
						  $_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Banco2.focus();\" class=\"titulo2\">Código do banco 2 deve possuir 3 dígitos</a>";
				}else{
					if( $_SESSION['Agencia2'] == "" ){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						  $_SESSION['Mens']      = 1;
						  $_SESSION['Tipo']      = 1;
						  $_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Agencia2.focus();\" class=\"titulo2\">Agência do Banco ".$_SESSION['Banco2']."</a>";
					}else if(strlen($_SESSION['Agencia2'])!=5){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						  $_SESSION['Mens']      = 1;
						  $_SESSION['Tipo']      = 2;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Agencia2.focus();\" class=\"titulo2\">Agência do Banco ".$_SESSION['Banco2']." deve possuir 5 dígitos</a>";

					}
					if( $_SESSION['ContaCorrente2'] == "" ){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						  $_SESSION['Mens']      = 1;
						  $_SESSION['Tipo']      = 1;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.ContaCorrente2.focus();\" class=\"titulo2\">Conta Corrente do Banco ".$_SESSION['Banco2']."</a>";
					}
				}
			}else{
					if( $_SESSION['Agencia2'] != "" ){
							if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
						  $_SESSION['Mens']      = 1;
						  $_SESSION['Tipo']      = 1;
							$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.Banco2.focus();\" class=\"titulo2\">Banco da Agência ".$_SESSION['Agencia2']."</a>";
					}else{
							if( $_SESSION['ContaCorrente2'] != "" ){
									if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
								  $_SESSION['Mens']      = 1;
								  $_SESSION['Tipo']      = 1;
									$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.ContaCorrente2.focus();\" class=\"titulo2\">Banco da Conta Corrente ".$_SESSION['ContaCorrente2']."</a>";
							}
					}
			}
	}
	if( ($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Limpar" ) ){
			ExibeAbaQualificEconFinanceira();
	}
}

# Exibe Aba Qualificação Econômica e Financeira - Formulário C #
function ExibeAbaQualificEconFinanceira(){
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
		 	document.CadGestaoFornecedorIncluir.Destino.value = Destino;
		 	document.CadGestaoFornecedorIncluir.submit();
	}
	function enviar(valor){
		document.CadGestaoFornecedorIncluir.Botao.value = valor;
		document.CadGestaoFornecedorIncluir.submit();
	}
	<?php  MenuAcesso(); ?>
	//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadGestaoFornecedorIncluir.php" method="post" name="CadGestaoFornecedorIncluir">
	<br><br><br><br>
	<table cellpadding="3" border="0" summary="">
		<!-- Caminho -->
	  <tr>
	    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
	    <td align="left" class="textonormal" colspan="2"><br>
	      <font class="titulo2">|</font>
	      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedor > Cadastro e Gestão
	    </td>
	  </tr>
	  <!-- Fim do Caminho-->

		<!-- Erro -->
		<tr>
		  <td width="100"></td>
		  <td align="left" colspan="2">
	  		<?php  if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],1);}?>
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
			    					INCLUIR - CADASTRO E GESTÃO DE FORNECEDOR
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
										<?php  echo NavegacaoAbas(off,off,on,off,off); ?>
										<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr bgcolor="#bfdaf2">
												<td colspan="4">
								          <table class="textonormal" border="0" align="left" width="100%" summary="">
														<tr>
															<td class="textonormal" height="20">
																<?php  if( strlen($_SESSION['CPF_CNPJ']) == 14 ){ echo "CNPJ"; }else{ echo "CPF";} ?>
															</td>
						          	    	<td class="textonormal">
						          	    		<?php 
																if( $_SESSION['CPF_CNPJ'] != "" ){
								          	    		if( strlen($_SESSION['CPF_CNPJ']) == 14 ){
								          	    				echo substr($_SESSION['CPF_CNPJ'],0,2).".".substr($_SESSION['CPF_CNPJ'],2,3).".".substr($_SESSION['CPF_CNPJ'],5,3)."/".substr($_SESSION['CPF_CNPJ'],8,4)."-".substr($_SESSION['CPF_CNPJ'],12,2);
								          	    		}else{
								          	    				echo substr($_SESSION['CPF_CNPJ'],0,3).".".substr($_SESSION['CPF_CNPJ'],3,3).".".substr($_SESSION['CPF_CNPJ'],6,3)."-".substr($_SESSION['CPF_CNPJ'],9,2);
								          	    		}
								          	    }
								          	    ?>
						          	    	</td>
					            			</tr>
								            <tr>
								              <td class="textonormal" height="20">
								              	<?php  if(  $_SESSION['TipoCnpjCpf'] == "CNPJ"  ){ echo "Razão Social\n"; }else{ echo "Nome\n"; } ?>
								              </td>
								              <td class="textonormal">
								              	<?php  echo $_SESSION['RazaoSocial'];?>
																<input type="hidden" name="Origem" value="C">
																<input type="hidden" name="Destino">
															</td>
								            </tr>
								            <?php  if( $_SESSION['TipoCnpjCpf'] == "CNPJ" and $_SESSION['TipoHabilitacao'] == 'L' ){ ?>
														<tr>
								              <td class="textonormal">Capital Social Subscrito*</td>
								              <td class="textonormal">
								              	<input type="text" name="CapSocial" size="20" maxlength="19" value="<?php  echo $_SESSION['CapSocial'];?>" class="textonormal">
								              </td>
								            </tr>
														<tr>
								              <td class="textonormal">Capital Integralizado</td>
								              <td class="textonormal">
								              	<input type="text" name="CapIntegralizado" size="20" maxlength="19" value="<?php  echo $_SESSION['CapIntegralizado'];?>" class="textonormal">
								              </td>
								            </tr>
								            <tr>
								            
								              <?php   if (  empty( $_SESSION['MicroEmpresa'])  ) {     ?>
								                  <td class="textonormal">Patrimônio Líquido*</td>
								              <?php  } else { ?>
								                  <td class="textonormal">Patrimônio Líquido</td>
								              <?php   } ?>    
								 
								              <td class="textonormal">
								              	<input type="text" name="Patrimonio" size="20" maxlength="19" value="<?php  echo $_SESSION['Patrimonio'];?>" class="textonormal">
								              </td>
								            </tr>
														<tr>
								              <td class="textonormal" width="45%">Índice de Liquidez Corrente</td>
								              <td class="textonormal">
								              	<input type="text" name="IndLiqCorrente" size="20" maxlength="19" value="<?php  echo $_SESSION['IndLiqCorrente'];?>" class="textonormal">
								              </td>
								            </tr>
								            <tr>
								              <td class="textonormal">Índice de Liquidez Geral</td>
															<td class="textonormal">
																<input type="text" name="IndLiqGeral" size="20" maxlength="19" value="<?php  echo $_SESSION['IndLiqGeral'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Índice de Endividamento</td>
															<td class="textonormal">
																<input type="text" name="IndEndividamento" size="20" maxlength="19" value="<?php  echo $_SESSION['IndEndividamento'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Índice de Solvência Geral</td>
															<td class="textonormal">
																<input type="text" name="IndSolvencia" size="20" maxlength="19" value="<?php  echo $_SESSION['IndSolvencia'];?>" class="textonormal">
															</td>
								            </tr>
								            <tr>
								            <?php   if (  empty( $_SESSION['MicroEmpresa'])  ) {     ?>
								                <td class="textonormal">Data de validade do balanço*</td>
								            <?php   } else { ?>  
								               <td class="textonormal">Data de validade do balanço</td>
								            <?php  } ?>  
								               
															<td class="textonormal">
					              				<?php  $URL = "../calendario.php?Formulario=CadGestaoFornecedorIncluir&Campo=DataBalanco" ?>
										          	<input type="text" name="DataBalanco" size="10" maxlength="10" value="<?php  echo $_SESSION['DataBalanco'];?>" class="textonormal">
																<a href="javascript:janela('<?php  echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a> dd/mm/aaaa
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Data de Certidão Negativa de Falência ou Concordata*</td>
															<td class="textonormal">
					              				<?php  $URL = "../calendario.php?Formulario=CadGestaoFornecedorIncluir&Campo=DataNegativa" ?>
										          	<input type="text" name="DataNegativa" size="10" maxlength="10" value="<?php  echo $_SESSION['DataNegativa'];?>" class="textonormal">
																<a href="javascript:janela('<?php  echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a> dd/mm/aaaa
															</td>
								            </tr>
								            <tr>
								              <td class="textonormal">Data de última alteração de contrato ou estatuto</td>
															<td class="textonormal">
					              				<?php  $URL = "../calendario.php?Formulario=CadGestaoFornecedorIncluir&Campo=DataContratoEstatuto" ?>
										          	<input type="text" name="DataContratoEstatuto" size="10" maxlength="10" value="<?php  echo $_SESSION['DataContratoEstatuto'];?>" class="textonormal">
																<a href="javascript:janela('<?php  echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a> dd/mm/aaaa
															</td>
								            </tr>
														<tr><td><br></td></tr>
														<?php  } ?>
														<tr>
								            	<td colspan="2">
									            	<table  align="center" border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" class="textonormal" width="100%" summary="">
									              	<tr>
									              		<td class="textoabasoff" bgcolor="#75ADE6" align="center">BANCO</td>
									              		<td class="textoabasoff" bgcolor="#75ADE6" align="center">AGÊNCIA </td>
									              		<td class="textoabasoff" bgcolor="#75ADE6" align="center">CONTA CORRENTE</td>
									              	</tr>
									              	<tr>
									              		<td class="textonormal" align="center">
									              			<input type="text" name="Banco1" size="3" maxlength="3" value="<?php  echo $_SESSION['Banco1'];?>" class="textonormal">
									              		</td>
									              		<td class="textonormal" align="center">
									              			<input type="text" name="Agencia1" size="11" maxlength="8" value="<?php  echo $_SESSION['Agencia1'];?>" class="textonormal">
									              		</td>
											              <td class="textonormal" align="center">
											              	<input type="text" name="ContaCorrente1" size="13" maxlength="10" value="<?php  echo $_SESSION['ContaCorrente1'];?>" class="textonormal">
											              </td>
											          	</tr>
											            <tr>
											          	  <td class="textonormal" align="center">
											          	  	<input type="text" name="Banco2" size="3" maxlength="3" value="<?php  echo $_SESSION['Banco2'];?>" class="textonormal">
											          	  </td>
											              <td class="textonormal" align="center">
											              	<input type="text" name="Agencia2" size="11" maxlength="8" value="<?php  echo $_SESSION['Agencia2']; ?>" class="textonormal">
											              </td>
											              <td class="textonormal" align="center">
											              	<input type="text" name="ContaCorrente2" size="13" maxlength="10" value="<?php  echo $_SESSION['ContaCorrente2'];?>" class="textonormal">
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
	<?php  if( $_SESSION['TipoCnpjCpf'] == "CNPJ" ){ ?>
	document.CadGestaoFornecedorIncluir.CapSocial.focus();
	<?php  } ?>
	//-->
	</script>
	</body>
	</html>
	<?php 
	exit;
}

# Critica aos Campos - Formulário D #
function CriticaAbaQualificTecnica(){
	if( $_SESSION['Botao'] == "Limpar" ){
			$_SESSION['Botao']            = "";
			$_SESSION['RegistroEntidade'] = "";
			$_SESSION['NomeEntidade']     = "";
			$_SESSION['DataVigencia']     = "";
			$_SESSION['RegistroTecnico']  = "";
	}elseif( $_SESSION['Botao'] == "RetirarAutorizacao" ){
			if( count($_SESSION['AutorizacaoNome']) != 0 ){
                $QtdAutorizacao = 0;
					for( $i=0; $i< count($_SESSION['AutorizacaoNome']); $i++ ){
							if( $_SESSION['CheckAutorizacao'][$i] == "" ){
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
					if( $_SESSION['AutorizacaoNome'][0] == "" or count($_SESSION['AutorizacaoNome']) == 0){
							unset($_SESSION['AutorizacaoNome']);
					}
					if( $_SESSION['AutorizacaoRegistro'][0] == "" or count($_SESSION['AutorizacaoRegistro']) == 0){
							unset($_SESSION['AutorizacaoRegistro']);
					}
					if( $_SESSION['AutorizacaoData'][0] == "" or count($_SESSION['AutorizacaoData']) == 0){
							unset($_SESSION['AutorizacaoData']);
					}
					if( $_SESSION['AutoEspecifica'][0] == "" or count($_SESSION['AutoEspecifica']) == 0){
							unset($_SESSION['AutoEspecifica']);
					}
					$_SESSION['AutorizaNome']     = "";
					$_SESSION['AutorizaRegistro'] = "";
					$_SESSION['AutorizaData']     = "";
					$_SESSION['AutoEspecifica']   = "";
			}
			ExibeAbas("D");
	}elseif( $_SESSION['Botao'] == "RetirarGrupos" ){
		$QtdMateriais=0;
			if( count($_SESSION['Materiais']) != 0 ){
					for( $i=0; $i<count($_SESSION['Materiais']); $i++ ){
							if( $_SESSION['CheckMateriais'][$i] == "" ){
									$QtdMateriais++;
									$_SESSION['CheckMateriais'][$i] = "";
									$_SESSION['Materiais'][$QtdMateriais-1] = $_SESSION['Materiais'][$i];
							}
					}

			//		$_SESSION['Materiais'] = array_splice($_SESSION['Materiais'],-1,$QtdMateriais); //alterado de array_slice pra array_splice - Everton
						$_SESSION['Materiais'] = array_slice($_SESSION['Materiais'],0,$QtdMateriais);

					if( count($_SESSION['Materiais']) == 1 and count($_SESSION['Materiais']) == "" ){
							unset($_SESSION['Materiais']);
					}
			}
			$QtdServicos=0;
			if( count($_SESSION['Servicos']) != 0 ){
					for( $i=0; $i<count($_SESSION['Servicos']); $i++ ){
							if( $_SESSION['CheckServicos'][$i] == "" ){
									$QtdServicos++;
									$_SESSION['CheckServicos'][$i] = "";
									$_SESSION['Servicos'][$QtdServicos-1] = $_SESSION['Servicos'][$i];
							}
					}

			//	$_SESSION['Servicos'] = array_splice($_SESSION['Servicos'],-1,$QtdServicos); //alterado de array_slice pra array_splice - Everton
					$_SESSION['Servicos'] = array_slice($_SESSION['Servicos'],0,$QtdServicos);

					if( count($_SESSION['Servicos']) == 1 and count($_SESSION['Servicos']) == "" ){
							unset($_SESSION['Servicos']);
					}
			}
			ExibeAbas("D");
	}else{
			$_SESSION['Mens']     = "";
			$_SESSION['Mensagem'] = "Informe: ";
			if( ( $_SESSION['RegistroEntidade'] != "" )  and ( ! SoNumeros($_SESSION['RegistroEntidade']) ) ){
					if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
				  $_SESSION['Mens']      = 1;
				  $_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.RegistroEntidade.focus();\" class=\"titulo2\">Registro da Entidade Válido</a>";
			}
			$MensErro = ValidaData($_SESSION['DataVigencia']);
			if( ( $_SESSION['DataVigencia'] != "" )  and ( $MensErro != "" ) ){
					if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
				  $_SESSION['Mens']      = 1;
				  $_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.DataVigencia.focus();\" class=\"titulo2\">$MensErro</a>";
			}
			if( ( $_SESSION['RegistroTecnico'] != "" )  and ( ! SoNumeros($_SESSION['RegistroTecnico']) ) ){
					if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
				  $_SESSION['Mens']      = 1;
				  $_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "<a href=\"javascript:document.CadGestaoFornecedorIncluir.RegistroTecnico.focus();\" class=\"titulo2\">Registro ou Inscrição do Técnico Válida</a>";
			}

			# Constuindo o array de Autorização Específica #
			if( $_SESSION['AutorizaNome'] != "" and $_SESSION['AutorizaRegistro'] != "" and  $_SESSION['AutorizaData'] != "" ){
					if( ! isset($_SESSION['AutorizacaoNome'])){
							$_SESSION['AutorizacaoNome'] = array();
					}
					if( ! isset($_SESSION['AutorizacaoRegistro'])){
							$_SESSION['AutorizacaoRegistro'] = array();
					}
					if( ! isset($_SESSION['AutorizacaoData'])){
							$_SESSION['AutorizacaoData'] = array();
					}
					if( ! isset($_SESSION['AutoEspecifica'])){
							$_SESSION['AutoEspecifica'] = array();
					}
					$AutorizacaoEspecifica = $_SESSION['AutorizaNome']."#".$_SESSION['AutorizaRegistro'];
					if( $_SESSION['AutoEspecifica'] == "" || ! in_array( $AutorizacaoEspecifica,$_SESSION['AutoEspecifica'] ) ){
							$_SESSION['AutorizacaoNome'][ count($_SESSION['AutorizacaoNome']) ] = $_SESSION['AutorizaNome'];
							$_SESSION['AutorizacaoRegistro'][ count($_SESSION['AutorizacaoRegistro']) ] = $_SESSION['AutorizaRegistro'];
							$_SESSION['AutorizacaoData'][ count($_SESSION['AutorizacaoData']) ] = $_SESSION['AutorizaData'];
							$_SESSION['AutoEspecifica'][ count($_SESSION['AutoEspecifica']) ] = $AutorizacaoEspecifica;
					}
			}

			if( count($_SESSION['Materiais']) == 0 and count($_SESSION['Servicos']) == 0 ){
					if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
				  $_SESSION['Mens']      = 1;
				  $_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "Pelo menos um Grupo de Fornecimento deve ser Incluído";
			}

			if( $_SESSION['Cumprimento'] == "" ){
				if($_SESSION['TipoHabilitacao'] == 'L'){
					if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
				  $_SESSION['Mens']      = 1;
				  $_SESSION['Tipo']      = 2;
					$_SESSION['Mensagem'] .= "Resposta para o Cumprimento da Lei";
				}
			}else{
					if( $_SESSION['Cumprimento'] == "N" ){
					if( $_SESSION['Mens'] == 1 ){ $_SESSION['Mensagem'] .= ", "; }
							$_SESSION['Mens']        = 1;
							$_SESSION['Tipo']        = 2;
							$_SESSION['Mensagem']    = "A Inscrição só será efetivada se o fornecedor cumprir o que está disposto no Inc. XXXIII do Art. 7º da Constituição Federal";
							$_SESSION['Cumprimento'] = "S";
					}
			}
	}
	if( ( $_SESSION['Mens'] != 0 ) or ($_SESSION['Botao'] == "Limpar" ) or ( $_SESSION['Email'] == "" and $_SESSION['Botao'] == "Incluir" ) ){
			ExibeAbaQualificTecnica();
	}
}

# Exibe Aba Qualificação Técnica - Formulário D #
function ExibeAbaQualificTecnica(){
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
	 	document.CadGestaoFornecedorIncluir.Destino.value = Destino;
	 	document.CadGestaoFornecedorIncluir.submit();
	}
	function enviar(valor){
		document.CadGestaoFornecedorIncluir.Botao.value = valor;
		document.CadGestaoFornecedorIncluir.submit();
	}
	function AbreJanela(url,largura,altura) {
		window.open(url,'pagina','status=no,scrollbars=no,left=20,top=130,width='+largura+',height='+altura);
	}
	<?php  MenuAcesso(); ?>
	//-->
	</script>
	<link rel="stylesheet" type="text/css" href="../estilo.css">
	<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="CadGestaoFornecedorIncluir.php" method="post" name="CadGestaoFornecedorIncluir">
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
	  		<?php  if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],1);}?>
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
			    					INCLUIR - CADASTRO E GESTÃO DE FORNECEDOR
			          	</td>
			        	</tr>
		  	      	<tr>
		    	      	<td class="textonormal" >
		      	    		<p align="justify">
		        	    		Informe os dados abaixo e clique no botão "Incluir".<br>
		        	    		Para informar os grupos de fornecimento clique no botão "Incluir Grupos". Se desejar eliminar um grupo de fornecimento já informado, marque o(s) grupo(s) desejado(s) e clique no botão "Retirar Grupos".
		          	   	</p>
		          		</td>
			        	</tr>
			        	<tr>
									<td align="left">
										<?php  echo NavegacaoAbas(off,off,off,on,off); ?>
										<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
											<tr bgcolor="#bfdaf2">
												<td colspan="4">
								          <table class="textonormal" border="0" align="left" width="100%" summary="">
														<tr>
															<td class="textonormal" height="20">
																<?php  if( strlen($_SESSION['CPF_CNPJ']) == 14 ){ echo "CNPJ"; }else{ echo "CPF";} ?>
															</td>
					          	    		<td class="textonormal">
						          	    		<?php 
																if( $_SESSION['CPF_CNPJ'] != "" ){
								          	    		if( strlen($_SESSION['CPF_CNPJ']) == 14 ){
								          	    				echo substr($_SESSION['CPF_CNPJ'],0,2).".".substr($_SESSION['CPF_CNPJ'],2,3).".".substr($_SESSION['CPF_CNPJ'],5,3)."/".substr($_SESSION['CPF_CNPJ'],8,4)."-".substr($_SESSION['CPF_CNPJ'],12,2);
								          	    		}else{
								          	    				echo substr($_SESSION['CPF_CNPJ'],0,3).".".substr($_SESSION['CPF_CNPJ'],3,3).".".substr($_SESSION['CPF_CNPJ'],6,3)."-".substr($_SESSION['CPF_CNPJ'],9,2);
								          	    		}
								          	    }
								          	    ?>
						          	    	</td>
					            			</tr>
								            <tr>
								              <td class="textonormal" height="20" width="45%">
								              	<?php  if(  $_SESSION['TipoCnpjCpf'] == "CNPJ" ){ echo "Razão Social\n"; }else{ echo "Nome\n"; } ?>
								              </td>
								              <td class="textonormal">
								              	<?php  echo $_SESSION['RazaoSocial']; ?>
					            	  			<input type="hidden" name="Origem" value="D">
																<input type="hidden" name="Destino">
															</td>
								            </tr>
								          </table>
								      	</td>
								      </tr>
											<?php 
											if($_SESSION['TipoHabilitacao']=="L"){
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
											              	<input type="text" name="NomeEntidade" size="25" maxlength="18" value="<?php  echo $_SESSION['NomeEntidade'];?>" class="textonormal">
											              </td>
											            </tr>
											            <tr>
											              <td class="textonormal" width="45%">Registro ou Inscrição </td>
											              <td class="textonormal">
											              	<input type="text" name="RegistroEntidade" size="10" maxlength="10" value="<?php  echo $_SESSION['RegistroEntidade'];?>" class="textonormal">
											              </td>
											            </tr>
																	<tr>
											              <td class="textonormal">Data da Vigência </td>
											              <td class="textonormal">
								              				<?php  $URL = "../calendario.php?Formulario=CadGestaoFornecedorIncluir&Campo=DataVigencia" ?>
													          	<input type="text" name="DataVigencia" size="10" maxlength="10" value="<?php  echo $_SESSION['DataVigencia'];?>" class="textonormal">
																			<a href="javascript:janela('<?php  echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
																		</td>
											           	</tr>
											            <tr>
											              <td class="textonormal" width="45%">Registro ou Inscrição do Técnico</td>
											              <td class="textonormal">
											              	<input type="text" name="RegistroTecnico" size="10" maxlength="10" value="<?php  echo $_SESSION['RegistroTecnico'];?>" class="textonormal">
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
					              		if( count($_SESSION['AutorizacaoNome']) != 0 ){
							              		for( $i=0; $i< count($_SESSION['AutorizacaoNome']);$i++ ){
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
					              				<?php 
																$Url = "CadIncluirAutorizacao.php?ProgramaOrigem=CadGestaoFornecedorIncluir";
																if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																?>
					              				<input class="botao" type="button" value="Incluir Autorização" onclick="javascript:AbreJanela('<?php echo $Url;?>',750,270);">
																<input class="botao" type="button" value="Retirar Autorizações Marcadas" onclick="javascript:enviar('RetirarAutorizacao');">
																<input type="hidden" name="AutorizaNome" value="<?php  echo $_SESSION['AutorizaNome'];?>">
																<input type="hidden" name="AutorizaRegistro" value="<?php  echo $_SESSION['AutorizaRegistro'];?>">
					            	  			<input type="hidden" name="AutorizaData" value="<?php  echo $_SESSION['AutorizaData'];?>">
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
					              		if( count($_SESSION['Materiais']) != 0 ){
							              		echo "<tr>\n";
							              		echo "	<td bgcolor=\"#DDECF9\" class=\"textonormal\" colspan=\"2\" align=\"center\">MATERIAIS</td>\n";
							              		echo "</tr>\n";
							              		$DescricaoGrupoAntes = "";
							              		for( $i=0; $i<count($_SESSION['Materiais']);$i++ ){
									              		$GrupoMaterial = explode("#",$_SESSION['Materiais'][$i]);
									              		$db   = Conexao();
		  												$sql  = "SELECT A.CGRUMSCODI, A.EGRUMSDESC ";
		  												$sql .= "  FROM SFPC.TBGRUPOMATERIALSERVICO A ";
		  												$sql .= " WHERE A.CGRUMSCODI = ".$GrupoMaterial[1]." ";
		  												$sql .= "   ORDER BY 2";
		  												$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
															while( $Linha = $res->fetchRow() ){
									          	      			$DescricaoGrupo   = substr($Linha[1],0,75);
										    	      			if( $DescricaoGrupo != $DescricaoGrupoAntes ){
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
					              		if( count($_SESSION['Servicos']) != 0 ){
						              		echo "<tr>\n";
						              		echo "	<td bgcolor=\"#DDECF9\" class=\"textonormal\" colspan=\"2\" align=\"center\">SERVIÇOS</td>\n";
						              		echo "</tr>\n";
						              		$DescricaoGrupoAntes = "";
						              		for( $i=0; $i<count($_SESSION['Servicos']);$i++ ){
							              		$GrupoServico = explode("#",$_SESSION['Servicos'][$i]);
							              		$db   = Conexao();
  												$sql  = "SELECT A.CGRUMSCODI, A.EGRUMSDESC ";
  												$sql .= "  FROM SFPC.TBGRUPOMATERIALSERVICO A ";
  												$sql .= " WHERE A.CGRUMSCODI = ".$GrupoServico[1]." ";
  												$sql .= " ORDER BY 2";
  												$res  = $db->query($sql);

												if( PEAR::isError($res) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
													while( $Linha = $res->fetchRow() ){
			          	      							$DescricaoGrupo   = substr($Linha[1],0,75);

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
					              				<?php 
																$Url = "CadIncluirGrupos.php?ProgramaOrigem=CadGestaoFornecedorIncluir";
																if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																?>
					              				<input class="botao" type="button" value="Incluir Grupos" onclick="javascript:AbreJanela('<?php echo $Url;?>',750,370);">
												<input class="botao" type="button" value="Retirar Grupos Marcados" onclick="javascript:enviar('RetirarGrupos');">
					              			</td>
					              		</tr>
					              	</table>
					              </td>
					            </tr>
											<?php 
											if($_SESSION['TipoHabilitacao']=="L"){
											?>
								      <tr><td bgcolor="#bfdaf2" colspan="4"><br></td></tr>
											<tr>
					              <td class="textonormal" colspan="4">
													<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
        			          		<tr>
					              			<td bgcolor="#75ADE6" colspan="2" class="textoabasoff" align="center">CUMPRIMENTO DA LEI</td>
					              		</tr>
					              		<tr>
					              			<td class="normal" align="left">
					              				O fornecedor declara que cumpre o disposto no Inc. XXXIII do Art. 7º da Constituição Federal. "Sim" ou "Não"?
					              			</td>
					              			<td class="normal" align="left">
					              				<input type="radio" name="Cumprimento" value="S" <?php  if( $_SESSION['Cumprimento'] == "S" ){ echo "checked"; }?>>Sim
					              				<input type="radio" name="Cumprimento" value="N" <?php  if( $_SESSION['Cumprimento'] == "N" ){ echo "checked"; }?>>Não
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
		<?php 
		if($_SESSION['TipoHabilitacao']=="L"){
		?>
			document.CadGestaoFornecedorIncluir.NomeEntidade.focus();
		<?php 
		}
		if( $_SESSION['Email'] == "" and $_SESSION['Botao'] == "Incluir" ){
		?>
			<?php 
			$Url = "RotVerificaEmail.php?ProgramaOrigem=CadGestaoFornecedorIncluir";
			if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
			?>
			window.open('<?php  echo $Url; ?>','pagina','status=no,scrollbars=no,left=200,top=150,width=400,height=225');
		<?php  } ?>
		//-->
	</script>
	</body>
	</html>
	<?php 
	exit;
}

function CriticaAbaDocumentos() { // Formulário E - Documentos

	$DDocumento = $_SESSION['DDocumento'];


	if ($_SESSION['Botao'] == "Limpar") {

		$_SESSION['Botao'] = "";
		$_SESSION['tipoDoc'] = 0;
		$_SESSION['obsDocumento'] = "";

	} elseif  ($_SESSION['Botao'] == 'IncluirDocumento') {

		$_SESSION['Mens']     = "";
		$_SESSION['Mensagem'] = "Informe: ";


					
		if ($_POST['tipoDoc'] == '0' ) {
			if ($_SESSION['Mens'] == 1) {
				$_SESSION['Mensagem'] .= ", ";
			}
			$_SESSION['Mens']      = 1;
			$_SESSION['Tipo']      = 2;
			$_SESSION['Mensagem'] .= "Tipo do documento";
			if(!$_FILES['Documentacao']['tmp_name']){
				if ($_SESSION['Mens'] == 1) {
					$_SESSION['Mensagem'] .= ", ";
				}
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= 'Documento anexo';
			}
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
					if (! ($_SESSION['Arquivos_Upload_Inc']['conteudo'][] = file_get_contents($_FILES['Documentacao']['tmp_name']))) {
						$_SESSION['Mens']= 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] = 'Caminho da Documentação Inválido';
					} else {
						$_SESSION['Arquivos_Upload_Inc']['nome'][] = $_FILES['Documentacao']['name'];
						$_SESSION['Arquivos_Upload_Inc']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
						$_SESSION['Arquivos_Upload_Inc']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
						$_SESSION['Arquivos_Upload_Inc']['tipoCod'][] = $_POST['tipoDoc']; 
						$_SESSION['Arquivos_Upload_Inc']['tipoDocumentoDesc'][] = $_POST['tipoDocDesc']; 
						$_SESSION['Arquivos_Upload_Inc']['observacao'][] = strtoupper2($_POST['obsDocumento']); 
						$_SESSION['Arquivos_Upload_Inc']['dataHora'][] = date('d/m/Y H:i'); 

						$_SESSION['tipoDoc'] = 0;
						$_SESSION['obsDocumento'] = "";
					}
				}

			} else {
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] = 'Informe: Documento anexo';
			}
		}
	} elseif ($_SESSION['Botao'] == 'RetirarDocumento') {
		//$Critica = 0;

		if ($DDocumento){
			foreach ($DDocumento as $valor) {
	
				if ($_SESSION['Arquivos_Upload_Inc']['situacao'][$valor] == 'novo') {
					$_SESSION['Arquivos_Upload_Inc']['situacao'][$valor] = 'cancelado'; // cancelado- quando o usuário incluiu um arquivo novo mas desistiu
				} elseif ($_SESSION['Arquivos_Upload_Inc']['situacao'][$valor] == 'existente') {
					$_SESSION['Arquivos_Upload_Inc']['situacao'][$valor] = 'excluido'; // excluído- quando o arquivo já existe e deve ser excluido no sistema
				}
	
			}

		}else{
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] = 'Selecione um anexo para ser retirado';
		}
	} elseif ($_SESSION['Botao'] == 'Incluir'){
		var_dump($_FILES['Documentacao']['tmp_name']);
		$checaNovo = false;
		for($i=0; $i < count($_SESSION['Arquivos_Upload_Inc']['situacao']); $i++){
			if($_SESSION['Arquivos_Upload_Inc']['situacao'][$i] == 'novo'){
				$checaNovo = true;
			}
		}
		if(empty($_SESSION['Arquivos_Upload_Inc']['nome']) || $checaNovo == false){
			$_SESSION['Mens']      = 1;
			$_SESSION['Tipo']      = 2;
			$_SESSION['Mensagem'] = "Informe: Documento";
		} else {
			if(empty($_FILES['Documentacao']['tmp_name']) && $_POST['tipoDoc'] != '0'){
				unset($_FILES['Documentacao']);
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] = "Informe: Documento anexo";
			} elseif(!empty($_FILES['Documentacao']['tmp_name']) && $_POST['tipoDoc'] == '0'){
				unset($_FILES['Documentacao']);
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] = "Informe: Tipo de documento";
			} elseif(!empty($_FILES['Documentacao']['tmp_name']) && $_POST['tipoDoc'] != '0'){
				unset($_FILES['Documentacao']);
				$_SESSION['Mens']      = 1;
				$_SESSION['Tipo']      = 2;
				$_SESSION['Mensagem'] = "Informe: Documento";
			}
			return true;
		}
	}
	
	if (($_SESSION['Mens'] != 0) or ($_SESSION['Botao'] == "Limpar") or ($_SESSION['Email'] == "" and $_SESSION['Botao'] == "Incluir") or ($_SESSION['Botao'] == "IncluirDocumento") or ($_SESSION['Botao'] == "RetirarDocumento")) {
		ExibeAbaDocumentos();
	}
}

function ExibeAbaDocumentos() { // Formulário E - Documentos
	?>
	<html>
		<?php 	# Carrega o layout padrão #
				layout();
		?>
		<script language="JavaScript" src="../janela.js" type="text/javascript"></script>
		<script language="JavaScript" type="">
			<!--
			function Submete(Destino){
				 document.CadGestaoFornecedorIncluir.Destino.value = Destino;
				 document.CadGestaoFornecedorIncluir.submit();
			}
			function enviar(valor){
				document.CadGestaoFornecedorIncluir.Botao.value = valor;
				document.CadGestaoFornecedorIncluir.submit();
			}

			function preencheTipoDocDesc(){
				$('#tipoDocDesc').val($('#tipoDoc option:selected').text());
			}


			<?php  MenuAcesso(); ?>
			//-->
		</script>
		<link rel="stylesheet" type="text/css" href="../estilo.css">
		<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
			<script language="JavaScript" src="../menu.js"></script>
			<script language="JavaScript">Init();</script>
			<form action="CadGestaoFornecedorIncluir.php" method="post" name="CadGestaoFornecedorIncluir" enctype="multipart/form-data">
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
							<?php 	if ($_SESSION['Mens'] != 0) {
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
														<br><br>Tamanho máximo para upload de arquivo: 5 MB.	
													</p>
												 </td>
												 </tr>
											<tr>
												<td align="left">
													<?php  echo NavegacaoAbas(off,off,off,off,on); ?>
													<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
														<tr bgcolor="#bfdaf2">
															<td colspan="4">
																  <table class="textonormal" border="0" align="left" summary="">
																	<tr>
																		<td class="textonormal" height="20" width="40%">
																			<?php  if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { echo "CNPJ"; } else { echo "CPF"; } ?>
																		</td>
																		<td class="textonormal">
																			<?php 	if ($_SESSION['CPF_CNPJ'] != "") {
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
																			  <?php  if ($_SESSION['TipoCnpjCpf'] == "CNPJ") { echo "Razão Social*\n"; } else { echo "Nome*\n"; } ?>
																		</td>
																		<td class="textonormal">
																		  	<?php  echo $_SESSION['RazaoSocial'] ?>
																		</td>
																	</tr>
																	<tr>
																		  <td class="textonormal">Nome Fantasia </td>
																		  <td class="textonormal">
																		  	<?php  echo $_SESSION['NomeFantasia'] ?>
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
																							<option value="<?php  echo $tipoDoc[0]; ?>" selected><?php  echo $tipoDoc[1].$docObrigatorio; ?></option>
																							<?php 
																						}else{
																							?>
																							<option value="<?php  echo $tipoDoc[0]; ?>"><?php  echo $tipoDoc[1].$docObrigatorio; ?></option>
																							<?php 
																						}	


																					}
																					
																				}
																			?>
																		</select>
																		<input type="hidden" id="tipoDocDesc" name="tipoDocDesc" value="<?php  echo $_SESSION['tipoDocDesc'] ?>">
																		</td>
																	</tr>
																	<tr>
																		  <td class="textonormal">Observação</td>
																		<td class="textonormal">
																			<font class="textonormal">máximo de 500 caracteres</font>
																			<input type="text" name="NCaracteres" disabled="" readonly="" size="3" value="0" class="textonormal"><br>
																			<textarea id="obsDocumento" name="obsDocumento" maxlength="500" cols="50" rows="4" onkeyup="javascript:CaracteresObservacao(1)" onblur="javascript:CaracteresObservacao(0)" onselect="javascript:CaracteresObservacao(1)" class="textonormal"><?php  echo $_SESSION['obsDocumento'] ?></textarea>
																			<script language="javascript" type="">
																			function CaracteresObservacao(valor){
																				CadGestaoFornecedorIncluir.NCaracteres.value = '' +  CadGestaoFornecedorIncluir.obsDocumento.value.length;
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
														<?php  	if (count($_SESSION['Arquivos_Upload_Inc']) > 0) {?>
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
																<?php 																						$sql = "SELECT EUSUPORESP FROM 
																				SFPC.TBUSUARIOPORTAL
																				WHERE CUSUPOCODI = ".$_SESSION['_cusupocodi_']." limit 1";

																				$nome_usuario = resultValorUnico(executarTransacao($db, $sql));

																	$DTotal = count($_SESSION['Arquivos_Upload_Inc']['conteudo']);
																	for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {
																		if ($_SESSION['Arquivos_Upload_Inc']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload_Inc']['situacao'][$Dcont] == 'existente') {
																		?>	<tr>
																				<td align='center' width='5%' bgcolor="#ffffff"><input type='checkbox' name='DDocumento[<?php  echo $Dcont?>]' value='<?php  echo $Dcont?>' ></td>
																			
																				<td class='textonormal' bgcolor='#ffffff'>
																					<?php  echo $_SESSION['Arquivos_Upload_Inc']['tipoDocumentoDesc'][$Dcont] ?>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<?php  echo $_SESSION['Arquivos_Upload_Inc']['nome'][$Dcont] ?>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<?php  


																					if($nome_usuario){
																						echo 'PCR - '.$nome_usuario;
																					}else{
																						echo '-';
																					}
																					

																					?>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<?php  echo $_SESSION['Arquivos_Upload_Inc']['dataHora'][$Dcont] ?>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<?php  echo 'EM ANÁLISE' ?>
																				</td>
																				<td class='textonormal' bgcolor="#ffffff">
																					<?php  echo $_SESSION['Arquivos_Upload_Inc']['observacao'][$Dcont] ?>
																				</td>
																			</tr>
																		<?php  

																			//$arquivo =  $_SESSION['Arquivos_Upload_Inc']['nome'][$Dcont];
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
													<?php  } ?>
													</table>
												</td>
											</tr>
											
								  <tr>
								  	<td colspan="4" align="right">
									  	<input type="hidden" name="Critica" size="1" value="2">
										<input type="hidden" name="Origem" value="E">
										<input type="hidden" name="Destino">
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
					document.CadGestaoFornecedorIncluir.CPF_CNPJ.focus()
				//-->
			</script>
		</body>
	</html>
	<?php 	exit;
	}


# Função de Navegação das Abas #
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
	$htm .=	"					<td background=\"../midia/aba_".$Ter.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('C');\" class=\"textoabas".$Ter."\">&nbsp;QUALIF.&nbsp;ECON.&nbsp;FINAN.&nbsp;</a></td>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Ter."_d.gif\" width=\"4\" valign=\"center\">&nbsp;</td>\n";
	$htm .=	"				</tr>\n";
	$htm .=	"			</table>\n";
	$htm .=	"		</td>\n";
	$htm .=	"		<td valign=\"bottom\">\n";
	$htm .=	"			<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" summary=\"\">\n";
	$htm .=	"				<tr>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Qua."_e.gif\" width=\"10\" valign=\"center\">&nbsp;</td>\n";
	$htm .=	"					<td background=\"../midia/aba_".$Qua.".gif\" valign=\"center\" class=\"textoabasoff\"><a href=\"javascript:Submete('D');\" class=\"textoabas".$Qua."\">&nbsp;QUALIF.&nbsp;TÉCNICA&nbsp;</a></td>\n";
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
}
?>
