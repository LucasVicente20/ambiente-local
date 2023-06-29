<?php
/**
 * Prefeitura do Recife
 * Portal de Compras
 * 
 * Programas: RelProtocoloImpressao.php
 * Autor:     Roberta Costa
 * Data:      20/05/2003
 * Objetivo:  Programa de Relatório do Protocolo de Entrega
 * ----------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     17/01/2023
 * Objetivo: Tarefa Redmine 277602
 * ----------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";
 
# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$Processo         = $_GET['Processo'];
	$AnoProcesso      = $_GET['AnoProcesso'];
	$GrupoCodigo      = $_GET['GrupoCodigo'];
	$ComissaoCodigo   = $_GET['ComissaoCodigo'];
	$OrgaoCodigo      = $_GET['OrgaoCodigo'];
	$ModalidadeCodigo = $_GET['ModalidadeCodigo'];  
	$Licitacao        = $_GET['Licitacao'];  
	$ListaCodigo      = $_GET['ListaCodigo'];  
	$Opcao            = $_GET['Opcao'];  
} else {
	$contador         = $_POST['contador'];  
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelProtocoloImpressao.php";
?>
<html>
<head>
<title>Portal de Compras - Prefeitura do Recife</title>
<script language="javascript" type="">
	<!--
	self.print();

	function Fecha() {
		window.close();
	}
	//-->
</script>	
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
	<form action="RelProtocoloImpressao.php" method="post" name="Relatorio">
	<p class="titulo3" align="center">
  		Prefeitura da Cidade do Recife<br><br>
  		<a href="javascript:Fecha()"><img src="../midia/brasao.jpg" width="50" height="40" border="0" alt=""></a>
	<p class="titulo3" align="right">
		Data: <?php echo date("d/m/Y H:i");?>
	</p>
	<table class="textonormal" border="0" align="left" summary="">
		<?php
  		# Verificando o tipo do navegador #
  		if ((preg_match("Nav", getenv("HTTP_USER_AGENT"))) || (preg_match("Gold", getenv("HTTP_USER_AGENT"))) || 
      		(preg_match("X11", getenv("HTTP_USER_AGENT"))) || (preg_match("Mozilla", getenv("HTTP_USER_AGENT"))) || 
      		(preg_match("Netscape", getenv("HTTP_USER_AGENT"))) AND (!preg_match("MSIE", getenv("HTTP_USER_AGENT")) AND 
      		(!preg_match("Konqueror", getenv("HTTP_USER_AGENT"))))) {
  			$browser = "Netscape"; 
  		} elseif (preg_match("MSIE", getenv("HTTP_USER_AGENT"))) {
  			$browser = "MSIE"; 
		} elseif (preg_match("Lynx", getenv("HTTP_USER_AGENT"))) {
			$browser = "Lynx"; 
		} elseif (preg_match("Opera", getenv("HTTP_USER_AGENT"))) {
			$browser = "Opera"; 
		} elseif (preg_match("WebTV", getenv("HTTP_USER_AGENT"))) {
			$browser = "WebTV"; 
		} elseif (preg_match("Konqueror", getenv("HTTP_USER_AGENT"))) {
			$browser = "Konqueror"; 
		} elseif ((preg_match("bot", getenv("HTTP_USER_AGENT"))) || (preg_match("Google", getenv("HTTP_USER_AGENT"))) || 
			(preg_match("Slurp", getenv("HTTP_USER_AGENT"))) || (preg_match("Scooter", getenv("HTTP_USER_AGENT"))) ||
			(preg_match("Spider", getenv("HTTP_USER_AGENT"))) || (preg_match("Infoseek", getenv("HTTP_USER_AGENT")))) {
			$browser = "Robôs de Busca"; 
		} else {
			$browser = "Desconhecido";
		}	 
		?>
  		<tr>
			<td align="center" valign="middle" class="titulo3">
				PROTOCOLO DE ENTREGA - RELATÓRIO<hr>
			</td>
		</tr>
		<tr>
  			<td>
  				<table class="textonormal" border="0" summary="">
						<tr>
      	  					<td width="10%">
      	  						<img src="../midia/brasao.jpg" width="50" height="40" border="0" alt="">
      	  					</td>
  	  						<td class="titulo3">
    	  						<?php
    							$db = Conexao();

								$sql    = "SELECT B.ECOMLIDESC, E.EMODLIDESC, D.EORGLIDESC, A.ALICPOANOL, ";
								$sql   .= "       A.CLICPOCODL, B.CCOMLICODI ";
								$sql   .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBGRUPOEMPRESA C, ";
								$sql   .= "       SFPC.TBORGAOLICITANTE D, SFPC.TBMODALIDADELICITACAO E ";
								$sql   .= " WHERE B.CCOMLICODI = $ComissaoCodigo AND E.CMODLICODI = $ModalidadeCodigo ";
								$sql   .= "   AND D.CORGLICODI = $OrgaoCodigo AND C.CGREMPCODI = $GrupoCodigo ";
								$sql   .= "   AND A.CGREMPCODI = C.CGREMPCODI AND A.CLICPOPROC = $Processo ";
								$sql   .= "   AND A.ALICPOANOL = $AnoProcesso AND A.CLICPOCODL = $Licitacao ";
								$sql   .= " ORDER BY B.ECOMLIDESC, A.ALICPOANOL, A.CLICPOPROC";

								$result = $db->query($sql);

								if (PEAR::isError($result)) {
						    		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								} else {
									while ($Linha = $result->fetchRow()) {
										$ComissaoDesc  = $Linha[0];
										$ModalidadeDes = $Linha[1];
										$OrgaoDesc     = $Linha[2];
										$AnoLicitacao  = $Linha[3];
										$Licitacao     = $Linha[4];
									}
								}

								$db->disconnect();

								echo $OrgaoDesc."<br>";
	      						echo $ComissaoDesc;
	      						?>
    						</td>
    					</tr>
						<tr>
      						<td class="titulo3" align="center" colspan="2">
      							PROTOCOLO DE ENTREGA VIA PORTAL DE COMPRAS
      						</td>
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

										$sql    = "SELECT ELISOLNOME, CLISOLCNPJ, CLISOLCCPF, ELISOLMAIL, ELISOLENDE, ";  
										$sql   .= "       ALISOLFONE, ALISOLNFAX, NLISOLCONT, TLISOLDREC ";
										$sql   .= "  FROM SFPC.TBLISTASOLICITAN ";
										$sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $AnoProcesso "; 
										$sql   .= "   AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo "; 
										$sql   .= "   AND CORGLICODI = $OrgaoCodigo";
										$sql   .= "   AND FLISOLPART = 'S'";

										// print_r($sql);
										$result = $db->query($sql);

										if (PEAR::isError($result)) {
								    		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}
										// Contar o número de downloads realizados
										$downloadsTotal = $result->numRows();
										
										// Contar o número de downloads realizados por interessados que não informaram seus dados
										$sqlAnonimos = "SELECT COUNT(*) FROM SFPC.TBLISTASOLICITAN WHERE CLICPOPROC = $Processo AND ALICPOANOP = $AnoProcesso AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo AND CORGLICODI = $OrgaoCodigo AND FLISOLPART = 'S' AND ELISOLMAIL IS NULL";
										$resultAnonimos = $db->query($sqlAnonimos);
										$LinhaDownloadAnonimos = $resultAnonimos->fetchRow();
										$downloadsAnonimos = $LinhaDownloadAnonimos[0];
										

										// Contar o número de downloads realizados por interessados que informaram seus dados
										$downloadsComDados = $downloadsTotal - $downloadsAnonimos;

										// Obter os emails dos interessados que informaram seus dados
										$sqlEmails = "SELECT DISTINCT(ELISOLMAIL) FROM SFPC.TBLISTASOLICITAN WHERE CLICPOPROC = $Processo AND ALICPOANOP = $AnoProcesso AND CGREMPCODI = $GrupoCodigo AND CCOMLICODI = $ComissaoCodigo AND CORGLICODI = $OrgaoCodigo AND FLISOLPART = 'S' AND ELISOLMAIL IS NOT NULL";
										// die($sqlEmails);
										$resultEmails = $db->query($sqlEmails);
										

										// print_r($resultEmails);die;
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
	    	    						?>
										

			      						<td class="textonormal" bgcolor="#DCEDF7">Nº de downloads realizados</td>
										
			      						<td class="textonormal"><?php echo $downloadsTotal; ?><br></td>
			      					</tr>
			   						<tr>
			      						<td class="textonormal" bgcolor="#DCEDF7">Nº de downloads realizados por interessados que desejaram não informar seus dados</td>
										
			      						<td class="textonormal"><?php echo $downloadsAnonimos; ?><br></td>
			      					</tr>
			   						<tr>
			      						<td class="textonormal" bgcolor="#DCEDF7">Nº de downloads realizados por interessados que informaram os seus dados</td>
										
			      						<td class="textonormal"><?php echo $downloadsComDados; ?><br></td>
			      					</tr>
			   						<tr>
			      						<td class="textonormal" bgcolor="#DCEDF7">Emails dos interessados que informaram seus dados</td>
										
			      						<tr>
											<td class="textonormal">
											<?php while ($LinhaEmail = $resultEmails->fetchRow()) { ?>
													<?php echo strtolower($LinhaEmail[0]) . '<br>'; ?>	
											<?php }?>					
											</td> 
										<tr>
			      					</tr>
			   						
			   						<?php
									$navegador  = $_SERVER['HTTP_USER_AGENT'];

									$Nnavegador = explode(";", $navegador);
									$navegador  = substr($Nnavegador[1],1,8);

									if ($cont == 3) {
								 		if ($browser == "MSIE") {
											if ($navegador == "MSIE 6.0") {
									        	echo "<tr>";  	  	  
								          		echo "	<td>";  	  	  
												echo "		<br><br><br><br><br><br><br><br><br><br><br><br>";
								          		echo "	</td>";  	  	  
								          		echo "</tr>";  	  	  
								      		} else {
									        	echo "<tr>";  	  	  
								          		echo "	<td>";  	  	  
								          		echo "		<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
								          		echo "	</td>";  	  	  
								          		echo "</tr>";  	  	  
									   		}
										}
									}

									if (( $cont%3 == 0) and ($cont != 3)) {
										if ($navegador == "MSIE 6.0") {
						          			echo "<tr>";
						          			echo "	<td>";
						          			echo "		<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
						          			echo "	</td>";
						          			echo "</tr>";
										} else {
											echo "<tr>";  	  	  
								      		echo "	<td>";  	  	  
								      		echo "		<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
								      		echo "	</td>";  	  	  
								      		echo "</tr>";  	  	  
						  	  			}	
						  			}
						  			?>
								</table>
							</td>
						</tr>
						<tr>
      						<td class="textonormal" colspan="2"><hr></td>
      					</tr>
					
  					</table>	
  				</td>	  
			</tr>
		</table>
	</form>
</body>
</html>