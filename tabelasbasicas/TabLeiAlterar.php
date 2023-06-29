<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLeiAlterar.php
# Autor:    Luiz Alves
# Data:     27/06/11
# Objetivo: Programa de Criação de leis - Demanda Redmine: #3281
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Luiz Alves
# Data:     20/09/2011
# Objetivo: Correção dos erros - Demanda Redmine: #3640
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     03/04/2012
# Objetivo: Correção dos erros - Demanda Redmine: #4375
#-------------------------------------------------------------------------

# Acesso ao arquivo de Funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabLeiExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabLeiSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao        			 = $_POST['Botao'];
		$TipoLei    			 = $_POST['TipoLei'];
		$DescLei   	 	         = $_POST['DescLei'];
		$DataLei                 = $_POST['DataLei'];
		$DescTipoLei             = $_POST['DescTipoLei'];
		if( $DataLei != "" ){ $DataLei = FormataData($DataLei); }
		$NumerodaLei   		     = $_POST['NumerodaLei'];
		$NCaracteres   			 = $_POST['NCaracteres'];
}else{
       $NumerodaLei     	     = $_GET['NumerodaLei'];
	   $TipoLei                  = $_GET['TipoLei'];



}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabLeiAlterar.php";

$db = Conexao();
if( $Botao == "Excluir" ){
		$Url = "TabLeiExcluir.php?NumerodaLei=$NumerodaLei&TipoLei=$TipoLei";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}elseif( $Botao == "Voltar" ){
		header("location: TabLeiSelecionar.php");
		exit();
}elseif( $Botao == "Alterar" ) {
	$Mens     = 0;
    $Mensagem = "Informe: ";
        if( $NumerodaLei == "" ) {
		 	  $Mens      = 1;
		 	  $Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.TabLeiAlterar.NumerodaLei.focus();\" class=\"titulo2\">Número da Lei </a>";
    }if (!ereg("^([0-9]){1,}$",$NumerodaLei) ) {
		  if($Mens == 1){ $Mensagem.=", "; }
		    $Mens      = 1;
			$Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TabLeiAlterar.NumerodaLei.focus();\" class=\"titulo2\"> O Número da Lei só deve conter números </a>";
		}
	if( $DataLei == "" ){
				if( $Mens == 1 ){ $Mensagem.=", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.TabLeiAlterar.DataLei.focus();\" class=\"titulo2\">Data da Lei </a>";
		}else{
				$MensErro = ValidaData($DataLei);
				if( $MensErro != "" ){
						if( $Mens == 1 ){ $Mensagem.=", "; }
						$Mens      = 1;
				  	$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.TabLeiAlterar.DataLei.focus();\" class=\"titulo2\">Data da Lei Válida</a>";
				}else {
				$Hoje = date("Ymd");

				$Data = substr($DataLei,-4).substr($DataLei,3,2).substr($DataLei,0,2);
				if( $Data > $Hoje ){
					if( $Mens == 1 ){ $Mensagem.=", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.TabLeiAlterar.DataLei.focus();\" class=\"titulo2\">Data Menor ou Igual a Data Atual</a>";
				}
			}
		}


	if($NCaracteres > "300"){
				if($Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Observação menor que 300 caracteres";
		}
    if( $Mens == 0 ){
	    # Atualiza o Lei #
		$Data   = date("Y-m-d H:i:s");
		$db->query("BEGIN TRANSACTION");
		$sql    = "UPDATE SFPC.TBLEIPORTAL SET DLEIPODATA = '".DataInvertida($DataLei)."' , NLEIPONOME = '$DescLei', TLEIPOULAT = '$Data',
		CUSUPOCODI = ".$_SESSION['_cusupocodi_']." WHERE
		CTPLEITIPO = $TipoLei
		AND CLEIPONUME = $NumerodaLei  ";
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
			$Mensagem = urlencode("Lei Alterada com Sucesso");
			$Url = "TabLeiSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
			header("location: ".$Url);
		}
	}

}
if( $Botao == "" ){
	$sql    = " SELECT B.CLEIPONUME, to_char(DLEIPODATA, 'DD-MM-YYYY'), B.NLEIPONOME, T.ETPLEITIPO
	FROM SFPC.TBLEIPORTAL B, SFPC.TBTIPOLEIPORTAL T WHERE
	B.CTPLEITIPO = T.CTPLEITIPO
	AND B.CLEIPONUME = $NumerodaLei
	AND B.CTPLEITIPO = $TipoLei";
	$result = $db->query($sql);
	if( PEAR::isError($result) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}else{
			while( $Linha = $result->fetchRow() ){
					$NumerodaLei       = $Linha[0];
					$DataLei           = $Linha[1];
					$DescLei    	   = $Linha[2];
					$DescTipoLei   	   = $Linha[3];
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
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript">
<!--
function enviar(valor){
	document.TabLeiAlterar.Botao.value=valor;
	document.TabLeiAlterar.submit();
}

function ncaracteres(valor){
	document.TabLeiAlterar.NCaracteres.value = '' +  document.TabLeiAlterar.DescLei.value.length;
	/* if( navigator.appName == 'Netscape' && valor ){ //Netscape Only
		document.TabLeiAlterar.ObjetoLei.focus();
	}*/
}
function AbreJanela(url,largura,altura) {
	window.open(url,'pagina','status=no,scrollbars=no,left=270,top=150,width='+largura+',height='+altura);
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabLeiAlterar.php" method="post" name="TabLeiAlterar">
<br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr><br>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Lei > Alterar
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
	           MANTER - LEI
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
             Para atualizar a Lei, preencha os campos Data da Lei, Descrição e clique no botão "Alterar".<br>
             Para apagar a Lei clique no botão "Excluir".<br>
			 Para retornar a seleção inicial, clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
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
			  <tr>
				<td class="textonormal" bgcolor="#DCEDF7">Data da Lei*</td>
				<td class="textonormal">
					    <input name="DataLei" id="DataLei" class="data" size="10" maxlength="10" value="<?php echo $DataLei?>" class="textonormal" type="text">
							   <a href="javascript:janela('../calendario.php?Formulario=TabLeiAlterar&amp;Campo=DataLei','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" alt="" border="0"></a>
				</td>
			  </tr>
	      	<tr>
			    <td class="textonormal" bgcolor="#DCEDF7">Descrição*</td>
			    <td class="textonormal">
			        <font class="textonormal">máximo de 300 caracteres</font>
					<input type="text" name="NCaracteres" disabled size="3" value="<?php echo $NCaracteres ?>" class="textonormal"><br>
					<textarea name="DescLei" cols="39" rows="5" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?php echo $DescLei; ?></textarea>
				</td>
			  </tr>
            </table>
          </td>
        </tr>
        <tr align="right">
          <td>
		    <input type="hidden" name="TipoLei" value="<?php echo $TipoLei; ?>">
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
document.TabLeiAlterar.NumerodaLei.focus();
ncaracteres();
//-->
</script>
