<?php 
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLimiteCompraIncluir.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     09/09/11
# Objetivo: Programa de Inclusão do Limite de Compra
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
		$CodigoLimiteCompra                = $_POST['CodigoLimiteCompra'];
		$CodigoTipoCompra                  = $_POST['CodigoTipoCompra'];
		$TipoOrgao                         = $_POST['TipoOrgao'];
		$CModalidadeProcessoLicitatorio    = $_POST['CModalidadeProcessoLicitatorio'];
		$VLimiteModalidadeObras            = $_POST['VLimiteModalidadeObras'];
		$VLimiteModalidadeOutrosServicos   = $_POST['VLimiteModalidadeOutrosServicos'];
		$Botao                             = $_POST['Botao'];

}else{
		$Mensagem     = urldecode($_GET['Mensagem']);
		$Mens         = $_GET['Mens'];
		$Tipo         = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabLimiteCompraIncluir.php";
$CODIGO_TIPO_COMPRA_LICITACAO = 2; // Código do tipo de compra licitação = 2 (SFPC.TBTIPOCOMPRA - 1: )

if( $Botao == "Incluir" ) {
		$Mens     = 0;
		$Mensagem = "Informe: ";
	   if( $CodigoTipoCompra  == "" or $CodigoTipoCompra  == 0){
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
		if($VLimiteModalidadeObras    == "" or $VLimiteModalidadeObras    == 0){
            if($Mens == 1){ $Mensagem.=", "; }
		    $Mens     = 1;
			$Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.LimiteCompra.VLimiteModalidadeObras.focus();\" class=\"titulo2\"> Valor Limite de Modalidade para Obras </a>";
		}
		else {
           # Verifica se as quantidades só são numeros e decimais #
           if (!DecimalValor($VLimiteModalidadeObras) || strlen($VLimiteModalidadeObras) >15  || !SoNumVirg($VLimiteModalidadeObras) ){
		   if($Mens == 1){ $Mensagem.=", "; }
              $Mens      = 1;
              $Tipo      = 2;
               $Mensagem .= "<a href=\"javascript:document.LimiteCompra.VLimiteModalidadeObras.focus();\" class=\"titulo2\"> Valor Limite de Modalidade para Obras válido </a>";
            }
        }
		if($VLimiteModalidadeOutrosServicos == "" or $VLimiteModalidadeOutrosServicos == 0 ){
            if($Mens == 1){ $Mensagem.=", "; }
		    $Mens     = 1;
			$Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.LimiteCompra.VLimiteModalidadeOutrosServicos.focus();\" class=\"titulo2\"> Valor Limite para Modalidade Outros Serviços </a>";
        }else {
            # Verifica se as quantidades só são numeros e decimais #
            if ( !DecimalValor($VLimiteModalidadeOutrosServicos) || strlen($VLimiteModalidadeOutrosServicos) > 15 || !SoNumVirg($VLimiteModalidadeOutrosServicos)){
			if($Mens == 1){ $Mensagem.=", "; }
              $Mens      = 1;
              $Tipo      = 2;
               $Mensagem .= "<a href=\"javascript:document.LimiteCompra.VLimiteModalidadeOutrosServicos.focus();\" class=\"titulo2\"> Valor Limite para Modalidade Outros Serviços válido </a>";
            }
        }
		
	if( $Mens == 0 ) {
				/*echo $CModalidadeProcessoLicitatorio ;
				exit();
				*/
			if ($CModalidadeProcessoLicitatorio == ""){
				# Verifica a Duplicidade do Limite de compra #
				$db     = Conexao();
		   	    $sql    = "SELECT COUNT(*) FROM SFPC.TBLIMITECOMPRA WHERE  RTRIM(LTRIM(CTPCOMCODI)) = $CodigoTipoCompra AND RTRIM(LTRIM(FLICOMTIPO)) = '$TipoOrgao'";
		 		$result = $db->query($sql);
				    if (PEAR::isError($result)) {
				       ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				    }
					else{
						$Linha = $result->fetchRow();
						$Qtd = $Linha[0];
						if( $Qtd > 0 ) {
								$Mens     = 1;
								$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.LimiteCompra.TipoOrgao.focus();\" class=\"titulo2\"> Limite de Compra Já Cadastrado</a>";
						}
	                    if ($Qtd == 0){
							# Recupera a última Ocorrencia e incrementa mais um #
							$sql    = "SELECT MAX(CLICOMCODI) FROM SFPC.TBLIMITECOMPRA";
							$result = $db->query($sql);
								if( PEAR::isError($result) ){
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");

								}
								else{
									while( $Linha = $result->fetchRow() ){
											$CodigoLimiteCompra = $Linha[0] + 1;
									}
								}
									if($CModalidadeProcessoLicitatorio  == "" ){
									   $CModalidadeProcessoLicitatorio  = 'null';
									}
								 # Insere o Limite de Compra#
								$Data   = date("Y-m-d H:i:s");
								$VLimiteModalidadeObras = moeda2float($VLimiteModalidadeObras);
								$VLimiteModalidadeOutrosServicos = moeda2float($VLimiteModalidadeOutrosServicos);
								$db->query("BEGIN TRANSACTION");
								$sql    = "INSERT INTO SFPC.TBLIMITECOMPRA (";
								$sql   .= "CLICOMCODI,CTPCOMCODI,FLICOMTIPO,CMODLICODI,VLICOMOBRA,VLICOMSERV,CUSUPOCODI,TLICOMULAT";
								$sql   .= ") VALUES ( ";
								$sql   .= " $CodigoLimiteCompra, $CodigoTipoCompra, '$TipoOrgao', $CModalidadeProcessoLicitatorio , " ;
								$sql   .= " $VLimiteModalidadeObras, $VLimiteModalidadeOutrosServicos,".$_SESSION['_cusupocodi_'].", '$Data' )";
								$result = $db->query($sql);
									if( PEAR::isError($result)){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$Mens                = 1;
										$Tipo                = 1;
										$Mensagem            = "Limite de Compra Incluído com Sucesso";
										$CodigoLimiteCompra                = "";
										$CodigoTipoCompra                  = "";
										$TipoOrgao                         = "";
										$CModalidadeProcessoLicitatorio    = "";
										$VLimiteModalidadeObras            = "";
										$VLimiteModalidadeOutrosServicos   = "";
										

									}
				        }
				    }
			}
			if ($CModalidadeProcessoLicitatorio != "") {
				  $db     = Conexao();
		   	      $sql    = "SELECT COUNT(*) FROM SFPC.TBLIMITECOMPRA WHERE  RTRIM(LTRIM(CTPCOMCODI)) = $CodigoTipoCompra 
				  AND RTRIM(LTRIM(FLICOMTIPO)) = '$TipoOrgao' AND RTRIM(LTRIM(CMODLICODI)) = $CModalidadeProcessoLicitatorio ";
		 		  $result = $db->query($sql);
				    if (PEAR::isError($result)) {
				       ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				    }
				else{
						$Linha = $result->fetchRow();
						$Qtd = $Linha[0];
						if( $Qtd > 0 ) {
								$Mens     = 1;
								$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.LimiteCompra.TipoOrgao.focus();\" class=\"titulo2\"> Limite de Compra Já Cadastrado</a>";
						}
						if ($Qtd == 0){
							# Recupera a última Ocorrencia e incrementa mais um #
							$sql    = "SELECT MAX(CLICOMCODI) FROM SFPC.TBLIMITECOMPRA";
							$result = $db->query($sql);
								if( PEAR::isError($result) ){
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");

								}
								else{
									while( $Linha = $result->fetchRow() ){
											$CodigoLimiteCompra = $Linha[0] + 1;
									}
								}
									if($CModalidadeProcessoLicitatorio  == "" ){
									   $CModalidadeProcessoLicitatorio  = 'null';
									}
								 # Insere o Limite de Compra#
								$Data   = date("Y-m-d H:i:s");
								$VLimiteModalidadeObras = moeda2float($VLimiteModalidadeObras);
								$VLimiteModalidadeOutrosServicos = moeda2float($VLimiteModalidadeOutrosServicos);
								$db->query("BEGIN TRANSACTION");
								$sql    = "INSERT INTO SFPC.TBLIMITECOMPRA (";
								$sql   .= "CLICOMCODI,CTPCOMCODI,FLICOMTIPO,CMODLICODI,VLICOMOBRA,VLICOMSERV,CUSUPOCODI,TLICOMULAT";
								$sql   .= ") VALUES ( ";
								$sql   .= " $CodigoLimiteCompra, $CodigoTipoCompra, '$TipoOrgao', $CModalidadeProcessoLicitatorio , " ;
								$sql   .= " $VLimiteModalidadeObras, $VLimiteModalidadeOutrosServicos,".$_SESSION['_cusupocodi_'].", '$Data' )";
								$result = $db->query($sql);
									if( PEAR::isError($result)){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$Mens                = 1;
										$Tipo                = 1;
										$Mensagem            = "Limite de Compra Incluído com Sucesso";
										$CodigoLimiteCompra                = "";
										$CodigoTipoCompra                  = "";
										$TipoOrgao                         = "";
										$CModalidadeProcessoLicitatorio    = "";
										$VLimiteModalidadeObras            = "";
										$VLimiteModalidadeOutrosServicos   = "";
										

									}
	                    }
			    }
		    

			}
							$db->disconnect();
		

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
function limpar(limpar){
  document.LimiteCompra.VLimiteModalidadeObras.value="";
  document.LimiteCompra.VLimiteModalidadeOutrosServicos.value="";
}


<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabLimiteCompraIncluir.php" method="post" name="LimiteCompra">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Limite de Compra > Incluir
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
	           INCLUIR - LIMITE DE COMPRA
			   </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Para Incluir um novo Limite de Compra, insira todos os dados, e depois clique em "Incluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
                  <tr>
				  <td class="textonormal" bgcolor="#DCEDF7">Tipo de Compra*</td>
                    <td class="textonormal">
					 <select name="CodigoTipoCompra" class="textonormal" onChange="javascript:limpar();document.LimiteCompra.submit();">
                  	   <option value="">Selecione o Tipo de Compra...</option>
                  	<!-- Mostra os Códigos da lei cadastrados -->
                  	<?php 
                		$db     = Conexao();
                		$sql    = "SELECT CTPCOMCODI,ETPCOMNOME FROM SFPC.TBTIPOCOMPRA ORDER BY CTPCOMCODI";
                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");

						}else{
							while( $Linha = $result->fetchRow() ){
								$Descricao   = substr($Linha[1],0,40);
								if( $Linha[0] == $CodigoTipoCompra ){
								echo"<option value='".$Linha [0]."' selected>$Descricao</option>";
								}else{
								echo"<option value='".$Linha [0]."'>$Descricao</option>";
								}
			               	}
			            }

  	              	$db->disconnect();
    	     	       ?>
                  </select>
                </td>
				</tr>

				<?php  if($CodigoTipoCompra == $CODIGO_TIPO_COMPRA_LICITACAO) { ?>

			    <tr>
				  <td class="textonormal" bgcolor="#DCEDF7">Modalidade*</td>
                    <td class="textonormal">
					 <select name="CModalidadeProcessoLicitatorio" class="textonormal" onChange="javascript:limpar();">
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
					            if( $Linha[0] ==  $CModalidadeProcessoLicitatorio){
								  echo"<option value='".$Linha [0]."' selected>".$Linha [1]."</option>";
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

				<?php  } ?>


			 <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Tipo de Órgão*</td>
                <td class="textonormal">
                  <select name="TipoOrgao" class="textonormal" onChange="javascript:limpar();">
                  	<option value="">Selecione o Tipo de Órgão...</option>
                  	<option value="D" <?php  if($TipoOrgao == "D")  {echo 'selected';} ?>>Administração Direta</option>
					<option value="I" <?php  if($TipoOrgao == "I") {echo 'selected';}?>>Administração Indireta</option>
				  </select>
                </td>
			</tr>

				<tr>
				  <td class="textonormal" bgcolor="#DCEDF7">Valor limite da Modalidade para Obras* </td>
				    <td class="textonormal">
                      <input type="text" name="VLimiteModalidadeObras" value="<?php  echo $VLimiteModalidadeObras; ?>" size="30" maxlength="60" class="textonormal">
			        </td>
				</tr>
				<tr>
                   <td class="textonormal" bgcolor="#DCEDF7">Valor limite da Modalidade Outros Serviços*</td>
                      <td class="textonormal">
                         <input type="text" name="VLimiteModalidadeOutrosServicos" value="<?php  echo $VLimiteModalidadeOutrosServicos; ?>" size="30" maxlength="60" class="textonormal">
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
      	      		<input type="button" value="Incluir" class="botao" onClick="javascript:enviar('Incluir');">
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
