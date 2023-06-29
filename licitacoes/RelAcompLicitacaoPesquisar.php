<?php
#-----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAcompLicitacaoPesquisar.php
# Autor:    Roberta Costa
# Data:     20/08/03
# Objetivo: Programa de Pesquisa do Relatório de Acompanhamento da Licitação
# OBS.:     Tabulação 2 espaços
#-----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/RelAcompLicitacaoResultado.php' );
AddMenuAcesso( '/licitacoes/RelAcompLicitacaoPesquisar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica          = $_POST['Critica'];
		$Botao            = $_POST['Botao'];
		$Fase             = $_POST['Fase'];
		$GrupoCodigo      = $_POST['GrupoCodigo'];
		$ComissaoCodigo   = $_POST['ComissaoCodigo'];
		$ModalidadeCodigo = $_POST['ModalidadeCodigo'];
		$Ano              = $_POST['Ano'];
}else{
		$GrupoCodigo      = $_GET['GrupoCodigo'];
		$ComissaoCodigo   = $_GET['ComissaoCodigo'];
		$ModalidadeCodigo = $_GET['ModalidadeCodigo'];
		$Fase             = $_GET['Fase'];
		$Critica          = $_GET['Critica'];
		$Mensagem         = $_GET['Mensagem'];
		$Mens             = $_GET['Mens'];
		$Tipo             = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelAcompLicitacaoPesquisar.php";

if( $Botao == "Pesquisar" ){
		$Mens      = 0;
		$Mensagem .= "Informe : ";
		if( $Ano == "" ){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Acomp.Ano.focus();\" class=\"titulo2\">Ano</a>";
		}else{
				if( ! SoNumeros($Ano) ){
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.Acomp.Ano.focus();\" class=\"titulo2\">Ano Válido</a>";
				}else{
						if( strlen($Ano) < 4 ){
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.Acomp.Ano.focus();\" class=\"titulo2\">Ano com 4 digítos</a>";
						}else{
								if( $Ano > date("Y") ){
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.Acomp.Ano.focus();\" class=\"titulo2\">Ano menor ou igual ao ano atual</a>";
								}
						}
				}
		}
  	if( $Mens == 0 ){
  			$Url = "RelAcompLicitacaoResultado.php?GrupoCodigo=$GrupoCodigo&ComissaoCodigo=$ComissaoCodigo&ModalidadeCodigo=$ModalidadeCodigo&Fase=$Fase&Ano=$Ano";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
  			header("location: ".$Url);
  			exit();
  	}
}elseif( $Botao == "Limpar" ){
		$Url = "RelAcompLicitacaoPesquisar.php?GrupoCodigo=&GrupoCodigo=&ModalidadeCodigo=&Fase=2&Ano=";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url );
	  exit();
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
	document.Acomp.Botao.value=valor;
	document.Acomp.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelAcompLicitacaoPesquisar.php" method="post" name="Acomp">
<br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2"><br>
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Relatório > Acompanhamento das Licitações
    </td>
	  <td></td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					RELATÓRIO DE ACOMPANHAMENTO DAS LICITAÇÕES - PESQUISAR
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal" >
	      	    		<p align="justify">
	        	    		Para gerar o Relatório de Acompanhamento das Licitações, selecione o item de pesquisa e clique no botão "Pesquisar". Para limpar a pesquisa, clique no botão "Limpar". Só irão aparecer licitações que possuem pelo menos uma fase cadastrada.
	        	    		Os campos com * são obrigatórios.
	          	   	</p>
	          		</td>
		        	</tr>
	  	        <tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" summary="">
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Grupo</td>
	          	    		<td class="textonormal" >
                        <select name="GrupoCodigo" value="" class="textonormal">
			                  	<option value="">Todos os Grupo...</option>
			                  	<!-- Mostra os grupos cadastrados -->
			                  	<?
		                  		$db  = Conexao();
		                  		$sql = "select CGREMPCODI, EGREMPDESC from SFPC.TBGRUPOEMPRESA ";
		                  		if( $_SESSION['_cgrempcodi_'] >= 0){
				  	              		$sql .= "WHERE CGREMPCODI <> 0 ORDER BY EGREMPDESC";
				                  }
		                  		$result = $db->query($sql);
													if( PEAR::isError($result) ){
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}
													while( $Linha = $result->fetchRow() ){
			          	      			echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
		  	                	}
		    	              	$db->disconnect();
			      	            ?>
			                  </select>
										  	<input type="hidden" name="Critica" value="1" size="1">
										  </td>
	            			</tr>
	            			<tr>
		              		<td class="textonormal"  bgcolor="#DCEDF7">Comissão </td>
		              		<td class="textonormal">
		  				  	      <select name="ComissaoCodigo" class="textonormal">
													<option value="">Todas as Comissões...</option>
													<?
													$db     = Conexao();
													$sql    = "SELECT CCOMLICODI,ECOMLIDESC,CGREMPCODI ";
													$sql   .= "  FROM SFPC.TBCOMISSAOLICITACAO ";
													$sql   .= " ORDER BY CGREMPCODI,ECOMLIDESC";
		                  		$result = $db->query($sql);
													if( PEAR::isError($result) ){
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
															   	if( $Linha[0] == $ComissaoCodigo ){
																    	echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
															   	}else{
																      echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
															   	}
											     		}
											    }
		    	              	$db->disconnect();
													?>
											  </select>
										  </td>
	            			</tr>
	            			<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Modalidade</td>
											<td class="textonormal" >
		  				  	      <select name="ModalidadeCodigo" class="textonormal">
													<option value="">Todas as Modalidades...</option>
													<?
											    $db     = Conexao();
													$sql    = "SELECT CMODLICODI, EMODLIDESC ";
													$sql   .= "  FROM SFPC.TBMODALIDADELICITACAO ";
													$sql   .= " ORDER BY AMODLIORDE";
		                  		$result = $db->query($sql);
													if( PEAR::isError($result) ){
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
															   	if( $Linha[0] == $ModalidadeCodigo ){
															    		echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
															   	}else{
															      	echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
															   	}
											     		}
											    }
									     		$db->disconnect();
													?>
											  </select>
										  </td>
										</tr>
										<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Processos em Andamento</td>
											<td class="textonormal" >
												<?php if( $Fase == "" ){ $Fase = 1; }?>
												<input type="radio" name="Fase" value="1" <?php if( $Fase == 1 ){ echo "checked "; }?>> Sim
		          	    		<input type="radio" name="Fase" value="2" <?php if( $Fase == 2 ){ echo "checked "; }?>> Não
		  				  	    </td>
										</tr>
	       						<tr>
										  <td class="textonormal" bgcolor="#DCEDF7">Ano*</td>
		              		<td class="textonormal">
		              			<?php if( $Ano == "" ){ $Ano = date("Y"); }?>
												<input type="text" name="Ano" size="4" maxlength="4" value="<?php echo $Ano?>" class="textonormal">
										  </td>
										</tr>
	          			</table>
		          	</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
        	      	<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
        	      	<input type="button" name="Limpar" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
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
</body>
</html>
<script language="javascript" type="">
<!--
document.Acomp.GrupoCodigo.focus();
//-->
</script>
