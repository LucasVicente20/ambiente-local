<?php
/**
 * Prefeitura do Recife
 * Portal de Compras
 * 
 * Programa: CadInventarioPeriodicoConsolidar.php
 * Autor:    Carlos Abreu
 * Data:     22/11/2006
 * ------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Carlos Abreu
 * Data:     15/05/2007
 * Objetivo: Ajuste para evitar erro quando trabalha com mais de uma localizacao
 * ------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Carlos Abreu
 * Data:     04/06/2007
 * Objetivo: Filtro no combo do almoxarifado para que quando usuario for do tipo atendimento apareça apenas o almox. que ele esteja relacionado
 * ------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     04/07/2008
 * Objetivo: Verificação para proibir se algum item possui a quantidade de consolidação nula
 * ------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     31/10/2008
 * Objetivo: Antes, caso fosse gerado a consolidação uma 2a vez, a nova consolidação era gerada em cima da consolidação anterior, através de updates. Isto gerava inconsistência em casos que um item contado na
 *           Consolidação antiga não eram contados na consolidação nova (pois a nova mantinha a contagem da consolidação anteriror). Agora a consolidação está simplesmente sendo deletada.
 * ------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     15/12/2008
 * Objetivo: Alteração para exibir a quantidade do estoque (AINVMAESTO em SFPC.INVENTARIOMATERIAL).
 * ------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Rodrigo Melo
 * Data:     11/07/2009
 * Objetivo: Correção para não exibir erro ao tentar consolidar sem a inclusão de nenhum materiais na contagem/recontagem.
 * ------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     03/11/2009
 * Objetivo: CR 3335
 * ------------------------------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     20/01/2023
 * Objetivo: Tarefa Redmine 277825
 * ------------------------------------------------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Aumenta o tempo de espera do servidor web para término de execução da página #
set_time_limit(600);

# Executa o controle de segurança #
session_start();
Seguranca();

function MaterialDescricao( $Db, $Localizacao, $CodigoMaterial ){
		if (!$Localizacao){ $Localizacao = 0; }
		if (!$CodigoMaterial){ $CodigoMaterial = 0; }
		$sql  = "SELECT A.EMATEPDESC ";
		$sql .= "  FROM SFPC.TBMATERIALPORTAL A, SFPC.TBARMAZENAMENTOMATERIAL B ";
		$sql .= " WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
		$sql .= "   AND B.CMATEPSEQU = $CodigoMaterial ";
		$sql .= "   AND B.CLOCMACODI = $Localizacao ";
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
		$_SESSION['Localizacao'] = $Localizacao;
		$CarregaLocalizacao  = $_POST['CarregaLocalizacao'];

		$Posicao             = $_POST['Posicao'];

		$Codigo = Array();
		$Consolidado = Array();
		for ($pos=1;$pos<=$Posicao;$pos++){
				if ( isset($_POST["Codigo".$pos]) ){
						${'Codigo'.$pos}            = $_POST["Codigo".$pos];
						$Codigo[]                   = $_POST["Codigo".$pos];
						${'Consolidado'.$pos}       = $_POST["Consolidado".$pos];
						$Consolidado[]              = $_POST["Consolidado".$pos];
				}
		}
		if (is_null($_SESSION['CodigoReduzidoAlterados'])){$_SESSION['CodigoReduzidoAlterados']=array();}
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Botao == "Limpar"){
		header("location: CadInventarioPeriodicoConsolidar.php");
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
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoConsolidar.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
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
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoConsolidar.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		$db = Conexao();
		$sql  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU
								FROM SFPC.TBINVENTARIOCONTAGEM A
							 WHERE A.CLOCMACODI=$Localizacao
								 AND A.FINVCOFECH IS NULL
								 AND A.AINVCOANOB=(SELECT MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM WHERE CLOCMACODI=$Localizacao)
							 GROUP BY A.AINVCOANOB
		";
		$res  = $db->query($sql);
		if(PEAR::isError($res)){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				$db->disconnect();
				exit;
		}else{
				$Linha = $res->fetchRow();
				$Ano        = $Linha[0];
				if (!$Ano){$Ano=date("Y");}
				$Sequencial = $Linha[1];
				$Linha = $res->fetchRow();
				$qtdeItensNovosNaoContados = $Linha[0];
				$db->disconnect();

				for($Row=1;$Row<=$Posicao;$Row++){
						if (${'Consolidado'.$Row}!=""){
								if(isset(${'Codigo'.$Row}) and !SoNumVirg(${'Consolidado'.$Row})){
										if ( $Mens == 1 ) { $Mensagem .= ", "; }
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoConsolidar.Consolidado".$Row.".focus();\" class=\"titulo2\">Quantidade (".$Row.")</a>";
								}
						}
				}


				if (
					( ( is_null($_SESSION['CodigoReduzidoAlterados']) or $_SESSION['CodigoReduzidoAlterados'] == '') or count($Codigo)!=count($_SESSION['CodigoReduzidoAlterados']) )
					or (count(array_intersect($Codigo,$_SESSION['CodigoReduzidoAlterados']))!=count($_SESSION['CodigoReduzidoAlterados']) )
				){
						$Mens      = 1;
						$Tipo      = 2;
						$Virgula   = 0;
						$Mensagem = "<font class=\"titulo2\">Não foi possível realizar a Consolidação deste Inventário, devido a pendências na rotina de Contagem/Recontagem. Acesse pelo Menu Principal > Estoques > Inventário > Periódico > Contagem/Recontagem para depois retornar a rotina de Consolidação</font>";
				} else if ( ( is_null($_SESSION['CodigoReduzidoAlterados']) or $_SESSION['CodigoReduzidoAlterados'] == '') or count(array_intersect($Codigo,$_SESSION['CodigoReduzidoAlterados']))!=count($_SESSION['CodigoReduzidoAlterados'])){
								$Mens      = 1;
								$Tipo      = 2;
								$Virgula   = 0;
								$Mensagem = "<font class=\"titulo2\">Não foi possível realizar a Consolidação deste Inventário, devido a pendências na rotina de Contagem/Recontagem. Acesse pelo Menu Principal > Estoques > Inventário > Periódico > Contagem/Recontagem para depois retornar a rotina de Consolidação</font>";
				}

				if($Mens == 0){
						$db   = Conexao();
						$sql  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU ";
						$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM A ";
						$sql .= " WHERE A.CLOCMACODI=$Localizacao ";
						$sql .= "   AND A.FINVCOFECH IS NULL ";
						$sql .= "   AND A.AINVCOANOB=( ";
						$sql .= "       SELECT MAX(AINVCOANOB) ";
						$sql .= "         FROM SFPC.TBINVENTARIOCONTAGEM ";
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
						$sql  = "SELECT DISTINCT TABELA.CMATEPSEQU";
						$sql .= "  FROM (SELECT CMATEPSEQU, AINVREQTDE AS QTD1, 0 AS QTD2 ";
						$sql .= "          FROM SFPC.TBINVENTARIOREGISTRO ";
						$sql .= "         WHERE FINVREETAP = '1'";
						$sql .= "           AND CLOCMACODI = $Localizacao";
						$sql .= "           AND AINVCOANOB = $Ano";
						$sql .= "           AND AINVCOSEQU = $Sequencial";
						$sql .= "         UNION ";
						$sql .= "        SELECT CMATEPSEQU, 0 AS QTD1, AINVREQTDE AS QTD2 ";
						$sql .= "          FROM SFPC.TBINVENTARIOREGISTRO ";
						$sql .= "         WHERE FINVREETAP = '2'";
						$sql .= "           AND CLOCMACODI = $Localizacao";
						$sql .= "           AND AINVCOANOB = $Ano";
						$sql .= "           AND AINVCOSEQU = $Sequencial";
						$sql .= "        ) AS TABELA";
						$res  = $db->query($sql);
						if(PEAR::isError($res)){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								$db->disconnect();
								exit;
						}else{
								$Rows = $res->numRows();
								if( $Rows != 0 ){
										$db->query("BEGIN");
										for($Row=1;$Row<=$Rows;$Row++){
												$Linha = $res->fetchRow();
												$CodigoReduzidoBanco[]=$Linha[0];
										}
								} else {
										$CodigoReduzidoBanco[]=Array();
								}
						}
						/*
						echo "[CodigoReduzido= '".$_SESSION['CodigoReduzido']."']";
						echo "[".is_null($_SESSION['CodigoReduzido'])."]";
						exit();
						*/
						if (is_null($_SESSION['CodigoReduzido'])){
								$Mens      = 1;
								$Tipo      = 2;
								$Virgula   = 0;
								$Mensagem = "<font class=\"titulo2\">Não há nenhum item para consolidação. Caso o almoxarifado não possua itens e não houve nenhuma movimentação desde o inventário anterior, ele deve ser finalizado sem contagem</font>";
						}else if (count(array_intersect($CodigoReduzidoBanco,$_SESSION['CodigoReduzido']))!=count($CodigoReduzidoBanco)){
								$Mens      = 1;
								$Tipo      = 2;
								$Virgula   = 0;
								$Mensagem = "<font class=\"titulo2\">Não foi possível realizar a Consolidação deste Inventário, devido a pendências na rotina de Contagem/Recontagem. Acesse pelo Menu Principal > Estoques > Inventário > Periódico > Contagem/Recontagem para depois retornar a rotina de Consolidação</font>";
						}
						$db->disconnect();
				}

				#verificar se almui item informado possui quantidade vazia
				if($Mens == 0){
					$noItensVazios=0;
					for($itr=0;$itr<count($Consolidado);$itr++){
						if($Consolidado[$itr]==""){
							if($noItensVazios==0){
								if ( $Mens == 1 ) { $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "Quantidade de consolidação do(s) item(ns) de número(s): ";
							}else{
								$Mensagem .= ", ";
							}
							$noItensVazios++;
							$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoConsolidar.Consolidado".($itr+1).".focus();\" class=\"titulo2\">".($itr+1)."</a>";
						}
					}
				}
				if($Mens == 0){
						# Gera a consolidação

						$datahora = date("Y-m-d H:i:s");

						$db   = Conexao();
						$sql  = "SELECT TABELA.CMATEPSEQU, MATERIAL.EMATEPDESC, SUM(TABELA.QTD1), SUM(TABELA.QTD2) ";
						$sql .= "  FROM (SELECT CMATEPSEQU, AINVREQTDE AS QTD1, 0 AS QTD2 ";
						$sql .= "          FROM SFPC.TBINVENTARIOREGISTRO ";
						$sql .= "         WHERE FINVREETAP = '1'";
						$sql .= "           AND CLOCMACODI = $Localizacao";
						$sql .= "           AND AINVCOANOB = $Ano";
						$sql .= "           AND AINVCOSEQU = $Sequencial";
						$sql .= "         UNION ";
						$sql .= "        SELECT CMATEPSEQU, 0 AS QTD1, AINVREQTDE AS QTD2 ";
						$sql .= "          FROM SFPC.TBINVENTARIOREGISTRO ";
						$sql .= "         WHERE FINVREETAP = '2'";
						$sql .= "           AND CLOCMACODI = $Localizacao";
						$sql .= "           AND AINVCOANOB = $Ano";
						$sql .= "           AND AINVCOSEQU = $Sequencial";
						$sql .= "        ) AS TABELA, SFPC.TBMATERIALPORTAL AS MATERIAL";
						$sql .= " WHERE TABELA.CMATEPSEQU = MATERIAL.CMATEPSEQU";
						$sql .= " GROUP BY TABELA.CMATEPSEQU, MATERIAL.EMATEPDESC";
						$sql .= " ORDER BY MATERIAL.EMATEPDESC";
						$res  = $db->query($sql);
						if(PEAR::isError($res)){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								$db->disconnect();
								exit;
						}else{
								$Rows = $res->numRows();
								if( $Rows != 0 ){
										$db->query("BEGIN");

										# Descartar consolidação anterior do periódico atual, se alguma
										$sql  = "
											DELETE FROM SFPC.TBINVENTARIOMATERIAL
											WHERE
												CLOCMACODI = $Localizacao
												AND AINVCOANOB = $Ano
												AND AINVCOSEQU = $Sequencial
										";
										$resDel  = $db->query($sql);
										if(PEAR::isError($resDel)){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												$db->disconnect();
												exit();
										}
										for($Row=1;$Row<=$Rows;$Row++){
												$Linha = $res->fetchRow();

												if ($Linha[2]==$Linha[3]){
														# Repete quantidade da Recontagem
														$sql  = "INSERT INTO SFPC.TBINVENTARIOMATERIAL ";
														$sql .= "       ( CLOCMACODI, CMATEPSEQU, AINVCOANOB, AINVCOSEQU, AINVMAESTO, ";
														$sql .= "         VINVMAUNIT, TINVMAULAT, CGREMPCODI, CUSUPOCODI ) ";
														$sql .= "VALUES ";
														$sql .= "       ( $Localizacao, $Linha[0], $Ano, $Sequencial, ".str_replace(",",".",$Linha[2]).", ";
														$sql .= "         null, '$datahora', ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_']." )";
												} else {
														# Coloca a quantidade da informada na consolidação
														$sql  = "INSERT INTO SFPC.TBINVENTARIOMATERIAL ";
														$sql .= "       ( CLOCMACODI, CMATEPSEQU, AINVCOANOB, AINVCOSEQU, AINVMAESTO, ";
														$sql .= "         VINVMAUNIT, TINVMAULAT, CGREMPCODI, CUSUPOCODI ) ";
														$sql .= "VALUES ";
														$sql .= "       ( $Localizacao, $Linha[0], $Ano, $Sequencial, '".str_replace(",",".",$Consolidado[array_search($Linha[0],$Codigo)])."', ";
														$sql .= "         null, '$datahora', ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_']." )";
												}
												$resMat  = $db->query($sql);
												if(PEAR::isError($resMat)){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														$db->query("ROLLBACK");
														$db->query("END");
														$db->disconnect();
														exit;
												}
										}
										$db->query("COMMIT");
										$db->query("END");
										$Mens     = 1;
										$Tipo     = 1;
										$Mensagem = "Dados Registrados com Sucesso";
								}
						}
						$db->disconnect();
				}
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
	document.CadInventarioPeriodicoConsolidar.Botao.value = valor;
	document.CadInventarioPeriodicoConsolidar.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
}
function AbreJanelaItem(url,largura,altura) {
	if( ! document.CadInventarioPeriodicoConsolidar.Almoxarifado.value ){
		document.CadInventarioPeriodicoConsolidar.submit();
	}else	if( ! document.CadInventarioPeriodicoConsolidar.Localizacao.value ){
		document.CadInventarioPeriodicoConsolidar.InicioPrograma.value = 2;
		document.CadInventarioPeriodicoConsolidar.submit();
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
<form action="CadInventarioPeriodicoConsolidar.php" method="post" name="CadInventarioPeriodicoConsolidar" id="CadInventarioPeriodicoConsolidar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Inventário > Periódico > Consolidar
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
									INVENTÁRIO PERIÓDICO - CONSOLIDAR
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Esta é a lista de materiais que passaram pelo processo de Contagem/Recontagem. Caso exista(m) diferença(s) de quantidade(s), linha(s) destacada(s) em vermelho, se faz necessário consolidar a(s) quantidade(s) do(s) respectivo(s) material(is). Para concluir a consolição do Inventário, clique no botão "Salvar". Para reiniciar o processo clique no botão "Limpar".<br><br>
										Caso apareça o nome "Inexistente" em contagem/recontagem, o usuário deve preencher a respectiva quantidade em Estoques/Inventário/Periódico/Contagem/Recontagem. <br><br>
										Sempre que a quantidade da Contagem/Recontagem for alterada, faz-se necessário entrar nesta opção de consolidação e clicar no botão "Salvar", para efetivar as mudanças e exibi-las corretamente na opção de Diferenças/Acertos.
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
										<tr>
				        					<td colspan="2">
												<?php if ($Localizacao != ""){?>
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
																		<table width="100%" cellpadding="3" cellspacing="1">
																			<tr bgcolor="#bfdaf2" bordercolor="#75ADE6">
																				<td width="6%" class="textoabason" rowspan="2">ORD.</td>
																				<td width="26%" class="textoabason" rowspan="2">DESCRIÇÃO</td>
																				<td width="10%" class="textoabason" rowspan="2" align="center">UNIDADE</td>
																				<td width="10%" class="textoabason" rowspan="2" align="center">CÓD.RED.</td>
																				<td width="48%" class="textoabason" colspan="3" align="center">QUANTIDADE</td>
																			</tr>
																			<tr bgcolor="#bfdaf2" bordercolor="#75ADE6">
																				<td width="16%" class="textoabason" align="center">CONTAGEM</td>
																				<td width="16%" class="textoabason" align="center">RECONTAGEM</td>
																				<td width="16%" class="textoabason" align="center">CONSOLIDADO</td>
																			</tr>
																		</table>
																		<?php
																			$db   = Conexao();
																			$sql  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU
																									FROM SFPC.TBINVENTARIOCONTAGEM A
																								 WHERE A.CLOCMACODI=$Localizacao
																								   AND A.FINVCOFECH IS NULL
																								   AND A.AINVCOANOB=(SELECT MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM WHERE CLOCMACODI=$Localizacao)
																								 GROUP BY A.AINVCOANOB";
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
																					$sql  = "
																						SELECT
																							TABELA.CMATEPSEQU, MATERIAL.EMATEPDESC, UNIDADE.EUNIDMSIGL, SUM(TABELA.QTD1), SUM(TABELA.QTD2),  "./* colunas 0-4 */"
																							CASE WHEN (SUM(EXISTE3.AINVMAESTO) IS NOT NULL) THEN SUM(TABELA.TOTAL) ELSE NULL END, "./* coluna 5 */"
																							CASE WHEN (SUM(EXISTE1.AINVREQTDE) IS NOT NULL) THEN 'S' ELSE 'N' END, "./* coluna 6 */"
																							CASE WHEN (SUM(EXISTE2.AINVREQTDE) IS NOT NULL) THEN 'S' ELSE 'N' END, "./* coluna 7 */"
																							SUM(TABELA.TOTAL) -- Utilizado para exibir o total que o usuário havia digitado. "./* coluna 8 */"
																						FROM ( "./*
																							Materiais da contagem */"
																							SELECT
																								CLOCMACODI, AINVCOANOB, AINVCOSEQU, CMATEPSEQU, AINVREQTDE AS QTD1, 0 AS QTD2, 0 AS TOTAL
																							FROM SFPC.TBINVENTARIOREGISTRO
																							WHERE
																								FINVREETAP = '1'
																								AND CLOCMACODI = $Localizacao
																								AND AINVCOANOB = $Ano
																								AND AINVCOSEQU = $Sequencial
																							UNION "./*
																							Materiais da recontagem */"
																							SELECT CLOCMACODI, AINVCOANOB, AINVCOSEQU, CMATEPSEQU, 0 AS QTD1, AINVREQTDE AS QTD2, 0 AS TOTAL
																							FROM SFPC.TBINVENTARIOREGISTRO
																							WHERE
																							FINVREETAP = '2'
																								AND CLOCMACODI = $Localizacao
																								AND AINVCOANOB = $Ano
																								AND AINVCOSEQU = $Sequencial
																							UNION
																							SELECT CLOCMACODI, AINVCOANOB, AINVCOSEQU, CMATEPSEQU, 0 AS QTD1, 0 AS QTD2, AINVMAESTO AS TOTAL
																							FROM SFPC.TBINVENTARIOMATERIAL
																							WHERE
																								CLOCMACODI = $Localizacao
																								AND AINVCOANOB = $Ano
																								AND AINVCOSEQU = $Sequencial
																								AND CMATEPSEQU IN (
																									SELECT CMATEPSEQU
																									FROM SFPC.TBINVENTARIOREGISTRO
																									WHERE
																										CLOCMACODI = $Localizacao
																										AND AINVCOANOB = $Ano
																										AND AINVCOSEQU = $Sequencial
																								)
																							) AS TABELA
																								LEFT OUTER JOIN SFPC.TBINVENTARIOREGISTRO AS EXISTE1
																									ON EXISTE1.CLOCMACODI = TABELA.CLOCMACODI
																										AND EXISTE1.AINVCOANOB = TABELA.AINVCOANOB
																										AND EXISTE1.AINVCOSEQU = TABELA.AINVCOSEQU
																										AND EXISTE1.CMATEPSEQU = TABELA.CMATEPSEQU
																										AND EXISTE1.FINVREETAP='1'
																								LEFT OUTER JOIN SFPC.TBINVENTARIOREGISTRO AS EXISTE2
																									ON EXISTE2.CLOCMACODI = TABELA.CLOCMACODI
																										AND EXISTE2.AINVCOANOB = TABELA.AINVCOANOB
																										AND EXISTE2.AINVCOSEQU = TABELA.AINVCOSEQU
																										AND EXISTE2.CMATEPSEQU = TABELA.CMATEPSEQU
																										AND EXISTE2.FINVREETAP='2'
																								LEFT OUTER JOIN SFPC.TBINVENTARIOMATERIAL AS EXISTE3
																									ON EXISTE3.CLOCMACODI = TABELA.CLOCMACODI
																										AND EXISTE3.AINVCOANOB = TABELA.AINVCOANOB
																										AND EXISTE3.AINVCOSEQU = TABELA.AINVCOSEQU
																										AND EXISTE3.CMATEPSEQU = TABELA.CMATEPSEQU ,
																							SFPC.TBMATERIALPORTAL AS MATERIAL, SFPC.TBUNIDADEDEMEDIDA AS UNIDADE
																						WHERE TABELA.CMATEPSEQU = MATERIAL.CMATEPSEQU
																							AND MATERIAL.CUNIDMCODI = UNIDADE.CUNIDMCODI
																						GROUP BY TABELA.CMATEPSEQU, MATERIAL.EMATEPDESC, UNIDADE.EUNIDMSIGL
																						ORDER BY MATERIAL.EMATEPDESC
																					";
																					$res  = $db->query($sql);
																					if(PEAR::isError($res)){
																							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																							$db->disconnect();
																							exit;
																					}else{
																							$Rows = $res->numRows();
																							$Posicao = $Rows;
																							if( $Rows != 0 ){
																									$_SESSION['CodigoReduzido']=array();
																									$_SESSION['CodigoReduzidoAlterados']=array();
																									for($Row=1;$Row<=$Rows;$Row++){
																											$Linha = $res->fetchRow();
																											$_SESSION['CodigoReduzido'][]=$Linha[0];
                                                      $QtdeConsolidada = $Linha[8];

                                                      //if($QtdeConsolidada == null){$QtdeConsolidada = 0;}

																											/* Se a contagem foi igual a recontagem e ambos existem (não são inexistentes) */

																											if ($Linha[3]==$Linha[4] and $Linha[6] == 'S' and $Linha[7] == 'S' ){
																													echo "<table border=\"0\" class=\"textonormal\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">";
																													echo "<tr bgcolor=\"DDECF9\">";
																													echo "<td width=\"6%\" align=\"right\">$Row</td>";
																													echo "<td width=\"26%\">$Linha[1]</td>";
																													echo "<td width=\"10%\" align=\"center\">$Linha[2]</td>";
																													echo "<td width=\"10%\" align=\"center\">$Linha[0]</td>";
																													if ($Linha[6]=='N'){
																															echo "<td width=\"16%\" align=\"right\">Inexistente</td>";
																													} else {
																															echo "<td width=\"16%\" align=\"right\">".str_replace(".",",",sprintf("%01.2f",$Linha[3]))."</td>";
																													}
																													if ($Linha[7]=='N'){
																															echo "<td width=\"16%\" align=\"right\">Inexistente</td>";
																													} else {
																															echo "<td width=\"16%\" align=\"right\">".str_replace(".",",",sprintf("%01.2f",$Linha[4]))."</td>";
																													}
																													echo "<td width=\"16%\" align=\"right\">".str_replace(".",",",sprintf("%01.2f",$Linha[3]))."</td>";
																													echo "</tr>";
																													echo "</table>";
																											} else {
																													if ( $Botao == "" ){
																															${'Consolidado'.$Row}=$Linha[5];
																													}
																													echo "<table border=\"0\" class=\"textonormal\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">";
																													echo "<tr bgcolor=\"FF8080\">";
																													echo "<td width=\"6%\" align=\"right\">$Row</td>";
																													echo "<td width=\"26%\">$Linha[1]</td>";
																													echo "<td width=\"10%\" align=\"center\">$Linha[2]</td>";
																													echo "<td width=\"10%\" align=\"center\">$Linha[0]</td>";
																													if ($Linha[6]=='N'){
																															echo "<td width=\"16%\" align=\"right\">Inexistente</td>";
																													} else {
																															echo "<td width=\"16%\" align=\"right\">".str_replace(".",",",sprintf("%01.2f",$Linha[3]))."</td>";
																													}
																													if ($Linha[7]=='N'){
																															echo "<td width=\"16%\" align=\"right\">Inexistente</td>";
																													} else {
																															echo "<td width=\"16%\" align=\"right\">".str_replace(".",",",sprintf("%01.2f",$Linha[4]))."</td>";
																													}
																													if ( $Linha[6]=='N' or $Linha[7]=='N' ){
																															echo "<td width=\"16%\" align=\"right\">".str_replace(".",",",sprintf("%01.2f",$Linha[3]))."</td>";
																													} else {
																															if (${'Consolidado'.$Row}!=""){
																																	echo "<td width=\"16%\" align=\"right\"><input type=\"hidden\" name=\"Codigo$Row\" value=\"".$Linha[0]."\"><input type=\"text\" class=\"textonormal\" name=\"Consolidado$Row\" size=\"8\" maxlength=\"13\" value=\"".str_replace(".",",",sprintf("%01.2f",str_replace(",",".",${'Consolidado'.$Row})))."\" style=\"text-align: right;\"></td>";
																															} else {
																																	echo "<td width=\"16%\" align=\"right\"><input type=\"hidden\" name=\"Codigo$Row\" value=\"".$Linha[0]."\"><input type=\"text\" class=\"textonormal\" name=\"Consolidado$Row\" size=\"8\" maxlength=\"13\" value=\"".str_replace(".",",",sprintf("%01.2f",str_replace(",",".",$QtdeConsolidada)))."\" style=\"text-align: right;\"></td>";
																															}
																													}
																													echo "</tr>";
																													echo "</table>";
																													$_SESSION['CodigoReduzidoAlterados'][]=$Linha[0];
																											}
																									}
																							}else{
																								?>
																									<table border="0" class="textonormal" cellpadding="3" cellspacing="1" width="100%">
																										<tr bgcolor="DDECF9">
																											<td colspan="8" align="left">Nenhum item contado ou recontado.</td>
																										</tr>
																									</table>
																								<?php
																							}
																					}
																			}
																			$db->disconnect();
																	?>
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