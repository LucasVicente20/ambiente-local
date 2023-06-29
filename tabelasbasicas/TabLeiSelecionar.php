<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLeiSelecionar.php
# Autor:    Luiz Alves de Oliveira Neto
# Data:     27/06/11
# Objetivo: Programa de Criação de leis - Demanda Redmine: #3281
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabLeiAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$DataLei				= $_POST['DataLei'];
		$DescLei				= $_POST['DescLei'];
		$TipoLei                = $_POST['TipoLei'];
		$NumerodaLei            = $_POST['NumerodaLei'];
		$Botao                  = $_POST['Botao'];
}else{
		$Mensagem     = urldecode($_GET['Mensagem']);
		$Mens         = $_GET['Mens'];
		$Tipo         = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabLeiSelecionar.php";

if( $Botao == "Selecionar" ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
        if( $TipoLei == ""){
		    if($Mens == 1){ $Mensagem.=", "; }
			$Mens     = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TabLeiSelecionar.TipoLei.focus();\" class=\"titulo2\">Selecione o Tipo da Lei</a>";
		}
		if( $NumerodaLei == ""){
            if($Mens == 1){ $Mensagem.=", "; }
		    $Mens     = 1;
			$Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TabLeiSelecionar.NumerodaLei.focus();\" class=\"titulo2\"> Selecione o Número da Lei </a>";
	    }		
		if( $Mens == 0){
	        
			$db     = Conexao();
			$sql    = "SELECT CLEIPONUME FROM SFPC.TBLEIPORTAL WHERE CTPLEITIPO = $TipoLei AND CLEIPONUME = $NumerodaLei";   
			$result = $db->query($sql);
			if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}
			$L = $result->fetchRow();
			$QTD = $L[0];
			
			if($QTD != 0){
		       $NumerodaLei = $L[0];
			   $Url = "TabLeiAlterar.php?NumerodaLei=$NumerodaLei&TipoLei=$TipoLei";
		       if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	           header("location: ".$Url);
	           exit();
	        }
		}
    	
    
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript">
<!--
function enviar(valor){
	document.TabLeiSelecionar.Botao.value=valor;
	document.TabLeiSelecionar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabLeiSelecionar.php" method="post" name="TabLeiSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Lei > Selecionar
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
	           MANTER - LEI
          </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             	Selecione os campos Tipo de Lei, Número da Lei e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
             <tr>
				  <td class="textonormal" bgcolor="#DCEDF7">Tipo de Lei*</td>
                    <td class="textonormal">
					 <select name="TipoLei" class="textonormal" onChange="javascript:submit();">
                  	   <option value="">Selecione o Tipo de Lei...</option>
                  	<!-- Mostra os Códigos da lei cadastrados -->
                  	<?php
                		$db     = Conexao();
                		$sql    = "SELECT CTPLEITIPO,ETPLEITIPO FROM SFPC.TBTIPOLEIPORTAL ORDER BY ETPLEITIPO";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");

						}else{
							while( $Linha = $result->fetchRow() ){
								if( $Linha[0] == $TipoLei ){
								echo"<option value='".$Linha [0]."' selected>".$Linha [1]."</option>";
								}else{
								echo"<option value='".$Linha [0]."'>".$Linha [1]."</option>";
								}
			               	}
			            }

  	              	$db->disconnect();
    	     	    ?>
                  </select>
                </td>
				</tr>
                <tr>
				<td class="textonormal" bgcolor="#DCEDF7">Número da Lei*</td>
                    <td class="textonormal">
					 <select name="NumerodaLei" class="textonormal" onChange="javascript:submit();">
                  	   <option value="">Selecione o Número da Lei...</option>
                  	<!-- Mostra os Códigos da lei cadastrados -->
                  	<?php
                  	if($TipoLei != "" ) {
                		$db     = Conexao();
                		$sql    = "SELECT CLEIPONUME FROM SFPC.TBLEIPORTAL LEI , SFPC.TBTIPOLEIPORTAL TIPO WHERE LEI.CTPLEITIPO = TIPO.CTPLEITIPO
						AND TIPO.CTPLEITIPO = $TipoLei";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
							while( $Linha = $result->fetchRow() ){
								if( $Linha[0] == $NumerodaLei ){
								        echo"<option value='".$Linha [0]."' selected>".$Linha[0]."</option>";
								
								    }else{
								        echo"<option value='".$Linha [0]."'>".$Linha [0]."</option>";
								
								    }
							}
			            }
						?>
  	              	$db->disconnect();
    	     	       
                  </select>
                </td>
				</tr>
				 <?php } ?>
			</table>
          </td>
        </tr>
        <tr>
          <td>
   	        <table class="textonormal" border="0" align="right">
              <tr>
      	      	<td>
      	      		<input type="button" value="Selecionar" class="botao" onClick="javascript:enviar('Selecionar');">
 									<input type="hidden" name="Botao" value="">
      	      	</td>
					     </tr>
            </table>
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
