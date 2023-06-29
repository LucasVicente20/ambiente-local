<?php
#------------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsMovimentacaoCompararCusto.php
# Autor:    Álvaro Faria
# Data:     03/10/2006
# Objetivo: Exibir as diferenças dos Custos gerados entre o Postgree e o Oracle
#           No caso de saída por requisição a data de movimentação é
#           baseada na data da situação (tipo=5 - baixada) da requisição em questão.
#--------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     09/11/2006 - Correção do link do calendário que não estava funcionando
# Alterado: Álvaro Faria
# Data:     13/12/2006 - Opção de não exibição de custo para almoxarifados
# Alterado: Carlos Abreu
# Data:     28/12/2006 - Valores aparecendo em colunas diferentes
# Alterado: Carlos Abreu
# Data:     08/01/2007 - Ajuste para exibição de novos tipos de gasto
# Alterado: Carlos Abreu
# Data:     25/01/2007 - Correção no cálculo levando em consideração as mov (19 e 20)
# Alterado: Carlos Abreu
# Data:     19/04/2007 - Ajuste para corrigir data para movimentacoes (21 e 22)
# Alterado: Carlos Abreu
# Data:     24/04/2007 - Ajuste para corrigir calculos de acertos de requisicao
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Alterado: Carlos Abreu
# Data:     27/08/2007 - Ajustes na query
# Alterado: Ariston Cordeiro
# Data:     27/03/2009 	- Alterações para pegar automaticamente o item de gasto, e o tipo de material
#												- Correção de bug na construção da tabela em que uma saída ficava com a data errada
#												- Correções gerais
#-----------------------------------------------
# OBS.:     - Tabulação 2 espaços
#						- Lembrar que sistema do portal guarda valores monetários com 4 casas para frações de real.
#							Isto pode resultar em movimentações em que as somas aparentemente possuem uma diferença de centavos em relação
#							ao sistema de custo e das movimentações separadas, pois no portal a contagem é mais precisa. Esta ferramenta
#							possui uma tolerância de 2 reais para esta diferença.
#------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/ConsMovimentacaoCompararCustoDetalhe.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$Almoxarifado        = $_POST['Almoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$DataFim             = $_POST['DataFim'];
		$DataIni             = $_POST['DataIni'];
		$Consist             = $_POST['Consist'];
		$Exibicao            = $_POST['Exibicao'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Definição da diferença máxima para considerar inconsistência entre valores do Postgree e Oracle #
$Tolerancia = 2;

# Função que agrupa as datas iguais para as movimentações de almoxarifado #
function Agrupa($Depois){
		# Ordena Array #
		sort($Depois);
		# Inicia variável que guardará o retorno #
		$Retorno = Array();
		# Procede o agrupamento #

		$itr = -1;

		$DataAnt         = null;
		$TipoMovAnt      = null;
		$AlmoxarifadoAnt = null;
		$GastoAnt        = null;
		$AnoAnt          = null;
		$MesAnt          = null;
		$DiaAnt          = null;
		$TipoMovAnt      = null;
		$CentroSeqAnt    = null;
		$TipoMaterialAnt = null;
		$MovpostCalc 		= null;


		foreach($Depois as $Itens){
				$itr ++;
				$ItensArray   = explode("_",$Itens);
				$Almoxarifado = $ItensArray[0];
				$Centro       = $ItensArray[1];
				$Deta         = $ItensArray[2];
				$Gasto        = $ItensArray[3];
				$Ano          = $ItensArray[4];
				$Mes          = $ItensArray[5];
				$Dia          = $ItensArray[6];
				$TipoMov      = $ItensArray[7];
				$CentroSeq    = $ItensArray[8];
				$Movpost      = $ItensArray[9];
				$TipoMaterial = $ItensArray[10];
				//echo "[".$TipoMaterial."]";

				$Data = $Ano.$Mes.$Dia;

				if($Data != $DataAnt or $TipoMov != $TipoMovAnt or $Gasto != $GastoAnt){
						if($itr>0){
							if($TipoMaterialAnt=="C"){
								$Retorno[] = $AlmoxarifadoAnt."_799_77_".sprintf("%02s",$GastoAnt)."_".$AnoAnt."_".$MesAnt."_".$DiaAnt."_".$TipoMovAnt."_".$CentroSeqAnt."_".$MovpostCalc."_".$TipoMaterialAnt;
							}else if($TipoMaterialAnt=="P"){
								$Retorno[] = $AlmoxarifadoAnt."_800_77_".$GastoAnt."_".$AnoAnt."_".$MesAnt."_".$DiaAnt."_".$TipoMovAnt."_".$CentroSeqAnt."_".$MovpostCalc."_".$TipoMaterialAnt;
							}

						}
						$DataAnt         = $Data;
						$TipoMovAnt      = $TipoMov;
						$AlmoxarifadoAnt = $Almoxarifado;
						$GastoAnt        = $Gasto;
						$AnoAnt          = $Ano;
						$MesAnt          = $Mes;
						$DiaAnt          = $Dia;
						$TipoMovAnt      = $TipoMov;
						$CentroSeqAnt    = $CentroSeq;
						$TipoMaterialAnt = $TipoMaterial;
						$MovpostCalc     = $Movpost;
				}elseif($Data == $DataAnt and $TipoMov == $TipoMovAnt){
						$MovpostCalc = $MovpostCalc + $Movpost;
				}
		}
		# Armazena no array o único/último elemento não processado dentro do loop #
		if($TipoMaterialAnt=="C"){
			$Retorno[] = $AlmoxarifadoAnt."_799_77_".sprintf("%02s",$GastoAnt)."_".$AnoAnt."_".$MesAnt."_".$DiaAnt."_".$TipoMovAnt."_".$CentroSeqAnt."_".$MovpostCalc."_".$TipoMaterialAnt;
		}else if($TipoMaterialAnt=="P"){
			$Retorno[] = $AlmoxarifadoAnt."_800_77_".$GastoAnt."_".$AnoAnt."_".$MesAnt."_".$DiaAnt."_".$TipoMovAnt."_".$CentroSeqAnt."_".$MovpostCalc."_".$TipoMaterialAnt;
		}

		return $Retorno;
}

if($Botao == "Limpar"){
		header("location: ConsMovimentacaoCompararCusto.php");
		exit;
}elseif($Botao == "Comparar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}elseif ($Almoxarifado == ""){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.ConsMovimentacaoCompararCusto.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"ConsMovimentacaoCompararCusto");
		if($MensErro != ""){ $Mensagem .= $MensErro; $Mens = 1; $Tipo = 2; }
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
	document.ConsMovimentacaoCompararCusto.Botao.value=valor;
	document.ConsMovimentacaoCompararCusto.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
}
<?php MenuAcesso(); ?>
//-->
</script>
<style type="text/css">
div.Aguarde {
position: absolute;
width: 200px;
height: 100px;
top: 350px;
left: 430px;
z-index: 1;
visibility: visible;
}
</style>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<div class="Aguarde" id="Aguarde"><font class=titulo2><img src="../midia/loading.gif" align="absmiddle"> Aguarde...</font></div>
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="ConsMovimentacaoCompararCusto.php" method="post" name="ConsMovimentacaoCompararCusto">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="7">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Movimentação > Comparar Custo
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
	<tr>
		<td width="150"></td>
		<td align="left" colspan="7"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="7">
									COMPARAÇÃO DE MOVIMENTAÇÕES SISTEMA ESTOQUE E SISTEMA CUSTO
								</td>
							</tr>
							<tr>
								<td class="textonormal" colspan="7">
									<p align="justify">
										Para comparar os Custos gerados entre os sistemas, selecione o Almoxarifado, o Período e clique no botão "Comparar".
									</p>
								</td>
							</tr>
							<tr>
								<td colspan="7" >
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado*</td>
											<td class="textonormal">
												<?php
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db = Conexao();
												if($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == 'S'){
														$sql    = "SELECT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A ";
														if($Almoxarifado and $Almoxarifado != "T"){
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
														$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND USU.FUSUCCTIPO IN ('T','R') ";

														# restringir almoxarifado quando requisitante
														$sql .= "            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END";

														$sql .= "       ) ";
												}
												$sql .= " ORDER BY A.EALMPODESC ";
												$res  = $db->query($sql);
												if(PEAR::isError($res)){
														EnviaErroBDRotinas("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $res->numRows();
														if($Rows == 1){
																$Linha = $res->fetchRow();
																$Almoxarifado = $Linha[0];
																echo "$Linha[1]<br>";
																echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																echo $DescAlmoxarifado;
														}elseif($Rows > 1){
																echo "<select name=\"Almoxarifado\" class=\"textonormal\">\n";
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
																echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
																echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
														}
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período</td>
											<td class="textonormal">
												<?php
												$DataMes = DataMes();
												if($DataIni == ""){ $DataIni = $DataMes[0]; }
												if($DataFim == ""){ $DataFim = $DataMes[1]; }
												$URLIni = "../calendario.php?Formulario=ConsMovimentacaoCompararCusto&Campo=DataIni";
												$URLFim = "../calendario.php?Formulario=ConsMovimentacaoCompararCusto&Campo=DataFim";
												?>
												<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
												&nbsp;a&nbsp;
												<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
												<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="25%">Consistência</td>
											<td class="textonormal">
												<select name="Consist" class="textonormal">
													<?php
													if($Consist == 'I'){
															echo "<option value=\"I\" selected>Só inconsistentes</option><option value=\"T\">Todos</option>";
													}elseif($Consist == 'T'){
															echo "<option value=\"I\">Só inconsistentes</option><option value=\"T\" selected>Todos</option>";
													}else{
															echo "<option value=\"I\">Só inconsistentes</option><option value=\"T\">Todos</option>";
													}
													?>
												</select>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="25%">Exibir Custo Almoxarifado</td>
											<td class="textonormal">
												<select name="Exibicao" class="textonormal">
													<?php
													if($Exibicao == 'N'){
															echo "<option value=\"N\" selected>Não</option><option value=\"S\">Sim</option>";
													}elseif($Exibicao == 'S'){
															echo "<option value=\"N\">Não</option><option value=\"S\" selected>Sim</option>";
													}else{
															echo "<option value=\"N\">Não</option><option value=\"S\">Sim</option>";
													}
													?>
												</select>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right" colspan="7">
									<input type="button" value="Comparar" class="botao" onclick="javascript:Aguarde.style.visibility='visible';enviar('Comparar');">
									<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
									<input type="hidden" name="Botao" value="">
								</td>
							</tr>
							<?php
							if($Botao == "Comparar" and $Mens == 0){
									# Conecta com os bancos de dados #
									$db    = Conexao();
									$dbora = ConexaoOracle();
									# Busca somatório das movimentações por dia no período especificado no Postgree #
									$sqlpost  = "
	SELECT
		DATABAIXA , FLAG , CALMPOCODI , CCENPOSEQU , SUM(SOMA) ,
		FTIPMVTIPO, CGRUSEELE1, CGRUSEELE2, CGRUSEELE3, CGRUSEELE4,
		CGRUSESUBE, AGRUSEANOI, FGRUMSTIPC
	FROM (
		SELECT
			CASE
				WHEN
					A.CREQMASEQU IS NOT NULL
					AND CTIPMVCODI IN (4,19,20)
				THEN
					TO_DATE(DATAHORA_REQUISICAO_BAIXADA(A.CREQMASEQU),'YYYY-MM-DD')
				ELSE
					CASE
						WHEN
							A.CTIPMVCODI IN (12,13,15,30)
						THEN (
							SELECT
								DATATRANSACAO.DMOVMAMOVI
							FROM SFPC.TBMOVIMENTACAOMATERIAL DATATRANSACAO
							WHERE
								DATATRANSACAO.CALMPOCOD1 = A.CALMPOCODI
								AND DATATRANSACAO.AMOVMAANO1 = A.AMOVMAANOM
								AND DATATRANSACAO.CMOVMACOD1 = A.CMOVMACODI
								AND DATATRANSACAO.CTIPMVCODI IN (6,9,11,29)
								AND
									CASE
										WHEN A.CTIPMVCODI = 11
										THEN DATATRANSACAO.CMATEPSEQ1 = A.CMATEPSEQU
										ELSE DATATRANSACAO.CMATEPSEQU = A.CMATEPSEQU
										END
						)
						ELSE A.DMOVMAMOVI
					END
			END AS DATABAIXA,
			CASE
				WHEN H.CCENPOCENT NOT IN (799,800)
				THEN '1'
				ELSE '2'
				END AS FLAG,
			A.CALMPOCODI, H.CCENPOSEQU,
			CASE
				WHEN CTIPMVCODI = 19
				THEN -AMOVMAQTDM
				ELSE AMOVMAQTDM
			END
			*
			CASE
				WHEN
					ULTIMO_VALOR_MATERIAL_REQUISICAO(A.CALMPOCODI,A.CREQMASEQU,A.CMATEPSEQU) IS NOT NULL
					AND CTIPMVCODI IN (4,19,20)
				THEN
					ULTIMO_VALOR_MATERIAL_REQUISICAO(A.CALMPOCODI,A.CREQMASEQU,A.CMATEPSEQU)
				ELSE VMOVMAVALO
			END as soma,
			FTIPMVTIPO,
			GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, GSE.CGRUSEELE4, GSE.CGRUSESUBE, GSE.AGRUSEANOI,
			D.FGRUMSTIPC
		FROM
			SFPC.TBMOVIMENTACAOMATERIAL A
				NATURAL JOIN SFPC.TBMATERIALPORTAL B
				NATURAL JOIN SFPC.TBSUBCLASSEMATERIAL C
				NATURAL JOIN SFPC.TBGRUPOMATERIALSERVICO D
				NATURAL JOIN SFPC.TBTIPOMOVIMENTACAO E
				NATURAL JOIN SFPC.TBALMOXARIFADOPORTAL ALM
				NATURAL JOIN SFPC.TBALMOXARIFADOORGAO F
				LEFT OUTER JOIN SFPC.TBREQUISICAOMATERIAL G ON A.CREQMASEQU = G.CREQMASEQU
				LEFT OUTER JOIN SFPC.TBCENTROCUSTOPORTAL H ON
					F.CORGLICODI = H.CORGLICODI
					AND
						CASE
							WHEN A.CREQMASEQU IS NOT NULL
							THEN G.CCENPOSEQU = H.CCENPOSEQU
							ELSE
								CASE
									WHEN D.FGRUMSTIPC = 'C'
									THEN
										(H.CCENPOCENT,H.CCENPODETA,H.CCENPONRPA) = (799,77,ALM.CALMPONRPA)
									ELSE
										(H.CCENPOCENT,H.CCENPODETA,H.CCENPONRPA) = (800,77,ALM.CALMPONRPA)
								END
						END
				LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE
					ON D.CGRUMSCODI = GSE.CGRUMSCODI

		WHERE
			A.CALMPOCODI = $Almoxarifado
			AND CTIPMVCODI NOT IN (3,5,7,8,18,31)
			AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A')
			AND (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)
			AND (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)
			AND (GSE.AGRUSEANOI = 2008 OR GSE.AGRUSEANOI IS NULL)
			AND
			CASE
				WHEN A.CREQMASEQU IS NOT NULL AND CTIPMVCODI IN (4,19,20)
				THEN
					TO_DATE(DATAHORA_REQUISICAO_BAIXADA(A.CREQMASEQU),'YYYY-MM-DD') BETWEEN '".DataInvertida($DataIni)."' AND '".DataInvertida($DataFim)."'
				ELSE
					CASE
						WHEN A.CTIPMVCODI IN (12,13,15,30)
						THEN (
							SELECT DATATRANSACAO.DMOVMAMOVI
							FROM SFPC.TBMOVIMENTACAOMATERIAL DATATRANSACAO
							WHERE
								DATATRANSACAO.CALMPOCOD1 = A.CALMPOCODI
								AND DATATRANSACAO.AMOVMAANO1 = A.AMOVMAANOM
								AND DATATRANSACAO.CMOVMACOD1 = A.CMOVMACODI
								AND DATATRANSACAO.CTIPMVCODI IN (6,9,11,29)
								AND
									CASE
										WHEN A.CTIPMVCODI = 11
										THEN DATATRANSACAO.CMATEPSEQ1 = A.CMATEPSEQU
										ELSE DATATRANSACAO.CMATEPSEQU = A.CMATEPSEQU
									END
						) BETWEEN '".DataInvertida($DataIni)."' and '".DataInvertida($DataFim)."'
						ELSE
							A.DMOVMAMOVI BETWEEN '".DataInvertida($DataIni)."'
							AND '".DataInvertida($DataFim)."'
					END
			END
	) AS TABELAMESTRE
	GROUP BY
		FLAG, CALMPOCODI, CCENPOSEQU, DATABAIXA, AGRUSEANOI, CGRUSEELE1,
		CGRUSEELE2, CGRUSEELE3, CGRUSEELE4, CGRUSESUBE, FTIPMVTIPO, FGRUMSTIPC
	ORDER BY
		FLAG, CALMPOCODI, CCENPOSEQU, DATABAIXA, AGRUSEANOI, CGRUSEELE1,
		CGRUSEELE2, CGRUSEELE3, CGRUSEELE4, CGRUSESUBE, FTIPMVTIPO DESC, FGRUMSTIPC
									";
									//echo "[".$sqlpost."]";
									$respost = $db->query($sqlpost);
									if( PEAR::isError($respost) ){
										EmailErroSQL("Erro em ConsMovimentacaoCompararCusto", __FILE__, __LINE__, "SQL falhou", $sqlpost, $respost);
										//ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlpost");
									}else{
											echo "<tr>\n";
											echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"7\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
											echo "</tr>\n";
											$numresp = $respost->numRows();
											if($numresp > 0){

													$itr=-1;
													$noItens = 0;

													$ArrayData = Array();
													$ArrayDia = Array();
													$ArrayMes = Array();
													$ArrayAno = Array();
													$ArrayFlag = Array();
													$ArrayAlmoxarifado = Array();
													$ArrayCentroSeq = Array();
													$ArrayMovpost = Array();
													$ArrayTipoMov = Array();
													$ArrayGasto = Array();
													$ArrayTipoMaterial = Array();

													$MovpostSum=0;

													$FlagAnterior = '';
													$AlmoxarifadoAnterior = '';
													$CentroSeqAnterior = '';
													$DataAnterior = '';
													$GastoAnterior = 0;
													$TipoMovAnterior = '';

													while($rowpost = $respost->fetchRow()){
														$itr++;
														$FlagNovo = $rowpost[1];
														$AlmoxarifadoNovo = $rowpost[2];
														$CentroSeqNovo = $rowpost[3];
														$DataNovo = $rowpost[0];
														$MovpostNovo = $rowpost[4];
														$TipoMovNovo = $rowpost[5];

														$elemento1 = $rowpost[6];
														$elemento2 = $rowpost[7];
														$elemento3 = $rowpost[8];
														$elemento4 = $rowpost[9];
														$subelemento = $rowpost[10];
														$anoelemento = $rowpost[11];
														$TipoMaterial = $rowpost[12];
														//echo "[!".$DataNovo."_".$MovpostNovo."]";

														$sqloracle="
																SELECT CESPCPCODI
																FROM SFCP.TBESPECIFICACAOSUBELEMENTO
																WHERE
																	DEXERCANOR = ".$anoelemento."
																	AND CELED1ELE1 = ".$elemento1."
																	AND CELED2ELE2 = ".$elemento2."
																	AND CELED3ELE3 = ".$elemento3."
																	AND CELED4ELE4 = ".$elemento4."
																	AND CSUBEDELEM = ".$subelemento."
														";
														$resoracle = $dbora->query($sqloracle);

														if( PEAR::isError($resoracle) ){
																$dbora->disconnect();
																EmailErroSQL("Erro em ConsMovimentacaoCompararCusto", __FILE__, __LINE__, "SQL para pegar item de gasto falhou", $sqloracle, $resoracle);
																//ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqloracle");
																exit();
														}
														$roworacle = $resoracle->fetchRow();
														if(is_null($roworacle[0])){
															$GastoNovo = -1;
														}else{
															$GastoNovo = $roworacle[0];
														}

														# comparar esta iteração com a anterior para ver se é um novo movimento ou o mesmo

														if(
															($itr>0) and
															(
																($FlagAnterior != $FlagNovo) or
																($AlmoxarifadoAnterior != $AlmoxarifadoNovo) or
																($CentroSeqAnterior != $CentroSeqNovo) or
																($DataAnterior != $DataNovo) or
																($GastoAnterior != $GastoNovo) or
																($TipoMovAnterior != $TipoMovNovo)
															)
														){
															$ArrayFlag[$noItens] = $FlagAnterior;
															$ArrayAlmoxarifado[$noItens] = $AlmoxarifadoAnterior;
															$ArrayCentroSeq[$noItens] = $CentroSeqAnterior;
															$ArrayData[$noItens] = $DataAnterior;
															$ArrayGasto[$noItens] = $GastoAnterior;
															$ArrayMovpost[$noItens] = $MovpostSum;
															$ArrayTipoMov[$noItens] = $TipoMovAnterior;
															$ArrayTipoMaterial[$noItens] = $TipoMaterialAnterior;
															//echo "--||[!".$DataAnterior."_".$MovpostSum."]||--";

															$MovpostSum=0;
															$noItens++;

														}

														$MovpostSum += $MovpostNovo;

														#guardar valores para comparação com a próxima iteração

														$FlagAnterior = $FlagNovo;
														$AlmoxarifadoAnterior = $AlmoxarifadoNovo;
														$CentroSeqAnterior = $CentroSeqNovo;
														$DataAnterior = $DataNovo;
														$GastoAnterior = $GastoNovo;
														$TipoMovAnterior = $TipoMovNovo;
														$TipoMaterialAnterior = $TipoMaterial;


													}

													#registrar o ultimo movimento
													if($itr>=0){
														$ArrayFlag[$noItens] = $FlagAnterior;
														$ArrayAlmoxarifado[$noItens] = $AlmoxarifadoAnterior;
														$ArrayCentroSeq[$noItens] = $CentroSeqAnterior;
														$ArrayData[$noItens] = $DataAnterior;
														$ArrayGasto[$noItens] = $GastoAnterior;
														$ArrayMovpost[$noItens] = $MovpostSum;
														$ArrayTipoMov[$noItens] = $TipoMovAnterior;
														$ArrayTipoMaterial[$noItens] = $TipoMaterialAnterior;



														$noItens++;
													}



													$Depois = Array();

													for($itr=0;$itr<$noItens;$itr++){

															$Data         = $ArrayData[$itr];
															$DataArray    = explode("-",$Data);
															$Dia          = $DataArray[2];
															$Mes          = $DataArray[1];
															$Ano          = $DataArray[0];
															$Flag         = $ArrayFlag[$itr];
															$Almoxarifado = $ArrayAlmoxarifado[$itr];
															$CentroSeq    = $ArrayCentroSeq[$itr];
															$Movpost      = $ArrayMovpost[$itr];

															$TipoMov      = $ArrayTipoMov[$itr];
															$Gasto = $ArrayGasto[$itr];
															$TipoMaterial = $ArrayTipoMaterial[$itr];

															//echo "[Data = ".$Data."# Flag = ".$Flag."# Almoxarifado = ".$Almoxarifado."# CentroSeq = ".$CentroSeq."# Movpost = ".$Movpost."# TipoMov = ".$TipoMov."# Gasto = ".$Gasto."]";

															$GastoStr = $Gasto;

															if($Gasto==-1){
																$GastoStr="<font color='red'>INDEFINIDO</font>";
															}

															$CentroQuebra = $CentroSeq."_".$Gasto;


															//echo "[".$TipoMaterial."]";
															# Armazena array de movimentações de almoxarifado que serão processadas depois #
															if($TipoMaterial == "C"){
																$Depois[] = $Almoxarifado."_799_77_".sprintf("%02s",$Gasto)."_".$Ano."_".$Mes."_".$Dia."_".$TipoMov."_".$CentroSeq."_".$Movpost."_".$TipoMaterial;
															}elseif($TipoMaterial == "P"){
																$Depois[] = $Almoxarifado."_800_77_".$Gasto."_".$Ano."_".$Mes."_".$Dia."_".$TipoMov."_".$CentroSeq."_".$Movpost."_".$TipoMaterial;
															}


															/*
															switch ($Gasto){
																	case 3:
																	case 37:
																	case 6:
																	case 30:
																			$Depois[] = $Almoxarifado."_799_77_".sprintf("%02s",$Gasto)."_".$Ano."_".$Mes."_".$Dia."_".$TipoMov."_".$CentroSeq."_".$Movpost."_".$Flag;
																			break;
																	case 27:
																			$Depois[] = $Almoxarifado."_800_77_".$Gasto."_".$Ano."_".$Mes."_".$Dia."_".$TipoMov."_".$CentroSeq."_".$Movpost."_".$Flag;
																			break;
															}
															*/


															#Inverte o sentido para as movimentações que serão processadas agora, do ponto de vista do Centro de Custo #
															if($TipoMov == 'S')     $TipoMov = 'E';
															elseif($TipoMov == 'E') $TipoMov = 'S';

															if(!$SetAlmox){
																	$sqlalmox = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
																	$resalmox = $db->query($sqlalmox);
																	if( PEAR::isError($resalmox) ){
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlalmox");
																	}else{
																			$rowalmox     = $resalmox->fetchRow();
																			$AlmoxDesc    = $rowalmox[0];
																			$EscreveAlmox = 1;
																	}
																	$AlmoxarifadoSet = 1;
																	$Escreve  = 1;
																	$SetAlmox = 1;
															}

															# Se for uma movimentação de requisição processa agora o CC, deixando para depois o Almoxarifado (799) #
															if($Flag == "1"){
																	# Bloco que carrega as mudanças de Centro de Custo #
																	if( ($CentroQuebra != $CentroQuebraAnt) or ($Escreve == 1) ){
																			# Se ao trocar o centro de custo no loop, uma coluna tiver ficado sem movimentação de saída e aberta, cria a movimentação zerada, e fecha a tabela #
																			if($JaRodou and $FlagMov == 'E'){
																					//echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=3>";
																					echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																					echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																					echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>&nbsp;</B></td>\n";
																					echo "</tr>\n";
																					$FlagMov = 'S';
																					$Inicio = 0;
																			}
																			$sqlcc  = "SELECT CCENPOCORG, CCENPOUNID, CCENPONRPA, CCENPOCENT, CCENPODETA ";
																			$sqlcc .= "  FROM SFPC.TBCENTROCUSTOPORTAL WHERE CCENPOSEQU = $CentroSeq";
																			$rescc = $db->query($sqlcc);
																			if( PEAR::isError($rescc) ){
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlcc");
																			}else{
																					$rowcc  = $rescc->fetchRow();
																					$Orgao        = $rowcc[0];
																					$Unidade      = $rowcc[1];
																					$RPA          = $rowcc[2];
																					$Centro       = $rowcc[3];
																					$Deta         = $rowcc[4];
																					$EscreveCC    = 1;
																			}
																			$CentroQuebraAnt = $CentroQuebra;
																			$Escreve = 0;
																	}

																	# Busca somatório das movimentações da data especificada do loop no Oracle #

																	$sqloracle  = "SELECT SUM(VMOVCUREQU) FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
																	$sqloracle .= " WHERE DEXERCANOR = $Ano ";
																	$sqloracle .= "   AND AMOVCUMESM = $Mes ";
																	$sqloracle .= "   AND AMOVCUDIAM = $Dia ";
																	$sqloracle .= "   AND CORGORCODI = $Orgao ";
																	$sqloracle .= "   AND CUNDORCODI = $Unidade ";
																	$sqloracle .= "   AND CRPAAACODI = $RPA ";
																	$sqloracle .= "   AND CCENCPCODI = $Centro ";
																	$sqloracle .= "   AND CDETCPCODI = $Deta ";
																	$sqloracle .= "   AND CESPCPCODI = $Gasto ";
																	$sqloracle .= "   AND CMOVCUALMO = $Almoxarifado ";
																	$sqloracle .= "   AND FMOVCULANC = '$TipoMov' ";
																	$resoracle = $dbora->query($sqloracle);
																	if( PEAR::isError($resoracle) ){
																			$dbora->disconnect();
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqloracle");
																	}else{
																			$roworacle = $resoracle->fetchRow();
																			$Movoracle = $roworacle[0];

																			if( ($Consist == 'T') or (abs($Movpost - $Movoracle) > $Tolerancia) ){

																					# Seta flag afirmando que existiu resultados impressos #
																					$Ocorrencias = 1;

																					# Bloco que imprime o almoxarifado #
																					if($EscreveAlmox == 1){
																							echo "<tr>\n";
																							echo "	<td align=\"center\" bgcolor=\"#BFDAF2\" colspan=\"7\" class=\"titulo3\">".$AlmoxDesc."</td>\n";
																							echo "</tr>\n";
																							$EscreveAlmox = 0;
																					}

																					# Bloco que imprime as mudanças de CC #
																					if($EscreveCC == 1){
																							$Url = "ConsMovimentacaoCompararCustoDetalhe.php?Orgao=$Orgao&Unidade=$Unidade&RPA=$RPA&Centro=$Centro&Deta=$Deta&Gasto=$GastoStr&TipoMaterial=$TipoMaterial";
																							if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																							echo "<tr>\n";
																							echo "	<td align=\"center\" bgcolor=\"#DDECF9\" colspan=\"7\" class=\"titulo3\">";
																							echo "		<a href=\"javascript:AbreJanela('$Url',600,320);\"><font color=\"#000000\">Org. $Orgao / Unid. $Unidade / RPA $RPA / Centro Custo $Centro / Func. de Gov. $Deta / Item Gasto $Gasto</font></a>";
																							echo "	</td>\n";
																							echo "</tr>\n";
																							$EscreveCC = null;
																							echo "<tr>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"CENTER\">DATA</td>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"17%\" align=\"CENTER\">ENTRADA ESTOQUE</td>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"17%\" align=\"CENTER\">ENTRADA CUSTO</td>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\" align=\"CENTER\">VÁLIDO</td>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"17%\" align=\"CENTER\">SAÍDA ESTOQUE</td>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"17%\" align=\"CENTER\">SAÍDA CUSTO</td>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\" align=\"CENTER\">VÁLIDO</td>\n";
																							echo "</tr>\n";
																					}
																					if($TipoMov == "S"){
																							if($FlagMov == 'S'){
																									if ($Inicio==''){
																											# Achou uma saída, mas não houve entrada para este dia, então imprime uma coluna com 0 para a entrada #
																											echo "<tr>\n";
																											echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\">$Dia/$Mes/$Ano</td>\n";
																											//echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=3>";
																											echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																											echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																											echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>&nbsp;</B></td>\n";
																									}
																									# E depois imprime a saída encontrada #
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">".converte_valor($Movpost)."</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">".converte_valor($Movoracle)."</td>\n";
																									if(abs($Movpost - $Movoracle) > $Tolerancia) echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><FONT COLOR=red><B>NÃO</B></font></td>\n";
																									else echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>SIM</B></td>\n";
																									echo "</tr>\n";
																									# Imprimiu saída, seta Flag #
																									$FlagMov = 'S';
																							}else{
																									if ($Inicio == 0){
																											# Achou uma saída, mas não houve entrada para este dia, então imprime uma coluna com 0 para a entrada #
																											echo "<tr>\n";
																											echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\">$Dia/$Mes/$Ano</td>\n";
																											//echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=3>";
																											echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																											echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																											echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>&nbsp;</B></td>\n";
																									}
																									# E imprime só a saída encontrada #
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">".converte_valor($Movpost)."</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">".converte_valor($Movoracle)."</td>\n";
																									if(abs($Movpost - $Movoracle) > $Tolerancia) echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><FONT COLOR=red><B>NÃO</B></font></td>\n";
																									else echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>SIM</B></td>\n";
																									echo "</tr>\n";
																									# Imprimiu entrada e saída, seta flag com saída para esperar uma nova entrada #
																									$FlagMov = 'S';
																							}
																							$Inicio = 0;
																					}elseif($TipoMov == "E"){
																							if($FlagMov == 'E' and $JaRodou == 1){
																									# Não imprimiu saída na data anterior, imprime 0, fecha a coluna e abre nova coluna para a entrada da data atual #
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>&nbsp;</B></td>\n";
																									echo "</tr>\n";
																									# Imprime a entrada #
																									echo "<tr>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\">$Dia/$Mes/$Ano</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">".converte_valor($Movpost)."</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">".converte_valor($Movoracle)."</td>\n";
																									if(abs($Movpost - $Movoracle) > $Tolerancia) echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><FONT COLOR=red><FONT COLOR=red><B>NÃO</B></font></font></td>\n";
																									else echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>SIM</B></td>\n";
																									# Imprimiu entrada, seta Flag #
																									$FlagMov = 'E';
																							}else{
																									# Imprime só a entrada encontrada #
																									echo "<tr>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\">$Dia/$Mes/$Ano</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">".converte_valor($Movpost)."</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">".converte_valor($Movoracle)."</td>\n";
																									if(abs($Movpost - $Movoracle) > $Tolerancia) echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><FONT COLOR=red><FONT COLOR=red><B>NÃO</B></font></font></td>\n";
																									else echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>SIM</B></td>\n";
																									# Imprimiu entrada, seta Flag #
																									$FlagMov = 'E';
																							}
																							$Inicio = 1;
																					}

																			}
																			$JaRodou = 1;
																	}
															}
													}
													if($JaRodou and $FlagMov == 'E'){
															echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
															echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
															echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>&nbsp;</B></td>\n";
															echo "</tr>\n";
															$FlagMov = 'S';
													}

													# Se houver almoxarifado escrito, ordena, processa e limpa o array de movimentações do almoxarifado #
													if($SetAlmox and $Exibicao == 'S'){
															# Ordena array e agrupa datas iguais #

															$Depois = Agrupa($Depois);

															$FlagMov = 'S';
															$DataForAnterior = "";
															foreach($Depois as $Itens){
																	$ItensArray = explode("_",$Itens);
																	$AlmoxarifadoFor = $ItensArray[0];
																	$CentroFor       = $ItensArray[1];
																	$DetaFor         = $ItensArray[2];
																	$GastoFor        = (int)$ItensArray[3];
																	$AnoFor          = $ItensArray[4];
																	$MesFor          = $ItensArray[5];
																	$DiaFor          = $ItensArray[6];
																	$TipoMovFor      = $ItensArray[7];
																	$DataFor = "$DiaFor/$MesFor/$AnoFor";
																	# O centro $CentroSeq só está no array para efeito de ordenação e quebra #
																	$MovpostFor      = $ItensArray[9];
																	$TipoMaterial		= $ItensArray[10];
																	$CentroQuebraFor = $CentroFor."_".$DetaFor."_".$GastoFor;
																	//echo "!!![".$DataFor."][".$MovpostFor."]!!!";
																	# Busca somatório das movimentações da data especificada do loop no Oracle #

																	$sqloracleFor  = "SELECT SUM(VMOVCUREQU) FROM SFCP.TBMOVCUSTOALMOXARIFADO ";
																	$sqloracleFor .= " WHERE DEXERCANOR = $AnoFor ";
																	$sqloracleFor .= "   AND AMOVCUMESM = $MesFor ";
																	$sqloracleFor .= "   AND AMOVCUDIAM = $DiaFor ";
																	$sqloracleFor .= "   AND CCENCPCODI = $CentroFor ";
																	$sqloracleFor .= "   AND CDETCPCODI = $DetaFor ";
																	$sqloracleFor .= "   AND CESPCPCODI = $GastoFor ";
																	$sqloracleFor .= "   AND CMOVCUALMO = $AlmoxarifadoFor ";
																	$sqloracleFor .= "   AND FMOVCULANC = '$TipoMovFor' ";


																	//echo "[".$sqloracleFor."]";


																	$resoracleFor = $dbora->query($sqloracleFor);
																	if( PEAR::isError($resoracleFor) ){
																			$dbora->disconnect();
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqloracleFor");
																	}else{
																			$roworacleFor = $resoracleFor->fetchRow();
																			$MovoracleFor = $roworacleFor[0];

																			# Bloco que carrega as mudanças de Centro de Custo #
																			if($CentroQuebraFor != $CentroQuebraForAnt){
																					# Se ao trocar o centro de custo no loop, uma coluna tiver ficado sem movimentação de saída e aberta, cria a movimentação zerada, e fecha a tabela #
																					if($JaRodou and $FlagMov == 'E'){
																							echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																							echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																							echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>&nbsp;</B></td>\n";
																							echo "</tr>\n";
																							$FlagMov = 'S';
																					}
																					$sqlcc  = "SELECT CCENPOCORG, CCENPOUNID, CCENPONRPA, CCENPOCENT, CCENPODETA ";
																					$sqlcc .= "  FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBALMOXARIFADOORGAO ALO ";
																					$sqlcc .= " WHERE CEN.CCENPOCENT = $CentroFor AND CEN.CCENPODETA = $DetaFor ";
																					$sqlcc .= "   AND CEN.CORGLICODI = ALO.CORGLICODI ";
																					$sqlcc .= "   AND ALO.CALMPOCODI = $AlmoxarifadoFor ";
																					$rescc = $db->query($sqlcc);
																					if( PEAR::isError($rescc) ){
																							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlcc");
																					}else{
																							$rowcc      = $rescc->fetchRow();
																							$OrgaoFor   = $rowcc[0];
																							$UnidadeFor = $rowcc[1];
																							$RPAFor     = $rowcc[2];
																							$CentroFor  = $rowcc[3];
																							$DetaFor    = $rowcc[4];
																							$EscreveCC    = 1;
																					}
																					$CentroQuebraForAnt = $CentroQuebraFor;
																					$Escreve = 0;
																			}

																			if( ($Consist == 'T') or (abs($MovpostFor - $MovoracleFor) > $Tolerancia) ){
																					# Seta flag afirmando que existiu resultados impressos #
																					$Ocorrencias = 1;

																					# Bloco que imprime o almoxarifado #
																					if($EscreveAlmox == 1){
																							echo "<tr>\n";
																							echo "	<td align=\"center\" bgcolor=\"#BFDAF2\" colspan=\"7\" class=\"titulo3\">".$AlmoxDesc."</td>\n";
																							echo "</tr>\n";
																							$EscreveAlmox = 0;
																					}

																					# Bloco que imprime as mudanças de CC #
																					if($EscreveCC == 1){
																							$Url = "ConsMovimentacaoCompararCustoDetalhe.php?Orgao=$OrgaoFor&Unidade=$UnidadeFor&RPA=$RPAFor&Centro=$CentroFor&Deta=$DetaFor&Gasto=$GastoFor&TipoMaterial=$TipoMaterial";
																							if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																							echo "<tr>\n";
																							echo "	<td align=\"center\" bgcolor=\"#DDECF9\" colspan=\"7\" class=\"titulo3\">";
																							echo "		<a href=\"javascript:AbreJanela('$Url',600,320);\"><font color=\"#000000\">Org. $OrgaoFor / Unid. $UnidadeFor / RPA $RPAFor / Centro Custo $CentroFor / Func. de Gov. $DetaFor / Item Gasto $GastoFor</font></a>";
																							echo "	</td>\n";
																							echo "</tr>\n";
																							$EscreveCC = null;
																							echo "<tr>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"CENTER\">DATA</td>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"17%\" align=\"CENTER\">ENTRADA ESTOQUE</td>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"17%\" align=\"CENTER\">ENTRADA CUSTO</td>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\" align=\"CENTER\">VÁLIDO</td>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"17%\" align=\"CENTER\">SAÍDA ESTOQUE</td>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"17%\" align=\"CENTER\">SAÍDA CUSTO</td>\n";
																							echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"11%\" align=\"CENTER\">VÁLIDO</td>\n";
																							echo "</tr>\n";
																					}

																					if($TipoMovFor == "S"){
																							$DataNova = false;
																							if($DataFor != $DataForAnterior){
																								$DataNova = true;
																							}
																							if($DataNova and $FlagMov == 'E'){
																								# Data mudou e entrada da data anterior está aberta. precisa imprimir a saída.
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>&nbsp;</B></td>\n";
																									echo "</tr>\n";

																							}
																							if($FlagMov == 'S' or $DataNova){
																									# Achou uma saída, mas não houve entrada para este dia, então imprime uma coluna com 0 para a entrada #
																									echo "<tr>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\">$DiaFor/$MesFor/$AnoFor</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>&nbsp;</B></td>\n";

																							}
																							# E imprime só a saída encontrada #
																							echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">".converte_valor($MovpostFor)."</td>\n";
																							echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">".converte_valor($MovoracleFor)."</td>\n";
																							if(abs($MovpostFor - $MovoracleFor) > $Tolerancia) echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><FONT COLOR=red><B>NÃO</B></font></td>\n";
																							else echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>SIM</B></td>\n";
																							echo "</tr>\n";
																							# Imprimiu entrada e saída, zera a flag de entrada para esperar uma nova entrada #
																							$FlagMov = 'S';

																					}elseif($TipoMovFor == "E"){
																							if($FlagMov == 'E' and $JaRodou == 1){
																									# Não imprimiu saída na data anterior, imprime 0, fecha a coluna e abre nova coluna para a entrada da data atual #
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																									echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>&nbsp;</B></td>\n";
																									echo "</tr>\n";

																							}
																							# Imprime só a entrada encontrada (precisa imprimir a saida) #
																							echo "<tr>\n";
																							echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\">$DiaFor/$MesFor/$AnoFor</td>\n";
																							echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">".converte_valor($MovpostFor)."</td>\n";
																							echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">".converte_valor($MovoracleFor)."</td>\n";
																							if(abs($MovpostFor - $MovoracleFor) > $Tolerancia) echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><FONT COLOR=red><B>NÃO</B></font></td>\n";
																							else echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>SIM</B></td>\n";
																							# Imprimiu entrada, seta Flag #
																							$FlagMov = 'E';

																					}
																			}
																			$JaRodou = 1;
																	}
																	$DataForAnterior = $DataFor; //Guarda a data da iteração para comparação na próxima iteração
															}
															# Já fora do loop, caso tenha finalizado com uma entrada, sem uma saída, é necessário colocar uma coluna de saída zerada e fechar a linha #
															if($JaRodou and $FlagMov == 'E'){
																	echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																	echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"right\" width=\"20%\">&nbsp;</td>\n";
																	echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"CENTER\" width=\"10%\"><B>&nbsp;</B></td>\n";
																	echo "</tr>\n";
															}
													}
											}
											$db->disconnect;
											$dbora->disconnect;
									}
									# Imprime mensagem, se, mesmo o select retornando valores, se eles forem todos filtrados, que nada retornou #
									if($Ocorrencias == 0){
											echo "<tr>\n";
											echo "	<td class=\"textonormal\">\n";
											echo "		Pesquisa sem Ocorrências.\n";
											echo "	</td>\n";
											echo "</tr>\n";
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
<script language="JavaScript">
	Aguarde.style.visibility='hidden';
</script>
</html>
