<?php
/*-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRelatorioSARPSelecionar.php
# Autor:    Igor Duarte
# Data:     02/10/2012
# Objetivo: Programa de selecao do dados para criacao do relatorio
#			do tipo SARP 
#-----------------------------------------------------------------------------
*/
require_once("../licitacoes/funcoesComplementaresLicitacao.php");
require_once("../compras/funcoesCompras.php");

# Acesso ao arquivo de funcoes #
require_once "../funcoes.php";

# Executa o controle de seguranca  #
session_start();
Seguranca();

# Adiciona paginas no MenuAcesso #
AddMenuAcesso( '/compras/CadRelatorioSARPPdf.php' );

# Variaveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
	$Botao            	= $_POST['Botao'];
	$DataIni      		= $_POST['DataIni'];		//ok
	$DataFim      		= $_POST['DataFim'];		//ok
	$Orgao				= $_POST['Orgao'];			//ok
	 
	if( $DataIni != "" ){
		$DataIni = FormataData($DataIni);
	}
	if( $DataFim != "" ){
		$DataFim = FormataData($DataFim);
	}
}
else{
	$Critica  = $_GET['Critica'];
	$Mensagem = urldecode($_GET['Mensagem']);
	$Mens     = $_GET['Mens'];
	$Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$usuario	= $_SESSION['_cusupocodi_'];

$dataTIni	= explode("/", $DataIni);
$dataTIni	= $dataTIni[2]."-".$dataTIni[1]."-".$dataTIni[0];

$dataTFim	= explode("/", $DataFim);
$dataTFim	= $dataTFim[2]."-".$dataTFim[1]."-".$dataTFim[0];

if( $Botao == "Limpar" ){
	
	$Botao		= "";	
	$DataIni    = NULL;		//ok
	$DataFim    = NULL;		//ok
	$Orgao		= NULL;		//ok
}
elseif($Botao == "Pesquisar"){
	$d1 = explode("/", $DataIni);
	$d2 = explode("/", $DataFim);
	
	$d3 = ValidaData(FormataData($DataIni));
	$d4 = ValidaData(FormataData($DataFim));
	
	if($Orgao == "" || $Orgao == NULL){
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript: document.CadRelatorioSARPSelecionar.Orgao.focus();\" class=\"titulo2\">Selecione um órgão</a>";
	}
	else{
		if(!empty($d3)){
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:void(0);\" class=\"titulo2\">$d3</a>";
		}
		elseif(!empty($d4)){
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:void(0);\" class=\"titulo2\">$d4</a>";
		}
		elseif(!(checkdate($d1[1], $d1[0], $d1[2])&&checkdate($d2[1], $d2[0], $d2[2]))){
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:void(0);\" class=\"titulo2\">Informe: data válida</a>";
		}
		elseif($dataTIni > $dataTFim){
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript: document.CadRelatorioSARPSelecionar.DataIni.focus();\" class=\"titulo2\">Informe: data final maior que data inicial</a>";
		}
		else{
			$Url = "CadRelatorioSARPPdf.php?DataIni=$DataIni&DataFim=$DataFim&Orgao=$Orgao";
			
			if (!in_array($Url,$_SESSION['GetUrl'])){
				$_SESSION['GetUrl'][] = $Url;
			}
				
			header("location: ".$Url);
			exit;
		}
				
	}
}
?>
<html>
<?php
# Carrega o layout padro #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script type="text/javascript">

function AbreJanela(url,largura,altura){
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=15,top=15,width='+largura+',height='+altura);
}

function CaracteresObjeto(text,campo){
	input = document.getElementById(campo);
	input.value = text.value.length;
}

function enviar(valor){
	document.CadRelatorioSARPSelecionar.Botao.value = valor;
	document.CadRelatorioSARPSelecionar.submit();
}

function remeter(){
	document.CadRelatorioSARPSelecionar.submit();
}

<?php MenuAcesso(); ?>
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadRelatorioSARPSelecionar.php" method="post" name="CadRelatorioSARPSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary=""  width="100%">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Compras > Relatórios > Por Tipo SARP
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2">
			<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
		</td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary=""  width="100%">
				<tr>
					<td class="textonormal">
						<table border="0" cellspacing="0" cellpadding="0" summary=""  width="100%">
							<tr>
								<td class="textonormal">
									<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
										<tr>
											<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="11">
												RELATÓRIO POR TIPO SARP
											</td>
										</tr>
										<tr>
											<td colspan="11">
												<table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td colspan="11">
															<table width="100%" class="textonormal" border="0" width="100%" summary="">
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" width="15%" height="20" align="left">Órgão</td>
																	<?php
																		$db 		= Conexao();
																		
																		$sqlperfil 	= " SELECT 	P.FPERFICORP
																						FROM	SFPC.TBPERFIL P
																								JOIN SFPC.TBUSUARIOPERFIL U ON P.CPERFICODI = U.CPERFICODI
																						WHERE	U.CUSUPOCODI = $usuario";
																		

																		
																		$perfil		= resultValorUnico(executarSQL($db, $sqlperfil));
																		//$db->disconnect();
																		
																		if($perfil == 'S'){
																	?>
																	<td class="textonormal">
																		<select name="Orgao" class="textonormal">
																			<option value="">Selecione um órgão...</option>
																			<option <?php if($Orgao=="TODOS"){echo "selected='selected'";}?> value="TODOS">Todos</option>
																			<?php
																				$db   = Conexao();
																				$sql  = "SELECT 	
																								ORG.CORGLICODI, ORG.EORGLIDESC
																						 FROM  
																								SFPC.TBORGAOLICITANTE ORG
																						 WHERE 
																								ORG.FORGLISITU = 'A'
																								--AND ORG.CORGLICODI IN (SELECT distinct(SOL.CORGLICODI) FROM SFPC.TBSOLICITACAOCOMPRA SOL )
																						 ORDER BY 
																								ORG.EORGLIDESC";
																
																				$res = $db->query($sql);
																				
																				if( PEAR::isError($res) ){
																					$CodErroEmail  = $res->getCode();
																					$DescErroEmail = $res->getMessage();
																					var_export($DescErroEmail);
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
																				}
																				else{
																					while( $Linha = $res->fetchRow() ){
																						if($Linha[0]==$Orgao){
																							echo "<option selected='selected' value=\"$Linha[0]\">$Linha[1]</option>\n";
																						}
																						else{
																							echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
																						}
																					}
																				}
																				$db->disconnect();
																			?>
																			
																		</select>
																	</td>
																	<?php }
																	else{
																		$db 			= Conexao();
																		
																		$consultaOrgao 	= "	 SELECT DISTINCT 
																									B.CORGLICODI, B.EORGLIDESC
																							 FROM 		
																							 		SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B 
																							 WHERE 	
																							 		A.CORGLICODI IS NOT NULL 
																									AND A.ACENPOANOE = ".date("Y")."
																									AND A.CORGLICODI = B.CORGLICODI  
																									AND A.FCENPOSITU <> 'I' 
																									AND A.CCENPOSEQU IN  (  SELECT USU.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO USU WHERE USU.CUSUPOCODI = ".$usuario.")
																							ORDER BY 1";
																		
																		$resConsulta	= $db->query($consultaOrgao);
																		
																		if( PEAR::isError($resConsulta) ){
																			$CodErroEmail  = $resConsulta->getCode();
																			$DescErroEmail = $resConsulta->getMessage();
																			var_export($DescErroEmail);
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($consultaOrgao)");
																		}
																		else{
																			$Linha = $resConsulta->fetchRow();
																			echo "<td class=\"textonormal\">\n";
																			echo "<input type=\"hidden\" name=\"Orgao\" value=\"$Linha[0]\">$Linha[1]\n";
																			echo "</td>\n";
																		}
																		
																		$db->disconnect();
																	}?>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" width="15%" height="20" align="left">Período</td>
																	<td class="textonormal">
																		<?php
																		$DataMes = DataMes();
																		if( $DataIni == "" ){ 
																			$DataIni = $DataMes[0]; 
																		}
																		if( $DataFim == "" ){ 
																			$DataFim = $DataMes[1]; 
																		}									
																		$URLIni = "../calendario.php?Formulario=CadRelatorioSARPSelecionar&Campo=DataIni";
																		$URLFim = "../calendario.php?Formulario=CadRelatorioSARPSelecionar&Campo=DataFim";
																		?>
																		<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
																		<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
																		&nbsp;A&nbsp;
																		<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
																		<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td colspan="11" align="right">
												<input type="submit" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar')">
												<input type="submit" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
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
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
</html>