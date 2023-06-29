<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabPerfilIncluir.php
# Autor:    Rossana Lira
# Data:     04/04/03
# Objetivo: Programa de Inclusão de Perfil
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     24/07/2018
# Objetivo: Tarefa Redmine 79809
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica         = $_POST['Critica'];
		$PerfilDescricao = strtoupper2(trim($_POST['PerfilDescricao']));
		$Situacao        = $_POST['Situacao'];
		$PerfCorp		 = $_POST['Corporativo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabPerfilIncluir.php";

# Critica dos Campos #
if( $Critica == 1 ) {
		$Mens     = 0;
		$Mensagem = "Informe: ";
	  if( $PerfilDescricao == "" ) {
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.Perfil.PerfilDescricao.focus();\" class=\"titulo2\">Perfil</a>";
    }
	  if( $Mens == 0 ) {
	  	  # Verifica a Duplicidade de Perfil #
				$db     = Conexao();
		   	$sql    = "SELECT COUNT(CPERFICODI) FROM SFPC.TBPERFIL WHERE RTRIM(LTRIM(EPERFIDESC))  = '$PerfilDescricao' ";
		 		$result = $db->query($sql);
				if( PEAR::isError($result) ){
				    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
		    		$Linha = $result->fetchRow();
				    $Qtd = $Linha[0];
		    		if( $Qtd > 0 ) {
					    	$Mens     = 1;
					    	$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.Perfil.PerfilDescricao.focus();\" class=\"titulo2\"> Perfil Já Cadastrado</a>";
						}else{
								# Recupera a última Perfil e incrementa mais um #
						    $sql    = "SELECT MAX(CPERFICODI) FROM SFPC.TBPERFIL";
						    $result = $db->query($sql);
								if( PEAR::isError($result) ){
								    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
				        		$Linha  = $result->fetchRow();
								    $Codigo = $Linha[0] + 1;

								    # Insere Perfil #
								    $Data   = date("Y-m-d H:i:s");
								    $sql    = "INSERT INTO SFPC.TBPERFIL (";
								    $sql   .= "CPERFICODI, EPERFIDESC, FPERFISITU, TPERFIULAT, FPERFICORP ";
								    $sql   .= ") VALUES ( ";
								    $sql   .= "$Codigo, '$PerfilDescricao', '$Situacao', '$Data', '$PerfCorp')";
								    $result = $db->query($sql);
										if (PEAR::isError($result)) {
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Mens            = 1;
												$Tipo            = 1;
												$Mensagem        = "Perfil Incluído com Sucesso";
												$PerfilDescricao = "";
										}
								}
						}
				}
		    $db->disconnect();
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
<form action="TabPerfilIncluir.php" method="post" name="Perfil">
<br><br><br><br><br>
<table cellpadding="3" border="0">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Perfil > Incluir
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					INCLUIR - PERFIL
		          	</td>
		        	</tr>
	  	      	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para incluir um novo perfil, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	        	    	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" class="caixa">
	      	      		<tr>
	        	      		<td class="textonormal" bgcolor="#DCEDF7">Perfil*</td>
	          	    		<td class="textonormal">
	          	    			<input type="text" name="PerfilDescricao" value="<?php echo $PerfilDescricao; ?>" size="40" maxlength="30" class="textonormal">
	            	  			<input type="hidden" name="Critica" value="1">
	            	  		</td>
	            			</tr>
	            			<tr>
		              		<td class="textonormal"  bgcolor="#DCEDF7">Situação*</td>
		              		<td class="textonormal" >
	  	              		<select name="Situacao" size="1" value="A"  class="textonormal">
	      	            		<option value="A">ATIVO </option>
	    	              		<option value="I">INATIVO</option>
	        	        		</select>
	          	    		</td>
	            			</tr>
							<tr>
		              			<td class="textonormal"  bgcolor="#DCEDF7">Perfil corporativo</td>
		              			<td class="textonormal" >
	  	              				<select name="Corporativo" size="1" value="N" class="textonormal">
	      	            				<option value="S">SIM</option>
	    	              				<option value="N" selected>NÃO</option>
	        	        			</select>
	          	    			</td>
	            			</tr>
	            		</table>
		          	</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
	          	  	<input type="submit" name="Incluir" value="Incluir" class="botao">
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

<script language="JavaScript">
<!--
document.Perfil.PerfilDescricao.focus();
//-->
</script>
