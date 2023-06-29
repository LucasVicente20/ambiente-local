<?php
/**
 * Portal de Compras
 * 
 * Programa: RelProtocoloParticipante.php
 * Autor:    Roberta Costa
 * Data:     20/05/2003
 * Objetivo: Programa de relatório do protocolo de entrega
 * ------------------------------------------------------------------------------------------
 * Alterado: Madson Felix
 * Data:     02/08/2019
 * Objetivo: Tarefa Redmine 217859
 * ------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     16/01/2023
 * Objetivo: Tarefa Redmine 277602
 * ------------------------------------------------------------------------------------------
 */

// Acesso ao arquivo de funções
include "../funcoes.php";

// Executa o controle de segurança
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso
AddMenuAcesso('/licitacoes/RelProtocoloSelecionar.php');
AddMenuAcesso('/licitacoes/RelProtocoloListagem.php');

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
	$Participantes    = $_POST['Participantes'];
} else {
	$Botao            = $_GET['Botao'];
	$OpcaoVoltar      = $_GET['OpcaoVoltar'];
	$Processo         = $_GET['Processo'];
	$AnoProcesso      = $_GET['AnoProcesso'];
	$GrupoCodigo      = $_GET['GrupoCodigo'];
	$ComissaoCodigo   = $_GET['ComissaoCodigo'];
	$OrgaoCodigo      = $_GET['OrgaoCodigo'];
	$ModalidadeCodigo = $_GET['ModalidadeCodigo'];
	$Licitacao        = $_GET['Licitacao'];
}

// Identifica o programa para erro de banco de dados
$ErroPrograma = "RelProtocoloParticipante.php";

$db = Conexao();

$sql  = "SELECT	CLICPOPROC ";
$sql .= "FROM	SFPC.TBLISTASOLICITAN ";
$sql .= "WHERE	CLICPOPROC = " . $Processo;
$sql .= "		AND ALICPOANOP = " . $AnoProcesso;
$sql .= "		AND CGREMPCODI = " . $GrupoCodigo;
$sql .= "		AND CCOMLICODI = " . $ComissaoCodigo;
$sql .= "   	AND CORGLICODI = " . $OrgaoCodigo;
$sql .= " ORDER BY ELISOLNOME ";

$result = $db->query($sql);

if (DB::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

$Rows = $result->numRows();

if ($Rows == 0) {
    $Mensagem .= urlencode("Nenhuma Ocorrência Encontrada");
	$Url = "RelProtocoloSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
	
	if (!in_array($Url, $_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}

	header("location: ".$Url);
	exit();
} else {
	if ($Botao == "Gerar") {
		if ($Participantes == "") {
			$Mens      = 1;
			$Tipo      = 2;
			$Critica   = 0;
		  	$Mensagem .= "Informe: Participante(s)";
		} else {
			$num = count($Participantes);

			if ($num > 1) {
				for ($i = 0; $i < $num; $i++) {
					$NParticipantes = explode("_",$Participantes[$i]);
					$ListaCodigo[$i] = $NParticipantes[5];
					$Codigos = $Codigos.$ListaCodigo[$i];
					$Codigos = $Codigos."_";
				}

				$NParticipantes   = explode("_",$Participantes[0]);
				$Processo         = $NParticipantes[0];
				$AnoProcesso      = $NParticipantes[1];
				$GrupoCodigo      = $NParticipantes[2];
				$ComissaoCodigo   = $NParticipantes[3];
				$OrgaoCodigo      = $NParticipantes[4];
				$ModalidadeCodigo = $ModalidadeCodigo;

				$Url = "RelProtocoloListagem.php?Processo=$Processo&AnoProcesso=$AnoProcesso&GrupoCodigo=$GrupoCodigo&ComissaoCodigo=$ComissaoCodigo&OrgaoCodigo=$OrgaoCodigo&ModalidadeCodigo=$ModalidadeCodigo&ListaCodigo=$Codigos&Licitacao=$Licitacao&Opcao=$Opcao&".time();

				if (!in_array($Url, $_SESSION['GetUrl'])) {
					$_SESSION['GetUrl'][] = $Url;
				}
				
				header("location: ".$Url);
				exit();
			} else {
				$NParticipantes   = explode("_",$Participantes[0]);
				$Processo         = $NParticipantes[0];
				$AnoProcesso      = $NParticipantes[1];
				$GrupoCodigo      = $NParticipantes[2];
				$ComissaoCodigo   = $NParticipantes[3];
				$OrgaoCodigo      = $NParticipantes[4];
				$ListaCodigo      = $NParticipantes[5];
				$ModalidadeCodigo = $ModalidadeCodigo;

				$Url = "RelProtocoloListagem.php?Processo=$Processo&AnoProcesso=$AnoProcesso&GrupoCodigo=$GrupoCodigo&ComissaoCodigo=$ComissaoCodigo&OrgaoCodigo=$OrgaoCodigo&ListaCodigo=$ListaCodigo&ModalidadeCodigo=$ModalidadeCodigo&Licitacao=$Licitacao&Opcao=$Opcao&".time();

				if (!in_array($Url, $_SESSION['GetUrl'])) {
					$_SESSION['GetUrl'][] = $Url;
				}
				
				header("location: ".$Url);
				exit();
			}
		}
	} elseif ($Botao == "Voltar") {
	 	header("location: RelProtocoloSelecionar.php");
	 	exit();
	}
}
?>
<html>
<?php
// Carrega o layout padrão
layout();
?>
<script language="javascript" type="text/javascript">
	<!--
	function enviar(valor) {
		document.Relatorio.Botao.value=valor;
		document.Relatorio.submit();
	}

	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="RelProtocoloParticipante.php" method="post" name="Relatorio">
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
					<table border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" summary="">
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
											PROTOCOLO DE ENTREGA - INTERESSADOS
										</td>
									</tr>
									<tr>
										<td class="textonormal">
											<p align="justify">
												Selecione o(s) Participantes(s) da Licitação e clique no botão "Gerar".
												Use (CTRL) +  clique no botão esquerdo do mouse para selecionar mais de um interessado.
											</p>
										</td>
									</tr>
		        					<tr>
	  	        						<td>
	    	      							<table class="textonormal" border="0" width="100%" summary="">
												<tr>
				              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Comissão </td>
				              						<td class="textonormal">
				              							<?php
	                  									$db = Conexao();

														$sql  = "SELECT	ECOMLIDESC ";
														$sql .= "FROM	SFPC.TBCOMISSAOLICITACAO ";
														$sql .= "WHERE	CCOMLICODI = " . $ComissaoCodigo;

														$result = $db->query($sql);

														if (DB::isError($result)) {
											    			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														} else {
															while ($Linha = $result->fetchRow()) {
							         	     					echo $Linha[0];
						              						}
						          						}
				              							?>
				              						</td>
				            					</tr>
			 									<tr>
				              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Processo</td>
				              						<td class="textonormal"><?php echo substr($Processo + 10000,1) ?></td>
				            					</tr>
												<tr>
				              						<td class="textonormal" bgcolor="#DCEDF7" height="20">Ano </td>
				              						<td class="textonormal"><?php echo $AnoProcesso ?></td>
				            					</tr>
												<tr>
	        	      								<td class="textonormal" bgcolor="#DCEDF7">Participantes</td>
													<td class="textonormal" bgcolor="#FFFFFF">
			                  							<select name="Participantes[]" value="" multiple size="8" class="textonormal">
			                  								<?php
															// Mostra os participantes da respectiva licitação
			                  								$sql  = "SELECT	CLICPOPROC, ALICPOANOP, CGREMPCODI, CCOMLICODI, CORGLICODI, ";
			                  								$sql .= "       CLISOLCODI, ELISOLNOME ";
			                  								$sql .= "FROM	SFPC.TBLISTASOLICITAN ";
															$sql .= "WHERE	CLICPOPROC = " . $Processo;
															$sql .= "		AND ALICPOANOP = " . $AnoProcesso;
															$sql .= "   	AND CGREMPCODI = " . $GrupoCodigo;
															$sql .= "		AND CCOMLICODI = " . $ComissaoCodigo;
															$sql .= "		AND CORGLICODI = " . $OrgaoCodigo;
															$sql .= "		AND FLISOLPART = 'S'";
															$sql .= "		AND ELISOLNOME <> ''";
															
															if ($Botao == "Alfabetica" or $OpcaoVoltar == "Alfabetica") {
																$sql  .= " ORDER BY ELISOLNOME";
																$Opcao = "Alfabetica";
															} else {
													    		$sql  .= " ORDER BY TLISOLDREC";
																$Opcao = "OrdemData";
															}

															$result = $db->query($sql);
													
															if (DB::isError($result)) {
													   	 		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}

															while ($Linha = $result->fetchRow()) {
																echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[3]_$Linha[4]_$Linha[5]\">$Linha[6]</option>\n" ;
															}

															$db->disconnect();
															?>
			                  							</select>
									    				<input type="hidden" name="Opcao" value="<?php echo $Opcao; ?>">
		          	    								<input type="hidden" name="Critica" value="1">
										    			<input type="hidden" name="Processo" value="<?php echo $Processo; ?>">
										    			<input type="hidden" name="AnoProcesso" value="<?php echo $AnoProcesso; ?>">
										    			<input type="hidden" name="GrupoCodigo" value="<?php echo $GrupoCodigo; ?>">
										    			<input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo; ?>">
										    			<input type="hidden" name="OrgaoCodigo" value="<?php echo $OrgaoCodigo; ?>">
										    			<input type="hidden" name="ModalidadeCodigo" value="<?php echo $ModalidadeCodigo; ?>">
										    			<input type="hidden" name="Licitacao" value="<?php echo $Licitacao; ?>">
									    			</td>
	            								</tr>
	      	  								</table>
		          						</td>
		        					</tr>
									<tr>
				   	        			<td class="textonormal" align="right">
	                						<input type="button" value="Ordem Alfabética" class="botao" onclick="javascript:enviar('Alfabetica')">
                							<input type="button" value="Ordem por Data" class="botao" onclick="javascript:enviar('OrdemData')">
	                						<input type="button" value="Gerar" class="botao" onclick="javascript:enviar('Gerar');">
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