<?php
#-------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: TabUsuarioSelecionar.php
# Autor:    Rossana Lira
# Data:     08/04/2003
# Alterado: Álvaro Faria
# Data:     26/06/2006
# Alterado: Carlos Abreu
# Data:     30/06/2006
# Objetivo: Programa de Manutenção de Usuário
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

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
$ErroPrograma = "TabUsuarioSelecionar.php";

if( $Critica == 1 ){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
    if( $UsuarioCodigo == "" ) {
	      $Mens      = 1;
	      $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript: document.Usuario.UsuarioCodigo.focus();\" class=\"titulo2\">Usuário</a>";
    }else{
    		$Url = "TabUsuarioAlterar.php?UsuarioCodigo=$UsuarioCodigo";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	      header("location: ".$Url);
	      exit();
    }
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabUsuarioSelecionar.php" method="post" name="Usuario">
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
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	MANTER - USUÁRIO
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para atualizar/excluir um Usuário já cadastrado, selecione o Usuário e clique no botão "Selecionar".
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
                <td class="textonormal" bgcolor="#DCEDF7">Usuário </td>
                <td class="textonormal" bgcolor="#FFFFFF">
                  <select name="UsuarioCodigo" class="textonormal">
                  	<option value="">Selecione um Usuário...</option>
                  	<?php
                  	# Mostra os usuários cadastrados #
                		$db   = Conexao();
                		$sql  = "SELECT A.CUSUPOCODI, A.EUSUPOLOGI, B.CGREMPCODI, B.EGREMPDESC, A.EUSUPORESP ";
	                  $sql .= "  FROM SFPC.TBGRUPOEMPRESA B, SFPC.TBUSUARIOPORTAL A ";
	                  $sql .= "  LEFT OUTER JOIN SFPC.TBUSUARIOPERFIL C ON (A.CUSUPOCODI = C.CUSUPOCODI) ";
	                  $sql .= "  LEFT OUTER JOIN SFPC.TBPERFIL D ON (C.CPERFICODI = D.CPERFICODI) ";
	                  $sql .= " WHERE A.CGREMPCODI = B.CGREMPCODI ";
	              		if( $_SESSION['_cgrempcodi_'] != 0){
	  	              		$sql .= "AND B.CGREMPCODI <> 0 ";
	  	              		if ($PerfilUsuarioDesc == 'GESTOR ALMOXARIFADO'){
	                  				$sql .= " AND A.CGREMPCODI = ".$_SESSION['_cgrempcodi_']." ";
	                  				$sql .= "AND (D.EPERFIDESC = 'ALMOXARIFE' OR D.EPERFIDESC = 'REQUISITANTE ALMOXARIFADO') ";
	                  		}
	                  }
	              		$sql   .= " ORDER BY  A.EUSUPORESP, B.EGREMPDESC ";
	              		$result = $db->query($sql);
										if( PEAR::isError($result) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
			              		while( $Linha = $result->fetchRow() ){
														$DescGrupo = substr($Linha[3],0,40);
												    echo"<option value=\"$Linha[0]\">$Linha[4] - $DescGrupo</option>\n";
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
          	<input type="submit" value="Selecionar" class="botao">
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
document.Usuario.UsuarioCodigo.focus();
//-->
</script>
