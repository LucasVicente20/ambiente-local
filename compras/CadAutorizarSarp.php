<?php
#------------------------------------------------------
# Portal da DGCO
# Programa: CadAutorizarSarp.php
# Autor:  	Luiz Alves
# Data:		19/01/2012
# Objetivo:	Autorizar Sarp - Demanda Redmine #5070
#------------------------------------------------------
# OBS.:		Tabulação 2 epsaços
#------------------------------------------------------
# Alterado: José Francisco <jose.francisco@pitang.com>
# Data:     30/05/2014 	- [CR121776]: REDMINE 14 (P4)

$programa = "CadAutorizarSarp.php";

# Acesso ao arquivo de funções #
require_once("funcoesCompras.php");

# Executa o controle de segurança #
session_start();
Seguranca();


# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/compras/CadAutorizarSarpConfirmar.php' );
AddMenuAcesso( '/compras/ConsAcompSolicitacaoCompra.php' );

$Orgao = '';
# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao          = filter_input(INPUT_POST, 'Botao');
	$DataIni        = filter_input(INPUT_POST, 'DataIni');
	$Orgao	        = filter_input(INPUT_POST, 'Orgao');
	$Situacao       = filter_input(INPUT_POST, 'Situacao');
	$DataFim        = filter_input(INPUT_POST, 'DataFim');
	if ($DataIni != "") {
		$DataIni = FormataData($DataIni);
	}
	if ($DataFim != "") {
		$DataFim = FormataData($DataFim);
	}

	if (isset($Botao, $Orgao, $Situacao, $DataIni, $DataFim)) {
		$_SESSION['Botao'] = $Botao;
		$_SESSION['Orgao'] = $Orgao;
		$_SESSION['Situacao'] = $Situacao;
		$_SESSION['DataIni'] = $DataIni;
		$_SESSION['DataFim'] = $DataFim;
	}
	#
	if ($Botao == "Preautorizar" || $Botao == "AutorizarSarp") {
		$arrCheckSolicitacoes = $_POST['checkSolicitacoes'];
	}

	$intOrgaoAgrupador = $_POST["intOrgaoAgrupador"];
	$Tiposarp	  = $_POST['Tiposarp'];

} elseif ($_SERVER['REQUEST_METHOD'] == "GET") {
	$Mensagem     = urldecode($_GET['Mensagem']);
	$Tiposarp	  = $_GET['Tiposarp'];
	$Mens         = $_GET['Mens'];
	$Tipo         = $_GET['Tipo'];
	$_SESSION['Orgao'] = '';
	$_SESSION['Situacao'] = '';
	$_SESSION['DataIni'] = '';
	$_SESSION['DataFim'] = '';
}

if ($Botao == 'Voltar') {
    $_SESSION["carregarSelecionarDoSession"] = true;
} elseif ($Botao == "Imprimir") {
    $Solicitacao = filter_has_var(INPUT_POST, 'Solicitacao')
        ? filter_input(INPUT_POST, 'Solicitacao', FILTER_SANITIZE_NUMBER_INT)
        : null;
    $Url = "RelAcompanhamentoSCCPdf.php?Solicitacao=" . $Solicitacao;
    header("location: " . $Url);
    exit;
}

if ($_SESSION["carregarSelecionarDoSession"]) {
    $Botao        = $_SESSION['Botao'];
    $Orgao	  = $_SESSION['Orgao'];
    $Situacao     = $_SESSION['Situacao'];
    $DataIni      = $_SESSION['DataIni'];
    $DataFim      = $_SESSION['DataFim'];
    $_SESSION["carregarSelecionarDoSession"]=false;
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;


# Abrindo Conexao #
$db      = Conexao();

# Redireciona




if ($Botao == "Limpar") {
		header("location: ".$programa);
		exit;
} else

if ($Botao == "AutorizarSarp") {

	if(!isset($arrCheckSolicitacoes)){
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">Selecione no mínimo 1 solicitações.</a>";
        $Botao = "Pesquisar";
	}

	if ($Mens  <> 1  ) {
		$vetor=$arrCheckSolicitacoes;
		$chave=$vetor[0];
		header("location: CadAutorizarSarpConfirmar.php?chave=$chave");
		exit;
	}

}
if( $Botao == "Pesquisar" || $boolPesquisar == true || $Botao == "Voltar"){
	$boolPesquisar = true;
    # Critica dos Campos #
	    if ( empty($DataIni)  )  {
	        $MensErro="Informar Data Inicial";
			adicionarMensagem("<a href='javascript:formulario.DataIni.focus();' class='titulo2'> $MensErro </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
			$boolPesquisar = false;
	    }

	    if ( empty($DataFim)  )  {
	        $MensErro="Informar Data Final";
			adicionarMensagem("<a href='javascript:formulario.DataFim.focus();' class='titulo2'> $MensErro </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
			$boolPesquisar = false;
	    }


		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"formulario");
		if( $MensErro != "" ){
			adicionarMensagem("<a href='javascript:formulario.DataIni.focus();' class='titulo2'> $MensErro </a>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
			$boolPesquisar = false;
		}else{

			if(DataInvertida($DataIni)>DataAtual() || DataInvertida($DataFim)>DataAtual()){
				$Mens      = 1;
				$Tipo      = 2;
		  		$Mensagem .= "<a href=\"javascript:document.formulario.DataIni.focus();\" class=\"titulo2\">Informe data válida </a>";
				$boolPesquisar = false;
			}
		}

		if( $Situacao == "" ){
				    $Mens      = 1;
				    $Tipo      = 2;
		  			$Mensagem .= "<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">Informe Situação </a>";
					$boolPesquisar = false;
		}
		if( $Orgao == "" ){
				    $Mens      = 1;
				    $Tipo      = 2;
		  			$Mensagem .= "<a href=\"javascript:document.formulario.Orgao.focus();\" class=\"titulo2\">Informe Orgão </a>";
					$boolPesquisar = false;
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
<!--
function enviar(valor){
	document.formulario.Botao.value = valor;
	document.formulario.submit();
}
function AbreJanela(url,largura,altura){
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=15,top=15,width='+largura+',height='+altura);
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<script language="JavaScript">
$(document).ready(function(){
	//No click do botão detalhar
	$(".detalhar").live("click", function() {
		//Pega o atributo ID que é a sequencia da solicitacao
		var seq = $(this).attr("id");
		//Ver a string dele (+ ou -)
		var valAtual = $(this).html();
		//Se for + mostra todas as tr que tem as classe 'opdetalhe' e com a 'seq' clicada
		if(valAtual=="+"){
				//Volto para -
				$(this).html("-");
				$(".opdetalhe."+seq).show();
		//Se for - esconde todas as tr que tem as classe 'opdetalhe' e com a 'seq' clicada
		}else{
				//Volto para +
				$(this).html("+");
				$(".opdetalhe."+seq).hide();
		}
	});
});
</script>
<form action="<?php $programa?>" method="post" name="formulario">
<br><br><br><br><br>
<table width="100%" cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Compras > Solicitação > Autorizar SARP
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
	<tr>
		<td width="150"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
			<table   border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
						AUTORIZAR SARP - SOLICITAÇÃO DE COMPRA E CONTRATAÇÃO(SCC)
					</td>
				</tr>
				<tr>
					<td class="textonormal" colspan="4">
						<p align="justify">
							Preencha os dados abaixo para efetuar a pesquisa e clique no botão pesquisar.
						</p>
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<table border="0"  summary="">
							<tr>


							<!--     inicio da selecao dp comboBox de Orgão     -->

							<?php if ($_SESSION['_cgrempcodi_'] == 0 or $_SESSION['_fperficorp_'] == "S") { ?>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7"  height="20">Órgão*</td>



								<td class="textonormal">
									<select name="Orgao" class="textonormal">
										<option value="">Selecione um Órgão...</option>
										<?php
										//$db      = Conexao();
										$sql  = "SELECT DISTINCT A.CORGLICODI, B.EORGLIDESC ";
										$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
										$sql .= " WHERE A.CORGLICODI = B.CORGLICODI ";
										$sql .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
										$sql .= " ORDER BY B.EORGLIDESC";

										$res = $db->query($sql);


										if( PEAR::isError($res) ){
												$CodErroEmail  = $res->getCode();
												$DescErroEmail = $res->getMessage();
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
										}
										else{
												while( $Linha = $res->fetchRow() ){
														if( $Linha[0] == $Orgao){
								                          echo"<option value='".$Linha [0]."' selected>".$Linha[1]."</option>";
								                        }
									                    else{
								                            echo"<option value='".$Linha [0]."'>".$Linha [1]."</option>";

												        }
										        }
										}
										//$db->disconnect();
										?>
									</select>
								</td>
							</tr>
							<?php
							}else{
									echo "<td class=\"textonormal\" bgcolor=\"#DCEDF7\"   height=\"20\">Órgão</td>\n";
									//$db   = Conexao();


									if( ($_SESSION['_cgrempcodi_'] != 0 ) and ($_SESSION['_fperficorp_'] <> 'S')){
											$sql  = "SELECT DISTINCT B.CORGLICODI, B.EORGLIDESC ";
											$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
											$sql .= " WHERE A.CORGLICODI IS NOT NULL AND A.ACENPOANOE = ".date("Y")."";
											$sql .= "   AND A.CORGLICODI = B.CORGLICODI  ";
											$sql .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
											$sql .= "   AND A.CCENPOSEQU IN  ";
											$sql .= "        ( SELECT USU.CCENPOSEQU FROM SFPC.TBUSUARIOCENTROCUSTO USU ";
											$sql .= "       WHERE USU.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] .")";
											$sql .= " ORDER BY 1";
									}else{
											$sql  = "SELECT DISTINCT A.CORGLICODI, A.EORGLIDESC";
											$sql .= "  FROM SFPC.TBORGAOLICITANTE A ";
											$sql .= " WHERE A.FORGLISITU <> 'I' "; // Exibe órgãos ativos
											$sql .= " ORDER BY D.EORGLIDESC";
									}
									$res      = $db->query($sql);


									if( PEAR::isError($res) ){
											$CodErroEmail  = $res->getCode();
											$DescErroEmail = $res->getMessage();
											EmailErroDB('Erro de SQL', 'Erro de SQL', $res);
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCC\n\n$DescErroEmail ($CodErroEmail)");
									}else{
											$rowsorg = $res->numRows();
											if($rowsorg == 0){
													echo "Nenhum Centro de Custo Ativo associado ao usuário";
											}elseif( $rowsorg == 1 ){
													$Linha = $res->fetchRow();
													$Orgao=$Linha[0];
													?>
						                             <input type="hidden" name="Orgao" value="<?php echo $Orgao ?>">
						                            <?php

													$_SESSION['Orgao']	= $Linha[0];
													$DescOrgao				= $Linha[1];
													echo "<td class=\"textonormal\" >$DescOrgao</td>\n";
											} else {
												echo "<td class=\"textonormal\" >";
												echo "<select name=\"Orgao\" class=\"textonormal\"><option value=\"\">Selecione um Órgao...</option>";
												while( $Linha = $res->fetchRow() ){
													echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
												}
												echo "</select>";
												echo "</td>\n";
											}
									}
									//$db->disconnect();
							}
							?>
							<tr>







								<!--     final alteracao    -->
							</tr>

							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Situação*</td>
								<td class="textonormal">
									<select name="Situacao" class="textonormal">
										<?php
										$sql  = "SELECT
											CSITSOCODI,
											ESITSONOME
										FROM
											SFPC.TBSITUACAOSOLICITACAO
										WHERE
											CSITSOCODI in ( 3,4,5)
										ORDER BY ESITSONOME";

										$res = $db->query($sql);

										if( PEAR::isError($res) ){
											$CodErroEmail  = $res->getCode();
											$DescErroEmail = $res->getMessage();
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
										}else{
											while( $Linha = $res->fetchRow() ){
												if( $Situacao == $Linha[0]){
													$selected = "selected = 'selected'";
												}else{
													$selected = "";
												}
												echo "<option $selected value=\"$Linha[0]\">$Linha[1]</option>\n";
											}
										}
										$_SESSION['Situacao'] = $Situacao;
										?>
									</select>
								</td>

							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período*</td>
								<td class="textonormal">
									<?php
									$DataMes = DataMes();
									 if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
									 if( $DataFim == "" ){ $DataFim = date("d/m/Y");}
									$URLIni = "../calendario.php?Formulario=formulario&Campo=DataIni";
									$URLFim = "../calendario.php?Formulario=formulario&Campo=DataFim";
									?>
									<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
									&nbsp;a&nbsp;
									<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="textonormal" align="right" colspan="4">
						<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar')">
						<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
						<input type="hidden" name="Botao" value="">

					</td>
				</tr>

				<?php
				if( $boolPesquisar){

						# Busca os Dados da Tabela de Solicitação  de acordo com o filtro

						$sql  = "SELECT
							SOL.CSOLCOSEQU , SOL.ASOLCOANOS, SOL.CSOLCOCODI, SOL.TSOLCODATA, ORG.EORGLIDESC,
							CEN.ECENPODESC, CEN.ECENPODETA, SOL.CSITSOCODI, SSO.ESITSONOME, CEN.CCENPOCORG, CEN.CCENPOUNID , SOL.CORGLICODI
						FROM
							SFPC.TBSOLICITACAOCOMPRA SOL,  SFPC.TBORGAOLICITANTE ORG, SFPC.TBCENTROCUSTOPORTAL CEN,
							SFPC.TBSITUACAOSOLICITACAO SSO
						WHERE
							SOL.CORGLICODI = ORG.CORGLICODI
							AND CEN.CCENPOSEQU = SOL.CCENPOSEQU
							AND SOL.CSITSOCODI = SSO.CSITSOCODI
							AND SOL.CSITSOCODI = $Situacao
							AND  SOL.CTPCOMCODI = 5

						";



						if($Orgao!="TODOS"){
								$sql .= " AND CEN.CCENPOSEQU  = SOL.CCENPOSEQU AND SOL.CORGLICODI = ".$Orgao ;
						}
						if( $DataIni != "" and $DataFim != "" ){
								$sql .= "  AND to_char(SOL.TSOLCODATA,'YYYY-MM-DD')  >= '".DataInvertida($DataIni)."' AND to_char(SOL.TSOLCODATA,'YYYY-MM-DD') <= '".DataInvertida($DataFim)."' ";
						}
						$sql .= " ORDER BY ORG.EORGLIDESC, CEN.ECENPODESC, SOL.CSOLCOSEQU, SOL.ASOLCOANOS DESC ";



						$res  = $db->query($sql);

						//  echo "<p>$sql</p>";
						//  echo "<p> 	$Orgao </p>";
						//  exit;


						if( PEAR::isError($res) ){
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						}else{
								$Qtd = $res->numRows();
								echo "<tr>\n";
								if($Preautorizar){
									echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">SOLICITAÇÕES AGRUPADAS</td>\n";

								}else{
									echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
								}
								echo "</tr>\n";
								if( $Qtd > 0 ){
										$DescOrgaoAntes  = "";
										$DescCentroAntes = "";
										while( $Linha = $res->fetchRow() ){
												$SeqSolicitacao  = $Linha[0];
												$AnoSolicitacao  = $Linha[1];
												$Solicitacao     = $Linha[2];
												$Data            = DataBarra($Linha[3]);
												$DescOrgao       = $Linha[4];
												$DescCentro      = $Linha[5];
												$Detalhamento    = $Linha[6];
												$DescSituacao    = $Linha[8];
												$OrgaoSofin 	 = $Linha[9];
												$UnidadeSofin 	 = $Linha[10];
												$arrOrgId[] 	 =  $Linha[11];
												if( $DescOrgaoAntes != $DescOrgao ){
														echo "<tr class='linhaorgao'>\n";
														echo "	<td align=\"center\" bgcolor=\"#BFDAF2\" colspan=\"5\" class=\"titulo3\">$DescOrgao</td>\n";
														echo "</tr>\n";
												}
												if( $DescCentroAntes != $DescCentro ){
														echo "<tr class='linhacentro'>\n";
														echo "	<td align=\"center\" bgcolor=\"#DDECF9\" colspan=\"5\" class=\"titulo3\">$DescCentro</td>\n";
														echo "</tr>\n";
														echo "<tr class='linhainfo'>\n";

														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">SOLICITAÇÃO</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DETALHAMENTO</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DATA</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">SITUAÇÃO</td>\n";
														echo "</tr>\n";
												}

												echo "<tr class='linhasol'>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">";
												$programaSelecao =  "ConsAcompSolicitacaoCompra.php";
												$Url = $programaSelecao."?SeqSolicitacao=$SeqSolicitacao&programa=$programa";

												if (!in_array($Url,$_SESSION['GetUrl'])){
													$_SESSION['GetUrl'][] = $Url;
												}

												$strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $SeqSolicitacao);
												//$codSolAno =  $Linha[0]."/".$Linha[1];


												//se for Preautorizar todos vem marcados
												if($strSolicitacoes != ""){$opcheck = "checked='checked'";}else{$opcheck="";}
												echo "<input $opcheck class='checksoli' name='checkSolicitacoes[]' value='".$SeqSolicitacao."' type='radio'/>
														<a href=\"$Url\">
															<!-- Heraldo para Teste <font color=\"#000000\">".$strSolicitacaoCodigo."($SeqSolicitacao)</font> -->
															<font color=\"#000000\">".$strSolicitacaoCodigo."</font>

														</a><span style='cursor:pointer;margin-left:5px;margin-right:10px;' id='".$SeqSolicitacao."' class='detalhar' onclick=''>+</span>";
												echo "	</td>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Detalhamento</td>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Data</td>\n";
												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DescSituacao</td>\n";
												echo "</tr>\n";
												//Detalhamento da solicitacao
												$estilotd = 'class="textoabason" align="center" bgcolor="#DCEDF7"';
												$estilotd2 = 'class="textonormal" align="center"';
												$sqlIntens  = " SELECT I.CITESCSEQU , I.CMATEPSEQU , I.CMATEPSEQU , I.CSERVPSEQU , I.AITESCORDE , I.AITESCQTSO , I.VITESCUNIT , I.VITESCVEXE  , M.EMATEPDESC , S.ESERVPDESC FROM SFPC.TBITEMSOLICITACAOCOMPRA I LEFT JOIN SFPC.TBMATERIALPORTAL M ON (M.CMATEPSEQU = I.CMATEPSEQU) LEFT JOIN SFPC.TBSERVICOPORTAL S ON (S.CSERVPSEQU = I.CSERVPSEQU)";
												$sqlIntens  .= " WHERE I.CSOLCOSEQU = $SeqSolicitacao";


												$resIntens  = $db->query($sqlIntens);
												if( PEAR::isError($resIntens) ){
													$CodErroEmail  = $resIntens->getCode();
													$DescErroEmail = $resIntens->getMessage();
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlIntens\n\n$DescErroEmail ($CodErroEmail)");
												}else{
												?>
												<!-- Monta a tabela que será exibida ao clicar no link de detalhamento -->
												<tr style="display:none;" class="opdetalhe <?php echo $SeqSolicitacao;?>">
													<td style="background-color:#F1F1F1;" colspan="4">
														<table summary="" bgcolor="#bfdaf2" border="1" bordercolor="#75ADE6" width="100%">
														<tbody>
															<tr><td <?php echo $estilotd;?> >ORD</td><td <?php echo $estilotd;?>>DESCRIÇÃO</td><td <?php echo $estilotd;?>>TIPO</td><td <?php echo $estilotd;?>>CÓD.RED</td><td <?php echo $estilotd;?>>QUANTIDADE</td><td <?php echo $estilotd;?>>VALOR ESTIMADO</td><td <?php echo $estilotd;?>>VALOR TOTAL</td></tr>
															<?php
																while( $LinhaItens = $resIntens->fetchRow() ){
																	$intSeqIntem = $LinhaItens[0];
																	$srtSeqMaterial = $LinhaItens[1];
																	$srtSeqServico = $LinhaItens[2];
																	$strOrdem = $LinhaItens[4];
																	$strQuantidade = $LinhaItens[5];
																	$strValorEstimado = $LinhaItens[6];
																	$strValorTotal = $LinhaItens[7];



																	if($srtSeqMaterial!=""){
																		$codRed = $srtSeqMaterial;
																		$strDescricao = $LinhaItens[8];
																		$strTipo = "CADUM";
																	}else{
																		$codRed = $strSeqServico;
																		$strDescricao = $LinhaItens[9];
																		$strTipo = "CADUS";
																	}
															?>
															<tr>
																<td <?php echo $estilotd2;?>>&nbsp;<?php echo $strOrdem;?></td>
																<td <?php echo $estilotd2;?>>&nbsp;<?php echo $strDescricao;?></td>
																<td <?php echo $estilotd2;?>>&nbsp;<?php echo $strTipo;?></td>
																<td	<?php echo $estilotd2;?>>&nbsp;<?php echo $codRed;?></td>
																<td <?php echo $estilotd2;?>>&nbsp;<?php echo converte_quant($strQuantidade);?></td>
																<td <?php echo $estilotd2;?>>&nbsp;<?php echo converte_valor($strValorEstimado);?></td>
																<td <?php echo $estilotd2;?>>&nbsp;<?php echo converte_valor($strQuantidade*$strValorEstimado);?></td>
															</tr>
															<?php
																}
															?>
														</table>
													</td>
												</tr>
										<?php
												}
											$DescOrgaoAntes  = $DescOrgao;
											$DescCentroAntes = $DescCentro;
										}

										if($Botao=="Preautorizar"&&$strSolicitacoes!=""){

										?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Órgão Gestor*</td>
											<td colspan="3" class="textonormal">
												<select id="intOrgaoAgrupador" name="intOrgaoAgrupador" class="textonormal">
													<option value="">Selecione um Órgao...</option>
													<?php
													//Lista os órgãos que pode ser selecionado para ser o órgão gestor
													$strOrgaosSelecionados = implode(",",$arrOrgId);

													$sql  = "SELECT B.CORGLICODI , B.EORGLIDESC ";
													$sql .= "  FROM  SFPC.TBORGAOLICITANTE B ";
													$sql .= "  WHERE B.FORGLISITU = 'A' AND CORGLICODI IN ($strOrgaosSelecionados)  ";
													$sql .= " ORDER BY B.EORGLIDESC";

													$res = $db->query($sql);

													if( PEAR::isError($res) ){
														$CodErroEmail  = $res->getCode();
														$DescErroEmail = $res->getMessage();
														var_export($DescErroEmail);
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
													}else{
														while( $Linha = $res->fetchRow() ){
															if($Linha[0]==$Orgao){
																echo "<option selected='selected' value=\"$Linha[0]\">$Linha[1]</option>\n";
															}else{
																echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
															}

														}

													}
													?>
												</select>
											</td>
										</tr>

										<?php }?>
										<tr>
											<td class="textonormal" align="right" colspan="4">


										       <?php if ( $Situacao==5 ) {  ?>
									           	<input name="voltar" type="button" value="Autorizar Sarp" class="botao" onclick="javascript:enviar('AutorizarSarp')">
									           <?php } ?>
									           <?php if ( $Situacao==3 || $Situacao==4  ) {  ?>
									           	<input name="voltar" type="button" value="Retirar Autorização Sarp" class="botao" onclick="javascript:enviar('AutorizarSarp')">
									           <?php } ?>

									           <input name="voltar" type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
									           </form>
											</td>
										</tr>
								<?php
								}else{
										echo "<tr>\n";
										echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
										echo "	Pesquisa sem Ocorrências.\n";
										echo "	</td>\n";
										echo "</tr>\n";
								}
								echo "</table>\n";
						}

				}
				?>
			</table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>

</body>
</html>
<?php
$db->disconnect();
?>
