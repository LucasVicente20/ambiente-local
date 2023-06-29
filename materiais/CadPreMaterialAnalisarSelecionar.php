<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPreMaterialAnalisarSelecionar.php
# Autor:    Rossana Lira/Altamiro Pedrosa
# Data:     03/08/2005
# Alterado: Álvaro Faria
# Data:     19/12/2006 - Substr da descrição do material
# Alterado: Rodrigo Melo
# Data:     21/08/2008 - Alteração para realizar a análise de serviços pré-cadastrados
# Alterado: Rodrigo Melo
# Data:     22/09/2009 - Alterando o nome das tabelas SFPC.TBPREMATERIAL e SFPC.TBPREMATERIALTIPOSITUACAO para SFPC.TBPREMATERIALSERVICO e BPREMATERIALSERVICOTIPOSITUACAO, respectivamente (CR 2749).
# Objetivo: Programa de Manutenção de Pré-Material
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/materiais/CadPreMaterialAnalisar.php');

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Situacao = $_POST['Situacao'];
		$TipoGrupo = $_POST['TipoGrupo'];
		$Botao = $_POST['Botao'];
		$DataIni = $_POST['DataIni'];
		$DataFim = $_POST['DataFim'];
}else{
		$Critica = $_GET['Critica'];
		$Mensagem = urldecode($_GET['Mensagem']);
		$Mens = $_GET['Mens'];
		$Tipo = $_GET['Tipo'];
		$DataIni = $_GET['DataIni'];
		$DataFim = $_GET['DataFim'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Mens == '' || $Mens == null){
	$Mens = 0;
}

# Sql para montagem da janela #
$sql  = "
	SELECT
		DISTINCT(PRE.CPREMACODI), PRE.EPREMADESC, PRE.DPREMACADA, GRU.CGRUMSCODI, CLA.CCLAMSCODI,
		PRESIT.EPREMSDESC, GRUEMP.EGREMPDESC, USUPOR.EUSUPORESP, USUPOR.AUSUPOFONE, USUPOR.EUSUPOMAIL,
		OL.EORGLIDESC, CC.ECENPODESC, CC.ECENPODETA, GRU.FGRUMSTIPO
	FROM
		SFPC.TBPREMATERIALSERVICO PRE,
		SFPC.TBGRUPOMATERIALSERVICO GRU,
		SFPC.TBCLASSEMATERIALSERVICO CLA,
		SFPC.TBPREMATERIALSERVICOTIPOSITUACAO PRESIT,
		SFPC.TBGRUPOEMPRESA GRUEMP,
		SFPC.TBUSUARIOPORTAL USUPOR
			-- estes joins retornam o 1º centro de custo e orgão licitante que o usuário solicitante está cadastrado
			LEFT OUTER JOIN	SFPC.TBUSUARIOCENTROCUSTO UCC ON
				UCC.CCENPOSEQU = (
					-- Retorna o 1o CCENPOSEQU registrado que o usuário está cadastrado
					SELECT MIN(UCC2.CCENPOSEQU)
					FROM SFPC.TBUSUARIOCENTROCUSTO UCC2
					WHERE USUPOR.CUSUPOCODI = UCC2.CUSUPOCODI
				)
			LEFT OUTER JOIN SFPC.TBCENTROCUSTOPORTAL CC ON UCC.CCENPOSEQU = CC.CCENPOSEQU
			LEFT OUTER JOIN SFPC.TBORGAOLICITANTE OL ON CC.CORGLICODI = OL.CORGLICODI
	WHERE
		GRU.CGRUMSCODI = PRE.CGRUMSCODI AND
		CLA.CCLAMSCODI = PRE.CCLAMSCODI AND
		PRE.CPREMSCODI = PRESIT.CPREMSCODI AND
		PRE.CGREMPCODI = GRUEMP.CGREMPCODI AND
		PRE.CUSUPOCODI = USUPOR.CUSUPOCODI AND
		PRE.CGREMPCODI = USUPOR.CGREMPCODI ";






# Verifica qual o tipo da situação #
if(($Botao == "Pesquisar")){

	# Critica dos Campos #
	$Mensagem	= "Informe: ";

	//Valida Tipo de Grupo
	if ($TipoGrupo == "" || $TipoGrupo == null){
		if($Mens == 1){ $Mensagem .= ", "; }
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadPreMaterialAnalisarSelecionar.TipoGrupo.focus();\" class=\"titulo2\">Tipo do Grupo</a>";
	}

	//Valilda Situação
	if ($Situacao == "" || $Situacao == null){
		if($Mens == 1){ $Mensagem .= ", "; }
		$Mens      = 1;
		$Tipo      = 2;
		$Mensagem .= "<a href=\"javascript:document.CadPreMaterialAnalisarSelecionar.Situacao.focus();\" class=\"titulo2\">Situação</a>";
	}

	//Valida o Período (data inicial e final)
	$MensErro = ValidaPeriodo($DataIni,$DataFim,$Mens,"CadPreMaterialAnalisarSelecionar");
	if( $MensErro != "" ){ //data errada
		if($Mens == 1){ $Mensagem .= ", "; }
		$Mensagem .= $MensErro;
		$Mens = 1;
		$Tipo = 2;
	}else{
		if($Mens == 0){ //Caso não tem criticas dos campos
			$where .= " AND PRESIT.CPREMSCODI = '".$Situacao."'";
			if( $DataIni != "" and $DataFim != "" ){
				$where .= " AND PRE.DPREMACADA >= '".DataInvertida($DataIni)."'
					AND PRE.DPREMACADA <= '".DataInvertida($DataFim)."' ";
			}
			if($TipoGrupo != 'T'){
				$where .= " AND GRU.FGRUMSTIPO = '$TipoGrupo' ";
			}
		}
	}
}

# Gera o SQL com a concatenação das variaveis $sql,$from,$where #
$sqlgeral = $sql.$from.$where;

if($Botao == "Limpar"){
		header("location: CadPreMaterialAnalisarSelecionar.php");
		exit;
}
?>
<html>
<?php 
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadPreMaterialAnalisarSelecionar.Botao.value=valor;
	document.CadPreMaterialAnalisarSelecionar.submit();
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadPreMaterialAnalisarSelecionar.php" method="post" name="CadPreMaterialAnalisarSelecionar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais/Serv > Pré-Cadastro > Análise
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php  if($Mens == 1){?>
	<tr>
		<td width="150"></td>
		<td align="left"><?php  ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php  } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
				<tr>
					<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">
						ANÁLISE - PRÉ-CADASTRO DE MATERIAIS / SERVIÇOS
					</td>
				</tr>
				<tr>
					<td class="textonormal" colspan="5">
						 <p align="justify">
						 Para efetuar a análise do pré-cadastro de Material/Serviço, selecione a situação "Em análise", as datas inicial e final, e depois clique em Pesquisar. Após isso, na descrição do material/serviço desejado.
						 As demais situações também podem ser visualizadas.
						 </p>
					</td>
				</tr>
				<tr>
					<td colspan="5">
						<table width="100%" summary="">

							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" colspan="5" width="30%">Tipo de Grupo*</td>
								<td class="textonormal">
									<select name="TipoGrupo" class="textonormal">
										<option value="">Selecione um Tipo de Grupo...</option>
										<option value="M" <?php if( $TipoGrupo == "M" ){ echo "selected"; }?>>MATERIAL</option>
										<option value="S" <?php if( $TipoGrupo == "S" ){ echo "selected"; }?>>SERVIÇO</option>
										<option value="T" <?php if( $TipoGrupo == "T" ){ echo "selected"; }?>>TODOS</option>
									</select>
								</td>
							</tr>

							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" colspan="5" width="30%">Situação*</td>
								<td class="textonormal" >
									<select name="Situacao" class="textonormal">

										<option value="">Selecione uma Situação...</option>
										<?php
										$db   = Conexao();
										$sql  = "SELECT CPREMSCODI, EPREMSDESC FROM SFPC.TBPREMATERIALSERVICOTIPOSITUACAO ";
										$sql .= " ORDER BY CPREMSCODI";
										$result = $db->query($sql);
										if( PEAR::isError($result) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												while( $Linha = $result->fetchRow() ){
														$Descricao = substr($Linha[1],0,40);
														if($Linha[0] == $Situacao){
																echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
														}else{
																echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
														}
												}
										}
										$db->disconnect();
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20" colspan="5">Período*</td>
								<td class="textonormal">
								<?php
									$DataMes = DataMes();
									if( $DataIni == "" ){ $DataIni = $DataMes[0]; }
									if( $DataFim == "" ){ $DataFim = $DataMes[1]; }
									$URLIni		= "../calendario.php?Formulario=CadPreMaterialAnalisarSelecionar&Campo=DataIni";
									$URLFim		= "../calendario.php?Formulario=CadPreMaterialAnalisarSelecionar&Campo=DataFim";
								?>
									<input type="text" name="DataIni" size="10" maxlength="10" value="<?php echo $DataIni;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLIni ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>&nbsp;a&nbsp;
									<input type="text" name="DataFim" size="10" maxlength="10" value="<?php echo $DataFim;?>" class="textonormal">
									<a href="javascript:janela('<?php echo $URLFim ?>','Calendario',220,170,1,0)"><img src="../midia/calendario.gif" border="0" alt=""></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="5" align="right">
						<input type="button" name="Pesquisar" value="Pesquisar" class="botao" onClick="javascript:enviar('Pesquisar')">
						<input type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar');">
						<input type="hidden" name="Botao" value="">
					</td>
				</tr>
				<?php 
				# Exibe o Resultado da Pesquisa #
				if(($Botao == "Pesquisar")&&($Mens==0)&&($Situacao != "")&&($TipoGrupo != "")){
						if($sqlgeral != ""){
								$db     = Conexao();
								$res    = $db->query($sqlgeral);
								$qtdres = $res->numRows();
								if( PEAR::isError($res) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlgeral");
								}else{
										echo "					<tr>\n";
										echo "						<td align=\"center\" bgcolor=\"#75ADE6\" colspan=\"5\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
										echo "					</tr>\n";
										if($qtdres > 0){
												echo "					<tr align=\"center\">\n";
												echo "						<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">TIPO DE GRUPO</td>\n";
												echo "						<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DESCRIÇÃO</td>\n";
												echo "						<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"12%\" align=\"center\">DATA</td>\n";
												echo "						<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"25%\">SOLICITANTE</td>\n";
												echo "						<td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"13%\">SITUAÇÃO</td>\n";
												echo "					</tr>\n";
												while( $row = $res->fetchRow() ){
														$MaterialCodigo       = $row[0];
														$MaterialDescricao    = substr($row[1],0,300);
														$DataCadastro         = DataBarra($row[2]);
														$GrupoCodigo          = $row[3];
														$ClasseCodigo         = $row[4];
														$TipoSitDescricao     = $row[5];
														$GrupoEmpDescricao    = substr($row[6],0,30);
														$ResponsavelDescricao = substr($row[7],0,30);
														$ResponsavelTelefone  = $row[8];
														$ResponsavelEmail     = $row[9];
														$ResponsavelOrgao    = $row[10];
														$ResponsavelCC     = $row[11];
														$ResponsavelCCDetalhamento     = $row[12];
														$TipoGrupoBanco = $row[13];
														echo"			<tr>\n";
														echo "					<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"12%\">\n";

														if($TipoGrupoBanco == "M" ){ //CASO SEJA MATERIAL (M)
															echo "MATERIAL";
														} else { //CASO SEJA SERVIÇO (S)
															echo "SERVIÇO";
														}

														echo "					</td>\n";
														echo "			<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"50%\">\n";
														if($Situacao == '1'){
														$Url = "CadPreMaterialAnalisar.php?Grupo=$GrupoCodigo&Classe=$ClasseCodigo&PreMaterialServico=$MaterialCodigo&TipoGrupo=$TipoGrupoBanco";
														if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																echo "<a href=\"CadPreMaterialAnalisar.php?Grupo=$GrupoCodigo&Classe=$ClasseCodigo&PreMaterialServico=$MaterialCodigo&TipoGrupo=$TipoGrupoBanco\"><font color=\"#000000\">".strtoupper2($MaterialDescricao)."</font></a>";
														}else{
																echo (strtoupper2($MaterialDescricao));
														}
														echo "					</td>\n";
														echo "					<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"12%\">\n";
														echo "						$DataCadastro";
														echo "					</td>\n";
														echo "					<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"25%\">\n";
														echo "						$GrupoEmpDescricao / $ResponsavelDescricao / $ResponsavelTelefone/ $ResponsavelEmail";
														if($ResponsavelOrgao){
															echo "/ $ResponsavelOrgao - $ResponsavelCC - $ResponsavelCCDetalhamento";
														}
														echo "					</td>\n";
														echo "					<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"13%\">\n";
														echo "						$TipoSitDescricao";
														echo "					</td>\n";
														echo "				</tr>\n";
												}
												$db->disconnect();
										}else{
												echo "<tr>\n";
												echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
												echo "		Pesquisa sem Ocorrências.\n";
												echo "	</td>\n";
												echo "</tr>\n";
										}
								}
						}
				}
				?>
			</table>
		</td>
	</tr>
	<!-- Fim do Corpo -->
</table>
</form>
</body>
</html>
