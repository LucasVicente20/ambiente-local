<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialTRPHistorico.php
# Autor:    Igor Duarte
# Data:     10/09/2012
# Objetivo: Programa de exibição do histórico de preços de uma TRP
# OBS.:     Tabulação 2 espaços
#-----------------------------------------------------------------------------
header("Content-Type: text/html; charset=UTF-8",true);

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadMaterialTRPHistorico.php' );
AddMenuAcesso( '/materiais/CadMaterialTRPHistoricoDetalhe.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$ProgramaOrigem            = $_POST['ProgramaOrigem'];
		$Botao                     = $_POST['Botao'];
		$Almoxarifado              = $_POST['Almoxarifado'];
		$TipoPesquisa              = 1;
		$TipoMaterial              = $_POST['TipoMaterial'];
		$TipoGrupo                 = $_POST['TipoGrupo'];
		$Grupo                     = $_POST['Grupo'];
		$Classe                    = $_POST['Classe'];
		$Subclasse                 = $_POST['Subclasse'];
		$SubclasseDescricaoFamilia = strtoupper2(trim($_POST['$SubclasseDescricaoFamilia']));
		$ChkSubclasse              = $_POST['chkSubclasse'];

		# Pesquisa direta #
		$OpcaoPesquisaMaterial     = $_POST['OpcaoPesquisaMaterial'];
		$OpcaoPesquisaSubClasse    = $_POST['OpcaoPesquisaSubClasse'];

		$OpcaoPesquisaServico      = $_POST['OpcaoPesquisaServico'];

		$SubclasseDescricaoDireta  = strtoupper2(trim($_POST['SubclasseDescricaoDireta']));
		$MaterialDescricaoDireta   = strtoupper2(trim($_POST['MaterialDescricaoDireta']));

		$ServicoDescricaoDireta    = strtoupper2(trim($_POST['ServicoDescricaoDireta']));

		$PesqApenas                = $_POST['PesqApenas']; // E - Para disponibilizar apenas Itens em Estoque, C - Apenas para Cadastro de Materiais, Null - Para os dois
		$Zerados                   = $_POST['Zerados'];    // N - Apenas Itens em Estoque não zerados, Null - Todos os Itens
		$sqlpost                   = str_replace('\\','',$_POST['sqlgeral']); // Criado para resolver demora para cadastrar itens do cadastro de materiais. No "Insere" era executado um select que retornava todos os materiais da DLC, com esta variável, Ã© executado na inclusão o mesmo select da pesquisa
}else{
		$ProgramaOrigem            = $_GET['ProgramaOrigem'];
		$Almoxarifado              = $_GET['Almoxarifado'];
		$Grupo                     = $_GET['Grupo'];
		$Classe                    = $_GET['Classe'];
		$Subclasse                 = $_GET['Subclasse'];  // Null - Considerar para Serviço
		$PesqApenas                = $_GET['PesqApenas']; // E - Para disponibilizar apenas Itens em Estoque, C - Apenas para Cadastro de Materiais, Null - Para os dois
		$Zerados                   = $_GET['Zerados'];    // N - Apenas Itens em Estoque não zerados, Null - Todos os Itens
		
		$Mensagem					= urldecode($_GET['Mensagem']);
		$Mens						= $_GET['Mens'];
		$Tipo						= $_GET['Tipo'];
		$Critica					= $_GET['Critica'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Ano da Requisição Ano Atual #
$AnoRequisicao = date("Y");


if ($TipoGrupo == null || is_null($TipoGrupo)){
	$TipoGrupo = "M";
}

if($ProgramaOrigem == 'CadSolicitacaoCompraIncluirManterExcluir'){

	$TextoServico = "/serviço"; //Utilizado para colocar na tela a descrição /Serviço ou /SERVIÇO quando a tela de inclusão de itens for chamado pelas telas de Solicitação de Compras

	if ($ServicoDescricaoDireta != "" && $SubclasseDescricaoDireta == "" && $MaterialDescricaoDireta == "" ) {
		$TipoGrupo = "S";
	}
}
else {
	$TextoServico = "";
}

# Monta o sql para montagem dinÃ¢mica da grade a partir da pesquisa #

$sql    = "SELECT DISTINCT(GRU.CGRUMSCODI),GRU.EGRUMSDESC,CLA.CCLAMSCODI,CLA.ECLAMSDESC, GRU.FGRUMSTIPO, ";

if( ($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {
	$sql   .= "       MAT.CMATEPSEQU, MAT.EMATEPDESC, MAT.CMATEPSITU, SUB.CSUBCLSEQU, SUB.ESUBCLDESC, UND.EUNIDMSIGL, GRU.FGRUMSTIPM ";
}
else {
	$sql   .= "       SERV.CSERVPSEQU, SERV.ESERVPDESC, SERV.CSERVPSITU ";
}

# Verifica o Tipo de Pesquisa para definir o relacionamento #

$from   = "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU 
			 LEFT JOIN SFPC.TBCLASSEMATERIALSERVICO CLA ON CLA.FCLAMSSITU = 'A' AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND GRU.FGRUMSSITU = 'A'";

if( ($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == ""){
	//PARA MATERIAL
	$from   .= " LEFT JOIN SFPC.TBSUBCLASSEMATERIAL SUB ON SUB.FSUBCLSITU = 'A' AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND SUB.CGRUMSCODI = CLA.CGRUMSCODI
				 LEFT JOIN SFPC.TBMATERIALPORTAL MAT ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU 
				 LEFT JOIN SFPC.TBUNIDADEDEMEDIDA UND ON MAT.CUNIDMCODI = UND.CUNIDMCODI ";

}
else {
	//PARA SERVIÇO
	$from   .= " LEFT JOIN SFPC.TBSERVICOPORTAL SERV ON SERV.CCLAMSCODI = CLA.CCLAMSCODI AND SERV.CGRUMSCODI = CLA.CGRUMSCODI ";
}

if ($TipoPesquisa != 0){
    $from   .= " LEFT JOIN SFPC.TBARMAZENAMENTOMATERIAL ITEM ON MAT.CMATEPSEQU = ITEM.CMATEPSEQU ";

    if($Zerados == 'N'){
		$from .= " AND ITEM.AARMATQTDE > 0 ";
	}

	//$from   .= " LEFT JOIN SFPC.TBLOCALIZACAOMATERIAL LOC ON LOC.CLOCMACODI = ITEM.CLOCMACODI AND LOC.CALMPOCODI = $Almoxarifado ";
}

$where  = " WHERE 1 = 1 "; //Artificio utilizado para colocar o 'AND' na clausula WHERE sem se preocupar.

# Verifica se o Tipo de Material foi escolhido #
if($TipoGrupo == "M" and $TipoMaterial != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == ""){
	$where .= " AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
}

# Verifica se o Grupo foi escolhido #
if($Grupo != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == ""){
	$where .= " AND GRU.CGRUMSCODI = $Grupo ";
}

# Verifica se a Classe foi escolhida #
if($Grupo != "" and $Classe != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == ""){
	$where .= " AND CLA.CGRUMSCODI = $Grupo AND CLA.CCLAMSCODI = $Classe ";
}

# Verifica se a SubClasse foi escolhida #
if($Subclasse != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == ""){
	$where .= " AND SUB.CSUBCLSEQU = $Subclasse ";
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa familia #
if($SubclasseDescricaoFamilia != "" and $MaterialDescricaoDireta == "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == ""){
	$where .= " AND ( ";
	$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($SubclasseDescricaoFamilia))."%' ";
	$where .= "     )";
}


# Se foi digitado algo na caixa de texto da subclasse em pesquisa direta #
if($SubclasseDescricaoDireta != "" and $MaterialDescricaoDireta == "" and $ServicoDescricaoDireta == ""){
	if($OpcaoPesquisaSubClasse == 0){
		if( SoNumeros($SubclasseDescricaoDireta) ){
			$where .= " AND SUB.CSUBCLSEQU = $SubclasseDescricaoDireta ";
		}
	}
	elseif($OpcaoPesquisaSubClasse == 1){
		$where .= "  AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
		$where .= " AND ( ";
		$where .= "    TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
		$where .= "     )";
	}
	else{
		$where .= "   AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
	}
}

# Se foi digitado algo na caixa de texto do material em pesquisa direta #
if($MaterialDescricaoDireta != "" and $SubclasseDescricaoDireta == "" and $ServicoDescricaoDireta == "" ){
	if($OpcaoPesquisaMaterial == 0 ){
		if(SoNumeros($MaterialDescricaoDireta)) {
			$where .= " AND MAT.CMATEPSEQU = $MaterialDescricaoDireta ";
		}
	}
	elseif($OpcaoPesquisaMaterial == 1){
		$where .= " AND ( ";
 		$where .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' ";
    	$where .= "     )";
	}
	else{
		$where .= "  AND TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($MaterialDescricaoDireta))."%' ";
	}
}


# Se foi digitado algo na caixa de texto do serviço em pesquisa direta #
if($ServicoDescricaoDireta != "" and $SubclasseDescricaoDireta == "" and $MaterialDescricaoDireta == "" ){
	if($OpcaoPesquisaServico == 0 ){
		if(SoNumeros($ServicoDescricaoDireta)) {
			$where .= " AND SERV.CSERVPSEQU = $ServicoDescricaoDireta ";
		}
	}
	elseif($OpcaoPesquisaServico == 1){
		$where .= "   AND TRANSLATE(SERV.ESERVPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '%".strtoupper2(RetiraAcentos($ServicoDescricaoDireta))."%' ";
	}
	else{
		$where .= "   AND TRANSLATE(SERV.ESERVPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($ServicoDescricaoDireta))."%' ";
	}
}


if( ($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == ""){
	//PARA MATERIAL
	$order  = " ORDER BY GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, MAT.EMATEPDESC ";
}
else {
	//PARA SERVIÇO
	$order  = " ORDER BY GRU.EGRUMSDESC, CLA.ECLAMSDESC, SERV.ESERVPDESC ";
}

# Gera o SQL com a concatenação das variaveis $sql,$from,$where,$order #
$sqlgeral = $sql.$from.$where.$order;
//FIM NOVA CONSULTA

# Critica dos Campos #
if($Botao == "Validar"){
	$Mens     = 0;
	$Mensagem = "Informe: ";
	if( $SubclasseDescricaoDireta != "" and $OpcaoPesquisaSubClasse == 0 and ! SoNumeros($SubclasseDescricaoDireta) ){
		if($Mens == 1){ 
			$Mensagem .= ", "; 
		}
			$Mens = 1;
			$Tipo = 2;
			$Mensagem .= "<a href=\"javascript:document.CadIncluirItem.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido da Subclasse</a>";
	}
	elseif($SubclasseDescricaoDireta != "" and ($OpcaoPesquisaSubClasse == 1 or $OpcaoPesquisaSubClasse == 2) and strlen($SubclasseDescricaoDireta)< 2){
		if($Mens == 1){ 
			$Mensagem .= ", "; 
		}
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.CadIncluirItem.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
	}
		
	if( $MaterialDescricaoDireta != "" and $OpcaoPesquisaMaterial == 0 and ! SoNumeros($MaterialDescricaoDireta) ){
		if($Mens == 1){ 
			$Mensagem .= ", "; 
		}
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.CadIncluirItem.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Código reduzido do Material</a>";
	}
	elseif($MaterialDescricaoDireta != "" and ($OpcaoPesquisaMaterial == 1 or $OpcaoPesquisaMaterial == 2) and strlen($MaterialDescricaoDireta)< 2){
		if($Mens == 1){ 
			$Mensagem .= ", "; 
		}
		$Mens = 1;
		$Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.CadIncluirItem.MaterialDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
	}
}
?>
<html>
<?php
# Carrega o layout padrÃÂ£o #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
function checktodos(){
	document.CadMaterialTRPHistorico.Subclasse.value = '';
	document.CadMaterialTRPHistorico.submit();
}

function enviar(valor){
	document.CadMaterialTRPHistorico.Botao.value = valor;
	document.CadMaterialTRPHistorico.submit();
}

function validapesquisa(){
	if( ( document.CadMaterialTRPHistorico.MaterialDescricaoDireta.value != '' ) || ( document.CadMaterialTRPHistorico.SubclasseDescricaoDireta.value != '') ) {
		if(document.CadMaterialTRPHistorico.Grupo){
			document.CadMaterialTRPHistorico.Grupo.value = '';
		}
		if(document.CadMaterialTRPHistorico.Classe){
			document.CadMaterialTRPHistorico.Classe.value = '';
		}
		document.CadMaterialTRPHistorico.Botao.value = 'Validar';
	}
	if(document.CadMaterialTRPHistorico.Subclasse){
		if(document.CadMaterialTRPHistorico.SubclasseDescricaoFamilia.value != "") {
			document.CadMaterialTRPHistorico.Subclasse.value = '';
		}
	}
	document.CadMaterialTRPHistorico.submit();
}

function remeter(){
	document.CadMaterialTRPHistorico.submit();
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMaterialTRPHistorico.php" method="post" name="CadMaterialTRPHistorico">
<br><br><br><br><br>
<table width="100%" cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais/Serviços > TRP > Histórico
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
			<table width="100%" border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table width="100%" border="0" cellspacing="0" cellpadding="0" summary="">
							<tr>
								<td class="textonormal">
									<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#bfdaf2">
										<tr>
											<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="6">
												HISTÓRICO DE PRECOS TRP
											</td>
										</tr>
										<tr>
											<td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="4">PESQUISA DIRETA</td>
										</tr>
										<tr>
											<td colspan="4" bgcolor="#ffffff">
												<table width="100%" border="0" width="100%" summary="" bgcolor="#ffffff" bordercolor="#75ADE6" >
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" width="31%">Subclasse</td>
														<td class="textonormal" colspan="4">
															<select name="OpcaoPesquisaSubClasse" class="textonormal">
																<option value="0">Código Reduzido</option>
																<option value="1">Descrição contendo</option>
																<option value="2">Descrição iniciada por</option>
															</select>
															<input type="text" name="SubclasseDescricaoDireta" value="<?php echo $SubclasseDescricaoDireta; ?>" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadMaterialTRPHistorico.ServicoDescricaoDireta.value = '';document.CadMaterialTRPHistorico.MaterialDescricaoDireta.value = '';document.CadMaterialTRPHistorico.TipoGrupo.value = 'M';">
															<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
														</td>
													</tr>
													<tr>
														<td class="textonormal" bgcolor="#DCEDF7" width="31%">Material</td>
														<td class="textonormal" colspan="4">
															<select name="OpcaoPesquisaMaterial" class="textonormal">
																<option value="0">Código Reduzido</option>
																<option value="1">Descrição contendo</option>
																<option value="2">Descrição iniciada por</option>
															</select>
															<input type="text" name="MaterialDescricaoDireta" value="<?php echo $MaterialDescricaoDireta; ?>" size="10" maxlength="10" class="textonormal" onFocus="javascript:document.CadMaterialTRPHistorico.ServicoDescricaoDireta.value = '';document.CadMaterialTRPHistorico.SubclasseDescricaoDireta.value = '';document.CadMaterialTRPHistorico.TipoGrupo.value = 'M';">
															<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="4">PESQUISA POR FAMILIA</td>
										</tr>
										<tr>
											<td colspan="4" bgcolor="#ffffff">
												<table width="100%" border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td colspan="4">
															<table width="100%" class="textonormal" border="0" width="100%" summary="">
															<?php if ($TipoGrupo == "M") { ?>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Tipo de Material </td>
																	<td class="textonormal">
																		<input type="radio" name="TipoMaterial" value="C" onClick="javascript:document.CadMaterialTRPHistorico.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> /> Consumo
																		<input type="radio" name="TipoMaterial" value="P" onClick="javascript:document.CadMaterialTRPHistorico.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; } ?> /> Permanente
																	</td>
																</tr>
															<?php } ?>
															<?php if( $TipoGrupo != "" ){ ?>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" width="31%" height="20">Grupo</td>
																	<td class="textonormal">
																		<select name="Grupo" onChange="javascript:remeter();" class="textonormal">
																			<option selected="selected" value="">Selecione um Grupo...</option>
																			<?php
																			$db = Conexao();
																			# Mostra os grupos cadastrados #
																			if(($TipoGrupo == "M" and ($TipoMaterial == "C" or $TipoMaterial == "P")) or $TipoGrupo == "S"){
																				$sql  = "
																					SELECT
																						CGRUMSCODI,EGRUMSDESC
																					FROM SFPC.TBGRUPOMATERIALSERVICO
																					WHERE
																						FGRUMSTIPO = '$TipoGrupo' AND FGRUMSSITU = 'A'
																						";

																				if($TipoGrupo == "M" and $TipoMaterial != ""){
																					$sql  .= " AND FGRUMSTIPM = '$TipoMaterial' ";
																				}

																				$sql  .= " ORDER BY EGRUMSDESC";

																				$res  = $db->query($sql);
																				if( PEAR::isError($res) ){
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				}
																				else{
																					while( $Linha = $res->fetchRow() ){
																						$Descricao = substr($Linha[1],0,75);
																						if( $Linha[0] == $Grupo ){
																							echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																						}else{
																							echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																						}
																					}
																				}
																			}
																			?>
																		</select>
																	</td>
																</tr>
															<?php
															}
															if($Grupo != ""){
															?>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" width="31%">Classe </td>
																	<td class="textonormal">
																		<select name="Classe" class="textonormal" onChange="javascript:remeter();">
																			<option selected="selected" value="">Selecione uma Classe...</option>
																			<?php
																			if($Grupo != ""){
																				$db   = Conexao();

																				$sql  = "SELECT CLA.CCLAMSCODI, CLA.ECLAMSDESC 
																						 FROM SFPC.TBCLASSEMATERIALSERVICO CLA, SFPC.TBGRUPOMATERIALSERVICO GRU 
																						 WHERE GRU.CGRUMSCODI = CLA.CGRUMSCODI AND CLA.CGRUMSCODI = $Grupo AND CLA.FCLAMSSITU = 'A' AND GRU.FGRUMSSITU = 'A' 
																						 ORDER BY ECLAMSDESC";

																				$res  = $db->query($sql);
																				if( PEAR::isError($res) ){
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				}
																				else{
																					while( $Linha = $res->fetchRow() ){
																						$Descricao = substr($Linha[1],0,75);
																						if($Linha[0] == $Classe){
																							echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																						}
																						else{
																							echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																						}
																					}
																				}
																				$db->disconnect();
																			}
																			?>
																		</select>
																	</td>
																</tr>
															<?php
															}
															if($Grupo != "" and $Classe != "" and $TipoGrupo == "M"){ //Apenas para Material
															?>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Subclasse</td>
																	<td class="textonormal">
																		<select name="Subclasse" onChange="javascript:remeter();" class="textonormal">
																			<option selected="selected" value="">Selecione uma Subclasse...</option>
																			<?php
																			$db = Conexao();
																			$sql   = "  SELECT 	SUB.CSUBCLSEQU, SUB.ESUBCLDESC 
																						FROM 	SFPC.TBGRUPOMATERIALSERVICO GRU,SFPC.TBCLASSEMATERIALSERVICO CLA, 
																								SFPC.TBSUBCLASSEMATERIAL SUB 
																						WHERE 	SUB.CGRUMSCODI = CLA.CGRUMSCODI 
																								AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI 
																								AND GRU.FGRUMSSITU = 'A' AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' 
																								AND SUB.CGRUMSCODI = '$Grupo' AND SUB.CCLAMSCODI = '$Classe'
																								ORDER BY ESUBCLDESC ";
																			
																			$result = $db->query($sql);

																			if( PEAR::isError($result) ){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			}
																			else{
																				while($Linha = $result->fetchRow()){
																					$Descricao   = substr($Linha[1],0,75);
																					if($Linha[0] == $Subclasse ){
																						echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																					}
																					else{
																						echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																					}
																				}
																			}
																			?>
																		</select>
																		<input type="text" name="SubclasseDescricaoFamilia" size="10" maxlength="10" class="textonormal">
																		<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
																		<input type="checkbox" name="chkSubclasse" onClick="javascript:checktodos();" value="T" <?php if($ChkSubclasse == "T") { echo ("checked"); } ?> >Todas
																	</td>
																</tr>
															<?php } ?>
															</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<?php
										if($MaterialDescricaoDireta != ""){
											if($OpcaoPesquisaMaterial == 0){
												if( !SoNumeros($MaterialDescricaoDireta) ){
													$sqlgeral = "";
												}
											}
										}
										
										if($SubclasseDescricaoDireta != "" ){
											if($OpcaoPesquisaSubClasse == 0){
												if( !SoNumeros($SubclasseDescricaoDireta) ){
													$sqlgeral = "";
												}
											}
										}

										if($sqlgeral != "" and $Mens == 0){
											if( ( ( $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "" ) or ($Subclasse != "") or ($SubclasseDescricaoFamilia != "") or ($ChkSubclasse == "T") )//Validação para Material
													or ( $ServicoDescricaoDireta != "" or ($TipoGrupo == 'S' and $Classe != 0) ) //Validação para Serviço
												){
												$db     = Conexao();
												$res    = $db->query($sqlgeral);
												
												//var_dump($sqlgeral); die;
												
												if( PEAR::isError($res) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlgeral");
												}
												else{
													$qtdres = $res->numRows();
													echo "<tr>\n";
													echo "  <td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
													echo "</tr>\n";

													if($qtdres > 0){
														$TipoMaterialAntes  = "";
														$GrupoAntes         = "";
														$ClasseAntes        = "";
														$SubClasseAntes     = "";
														$SubClasseSequAntes = "";
														$irow               = 1;
														
														while( $row = $res->fetchRow() ){
															$GrupoCodigo        = $row[0];
															$GrupoDescricao     = $row[1];
															$ClasseCodigo       = $row[2];
															$ClasseDescricao    = $row[3];
															$TipoGrupoBanco     = $row[4];
															$CodRedMaterialServicoBanco = $row[5]; //$MaterialSequencia
															$DescricaoMaterialServicoBanco = $row[6]; //$MaterialDescricao

															if( ($TipoGrupo == "M" or $MaterialDescricaoDireta != "" or $SubclasseDescricaoDireta != "") and $ServicoDescricaoDireta == "") {//PARA MATERIAL
																$SubClasseSequ      = $row[8];
																$SubClasseDescricao = $row[9];
																$UndMedidaSigla     = $row[10];
																$TipoMaterialCodigo = $row[11];
															}

															if( $TipoGrupoBanco == "M" and $TipoMaterialAntes != $TipoMaterialCodigo ) { //PARA MATERIAL
																echo "<tr>\n";
																echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"4\" align=\"center\">";
																
																if($TipoMaterialCodigo == "C"){ 
																	echo "CONSUMO"; 
																}
																else{ 
																	echo "PERMANENTE";
																}
															
																echo "  </td>\n";
																echo "</tr>\n";
															}

															if($GrupoAntes != $GrupoDescricao){
																if($ClasseAntes != $ClasseDescricao){
																	echo "<tr>\n";
																	echo "	<td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																	echo "</tr>\n";
																}
															}
															else{
																if($ClasseAntes != $ClasseDescricao){
																	echo "<tr>\n";
																	echo "	<td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
																	echo "</tr>\n";
																}
															}
																
															//COLOCAR DESCRIÇÃO MATERIAL OU SERVIÇO E AJUSTAR DAS VARIAVEIS DE MATERIAL E SERVIÇO AQUI
															if($TipoGrupoBanco == "M"){
																$Descricao = "Material";
															} 
															else {
																$Descricao = "Serviço";
															}

															if($ClasseAntes != $ClasseDescricao){
																echo "<tr>\n";
																echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"92%\">DESCRIÇÃO DO ".strtoupper2($Descricao)."</td>\n";
																echo "	<td align=\"center\" class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"8%\">CÓD.RED.</td>\n";
																echo "</tr>\n";
															}
																
															echo "<tr>\n";
														
															if($flg == "S"){ //Só ocorre para Material
																$Url = "CadMaterialTRPHistoricoDetalhe.php?Material=$CodRedMaterialServicoBanco";
																
																echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"92%\">\n";
																echo "		<a href=\"$Url\" ><font color=\"#000000\">$DescricaoMaterialServicoBanco</font></a>";
																echo "	</td>\n";
																
																echo "	<td align=\"center\" valign=\"middle\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"8%\">\n";
																echo "		$CodRedMaterialServicoBanco";
																echo "	</td>\n";
																
																$flg = "";
															}
															else{
																$Url = "CadMaterialTRPHistoricoDetalhe.php?Material=$CodRedMaterialServicoBanco";
																
																//Ocorre para Material e Serviço
																echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"92%\">\n";
																echo "		<a href=\"$Url\" ><font color=\"#000000\">$DescricaoMaterialServicoBanco</font></a>";
																echo "	</td>\n";
																echo "  <td align=\"center\" valign=\"middle\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"8%\">\n";
																echo "		$CodRedMaterialServicoBanco";
																echo "  </td>\n";
															}

															if (!in_array($Url,$_SESSION['GetUrl'])){ 
																$_SESSION['GetUrl'][] = $Url; 
															}
																
															echo "</tr>\n";
															
															$TipoMaterialAntes  = $TipoMaterialCodigo;
															$GrupoAntes         = $GrupoDescricao;
															$ClasseAntes        = $ClasseDescricao;
															$SubClasseAntes     = $SubClasseDescricao;
															$SubClasseSequAntes = $SubClasseSequ;
														}
														
														$db->disconnect();
													}
													else{
														echo "<tr>\n";
														echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
														echo "		Pesquisa sem Ocorrências.\n";
														echo "	</td>\n";
														echo "</tr>\n";
													}
												}
											}
										}
										?>
										<tr>
											<td colspan="4" align="right" bgcolor="#ffffff">
												<input type="reset" value="Limpar" class="botao">
												<input type="hidden" name="Botao" value="">
												<input type="hidden" name="PesqApenas" value="<?php echo $PesqApenas; ?>">
												<input type="hidden" name="Zerados" value="<?php echo $Zerados; ?>">
												<input type="hidden" name="sqlgeral" value="<?php echo substr($sqlgeral,7); ?>">
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
		</td>
	</tr>
</table>	
</form>
</body>
</html>