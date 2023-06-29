<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialIncluirSelecionar.php
# Autor:    Roberta Costa
# Data:     06/06/2005
# Alterado: Álvaro Faria
# Data:     29/08/2006 - Opção de pesquisa de material com descrição "iniciada por"
# Alterado: Rodrigo Melo
# Data:     25/08/2009 - Alteração para inserir o cadastro de serviços
# Objetivo: Programa de Manutenção de Pré-cadastro de Material
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadMaterialIncluir.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao    	 	             = $_POST['Botao'];
		$TipoMaterial	             = $_POST['TipoMaterial'];
		$TipoGrupo	                 = $_POST['TipoGrupo'];
		$Grupo	 		               = $_POST['Grupo'];
		$Classe                    = $_POST['Classe'];
		$Subclasse                 = $_POST['Subclasse'];

		$ChkSubclasse              = $_POST['chksubclasse'];
		$SubclasseDescricaoFamilia = strtoupper2(trim($_POST['SubclasseDescricaoFamilia']));
		$OpcaoPesquisaSubclasse    = $_POST['OpcaoPesquisaSubclasse'];
		$SubclasseDescricaoDireta  = strtoupper2(trim($_POST['SubclasseDescricaoDireta']));

		$ChkClasse              = $_POST['chkclasse'];
		$ClasseDescricaoFamilia = strtoupper2(trim($_POST['ClasseDescricaoFamilia']));
}else{
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Seleciona as subclasses para a inclusão do Material #
$sql    = "SELECT GRU.CGRUMSCODI, CLA.CCLAMSCODI, SUB.CSUBCLSEQU, SUB.ESUBCLDESC, CLA.ECLAMSDESC, GRU.FGRUMSTIPO ";
$from   = "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA ";
$from  .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ON SUB.CGRUMSCODI = CLA.CGRUMSCODI AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND SUB.FSUBCLSITU = 'A' ";
$where  = " WHERE CLA.CGRUMSCODI = GRU.CGRUMSCODI AND GRU.FGRUMSSITU = 'A' ";
$where .= "   AND CLA.FCLAMSSITU = 'A' ";

if($TipoGrupo == 'M' or $SubclasseDescricaoDireta != "" or $SubclasseDescricaoFamilia != "" or $ChkSubclasse != "") {
	$order  = " ORDER BY SUB.ESUBCLDESC "; //ORDENAÇÃO PARA MATERIAL - POR SUBCLASSE
} else {
	$order  = " ORDER BY CLA.ECLAMSDESC "; //ORDENAÇÃO PARA SERVIÇO - POR CLASSE
}

# Verifica se o Tipo de Grupo foi escolhido #
if($TipoGrupo != "" and $SubclasseDescricaoDireta == ""){
		$where .= " AND GRU.FGRUMSTIPO = '$TipoGrupo' ";
}

# Verifica se o Tipo de Material foi escolhido #
if($TipoGrupo == "M" and ($TipoMaterial != "" and $SubclasseDescricaoDireta == "") ){
		$where .= " AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
}

# Verifica se o Grupo foi escolhido #
if($Grupo != "" and ($SubclasseDescricaoDireta == "")){
		$where .= " AND GRU.CGRUMSCODI = $Grupo ";
}

# Verifica se a Classe foi escolhida #
if($Classe != "" and ($SubclasseDescricaoDireta == "")){
		$where .= " AND CLA.CGRUMSCODI = $Grupo AND CLA.CCLAMSCODI = $Classe ";
}

# Verifica se a SubClasse foi escolhida #
if($Subclasse != "" and $SubclasseDescricaoDireta == ""){
		$where .= " AND SUB.CSUBCLSEQU = $Subclasse ";
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa familia #
if($SubclasseDescricaoFamilia != "" and $SubclasseDescricaoDireta == ""){
		$where .= " AND ( ";
		$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($SubclasseDescricaoFamilia))."%' OR ";
		$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper2(RetiraAcentos($SubclasseDescricaoFamilia))."%' ";
		$where .= "     )";
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa direta #
if($SubclasseDescricaoDireta != "" and $OpcaoPesquisaSubclasse != ""){
		if($OpcaoPesquisaSubclasse == 0){
				$where .= " AND SUB.CSUBCLSEQU = $SubclasseDescricaoDireta ";
		}elseif($OpcaoPesquisaSubclasse == 1){
				$where .= " AND ( ";
				$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' OR ";
				$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
				$where .= "     )";
		}else{
				$where .= " AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($SubclasseDescricaoDireta))."%' ";
		}
}

# Se foi digitado algo na caixa de texto da classe em pesquisa familia #
if($ClasseDescricaoFamilia != ""){
		$where .= " AND ( ";
		$where .= "      TRANSLATE(CLA.ECLAMSDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper2(RetiraAcentos($ClasseDescricaoFamilia))."%' OR ";
		$where .= "      TRANSLATE(CLA.ECLAMSDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper2(RetiraAcentos($ClasseDescricaoFamilia))."%' ";
		$where .= "     )";
}

# Gera o SQL com a concatenação das variaveis $sql,$from,$where #
$sqlgeral = $sql.$from.$where.$order;

if( $Botao == "Limpar" ){
		header("location: CadMaterialIncluirSelecionar.php");
		exit;
}elseif($Botao == "Validar"){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $SubclasseDescricaoDireta != "" and $OpcaoPesquisaSubclasse == 0 and ! SoNumeros($SubclasseDescricaoDireta) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMaterialIncluirSelecionar.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">O Código Reduzido da Subclasse</a>";
		}elseif($SubclasseDescricaoDireta != "" and ($OpcaoPesquisaSubclasse == 1 or $OpcaoPesquisaSubclasse == 2) and strlen($SubclasseDescricaoDireta)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMaterialIncluirSelecionar.SubclasseDescricaoDireta.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
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
	document.CadMaterialIncluirSelecionar.Botao.value = valor;
	document.CadMaterialIncluirSelecionar.submit();
}
function checkSubclasseTodos(){
	document.CadMaterialIncluirSelecionar.Subclasse.value = 0;
	document.CadMaterialIncluirSelecionar.submit();
}
function checkClasseTodos(){
	document.CadMaterialIncluirSelecionar.Classe.value = 0;
	document.CadMaterialIncluirSelecionar.submit();
}
function validapesquisaSubclasse(){
	if(document.CadMaterialIncluirSelecionar.SubclasseDescricaoDireta) {
			if(document.CadMaterialIncluirSelecionar.SubclasseDescricaoDireta.value != ""){
					document.CadMaterialIncluirSelecionar.Grupo.value = "";
					document.CadMaterialIncluirSelecionar.Classe.value = "";
					document.CadMaterialIncluirSelecionar.Botao.value = "Validar";
					document.CadMaterialIncluirSelecionar.submit();
			}
	}
	if(document.CadMaterialIncluirSelecionar.SubclasseDescricaoFamilia){
			if(document.CadMaterialIncluirSelecionar.SubclasseDescricaoFamilia.value != ""){
					document.CadMaterialIncluirSelecionar.Subclasse.value = "0";
					document.CadMaterialIncluirSelecionar.submit();
			}
	}
}
function validapesquisaClasse(){
	if(document.CadMaterialIncluirSelecionar.ClasseDescricaoFamilia){
			if(document.CadMaterialIncluirSelecionar.ClasseDescricaoFamilia.value != ""){
					document.CadMaterialIncluirSelecionar.Classe.value = "0";
					document.CadMaterialIncluirSelecionar.submit();
			}
	}
}
function remeter(){
	document.CadMaterialIncluirSelecionar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMaterialIncluirSelecionar.php" method="post" name="CadMaterialIncluirSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" width="100%" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais/Serv > Cadastro > Incluir
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php 
              if ( ($Mens == 1) 
                     || (isset ($_SESSION['InclusaoMensagem']) ) 
              ) {
                     $flag = 1;
                     if (isset ($_SESSION['InclusaoMensagem']) ) {                                                                                                  
                           $Mensagem =  $_SESSION['InclusaoMensagem'];
                           $Tipo = 1;
                           $flag = 0;
                     }
              ?>
	<tr>
		<td width="150"></td>
		<td align="left" colspan="2">
                                <?php ExibeMens($Mensagem,$Tipo,$flag); ?>
                            </td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="1">
						INCLUIR - CADASTRO DE MATERIAIS/SERVIÇOS
					</td>
				</tr>
				<tr>
					<td class="textonormal" colspan="1">
						<p align="justify">
							Para incluir um novo Material/Serviço, selecione os dados abaixo para efetuar a pesquisa.
						</p>
					</td>
				</tr>
				<tr>
					<td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="2">PESQUISA DIRETA DE MATERIAL</td>
				</tr>
				<tr>
					<td colspan="2">
						<table border="0" width="100%" summary="">
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="25%">Subclasse de Material</td>
								<td class="textonormal" colspan="2">
									<select name="OpcaoPesquisaSubclasse" class="textonormal">
										<option value="0">Código Reduzido</option>
										<option value="1">Descrição contendo</option>
										<option value="2">Descrição iniciada por</option>
									</select>
									<input type="text" name="SubclasseDescricaoDireta" size="10" maxlength="10" class="textonormal">
									<a href="javascript:validapesquisaSubclasse();"><img src="../midia/lupa.gif" border="0"></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="2">PESQUISA POR FAMILIA - MATERIAL/SERVIÇO</td>
				</tr>
				<tr>
					<td colspan="2">
						<table border="0" width="100%" summary="">

							<tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="25%">Tipo de Grupo</td>
				              <td class="textonormal">

				              	<input type="radio" name="TipoGrupo" value="M" onClick="javascript:document.CadMaterialIncluirSelecionar.Grupo.value='';javascript:document.CadMaterialIncluirSelecionar.Classe.value='';document.CadMaterialIncluirSelecionar.submit();" <?php if( $TipoGrupo == "M" ){ echo "checked"; }?> > Material
				              	<input type="radio" name="TipoGrupo" value="S" onClick="javascript:document.CadMaterialIncluirSelecionar.Grupo.value='';javascript:document.CadMaterialIncluirSelecionar.Classe.value='';document.CadMaterialIncluirSelecionar.submit();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> > Serviço
				              </td>
				            </tr>

				            <?php if ($TipoGrupo == "M") { ?>
					            <tr>
					              <td class="textonormal" bgcolor="#DCEDF7" width="25%">Tipo de Material</td>
					              <td class="textonormal">
					              	<input type="radio" name="TipoMaterial" value="C" onClick="javascript:document.CadMaterialIncluirSelecionar.Grupo.value='';javascript:document.CadMaterialIncluirSelecionar.Classe.value='';document.CadMaterialIncluirSelecionar.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
					              	<input type="radio" name="TipoMaterial" value="P" onClick="javascript:document.CadMaterialIncluirSelecionar.Grupo.value='';javascript:document.CadMaterialIncluirSelecionar.Classe.value='';document.CadMaterialIncluirSelecionar.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
					              </td>
					            </tr>
			 		        <?php } ?>

							<tr>
								<td class="textonormal" bgcolor="#DCEDF7">Grupo </td>
								<td class="textonormal">
									<select name="Grupo" class="textonormal" onChange="javascript:document.CadMaterialIncluirSelecionar.Classe.value='';remeter();">
										<option value="">Selecione um Grupo...</option>
										<?php
										# Mostra os grupos cadastrados #
										if(($TipoGrupo == "M" and ($TipoMaterial == "C" or $TipoMaterial == "P")) or $TipoGrupo == "S"){
												$db   = Conexao();
												$sql  = "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
												$sql .= " WHERE FGRUMSTIPO = '$TipoGrupo' ";

												if($TipoGrupo == "M" and $TipoMaterial != ""){
													$sql .= " AND FGRUMSTIPM = '$TipoMaterial' ";
												}

												$sql .= "   AND FGRUMSSITU = 'A' ";
												$sql .= " ORDER BY EGRUMSDESC";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														while( $Linha = $res->fetchRow() ){
																$Descricao = substr($Linha[1],0,75);
																if($Linha[0] == $Grupo){
																		echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																}else{
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
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7">Classe </td>
								<td class="textonormal">
									<select name="Classe" class="textonormal" onChange="javascript:remeter();">
										<option value="">Selecione uma Classe...</option>
										<?php
										if($Grupo != ""){
												$db   = Conexao();
												$sql  = "SELECT CCLAMSCODI, ECLAMSDESC ";
												$sql .= "  FROM SFPC.TBCLASSEMATERIALSERVICO ";
												$sql .= " WHERE CGRUMSCODI = $Grupo AND FCLAMSSITU = 'A' ";
												$sql .= " ORDER BY ECLAMSDESC";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														while( $Linha = $res->fetchRow() ){
																$Descricao = substr($Linha[1],0,75);
																if($Linha[0] == $Classe){
																		echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																}else{
																		echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																}
														}
												}
												$db->disconnect();
										}
										?>
									</select>

									<?php if($TipoGrupo == 'S'){ ?>
										<input type="text" name="ClasseDescricaoFamilia" size="10" maxlength="10" class="textonormal">
										<a href="javascript:validapesquisaClasse();"><img src="../midia/lupa.gif" border="0"></a>
										<input type="checkbox" name="chkclasse" onClick="javascript:checkClasseTodos();">Todas
									<?php } //FIM if($TipoGrupo == 'S') ?>
								</td>
							</tr>
							<?php
								if($Grupo != "" and $Classe != "" and $TipoGrupo == "M"){ ?>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7">Subclasse </td>
										<td class="textonormal">
											<select name="Subclasse" class="textonormal" onChange="javascript:remeter();">
												<option value="">Selecione uma Subclasse...</option>
												<?php
												$db   = Conexao();
												$sql  = "SELECT CSUBCLSEQU, ESUBCLDESC FROM SFPC.TBSUBCLASSEMATERIAL";
												$sql .= " WHERE CGRUMSCODI = '$Grupo' and CCLAMSCODI = '$Classe' AND FSUBCLSITU = 'A' ";
												$sql .= " ORDER BY ESUBCLDESC";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														while( $Linha = $res->fetchRow() ){
																$Descricao = substr($Linha[1],0,75);
																if($Linha[0] == $Subclasse){
																		echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																}else{
																		echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																}
														}
												}
												$db->disconnect();
												?>
											</select>
											<input type="text" name="SubclasseDescricaoFamilia" size="10" maxlength="10" class="textonormal">
											<a href="javascript:validapesquisaSubclasse();"><img src="../midia/lupa.gif" border="0"></a>
											<input type="checkbox" name="chksubclasse" onClick="javascript:checkSubclasseTodos();">Todas
										</td>
									</tr>
								<?php } ?>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="1" align="right">
						<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
						<input type="hidden" name="Botao" value="">
					</td>
				</tr>
				<?php
				if($SubclasseDescricaoDireta != ""){
						if($OpcaoPesquisaSubclasse == 0){
								if( !SoNumeros($SubclasseDescricaoDireta) ){ $sqlgeral = ""; }
						}
				}
				# Mostra o resultado da Pesquisa #
				if($sqlgeral != "" and $Mens == 0){
						if( ( ( $SubclasseDescricaoDireta != "" and $OpcaoPesquisaSubclasse != "" ) or ($Subclasse != 0) or ($SubclasseDescricaoFamilia != "") or ($ChkSubclasse != "") )//Validação para Material
						   or ($TipoGrupo == 'S' and ($Classe != 0 or $ClasseDescricaoFamilia != "" or $ChkClasse != "") ) //Validação para Serviço
						) {
								$db     = Conexao();
								$res    = $db->query($sqlgeral);
								$qtdres = $res->numRows();
								if( PEAR::isError($res) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										echo "			   <tr>\n";
										echo "				   <td align=\"center\" bgcolor=\"#75ADE6\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
										echo "			   </tr>\n";
										if( $qtdres > 0 ){
												echo "				<tr>\n";

												if($TipoGrupo == 'M' || ($SubclasseDescricaoDireta != "")){
													echo "					<td class=\"titulo3\" bgcolor=\"#F7F7F7\" >DESCRIÇÃO DA SUBCLASSE</td>\n";
												} else {
													echo "					<td class=\"titulo3\" bgcolor=\"#F7F7F7\" >DESCRIÇÃO DA CLASSE</td>\n";
												}


												echo "				</tr>\n";
												while( $row = $res->fetchRow() ){
														$GrupoCodigo        = $row[0];
														$ClasseCodigo       = $row[1];
														$SubClasseCodigo    = $row[2];
														$SubClasseDescricao = $row[3];
														$ClasseDescricao    = $row[4];
														$TipoGrupoBanco     = $row[5];

														echo "			<tr>\n";
														echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"90%\">\n";
														$Url = "CadMaterialIncluir.php?Grupo=$GrupoCodigo&Classe=$ClasseCodigo&Subclasse=$SubClasseCodigo&TipoGrupo=$TipoGrupoBanco";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }

														if($TipoGrupo == 'M' || ($SubclasseDescricaoDireta != "")){
															echo "					<a href=\"$Url\"><font color=\"#000000\">$SubClasseDescricao</font></a>";
														} else {
															echo "					<a href=\"$Url\"><font color=\"#000000\">$ClasseDescricao</font></a>";
														}

														echo "				</td>\n";
														echo "			</tr>\n";
												}
												$db->disconnect();
										}else{
												echo "<tr>\n";
												echo "	<td valign=\"top\" colspan=\"2\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
												echo "		Pesquisa sem Ocorrências.\n";
												echo "	</td>\n";
												echo "</tr>\n";
										}
								}
						}
				}
				?>
			</table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
</html>
