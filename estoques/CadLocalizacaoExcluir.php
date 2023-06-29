<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadLocalizacaoExcluir.php
# Autor:    Franklin Alves
# Data:     13/07/05
# Objetivo: Programa de Exclusão de Localização
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadLocalizacaoAlterar.php' );
AddMenuAcesso( '/estoques/CadLocalizacaoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao             = $_POST['Botao'];
		$Almoxarifado      = $_POST['Almoxarifado'];
		$Area					     = $_POST['Area'];
		$Localizacao       = $_POST['Localizacao'];
		$TipoEquipamento   = $_POST['TipoEquipamento'];
		$NumeroEquipamento = $_POST['NumeroEquipamento'];
}else{
		$Almoxarifado      = $_GET['Almoxarifado'];
		$Area					     = $_GET['Area'];
		$TipoEquipamento   = $_GET['TipoEquipamento'];
		$NumeroEquipamento = $_GET['NumeroEquipamento'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
if( $Botao == "Voltar" ){
		$Url = "CadLocalizacaoAlterar.php?TipoEquipamento=$TipoEquipamento&Almoxarifado=$Almoxarifado&Area=$Area&NumeroEquipamento=$NumeroEquipamento";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}else{
		if( $Botao == "Excluir" ) {
				$Mens = 0;
				if( $Mens == 0 ){
						$db     = Conexao();
						# Verifica se a localização tem algum armazenamento relacionado #
						$sql    = "SELECT COUNT(CLOCMACODI) FROM SFPC.TBARMAZENAMENTOMATERIAL ";
						$sql   .= " WHERE CLOCMACODI IN ";
						$sql   .= "       (SELECT CLOCMACODI FROM SFPC.TBLOCALIZACAOMATERIAL ";
						$sql   .= "         WHERE FLOCMAEQUI = '$TipoEquipamento' AND CALMPOCODI = $Almoxarifado ";
						$sql   .= "           AND CARLOCCODI = $Area AND ALOCMANEQU = $NumeroEquipamento)";
						$result = $db->query($sql);
						if (PEAR::isError($result)) {
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha = $result->fetchRow();
								$Qtd   = $Linha[0];
								if( $Qtd > 0 ) {
								    $Mensagem = "Exclusão Cancelada!<br>Localização Relacionada com ($Qtd) Armazenamento(s)";
								    $Url = "CadLocalizacaoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								    header("location: ".$Url);
								    $db->disconnect();
								    exit;
								}else{
										# Verifica se a localização tem algum inventário relacionado #
										$sql    = "SELECT COUNT(CLOCMACODI) FROM SFPC.TBINVENTARIOCONTAGEM ";
										$sql   .= " WHERE CLOCMACODI IN ";
										$sql   .= "       (SELECT CLOCMACODI FROM SFPC.TBLOCALIZACAOMATERIAL ";
										$sql   .= "         WHERE FLOCMAEQUI = '$TipoEquipamento' AND CALMPOCODI = $Almoxarifado ";
										$sql   .= "           AND CARLOCCODI = $Area AND ALOCMANEQU = $NumeroEquipamento)";
										$result = $db->query($sql);
										if (PEAR::isError($result)) {
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Linha = $result->fetchRow();
												$Qtd   = $Linha[0];
												if( $Qtd > 0 ) {
												    $Mensagem = "Exclusão Cancelada!<br>Localização Relacionada com ($Qtd) Inventário(s)";
												    $Url = "CadLocalizacaoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
														if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
												    header("location: ".$Url);
												    $db->disconnect();
												    exit;
												}else{
														# Verifica se a localização tem algum item do inventário relacionado #
														$sql    = "SELECT COUNT(CLOCMACODI) FROM SFPC.TBINVENTARIOMATERIAL ";
														$sql   .= " WHERE CLOCMACODI IN ";
														$sql   .= "       (SELECT CLOCMACODI FROM SFPC.TBLOCALIZACAOMATERIAL ";
														$sql   .= "         WHERE FLOCMAEQUI = '$TipoEquipamento' AND CALMPOCODI = $Almoxarifado ";
														$sql   .= "           AND CARLOCCODI = $Area AND ALOCMANEQU = $NumeroEquipamento)";
														$result = $db->query($sql);
														if (PEAR::isError($result)) {
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Linha = $result->fetchRow();
																$Qtd   = $Linha[0];
																if( $Qtd > 0 ) {
																    $Mensagem = "Exclusão Cancelada!<br>Localização Relacionada com ($Qtd) Item(ns) do Inventário(s)";
																    $Url = "CadLocalizacaoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
																		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																    header("location: ".$Url);
																    $db->disconnect();
																    exit;
																}else{
																		# Exclui Localização #
																		$db->query("BEGIN TRANSACTION");
																		$sql    = "DELETE FROM SFPC.TBLOCALIZACAOMATERIAL ";
																		$sql   .= "WHERE FLOCMAEQUI = '$TipoEquipamento' AND CALMPOCODI = $Almoxarifado ";
																		$sql   .= "  AND CARLOCCODI = $Area AND ALOCMANEQU = $NumeroEquipamento";
																		$result = $db->query($sql);
																		if( PEAR::isError($result) ){
																				$db->query("ROLLBACK");
																		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				$db->query("COMMIT");
																				$db->query("END TRANSACTION");
																				$db->disconnect();
																				# Envia mensagem para página selecionar #
																				$Mensagem = urlencode("Localização Excluída com Sucesso");
																				$Url = "CadLocalizacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
																				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																				header("location: ".$Url);
																				exit();
																		}
														    }
														}
												}
										}
							  }
						}
						$db->disconnect();
				}
		}
}

if( $Botao == "" ){
	  $db     = Conexao();
	  $sql    = "SELECT CLOCMACODI, CALMPOCODI, CARLOCCODI, FLOCMAEQUI, ALOCMANEQU, FLOCMASITU	";
    $sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL WHERE CALMPOCODI = $Almoxarifado	";
		$sql   .= "  AND  CARLOCCODI = $Area AND FLOCMAEQUI = '$TipoEquipamento' ";
		$sql   .= "  AND  ALOCMANEQU = $NumeroEquipamento ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$Localizacao     			 = $Linha[0];
						$Almoxarifado			 = $Linha[1];
						$Area					     = $Linha[2];
						$TipoEquipamento	 = $Linha[3];
						$NumeroEquipamento = $Linha[4];
						$Situacao 				 = $Linha[5];
				}
		}
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
	document.CadLocalizacaoExcluir.Botao.value=valor;
	document.CadLocalizacaoExcluir.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadLocalizacaoExcluir.php" method="post" name="CadLocalizacaoExcluir">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Localização > Manter
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
		<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#ffffff">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	MANTER - LOCALIZAÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
               Para confirmar a exclusão de uma Localização clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" width="100%" summary="">
              <tr>
              	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
	              <td class="textonormal">
	              	<?
		                $db     = Conexao();
		            		$sql    = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado ";
		            		$result = $db->query($sql);
		            		if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
		        	      					echo $Linha[0];
		        	      		 }
		        	      }
		              	$db->disconnect();
                	?>
	              </td>
	            </tr>
              <tr>
		            <td class="textonormal" bgcolor="#DCEDF7" height="20">Área</td>
		            <td class="textonormal">
	                <?
	                  $db     = Conexao();
		            		$sql    = "SELECT CARLOCCODI, CALMPOCODI, EARLOCDESC ";
										$sql   .= "  FROM SFPC.TBAREAALMOXARIFADO WHERE CARLOCCODI = $Area ";
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
		          	      			 echo $Linha[2];
			                	 }
			              }
		              	$db->disconnect();
		                ?>
		              </td>
		          </tr>
              <tr>
								<td class="textonormal" bgcolor="#DCEDF7" height="20">Localização</td>
								<td class="textonormal">
				  	      <?
										if ($TipoEquipamento == 'E'){
											$Equipamento = "ESTANTE";
										}else
										if ($TipoEquipamento == 'P'){
											$Equipamento = "PALETE";
										}else
										if ($TipoEquipamento == 'A'){
											$Equipamento = "ARMÁRIO";
										}
									  echo "$Equipamento - $NumeroEquipamento";
									?>
								</td>
	            </tr>
   	            <tr>
		             <td class="textonormal" bgcolor="#DCEDF7" height="20">Situação*</td>
		             <td class="textonormal">
	  	             <?php if( $Situacao == "A" ){ echo "ATIVO"; }else{ echo "INATIVO"; }?>
	          	   </td>
	            </tr>
	          </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
            <input type="hidden" name="Localizacao" value="<?php echo $Localizacao; ?>">
            <input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
            <input type="hidden" name="Area" value="<?php echo $Area; ?>">
            <input type="hidden" name="TipoEquipamento" value="<?php echo $TipoEquipamento; ?>">
            <input type="hidden" name="NumeroEquipamento" value="<?php echo $NumeroEquipamento; ?>">
            <input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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
