<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadComissaoExcluir.php
# Autor:    Rossana Lira
# Data:     07/04/03
# Objetivo: Programa de Exclusão do Comissão
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/CadComissaoAlterar.php' );
AddMenuAcesso( '/licitacoes/CadComissaoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao             = $_POST['Botao'];
		$Critica           = $_POST['Critica'];
		$ComissaoDescricao = $_POST['ComissaoDescricao'];
		$ComissaoCodigo    = $_POST['ComissaoCodigo'];
		$Presidente        = $_POST['Presidente'];
		$Email             = $_POST['Email'];
		$Fone              = $_POST['Fone'];
		$Fax               = $_POST['Fax'];
		$Local             = $_POST['Local'];
		$Situacao          = $_POST['Situacao'];
        $sigla             = $_POST['sigla'];
}else{
		$ComissaoCodigo    = $_GET['ComissaoCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadComissaoExcluir.php";

# Critica dos Campos #
if( $Botao == "Voltar" ){
		$Url = "CadComissaoAlterar.php?ComissaoCodigo=$ComissaoCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}else{
		if( $Critica == 1 ) {
				$Mens     = 0;
		    $Mensagem = "Informe: ";

			  # Verifica se a comissão está relacionada com alguma licitação
		    $db  = Conexao();
		    $sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBLICITACAOPORTAL WHERE CCOMLICODI = $ComissaoCodigo";
		    $res = $db->query($sql);
				if( PEAR::isError($res) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						while( $Linha = $res->fetchRow() ){
				    		$QtdLicitacao = $Linha[0];
				    }
				    if( $QtdLicitacao > 0 ){
				        $Mens     = 1;
				        $Mensagem = "Exclusão Cancelada!<br>Comissão Relacionada com ($QtdLicitacao) Licitação(ões)";
				    }else{
				    		# Verifica se o usuário está relacionado com alguma comissão #
						    $sql = "SELECT COUNT(*) FROM SFPC.TBUSUARIOCOMIS WHERE CCOMLICODI = $ComissaoCodigo";
								$res = $db->query($sql);
						    if( PEAR::isError($res) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										while( $Linha = $res->fetchRow() ){
								    		$QtdUsuarioComissao = $Linha[0];
								    }
								    if( $QtdUsuarioComissao > 0 ){
								        $Mens     = 1;
								        $Mensagem = "Exclusão Cancelada!<br>Comissão Relacionada com ($QtdUsuarioComissao) Usuário(s)";
								        $Mensagem = urlencode($Mensagem);
								    }
								}
						}
				    if( $Mens == 0 ){
					    	# Exclui Comissão #
					    	$db->query("BEGIN TRANSACTION");
					    	$sql = "DELETE FROM SFPC.TBCOMISSAOLICITACAO WHERE CCOMLICODI = $ComissaoCodigo";
								$res = $db->query($sql);
							  if( PEAR::isError($res) ){
							  		$db->query("ROLLBACK");
							    	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
	     					   	$db->disconnect();

							     	# Envia mensagem para página selecionar #
							     	$Mensagem = "Comissão Excluída com Sucesso";
							     	$Url = "CadComissaoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
							     	header("location: ".$Url);
							     	exit();
							  }
							  $db->query("END TRANSACTION");
				  	}else{
				  			$Url = "CadComissaoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								$db->disconnect();
				 				header("location: ".$Url);
				 				exit();
				   	}
						$db->disconnect();
				}
		}
}
if( $Critica == 0 ){
        $db     = Conexao();
        $sql    = "SELECT ECOMLIDESC, NCOMLIPRES, ECOMLIMAIL, ECOMLILOCA, ACOMLIFONE, ACOMLINFAX, CGREMPCODI, FCOMLISTAT, ECOMLISIGL FROM SFPC.TBCOMISSAOLICITACAO WHERE CCOMLICODI = $ComissaoCodigo";
        $result = $db->query($sql);
        if( PEAR::isError($result) ){
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        }
        while( $Linha = $result->fetchRow() ){
            $ComissaoDescricao = $Linha[0];
            $Presidente        = $Linha[1];
            $Email             = $Linha[2];
            $Local             = $Linha[3];
            $Fone              = $Linha[4];
            $Fax               = $Linha[5];
            $Situacao          = $Linha[7];
            $sigla             = $Linha[8];
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
	document.Comissao.Botao.value=valor;
	document.Comissao.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadComissaoExcluir.php" method="post" name="Comissao">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Comissão > Manter
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
		<td class="textonormal"><br>
		<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           EXCLUIR - COMISSÃO DE LICITAÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
               Para confirmar a exclusão da Comissão clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Comissão </td>
               	<td class="textonormal">
               		<?php echo $ComissaoDescricao; ?>
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo ?>">
                </td>
              </tr>
                <tr>
                    <td class="textonormal" bgcolor="#DCEDF7" height="20">Sigla </td>
                    <td class="textonormal"><?php echo $sigla; ?></td>
                </tr>
             	<tr>
              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Presidente </td>
      	    		<td class="textonormal"><?php echo $Presidente; ?></td>
        	  	</tr>
        			<tr>
              	<td class="textonormal" bgcolor="#DCEDF7" height="20">E-mail </td>
      	    		<td class="textonormal"><?php echo $Email; ?></td>
        	  	</tr>
							<tr>
              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Localização </td>
      	    		<td class="textonormal"><?php echo $Local; ?></td>
        	  	</tr>
							<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Telefone </td>
    	      		<td class="textonormal"><?php echo $Fone; ?></td>
      	    	</tr>
							<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Fax </td>
    	      		<td class="textonormal"><?php echo $Fax; ?></td>
      	    	</tr>
      	    	<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Situação </td>
    	      		<?php if( $Situacao == "I" ){?>
    	      		<td class="textonormal">INATIVA</td>
    	      		<?php }else{ ?>
    	      		<td class="textonormal">ATIVA</td>
    	      		<?php } ?>
      	    	</tr>
            </table>
          </td>
        </tr>
        <tr>
          <td align="right">
          	<input type="submit" value="Excluir" class="botao" onclick="javascript:enviar('Excluir')">
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
