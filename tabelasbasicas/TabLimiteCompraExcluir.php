<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLimiteCompraExcluir.php
# Objetivo: Programa de Exclusão do Limite de Compra
# Autor:    Marcos Túlio
# Data:     09/09/2011
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabLimiteCompraSelecionar.php' );
AddMenuAcesso( '/tabelasbasicas/TabLimiteCompraAlterar.php' );


# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$CodigoLimiteCompra                = $_POST['CodigoLimiteCompra'];
		$CodigoTipoCompra                  = $_POST['CodigoTipoCompra'];
		$TipoOrgao                         = strtoupper2(trim($_POST['TipoOrgao']));
		$CModalidadeProcessoLicitatorio    = $_POST['CModalidadeProcessoLicitatorio'];
		$VLimiteModalidadeObras            = $_POST['VLimiteModalidadeObras'];
		$VLimiteModalidadeOutrosServicos   = $_POST['VLimiteModalidadeOutrosServicos'];
		$Botao                             = $_POST['Botao'];
}else{
	    $CodigoLimiteCompra   = $_GET['CodigoLimiteCompra'];
		$Mens                 = $_GET['Mens'];
		$Tipo                 = $_GET['Tipo'];
		$Mensagem             = urldecode($_GET['Mensagem']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabLimiteCompraExlcuir.php";
$CODIGO_TIPO_COMPRA_LICITACAO = 2; 
$db = Conexao();
if ( $Botao == "Voltar" ){
		$Url = "TabLimiteCompraAlterar.php?CodigoLimiteCompra=$CodigoLimiteCompra";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}elseif( $Botao == "Excluir" ){
        $Mens     = 0;
        $Mensagem = "Informe: ";
                                
								# Exclui Ocorrência #
								$db->query("BEGIN TRANSACTION");
								$sql    = " DELETE FROM SFPC.TBLIMITECOMPRA WHERE CLICOMCODI = $CodigoLimiteCompra  ";
								$result = $db->query($sql);
								if( PEAR::isError($result) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$db->disconnect();

										# Envia mensagem para página selecionar #
										$Mensagem = urlencode("Tipo de Limite de Compra Excluído com Sucesso");
										$Url = "TabLimiteCompraSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();
								}
				   	}
		
		
if( $Botao == "" ){
		# Carrega os dados do Limite de Compra selecionado #
		$db     = Conexao();
		$sql    = " SELECT LC.CTPCOMCODI,TC.ETPCOMNOME,LC.FLICOMTIPO,ML.EMODLIDESC,VLICOMOBRA,VLICOMSERV FROM ";
		$sql   .= " SFPC.TBLIMITECOMPRA LC LEFT OUTER JOIN SFPC.TBMODALIDADELICITACAO ML ON LC.CMODLICODI = ML.CMODLICODI , SFPC.TBTIPOCOMPRA TC ";
		$sql   .= " WHERE LC.CTPCOMCODI = TC.CTPCOMCODI AND LC.CLICOMCODI = $CodigoLimiteCompra  ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
		        $CodigoTipoCompra                  = $Linha[0];
				$DescTipoCompra                    = $Linha[1];
				$TipoOrgao                         = $Linha[2];
		        $CModalidadeProcessoLicitatorio    = $Linha[3];
		        $VLimiteModalidadeObras            = $Linha[4];
		        $VLimiteModalidadeOutrosServicos   = $Linha[5];
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
<form action="TabLimiteCompraExcluir.php" method="post" name="LimiteCompra">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Limite de Compra > Excluir
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
	           EXCLUIR - LIMITE DE COMPRA
			   </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Para confirmar a exclusão do Limite de Compra clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
          <tr>
             <td>
             <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Compra </td>
               	<td class="textonormal">
               		<?php  echo $DescTipoCompra; ?>
                	<input type="hidden" name="DescTipoCompra" value="<?php  echo $DescTipoCompra;?>">
					<input type="hidden" name="CodigoTipoCompra" value="<?php echo $CodigoTipoCompra;?>">
                </td>
              </tr>
				<?php if($CodigoTipoCompra == $CODIGO_TIPO_COMPRA_LICITACAO) { ?>
				<tr>
				  <td class="textonormal" bgcolor="#DCEDF7">Modalidade</td>
				    <td class="textonormal">
					<?php  echo $CModalidadeProcessoLicitatorio ; ?>
                      <input type="hidden" name="CModalidadeProcessoLicitatorio" value="<?php  echo $CModalidadeProcessoLicitatorio ; ?>">
			        </td>
				</tr> 
			    <?php } ?>
				<tr>
					<td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Órgão </td>
					<td class="textonormal">
						<?php  
							if ( $TipoOrgao == "D"){
							  $TipoOrgao = "ADMINISTRAÇÃO DIRETA";
							  
							}
							  if ( $TipoOrgao == "I"){
							  $TipoOrgao = "ADMINISTRAÇÃO INDIRETA";
							  
							} echo $TipoOrgao; 
						?>
                	
                    </td>
                </tr>
			    <tr>
				  <td class="textonormal" bgcolor="#DCEDF7">Valor limite da Modalidade para Obras </td>
				    <td class="textonormal">
                      <input type="text" name="VLimiteModalidadeObras" value="<?php  echo converte_valor_estoques($VLimiteModalidadeObras); ?>" size="30" maxlength="60" class="textonormal">
			        </td>
				</tr>
				<tr>
                   <td class="textonormal" bgcolor="#DCEDF7">Valor limite da Modalidade Outros Serviços</td>
                      <td class="textonormal">
                         <input type="text" name="VLimiteModalidadeOutrosServicos" value="<?php  echo converte_valor_estoques($VLimiteModalidadeOutrosServicos); ?>" size="30" maxlength="60" class="textonormal">
			          </td>				
				</tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
   	        <table class="textonormal" border="0" align="right">
              <tr align="right">
          <td>
          	<input type="Button" value="Excluir" class="Botao" onclick="javascript:enviar('Excluir');">
          	<input name="voltar" type="Button" value="Voltar" class="Botao" onclick="javascript:enviar('Voltar')">
          	<input type="hidden" name="Botao" value="">
			<input type="hidden" name="CodigoLimiteCompra" value="<?php  echo $CodigoLimiteCompra; ?>">
			
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
<script language="javascript" type="">
<!--
document.LimiteCompra.CodigoTipoCompra.focus();
//-->
</script>
