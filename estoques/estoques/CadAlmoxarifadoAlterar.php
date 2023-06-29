<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAlmoxarifadoAlterar.php
# Objetivo: Programa de Alteração de Almoxarifado
# Autor:    Franklin Alves
# Data:     27/06/2005
# OBS.:     Tabulação 2 espaços
# -------------------------------------------------------------------------
# Alterado: Carlos Abreu 
# Data:     12/03/2007 - Inclusão do campo RPA
# -------------------------------------------------------------------------
# Alterado: Carlos Abreu 
# Data:     01/06/2007 - Inclusão da critica para pelo menos 1 orgao
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		09/08/2018
# Objetivo: Tarefa Redmine 200989
# -------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     18/03/2019
# Objetivo: Tarefa Redmine 177358
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/estoques/CadAlmoxarifadoExcluir.php');
AddMenuAcesso ('/estoques/CadAlmoxarifadoSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao          = $_POST['Botao'];
	$Almoxarifado   = strtoupper2(trim($_POST['Almoxarifado']));
	$Descricao      = strtoupper2(trim($_POST['Descricao']));
	$Abre           = strtoupper2(trim($_POST['Abre']));
	$Endereco       = strtoupper2(trim($_POST['Endereco']));
	$Fone           = strtoupper2(trim($_POST['Fone']));
	$RPA            = $_POST['RPA'];
	$TipoAlmo       = $_POST['TipoAlmo'];
	$Situacao       = $_POST['Situacao'];
	$Orgao          = $_POST['Orgao'];
	$EstoqueVirtual = $_POST['EstoqueVirtual'];
} else {
	$Almoxarifado = $_GET['Almoxarifado'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$db = Conexao();

if ($Botao == "Excluir") {
	$Url = "CadAlmoxarifadoExcluir.php?Almoxarifado=$Almoxarifado";
	
	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	
	header ("location: ".$Url);
	exit;
} elseif ($Botao == "Voltar") {
	header ("location: CadAlmoxarifadoSelecionar.php");
	exit;
} elseif ($Botao == "Alterar") {
	$Mens     = 0;
    $Mensagem = "Informe: ";
	
	if ($Descricao == "") {
		if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadAlmoxarifadoAlterar.Descricao.focus();\" class=\"titulo2\">Descricao</a>";
    }
	
	if ($Abre == "") {
    	if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadAlmoxarifadoAlterar.Abre.focus();\" class=\"titulo2\">Abreviatura da Descrição</a>";
	}
	
    if ($Endereco == "") {
    	if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadAlmoxarifadoAlterar.Endereco.focus();\" class=\"titulo2\">Endereço</a>";
    }
	
	if ($Fone == "") {
    	if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadAlmoxarifadoAlterar.Fone.focus();\" class=\"titulo2\">Fone</a>";
	}
	
    if (count($Orgao) == 0) {
    	if ($Mens == 1) {
			$Mensagem .= ", ";
		}
		
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadAlmoxarifadoAlterar.Orgao.focus();\" class=\"titulo2\">Pelo menos 1 orgão</a>";
    }

    if ($Mens == 0) {
		# Verifica a Duplicidade de Almoxarifado #
		$sql = "SELECT	COUNT(CALMPOCODI)
				FROM	SFPC.TBALMOXARIFADOPORTAL
				WHERE	RTRIM(LTRIM(EALMPODESC)) = '$Descricao'
						AND CALMPOCODI <> $Almoxarifado ";
		
		$result = $db->query($sql);
		
		if (db::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
		    $Linha = $result->fetchRow();
			
			$Qtd = $Linha[0];
			
			if ($Qtd > 0) {
				$Mens = 1;
				$Tipo = 2;
				$Mensagem = "<a href=\"javascript:document.CadAlmoxarifadoAlterar.Descricao.focus();\" class=\"titulo2\"> Descrição Já Cadastrada</a>";
			} else {
				# Atualiza Almoxarifado #
				$Data = date("Y-m-d H:i:s");
				
				$db->query("BEGIN TRANSACTION");
				   
				$sql = "UPDATE	SFPC.TBALMOXARIFADOPORTAL
						SET		EALMPODESC = '$Descricao',
								EALMPOABRE = '$Abre',
								EALMPOENDE = '$Endereco',
								AALMPOFONE = '$Fone',
								FALMPOTIPO = '$TipoAlmo',
								FALMPOSITU = '$Situacao',
								TALMPOULAT = '$Data',
								CALMPONRPA = '$RPA',
								FALMPOESTV = '$EstoqueVirtual'
						WHERE	CALMPOCODI = $Almoxarifado ";
				
				$result = $db->query($sql);
				
				if (db::isError($result)) {
					$db->query("ROLLBACK");
				   	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {
					# Deleta todos os órgãos cadastrados do Almoxarifado selecionado #
					$sql = "DELETE FROM SFPC.TBALMOXARIFADOORGAO
							WHERE CALMPOCODI = $Almoxarifado ";
					
					$result = $db->query($sql);
					
					if (db::isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					}
					 
					for ($i = 0; $i < count($Orgao); $i++) {
						if ($Orgao[$i] != 0) {
							# Insere os órgãos licitantes marcados #
							$sql = "INSERT INTO SFPC.TBALMOXARIFADOORGAO (CALMPOCODI, CORGLICODI, TALMORULAT)
									VALUES ($Almoxarifado, $Orgao[$i], '$Data')";
							
							$result = $db->query($sql);
							
							if (db::isError($result)) {
								$db->query("ROLLBACK");
				   			    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
							}
						}
					}
					
					$db->query("COMMIT");
					$db->query("END TRANSACTION");
					$db->disconnect();

				   	# Envia mensagem para página selecionar #
					$Mensagem = urlencode("Almoxarifado Alterado com Sucesso");
					$Url = "CadAlmoxarifadoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
					
					if (!in_array($Url,$_SESSION['GetUrl'])) {
						$_SESSION['GetUrl'][] = $Url;
					}
					
					header("location: ".$Url);
					exit;
				}
			}
		}
    }
}

if ($Botao == "") {
	$sql = "SELECT	CALMPOCODI, EALMPODESC, EALMPOABRE, EALMPOENDE,
					AALMPOFONE, FALMPOTIPO, FALMPOSITU, CALMPONRPA,
					FALMPOESTV
			FROM	SFPC.TBALMOXARIFADOPORTAL
			WHERE	CALMPOCODI = $Almoxarifado";
	
	$result = $db->query($sql);
	
	if (db::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		while ($Linha = $result->fetchRow()) {
			$Almoxarifado   = $Linha[0];
			$Descricao      = $Linha[1];
			$Abre           = $Linha[2];
			$Endereco       = $Linha[3];
			$Fone           = $Linha[4];
			$TipoAlmo       = $Linha[5];
			$Situacao       = $Linha[6];
			$RPA            = $Linha[7];
			$EstoqueVirtual = $Linha[8];
		}
		
		# Carrega os órgãos do almoxarifado selecionado #
		$sql = "SELECT	CORGLICODI
				FROM	SFPC.TBALMOXARIFADOORGAO
				WHERE	CALMPOCODI = $Almoxarifado";
		
		$result = $db->query($sql);
		
		if (db::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
			while ($Linha = $result->fetchRow()) {
				$Orgao[] .= $Linha[0];
			}
		}
	}
}

$db->disconnect();
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script type="" language="javascript">
<!--
function enviar(valor){
	document.CadAlmoxarifadoAlterar.Botao.value=valor;
	document.CadAlmoxarifadoAlterar.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAlmoxarifadoAlterar.php" method="post" name="CadAlmoxarifadoAlterar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Almoxarifado > Manter
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
	           			MANTER - ALMOXARIFADO
          			</td>
        		</tr>
        		<tr>
          			<td class="textonormal" >
             			<p align="justify">
             				Para atualizar o Almoxarifado, preencha os dados abaixo e clique no botão "Alterar". Para apagar o Almoxarifado clique no botão "Excluir".
             			</p>
          			</td>
        		</tr>
        		<tr>
          			<td>
            			<table border="0" summary="">
              				<tr>
                				<td class="textonormal" bgcolor="#DCEDF7" width="30%">Descrição*</td>
               					<td class="textonormal">
               						<input type="text" name="Descricao" size="100" maxlength="200" value="<?echo $Descricao;?>" class="textonormal">
                				</td>
              				</tr>
              				<tr>
                				<td class="textonormal" bgcolor="#DCEDF7">Abreviatura da Descrição*</td>
	          	    			<td class="textonormal">
	          	    				<input type="text" name="Abre" value="<?php echo $Abre; ?>" size="20" maxlength="20" class="textonormal">
	            	    			<input type="hidden" name="Critica" value="1">
	            	  			</td>
	            			</tr>
						  	<tr>
	        	    			<td class="textonormal" bgcolor="#DCEDF7">Endereço*</td>
	          	  				<td class="textonormal">
	          	   					<input type="text" name="Endereco" value="<?php echo $Endereco; ?>" size="45" maxlength="60" class="textonormal">
	            				</td>
	            			</tr>
						  	<tr>
	        	    			<td class="textonormal" bgcolor="#DCEDF7">Fone*</td>
	          	  				<td class="textonormal">
	          	   					<input type="text" name="Fone" value="<?php echo $Fone; ?>" size="30" maxlength="25" class="textonormal">
	            				</td>
	            			</tr>
	            			<tr>
								<td class="textonormal" bgcolor="#DCEDF7">RPA</td>
								<td class="textonormal">
				 					<select name="RPA" class="textonormal">
				 						<option value="1"<?php if ($RPA==1) echo " selected";?>>RPA 1
				 						<option value="2"<?php if ($RPA==2) echo " selected";?>>RPA 2
				 						<option value="3"<?php if ($RPA==3) echo " selected";?>>RPA 3
				 						<option value="4"<?php if ($RPA==4) echo " selected";?>>RPA 4
				 						<option value="5"<?php if ($RPA==5) echo " selected";?>>RPA 5
				 						<option value="6"<?php if ($RPA==6) echo " selected";?>>RPA 6
				 					</select>
				 					<input type="hidden" name="Critica" value="1">
								</td>
							</tr>
							<tr>
		             			<td class="textonormal" bgcolor="#DCEDF7">Tipo</td>
		             			<td class="textonormal">
	  	             				<select name="TipoAlmo" class="textonormal">
	      	            				<option value="C" <?php if( $TipoAlmo == "C" or $TipoAlmo == ""){ echo "selected"; }?>>CENTRAL </option>
	    	              				<option value="S" <?php if( $TipoAlmo == "S" ){ echo "selected"; }?>>SUBALMOXARIFADO</option>
	    	              				<option value="A" <?php if( $TipoAlmo == "A" ){ echo "selected"; }?>>ALMOXARIFADO</option>
	        	       				</select>
	          	   				</td>
	            			</tr>
	            			<tr>
		             			<td class="textonormal" bgcolor="#DCEDF7">Situação</td>
		             			<td class="textonormal">
	  	             				<select name="Situacao" class="textonormal">
	      	            				<option value="A" <?php if( $Situacao == "A" or $Situacao == ""){ echo "selected"; }?>>ATIVO </option>
	    	              				<option value="I" <?php if( $Situacao == "I" ){ echo "selected"; }?>>INATIVO</option>
	        	       				</select>
	          	   				</td>
	            			</tr>
							<tr>
		             			<td class="textonormal" bgcolor="#DCEDF7">Liberação do estoque virtual</td>
		             			<td class="textonormal">
	  	             				<select name="EstoqueVirtual" class="textonormal">
	      	            				<option value="N" <?php	if ($EstoqueVirtual == "N" or $EstoqueVirtual == "") { echo "selected"; }?>>NÃO</option>
	    	              				<option value="S" <?php	if ($EstoqueVirtual == "S") { echo "selected"; }?>>SIM</option>
	        	       				</select>
	          	   				</td>
	            			</tr>
        	   				<tr>
	        	   				<td class="textonormal" bgcolor="#DCEDF7">Órgãos</td>
        	   					<td class="textonormal">
									<select name="Orgao[]" multiple size="8" value="" class="textonormal">
									<?php	$db  = Conexao();

										$sql = "SELECT	CORGLICODI, EORGLIDESC
												FROM	SFPC.TBORGAOLICITANTE
												WHERE	FORGLISITU = 'A'
												ORDER BY EORGLIDESC";
										
										$result = $db->query($sql);
										
										if (db::isError($result)) {
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										} else {
											while ($Linha = $result->fetchRow()) {
												if (FindArray($Linha[0],$Orgao)) {
													echo "<option value=\"$Linha[0]\" selected>$Linha[1]\n";
												} else {
													echo "<option value=\"$Linha[0]\">$Linha[1]\n";
												}
											}
										}
										$db->disconnect();?>
									</select>
								</td>
							</tr>
           				</table>
          			</td>
        		</tr>
        		<tr align="right">
          			<td>
          				<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
          				<input type="button" value="Alterar" class="botao" onclick="javascript:enviar('Alterar');">
						<input type="button" value="Excluir" class="botao" onclick="javascript:enviar('Excluir');">
          				<input name="voltar" type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
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
