<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabIncisoIncluir.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     17/08/11
# Objetivo: Programa de Inclusão do Inciso/Paragráfo
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     28/03/12
# Objetivo: Correção dos erros - Demanda Redmine: #4506
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     12/04/12
# Objetivo: Correção dos erros - Demanda Redmine: #9144
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     03/07/12
# Objetivo: Correção dos erros - Demanda Redmine: #11894
#-------------------------------------------------------------------------
# Alterado: João Batista Brito
# Data:     25/09/12
# Objetivo: Correção dos erros - Demanda Redmine: #15947
#-------------------------------------------------------------------------
# Alterado: Osmar Celestino	
# Data:     10/05/2021
# Objetivo: Tarefa redmine #247954
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
	$CodigoLei    = $_POST['CodigoLei'];
	$NLei         = $_POST['NLei'];
	$CodigoArtigo = $_POST['CodigoArtigo'];
	$NumeroInciso = strtoupper2(trim($_POST['NumeroInciso']));
	$Inciso       = strtoupper2(trim($_POST['Inciso']));
	$Botao        = $_POST['Botao'];
}else{
	$Mensagem     = urldecode($_GET['Mensagem']);
	$Mens         = $_GET['Mens'];
	$Tipo         = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabIncisoIncluir.php";

if( $Botao == "Incluir" ){
	  $Mens     = 0;
	  $Mensagem = "Informe: ";
	if($CodigoLei == ""){
		if($Mens == 1){ $Mensagem.=", "; }
		$Mens  = 1;
		$Tipo  = 2;
		$Mensagem .= "<a href=\"javascript:document.TipoInciso.CodigoLei.focus();\" class=\"titulo2\">Selecione o Tipo da Lei</a>";
	}
	if($NLei == "" ){
    if($Mens == 1){ $Mensagem.=", "; }
	  $Mens  = 1;
	  $Tipo  = 2;
  	$Mensagem .= "<a href=\"javascript:document.TipoInciso.NLei.focus();\" class=\"titulo2\">Selecione o Número da Lei</a>";
	}
	if($CodigoArtigo == "" ){
    if($Mens == 1){ $Mensagem.=", "; }
	  $Mens  = 1;
	  $Tipo  = 2;
  	$Mensagem .= "<a href=\"javascript:document.TipoInciso.CodigoArtigo.focus();\" class=\"titulo2\">Selecione o Código do Artigo</a>";
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
	if($Inciso == ""){
		if($Mens == 1){ $Mensagem.=", "; }
	  $Mens  = 1;
	  $Tipo  = 2;
  	$Mensagem .= "<a href=\"javascript:document.TipoInciso.Inciso.focus();\" class=\"titulo2\">Descrição do Inciso/Parágrafo</a>";
    } elseif(strlen($Inciso) > 1000){
      if($Mens == 1){ $Mensagem.=", "; }
	 	  $Mens  = 1;
		  $Tipo  = 2;
  		$Mensagem .= "<a href=\"javascript:document.TipoInciso.Inciso.focus();\" class=\"titulo2\">A Descrição do Inciso/Parágrafo deve conter até 1000 caracteres</a>";
      }
	if( $Mens == 0 ) {

# Verifica a Duplicidade do Inciso/paragrafo #

		$db  = Conexao();
	  $sql =  "SELECT COUNT(*)
		      		 FROM SFPC.TBINCISOPARAGRAFOPORTAL
	   	     		WHERE RTRIM(LTRIM(CTPLEITIPO)) = $CodigoLei
	   	      	  AND RTRIM(LTRIM(CLEIPONUME)) = $NLei
	   	    	    AND RTRIM(LTRIM(CARTPOARTI)) = $CodigoArtigo
	   	    	    AND RTRIM(LTRIM(NINCPANUME)) = '$NumeroInciso'    ";
	 	$result = $db->query($sql);

	 	if (PEAR::isError($result)) {
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
	    	$Linha = $result->fetchRow();
		    $Qtd = $Linha[0];
	    	if( $Qtd > 0 ) {
			  $Mens  = 1;
			  $Tipo  = 2;
				$Mensagem = "<a href=\"javascript:document.TipoInciso.CodigoLei.focus();\" class=\"titulo2\"> Inciso/Parágrafo Já Cadastrado</a>";
			  }else{

# Insere o Artigo#

			  $Data = date("Y-m-d H:i:s");
				$db->query("BEGIN TRANSACTION");

				$sql = "LOCK TABLE SFPC.TBINCISOPARAGRAFOPORTAL";
				$result = $db->query($sql);
				if (PEAR::isError($result)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					exit();
				}

				$sql = "SELECT MAX(CINCPAINCI)+1 AS NumeroInciso FROM SFPC.TBINCISOPARAGRAFOPORTAL ";
				$result = $db->query($sql);
				if (PEAR::isError($result)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					exit();
				}
				$Linha = $result->fetchRow();
				$CodigoInciso = $Linha[0];
				if(is_null($CodigoInciso)){
					$CodigoInciso = 1;
				}
				
				$sql  = "INSERT INTO SFPC.TBINCISOPARAGRAFOPORTAL (";
				$sql .= "CTPLEITIPO,CLEIPONUME,CARTPOARTI,CINCPAINCI,NINCPANUME,NINCPANOME,CUSUPOCODI,TINCPAULAT";
				$sql .= ") VALUES ( ";
				$sql .= " $CodigoLei, $NLei, $CodigoArtigo, $CodigoInciso, '$NumeroInciso' , '$Inciso', ";
				$sql .= " ".$_SESSION['_cusupocodi_'].", '$Data' )";
				$result = $db->query($sql);
				if( PEAR::isError($result)){
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
					$db->query("COMMIT");
					$Mens         = 1;
					$Tipo         = 1;
					$Mensagem     = "Tipo de Inciso/Paragráfo Incluído com Sucesso";
					$CodigoLei    = "";
					$NLei	      = "";
					$CodigoArtigo = "";
					$NumeroInciso = "";
					$Inciso       = "";
				}
				$db->query("END TRANSACTION");
		    	$db->disconnect();
			}
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
<form action="TabIncisoIncluir.php" method="post" name="TipoInciso">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Inciso/Parágrafo > Incluir
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
	           INCLUIR - INCISO/PARÁGRAFO
		  </td>
        </tr>
        <tr>
          <td class="textonormal" bgcolor="#FFFFFF">
             <p align="justify">
              Para incluir um novo Inciso/Parágrafo, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
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
                	if( $CodigoLei != "" ) {

                		$db  = Conexao();
                		$sql = "SELECT CLEIPONUME
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
					 <select name="CodigoArtigo" class="textonormal"onChange="javascript:submit();">
                  	   <option value="">Selecione o Código do Artigo...</option>
                  	<!-- Mostra os Códigos da lei cadastrados -->
                  	<?php

                  	if($NLei != "" )  {
									  $db   =  Conexao();
                		$sql  =  "SELECT CARTPOARTI
                		      			FROM SFPC.TBARTIGOPORTAL ART , SFPC.TBLEIPORTAL LEI
                				       WHERE ART.CTPLEITIPO = LEI.CTPLEITIPO
                					       AND ART.CLEIPONUME = LEI.CLEIPONUME
									               AND ART.CLEIPONUME = $NLei
									               AND ART.CTPLEITIPO = $CodigoLei	";

                		$result = $db->query($sql);
                		if( PEAR::isError($result) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");

						}else{
							while( $Linha = $result->fetchRow() ){
								if($Linha[0] == $CodigoArtigo){
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
						  <td class="textonormal" bgcolor="#DCEDF7">Número do Inciso/Parágrafo*</td>
						  <td class="textonormal">
							  <input type="text" name="NumeroInciso" value="<?php echo $NumeroInciso; ?>" size="10" maxlength="10" class="textonormal" >
						  </td>


					<tr>
					   <td class="textonormal" bgcolor="#DCEDF7">Descrição do Inciso/Parágrafo*</td>
						  <td class="textonormal">
							 <input type="text" name="Inciso" value="<?php echo $Inciso; ?>" size="50" maxlength="1000" class="textonormal" >
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
document.TipoInciso.SequenNInciso.focus();
//-->
</script>
