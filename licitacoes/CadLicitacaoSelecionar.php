<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadLicitacaoSelecionar.php
# Autor:    Rossana Lira
# Data:     14/04/03
# Objetivo: Programa de Manutenção de Licitação
# OBS.:     Tabulação 2 espaços
#						Irão aparecer as licitações de acordo com a(s) comissão(ões)
#           do usuário que está logado
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
AddMenuAcesso( '/licitacoes/CadLicitacaoAlterar.php' );
AddMenuAcesso( '/licitacoes/CadLicitacaoAlterarNovo.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$LicitacaoProcessoAnoComissao = $_POST['LicitacaoProcessoAnoComissao'];
		$Critica                      = $_POST['Critica'];
}else{
		$Critica  = $_GET['Critica'];
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens     = $_GET['Mens'];
		$Tipo     = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadLicitacaoSelecionar.php";

# Critica dos Campos #
if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $LicitacaoProcessoAnoComissao == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Licitacao.LicitacaoCodigo.focus();\" class=\"titulo2\">Selecione um Processo (Processo/Ano)</a>";
    }else{
		    	$NProcessoAnoComissao = explode("_",$LicitacaoProcessoAnoComissao);
				$Processo             = substr($NProcessoAnoComissao[0] + 10000,1);
				$ProcessoAno          = $NProcessoAnoComissao[1];
				$ComissaoCodigo       = $NProcessoAnoComissao[2];
				$novaTela 			  = $NProcessoAnoComissao[3];
				if($novaTela=="1"){
					$Url = "CadLicitacaoAlterarNovo.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo";
				}else{
					$Url = "CadLicitacaoAlterar.php?Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo";
				}
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
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
<form action="CadLicitacaoSelecionar.php" method="post" name="Licitacao">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Licitação > Manter
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
	           MANTER - LICITAÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar/excluir uma Licitação cadastrada, selecione o Processo e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Processo </td>
                <td class="textonormal"><input type="hidden" name="Critica" value="1"></td>
                <td class="textonormal">
                  <select name="LicitacaoProcessoAnoComissao" value="" class="textonormal">
                  	<option value="">Selecione um Processo Licitatório...</option>
                  	<?php
										$db     = Conexao();
										$sql    = "SELECT A.CLICPOPROC, A.ALICPOANOP, A.CCOMLICODI, A.CGREMPCODI , A.CORGLICODI , B.ECOMLIDESC ";
										$sql   .= "FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBUSUARIOCOMIS D ";
										$sql   .= "WHERE D.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
                                        $sql   .= "AND D.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
										$sql   .= "AND D.CCOMLICODI = A.CCOMLICODI ";
                                        $sql   .= "AND A.CGREMPCODI = D.CGREMPCODI ";
										$sql   .= "AND A.CCOMLICODI = B.CCOMLICODI  ";
										$sql   .= "AND make_date(a.alicpoanop, 1, 1) > CURRENT_DATE - INTERVAL '5 YEARS'"; //CR 206442 make_date
										$sql   .= "ORDER BY B.ECOMLIDESC ASC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
										  	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
													
													//Sql que verifica se a licitacao é do tipo novo ou antigo
													$sql = "SELECT COUNT(*) AS QUANTIDADE FROM SFPC.TBSOLICITACAOLICITACAOPORTAL WHERE CLICPOPROC = $Linha[0] AND  ALICPOANOP = $Linha[1] AND  CGREMPCODI  = $Linha[3] AND CCOMLICODI = $Linha[2] AND CORGLICODI = $Linha[4] ";
													$qtdSolicitacoes = resultValorUnico(executarSQL($db, $sql));
													if($qtdSolicitacoes>0){
														$novaTela = 1;
													}else{
														$novaTela = 0;
													}
														if( $Linha[2] != $ComissaoCodigoAnt ){
																$ComissaoCodigoAnt = $Linha[2];
																echo "<option value=\"\">$Linha[5]</option>\n" ;
														}
														$NProcesso = substr($Linha[0] + 10000,1);
														echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$novaTela\">&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[1]</option>\n" ;
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
<script language="javascript" type="">
<!--
document.Licitacao.LicitacaoProcessoAnoComissao.focus();
//-->
</script>
<?php ////($sql); ?>
</body>
</html>
