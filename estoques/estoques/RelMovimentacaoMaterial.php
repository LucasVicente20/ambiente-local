<?php
#------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelMovimentacaoMaterial.php
# Objetivo: Programa de Impressão do Relatório de Movimentação de Material.
# Autor:    Rossana Lira
# Data:     23/08/2005
# Alterado: Marcus Thiago
# Data:     04/01/2006
# Alterado: Álvaro Faria
# Data:     18/08/2006 - Na pesquisa, só exibir materiais que tiveram movimentação no período
# Alterado: Álvaro Faria
# Data:     23/08/2006 - Simplificação do select principal
# Alterado: Álvaro Faria
# Data:     29/08/2006 - Opção de pesquisa de material com descrição "iniciada por"
# Alterado: Álvaro Faria
# Data:     30/08/2006
# Alterado: Wagner Barros
# Data:     29/09/2006 - Exibir o código reduzido do material ao lado da descrição"
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Alterado: Rodrigo Melo
# Data:     23/04/2009 - Resolvendo CR770 - Colocando filtro para o tipo de movimentação (entrada, saída ou todos)
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelMovimentacaoMaterial.php' );
AddMenuAcesso( '/estoques/RelMovimentacaoMaterialPdf.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
		$Almoxarifado   = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Localizacao    = $_POST['Localizacao'];
		$CarregaLocalizacao = $_POST['CarregaLocalizacao'];
		$Opcao          = $_POST['Opcao'];
		$DescMaterial   = strtoupper2(trim($_POST['DescMaterial']));
		$DataIni        = $_POST['DataIni'];
		if($DataIni != ""){ $DataIni = FormataData($DataIni); }
		$DataFim        = $_POST['DataFim'];
		if($DataFim != ""){ $DataFim = FormataData($DataFim); }
		$CodigoReduzido = $_POST['CodigoReduzido'];
		$TipoMovimentacao           = $_POST['TipoMovimentacao'];
		$CheckTodosTipoMovimentacao = $_POST['CheckTodosTipoMovimentacao'];
}else{
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if($Botao == "Limpar"){
		header("location: RelMovimentacaoMaterial.php");
		exit;
}elseif($Botao == "Validar" or $Botao == "Imprimir"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}elseif($Almoxarifado == "") {
				if($Mens == 1) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelMovimentacaoMaterial.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		}elseif($Localizacao == "") {
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelMovimentacaoMaterial.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"RelMovimentacaoMaterial");
		if($MensErro != ""){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }


        //TESTE
		if( (!$TipoMovimentacao) && (!$CheckTodosTipoMovimentacao) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelMovimentacaoMaterial.TipoMovimentacao.focus();\" class=\"titulo2\">Tipo de Movimentação</a>";
		}
        //FIM TESTE


		if($DescMaterial == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelMovimentacaoMaterial.DescMaterial.focus();\" class=\"titulo2\">Material</a>";
		}else{
				if( $DescMaterial != "" and $Opcao == 0 and ( ! SoNumeros($DescMaterial) ) ){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens = 1;
						$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.RelMovimentacaoMaterial.DescMaterial.focus();\" class=\"titulo2\">Código Reduzido do Material Válido</a>";
				}elseif($DescMaterial != "" and ($Opcao == 1 or $Opcao == 2) and strlen($DescMaterial)< 2){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens = 1;
						$Tipo = 2;
						$Mensagem .= "<a href=\"javascript:document.RelMovimentacaoMaterial.DescMaterial.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
				}elseif($DescMaterial != ""){
						# Datas para consulta no banco de dados #
						$DataInibd = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
						$DataFimbd = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);
						$sql   = "SELECT DISTINCT MAT.CMATEPSEQU, MAT.EMATEPDESC, UND.EUNIDMSIGL ";
						$from  = "  FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBUNIDADEDEMEDIDA UND, ";
						$from .= "       SFPC.TBMOVIMENTACAOMATERIAL MOV ";
						# Se foi digitado algo na caixa de texto do material em pesquisa direta #
						if($DescMaterial != ""){
								if($Opcao == 0){
										if( SoNumeros($DescMaterial )) {
												$where = " WHERE MAT.CMATEPSEQU = $DescMaterial ";
										}
								}elseif($Opcao == 1){
										$where .= " WHERE ( ";
										$where .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($DescMaterial))."%' OR ";
										$where .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper2(RetiraAcentos($DescMaterial))."%' ";
										$where .= "     )";
								}else{
										$where .= " WHERE TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($DescMaterial))."%' ";
								}
								$where .= " AND MAT.CUNIDMCODI = UND.CUNIDMCODI ";
								$where .= " AND MOV.CMATEPSEQU = MAT.CMATEPSEQU ";
								$where .= " AND MOV.CALMPOCODI = $Almoxarifado ";
								$where .= " AND MOV.DMOVMAMOVI >= '$DataInibd' AND MOV.DMOVMAMOVI <= '$DataFimbd' ";
								$order .= " ORDER BY MAT.EMATEPDESC ";
								# Gera o SQL com a concatenação das variaveis $sql,$from,$where #
								$sqlgeral = $sql.$from.$where.$order;
						}
				}
		}
		if($Mens == 0 and $Botao == "Imprimir"){
				$Url = "RelMovimentacaoMaterialPdf.php?Almoxarifado=$Almoxarifado&TipoMovimentacao=$TipoMovimentacao&Localizacao=$Localizacao&Material=$CodigoReduzido&DataIni=$DataIni&DataFim=$DataFim&".mktime();
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
<?php MenuAcesso(); ?>


function checktodosTipoMovimentacao(){
	document.RelMovimentacaoMaterial.TipoMovimentacao.value = '';
}

function unchecktodosTipoMovimentacao(){
	document.RelMovimentacaoMaterial.CheckTodosTipoMovimentacao.checked = false;
}

function enviar(valor){
	document.RelMovimentacaoMaterial.Botao.value=valor;
	document.RelMovimentacaoMaterial.submit();
}
function remeter(valor){
	document.RelMovimentacaoMaterial.Botao.value='Imprimir';
	document.RelMovimentacaoMaterial.CodigoReduzido.value=valor;
	document.RelMovimentacaoMaterial.submit();
}
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelMovimentacaoMaterial.php" method="post" name="RelMovimentacaoMaterial">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="3">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Relatórios > Movimentação
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="3">
			<?php if($Mens == 1){ ExibeMens($Mensagem,$Tipo,1); } ?>
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
									RELATÓRIO DE MOVIMENTAÇÃO DE MATERIAL
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="3">
									<p align="justify">
										Para imprimir os dados do Relatório de Movimentação de Material, selecione o material desejado através da pesquisa.
										Para Alterar o Período, digite as datas desejadas e tecle 'TAB' ou Acione o Calendário.
										Para limpar os campos, clique no botão "Limpar".<br><br>
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
												$db  = Conexao();
												if($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == 'S'){
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
														if($Almoxarifado){
																$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
												}else{
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
														$sql   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
														if ($Almoxarifado) {
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
														if($Rows == 1){
																$Linha = $res->fetchRow();
																$Almoxarifado = $Linha[0];
																echo "$Linha[1]<br>";
																echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																echo $DescAlmoxarifado;
														}elseif( $Rows > 1 ){
																echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"submit();\">\n";
																echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																for( $i=0;$i< $Rows; $i++ ){
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
																if(!$Almoxarifado){
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																}
														}elseif(!$Almoxarifado){
																echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
																echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
																echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
														}
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização*</td>
											<td class="textonormal">
												<?php
												$db = Conexao();
												if( $Localizacao != "" ){
														# Mostra a Descrição de Acordo com o Almoxarifado #
														$sql    = "SELECT A.FLOCMAEQUI, A.ALOCMANEQU, A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B";
														$sql   .= " WHERE A.CLOCMACODI = $Localizacao AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$res  = $db->query($sql);
														if( db::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Linha = $res->fetchRow();
																if($Linha[0] == "E"){
																		$Equipamento = "ESTANTE";
																}if($Linha[0] == "A"){
																		$Equipamento = "ARMÁRIO";
																}if($Linha[0] == "P"){
																		$Equipamento = "PALETE";
																}
																$DescArea = $Linha[4];
																echo "ÁREA: $DescArea - $Equipamento - $Linha[1]: ESCANINHO $Linha[2]$Linha[3]";
																echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
														}
												}elseif($Almoxarifado) {
														# Mostra as Localizações de acordo com o Almoxarifado #
														$sql    = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
														$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql   .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
														$res  = $db->query($sql);
														if( db::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Rows = $res->numRows();
																if( $Rows == 0 ){
																		echo "NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO";
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																}else{
																		if( $Rows == 1 ){
																				$Linha = $res->fetchRow();
																				if($Linha[1] == "E"){
																						$Equipamento = "ESTANTE";
																				}if($Linha[1] == "A"){
																						$Equipamento = "ARMÁRIO";
																				}if($Linha[1] == "P"){
																						$Equipamento = "PALETE";
																				}
																				echo "ÁREA: $Linha[5] - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																				$Localizacao = $Linha[0];
																				echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																		}else{
																				if($Rows == 1){
																						$Linha = $res->fetchRow();
																						if($Linha[1] == "E"){
																								$Equipamento = "ESTANTE";
																						}if($Linha[1] == "A"){
																								$Equipamento = "ARMÁRIO";
																						}if($Linha[1] == "P"){
																								$Equipamento = "PALETE";
																						}
																						echo "ÁREA: $Linha[5] - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																						$Localizacao = $Linha[0];
																						echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																				}else{
																						echo "<select name=\"Localizacao\" class=\"textonormal\" onChange=\"submit();\">\n";
																						echo "	<option value=\"\">Selecione uma Localização...</option>\n";
																						$EquipamentoAntes = "";
																						$DescAreaAntes    = "";
																						for($i=0;$i< $Rows; $i++ ){
																								$Linha = $res->fetchRow();
																								$CodEquipamento = $Linha[2];
																								if($Linha[1] == "E"){
																										$Equipamento = "ESTANTE";
																								}if($Linha[1] == "A"){
																										$Equipamento = "ARMÁRIO";
																								}if($Linha[1] == "P"){
																										$Equipamento = "PALETE";
																								}
																								$NumeroEquip = $Linha[2];
																								$Prateleira  = $Linha[3];
																								$Coluna      = $Linha[4];
																								$DescArea    = $Linha[5];
																								if( $DescAreaAntes != $DescArea ){
																										echo"<option value=\"\">$DescArea</option>\n";
																										$Edentecao = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																								}
																								if( $CodEquipamentoAntes != $CodEquipamento or $EquipamentoAntes != $Equipamento ){
																										echo"<option value=\"\">$Edentecao $Equipamento - $NumeroEquip</option>\n";
																								}
																								if( $Localizacao == $Linha[0] ){
																										echo"<option value=\"$Linha[0]\" selected>$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
																								}else{
																										echo"<option value=\"$Linha[0]\">$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
																								}
																								$DescAreaAntes       = $DescArea;
																								$CodEquipamentoAntes = $CodEquipamento;
																								$EquipamentoAntes    = $Equipamento;
																						}
																						echo "</select>\n";
																						$CarregaLocalizacao = "";
																				}
																		}
																}
														}
												}
												$db->disconnect();
												?>
											</td>
										</tr>

										<!-- TESTE -->
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
										<!--FIM TESTE-->


										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período</td>
											<td class="textonormal">
												<?php
												$DataMes = DataMes();
												if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
												if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
												$URLIni = "../calendario.php?Formulario=RelMovimentacaoMaterial&Campo=DataIni";
												$URLFim = "../calendario.php?Formulario=RelMovimentacaoMaterial&Campo=DataFim";
												?>
												<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
												&nbsp;a&nbsp;
												<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">Material</td>
											<td class="textonormal" colspan="3">
												<select name="Opcao" class="textonormal">
													<option value="0" <?php if( $Opcao == 0 or $Opcao == "" ){ echo "selected"; }?>>Código Reduzido</option>
													<option value="1" <?php if( $Opcao == 1 ){ echo "selected"; }?>>Descrição contendo</option>
													<option value="2" <?php if( $Opcao == 2 ){ echo "selected"; }?>>Descrição iniciada por</option>
												</select>
												<input type="text" name="DescMaterial" size="10" maxlength="10" class="textonormal" value="<?php echo $DescMaterial; ?>">
												<a href="javascript:enviar('Validar');"><img src="../midia/lupa.gif" border="0"></a>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="3" align="right">
									<input type="hidden" name="CodigoReduzido" value="<?php echo $CodigoReduzido?>">
									<input type="hidden" name="Critica" value="1">
									<input type="hidden" name="Botao" value="">
									<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
								</td>
							</tr>
							<?php
							if($DescMaterial != ""){
									if($Opcao == 0){
											if( !SoNumeros($DescMaterial) ){ $sqlgeral = ""; }
									}
							}
							if( ($sqlgeral != "") && ($Mens == 0) ){
									if($DescMaterial != ""){
											$db     = Conexao();
											$res    = $db->query($sqlgeral);
											$qtdres = $res->numRows();
											if( db::isError($res) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											}else{
													echo "<tr>\n";
													echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"3\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
													echo "</tr>\n";
													if($qtdres > 0){
															echo "<tr>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"80%\">DESCRIÇÃO DO MATERIAL</td>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
															echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">UNIDADE</td>\n";
															echo "</tr>\n";
															while( $row = $res->fetchRow() ){
																	$CodigoReduzido    = $row[0];
																	$MaterialDescricao = $row[1];
																	$UndMedidaSigla    = $row[2];
																	echo "<tr>\n";
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"80%\">\n";
																	echo "	<a href=\"javascript:remeter($CodigoReduzido)\"> <input type=\"hidden\" name=\"Material\" value=\"$row[0]\"> <font color=\"#000000\">$MaterialDescricao</font></a>";
																	echo "	</td>\n";
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
																	echo "		$CodigoReduzido";
																	echo "	</td>\n";
																	echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"10%\">\n";
																	echo "		$UndMedidaSigla";
																	echo "	</td>\n";
																	echo "</tr>\n";
															}
													}else{
															echo "<tr>\n";
															echo "	<td class=\"textonormal\" colspan=\"3\" >\n";
															echo "		Pesquisa sem Ocorrências.\n";
															echo "	</td>\n";
															echo "</tr>\n";
													}
											}
											$db->disconnect();
									}
							}
							?>
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
