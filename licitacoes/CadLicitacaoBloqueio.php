<?php
# -------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadLicitacaoBloqueio.php
# Autor:    Roberta Costa
# Data:     22/12/04
# Objetivo: Programa de Inclusão de Bloquei na Licitação
# OBS.:     Tabulação 2 espaços
# -------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/oracle/licitacoes/RotValidaBloqueio.php' );

# Ano Atual do Exercicio #
$AnoExercicio = AnoExercicio();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                 = $_POST['Botao'];
		$ProgramaOrigem	       = $_POST['ProgramaOrigem'];
		$Existe        	       = $_POST['Existe'];
		$Bloqueio              = $_POST['Bloqueio'];
		$Exercicio             = $_POST['Exercicio'];
		$Orgao                 = $_POST['Orgao'];
		$Unidade               = $_POST['Unidade'];
		$Funcao        	       = $_POST['Funcao'];
		$Subfuncao    	       = $_POST['Subfuncao'];
		$Programa     	       = $_POST['Programa'];
		$TipoProjAtiv  	       = $_POST['TipoProjAtiv'];
		$ProjAtividade 	       = $_POST['ProjAtividade'];
		$Elemento1    	       = $_POST['Elemento1'];
		$Elemento2    	       = $_POST['Elemento2'];
		$Elemento3    	       = $_POST['Elemento3'];
		$Elemento4      	     = $_POST['Elemento4'];
		$Fonte        	       = $_POST['Fonte'];
		$Dotacao      	       = $_POST['Dotacao'];
		$Valor                 = $_POST['Valor'];
		$AlteraValorHomologado = $_POST['AlteraValorHomologado'];
}else{
		$ProgramaOrigem	       = $_GET['ProgramaOrigem'];
		$Existe             	 = $_GET['Existe'];
		$Bloqueio              = $_GET['Bloqueio'];
		$Exercicio             = $_GET['Exercicio'];
		$Orgao                 = $_GET['Orgao'];
		$Unidade               = $_GET['Unidade'];
		$Funcao        	       = $_GET['Funcao'];
		$Subfuncao    	       = $_GET['Subfuncao'];
		$Programa     	       = $_GET['Programa'];
		$TipoProjAtiv  	       = $_GET['TipoProjAtiv'];
		$ProjAtividade 	       = $_GET['ProjAtividade'];
		$Elemento1    	       = $_GET['Elemento1'];
		$Elemento2    	       = $_GET['Elemento2'];
		$Elemento3    	       = $_GET['Elemento3'];
		$Elemento4    	       = $_GET['Elemento4'];
		$Fonte        	       = $_GET['Fonte'];
		$Valor                 = $_GET['Valor'];
		$AlteraValorHomologado = $_GET['AlteraValorHomologado'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadLicitacaoBloqueio.php";

if( $Botao == "Incluir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $Bloqueio == "" ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.LicitacaoBloqueio.Bloqueio.focus();\" class=\"titulo2\">Número do Bloqueio</a>";
		}else{
				$Url = "licitacoes/RotValidaBloqueio.php?NomePrograma=".urlencode("CadLicitacaoBloqueio.php")."&ProgramaOrigem=".urlencode($ProgramaOrigem)."&Bloqueio=$Bloqueio&Orgao=$Orgao&Unidade=$Unidade&Exercicio=$Exercicio";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				Redireciona($Url);
				exit;
		}
}
if( $Existe != "" ){
		$Mens     = 0;
		$Mensagem = "";
		if( $Existe == "N" ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Informe: <a href=\"javascript:document.LicitacaoBloqueio.Bloqueio.focus();\" class=\"titulo2\">Número de Bloqueio Válido</a>";
		}elseif( $Existe == "S" ){
				if( $AlteraValorHomologado == "N" ){
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.LicitacaoBloqueio.Bloqueio.focus();\" class=\"titulo2\">Número de Bloqueio Já Ajustado no SOFIN</a>";
						$Existe    = "N";
				}
				if( $Mens == 0 ){
					  $db     = Conexao();
					  $sql    = "SELECT TUNIDOEXER, CUNIDOORGA, CUNIDOCODI, ALICBLSEQU ";
					  $sql   .= "  FROM SFPC.TBLICITACAOBLOQUEIOORCAMENT ";
						$sql   .= " WHERE ALICBLSEQU = $Bloqueio AND TUNIDOEXER = $Exercicio ";
						$sql   .= "   AND CUNIDOORGA = $Orgao AND CUNIDOCODI = $Unidade  ";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: 103\nSql: $sql");
						}else{
								$Rows = $result->numRows();
								if( $Rows != 0 ){
										$Mens     = 1;
										$Tipo     = 2;
										$Mensagem = "Número do Bloqueio já foi informado em outro Processo Licitatório";
										$Existe   = "N";
								}else{
										$Dotacao = NumeroDotacao($Funcao,$Subfuncao,$Programa,$Orgao,$Unidade,$TipoProjAtiv,$ProjAtividade,$Elemento1,$Elemento2,$Elemento3,$Elemento4,$Fonte);
								}
						}
						$db->disconnect();
				}
		}
}
?>
<html>
<head>
<title>Portal de Compras - Incluir Bloqueio</title>
<script language="javascript" type="">
function voltar(){
	self.close();
}
function enviar(valor){
	document.LicitacaoBloqueio.Botao.value = valor;
	document.LicitacaoBloqueio.submit();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
</head>
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<form action="CadLicitacaoBloqueio.php" method="post" name="LicitacaoBloqueio">
<table cellpadding="3" border="0" summary="">
	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php }else{ echo "<br>";} ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					INCLUIR - BLOQUEIO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir um bloqueio para a licitação, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
				        <td>
				          <table class="textonormal" border="0" align="left" summary="">
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" height="20">Exercício</td>
				              <td class="textonormal"><?php	echo $Exercicio = $AnoExercicio; ?></td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Número do Bloqueio* </td>
				              <td class="textonormal">
				                <input type="text" name="Orgao" size="2" maxlength="2" value="<?php echo $Orgao; ?>" class="textonormal">.
				                <input type="text" name="Unidade" size="2" maxlength="2" value="<?php echo $Unidade; ?>" class="textonormal">.1.
				                <input type="text" name="Bloqueio" size="5" maxlength="4" value="<?php echo $Bloqueio; ?>" class="textonormal">
				              </td>
				            </tr>
				          </table>
				        </td>
				     	</tr>
				      <tr>
				        <td class="textonormal" align="right">
			            <input type="hidden" name="Existe" value="<?php echo $Existe; ?>">
			            <input type="hidden" name="Exercicio" value="<?php echo $Exercicio; ?>">
			            <input type="hidden" name="Funcao" value="<?php echo $Funcao; ?>">
			            <input type="hidden" name="Subfuncao" value="<?php echo $Subfuncao; ?>">
			            <input type="hidden" name="Programa" value="<?php echo $Programa; ?>">
			            <input type="hidden" name="TipoProjAtiv" value="<?php echo $TipoProjAtiv; ?>">
			            <input type="hidden" name="ProjAtividade" value="<?php echo $ProjAtividade; ?>">
			            <input type="hidden" name="Elemento1" value="<?php echo $Elemento1; ?>">
			            <input type="hidden" name="Elemento2" value="<?php echo $Elemento2; ?>">
			            <input type="hidden" name="Elemento3" value="<?php echo $Elemento3; ?>">
			            <input type="hidden" name="Elemento4" value="<?php echo $Elemento4; ?>">
			            <input type="hidden" name="Fonte" value="<?php echo $Fonte; ?>">
			            <input type="hidden" name="Valor" value="<?php echo $Valor; ?>">
			            <input type="hidden" name="Dotacao" value="<?php echo $Dotacao; ?>">
			            <input type="hidden" name="AlteraValorHomologado" value="<?php echo $AlteraValorHomologado; ?>">
			            <input type="hidden" name="ProgramaOrigem" value="<?php echo $ProgramaOrigem; ?>">
				          <input type="button" value="Incluir" class="botao" onClick="javascript:enviar('Incluir');">
 		            	<input type="button" value="Voltar" class="botao" onClick="javascript:voltar();">
			            <input type="hidden" name="Botao" value="">
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
<script language="javascript" type="">
<!--
window.focus();
document.LicitacaoBloqueio.Orgao.focus();
if( document.LicitacaoBloqueio.Existe.value == 'S' ){
	opener.document.<?php echo $ProgramaOrigem; ?>.NumBloqueio.value = document.LicitacaoBloqueio.Bloqueio.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumOrgao.value = document.LicitacaoBloqueio.Orgao.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumUnidade.value = document.LicitacaoBloqueio.Unidade.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumFuncao.value = document.LicitacaoBloqueio.Funcao.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumSubfuncao.value = document.LicitacaoBloqueio.Subfuncao.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumPrograma.value = document.LicitacaoBloqueio.Programa.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumTipoProjAtiv.value = document.LicitacaoBloqueio.TipoProjAtiv.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumProjAtividade.value = document.LicitacaoBloqueio.ProjAtividade.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumElemento1.value = document.LicitacaoBloqueio.Elemento1.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumElemento2.value = document.LicitacaoBloqueio.Elemento2.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumElemento3.value = document.LicitacaoBloqueio.Elemento3.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumElemento4.value = document.LicitacaoBloqueio.Elemento4.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumFonte.value = document.LicitacaoBloqueio.Fonte.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumDotacao.value = document.LicitacaoBloqueio.Dotacao.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumValor.value = document.LicitacaoBloqueio.Valor.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.NumExercicio.value = document.LicitacaoBloqueio.Exercicio.value;
	opener.document.<?php echo $ProgramaOrigem; ?>.submit();
	self.close();
}
//-->
</script>
</body>
</html>
