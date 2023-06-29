<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabGrupoMaterialServicoAlterar.php
# Autor:    Rossana Lira
# Data:     01/02/05
# Objetivo: Programa de Alteração do Grupo de Material e Serviço
#------------------------------------
# Alterado: Carlos Abreu
# Data:     02/01/2007 - Campo Tipo de Material para Custo acrescentado
# Alterado: Ariston Cordeiro
# Data:     10/03/2010 - CR4223- Correção relacionada ao preenchimento de $TipoGrupo para grupos do tipo 'Serviço'
#-----------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso("/materiais/TabGrupoMaterialServicoExcluir.php");
AddMenuAcesso("/materiais/TabGrupoMaterialServicoSelecionar.php");

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                = $_POST['Botao'];
		$Critica              = $_POST['Critica'];
		$GrupoCodigo          = $_POST['GrupoCodigo'];
		$TipoGrupo				    = $_POST['TipoGrupo'];
		$TipoMaterial         = $_POST['TipoMaterial'];
		$GrupoDescricao       = strtoupper2(trim($_POST['GrupoDescricao']));
		$Situacao             = $_POST['Situacao'];
}else{
		$GrupoCodigo          = $_GET['GrupoCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Redireciona para a página de excluir #
if( $Botao == "Excluir" ){
		$Url = "TabGrupoMaterialServicoExcluir.php?GrupoCodigo=$GrupoCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit;
}elseif( $Botao == "Voltar" ){
	  header("location: TabGrupoMaterialServicoSelecionar.php");
	  exit;
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
			  $Mens     = 0;
		    $Mensagem = "Informe: ";
		    if( $GrupoDescricao == "" ) {
			      $Critica   = 1;
				 	  $Mens      = 1;
				 	  $Tipo      = 2;
				    $Mensagem .= "<a href=\"javascript:document.Grupo.GrupoDescricao.focus();\" class=\"titulo2\">Grupo</a>";
		    }
		    if( $Mens == 0 ){
						# Verifica a Duplicidade de Grupo #
						$db     = Conexao();
						$sql    = "SELECT COUNT(CGRUMSCODI) FROM SFPC.TBGRUPOMATERIALSERVICO ";
						$sql   .= " WHERE RTRIM(LTRIM(EGRUMSDESC)) = '$GrupoDescricao' ";
						$sql   .= "   AND CGRUMSCODI <> $GrupoCodigo AND FGRUMSTIPO = '$TipoGrupo'";
				 		$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
						    $Linha = $result->fetchRow();
						    $Qtd   = $Linha[0];
				    		if( $Qtd > 0 ) {
									$Critica   = 1;
						    	$Mens      = 1;
						    	$Tipo      = 2;
									$Mensagem  = "<a href=\"javascript:document.Grupo.GrupoDescricao.focus();\" class=\"titulo2\"> Grupo Já Cadastrado para o Tipo de Material ou Serviço</a>";
				    		}
								if( $Mens == 0 ){
										# Atualiza Grupo #
										$Data   = date("Y-m-d H:i:s");
										$db->query("BEGIN TRANSACTION");
										$sql    = "UPDATE SFPC.TBGRUPOMATERIALSERVICO ";
										$sql   .= "   SET FGRUMSTIPO = '$TipoGrupo', FGRUMSTIPM = '$TipoMaterial', FGRUMSTIPC = '$TipoMaterial',  ";
										$sql   .= "       EGRUMSDESC = '$GrupoDescricao',FGRUMSSITU = '$Situacao',TGRUMSULAT = '$Data' ";
										$sql   .= " WHERE CGRUMSCODI = $GrupoCodigo";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}
						        $db->query("COMMIT");
						        $db->query("EN TRANSACTION");
										$db->disconnect();

						        # Envia mensagem para página selecionar #
						        $Mensagem = urlencode("Grupo Alterado com Sucesso");
						        $Url = "TabGrupoMaterialServicoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
						        exit;
								}
						}
						$db->disconnect();
				}
		}
}
if( $Critica == 0 ){
		# Carrega os dados do grupo selecionado #
		$db     = Conexao();
		$sql    = "SELECT  CGRUMSCODI, FGRUMSTIPO, FGRUMSTIPM, EGRUMSDESC, FGRUMSSITU FROM SFPC.TBGRUPOMATERIALSERVICO WHERE CGRUMSCODI = $GrupoCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
		    while( $Linha = $result->fetchRow() ){
						$GrupoCodigo		= $Linha[0];
						$TipoGrupo      = $Linha[1];
						$TipoMaterial   = $Linha[2];
						$GrupoDescricao = $Linha[3];
						$Situacao       = $Linha[4];
				}
		}
		$db->disconnect();
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Grupo.Botao.value=valor;
	document.Grupo.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabGrupoMaterialServicoAlterar.php" method="post" name="Grupo">
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
		<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	MANTER - GRUPO DE MATERIAL OU SERVIÇO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar o Grupo de Material ou Serviço, preencha os dados abaixo e clique no botão "Alterar". <br>
             Para apagar o Grupo clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table summary="">
        			<tr>
            		<td class="textonormal"  bgcolor="#DCEDF7">Tipo de Grupo</td>
	                <?php
                  if (($TipoGrupo == "M") or ($TipoGrupo == "")) {
                     $DescTipo = "MATERIAL";
                  }else{
                     $DescTipo = "SERVIÇO";
                  }
	                ?>
  	           	<td class="textonormal">
               		<?php echo $DescTipo; ?>
               	</td>
        			</tr>
	            <?php if ($TipoGrupo == "M") { ?>
		            <tr>
		              <td class="textonormal" bgcolor="#DCEDF7" width="40%">Tipo de Material*</td>
		              <td class="textonormal">
		              	<input type="radio" name="TipoMaterial" value="C" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
		              	<input type="radio" name="TipoMaterial" value="P" <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
		              </td>
		            </tr>
 		          <?php } else {
 		          				$TipoMaterial = "";
 		          } ?>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Grupo*</td>
               	<td class="textonormal">
               		<input type="text" name="GrupoDescricao" size="45" maxlength="100" value="<?php echo $GrupoDescricao; ?>" class="textonormal">
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="GrupoCodigo" value="<?php echo $GrupoCodigo; ?>">
                </td>
	            </tr>
        			<tr>
            		<td class="textonormal"  bgcolor="#DCEDF7">Situação*</td>
	                <?php
                  if($Situacao == "A") {
                     $DescSituacao = "ATIVO";
                  }else{
                     $DescSituacao = "INATIVO";
                  }
	                ?>
	              <td>
	                <select name="Situacao" value="<?php echo $DescSituacao; ?>" class="textonormal">
	        	        <option value="A" <?php if ( $Situacao == "A" ) { echo "selected"; }?>>ATIVO</option>
                    <option value="I" <?php if ( $Situacao == "I" ) { echo "selected"; }?>>INATIVO</option>
                  </select>
                </td>
        			</tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
            <input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
						<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
            <input type="hidden" name="Botao" value="">
            <input type="hidden" name="TipoGrupo" value="<?php echo $TipoGrupo; ?>">
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
document.Grupo.GrupoDescricao.focus();
//-->
</script>
</body>
</html>
