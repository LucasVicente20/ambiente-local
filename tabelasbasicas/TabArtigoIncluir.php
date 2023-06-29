<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabArtigoIncluir.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     02/12/11
# Objetivo: Programa de Inclusão do Artigo
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     28/03/12
# Objetivo: Correção dos erros - Demanda Redmine: #4506
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     12/04/12
# Objetivo: Correção dos erros - Demanda Redmine: #9143
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
		$CodigoLei                = $_POST['CodigoLei'];
		$Artigo                   = strtoupper2(trim($_POST['Artigo']));
		$CodigoArtigo             = $_POST['CodigoArtigo'];
		$NLei                     = $_POST['NLei'];
		$Botao                    = $_POST['Botao'];
}else{
		$Mensagem     = urldecode($_GET['Mensagem']);
		$Mens         = $_GET['Mens'];
		$Tipo         = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabArtigoIncluir.php";

if( $Botao == "Incluir" )
    {
		$Mens     = 0;
		$Mensagem = "Informe: ";
	    if( $CodigoLei == ""){
		    if($Mens == 1){ $Mensagem.=", "; }
			$Mens     = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TipoArtigo.CodigoLei.focus();\" class=\"titulo2\">Selecione o Tipo da Lei</a>";
		}
		if( $NLei == "" ){
            if($Mens == 1){ $Mensagem.=", "; }
		    $Mens     = 1;
			$Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TipoArtigo.NLei.focus();\" class=\"titulo2\">Selecione o Número da Lei</a>";
	    }
		if($CodigoArtigo == ""){
            if($Mens == 1){ $Mensagem.=", "; }
		    $Mens     = 1;
			$Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TipoArtigo.CodigoArtigo.focus();\" class=\"titulo2\">Digite o Código do Artigo</a>";
		}
		else if ( (!ereg("^[0-9]{1,}$",$CodigoArtigo)) or (!ereg("^[^0]{1}",$CodigoArtigo)) ) {
		  if($Mens == 1){ $Mensagem.=", "; }
		    $Mens      = 1;
			$Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TipoArtigo.CodigoArtigo.focus();\" class=\"titulo2\">Código
            do Artigo deve ter valor maior que zero e diferente de letras</a>";
		}
		if( $Artigo == ""){
		    if($Mens == 1){ $Mensagem.=", "; }
		    $Mens     = 1;
			$Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TipoArtigo.Artigo.focus();\" class=\"titulo2\">Descrição do Artigo</a>";
        }
		else if(strlen($Artigo) > 30){
            if($Mens == 1){ $Mensagem.=", "; }
		    $Mens     = 1;
			$Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.TipoArtigo.Artigo.focus();\" class=\"titulo2\">Artigo deve conter até 30 caracteres</a>";
        }
        if( $Mens == 0 ) {
                 # Verifica a Duplicidade do Artigo #
				$db     = Conexao();
		   	    $sql    = "SELECT COUNT(*) FROM SFPC.TBARTIGOPORTAL WHERE  RTRIM(LTRIM(CTPLEITIPO)) = $CodigoLei AND RTRIM(LTRIM(CLEIPONUME)) = $NLei AND
				RTRIM(LTRIM(CARTPOARTI)) = $CodigoArtigo ";
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
							$Mensagem = "<a href=\"javascript:document.TipoArtigo.CodigoLei.focus();\" class=\"titulo2\"> Artigo Já Cadastrado</a>";
						}
					else{
						# Insere o Artigo #
							$Data   = date("Y-m-d H:i:s");
							$db->query("BEGIN TRANSACTION");
							$sql    = "INSERT INTO SFPC.TBARTIGOPORTAL (";
							$sql   .= "CTPLEITIPO,CLEIPONUME,CARTPOARTI,NARTPONOME,CUSUPOCODI,TARTPOULAT";
							$sql   .= ") VALUES ( ";
							$sql   .= " $CodigoLei, $NLei, $CodigoArtigo, '$Artigo' , ";
							$sql   .= " ".$_SESSION['_cusupocodi_'].", '$Data' )";
							$result = $db->query($sql);
									if( PEAR::isError($result)){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");
										$Mens                = 1;
										$Tipo                = 1;
										$Mensagem            = "Atenção! Artigo Incluído com Sucesso.";
										$CodigoLei                = "";
										$Artigo                   = "";
										$CodigoArtigo             = "";
										$NLei                     = "";

									}

		                }
		                $db->disconnect();
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
<form action="TabArtigoIncluir.php" method="post" name="TipoArtigo">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Artigo > Incluir
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
	           INCLUIR - ARTIGO
	    </td>
    </tr>
     <tr>
        <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
             Para incluir um novo Artigo, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
             </p>
        </td>
    </tr>
     <tr>
        <td>
           <table>
     <tr>
	    <td class="textonormal" bgcolor="#DCEDF7">Tipo de Lei*</td>
        <td class="textonormal">
		<select name="CodigoLei" class="textonormal" onChange="javascript:submit();">
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
								if( $Linha[0] == $CodigoLei ){
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
		 <select name="NLei" class="textonormal" onChange="javascript:submit();" >
                  	   <option value="">Selecione o Número da Lei...</option>
                  	<!-- Mostra os Códigos da lei cadastrados -->
                  	<?php
                	  if($CodigoLei != ""){
						 $db     = Conexao();
                		 $sql    = "SELECT CLEIPONUME FROM SFPC.TBLEIPORTAL LEI , SFPC.TBTIPOLEIPORTAL TIPO WHERE LEI.CTPLEITIPO = TIPO.CTPLEITIPO
						           AND TIPO.CTPLEITIPO = $CodigoLei";
                		 $result = $db->query($sql);
                		 if( PEAR::isError($result) ){
						 	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");

						}else{
							while( $Linha = $result->fetchRow() ){
							   if( $Linha[0] == $NLei ){
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
				    <td class="textonormal" bgcolor="#DCEDF7">Código do Artigo*</td>
				    <td class="textonormal">
                    <input type="text" name="CodigoArtigo" value="<?php echo $CodigoArtigo; ?>" size="5" maxlength="60" class="textonormal">
			        </td>
				</tr>
				<tr>
                    <td class="textonormal" bgcolor="#DCEDF7">Descrição do Artigo*</td>
                    <td class="textonormal">
                    <input type="text" name="Artigo" value="<?php echo $Artigo; ?>" size="30" maxlength="60" class="textonormal">
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
<script language="javascript">
<!--
document.TipoArtigo.CodigoArtigo.focus();
//-->
</script>
