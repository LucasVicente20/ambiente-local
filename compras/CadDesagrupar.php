<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadDesagrupar.php
# Autor:    Gladstone Barbosa
# Data:     02/01/2012
# Objetivo: Desagrupar Solicitacoes do sistema
#-------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: José Francisco <jose.francisco@pitang.com>
# Data:     30/05/2014 	- [CR121776]: REDMINE 14 (P4)

$programa = "CadDesagrupar.php";

# Acesso ao arquivo de funções #
require_once("funcoesCompras.php");

# Executa o controle de segurança #
session_start();

Seguranca();

AddMenuAcesso( '/compras/ConsAcompSolicitacaoCompra.php' );

# Abrindo a Conexão
$db   = Conexao();

$Orgao = '';

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao          = filter_input(INPUT_POST, 'Botao');
    $DataIni        = filter_input(INPUT_POST, 'DataIni');
    $Orgao	            = filter_input(INPUT_POST, 'Orgao');
    $Situacao           = filter_input(INPUT_POST, 'Situacao');
    $DataFim            = filter_input(INPUT_POST, 'DataFim');
	$RadioIdSolicitacao = $_POST['idSolicitacao'];

	if( $DataIni != "" ){ $DataIni = FormataData($DataIni); }
	if( $DataFim != "" ){ $DataFim = FormataData($DataFim); }

	if (isset($Botao, $Orgao, $Situacao, $DataIni, $DataFim)) {
	    $_SESSION['Botao'] = $Botao;
	    $_SESSION['Orgao'] = $Orgao;
	    $_SESSION['Situacao'] = $Situacao;
	    $_SESSION['DataIni'] = $DataIni;
	    $_SESSION['DataFim'] = $DataFim;
	}
} else {
	$Mensagem     = urldecode($_GET['Mensagem']);
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

if ($Botao == "Limpar") {
	header("location: ".$programa);
	exit;
}

if ($Botao == "Pesquisar" || $Botao == "Voltar") {
    # Critica dos Campos #
		$RadioIdSolicitacao = "";
		$boolOrgaoGestor = false;
		$boolPesquisar = true;
		$Mens     = 0;
		$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"formulario");

		if ($MensErro != "") {
		    adicionarMensagem("<a href='javascript:formulario.DataIni.focus();' class='titulo2'> $MensErro </a><br>", $GLOBALS["TIPO_MENSAGEM_ERRO"]);
  			$boolPesquisar = false;
		} else {
			if($DataIni==""){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.".$Programa.".DataIni.focus();\" class=\"titulo2\">Data Inicial inválida.</a><br>";
			}
			if($DataFim==""){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.".$Programa.".DataFim.focus();\" class=\"titulo2\">Data Final inválida.</a><br>";
			}
			if ( (DataInvertida($DataIni) > DataAtual()) and ($Mensagem == "") ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.".$Programa.".DataIni.focus();\" class=\"titulo2\">Data Inicial maior que a Data Atual</a><br>";
			}

		}

		if( $Situacao == "" ){
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.formulario.Situacao.focus();\" class=\"titulo2\">Informe a Situação</a><br>";
  			$boolPesquisar = false;
		}
		if( $Orgao == "" ){
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.formulario.Orgao.focus();\" class=\"titulo2\">Informe o Orgão</a><br>";
  			$boolPesquisar = false;
		}
}

if( $Botao == "Desagrupar" ){
	# Critica dos Campos #
		$boolPesquisar = true;
		$boolOrgaoGestor = true;
		$Mens     = 0;
		if ( $Orgao == "" ){
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.formulario.Orgao.focus();\" class=\"titulo2\">Informe o Orgão</a><br>";
			$boolPesquisar = false;
			$boolOrgaoGestor = false;
		}
		if ( $RadioIdSolicitacao == "" & $boolPesquisar == true ){
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.formulario.Orgao.focus();\" class=\"titulo2\">Selecione o grupo que deseja desagrupar</a><br>";
			$boolPesquisar = true;
			$boolOrgaoGestor = false;
		}
		$Botao = "Pesquisar";
}

if ( $Botao == "ConfirmarDesagrupamento"){

	if ( isset($RadioIdSolicitacao) ){

		$intCodUsuario = $_SESSION['_cusupocodi_'];
		$sql = "UPDATE sfpc.tbsolicitacaocompra SET tsolcoulat = now() , cusupocod1 = $intCodUsuario where
		csolcosequ IN ( select csolcosequ  FROM sfpc.tbagrupasolicitacao WHERE  CAGSOLSEQU=$RadioIdSolicitacao )";
		$res = executarTransacao($db, $sql);

		$sql = "DELETE FROM SFPC.TBAGRUPASOLICITACAO WHERE CAGSOLSEQU=$RadioIdSolicitacao";
		$res = executarTransacao($db, $sql);
		if( PEAR::isError($res) ){
			$CodErroEmail  = $res->getCode();
			$DescErroEmail = $res->getMessage();
			var_export($DescErroEmail);
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		}

		finalizarTransacao($db);

		$Mens      = 1;
		$Tipo      = 1;
		$Mensagem .= "<a href=\"javascript:document.formulario.Orgao.focus();\" class=\"titulo2\">Solicitação desagrupada com Sucesso</a>";
		unset($RadioIdSolicitacao);
		$Botao = "Pesquisar";
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
		//Pega o atributu ID que é a sequencia da solicitacao
		var seq = $(this).attr("id");
		//Ver a string dele (+ ou -)
		var valAtual = $(this).html();
		//Se for + mostra todas as tr que tem as classe 'opdetalhe' e com a 'seq' clicada
		if(valAtual=="+"){
				$(this).html("-");
				$(".opdetalhe."+seq).show();
		//Se for - esconde todas as tr que tem as classe 'opdetalhe' e com a 'seq' clicada
		}else{
				$(this).html("+");
				$(".opdetalhe."+seq).hide();
		}
	});
});
</script>

<form action="<?=$programa?>" method="post" name="formulario">
<br><br><br><br><br>
<table width="100%" cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Compras > Solicitação > Desagrupar
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
						 DESAGRUPAR - SOLICITAÇÃO DE COMPRA E CONTRATAÇÃO (SCC)
					</td>
				</tr>
				<tr>
					<td align="left" class="textonormal" colspan="4">
						 preencha os dados abaixo para efetuar a pesquisa e clique no grupo desejado para desagrupar.
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<table border="0" width="100%" summary="">
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Órgão*</td>
								<td class="textonormal">
									<select name="Orgao" class="textonormal">
										<option value="">Selecione um Órgao...</option>
										<option <?php if($Orgao=="TODOS"){echo "selected='selected'";}?> value="TODOS">Todos</option>
										<?php

										$sql  = "SELECT B.CORGLICODI , B.EORGLIDESC ";
										$sql .= "  FROM  SFPC.TBORGAOLICITANTE B ";
										$sql .= "  WHERE B.FORGLISITU = 'A'  ";
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

							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Situação*</td>
								<td class="textonormal">
								<input name="Situacao" type="hidden" value="7">PARA ENCAMINHAMENTO
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Período*</td>
								<td class="textonormal">
									<?php
									$DataMes = DataMes();
									if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
									if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
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
				if( $boolPesquisar ){
						# Busca os Dados da Tabela Grupo de Solicitação  de acordo com o filtro

						$sql1 = "SELECT DISTINCT (CAGSOLSEQU) AS GRUPO
						FROM
							SFPC.TBAGRUPASOLICITACAO AS GRU
						JOIN
							SFPC.TBSOLICITACAOCOMPRA AS SOL
								ON GRU.CSOLCOSEQU = SOL.CSOLCOSEQU
						WHERE
							CAGSOLSEQU IS NOT NULL";

						//Filtrando Pelo orgao
						if( $Orgao != "TODOS" ){
							$sql1 .= " AND SOL.CORGLICODI = ".$Orgao ;
						}

						//Filtrando Pela Situação
						if(SoNumeros($Situacao)) {
							$sql1 .= " AND  SOL.CSITSOCODI = $Situacao ";
						}

						if( $DataIni != "" and $DataFim != "" ){
							$sql1 .= " AND DATE(SOL.TSOLCODATA)  >= '".DataInvertida($DataIni)."' AND DATE(SOL.TSOLCODATA)  <= '".DataInvertida($DataFim)."' ";
						}
						if( isset($RadioIdSolicitacao) & is_numeric($RadioIdSolicitacao) ){
							$sql1 .= " AND GRU.CAGSOLSEQU = $RadioIdSolicitacao " ;
						}

						$sql = "SELECT
						SOL.CSOLCOSEQU,
						SOL.TSOLCODATA,
						SOL.CORGLICODI,
						ORG.EORGLIDESC,
						SOL.CSITSOCODI,
						SSO.ESITSONOME,
						GRU.CAGSOLSEQU,
						GRU.FAGSOLFLAG,
						GRU.TAGSOLULAT,
						CEN.ECENPODESC,
						CEN.ECENPODETA
					FROM
						SFPC.TBSOLICITACAOCOMPRA AS SOL
					JOIN
						SFPC.TBORGAOLICITANTE AS ORG
							ON SOL.CORGLICODI = ORG.CORGLICODI
					JOIN
						SFPC.TBSITUACAOSOLICITACAO AS SSO
							ON SOL.CSITSOCODI = SSO.CSITSOCODI
					JOIN
						SFPC.TBAGRUPASOLICITACAO AS GRU
							ON SOL.CSOLCOSEQU = GRU.CSOLCOSEQU
					JOIN
						SFPC.TBCENTROCUSTOPORTAL AS CEN
							ON SOL.CCENPOSEQU = CEN.CCENPOSEQU
					WHERE
						SOL.CTPCOMCODI = 2
						AND SOL.FSOLCORGPR = 'S'
						AND GRU.CAGSOLSEQU IN ($sql1)";
						//Se $RadioIdSolicitacao = true já está tela de confirmação
						if ( isset($RadioIdSolicitacao) & is_numeric($RadioIdSolicitacao) ){
							$sql .= " ORDER BY GRU.TAGSOLULAT DESC , GRU.CAGSOLSEQU, ORG.CORGLICODI , SOL.CCENPOSEQU , SOL.CSOLCOSEQU DESC";
						}else{
							$sql .= " ORDER BY GRU.TAGSOLULAT DESC , GRU.CAGSOLSEQU, GRU.FAGSOLFLAG DESC, ORG.EORGLIDESC, SOL.CSOLCOSEQU DESC";
						}

						$res  = $db->query($sql);
						if( PEAR::isError($res) ){
								$CodErroEmail  = $res->getCode();
								$DescErroEmail = $res->getMessage();
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
						}else{
								$Qtd = $res->numRows();
								if (!isset($RadioIdSolicitacao) & !is_numeric($RadioIdSolicitacao) ){
									echo "<tr>\n";
									echo "	<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
									echo "</tr>\n";
								}
								if( $Qtd > 0 ){
									//Se for a tela de finalizar desagrupamento mostra o detalhamento
									if ( isset($RadioIdSolicitacao) & is_numeric($RadioIdSolicitacao) ){
									$estilotd = 'class="titulo3" align="center" bgcolor="#F7F7F7"';
									$estiloClasstd = 'class="textonormal" align="center" bgcolor="#F7F7F7"';
									$sqlIntens  = " SELECT I.CITESCSEQU, I.CMATEPSEQU , I.CSERVPSEQU, I.AITESCORDE,
												I.AITESCQTSO, I.VITESCUNIT, I.VITESCVEXE, M.EMATEPDESC, S.ESERVPDESC
											FROM
												SFPC.TBITEMSOLICITACAOCOMPRA I
											LEFT JOIN
												SFPC.TBMATERIALPORTAL M ON (M.CMATEPSEQU = I.CMATEPSEQU)
											LEFT JOIN
												SFPC.TBSERVICOPORTAL S ON (S.CSERVPSEQU = I.CSERVPSEQU)";
									$sqlIntens  .= " WHERE I.CSOLCOSEQU IN( SELECT csolcosequ FROM SFPC.tbagrupasolicitacao WHERE cagsolsequ = $RadioIdSolicitacao )  ORDER BY I.CMATEPSEQU , I.CSERVPSEQU  ASC";
									$resIntens  = $db->query($sqlIntens);
									if( PEAR::isError($resIntens) ){
										$CodErroEmail  = $resIntens->getCode();
										$DescErroEmail = $resIntens->getMessage();
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlIntens\n\n$DescErroEmail ($CodErroEmail)");
									}else{
										while( $LinhaItens = $resIntens->fetchRow() ){
											$intSeqIntem 		= $LinhaItens[0];
											$srtSeqMaterial 	= $LinhaItens[1];
											$strSeqServico 		= $LinhaItens[2];
											$strOrdem 			= $LinhaItens[3];
											$strQuantidade 		= $LinhaItens[4];
											$strValorEstimado 	= $LinhaItens[5];
											$strValorTotal 		= $LinhaItens[6];


											if( $srtSeqMaterial != "" ){
												$codRed 		= $srtSeqMaterial;
												$strDescricao 	= $LinhaItens[7];
												$strTipo 		= "CADUM";
											}else{
												$codRed 		= $strSeqServico;
												$strDescricao 	= $LinhaItens[8];
												$strTipo 		= "CADUS";
											}
											if( isset($listaIntens[$codRed]['codRed']) ){
												$listaIntens[$codRed]['strQuantidade'] += $strQuantidade;
											}else{
												$listaIntens[$codRed]['codRed'] = $codRed;
												$listaIntens[$codRed]['strDescricao'] =  $strDescricao;
												$listaIntens[$codRed]['strTipo'] =  $strTipo;
												$listaIntens[$codRed]['strQuantidade'] =  $strQuantidade;
											}


										}


									}

									?>
									<tr>
										<td align="center" bgcolor="#75ADE6" colspan="5" class="titulo3">DESAGRUPAR - SOLICITAÇÃO DE COMPRA E CONTRATAÇÃO (SCC)</td>
									</tr>
									<tr>
										<td align="left" bgcolor="#75ADE6" colspan="5" class="titulo3">Itens selecionados</td>
									</tr>
									<tr>
										<td style="background-color:#F1F1F1;" colspan="4">
											<table bordercolor="#75ADE6" border="0" cellspacing="" bgcolor="bfdaf2" width="100%" class="textonormal">
												<tr class="linhainfo">
													<td <?php echo $estilotd;?>>DESCRIÇÃO</td>
													<td <?php echo $estilotd;?>>TIPO</td>
													<td <?php echo $estilotd;?>>CÓD.RED</td>
													<td <?php echo $estilotd;?>>QUANTIDADE</td>
													<td <?php echo $estilotd;?>>VALOR ESTIMADO</td>
													<td <?php echo $estilotd;?>>VALOR TOTAL</td>
												</tr>
												<?php

												foreach ($listaIntens as $lista){
												?>
												<tr>
													<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $lista['strDescricao'];?></td>
													<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $lista['strTipo'];?></td>
													<td	<?php echo $estiloClasstd;?>>&nbsp;<?php echo $lista['codRed'];?></td>
													<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_quant($lista['strQuantidade']);?></td>
													<td <?php echo $estiloClasstd;?>>&nbsp;</td>
													<td <?php echo $estiloClasstd;?>>&nbsp;</td>
												</tr>
												<?php
												}
												?>
											</table>
										</td>
									</tr>
									<tr>
										<td align="center" bgcolor="#75ADE6" colspan="5" class="titulo3">Solicitações Agrupadas</td>
									</tr>



									<?php

									}
										$CodAgrupamentoAntes = "";
										$ContagemGrupo = 0;
										$DescOrgaoAntes = "";
										$DescCentroCustoAntes = "";
										while( $Linha = $res->fetchRow() ){

												$CodSolicitacao  = $Linha[0]; 			 // SOL.CSOLCOSEQU, /* CÓDIGO SEQUENCIAL DA SOLICITAÇÃO DE COMPRA */
												$DataSolicitacao = DataBarra($Linha[1]); // SOL.TSOLCODATA, /* DATA E HORA DA SOLICITAÇÃO DE COMPRA */
												$CodOrgao        = $Linha[2]; 			 // SOL.CORGLICODI, /* CÓDIGO DO ÓRGÃO */
												$DescOrgao  	 = $Linha[3]; 			 // ORG.EORGLIDESC, /* DESCRIÇÃO DO ÓRGÃO LICITANTE */
												$CodSituacao	 = $Linha[4];			 // SOL.CSITSOCODI, /* SITUAÇÃO ATUAL DA SOLICITAÇÃO */
												$DescSolicitacao = $Linha[5];			 // SSO.ESITSONOME, /* DESCRIÇÃO DA SOLICITAÇÃO DA LICITAÇÃO */
												$CodAgrupamento  = $Linha[6];			 // GRU.CAGSOLSEQU, /* CÓDIGO SEQUENCIAL DO AGRUPAMENTO DAS LICITAÇÕES */
												$FlagGrupo		 = $Linha[7]; 			 // GRU.FAGSOLFLAG, /* FLAG QUE INDICA A SCC COM O ÓRGÃO GESTOR RESPONSÁVEL PELO AGRUPAMENTO - S/N */
												$DataAgrupamento = DataBarra($Linha[8]); // GRU.TAGSOLULAT  /* DATA E HORA DA ÚLTIMA ATUALIZAÇÃO */
												$DescCentroCusto = $Linha[9];			 // CEN.ECENPODESC, /* DESCRIÇÃO DO CENTRO DE CUSTO SFPC */
												$DetaCentroCusto = $Linha[10];			 // CEN.ECENPODETA, /* DESCRIÇÃO DO DETALHAMENTO DO CENTRO DE CUSTO SFPC */

												//Se $RadioIdSolicitacao estiver sido iniciado então ele monta a tabela de confirmaçao de desagrupamento
												if ( isset($RadioIdSolicitacao) & is_numeric($RadioIdSolicitacao) ){

													if ( $FlagGrupo == "S" ){
														$OrgaoGestor = $DescOrgao;
													}

													if( $DescOrgaoAntes != $DescOrgao ){
														echo "<tr class='linhaorgao'>\n";
														echo "	<td align=\"center\" bgcolor=\"#BFDAF2\" colspan=\"5\" class=\"titulo3\">$DescOrgao</td>\n";
														echo "</tr>\n";
													}
													$DescOrgaoAntes = $DescOrgao;

													if( $DescCentroCustoAntes != $DescCentroCusto ){
														echo "<tr class='linhacentro'>\n";
														echo "	<td align=\"center\" bgcolor=\"#DDECF9\" colspan=\"5\" class=\"titulo3\">$DescCentroCusto</td>\n";
														echo "</tr>\n";
														echo "<tr class='linhainfo'>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">SOLICITAÇÃO</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DETALHAMENTO</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DATA</td>\n";
														echo "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">SITUAÇÃO</td>\n";
														echo "</tr>\n";
													}
													$DescCentroCustoAntes = $DescCentroCusto;

													echo "<tr>\n";
													echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">";

													$programaSelecao =  "ConsAcompSolicitacaoCompra.php";
													$Url = $programaSelecao."?SeqSolicitacao=$CodSolicitacao&programa=$programa";
													$strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $CodSolicitacao);

													echo "<a href=\"$Url\"><font color=\"#000000\">".$strSolicitacaoCodigo."</font></a>
													<span style='cursor:pointer;margin-left:5px;margin-right:10px;' id='".$CodSolicitacao."' class='detalhar' onclick=''>+</span>";
													echo "	</td>\n";
													echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DetaCentroCusto</td>\n";
													echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DataSolicitacao</td>\n";
													echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DescSolicitacao</td>\n";
													echo "</tr>\n";

												} else {

													if ( $CodAgrupamentoAntes != $CodAgrupamento ){
														$ContagemGrupo++;
													?>
														<tr>
															<td align="left" bgcolor="#BFDAF2" colspan="5" class="titulo3">
															<?php
															if ($RadioIdSolicitacao != ""){
																$checked = "checked='checked'";
															}else{
																$checked = "";
															}
															?>
																<input <?php echo $checked; ?> type="radio" class="idSolicitacao" name="idSolicitacao" value="<?php echo $CodAgrupamento?>" />
																<?php echo $ContagemGrupo; ?> - Agrupamento - DATA: <?php echo $DataAgrupamento; ?>
															</td>
														</tr>
														<tr>
															<td class="titulo3" bgcolor="#F7F7F7">SOLICITAÇÃO</td>
															<td class="titulo3" bgcolor="#F7F7F7">ORGÃO</td>
															<td class="titulo3" bgcolor="#F7F7F7">DATA</td>
														</tr>
													<?php
													}
													$CodAgrupamentoAntes = $CodAgrupamento;

													echo "<tr>\n";
													echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">";

													$programaSelecao =  "ConsAcompSolicitacaoCompra.php";
													$Url = $programaSelecao."?SeqSolicitacao=$CodSolicitacao&programa=$programa";


													$strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $CodSolicitacao);

													echo "<a href=\"$Url\"><font color=\"#000000\">".$strSolicitacaoCodigo."</font></a>
															<span style='cursor:pointer;margin-left:5px;margin-right:10px;' id='".$CodSolicitacao."' class='detalhar' onclick=''>+</span>";
													echo "	</td>\n";
													echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DescOrgao</td>\n";
													echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DataSolicitacao</td>\n";
													echo "</tr>\n";
												}

												//Detalhamento da solicitacao

												//$estilotd = 'class="titulo3" bgcolor="#F7F7F7"';
												$estilotd = 'class="textoabason" align="center" bgcolor="#DCEDF7"';
												$estiloClasstd = 'class="textonormal" align="center" bgcolor="#bfdaf2"';

												$sqlIntens  = " SELECT I.CITESCSEQU, I.CMATEPSEQU, I.CMATEPSEQU, I.CSERVPSEQU, I.AITESCORDE,
												I.AITESCQTSO, I.VITESCUNIT, I.VITESCVEXE, M.EMATEPDESC, S.ESERVPDESC
											FROM
												SFPC.TBITEMSOLICITACAOCOMPRA I
											LEFT JOIN
												SFPC.TBMATERIALPORTAL M ON (M.CMATEPSEQU = I.CMATEPSEQU)
											LEFT JOIN
												SFPC.TBSERVICOPORTAL S ON (S.CSERVPSEQU = I.CSERVPSEQU)";
												$sqlIntens  .= " WHERE I.CSOLCOSEQU = $CodSolicitacao  ORDER BY I.CITESCSEQU ASC";

												$resIntens  = $db->query($sqlIntens);
												if( PEAR::isError($resIntens) ){
													$CodErroEmail  = $resIntens->getCode();
													$DescErroEmail = $resIntens->getMessage();
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlIntens\n\n$DescErroEmail ($CodErroEmail)");
												}else{
												?>
												<tr style="display:none;" class="opdetalhe <?php echo $CodSolicitacao;?>">
													<td style="background-color:#F1F1F1;" colspan="4">
														<table bordercolor="#75ADE6" border="1" bgcolor="bfdaf2" width="100%" class="textonormal">
															<tr>
																<td <?php echo $estilotd;?>>ORD</td>
																<td <?php echo $estilotd;?>>DESCRIÇÃO</td>
																<td <?php echo $estilotd;?>>TIPO</td>
																<td <?php echo $estilotd;?>>CÓD.RED</td>
																<td <?php echo $estilotd;?>>QUANTIDADE</td>
																<td <?php echo $estilotd;?>>VALOR ESTIMADO</td>
																<td <?php echo $estilotd;?>>VALOR TOTAL</td>
															</tr>
															<?php
																while( $LinhaItens = $resIntens->fetchRow() ){
																	$intSeqIntem 		= $LinhaItens[0];
																	$srtSeqMaterial 	= $LinhaItens[1];
																	$srtSeqServico 		= $LinhaItens[2];
																	$strOrdem 			= $LinhaItens[4];
																	$strQuantidade 		= $LinhaItens[5];
																	$strValorEstimado 	= $LinhaItens[6];
																	$strValorTotal 		= $LinhaItens[7];

																	if($srtSeqMaterial!=""){
																		$codRed 		= $srtSeqMaterial;
																		$strDescricao 	= $LinhaItens[8];
																		$strTipo 		= "CADUM";
																	}else{
																		$codRed 		= $strSeqServico;
																		$strDescricao 	= $LinhaItens[9];
																		$strTipo 		= "CADUS";
																	}

															?>
															<tr>
																<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $strOrdem;?></td>
																<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $strDescricao;?></td>
																<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo $strTipo;?></td>
																<td	<?php echo $estiloClasstd;?>>&nbsp;<?php echo $codRed;?></td>
																<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_quant($strQuantidade);?></td>
																<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_valor($strValorEstimado);?></td>
																<td <?php echo $estiloClasstd;?>>&nbsp;<?php echo converte_valor($strQuantidade*$strValorEstimado);?></td>
															</tr>
															<?php
																}
															?>
														</table>
													</td>
												</tr>
												<?php
												}//Fim do Else de Detalhamento
										}//Fim do While


										if ( $boolOrgaoGestor ){ ?>
										<tr>
											<td <?php echo $estilotd; ?> >
												<label>Órgão Gestor:</label>
											</td>
											<td class="textonormal" align="left" colspan="3">
												<label><?php echo $OrgaoGestor; ?></label>
											</td>
										</tr>
										<?php
										}
										?>
										<tr>
											<td class="textonormal" align="right" colspan="4">
											<?php if ( $boolOrgaoGestor ){ ?>
												<input class='idSolicitacao' name='idSolicitacao' value='<?php echo $CodAgrupamento;?>' type='hidden'/>
												<input type="button" name="ConfirmarDesagrupamento" value="Confirmar Desagrupamento" class="botao" onClick="javascript:enviar('ConfirmarDesagrupamento')" >
												<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Pesquisar')">
											<?php }else{?>
												<input type="button" name="Desagrupar" value="Desagrupar" class="botao" onClick="javascript:enviar('Desagrupar')">
											<?php } ?>
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
						}//Fim do Else
				}//Fim do If
				?>
			</table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
<?php $db->disconnect();?>
</html>
