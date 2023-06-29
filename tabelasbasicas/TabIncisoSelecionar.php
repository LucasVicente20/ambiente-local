<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabIncisoSelecionar.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     17/08/11
# Objetivo: Programa para Seleção do Inciso/Paragráfo
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     28/03/12
# Objetivo: Correção dos erros - Demanda Redmine: #4506
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     03/07/12
# Objetivo: Correção dos erros - Demanda Redmine: #11894
#-------------------------------------------------------------------------
# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabIncisoAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
	$CodigoLei     = $_POST['CodigoLei'];
	$NLei          = $_POST['NLei'];
	$CodigoArtigo  = $_POST['CodigoArtigo'];
	$CodigoInciso  = $_POST['CodigoInciso'];
	$Inciso        = $_POST['Inciso'];
	$Botao         = $_POST['Botao'];
}else{
	$Mensagem      = urldecode($_GET['Mensagem']);
	$Mens          = $_GET['Mens'];
	$Tipo          = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabIncisoSelecionar.php";

if($CodigoLei == "" || $CodigoLei == null){
	$NLei = "";
	$CodigoArtigo = "";
	$SequenNInciso = "";
	$Inciso = "" ;
}
if($NLei == "" || $NLei == null){
	$CodigoArtigo = "";
	$SequenNInciso = "";
	$Inciso = "" ;
}
if($CodigoArtigo == "" || $CodigoArtigo == null){
	$SequenNInciso = "";
	$Inciso = "" ;
}

if( $Botao == "Selecionar" ){
	# Critica dos Campos #
	$Mens     = 0;
	$Mensagem = "Informe: ";
	if($CodigoLei == ""){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.TipoInciso.CodigoLei.focus();\" class=\"titulo2\">Selecione o Tipo da Lei</a>";
	}
	if($NLei == ""){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.TipoInciso.NLei.focus();\" class=\"titulo2\">Selecione o Número da Lei</a>";
	}
	if($CodigoArtigo == ""){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.TipoInciso.CodigoArtigo.focus();\" class=\"titulo2\">Selecione o Código do Artigo</a>";
	}
	if($CodigoInciso == ""){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.TipoInciso.NumeroInciso.focus();\" class=\"titulo2\">Selecione o Número do Inciso/Parágrafo</a>";
	}
		
		
	if($Mens == 0){
	        
		$db   = Conexao();
		$sql  = "SELECT CINCPAINCI 
				   FROM SFPC.TBINCISOPARAGRAFOPORTAL 
				  WHERE CTPLEITIPO = $CodigoLei
					AND CLEIPONUME = $NLei
					AND CARTPOARTI = $CodigoArtigo
					AND CINCPAINCI = $CodigoInciso";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		$L = $result->fetchRow();
		$QTD = $L[0];
		if($QTD == 0){
			$Mens     = 1;
			$Tipo     = 2;
			$Mensagem = "<a href=\"javascript:document.TipoInciso.CodigoLei.focus();\" class=\"titulo2\">Não existe nenhum Inciso/Parágrafo cadastrado para a sua consulta</a>";
		} 
		if($QTD != 0){
			$SequenNInciso = $L[0];
			$Url = "TabIncisoAlterar.php?SequenNInciso=$SequenNInciso&CodigoArtigo=$CodigoArtigo&CodigoLei=$CodigoLei&NLei=$NLei";
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
	document.TipoInciso.Botao.value=valor;
	document.TipoInciso.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabIncisoSelecionar.php" method="post" name="TipoInciso">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Inciso/Parágrafo > Selecionar
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
	           MANTER - INCISO/PARÁGRAFO
		</td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Selecione os campos Tipo da Lei, Número da Lei, Código do Artigo, Número do Inciso/Parágrafo e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Tipo da Lei*</td>
                <td class="textonormal">
                  <select name="CodigoLei" class="textonormal" onChange="javascript:submit();">
                  	<option value="">Selecione o Tipo da Lei...</option>
                  	<!-- Mostra os códigos das leis cadastrados -->
                  	<?php
					$db   = Conexao();
					$sql  = "SELECT CTPLEITIPO,ETPLEITIPO 
							   FROM SFPC.TBTIPOLEIPORTAL
							  ORDER BY ETPLEITIPO";
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
						while( $Linha = $result->fetchRow() ){
							if( $Linha[0] == $CodigoLei){
								echo"<option value='".$Linha [0]."' selected>".$Linha[1]."</option>";
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
					 <select name="NLei" class="textonormal" onChange="javascript:submit();">
						<option value="">Selecione o Número da Lei...</option>
	                  	<!-- Mostra os Códigos da lei cadastrados -->
	                  	<?php
	                	if($CodigoLei != "" ) {
							$db    = Conexao();
	                		$sql   = "SELECT CLEIPONUME
	                				 FROM SFPC.TBLEIPORTAL LEI , SFPC.TBTIPOLEIPORTAL TIPO
	                				 WHERE LEI.CTPLEITIPO = TIPO.CTPLEITIPO
									 AND TIPO.CTPLEITIPO = $CodigoLei";
	                		$result = $db->query($sql);
	                		if( PEAR::isError($result) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							
							}else{
								while( $Linha = $result->fetchRow() ){
									if( $Linha[0] == $NLei){
									        echo"<option value='".$Linha [0]."' selected>".$Linha[0]."</option>";
									    }
										else{
									      echo"<option value='".$Linha [0]."'>".$Linha [0]."</option>";
									    }
								}
				            }
							
	  	              	   $db->disconnect();
						}
	    	     	    ?>
                  </select>
                </td>
				</tr>
				<tr>
				  <td class="textonormal" bgcolor="#DCEDF7">Código do Artigo*</td>
                    <td class="textonormal">
					 <select name="CodigoArtigo" class="textonormal" onChange="javascript:submit();">
                  	   <option value="">Selecione o Código do Artigo...</option>
                  		<!-- Mostra os Códigos da lei cadastrados -->
                  		<?php
                		if($NLei != "" ) {
							$db   = Conexao();
                			$sql  = "SELECT CARTPOARTI 
									   FROM SFPC.TBARTIGOPORTAL ART , SFPC.TBLEIPORTAL LEI 
									  WHERE ART.CTPLEITIPO = LEI.CTPLEITIPO
										AND ART.CLEIPONUME = LEI.CLEIPONUME 
										AND ART.CLEIPONUME = $NLei 
										AND ART.CTPLEITIPO = $CodigoLei ";
							$result = $db->query($sql);
                			if( PEAR::isError($result) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}else{
								while( $Linha = $result->fetchRow() ){
									if( $Linha[0] == $CodigoArtigo){
						    			echo"<option value='".$Linha [0]."' selected>".$Linha[0]."</option>";
						    		}else{
										echo"<option value='".$Linha [0]."'>".$Linha [0]."</option>";
							 		}
								}
							}
							$db->disconnect();
						}
    	     	       ?>
                  </select>
                </td>
				</tr>
			  <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Número do Inciso/Parágrafo* </td>
                <td class="textonormal">
                  <select name="CodigoInciso" class="textonormal" onChange="javascript:submit();">
                  	<option value="">Selecione o Número do Inciso/Parágrafo...</option>
                  	<!-- Mostra os perfis cadastrados -->
                  	<?php
                	if($CodigoArtigo != "" ) {
						$db    = Conexao();
                		$sql   = "SELECT CINCPAINCI, NINCPANUME
                				    FROM SFPC.TBINCISOPARAGRAFOPORTAL INC
                				   WHERE INC.CLEIPONUME = $NLei
                				     AND INC.CTPLEITIPO = $CodigoLei 
                				     AND INC.CARTPOARTI = $CodigoArtigo
								   ORDER BY NINCPANOME ";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
							while( $Linha = $result->fetchRow() ){
		          	        if( $Linha[0] == $CodigoInciso){
						    echo"<option value='".$Linha [0]."' selected>".$Linha[1]."</option>";
						    }else{
						    echo"<option value='".$Linha [0]."'>".$Linha [1]."</option>";
						    }      
						}
			       }
  	              	  $db->disconnect();
					}
    	     	       ?>
                  </select>
                </td>
              </tr>
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
