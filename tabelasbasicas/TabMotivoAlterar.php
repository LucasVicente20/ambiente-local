<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabMotivoAlterar.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     27/06/11
# Objetivo: Programa de Alteração do Motivo
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabMotivoExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabMotivoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          		= $_POST['Botao'];
		$MotivoCodigo    		= $_POST['MotivoCodigo'];
		$MotivoNome 			= strtoupper2(trim($_POST['MotivoNome']));
}else{
		$MotivoCodigo    		= $_GET['MotivoCodigo'];
		
		
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabMotivoAlterar.php";

$db = Conexao();

if( $Botao == "Excluir" ){
       $Url = "TabMotivoExcluir.php?MotivoCodigo=$MotivoCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}elseif( $Botao == "Voltar" ){
		header("location: TabMotivoSelecionar.php");
		exit();
}elseif( $Botao == "Alterar" ) {
	  $Mens     = 0;
    $Mensagem = "Informe: ";
    if( $MotivoNome == "" ) {
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.Motivo.MotivoNome.focus();\" class=\"titulo2\">Motivo</a>";
    }
	/*else if (!preg_match("/^[a-zA-ZãÃáÁàÀêÊéÉèÈíÍìÌôÔõÕóÓòÒúÚùÙûÛçÇºª' ']+$/", $MotivoNome) ){
	    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.Motivo.MotivoNome.focus();\" class=\"titulo2\">Prencha o campo de Motivo corretamente ex: Portal DGCO</a>";
    }*/
    if( $Mens == 0 ){
				# Verifica a Duplicidade do Motivo #
				$sql    = "SELECT COUNT(CMOTNLSEQU) FROM SFPC.TBMOTIVOITEMNAOLOGRADO WHERE RTRIM(LTRIM(EMOTNLNOME)) = '$MotivoNome' AND CMOTNLSEQU <> $MotivoCodigo ";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
						$Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens = 1;$Tipo = 2;
								$Mensagem = "<a href=\"javascript:document.Motivo.MotivoNome.focus();\" class=\"titulo2\"> Motivo Já Cadastrado</a>";
						}else{
				        # Atualiza o Motivo #
				        $Data   = date("Y-m-d H:i:s");
				       	$db->query("BEGIN TRANSACTION");
		   				$sql    = " UPDATE SFPC.TBMOTIVOITEMNAOLOGRADO ";
				        $sql   .= " SET EMOTNLNOME = '$MotivoNome',CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ,TMOTNLULAT = '$Data' ";
				        $sql   .= " WHERE CMOTNLSEQU = $MotivoCodigo ";
				        $result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
				   			    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
						        $db->query("COMMIT");
						        $db->query("END TRANSACTION");
						        $db->disconnect();

				   			    # Envia mensagem para página selecionar #
						        $Mensagem = urlencode("Motivo Alterado com Sucesso");
						        $Url = "TabMotivoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						        header("location: ".$Url);
					      }
					  }
				}
    }
}
if( $Botao == "" ){
		$sql    = "SELECT EMOTNLNOME FROM SFPC.TBMOTIVOITEMNAOLOGRADO WHERE  CMOTNLSEQU  = '$MotivoCodigo'";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$MotivoNome      = $Linha[0];
				}
		}
}
$db->disconnect();
?>
<html>
<?php 
# Carrega o layout padrão #
layout();
?>
<script language="javascript">
<!--
function enviar(valor){
	document.Motivo.Botao.value=valor;
	document.Motivo.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabMotivoAlterar.php" method="post" name="Motivo">
<br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr><br>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Motivo > Alterar
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php  if ( $Mens == 1 ) {?>
  <tr>
  	<td width="150"></td>
		<td align="left" colspan="2"><?php  ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php  } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
		<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           MANTER - MOTIVO DE ITEM NÃO LOGRADO
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
             Para atualizar o Motivo, preencha os dados abaixo e clique no botão "Alterar". Para apagar o Motivo clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Nome do Motivo* </td>
               	<td class="textonormal">
				
               		<input type="text" name="MotivoNome" size="40" maxlength="60" value="<?php echo $MotivoNome?>">
                	<input type="hidden" name="MotivoCodigo" value="<?php echo $MotivoCodigo?>">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr align="right">
          <td>
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
<script language="javascript">
<!--
document.Motivo.MotivoNome.focus();
//-->
</script>
