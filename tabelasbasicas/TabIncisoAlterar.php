<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabIncisoAlterar.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     17/08/11
# Objetivo: Programa de Alteração do Inciso/Paragráfo
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     28/03/12
# Objetivo: Correção dos erros - Demanda Redmine: #4506
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     12/04/12
# Objetivo: Correção dos erros - Demanda Redmine: #8892
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     17/04/12
# Objetivo: Correção dos erros - Demanda Redmine: #9226
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     03/07/12
# Objetivo: Correção dos erros - Demanda Redmine: #11894
#-------------------------------------------------------------------------
# Alterado: Osmar Celestino	
# Data:     10/05/2021
# Objetivo: Tarefa redmine #247954
#-------------------------------------------------------------------------




# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabIncisoExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabIncisoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
	$CodigoLei      = $_POST['CodigoLei'];
	$DescLei        = $_POST['DescLei'];
	$Inciso         = strtoupper2(trim($_POST['Inciso']));
    $CodigoArtigo   = $_POST['CodigoArtigo'];
	$NumeroInciso 	= strtoupper2(trim($_POST['NumeroInciso']));
    $NLei           = $_POST['NLei'];
	$SequenNInciso  = $_POST['SequenNInciso'];
	$Botao          = $_POST['Botao'];
}else{
	$SequenNInciso  = $_GET['SequenNInciso'];
	$CodigoLei      = $_GET['CodigoLei'];
	$CodigoArtigo   = $_GET['CodigoArtigo'];
	$NLei           = $_GET['NLei'];	 
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabIncisoAlterar.php";

$db = Conexao();
if( $Botao == "Excluir" ){
	$Url = "TabIncisoExcluir.php?SequenNInciso=$SequenNInciso&CodigoArtigo=$CodigoArtigo&CodigoLei=$CodigoLei&NLei=$NLei";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	header("location: ".$Url);
	exit();
}elseif( $Botao == "Voltar" ){
	header("location: TabIncisoSelecionar.php");
	exit();
}elseif( $Botao == "Alterar" ) {
	$Mens     = 0;
    $Mensagem = "Informe: ";
	if($CodigoLei == ""){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens     = 1;
		$Tipo      = 2;
  		$Mensagem .= "<a href=\"javascript:document.TipoInciso.CodigoLei.focus();\" class=\"titulo2\">Selecione o Tipo da Lei</a>";
	}
	if($NLei == ""){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens     = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.TipoInciso.NLei.focus();\" class=\"titulo2\"> Selecione o Número da Lei </a>";
	}
	if($CodigoArtigo == ""){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens     = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.TipoInciso.CodigoArtigo.focus();\" class=\"titulo2\"> Selecione o Código do Artigo </a>";
	}
		
	// Criação do novo campo Número do Inciso/Parágrafo " Romanos "		
	if($NumeroInciso == ""){
       	if($Mens == 1){ $Mensagem.=", "; }
	   	$Mens  = 1;
	   	$Tipo  = 2;
  	   	$Mensagem .= "<a href=\"javascript:document.TipoInciso.NumeroInciso.focus();\" class=\"titulo2\">Digite o Número do Inciso/Parágrafo</a>";
	} 
	 elseif(strlen($NumeroInciso) > 10){
       	if($Mens == 1){ $Mensagem.=", "; }
	   	$Mens  = 1;
	   	$Tipo  = 2;
  	   	$Mensagem .= "<a href=\"javascript:document.TipoInciso.NumeroInciso.focus();\" class=\"titulo2\">Número do Inciso/Parágrafo deve conter até 10 caracteres</a>";
	}
	// Final do campo Inciso/Parágrafo  
		
	/*if($SequenNInciso == ""){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens     = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.TipoInciso.SequenNInciso.focus();\" class=\"titulo2\"> Digite o Sequencial Númerico do Inciso/Parágrafo </a>";
	}else if(strlen($SequenNInciso) > 3){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens     = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.TipoInciso.SequenNInciso.focus();\" class=\"titulo2\"> O Sequencial Númerico do Inciso/Parágrafo deve conter até 3 caracteres </a>";		
	}if (!ereg("^([0-9]){1,}$",$SequenNInciso) ) {
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.TipoInciso.SequenNInciso.focus();\" class=\"titulo2\"> O Sequencial Númerico do Inciso/Parágrafo só deve conter números </a>";
	}*/
	if($Inciso == "" ){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens     = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.TipoInciso.Inciso.focus();\" class=\"titulo2\"> Digite a Descrição Inciso/Parágrafo </a>";
	}else if(strlen($Inciso) > 1000){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens     = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.TipoInciso.Inciso.focus();\" class=\"titulo2\"> O Inciso/Parágrafo deve conter até 1000 caracteres </a>";
	}
	if($Mens == 0 ){
		$Mensagem = "";
		# Verifica a Duplicidade do Artigo por numero #
		$sql = "SELECT COUNT(*) FROM SFPC.TBINCISOPARAGRAFOPORTAL WHERE CTPLEITIPO = $CodigoLei
					AND CLEIPONUME = $NLei
					AND CARTPOARTI = $CodigoArtigo 
					AND CINCPAINCI != $SequenNInciso
					AND RTRIM(LTRIM(NINCPANUME)) = '$NumeroInciso' ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
    		$Linha = $result->fetchRow();
			$Qtd = $Linha[0];
    		if( $Qtd > 0 ) {
	    		$Mens = 1;$Tipo = 2;
				$Mensagem.= "<a href=\"javascript:document.TipoInciso.Inciso.focus();\" class=\"titulo2\"> Número do Inciso/Parágrafo Já Cadastrado</a>";
			}else{
				# Atualiza o Artigo #
				$Data   = date("Y-m-d H:i:s");
				$db->query("BEGIN TRANSACTION");
				$sql    = " UPDATE SFPC.TBINCISOPARAGRAFOPORTAL ";
				$sql   .= " SET NINCPANUME = '$NumeroInciso', ";
				$sql   .= " NINCPANOME = '$Inciso', ";
				$sql   .= " CUSUPOCODI = ".$_SESSION['_cusupocodi_']." , TINCPAULAT = '$Data' ";
				$sql   .= " WHERE 
					CTPLEITIPO = $CodigoLei
					AND CLEIPONUME = $NLei
					AND CARTPOARTI = $CodigoArtigo
					AND CINCPAINCI = $SequenNInciso";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
			        $db->query("COMMIT");
			        $db->query("END TRANSACTION");
			        $db->disconnect();
	
					# Envia mensagem para página selecionar #
					$Mensagem = urlencode("Inciso/Parágrafo Alterado com Sucesso");
					$Url = "TabIncisoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
					header("location: ".$Url);
				}
			}
		}
    }
}

if( $Botao == "" ){    
		
	$sql = " 
	SELECT TLEI.CTPLEITIPO,TLEI.ETPLEITIPO,LEI.CLEIPONUME,ART.CARTPOARTI,INC.CINCPAINCI,NINCPANUME,INC.NINCPANOME 
	  FROM SFPC.TBINCISOPARAGRAFOPORTAL INC, SFPC.TBARTIGOPORTAL ART, SFPC.TBTIPOLEIPORTAL TLEI,SFPC.TBLEIPORTAL LEI 
     WHERE INC.CTPLEITIPO     = TLEI.CTPLEITIPO 
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
	   AND INC.CINCPAINCI = $SequenNInciso
	";
	$result = $db->query($sql);
	if( PEAR::isError($result) ){
	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}else{
		while(	$Linha = $result->fetchRow() ){
		       	$CodigoLei     = $Linha[0];
				$DescLei       = $Linha[1];
				$NLei          = $Linha[2];
				$CodigoArtigo  = $Linha[3];
				$SequenNInciso = $Linha[4];
				$NumeroInciso  = $Linha[5];
				$Inciso        = $Linha[6];
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
<form action="TabIncisoAlterar.php" method="post" name="TipoInciso">
<br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr><br>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Inciso/Parágrafo > Alterar
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
	           MANTER - Inciso/Parágrafo
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
             Para atualizar a Lei, preencha o campo Descrição do Inciso/Parágrafo e clique no botão "Alterar".<br>
			 Para apagar o Inciso/Parágrafo clique no botão "Excluir".<br>
			 Para retornar a seleção inicial, clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Lei</td>
               	<td class="textonormal">
               		<?php echo $DescLei;?>
                	<input type="hidden" name="CodigoLei" value="<?php echo $CodigoLei;?>">
                	<input type="hidden" name="DescLei" value="<?php echo $DescLei;?>">
                </td>
              </tr>
				<tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Número da Lei</td>
               	<td class="textonormal">
               		<?php echo $NLei;?>
                	<input type="hidden" name="NLei" value="<?php echo $NLei;?>">
                </td>
              </tr>
			   <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Código do Artigo</td>
               	<td class="textonormal">
               		<?php echo $CodigoArtigo;?>
                	<input type="hidden" name="CodigoArtigo" value="<?php echo $CodigoArtigo;?>">
                </td>
              </tr>
			  <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Número do Inciso/Parágrafo </td>
               	<td class="textonormal">
               		<?php //echo $SequenNInciso;?>
                	<input type="hidden" name="SequenNInciso" value="<?php echo $SequenNInciso;?>" />
                	<input type="text" name="NumeroInciso" value="<?php echo $NumeroInciso; ?>" size="10" maxlength="10" class="textonormal" />
                </td>
              </tr>             
			  <tr>
                 <td class="textonormal" bgcolor="#DCEDF7">Descrição do Inciso/Parágrafo*</td>
				 <td class="textonormal">
					<input type="text" name="Inciso" value="<?php echo $Inciso; ?>" size="50" maxlength="1000" class="textonormal" />
		         </td>				
			  </tr>		  
 			</table>
          </td>
        </tr>
        <tr align="right">
          <td>
          	<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');" />
			<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');" />
          	<input name="voltar" type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')" />
          	<input type="hidden" name="Botao" value="" />
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
document.TipoInciso.Inciso.focus();
//-->
</script>
