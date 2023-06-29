<?php
#-------------------------------------------------------------------------
# Portal da DGCO 
# Programa: CadAlmoxarifadoExcluir.php
# Autor:    Franklin Alves
# Data:     28/05/05
# Objetivo: Programa de Exclusão de Almoxarifado
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadAlmoxarifadoAlterar.php' );
AddMenuAcesso( '/estoques/CadAlmoxarifadoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao        = $_POST['Botao'];
		$Almoxarifado = $_POST['Almoxarifado'];
}else{
		$Almoxarifado = $_GET['Almoxarifado'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Url = "CadAlmoxarifadoAlterar.php?Almoxarifado=$Almoxarifado";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit;
}elseif( $Botao == "Excluir" ){
		$Mens     = 0;
    $Mensagem = "Informe: ";

		# Verifica se o Almoxarifado tem algum localização relacionada #
		$sql    = "SELECT COUNT(CALMPOCODI) FROM SFPC.TBLOCALIZACAOMATERIAL WHERE CALMPOCODI = $Almoxarifado";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
				$db->query("ROLLBACK");
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha = $result->fetchRow();
				$Qtd   = $Linha[0];
				if( $Qtd > 0 ) {
				    $Mensagem = "Exclusão Cancelada!<br>Almoxarifado Relacionado com ($Qtd) Localização(ões)";
				    $Url = "CadAlmoxarifadoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				    header("location: ".$Url);
				    exit;
				}else{
						if( $Mens == 0 ){
								# Exclui Almoxarifado #
								$db->query("BEGIN TRANSACTION");
								$sql    = "DELETE FROM SFPC.TBALMOXARIFADOORGAO WHERE CALMPOCODI = $Almoxarifado";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$sql    = "DELETE FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
												$db->query("ROLLBACK");
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$db->query("COMMIT");
												$db->query("END TRANSACTION");
												$db->disconnect();

												# Envia mensagem para página selecionar #
												$Mensagem = urlencode("Almoxarifado Excluído com Sucesso");
												$Url = "CadAlmoxarifadoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
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
		$sql    = "SELECT CALMPOCODI, EALMPODESC, EALMPOABRE, EALMPOENDE, ";
		$sql   .= "       AALMPOFONE, FALMPOTIPO, FALMPOSITU ";
		$sql   .= "  FROM SFPC.TBALMOXARIFADOPORTAL ";
		$sql   .= " WHERE CALMPOCODI = $Almoxarifado";
		$result = $db->query($sql);
		if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$Almoxarifado = $Linha[0];
						$Descricao    = $Linha[1];
						$Abre         = $Linha[2];
            $Endereco     = $Linha[3];
            $Fone         = $Linha[4];
            $TipoAlmo     = $Linha[5];
            $Situacao     = $Linha[6];
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
	document.CadAlmoxarifadoExcluir.Botao.value=valor;
	document.CadAlmoxarifadoExcluir.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAlmoxarifadoExcluir.php" method="post" name="CadAlmoxarifadoExcluir">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Almoxarifado > Manter
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
	           EXCLUIR - ALMOXARIFADO
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão de Almoxarifado clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Almoxarifado</td>
               	<td class="textonormal">
               		<?php echo $Descricao; ?>
                	<input type="hidden" name="Descricao" value="<?php echo $Descricao; ?>">
                </td>
              </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Abreviatura da Descrição</td>
               	<td class="textonormal">
               		<?php echo $Abre; ?>
                	<input type="hidden" name="Abre" value="<?php echo $Abre; ?>">
                </td>
              </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Endereço</td>
               	<td class="textonormal">
               		<?php echo $Endereco; ?>
                	<input type="hidden" name="Endereco" value="<?php echo $Endereco; ?>">
                </td>
              </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Fone</td>
               	<td class="textonormal">
               		<?php echo $Fone; ?>
                	<input type="hidden" name="Fone" value="<?php echo $Fone; ?>">
                </td>
              </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Almoxarifado</td>
               	<td class="textonormal">
               		<?
               		  if($TipoAlmo == "C"){echo "CENTRAL";}
               				else
               			if($TipoAlmo == "S"){echo "SUBALMOXARIFADO";}
               				else
               			if($TipoAlmo == "A"){echo "ALMOXARIFADO";}
               		?>
                	<input type="hidden" name="Tipo" value="<?php echo $TipoAlmo; ?>">
                </td>
              </tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Situação</td>
               	<td class="textonormal">
               		<?
               			if($Situacao == "A"){echo "ATIVO";}
               				else
               			if($Situacao == "I"){echo "INATIVO";}
               		?>
                	<input type="hidden" name="Situacao" value="<?php echo $Situacao; ?>">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td align="right">
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
