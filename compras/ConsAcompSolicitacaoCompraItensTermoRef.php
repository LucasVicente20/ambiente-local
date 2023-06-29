<?php
#--------------------------------------------------------------------------------
# Portal da DGCO
# Programa:	ConsAcompSolicitacaoCompraItensTermoRef.php
# Autor:    Carlos Abreu
# Data:     19/06/2007
# Objetivo:	Programa de Listagem dos Itens da Solicitação para Termo de Referência
# Alterado: Carlos Abreu
# Data:     01/10/2007 - Acrescentado o destaque '*' quando o preco do material não esta registrado na tabela de registro de preco
# Alterado: Rossana Lira/ Rodrigo Melo 
# Data:     22/10/2007 - Troca do Natural Join pelo Inner Join pois não estava exibindo dados 
#                        dos itens das solicitações para uma solicitação específica
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
	$SeqSolicitacao = $_GET['SeqSolicitacao'];
	$AnoSolicitacao = $_GET['AnoSolicitacao'];
	$CentroCusto    = $_GET['CentroCusto'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
if( $Botao == "" ){
	# Verifica se é a primeira vez que entra no programa #
	unset($_SESSION['item']);
	$db = Conexao();
	# Pega os dados dos Itens da Solicitação de Material de acordo com o Sequencial da Solicitação#
	$sql  = "SELECT AITESCORDE, MAT.CMATEPSEQU, EMATEPCOMP, EUNIDMSIGL, AITESCQTSO, ";
	$sql .= "(SELECT CASE WHEN COUNT(*)>0 THEN 'S' ELSE 'N' END FROM SFPC.TBPRECOMATERIAL PRECO WHERE MAT.CMATEPSEQU = PRECO.CMATEPSEQU ) ";
	$sql .= "  FROM SFPC.TBSOLICITACAOCOMPRA SOL";
	$sql .= "       INNER JOIN SFPC.TBITEMSOLICITACAOCOMPRA ITEM ON SOL.CSOLCOSEQU = ITEM.CSOLCOSEQU";
	$sql .= "       INNER JOIN SFPC.TBMATERIALPORTAL MAT ON ITEM.CMATEPSEQU = MAT.CMATEPSEQU";
	$sql .= "       INNER JOIN SFPC.TBUNIDADEDEMEDIDA UND ON MAT.CUNIDMCODI = UND.CUNIDMCODI";
	$sql .= " WHERE SOL.CSOLCOSEQU = $SeqSolicitacao ";
	$sql .= " ORDER BY AITESCORDE ";
	$res = $db->query($sql);
	if( PEAR::isError($res) ){
		$CodErroEmail  = $res->getCode();
		$DescErroEmail = $res->getMessage();
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
	}else{
		$Rows = $res->numRows();
		for( $i=0;$i<$Rows;$i++ ){
			$Linha = $res->fetchRow();
			$Material[$i]      = $Linha[1];
			$DescMaterial[$i]  = $Linha[2];
			$Unidade[$i]       = $Linha[3];
			$QtdSolicitada[$i] = str_replace(".",",",$Linha[4]);
			$QtdSolicitada[$i] = converte_valor($QtdSolicitada[$i]);
			$ExistePreco[$i] = $Linha[5];
		}
	}
	$db->disconnect();
}
?>
<html>
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<br>
<table border="1" cellpadding="3" cellspacing="0" width="100%" summary="">
	<tr>
		<td class="textonormal" width="5%" align="center"><b>ORD.</td>
		<td class="textonormal" width="50%" align="center"><b>DESCRIÇÃO DO MATERIAL</td>
		<td class="textonormal" width="5%" align="center"><b>CÓDIGO REDUZIDO</td>
		<td class="textonormal" width="10%" align="center"><b>UNIDADE</td>
		<td class="textonormal" width="10%" align="center"><b>QUANTIDADE</td>
	</tr>
	<?php for( $i=0;$i< count($Material);$i++ ){ ?>
	<tr>
		<td class="textonormal" align="center" width="5%">
			<?php echo ($i+1); if ($ExistePreco[$i]=='N'){ echo " *";} ?>
		</td>
		<td class="textonormal" width="50%">
			<?php echo $DescMaterial[$i];?>
		</td>
		<td class="textonormal" align="center" width="5%">
			<?php echo $Material[$i];?>
		</td>
		<td class="textonormal" width="5%" align="center">
			<?php echo $Unidade[$i];?>
		</td>
		<td class="textonormal" width="5%" align="center">
			<?php echo $QtdSolicitada[$i];?>
		</td>
	</tr>
	<?php } ?>
</table>
</body>
</html>
 
