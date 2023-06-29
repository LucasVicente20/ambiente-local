 <?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLeiAlterar.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     12/08/11
# Objetivo: Programa de Alteração do Artigo
# OBS.:     Tabulação 2 espaços
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
# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabArtigoExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabArtigoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$TipoLei                = $_POST['TipoLei'];
		$CodigoLei              = $_POST['CodigoLei'];
		$Artigo                 = strtoupper2(trim($_POST['Artigo']));
		$CodigoArtigo           = $_POST['CodigoArtigo'];
		$NLei                   = $_POST['NLei'];
		$Botao                  = $_POST['Botao'];
	
}else{
        $CodigoArtigo           = $_GET['CodigoArtigo'];	
        $CodigoLei              = $_GET['CodigoLei'];
        $NLei                   = $_GET['NLei'];	
	
}
# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabArtigoAlterar.php";

$db = Conexao();
if( $Botao == "Excluir" ){
		$Url = "TabArtigoExcluir.php?CodigoArtigo=$CodigoArtigo&CodigoLei=$CodigoLei&NLei=$NLei";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}elseif( $Botao == "Voltar" ){
	  header("location: TabArtigoSelecionar.php");
	  exit();
}elseif( $Botao == "Alterar" ) {
	  $Mens     = 0;
      $Mensagem = "Informe: ";
       if($Artigo == "" ){
		    if($Mens == 1){ $Mensagem.=", "; }
		    $Mens     = 1;
			$Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TipoArtigo.Artigo.focus();\" class=\"titulo2\"> Digite o Artigo </a>";
        }else if(strlen($Artigo) > 30){
            if($Mens == 1){ $Mensagem.=", "; }
		    $Mens     = 1;
			$Tipo     = 2;
  			$Mensagem .= "<a href=\"javascript:document.TipoArtigo.Artigo.focus();\" class=\"titulo2\"> O Artigo deve conter até 30 caracteres </a>";
        }
       		
      if( $Mens == 0 ){
				# Verifica a Duplicidade do Artigo #
				 
				$sql    = "SELECT COUNT(*) FROM SFPC.TBARTIGOPORTAL WHERE CTPLEITIPO = $CodigoLei
				 AND CLEIPONUME = $NLei
				 AND RTRIM(LTRIM(NARTPONOME)) = '$Artigo' ";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
						$Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    //	$Mens = 1;$Tipo = 2;
						//		$Mensagem = "<a href=\"javascript:document.TipoArtigo.Artigo.focus();\" class=\"titulo2\"> Tipo de Artigo Já Cadastrado</a>";
					}else{

				# Atualiza o Artigo #
				  $Data   = date("Y-m-d H:i:s");
				  $db->query("BEGIN TRANSACTION");
		   		  $sql    = "UPDATE SFPC.TBARTIGOPORTAL SET NARTPONOME = '$Artigo', CUSUPOCODI = ".$_SESSION['_cusupocodi_']." , 
				  TARTPOULAT = '$Data' WHERE 
				  CTPLEITIPO = $CodigoLei
				  AND CLEIPONUME = $NLei
				  AND CARTPOARTI = $CodigoArtigo ";
				  $result = $db->query($sql);
					if( PEAR::isError($result) ){
						$db->query("ROLLBACK");
				   	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
					    $db->query("COMMIT");
					    $db->query("END TRANSACTION");
					    $db->disconnect();

				# Envia mensagem para página selecionar #
				  $Mensagem = urlencode("Artigo Alterado com Sucesso");
				  $Url = "TabArtigoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
					   header("location: ".$Url);
				      }
				  }
			    }
    }
}
if( $Botao == "" ){
        
		$sql    = " 
		SELECT TLEI.CTPLEITIPO,TLEI.ETPLEITIPO,ART.CLEIPONUME,ART.CARTPOARTI,ART.NARTPONOME FROM SFPC.TBARTIGOPORTAL ART,SFPC.TBTIPOLEIPORTAL TLEI,
		SFPC.TBLEIPORTAL LEI WHERE
		ART.CTPLEITIPO = TLEI.CTPLEITIPO 
		AND ART.CLEIPONUME = LEI.CLEIPONUME 
		AND ART.CARTPOARTI = $CodigoArtigo 
		AND ART.CLEIPONUME = $NLei
        AND ART.CTPLEITIPO = $CodigoLei ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
		               $CodigoLei    = $Linha[0];
					   $TipoLei      = $Linha[1];
					   $NLei         = $Linha[2];
					   $CodigoArtigo = $Linha[3];
					   $Artigo       = $Linha[4];
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
		 document.TipoArtigo.Botao.value=valor;
		 document.TipoArtigo.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabArtigoAlterar.php" method="post" name="TipoArtigo">
<br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr><br>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Artigo > Alterar
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
	           MANTER - ARTIGO
       </td>
   </tr>
    <tr>
       <td class="textonormal" >
           <p align="justify">
            Para atualizar os dados da Descrição do Artigo, digite no campo e clique no botão "Alterar".<br>
		 	Para apagar o Artigo clique no botão "Excluir".<br>
			Para retornar a seleção inicial, clique no botão "Voltar".
           </p>
       </td>
    </tr>
     <tr>
       <td>
        <table>
	 <tr>
        <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo da Lei</td>
       	<td class="textonormal">
       		<?php echo $TipoLei; ?>
               <input type="hidden" name="CodigoLei" value="<?php echo $CodigoLei; ?>">
            <input type="hidden" name="TipoLei" value="<?php echo $TipoLei; ?>">				
	    </td>
    </tr>
	 <tr>
         <td class="textonormal" bgcolor="#DCEDF7" height="20">Número da Lei </td>
       	 <td class="textonormal">
             <?php echo $NLei; ?>
            <input type="hidden" name="NLei" value="<?php echo $NLei; ?>">	                    						
		</td>
    </tr>
	 <tr>
        <td class="textonormal" bgcolor="#DCEDF7" height="20">Código do Artigo </td>
       	<td class="textonormal">
       		<?php echo $CodigoArtigo; ?>
            <input type="hidden" name="CodigoArtigo" value="<?php echo $CodigoArtigo; ?>">
        </td>
    </tr>
	 <tr>
        <td class="textonormal" bgcolor="#DCEDF7">Descrição do Artigo* </td>
       	<td class="textonormal">
       		<input type="text" name="Artigo" size="40" maxlength="60" value="<?php echo $Artigo?>" class="textonormal">
        </td>
    </tr>
        </table>
        </td>
    </tr>
     <tr align="right">
         <td>
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
document.TipoArtigo.Artigo.focus();
//-->
</script>
