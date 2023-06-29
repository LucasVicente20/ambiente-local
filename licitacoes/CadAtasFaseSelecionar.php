<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAtasFaseSelecionar.php
# Autor:    Rossana Lira
# Data:     24/04/03
# Objetivo: Programa de Seleção de Atas/Fase de Licitação
#						para os funcionários cadastrados para alguma comissão
# OBS.:     Tabulação 2 espaços
#						Irão aparecer as licitações de acordo com a(s) comissão(ões)
#						do usuário que está logado
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:			04/07/2018
# Objetivo:	Tarefa Redmine 95885
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     27/12/2018
# Objetivo: Tarefa Redmine 208783
#-------------------------------------------------------------------------
# Alterado: Lucas Vicente
# Data:     10/10/2022
# Objetivo: Tarefa Redmine 206442
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/CadAtasFaseManter.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica                               = $_POST['Critica'];
		$LicitacaoProcessoAnoComissaoOrgaoFase = $_POST['LicitacaoProcessoAnoComissaoOrgaoFase'];

}if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Mensagem2                             = $_GET['Mensagem2'];
}if (!is_null($Mensagem2)){
{ $Mensagem .= " "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Arquivo maximo até 5 mb ";

}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadAtasFaseSelecionar.php";

# Critica dos Campos #
$Mensagem = urldecode($Mensagem);
if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $LicitacaoProcessoAnoComissaoOrgaoFase == "" ) {
	      $Mens = 1; $Tipo = 2; $Troca = 1;
        $Mensagem .= "<a href=\"javascript: document.AtasFase.AtasFaseCodigo.focus();\" class=\"titulo2\">Selecione um Processo (Processo-Ano/Fase)</a>";
    }else{
		    $NProcessoAnoComissao = explode("_",$LicitacaoProcessoAnoComissaoOrgaoFase);
				$LicitacaoProcesso    = substr($NProcessoAnoComissao[0] + 10000,1);
				$LicitacaoAno         = $NProcessoAnoComissao[1];
				$ComissaoCodigo       = $NProcessoAnoComissao[2];
				$OrgaoLicitanteCodigo = $NProcessoAnoComissao[3];
				$FaseCodigo           = $NProcessoAnoComissao[4];
				$Url = "CadAtasFaseManter.php?LicitacaoProcesso=$LicitacaoProcesso&LicitacaoAno=$LicitacaoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit();
    }
}
?>

<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAtasFaseSelecionar.php" method="post" name="AtasFase">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Atas da Fase
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" bgcolor="#FFFFFF" class="textonormal" summary="">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           MANTER - ATAS DA FASE DE LICITAÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para incluir/excluir um Ata cadastrada, selecione o Processo Licitatório/Fase e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Processo </td>
                <td class="textonormal">
                  <select name="LicitacaoProcessoAnoComissaoOrgaoFase" class="textonormal">
                  	<option value="">Selecione um Processo Licitatório/Fase...</option>
                  	<?php
										$db     = Conexao();
										$sql    = "SELECT A.CLICPOPROC, A.ALICPOANOP, A.CCOMLICODI, B.ECOMLIDESC, ";
										$sql   .= "       C.EGREMPDESC, A.CORGLICODI, D.CFASESCODI, D.EFASESDESC ";
										$sql   .= "  FROM SFPC.TBFASELICITACAO A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBGRUPOEMPRESA C, ";
										$sql   .= "       SFPC.TBFASES D, SFPC.TBUSUARIOCOMIS E ";
										$sql   .= " WHERE E.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND E.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
										$sql   .= "   AND E.CCOMLICODI = A.CCOMLICODI AND A.CGREMPCODI = E.CGREMPCODI ";
										$sql   .= "   AND A.CCOMLICODI = B.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ";
										$sql   .= "   AND A.CFASESCODI = D.CFASESCODI ";
										$sql   .= "   AND MAKE_DATE (A.ALICPOANOP,1,1) > CURRENT_DATE - INTERVAL '5 YEARS' "; //CR 206442 MAKE_DATE
										$sql   .= " ORDER BY B.ECOMLIDESC ASC, A.CGREMPCODI ASC, A.ALICPOANOP DESC, A.CLICPOPROC DESC, D.AFASESORDE ASC";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$ComissaoCodigoAnt = "";
												while( $Linha = $result->fetchRow() ){
														if( $Linha[2] != $ComissaoCodigoAnt ){
																$ComissaoCodigoAnt = $Linha[2];
																echo "<option value=\"\">$Linha[3]</option>\n" ;
														}

														$NProcesso = substr($Linha[0] + 10000,1);
														echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[5]_$Linha[6]\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$NProcesso-$Linha[1]/$Linha[7]</option>\n" ;
												}
										}
										$db->disconnect();
										?>
                  </select>
                  <input type="hidden" name="Critica" value="1">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="submit" value="Selecionar" class="botao">
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
<script language="javascript" type="">
<!--
document.AtasFase.LicitacaoProcessoAnoComissaoOrgaoFase.focus();
//-->
</script>
