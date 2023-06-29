<?php
#------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelMovimentacaoTipoMovimento.php
# Objetivo: Programa para impressão do relatório de movimentações de material
#           agrupadas por tipo de movimentação
#-----------------------------------
# Autor:    Álvaro Faria
# Data:     03/11/2005
# Alterado: Marcus Thiago
# Data:     04/01/2006
# Alterado: Álvaro Faria
# Data:     23/08/2006
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Alterado: Ariston Cordeiro
# Data:     22/10/2010 - Adicionando movimentação de entrada por cancelamento de requisição
#-----------------------------------
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelMovimentacaoTipoMovimento.php' );
AddMenuAcesso( '/estoques/RelMovimentacaoTipoMovimentoPdf.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                      = $_POST['Botao'];
		$Almoxarifado               = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado        = $_POST['CarregaAlmoxarifado'];
		$Opcao                      = $_POST['Opcao'];
		$DescMaterial               = strtoupper2(trim($_POST['DescMaterial']));
		$DataIni                    = $_POST['DataIni'];
		if( $DataIni != "" ){ $DataIni = FormataData($DataIni); }
		$DataFim                    = $_POST['DataFim'];
		if( $DataFim != "" ){ $DataFim = FormataData($DataFim); }
		$TipoMovimentacao           = $_POST['TipoMovimentacao'];
		$Movimentacao               = $_POST['Movimentacao'];
		$Ordem                      = $_POST['Ordem'];
		$CheckTodosTipoMovimentacao = $_POST['CheckTodosTipoMovimentacao'];
		$CheckTodosMovimentacao     = $_POST['CheckTodosMovimentacao'];
}else{
		$Mensagem                   = urldecode($_GET['Mensagem']);
		$Mens                       = $_GET['Mens'];
		$Tipo                       = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if( $Botao == "Limpar" ){
		header("location: RelMovimentacaoTipoMovimento.php");
		exit;
}elseif( $Botao == "Validar" or $Botao == "Imprimir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";

		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}elseif($Almoxarifado == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelMovimentacaoTipoMovimento.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}

		if( (!$TipoMovimentacao) && (!$CheckTodosTipoMovimentacao) && ($Botao == "Imprimir") ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelMovimentacaoTipoMovimento.TipoMovimentacao.focus();\" class=\"titulo2\">Tipo de Movimentação</a>";
		}

		if( ($TipoMovimentacao) && (!$Movimentacao) && (!$CheckTodosMovimentacao) && ($Botao == "Imprimir") ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelMovimentacaoTipoMovimento.Movimentacao.focus();\" class=\"titulo2\">Movimentação</a>";
		}

		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"RelMovimentacaoTipoMovimento");
		if($MensErro != ""){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }

		if($Mens == 0 and $Botao == "Imprimir"){
				if( ( $TipoMovimentacao) and ( $Movimentacao) ) {
						$Url = "RelMovimentacaoTipoMovimentoPdf.php?Almoxarifado=$Almoxarifado&TipoMovimentacao=$TipoMovimentacao&Movimentacao=$Movimentacao&Ordem=$Ordem&DataIni=$DataIni&DataFim=$DataFim&".mktime();
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit();
				}
				if( ( $TipoMovimentacao) and (!$Movimentacao) ){
						$Url = "RelMovimentacaoTipoMovimentoPdf.php?Almoxarifado=$Almoxarifado&TipoMovimentacao=$TipoMovimentacao&Ordem=$Ordem&DataIni=$DataIni&DataFim=$DataFim&".mktime();
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit();
				}
				if( (!$TipoMovimentacao) and (!$Movimentacao) ){
						$Url = "RelMovimentacaoTipoMovimentoPdf.php?Almoxarifado=$Almoxarifado&Ordem=$Ordem&DataIni=$DataIni&DataFim=$DataFim&".mktime();
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit();
				}
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
<?php MenuAcesso(); ?>
function checktodosTipoMovimentacao(){
	document.RelMovimentacaoTipoMovimento.TipoMovimentacao.value = '';
	document.RelMovimentacaoTipoMovimento.Movimentacao.value = '';
	document.RelMovimentacaoTipoMovimento.Botao.value = 'Validar';
	document.RelMovimentacaoTipoMovimento.submit();
}
function checktodosMovimentacao(){
	document.RelMovimentacaoTipoMovimento.Movimentacao.value = '';
	document.RelMovimentacaoTipoMovimento.Botao.value = 'Validar';
	document.RelMovimentacaoTipoMovimento.submit();
}
function unchecktodosTipoMovimentacao(){
	document.RelMovimentacaoTipoMovimento.CheckTodosTipoMovimentacao.checked = false;
	document.RelMovimentacaoTipoMovimento.Movimentacao.value = '';
	document.RelMovimentacaoTipoMovimento.Botao.value = 'Validar';
	document.RelMovimentacaoTipoMovimento.submit();
}
function unchecktodosMovimentacao(){
	document.RelMovimentacaoTipoMovimento.CheckTodosMovimentacao.checked = false;
	document.RelMovimentacaoTipoMovimento.Botao.value = 'Validar';
	document.RelMovimentacaoTipoMovimento.submit();
}
function enviar(valor){
	document.RelMovimentacaoTipoMovimento.Botao.value=valor;
	document.RelMovimentacaoTipoMovimento.submit();
}
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelMovimentacaoTipoMovimento.php" method="post" name="RelMovimentacaoTipoMovimento">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="3">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Relatórios > Movimentação Por Tipo
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="3">
			<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
		</td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal" colspan="3">
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal" >
						<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle"  class="titulo3" colspan="3">
									RELATÓRIO DE MOVIMENTAÇÃO POR TIPO DE MOVIMENTO
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="3">
									<p align="justify">
										Para imprimir os dados do Relatório de Movimentação por Tipo, selecione o Almoxarifado.
										Para Alterar o Período, digite as datas desejadas e tecle 'TAB' ou Acione o Calendário.
										Selecione o tipo de movimento através da pesquisa. Especifique a Ordem das Informações.
										Para criar o Relatório, use o botão Imprimir.
										Para limpar os campos, clique no botão "Limpar".<br><br>
										Notas Fiscais canceladas não são exibidas neste Relatório.<BR><BR>
										Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
									</p>
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="3">
									<table class="textonormal" border="0" align="left" summary="" width="100%">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
											<td class="textonormal">
												<?php
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db = Conexao();
												if($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == 'S'){
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
														if ($Almoxarifado) {
																$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
												}else{
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
														$sql   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
														if($Almoxarifado){
																$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
														$sql .= "   AND B.CORGLICODI = ";
														$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
														$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
														$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R') ";

														# restringir almoxarifado quando requisitante
														$sql .= "            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END";

														$sql .= "       ) ";
												}
												$sql .= " ORDER BY A.EALMPODESC ";
												$res  = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $res->numRows();
														if( $Rows == 1 ){
																$Linha = $res->fetchRow();
																$Almoxarifado = $Linha[0];
																echo "$Linha[1]<br>";
																echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																echo $DescAlmoxarifado;
														}elseif( $Rows > 1 ){
																echo "<select name=\"Almoxarifado\" class=\"textonormal\">\n";
																echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																for($i=0; $i< $Rows; $i++){
																		$Linha = $res->fetchRow();
																		$DescAlmoxarifado = $Linha[1];
																		if( $Linha[0] == $Almoxarifado ){
																				echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
																		}else{
																				echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
																		}
																}
																echo "</select>\n";
																$CarregaAlmoxarifado = "";
														}else{
																echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
																echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
														}
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="27%" height="20">Período</td>
											<td class="textonormal">
												<?php
												$DataMes = DataMes();
												if($DataIni == ""){ $DataIni = $DataMes[0]; }
												if($DataFim == ""){ $DataFim = $DataMes[1]; }
												$URLIni = "../calendario.php?Formulario=RelMovimentacaoTipoMovimento&Campo=DataIni";
												$URLFim = "../calendario.php?Formulario=RelMovimentacaoTipoMovimento&Campo=DataFim";
												?>
												<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
												&nbsp;a&nbsp;
												<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Movimentação*</td>
											<td class="textonormal">
												<select name="TipoMovimentacao" class="textonormal" onChange="javascript:unchecktodosTipoMovimentacao();">
													<?php
													if($TipoMovimentacao == ""){
															echo "<option value=\"\" selected>Selecione o Tipo de Movimentação...</option>";
															echo "<option value=\"E\" >ENTRADA</option>";
															echo "<option value=\"S\" >SAÍDA</option>";
													}elseif($TipoMovimentacao == "E"){
															echo "<option value=\"\" >Selecione o Tipo de Movimentação...</option>";
															echo "<option value=\"E\" selected>ENTRADA</option>";
															echo "<option value=\"S\" >SAÍDA</option>";
													}elseif( $TipoMovimentacao == "S" ){
															echo "<option value=\"\" >Selecione o Tipo de Movimentação...</option>";
															echo "<option value=\"E\" >ENTRADA</option>";
															echo "<option value=\"S\" selected>SAÍDA</option>";
													}
													?>
												</select>
												<?php
												if($CheckTodosTipoMovimentacao){
														echo "<input type=\"checkbox\" checked name=\"CheckTodosTipoMovimentacao\" onClick=\"javascript:checktodosTipoMovimentacao();\" value=\"T\">";
												}else{
														echo "<input type=\"checkbox\" name=\"CheckTodosTipoMovimentacao\" onClick=\"javascript:checktodosTipoMovimentacao();\" value=\"T\">";
												}
												echo "Todos";
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Movimentação*</td>
											<td class="textonormal">
											<?php
											$MovimentacaoIgnorada = array (0,1,5,21,22);

											# Resgata os Tipos Movimentações #
											$db     = Conexao();
											$sql    = "SELECT  DISTINCT CTIPMVCODI, ETIPMVDESC FROM SFPC.TBTIPOMOVIMENTACAO ";
											$sql   .= " WHERE FTIPMVTIPO = '$TipoMovimentacao' ";
											$result = $db->query($sql);
											if( db::isError($result) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											}else{
													$Rows = $result->numRows();
													if($Rows == 0){
															if($CheckTodosTipoMovimentacao || (!$TipoMovimentacao) ){
																	echo "<select disabled name=\"Movimentacao\" class=\"textonormal\" onChange=\"javascript:unchecktodosMovimentacao();\">\n";
																	echo "	<option value=\"\">Selecione uma Movimentacao...</option>\n";
																	echo "</select>";
															}else{
																	echo "<select name=\"Movimentacao\" class=\"textonormal\" onChange=\"javascript:unchecktodosMovimentacao();\">\n";
																	echo "	<option value=\"\">Selecione uma Movimentacao...</option>\n";
																	echo "</select>";
															}
													}else{
															echo "<select name=\"Movimentacao\" class=\"textonormal\" onChange=\"javascript:unchecktodosMovimentacao();\">\n";
															echo "	<option value=\"\">Selecione uma Movimentacao...</option>\n";
															for($i=0; $i< $Rows; $i++){
																	$Linha = $result->fetchRow();
																	if(!array_search($Linha[0],$MovimentacaoIgnorada)){
																			if($Movimentacao == $Linha[0]){
																					echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>";
																			}else{
																					echo "<option value=\"$Linha[0]\">$Linha[1]</option>";
																			}
																	}
															}
															echo "</select>";
													}
											}
											$db->disconnect();
											if( $CheckTodosTipoMovimentacao || (!$TipoMovimentacao) ){
													echo "<input disabled type=\"checkbox\" name=\"CheckTodosMovimentacao\" onClick=\"javascript:checktodosMovimentacao();\" value=\"T\">";
											}else{
													if($CheckTodosMovimentacao){
															echo "<input type=\"checkbox\" checked name=\"CheckTodosMovimentacao\" onClick=\"javascript:checktodosMovimentacao();\" value=\"T\">";
													}else{
															echo "<input type=\"checkbox\" name=\"CheckTodosMovimentacao\" onClick=\"javascript:checktodosMovimentacao();\" value=\"T\">";
													}
											}
											echo "Todas";
											?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Ordem*</td>
											<td class="textonormal">
												<select name="Ordem" class="textonormal">
													<option value="C">Centro de Custo</option>
													<option value="D" selected>Data da Movimentação</option>
												</select>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="3" align="right">
									<input type="button" name="Imprimir" value="Imprimir" class="botao" onclick="javascript:enviar('Imprimir');">
									<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
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
