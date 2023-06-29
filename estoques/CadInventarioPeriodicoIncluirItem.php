<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadInventarioPeriodicoIncluirItem.php
# Objetivo: Programa de Incluir Novo Item ao Inventario
# Autor:    Carlos Abreu
# Data:     08/12/2006
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Alterado: Rodrigo Melo
# Data:     28/10/2008 - Alteração do texto de erro ao inserir um material já existente na lista de inventário.
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Aumenta o tempo de espera do servidor web para término de execução da página #
set_time_limit(600);

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadIncluirItem.php' );
AddMenuAcesso( '/estoques/CadItemDetalhe.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$InicioPrograma      = $_POST['InicioPrograma'];
		$Montou              = $_POST['Montou'];
		$Almoxarifado        = $_POST['Almoxarifado'];
		$DescAlmoxarifado    = $_POST['DescAlmoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Localizacao         = $_POST['Localizacao'];
		$CarregaLocalizacao  = $_POST['CarregaLocalizacao'];
		$CheckItem           = $_POST['CheckItem'];
		$ItemCarga           = $_POST['ItemCarga'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
if($Botao == "Limpar"){
		header("location: CadInventarioPeriodicoIncluirItem.php");
		exit;
}elseif($Botao == "Carregar"){
		unset($_SESSION['item']);
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}elseif($Almoxarifado == ""){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoIncluirItem.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		}elseif($Localizacao == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoIncluirItem.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}

		if($Mens == 0){
				$db = Conexao();
				$sql  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU ";
				$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM A ";
				$sql .= " WHERE A.CLOCMACODI=$Localizacao ";
				$sql .= "   AND A.FINVCOFECH IS NULL ";
				$sql .= "   AND A.AINVCOANOB=( ";
				$sql .= "       SELECT MAX(AINVCOANOB)  ";
				$sql .= "         FROM SFPC.TBINVENTARIOCONTAGEM  ";
				$sql .= "        WHERE CLOCMACODI=$Localizacao";
				$sql .= "       ) ";
				$sql .= " GROUP BY A.AINVCOANOB";
				$res  = $db->query($sql);
				if(PEAR::isError($res)){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						$db->disconnect();
						exit;
				}else{
						$Rows = $res->numRows();
						if( $Rows != 0 ){
								$Linha = $res->fetchRow();
						}
						$Ano        = $Linha[0];
						if (!$Ano){$Ano=date("Y");}
						$Sequencial = $Linha[1];
				}
				if ($Mens == 0){
						$Mensagem = "";
						for( $i=0; $i< count($ItemCarga); $i++ ){
								$Dados = explode($SimboloConcatenacaoArray,$ItemCarga[$i]);
								$DescMaterial    = $Dados[0];
								$Material        = $Dados[1];
								$Unidade         = $Dados[2];
								$Movimentado     = $Dados[5];
								$sql  = "SELECT COUNT(*) ";
								$sql .= "  FROM SFPC.TBINVENTARIOMATERIAL ";
								$sql .= " WHERE CLOCMACODI = $Localizacao";
								$sql .= "   AND CMATEPSEQU = $Material";
								$sql .= "   AND AINVCOANOB = $Ano";
								$sql .= "   AND AINVCOSEQU = $Sequencial";
								$result  = $db->query($sql);
								if(PEAR::isError($result)){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										$db->disconnect();
										exit;
								} else {
										$Linha = $result->fetchRow();
										if ($Linha[0]==1){
												if ( $Mens == 1 ) { $Mensagem .= ", "; }
												$Mens      = 1;
												$Tipo      = 2;
												$Mensagem .= "<font class=\"titulo2\">Material com o Código Reduzido $Material já está presente na lista de Inventário ";
												$Mensagem .= "ou não houve movimentação. Favor inserir o material acessando o Menu Principal > Estoques > Inventário > Periódico > Contagem/Recontagem</a>";
										}
								}
						}
				}

				if ( $Mens == 0 ){

						$datahora = date("Y-m-d H:i:s");

						$db->query("BEGIN TRANSACTION");
						for( $i=0; $i< count($ItemCarga); $i++ ){
								$Dados = explode($SimboloConcatenacaoArray,$ItemCarga[$i]);
								$DescMaterial    = $Dados[0];
								$Material        = $Dados[1];
								$Unidade         = $Dados[2];
								$Movimentado     = $Dados[5];
								$sql  = "SELECT COUNT(*) ";
								$sql .= "  FROM SFPC.TBINVENTARIOMATERIAL ";
								$sql .= " WHERE CLOCMACODI = $Localizacao";
								$sql .= "   AND CMATEPSEQU = $Material";
								$sql .= "   AND AINVCOANOB = $Ano";
								$sql .= "   AND AINVCOSEQU = $Sequencial";
								$result  = $db->query($sql);
								if(PEAR::isError($result)){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										$db->query("ROLLBACK");
										$db->query("END TRANSACTION");
										$db->disconnect();
										exit;
								} else {
										$Linha = $result->fetchRow();
										if ($Linha[0]==0){
												$sql  = "INSERT INTO SFPC.TBINVENTARIOMATERIAL ";
												$sql .= "       (CLOCMACODI, CMATEPSEQU, AINVCOANOB, AINVCOSEQU, AINVMAESTO, ";
												$sql .= "        VINVMAUNIT, TINVMAULAT, CGREMPCODI, CUSUPOCODI) ";
												$sql .= "VALUES ($Localizacao, $Material, $Ano, $Sequencial, NULL, ";
												$sql .= "        0, '$datahora', ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].")";
												$result  = $db->query($sql);
												if(PEAR::isError($result)){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														exit;
												}
										}
								}
						}
						if( count($ItemCarga) > 0 ){
								$Mens           = 1;
								$Tipo           = 1;
								$Mensagem       = "Inclusão Realizada com Sucesso";

								# Limpando as variáveis #
								$InicioPrograma = "";
								$Montou         = "";
								$Almoxarifado	  = "";
								$Localizacao	  = "";
								unset($ItemCarga);
								unset($_SESSION['item']);
								unset($_SESSION['ItemDelete']);
						}else{
								$Mens     = 1;
								$Tipo     = 1;
								$Mensagem = "Nenhuma Inclusão foi Efetuada";
						}
						$db->query("COMMIT");
						$db->query("END TRANSACTION");
						$db->disconnect();
				}
		}

}elseif($Botao == "Retirar"){
		if(count($ItemCarga) != 0){
				for($i=0; $i< count($ItemCarga); $i++){
						if($CheckItem[$i] == ""){
								$Qtd++;
								$CheckItem[$i]           = "";
								$ItemCarga[$Qtd-1]       = $ItemCarga[$i];
						}else{
								$ItemArray = explode("Æ",$ItemCarga[$i]);
								$Material = $ItemArray[1];
								# Monta um Array para Deletar itens da Carga Inicial #
								if( $_SESSION['ItemDelete'] == "" or ! in_array($Material.$SimboloConcatenacaoDesc.$Localizacao,$_SESSION['ItemDelete']) ){
										$_SESSION['ItemDelete'][count($_SESSION['ItemDelete'])] = $Material.$SimboloConcatenacaoDesc.$Localizacao;
								}
						}
				}
				if(count($ItemCarga) > 1){
						$ItemCarga       = array_slice($ItemCarga,0,$Qtd);
				}else{
						unset($ItemCarga);
				}
		}
		unset($_SESSION['item']);
}

if($Botao == "" and $Montou == ""){
		if($InicioPrograma == ""){
				unset($_SESSION['item']);
				unset($_SESSION['ItemDelete']);
		}else{
				if($Almoxarifado == ""){
						$Mens = 1;
						$Tipo = 2;
						$Mensagem = "Informe: <a href=\"javascript:document.CadInventarioPeriodicoIncluirItem.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
				}else{
						if($Localizacao == ""){
								if($Mens == 1) { $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "Informe: <a href=\"javascript:document.CadInventarioPeriodicoIncluirItem.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
						}
						if($InicioPrograma == 2 and $Localizacao == ""){
								if($Mens == 1){ $Mensagem .= ", "; }
								$Mens = 1;
								$Tipo = 2;
								$Mensagem .= "Informe: <a href=\"javascript:document.CadInventarioPeriodicoIncluirItem.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
						}
				}
		}
}

# Monta o array de itens da Carga de material com os dados que vieram da Inclusão de itens #
if( count($_SESSION['item']) != 0 ){
		sort($_SESSION['item']);

		# Retira o primeiro bloco da descrição (sem acentuação) #
		$ItensAdd = $_SESSION['item'];
		for($j=0;$j<count($ItensAdd);$j++){
				$TiraSemAcento = explode("æ",$ItensAdd[$j]);
				$ItensAdd[$j]  = $TiraSemAcento[1];
		}

		if( $ItemCarga == "" ){
				for( $i=0;$i<count($ItensAdd);$i++ ){
						$ItemCarga[count($ItemCarga)] = $ItensAdd[$i];
				}
		}else{
				for( $i=0;$i<count($ItemCarga);$i++ ){
						$DadosItem            = explode($SimboloConcatenacaoArray,$ItemCarga[$i]);
						$SequencialItem[$i]   = $DadosItem[1];
				}
			  for( $i=0;$i<count($ItensAdd);$i++ ){
						$DadosSessao          = explode($SimboloConcatenacaoArray,$ItensAdd[$i]);
						$SequencialSessao[$i] = $DadosSessao[1];
				 		if( ! in_array($SequencialSessao[$i],$SequencialItem) ){
			  				$ItemCarga[count($ItemCarga)] = $ItensAdd[$i];
			 			}
		 		}
    }
    unset($_SESSION['item']);
}

?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadInventarioPeriodicoIncluirItem.Botao.value = valor;
	document.CadInventarioPeriodicoIncluirItem.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
}
function AbreJanelaItem(url,largura,altura) {
	if( ! document.CadInventarioPeriodicoIncluirItem.Almoxarifado.value ){
		document.CadInventarioPeriodicoIncluirItem.submit();
	}else	if( ! document.CadInventarioPeriodicoIncluirItem.Localizacao.value ){
		document.CadInventarioPeriodicoIncluirItem.InicioPrograma.value = 2;
		document.CadInventarioPeriodicoIncluirItem.submit();
	}else{
		window.open(url,'item','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
	}
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadInventarioPeriodicoIncluirItem.php" method="post" name="CadInventarioPeriodicoIncluirItem">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Inventário > Periódico > Incluir Novo Item
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
			<table  border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									INVENTÁRIO PERIÓDICO - INCLUIR NOVO ITEM
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para efetuar uma Carga Inicial em Estoque informe o almoxarifado, inclua os itens desejados e clique em 'Salvar'. Note que estes itens devem ser contados, recontados e consolidados, e seu valor informado em Diferenças e acertos.
										<br/><b>Observação:</b> Itens novos que não forem contados nem recontados serão excluídos após a consolidação.
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado*</td>
											<td class="textonormal">
												<?php
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db   = Conexao();
												if(($_SESSION['_cgrempcodi_'] == 0) or ($_SESSION['_fperficorp_'] == 'S')){
														$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A ";
														$sql .= " WHERE A.FALMPOSITU = 'A'";
														$sql .= "   AND A.FALMPOINVE = 'S'";
												} else {
														$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
														$sql .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
														$sql .= "   AND A.FALMPOSITU = 'A'";
														$sql .= "   AND A.FALMPOINVE = 'S'";
														$sql .= "   AND B.CORGLICODI IN ";
														$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
														$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
														$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.FUSUCCTIPO IN ('T','R') ";
														$sql .= "            AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
														$sql .= "            AND CEN.FCENPOSITU <> 'I' ";

														# restringir almoxarifado quando requisitante
														$sql .= "            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END";

														$sql .= "       ) ";
														$sql .= "   AND A.CALMPOCODI NOT IN ";
														$sql .= "       ( SELECT CALMPOCODI ";
														$sql .= "           FROM SFPC.TBMOVIMENTACAOMATERIAL ";
														$sql .= "          GROUP BY CALMPOCODI ";
														$sql .= "         HAVING COUNT(*) = 0)";
												}
												$sql .= " ORDER BY A.EALMPODESC ";
												$res  = $db->query($sql);
												if(PEAR::isError($res)){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														$db->disconnect();
														exit;
												}else{
														$Rows = $res->numRows();
														if($Rows == 1){
																$Linha = $res->fetchRow();
																$Almoxarifado = $Linha[0];
																echo "$Linha[1]<br>";
																echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																echo "<input type=\"hidden\" name=\"DescAlmoxarifado\" value=\"$DescAlmoxarifado\">";
																echo $DescAlmoxarifado;
														}elseif($Rows > 1){
																echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"javascript:enviar('TrocaAlmoxarifado');\">\n";
																echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																for($i=0;$i< $Rows; $i++){
																		$Linha = $res->fetchRow();
																		$DescAlmoxarifado = $Linha[1];
																		if($Linha[0] == $Almoxarifado){
																				echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
																		}else{
																				echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
																		}
																}
																echo "</select>\n";
																$CarregaAlmoxarifado = "";
														}else{
																echo "NENHUM ALMOXARIFADO DISPONÍVEL";
																echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
																echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
														}
												}
												$db->disconnect();
												?>
												<input type="hidden" name="DefineAlmoxarifado" value="<?php echo $DefineAlmoxarifado; ?>">
											</td>
										</tr>
										<?php if( $Almoxarifado != "" ){ ?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização*</td>
											<td class="textonormal">
												<?php
												$db = Conexao();
												if($Localizacao != ""){
														# Mostra a Descrição de Acordo com o Almoxarifado #
														$sql    = "SELECT A.FLOCMAEQUI, A.ALOCMANEQU, A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B";
														$sql   .= " WHERE A.CLOCMACODI = $Localizacao AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$res  = $db->query($sql);
														if(PEAR::isError($res)){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																$db->disconnect();
																exit;
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
												}else{
														# Mostra as Localizações de acordo com o Almoxarifado #
														$sql    = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
														$sql   .= " WHERE A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql   .= "   AND A.CALMPOCODI = $Almoxarifado ";
														$sql   .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
														$res  = $db->query($sql);
														if(PEAR::isError($res)){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																$db->disconnect();
																exit;
														}else{
																$Rows = $res->numRows();
																if($Rows == 0){
																		echo "NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO";
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																}elseif($Rows == 1){
																		$Linha = $res->fetchRow();
																		if($Linha[1] == "E"){
																				$Equipamento = "ESTANTE";
																		}if($Linha[1] == "A"){
																				$Equipamento = "ARMÁRIO";
																		}if($Linha[1] == "P"){
																				$Equipamento = "PALETE";
																		}
																		$DescArea = $Linha[5];
																		$Localizacao = $Linha[0];
																		echo "ÁREA: $DescArea - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																		echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																} else {
																		echo "<select name=\"Localizacao\" class=\"textonormal\" onChange=\"submit();\">\n";
																		echo "	<option value=\"\">Selecione uma Localização...</option>\n";
																		$EquipamentoAntes = "";
																		$DescAreaAntes    = "";
																		for($i=0;$i< $Rows; $i++){
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
												$db->disconnect();
												?>
											</td>
										</tr>
										<?php }
										?>
										<tr>
											<td class="textonormal" colspan="4">
												<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
													<?php

													$countItemCarga = (is_null($ItemCarga)) ? 0 : count($ItemCarga);

													for($i=0;$i< $countItemCarga ;$i++){
															$Dados = explode($SimboloConcatenacaoArray,$ItemCarga[$i]);
															$DescMaterial    = $Dados[0];
															$Material        = $Dados[1];
															$Unidade         = $Dados[2];
															$Movimentado     = $Dados[5];
															if($i == 0){
																	echo "		<tr>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" wdith=\"5%\">&nbsp;</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\">DESCRIÇÃO DO MATERIAL</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"5%\">COD. RED.</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"5%\">UNIDADE</td>\n";
																	echo "		</tr>\n";
															}
													?>
													<tr>
														<td class="textonormal" align="right">
														<?php
														if (!$Movimentado){
																echo "	<input type=\"checkbox\" name=\"CheckItem[$i]\" value=\"$Material\"\n";
														}else{
																echo "&nbsp;&nbsp;&nbsp;\n";
																# Inclue campo hidden apenas para não diferenciar a contagem dos elements para mensagens de erro, no caso da não aparição do checkbox #
																echo "<input type=\"hidden\" name=\"MaterialMovimentado[$i]\" value=\"$Material\"\n";
														}
														?>
														</td>
														<td class="textonormal">
															<?php
															$Url = "CadItemDetalhe.php?ProgramaOrigem=CadRequisicaoMaterialIncluir&Material=$Material";
															if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
															?>
															<a href="javascript:AbreJanela('<?=$Url;?>',700,370);">
																<font color="#000000">
																	<?php
																	echo trim($DescMaterial);
																	?>
	  							    					</font>
  							    					</a>
  							    					<input type="hidden" name="ItemCarga[<?php echo $i; ?>]" value="<?php echo $ItemCarga[$i]; ?>">
							  	        	</td>
							  	        	<td class="textonormal" align="center">
							  	        		<?php echo $Material; ?>
							  	        	</td>
					              		<td class="textonormal" align="center">
					              			<?php echo $Unidade; ?>
				          	    		</td>
								        	</tr>
								        	<?php } ?>
				            			<?php if($Almoxarifado){ ?>
				            			<tr>
						   	  	  			<td class="textonormal" colspan="9" align="center">
						   	  	  				<?php
															$Url = "CadIncluirItem.php?ProgramaOrigem=CadInventarioPeriodicoIncluirItem&Almoxarifado=$Almoxarifado&PesqApenas=C";
															if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
															?>
						         	      	<input type="button" name="IncluirItem" value="Incluir Item" class="botao" onclick="javascript:AbreJanelaItem('<?php $Url;?>',700,350);">
						         	      	<input type="button" name="Retirar" value="Retirar Item" class="botao" onClick="javascript:enviar('Retirar');">
						            		</td>
								        	</tr>
								        	<?php } ?>
								        </table>
								      </td>
										</tr>
	           			</table>
	           		</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
               		<input type="hidden" name="Montou" value="<?php echo $Montou; ?>">
               		<input type="hidden" name="InicioPrograma" value="1">
			  	      	<input type="button" name="Carregar" value="Salvar" class="botao" onClick="javascript:enviar('Carregar');">
			  	      	<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar');">
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
