<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadResultadoPosHomologacaoSelecionar.php
# Autor:    Raphael Borborema (raphael.borborema@banksystem.com.br)
# Data:     04/07/12
# Objetivo: Programa de Seleção de Licitação já homologadas, para alteração de resultado (Resultado)
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();

//Remover essa linha quando o programa for adicionado as permissoes do menu
AddMenuAcesso( '/licitacoes/CadResultadoPosHomologacaoSelecionar.php' );

Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/CadResultadoPosHomologacaoAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$LicitacaoProcessoAnoComissaoOrgao = $_POST['LicitacaoProcessoAnoComissaoOrgao'];
		$Critica                           = $_POST['Critica'];
}else{
		$Critica                           = $_GET['Critica'];
		$Mensagem                          = $_GET['Mensagem'];
		$Mens                              = $_GET['Mens'];
		$Tipo                              = $_GET['Tipo'];
}

# Critica dos Campos #
$Mensagem = urldecode($Mensagem);
if( $Critica == 1 ){
		$Mens = 0;
		$Mensagem = "Informe: ";
    if( $LicitacaoProcessoAnoComissaoOrgao == "" ) {
	      $Mens = 1; $Tipo = 2; $Troca = 1;
          $Mensagem .= "<a href=\"javascript: document.Resultado.ResultadoCodigo.focus();\" class=\"titulo2\">Selecione um Processo (Processo/Ano)</a>";
    }else{
		    	$NProcessoAnoComissao = explode("_",$LicitacaoProcessoAnoComissaoOrgao);
				$Processo             = $NProcessoAnoComissao[0];
				$ProcessoAno          = $NProcessoAnoComissao[1];
				$ComissaoCodigo       = $NProcessoAnoComissao[2];
				$OrgaoLicitanteCodigo = $NProcessoAnoComissao[3];
				$Url = "CadResultadoPosHomologacaoAlterar.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo";
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
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadResultadoPosHomologacaoSelecionar.php" method="post" name="Resultado">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Resultado Pós-Homologação
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
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           MANTER RESULTADO PÓS HOMOLOGAÇÃO DA LICITAÇÃO
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" width="100%" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Processo </td>
                <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                <td class="textonormal" bgcolor="#FFFFFF">
                  <?php
                  
                  $sql = "SELECT 
											LIC.CLICPOPROC, 
											LIC.ALICPOANOP, 
											LIC.CCOMLICODI, 
											COM.ECOMLIDESC,
											LIC.CORGLICODI
										FROM 
											
											SFPC.TBLICITACAOPORTAL LIC, 
											SFPC.TBCOMISSAOLICITACAO COM, 
											SFPC.TBUSUARIOCOMIS USU,
											SFPC.TBFASELICITACAO FAS ,
											SFPC.TBPRESOLICITACAOEMPENHO EMP
										WHERE LIC.FLICPOREGP <> 'S' ";
										 $sql .= " AND USU.CGREMPCODI = ".$_SESSION['_cgrempcodi_'] ;
										 $sql .= " AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_'] ;
										 $sql .= " AND USU.CCOMLICODI = LIC.CCOMLICODI 
									    AND LIC.CGREMPCODI = USU.CGREMPCODI
										AND LIC.CCOMLICODI = COM.CCOMLICODI 
										
										AND EMP.CGREMPCODI = LIC.CGREMPCODI
										AND EMP.CLICPOPROC = LIC.CLICPOPROC
										AND EMP.ALICPOANOP = LIC.ALICPOANOP 
										AND EMP.CORGLICODI = LIC.CORGLICODI
										AND EMP.CCOMLICODI = LIC.CCOMLICODI
										AND EMP.TPRESOIMPO IS NOT NULL
										
										
										AND FAS.CLICPOPROC = LIC.CLICPOPROC 
										AND FAS.alicpoanop = LIC.alicpoanop 
										AND FAS.cgrempcodi = LIC.cgrempcodi 
										AND FAS.ccomlicodi = LIC.ccomlicodi 
										AND FAS.corglicodi = LIC.corglicodi
										AND FAS.cfasescodi = 13
										AND (FAS.clicpoproc,FAS.alicpoanop,FAS.cgrempcodi,FAS.ccomlicodi,FAS.corglicodi)
										NOT IN (SELECT FAS2.clicpoproc,FAS2.alicpoanop,FAS2.cgrempcodi,FAS2.ccomlicodi,FAS2.corglicodi FROM sfpc.tbfaselicitacao AS FAS2 WHERE FAS2.cfasescodi <> 13 AND FAS2.tfaselulat > FAS.tfaselulat  )
										AND (FAS.clicpoproc,FAS.alicpoanop,FAS.cgrempcodi,FAS.ccomlicodi,FAS.corglicodi)
										IN (SELECT clicpoproc , alicpoanop , cgrempcodi , ccomlicodi , corglicodi from sfpc.tbsolicitacaolicitacaoportal )
										
										ORDER BY 
											COM.ECOMLIDESC ASC, 
											LIC.ALICPOANOP DESC, 
											LIC.CLICPOPROC DESC";
										 
                  
                  
                  
                  ?>
                  
                  <select name="LicitacaoProcessoAnoComissaoOrgao" class="textonormal">
                  	<option value="">Selecione um Processo Licitatório...</option>
                  	<!-- Mostra as licitações cadastradas -->
                  	<?php
										$db     = Conexao();
										$sql = "SELECT 
											LIC.CLICPOPROC, 
											LIC.ALICPOANOP, 
											LIC.CCOMLICODI, 
											COM.ECOMLIDESC,
											LIC.CORGLICODI
										FROM 
											
											SFPC.TBLICITACAOPORTAL LIC, 
											SFPC.TBCOMISSAOLICITACAO COM, 
											SFPC.TBUSUARIOCOMIS USU,
											SFPC.TBFASELICITACAO FAS ,
											SFPC.TBPRESOLICITACAOEMPENHO EMP
										WHERE LIC.FLICPOREGP <> 'S' ";
										 $sql .= " AND USU.CGREMPCODI = ".$_SESSION['_cgrempcodi_'] ;
										 $sql .= " AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_'] ;
										 $sql .= " AND USU.CCOMLICODI = LIC.CCOMLICODI 
									    AND LIC.CGREMPCODI = USU.CGREMPCODI
										AND LIC.CCOMLICODI = COM.CCOMLICODI 
										
										AND EMP.CGREMPCODI = LIC.CGREMPCODI
										AND EMP.CLICPOPROC = LIC.CLICPOPROC
										AND EMP.ALICPOANOP = LIC.ALICPOANOP 
										AND EMP.CORGLICODI = LIC.CORGLICODI
										AND EMP.CCOMLICODI = LIC.CCOMLICODI
										AND EMP.TPRESOIMPO IS NOT NULL
										
										
										AND FAS.CLICPOPROC = LIC.CLICPOPROC 
										AND FAS.alicpoanop = LIC.alicpoanop 
										AND FAS.cgrempcodi = LIC.cgrempcodi 
										AND FAS.ccomlicodi = LIC.ccomlicodi 
										AND FAS.corglicodi = LIC.corglicodi
										AND FAS.cfasescodi = 13
										AND (FAS.clicpoproc,FAS.alicpoanop,FAS.cgrempcodi,FAS.ccomlicodi,FAS.corglicodi)
										NOT IN (SELECT FAS2.clicpoproc,FAS2.alicpoanop,FAS2.cgrempcodi,FAS2.ccomlicodi,FAS2.corglicodi FROM sfpc.tbfaselicitacao AS FAS2 WHERE FAS2.cfasescodi <> 13 AND FAS2.tfaselulat > FAS.tfaselulat  )
										AND (FAS.clicpoproc,FAS.alicpoanop,FAS.cgrempcodi,FAS.ccomlicodi,FAS.corglicodi)
										IN (SELECT clicpoproc , alicpoanop , cgrempcodi , ccomlicodi , corglicodi from sfpc.tbsolicitacaolicitacaoportal )
										
										ORDER BY 
											COM.ECOMLIDESC ASC, 
											LIC.ALICPOANOP DESC, 
											LIC.CLICPOPROC DESC";
											
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
														echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[4]\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[1]</option>\n" ;
												}
										}
										$db->disconnect();
										?>
                  </select>
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
