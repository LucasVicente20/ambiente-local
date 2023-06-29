<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelFornecedorClasse.php
# Autor:    Roberta Costa
# Data:     26/10/04
# Objetivo: Programa de Relatório dos Fornecedores Inscritos
#---------------------
# Alterado: Ariston Cordeiro
# Data:     30/05/11-	Alterando para mostrar grupos ao invés de classes
#---------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Daniel Augusto
# Data:		16/05/2023
# Objetivo: Tarefa Redmine 282903
# -----------------------------------------------------------------------------------------------------------------------------------------------
# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/RelFornecedorGrupo.php' );
AddMenuAcesso( '/fornecedores/RelFornecedorGrupoPdf.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao     = $_POST['Botao'];
		$TipoGrupo = $_POST['TipoGrupo'];
		$Grupo     = $_POST['Grupo'];
}else{
		$Mens     = $_GET['Mens'];
		$Mensagem = urldecode($_GET['Mensagem']);
		$Tipo			= $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if( $Botao == "Limpar" ){
	  header("location: RelFornecedorGrupo.php");
	  exit;
}elseif( $Botao == "Imprimir" ){
		$Mens				= 0;
		$Mensagem 	= "Informe: ";
		if( $TipoGrupo == "" ){
		  	$Mens 		 = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelFornecedorGrupo.TipoGrupo.focus();\" class=\"titulo2\">Tipo de Grupo</a>";
		}
		if( $Grupo == "" ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens 		 = 1;
		  	$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.RelFornecedorGrupo.Grupo.focus();\" class=\"titulo2\">Grupo</a>";
		}
		if( $Mens == 0 ){
				$Url = "RelFornecedorGrupoPdf.php?TipoGrupo=$TipoGrupo&Grupo=$Grupo&".mktime();
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
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function enviar(valor){
	document.RelFornecedorGrupo.Botao.value=valor;
	document.RelFornecedorGrupo.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelFornecedorGrupo.php" method="post" name="RelFornecedorGrupo">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Relatórios > Fornecedores por Fornecimento
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2">
	  	<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
	  </td>
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
	        	<table border="1" cellpadding="3" cellspacing="0"  bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
		    					RELATÓRIO DOS FORNECEDORES POR FORNECIMENTO
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para imprimir os Fornecedores por Fornecimento, preencha os campos abaixo e clique no botão "Imprimir".
										Para limpar os campos, clique no botão "Limpar". Os campos obrigatórios estão com *.<br><br>
						        Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
	          	   	</p>
	          		</td>
	          	</tr>
		        	<tr>
								<td class="textonormal">
									<table class="textonormal" border="0" align="left" summary="" width="100%">
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7" width="30%">Tipo de Grupo<span style="color: red;">*</span></td>
				              <td class="textonormal">
				              	<input type="radio" name="TipoGrupo" value="M" onClick="submit(); document.RelFornecedorGrupo.Botao.value='';" <?php if( $TipoGrupo == "" or $TipoGrupo == "M" ){ echo "checked"; }?> > Material
				              	<input type="radio" name="TipoGrupo" value="S" onClick="submit(); document.RelFornecedorGrupo.Botao.value='';" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> > Serviço
				              </td>
				            </tr>
				            <tr>
				              <td class="textonormal" bgcolor="#DCEDF7">Grupo<span style="color: red;">*</span></td>
				              <td class="textonormal">
				              	<select name="Grupo" class="textonormal" onChange="submit(); document.RelFornecedorGrupo.Botao.value='';">
				              		<option value="">Selecione um Grupo...</option>
				              		<?php
				              		$db   = Conexao();
  												$sql  = "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO WHERE FGRUMSSITU = 'A' AND ";
  												if( $TipoGrupo == "M" or $TipoGrupo == "" ){
  														$sql .= "FGRUMSTIPO = 'M'";
  												}else{
  														$sql .= "FGRUMSTIPO = 'S'";
  												}
  												$sql .= "ORDER BY 2";
  												$res  = $db->query($sql);
												  if( PEAR::isError($res) ){
														  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $res->fetchRow() ){
					          	      			$Descricao   = substr($Linha[1],0,75);
					          	      			if( $Linha[0] == $Grupo ){
										    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
								      	      		}else{
										    	      			echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
								      	      		}
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
			      		<td align="right">
			    				<input type="button" name="Imprimir" value="Imprimir" class="botao" onclick="javascript:enviar('Imprimir');">
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
