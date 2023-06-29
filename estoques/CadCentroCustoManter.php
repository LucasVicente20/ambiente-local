<?php
#--------------------------------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: CadCentroCustoManter.php
# Autor:		Marcus Thiago
# Data:     19/01/2006
# Alterado: Rodrigo Melo
# Data:     27/05/2008 - Alteração para obter a descrição do órgão e RPA quando não existe para o ano do exercício.
# Objetivo: Programa que executa a ação de alteração de situação ou exclusão de um Centro de Custo
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile IT - Caio Coutinho
# Data:     18/12/2018
# Objetivo: 207375
#--------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadIncluirCentroCusto.php' );
AddMenuAcesso( '/estoques/CadCentroCustoManterSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao       = $_POST['Botao'];
		$CentroCusto = $_POST['CentroCusto'];
		$Todos       = $_POST['Todos'];
		$Orgao       = $_POST['Orgao'];
		$Descricao   = strtoupper2(trim($_POST['Descricao']));
		$SituacaoRadio = $_POST['SituacaoRadio'];
}else{
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
		$CentroCusto = $_GET['CentroCusto'];
		$Unidade	= $_GET['Unidade'];
		$RPA			= $_GET['RPA'];
}

# Identifica e executa a ação do botão #
if( $Botao == "Voltar" ){
    header("location: CadCentroCustoManterSelecionar.php");
    exit;
}elseif( $Botao == "Alterar" ){
		$db   = Conexao();
		$alterar  = "UPDATE SFPC.TBCENTROCUSTOPORTAL";
		$alterar .=	"  SET FCENPOSITU = '$SituacaoRadio', CUSUPOCODI = " . $_SESSION['_cusupocodi_'];
		$alterar .= "    WHERE CCENPOSEQU = '$CentroCusto'";
		$res  = $db->query($alterar);
		if( PEAR::isError($res) ){
		  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $alterar");
		}else{
			$Mens	=	1;
			$Tipo = 1;
			$Mensagem = "Situação Alterada com Sucesso";
		}
}elseif( $Botao == "Excluir" ){
		$db   = Conexao();
		$sql  = "SELECT COUNT(CUSUPOCODI) FROM SFPC.TBUSUARIOCENTROCUSTO";
		$sql .= "    WHERE CCENPOSEQU = '$CentroCusto' AND FUSUCCTIPO IN ('T','R')";
		$res  = $db->query($sql);
		$Linha  = $res->fetchRow();
		if( PEAR::isError($res) ){
		  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
			if ($Linha[0] > 0){
				$Mens	=	1;
				$Tipo = 2;
				if ($Linha[0] == 1){
					$Mensagem = "Este Centro de Custo não pode ser excluído porque existe $Linha[0] Usuário relacionado a ele";
				}else{
					$Mensagem = "Este Centro de Custo não pode ser excluído porque existem $Linha[0] Usuários relacionados a ele";
				}
			}else{
				$db   = Conexao();
				$apagar  = "DELETE FROM SFPC.TBCENTROCUSTOPORTAL";
				$apagar .= "    WHERE CCENPOSEQU = '$CentroCusto'";
				$res  = $db->query($apagar);
				if( PEAR::isError($res) ){
				  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $apagar");
				}else{
					$Url = "CadCentroCustoManterSelecionar.php?Mens=1&Tipo=1&Mensagem=Centro de Custo Apagado com Sucesso";
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
			    header("location: ".$Url);
			    exit;
				}
			}
		}
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Carrega os dados do Centro de Custo selecionado #
$db   = Conexao();

$sql  = "SELECT A.CCENPOSEQU, A.CCENPOCORG, A.CCENPOUNID, A.ECENPODESC, ";
$sql .= "       A.CCENPONRPA, A.ECENPODETA, B.EUNIDODESC, A.FCENPOSITU,
								A.CCENPOCENT, A.CCENPODETA, A.ACENPOANOE ";
$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A INNER JOIN SFPC.TBUNIDADEORCAMENTPORTAL B ON ";
$sql .= "				A.CCENPOCORG = B.CUNIDOORGA AND A.CCENPOUNID = B.CUNIDOCODI ";
$sql .= " WHERE A.CCENPOSEQU = '$CentroCusto'";
$sql .= " AND B.TUNIDOEXER = (SELECT MAX(TUNIDOEXER) FROM SFPC.TBUNIDADEORCAMENTPORTAL WHERE CUNIDOORGA = B.CUNIDOORGA AND CUNIDOCODI = B.CUNIDOCODI) ";

$res  = $db->query($sql);
if( PEAR::isError($res) ){
  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Linha      = $res->fetchRow();
		$Sequencial = $Linha[0];
		$Centro     = $Linha[3];
		$RPA        = $Linha[4];
		$Detalhe    = $Linha[5];
		$Unidade		=	$Linha[6];
		$Situacao		=	$Linha[7];

		$Ano		=	$Linha[10];
		$OrgaoSofin		=	$Linha[1];
		$UnidadeSofin		=	$Linha[2];
		$CCSofin		=	$Linha[8];
		$DetalheSofin		=	$Linha[9];

		if ($Linha[7] == 'I'){
			$Radio = "<input type='radio' name='SituacaoRadio' value='A'>ATIVO&nbsp;&nbsp;&nbsp;<input type='radio' name='SituacaoRadio' value='I' checked>INATIVO";
		}else{
			$Radio = "<input type='radio' name='SituacaoRadio' value='A' checked>ATIVO&nbsp;&nbsp;&nbsp;<input type='radio' name='SituacaoRadio' value='I'>INATIVO";
		}
}

?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadCentroCustoManter.Botao.value = valor;
	document.CadCentroCustoManter.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadCentroCustoManter.php" method="post" name="CadCentroCustoManter">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="" width="100%">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Centro de Custo > Manter
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
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
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">CENTRO DE CUSTO - MANTER</td>
        </tr>
		    <tr>
	    		<td class="textoabason" bgcolor="#BFDAF2" align="center"><?php echo "$Unidade"; ?></td>
				</tr>
				<tr>
					<td class="textoabason" bgcolor="#DDECF9" align="center">RPA <?php echo "$RPA"; ?></td>
				</tr>
        <tr>
 	        <td class="textonormal" align="right">
					<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#FFFFFF" summary="" class="textonormal"  bgcolor="#FFFFFF">
						<tr>
							<td class="textonormal" bgcolor="#DCEDF7" width="30%">Centro de Custo</td>
							<td class="textonormal" bgcolor="#FFFFFF" width="70%"><?php echo "$Centro"; ?></td>
						</tr>
						<tr>
							<td class="textonormal" bgcolor="#DCEDF7" width="30%">Detalhamento</td>
							<td class="textonormal" bgcolor="#FFFFFF" width="70%"><?php echo "$Detalhe"; ?></td>
						</tr>
						<tr>
							<td class="textonormal" bgcolor="#DCEDF7" width="30%">Ano</td>
							<td class="textonormal" bgcolor="#FFFFFF" width="70%"><?php echo "$Ano"; ?></td>
						</tr>
						<tr>
							<td class="textonormal" bgcolor="#DCEDF7" width="30%">N° Órgão (sistema de custos)</td>
							<td class="textonormal" bgcolor="#FFFFFF" width="70%"><?php echo "$OrgaoSofin"; ?></td>
						</tr>
						<tr>
							<td class="textonormal" bgcolor="#DCEDF7" width="30%">N° Unidade (sistema de custos)</td>
							<td class="textonormal" bgcolor="#FFFFFF" width="70%"><?php echo "$UnidadeSofin"; ?></td>
						</tr>
						<tr>
							<td class="textonormal" bgcolor="#DCEDF7" width="30%">N° RPA (sistema de custos)</td>
							<td class="textonormal" bgcolor="#FFFFFF" width="70%"><?php echo "$RPA"; ?></td>
						</tr>
						<tr>
							<td class="textonormal" bgcolor="#DCEDF7" width="30%">N° Centro de custos (sistema de custos)</td>
							<td class="textonormal" bgcolor="#FFFFFF" width="70%"><?php echo "$CCSofin"; ?></td>
						</tr>
						<tr>
							<td class="textonormal" bgcolor="#DCEDF7" width="30%">N° Detalhamento (sistema de custos)</td>
							<td class="textonormal" bgcolor="#FFFFFF" width="70%"><?php echo "$DetalheSofin"; ?></td>
						</tr>
						<tr>
							<td class="textonormal" bgcolor="#DCEDF7" width="30%">Situação</td>
							<td class="textonormal" bgcolor="#FFFFFF" width="70%"><?php echo "$Radio"; ?></td>
						</tr>
					</table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
 	        	<input type="hidden" name="CentroCusto" value="<?php echo "$CentroCusto"; ?>">
 	        	<input type="button" name="Alterar" value="Alterar" class="botao" onClick="javascript:enviar('Alterar')">
 	        	<input type="button" name="Excluir" value="Excluir" class="botao" onClick="javascript:enviar('Excluir')">
   	      	<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar')">
   	      	<input type="hidden" name="Botao" value="">
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
