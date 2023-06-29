<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLeiExcluir.php
# Autor:    Luiz Alves
# Data:     27/06/11
# Objetivo: Programa de Criação de leis do Portal - Demanda Redmine: #3281
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Luiz Alves
# Data:     20/09/2011
# Objetivo: Correção dos erros - Demanda Redmine: #3640
# Acesso ao arquivo de funções #
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/tabelasbasicas/TabLeiAlterar.php');
AddMenuAcesso('/tabelasbasicas/TabLeiSelecionar.php');

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
	  $Botao         		= $_POST['Botao'];
	  $Critica       		= $_POST['Critica'];
	  $TipoLei     			= $_POST['TipoLei'];
	  $CodigoArtigo  		= $_POST['CodigoArtigo'];
	  $NumerodaLei          = $_POST['NumerodaLei'];	 
	  $DataLei				= $_POST['DataLei'];
	  $DescTipoLei			= $_POST['DescTipoLei'];
	  $DescLei				= $_POST['DescLei'];
}else{

	 $TipoLei				= $_GET['TipoLei'];
     $NumerodaLei     		= $_GET['NumerodaLei'];


}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabLeiExcluir.php";

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Url = "TabLeiAlterar.php?NumerodaLei=$NumerodaLei&TipoLei=$TipoLei";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}elseif( $Botao == "Excluir" ){
	   $Mens     = 0;


	   # Verifica se a Lei está relacionada com algum Artigo #
						$db     = Conexao();
						$sql    = "
									SELECT COUNT(*) 
									FROM SFPC.TBARTIGOPORTAL T, SFPC.TBLEIPORTAL B 
									WHERE 
									T.CTPLEITIPO = B.CTPLEITIPO 
                        		    AND T.CLEIPONUME = B.CLEIPONUME
                        		    AND T.CLEIPONUME = $NumerodaLei
        						    AND T.CTPLEITIPO = $TipoLei"; 
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								while( $Linha = $result->fetchRow() ){
										$QtdArtigo = $Linha[0];
								}
								if( $QtdArtigo > 0 ){
								$Mens     = 1;
								$Tipo     = 2;
									# Envia mensagem para página selecionar #
										$Mensagem = urlencode("Exclusão Cancelada! Lei Relacionada com algum Artigo");
										$Url = "TabLeiSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();


						}else{

							  # Exclui Lei #
								$db->query("BEGIN TRANSACTION");
								$sql    = "DELETE FROM SFPC.TBLEIPORTAL  WHERE 
								CLEIPONUME = $NumerodaLei
								AND CTPLEITIPO = $TipoLei ";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$db->disconnect();

										# Envia mensagem para página selecionar #
										$Mensagem = urlencode("Lei Excluída com Sucesso");
										$Url = "TabLeiSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();
								}
				   	}
          }
}



if( $Botao == "" ){
			$sql    = " SELECT B.CLEIPONUME, to_char(DLEIPODATA, 'DD-MM-YYYY'), B.NLEIPONOME, T.ETPLEITIPO, B.CTPLEITIPO
		FROM SFPC.TBLEIPORTAL B, SFPC.TBTIPOLEIPORTAL T WHERE B.CTPLEITIPO = T.CTPLEITIPO AND B.CLEIPONUME = $NumerodaLei AND B.CTPLEITIPO = $TipoLei";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
		                $NumerodaLei       = $Linha[0];
				        $DataLei           = $Linha[1];
						$DescLei    	   = $Linha[2];
						$DescTipoLei   	   = $Linha[3];
						$TipoLei		   = $Linha[4];
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
	document.TabLeiExcluir.Botao.value=valor;
	document.TabLeiExcluir.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabLeiExcluir.php" method="post" name="TabLeiExcluir">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Lei > Excluir
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
	           EXCLUIR - LEI
	                    </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão da Lei clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
			<tr>
                <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo da Lei </td>
               	<td class="textonormal">
               		<?php echo $DescTipoLei; ?>
                	<input type="hidden" name="DescTipoLei" value="<?php echo $DescTipoLei; ?>">
                </td>
              </tr>
			  <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Número da Lei </td>
               	<td class="textonormal">
               		<?php echo $NumerodaLei; ?>
                	<input type="hidden" name="NumerodaLei" value="<?php echo $NumerodaLei; ?>">	                    						
			</td>
              </tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Lei </td>
               	<td class="textonormal">
               		<?php echo $DataLei ?>
                	<input type="hidden" name="DataLei" value="<?php echo $DataLei ?>">
                </td>
              </tr>
			  <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Descrição </td>
               	<td class="textonormal">
               		<?php echo $DescLei ?>
                	<input type="hidden" name="DescLei" value="<?php echo $DescLei ?>">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td align="right">
			<input type="hidden" name="TipoLei" value="<?php echo $TipoLei; ?>">
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
