<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabUsuarioAlterar.php
# Objetivo: Programa de Alteração da Usuário
# Autor:    Rossana Lira
# Data:     08/04/2003
# Alterado: Álvaro Faria
# Data:     26/06/2006
# Alterado: Carlos Abreu
# Data:     13/06/2007 - Inclusão de Botão "Desativar"
# Alterado: Carlos Abreu
# Data:     26/06/2007 - Acrescentado mais uma Critica (tbprematerial)
# Alterado: Rodrigo Melo
# Data:     22/09/2009 - Alterando o nome das tabelas SFPC.TBPREMATERIAL e SFPC.TBPREMATERIALTIPOSITUACAO para SFPC.TBPREMATERIALSERVICO e BPREMATERIALSERVICOTIPOSITUACAO, respectivamente (CR 2749).
# Alterado: Everton Lino
# Data:     07/04/2010 	- Inclusão do campo CPF.
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: João Madson
# Data:     16/11/2020 	
# CR #240763
#-------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 13/09/2021
# Objetivo: CR #252575
#---------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabUsuarioDesativar.php' );
AddMenuAcesso( '/tabelasbasicas/TabUsuarioExcluir.php' );
AddMenuAcesso( '/tabelasbasicas/TabUsuarioSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao         = $_POST['Botao'];
		$Critica       = $_POST['Critica'];
		$UsuarioCodigo = $_POST['UsuarioCodigo'];
		$GrupoAnt      = $_POST['GrupoAnt'];
		$CPF           = $_POST['CPF'];
		$Login         = trim($_POST['Login']);
		$Nome          = strtoupper2(trim($_POST['Nome']));
		$Email         = trim($_POST['Email']);
		$Fone          = trim($_POST['Fone']);
		$GrupoCodigo   = $_POST['GrupoCodigo'];
		$PerfilCodigo  = $_POST['PerfilCodigo'];
}else{
		$UsuarioCodigo = $_GET['UsuarioCodigo'];
		$Mens          = $_GET['Mens'];
		$Tipo          = $_GET['Tipo'];
		$Mensagem      = urldecode($_GET['Mensagem']);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabUsuarioAlterar.php";

if( $Botao == "Excluir" ){
	$Url = "TabUsuarioExcluir.php?UsuarioCodigo=$UsuarioCodigo";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	header("location: ".$Url);
	exit();
} elseif ( $Botao == "Desativar" ){
	$Url = "TabUsuarioDesativar.php?UsuarioCodigo=$UsuarioCodigo";
	if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	header("location: ".$Url);
	exit();
} elseif ( $Botao == "Voltar" ){
	header("location: TabUsuarioSelecionar.php");
	exit();
} elseif ( $Botao == "Alterar" ){
	# Critica dos Campos #
	if( $Critica == 1 ) {
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $CPF == "" ) {
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.Usuario.CPF.focus();\" class=\"titulo2\">CPF</a>";
		}		else{
    	# Chama a função para validar CPF #
						if( !valida_CPF($CPF)){
							$Mens      = 1;
		          $Tipo      = 2;
  		       	$Mensagem .= "<a href=\"javascript:document.Usuario.CPF.focus();\" class=\"titulo2\">CPF Válido</a>";
		  				}
    }
		if( $Login == "" ) {
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.Usuario.Login.focus();\" class=\"titulo2\">Login</a>";
		}
		if( $Nome == "" ) {
			if ($Mens == 1){$Mensagem.=", ";}
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.Usuario.Nome.focus();\" class=\"titulo2\">Nome</a>";
		}
		if( $Email == "" || !strchr($Email, "@")){
			if ($Mens == 1){$Mensagem.=", ";}
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.Usuario.Email.focus();\" class=\"titulo2\">E-Mail Válido</a>";
		}
		// else if(substr_compare($Email, "@recife.pe.gov.br", -17, 17)){
		// 	if ($Mens == 1){$Mensagem.=", ";}
		// 	$Mens      = 1;
		// 	$Tipo      = 2;
		// 	$Mensagem .= "<a href=\"javascript:document.Usuario.Email.focus();\" class=\"titulo2\">E-Mail deve conter '@recife.pe.gov.br'</a>";

		// }
		if( $Fone == "" ){
			if ($Mens == 1){$Mensagem.=", ";}
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.Usuario.Fone.focus();\" class=\"titulo2\">Telefone</a>";
		}
		if ( $GrupoCodigo == "" ) {
			if ($Mens == 1){$Mensagem.=", ";}
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.Usuario.GrupoCodigo.focus();\" class=\"titulo2\">Grupo</a>";
		}
		if( $Mens == 0 ){
			# Verifica a Duplicidade de Usuário #
			$db   = Conexao();
			$sql  = "SELECT COUNT(CUSUPOCODI) ";
			$sql .= "  FROM SFPC.TBUSUARIOPORTAL ";
			$sql .= " WHERE RTRIM(LTRIM(EUSUPOLOGI)) = '$Login' ";
			$sql .= "   AND CUSUPOCODI <> $UsuarioCodigo ";
			$result = $db->query($sql);
			if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}else{
				$Linha = $result->fetchRow();
				$Qtd   = $Linha[0];
				if( $Qtd > 0 ) {
					$Mens     = 1;
					$Tipo     = 2;
					$Mensagem = "<a href=\"javascript:document.Usuario.Login.focus();\" class=\"titulo2\">Login Já Cadastrado</a>";
				}else{

			# Verifica a Duplicidade de CPF #
			$db   = Conexao();
			$sql  = "SELECT COUNT(CUSUPOCODI) ";
			$sql .= "  FROM SFPC.TBUSUARIOPORTAL ";
			$sql .= " WHERE AUSUPOCCPF = '$CPF' ";
			$sql .= "   AND CUSUPOCODI <> $UsuarioCodigo ";
			$result = $db->query($sql);
			if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}else {
				$Linha = $result->fetchRow();
				$Qtd   = $Linha[0];
				if( $Qtd > 0 ) {
					$Mens     = 1;
					$Tipo     = 2;
					$Mensagem = "<a href=\"javascript:document.Usuario.CPF.focus();\" class=\"titulo2\">CPF Já Cadastrado</a>";
				}else{
                    /*
					$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBUSUARIOCENTROCUSTO WHERE CUSUPOCODI = $UsuarioCodigo";
					$result = $db->query($sql);
					if( PEAR::isError($result) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}else{
						$Linha          = $result->fetchRow();
						$QtdUsuCC = $Linha[0];
						if( ($QtdUsuCC > 0 ) and ($GrupoAnt <> $GrupoCodigo)){
							$Mens      = 1;
							$Tipo      = 2;
							$Mensagem .= "Alteração do Grupo Cancelada!<br>Usuário Relacionado com ($QtdUsuCC) Centro(s) de Custo";
						}else{
							$sql = "SELECT COUNT(*) AS Qtd FROM SFPC.TBPREMATERIALSERVICO WHERE CUSUPOCODI = $UsuarioCodigo";
							$result = $db->query($sql);
							if( PEAR::isError($result) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}else{
								$Linha          = $result->fetchRow();
								$QtdPreMaterial = $Linha[0];
								if( ($QtdPreMaterial > 0 ) and ($GrupoAnt <> $GrupoCodigo)){
									$Mens      = 1;
									$Tipo      = 2;
									$Mensagem .= "Alteração do Grupo Cancelada!<br>Usuário Relacionado com ($QtdPreMaterial) Pré-cadastro de Material(is)";
								}else{
									# Verifica se o usuário está relacionado com alguma Comissão #
									$sql    = "SELECT COUNT(*) AS Qtd FROM SFPC.TBUSUARIOCOMIS WHERE CUSUPOCODI = $UsuarioCodigo";
									$result = $db->query($sql);
									if( PEAR::isError($result) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
										$Linha       = $result->fetchRow();
										$QtdComissao = $Linha[0];
										if( ($QtdComissao > 0 ) and ($GrupoAnt <> $GrupoCodigo)){
											$Mens      = 1;
											$Tipo      = 2;
											$Mensagem .= "Alteração do Grupo Cancelada!<br>Usuário Relacionado com ($QtdComissao) Comissão(ões)";
										}else{
											# Verifica se o usuário está relacionado com alguma licitação
											$sql    = "SELECT COUNT(*) AS Qtd FROM SFPC.TBLICITACAOPORTAL WHERE CUSUPOCODI = $UsuarioCodigo";
											$result = $db->query($sql);
											if( PEAR::isError($result) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											}else{
												$Linha = $result->fetchRow();
												$QtdLicitacao = $Linha[0];
												if( ( $QtdLicitacao > 0 ) and ($GrupoAnt <> $GrupoCodigo)) {
													$Mens     = 1;
													$Tipo     = 2;
													$Mensagem = "Alteração do Grupo Cancelada!<br>Usuário Relacionado com ($QtdLicitacao) Licitação(ões)";
												}else{
													# Verifica se o usuário está relacionado com algum documento #
													$sql    = "SELECT COUNT(*) AS Qtd FROM SFPC.TBDOCUMENTOLICITACAO WHERE CUSUPOCODI = $UsuarioCodigo";
													$result = $db->query($sql);
													if( PEAR::isError($result) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
														$Linha = $result->fetchRow();
														$QtdDocumento = $Linha[0];
														if( ( $QtdDocumento > 0 ) and ($GrupoAnt <> $GrupoCodigo)) {
															$Mens      = 1;
															$Tipo      = 2;
															$Mensagem .= "Alteração do Grupo Cancelada!<br>Usuário Relacionado com ($QtdDocumento) Documento(s)";
														}else{
															# Verifica se o usuário está relacionado com alguma Fase de Licitação
															$sql    = "SELECT COUNT(*) AS Qtd FROM SFPC.TBFASELICITACAO WHERE CUSUPOCODI = $UsuarioCodigo";
															$result = $db->query($sql);
															if (PEAR::isError($result)) {
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																$Linha            = $result->fetchRow();
																$QtdFaseLicitacao = $Linha[0];
																if( ( $QtdFaseLicitacao > 0 ) and ($GrupoAnt <> $GrupoCodigo)) {
																	$Mens      = 1;
																	$Tipo      = 2;
																	$Mensagem .= "Alteração do Grupo Cancelada!<br>Usuário Relacionado com ($QtdFaseLicitacao) Fase(s) de Licitação";
																}else{
																	# Verifica se o usuário está relacionado com alguma Ata da Fase de Licitação
																	$sql    = "SELECT COUNT(*) AS Qtd FROM SFPC.TBATASFASE WHERE CUSUPOCODI = $UsuarioCodigo";
																	$result = $db->query($sql);
																	if( PEAR::isError($result) ){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	}else{
																		$Linha       = $result->fetchRow();
																		$QtdAtasFase = $Linha[0];
																		if( ( $QtdAtasFase > 0 ) and ($GrupoAnt <> $GrupoCodigo)) {
																			$Mens      = 1;
																			$Tipo      = 2;
																			$Mensagem .= "Alteração do Grupo Cancelada!<br>Usuário Relacionado com ($QtdAtasFase) Ata(s) da Fase de Licitação";
																		}else{
																			# Verifica se o usuário está relacionado com algum Resultado de Licitação
																			$sql    = "SELECT COUNT(*) AS Qtd FROM SFPC.TBRESULTADOLICITACAO WHERE CUSUPOCODI = $UsuarioCodigo";
																			$result = $db->query($sql);
																			if( PEAR::isError($result) ){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			}else{
																				$Linha        = $result->fetchRow();
																				$QtdResultado = $Linha[0];
																				if( ( $QtdResultado > 0 ) and ($GrupoAnt <> $GrupoCodigo)) {
																					$Mens      = 1;
																					$Tipo      = 2;
																					$Mensagem .= "Alteração do Grupo Cancelada!<br>Usuário Relacionado com ($QtdResultado) Resultado(s) de Licitação";
																				}else{
																				*/
																					# Deleta o perfil cadastrado do usuário selecionado #
																					$db->query("BEGIN TRANSACTION");
																					$sql    = "DELETE FROM SFPC.TBUSUARIOPERFIL WHERE CUSUPOCODI = $UsuarioCodigo";
																					$result = $db->query($sql);
																					if( PEAR::isError($result) ){
																						$RowBack = 1;
																						$db->query("ROLLBACK");
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																					}else{
																						# Atualiza Usuário #
																						$Data   = date("Y-m-d H:i:s");
																						$sql    = "UPDATE SFPC.TBUSUARIOPORTAL ";
																						$sql   .= "   SET AUSUPOCCPF = '$CPF' , ";
																						$sql   .= "       EUSUPOLOGI = '$Login', EUSUPORESP = '$Nome', ";
																						$sql   .= "       EUSUPOMAIL = '$Email', AUSUPOFONE = '$Fone', ";
																						$sql   .= "       CGREMPCODI = $GrupoCodigo, TUSUPOULAT = '$Data' ";
																						$sql   .= " WHERE CUSUPOCODI = $UsuarioCodigo";
																						//var_dump($sql); die;
																						$result = $db->query($sql);
																						if( PEAR::isError($result) ){
																							$RowBack = 1;
																							$db->query("ROLLBACK");
																							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																						}else{
																							# Grava o perfil selecionado na tabela de UsuarioPerfil #
																							$sql    = "INSERT INTO SFPC.TBUSUARIOPERFIL ( ";
																							$sql   .= "CGREMPCODI, CUSUPOCODI, CPERFICODI, TUSUPEULAT";
																							$sql   .= ") VALUES ( ";
																							$sql   .= "$GrupoCodigo, $UsuarioCodigo, $PerfilCodigo, '$Data')";

																							$result = $db->query($sql);
																							if( PEAR::isError($result) ){
																								$RowBack = 1;
																								$db->query("ROLLBACK");
																								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																							}
																							# Caso não tenha ocorrido nenhum erro, redireciona página selecionar com mensagem de sucesso #
																							if(!$RowBack){
																								$db->query("COMMIT");
																								$db->query("END TRANSACTION");
																								$db->disconnect();
																								# Envia mensagem para página selecionar #
																								$Mensagem = urlencode("Usuário Alterado com Sucesso");
																								$Url = "TabUsuarioSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
																								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																								header("location: ".$Url);
																								exit();
																							}
																						/*
																						}
																					}
																				}
																			}
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							} */
						}
					}
				}
			}
				$db->disconnect();
			}
			}
		}
	}
}

if( $Critica == 0 ){
		# Carrega os dados do usuário selecionado #
		$db     = Conexao();
		$sql    = "SELECT AUSUPOCCPF, EUSUPOLOGI, EUSUPORESP, EUSUPOMAIL, AUSUPOFONE, CGREMPCODI ";
		$sql   .= "FROM SFPC.TBUSUARIOPORTAL WHERE CUSUPOCODI = $UsuarioCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$CPF         = $Linha[0];
						$Login       = $Linha[1];
						$Nome        = $Linha[2];
						$Email       = $Linha[3];
						$Fone        = $Linha[4];
						$GrupoCodigo = $Linha[5];
						$GrupoAnt    = $GrupoCodigo;
				}
		}

		# Carrega o perfil do usuário selecionado #
		$sql    = "SELECT CPERFICODI FROM SFPC.TBUSUARIOPERFIL WHERE CUSUPOCODI = $UsuarioCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				while( $Linha = $result->fetchRow() ){
						$PerfilCodigo= $Linha[0];
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
	document.Usuario.Botao.value=valor;
	document.Usuario.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabUsuarioAlterar.php" method="post" name="Usuario">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Usuário > Manter
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
	       		MANTER - USUÁRIO
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
             Para atualizar o Usuário, preencha os dados abaixo e clique no botão "Alterar". Para apagar o Usuário clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Login*</td>
               	<td class="textonormal">
               		<input name="Login" size="10" maxlength="10" value="<?php echo $Login; ?>" class="textonormal">
	                <input type="hidden" name="Critica" value="1">
	                <input type="hidden" name="UsuarioCodigo" value="<?php echo $UsuarioCodigo?>">
	                <input type="hidden" name="GrupoAnt" value="<?php echo $GrupoAnt?>">
                </td>
              </tr>
							<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7">Nome do Usuário* </td>
      	    		<td class="textonormal">
      	    			<input type="text" text-transform="uppercase" name="Nome" value="<?php echo $Nome; ?>" size="45" maxlength="60" class="textonormal">
      	    		</td>
        	  	</tr>
        	  	<tr>
                <td class="textonormal" bgcolor="#DCEDF7">CPF*</td>
               	<td class="textonormal">
               		<input type="text" name="CPF" size="11" maxlength="11" value="<?php echo $CPF; ?>" class="textonormal">
               	</td>
             </tr>
             	<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7">E-mail* </td>
      	    		<td class="textonormal">
      	    			<input type="text" name="Email" value="<?php echo $Email; ?>" size="45" maxlength="60" class="textonormal" style="text-transform: none;">
      	    		</td>
        	  	</tr>
        			<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7">Telefone* </td>
      	    		<td class="textonormal">
      	    			<input type="text" name="Fone" value="<?php echo $Fone; ?>" size="25" maxlength="25" class="textonormal">
      	    		</td>
        	  	</tr>
        	  	<?php
							# Pega a descrição do Perfil do usuário logado #
							if( $_SESSION['_cperficodi_'] != 2 and $_SESSION['_cperficodi_'] != 0 ){
									$db  = Conexao();
									$sqlusuario = "SELECT CPERFICODI, EPERFIDESC FROM SFPC.TBPERFIL ";
									$sqlusuario .= "WHERE CPERFICODI = ".$_SESSION['_cperficodi_']." ";
									$resultUsuario = $db->query($sqlusuario);
									if( PEAR::isError($result) ){
									    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlusuario");
									}else{
                  		$PerfilUsuario = $resultUsuario->fetchRow();
                  		$PerfilUsuarioDesc = $PerfilUsuario[1];
									}
							}
							?>
        	   	<tr>
        	   		<td class="textonormal" bgcolor="#DCEDF7">Grupo* </td>
        	   		<td class="textonormal">
									<select name="GrupoCodigo" class="textonormal">
										<?php
										$db  = Conexao();
										if( $_SESSION['_cgrempcodi_'] != 0){
														$sql = "SELECT CGREMPCODI, EGREMPDESC FROM SFPC.TBGRUPOEMPRESA ";
	                  				$sql .= " WHERE CGREMPCODI <> 0 ";
	                  				if ($PerfilUsuarioDesc == 'GESTOR ALMOXARIFADO'){
	                  						$sql .= " AND CGREMPCODI = ".$_SESSION['_cgrempcodi_'];
	                  				}
			                  } else {
			                  		$sql = "SELECT CGREMPCODI, EGREMPDESC FROM SFPC.TBGRUPOEMPRESA ";
			                  		if ($PerfilUsuarioDesc == 'GESTOR ALMOXARIFADO'){
	                  						$sql .= " WHERE CGREMPCODI = ".$_SESSION['_cgrempcodi_'];
	                  				}
				            		}
			              $sql .= "ORDER BY EGREMPDESC";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
		                		while( $Linha = $result->fetchRow() ){
														if( $Linha[0] == $GrupoCodigo ) {
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
							<tr>
        	   		<td class="textonormal" bgcolor="#DCEDF7">Perfil* </td>
        	   		<td class="textonormal">
									<select name="PerfilCodigo" class="textonormal">
										<option value="">Selecione um Perfil...</option>
										<?php
										$db  = Conexao();
	                  if( $_SESSION['_cperficodi_'] != 2 and $_SESSION['_cperficodi_'] != 0 ){
												$sql = "SELECT CPERFICODI, EPERFIDESC FROM SFPC.TBPERFIL ";
                				$sql .= " WHERE CPERFICODI <> 2 AND FPERFISITU = 'A' ";
	                  		if ($PerfilUsuarioDesc == 'GESTOR ALMOXARIFADO'){
	                					$sql .= "AND (EPERFIDESC = 'ALMOXARIFE' OR EPERFIDESC = 'REQUISITANTE ALMOXARIFADO') ";
	                			}
	                  } else {
	                  		$sql = "SELECT CPERFICODI, EPERFIDESC FROM SFPC.TBPERFIL WHERE FPERFISITU = 'A'";
	                  }
	                  $sql .= " ORDER BY EPERFIDESC";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
		                		while( $Linha = $result->fetchRow() ){
		                  			if( $Linha[0] == $PerfilCodigo ){
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
          	<input type="button" value="Desativar" class="botao" onclick="javascript:enviar('Desativar');">
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
document.Usuario.Login.focus();
//-->
</script>
