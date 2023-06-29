<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLimiteCompraAlterar.php
# Objetivo: Programa de Alteração do Limite de Compra
# Autor:    Marcos Túlio de Almeida Alves
# Data:     09/09/2011
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabLimiteCompraSelecionar.php' );
AddMenuAcesso( '/tabelasbasicas/TabLimiteCompraExcluir.php' );


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
$ErroPrograma = "TabLimiteCompraAlterar.php";
$CODIGO_TIPO_COMPRA_LICITACAO = 2; 
if( $Botao == "Excluir" ){
	$Url = "TabLimiteCompraExcluir.php?CodigoLimiteCompra=$CodigoLimiteCompra";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	header("location: ".$Url);
    exit();
	
}elseif ( $Botao == "Voltar" ){
	header("location: TabLimiteCompraSelecionar.php");
	exit();
}elseif( $Botao == "Alterar" ){
        $Mens     = 0;
		$Mensagem = "Informe: ";	  
		if($VLimiteModalidadeObras    == ""){
            if($Mens == 1){ $Mensagem.=", "; }
		    $Mens     = 1;
			$Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.LimiteCompra.VLimiteModalidadeObras.focus();\" class=\"titulo2\"> Digite o Valor Limite de Modalidade para Obras </a>";
		}
		if($VLimiteModalidadeOutrosServicos == "" ){
            if($Mens == 1){ $Mensagem.=", "; }
		    $Mens     = 1;
			$Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.LimiteCompra.VLimiteModalidadeOutrosServicos.focus();\" class=\"titulo2\"> Digite o Valor Limite para Modalidade Outros Serviços </a>";
        }
		
		  
    if( $Mens == 0 ){
           
				#Atualiza o Limite de Compra#
				$db     = Conexao();
				$Data   = date("Y-m-d H:i:s");
				$VLimiteModalidadeObras = moeda2float($VLimiteModalidadeObras);
				$VLimiteModalidadeOutrosServicos = moeda2float($VLimiteModalidadeOutrosServicos);
				$sql    = " UPDATE SFPC.TBLIMITECOMPRA SET VLICOMOBRA = $VLimiteModalidadeObras, VLICOMSERV = $VLimiteModalidadeOutrosServicos, 
				CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TLICOMULAT = '$Data' 
				WHERE CLICOMCODI = $CodigoLimiteCompra  ";
				$result = $db->query($sql);
					if( PEAR::isError($result) ){
						$RowBack = 1;
						$db->query("ROLLBACK");
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}
					else{
						$db->query("COMMIT");
						$db->query("END TRANSACTION");
						$db->disconnect();
						# Envia mensagem para página selecionar #
						$Mensagem = urlencode("Limite de compra Alterado com Sucesso");
						$Url = "TabLimiteCompraSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
					
					}
						
			
				
				
				
			
			
			
    }
}	
if( $Botao == "" ){
		# Carrega os dados do Limite de Compra selecionado #
		$db     = Conexao();
		$sql    = " SELECT LC.CTPCOMCODI,TC.ETPCOMNOME,LC.FLICOMTIPO,ML.EMODLIDESC,VLICOMOBRA,VLICOMSERV FROM 
		SFPC.TBLIMITECOMPRA LC LEFT OUTER JOIN SFPC.TBMODALIDADELICITACAO ML ON LC.CMODLICODI = ML.CMODLICODI , SFPC.TBTIPOCOMPRA TC 
		WHERE LC.CTPCOMCODI = TC.CTPCOMCODI AND LC.CLICOMCODI = $CodigoLimiteCompra  ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
		        $CodigoTipoCompra                  = $Linha[0];
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
<form action="TabLimiteCompraAlterar.php" method="post" name="LimiteCompra">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Limite de Compra > Alterar
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
	           ALTERAR - LIMITE DE COMPRA
			   </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Para Alterar o Limite de Compra,insira todos os dados, e depois clique em "Alterar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
                <tr>
				  <td class="textonormal" bgcolor="#DCEDF7">Tipo de Compra </td>
				    <td class="textonormal">
					    <?php 
							$db     = Conexao();
							$sql = "SELECT DISTINCT TC.ETPCOMNOME FROM SFPC.TBTIPOCOMPRA TC, SFPC.TBLIMITECOMPRA LC WHERE LC.CTPCOMCODI = TC.CTPCOMCODI AND LC.CTPCOMCODI = $CodigoTipoCompra";
							$result = $db->query($sql);
							if( PEAR::isError($result) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}
							else{
							    while( $Linha = $result->fetchRow() ){
								     $DescTipoCompra  = $Linha[0];
							    }	
							}
							$db->disconnect();
							echo $DescTipoCompra;
				
		
					    ?>
						<input type="hidden" name="CodigoTipoCompra" value="<?php  echo   $CodigoTipoCompra;?>">
					</td>
				</tr>
                <?php if($DescTipoCompra == "LICITAÇÃO") { ?>
				<tr>
				  <td class="textonormal" bgcolor="#DCEDF7">Modalidade</td>
                    <td class="textonormal">
					 <?php echo  $CModalidadeProcessoLicitatorio;?>
					 <input type="hidden" name="CModalidadeProcessoLicitatorio" value="<?php echo  $CModalidadeProcessoLicitatorio;?>">
					 </td>
				</tr>
			    <?php } ?>
				<tr>
					<td class="textonormal" bgcolor="#DCEDF7">Tipo de Órgão</td>
					<td class="textonormal">
					
					<?php   if ($TipoOrgao == "D"){
						 $DescTipoOrgao = "ADMINISTRAÇÃO DIRETA";
					    }
					    if ($TipoOrgao == "I"){
						  $DescTipoOrgao = "ADMINISTRAÇÃO INDIRETA";
						}
						echo $DescTipoOrgao;
					?> 
					<input type="hidden" name="TipoOrgao" value="<?php  echo $TipoOrgao;?>">
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
          	
			<input type="Button" value="Alterar" class="Botao" onclick="javascript:enviar('Alterar');">
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
