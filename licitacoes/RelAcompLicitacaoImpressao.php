<?php  
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAcompLicitacaoImpressao.php
# Autor:    Roberta Costa
# Data:     28/08/03
# Objetivo: Programa que Imprime o Relatório de Acompanhamento de Licitações
# Alterado: Carlos Abreu
# Data:     19/09/2007 - Acrescido campo Valor
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
 
# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$GrupoCodigo      = $_GET['GrupoCodigo'];
		$ComissaoCodigo   = $_GET['ComissaoCodigo'];
		$ModalidadeCodigo = $_GET['ModalidadeCodigo'];  
		$Fase							= $_GET['Fase'];
		$Ano							= $_GET['Ano'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelAcompLicitacaoImpressao.php";
?>
<html>
<head>
<title>Portal de Compras - Prefeitura do Recife</title>
<script language="javascript" type="">
<!--
self.print();
function Fecha(){
	window.close();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body bgcolor="#ffffff" text="#000000" marginwidth="0" marginheight="0">
<form action="RelAcompLicitacaoImpressao.php" method="post" name="Relatorio">
<p class="titulo3" align="center">
  Prefeitura da Cidade do Recife<br><br>
  <a href="javascript:Fecha()"><img src="../midia/brasao.jpg" width="50" height="40" border="0" alt=""></a>
<p class="titulo3" align="right">
	Data: <?php echo date("d/m/Y H:i"); ?>
	<font class="textonegrito"><center>Relatório de Acompanhamento das Licitações</center></font> 
	<br>
</p>
  
<table border="1" cellpadding="3" cellspacing="0" summary="" class="textonormal"><hr>
<?
$Mens = 0; 
if( $Mens == 0 ) {
		# Seleciona todas as licitações ativas com ordem da fase <> 99 (Homologação e Adjudicação) #
		$db   = Conexao();
		$sql  = "SELECT c.EGREMPDESC, e.EMODLIDESC, d.ECOMLIDESC, a.CLICPOPROC, a.ALICPOANOP, ";
		$sql .= "       a.CLICPOCODL, a.ALICPOANOL, a.XLICPOOBJE, a.TLICPODHAB, b.EORGLIDESC, ";
		$sql .= "       a.CGREMPCODI, a.CCOMLICODI, a.CORGLICODI, f.EFASELDETA, g.EFASESDESC, ";
		$sql .= "       CASE WHEN G.CFASESCODI <> 13 THEN A.VLICPOVALE ELSE A.VLICPOVALH END ";
		$sql .= "  FROM SFPC.TBLICITACAOPORTAL a, SFPC.TBORGAOLICITANTE b, SFPC.TBGRUPOEMPRESA c, SFPC.TBCOMISSAOLICITACAO d, SFPC.TBMODALIDADELICITACAO e, ";
		$sql .= "       SFPC.TBFASELICITACAO f, SFPC.TBFASES g, ";
		$sql .= "       ( SELECT l.CLICPOPROC as Proc, l.ALICPOANOP as Ano, l.CGREMPCODI as Grupo, ";
		$sql .= "                l.CCOMLICODI as Comis, l.CORGLICODI as Orgao, MAX(o.AFASESORDE) as Maior ";
		$sql .= "           FROM SFPC.TBFASELICITACAO l, SFPC.TBFASES o ";
		$sql .= "          WHERE l.CFASESCODI = o.CFASESCODI ";
		$sql .= "          GROUP BY l.CLICPOPROC, l.ALICPOANOP, l.CGREMPCODI, l.CCOMLICODI, l.CORGLICODI ";
		$sql .= "       ) as om ";
		$sql .= " WHERE a.CORGLICODI = b.CORGLICODI AND a.CGREMPCODI = c.CGREMPCODI ";
		$sql .= "   AND a.CCOMLICODI = d.CCOMLICODI AND a.CMODLICODI = e.CMODLICODI ";
		$sql .= "   AND a.CLICPOPROC = f.CLICPOPROC AND a.ALICPOANOP = f.ALICPOANOP ";
		$sql .= "   AND a.CGREMPCODI = f.CGREMPCODI AND a.CCOMLICODI = f.CCOMLICODI ";
		$sql .= "   AND a.CORGLICODI = f.CORGLICODI AND f.CFASESCODI = g.CFASESCODI ";
		$sql .= "   AND a.CLICPOPROC = om.Proc AND a.ALICPOANOP  = om.Ano ";
		$sql .= "   AND a.CGREMPCODI = om.Grupo AND a.CCOMLICODI = om.Comis ";
		$sql .= "   AND a.CORGLICODI = om.Orgao AND g.AFASESORDE = om.Maior ";
		
		# Opção sem todas fases ou com as fases - Última Fase é 99 #
		if( $Fase == 2 ){
				$sql .= " AND g.AFASESORDE = 99 ";  
		}elseif( $Fase == 1 ){
				$sql .= " AND g.AFASESORDE < 99 ";  
		}
		
		if( $ModalidadeCodigo != "" ) {
			  $sql .= " AND a.CMODLICODI = $ModalidadeCodigo ";
		}
		
		if ($ComissaoCodigo!=""){ $sql .= " AND a.CCOMLICODI = $ComissaoCodigo "; }
		if ($ModalidadeCodigo!=""){ $sql .= " AND a.CMODLICODI = $ModalidadeCodigo "; }
		if ($GrupoCodigo!=""){ $sql .= " AND a.CGREMPCODI = $GrupoCodigo "; }
		$DataIni  = $Ano."-01-01 00:00:00";
		$DataFim  = $Ano."-12-31 23:59:59";
		if( $Ano != "" ){ $sql .= " AND a.TLICPODHAB >= '$DataIni' AND a.TLICPODHAB <= '$DataFim' "; }

		$sql .= "   ORDER BY c.EGREMPDESC, d.ECOMLIDESC, e.EMODLIDESC, a.ALICPOANOP, a.CLICPOPROC";

		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		$Rows = $result->numRows();

		$GrupoDescricao = "";
		if( $Rows != 0){
				while( $Linha = $result->fetchRow() ){
						 if( $GrupoDescricao != $Linha[0] ){
								 $GrupoDescricao = $Linha[0];
								 echo "<tr>\n";
								 echo "	<td align=\"center\" class=\"titulo3\" colspan=\"6\" bgcolor=\"#DCEDF7\">$GrupoDescricao</td>\n";
								 echo "</tr>\n";
							 	 $ComissaoDescricao = "";
							 	 $ExibeCabecalho    = "S";
							}
							if( $ComissaoDescricao != $Linha[2] ){
								 	$ComissaoDescricao = $Linha[2];
							  	echo "<tr>\n";
							  	echo "	<td align=\"center\" class=\"titulo2\" colspan=\"6\">$ComissaoDescricao</td>\n";
							  	echo "</tr>\n";
							    $ModalidadeDescricao = "";
							    $ExibeCabecalho      = "S";
							}
							if( $ModalidadeDescricao != $Linha[1] ){
									$ModalidadeDescricao = $Linha[1];
									echo "<tr>\n";
									echo "	<td align=\"center\" class=\"titulo3\" colspan=\"6\">$ModalidadeDescricao</td>\n";
									echo "</tr>\n";
									$ExibeCabecalho = "S";
							}
							if( $ExibeCabecalho == "S" ){
									echo "<tr>\n";
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">PROCESSO</td>\n";
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">OBJETO</td>\n";
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">DATA/HORA<br>ABERTURA</td>\n";
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">FASE</td>\n";
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">VALOR</td>\n";
									echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">SITUAÇÃO</td>\n";
									echo "</tr>\n";
									$ExibeCabecalho = "N";
							}
							$NProcesso             = substr($Linha[3] + 10000,1);
							$NLicitacao            = substr($Linha[5] + 10000,1);
							$LicitacaoDtAbertura   = substr($Linha[8],8,2) ."/". substr($Linha[8],5,2) ."/". substr($Linha[8],0,4);
							$LicitacaoHoraAbertura = substr($Linha[8],11,5);
							echo "<tr>\n";
							echo "	<td valign=\"top\" class=\"textonormal\" width=\"20%\">$NProcesso/$Linha[4]<br><br><font class=\"textonegrito\">LICITAÇÃO </font>".$NLicitacao."/".$Linha[6]."</td>\n";
							echo "	<td valign=\"top\" class=\"textonormal\" width=\"20%\">$Linha[7]</td>\n";
							echo "	<td valign=\"top\" class=\"textonormal\" width=\"20%\">$LicitacaoDtAbertura<br>$LicitacaoHoraAbertura h</td>\n";
							echo "	<td valign=\"top\" class=\"textonormal\" width=\"20%\">$Linha[14]</td>\n";
							echo "	<td valign=\"top\" class=\"textonormal\" width=\"20%\" align=\"right\">".converte_valor($Linha[15])."</td>\n";
							if( $Linha[13] == "" ){ $Linha[13]= "-"; }
							echo "	<td valign=\"top\" class=\"textonormal\" width=\"20%\">$Linha[13]</td>\n";
							echo "</tr>\n";
							$OrgaoLicitante = $Linha[9];
							$Modalidade     = $Linha[1];
				}
		}
		$db->disconnect();
}
?>
</table>
</form>
</body>
</html>
