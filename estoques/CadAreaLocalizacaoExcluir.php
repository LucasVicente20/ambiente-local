<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAreaLocalizacaoExcluir.php
# Autor:    Franklin Alves
# Data:     05/07/05
# Objetivo: Programa de Exclusão de Área de Localização
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadAreaLocalizacaoAlterar.php' );
AddMenuAcesso( '/estoques/CadAreaLocalizacaoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao        = $_POST['Botao'];
		$Almoxarifado = $_POST['Almoxarifado'];
		$Area				  = $_POST['Area'];
}else{
		$Area				  = $_GET['Area'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Url = "CadAreaLocalizacaoAlterar.php?Area=$Area";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}elseif( $Botao == "Excluir" ){
		$Mens     = 0;
    $Mensagem = "Informe: ";

    if( $Mens == 0 ){
				# Verifica se a área tem alguma localização relacionada #
				$sql    = "SELECT COUNT(CALMPOCODI) FROM SFPC.TBLOCALIZACAOMATERIAL ";
				$sql   .= " WHERE CALMPOCODI = $Almoxarifado AND CARLOCCODI = $Area ";
				$result = $db->query($sql);
				if (PEAR::isError($result)) {
						$db->query("ROLLBACK");
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha = $result->fetchRow();
						$Qtd   = $Linha[0];
						if( $Qtd > 0 ) {
						    $Mensagem = "Exclusão Cancelada!<br>Área Relacionada com ($Qtd) Localização(ões)";
						    $Url = "CadAreaLocalizacaoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						    header("location: ".$Url);
						    exit;
						}else{
								if( $Mens == 0 ){
										# Exclui Área #
										$db->query("BEGIN TRANSACTION");
										$sql    = "DELETE FROM SFPC.TBAREAALMOXARIFADO WHERE CARLOCCODI = $Area";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$db->query("COMMIT");
												$db->query("END TRANSACTION");
												$db->disconnect();

												# Envia mensagem para página selecionar #
												$Mensagem = urlencode("Área Excluída com Sucesso");
												$Url = "CadAreaLocalizacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												header("location: ".$Url);
												exit;
										}
							 }
						}
				}
   	}
}
if( $Botao == "" ){
		$sql    = "SELECT CARLOCCODI, CALMPOCODI, EARLOCDESC ";
		$sql   .= "  FROM SFPC.TBAREAALMOXARIFADO ";
		$sql   .= " WHERE CARLOCCODI = $Area";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$Area					= $Linha[0];
						$Almoxarifado = $Linha[1];
						$Descricao    = $Linha[2];
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
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadAreaLocalizacaoExcluir.Botao.value=valor;
	document.CadAreaLocalizacaoExcluir.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAreaLocalizacaoExcluir.php" method="post" name="CadAreaLocalizacaoExcluir">
<br><br><br><br><br>
<table cellpadding="3" border="0">
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
	           EXCLUIR - ÁREA
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão de Área clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
		            <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
		            <td class="textonormal">
	              	<?php
	                $db     = Conexao();
	            		$sql    = "SELECT CALMPOCODI, FALMPOTIPO, EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
	            		$result = $db->query($sql);
	            		if( PEAR::isError($result) ){
									    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											while( $Linha = $result->fetchRow() ){
	          	      			if ( $Linha[1] == "C" ){
	          	      				  $Tipo = "CENTRAL";
	          	      			}elseif (	$Linha[1] == "S"){
	          	      				  $Tipo = "SUBAMOXARIFADO";
	          	      			}elseif (	$Linha[1] == "A"){
	          	      				  $Tipo = "ALMOXARIFADO";
	          	      			}
	          	      		  echo" $Tipo - $Linha[2] ";
		                	}
		              }
	              	$db->disconnect();
	                ?>
		            </td>
		          </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Área</td>
               	<td class="textonormal">
               		<?php echo $Descricao; ?>
                	<input type="hidden" name="Descricao" value="<?php echo $Descricao; ?>">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td align="right">
          	<input type="hidden" name="Area" value="<?php echo $Area; ?>">
						<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
          	<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir')">
          	<input type="button" value="Voltar"  class="botao" onclick="javascript:enviar('Voltar')">
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
