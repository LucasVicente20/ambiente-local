<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabGrupoExcluir.php
# Autor:    Rossana Lira
# Data:     04/04/03
# Objetivo: Programa de Exclusão do Grupo
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabGrupoAlterar.php' );
AddMenuAcesso( '/tabelasbasicas/TabGrupoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao       = $_POST['Botao'];
		$Critica     = $_POST['Critica'];
		$GrupoCodigo = $_POST['GrupoCodigo'];
}else{
		$GrupoCodigo = $_GET['GrupoCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabGrupoExcluir.php";

if( $Botao == "Voltar" ){
		$Url = "TabGrupoAlterar.php?GrupoCodigo=$GrupoCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
		header("location: ".$Url);
		exit();
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
		    $Mens     = 0;
			  $Mensagem = "Informe: ";

		    # Verifica se o grupo está relacionado com alguma licitação #
		    $db     = Conexao();
		    $sql    = "SELECT COUNT(*) FROM SFPC.TBLICITACAOPORTAL WHERE CGREMPCODI = $GrupoCodigo";
		    $result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha        = $result->fetchRow();
						$QtdLicitacao = $Linha[0];

				    if( $QtdLicitacao > 0 ) {
				        $Mens     = 1;
				        $Mensagem = "Exclusão Cancelada!<br>Grupo Relacionado com ($QtdLicitacao) Licitação(ões)";
				    }

				    # Verifica se o Grupo está relacionado com algum usuário #
				    $sql    = "SELECT COUNT(*) FROM SFPC.TBUSUARIOPORTAL WHERE CGREMPCODI = $GrupoCodigo";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha      = $result->fetchRow();
								$QtdUsuario = $Linha[0];
								if( $QtdUsuario > 0 ){
										if( $Mens == 1 ){ $Mensagem .= "<br>"; }else{ $Mensagem .= "Exclusão Cancelada!<br>";}
										$Mens      = 1;
										$Mensagem .= "Grupo Relacionado com ($QtdUsuario) Usuário(s)";
								}

						    # Verifica se o Grupo está relacionado com alguma comissão de licitação #
						    $sql    = "SELECT COUNT(*) FROM SFPC.TBCOMISSAOLICITACAO WHERE CGREMPCODI = $GrupoCodigo";
								$result = $db->query($sql);
								if (PEAR::isError($result)) {
								   ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Linha = $result->fetchRow();
										$QtdComissao = $Linha[0];

										if( $QtdComissao > 0 ) {
												if ($Mens == 1){$Mensagem .= "<br>";} else {$Mensagem .= "Exclusão Cancelada!<br>";}
												$Mens      = 1;
												$Mensagem .= "Grupo Relacionado com ($QtdComissao) Comissão(ões)";
										}
								    if( $Mens == 0 ){
												# Exclui Grupo/Órgão #
												$db->query("BEGIN TRANSACTION");
												$sql    = "DELETE FROM SFPC.TBGRUPOORGAO WHERE CGREMPCODI = $GrupoCodigo";
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
														$db->query("ROLLBACK");
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														# Exclui Grupo #
														$sql    = "DELETE FROM SFPC.TBGRUPOEMPRESA WHERE CGREMPCODI = $GrupoCodigo ";
														$result = $db->query($sql);
														if( PEAR::isError($result) ){
																$db->query("ROLLBACK");
														    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$db->query("COMMIT");
																$db->query("END TRANSACTION");
																$db->disconnect();

																# Envia mensagem para página selecionar #
																$Mensagem = urlencode("Grupo Excluído com Sucesso");
																$Url = "TabGrupoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
																if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																header("location: ".$Url);
																exit();
														}
												}
												$db->query("COMMIT");
												$db->query("END TRANSACTION");
												$db->disconnect();
								  	}else{
								       	$db->disconnect();
								 				$Mensagem = urlencode($Mensagem);
								 				$Url = "TabGrupoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=2";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								       	header("location: ".$Url);
								       	exit();
								   	}
								}
						}
				}
		}
}
if( $Critica == 0 ){
		$db     = Conexao();
		$sql    = "SELECT EGREMPDESC, EGREMPMAIL, EGREMPENDW, AGREMPFONE, CGREMPCODI ";
		$sql   .= "  FROM SFPC.TBGRUPOEMPRESA WHERE CGREMPCODI = $GrupoCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$GrupoDescricao = $Linha[0];
						$Email          = $Linha[1];
						$WWW            = $Linha[2];
						$Fone           = $Linha[3];
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
	document.Grupo.Botao.value=valor;
	document.Grupo.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabGrupoExcluir.php" method="post" name="Grupo">
<br><br><br><br><br>
<table cellpadding="3" border="0">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Grupo > Manter
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
	        	EXCLUIR - GRUPO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
               Para confirmar a exclusão do Grupo clique no botão "Excluir", caso contrário clique no botão "Voltar".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
               	<td class="textonormal">
               		<?php echo $GrupoDescricao; ?>
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="GrupoCodigo" value="<?php echo $GrupoCodigo; ?>">
                </td>
              </tr>
             	<tr>
              	<td class="textonormal" bgcolor="#DCEDF7" height="20">E-mail</td>
      	    			<td class="textonormal"><?php echo $Email; ?></td>
        	  	</tr>
        			<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Home Page</td>
    	      		<td class="textonormal">
    	      			<?php if( $WWW != "" ){ echo $WWW; }else{ echo "NÃO INFORMADO"; }; ?>
    	      		</td>
      	    	</tr>
							<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7" height="20">Telefone</td>
    	      		<td class="textonormal"><?php echo $Fone; ?></td>
      	    	</tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="submit" value="Excluir" class="botao" onclick="javascript:enviar('Excluir')">
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
