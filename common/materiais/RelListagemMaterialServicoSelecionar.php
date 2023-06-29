<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelListagemMaterialServicoSelecionar.php
# Autor:    Rossana Lira
# Data:     10/02/05
# Objetivo: Programa de Seleção da Listagem de Material e Serviço
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso("/materiais/RelListagemMaterialServicoSelecionar.php");
AddMenuAcesso("/materiais/RelListagemMaterialServicoPdf.php");

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$TipoGrupo	 	= $_POST['TipoGrupo'];
		$Grupo	 		  = $_POST['Grupo'];
		$GrupoTodos	  = $_POST['GrupoTodos'];
		$Classe 			= $_POST['Classe'];
		$ClasseTodas	= $_POST['ClasseTodas'];
		$Ordem			 	= $_POST['Ordem'];
		$Botao	     	= $_POST['Botao'];
}else{
		$Mensagem    = urldecode($_GET['Mensagem']);
		$Mens        = $_GET['Mens'];
		$Tipo        = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RelListagemMaterialServicoSelecionar.php";

# Critica dos Campos #

if( $Botao == "Limpar" ){
		header("location: RelListagemMaterialServicoSelecionar.php");
		exit();
}
if( $Botao == "Imprimir" ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Grupo == "") and ($GrupoTodos == "")){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Listagem.Grupo.focus();\" class=\"titulo2\">Grupo ou marque a opção 'Todos' </a>";
		}
    if( ($Classe == "" )and ($ClasseTodas == "")) {
    		if ($Mens == 1) {$Mensagem .= ", ";}
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Listagem.Classe.focus();\" class=\"titulo2\">Classe ou marque a opção 'Todas'</a>";
    }
    if ($Mens == 0){
    		$Url = "RelListagemMaterialServicoPdf.php?TipoGrupo=$TipoGrupo&Grupo=$Grupo&GrupoTodos=$GrupoTodos&Classe=$Classe&ClasseTodas=$ClasseTodas&Ordem=$Ordem&".mktime();
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
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Listagem.Botao.value=valor;
	document.Listagem.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="RelListagemMaterialServicoSelecionar.php" method="post" name="Listagem">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Relatórios > Listagem
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
	           LISTAGEM DE MATERIAL E SERVIÇO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para Emitir a Listagem de Material e Serviço, selecione o Grupo e a Classe desejada e clique no botão "Imprimir".<br>
             Se desejar imprimir todos os grupos e/ou classes marque a opção "Todos" ou "Todas" respectivamente. Para limpar os itens de seleção clique no botão "Limpar".<br><br>
						 Se você não possui o Acrobat Reader, clique <a href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)" class="titulo2">AQUI</a> para fazer o download.
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table summary="">
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" >Tipo de Grupo*</td>
	              <td class="textonormal">
	              	<input type="radio" name="TipoGrupo" value="M" onClick="javascript:document.Listagem.Botao.value=0;javascript:document.Listagem.Grupo.value=0;javascript:document.Listagem.GrupoTodos.value=0;javascript:document.Listagem.ClasseTodas.value=0;document.Listagem.submit();" <?php if( $TipoGrupo == "" or $TipoGrupo == "M" ){ echo "checked"; } ?> > Material
	              	<input type="radio" name="TipoGrupo" value="S" onClick="javascript:document.Listagem.Botao.value=0;javascript:document.Listagem.Grupo.value=0;;javascript:document.Listagem.GrupoTodos.value=0;javascript:document.Listagem.ClasseTodas.value=0;document.Listagem.submit();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> > Serviço
	              </td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7">Grupo* </td>
	              <td class="textonormal">
	              	<select name="Grupo" class="textonormal" onChange="javascript:document.Listagem.Botao.value=0;document.Listagem.GrupoTodos.value=0;document.Listagem.submit();">
	              		<option value="">Selecione um Grupo...</option>
	              		<?php
	              		$db   = Conexao();
										$sql  = "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO WHERE ";
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
		          	      			$Descricao   = substr($Linha[1],0,50);
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
	              <td class="textonormal">
	              	<input type="checkbox" name="GrupoTodos" value="1" onClick="javascript:document.Listagem.Botao.value=0;javascript:document.Listagem.Grupo.value=0;document.Listagem.submit();" <?php if( $GrupoTodos == "1" ){ echo "checked";	} ?> > Todos
	              </td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7">Classe* </td>
	              <td class="textonormal">
	              	<select name="Classe" class="textonormal" onChange="javascript:document.Listagem.Botao.value=0;document.Listagem.ClasseTodas.value=0;document.Listagem.submit();"> <?php echo $Classe;?>>
	              		<option value="">Selecione uma Classe...</option>
	              		<?php
	              		if( $Grupo != "" ){
			              		$db  = Conexao();
												$sql = "SELECT CCLAMSCODI,ECLAMSDESC FROM SFPC.TBCLASSEMATERIALSERVICO WHERE CGRUMSCODI = $Grupo ";
												$sql.= "ORDER BY 2";
												$res = $db->query($sql);
											  if( PEAR::isError($res) ){
													  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														while( $Linha = $res->fetchRow() ){
				          	      			$Descricao = substr($Linha[1],0,50);
				          	      			if( $Linha[0] == $Classe){
									    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
							      	      		}else{
																		echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
							      	      		}
					                	}
												}
		  	              	$db->disconnect();
		  	            }
	              		?>
	              	</select>
	              </td>
	              <td class="textonormal">
	              	<input type="checkbox" name="ClasseTodas" value="1" onClick="javascript:document.Listagem.Botao.value=0;javascript:document.Listagem.Classe.value=0;document.Listagem.submit();" <?php if( $ClasseTodas == "1" ){ echo "checked"; } ?> > Todas
	              </td>
	            </tr>
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7">Ordem*</td>
	              <td class="textonormal">
	              	<input type="radio" name="Ordem" value="D" <?php if( $Ordem == "" or $Ordem == "D" ){ echo "checked"; } ?> > Descrição
	              	<input type="radio" name="Ordem" value="C" <?php if( $Ordem == "C" ){ echo "checked"; }?> > Código
	              </td>
	            </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
    				<input type="button" value="Imprimir" class="botao" onclick="javascript:enviar('Imprimir');">
    				<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
      			<input type="hidden" name="Botao" value="">
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
document.Listagem.Grupo.focus();
//-->
</script>
</body>
</html>
