<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: EmissaoCHF.php
# Autor:    Roberta Costa
# Data:     21/09/04
# Objetivo: Programa que Exibe os dados do CHF do Fornecedor Cadastrado
#---------------------------------
# Alterado: Rossana Lira
# Data:     16/05/07 - Troca do nome fornecedor para firma
# Data:     09/07/07 - Permitir emitir o CHF, mesmo estando com certidões fora do
#                      prazo de validade
#                    - Passar mensagem fornecedor c/certidões fora do prazo p/impressão
# Alterado: Everton Lino
# Data:     06/08/2010 - Verificação de data de balanço anual se está no prazo
# Alterado: Everton Lino
# Data:     14/10/2010- Correção
# Alterado: Ariston Cordeiro
# Data:     05/11/2010 - Alterando prazos de balanço anual e certidão negativa
# Alterado: Rodrigo Melo
# Data:     25/04/2011 - Retirando da mensagem de atenção a palavra "Inabilitado", devido a solicitação do usuário. Tarefa Redmine: 2205.
# Alterado: Pitang
# Data:     13/08/2014 - Inclui verificação para adicionar mensagem "Fornecedor INABILITADO CHF fora do prazo de validade". Redmine 108
#----------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
require_once( "funcoesFornecedores.php");

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/EmissaoCHFSenha.php' );
AddMenuAcesso( '/fornecedores/EmissaoCHFSelecionar.php' );
AddMenuAcesso( '/fornecedores/RelEmissaoCHFPdf.php' );
AddMenuAcesso( '/oracle/fornecedores/RotDebitoCredorConsulta.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET" ){
		$Sequencial     = $_GET['Sequencial'];
		$Irregularidade = $_GET['Irregularidade'];
    $CPF_CNPJ       = $_GET['CPF_CNPJ'];
    $TipoDoc        = $_GET['TipoDoc'];
    $TipoCnpjCpf    = $_GET['TipoCnpjCpf'];
    if( $TipoCnpjCpf == "CNPJ"){
    		$TipoDoc = 1;
  	}elseif( $TipoCnpjCpf == "CNPJ"){
  			$TipoDoc = 2;
  	}
    $Mens           = $_GET['Mens'];
    $Mensagem       = $_GET['Mensagem'];
    $Tipo           = $_GET['Tipo'];
}else{
		$Mensagem       = $_POST['Mensagem'];
		$Botao          = $_POST['Botao'];
		$Sequencial     = $_POST['Sequencial'];
		$CPF_CNPJ       = $_POST['CPF_CNPJ'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona o programa de acordo com o botão voltar #
if( $Botao == "Voltar" ){
		if( $_SESSION['_cperficodi_'] == 0 ){
			  header("location: EmissaoCHFSenha.php");
			  exit;
		}else{
				header("location: EmissaoCHFSelecionar.php");
				exit;
		}
}
$db	= Conexao();
if( $Botao == "Imprimir" ){
		$db->query("BEGIN TRANSACTION");
		if( $_SESSION['_cperficodi_'] == 0 ){
				$sql = " SELECT MAX(AFORCHNEMF) FROM SFPC.TBFORNECEDORCHF";
		}else{
				$sql = " SELECT MAX(AFORCHNEMU) FROM SFPC.TBFORNECEDORCHF";
		}
		$sql   .= " WHERE AFORCRSEQU = $Sequencial";
	  $result = $db->query($sql);
		if( PEAR::isError($result) ){
				$db->query("ROLLBACK");
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				if( $Linha[0] == 0 ){ $QtdVias = 1; }else{ $QtdVias = $Linha[0] + 1; }

				# Atualiza a tabela de CHF #
				$sql = " UPDATE SFPC.TBFORNECEDORCHF SET ";
				if( $_SESSION['_cperficodi_'] == 0 ){
						$sql .= " AFORCHNEMF = $QtdVias, DFORCHULEF = '".date("Y-m-d")."', ";
				}else{
						$sql .= " AFORCHNEMU = $QtdVias, CGREMPCOD1 = ".$_SESSION['_cgrempcodi_'].", ";
						$sql .= " CUSUPOCOD1 = ".$_SESSION['_cusupocodi_'].", DFORCHULEU = '".date("Y-m-d")."', ";
				}
				$sql   .= "        TFORCHULAT = '".date("Y-m-d H:i:s")."'";
				$sql   .= "  WHERE AFORCRSEQU = $Sequencial";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
						$db->query("ROLLBACK");
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$db->query("COMMIT");
						$db->query("END TRANSACTION");
						$Url = "RelEmissaoCHFPdf.php?Sequencial=$Sequencial&Mensagem=".urlencode($Mensagem)."";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}
		}
}
if( $Botao == "" ){
		$Fornecedor = "";

		# Verifica se o fornecedor foi incluido por inscrição e aprovado #
		$sqlpre  = "SELECT COUNT(A.APREFOSEQU) ";
		$sqlpre .= "  FROM SFPC.TBPREFORNECEDOR A, SFPC.TBFORNECEDORCREDENCIADO B ";
		$sqlpre .= "  WHERE A.APREFOSEQU = B.APREFOSEQU AND ";
		if( $TipoDoc == 1 ){
				$sqlpre .= " A.APREFOCCGC = '$CPF_CNPJ'";
		}elseif( $TipoDoc == 2){
				$sqlpre .= " A.APREFOCCPF = '$CPF_CNPJ'";
		}
		$respre = $db->query($sqlpre);
		if( PEAR::isError($respre) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpre");
		}else{
				$Linha = $respre->fetchRow();
				if( $Linha[0] == 0 ){
						# Verifica se o fornecedor foi incluido por cadastro e gestão #
						$sqlfor  = " SELECT COUNT(AFORCRSEQU)FROM SFPC.TBFORNECEDORCREDENCIADO ";
						$sqlfor .= "  WHERE AFORCRSEQU = $Sequencial";
					  $resfor  = $db->query($sqlfor);
						if( PEAR::isError($resfor) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha            = $resfor->fetchRow();
								if( $Linha[0] == 0 ){
										$Mens     = 1;
										$Tipo     = 2;
										$Mensagem = "O Fornecedor está apenas Inscrito não pode Emitir CHF";
										if( $_SESSION['_cperficodi_'] == 0 ){
												$Url = "EmissaoCHFSenha.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												header("location: ".$Url);
												exit;
										}else{
												$Url = "EmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												header("location: ".$Url);
												exit;
										}
								}else{
										$Fornecedor = "S";
								}
						}
				}else{
				 		$Fornecedor = "S";
				}

				if( $Fornecedor == "S" ){
						# Pega os Dados do Fornecedor Cadastrado #
						// inserir as colunas DFORCRULTB, FFORCRMEPP (Heraldo)
						$sqlfor  = " SELECT CRE.AFORCRSEQU, CRE.APREFOSEQU, CRE.AFORCRCCGC, CRE.AFORCRCCPF, CRE.NFORCRRAZS ";
						$sqlfor .= " 			 ,CRE.DFORCRULTB, CRE.DFORCRCNFC, CRE.DFORCRULTB, CRE.FFORCRMEPP, PRE.DPREFOGERA ";
						$sqlfor .= "   FROM SFPC.TBFORNECEDORCREDENCIADO AS CRE";
						$sqlfor .= "   LEFT JOIN  SFPC.TBPREFORNECEDOR AS PRE ON PRE.APREFOSEQU = CRE.APREFOSEQU ";
						$sqlfor .= "  WHERE CRE.AFORCRSEQU = $Sequencial";
					  $resfor  = $db->query($sqlfor);
						if( PEAR::isError($resfor) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
							$Linha        = $resfor->fetchRow();
								$Sequencial		= $Linha[0];
								$PreInscricao = $Linha[1];
								$CNPJ					= $Linha[2];
								$CPF					= $Linha[3];
								$RazaoSocial  = $Linha[4];
								$DataNovaUltBalanco  = $Linha[5];
								$DataNovaCertidaoNeg = $Linha[6];
								$DataBalanco = $Linha[7];
								$MicroEmpresa = $Linha[8];
								$DataInscSicref = $Linha[9];
								
								
								
								

								# Pega os Dados da Tabela de Situação #
								$sql    = "SELECT A.DFORSISITU, B.CFORTSCODI, A.EFORSIMOTI, A.DFORSIEXPI, B.EFORTSDESC ";
								$sql   .= "  FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B ";
								$sql   .= " WHERE A.AFORCRSEQU = $Sequencial ";
								$sql   .= "   AND A.CFORTSCODI = B.CFORTSCODI ";
								$sql   .= " ORDER BY A.DFORSISITU DESC";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										for( $i=0;$i<1;$i++ ){
												$Linha = $result->fetchRow();
												if( $Linha[0] != "" ){
														$DataSituacao = substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
												}else{
														$DataSituacao = "";
												}
												if ( $Linha[1] <> 1 ) {
														$Mens     = 1;
														$Tipo     = 1;
														$Mensagem = "Fornecedor $RazaoSocial -".$Linha[4];
														if( $_SESSION['_cperficodi_'] == 0 ){
																$Url = "EmissaoCHFSenha.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
																if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																header("location: ".$Url);
																exit;
														}else{
																$Url = "EmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
																if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																header("location: ".$Url);
																exit;
														}
												}
										}
								}

								$Cadastrado = "HABILITADO";
								// Variáveis informando os motivos de inabilitação
								$InabilitacaoCertidaoObrigatoria = false;
								$InabilitacaoUltBalanco = false;
								$InabilitacaoCertidaoNeg = false;

								# Verifica também se a data de balanço anual está no prazo #
								if ( !empty($DataNovaUltBalanco) and !empty($MicroEmpresa ) ) {
									if( $DataNovaUltBalanco < prazoUltimoBalanço()->format('Y-m-d') )
									{
										//$Cadastrado = "INABILITADO";
										$InabilitacaoUltBalanco = true;
									}
								}
								
								
								
								# Verifica também se a data de certidão negativa está no prazo #
								if( $DataNovaCertidaoNeg < prazoCertidaoNegDeFalencia()->format('Y-m-d') )
								{
									$Cadastrado = "INABILITADO";
									$InabilitacaoCertidaoNeg = true;
								}

						  	$Cadastrado = 0;
								# Verifica a Validação das Certidões do Fornecedor #
						 		$sql  = "SELECT A.CTIPCECODI, A.ETIPCEDESC, B.DFORCEVALI ";
								$sql .= "  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBFORNECEDORCERTIDAO B ";
								$sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI AND A.FTIPCEOBRI = 'S' ";
								$sql .= "   AND B.AFORCRSEQU = $Sequencial";
								$sql .= " ORDER BY 2";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Rows = $result->numRows();
										for( $i=0; $i<$Rows;$i++ ){
												$DataHoje = date("Y-m-d");
												$Linha 	  = $result->fetchRow();
												if( $Linha[2] < $DataHoje ){
														$Cadastrado = "INABILITADO";
														$InabilitacaoCertidaoObrigatoria = true;
														break;
												}

										}
								}

								# Verifica se já Existe Data de CHF #
								$sql    = "SELECT DFORCHGERA, DFORCHVALI, AFORCHNEMF, DFORCHULEF, ";
								$sql   .= "       AFORCHNEMU, CGREMPCOD1, CUSUPOCOD1, DFORCHULEU ";
								$sql   .= " FROM SFPC.TBFORNECEDORCHF WHERE AFORCRSEQU = $Sequencial ";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Rows = $result->numRows();
										if( $Rows != 0 ){
												$Linha 	        = $result->fetchRow();
												$DataGeracaoCHF = DataBarra($Linha[0]);
												$DataValidade   = DataBarra($Linha[1]);
												$NumFornecedor  = $Linha[2];
												if( $Linha[3] != "" ){ $DataFornecedor = DataBarra($Linha[3]); }
												$NumPrefeitura  = $Linha[4];
												$Grupo          = $Linha[5];
												$Usuario        = $Linha[6];
												if( $Linha[7] != "" ){ $DataPrefeitura = DataBarra($Linha[7]); }
										}else{
												$Mens     = 1;
												$Tipo     = 2;
												$Mensagem = "Data de Validade do CHF não informado no Cadastro";
												if( $_SESSION['_cperficodi_'] == 0 ){
														$Url = "EmissaoCHFSenha.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit;
												}else{
														$Url = "EmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit;
												}
										}
								}

								if( $NumPrefeitura != 0 ){
										# Pega o Nome do Responsável #
										$sql    = "SELECT EUSUPORESP FROM SFPC.TBUSUARIOPORTAL";
										$sql   .= " WHERE CGREMPCODI = $Grupo AND CUSUPOCODI = $Usuario";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Linha 	     = $result->fetchRow();
												$Responsavel = $Linha[0];
										}
								}
						}

						# Verifica se o Fornecedor está Regular na Prefeitura #
						if( $Irregularidade == "" ){
								if( $CNPJ != "" ){
										$TipoDoc  = 1;
										$CPF_CNPJ = $CNPJ;
								}elseif( $CPF != "" ){
										$TipoDoc  = 2;
										$CPF_CNPJ = $CPF;
								}
								$NomePrograma = urlencode("EmissaoCHF.php");
								$Url = "fornecedores/RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&TipoDoc=$TipoDoc&CPF_CNPJ=$CPF_CNPJ&Sequencial=$Sequencial";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								//Redireciona($Url);
								//exit;
						}
				}
		}
}

# Mensagem para Fornecedor Inabilitado #
//								$InabilitacaoCertidaoObrigatoria = false;
					//			$InabilitacaoUltBalanco = false;
							//	$InabilitacaoCertidaoNeg = false;

			$bloquearFornecedor = false;
			if($InabilitacaoCertidaoObrigatoria){
				if( $Irregularidade == "S" ){
						$Mens     = 1;
						$Tipo     = 1;
						$bloquearFornecedor = true;
						if( $Cadastrado == "INABILITADO" ){
					 			$Mensagem = "Certidão(ões) fora do prazo de validade e com situação irregular na Prefeitura";
					 	} else {
								$Mensagem = "situação irregular na Prefeitura";
						}
						if( $_SESSION['_cperficodi_'] == 0 ){
								$Url = "EmissaoCHFSenha.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						}else{
								$Url = "EmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						}
				}	else {
						if( $Cadastrado == "INABILITADO" ){
								$Mens     = 1;
								$Tipo     = 1;
								$Mensagem = "Certidão(ões) fora do prazo de validade";
								$bloquearFornecedor = true;
						}
				}
		}
		if( $Cadastrado == "INABILITADO" AND $InabilitacaoUltBalanco ){
			if( $Mens == 1 ){ $Mensagem .=", "; }
			$Mens     = 1;
			$Tipo     = 1;
			$Mensagem.= "Data de Validade do Balanço expirada";
			$bloquearFornecedor = true;

		}
		if( $Cadastrado == "INABILITADO" AND $InabilitacaoCertidaoNeg){
			if( $Mens == 1 ){ $Mensagem .=", "; }
			$Mens     = 1;
			$Tipo     = 1;
			$Mensagem.= "Data de Certidão Negativa expirada";
			$bloquearFornecedor = true;
		}
		
		// Inserido por Heraldo (23/01/2014)
		if (  !empty($MicroEmpresa)  and empty($DataBalanco) ) {
			if( $Mens == 1 ){
				$Mensagem .=", ";
			}
			$Mens     = 1;
			$Tipo     = 1;
			$Mensagem .= "CHF simplificado sem demonstrações contábeis";
			$bloquearFornecedor = true;
		}
		
		if($bloquearFornecedor){
			$Mensagem =  "Fornecedor com ".$Mensagem;
		}
		
		// [Redmine 108]
		if ($DataValidade != "" and (DataInvertida($DataValidade) < date("Y-m-d"))) {
			if ($Mensagem != "") {
				$Mensagem .= "<br/>";
			}
			
			$Mens     = 1;
			$Tipo     = 1;
			$Mensagem .= "Fornecedor INABILITADO CHF fora do prazo de validade";
		}

# Esta parte do programa foi comentada , pois o usuário pediu para emitir o CHF, mesmo que o fornecedor esteja inabilitado.
# Deixar comentado, pois outro usuário pode pedir para colocar novamente. Se for caso trocar pelo if anterior

//if( $Irregularidade == "S" ){
//		$Mens     = 1;
//		$Tipo     = 1;
//		$Mensagem = "Fornecedor Inabilitado (Com situação irregular na Prefeitura)";
//		if( $Cadastrado == "INABILITADO" ){
//	 			$Mensagem = "Fornecedor Inabilitado com Certidão(ões) fora do prazo de validade e com situação irregular na Prefeitura";
//				if( $_SESSION['_cperficodi_'] == 0 ){
//						$Url = "EmissaoCHFSenha.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
//						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
//						header("location: ".$Url);
//						exit;
//				}else{
//						$Url = "EmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
//						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
//						header("location: ".$Url);
//						exit;
//				}
//		}else{
//	 			$Mensagem = "Fornecedor Inabilitado (Com situação irregular na Prefeitura)";
//				if( $_SESSION['_cperficodi_'] == 0 ){
//						$Url = "EmissaoCHFSenha.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
//						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
//						header("location: ".$Url);
//						exit;
//				}else{
//						$Url = "EmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
//						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
//						header("location: ".$Url);
//						exit;
//				}
//		}
//}elseif( $Irregularidade == "N" and $Cadastrado == "INABILITADO" ){
//		$Mens     = 1;
//		$Tipo     = 1;
//		$Mensagem = "Fornecedor Inabilitado com Certidão(ões) fora do prazo de validade";
//		if( $_SESSION['_cperficodi_'] == 0 ){
//				$Url = "EmissaoCHFSenha.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
//				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
//				header("location: ".$Url);
//				exit;
//		}else{
//				$Url = "EmissaoCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
//				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
//				header("location: ".$Url);
//				exit;
//		}
//}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.EmissaoCHF.Botao.value = valor;
	document.EmissaoCHF.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="Stylesheet" type="Text/Css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="EmissaoCHF.php" method="post" name="EmissaoCHF">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font><a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > CHF > Emissão de CHF
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
		    					EMISSÃO DO CERTIFICADO DE HABILITAÇÃO DE FIRMAS
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" >
									<p align="justify">
										Para emitir o Certificado de Habilitação de Firmas (CHF) do Fornecedor abaixo descrito, clique no botão "Imprimir".<br>
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
														<td class="textonormal" bgcolor="#DCEDF7">
															<?php if( $CNPJ != "" ){ echo "CNPJ\n"; } else { echo "CPF\n"; }?>
			          	    			</td>
														<td class="textonormal" height="20">
					          	    		<?php if( $CNPJ <> 0 ){ echo FormataCNPJ($CNPJ); }else{ echo FormataCPF($CPF); } ?>
				          	    		</td>
				            			</tr>
							            <tr>
							              <td class="textonormal" bgcolor="#DCEDF7">Razão Social/Nome</td>
							              <td class="textonormal" height="20"><?php echo $RazaoSocial;?></td>
							            </tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Geração do CHF</td>
														<td class="textonormal"><?php echo $DataGeracaoCHF;?></td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Validade do CHF</td>
														<td class="textonormal"><?php echo $DataValidade;?></td>
												  </tr>
												  <tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Inscrição do SICREF</td>
														<td class="textonormal">
															<?php if( $DataInscSicref != "" ){ 
																	echo substr($DataInscSicref, 8, 2).'/'.substr($DataInscSicref, 5, 2).'/'.substr($DataInscSicref, 0, 4);
																} else { 
																	echo "-"; 
																}
															?>
														</td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Número de Emissões do Fornecedor</td>
														<td class="textonormal">
															<?php if( $NumFornecedor != 0 ){ echo $NumFornecedor; }else{ echo "0"; }?>
														</td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Última Data de Emissão do Fornecedor</td>
														<td class="textonormal">
															<?php if( $DataFornecedor != 0 ){ echo $DataFornecedor; }else{ echo "-"; }?>
														</td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Número de Emissões da Prefeitura</td>
														<td class="textonormal">
															<?php if( $NumPrefeitura != 0 ){ echo $NumPrefeitura; }else{ echo "0"; }?>
														</td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20" width="45%">Última Data de Emissão da Prefeitura</td>
														<td class="textonormal">
															<?php if( $DataPrefeitura != "" ){ echo $DataPrefeitura; }else{ echo "-"; }?>
														</td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Responsável pela Emissão da Prefeitura</td>
														<td class="textonormal">
															<?php if( $Responsavel != "" ){ echo $Responsavel; }else{ echo "-"; }?>
														</td>
									  			</tr>
												</table>
						  				</td>
						  			</tr>
									</table>
								</td>
		        	</tr>
  						<tr>
								<td class="textonormal" align="right">
									<input type="hidden" name="CPF_CNPJ" value="<?php echo $CPF_CNPJ; ?>">
									<input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
									<input type="hidden" name="Usuario" value="<?php echo $Usuario; ?>">
									<input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
									<input type="hidden" name="Mensagem" value="<?php echo str_replace("<br/>", "\n", $Mensagem); ?>">
									<input type="button" value="Imprimir" class="botao" onclick="javascript:enviar('Imprimir');">
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
<?php $db->disconnect();?>
