<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAreaLocalizacaoAlterar.php
# Autor:    Franklin Alves
# Data:     14/07/05
# Objetivo: Programa de Alteração de Área de Localização
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadAreaLocalizacaoExcluir.php' );
AddMenuAcesso( '/estoques/CadAreaLocalizacaoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        = $_POST['Botao'];
		$Area					= $_POST['Area'];
		$Almoxarifado = $_POST['Almoxarifado'];
		$DescrArea    = strtoupper2(trim($_POST['DescrArea']));
		$DescrAlmox   = $_POST['DescrAlmox'];
}else{
		$Area				  = $_GET['Area'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db = Conexao();
if( $Botao == "Excluir" ){
		$Url = "CadAreaLocalizacaoExcluir.php?Area=$Area";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit;
}elseif( $Botao == "Voltar" ){
		header("location: CadAreaLocalizacaoSelecionar.php");
		exit;
}elseif( $Botao == "Alterar" ) {
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $DescrArea == "" ) {
		 	  if( $Mens == 1 ){ $Mensagem .= ", "; }
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.CadAreaLocalizacaoAlterar.DescrArea.focus();\" class=\"titulo2\">Descrição</a>";
    }
    if( $Mens == 0 ){
				# Verifica a Duplicidade de Área de Localização #
				$sql    = "SELECT COUNT(CARLOCCODI) FROM SFPC.TBAREAALMOXARIFADO WHERE RTRIM(LTRIM(EARLOCDESC)) ";
				$sql   .= " = '$DescrArea' AND CARLOCCODI <> $Area AND CALMPOCODI = $Almoxarifado ";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
						$Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens = 1;$Tipo = 2;
								$Mensagem = "<a href=\"javascript:document.CadAreaLocalizacaoAlterar.DescrArea.focus();\" class=\"titulo2\"> Área Já Cadastrada</a>";
						}else{
				        # Atualiza a Área #
				        $Data   = date("Y-m-d H:i:s");
				       	$db->query("BEGIN TRANSACTION");
		   					$sql    = "UPDATE SFPC.TBAREAALMOXARIFADO ";
				        $sql   .= "   SET EARLOCDESC = '$DescrArea', TARLOCULAT = '$Data' ";
				        $sql   .= " WHERE CARLOCCODI = $Area ";
				        $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
				   			    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						        $db->query("COMMIT");
						        $db->query("END TRANSACTION");
						        $db->disconnect();

				   			    # Envia mensagem para página selecionar #
						        $Mensagem = urlencode("Área Alterada com Sucesso");
						        $Url = "CadAreaLocalizacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
						        exit;
					      }
					  }
				}
    }
}
if( $Botao == "" ){
		$sql    = "SELECT A.CARLOCCODI, A.EARLOCDESC, B.CALMPOCODI, B.EALMPODESC ";
		$sql   .= "FROM SFPC.TBAREAALMOXARIFADO A, SFPC.TBALMOXARIFADOPORTAL B WHERE CARLOCCODI = $Area ";
		$sql   .= "AND A.CALMPOCODI = B.CALMPOCODI ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$DescrArea    = $Linha[1];
						$Almoxarifado = $Linha[2];
						$DescrAlmox   = $Linha[3];
        }
		}
}
$db->disconnect();
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script type="" language="javascript">
<!--
function enviar(valor){
	document.CadAreaLocalizacaoAlterar.Botao.value=valor;
	document.CadAreaLocalizacaoAlterar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAreaLocalizacaoAlterar.php" method="post" name="CadAreaLocalizacaoAlterar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Área > Manter
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
	           MANTER - ÁREA
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
              Para atualizar a Área, preencha os dados abaixo e clique no botão "Alterar". Para apagar a Área clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Almoxarifado</td>
               	<td class="textonormal">
               		<?php echo $DescrAlmox; ?>
                	<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
                	<input type="hidden" name="DescrAlmox" value="<?php echo $DescrAlmox; ?>">
                </td>
              </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" width="30%">Descrição*</td>
               	<td class="textonormal">
               		<input type="text" name="DescrArea" size="45" maxlength="60" value="<?echo $DescrArea;?>" class="textonormal">
                </td>
              </tr>
					 </table>
          </td>
        </tr>
        <tr align="right">
          <td>
          	<input type="hidden" name="Area" value="<?php echo $Area; ?>">
          	<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
						<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
          	<input name="voltar" type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
          	<input type="hidden" name="Botao" value="">
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
