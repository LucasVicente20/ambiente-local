<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadResultadoSelecionar.php
# Autor:    Rossana Lira
# Data:     02/05/03
# Objetivo: Programa de Seleção de Licitação (Resultado)
# OBS.:     Tabulação 2 espaços
#						Irão aparecer as licitações de acordo com a(s) comissão(ões)
#           do usuário que está logado
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		04/07/2018
# Objetivo:	Tarefa Redmine 95885
#-------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:		11/07/2018
# Objetivo:	Tarefa Redmine 194552
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
AddMenuAcesso( '/licitacoes/CadResultadoAlterar.php' );
AddMenuAcesso( '/licitacoes/CadResultadoAlterarNovo.php' );

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
                $ProcessoGrupo        = $NProcessoAnoComissao[4];
				
				//Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
				$sqlSolicitacoes = " SELECT  csolcosequ ,clicpoproc , alicpoanop , cgrempcodi ,ccomlicodi ,corglicodi
											FROM SFPC.TBSOLICITACAOLICITACAOPORTAL SOL 
											WHERE 	SOL.CLICPOPROC = $Processo 
													AND SOL.ALICPOANOP = $ProcessoAno
													AND SOL.CCOMLICODI = $ComissaoCodigo 
													AND SOL.corglicodi = $OrgaoLicitanteCodigo 
													AND  SOL.cgrempcodi =". $ProcessoGrupo ; // Grupo alterado da sessao
					
					
				
				$db     = Conexao();
				$resultSoli = $db->query($sqlSolicitacoes);
				
				if( PEAR::isError($resultSoli) ){
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
				}
				
				$intQuantidade = $resultSoli->numRows();
				$db->disconnect();
				
				
				if($intQuantidade>0){
					$Url = "CadResultadoAlterarNovo.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&Grupo=$ProcessoGrupo";
				}else{
					$Url = "CadResultadoAlterar.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo";
				}
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
<form action="CadResultadoSelecionar.php" method="post" name="Resultado">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Resultado
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
	           MANTER - RESULTADO DA LICITAÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Para incluir/excluir um Resultado cadastrado, selecione o Processo e clique no botão "Selecionar".
             </p>
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
                  
                 $sql    = "SELECT A.CLICPOPROC, A.ALICPOANOP, A.CCOMLICODI, B.ECOMLIDESC, ";
										$sql   .= "        A.CORGLICODI ";
										$sql   .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B , ";
										$sql   .= "       SFPC.TBUSUARIOCOMIS D ";
										$sql   .= " WHERE D.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND D.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
										$sql   .= "   AND D.CCOMLICODI = A.CCOMLICODI AND A.CGREMPCODI = D.CGREMPCODI ";
										$sql   .= "   AND A.CCOMLICODI = B.CCOMLICODI  ";
										$sql   .= " ORDER BY B.ECOMLIDESC ASC, A.CGREMPCODI ASC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";
										
								
                  
                  ?>
                  <select name="LicitacaoProcessoAnoComissaoOrgao" class="textonormal">
                  	<option value="">Selecione um Processo Licitatório...</option>
                  	<!-- Mostra as licitações cadastradas -->
                  	<?php
										$db     = Conexao();
										$sql    = "SELECT A.CLICPOPROC, A.ALICPOANOP, A.CCOMLICODI, B.ECOMLIDESC, ";
										$sql   .= "        A.CORGLICODI, A.CGREMPCODI ";
										$sql   .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B , ";
										$sql   .= "       SFPC.TBUSUARIOCOMIS D ";
										$sql   .= " WHERE D.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND D.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
										$sql   .= "   AND D.CCOMLICODI = A.CCOMLICODI AND A.CGREMPCODI = D.CGREMPCODI ";
										$sql   .= "   AND A.CCOMLICODI = B.CCOMLICODI  ";
										$sql   .= "   AND MAKE_DATE(A.ALICPOANOP,1,1) > CURRENT_DATE - INTERVAL '5 YEARS'"; //CR 206442 MAKE_DATE
										$sql   .= " ORDER BY B.ECOMLIDESC ASC, A.CGREMPCODI ASC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";
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
														echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[4]_$Linha[5]\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[1]</option>\n" ;
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
