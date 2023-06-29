<?php 
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabIncisoExcluir.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     17/08/11
# Objetivo: Programa de Exclusão do Inciso/Paragráfo
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     03/07/12
# Objetivo: Correção dos erros - Demanda Redmine: #11894
#-------------------------------------------------------------------------
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabIncisoAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabIncisoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
	$CodigoLei      = $_POST['CodigoLei'];
	$NLei           = $_POST['NLei'];
	$CodigoArtigo   = $_POST['CodigoArtigo'];
	$SequenNInciso  = $_POST['SequenNInciso'];
	$Inciso         = strtoupper2(trim($_POST['Inciso']));
	$Botao          = $_POST['Botao'];
		
}else{
    $SequenNInciso	= $_GET['SequenNInciso'];
	$CodigoLei      = $_GET['CodigoLei'];
	$CodigoArtigo   = $_GET['CodigoArtigo'];
	$NLei           = $_GET['NLei'];		
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabIncisoExcluir.php";

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Url = "TabIncisoAlterar.php?SequenNInciso=$SequenNInciso&CodigoArtigo=$CodigoArtigo&CodigoLei=$CodigoLei&NLei=$NLei";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}elseif( $Botao == "Excluir" ){
		$Mens     = 0;
        $Mensagem = "Informe: ";
        #VERIFICA SE INCISO/PARÁGRAFO TEM ALGUMA RELAÇÃO COM SOLICITAÇÃO COMPRA #
	    $db     = Conexao();
	    $sql    = "SELECT COUNT(*) FROM SFPC.TBSOLICITACAOCOMPRA SOLIC WHERE
        CTPLEITIPO = $CodigoLei 
		AND CLEIPONUME = $NLei 
		AND CARTPOARTI = $CodigoArtigo 
		AND CINCPAINCI = $SequenNInciso ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		else{
				$Linha = $result->fetchRow();
				$QtdInc = $Linha[0];
				
				if( $QtdInc > 0 ){
				   $Mens     = 1;
				   $Tipo     = 2;
					# Envia mensagem para página selecionar #
					$Mensagem = urlencode("Exclusão cancelada! Inciso/Parágrafo está relacionado a uma Lei que está sendo utilizada por uma SCC.");
					$Url = "TabIncisoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
					header("location: ".$Url);
					exit();
				}
                else{
					# Exclui Ocorrência #
					$db->query("BEGIN TRANSACTION");
					$sql    = " DELETE FROM SFPC.TBINCISOPARAGRAFOPORTAL INC WHERE INC.CINCPAINCI = $SequenNInciso 
					AND INC.CTPLEITIPO = $CodigoLei
					AND INC.CLEIPONUME = $NLei
					AND INC.CARTPOARTI = $CodigoArtigo ";
					$result = $db->query($sql);
					
					if( PEAR::isError($result) ){
							$db->query("ROLLBACK");
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}
					else{
							$db->query("COMMIT");
							$db->query("END TRANSACTION");
							$db->disconnect();

							# Envia mensagem para página selecionar #
							$Mensagem = urlencode("Inciso/Parágrafo Excluído com Sucesso");
							$Url = "TabIncisoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
							if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
							header("location: ".$Url);
							exit();
					}
                }				
			
		}
			
			
}
if( $Botao == ""){ 
	$sql    =  
	" SELECT TLEI.CTPLEITIPO,TLEI.ETPLEITIPO,LEI.CLEIPONUME,ART.CARTPOARTI,INC.CINCPAINCI,INC.NINCPANOME 
		FROM SFPC.TBINCISOPARAGRAFOPORTAL INC, SFPC.TBARTIGOPORTAL ART, SFPC.TBTIPOLEIPORTAL TLEI,SFPC.TBLEIPORTAL LEI 
   	   WHERE INC.CTPLEITIPO = TLEI.CTPLEITIPO 
   		 AND INC.CTPLEITIPO = LEI.CTPLEITIPO 
   		 AND INC.CLEIPONUME = LEI.CLEIPONUME 
   		 AND INC.CTPLEITIPO = ART.CTPLEITIPO 
   		 AND INC.CLEIPONUME = ART.CLEIPONUME 
   		 AND INC.CARTPOARTI = ART.CARTPOARTI 
   		 AND INC.CTPLEITIPO = ART.CTPLEITIPO 
   		 AND INC.CLEIPONUME = ART.CLEIPONUME 
   		 AND INC.CARTPOARTI = ART.CARTPOARTI 
   		 AND INC.CTPLEITIPO = $CodigoLei 
   		 AND INC.CLEIPONUME = $NLei 
   		 AND INC.CARTPOARTI = $CodigoArtigo 
   		 AND INC.CINCPAINCI = $SequenNInciso ";
	$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
			   while($Linha = $result->fetchRow() ){
					 $CodigoLei     = $Linha[0];
					 $DescLei       = $Linha[1];
					 $NLei          = $Linha[2];
					 $CodigoArtigo  = $Linha[3];
					 $SequenNInciso = $Linha[4];
					 $Inciso        = $Linha[5];
						
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
<script language="javascript" type="">
<!--
function enviar(valor){
	document.TipoInciso.Botao.value=valor;
	document.TipoInciso.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabIncisoExcluir.php" method="post" name="TipoInciso">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Inciso/Parágrafo > Excluir
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
	           EXCLUIR - INCISO/PARÁGRAFO
	      </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão do Inciso/Parágrafo clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
                 <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Lei</td>
               	<td class="textonormal">
               		<?php  echo $DescLei;?>
                	<input type="hidden" name="CodigoLei" value="<?php echo $CodigoLei;?>">
                </td>
              </tr>
				<tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Número da Lei</td>
               	<td class="textonormal">
               		<?php  echo $NLei;?>
                	<input type="hidden" name="NLei" value="<?php echo $NLei;?>">
                </td>
              </tr>
			   <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Código do Artigo</td>
               	<td class="textonormal">
               		<?php  echo $CodigoArtigo;?>
                	<input type="hidden" name="CodigoArtigo" value="<?php echo $CodigoArtigo;?>">
                </td>
              </tr>
			  <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Código do Inciso/Parágrafo </td>
               	<td class="textonormal">
               		<?php  echo $SequenNInciso;?>
                	<input type="hidden" name="SequenNInciso" value="<?php echo $SequenNInciso;?>">
                </td>
              </tr>             
			 <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Descrição do Inciso/Parágrafo</td>
               	<td class="textonormal">
               		<?php  echo $Inciso;?>
                	<input type="hidden" name="Inciso" value="<?php echo $Inciso;?>">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td align="right">
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
