<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabOrgaoLicitanteAlterar.php
# Autor:    Roberta Costa
# Data:     01/04/03
# Objetivo: Programa de Alteração do Órgão Licitante
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
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/tabelasbasicas/TabOrgaoLicitanteExcluir.php');
AddMenuAcesso ('/tabelasbasicas/TabOrgaoLicitanteSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$Botao                      = $_POST['Botao'];
	$Critica                    = $_POST['Critica'];
	$OrgaoLicitanteCodigo       = $_POST['OrgaoLicitanteCodigo'];
	$OrgaoLicitanteDescricao    = strtoupper2(trim($_POST['OrgaoLicitanteDescricao']));
	$OrdenadorDespesasDescricao = trim($_POST['OrdenadorDespesasDescricao']);
	$Situacao                   = $_POST['Situacao'];
	$TipoAdministracao          = $_POST['TipoAdministracao'];
	$DescSituacao               = $_POST['DescSituacao'];
	$exibicaoValor              = $_POST['ExibicaoValor'];
    $cnpj                       = str_replace('.', '', str_replace('-', '', $_POST['cnpj']));
    $cnpj                       = str_replace('/','',$cnpj);
	
} else {
	$OrgaoLicitanteCodigo    = $_GET['OrgaoLicitanteCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "TabOrgaoLicitanteAlterar.php";

# Redireciona para a página de excluir #
if ($Botao == "Excluir") {
	$Url = "TabOrgaoLicitanteExcluir.php?OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo";
	
	if (!in_array($Url,$_SESSION['GetUrl'])) {
		$_SESSION['GetUrl'][] = $Url;
	}
	header("location: ".$Url);
	exit();
} elseif ($Botao == "Voltar") {
	header("location: TabOrgaoLicitanteSelecionar.php");
	exit();
} else {
	# Critica dos Campos #
	if ($Critica == 1) {
		$Mens = 0;
		$Mensagem = "Informe: ";
		
		if ($OrgaoLicitanteDescricao == "")  {
			$Critica   = 1;
		    $LerTabela = 0;
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.Orgao.OrgaoLicitanteDescricao.focus();\" class=\"titulo2\">Órgão Licitante</a>";
		}
		
		if (is_null($TipoAdministracao)) {
			$Critica   = 1;
			$LerTabela = 0;
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.Orgao.TipoAdministracao.focus();\" class=\"titulo2\">Tipo de administração</a>";
		}
		
		if ($Mens == 0) {
			# Verifica a Duplicidade de Órgão Licitante #
			$db = Conexao();
			
			$sql = "SELECT COUNT(CORGLICODI) FROM SFPC.TBORGAOLICITANTE WHERE RTRIM(LTRIM(EORGLIDESC)) = '$OrgaoLicitanteDescricao' AND CORGLICODI <> $OrgaoLicitanteCodigo";
			
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
					# Atualiza Orgão #
					$Data = date("Y-m-d H:i:s");
					
					$db->query("BEGIN TRANSACTION");

					$cnpj                       = str_replace('.', '', str_replace('-', '', $_POST['cnpj']));
    				$cnpj                       = str_replace('/','',$cnpj);
					
					$sql  = "UPDATE SFPC.TBORGAOLICITANTE ";
					$sql .= "   SET EORGLIDESC = '$OrgaoLicitanteDescricao', FORGLISITU = '$Situacao', ";
					$sql .= "       TORGLIULAT = '$Data', ";
					$sql .= "       FORGLITIPO = '$TipoAdministracao', ";
					$sql .= "       EORGLIORDE = '".strtoupper ($OrdenadorDespesasDescricao)."', ";
					$sql .= "       FORGLIEXVE = '$exibicaoValor' ,";
                    $sql .= "       AORGLICNPJ = '$cnpj' ";
					$sql .= " WHERE CORGLICODI = $OrgaoLicitanteCodigo";
					// print_r($sql);die;

					$result = $db->query($sql);
					
					if (PEAR::isError($result)) {
						$db->query("ROLLBACK");
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
					} else {
						$db->query("COMMIT");
						$db->query("END TRANSACTION");
						$db->disconnect();

						# Envia mensagem para página selecionar #
						$Mensagem = urlencode("Órgão Licitante Alterado com Sucesso");
						$Url = "TabOrgaoLicitanteSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1&Critica=0";
						
						if (!in_array($Url,$_SESSION['GetUrl'])) {
							$_SESSION['GetUrl'][] = $Url;
						}
						header("location: ".$Url);
						exit();
					}
				}
			}
		}
	}
}

if ($Critica == 0) {
	$db = Conexao();
	$sql  = "SELECT EORGLIDESC, FORGLISITU, CORGLICODI, FORGLITIPO, EORGLIORDE, FORGLIEXVE, AORGLICNPJ  ";
	$sql .= "FROM SFPC.TBORGAOLICITANTE ";
	$sql .= "WHERE CORGLICODI = $OrgaoLicitanteCodigo";
	$result = $db->query($sql);
	if (PEAR::isError($result)) {
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	} else {
		while ($Linha = $result->fetchRow()) {
			$OrgaoLicitanteDescricao     = $Linha[0];
			$Situacao                    = $Linha[1];
			$OrgaoLicitanteCodigo        = $Linha[2];
			$TipoAdministracao	         = $Linha[3];
			$OrdenadorDespesasDescricao  = $Linha[4];
			$exibicaoValor               = $Linha[5];
            $cnpj                        = $Linha[6];
			$cnpj 						 = str_pad($cnpj, 14, "0",STR_PAD_LEFT);
		}
	}
	// print_r($cnpj);die;
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
		document.Orgao.Botao.value=valor;
		document.Orgao.submit();
	}
	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="TabOrgaoLicitanteAlterar.php" method="post" name="Orgao">
		<br><br><br><br><br>
		<table cellpadding="3" border="0">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php"><font color="#000000">Página Principal</font></a> > Tabelas > Órgao Licitante > Manter
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php if ($Mens == 1) {	?>
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
								MANTER - ÓRGÃO LICITANTE
          					</td>
        				</tr>
        				<tr>
          					<td class="textonormal">
             					<p align="justify">
             						Para atualizar o órgão, preencha os dados abaixo e clique no botão "Alterar". Para apagar o órgão clique no botão "Excluir".
             					</p>
          					</td>
        				</tr>
        				<tr>
          					<td>
            					<table>
									<tr>
									   	<td>
											<input type="hidden" name="OrgaoLicitanteCodigo" value="<?php echo $OrgaoLicitanteCodigo?>">
                						</td>
              						</tr>

              						<tr>
                						<td class="textonormal" bgcolor="#DCEDF7">Órgão Licitante*</td>
               							<td class="textonormal">
               								<input type="text" name="OrgaoLicitanteDescricao" size="45" maxlength="200" value="<?php echo $OrgaoLicitanteDescricao?>" class="textonormal">
                							<input type="hidden" name="Critica" value="1">
                    					</td>
									</tr>
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">CNPJ*</td>
                                        <td class="textonormal">
                                            <input type="text" name="cnpj" id="cnpj" value="<?php echo $cnpj; ?>" size="20" maxlength="200" class="textonormal">
                                        </td>
                                    <tr>
									<tr>
	        							<td class="textonormal" bgcolor="#DCEDF7">Ordenador de Despesas</td>
	        							<td class="textonormal">
	          								<input type="text" name="OrdenadorDespesasDescricao" value="<?php echo $OrdenadorDespesasDescricao; ?>" size="70" maxlength="200" class="textonormal">
										</td>
	         						</tr>
              						<tr>
              							<td class="textonormal" bgcolor="#DCEDF7">Situação*</td>
	              						<td class="textonormal">
	                						<?php
                  							if ($Situacao == "A") {
                     							$DescSituacao = "ATIVO";
                  							} else {
                     							$DescSituacao = "INATIVO";
                  							}
	                						?>
	                						<select name="Situacao" value="<?php echo $DescSituacao; ?>" class="textonormal">
	        	        						<option value="A" <?php if ($Situacao == "A") { echo "selected"; }?>>ATIVO</option>
                    							<option value="I" <?php if ($Situacao == "I") { echo "selected"; }?>>INATIVO</option>
                  							</select>
                						</td>
			            			</tr>
		    						<tr>
		          						<td class="textonormal"  bgcolor="#DCEDF7">Tipo de Administração*</td>
		          						<td class="textonormal">
		          							<select name="TipoAdministracao" size="1" class="textonormal">
		            							<option value="D" <?php if ($TipoAdministracao == "D") { echo "selected"; }?> >DIRETA</option>
		              							<option value="I" <?php if ($TipoAdministracao == "I") { echo "selected"; }?> >INDIRETA</option>
			        						</select>
		  	    						</td>
		    						</tr>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7">Exibição na internet do valor estimado de processos licitatórios em andamento</td>
										<td class="textonormal">
											<select name="ExibicaoValor" size="1" class="textonormal">
												<option value="S" <?php if ($exibicaoValor != "N") { echo "selected"; }?>>SIM</option>
												<option value="N" <?php if ($exibicaoValor == "N") { echo "selected"; }?>>NÃO</option>
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
            					<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar')">
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
<script language="JavaScript">
		$('#cnpj').mask('99.999.999/9999-99');
</script>
<script language="javascript" type="">
	<!--
	document.Orgao.OrgaoLicitanteDescricao.focus();
	//-->
</script>