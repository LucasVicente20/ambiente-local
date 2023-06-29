<?php
/**
 * Portal de Compras
 * 
 * Programa: RelProtocoloListagem.php
 * Autor:    Roberta Costa
 * Data:     20/05/2003
 * Objetivo: Programa de relatório do protocolo de entrega
 * ----------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     24/10/2018
 * Objetivo: Tarefa Redmine 73662
 * ----------------------------------------------------------------------------------------------
 * Alterado: Madson Felix
 * Data: 02/08/2019
 * Objetivo: Tarefa Redmine 217859
 * ----------------------------------------------------------------------------------------------
 */

// Acesso ao arquivo de funções
include "../funcoes.php";

// Executa o controle de segurança
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso
AddMenuAcesso('/licitacoes/RelProtocoloImpressao.php');
AddMenuAcesso('/licitacoes/RelProtocoloParticipante.php');

// Variáveis com o global off
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Critica          = $_POST['Critica'];
	$Botao            = $_POST['Botao'];
	$Opcao            = $_POST['Opcao'];
	$Processo         = $_POST['Processo'];
	$AnoProcesso      = $_POST['AnoProcesso'];
	$GrupoCodigo      = $_POST['GrupoCodigo'];
	$ComissaoCodigo   = $_POST['ComissaoCodigo'];
	$OrgaoCodigo      = $_POST['OrgaoCodigo'];
	$ModalidadeCodigo = $_POST['ModalidadeCodigo'];
	$Licitacao        = $_POST['Licitacao'];
	$ListaCodigo      = $_POST['ListaCodigo'];
} else {
	$Processo         = $_GET['Processo'];
	$AnoProcesso      = $_GET['AnoProcesso'];
	$GrupoCodigo      = $_GET['GrupoCodigo'];
	$ComissaoCodigo   = $_GET['ComissaoCodigo'];
	$OrgaoCodigo      = $_GET['OrgaoCodigo'];
	$ModalidadeCodigo = $_GET['ModalidadeCodigo'];
	$Licitacao        = $_GET['Licitacao'];
	$ListaCodigo      = $_GET['ListaCodigo'];
	$Opcao            = $_GET['Opcao'];
}

// Identifica o programa para erro de banco de dados
$ErroPrograma = "RelProtocoloListagem.php";

if ($Botao == "Voltar") {
	if ($Opcao == "Alfabetica") {
    	$OpcaoVoltar = "Alfabetica";
  	} else {
  		$OpcaoVoltar = "OrdemData";
	}

  	$Url = "RelProtocoloParticipante.php?Processo=$Processo&AnoProcesso=$AnoProcesso&GrupoCodigo=$GrupoCodigo&ComissaoCodigo=$ComissaoCodigo&OrgaoCodigo=$OrgaoCodigo&ModalidadeCodigo=$ModalidadeCodigo&ListaCodigo=$ListaCodigo&Licitacao=$Licitacao&OpcaoVoltar=$OpcaoVoltar";

	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}

	header("location: ".$Url);
  	exit();
}
?>
<html>
<?php
// Carrega o layout padrão
layout();
?>
<script language="javascript" type="">
	<!--
	function enviar(valor) {
		document.Relatorio.Botao.value=valor;
		document.Relatorio.submit();
	}

	function Relatorio() {
		document.Relatorio.Codigo.value = document.Relatorio.Participantes.options[document.Relatorio.Participantes.selectedIndex].value;
	}

	function janela(pageToLoad, winName, width, height, center) {
		xposition = 0;
		yposition = 0;

		if ((parseInt(navigator.appVersion) >= 4 ) && (center)) {
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

		window.open(pageToLoad,winName,args);
	}

	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="RelProtocoloListagem.php" method="post" name="Relatorio">
		<br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2"><br>
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Relatórios > Protocolo de Entrega
    			</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($Mens == 1) {
				?>
				<tr>
					<td width="100"></td>
					<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
				<?php
			}
			?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="100"></td>
				<td class="textonormal">
					<table border="0" cellspacing="1" cellpadding="3" bgcolor="#ffffff" summary="">
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
											PROTOCOLO DE ENTREGA - RELATÓRIO
										</td>
									</tr>
									<tr>
										<td class="textonormal">
											<p align="justify">
												Para emitir o relatório clique no botão "Imprimir". Para retornar a tela anterior clique no botão "Voltar".
											</p>
										</td>
									</tr>
									<tr>
		   	        					<td class="textonormal" align="right">
											<input type="hidden" name="Processo" value="<?php echo $Processo;?>">
							    			<input type="hidden" name="AnoProcesso" value="<?php echo $AnoProcesso;?>">
							    			<input type="hidden" name="GrupoCodigo" value="<?php echo $GrupoCodigo;?>">
							    			<input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo;?>">
							    			<input type="hidden" name="OrgaoCodigo" value="<?php echo $OrgaoCodigo;?>">
							    			<input type="hidden" name="ModalidadeCodigo" value="<?php echo $ModalidadeCodigo;?>">
							    			<input type="hidden" name="ListaCodigo" value="<?php echo $ListaCodigo;?>">
							    			<input type="hidden" name="Licitacao" value="<?php echo $Licitacao;?>">
											<input type="hidden" name="Opcao" value="<?php echo $Opcao;?>">
											<?php
											$url = "RelProtocoloImpressao.php?Processo=$Processo&AnoProcesso=$AnoProcesso&GrupoCodigo=$GrupoCodigo&ComissaoCodigo=$ComissaoCodigo&OrgaoCodigo=$OrgaoCodigo&ModalidadeCodigo=$ModalidadeCodigo&ListaCodigo=$ListaCodigo&Licitacao=$Licitacao";

											if (!in_array($url,$_SESSION['GetUrl'])) {
												$_SESSION['GetUrl'][] = $url;
											}
											?>
	                						<input type="button" value="Imprimir" class="botao" onclick="javascript:janela('<?php echo $url?>','PortalCompras',700,300,1)">
	                						<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
	                						<input type="hidden" name="Botao" value="">
			          					</td>
									</tr>
        		  					<?php
	      							$db = Conexao();

									$sql  = "SELECT	B.ECOMLIDESC, E.EMODLIDESC, D.EORGLIDESC, A.ALICPOANOL, A.CLICPOCODL, ";
									$sql .= "      	B.CCOMLICODI ";
									$sql .= "FROM	SFPC.TBLICITACAOPORTAL A, ";
									$sql .= "		SFPC.TBCOMISSAOLICITACAO B, ";
									$sql .= "		SFPC.TBGRUPOEMPRESA C, ";
									$sql .= "       SFPC.TBORGAOLICITANTE D, ";
									$sql .= "		SFPC.TBMODALIDADELICITACAO E ";
									$sql .= "WHERE	B.CCOMLICODI = " . $ComissaoCodigo;
									$sql .= "		AND E.CMODLICODI = " . $ModalidadeCodigo;
									$sql .= "   	AND D.CORGLICODI = " . $OrgaoCodigo;
									$sql .= "		AND C.CGREMPCODI = " . $GrupoCodigo;
									$sql .= "   	AND A.CGREMPCODI = C.CGREMPCODI ";
									$sql .= "		AND A.CLICPOPROC = " . $Processo;
									$sql .= "   	AND A.ALICPOANOL = " . $AnoProcesso;
									$sql .= "		AND A.CLICPOCODL = " . $Licitacao;
									$sql .= " ORDER BY B.ECOMLIDESC, A.ALICPOANOL, A.CLICPOPROC ";

									$result = $db->query($sql);
							
									if (PEAR::isError($result)) {
								  		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									} else {
										$Rows = $result->numRows();

										while ($Linha = $result->fetchRow()) {
											$ComissaoDesc  = $Linha[0];
											$ModalidadeDes = $Linha[1];
											$OrgaoDesc     = $Linha[2];
											$AnoLicitacao  = $Linha[3];
											$Licitacao     = $Linha[4];
										}
									}

									$db->disconnect();

									$NCodigos = explode("_",$ListaCodigo);
	  	        					$Rows     = count($NCodigos);

									if ($Rows > 1) {
										$Rows = $Rows - 1;
									}

									for ($i = 0; $i < $Rows; $i++) {
    	      							?>
						  				<tr>
	  	        							<td>
		  	      								<table class="textonormal" border="0" summary="">
													<tr>
				  	    								<td colspan="2">
				  	    									<table class="textonormal" border="0" align="left" summary="">
																<tr>
				  	    			  								<td>
																		<img src="../midia/brasao.jpg" width="50" height="40" border="0" alt="">
																	</td>
				  	    			  	  							<td class="titulo3"><?php echo $OrgaoDesc; ?><br><?php echo $ComissaoDesc; ?></td>
				            									</tr>
	            				  							</table>
					  	        						</td>
	        	    								</tr>
													<tr>
	        	      									<td class="titulo3" align="center" colspan="5">PROTOCOLO DE ENTREGA VIA PORTAL DE COMPRAS</td>
	            									</tr>
													<tr>
	        	      									<td class="textonormal" colspan="2">
	        	      										<p align="justify">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	        	      										Recebemos da(o) <?php echo $OrgaoDesc."/".$ComissaoDesc; ?>, o Processo Licitatório Nº <?php echo $NProcesso = substr($Processo + 10000,1)."/".$AnoProcesso;?>, <?php echo $Reg[1]; ?> Nº <?php echo substr($Licitacao + 10000,1)."/".$AnoLicitacao;?>.
	        	      										</p>
	        	      									</td>
	            									</tr>
					          						<tr>
	            			  							<td colspan="2">
				    	      								<table class="textonormal" border="0" align="left" summary="">
																<tr>
													 				<?php
											   						$db = Conexao();

																	$sql  = "SELECT	ELISOLNOME, CLISOLCNPJ, CLISOLCCPF, ELISOLMAIL, ELISOLENDE, ";
																	$sql .= "       ALISOLFONE, ALISOLNFAX, NLISOLCONT, TLISOLDREC ";
																	$sql .= "FROM	SFPC.TBLISTASOLICITAN ";
																	$sql .= "WHERE	CLICPOPROC = " . $Processo;
																	$sql .= "		AND ALICPOANOP = " . $AnoProcesso;
																	$sql .= "   	AND CGREMPCODI = " . $GrupoCodigo;
																	$sql .= "		AND CCOMLICODI = " . $ComissaoCodigo;
																	$sql .= "   	AND CORGLICODI = " . $OrgaoCodigo;
																	$sql .= "		AND CLISOLCODI = " . $NCodigos[$i];
																	$sql .= "   	AND FLISOLPART = 'S' ";
																	
																	$result = $db->query($sql);

																	if (PEAR::isError($result)) {
														    			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	} else {
																		while ($Linha = $result->fetchRow()) {
																			$RazaoSocial = $Linha[0];
																			$CNPJ        = $Linha[1];
																			$CPF         = $Linha[2];
																			$Email       = $Linha[3];
																			$Endereco    = $Linha[4];
																			$Telefone    = $Linha[5];
																			$Fax         = $Linha[6];
																			$Contato     = $Linha[7];
																			$Data        = $Linha[8];
																		}
																	}
									    	    					?>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Razão Social</td>
																	<td class="textonormal"><?php echo $RazaoSocial; ?></td>
							           							</tr>
				            									<tr>
																	<?php
																	if ($CNPJ == "") {
																		?>
					        	      		 							<td class="textonormal" bgcolor="#DCEDF7" height="20">CPF/CNPJ</td>
					        	      		 							<td class="textonormal"><?php echo $CPF; ?></td>
																			<?php
																	} elseif ($CPF == "") {
																		?>
					        	      		 							<td class="textonormal" bgcolor="#DCEDF7" height="20">CPF/CNPJ</td>
					        	      		 							<td class="textonormal"><?php echo $CNPJ; ?></td>
																			<?php
																	}
																	?>
				            									</tr>
				            									<tr>
				        	      									<td class="textonormal" bgcolor="#DCEDF7" height="20">Endereço</td>
				        	      									<td class="textonormal"><?php echo $Endereco; ?></td>
				            									</tr>
				            									<tr>
				        	      									<td class="textonormal" bgcolor="#DCEDF7" height="20">E-mail</td>
				        	      									<td class="textonormal"><?php echo $Email; ?></td>
				            									</tr>
				            									<tr>
				        	      									<td class="textonormal" bgcolor="#DCEDF7" height="20">Telefone</td>
				        	      									<td class="textonormal"><?php echo $Telefone; ?></td>
				            									</tr>
				            									<tr>
				        	      									<td class="textonormal" bgcolor="#DCEDF7" height="20">Fax</td>
				        	      									<td class="textonormal"><?php echo $Fax; ?></td>
				            									</tr>
				            									<tr>
				        	      									<td class="textonormal" bgcolor="#DCEDF7" height="20">Nome do Contato</td>
				        	      									<td class="textonormal"><?php echo $Contato; ?></td>
				            									</tr>
							       								<tr>
				        	      									<td class="textonormal" bgcolor="#DCEDF7" height="20">Data do Download</td>
				        	      									<?php $Data= substr($Data,8,2) ."/". substr($Data,5,2) ."/". substr($Data,0,4) ." ". substr($Data,11,5); ?>
				        	      									<td class="textonormal"><?php echo $Data; ?><br></td>
				        	      								</tr>
				            								</table>
				            							</td>
				            						</tr>
	      	  									</table>
		          							</td>
		        						</tr>
										<?php
									}
									?>
									<tr>
	            			  			<td colspan="2">
				    	      				<table class="textonormal" border="0" align="left" summary="">
												<tr>
				        	      					<td class="textonormal" bgcolor="#DCEDF7" height="20" length="30" >Nº total de fornecedores:</td>
				        	      					<td class="textonormal"><?php echo $ListaCodigo; ?></td>
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
				<!-- Fim do Corpo -->
			</table>
		</form>
	</body>
</html>