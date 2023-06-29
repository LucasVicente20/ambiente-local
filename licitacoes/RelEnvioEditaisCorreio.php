<?php
#--------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelEnvioEditaisCorreio.php
# Autor:    Roberta Costa
# Data:     24/05/03
# Objetivo: Programa de Relatório de Envio de Editais Via Correio Eletrônico
# OBS.:     Tabulação 2 espaços
#						Só irão aparecer as comissões de Licitação cadastradas p/o usuário logado
#--------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		04/07/2018
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
AddMenuAcesso( '/licitacoes/RelEnvioEditaisListagem.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica          = $_POST['Critica'];
		$Licitacao        = $_POST['Licitacao'];
		$Processo         = $_POST['Processo'];
		$AnoProcesso      = $_POST['AnoProcesso'];
		$GrupoCodigo      = $_POST['GrupoCodigo'];
		$ComissaoCodigo   = $_POST['ComissaoCodigo'];
		$OrgaoCodigo      = $_POST['OrgaoCodigo'];
		$ModalidadeCodigo = $_POST['ModalidadeCodigo'];
		$Valor            = $_POST['Valor'];
}else{
		$Critica          = $_GET['Critica'];
		$Mensagem         = urldecode($_GET['Mensagem']);
		$Mens             = $_GET['Mens'];
		$Tipo             = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelEnvioEditaisCorreio.php";

# Critica dos Campos #
if( $Critica == 1 ) {
	  if( $Licitacao == ""){

	  	  $Mens = 0;$Tipo=1;
	  	  $Mensagem = "<a href =\"javascript:document.Relatorio.Licitacao.focus();\" class=\"titulo2\">Processo</a>";
	  }else{
	      $NLicitacao       = explode("_",$Licitacao);
				$Processo         = $NLicitacao[0];
				$AnoProcesso      = $NLicitacao[1];
				$OrgaoCodigo      = $NLicitacao[2];
				$ComissaoCodigo   = $NLicitacao[3];
				$GrupoCodigo      = $NLicitacao[4];
				$ModalidadeCodigo = $NLicitacao[5];
				$Url = "RelEnvioEditaisListagem.php?Processo=$Processo&AnoProcesso=$AnoProcesso&GrupoCodigo=$GrupoCodigo&ComissaoCodigo=$ComissaoCodigo&OrgaoCodigo=$OrgaoCodigo";
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
function Relatorio(){
	document.Relatorio.Codigo.value = document.Relatorio.Participantes.options[document.Relatorio.Participantes.selectedIndex].value;
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelEnvioEditaisCorreio.php" method="post" name="Relatorio">
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2"><br>
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Relatórios > Envio de Editais
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					RELATÓRIO DE ENVIO DE EDITAIS - SELECIONAR
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" >
	      	    		<p align="justify">
	        	    		Para gerar o relatório, selecione o Processo e clique no botão "Gerar" para exibir os participantes que receberam o documento pelo correio eletrônico.
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
			            <table border="0" class="titulo3" summary="">
			              <tr>
			                <td class="textonormal" bgcolor="#DCEDF7">Processo </td>
			                <td class="textonormal" bgcolor="#FFFFFF">
			                  <select name="Licitacao" class="textonormal">
			                  	<option value="">Selecione um Processo Licitatório...</option>
			                  	<!-- Mostra as licitações cadastradas -->
			                  	<?php
													$db     = Conexao();
													$sql    = "SELECT A.CLICPOPROC, A.ALICPOANOP, A.CORGLICODI, B.ECOMLIDESC, ";
													$sql   .= "       B.CCOMLICODI, C.CGREMPCODI, C.EGREMPDESC, A.CMODLICODI ";
													$sql   .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBGRUPOEMPRESA C, SFPC.TBUSUARIOCOMIS D ";
													$sql   .= " WHERE D.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." AND D.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
													$sql   .= "   AND D.CCOMLICODI = A.CCOMLICODI AND  A.CGREMPCODI = D.CGREMPCODI ";
													$sql   .= "   AND A.CCOMLICODI = B.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ";
													$sql   .= "   AND MAKE_DATE(A.ALICPOANOP,1,1) > CURRENT_DATE - INTERVAL '5 YEARS' "; //CR 206442 MAKE_DATE
													$sql   .= " ORDER BY B.ECOMLIDESC ASC, A.ALICPOANOP DESC, A.CLICPOPROC DESC ";
													$result = $db->query($sql);
													if( PEAR::isError($result) ){
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															$Rows              = $result->numRows();
															$ComissaoCodigoAnt = "";
															if( $Rows == 0 ){ $Valor = 1; }
															while( $Linha = $result->fetchRow() ){
																	if( $Linha[4] != $ComissaoCodigoAnt ){
																			$ComissaoCodigoAnt = $Linha[4];
																			echo "<option value=\"\">$Linha[3]</option>\n" ;
																	}
																	$NProcesso = substr($Linha[0] + 10000,1);
																	echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[4]_$Linha[5]_$Linha[7]\">&nbsp;&nbsp;&nbsp;$NProcesso/$Linha[1]</option>\n" ;
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
        	      	<input type="hidden" name="Critica" value="1">
							    <input type="hidden" name="Processo" value="<?php echo $Linha[0]; ?>">
							    <input type="hidden" name="AnoProcesso" value="<?php echo $Linha[1]; ?>">
							    <input type="hidden" name="GrupoCodigo" value="<?php echo $Linha[2]; ?>">
							    <input type="hidden" name="ComissaoCodigo" value="<?php echo $Linha[4]; ?>">
							    <input type="hidden" name="OrgaoCodigo" value="<?php echo $Linha[5]; ?>">
							    <input type="hidden" name="ModalidadeCodigo" value="<?php echo $Linha[7]; ?>">
          	    	<input type="hidden" name="Valor" value="<?php echo $Valor; ?>">
          	    	<input type="submit" name="Gerar" value="Gerar" class="botao">
		          	</td>
		        	</tr>
    	  	  </table>
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
document.Relatorio.Licitacao.focus();
//-->
</script>
