<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabGrupoMaterialServicoSelecionar.php
# Autor:    Rossana Lira
# Data:     01/02/05
# Objetivo: Programa de Manutenção de Grupo de Material e Serviço
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/TabGrupoMaterialServicoAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$TipoGrupo	 = $_POST['TipoGrupo'];
		$TipoMaterial= $_POST['TipoMaterial'];
		$GrupoCodigo = $_POST['GrupoCodigo'];
		$Critica     = $_POST['Critica'];
}else{
		$Critica     = $_GET['Critica'];
		$Mensagem    = urldecode($_GET['Mensagem']);
		$Mens        = $_GET['Mens'];
		$Tipo        = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if( $Critica == 1 ){
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $GrupoCodigo == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Grupo.GrupoCodigo.focus();\" class=\"titulo2\">Grupo</a>";
    }else{
    		$Url = "TabGrupoMaterialServicoAlterar.php?GrupoCodigo=$GrupoCodigo";
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
<form action="TabGrupoMaterialServicoSelecionar.php" method="post" name="Grupo">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Grupo > Manter
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
	           MANTER - GRUPO DE MATERIAL E SERVIÇO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar/excluir um Grupo de Material ou Serviço já cadastrado, selecione o Tipo, o Grupo e clique no botão "Selecionar".<br>
             "ATENÇÃO: No caso de ativação algum grupo inativado, lembrar de integrá-lo a um sub-elemento de despesa."
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table summary="">
	            <tr>
	              <td class="textonormal" bgcolor="#DCEDF7" width="40%">Tipo de Grupo</td>
	              <td class="textonormal">
	              	<input type="radio" name="TipoGrupo" value="M" onClick="javascript:document.Grupo.Critica.value=0;document.Grupo.submit();" <?php if( $TipoGrupo == "M" ){ echo "checked"; } ?> > Material
	              	<input type="radio" name="TipoGrupo" value="S" onClick="javascript:document.Grupo.Critica.value=0;document.Grupo.submit();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> > Serviço
	              </td>
	            </tr>
	            <?php if ($TipoGrupo == "M") { ?>
		            <tr>
		              <td class="textonormal" bgcolor="#DCEDF7" width="40%">Tipo de Material</td>
		              <td class="textonormal">
		              	<input type="radio" name="TipoMaterial" value="C" onClick="javascript:document.Grupo.Critica.value=0;document.Grupo.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
		              	<input type="radio" name="TipoMaterial" value="P" onClick="javascript:document.Grupo.Critica.value=0;document.Grupo.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
		              </td>
		            </tr>
 		          <?php } else {
 		          				$TipoMaterial = "";
 		          } ?>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Grupo </td>
                <td class="textonormal">
                  <select name="GrupoCodigo" class="textonormal">
                  	<option value="">Selecione um Grupo...</option>
                  	<?php
											if( $TipoGrupo == "M" or $TipoGrupo == "S") {
			                	$db     = Conexao();
												if( $TipoMaterial == "C" or $TipoMaterial == "P") {
													$sql 		= "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
													$sql   .= "WHERE  FGRUMSTIPO = 'M' AND FGRUMSTIPM = '$TipoMaterial' ";
			                		$sql   .= "ORDER  BY EGRUMSDESC";
			                		$result = $db->query($sql);
			                		if (PEAR::isError($result)) {
													    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															while( $Linha = $result->fetchRow() ){
				          	      			$Descricao   = substr($Linha[1],0,75);
				          	      			if( $Linha[0] == $Grupo ){
									    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
							      	      		}else{
									    	      			echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
							      	      		}
							      	      	}
						              }
			                	}	else {
													if( $TipoGrupo == "S" ){
				                	  # Mostra os grupos cadastrados #
				                		$db     = Conexao();
														$sql 		= "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
														$sql   .= "WHERE FGRUMSTIPO = 'S' ORDER BY EGRUMSDESC";
				                		$result = $db->query($sql);
				                		if (PEAR::isError($result)) {
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																while( $Linha = $result->fetchRow() ){
					          	      			$Descricao   = substr($Linha[1],0,75);
					          	      			if( $Linha[0] == $Grupo ){
										    	      			echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
								      	      		}else{
										    	      			echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
								      	      		}
							                	}
							              }
				  	              }
				  	            }
				  	            $db->disconnect();
		  	              }
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
<script language="javascript" type="">
<!--
document.Grupo.GrupoCodigo.focus();
//-->
</script>
</body>
</html>
