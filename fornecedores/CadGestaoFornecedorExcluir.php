<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadGestaoFornecedorExcluir.php
# Autor:    Roberta Costa
# Data:     26/08/2004
# Objetivo: Programa que Exibe os Dados do Fornecedor e Confirma a Exlusão
#-----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     26/06/2006 - Integridade antes de excluir / Correções para não
#                        rodar funções de formatação em variáveis vazias
#-----------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data:     15/05/2007 - Exibir preenchimento do Motivo
#                      - Retirada do $_SESSION['GetUrl'] = array();
# 			    29/05/2007 - Exibir comissão e data análise documentação
#                      - Exibir novos campos (índice Endividamento e Microempresa ou EPP)
#-----------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     18/06/2007 - Receber novo campo (índice de solvência)
#-----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     02/06/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
#                      - Alteração do nome do arquivo de "CadIncluirClasses.php" para "CadIncluirGrupos.php"
#-----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     02/06/2011 - Tarefa Redmine: 2727 - Incluir novos campos para preenchimento no SICREF - Dados do Representante Legal
#-----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     16/09/2011 - Tarefa Redmine: 3718 - Remoção de campos de Representante Legal no módulo de fornecedores (SICREF)
#-----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     25/07/2018
# Objetivo: Tarefa Redmine 80154
#-----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "funcoesFornecedores.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/CadGestaoFornecedor.php' );
AddMenuAcesso( '/fornecedores/CadGestaoFornecedorSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "GET" ){
		$Origem          = $_GET['Origem'];
		$Destino         = $_GET['Destino'];
		$Sequencial      = $_GET['Sequencial'];
		$FornecedorDecon = $_GET['FornecedorDecon'];
}else{
		$NCaracteres  	 = $_POST['NCaracteres'];
		$Botao           = $_POST['Botao'];
		$Sequencial      = $_POST['Sequencial'];
		$Motivo          = strtoupper2(trim($_POST['Motivo']));
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$Mens     = 0;
$Mensagem = "";
# Redireciona o programa de acordo com o botão voltar #
if( $Botao == "Voltar" ){
		$Url = "CadGestaoFornecedor.php?Sequencial=$Sequencial";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}elseif( $Botao == "Excluir" ){
		# Verifica se o Fornecedor está Ligado a algum Sistema(DECON) #
		/*if( $FornecedorDecon == "" ){
				$NomePrograma = urlencode("CadGestaoFornecedorExcluir.php");
				$Url = "fornecedores/$NomePrograma?ProgramaSelecao=$ProgramaSelecao&InscricaoValida=$InscricaoValida&Origem=B&Destino=$Destino&Sequencial=$Sequencial";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				Redireciona($Url);
				exit;
		}elseif( $FornecedorDecon == "N" ){*/

		if ( ( $Motivo == "" ) or ( $Motivo == "NULL" ))  {
					$Botao      = "";
				 	$Mens 		 	= 1;
				 	$Tipo       = 2;
					$Mensagem  .= "<a href=\"javascript:document.CadGestaoFornecedorExcluir.Motivo.focus();\" class=\"titulo2\">Informe: Motivo</a>";

		} elseif (strlen($Motivo) > 200){
					$Botao      = "";
				 	$Mens 		 	= 1;
				 	$Tipo       = 2;
					$Mensagem  .= "<a href=\"javascript:document.CadGestaoFornecedorExcluir.Motivo.focus();\" class=\"titulo2\">Informe: Motivo no Máximo com 200 Caracteres</a>";

		} else {
				# Verifica se o Fornecedor está relacionado com a entrada de nota fiscal #
				$db   = Conexao();
			  $sql  = "SELECT COUNT(*) FROM SFPC.TBENTRADANOTAFISCAL ";
			  $sql .= "	WHERE AFORCRSEQU = $Sequencial ";
			  $sql .= "    OR CFORESCODI = $Sequencial ";
			  $res  = $db->query($sql);
			  if( PEAR::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Qtd = $res->fetchRow();
						if( $Qtd[0] > 0 ){
		    				$Mens     = 1;
		    				$Tipo     = 2;
		    				# Verificação no módulo de estoques
					 			if($Qtd[0] == 1) $Mensagem = urlencode("Exclusão Cancelada! Fornecedor Ligado a (".$Qtd[0].") Nota Fiscal");
					 			if($Qtd[0] >  1) $Mensagem = urlencode("Exclusão Cancelada! Fornecedor Ligado a (".$Qtd[0].") Nota(s) Fiscal(is)");
					 			$Url = "CadGestaoFornecedorSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						}else{
								# Apaga grupos do Fornecedor #
								$db	= Conexao();
								$db->query("BEGIN TRANSACTION");
								$sql    = "DELETE FROM SFPC.TBGRUPOFORNECEDOR";
								$sql   .= " WHERE AFORCRSEQU = $Sequencial";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
								    $db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}

								# Apaga Conta Bancária do Fornecedor #
								$sql    = "DELETE FROM SFPC.TBFORNCONTABANCARIA ";
								$sql   .= " WHERE AFORCRSEQU = $Sequencial";
								$result = $db->query($sql);

																if( PEAR::isError($result) ){
								    $db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}

								# Apaga Certidões do Fornecedor #
								$sql    = "DELETE FROM SFPC.TBFORNECEDORCERTIDAO ";
								$sql   .= " WHERE AFORCRSEQU = $Sequencial";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
								    $db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}

								# Apaga Ocorrências do Fornecedor #
								$sql    = "DELETE FROM SFPC.TBFORNECEDOROCORRENCIA";
								$sql   .= " WHERE AFORCRSEQU = $Sequencial";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
								    $db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}

								# Apaga CHF do Fornecedor #
								$sql    = "DELETE FROM SFPC.TBFORNECEDORCHF";
								$sql   .= " WHERE AFORCRSEQU = $Sequencial";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
								    $db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}

								$DataAtual = date("Y-m-d H:i:s");

								# Altera o Fornecedor da SFPC.TBFORNECEDORCREDENCIADO para a situação de Excluído #
								$sql    = "UPDATE SFPC.TBFORNECEDORCREDENCIADO ";
								$sql   .= "   SET NFORCRSENH = NULL, NFORCRFANT = NULL, CCEPPOCODI = NULL, ";
								$sql   .= "       EFORCRLOGR = NULL, AFORCRNUME = NULL, EFORCRCOMP = NULL, ";
								$sql   .= "       EFORCRBAIR = NULL, NFORCRCIDA = NULL, CFORCRESTA = NULL, ";
								$sql   .= "       AFORCRCDDD = NULL, AFORCRTELS = NULL, AFORCRNFAX = NULL, ";
								$sql   .= "       NFORCRMAIL = NULL, NFORCRCONT = NULL, NFORCRCARG = NULL, ";
								$sql   .= "       AFORCRDDDC = NULL, AFORCRTELC = NULL, AFORCRREGJ = NULL, ";
								$sql   .= "       DFORCRREGJ = NULL, AFORCRINES = NULL, AFORCRINME = NULL, ";
								$sql   .= "       AFORCRINSM = NULL, VFORCRCAPS = NULL, VFORCRCAPI = NULL, ";
								$sql   .= "       VFORCRPATL = NULL, VFORCRINLC = NULL, VFORCRINLG = NULL, ";
								$sql   .= "       AFORCRENTR = NULL, NFORCRENTP = NULL, DFORCRVIGE = NULL, ";
								$sql   .= "       CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", ";
								$sql   .= "       TFORCRULAT ='$DataAtual' ";
								$sql   .= " WHERE AFORCRSEQU = $Sequencial";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}

								# Altera a situação do Fornecedor par Excluído #
								$sql    = "INSERT INTO SFPC.TBFORNSITUACAO (";
								$sql   .= "AFORCRSEQU, CFORTSCODI, DFORSISITU, EFORSIMOTI, ";
								$sql   .= "DFORSIEXPI, CGREMPCODI, CUSUPOCODI, TFORSIULAT ";
								$sql   .= ") VALUES (";
								$sql   .= "$Sequencial, 5, '".substr($DataAtual,0,10)."', '$Motivo', ";
								$sql   .= "NULL, ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '$DataAtual')";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
								    $db->query("ROLLBACK");
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}

								$db->query("COMMIT");
								$db->query("END");
								$db->disconnect();

								$Mensagem = "Exclusão Realizada com Sucesso";
								$Url = "CadGestaoFornecedorSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						/*}elseif( $FornecedorDecon == "S" ){
								$Mensagem = "Exclusão Cancelada! Fornecedor Ligado ao sistema DECON.";
								$Url = "CadGestaoFornecedorSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						}*/
						}
				}
		}
}
if( $Botao == "" ){
		$NCaracteres = strlen($Motivo);

		# Busca os Dados da Tabela de fornecedor de Acordo com o sequencial do fornecedor  #
		$db	    = Conexao();
		$sql    = " SELECT AFORCRCCGC, AFORCRCCPF, AFORCRIDEN, NFORCRORGU, NFORCRRAZS, ";
		$sql   .= "        NFORCRFANT, CCEPPOCODI, CCELOCCODI, EFORCRLOGR, AFORCRNUME, ";
		$sql   .= "        EFORCRCOMP, EFORCRBAIR, NFORCRCIDA, CFORCRESTA, AFORCRCDDD, ";
		$sql   .= "        AFORCRTELS, AFORCRNFAX, NFORCRMAIL, AFORCRCPFC, NFORCRCONT, ";
		$sql   .= "        NFORCRCARG, AFORCRDDDC, AFORCRTELC, AFORCRREGJ, DFORCRREGJ, ";
		$sql   .= "        AFORCRINES, AFORCRINME, AFORCRINSM, VFORCRCAPS, VFORCRCAPI, ";
		$sql   .= "        VFORCRPATL, VFORCRINLC, VFORCRINLG, DFORCRULTB, DFORCRCNFC, ";
		$sql   .= "        NFORCRENTP, AFORCRENTR, DFORCRVIGE, AFORCRENTT, DFORCRGERA, ";
		$sql   .= "        FFORCRCUMP, ECOMLIDESC, DFORCRANAL, FFORCRMEPP, VFORCRINDI, ";
		$sql   .= "        VFORCRINSO  ";

		$sql   .= "   FROM SFPC.TBFORNECEDORCREDENCIADO FORN";
    	$sql   .= "   LEFT OUTER JOIN SFPC.TBCOMISSAOLICITACAO COM ON FORN.CCOMLICODI = COM.CCOMLICODI ";
		$sql   .= "  WHERE AFORCRSEQU = $Sequencial";
	  $result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();

				# Variáveis Formulário A #
				$CNPJ							= $Linha[0];
				$CPF							= $Linha[1];
				$MicroEmpresa			= $Linha[43];
				$Identidade   		= $Linha[2];
				$OrgaoUF       		= $Linha[3];
				$RazaoSocial  		= $Linha[4];
				$NomeFantasia			= $Linha[5];
				if( $Linha[6] != "" ){
						$CEP = $Linha[6];
				}else{
						$CEP = $Linha[7];
				}
				$Logradouro 			= $Linha[8];
				$Numero    				= $Linha[9];
				$Complemento			= $Linha[10];
				$Bairro   	 			= $Linha[11];
				$Cidade 					= $Linha[12];
				$UF       				= $Linha[13];
				$DDD       				= $Linha[14];
				$Telefone	 				= $Linha[15];
				$Fax      	   		= $Linha[16];
				$Email  					= $Linha[17];
				if( $Linha[18] != "" ){
						$CPFContato = FormataCPF($Linha[18]);
				}
				$NomeContato 			= $Linha[19];
				$CargoContato 		= $Linha[20];
				$DDDContato 			= $Linha[21];
				$TelefoneContato	= $Linha[22];
				$RegistroJunta		= $Linha[23];
				if($Linha[24]) $DataRegistro = DataBarra($Linha[24]);

				# Variáveis Formulário B #
				$InscEstadual			= $Linha[25];
				$InscMercantil		= $Linha[26];
				$InscOMunic				= $Linha[27];

				# Variáveis Formulário C #
				$CapSocial				= str_replace(".",",",$Linha[28]);
				$CapIntegralizado	= str_replace(".",",",$Linha[29]);
				$Patrimonio				= str_replace(".",",",$Linha[30]);
				$IndLiqCorrente		= str_replace(".",",",$Linha[31]);
				$IndLiqGeral			= str_replace(".",",",$Linha[32]);
				$IndEndividamento = str_replace(".",",",$Linha[44]);
				$IndSolvencia     = str_replace(".",",",$Linha[45]);
				if($Linha[33]) $DataBalanco  = DataBarra($Linha[33]);
				if($Linha[34]) $DataNegativa = DataBarra($Linha[34]);

				# Variáveis Formulário D #
				$NomeEntidade			= $Linha[35];
				$RegistroEntidade	= $Linha[36];
				if($Linha[37]) $DataVigencia  = DataBarra($Linha[37]);
				$RegistroTecnico = $Linha[38];
				if($Linha[39]) $DataInscricao = DataBarra($Linha[39]);
				$Cumprimento	   = $Linha[40];
				$ComissaoResp		 = $Linha[41];
				if( $Linha[42] <> "" ){
						$DataAnaliseDoc= substr($Linha[42],8,2)."/".substr($Linha[42],5,2)."/".substr($Linha[42],0,4);
				}	else {
						$DataAnaliseDoc= "";
				}
		}

		# Busca os Dados da Tabela de Situação de acordo com o sequencial do Fornecedor #
		$sql    = "SELECT A.DFORSISITU, B.CFORTSCODI, A.EFORSIMOTI, A.DFORSIEXPI ";
		$sql   .= "  FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B ";
		$sql   .= " WHERE A.AFORCRSEQU = $Sequencial ";
		$sql   .= "   AND A.CFORTSCODI = B.CFORTSCODI ";
		$sql   .= " ORDER BY A.DFORSISITU DESC	";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				for( $i=0;$i<1;$i++ ){
						$Linha 	= $result->fetchRow();
						if( $Linha[0] != "" ){
								$DataSituacao = substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
						}else{
								$DataSituacao = "";
						}
						$Situacao	= $Linha[1];
						if( $Linha[3] != "" ){
								$DataSuspensao = substr($Linha[3],8,2)."/".substr($Linha[3],5,2)."/".substr($Linha[3],0,4);
						}else{
								$DataSuspensao = "";
						}
				}
		}

		# Mostra Tabela de Situação #
		$sql    = "SELECT EFORTSDESC FROM SFPC.TBFORNECEDORTIPOSITUACAO";
		$sql   .= " WHERE CFORTSCODI = $Situacao";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$situacao = $result->fetchRow();
				$DescSituacao = $situacao[0];
		}

		# Verifica se já Existe Data de CHF #
		$sql    = "SELECT DFORCHGERA, DFORCHVALI FROM SFPC.TBFORNECEDORCHF ";
		$sql   .= " WHERE AFORCRSEQU = $Sequencial ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $result->numRows();
				if( $Rows != 0 ){
						$Linha 	         = $result->fetchRow();
						if($Linha[0]) $DataGeracaoCHF  = DataBarra($Linha[0]);
						if($Linha[1]) $DataValidadeCHF = DataBarra($Linha[1]);
				}
		}

		# Busca os Dados da Tabela de Conta Bancária de acordo com o sequencial do Fornecedor #
		$sql    = "SELECT CFORCBBANC, CFORCBAGEN, CFORCBCONT, TFORCBULAT ";
		$sql   .= "  FROM SFPC.TBFORNCONTABANCARIA ";
		$sql   .= " WHERE AFORCRSEQU = $Sequencial ";
		$sql   .= " ORDER BY TFORCBULAT";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $result->numRows();
				for( $i=0;$i<$Rows;$i++ ){
						$Linha 	= $result->fetchRow();
						if( $i == 0	){
								$Banco1					= $Linha[0];
								$Agencia1				= $Linha[1];
								$ContaCorrente1	= $Linha[2];
						}else{
								$Banco2					= $Linha[0];
								$Agencia2				= $Linha[1];
								$ContaCorrente2	= $Linha[2];
						}
				}
		}
		$db->disconnect();
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function ncaracteres(valor){
	document.CadGestaoFornecedorExcluir.NCaracteres.value = '' +  document.CadGestaoFornecedorExcluir.Motivo.value.length;
//	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
//		document.CadGestaoFornecedorExcluir.NCaracteres.focus();
//	}
}
function enviar(valor){
	document.CadGestaoFornecedorExcluir.Botao.value = valor;
	document.CadGestaoFornecedorExcluir.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="Stylesheet" type="Text/Css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadGestaoFornecedorExcluir.php" method="post" name="CadGestaoFornecedorExcluir">
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
			<?php if( $Mens <> 0 ){ ExibeMens($Mensagem,$Tipo,$Virgula);	}?>
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
		    					EXCLUIR - CADASTRO E GESTÃO DE FORNECEDOR
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
									<p align="justify">
										Para confirmar a exclusão do fornecedor, informe o Motivo, se desejar, e clique no botão "Excluir". Este procedimento acarretará na perda dos dados do fornecedor.
	          	   	</p>
	          		</td>
		        	</tr>
        	    <tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="100%" summary="">
										<tr>
											<td>
						  					<table class="textonormal" border="0" cellpadding="0" cellspacing="2" width="100%" summary="">
					   	    				<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Motivo</td>
														<td class="textonormal">máximo de 200 caracteres <input class="textonormal" type="text" name="NCaracteres" size="3" value="<?php echo $NCaracteres ?>" OnFocus="javascript:document.Avaliacao.Motivo.focus();"><br>
															<textarea class="textonormal" name="Motivo" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)"><?php echo $Motivo ?></textarea>
														</td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" width="45%">Cumprimento</td>
														<td class="textonormal" height="20">
				              				<?php if( $Cumprimento == "S" ){ echo "SIM"; } else { echo "NÃO"; } ?>
														</td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Data de Geração do CHF&nbsp;</td>
														<td class="textonormal" height="20">
															<?php if( $DataGeracaoCHF != "" ){ echo $DataGeracaoCHF; }else{ echo "NÃO INFORMADO"; } ?>
									  				</td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Data de Validade do CHF&nbsp;</td>
														<td class="textonormal" height="20">
															<?php if( $DataValidadeCHF != "" ){ echo $DataValidadeCHF; }else{ echo "-"; } ?>
									  				</td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Data de Cadastramento</td>
														<td class="textonormal" height="20"><?php echo $DataInscricao; ?></td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="45%">Comissão Responsável Análise </td>
														<td class="textonormal"><?php echo $ComissaoResp;?></td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="45%">Data da Análise </td>
														<td class="textonormal"><?php echo $DataAnaliseDoc;?></td>
									  			</tr>
												</table>
						  				</td>
						  			</tr>
									</table>
								</td>
		        	</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
			          		<!-- OCORRÊNCIAS -->
			          		<tr>
	              			<td bgcolor="#75ADE6" colspan="2" class="textoabasoff" align="center" height="20">OCORRÊNCIAS</td>
	              		</tr>
										<tr>
											<td colspan="2" class="textonormal">
			              		<?php
												$db	  = Conexao();
												$sql  = "SELECT A.CFORTOCODI, A.EFOROCDETA, A.DFOROCDATA, B.EFORTODESC ";
												$sql .= "  FROM SFPC.TBFORNECEDOROCORRENCIA A, SFPC.TBFORNTIPOOCORRENCIA B";
												$sql .= " WHERE A.CFORTOCODI = B.CFORTOCODI AND A.AFORCRSEQU = $Sequencial ORDER BY 3,1";
												$res  = $db->query($sql);
											  if( PEAR::isError($res) ){
													  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $res->numRows();
														if( $Rows == 0 ){
										            echo "<center>NENHUMA OCORRÊNCIA ENCONTRADA</center></td></tr>\n";
														}else{
																echo "<table class=\"textonormal\" border=\"1\" align=\"left\" bordercolor=\"#75ADE6\" cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">";
																for( $i=0;$i<$Rows;$i++ ){
																		$Linha     = $res->fetchRow();
						          	      			$Codigo    = $Linha[0];
						          	      			$Detalhe   = $Linha[1];
						          	      			$Data      = $Linha[2];
						          	      			$Descricao = $Linha[2];
						          	      			if( $i == 0 ){
						          			            echo "			<tr>\n";
																				echo "				<td align=\"center\" bgcolor=\"#DCEDF7\" colspan=\"3\" class=\"titulo3\">OCORRÊNCIAS</td>\n";
																				echo "			</tr>\n";
														            echo "<tr>\n";
													        	    echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\" width=\"11%\">DATA</td>\n";
														            echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">TIPO DE OCORRÊNCIA</td>\n";
													        	    echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">DETALHAMENTO</td>\n";
													        	    echo "</tr>\n";
											        	  	}
												            echo "<tr>\n";
											        	    echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" align=\"center\">".substr($Data,8,2)."/".substr($Data,5,2)."/".substr($Data,0,4)."</td>\n";
												            echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\" align=\"center\">".strtoupper2($Descricao)."</td>\n";
											        	    echo "  <td class=\"textonormal\" bgcolor=\"#F7F7F7\">$Detalhe</td>\n";
											        	    echo "</tr>\n";
							                	}
											          echo "</table>";
							              }
												}
			              		?>
											</td>
										</tr>
			        		</table>
			        	</td>
			        </tr>
			        <tr>
								<td>
									<table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
			          		<!-- HABLITAÇÃO JURÍDICA -->
			          		<tr>
	              			<td bgcolor="#75ADE6" colspan="2" class="textoabasoff" align="center" height="20">HABLITAÇÃO JURÍDICA</td>
	              		</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">
												<?php if( $CNPJ <> 0 ){ echo "CNPJ\n"; } else { echo "CPF\n"; }?>
          	    			</td>
											<td class="textonormal" height="20">
		          	    		<?php
												if($CNPJ){
    												echo substr($CNPJ,0,2).".".substr($CNPJ,2,3).".".substr($CNPJ,5,3)."/".substr($CNPJ,8,4)."-".substr($CNPJ,12,2);
  											}elseif($CPF){
	    											echo substr($CPF,0,3).".".substr($CPF,3,3).".".substr($CPF,6,3)."-".substr($CPF,9,2);
    										}
												?>
	          	    		</td>
	            			</tr>
										<?php if( $CNPJ <> 0 ){ ?>
	            				</tr>
												<td class="textonormal" bgcolor="#DCEDF7"><?php echo getDescPorteEmpresaTitulo(); ?></td>
												<td class="textonormal" height="20"><?php echo getDescPorteEmpresa($MicroEmpresa) ?></td>
	            				</tr>
										<?php	} ?>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">
				              	<?php if( $CNPJ != "" ){ echo "Identidade Repres.Legal(Empr.Individual)\n"; }else{ echo "Identidade\n"; } ?>
				              </td>
											<td class="textonormal" height="20">
				              	<?php if( $Identidade != "" ){ echo $Identidade; } else { echo "NÃO INFORMADO"; } ?>
											</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Órgão Emissor/UF</td>
											<td class="textonormal" height="20">
												<?php if( $OrgaoUF != "" ){ echo $OrgaoUF; } else { echo "NÃO INFORMADO"; } ?>
											</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Razão Social/Nome</td>
				              <td class="textonormal" height="20"><?php echo $RazaoSocial;?></td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Nome Fantasia</td>
				              <td class="textonormal" height="20">
				              	<?php if( $NomeFantasia != "" ){ echo $NomeFantasia; } else { echo "NÃO INFORMADO"; } ?>
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">CEP</td>
											<td class="textonormal" height="20"><?php if($CEP) echo substr($CEP,0,2).".".substr($CEP,2,3)."-".substr($CEP,5,3); ?></td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Logradouro</td>
				              <td class="textonormal" height="20"><?php echo $Logradouro; ?></td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Número</td>
 				              <td class="textonormal" height="20">
				              	<?php if( $Numero != "" ){ echo $Numero; } else { echo "NÃO INFORMADO"; } ?>
				              </td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Complemento</td>
				              <td class="textonormal" height="20">
				              	<?php if( $Complemento != "" ){ echo $Complemento; } else { echo "NÃO INFORMADO"; } ?>
				              </td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Bairro</td>
				              <td class="textonormal" height="20"><?php echo $Bairro; ?></td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Cidade</td>
				              <td class="textonormal" height="20"><?php echo $Cidade; ?></td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">UF</td>
		    	      			<td class="textonormal" height="20"><?php echo $UF; ?></td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">DDD</td>
				              <td class="textonormal" height="20">
				              	<?php if( $DDD != "" ){ echo $DDD; } else { echo "NÃO INFORMADO"; } ?>
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Telefone(s)</td>
											<td class="textonormal" height="20">
												<?php if( $Telefone != "" ){ echo $Telefone; } else { echo "NÃO INFORMADO"; } ?>
											</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">E-mail</td>
											<td class="textonormal" height="20">
												<?php if( $Email != "" ){ echo $Email; } else { echo "NÃO INFORMADO"; } ?>
											</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Fax</td>
											<td class="textonormal" height="20">
												<?php if( $Fax != "" ){ echo $Fax; } else { echo "NÃO INFORMADO"; } ?>
											</td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Registro Junta Comercial ou Cartório</td>
											<td class="textonormal" height="20"><?php echo $RegistroJunta; ?></td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="45%">Data Reg. Junta Comercial ou Cartório</td>
				              <td class="textonormal" height="20"><?php echo $DataRegistro; ?></td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">CPF do Contato</td>
											<td class="textonormal" height="20">
												<?php if( $CPFContato != "" ){ echo $CPFContato; } else { echo "NÃO INFORMADO"; } ?>
											</td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Nome do Contato</td>
											<td class="textonormal" height="20">
												<?php if( $NomeContato != "" ){ echo $NomeContato; } else { echo "NÃO INFORMADO"; } ?>
											</td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Cargo do Contato</td>
											<td class="textonormal" height="20">
												<?php if( $CargoContato != "" ){ echo $CargoContato; } else { echo "NÃO INFORMADO"; } ?>
											</td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">DDD do Contato</td>
											<td class="textonormal" height="20">
												<?php if( $DDDContato != "" ){ echo $DDDContato; } else { echo "NÃO INFORMADO"; } ?>
											</td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Telefone do Contato</td>
											<td class="textonormal" height="20">
												<?php if( $TelefoneContato != "" ){ echo $TelefoneContato; } else { echo "NÃO INFORMADO"; } ?>
											</td>
				            </tr>
			        		</table>
			        	</td>
			        </tr>
			        <tr>
								<td>
									<table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
										<!-- REGULARIDADE FISCAL -->
			          		<tr>
	              			<td bgcolor="#75ADE6" colspan="2" class="textoabasoff" align="center" height="20">REGULARIDADE FISCAL</td>
	              		</tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Inscrição Municipal Recife</td>
				              <td class="textonormal" height="20">
				              	<?php if( $InscMercantil != "" ) { echo $InscMercantil; }else{ echo "-"; } ?>
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="45%">Inscrição Outro Município</td>
											<td class="textonormal" height="20">
												<?php if( $InscOMunic != "" ) { echo $InscOMunic; }else{ echo "-"; } ?>
											</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Inscrição Estadual</td>
				              <td class="textonormal" height="20">
				              	<?php if( $InscEstadual != "" ) { echo $InscEstadual; }else{ echo "NÃO INFORMADO"; } ?>
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" colspan="2">
												<table border="1" cellpadding="3" cellspacing="0" bgcolor="#DCEDF7" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
      			          		<tr>
				              			<td bgcolor="#BFDAF2" class="textoabason" colspan="2" align="center">CERTIDÃO FISCAL</td>
				              		</tr>
				              		<tr>
				              			<td bgcolor="#FFFFFF" class="textoabason" colspan="2" align="center">OBRIGATÓRIAS</td>
				              		</tr>
	              					<tr>
	              						<td bgcolor="#DCEDF7" class="textonormal">Nome da Certidão</td>
	              						<td bgcolor="#DCEDF7" class="textonormal">Data de Validade</td>
	              					</tr>
		              				<?php
				              		# Mostra a lista de certidões obrigatórias #
				              		$sql = "SELECT CTIPCECODI, ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO WHERE FTIPCEOBRI = 'S' ORDER BY 1";
  												$res = $db->query($sql);
												  if( PEAR::isError($res) ){
														  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															$Rows = $res->numRows();
						              		for( $i=0; $i< $Rows;$i++ ){
																	$Linha = $res->fetchRow();
					          	      			$DescricaoOb = substr($Linha[1],0,75);
					          	      			$CertidaoOb  = $Linha[0];
					          	      			echo "<tr>\n";
					              					echo "	<td class=\"textonormal\" bgcolor=\"#FFFFFF\" width=\"*\">$DescricaoOb</td>\n";
				              						echo "	<td class=\"textonormal\" bgcolor=\"#FFFFFF\" width=\"22%\" align=\"center\">\n";

			                  	      	# Verifica se existem certidões obrigatórias cadastradas para o Fornecedor #
				  												$sqlData  = "SELECT DFORCEVALI FROM SFPC.TBFORNECEDORCERTIDAO ";
				  												$sqlData .= " WHERE AFORCRSEQU = $Sequencial AND CTIPCECODI = $CertidaoOb";
				  												$resData  = $db->query($sqlData);
																  if( PEAR::isError($resData) ){
																		  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	}else{
																			$LinhaData = $resData->fetchRow();
							          	      			if( $LinhaData[0] <> 0 ){
																					$DataCertidaoOb[$ob-1] = substr($LinhaData[0],8,2)."/".substr($LinhaData[0],5,2)."/".substr($LinhaData[0],0,4);
																			}else{
																					$DataCertidaoOb[$ob-1] = null;
										                	}
									                }
									                echo $DataCertidaoOb[$ob-1];
								            	}
										          echo "	</td>\n";
		              						echo "</tr>\n";
					      		    	}

			              			# Verifica se existem certidões Complementares cadastradas para o Fornecedor #
  												$sql  = "SELECT A.DFORCEVALI, B.CTIPCECODI, B.ETIPCEDESC  ";
  												$sql .= "  FROM SFPC.TBFORNECEDORCERTIDAO A, SFPC.TBTIPOCERTIDAO B ";
  												$sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CTIPCECODI = B.CTIPCECODI";
	  											$sql .= "   AND B.FTIPCEOBRI = 'N' ORDER BY 2";
	  											$res  = $db->query($sql);
												  if( PEAR::isError($res) ){
														  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															$Rows = $res->numRows();
															if( $Rows != 0 ){
						              				# Mostra as certidões Complementares cadastradas #
												          echo "<tr>\n";
								              		echo "	<td bgcolor=\"#FFFFFF\" class=\"textoabason\" colspan=\"2\" align=\"center\">COMPLEMENTAR</td>\n";
								              		echo "</tr>\n";
						              				echo "<tr>\n";
						              				echo "	<td bgcolor=\"#DCEDF7\" class=\"textonormal\">Nome da Certidão</td>\n";
						              				echo "	<td bgcolor=\"#DCEDF7\" class=\"textonormal\">Data de Validade</td>\n";
						              				echo "</tr>\n";
								              		for( $i=0; $i<$Rows;$i++ ){
																			$Linha                    = $res->fetchRow();
							          	      			$DescricaoOp							= substr($Linha[2],0,75);
							          	      			$CertidaoOpCodigo					= $Linha[1];
							          	      			$CertidaoComplementar[$i] = $Linha[1];
																			$DataCertidaoOp[$i]		    = substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
								              				echo "			<tr>\n";
							              					echo "				<td class=\"textonormal\" bgcolor=\"#FFFFFF\" width=\"*\">$DescricaoOp</td>\n";
						              						echo "				<td class=\"textonormal\" bgcolor=\"#FFFFFF\" width=\"22%\" align=\"center\">".$DataCertidaoOp[$i]."</td>\n";
						              						echo "			</tr>\n";
									                }
							                }
							            }
													?>
						        		</table>
						        	</td>
				        		</tr>
			        		</table>
			        	</td>
			        </tr>
			        <tr>
								<td>
									<table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
										<!-- QUALIFICAÇÃO ECONÔMICA E FINANCEIRA -->
          		      <tr>
	              			<td bgcolor="#75ADE6" class="textoabasoff" colspan="2" align="center" height="20">QUALIFICAÇÃO ECONÔMICA E FINANCEIRA</td>
	              		</tr>
										<?php if( $CNPJ != "" ){ ?>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Capital Social Subscrito</td>
				              <td class="textonormal" height="20"><?php echo $CapSocial;?></td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Capital Integralizado </td>
				              <td class="textonormal" height="20">
				              	<?php if( $CapIntegralizado != "" ){ echo $CapIntegralizado; } else { echo "NÃO INFORMADO";} ?>
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Patrimônio Líquido </td>
				              <td class="textonormal" height="20"><?php echo $Patrimonio;?></td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="45%">Índice de Liquidez Corrente </td>
				              <td class="textonormal" height="20">
				              	<?php if( $IndLiqCorrente != "" ){ echo $IndLiqCorrente; } else { echo "NÃO INFORMADO";} ?>
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Índice de Liquidez Geral </td>
											<td class="textonormal" height="20">
												<?php if( $IndLiqGeral != "" ){ echo $IndLiqGeral; } else { echo "NÃO INFORMADO";} ?>
											</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Índice de Endividamento</td>
											<td class="textonormal" height="20">
												<?php if( $IndEndividamento != "" ){ echo $IndEndividamento; } else { echo "NÃO INFORMADO";} ?>
											</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Índice de Solvência</td>
											<td class="textonormal" height="20">
												<?php if( $IndSolvencia != "" ){ echo $IndSolvencia; } else { echo "NÃO INFORMADO";} ?>
											</td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Data de validade do balanço</td>
				              <td class="textonormal" height="20"><?php echo $DataBalanco;?></td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Data de Certidão Negativa de Falência ou Concordata</td>
				              <td class="textonormal" height="20"><?php echo $DataNegativa;?></td>
				            </tr>
				            <?php } ?>
										<tr>
				            	<td colspan="2">
					            	<table align="center" border="1" cellpadding="2" cellspacing="0" bgcolor="#DCEDF7" bordercolor="#75ADE6" class="textonormal" width="100%" summary="">
					              	<tr>
					              		<td class="textonormal" bgcolor="#DCEDF7" align="center">Banco</td>
					              		<td class="textonormal" bgcolor="#DCEDF7" align="center">Agência </td>
					              		<td class="textonormal" bgcolor="#DCEDF7" align="center">Conta Corrente</td>
					              	</tr>
					              	<tr>
					              		<?php if( $Banco1 != "" ){?>
					              		<td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $Banco1;?></td>
					            	  	<td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $Agencia1;?></td>
							              <td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $ContaCorrente1;?></td>
							              </tr>
								          	<?php }elseif( $Banco2 != "" ){?>
								            <tr>
								          	  <td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $Banco2;?></td>
								              <td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $Agencia2; ?></td>
								              <td class="textonormal" bgcolor="#FFFFFF" align="center"><?php echo $ContaCorrente2;?></td>
							            	<?php }else{?>
							            		<td class="textonormal" bgcolor="#FFFFFF" align="center" colspan="3"><?php echo "NÃO INFORMADO";?></td>
							            	<?php } ?>
					            		</tr>
					            	</table>
					            </td>
				            </tr>
			        		</table>
			        	</td>
			        </tr>
			        <tr>
								<td>
									<table cellpadding="0" cellspacing="2" border="0" bordercolor="#75ADE6" width="100%" summary="">
										<!-- QUALIFICAÇÃO TÉCNICA -->
							      <tr>
	              			<td bgcolor="#75ADE6" class="textoabasoff" colspan="2" align="center" height="20">QUALIFICAÇÃO TÉCNICA</td>
	              		</tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Nome</td>
				              <td class="textonormal" height="20">
				              	<?php if( $NomeEntidade != "" ){ echo "$NomeEntidade"; } else { echo "NÃO INFORMADO"; } ?>
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="45%">Registro ou Inscrição </td>
				              <td class="textonormal" height="20">
				              	<?php if( $RegistroEntidade != "" ){ echo "$RegistroEntidade"; } else { echo "NÃO INFORMADO"; } ?>
				              </td>
				            </tr>
										<tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Data da Vigência</td>
				              <td class="textonormal" height="20">
				              	<?php if( $DataVigencia != "" ){ echo "$DataVigencia"; } else { echo "NÃO INFORMADO"; } ?>
				              </td>
				           	</tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="45%">Registro ou Inscrição do Técnico</td>
				              <td class="textonormal" height="20">
				              	<?php if( $RegistroTecnico != "" ){ echo "$RegistroTecnico"; } else { echo "NÃO INFORMADO"; } ?>
				              </td>
				            </tr>
										<tr>
				              <td class="textonormal" colspan="4">
												<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
      			          		<tr>
				              			<td bgcolor="#BFDAF2" class="textoabason" colspan="4" align="center">AUTORIZAÇÃO ESPECÍFICA</td>
				              		</tr>
				              		<?php
			                		# Mostra as autorizações específicas já cadastradas do Inscrito #
  												$sql  = "SELECT NFORAENOMA, AFORAENUMA, DFORAEVIGE ";
  												$sql .= "  FROM SFPC.TBFORNAUTORIZACAOESPECIFICA ";
  												$sql .= " WHERE AFORCRSEQU = ".$_SESSION['Sequencial'];
													$res = $db->query($sql);
												  if( PEAR::isError($res) ){
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															$Rows = $res->numRows();
															if( $Rows != 0 ){
								              		echo "<tr>\n";
								              		echo "	<td bgcolor=\"#DCEDF7\" class=\"textonormal\">Nome</td>\n";
								              		echo "	<td bgcolor=\"#DCEDF7\" class=\"textonormal\">Registro ou Inscrição</td>\n";
								              		echo "	<td bgcolor=\"#DCEDF7\" class=\"textonormal\" align=\"center\">Data de Vigência</td>\n";
								              		echo "</tr>\n";
								              		for( $i=0; $i< $Rows;$i++ ){
																			$Linha        	                     = $res->fetchRow();
							          	      			$_SESSION['AutorizacaoNome'][$i]     = $Linha[0];
							          	      			$_SESSION['AutorizacaoRegistro'][$i] = $Linha[1];
							          	      			$_SESSION['AutorizacaoData'][$i]     = substr($Linha[2],8,2)."/".substr($Linha[2],5,2)."/".substr($Linha[2],0,4);
							          	      			$_SESSION['AutoEspecifica'][$i]      = $_SESSION['AutorizacaoNome'][$i]."#".$_SESSION['AutorizacaoRegistro'][$i];
								              				echo "			<tr>\n";
								              				echo "				<td class=\"textonormal\" bgcolor=\"#FFFFFF\">".$_SESSION['AutorizacaoNome'][$i]."</td>\n";
								              				echo "				<td class=\"textonormal\" bgcolor=\"#FFFFFF\">".$_SESSION['AutorizacaoRegistro'][$i]."</td>\n";
								              				echo "				<td class=\"textonormal\" bgcolor=\"#FFFFFF\" align=\"center\">".$_SESSION['AutorizacaoData'][$i]."</td>\n";
										    	      			echo "			</tr>\n";
						              				}
						              		}else{
										            	echo "<tr><td class=\"textonormal\" bgcolor=\"#FFFFFF\" align=\"center\">NENHUMA AUTORIZAÇÃO ESPECÍFICA INFORMADA</td></tr>\n";
						              		}
						              }
				              		?>
				              	</table>
				              </td>
				            </tr>
	              		<tr>
	              			<td colspan="2">
	              				<table border="1" cellpadding="2" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
						          		<tr>
				              			<td bgcolor="#BFDAF2" class="textoabason" colspan="2" align="center" height="20">GRUPOS DE FORNECIMENTO (OBJETO SOCIAL)</td>
				              		</tr>
		              				<?php
				              		# Mostra os grupos de materiais já cadastrados do Fornecedor #
													$sql  = "SELECT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
													$sql .= "  FROM SFPC.TBGRUPOFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B  ";
													$sql .= " WHERE A.AFORCRSEQU = ".$Sequencial." AND A.CGRUMSCODI = B.CGRUMSCODI ";
													$sql .= "   AND B.FGRUMSTIPO = 'M' ORDER BY 1,3";
													$res  = $db->query($sql);
												  if( PEAR::isError($res) ){
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															$Rows = $res->numRows();
															if( $Rows <> 0 ){
					              					# Mostra os grupos de materiais cadastrados #
								              		echo "<tr>\n";
								              		echo "	<td bgcolor=\"#DDECF9\" class=\"textonormal\" colspan=\"2\" align=\"center\">MATERIAIS</td>\n";
								              		echo "</tr>\n";
								              		$DescricaoGrupoAntes = "";
								              		for( $i=0; $i<$Rows;$i++ ){
																			$Linha										= $res->fetchRow();
							          	      			$DescricaoGrupo   				= substr($Linha[2],0,75);
										    	      			$Materiais[$i]= "M#".$Linha[1];
										    	      			if( $DescricaoGrupoAntes <> $DescricaoGrupo ){
												    	      			echo "			<tr>\n";
										              				echo "				<td bgcolor=\"#FFFFFF\" class=\"textonormal\" colspan=\"2\" height=\"18\">$DescricaoGrupo</td>\n";
										              				echo "			</tr>\n";
								              				}
										    	      		$DescricaoGrupoAntes = $DescricaoGrupo;
									    	      		}
								    	      	}
						            	}

			                		# Mostra os grupos de serviços já cadastrados do Fornecedor #
													$sql  = "SELECT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
													$sql .= "  FROM SFPC.TBGRUPOFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B  ";
													$sql .= " WHERE A.AFORCRSEQU = ".$Sequencial." AND A.CGRUMSCODI = B.CGRUMSCODI ";
													$sql .= "   AND B.FGRUMSTIPO = 'S' ORDER BY 1,3";
													$res  = $db->query($sql);
												  if( PEAR::isError($res) ){
														  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															$Rows = $res->numRows();
															if( $Rows <> 0 ){
					              					# Mostra os grupos de serviços cadastrados #
								              		echo "<tr>\n";
								              		echo "	<td bgcolor=\"#DDECF9\" class=\"textonormal\" colspan=\"2\" align=\"center\" height=\"20\">SERVIÇOS</td>\n";
								              		echo "</tr>\n";
								    	      			$DescricaoGrupoAntes = "";
								              		for( $i=0; $i<$Rows;$i++ ){
																		$Linha = $res->fetchRow();
						          	      			$DescricaoGrupo   = substr($Linha[2],0,75);
									    	      			$Servicos[$i]= "S#".$Linha[1];
									    	      			if( $DescricaoGrupo <> $DescricaoGrupoAntes ){
											    	      			echo "			<tr>\n";
									              				echo "				<td bgcolor=\"#FFFFFF\" class=\"textonormal\" colspan=\"2\" height=\"18\">$DescricaoGrupo</td>\n";
									              				echo "			</tr>\n";
							              				}

										    	      	$DescricaoGrupoAntes = $DescricaoGrupo;
									    	      		}
						              		}
							            }
							            $db->disconnect();
							            ?>
			              		</table>
			              	</td>
			            	</tr>
				          </table>
				        </td>
							</tr>
            	<tr>
								<td align="right">
									<input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
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
</form>
</body>
</html>
