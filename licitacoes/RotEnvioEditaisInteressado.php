<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotEnvioEditaisInteressado.php
# Objetivo: Programa de Envio de Editais por Interessados
# Autor:    Roberta Costa
# Data:     20/05/03
# Alterado: Carlos Abreu
# Data:     10/05/2007 - coloção da funcao set_time_limite com tempo de 5min para evitar que pare o programa pare por falta de tempo
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Aumenta o tempo de espera do servidor web para término de execução da página #
set_time_limit(3000);

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/RotEnvioEditaisCorreio.php' );
AddMenuAcesso( '/licitacoes/RotEnvioEditaisExibir.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica              = $_POST['Critica'];
		$Botao                = $_POST['Botao'];
		$Licitacao            = $_POST['Licitacao'];
		$Processo             = $_POST['Processo'];
		$AnoProcesso          = $_POST['AnoProcesso'];
		$GrupoCodigo          = $_POST['GrupoCodigo'];
		$ComissaoCodigo       = $_POST['ComissaoCodigo'];
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
		$ModalidadeCodigo     = $_POST['ModalidadeCodigo'];
		$Participantes        = $_POST['Participantes'];
		$NomeArquivo          = $_POST['NomeArquivo'];
		$TipoDocumento        = $_POST['TipoDocumento'];
}else{
		$Critica              = $_GET['Critica'];
		$Mensagem             = $_GET['Mensagem'];
		$Mens                 = $_GET['Mens'];
		$Tipo                 = $_GET['Tipo'];
		$Processo             = $_GET['Processo'];
		$AnoProcesso          = $_GET['AnoProcesso'];
		$GrupoCodigo          = $_GET['GrupoCodigo'];
		$ComissaoCodigo       = $_GET['ComissaoCodigo'];
		$OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
		$ModalidadeCodigo     = $_GET['ModalidadeCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Verifica dados na tabela de Lista de Solicitantes #
$db     = Conexao();
$sql    = "SELECT CLICPOPROC FROM SFPC.TBLISTASOLICITAN ";
$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $AnoProcesso ";
$sql   .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND FLISOLPART = 'S' " ;
$sql   .= " ORDER BY ELISOLNOME";
$result = $db->query($sql);
if( PEAR::isError($result) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Rows = $result->numRows();
		if( $Rows == 0 ){
				$Mensagem .= "Nenhum participante cadastrado para esse processo licitatório";
				$Url = "RotEnvioEditaisCorreio.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1&Critica=0";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit();
		}else{
				if( $Botao == "Exibir" ){
						$Url = "RotEnvioEditaisExibir.php?Processo=$Processo&AnoProcesso=$AnoProcesso&GrupoCodigo=$GrupoCodigo&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&ModalidadeCodigo=$ModalidadeCodigo&ListaCodigo=$ListaCodigo&Rows=$Rows";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				  	header("location: ".$Url);
				  	exit();
				}elseif( $Botao == "Voltar" ){
						$Url = "RotEnvioEditaisCorreio.php?Processo=$Processo&AnoProcesso=$AnoProcesso&GrupoCodigo=$GrupoCodigo&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&ModalidadeCodigo=$ModalidadeCodigo&ListaCodigo=$ListaCodigo";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				  	header("location: ".$Url);
				  	exit();
				}else{
						if( $Critica == 1 ){
								$_FILES['NomeArquivo']['name'] = RetiraAcentos($_FILES['NomeArquivo']['name']);
								$Mens     = 0;
								$Mensagem = "Informe: ";
								if( $Participantes == "" ){
										$Mens      = 1;
										$Tipo      = 2;
										$Critica   = 0;
									  $Mensagem .= "Participante(s)";
								}
							  if ( !eregi("\.zip$", $_FILES['NomeArquivo']['name']) &&
							  		 !eregi("\.pdf$", $_FILES['NomeArquivo']['name']) &&
							  		 !eregi("\.rtf$", $_FILES['NomeArquivo']['name']) &&
							  		 !eregi("\.doc$", $_FILES['NomeArquivo']['name']) &&
							  		 !eregi("\.xls$", $_FILES['NomeArquivo']['name']) &&
							  		 !eregi("\.txt$", $_FILES['NomeArquivo']['name']) &&
							  		 !eregi("\.sdw$", $_FILES['NomeArquivo']['name']) &&
							  		 !eregi("\.jpg$", $_FILES['NomeArquivo']['name']) &&
							  		 !eregi("\.bmp$", $_FILES['NomeArquivo']['name']) ) {
									  		if ($Mens == 1){ $Mensagem .= ", "; }
												$Mens      = 1;
												$Tipo      = 2;
												$Mensagem .= "Somente Arquivos com a Extensão .zip, .jpg, .bmp, .pdf, .rtf, .doc, .xls, .txt ou .sdw";
								}else{
										if( $_FILES['NomeArquivo']['size'] == 0 ){
												$Mens      = 1;
												$Tipo      = 2;
												$Mensagem .= "Arquivo com Tamanho diferente de 0 Kb";
										}else{
												$Tamanho = 2097152; /* 2MB */
												if( ( $_FILES['NomeArquivo']['size'] > $Tamanho )  ) {
														if ($Mens == 1){ $Mensagem .= ", "; }
														$Mens      = 1;
														$Tipo      = 2;
														$Kbytes    = $Tamanho/1024;
														$Kbytes    = (int) $Kbytes;
														$Mensagem .= "Arquivo Tamanho Máximo: $Kbytes Kb";
												}
										}
								}
								if( $TipoDocumento == "" ) {
										if ($Mens == 1){ $Mensagem .= ", "; }
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "Tipo de Documento";
								}
								if( $Mens == 0 ){
								    $Arquivo = $GLOBALS["CAMINHO_UPLOADS"]."temp/licitacoes/".$_FILES['NomeArquivo']['name'];
								    $ArquivoNome = $_FILES['NomeArquivo']['name'];
										if( file_exists($Arquivo) ){ unlink ($Arquivo);}
										if( @move_uploaded_file($_FILES['NomeArquivo']['tmp_name'], $Arquivo) ) {
												$Mens     = 1;
												$Tipo     = 1;
												$Mensagem = "Documento Carregado com Sucesso";

												## Email - Abreu ##
											  if ( eregi("\.zip$", $_FILES['NomeArquivo']['name']) ) { $Tipo = "zip"; }
												if ( eregi("\.pdf$", $_FILES['NomeArquivo']['name']) ) { $Tipo = "pdf"; }
												if ( eregi("\.rtf$", $_FILES['NomeArquivo']['name']) ) { $Tipo = "rtf"; }
												if ( eregi("\.doc$", $_FILES['NomeArquivo']['name']) ) { $Tipo = "doc"; }
												if ( eregi("\.bmp$", $_FILES['NomeArquivo']['name']) ) { $Tipo = "bmp"; }
												if ( eregi("\.jpg$", $_FILES['NomeArquivo']['name']) ) { $Tipo = "jpg"; }
												if ( eregi("\.xls$", $_FILES['NomeArquivo']['name']) ) { $Tipo = "xls"; }
												if ( eregi("\.txt$", $_FILES['NomeArquivo']['name']) ) { $Tipo = "txt"; }
												if ( eregi("\.sdw$", $_FILES['NomeArquivo']['name']) ) { $Tipo = "sdw"; }

												$mime_list = array("html"=>"text/html","htm"=>"text/html", "txt"=>"text/plain", "rtf"=>"text/enriched","csv"=>"text/tab-separated-values","css"=>"text/css","gif"=>"image/gif","doc"=>"application/msword","jpg"=>"image/jpg","bmp"=>"image/jpg","zip"=>"application/x-zip-compressed","pdf"=>"application/pdf","xls"=>"text/enriched","sdw"=>"text/enriched");

												if( $TipoDocumento == "E" ){
														$Subject = "Envio de Edital Atualizado - Prefeitura do Recife";
												}elseif( $TipoDocumento == "D" ){
														$Subject = "Envio de Documento - Prefeitura do Recife";
												}

												$Header = "From: portalcompras@recife.pe.gov.br";

												$con = "SELECT ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO WHERE CCOMLICODI = $ComissaoCodigo";
												$res = $db->query($con);
												if( PEAR::isError($res) ){
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $con");
												}else{
														while( $col = $res->fetchRow() ){
																$ComissaoDesc = $col[0];
														}
												}

												$Body = "A comissão $ComissaoDesc está enviando este documento para o(a) Sr(a), ";
												if( $TipoDocumento == "E" ){
														$Body .= "pois houve uma alteração no Edital do ";
												}elseif( $TipoDocumento == "D" ){
														$Body .= "referente ao ";
												}
												$Body .= "Processo Licitatório nº ";
												$Body .= $Processo."/".$AnoProcesso.". ";
												$Body .= "Qualquer dúvida entrar em contato com a referida comissão de licitação.";

								        $ParticipantesSelecionados = $Participantes;
						            for( $P = 0; $P < count($ParticipantesSelecionados); $P++ ) {
														$Participantes = $ParticipantesSelecionados[$P];
														$Participantes = explode("_",$Participantes);
														$sql    = "SELECT CLICPOPROC, ALICPOANOP, ELISOLNOME, ELISOLMAIL ";
														$sql   .= "  FROM SFPC.TBLISTASOLICITAN ";
														$sql   .= " WHERE CLICPOPROC = $Participantes[0] AND ALICPOANOP = $Participantes[1] ";
														$sql   .= "   AND CGREMPCODI = $Participantes[2] AND CCOMLICODI = $Participantes[3] ";
														$sql   .= "   AND CORGLICODI = $Participantes[4] AND CLISOLCODI = $Participantes[5] ";
														$result = $db->query($sql);
														if( PEAR::isError($result) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																while( $Linha = $result->fetchRow() ){
																		$Processo    = $Linha[0];
																		$AnoProcesso = $Linha[1];
																		$Nome        = $Linha[2];
																		$to          = $Linha[3];
																}
																if( EnviaEmail($to, $Subject, $Body, $Header, $Arquivo , $ArquivoNome) ){
																		# Atualizando a tabela de lista de solicitantes #
																		$db->query("BEGIN TRANSACTION");
																		$sql    = "UPDATE SFPC.TBLISTASOLICITAN ";
																		$sql   .= "   SET FLISOLENVI = 'S', TLISOLULAT = '".date("Y-m-d")."'";
																		$sql   .= " WHERE CLICPOPROC = $Participantes[0] ";
																		$sql   .= "   AND ALICPOANOP = $Participantes[1] ";
																		$sql   .= "   AND CGREMPCODI = $Participantes[2] ";
																		$sql   .= "   AND CCOMLICODI = $Participantes[3] ";
																		$sql   .= "   AND CORGLICODI = $Participantes[4] ";
																		$sql   .= "   AND CLISOLCODI = $Participantes[5]";
																		$result = $db->query($sql);
																		if( PEAR::isError($result) ){
																				$db->query("ROLLBACK");
																		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				$db->query("COMMIT");
																				$db->query("END TRANSACTION");
																				$NProcesso = substr($Processo + 10000,1);
																				if ( $Mensagem != "" ){ $Mensagem .= ".<br>"; }
																				$Mensagem .= "$NProcesso/$AnoProcesso $Nome - Email enviado com sucesso";
																		}
																}else{
																		$NProcesso = substr($Linha[0] + 10000,1);
																		if ( $Mensagem != "" ){ $Mensagem .= ".<br>"; }
																		$Mensagem .= "$NProcesso/$AnoProcesso $Nome - Email falhou";
																}
														}
												}
												$db->disconnect();
												unlink($Arquivo);
												$Url = "RotEnvioEditaisCorreio.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1&Critica=0";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												header("location: ".$Url);
												exit();
										}else{
												$Mens     = 1;
												$Tipo     = 2;
												$Mensagem = "Erro no Carregamento do Arquivo!";
										}
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
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Rot.Botao.value=valor;
	document.Rot.submit();
}
function janela( pageToLoad, winName, width, height, center) {
	xposition=0;
	yposition=0;
	if ((parseInt(navigator.appVersion) >= 4 ) && (center)){
		xposition = (screen.width - width) / 2;
		yposition = (screen.height - height) / 2;
	}
	args = "width=" + width + ","
	+ "height=" + height + ","
	+ "location=0,"
	+ "menubar=0,"
	+ "resizable=0,"
	+ "scrollbars=0,"
	+ "status=0,"
	+ "titlebar=no,"
	+ "toolbar=0,"
	+ "hotkeys=0,"
	+ "z-lock=1," //Netscape Only
	+ "screenx=" + xposition + "," //Netscape Only
	+ "screeny=" + yposition + "," //Netscape Only
	+ "left=" + xposition + "," //Internet Explore Only
	+ "top=" + yposition; //Internet Explore Only
	window.open( pageToLoad,winName,args );
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form enctype="multipart/form-data" action="RotEnvioEditaisInteressado.php" method="post" name="Rot">
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2"><br>
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Envio de Editais
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									ENVIO DE EDITAIS - INTERESSADOS
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Selecione o(s) interessado(s) desejado(s). Use (CTRL) +  clique no botão esquerdo do mouse para selecionar mais de um interessado. Depois, selecione o documento desejado através do botão procurar e clique no botão "Enviar".
									</p>
								</td>
							</tr>
							<tr>
								<td>
		    	      	<table class="textonormal" border="0" align="left" summary="">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Comissão</td>
	        	      		<td class="textonormal">
   		                <?php
        	      			$db     = Conexao();
	                  	$sql    = "SELECT ECOMLIDESC FROM SFPC.TBCOMISSAOLICITACAO ";
											$sql   .= " WHERE CCOMLICODI = $ComissaoCodigo ORDER BY 1";
											$result = $db->query($sql);
											if( PEAR::isError($result) ){
											    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											}else{
													while( $Linha = $result->fetchRow() ){
															echo $Linha[0];
		   		                }
		   		            }
   		                ?>
   		                </td>
   		                <td class="textonormal" >
   		                	<input type="hidden" name="ComissaoDesc" value="<?php echo $Linha[0]; ?>">
   		                </td>
	            			</tr>
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Processo</td>
   		                <td class="textonormal"><?php echo substr($Processo + 10000,1);?></td>
	            			</tr>
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Ano</td>
   		                <td class="textonormal"><?php echo $AnoProcesso;?></td>
	            			</tr>
										<tr>
					           	<td class="textonormal" bgcolor="#DCEDF7" valign="top">Interessados*</td>
											<td class="textonormal">
			                  <select name="Participantes[]" multiple size="8" class="textonormal">
			                  	<?php
													# Mostra os participantes da respectiva licitação #
													$sql    = "SELECT CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, ";
													$sql   .= " 			CORGLICODI, CLISOLCODI, ELISOLNOME ";
													$sql   .= "  FROM SFPC.TBLISTASOLICITAN ";
													$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $AnoProcesso ";
													$sql   .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo ";
													$sql   .= "   AND CORGLICODI = $OrgaoLicitanteCodigo AND FLISOLPART = 'S' ";
													$sql   .= " ORDER BY ELISOLNOME";
													$result = $db->query($sql);
													if( PEAR::isError($result) ){
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															$email = "";
															while( $Linha = $result->fetchRow() ){
																	if( FindArray("$Linha[0]_$Linha[1]_$Linha[2]_$Linha[3]_$Linha[4]_$Linha[5]",$Participantes) ){
																			echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[3]_$Linha[4]_$Linha[5]\" selected>$Linha[6]</option>\n" ;
																	}else{
																			echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[3]_$Linha[4]_$Linha[5]\">$Linha[6]</option>\n" ;
																	}
															}
													}
													$db->disconnect();
													?>
			                  </select>
				              </td>
										</tr>
			              <tr>
                 	    <td class="textonormal" bgcolor="#DCEDF7">Documento*</td>
                 	    <td>
                 	    	<input type="file" name="NomeArquivo" class="textonormal">
												<input type="hidden" name="Processo" value="<?php echo $Processo; ?>">
										    <input type="hidden" name="AnoProcesso" value="<?php echo $AnoProcesso; ?>">
										    <input type="hidden" name="GrupoCodigo" value="<?php echo $GrupoCodigo; ?>">
										    <input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo; ?>">
										    <input type="hidden" name="OrgaoLicitanteCodigo" value="<?php echo $OrgaoLicitanteCodigo; ?>">
										    <input type="hidden" name="ModalidadeCodigo" value="<?php echo $ModalidadeCodigo; ?>">
											  <input type="hidden" name="ListaCodigo" value="<?php echo $ListaCodigo;?>">
					              <input type="hidden" name="Critica" value="1">
				              </td>
             	  		</tr>
				 	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Documento*</td>
   		                <td class="textonormal">
   		                	<input type="radio" name="TipoDocumento" value="E" <?php if( $TipoDocumento == "E" ){ echo "checked"; } ?>> Edital Atualizado
   		                	<input type="radio" name="TipoDocumento" value="D" <?php if( $TipoDocumento == "D" ){ echo "checked"; } ?>> Outros
   		                </td>
	            			</tr>
									</table>
								</td>
							</tr>
							<tr>
		   	        <td class="textonormal" align="right">
             	    <input type="button" value="Exibir Interessados" class="botao" onclick="javascript:enviar('Exibir');">
             	    <input type="button" value="Enviar" class="botao" onclick="javascript:enviar('Enviar');">
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
</body>
</html>
