<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadCHFAlterar.php
#-------------------------------------------------------------------------
# Autor:    Roberta Costa
# Data:     21/09/04
# Objetivo: Programa que Altera a data do CHF dos Fornecedores
#-------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     06/08/2010
# Objetivo: Verificação de data de balanço anual se está no prazo
#-------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     14/10/2010
# Objetivo: Correção
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		25/07/2018
# Objetivo:	Tarefa Redmine 199906
#-------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
require_once("funcoesFornecedores.php");

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/CadCHFSelecionar.php' );
AddMenuAcesso( '/oracle/fornecedores/RotDebitoCredorConsulta.php' );
ini_set('display_errors', 0);
 error_reporting(E_ALL ^ E_NOTICE);
# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']	== "POST" ){
		$Botao                = $_POST['Botao'];
		$Documento            = $_POST['Documento'];
		$DataInscricao        = $_POST['DataInscricao'];
		$RazaoSocial          = $_POST['RazaoSocial'];
		$DataGeracaoCHF       = $_POST['DataGeracaoCHF'];
		$DataValidadeCHF      = $_POST['DataValidadeCHF'];
		$DataValidadeCHFAntes = $_POST['DataValidadeCHFAntes'];
		$Sequencial           = $_POST['Sequencial'];
		$Irregularidade       = $_POST['Irregularidade'];
}else{
		$Sequencial     = $_GET['Sequencial'];
		$Irregularidade = $_GET['Irregularidade'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Irregularidade == "S" ){
		$Mensagem = "Fornecedor possui alguma irregularidade com a Prefeitura, deve procurar o Centro de Atendimento ao Contribuinte - CAC da Prefeitura - térreo - no Cais do Apolo, 925 - Bairro do Recife/PE e após a solução das pendências tentar executar a Alteração da Data de Validade do CHF novamente";
		$Url = "CadCHFSelecionar.php?Mens=1&Tipo=1&Mensagem=".urlencode($Mensagem)."";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}else{
		if( $Botao == "Voltar" ){
				header("location: CadCHFSelecionar.php");
				exit;
		}
}

# Carrega as variáveis dos formulários #
$db	= Conexao();

if( $Botao == "Alterar" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $DataValidadeCHF == "" ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadCHFAlterar.DataValidadeCHF.focus();\" class=\"titulo2\">Data de Validade</a>";
		}else{
				$MensErro = ValidaData($DataValidadeCHF);
				if( $MensErro != "" ){
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadCHFAlterar.DataValidadeCHF.focus();\" class=\"titulo2\">Data Válida</a>";
				}else{
						if( DataInvertida($DataValidadeCHF) < DataInvertida($DataValidadeCHFAntes) ){
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadCHFAlterar.DataValidadeCHF.focus();\" class=\"titulo2\">Data Superior a Data de Validade do CHF Atual</a>";
						}
				}
		}
		if( $Mens == 0 ){
				$DataValidadeCHFInv = substr($DataValidadeCHF,6,4)."-".substr($DataValidadeCHF,3,2)."-".substr($DataValidadeCHF,0,2);
				$db->query("BEGIN TRANSACTION");
				$sql    = "UPDATE SFPC.TBFORNECEDORCHF ";
				$sql   .= "   SET DFORCHVALI = '$DataValidadeCHFInv', CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", ";
				$sql   .= "       CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", TFORCHULAT = '".date("Y-m-d")."'";
				$sql   .= " WHERE AFORCRSEQU = $Sequencial ";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    $db->query("ROLLBACK");
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$db->query("COMMIT");
						$db->query("END TRANSACTION");
						$Mensagem = "Data de validade do CHF alterada com sucesso";
						$Url = "CadCHFSelecionar.php?Mens=1&Tipo=1&Mensagem=".urlencode($Mensagem)."";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}
		}
}elseif( $Botao == "" ){
		$Mens       = 0;
		$Mensagem   = "";
		$Fornecedor = "";

		# Pega os dados do Fornecedor #
		// Inserido por Heraldo (23/01/2014) as colunas A.DFORCRULTB, A.FFORCRMEPP
		$sql    = "SELECT A.NFORCRRAZS, A.AFORCRCCGC, A.AFORCRCCPF, B.DFORCHVALI, A.DFORCRGERA, A.DFORCRULTB, A.FFORCRMEPP ";
		$sql   .= "  FROM SFPC.TBFORNECEDORCREDENCIADO A, SFPC.TBFORNECEDORCHF B ";
		$sql   .= " WHERE A.AFORCRSEQU = B.AFORCRSEQU AND A.AFORCRSEQU = $Sequencial ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha 	         = $result->fetchRow();
				$RazaoSocial     = $Linha[0];
				$CNPJ            = $Linha[1];
				$CPF             = $Linha[2];
				$DataBalanco     = $Linha[5];
				$MicroEmpresa     = $Linha[6];
				
				if( $CNPJ != "" ){
						$CPF_CNPJ  = $CNPJ;
						$Documento = FormataCNPJ($CNPJ);
				}else{
						$CPF_CNPJ  = $CPF;
	    			$Documento = FormataCPF($CPF);
				}
				$DataValidadeCHFAntes = DataBarra($Linha[3]);
				$DataInscricao        = DataBarra($Linha[4]);
		}

		# Verifica se o fornecedor foi incluido por inscrição e aprovado #
		$sqlpre  = "SELECT COUNT(A.APREFOSEQU) ";
		$sqlpre .= "  FROM SFPC.TBPREFORNECEDOR A, SFPC.TBFORNECEDORCREDENCIADO B ";
		$sqlpre .= "  WHERE A.APREFOSEQU = B.APREFOSEQU AND ";
		if( $CNPJ != "" ){
				$sqlpre .= "A.APREFOCCGC = '$CPF_CNPJ'";
		}else{
				$sqlpre .= "A.APREFOCCPF = '$CPF_CNPJ'";
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
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlfor");
						}else{
								$Linha            = $resfor->fetchRow();
								if( $Linha[0] == 0 ){
										$Mens     = 1;
										$Tipo     = 2;
										$Mensagem = "O Fornecedor está apenas Inscrito não pode Emitir CHF";
										$Url = "CadCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit;
								}else{
										$Fornecedor = "S";
								}
						}
				}else{
				 		$Fornecedor = "S";
				}

				$Tipo_Habilitacao = "HABILITADO";
				$Cadastrado = "HABILITADO";

				if( $Fornecedor == "S" ){
						# Pega os Dados do Fornecedor Cadastrado #
						$sqlfor  = " SELECT AFORCRSEQU, APREFOSEQU, AFORCRCCGC, AFORCRCCPF, NFORCRRAZS ";
						$sqlfor .= " 			 ,DFORCRULTB, DFORCRCNFC ";
						$sqlfor .= "   FROM SFPC.TBFORNECEDORCREDENCIADO ";
						$sqlfor .= "  WHERE AFORCRSEQU = $Sequencial";
					  $resfor  = $db->query($sqlfor);
						if( PEAR::isError($resfor) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlfor");
						}else{
								$Linha        = $resfor->fetchRow();
								$Sequencial		= $Linha[0];
								$PreInscricao = $Linha[1];
								$CNPJ					= $Linha[2];
								$CPF					= $Linha[3];
								$RazaoSocial  = $Linha[4];
								$DataNovaUltBalanco  = $Linha[5];
								$DataNovaCertidaoNeg = $Linha[6];

								# Pega os Dados da Tabela de Situação #
								$sql    = "SELECT A.DFORSISITU, B.CFORTSCODI, A.EFORSIMOTI, A.DFORSIEXPI ";
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
												if( $Linha[1] == 3 ){
														$Mens     = 1;
														$Tipo     = 1;
														$Mensagem = "Fornecedor $RazaoSocial - Suspenso";
														$Url = "CadCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit;
												}elseif( $Linha[1] == 4 ){
														$Mens     = 1;
														$Tipo     = 1;
														$Mensagem = "Fornecedor $RazaoSocial - Cancelado";
														$Url = "CadCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
														header("location: ".$Url);
														exit;
												}else{
														$Situacao = $Linha[1];
												}
												$Motivo = strtoupper2($Linha[2]);
												if( $Linha[3] != "" ){
														$DataSuspensao = substr($Linha[3],8,2)."/".substr($Linha[3],5,2)."/".substr($Linha[3],0,4);
												}else{
														$DataSuspensao = "";
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
								
								
								  if( $DataNovaCertidaoNeg < prazoCertidaoNegDeFalencia()->format('Y-m-d') )
								{
 									 $Cadastrado = "INABILITADO";
									 $InabilitacaoCertidaoNeg = true;
										
								}  

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
												//echo "[".$Linha[2]."; ".$DataHoje."]";
												//if( $i == 0 ){
														if( $Linha[2] < $DataHoje ){
																$Cadastrado = "INABILITADO";
																$InabilitacaoCertidaoObrigatoria = true;
														}
												//}
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
												$Mensagem = "Data de Validade do CHF não informado";
												$Url = "CadCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												header("location: ".$Url);
												exit;
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
								$NomePrograma = urlencode("CadCHFAlterar.php");
								$Url = "fornecedores/RotDebitoCredorConsulta.php?NomePrograma=$NomePrograma&TipoDoc=$TipoDoc&CPF_CNPJ=$CPF_CNPJ&Sequencial=$Sequencial";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								//Redireciona($Url);
								//exit;
						}
				}
		}

		# Mensagem para Fornecedor Inabilitado #

			$bloquearFornecedor = false;
			if($InabilitacaoCertidaoObrigatoria){
				if( $Irregularidade == "S" ){
							$Mens     = 1;
							$Tipo     = 1;
							
						    if( $Cadastrado == "INABILITADO" ){
					 			$Mensagem = "Certidão(ões) fora do prazo de validade e com situação irregular na Prefeitura";
					 			$bloquearFornecedor = true;
						    }else{
					 			$Mensagem = "situação irregular na Prefeitura";
					 			$bloquearFornecedor = true;
						    }
				}elseif( $Irregularidade == "N" and $Cadastrado == "INABILITADO" ){
						$Mens     = 1;
						$Tipo     = 1;
						$Mensagem = "Certidão(ões) fora do prazo de validade";
			 			$bloquearFornecedor = true;
			 			 
			    }
		}
		if( $Cadastrado == "INABILITADO" and $InabilitacaoUltBalanco ){
			if( $Mens == 1 ){ $Mensagem .=", "; }
			$Mens     = 1;
			$Tipo     = 1;
			$Mensagem .= "Data de Validade do Balanço expirada";
 			$bloquearFornecedor = true;
		 }
		 if( $Cadastrado == "INABILITADO" and $InabilitacaoCertidaoNeg ){
			if( $Mens == 1 ){ $Mensagem .=", "; }
			$Mens     = 1;
			$Tipo     = 1;
			$Mensagem .= "Data de Certidão Negativa expirada";
 			$bloquearFornecedor = true;
		 }
		 
		 // Inserido por Heraldo (23/01/2014)
		 if (  !empty($MicroEmpresa)  and empty($DataBalanco) ) {
		 	if( $Mens == 1 ){	$Mensagem .=", ";	}
		 	$Mens     = 1;
		 	$Tipo     = 1;
		 	$Mensagem .= "CHF simplificado sem demonstrações contábeis";
		 	//$bloquearFornecedor = true;
		 }
		 	
		 
		 if($bloquearFornecedor){
		 	$Mensagem = "Fornecedor Inabilitado com ".$Mensagem;
			$Url = "CadCHFSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
			if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
			header("location: ".$Url);
			exit;
		 }
	}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadCHFAlterar.Botao.value = valor;
	document.CadCHFAlterar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadCHFAlterar.php" method="post" name="CadCHFAlterar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font><a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedor > CHF > Alteração da Data do CHF
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
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" bgcolor="#ffffff" class="textonormal" summary="">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					ALTERAÇÃO DA DATA DO CHF
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
									<p align="justify">
										Informe a nova data de validade do CHF e clique no botão "Alterar". Para retornar a página anterior clique no botão "Voltar".
	          	   	</p>
	          		</td>
		        	</tr>
							<tr>
								<td>
									<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" summary="">
										<tr>
											<td>
						  					<table class="textonormal" border="0" cellpadding="0" cellspacing="2" width="100%" summary="">
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">CPF/CNPJ</td>
														<td class="textonormal">
															<?php echo $Documento; ?>
															<input type="hidden" name="Documento" value="<?php echo $Documento; ?>">
														</td>
									  			</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Razão Social</td>
														<td class="textonormal">
															<?php echo $RazaoSocial; ?>
															<input type="hidden" name="RazaoSocial" value="<?php echo $RazaoSocial; ?>">
														</td>
									  			</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Cadastramento</td>
														<td class="textonormal">
															<?php echo $DataInscricao; ?>
															<input type="hidden" name="DataInscricao" value="<?php echo $DataInscricao; ?>">
														</td>
									  			</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Geração</td>
														<td class="textonormal">
															<?php echo $DataGeracaoCHF; ?>
															<input type="hidden" name="DataGeracaoCHF" value="<?php echo $DataGeracaoCHF; ?>">
														</td>
									  			</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Validade</td>
														<td class="textonormal">
															<?php echo $DataValidadeCHFAntes; ?>
															<input type="hidden" name="DataValidadeCHFAntes" value="<?php echo $DataValidadeCHFAntes; ?>">
														</td>
									  			</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7">Nova Data de Validade*</td>
														<td class="textonormal">
															<?php $URL = "../calendario.php?Formulario=CadCHFAlterar&Campo=DataValidadeCHF";?>
															<input type="text" name="DataValidadeCHF" size="10" maxlength="10" value="<?php echo $DataValidadeCHF; ?>" class="textonormal">
															<a href="javascript:janela('<?php echo $URL; ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
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
		           		<input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
			         		<input type="hidden" name="Irregularidade" value="<?php echo $Irregularidade; ?>">
	            		<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
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
<script language="javascript" type="">
<!--
document.CadCHFAlterar.DataValidadeCHF.focus();
//-->
</script>
</body>
</html>
<?php $db->disconnect(); ?>
