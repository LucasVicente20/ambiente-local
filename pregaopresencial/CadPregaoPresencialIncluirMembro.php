<?php
# -------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: CadPregaoPresencialIncluirMembro.php
# Autor:    Hélio Miranda
# Data:     29/07/2016
# Objetivo: Programa de Manutenção de Usuário
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		17/04/2018
# Objetivo: Tarefa Redmine 192112
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -------------------------------------------------------------------------

header("Content-Type: text/html; charset=UTF-8",true);


# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabUsuarioAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$UsuarioCodigo = $_POST['UsuarioCodigo'];
		$Critica       = $_POST['Critica'];
}else{
		$Critica       = $_GET['Critica'];
		$Mensagem      = urldecode($_GET['Mensagem']);
		$Mens          = $_GET['Mens'];
		$Tipo          = $_GET['Tipo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadPregaoPresencialIncluirMembro.php";
$ComissaoCodigo	= $_SESSION['ComissaoCodigo'];
if($Critica == 1){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		
    if($UsuarioCodigo == "") {
	    $Mens      = 1;
	    $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Usuario.UsuarioCodigo.focus();\" class=\"titulo2\">Usuário</a>";
    }
	else{
		$PregaoCod		= $_SESSION['PregaoCod'];
		
		$db     = Conexao();
		
		//Verificando se existe licitações ligadas a o processo , para ver qual programa devo chamar
		$sqlSolicitacoes = " SELECT cpregmsequ
									FROM sfpc.tbpregaopresencialmembro pm 
									WHERE 		pm.cpregasequ  = $PregaoCod 
											AND pm.cusupocodi  = $UsuarioCodigo "; 
			
			
		
		$result = $db->query($sqlSolicitacoes);
		
		if( PEAR::isError($resultSoli) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
		}
		
		$Linha = $result->fetchRow();
		
		$intQuantidade = 0;
		
		$intQuantidade = $result->numRows();		
		
		
		if($intQuantidade == 0){
			$sql = "SELECT MAX(cpregmsequ) FROM sfpc.tbpregaopresencialmembro";
			$res = $db->query($sql);
			
			if (PEAR::isError($res)) {
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}else{
					$Linha  = $res->fetchRow();
					$Codigo = $Linha[0] + 1;
			}			
		
		
			# Insere Membro de Comissão #
			$sql  = "INSERT INTO sfpc.tbpregaopresencialmembro( ";
			$sql .= "cpregmsequ, cpregasequ, cusupocodi, epregmtipo, ";
			$sql .= "dpregmcada, ";
			$sql .= "tpregmulat ";
			$sql .= " ) VALUES ( ";
			$sql .= "$Codigo, $PregaoCod, $UsuarioCodigo, 'M',";
			$sql .= "'".date("Y-m-d")."', ";
			$sql .= "'".date("Y-m-d H:i:s")."' )";
			
			$res  = $db->query($sql);
			
			
			if( PEAR::isError($result) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}  
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 1;
			$_SESSION['Mensagem'] .= "- Membro de Comissão incluído com sucesso!";			
		}
		else
		{
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- O Membro de Comissão já está vinculado ao Pregão Presencial selecionado!";	
		}
		$db->disconnect();		
		
		echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";
		echo "<script>self.close()</script>";
    }

}

// print_r($ComissaoCodigo);
// die;

?>
<html>
<head>
<title>Portal de Compras - Incluir Membro</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script language="javascript" type="">
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">

</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadPregaoPresencialIncluirMembro.php" method="post" name="Usuario">
<table cellpadding="3" border="0" summary="">
	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td class="textonormal">
			<table width="200%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	INCLUIR MEMBRO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Selecione o usuário e clique no botão "Incluir membro".
             </p>
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
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Usuário: </td>
                <td class="textonormal" bgcolor="#FFFFFF">
                  <select name="UsuarioCodigo" class="textonormal">
                  	<option value="">Selecione um usuário...</option>
                  	<?
                  	# Mostra os usuários cadastrados #
                		$db   = Conexao();
                		$sql  = "SELECT	UC.CUSUPOCODI,
										UP.EUSUPORESP,
										UC.CCOMLICODI,
										CL.ECOMLIDESC
								FROM	SFPC.TBUSUARIOCOMIS UC
										LEFT JOIN SFPC.TBUSUARIOPORTAL UP
											ON UC.CUSUPOCODI = UP.CUSUPOCODI
										LEFT JOIN SFPC.TBCOMISSAOLICITACAO CL
											ON UC.CCOMLICODI = CL.CCOMLICODI
								WHERE	UC.CCOMLICODI = $ComissaoCodigo
								ORDER BY 	UP.EUSUPORESP ASC,
											CL.ECOMLIDESC ASC";
						
						
	              		$result = $db->query($sql);
										
						if(PEAR::isError($result)){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}
						else{
			              	while($Linha = $result->fetchRow()){
								echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
							}
						}
						
                		$db->disconnect();
      	            ?>
                  </select>
                  <input type="hidden" name="Critica" value="1">
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="submit" value="Incluir Membro" class="botao">
			<input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
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
</script>
