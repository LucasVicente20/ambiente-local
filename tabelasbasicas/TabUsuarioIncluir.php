<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabUsuarioIncluir.php
# Autor:    Rossana Lira
# Data:     08/04/03
# Objetivo: Programa de Inclusão de Usuario
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Everton Lino
# Data:     06/04/2010 	- Inclusão do campo CPF.
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
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
	carregarVariavel("Critica", "POST");
	carregarVariavel("CPF", "POST");
	carregarVariavel("Login", "POST");
	carregarVariavel("Nome", "POST");
	carregarVariavel("Email", "POST");
	carregarVariavel("Fone", "POST");
	carregarVariavel("GrupoCodigo", "POST");
	carregarVariavel("PerfilCodigo", "POST");
	
	$Login        = $Login;
	$Nome         = $Nome;
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabUsuarioIncluir.php";

# Critica dos Campos #
if($Critica == 1) {
	if($CPF == "") {
		adicionarMensagem("<a href=\"javascript:document.Usuario.CPF.focus();\" class=\"titulo2\">CPF</a>", TIPO_MENSAGEM_ERRO);
    } else {
    	# Chama a função para validar CPF #
		if(!valida_CPF($CPF)) {
			adicionarMensagem("<a href=\"javascript:document.Usuario.CPF.focus();\" class=\"titulo2\">CPF Válido</a>", TIPO_MENSAGEM_ERRO);
  		}
    }
	
	if($Login == "") {
		adicionarMensagem("<a href=\"javascript:document.Usuario.Login.focus();\" class=\"titulo2\">Login</a>", TIPO_MENSAGEM_ERRO);
    }
	
	if($Nome == "") {
		adicionarMensagem("<a href=\"javascript:document.Usuario.Nome.focus();\" class=\"titulo2\">Nome</a>", TIPO_MENSAGEM_ERRO);
	}
	
	if($Email == "") {
		adicionarMensagem("<a href=\"javascript:document.Usuario.Email.focus();\" class=\"titulo2\">E-Mail Válido</a>", TIPO_MENSAGEM_ERRO);
	} // else if (strchr($Email, "@")) {
	// 	adicionarMensagem("<a href=\"javascript:document.Usuario.Email.focus();\" class=\"titulo2\">Campo de E-Mail deve conter apenas o nome do usuário (do email) pois já será incluso automaticamente '@recife.pe.gov.br'</a>", TIPO_MENSAGEM_ERRO);
	// }
	
	if($Fone == "") {
		adicionarMensagem("<a href=\"javascript:document.Usuario.Fone.focus();\" class=\"titulo2\">Telefone</a>", TIPO_MENSAGEM_ERRO);
	}
	
	if($GrupoCodigo == "") {
		adicionarMensagem("<a href=\"javascript:document.Usuario.GrupoCodigo.focus();\" class=\"titulo2\">Grupo</a>", TIPO_MENSAGEM_ERRO);
	}
	
	if($PerfilCodigo == "") {
		adicionarMensagem("<a href=\"javascript:document.Usuario.PerfilCodigo.focus();\" class=\"titulo2\">Perfil</a>", TIPO_MENSAGEM_ERRO);
	}
	
	if($Mens == 0) {
	  	# Verifica a Duplicidade de CPF #
		$db     = Conexao();
		
		$sql    = "SELECT COUNT(CUSUPOCODI) FROM SFPC.TBUSUARIOPORTAL WHERE AUSUPOCCPF = '$CPF' ";
			 
		$result = $db->query($sql);
		
		if(PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
		    $Linha = $result->fetchRow();
			$Qtd   = $Linha[0];
			
			if($Qtd > 0) {
				adicionarMensagem("<a href=\"javascript:document.Usuario.CPF.focus();\" class=\"titulo2\">CPF Já Cadastrado</a>", TIPO_MENSAGEM_ERRO);
			} else {
		  		# Verifica a Duplicidade de Usuário #
				$db     = Conexao();
				   
				$sql    = "SELECT COUNT(CUSUPOCODI) FROM SFPC.TBUSUARIOPORTAL WHERE RTRIM(LTRIM(EUSUPOLOGI)) = '$Login' ";
				 
				$result = $db->query($sql);
				
				if(PEAR::isError($result)) {
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
		    		$Linha = $result->fetchRow();
				    $Qtd   = $Linha[0];
					
					if($Qtd > 0) {
						adicionarMensagem("<a href=\"javascript:document.Usuario.Login.focus();\" class=\"titulo2\">Login Já Cadastrado</a>", TIPO_MENSAGEM_ERRO);
					} else {
				  		# Verifica a Duplicidade de Nome de Usuário #
						$db     = Conexao();
						   
						$sql    = "SELECT COUNT(CUSUPOCODI) FROM SFPC.TBUSUARIOPORTAL WHERE RTRIM(LTRIM(EUSUPORESP)) = '$Nome' ";
						 
						$result = $db->query($sql);
						
						if(PEAR::isError($result)) {
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						} else {
					    	$Linha = $result->fetchRow();
							$Qtd   = $Linha[0];
							
							if($Qtd > 0) {
								adicionarMensagem("<a href=\"javascript:document.Usuario.Login.focus();\" class=\"titulo2\">Nome já Cadastrado</a>", TIPO_MENSAGEM_ERRO);
							} else {
								# Recupera o último usuário e incrementa mais um #
								$sql    = "SELECT MAX(CUSUPOCODI) FROM SFPC.TBUSUARIOPORTAL";
								
								$result = $db->query($sql);
								
								if (PEAR::isError($result)) {
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								} else {
								    $Linha      = $result->fetchRow();
									$Codigo     = $Linha[0] + 1;
									$Senha      = CriaSenha();
									$SenhaCript = hash('sha512', $Senha);
									$Data       = date("Y-m-d H:i:s");
									$Email      = $Email;
									// $Email      = $Email . "@recife.pe.gov.br";

								# Insere Usuario #
								$Nome = strtoupper($Nome);
								$db->query("BEGIN TRANSACTION");
								
								$sql    = "INSERT INTO SFPC.TBUSUARIOPORTAL ( ";
								$sql   .= "CGREMPCODI, CUSUPOCODI, AUSUPOCCPF,EUSUPOLOGI, EUSUPOSENH, ";
								$sql   .= "EUSUPORESP, EUSUPOMAIL, AUSUPOFONE, TUSUPOULAT, EUSUPOSEN2 ";
								$sql   .= ") VALUES ( ";
								$sql   .= "$GrupoCodigo, $Codigo,'$CPF' ,'$Login', '$SenhaCript', ";
								$sql   .= "'$Nome', '$Email', '$Fone', '$Data', '$SenhaCript')";
								
								$result = $db->query($sql);
								
								if(PEAR::isError($result)) {
									$db->query("ROLLBACK");
									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								} else {
									if($PerfilCodigo != "" ) {
										# Grava o perfil marcado na tabela de UsuarioPerfil #
										$sql    = "INSERT INTO SFPC.TBUSUARIOPERFIL ( ";
										$sql   .= "CGREMPCODI, CUSUPOCODI, CPERFICODI, TUSUPEULAT ";
										$sql   .= ") VALUES ( ";
										$sql   .= "$GrupoCodigo, $Codigo, $PerfilCodigo, '$Data')";
										
										$result = $db->query($sql);
										
										if( PEAR::isError($result) ){
											$db->query("ROLLBACK");
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										} else {
											$db->query("COMMIT");
											$db->query("END TRANSACTION");

											# Envia a senha para o e-mail do usuário #
											EnviaEmail("$Email","Senha temporária do usuário do Portal de Compras","Login: $Login e Senha: $Senha ","from: portalcompras@recife.pe.gov.br");
											adicionarMensagem("Usuário incluído com sucesso. A senha temporária foi enviada para o e-mail do usuário", TIPO_MENSAGEM_ATENCAO);
											
											$Login        = "";
											$Nome         = "";
											$Email        = "";
											$WWW          = "";
											$Fone         = "";
											$GrupoCodigo  = "";
											$PerfilCodigo = "";
											$CPF = "";
										}
									}
								}
								}
							}
						}
					}
				}
			}
	   		$db->disconnect();
		}
	}
}

# GERANDO O HTML- INÍCIO
$template = new TemplatePaginaPadrao("templates/TabUsuarioIncluir.template.html", "Tabelas > Usuário > Incluir");

$template->VALOR_LOGIN = $Login;
$template->VALOR_USUARIO = $Nome;
$template->VALOR_CPF = $CPF;
$template->VALOR_EMAIL = $Email;
$template->VALOR_TELEFONE = $Fone;

# Pega a descrição do Perfil do usuário logado #
if($_SESSION['_cperficodi_'] != 2 and $_SESSION['_cperficodi_'] != 0) {
	$db  = Conexao();
	
	$sqlusuario  = "SELECT CPERFICODI, EPERFIDESC FROM SFPC.TBPERFIL ";
	$sqlusuario .= "WHERE CPERFICODI = ".$_SESSION['_cperficodi_']." ";
	
	$resultUsuario = $db->query($sqlusuario);
	
	if(PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		$PerfilUsuario     = $resultUsuario->fetchRow();
		$PerfilUsuarioDesc = $PerfilUsuario[1];
	}
}

$db  = Conexao();
if($_SESSION['_cgrempcodi_'] != 0) {
	$sql  = "SELECT CGREMPCODI,EGREMPDESC FROM SFPC.TBGRUPOEMPRESA ";
	$sql .= "WHERE CGREMPCODI <> 0 ";
	
	if (($PerfilUsuarioDesc == 'GESTOR ALMOXARIFADO') and ($PerfilUsuarioDesc != 'SUPORTE AO ALMOXARIFADO')) {
		$sql .= "AND CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
	}
} else {
	$sql = "SELECT CGREMPCODI,EGREMPDESC FROM SFPC.TBGRUPOEMPRESA ";
}
	
$sql .= "ORDER BY EGREMPDESC";

$result = $db->query($sql);

if(PEAR::isError($result)) {
	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
} else {
	while($Linha = $result->fetchRow()) {
		
		$template->block("BLOCO_ITEM_GRUPO");
		$template->VALOR_ITEM_GRUPO = $Linha[0];
		$template->ITEM_GRUPO = $Linha[1];
			
		if($Linha[0] == $GrupoCodigo ){
			$template->ITEM_GRUPO_SELECIONADO = "selected";
		} else {
			$template->ITEM_GRUPO_SELECIONADO = "";
		}
	}
}

if($_SESSION['_cperficodi_'] != 2 and $_SESSION['_cperficodi_'] != 0) {
	
	$sql  = "SELECT CPERFICODI, EPERFIDESC FROM SFPC.TBPERFIL ";
	$sql .= "WHERE CPERFICODI <> 2 AND CPERFICODI <> 0 AND FPERFISITU = 'A' ";
	
	if ($PerfilUsuarioDesc == 'GESTOR ALMOXARIFADO') {
		$sql .= "AND (EPERFIDESC = 'ALMOXARIFE' OR EPERFIDESC = 'REQUISITANTE ALMOXARIFADO') ";
	}
} else {
	$sql = "SELECT CPERFICODI, EPERFIDESC FROM SFPC.TBPERFIL WHERE FPERFISITU = 'A'";
}
	$sql .= " ORDER BY EPERFIDESC";
	
	$result = $db->query($sql);
	
	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		while($Linha = $result->fetchRow()) {
			
			$template->block("BLOCO_ITEM_PERFIL");
			$template->VALOR_ITEM_PERFIL = $Linha[0];
			$template->ITEM_PERFIL = $Linha[1];
					
			if($Linha[0] == $PerfilCodigo ){
				$template->ITEM_PERFIL_SELECIONADO = "selected";
			} else {
				$template->ITEM_PERFIL_SELECIONADO = "";
			}
		}
	}
	$db->disconnect();
		

$template->show();
# GERANDO O HTML- FIM

?>

