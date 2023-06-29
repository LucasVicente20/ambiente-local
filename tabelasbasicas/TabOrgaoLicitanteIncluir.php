<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabOrgaoLicitanteIncluir.php
# Autor:    Roberta Costa/Rossana Lira
# Data:     01/04/03
# Objetivo: Programa de Inclusão de Orgão Licitante
# -------------------------------------------------------------------------
# Alterado:	Ariston Cordeiro
# Data:		02/09/2010
# Objetivo: Adicionado campo 'tipo de administração'
# -------------------------------------------------------------------------
# Alterado:	João Batista Brito
# Data:		08/03/2012
# Objetivo: Adicionado campo 'ordenador de despesas'
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     03/12/2018
# Objetivo: Tarefa Redmine 207615
# -------------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# Data:     11/03/2019
# Objetivo: Tarefa Redmine 211777
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Critica                    = $_POST['Critica'];
	$OrgaoLicitanteDescricao    = strtoupper2(trim($_POST['OrgaoLicitanteDescricao']));
	$OrdenadorDespesasDescricao = trim($_POST['OrdenadorDespesasDescricao']);
	$Situacao                   = $_POST['Situacao'];
	$TipoAdministracao          = $_POST['TipoAdministracao'];
	$Virgula                    = $_POST['Virgula'];
	$exibicaoValor				= $_POST['ExibicaoValor'];
    $cnpj                       = str_replace('.', '', str_replace('-', '', $_POST['cnpj']));
    $cnpj                       =str_replace('/','',$cnpj);
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabOrgaoLicitanteIncluir.php";

# Critica dos Campos #
if ($Critica == 1) {
	$Mens     = 0;
	$Mensagem = "Informe: ";
	
	if ($OrgaoLicitanteDescricao == "") {
		$Mens      = 1;
		$Tipo      = 2;
  		$Mensagem .= "<a href=\"javascript:document.Orgao.OrgaoLicitanteDescricao.focus();\" class=\"titulo2\">Órgão Licitante</a>";
	} elseif (is_null($TipoAdministracao) or $TipoAdministracao=='N') {
		$Mens      = 1;
		$Tipo      = 2;
  		$Mensagem .= "<a href=\"javascript:document.Orgao.TipoAdministracao.focus();\" class=\"titulo2\">Tipo de administração</a>";
	}
	
	if ($Mens == 0) {	
	  	$db = Conexao();
		
		# Verifica a Duplicidade de Órgão Licitante #
		$sql = "SELECT COUNT(CORGLICODI) FROM SFPC.TBORGAOLICITANTE WHERE RTRIM(LTRIM(EORGLIDESC)) = '$OrgaoLicitanteDescricao' ";
		
		$result = $db->query($sql);
		
		if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
		    $Linha = $result->fetchRow();
			$Qtd = $Linha[0];
			
			if ($Qtd > 0) {
				$Mens = 1;
				$Tipo = 2;
				$Mensagem = "<a href=\"javascript:document.Orgao.OrgaoLicitanteDescricao.focus();\" class=\"titulo2\"> Órgão Licitante Já Cadastrado</a>";
			} else {
				$sql = "SELECT MAX(CORGLICODI) FROM SFPC.TBORGAOLICITANTE";
				
				$result = $db->query($sql);
				
				if (PEAR::isError($result)) {
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				} else {	
					while ($Linha = $result->fetchRow()) {
						$Codigo = $Linha[0] + 1;
					}
					# Insere Órgão #
					$db->query("BEGIN TRANSACTION");
					
					$Data = date("Y-m-d H:i:s");
					
					$sql  = "INSERT INTO SFPC.TBORGAOLICITANTE (";
					$sql .= "CORGLICODI, EORGLIDESC, FORGLISITU, TORGLIULAT, FORGLITIPO, EORGLIORDE, AORGLICNPJ";
					$sql .= ") VALUES ( ";
					$sql .= "$Codigo, '$OrgaoLicitanteDescricao', '$Situacao', '$Data', '$TipoAdministracao', '".strtoupper ($OrdenadorDespesasDescricao)."','$cnpj')";
					
					$result = $db->query($sql);
					
					if (PEAR::isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					} else {
						$db->query("COMMIT");
						$db->query("END TRANSACTION");
						
						$Mens = 1;
						$Tipo = 1;
						$Mensagem = "Órgão Licitante Incluído com Sucesso";
						
						$OrgaoLicitanteDescricao = "";
						$OrdenadorDespesasDescricao = "";						
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

	<?php MenuAcesso(); ?>

</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
	<form action="TabOrgaoLicitanteIncluir.php" method="post" name="Orgao">
		<br><br><br><br><br>
		<table cellpadding="3" border="0">
  			<!-- Caminho -->
  			<tr>
    			<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    			<td align="left" class="textonormal" colspan="2">
      				<font class="titulo2">|</font>
      				<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Órgao Licitante > Incluir
    			</td>
  			</tr>
  			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php
            if ($Mens == 1) {
                ?>
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
      				<table border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF">
        				<tr>
	      					<td class="textonormal">
	        					<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
	          						<tr>
	            						<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    								INCLUIR - ÓRGÃO LICITANTE
		          						</td>
		        					</tr>
	  	      						<tr>
	    	      						<td class="textonormal">
	      	    							<p align="justify">
	        	    							Para incluir um novo órgão licitante, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
	          	   							</p>
	          							</td>
		        					</tr>
		        					<tr>
	  	        						<td>
	    	      							<table class="textonormal" border="0" align="left" class="caixa">
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Órgão Licitante*</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="OrgaoLicitanteDescricao" value="<?php echo $OrgaoLicitanteDescricao; ?>" size="45" maxlength="200" class="textonormal">
                                                        <input type="hidden" name="Critica" value="1">
                                                    </td>
                                                <tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">CNPJ*</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="cnpj" id="cnpj"  value="<?php echo $cnpj; ?>" size="20" maxlength="200" class="textonormal"
                                                    </td>
                                                <tr>
		        	      							<td class="textonormal" bgcolor="#DCEDF7">Ordenador de Despesas</td>
                                                <td class="textonormal">
	          	    									<input type="text" name="OrdenadorDespesasDescricao" value="<?php echo $OrdenadorDespesasDescricao; ?>" size="70" maxlength="200" class="textonormal">
													</td>
	           									</tr>
	            								<tr>
		              								<td class="textonormal"  bgcolor="#DCEDF7">Situação*</td>
		              								<td class="textonormal" >
	  	              									<select name="Situacao" size="1" value="A" class="textonormal">
	      	            									<option value="A">ATIVO </option>
	    	              									<option value="I">INATIVO</option>
	        	        								</select>
	          	    								</td>
	            								</tr>
	            								<tr>
		              								<td class="textonormal"  bgcolor="#DCEDF7">Tipo de Administração*</td>
		              								<td class="textonormal">
	  	              									<select name="TipoAdministracao" size="1" value="N" class="textonormal">
	      	            									<option value="N">Escolha tipo de administração</option>
	      	            									<option value="D">DIRETA</option>
	    	              									<option value="I">INDIRETA</option>
	        	        								</select>
	          	    								</td>
	            								</tr>
												<tr>
													<td class="textonormal" bgcolor="#DCEDF7">Exibição na internet do valor estimado de processos licitatórios em andamento</td>
													<td class="textonormal">
														<select name="ExibicaoValor" size="1" value="S" class="textonormal">
															<option value="S">SIM</option>
															<option value="N">NÃO</option>
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
$('#cnpj').mask('99.999.999/9999-99');
</script>
<script language="javascript" type="">


	<!--
	document.Orgao.OrgaoLicitanteDescricao.focus();
	//-->
</script>