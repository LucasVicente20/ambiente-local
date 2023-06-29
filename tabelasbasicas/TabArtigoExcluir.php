<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabLeiExcluir.php
# Autor:    Marcos Túlio de Almeida Alves
# Data:     12/08/11
# Objetivo: Programa de Exclusão do Tipo de Lei
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabLeiAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabLeiSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$TipoLei                 = $_POST['TipoLei'];
		$CodigoLei               = $_POST['CodigoLei'];
		$Artigo                  = strtoupper2(trim($_POST['Artigo']));
		$CodigoArtigo            = $_POST['CodigoArtigo'];
		$NLei                    = $_POST['NLei'];
		$Botao                   = $_POST['Botao'];
}else{
       $CodigoArtigo       		 = $_GET['CodigoArtigo'];
	   $CodigoLei          		 = $_GET['CodigoLei'];
	   $NLei               		 = $_GET['NLei'];
	   $Mens               		 = $_GET['Mens'];
	   $Tipo               		 = $_GET['Tipo'];
	   $Mensagem           		 = urldecode($_GET['Mensagem']);
		
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabArtigoExcluir.php";

# Critica dos Campos #
$db = Conexao();
if( $Botao == "Voltar" ){
		$Url = "TabArtigoAlterar.php?CodigoArtigo=$CodigoArtigo&CodigoLei=$CodigoLei&NLei=$NLei";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}elseif( $Botao == "Excluir" ){
		 $Mens     = 0;
         $Mensagem = "Informe: ";
                    #VERIFICA SE TIPO DE LEI TEM ALGUMA RELAÇÃO COM INCISO/PARAGRÁFO#
						$db     = Conexao();
						$sql    = "SELECT COUNT(*) 
							       FROM SFPC.TBARTIGOPORTAL ART, SFPC.TBINCISOPARAGRAFOPORTAL INC
                        		   WHERE 
                        		   ART.CTPLEITIPO = INC.CTPLEITIPO 
                        		   AND ART.CLEIPONUME = INC.CLEIPONUME
                        		   AND ART.CARTPOARTI = INC.CARTPOARTI
                        		   AND ART.CARTPOARTI = $CodigoArtigo 
								   AND ART.CLEIPONUME = $NLei
        						   AND ART.CTPLEITIPO = $CodigoLei ";
                         
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								while( $Linha = $result->fetchRow() ){
									   $QtdLei = $Linha[0];
							 }
								if( $QtdLei > 0 ){
								$Mens     = 1;
								$Tipo     = 2;
											  
					# Envia mensagem para página selecionar #
									$Mensagem = urlencode("Exclusão cancelada! Artigo ".$Artigo." está relacionado com algum Inciso!");
									$Url = "TabArtigoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
									if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
									exit();
                                          }			
									}                            

					# Exclui Ocorrência #
								$db->query("BEGIN TRANSACTION");
								$sql    = " DELETE FROM SFPC.TBARTIGOPORTAL WHERE 
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
										$Mensagem = urlencode("Artigo Excluído com Sucesso");
										$Url = "TabArtigoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
										if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit();
								}
				   	        
					
                    }

if( $Botao == "" ){
	$sql    = " SELECT TLEI.CTPLEITIPO,TLEI.ETPLEITIPO,ART.CLEIPONUME,ART.CARTPOARTI,ART.NARTPONOME 
		     FROM SFPC.TBARTIGOPORTAL ART,SFPC.TBTIPOLEIPORTAL TLEI,
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
<script language="javascript" type="">
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
<form action="TabArtigoExcluir.php" method="post" name="TipoArtigo">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Artigo > Excluir
			
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
	           EXCLUIR - ARTIGO
	                    </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
               Para confirmar a exclusão do Artigo clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
        </tr>             
			 <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo da Lei</td>
               	<td class="textonormal">
               		<?php echo $TipoLei; ?>
                	<input type="hidden" name="CodigoLei" value="<?php echo $CodigoLei; ?>">	                    						
			    </td>
             </tr>
			 <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Número da Lei </td>
               	<td class="textonormal">
               		<?php echo $NLei; ?>
                	<input type="hidden" name="NLei" value="<?php echo $NLei;?>">
                </td>
              </tr>
            
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
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Artigo </td>
               	<td class="textonormal">
               		<?php echo $Artigo; ?>
                	<input type="hidden" name="Artigo" value="<?php echo $Artigo;?>">
                </td>
              </tr>
			  </td>
        </table>
		</tr>
        <tr>
          <td>
   	        <table class="textonormal" border="0" align="right">
              <tr align="right">
          <td>
          	<input type="Button" value="Excluir" class="Botao" onclick="javascript:enviar('Excluir');">
          	<input name="voltar" type="Button" value="Voltar" class="Botao" onclick="javascript:enviar('Voltar')">
          	<input type="hidden" name="Botao" value="">
			<input type="hidden" name="CodigoLimiteCompra" value="<?php echo $CodigoLimiteCompra; ?>">
			
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
