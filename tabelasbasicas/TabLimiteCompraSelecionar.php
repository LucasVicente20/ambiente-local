<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLimiteCompraSelecionar.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     09/09/11
# Objetivo: Programa de Manutenção do Limite de Compra
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabLimiteCompraAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$CodigoLimiteCompra              = $_POST['CodigoLimiteCompra'];
		$CModalidadeProcessoLicitatorio  = $_POST['CModalidadeProcessoLicitatorio'];
		$CodigoTipoCompra                = $_POST['CodigoTipoCompra'];
		$TipoOrgao                       = $_POST['TipoOrgao'];
		$Botao                           = $_POST['Botao'];
}else{
		$Mensagem     = urldecode($_GET['Mensagem']);
		$Mens         = $_GET['Mens'];
		$Tipo         = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabLimiteCompraSelecionar.php";
$CODIGO_TIPO_COMPRA_LICITACAO = 2; // Código do tipo de compra licitação = 2 (SFPC.TBTIPOCOMPRA - coluna: )

if( $Botao == "Selecionar" ){
	# Critica dos Campos #
	$Mens     = 0;
	$Mensagem = "Informe: ";
	if( $CodigoTipoCompra  == ""){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens     = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.LimiteCompra.CodigoTipoCompra .focus();\" class=\"titulo2\">Selecione o Código do Tipo de Compra </a>";
	}
	if($CModalidadeProcessoLicitatorio  == "" && $CodigoTipoCompra == $CODIGO_TIPO_COMPRA_LICITACAO){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens     = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.LimiteCompra.CModalidadeProcessoLicitatorio.focus();\" class=\"titulo2\"> Selecione o Tipo de Modalidade </a>";
	}
	if($TipoOrgao  == ""){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens     = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.LimiteCompra.TipoOrgao.focus();\" class=\"titulo2\"> Selecione o Tipo de Órgão </a>";
	}
	if($Mens == 0){
		$db     = Conexao();
		if($CModalidadeProcessoLicitatorio  != "" && $CodigoTipoCompra == $CODIGO_TIPO_COMPRA_LICITACAO){
			$sql    = "SELECT CLICOMCODI FROM SFPC.TBLIMITECOMPRA WHERE CTPCOMCODI = $CodigoTipoCompra AND FLICOMTIPO = '$TipoOrgao' AND CMODLICODI = $CModalidadeProcessoLicitatorio ";   
			$result = $db->query($sql);
			if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}
			$L = $result->fetchRow();
			$QTD = $L[0];
			if($QTD == 0){
			   $Mens     = 1;
			   $Tipo     = 2;
			   $Mensagem = "<a href=\"javascript:document.LimiteCompra.TipoOrgao.focus();\" class=\"titulo2\">Não existe nenhum limite de compra cadastrado para a sua consulta</a>";
			
			}    
		} else { 		
			$sql    = "SELECT CLICOMCODI FROM SFPC.TBLIMITECOMPRA WHERE CTPCOMCODI = $CodigoTipoCompra AND FLICOMTIPO = '$TipoOrgao' ";   
			$result = $db->query($sql);
			if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}
			$L = $result->fetchRow();
			$QTD = $L[0];
			if($QTD == 0){
			   $Mens     = 1;
			   $Tipo     = 2;
			   $Mensagem = "<a href=\"javascript:document.LimiteCompra.TipoOrgao.focus();\" class=\"titulo2\">Não existe nenhum limite de compra cadastrado para a sua consulta</a>";
            
			}   
		}
		if($QTD != 0){
		$CodigoLimiteCompra = $L[0];
		$Url = "TabLimiteCompraAlterar.php?CodigoLimiteCompra=$CodigoLimiteCompra";			
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url;} 
		header("location: ".$Url);
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
	document.LimiteCompra.Botao.value=valor;
	document.LimiteCompra.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabLimiteCompraSelecionar.php" method="post" name="LimiteCompra">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Limite de Compra > Selecionar
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           MANTER - LIMITE DE COMPRA
          </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Para atualizar/excluir um Limite de Compra já cadastrado, selecione o Limite de Compra e clique no botão "Selecionar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Tipo de Compra</td>
                <td class="textonormal">
                  <select name="CodigoTipoCompra" class="textonormal" onChange="javascript:submit();">
                  	<option value="">Selecione um Limite de Compra...</option>
                  	<!-- Mostra os perfis cadastrados -->
                  	<?php
                		$db     = Conexao();
                		$sql    = "SELECT CTPCOMCODI,ETPCOMNOME FROM SFPC.TBTIPOCOMPRA ORDER BY CTPCOMCODI";
                        $result = $db->query($sql);
                		if( PEAR::isError($result) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						} else {
                        	while( $Linha = $result->fetchRow() ){
						        $Descricao   = substr($Linha[1],0,40);
						        if( $Linha[0] == $CodigoTipoCompra ){
									echo"<option value='".$Linha [0]."' selected>$Descricao</option>";
						        } else{
									echo"<option value='".$Linha [0]."'>$Descricao</option>";
								}											               	    
							}	
			            }
						$db->disconnect();
    	     	    ?>
                  </select>
                </td>
              </tr>
              <?php if($CodigoTipoCompra == $CODIGO_TIPO_COMPRA_LICITACAO) { ?>
			    <tr>
				  <td class="textonormal" bgcolor="#DCEDF7">Modalidade</td>
                    <td class="textonormal">
					 <select name="CModalidadeProcessoLicitatorio" class="textonormal">
                  	   <option value="">Selecione o Tipo de Modalidade...</option>
                  	<!-- Mostra os Códigos da lei cadastrados -->
                  	<?php 
                		$db     = Conexao();
                		$sql    = "SELECT CMODLICODI,EMODLIDESC FROM SFPC.TBMODALIDADELICITACAO";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						
						}else{
							while( $Linha = $result->fetchRow() ){
						          if( $Linha[0] == $CModalidadeProcessoLicitatorio){
								        echo"<option value='".$Linha [0]."' selected>".$Linha[1]."</option>";
								    }
									else{
								      echo"<option value='".$Linha [0]."'>".$Linha [1]."</option>";
								    }
								
							}
			            }
						$db->disconnect();
   	     	       ?>
                  </select>
                </td>
				</tr>
				
				<?php } ?>			  
			    <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Tipo de Órgão</td>
                <td class="textonormal">
                  <select name="TipoOrgao" class="textonormal">
                  	<option value="">Selecione o Tipo de Órgão...</option>
                  	<option value="D" <?php if($TipoOrgao == "D")  {echo 'selected';} ?>>Administração Direta</option>
					<option value="I" <?php if($TipoOrgao == "I") {echo 'selected';}?>>Administração Indireta</option>
                  </select>
                </td>
			</tr>
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
<script language="javascript">
<!--
document.LimiteCompra.CodigoLimiteCompra.focus();
//-->
</script>
