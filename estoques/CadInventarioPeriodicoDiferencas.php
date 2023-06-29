<?php
/**
 * Portal de Compras
 * 
 * Programa: CadInventarioPeriodicoDiferencas.php
 * Objetivo: Programa de justificativas de diferenças entre contagem e recontagem de inventário periódico
 * Autor:    Carlos Abreu
 * Data:     20/12/2006
 * ---------------------------------------------------------------------------------------------------------
 * Alterado: Carlos Abreu
 * Data:     15/05/2007
 * Objetivo: Ajuste para evitar erro quando trabalha com mais de uma localizacao
 * ---------------------------------------------------------------------------------------------------------
 * Alterado: Carlos Abreu
 * Data:     04/06/2007
 * Objetivo: Filtro no combo do almoxarifado para que quando usuario for do tipo atendimento apareça apenas o almox. que ele esteja relacionado
 * ---------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     09/07/2008
 * Objetivo: Diferenças agora se baseiam em quantidade real do estoque (AARMATQTDE), ao invés da quantidade total (AARMATESTR)
 * ---------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     22/10/2008
 * Objetivo: Ignorar itens zerados sem movimentação desde o último periódico
 * ---------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     15/12/2008
 * Objetivo: Não ignorar itens zerados sem movimentação que foram incluídos após a abertura do inventário
 * ---------------------------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Aumenta o tempo de espera do servidor web para término de execução da página #
set_time_limit(600);

# Executa o controle de segurança #
session_start();
Seguranca();

$Arquivo = "CadInventarioPeriodicoDiferencas.php";
$ErroAssunto = "Erro em ".$Arquivo;

function MaterialDescricao( $Db, $Localizacao, $CodigoMaterial ){
		if (!$Localizacao){ $Localizacao = 0; }
		if (!$CodigoMaterial){ $CodigoMaterial = 0; }
		$sql  = "SELECT A.EMATEPDESC ";
		$sql .= "  FROM SFPC.TBMATERIALPORTAL A, SFPC.TBARMAZENAMENTOMATERIAL B ";
		$sql .= " WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
		$sql .= "   AND B.CMATEPSEQU = $CodigoMaterial ";
		$sql .= "   AND B.CLOCMACODI = $Localizacao";
		$result = $Db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		    $Db->disconnect();
		    exit;
		}else{
				$Rows = $result->numRows();
				if ($Rows>0){
						$Linha = $result->fetchRow();
						return $Linha[0];
				} else {
						return "<b>MATERIAL NÃO CADASTRADO NO ALMOXARIFADO</b>";
				}
		}
}

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$Almoxarifado        = $_POST['Almoxarifado'];
		$DescAlmoxarifado    = $_POST['DescAlmoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Localizacao	       = $_POST['Localizacao'];
		$CarregaLocalizacao  = $_POST['CarregaLocalizacao'];

		$Bloqueio            = $_POST['Bloqueio'];
		$Posicao             = $_POST['Posicao'];
		$Codigo              = Array();
		$Valor               = Array();
		$Justificativa       = Array();
		if (is_null($_SESSION['CodigoReduzido'])){$_SESSION['CodigoReduzido']=array();}
		for ($pos=1;$pos<=$Posicao;$pos++){
				if ( isset($_POST["Codigo".$pos]) ){
						${'Codigo'.$pos}            = $_POST["Codigo".$pos];
						$Codigo[]                   = $_POST["Codigo".$pos];
						${'Valor'.$pos}             = $_POST["Valor".$pos];
						$Valor[]                    = $_POST["Valor".$pos];
						${'Justificativa'.$pos}     = strtoupper2($_POST["Justificativa".$pos]);
						$Justificativa[]            = strtoupper2($_POST["Justificativa".$pos]);
				}
		}
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Botao == "Limpar"){
		header("location: CadInventarioPeriodicoDiferencas.php");
		exit;
}elseif($Botao == "Carregar"){
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
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoDiferencas.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
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
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoDiferencas.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		for($Row=1;$Row<=$Posicao;$Row++){
				if(isset(${'Valor'.$Row}) and ${'Valor'.$Row}!=""){
						if(!SoNumVirg(${'Valor'.$Row})){
								if ( $Mens == 1 ) { $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoDiferencas.Valor".$Row.".focus();\" class=\"titulo2\">Valor (".$Row.")</a>";
						}
				}
				if(isset(${'Justificativa'.$Row})){
						if(${'Justificativa'.$Row}!=""){
								if(strlen(${'Justificativa'.$Row})>200) {
										if ( $Mens == 1 ) { $Mensagem .= ", "; }
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoDiferencas.Justificativa".$Row.".focus();\" class=\"titulo2\">Justificativa (".$Row.") com menos de 200 caracteres</a>";
								}
						}
				}
		}
		if($Bloqueio == 1){
				$Mens      = 1;
				$Tipo      = 2;
				$Virgula   = 0;
				$Mensagem = "<font class=\"titulo2\">Não foi possível realizar a rotina de Diferenças/Acertos deste Inventário, devido a pendências na rotina de Consolidação. Acesse pelo Menu Principal > Estoques > Inventário > Periódico > Consolidar para depois retornar a rotina de Diferenças/Acertos</font>";
		}
		if (count(array_intersect($Codigo, $_SESSION['CodigoReduzido'])) != count($_SESSION['CodigoReduzido'])){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem = "<font class=\"titulo2\">REINICIE O PROCESSO</font>";
		}

		if ($Mens==0){
				$db   = Conexao();
				$sql  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU ";
				$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM A ";
				$sql .= " WHERE A.CLOCMACODI=$Localizacao ";
				$sql .= "   AND A.FINVCOFECH IS NULL ";
				$sql .= "   AND A.AINVCOANOB=( ";
				$sql .= "       SELECT MAX(AINVCOANOB)  ";
				$sql .= "         FROM SFPC.TBINVENTARIOCONTAGEM  ";
				$sql .= "        WHERE CLOCMACODI=$Localizacao ";
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
				$sql  = "SELECT TABELA.CMATEPSEQU ";
				$sql .= "  FROM ";
				$sql .= "     ( ";
				$sql .= "           SELECT CMATEPSEQU, AARMATESTR AS QTD1, VARMATUMED AS VAL1, 0.00 AS QTD2, 0.0000 AS VAL2 ";
				$sql .= "             FROM SFPC.TBARMAZENAMENTOMATERIAL ";
				$sql .= "            WHERE CLOCMACODI = $Localizacao AND (AARMATESTR<>0 OR VARMATUMED<>0) ";
				$sql .= "            UNION ALL ";
				$sql .= "           SELECT CMATEPSEQU, 0.00, 0.0000, AINVMAESTO, VINVMAUNIT ";
				$sql .= "             FROM SFPC.TBINVENTARIOMATERIAL ";
				$sql .= "            WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) = ($Localizacao,$Ano,$Sequencial) ";
				$sql .= "              AND (AINVMAESTO<>0 OR VINVMAUNIT<>0) ";
				$sql .= "     ) AS TABELA";
				$sql .= " GROUP BY TABELA.CMATEPSEQU";
				$res  = $db->query($sql);
				if(PEAR::isError($res)){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						$db->disconnect();
						exit;
				}else{
						$Rows = $res->numRows();
						if( $Rows != 0 ){
								for($Row=1;$Row<=$Rows;$Row++){
										$Linha = $res->fetchRow();
										$CodigoReduzidoBanco[]=$Linha[0];
								}
						} else {
								$CodigoReduzidoBanco[]=Array();
						}
				}
				if (count(array_intersect($CodigoReduzidoBanco,$_SESSION['CodigoReduzido']))!=count($CodigoReduzidoBanco)){
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem = "<font class=\"titulo2\">MATERIAIS ALTERADOS. REINICIE O PROCESSO ".array_intersect($CodigoReduzidoBanco,$_SESSION['CodigoReduzido'])."</font>";
				}
		
				# Realiza as alterações
				
				$datahora = date("Y-m-d H:i:s");
				if ($Mens == 0){
						$sql  = "
							SELECT 
								TABELA.CMATEPSEQU, SUM(TABELA.QTD1) AS ALMQTD, SUM(TABELA.VAL1) AS ALMVAL, SUM(TABELA.QTD2) AS INVQTD, SUM(TABELA.VAL2) AS INVVAL, 
								INVENTARIO.CMATEPSEQU 
							FROM (
								SELECT CMATEPSEQU, AARMATESTR AS QTD1, VARMATUMED AS VAL1, 0.00 AS QTD2, 0.0000 AS VAL2 
								FROM SFPC.TBARMAZENAMENTOMATERIAL 
								WHERE CLOCMACODI = $Localizacao
						      AND (AARMATQTDE<>0 OR VARMATUMED<>0)             
								UNION ALL 
								SELECT CMATEPSEQU, 0.00, 0.0000, AINVMAESTO, VINVMAUNIT 
								FROM SFPC.TBINVENTARIOMATERIAL 
								WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) = ($Localizacao,$Ano,$Sequencial)
									AND (AINVMAESTO<>0 OR VINVMAUNIT<>0)    
							) AS TABELA 
								LEFT OUTER JOIN SFPC.TBINVENTARIOMATERIAL AS INVENTARIO 
									ON TABELA.CMATEPSEQU = INVENTARIO.CMATEPSEQU AND (CLOCMACODI, AINVCOANOB, AINVCOSEQU) = ($Localizacao,$Ano,$Sequencial)
							GROUP BY TABELA.CMATEPSEQU, INVENTARIO.CMATEPSEQU
						";
						$res  = $db->query($sql);
						if(PEAR::isError($res)){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								$db->disconnect();
								exit;
						}else{
								$Rows = $res->numRows();
								$cmatepsequ = $Linha[0];
								if( $Rows != 0 ){
										$db->query("BEGIN");
										for($Row=1;$Row<=$Rows;$Row++){
												$Linha = $res->fetchRow();
												$almqtd = $Linha[1];
												$invqtd = $Linha[3];
												/* auto-preencher registro de materiais com 0 no almoxarifado e 0 no inventario
												if( ($almqtd==0) and ($invqtd==0) ){
													$sql = "       
														select count(*) 
														from 
															sfpc.tbinventarioregistro 
														where 
															clocmacodi = $Localizacao and ainvcoanob = $Ano and cmatepsequ = $cmatepsequ and ainvcosequ = $Sequencial
													";
													$resRegistro  = $db->query($sql);
													if(PEAR::isError($res)){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														$db->query("ROLLBACK");
														$db->query("END");
														$db->disconnect();
														exit(0);
													}
													$LinhaRegistro = $resRegistro->fetchRow();
													if($LinhaRegistro[0]==0){
														$sql  = "
															INSERT INTO 
																SFPC.TBINVENTARIOREGISTRO 
																(
																	CLOCMACODI, AINVCOANOB, AINVCOSEQU, CINVPOCCPF, CMATEPSEQU,
																	FINVREETAP, AINVREQTDE, TINVREULAT, CGREMPCODI, CUSUPOCODI
																) VALUES (
																	$Localizacao, $Ano, $Sequencial, '$Cpf', $Codigo, 
																	'$Etapa', ".str_replace(",",".",$MaterialQuantidade[$Chave]).", '$datahora', ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_']."
																)
														";
													}
												}*/
												if ($Linha[5]!=""){
														if ((float)$Linha[2] == 0){
																$sql  = "UPDATE SFPC.TBINVENTARIOMATERIAL ";
																if ($Valor[array_search($Linha[0],$Codigo)]!=""){
																		$sql .= "   SET VINVMAUNIT = ".str_replace(",",".",$Valor[array_search($Linha[0],$Codigo)]).", ";
																} else {
																		$sql .= "   SET VINVMAUNIT = NULL, ";
																}
																$sql .= "       EINVMAJUST = '".$Justificativa[array_search($Linha[0],$Codigo)]."', ";
																$sql .= "       TINVMAULAT = '$datahora', ";
																$sql .= "       CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
																$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'];
																$sql .= " WHERE CLOCMACODI = $Localizacao";
																$sql .= "   AND AINVCOANOB = $Ano";
																$sql .= "   AND AINVCOSEQU = $Sequencial";
																$sql .= "   AND CMATEPSEQU = $Linha[0]";
																$resMat  = $db->query($sql);
																if(PEAR::isError($resMat)){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		$db->query("ROLLBACK");
																		$db->query("END");
																		$db->disconnect();
																		exit;
																}																		
														} else {
																if ($Linha[1]==$Linha[3]){
																		$sql  = "UPDATE SFPC.TBINVENTARIOMATERIAL ";
																		if ($Linha[2]!=""){
																				$sql .= "   SET VINVMAUNIT = ".str_replace(",",".",$Linha[2]).", ";
																		} else {
																				$sql .= "   SET VINVMAUNIT = NULL, ";
																		}
																		$sql .= "       EINVMAJUST = '', ";
																		$sql .= "       TINVMAULAT = '$datahora', ";
																		$sql .= "       CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
																		$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'];
																		$sql .= " WHERE CLOCMACODI = $Localizacao";
																		$sql .= "   AND AINVCOANOB = $Ano";
																		$sql .= "   AND AINVCOSEQU = $Sequencial";
																		$sql .= "   AND CMATEPSEQU = $Linha[0]";
																		$resMat  = $db->query($sql);
																		if(PEAR::isError($resMat)){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				$db->query("ROLLBACK");
																				$db->query("END");
																				$db->disconnect();
																				exit;
																		}																		
																} else {
																		$sql  = "UPDATE SFPC.TBINVENTARIOMATERIAL ";
																		if ($Linha[2]!=""){
																				$sql .= "   SET VINVMAUNIT = ".str_replace(",",".",$Linha[2]).", ";
																		} else {
																				$sql .= "   SET VINVMAUNIT = NULL, ";
																		}
																		$sql .= "       EINVMAJUST = '".$Justificativa[array_search($Linha[0],$Codigo)]."', ";
																		$sql .= "       TINVMAULAT = '$datahora', ";
																		$sql .= "       CGREMPCODI = ".$_SESSION['_cgrempcodi_'].", ";
																		$sql .= "       CUSUPOCODI = ".$_SESSION['_cusupocodi_'];
																		$sql .= " WHERE CLOCMACODI = $Localizacao";
																		$sql .= "   AND AINVCOANOB = $Ano";
																		$sql .= "   AND AINVCOSEQU = $Sequencial";
																		$sql .= "   AND CMATEPSEQU = $Linha[0]";
																		$resMat  = $db->query($sql);
																		if(PEAR::isError($resMat)){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				$db->query("ROLLBACK");
																				$db->query("END");
																				$db->disconnect();
																				exit;
																		}																		
																}
														}
												} else {
														if ((float)$Linha[2] == 0){
																$sql  = "INSERT INTO SFPC.TBINVENTARIOMATERIAL ";
																$sql .= "       (CLOCMACODI, AINVCOANOB, AINVCOSEQU, CMATEPSEQU, AINVMAESTO, ";
																$sql .= "        VINVMAUNIT, EINVMAJUST, TINVMAULAT, CGREMPCODI, CUSUPOCODI) ";
																$sql .= "       VALUES ";
																$sql .= "       ($Localizacao, $Ano, $Sequencial, $Linha[0], 0, ";
																$sql .= "        ".str_replace(",",".",$Valor[array_search($Linha[0],$Codigo)]).", '".$Justificativa[array_search($Linha[0],$Codigo)]."', '$datahora', ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].")";
																$resMat  = $db->query($sql);
																if(PEAR::isError($resMat)){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		$db->query("ROLLBACK");
																		$db->query("END");
																		$db->disconnect();
																		exit;
																}
														} else {
																$sql  = "INSERT INTO SFPC.TBINVENTARIOMATERIAL ";
																$sql .= "       (CLOCMACODI, AINVCOANOB, AINVCOSEQU, CMATEPSEQU, AINVMAESTO, ";
																$sql .= "        VINVMAUNIT, EINVMAJUST, TINVMAULAT, CGREMPCODI, CUSUPOCODI) ";
																$sql .= "       VALUES ";
																$sql .= "       ($Localizacao, $Ano, $Sequencial, $Linha[0], 0, ";
																$sql .= "        $Linha[2], '".$Justificativa[array_search($Linha[0],$Codigo)]."', '$datahora', ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].")";
																$resMat  = $db->query($sql);
																if(PEAR::isError($resMat)){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		$db->query("ROLLBACK");
																		$db->query("END");
																		$db->disconnect();
																		exit;
																}
														}
												}
										}
										$db->query("COMMIT");
										$db->query("END");
										$Mens     = 1;
										$Tipo     = 1;
										$Mensagem = "Dados Registrados com Sucesso";
										$Botao = "";
								}
						}
				}
				$db->disconnect();
		}
}

?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script type="text/javascript" language="javascript" src="ajax/ajax.js"></script>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadInventarioPeriodicoDiferencas.Botao.value = valor;
	document.CadInventarioPeriodicoDiferencas.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
}
function AbreJanelaItem(url,largura,altura) {
	if( ! document.CadInventarioPeriodicoDiferencas.Almoxarifado.value ){
		document.CadInventarioPeriodicoDiferencas.submit();
	}else	if( ! document.CadInventarioPeriodicoDiferencas.Localizacao.value ){
		document.CadInventarioPeriodicoDiferencas.InicioPrograma.value = 2;
		document.CadInventarioPeriodicoDiferencas.submit();
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
<form action="CadInventarioPeriodicoDiferencas.php" method="post" name="CadInventarioPeriodicoDiferencas" id="CadInventarioPeriodicoDiferencas">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Inventário > Periódico > Diferenças/Acertos
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php 
		if ( $Mens == 1 ) {
				if (!isset($Virgula)){ $Virgula = 1; }
		?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Virgula); ?></td>
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
									INVENTÁRIO PERIÓDICO - DIFERENÇAS/ACERTOS
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Estes são os materiais estocados que passaram pelo processo de Consolidação de Inventário. Se um material estiver sem valor ou houver divergência entre as quantidades, este será destacado.
										Em caso de divergência, digite a justificativa. Para registrar as informações, clique no botão "Salvar", mesmo que não haja divergência entre as informações de estoque e de inventário. Para reiniciar o processo, clique no botão "Limpar".
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
														$sql .= " WHERE A.FALMPOSITU = 'A' ";
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
														if($Rows == 1 or $Almoxarifado != ''){
																if ( $Rows == 1 ){
																		$Linha = $res->fetchRow();
																		$Almoxarifado = $Linha[0];
																		echo "$Linha[1]<br>";
																		echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																		echo "<input type=\"hidden\" name=\"DescAlmoxarifado\" value=\"$DescAlmoxarifado\">";
																		echo $DescAlmoxarifado;
																} else {
																		for($i=0;$i< $Rows; $i++){
																				$Linha = $res->fetchRow();
																				if ($Almoxarifado == $Linha[0]){
																						echo "$Linha[1]<br>";
																						echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																						echo "<input type=\"hidden\" name=\"DescAlmoxarifado\" value=\"$DescAlmoxarifado\">";
																						echo $DescAlmoxarifado;
																				}
																		}
																}
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
										
										<tr>
				        					<td colspan="2">
				        					<?php if ($Localizacao!= ""){?>
												<table border=1 cellpadding=3 cellspacing=0 width=100%>
													<tr>
														<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">
															MATERIAIS EM ESTOQUE
														</td>
													</tr>
													<tr>
														<td>
															<table width=100%>
																<tr>
																	<td>
																		<table width="100%" cellpadding="3" cellspacing="1" class="textonormal">
																			<tr bgcolor="#bfdaf2" bordercolor="#75ADE6">
																				<td width="3%" class="textoabason" rowspan="2">ORD.</td>
																				<td width="27%" class="textoabason" rowspan="2">DESCRIÇÃO</td>
																				<td width="8%" class="textoabason" rowspan="2" align="center">CÓD.RED.</td>
																				<td width="8%" class="textoabason" rowspan="2" align="center">UNIDADE</td>
																				<td width="16%" class="textoabason" colspan="2" align="center">QUANTIDADE</td>
																				<td width="8%" class="textoabason" rowspan="2" align="center">VALOR</td>
																				<td width="30%" class="textoabason" rowspan="2" align="center">JUSTIFICATIVA (200 CARACTERES)</td>
																			</tr>
																			<tr bgcolor="#bfdaf2" bordercolor="#75ADE6">
																				<td width="8%" class="textoabason" align="center">ESTOQUE</td>
																				<td width="8%" class="textoabason" align="center">INVENTÁRIO</td>
																			</tr>
																		<?php
																			$db   = Conexao();
																			$sql  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU ";
																			$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM A ";
																			$sql .= " WHERE A.CLOCMACODI=$Localizacao  ";
																			$sql .= "   AND A.FINVCOFECH IS NULL ";
																			$sql .= "   AND A.AINVCOANOB=( ";
																			$sql .= "       SELECT MAX(AINVCOANOB) ";
																			$sql .= "         FROM SFPC.TBINVENTARIOCONTAGEM ";
																			$sql .= "        WHERE CLOCMACODI=$Localizacao ";
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
																					
																					# Verifica data do último inventório
																					$sql = "
																						SELECT max(tinvcoulat) as ultimo_periodico 
																						FROM SFPC.TBinventariocontagem 
																						WHERE clocmacodi = $Localizacao and finvcofech = 'S'
																					";
																					$res  = $db->query($sql);
																					if(PEAR::isError($res)){
																							//ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																							EmailErroSQL($ErroAssunto, __FILE__, __LINE__, "Erro no SQL", $sql, $res);
																							$db->disconnect();
																							exit(0);
																					}			
																					$Linha = $res->fetchRow();
																					$UltimoPeriodico = $Linha[0];
																					
																					if($UltimoPeriodico=="" or is_null($UltimoPeriodico)){
																							EmailErro($ErroAssunto, __FILE__, __LINE__, "Data do último periódico veio nula ou vazia.");
																							$db->disconnect();
																							exit(0);																						
																					}
																					/*
																						# removido do Coluna que verifica se material com item zerado
																							
																							-----------------------------------------------
																							-- Verifica se material teve movimentação desde último inventário (coluna 12)
																							-----------------------------------------------
																							CASE 
																								WHEN( 
																									SELECT 
																										count(*) 
																									FROM SFPC.TBmovimentacaomaterial 
																									WHERE 
																										calmpocodi = ".$Almoxarifado." 
																										and cmatepsequ = DADOS.CMATEPSEQU 
																										and tmovmaulat  > '".$UltimoPeriodico."' 
																								) > 0
																								THEN 'S'
																								ELSE 'N'
																								END AS MOVIMENTADO
																							---------------------------------------------
																																													
																					*/
																					$sql  = "
																						SELECT DADOS.CMATEPSEQU, DADOS.EMATEPDESC, DADOS.EUNIDMSIGL, DADOS.ALMQTD, DADOS.ALMVAL, -- colunas 0 a 4
																							DADOS.INVQTD, DADOS.INVVAL, -- colunas 5 a 6
																							CASE WHEN (ARMAZENAMENTO.CLOCMACODI IS NULL ) THEN 'N' ELSE 'S' END, -- coluna 7
																							INVENTARIO.EINVMAJUST, DADOS.CONTAGEM, DADOS.RECONTAGEM, DADOS.CONSOLIDADO, -- coluna 8 a 11
																							--------------------------------------------------------
																							-- Coluna que verifica se material com item zerado teve movimentação desde último inventário
																							--------------------------------------------------------
																							CASE 
																								WHEN
																									(
																										( 
																											-- Verifica se teve movimentação desde último periódico
																											SELECT 
																												count(*) 
																											FROM SFPC.TBmovimentacaomaterial 
																											WHERE 
																												calmpocodi = ".$Almoxarifado."
																												and cmatepsequ = DADOS.CMATEPSEQU 
																												and tmovmaulat  > '".$UltimoPeriodico."'
																										) = 0
																									) AND (
																										(
																											(
																													-- Verifica se este item não existe no armazenamento (foi adicionado após a criação do inventário)
																													-- Diferente de contagem, para diferenças e acertos, itens incluídos serão considerados como movimentados,
																													-- porque eles devem aparecer no relatório.
																													SELECT COUNT(*)
																													FROM SFPC.TBARMAZENAMENTOMATERIAL ARMA 
																													WHERE ARMA.CLOCMACODI = DADOS.CLOCMACODI AND ARMA.CMATEPSEQU = DADOS.CMATEPSEQU
																												) IS NULL
																										) OR (
																											(
																											-- verificação se o material está zerado no almoxarifado 
																											-- (note:itens adicionados durante o inventario retornarão nulo pois só serao adicionados no armazenamento após o fechamento)
																												SELECT ARMA.AARMATQTDE
																												FROM SFPC.TBARMAZENAMENTOMATERIAL ARMA 
																												WHERE ARMA.CLOCMACODI = DADOS.CLOCMACODI AND ARMA.CMATEPSEQU = DADOS.CMATEPSEQU
																											) = 0
																										) 
																								)
																								THEN 'S'
																								ELSE 'N'
																								END AS ZERADONAOMOVIMENTADO
																								----------------------------------------------
																						FROM (
																							SELECT
																								TABELA.CLOCMACODI, TABELA.CMATEPSEQU, MATERIAL.EMATEPDESC, UNIDADE.EUNIDMSIGL,
																								SUM(TABELA.QTD1) AS ALMQTD, SUM(TABELA.VAL1) AS ALMVAL,
																								SUM(TABELA.QTD2) AS INVQTD, SUM(TABELA.VAL2) AS INVVAL,
																								CASE WHEN (SUM(EXISTE1.AINVREQTDE) IS NOT NULL) THEN 'S' ELSE 'N' END AS CONTAGEM,
																								CASE WHEN (SUM(EXISTE2.AINVREQTDE) IS NOT NULL) THEN 'S' ELSE 'N' END AS RECONTAGEM,
																								CASE WHEN (EXISTE3.AINVMAESTO IS NOT NULL) THEN 'S' ELSE 'N' END AS CONSOLIDADO
																							FROM
																								(
																									SELECT
																										CLOCMACODI, CMATEPSEQU, AARMATESTR AS QTD1, VARMATUMED AS VAL1, 0.00 AS QTD2, 0.0000 AS VAL2 
																									FROM SFPC.TBARMAZENAMENTOMATERIAL 
																									WHERE CLOCMACODI = $Localizacao 
																									UNION ALL 
																									SELECT CLOCMACODI, CMATEPSEQU, 0.00, 0.0000, AINVMAESTO, VINVMAUNIT 
																									FROM SFPC.TBINVENTARIOMATERIAL
																									WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) = ($Localizacao,$Ano,$Sequencial)
																								) AS TABELA 
																									LEFT OUTER JOIN SFPC.TBINVENTARIOREGISTRO AS EXISTE1 
																										ON EXISTE1.CLOCMACODI = TABELA.CLOCMACODI 
																										AND EXISTE1.AINVCOANOB = $Ano 
																										AND EXISTE1.AINVCOSEQU = $Sequencial 
																										AND EXISTE1.CMATEPSEQU = TABELA.CMATEPSEQU 
																										AND EXISTE1.FINVREETAP = '1' 
																											LEFT OUTER JOIN SFPC.TBINVENTARIOREGISTRO AS EXISTE2 
																												ON EXISTE2.CLOCMACODI = TABELA.CLOCMACODI 
																										AND EXISTE2.AINVCOANOB = $Ano 
																										AND EXISTE2.AINVCOSEQU = $Sequencial 
																										AND EXISTE2.CMATEPSEQU = TABELA.CMATEPSEQU 
																										AND EXISTE2.FINVREETAP = '2' 
																											LEFT OUTER JOIN SFPC.TBINVENTARIOMATERIAL AS EXISTE3 
																												ON EXISTE3.CLOCMACODI = TABELA.CLOCMACODI 
																										AND EXISTE3.AINVCOANOB = $Ano 
																										AND EXISTE3.AINVCOSEQU = $Sequencial 
																										AND EXISTE3.CMATEPSEQU = TABELA.CMATEPSEQU 
																								, SFPC.TBMATERIALPORTAL AS MATERIAL, SFPC.TBUNIDADEDEMEDIDA AS UNIDADE
																							WHERE 
																								TABELA.CMATEPSEQU = MATERIAL.CMATEPSEQU
																								AND MATERIAL.CUNIDMCODI = UNIDADE.CUNIDMCODI
																							GROUP BY 
																								TABELA.CLOCMACODI, TABELA.CMATEPSEQU, MATERIAL.EMATEPDESC, UNIDADE.EUNIDMSIGL, EXISTE3.AINVMAESTO
																						) AS DADOS 
																							LEFT OUTER JOIN SFPC.TBARMAZENAMENTOMATERIAL AS ARMAZENAMENTO 
																								ON 
																									DADOS.CLOCMACODI = ARMAZENAMENTO.CLOCMACODI 
																									AND DADOS.CMATEPSEQU = ARMAZENAMENTO.CMATEPSEQU 
																								LEFT OUTER JOIN SFPC.TBINVENTARIOMATERIAL AS INVENTARIO 
																									ON 
																										DADOS.CLOCMACODI = INVENTARIO.CLOCMACODI 
																										AND DADOS.CMATEPSEQU = INVENTARIO.CMATEPSEQU 
																										AND INVENTARIO.AINVCOANOB = $Ano 
																										AND INVENTARIO.AINVCOSEQU = $Sequencial 
																						ORDER BY DADOS.EMATEPDESC
																					";
																					//echo $sql;
																					//exit(0);
																					$res  = $db->query($sql);
																					if(PEAR::isError($res)){
																							//ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																							EmailErroSQL($ErroAssunto, __FILE__, __LINE__, "Erro no SQL", $sql, $res);
																							$db->disconnect();
																							exit;
																					}else{
																							$Rows = $res->numRows();
																							$Posicao = $Rows;
																							$CntItem = 0;//número do item na tela
																							if( $Rows != 0 ){
																									$_SESSION['CodigoReduzido']=Array();
																									$_SESSION['Valor']=Array();
																									$_SESSION['Justificativa']=Array();
																									for($Row=1;$Row<=$Rows;$Row++){
																											$Linha = $res->fetchRow();

																											$qtdeEstoque = $Linha[3];
																											$Contado = $Linha[9];
																											$Recontado = $Linha[10];
																											$Consolidado = $Linha[11];
																											$vazioNaoMovimentado = $Linha[12];

																											$_SESSION['CodigoReduzido'][]=$Linha[0];

																											if ( $Botao == "" or $Botao == "TrocaAlmoxarifado" ){
																													${'Codigo'.$Row}=$Linha[0];
																													${'Valor'.$Row}=$Linha[6];
																													${'Justificativa'.$Row}=$Linha[8];
																											}
																											echo "<input type=\"hidden\" name=\"Codigo$Row\" value=\"".$Linha[0]."\">";
																											if($vazioNaoMovimentado=='N' or $Contado == "S"){
																												if ( $Linha[11] == 'S' and ($Linha[3]==0 and $Linha[5]==0 and $Linha[4]==0) or (($Linha[3]==$Linha[5] and (float)$Linha[4]!=0) and ($Linha[9] == 'S' and $Linha[10] == 'S'))){
																														echo "<tr bgcolor=\"DDECF9\">\n";
																												} else {
																														echo "<tr bgcolor=\"FF8080\">\n";
																												}
																												$CntItem++;
																												echo "<td width=\"5%\" align=\"right\">".$CntItem."</td>\n";
																												echo "<td width=\"35%\">$Linha[1]</td>\n";
																												echo "<td width=\"8%\" align=\"center\">$Linha[0]</td>\n";
																												echo "<td width=\"8%\" align=\"center\">$Linha[2]</td>\n";
																												if ($Linha[7]=='S'){
																														echo "<td width=\"8%\" align=\"right\">".str_replace(".",",",sprintf("%01.2f",$Linha[3]))."</td>\n";
																												} else {
																														echo "<td width=\"8%\" align=\"right\">Inexistente</td>\n";
																												}
																												if ($Contado=='N' or $Recontado=='N' or $Consolidado=='N'){
																														echo "<td width=\"8%\" align=\"right\">Inexistente</td>\n";
																														$Bloqueio = 1;
																												} else {
																														echo "<td width=\"8%\" align=\"right\">".str_replace(".",",",sprintf("%01.2f",$Linha[5]))."</td>\n";
																												}
																												if (($Linha[3]==0 and $Linha[5]==0 and $Linha[4]==0) or (float)$Linha[4]>0 or $Linha[9]=='N' or $Linha[10]=='N' or $Linha[11]=='N'){                                                      
																														echo "<td width=\"8%\" align=\"right\">".str_replace(".",",",sprintf("%01.4f",$Linha[4]))."</td>\n";
																												} else {
																														echo "<td width=\"8%\" align=\"right\"><input type=\"text\" name=\"Valor$Row\" class=\"textonormal\" size=\"12\" maxlength=\"23\" value=\"".str_replace(".",",",sprintf("%01.4f",str_replace(",",".",${'Valor'.$Row})))."\" style=\"text-align:right;\"></td>\n";
																												}
																												if (($Linha[3]==0 and $Linha[5]==0 and $Linha[4]==0) or ($Linha[3]==$Linha[5] and (float)$Linha[4]!=0) or $Linha[9]=='N' or $Linha[10]=='N' or $Linha[11]=='N'){
																														echo "<td width=\"30%\" align=\"center\">&nbsp;</td>\n";
																												} else {
																														echo "<td width=\"30%\" align=\"center\"><textarea name=\"Justificativa$Row\" rows=\"4\" cols=\"28\" class=\"textonormal\">".${'Justificativa'.$Row}."</textarea></td>\n";
																												}
																												echo "</tr>\n";
																											}
																									}
																									echo "<input type=\"hidden\" name=\"Bloqueio\" value=\"$Bloqueio\">\n";
																							}
																					}
																			}
																			$db->disconnect();
																		?>
																		</table>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
													
											</table>
											<?php } ?>
				        				</td>
				        		</tr>
							<?php } // Almoxarifado ?>
	           			</table>
	           		</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
               		<input type="hidden" name="InicioPrograma" value="1">
			  	      	<input type="button" name="Carregar" value="Salvar" class="botao" onClick="javascript:enviar('Carregar');">
			  	      	<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar');">
         	      	<input type="hidden" name="Botao" value="">
         	      	<input type="hidden" name="Posicao" id="Posicao" value="<?php echo $Posicao; ?>">
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
