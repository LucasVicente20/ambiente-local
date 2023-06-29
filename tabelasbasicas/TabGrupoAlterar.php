<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabGrupoAlterar.php
# Autor:    Rossana Lira
# Data:     03/04/03
# Objetivo: Programa de Alteração da Grupo
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabGrupoExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabGrupoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                = $_POST['Botao'];
		$Critica              = $_POST['Critica'];
		$GrupoCodigo          = $_POST['GrupoCodigo'];
		$GrupoDescricao       = strtoupper2(trim($_POST['GrupoDescricao']));
		$Email                = trim($_POST['Email']);
		$WWW                  = trim($_POST['WWW']);
		$Fone                 = trim($_POST['Fone']);
		$OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
}else{
		$GrupoCodigo          = $_GET['GrupoCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabGrupoAlterar.php";

# Redireciona para a página de excluir #
if( $Botao == "Excluir" ){
		$Url = "TabGrupoExcluir.php?GrupoCodigo=$GrupoCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}elseif( $Botao == "Voltar" ){
	  header("location: TabGrupoSelecionar.php");
	  exit();
}else{
		# Critica dos Campos #
		if( $Critica == 1 ) {
			  $Mens     = 0;
		    $Mensagem = "Informe: ";
		    if( $GrupoDescricao == "" ) {
			      $Critica   = 1;
		        $LerTabela = 0;
				 	  $Mens      = 1;
				 	  $Tipo      = 2;
				    $Mensagem .= "<a href=\"javascript:document.Grupo.GrupoDescricao.focus();\" class=\"titulo2\">Grupo</a>";
		    }
				if( $Email != 0 and !strchr($Email, "@")){
		    		if ($Mens == 1){$Mensagem.=", ";}
		    		$Mens      = 1;
		    		$Tipo      = 2;
    				$Mensagem .= "<a href=\"javascript:document.Grupo.Email.focus();\" class=\"titulo2\">E-Mail Válido</a>";
				}
		    if ($Fone == "") {
            if ($Mens == 1){$Mensagem.=", ";}
			      $Critica   = 1;
		        $LerTabela = 0;
		    		$Mens      = 1;
		    		$Tipo      = 2;
				    $Mensagem .= "<a href=\"javascript:document.Grupo.Fone.focus();\" class=\"titulo2\">Telefone</a>";
		    }
		    if( $Mens == 0 ){
						# Verifica se o Grupo/Órgão Licitante possui Licitações cadastradas #
						for( $P = 0; $P < count($OrgaoLicitanteCodigo); $P++ ){
								$OrgaosSelecionados[$P] = $OrgaoLicitanteCodigo[$P];
						}

						$db     = Conexao();
						$sql    = "SELECT CORGLICODI FROM SFPC.TBGRUPOORGAO ";
						$sql   .= " WHERE CGREMPCODI = $GrupoCodigo AND CORGLICODI ";
						$sql   .= "    IN ( SELECT CORGLICODI FROM SFPC.TBLICITACAOPORTAL ";
						$sql   .= "          WHERE CGREMPCODI = $GrupoCodigo )";
						$result = $db->query($sql);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Dependencias = 0;
								while( $Linha = $result->fetchRow() ){
										if( ! in_array($Linha[0],$OrgaosSelecionados) ){
												$Dependencias = $Dependencias + 1;
										}
								}
								if( $Dependencias > 0 ){
										if ($Mens == 1){$Mensagem.=", ";}
										$Critica   = 1;
							      $LerTabela = 0;
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem  = "Órgão Licitante possui Licitações Dependentes";
								}

								# Verifica a Duplicidade de Grupo #
								$sql    = "SELECT COUNT(CGREMPCODI) FROM SFPC.TBGRUPOEMPRESA ";
								$sql   .= " WHERE RTRIM(LTRIM(EGREMPDESC)) = '$GrupoDescricao' ";
								$sql   .= "   AND CGREMPCODI <> $GrupoCodigo ";
						 		$result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
								    $Linha = $result->fetchRow();
								    $Qtd   = $Linha[0];
						    		if( $Qtd > 0 ) {
											$Critica   = 1;
						    			$LerTabela = 0;
								    	$Mens      = 1;
								    	$Tipo      = 2;
											$Mensagem  = "<a href=\"javascript:document.Grupo.GrupoDescricao.focus();\" class=\"titulo2\"> Grupo Já Cadastrado</a>";
						    		}
										if( $Mens == 0 ){
												# Atualiza Grupo #
												$Data   = date("Y-m-d H:i:s");
												$db->query("BEGIN TRANSACTION");
												$sql    = "UPDATE SFPC.TBGRUPOEMPRESA ";
												$sql   .= "   SET EGREMPDESC = '$GrupoDescricao', EGREMPMAIL = '$Email', ";
												$sql   .= "       EGREMPENDW = '$WWW', AGREMPFONE = '$Fone', ";
												$sql   .= "       TGREMPULAT = '$Data' ";
												$sql   .= " WHERE CGREMPCODI = $GrupoCodigo";
												$result = $db->query($sql);
												if( PEAR::isError($result) ){
														$db->query("ROLLBACK");
												    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$sql    = "SELECT CORGLICODI FROM SFPC.TBGRUPOORGAO WHERE CGREMPCODI = $GrupoCodigo";
														$result = $db->query($sql);
														if( PEAR::isError($result) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$c = 0;
														    while( $Linha = $result->fetchRow() ){
																		$OrgaosCadastrados[$c] = $Linha[0];
																		$c++;
																}
														}
														for( $P = 0; $P < count($OrgaosSelecionados); $P++ ){
																$OrgaoCodigo = $OrgaosSelecionados[$P];
																if( ! in_array($OrgaoCodigo,$OrgaosCadastrados) ){
																		$Data   = date("Y-m-d H:i:s");

																		# Insere os órgãos licitantes marcados #
																		$sql    = "INSERT INTO SFPC.TBGRUPOORGAO ( ";
													    			$sql   .= "CGREMPCODI, CORGLICODI, TGRUORULAT ";
													    			$sql   .= ") VALUES ( ";
													    			$sql   .= "$GrupoCodigo, $OrgaoCodigo, '$Data' )";
																		$result = $db->query($sql);
																		if( PEAR::isError($result) ){
																				$db->query("ROLLBACK");
																		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}
																}
														}

														for( $P = 0; $P < count($OrgaosCadastrados); $P++ ){
																$OrgaoCodigo = $OrgaosCadastrados[$P];
																if( ! in_array($OrgaoCodigo, $OrgaosSelecionados) ){
																		# Deleta todos os órgãos cadastrados do grupo selecionado #
																		$sql    = "DELETE FROM SFPC.TBGRUPOORGAO ";
																		$sql   .= " WHERE CGREMPCODI = $GrupoCodigo AND CORGLICODI = $OrgaoCodigo";
																		$result = $db->query($sql);
																		if( PEAR::isError($result) ){
																				$db->query("ROLLBACK");
																		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}
																}
														}
												}
								        $db->query("COMMIT");
								        $db->query("EN TRANSACTION");
												$db->disconnect();

								        # Envia mensagem para página selecionar #
								        $Mensagem = urlencode("Grupo Alterado com Sucesso");
								        $Url = "TabGrupoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
												if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								        header("location: ".$Url);
								        exit();
										}
								}
						}
						$db->disconnect();
				}
		}
}
if( $Critica == 0 ){
		# Carrega os dados do grupo selecionado #
		$db     = Conexao();
		$sql    = "SELECT EGREMPDESC, EGREMPMAIL, EGREMPENDW, AGREMPFONE, CGREMPCODI ";
		$sql   .= "FROM SFPC.TBGRUPOEMPRESA WHERE CGREMPCODI = $GrupoCodigo";
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

				# Carrega os órgãos licitantes do grupo selecionado #
				$sql    = "SELECT CORGLICODI FROM SFPC.TBGRUPOORGAO WHERE CGREMPCODI = $GrupoCodigo";
				$result = $db->query($sql);
				if (PEAR::isError($result)) {
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
				    while( $Linha = $result->fetchRow() ){
								$OrgaoLicitanteCodigo[] .= $Linha[0];
						}
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
<form action="TabGrupoAlterar.php" method="post" name="Grupo">
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
	        	MANTER - GRUPO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar o Grupo, preencha os dados abaixo, selecione o(s) órgão(s) licitante(s), use (CTRL) + clique no botão esquerdo do mouse para selecionar mais de um órgão licitante.  e clique no botão "Alterar". Para apagar o Grupo clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Grupo*</td>
               	<td class="textonormal">
               		<input type="text" name="GrupoDescricao" size="45" maxlength="60" value="<?php echo $GrupoDescricao; ?>" class="textonormal">
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="GrupoCodigo" value="<?php echo $GrupoCodigo; ?>">
                </td>
              </tr>
             	<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7">E-mail</td>
      	    		<td class="textonormal">
      	    			<input type="text" name="Email" value="<?php echo $Email; ?>" size="45" maxlength="60" class="textonormal">
      	    		</td>
        	  	</tr>
        			<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7">Home Page</td>
      	    		<td class="textonormal">
      	    			<input type="text" name="WWW" value="<?php echo $WWW; ?>" size="45" maxlength="60" class="textonormal">
      	    		</td>
        	  	</tr>
							<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7">Telefone*</td>
      	    		<td class="textonormal">
      	    			<input type="text" name="Fone" value="<?php echo $Fone; ?>" size="25" maxlength="25" class="textonormal">
      	    		</td>
        	  	</tr>
        	   	<tr>
        	   		<td class="textonormal" bgcolor="#DCEDF7">Orgãos Licitantes</td>
        	   		<td class="normal">
									<select name="OrgaoLicitanteCodigo[]" multiple size="8" class="textonormal">
										<?php
										$db     = Conexao();
										$sql    = "SELECT CORGLICODI, EORGLIDESC FROM SFPC.TBORGAOLICITANTE ORDER BY EORGLIDESC";
										$result = $db->query($sql);
										if (PEAR::isError($result)) {
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
														if( FindArray($Linha[0],$OrgaoLicitanteCodigo) ){
																echo "<option value=\"$Linha[0]\" selected>$Linha[1]</option>\n";
														}else{
																echo "<option value=\"$Linha[0]\">$Linha[1]</option>\n";
														}
												}
										}
										$db->disconnect();
										?>
									</select>
								</td>
							</tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
            <input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
						<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
            <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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
<script language="javascript" type="">
<!--
document.Grupo.GrupoDescricao.focus();
//-->
</script>
