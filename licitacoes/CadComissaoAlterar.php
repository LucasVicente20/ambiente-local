<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadComissaoAlterar.php
# Autor:    Rossana Lira
# Data:     07/04/03
# Objetivo: Programa de Alteração da Comissao de Licitação
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     18/03/2019
# Objetivo: Tarefa Redmine 177358
#-------------------------------------------------------------------------
# Alterado: Lucas André 
# Data:     28/04/2023
# Objetivo: Tarefa Redmine 282316
#-------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/licitacoes/CadComissaoExcluir.php' );
AddMenuAcesso( '/licitacoes/CadComissaoSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD']  == "POST"){
		$Botao             = $_POST['Botao'];
		$Critica           = $_POST['Critica'];
		$ComissaoDescricao = strtoupper2(trim($_POST['ComissaoDescricao']));
		$ComissaoCodigo    = $_POST['ComissaoCodigo'];
		$Presidente        = strtoupper2(trim($_POST['Presidente']));
		$Email             = trim($_POST['Email']);
		$Fone              = trim($_POST['Fone']);
		$Fax               = trim($_POST['Fax']);
		$Local             = strtoupper2(trim($_POST['Local']));
		$Situacao          = $_POST['Situacao'];
        $Sigla             = $_POST['Sigla'];
}else{
		$ComissaoCodigo    = $_GET['ComissaoCodigo'];
		
		$db  = Conexao();
		
		$sql = "SELECT ecomlisigl FROM SFPC.TBCOMISSAOLICITACAO WHERE ccomlicodi = $ComissaoCodigo ";
		
		$result = $db->query($sql);

		if( PEAR::isError($result) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
			$row = $result->fetchRow();
			$Sigla = $row[0];
		}

		$_SESSION['Sigla'] = $Sigla;
}


# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadComissaoAlterar.php";

# Redireciona para a página de excluir #
if( $Botao == "Excluir" ){
		$Url = "CadComissaoExcluir.php?ComissaoCodigo=$ComissaoCodigo";
		if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
	  header("location: ".$Url);
	  exit();
}elseif( $Botao == "Voltar" ){
	  header("location: CadComissaoSelecionar.php");
	  exit();
}else{
	# Critica dos Campos #
	if( $Critica == 1 ) {
		$Mens     = 0;
		$Mensagem = "Informe: ";
	  	if( $ComissaoDescricao == "" ){
			$Critica   = 1;
		    $LerTabela = 0;
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.Comissao.ComissaoDescricao.focus();\" class=\"titulo2\">Comissão</a>";
		}
		if( $Presidente == "" ){
			if ($Mens == 1){
				$Mensagem.=", ";
			}
			$Mens      = 1;
			$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.Comissao.Presidente.focus();\" class=\"titulo2\">Nome do Presidente</a>";
		}
		if( ( $Email != "" ) and !strchr($Email, "@")){
		    if ($Mens == 1){
				$Mensagem.=", ";
			}
		    $Mens      = 1;
		    $Tipo      = 2;
    		$Mensagem .= "<a href=\"javascript:document.Comissao.Email.focus();\" class=\"titulo2\">E-Mail Válido</a>";
			}
		if( $Local == "" ){
			if ($Mens == 1){
				$Mensagem.=", ";
			}
			$Mens      = 1;
			$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.Comissao.Local.focus();\" class=\"titulo2\">Localização</a>";
		}
		if($Fax != "") {
			if(!SoNumeros($Fax)){
				if ($Mens == 1){$Mensagem.=", ";}
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Comissao.Fax.focus();\" class=\"titulo2\">Fax com Números</a>";
		    }
		}
		if($Fone != ""){
			if(!SoNumeros($Fone)){
				if ($Mens == 1){$Mensagem.=", ";}
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.Comissao.Fone.focus();\" class=\"titulo2\">Telefone com Números</a>";
		    }
		}
		if($Sigla == "") {
			if ($Mens == 1){
				$Mensagem.=", ";
			}
			$Mens = 1;$Tipo = 2;$Virgula=1;
			$Mensagem .= "<a href=\"javascript:document.Comissao.Sigla.focus();\" class=\"titulo2\">Sigla</a>";
		}


		if( $Mens == 0 ){
				# Verifica a Duplicidade de Comissão #
				$db     = Conexao();
				$sql    = "SELECT COUNT(CCOMLICODI) FROM SFPC.TBCOMISSAOLICITACAO WHERE ECOMLIDESC = '$ComissaoDescricao' AND CCOMLICODI <> $ComissaoCodigo";
				$result = $db->query($sql);
				if( PEAR::isError($result) ){
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
					while( $Linha = $result->fetchRow() ){
						$Qtd = $Linha[0];
					}
				    if( $Qtd > 0 ) {
						$Mens = 1;$Tipo = 2;
						$Mensagem = "<a href=\"javascript:document.Comissao.ComissaoDescricao.focus();\" class=\"titulo2\">Comissao Já Cadastrada</a>";
					}
					#Verifica Duplicidade da Sigla da Comissão#
					//Primeiro Verifica se a nova sigla é diferente da sigla anterior e depois verifica se a nova sigla já existe no banco de dados
					if($_SESSION['Sigla'] != $Sigla){
						$db     = Conexao();
						$sql    = "SELECT COUNT(ecomlisigl) FROM SFPC.TBCOMISSAOLICITACAO WHERE (ecomlisigl) = '$Sigla'";
						$result = $db->query($sql);

						if (PEAR::isError($result)) {
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
							$Linha = $result->fetchRow();
							$Qtd = $Linha[0];
						}
						if($Qtd > 0){
							$Mens = 1;$Tipo = 2;
							$Mensagem = "<a href=\"javascript:document.Comissao.Sigla.focus();\" class=\"titulo2\">Sigla de Comissão já Cadastrada</a>";
						}else{
							# Atualiza Comissao #
							$db->query("BEGIN TRANSACTION");
							$sql    = "UPDATE SFPC.TBCOMISSAOLICITACAO ";
							$sql   .= "   SET ECOMLIDESC = '$ComissaoDescricao', NCOMLIPRES = '$Presidente', ";
							$sql   .= "       ECOMLIMAIL = '$Email', ECOMLILOCA = '$Local', ";
							$sql   .= "       ACOMLIFONE = '$Fone', ACOMLINFAX = '$Fax', ";
							$sql   .= "       FCOMLISTAT = '$Situacao', TCOMLIULAT = '".date("Y-m-d H:i:s")."' ,";
							$sql   .= "       ECOMLISIGL = '$Sigla'";
							$sql   .= " WHERE CCOMLICODI = $ComissaoCodigo";
							$result = $db->query($sql);
							if( PEAR::isError($result) ){
								$db->query("ROLLBACK");
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}else{
								$db->query("COMMIT");
								$db->query("END TRANSACTION");
								$db->disconnect();
	
								# Envia mensagem para página selecionar #
								$Mensagem = "Comissão Alterada com Sucesso";
								$Url = "CadComissaoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1&Critica=0";
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit();
							}
						}
					}	
				}
			$db->disconnect();
		}
	}
}
if( $Critica == 0 ){
		# Carrega os dados da comissão selecionada #
		$db     = Conexao();
		$sql    = "SELECT ECOMLIDESC, NCOMLIPRES, ECOMLIMAIL, ECOMLILOCA, ACOMLIFONE, ACOMLINFAX, CGREMPCODI, FCOMLISTAT, ECOMLISIGL FROM SFPC.TBCOMISSAOLICITACAO WHERE CCOMLICODI = $ComissaoCodigo";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}
		while( $Linha = $result->fetchRow() ){
				$ComissaoDescricao = $Linha[0];
				$Presidente        = $Linha[1];
				$Email             = $Linha[2];
				$Local             = $Linha[3];
				$Fone              = $Linha[4];
				$Fax               = $Linha[5];
				$Situacao          = $Linha[7];
                $Sigla             = $Linha[8];
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
	document.Comissao.Botao.value=valor;
	document.Comissao.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadComissaoAlterar.php" method="post" name="Comissao">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Comissão > Manter
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
		<td class="textonormal"><br>
		<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	           MANTER - COMISSÃO DE LICITAÇÃO
          </td>
        </tr>
        <tr>
          <td class="textonormal" >
             <p align="justify">
             Para atualizar a Comissão, preencha os dados abaixo e clique no botão "Alterar". Para apagar a Comissão clique no botão "Excluir".
             </p>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" summary="">
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Comissão*</td>
               	<td class="textonormal">
               		<input type="text" name="ComissaoDescricao" size="100" maxlength="200" value="<?php echo $ComissaoDescricao?>" class="textonormal">
                	<input type="hidden" name="Critica" value="1">
                	<input type="hidden" name="ComissaoCodigo" value="<?php echo $ComissaoCodigo?>">
                </td>
              </tr>
                <tr>
                    <td class="textonormal" bgcolor="#DCEDF7">Sigla*</td>
                    <td class="textonormal">
                        <input type="text" name="Sigla" value="<?php echo $Sigla; ?>" size="10" maxlength="10" class="textonormal">
                        <input type="hidden" name="Critica" value="1">
                    </td>
                </tr>
                <tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7">Presidente*</td>
      	    		<td class="textonormal">
      	    			<input type="text" name="Presidente" value="<?php echo $Presidente; ?>" size="45" maxlength="60" class="textonormal">
      	    		</td>
        	  	</tr>


             	<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7">E-mail</td>
      	    		<td class="textonormal">
      	    			<input type="text" name="Email" value="<?php echo $Email; ?>" size="45" maxlength="60" class="textonormal">
      	    		</td>
        	  	</tr>
        			<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7">Local*</td>
      	    		<td class="textonormal">
      	    			<input type="text" name="Local" value="<?php echo $Local; ?>" size="45" maxlength="100" class="textonormal">
      	    		</td>
        	  	</tr>
							<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7">Telefone</td>
      	    		<td class="textonormal">
      	    			<input type="text" name="Fone" value="<?php echo $Fone; ?>" size="25" maxlength="25" class="textonormal">
      	    		</td>
        	  	</tr>
							<tr>
    	      		<td class="textonormal" bgcolor="#DCEDF7">Fax</td>
      	    		<td class="textonormal">
      	    			<input type="text" name="Fax" value="<?php echo $Fax; ?>" size="25" maxlength="25" class="textonormal">
      	    		</td>
        	  	</tr>
        	  	<tr>
			      		<td class="textonormal" bgcolor="#DCEDF7">Situação</td>
								<td class="textonormal">
									<?php if( $Situacao == "A" ){?>
								  <input type="radio" name="Situacao" value="A" checked > ATIVA
								  <input type="radio" name="Situacao" value="I"> INATIVA
									<?php }else{ ?>
							 		<input type="radio" name="Situacao" value="A"> ATIVA
									<input type="radio" name="Situacao" value="I" checked > INATIVA
									<?php } ?>
								</td>
			  	  	</tr>
            </table>
          </td>
        </tr>
        <tr>
          <td align="right">
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
document.Comissao.ComissaoDescricao.focus();
//-->
</script>
