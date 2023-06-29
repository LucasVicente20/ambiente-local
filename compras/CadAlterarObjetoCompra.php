<?php
/**
 * Prefeitura do Recife
 * Portal de Compras
 * 
 * Programa: CadAlterarObjetoCompra.php
 * Autor:    Lucas Baracho
 * Data:     28/05/2019
 * -------------------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     20/12/2021
 * Objetivo: Tarefa Redmine 256887
 * -------------------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     04/12/2021
 * Objetivo: Tarefa Redmine 257267
 * -------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     26/01/2023
 * Objetivo: Tarefa Redmine 267990
 * -------------------------------------------------------------------------------------
 */

# Acesso ao arquivo de funções #
require_once 'funcoesCompras.php';
require_once '../funcoes.php';
require_once '../geral/funcoesBanco.php';

# Executa o controle de segurança #
session_start();
Seguranca();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $botao              = $_POST['Botao'];
    $InicioPrograma     = $_POST['InicioPrograma'];
    $CentroCusto        = $_POST['CentroCusto'];
    $Observacao         = strtoupper2(trim($_POST['Observacao']));
    $Objeto             = strtoupper2(trim($_POST['Objeto']));
    $sequencialIntencao = $_POST['sequencialIntencao'];
    $anoIntencao        = $_POST['anoIntencao'];    
    $Solicitacao        = $_POST['SeqSolicitacao']; // sequencial da solicitação usado pelo 'Manter'
    $Numero             = $_POST['Numero'];
    $DataSolicitacao    = $_POST['DataSolicitacao'];   
	$botao		        = $_POST['botao'];
	$Solicitacao        = $_POST['sequencial'];
} else {
	$Solicitacao = $_GET['SeqSolicitacao']; // sequencial da solicitação usado pelo 'Manter'
}

$db = Conexao();
$numeroScc = getNumeroSolicitacaoCompra($db, $Solicitacao);

$sqlScc  = "SELECT CCENPOSEQU, ESOLCOOBSE, ESOLCOOBJE, tsolcodata, CTPCOMCODI ";
$sqlScc .= "FROM SFPC.TBSOLICITACAOCOMPRA ";
$sqlScc .= "WHERE CSOLCOSEQU = $Solicitacao ";

$res = executarSQL($db, $sqlScc);

if (PEAR::isError($res)) {
	$CodErroEmail  = $res->getCode();
	$DescErroEmail = $res->getMessage();
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
} else {
	$LinhaScc = $res->fetchRow();
	$CentroCusto    = $LinhaScc[0];
	$observacao     = $LinhaScc[1];
	$objeto         = $LinhaScc[2];
	$data           = $LinhaScc[3];
	$sequTipoCompra = $LinhaScc[4];

	$ano = substr($data,0,4);
	$mes = substr($data,5,2);
	$dia = substr($data,8,2);
	$dataFormat = $dia.'/'.$mes.'/'.$ano;
}

if ($CentroCusto != '') {
    // Carrega os dados do Centro de Custo selecionado #
    $sql  = "SELECT A.ECENPODESC, B.EORGLIDESC, A.CORGLICODI, A.CCENPONRPA, A.ECENPODETA, B.FORGLITIPO, A.FCENPOSITU";
    $sql .= " FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B ";
    $sql .= " WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = $CentroCusto ";

    $res = executarSQL($db, $sql);
	
	if (PEAR::isError($res)) {
		$CodErroEmail  = $res->getCode();
		$DescErroEmail = $res->getMessage();
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
	} else {
		while ($Linha = $res->fetchRow()) {
			$DescCentroCusto = $Linha[0];
			$DescOrgao       = $Linha[1];
			$Orgao           = $Linha[2];
			$RPA             = $Linha[3];
			$Detalhe         = $Linha[4];
			$administracao   = $Linha[5];
			$ccSituacao      = $Linha[6];
			$Detalhamento    = '   (Centro de Custo Ativo)';

			if ($ccSituacao == 'I') {
				$Detalhamento = ' (Centro de custo inativo)';
			}
   		}
	}
}

$inserirObjeto     = strtoupper2(RetiraAcentos(removeSimbolos($_POST['objeto'])));
$inserirObservacao = strtoupper2(RetiraAcentos(removeSimbolos($_POST['observacao'])));
$inserirObservacao = (!empty($inserirObservacao)) ? $inserirObservacao: '' ;
$objetoSemEspaco   = trim($_POST['objeto']);
$objetoValida      = (empty($objetoSemEspaco)) ? false : true;
$objetoVazio       = false;
$mostrarErro       = false;
$Mensagem          = '';

if ($botao == 'Manter') {
	if (!empty($objetoSemEspaco)) {
		$updateScc  = "UPDATE  SFPC.TBSOLICITACAOCOMPRA ";
		$updateScc .= "SET ESOLCOOBSE = '".$inserirObservacao."', ESOLCOOBJE = '".$inserirObjeto."'";
		$updateScc .= "WHERE CSOLCOSEQU = $Solicitacao ";

		$res = executarSQL($db, $updateScc);

		if (PEAR::isError($res)) {
			$CodErroEmail  = $res->getCode();
			$DescErroEmail = $res->getMessage();
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		} else {
			$Mensagem =  'Objeto Alterado com Sucesso';
			header('Location:CadObjetoCompraPesquisar.php?Mens=1&Tipo=1&Mensagem=' . $Mensagem);
			exit();
		}
	} else {
		$mostrarErro = True;
		$Mensagem = 'Objeto.';
	}
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
<script language="javascript" type="">
	<!--
	function enviar(valor) {
		console.log(valor);

		if (valor == "Voltar") {
			document.CadAlterarObjetoCompra.action = "CadObjetoCompraPesquisar.php";
			document.CadAlterarObjetoCompra.submit();
		}

		document.CadAlterarObjetoCompra.botao.value = valor;
		document.CadAlterarObjetoCompra.submit();
	}

	function AbreJanela(url,largura,altura) {
		window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=15,top=15,width='+largura+',height='+altura);
	}

	function onClickDesativado(erro) {
		alert(erro);
	}

    $(document).ready(function() {
        $('#numeroScc').mask('9999.9999/9999');
    });

	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<br>
	<form action="CadAlterarObjetoCompra.php" method="post" name="CadAlterarObjetoCompra" id ="CadAlterarObjetoCompra"  enctype="multipart/form-data">
		<br><br><br><br><br>
		<table width="100%" cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Compras > Manter Objeto
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
			if ($mostrarErro == True) {
				?>
				<table border="0" width="100%" style="margin-left: 200px;">
					<tbody style="margin-left: 200px;">
						<tr>
							<td bgcolor="DCEDF7" class="titulo1">
								<blink><font class="titulo1">Erro</font></blink>
							</td>
						</tr>
						<tr style="margin-left: 200px;">
							<td class="titulo2" style="margin-left: 200px;">Informe:<?php echo $Mensagem; ?></td>
						</tr>
					</tbody>
				</table>
				<?php
			}
			?>
			<!-- Fim do Erro -->
			<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="50%;" bgcolor="#FFFFFF" summary="" style="margin-left: 200px;">
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
						MANTER SCC - OBJETO
					</td>
				</tr>
				<tr>
					<td class="textonormal">
						<p align="justify"></p>
					</td>
				</tr>
				<tr>
					<td>
						<table class="textonormal" border="0" align="left" width="100%" summary="">
							<!-- BEGIN BLOCO_NUMERO_SCC -->
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" >Número</td>
								<td class="textonormal">
									<?php echo $numeroScc; ?>
									<input type="hidden" id="numeroScc" name="numeroScc" value="<?php echo $numeroScc; ?>"/>
								</td>
							</tr>
							<!-- END BLOCO_NUMERO_SCC -->
							<!-- BEGIN BLOCO_SEQUENCIAL_SCC -->
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" >Sequencial</td>
								<td class="textonormal">
									<?php echo $sequTipoCompra; ?>
									<input type="hidden" id="Numero" name="Numero" value="<?php echo $sequTipoCompra; ?>"/>
								</td>
							</tr>
							<!-- END BLOCO_SEQUENCIAL_SCC -->
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="150px">Data</td>
								<td class="textonormal">
									<?php echo $dataFormat; ?>
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">Centro de Custo*</td>
								<td class="textonormal">
									<br>						
									<?php echo $DescOrgao; ?><br>&nbsp;&nbsp;&nbsp;&nbsp;
									<?php echo 'RPA' .$RPA; ?><br>	<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<?php echo $DescCentroCusto; ?><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<?php echo $Detalhe . $Detalhamento; ?>
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7">Objeto*</td>
								<td class="textonormal">
									<textarea maxlength="200" name ="objeto" id="objeto" style="vertical-align: text-top; width: 300px; height:80px; text-transform:uppercase;"><?php echo $objeto; ?></textarea>
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7">Observação</td>
								<td class="textonormal">
									<textarea maxlength="200" name ="observacao" style="text-align: top; width: 300px; height:80px; text-transform:uppercase;">
										<?php echo (empty($_POST['observacao'])) ? $observacao: $_POST['observacao']; ?>
									</textarea>
								</td>
							</tr>
						</table>
						<tr>
							<td colspan="2" align = "right">
								<input type="hidden" name="sequencial" value="<?php echo $Solicitacao; ?>">
								<input type="hidden" name="Objetovazio" id="Objetovazio" value="<?php echo $objetoVazio; ?>">
								<input type="button" name="Manter" value="Manter" class="botao" onClick="javascript:enviar('Manter');"> 
								<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar');">
								<input type="hidden" name="botao" id = "botao" value="">
							</td>
						</tr>
					</td>
				</tr>
			</table>
		</table>
	</form>
</html>