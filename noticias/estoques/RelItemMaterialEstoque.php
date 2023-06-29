<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelItemMaterialEstoque.php
# Objetivo: Programa de Impressão da Produtos em Estoque
# Autor:    Rossana Lira
# Data:     12/08/2005
# Alterado: Marcus Thiago
# Data:     04/01/2006
# Alterado: Álvaro Faria
# Data:     17/03/2006
# Alterado: Álvaro Faria
# Data:     03/10/2006 - Identação / Padronização do cabeçalho
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo 
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Alterado: Ariston Cordeiro
# Data:     03/10/2006 - Identação / Padronização do cabeçalho
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelItemMaterialEstoquePdf.php' );
AddMenuAcesso( '/estoques/RelItemMaterialEstoqueDataPdf.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        = $_POST['Botao'];
		$Almoxarifado = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Ordem        = $_POST['Ordem'];
		$ExibirLoc    = $_POST['ExibirLoc'];
		$ExibirZer    = $_POST['ExibirZer'];
		$Data         = $_POST['Data'];
		$GrupoClasses = $_POST['GrupoClasses'];
		$GrupoClasseTodos = $_POST['GrupoClasseTodos'];
}else{
		$Mensagem     = urldecode($_GET['Mensagem']);
		$Mens         = $_GET['Mens'];
		$Tipo			    = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Proibir selecionar grupos e classes antes de definir almoxarifado
if(($Almoxarifado == "" or is_null($Almoxarifado)) and $Ordem == "1"){
	$Ordem = "2";
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelItemMaterialEstoque.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
}

if($Botao == "Limpar"){
		header("location: RelItemMaterialEstoque.php");
		exit;
}elseif( $Botao == "Imprimir" ){
		$DataAtual = date("Y-m-d");
		$Mens      = 0;
		$Mensagem  = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}elseif($Almoxarifado == "") {
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelItemMaterialEstoque.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		$DataValida = ValidaData($Data);
		if($DataValida != ""){
				if($Mens == 1 ){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.RelItemMaterialEstoque.Data.focus();\" class=\"titulo2\">Data Válida</a>";
		}elseif(DataInvertida($Data) > $DataAtual){
				if($Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelItemMaterialEstoque.Data.focus();\" class=\"titulo2\">Data menor ou igual a atual</a>";
		}elseif(DataInvertida($Data) < '2006-03-24' ) {
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelItemMaterialEstoque.Data.focus();\" class=\"titulo2\">Data maior que 24/03/2006</a>";
		}
		if($Ordem == 0){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelItemMaterialEstoque.Ordem.focus();\" class=\"titulo2\">Ordem</a>";
		}
		//----------------------------------
		if( ($Ordem == "1") and (is_null($GrupoClasses) or count($GrupoClasses)==0) and (!$GrupoClasseTodos) ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelItemMaterialEstoque.GrupoClasseTodos.focus();\" class=\"titulo2\">Selecione grupo/classe ou opção Todos</a>";
		}
		if($Mens == 0){
				$StrGrupoClasses="";
				if(!is_null($GrupoClasses) and !$GrupoClasseTodos){
					foreach($GrupoClasses as $Grupo){
						$StrGrupoClasses .= $Grupo."!";
					}
					$StrGrupoClasses = substr($StrGrupoClasses,0,count($StrGrupoClasses)-2); //remover último caractere '#'
				}
				# Se a data especificada for igual a atual, direciona para RelItemMaterialEstoquePdf, se for diferente, direciona para RelItemMaterialEstoqueDataPdf
				if(DataInvertida($Data) == $DataAtual ) {
						$Url = "RelItemMaterialEstoquePdf.php?Almoxarifado=$Almoxarifado&Ordem=$Ordem&ExibirLoc=$ExibirLoc&ExibirZer=$ExibirZer&".mktime()."&GrupoClasses=".$StrGrupoClasses;
				}else{
						$Url = "RelItemMaterialEstoqueDataPdf.php?Almoxarifado=$Almoxarifado&Ordem=$Ordem&ExibirLoc=$ExibirLoc&ExibirZer=$ExibirZer&Data=$Data&".mktime()."&GrupoClasses=".$StrGrupoClasses;
				}
				if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
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
function enviar(valor){
	document.RelItemMaterialEstoque.Botao.value=valor;
	document.RelItemMaterialEstoque.submit();
}

function mudarGrupoClasse(){
	document.RelItemMaterialEstoque.GrupoClasseTodos.checked=false;
}

function selecionarGrupoClasseTodos(){
		gc = document.getElementById("GrupoClasses");
	for(itr=0;itr<gc.length;itr++){
		gc.options[itr].selected=false;
	}
}


<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelItemMaterialEstoque.php" method="post" name="RelItemMaterialEstoque">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Relatórios > Itens em Estoque
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
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
									RELATÓRIO DE ITENS DE MATERIAIS EM ESTOQUE
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para imprimir os materiais em estoque, selecione o almoxarifado, a data, a ordem, as opções de exibição e clique no botão "Imprimir".
										Para limpar os campos, clique no botão "Limpar". O relatório de uma data diferente da atual pode ser um pouco mais demorado.<br><br>
										Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
									</p>
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<table class="textonormal" border="0" align="left" summary="" width="100%">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
											<td class="textonormal">
												<?php
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db  = Conexao();
												if($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == 'S'){
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
														if ($Almoxarifado) {
																$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
												} else {
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
														$sql   .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
														if ($Almoxarifado) {
																$sql   .= " AND A.CALMPOCODI = $Almoxarifado AND A.FALMPOSITU = 'A'";
														}
														$sql .= "   AND B.CORGLICODI = ";
														$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
														$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
														$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND CEN.FCENPOSITU <> 'I' AND USU.FUSUCCTIPO IN ('T','R')";
														
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
														}else{
																echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
																echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
														}
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<?php
										$URL = "../calendario.php?Formulario=RelItemMaterialEstoque&Campo=Data";
										if( $Data == "" ){ $Data = date("d/m/Y"); }
										?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%">Data*</td>
											<td class="textonormal">
												<input type="text" name="Data" size="10" maxlength="10" value="<?php echo $Data;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%">Ordem*</td>
											<td class="textonormal">
												<select name="Ordem" class="textonormal" onChange="javascript:enviar('');">
													<option value=""  <?php if( $Ordem == ""  ){ echo "selected"; }?> >Selecione uma Ordem...</option>
													<option value="1" <?php if( $Ordem == "1" ){ echo "selected"; }?> >FAMÍLIA</option>
													<option value="2" <?php if( $Ordem == "2" ){ echo "selected"; }?> >MATERIAL</option>
												</select>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Exibir Localização*</td>
											<td class="textonormal">
												<input type="radio" name="ExibirLoc" value="N" <?php if( $ExibirLoc == "N" or $ExibirLoc == ""  ){ echo "checked"; }?>> Não
												<input type="radio" name="ExibirLoc" value="S" <?php if( $ExibirLoc == "S" ){ echo "checked"; }?>> Sim
											</td>
										</tr>
										<?php
										if( $Ordem == "1" and !is_null($Almoxarifado) and $Almoxarifado != "" ){
										?>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Grupos / Classes</td>
												<td class="textonormal">
													Selecione as Classes de materiais a serem visualizados:<br/>
													<select name="GrupoClasses[]" id="GrupoClasses" class="textonormal" multiple size="8" onChange='javascript:mudarGrupoClasse(".$GrupoCod.",".$ClasseCod.");'>
														<?
															$db     = Conexao();
															# Mostra as Unidades Orçamentárias #
															$sql    = "
																SELECT DISTINCT
																	GRU.CGRUMSCODI, CLA.CCLAMSCODI, GRU.EGRUMSDESC, CLA.ECLAMSDESC
																FROM 
																	SFPC.TBMATERIALPORTAL MAT,
																	SFPC.TBARMAZENAMENTOMATERIAL ARM, 
																	SFPC.TBLOCALIZACAOMATERIAL LOC, 
																	SFPC.TBSUBCLASSEMATERIAL SUB, 
																	SFPC.TBGRUPOMATERIALSERVICO GRU,
																	SFPC.TBCLASSEMATERIALSERVICO CLA 
																WHERE 
																	LOC.CALMPOCODI = ".$Almoxarifado." AND 
																	LOC.CLOCMACODI = ARM.CLOCMACODI AND 
																	MAT.CMATEPSEQU = ARM.CMATEPSEQU AND 
																	MAT.CSUBCLSEQU = SUB.CSUBCLSEQU AND 
																	SUB.CGRUMSCODI = GRU.CGRUMSCODI AND 
																	GRU.CGRUMSCODI = CLA.CGRUMSCODI AND 
																	CLA.CCLAMSCODI = SUB.CCLAMSCODI AND 
																	CLA.CGRUMSCODI = SUB.CGRUMSCODI
															";
															if($ExibirZer=="N"){
																$sql    .= "AND ARM.AARMATQTDE != 0 -- classes de materiais em que o material tem 0 no estoque serão ignorados no relatório";
															}
															$sql    .= "
																ORDER BY
																	GRU.EGRUMSDESC, CLA.ECLAMSDESC
															";
	
															$result = $db->query($sql);
															if( db::isError($result) ){
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	while( $Linha = $result->fetchRow() ){
																			$GrupoCod    = $Linha[0];
																			$ClasseCod   = $Linha[1];
																			$GrupoDesc 	= $Linha[2];
																			$ClasseDesc = $Linha[3];
																			echo "<option value='".$GrupoCod."_".$ClasseCod."' >".$GrupoDesc." / ".$ClasseDesc."</option>\n";
																	}
															}
															$db->disconnect();
														?>
													</select><br/>
													<input type="checkbox" name="GrupoClasseTodos" checked onclick="selecionarGrupoClasseTodos()"/>Todos

												</td>
											</tr>											
										<?php
										}
										?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Exibir Itens Zerados*</td>
											<td class="textonormal">
												<input type="radio" name="ExibirZer" value="N" <?php if( $ExibirZer == "N" or $ExibirZer == ""  ){ echo "checked"; }?> onclick="javascript:enviar('');"> Não
												<input type="radio" name="ExibirZer" value="S" <?php if( $ExibirZer == "S" ){ echo "checked"; }?> onclick="javascript:enviar('');"> Sim
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td align="right">
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
