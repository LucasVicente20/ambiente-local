<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadCertidaoAlterar.php
# Autor:    Roberta Costa
# Data:     16/09/04
# Objetivo: Programa de Alteração dos Prazos das Certidões Vencidas
# Alterado: Rossana Lira
# Data:     16/05/07 - Exibição da data da última alteração
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/CadCertidaoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao	 					    = $_POST['Botao'];
		$Cumprimento  		    = $_POST['Cumprimento'];
		$Situacao  			      = $_POST['Situacao'];
		$DataSituacao  		    = $_POST['DataSituacao'];
		$Motivo  		          = strtoupper2(trim($_POST['Motivo']));
		$DataGeracaoCHF 	    = $_POST['DataGeracaoCHF'];
		$DataSuspensao   	    = $_POST['DataSuspensao'];
		$SituacaoAntes        = $_POST['SituacaoAntes'];
		$CPF_CNPJ				      = $_POST['CPF_CNPJ'];
		$RazaoSocial		      = strtoupper2(trim($_POST['RazaoSocial']));
		$CertidaoComplementar	= $_POST['CertidaoComplementar'];
		$CertidaoObrigatoria  = $_POST['CertidaoObrigatoria'];
		$DataCertidaoOb	      = $_POST['DataCertidaoOb'];
		$DataCertidaoComp     = $_POST['DataCertidaoComp'];
		$Sequencial           = $_POST['Sequencial'];
		$CarregaCertComp      = $_POST['CarregaCertComp'];
		$CarregaCertOb        = $_POST['CarregaCertOb'];
}else{
		$Sequencial       = $_GET['Sequencial'];
		$Irregularidade   = $_GET['Irregularidade'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona o programa de acordo com o botão voltar #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Botao = "";
		header("location: CadCertidaoSelecionar.php");
		exit;
}elseif( $Botao == "Atualizar" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $DataCertidaoOb != 0 ){
				for( $i=0;$i<count($DataCertidaoOb);$i++ ){
						if( $DataCertidaoOb[$i] == "" ){
								$cont++;
								if( $cont == 1 ){ $PosOb = $i; }
								$ExisteDataOb = "N";
						}else{
								$Erro = ValidaData($DataCertidaoOb[$i]);
								if( $Erro != "" ){
										$con++;
										if( $con == 1 ) { $PosValOb = $i; }
										$DataValidaOb = "N";
								}
						}
				}
				if( $ExisteDataOb == "N" ){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$PosOb     = 2 * $PosOb;
						$Mensagem .= "<a href=\"javascript:document.CadCertidaoAlterar.elements[$PosOb].focus();\" class=\"titulo2\"> Data de Validade da(s) Certidão(ões) Obrigatória(s)</a>";
				}
				if( $DataValidaOb == "N" ){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$PosValOb  = ( 2 * $PosValOb );
						$Mensagem .= "<a href=\"javascript:document.CadCertidaoAlterar.elements[$PosValOb].focus();\" class=\"titulo2\"> Data de Validade da(s) Certidão(ões) Obrigatória(s) Válida</a>";
				}
		}
		if( $Mens == 0){
				if( $DataCertidaoComp != 0 ){
						for ( $i=0;$i<count($DataCertidaoComp);$i++) {
								if( $DataCertidaoComp[$i] == "" ){
										$cont++;
										if( $cont == 1 ){ $PosComp = $i; }
										$ExisteDataComp = "N";
								}else{
										if( ValidaData($DataCertidaoComp[$i]) ){
												$con++;
												if( $con == 1 ) { $PosComp = $i; }
												$DataValidaComp = "N";
										}
								}
						}
						if( $ExisteDataComp == "N" ){
								if ( $Mens == 1 ) { $Mensagem .= ", "; }
								$Mens       = 1;
								$Tipo       = 2;
								$PosOb      = count($DataCertidaoOb) * 2;
								$PosCompVaz = ( 2 * $PosComp ) + $PosOb;
								$Mensagem  .= "<a href=\"javascript:document.CadCertidaoAlterar.elements[$PosCompVaz].focus();\" class=\"titulo2\"> Data de Validade da(s) Certidão(ões) Complementar(es)</a>";
						}elseif( $DataValidaComp == "N" ){
								if( $Mens == 1 ){ $Mensagem .= ", "; }
								$Mens       = 1;
								$Tipo       = 2;
								$PosOb      = count($DataCertidaoOb) * 2;
								$PosCompVal = ( 2 * $PosComp ) + $PosOb;
								$Mensagem  .= "<a href=\"javascript:document.CadCertidaoAlterar.elements[$PosCompVal].focus();\" class=\"titulo2\"> Data de Validade da(s) Certidão(ões) Complementar(es) Válida</a>";
						}
				}
		}
		if( $Mens == 0 ){
				$db->query("BEGIN TRANSACTION");
				$DataAtual = date("Y-m-d H:i:s");

				# Alterando as datas da Certidões Obrigatórias #
				if( count($CertidaoObrigatoria) != 0 ){
						for( $i=0; $i<count($CertidaoObrigatoria); $i++ ){
								$DataCertidaoOb[$i] =  substr($DataCertidaoOb[$i],6,4)."-".substr($DataCertidaoOb[$i],3,2)."-".substr($DataCertidaoOb[$i],0,2);
								$sql    = "UPDATE SFPC.TBFORNECEDORCERTIDAO";
								$sql   .= "   SET DFORCEVALI = '$DataCertidaoOb[$i]', TFORCEULAT = '$DataAtual'";
								$sql   .= " WHERE AFORCRSEQU = $Sequencial AND CTIPCECODI = $CertidaoObrigatoria[$i] ";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}
						}
				}

				# Alterando as datas da Certidões Obrigatórias #
				if( count($CertidaoComplementar) != 0 ){
						for( $i=0; $i<count($CertidaoComplementar); $i++ ){
								$DataCertidaoComp[$i] =  substr($DataCertidaoComp[$i],6,4)."-".substr($DataCertidaoComp[$i],3,2)."-".substr($DataCertidaoComp[$i],0,2);
								$sql    = "UPDATE SFPC.TBFORNECEDORCERTIDAO";
								$sql   .= "   SET DFORCEVALI = '$DataCertidaoComp[$i]', TFORCEULAT = '$DataAtual'";
								$sql   .= " WHERE AFORCRSEQU = $Sequencial AND CTIPCECODI = $CertidaoComplementar[$i] ";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}
						}
				}
				$db->query("COMMIT");

				if( $Mens == 0 ){
						# Redireciona o programa de acordo com o botão voltar #
						$Botao    = "";
						$Mens     = 1;
						$Tipo     = 1;
						$Mensagem = "Atualização Realizada com Sucesso";
						$Url = "CadCertidaoSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}
				$Destino = "A";
				$db->query("END TRANSACTION");
		}
}

# Mensagens para Irregularidade e Certidões Fora do Prazo de Validade #
if( $Irregularidade == "S" ){
		$Mens = 1;
		$Tipo = 2;
		if( $Cadastrado == "INABILITADO" ){
	 			$Mensagem = "Fornecedor Inabilitado(Com <a href=\"CadCertidaoAlterar.php?Sequencial=$Sequencial\" class=\"titulo2\">Certidão(ões) fora do prazo de validade</a> e com situação irregular na Prefeitura)";
		}else{
	 			$Mensagem = "Fornecedor Inabilitado(Com situação irregular na Prefeitura)";
		}
		$Url = "CadCertidaoSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}else{
		if( $Cadastrado == "INABILITADO" ){
				$Mens     = 1;
				$Tipo     = 2;
				$Mensagem = "Fornecedor Inabilitado(Com <a href=\"CadCertidaoAlterar.php?&Sequencial=$Sequencial\" class=\"titulo2\">Certidão(ões) fora do prazo de validade</a>)";
				$Url = "CadCertidaoSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}
}

if( $Critica == "" ){
		# Busca os Dados da Tabela de fornecedor de Acordo com o sequencial do fornecedor  #
		$sql  = " SELECT AFORCRSEQU, APREFOSEQU, AFORCRCCGC, AFORCRCCPF, ";
		$sql .= "        NFORCRRAZS, DFORCRGERA, FFORCRCUMP, TFORCRULAT";
		$sql .= "   FROM SFPC.TBFORNECEDORCREDENCIADO ";
		$sql .= "  WHERE AFORCRSEQU = $Sequencial";
	  $result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();

				# Variáveis Formulário A #
				$Sequencial		    = $Linha[0];
				$PreInscricao   	= $Linha[1];
				$CNPJ							= $Linha[2];
				$CPF							= $Linha[3];
				$RazaoSocial  		= $Linha[4];
				$DataInscricao		= substr($Linha[5],8,2)."/".substr($Linha[5],5,2)."/".substr($Linha[5],0,4);
				$Cumprimento			= $Linha[6];
				$DataAlteracao		= substr($Linha[7],8,2)."/".substr($Linha[7],5,2)."/".substr($Linha[7],0,4);				
		}

		# Busca os Dados da Tabela de Situação de acordo com o sequencial do Fornecedor #
		$sql    = "SELECT A.DFORSISITU, B.CFORTSCODI, A.EFORSIMOTI, A.DFORSIEXPI ";
		$sql   .= "  FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B ";
		$sql   .= " WHERE A.AFORCRSEQU = $Sequencial ";
		$sql   .= "   AND A.CFORTSCODI = B.CFORTSCODI ";
		$sql   .= " ORDER BY A.DFORSISITU DESC";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha 	 			= $result->fetchRow();
				$DataSituacao = substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
				if( $Linha[1] == 3 ){
						$Mens     = 1;
						$Tipo     = 1;
						$Mensagem = "Fornecedor $RazaoSocial - Suspenso";
						$Url = "CadCertidaoSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}elseif( $Linha[1] == 4 ){
						$Mens     = 1;
						$Tipo     = 1;
						$Mensagem = "Fornecedor $RazaoSocial - Cancelado";
						$Url = "CadCertidaoSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}elseif( $Linha[1] == 5 ){
						$Mens     = 1;
						$Tipo     = 1;
						$Mensagem = "Fornecedor $RazaoSocial - Excluído";
						$Url = "CadCertidaoSelecionar.php?Mens=$Mens&Tipo=$Tipo&Mensagem=".urlencode($Mensagem)."";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}else{
						$Situacao	= $Linha[1];
				}
				$SituacaoAntes = $Linha[1];
				$Motivo				 = strtoupper2($Linha[2]);
				$DataSuspensao = $Linha[3];
				if( $DataSuspensao != "" ){
						$DataSuspensao = DataBarra($Linha[3]);
				}
				if( $Mens == 0 ){
						# Verifica a Validação das Certidões do Fronecedor #
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
								for( $i=0; $i<=$Rows;$i++ ){
										$Linha = $result->fetchRow();
										if( $i == 0 ){
												if( $Linha[2] < date("Y-m-d") ){
														$Cadastrado = "INABILITADO";
												}else{
														$Cadastrado = "HABILITADO";
												}
										}
								}
						}

						# Verifica se Existe Data de CHF #
						$sql    = "SELECT DFORCHGERA FROM SFPC.TBFORNECEDORCHF ";
						$sql   .= " WHERE AFORCRSEQU = $Sequencial ";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Rows = $result->numRows();
								if( $Rows != 0 ){
										$Linha 	= $result->fetchRow();
										$DataGeracaoCHF = DataBarra($Linha[0]);
								}else{
										$DataGeracaoCHF = "";
								}
						}
				}
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
	document.CadCertidaoAlterar.Botao.value = valor;
	document.CadCertidaoAlterar.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'pagina','status=no,scrollbars=yes,left=20,top=150,width='+largura+',height='+altura);
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadCertidaoAlterar.php" method="post" name="CadCertidaoAlterar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font><a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Atualização de Certidão
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
			<?php if( $Mens != 0 ){ ExibeMens($Mensagem,$Tipo,$Virgula);	}?>
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
		    					ATUALIZAÇÃO DOS PRAZOS DAS CERTIDÕES VENCIDAS
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
									<p align="justify">
										Para atualizar os Prazos das Certidões Vencidas, informe os dados abaixo e clique no botão "Salvar". Os itens obrigatórios estão com *.<br>
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
														<td class="textonormal" bgcolor="#DCEDF7" height="20">
															<?php if( $CNPJ != 0 ){ echo "CNPJ"; }else{ echo "CPF"; } ?>
														</td>
														<td class="textonormal">
															<?php
															if( $CNPJ != 0 ){
																	echo substr($CNPJ,0,2).".".substr($CNPJ,2,3).".".substr($CNPJ,5,3)."/".substr($CNPJ,8,4)."-".substr($CNPJ,12,2);
															}else{
																	echo substr($CPF,0,3).".".substr($CPF,3,3).".".substr($CPF,6,3)."-".substr($CPF,9,2);
															}
															?>
														</td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Razão Social/Nome </td>
														<td class="textonormal"><?php echo $RazaoSocial; ?></td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Cumprimento</td>
														<td class="textonormal"><?php if( $Cumprimento == "S" ){ echo "SIM"; }else{ echo "NÃO"; } ?></td>
									  			</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Situação</td>
														<td class="textonormal">
															<?php
															# Mostra Tabela de Situação #
															$sql    = "SELECT EFORTSDESC FROM SFPC.TBFORNECEDORTIPOSITUACAO WHERE CFORTSCODI = $Situacao";
															$result = $db->query($sql);
															if( PEAR::isError($result) ){
															    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	$Linha = $result->fetchRow();
																	echo $Linha[0];
															}
															?>
														</td>
									  			</tr>
													<?php if( $Situacao != 1 ) { ?>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Situação*</td>
														<td class="textonormal"><?php echo $DataSituacao; ?></td>
									  			</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Motivo*</td>
														<td class="textonormal"><?php echo $Motivo; ?></td>
									  			</tr>
													<?php
													}
													if( $Situacao == 3 ) {
													?>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Expiração da Suspensão</td>
														<td class="textonormal" height="25"><?php echo $DataSuspensao; ?></td>
									  			</tr>
													<?php
													}
													if( $DataGeracaoCHF != "" ){
													?>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Geração do CHF</td>
														<td class="textonormal"><?php echo $DataGeracaoCHF; ?></td>
									  			</tr>
													<?php } ?>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Cadastramento</td>
														<td class="textonormal"><?php echo $DataInscricao; ?></td>
									  			</tr>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Data de Alteração</td>
														<td class="textonormal"><?php echo $DataAlteracao; ?></td>
									  			</tr>
													<?php if( $Ocorrencias != 0 ){ ?>
									  			<tr>
														<td class="textonormal" bgcolor="#DCEDF7" height="20">Ocorrências</td>
														<td class="textonormal">
															<?
															$Url = "CadGestaoFornecedorOcorrencias.php?ProgramaOrigem=CadCertidaoAlterar&Sequencial=$Sequencial";
															if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
															?>
															<a href="javascript:AbreJanela('<?=$Url;?>',750,210);"> Clique AQUI para visualizar as Ocorrências.</a>
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
								<td>
									<table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6"  width="100%" summary="">
										<tr bgcolor="#bfdaf2">
											<td colspan="4">
							          <table class="textonormal" border="0" align="left" width="100%" summary="">
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
								              				<?php
								              				if( $CarregaCertOb == 0 ){
												              		# Mostra a lista de certidões obrigatórias #
												              		$sql  = "SELECT A.CTIPCECODI, A.ETIPCEDESC, B.DFORCEVALI ";
											                		$sql .= "  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBFORNECEDORCERTIDAO B ";
											                		$sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI AND A.FTIPCEOBRI = 'S' ";
											                		$sql .= "   AND B.AFORCRSEQU = $Sequencial ";
											                		$sql .= " ORDER BY 1";
																		  		$res  = $db->query($sql);
																				  if( PEAR::isError($res) ){
																						  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																					}else{
																							$Rows = $res->numRows();
																							for( $i=0;$i<$Rows;$i++ ){
													          	      			$Linha              = $res->fetchRow();
													          	      			$CertidaoOb         = $Linha[0];
													              					$DescricaoOb        = substr($Linha[1],0,75);
																									$DataCertidaoOb[$i] = substr($Linha[2],8,2)."/".substr($Linha[2],5,2)."/".substr($Linha[2],0,4);
																                	$DataCertidaoObInv  = substr($DataCertidaoOb[$i],6,4)."-".substr($DataCertidaoOb[$i],3,2)."-".substr($DataCertidaoOb[$i],0,2);
																                	if( $DataCertidaoObInv < date("Y-m-d") ){
																                			$Validade = "titulo1";
																                	}else{
																                			$Validade = "textonormal";
																                			$Desativa = "S";
																                	}
										              								$ElementoOb  = 2 * $i;
												              						echo "<tr>\n";
													              					echo "	<td class=\"$Validade\" width=\"*\">$DescricaoOb</td>\n";
												              						echo "	<td class=\"textonormal\" width=\"22%\" height=\"25\">\n";
																									if( $Desativa != "S" ){
																											$URL = "../calendario.php?Formulario=CadCertidaoAlterar&Campo=elements[$ElementoOb]";
																						          echo "		<input class=\"textonormal\" type=\"text\" name=\"DataCertidaoOb[$i]\" size=\"10\" maxlength=\"10\" value=\"$DataCertidaoOb[$i]\">\n";
																											echo "		<a href=\"javascript:janela('$URL','Calendario',220,165,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>\n";
																											echo "		<input type=\"hidden\" name=\"CertidaoObrigatoria[$i]\" value=\"$CertidaoOb\">\n";
																									}else{
																											echo " 	$DataCertidaoOb[$i]\n";
																											echo "	<input type=\"hidden\" name=\"DataCertidaoOb[$i]\" value=\"$DataCertidaoOb[$i]\">\n";
																											echo "	<input type=\"hidden\" name=\"CertidaoObrigatoria[$i]\" value=\"$CertidaoOb\">\n";
																									}
																									echo "	</td>\n";
												              						echo "</tr>\n";
												              						$Desativa = "";
															            		}
															          	}
																					$CarregaCertOb = 1;
													          	}else{
													          			if( count($CertidaoObrigatoria) > 0 ){
	   												              		for( $i=0; $i< count($CertidaoObrigatoria);$i++ ){
																              		$sql = "SELECT CTIPCECODI, ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO WHERE CTIPCECODI = $CertidaoObrigatoria[$i] ORDER BY 2";
												  												$res = $db->query($sql);
																								  if( PEAR::isError($res) ){
																										  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																									}else{
																											$Linha             = $res->fetchRow();
															          	      			$CertidaoObCodigo  = $Linha[0];
															          	      			$Descricao         = substr($Linha[1],0,75);
																		                	$DataHoje          = date("Y-m-d");
																		                	$DataCertidaoObInv = substr($DataCertidaoOb[$i],6,4)."-".substr($DataCertidaoOb[$i],3,2)."-".substr($DataCertidaoOb[$i],0,2);
																		                	if( $DataCertidaoObInv < $DataHoje ){
																		                			$Validade = "titulo1";
																		                	}else{
																		                			$Validade = "textonormal";
																		                			$Desativa = "S";
																		                	}
														              						echo "<tr>\n";
															              					echo "	<td class=\"$Validade\">$Descricao</td>\n";
															              					echo "	<td class=\"textonormal\" width=\"22%\" height=\"25\">\n";
															              					$ElementoOb = 2 * $i;
																											if( $Desativa != "S" ){
																	              					$URL = "../calendario.php?Formulario=CadCertidaoAlterar&Campo=elements[$ElementoOb]";
																									        echo "  	<input class=\"textonormal\" type=\"text\" name=\"DataCertidaoOb[$i]\" size=\"10\" maxlength=\"10\" value=\"$DataCertidaoOb[$i]\">&nbsp;\n";
																													echo "		<a href=\"javascript:janela('$URL','Calendario',220,170,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>\n";
																	              					echo "		<input type=\"hidden\" name=\"CertidaoObrigatoria[$i]\" value=\"$CertidaoObrigatoria[$i]\">\n";
																											}else{
																													echo " 	$DataCertidaoOb[$i]\n";
																													echo "	<input type=\"hidden\" name=\"DataCertidaoOb[$i]\" value=\"$DataCertidaoOb[$i]\">\n";
																													echo "	<input type=\"hidden\" name=\"CertidaoObrigatoria[$i]\" value=\"$CertidaoObrigatoria[$i]\">\n";
																											}
																											echo "	</td>\n";
															              					echo "</tr>\n";
															              					$Desativa = "";
																	                }
																							}
													  	            }
									  	              	}
							  	              			?>
							              				</table>
							              			</td>
							              		</tr>
							              		<tr>
							              			<td bgcolor="#DDECF9" class="textoabason" colspan="2" align="center">COMPLEMENTARES</td>
							              		</tr>
							              		<tr>
							              			<td class="textonormal" width="50%">
									              		<?php
																		$ElementoComp = $ElementoOb + 1;
																		if( $CarregaCertComp == 0 ){
											              		# Mostra a Lista de Certidões Complementares #
											              		$sql  = "SELECT A.CTIPCECODI, A.ETIPCEDESC, B.DFORCEVALI ";
										                		$sql .= "  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBFORNECEDORCERTIDAO B ";
										                		$sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI AND A.FTIPCEOBRI = 'N' ";
										                		$sql .= "   AND B.AFORCRSEQU = $Sequencial ";
										                		$sql .= " ORDER BY 1";
																	  		$res  = $db->query($sql);
																			  if( PEAR::isError($res) ){
																					  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				}else{
											              				$Rows = $res->numRows();
																						echo "<table class=\"textonormal\" border=\"1\" width=\"100%\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\">\n";
											              				if( $Rows != 0 ){
												              					echo "	<tr>\n";
												              					echo "		<td bgcolor=\"#75ADE6\" class=\"textoabasoff\">NOME DA CERTIDÃO</td>\n";
												              					echo "		<td bgcolor=\"#75ADE6\" class=\"textoabasoff\">DATA DE VALIDADE</td>\n";
												              					echo "	</tr>\n";
															              		for( $i=0; $i<$Rows;$i++ ){
																				      			$Linha                = $res->fetchRow();
														          	      			$CertidaoComp         = $Linha[0];
														          	      			$DescricaoComp        = substr($Linha[1],0,75);
																										$DataCertidaoComp[$i] = DataBarra($Linha[2]);
																	                	if( DataInvertida($DataCertidaoComp[$i]) < date("Y-m-d") ){
																	                			$Validade ="titulo1";
																	                	}else{
																	                			$Validade = "textonormal";
																	                			$Desativa = "S";
																	                	}
													              						echo "<tr>\n";
														              					echo "	<td class=\"$Validade\" width=\"*\">$DescricaoComp</td>\n";
														              					echo "	<td class=\"textonormal\" width=\"22%\" height=\"25\">\n";
														              					$ElementoComp = 12 + ( $i * 2 );
																										if( $Desativa != "S" ){
																              					$URL = "../calendario.php?Formulario=CadCertidaoAlterar&Campo=elements[$ElementoComp]";
																								        echo "  	<input class=\"textonormal\" type=\"text\" name=\"DataCertidaoComp[$i]\" size=\"10\" maxlength=\"10\" value=\"$DataCertidaoComp[$i]\">\n";
																												echo "		<a href=\"javascript:janela('$URL','Calendario',220,165,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>\n";
																              					echo "		<input type=\"hidden\" name=\"CertidaoComplementar[$i]\" value=\"$CertidaoComp\">\n";
																										}else{
																												echo " 	$DataCertidaoComp[$i]\n";
																												echo "	<input type=\"hidden\" name=\"DataCertidaoComp[$i]\" value=\"$DataCertidaoComp[$i]\">\n";
																												echo "	<input type=\"hidden\" name=\"CertidaoComplementar[$i]\" value=\"$CertidaoComp\">\n";
																										}
																										$Desativa = "";
																										echo "	</td>\n";
																								}
													  	              }else{
													  	            			echo "<tr><td align=\"center\">NÃO INFORMADO</td>\n";
													  	            	}
										              					echo "		</tr>\n";
											              				echo "	</table>\n";
											              		}
																				$CarregaCertComp = 1;
										              	}else{
										              			$ElementoComp = $ElementoOb;
									              				if( count($CertidaoComplementar) > 0 ){
											              				echo "<table class=\"textonormal\" border=\"1\" width=\"100%\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\">\n";
											              				echo "	<tr>\n";
										              					echo "		<td bgcolor=\"#75ADE6\" class=\"textoabasoff\">NOME DA CERTIDÃO</td>\n";
										              					echo "		<td bgcolor=\"#75ADE6\" class=\"textoabasoff\">DATA DE VALIDADE</td>\n";
										              					echo "	</tr>\n";
   												        					for( $i=0; $i< count($CertidaoComplementar);$i++ ){
															              		$sql = "SELECT CTIPCECODI,ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO WHERE CTIPCECODI = $CertidaoComplementar[$i] ORDER BY 2";
											  												$res = $db->query($sql);
																							  if( PEAR::isError($res) ){
																									  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																								}else{
																										$Linha = $res->fetchRow();
														          	      			$CertidaoCompCodigo  = $Linha[0];
														          	      			$Descricao         = substr($Linha[1],0,75);
														          	      			$DataCertidaoCompInv = substr($DataCertidaoComp[$i],6,4)."-".substr($DataCertidaoComp[$i],3,2)."-".substr($DataCertidaoComp[$i],0,2);
																	                	if( $DataCertidaoCompInv  < date("Y-m-d") ){
																	                			$Validade = "titulo1";
																	                	}else{
																	                			$Validade = "textonormal";
																	                			$Desativa = "S";
																	                	}
															          						echo "<tr>\n";
														              					echo "	<td class=\"$Validade\">$Descricao</td>\n";
														              					echo "	<td class=\"textonormal\" width=\"22%\" height=\"25\">\n";
														              					$ElementoComp = $ElementoComp + 2;
																										if( $Desativa != "S" ){
																              					$URL = "../calendario.php?Formulario=CadCertidaoAlterar&Campo=elements[$ElementoComp]";
																								        echo "  	<input class=\"textonormal\" type=\"text\" name=\"DataCertidaoComp[$i]\" size=\"10\" maxlength=\"10\" value=\"$DataCertidaoComp[$i]\">\n";
																												echo "		<a href=\"javascript:janela('$URL','Calendario',220,170,1,0)\"><img src=\"../midia/calendario.gif\" border=\"0\" alt=\"\"></a>\n";
																												echo "		<input type=\"hidden\" name=\"CertidaoComplementar[$i]\" value=\"$CertidaoComplementar[$i]\">\n";
																										}else{
																												echo " 	$DataCertidaoComp[$i]\n";
																												echo "	<input type=\"hidden\" name=\"DataCertidaoComp[$i]\" value=\"$DataCertidaoComp[$i]\">\n";
																												echo "	<input type=\"hidden\" name=\"CertidaoComplementar[$i]\" value=\"$CertidaoComplementar[$i]\">\n";
																										}
														              					$Desativa = "";
														              					echo "	</td>\n";
														              					echo "</tr>\n";
																                }
																						}
													  	              echo "	</table>\n";
											  	              }
									              		}
									              		?>
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
				        <td align="right" class="textonormal colspan="4">
	            		<input type="hidden" name="CarregaCertOb" value="<?php echo $CarregaCertOb; ?>">
	            		<input type="hidden" name="CarregaCertComp" value="<?php echo $CarregaCertComp; ?>">
	            		<input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
	            		<input type="button" value="Atualizar" class="botao" onclick="javascript:enviar('Atualizar');">
	            		<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
	            		<input type="hidden" name="Botao" value="">
				        </td>
        			</tr>
	     			</table>
					</td>
				</tr>
				<!-- Fim do Corpo -->
			</table>
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?php $db->disconnect(); ?>
