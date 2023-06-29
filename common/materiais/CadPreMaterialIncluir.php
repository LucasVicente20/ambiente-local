<?php 
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPreMaterialIncluir.php
# Autor:    Roberta Costa
# Data:     25/04/2005
# Objetivo: Programa de Pré-Inclusão de Material
#------------------
# Alterado: Álvaro Faria
# Data:     19/12/2006 - Alteração de crítica e label de Observação para 100 caracteres
# Alterado: Álvaro Faria
# Data:     26/12/2006 - Retirada de quebra de linha da descrição do material
# Alterado: Carlos Abreu
# Data:     17/01/2007 - Correção para pegar o Novo Código para o pré-cadastro apenas quando for incluir e não no início do processo
# Alterado: Carlos Abreu
# Data:     12/03/2007 - Alteração para utilizar a função RetiraAcentos quando pegar a descricao do material quando submetido
# Alterado: Rodrigo Melo
# Data:     21/11/2007 - Alteração para inserir a descricao do material em maiúsculo e com acentos e caracteres especiais quando submetido ao banco
# Alterado: Rossana Lira
# Data:     03/06/2008 - Limitação de unidades de material para o pré-cadastro solicitado pelo usuário
# Alterado: Ariston Cordeiro
# Data:     18/06/2008 	- Consertado bug em que, quando a descrição é em letras minúsculas, é permitido a criação de descrições iguais na mesma classe
# 											- Proibido a inclusão de pré-materiais em que sua descrição e unidades são iguais às de um pre-material já existente em outra classe
# Alterado: Ariston Cordeiro
# Data:     02/07/2008 	- Permitido a inclusão de descrições de nomes iguais a pré-materiais com situação "Não-Cadastrado"
# Alterado: Ariston Cordeiro
# Data:     11/07/2008 	- Mudança das alterações anteriores para que pre-materiais apenas não possam ter a mesma descrição quando possuem a mesma unidade, independente se as classes são iguais ou não.
# Alterado: Ariston Cordeiro
# Data:     11/07/2008 	- Removido Campo "Centro de custo"
# Alterado: Rodrigo Melo
# Data:     20/08/2009 - Alteração para inserir o pré-cadastro de serviços
# Alterado: Rodrigo Melo
# Data:     22/09/2009 - Alterando o nome das tabelas SFPC.TBPREMATERIAL e SFPC.TBPREMATERIALTIPOSITUACAO para SFPC.TBPREMATERIALSERVICO e BPREMATERIALSERVICOTIPOSITUACAO, respectivamente (CR 2749).
# Autor:    Everton Lino
# Data:     11/08/2010- Remoção de menssagem de código de pré-cadastro
# Alterado: Ariston Cordeiro
# Data:     04/02/2011 - #1546 Red Mine- Campo de descrição de serviços vai de 300 para 500 caracteres
# Alterado: Ariston Cordeiro
# Data:     23/02/2011 - #407 Red Mine- Movendo lista de unidades de pre materiais para config.php
# Alterado: Rodrigo Melo
# Data:     23/05/2011 - Tarefa do Redmine: 2694 - Campo de descrição de serviços vai de 500 para 700 caracteres
# -------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 14/02/2022
# Objetivo: CR #255888
#---------------------------------------------------------------------------
# -------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 07/04/2022
# Objetivo: CR #261839
#---------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadPreMaterialIncluirSelecionar.php' );


# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao              = $_POST['Botao'];
		$TipoMaterial       = $_POST['TipoMaterial'];
		$Grupo              = $_POST['Grupo'];
		$TipoGrupo          = $_POST['TipoGrupo'];
		$Classe             = $_POST['Classe'];
		$Subclasse          = $_POST['Subclasse'];
		$Unidade            = $_POST['Unidade'];
		$DescMaterialServico       = RetiraAcentosVirgula($_POST['DescMaterialServico']);
		$Observacao        = RetiraAcentosVirgula($_POST['Observacao']); 
		$NCaracteres        = $_POST['NCaracteres'];
		$NCaracteresO       = $_POST['NCaracteresO'];
}else{
		$Grupo              = $_GET['Grupo'];
		$Classe             = $_GET['Classe'];
		$TipoGrupo          = $_GET['TipoGrupo'];
		$Subclasse          = $_GET['Subclasse'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

//Variáveis dinâmicas para colocar as informações para material ou serviço.
if($TipoGrupo == 'M') {
   $QtdeCaracteres = 3000;
   $Descricao = "Material";
} else {
	$QtdeCaracteres = 700;
	$Descricao = "Serviço";
}

if($Botao == "Voltar"){
		header("Location: CadPreMaterialIncluirSelecionar.php");
		exit;
}elseif($Botao == "Incluir"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		$DescMaterialServico=strtoupper2($DescMaterialServico);
		if($Unidade == "" && $TipoGrupo == "M"){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadPreMaterialIncluir.Unidade.focus();\" class=\"titulo2\">Unidade de Medida</a>";
		}
		if($DescMaterialServico == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadPreMaterialIncluir.DescMaterialServico.focus();\" class=\"titulo2\">$Descricao</a>";
		}else{
				//if((strlen($DescMaterialServico) > 3000 && $TipoGrupo == "M") || (strlen($DescMaterialServico) > 300 && $TipoGrupo == "S") ){
				if( strlen($DescMaterialServico) > $QtdeCaracteres ){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadPreMaterialIncluir.DescMaterialServico.focus();\" class=\"titulo2\">$Descricao no Máximo com $QtdeCaracteres Caracteres</a>";
				}
		}
		if(strlen($Observacao) > 2000){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadPreMaterialIncluir.Observacao.focus();\" class=\"titulo2\">Observação no Máximo com 2000 Caracteres</a>";
		}
		if($Mens == 0){// verificar se já existe um Pré-Material com mesmo nome e mema unidade já cadastrado no sistema (não cadastrados são ignorados)
				$db   = Conexao();
				$sql  = "
					SELECT count(*)
					FROM SFPC.TBprematerialservico
					WHERE
						epremadesc='".$DescMaterialServico."' AND
						NOT (CPREMSCODI = 3)
				";
				if( $TipoGrupo == "M"){
					$sql .= " AND cunidmcodi='".$Unidade."'  ";
				}

				$res  = $db->query($sql);
				if( PEAR::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Qtd = $res->fetchRow();
						if($Qtd[0] > 0){
								$Mens     = 1;
								$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.CadPreMaterialIncluir.DescMaterialServico.focus();\" class=\"titulo2\">Já existe um Pré-Material em análise ou aprovado com mesmo nome e mesma unidade</a>";
						}
				}
				$db->disconnect();
		}

		if($Mens == 0){ //pré-material aprovado para inclusão
			$Situacao = 1; //1 = EM ANÁLISE

			# Inclui na Tabela de Materiais/Serviços #
			$db   = Conexao();
			$db->query("BEGIN TRANSACTION");
			$sql = "SELECT MAX(CPREMACODI) FROM SFPC.TBPREMATERIALSERVICO ";
			$res = $db->query($sql);
			if( PEAR::isError($res) ){
					$CodErroEmail  = $res->getCode();
					$DescErroEmail = $res->getMessage();
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
					$db->query("END TRANSACTION");
					$db->disconnect();
			}else{
					$Linha  = $res->fetchRow();
					if( $Linha[0] == "" ){ $Material = 1; }else{ $Material = $Linha[0] + 1; }
					if( $Unidade == "" || $Unidade == '' ) { $Unidade = "NULL"; }
			}
			$sql  = "INSERT INTO SFPC.TBPREMATERIALSERVICO( ";
			$sql .= "CPREMACODI, CGRUMSCODI, CCLAMSCODI, CUNIDMCODI, ";
			$sql .= "CPREMSCODI, EPREMAOBSE, EPREMADESC, TPREMAULAT, ";
			$sql .= "CGREMPCODI, CUSUPOCODI, DPREMACADA ";
			$sql .= ") VALUES ( ";
			$sql .= "$Material, $Grupo, $Classe, $Unidade, ";
			$sql .= "$Situacao, '".strtoupper2($Observacao)."','".$DescMaterialServico."', '".date("Y-m-d H:i:s")."', ";
			$sql .= " ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", '".date("Y-m-d")."' ) ";
			$res  = $db->query($sql);
			if( PEAR::isError($res) ){
					$CodErroEmail  = $res->getCode();
					$DescErroEmail = $res->getMessage();
					$db->query("ROLLBACK");
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
					$db->query("END TRANSACTION");
					$db->disconnect();
			}else{
					$Tipo         = 1;
					$Mens         = 1;
		//		$Mensagem     = "Inclusão Efetuada com Sucesso. Código do Pré-Cadastro $Material";
					$Mensagem     = "Inclusão Efetuada com Sucesso";
					$TipoMaterial = "";
					$Unidade      = "";
					$Material     = "";
					$Observacao   = "";
					$DescMaterialServico = "";

					$db->query("COMMIT");
					$db->query("END TRANSACTION");
					$db->disconnect();
			}
		}
}
if($Botao == ""){
		$NCaracteres  = strlen($DescMaterialServico);
		$NCaracteresO = strlen($Observacao);
}
?>
<html>
<?php 
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function remeter(){
	document.CadPreMaterialIncluir.submit();
}
function enviar(valor){
	document.CadPreMaterialIncluir.Botao.value=valor;
	document.CadPreMaterialIncluir.submit();
}
function ncaracteres(valor){
	document.CadPreMaterialIncluir.NCaracteres.value = '' +  document.CadPreMaterialIncluir.DescMaterialServico.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadPreMaterialIncluir.NCaracteres.focus();
	}
}
function ncaracteresO(valor){
	document.CadPreMaterialIncluir.NCaracteresO.value = '' +  document.CadPreMaterialIncluir.Observacao.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadPreMaterialIncluir.NCaracteresO.focus();
	}
}
<?php  MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadPreMaterialIncluir.php" method="post" name="CadPreMaterialIncluir">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais/Serv > Pré-Cadastro > Incluir
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php  if($Mens == 1){?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2">
			<?php  if($Mens == 1){ ExibeMens($Mensagem,$Tipo,1); } ?>
		</td>
	</tr>
	<?php  } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="0" cellspacing="0" cellpadding="0" summary="">
							<tr>
								<td class="textonormal">
									<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
										<tr>
											<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="2">
												INCLUIR - PRÉ-CADASTRO DE MATERIAIS/SERVIÇOS
											</td>
										</tr>
										<tr>
											<td class="textonormal" colspan="2">
												<p align="justify">
													Para incluir um novo material/serviço, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
													A equipe responsável pelo cadastramento fará a análise do pré-cadastro do material/serviço, o resultado da análise poderá ser
													visualizada na opção de acompanhamento de pré-cadastro.
												</p>
											</td>
										</tr>
										<tr>
											<td>
												<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td colspan="2">
															<table class="textonormal" border="0" width="100%" summary="">
<?php /*																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Centro de Custo</td>
																	<td class="textonormal">
																		<?php 
																			# Exibe os Centro de Custo #
																			$db     = Conexao();
																			//if( ($_SESSION['_cgrempcodi_'] != 0 ) and ($TipoUsuario == "C")){
																					$sqlCC    = "
																						SELECT
																							A.CCENPOSEQU, A.ECENPODESC, A.CCENPONRPA, A.ECENPODETA, B.CORGLICODI,
																							B.EORGLIDESC
																						FROM
																							SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBUSUARIOCENTROCUSTO UCC
																						WHERE
																							UCC.CUSUPOCODI = ". $_SESSION['_cusupocodi_'] ." AND
																							A.CCENPOSEQU = UCC.CCENPOSEQU AND
																							A.CORGLICODI IS NOT NULL AND
																							A.CORGLICODI = B.CORGLICODI AND
																							A.ACENPOANOE = ".date("Y")." AND
																							A.FCENPOSITU <> 'I'
																						ORDER BY
																							B.EORGLIDESC, A.CCENPONRPA, A.ECENPODESC, A.CCENPOCENT, A.CCENPODETA
																					";
																			$resCC     = $db->query($sqlCC);
																			if( PEAR::isError($resCC) ){
																					$CodErroEmail  = $resCC->getCode();
																					$DescErroEmail = $resCC->getMessage();
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlCC\n\n$DescErroEmail ($CodErroEmail)");
																			}else{
																					$RowsCC = $resCC->numRows();
																					if($RowsCC == 0){
																							echo "Nenhum Centro de Custo cadastrado";
																					}else {
																							$Linha           = $resCC->fetchRow();
																							$CentroCusto     = $Linha[0];
																							$DescCentroCusto = $Linha[1];
																							$RPA             = $Linha[2];
																							$Detalhamento    = $Linha[3];
																							$Orgao           = $Linha[4];
																							$DescOrgao       = $Linha[5];
																							echo $DescOrgao;
																							echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;";
																							echo "RPA ".$RPA;
																							echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																							echo $DescCentroCusto;
																							echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																							echo $Detalhamento;
																					}
																			}
																			$db->disconnect();
																			?>
																	</td>
																</tr>*/?>


																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Tipo de Grupo*</td>
																	<td class="textonormal">
																		<?php 
																		    $db   = Conexao();
																			echo strtoupper2($Descricao);
																		?>
																	</td>
																</tr>


																<?php 

																	//Obtendo informações do grupo
																	$sql  = "SELECT FGRUMSTIPM, EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
																	$sql .= " WHERE FGRUMSTIPO = '$TipoGrupo' AND CGRUMSCODI = $Grupo";
																	$res  = $db->query($sql);
																	if( PEAR::isError($res) ){
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	}else{
																		$Linha = $res->fetchRow();
																		$TipoMaterial   = $Linha[0];
																		$GrupoDescricao = $Linha[1];
																	}

																	//Variáveis dinâmicas para colocar as informações para material ou serviço.
																	if($TipoGrupo == 'M') {
																 ?>

																<tr>
																	<td class="textonormal" maxlength = "3000" bgcolor="#DCEDF7" width="30%" height="20">Tipo de Material*</td>
																	<td class="textonormal">
																		<?php 
																			if( $TipoMaterial == "C" ){ echo "CONSUMO"; }else{ echo "PERMANENTE"; }
																		?>
																	</td>
																</tr>

																<?php  } //Fecha o if($TipoGrupo == 'M')  ?>

																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo* </td>
																	<td class="textonormal"><?php  echo $GrupoDescricao;?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Classe*</td>
																	<td class="textonormal">
																		<?php 
																		$sql  = "SELECT ECLAMSDESC FROM SFPC.TBCLASSEMATERIALSERVICO";
																		$sql .= " WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe";
																		$result = $db->query($sql);
																		if (PEAR::isError($result)) {
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				$Linha = $result->fetchRow();
																				echo $Linha[0];
																		}
																		?>
																	</td>
																</tr>
																 <?php 
																	//Variáveis dinâmicas para colocar as informações para material ou serviço.
																	if($TipoGrupo == 'M') {
																 ?>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7">Unidade de Medida*</td>
																	<td class="textonormal">
																		<select name="Unidade" class="textonormal">
																			<option value="">Selecione uma Unidade de Medida...</option>
																			<?php 
																			$db   = Conexao();
																			$sql  = "SELECT CUNIDMCODI, EUNIDMDESC ";
																			$sql .= "  FROM SFPC.TBUNIDADEDEMEDIDA WHERE EUNIDMDESC IN (".$GLOBALS["PREMATERIAL_UNIDADES"].")";
																			$sql .= " ORDER BY EUNIDMDESC";
																			$result = $db->query($sql);
																			if (PEAR::isError($result)) {
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			}else{
																					while( $Linha = $result->fetchRow() ){
																							$DescUnidade = substr($Linha[1],0,60);
																							if($Linha[0]== $Unidade){
																									echo "<option value=\"$Linha[0]\" selected>$DescUnidade</option>\n";
																							}else{
																									echo "<option value=\"$Linha[0]\">$DescUnidade</option>\n";
																							}
																					}
																			}
																			$db->disconnect();
																			?>
																		</select>
																	</td>
																</tr>
																	<?php  } //Fecha o if($TipoGrupo == 'M')  ?>

																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7"><?php  echo "$Descricao" ?>*</td>
																	<td class="textonormal">



																		<font class="textonormal">máximo de <?php  echo "$QtdeCaracteres" ?> caracteres</font>

																		<input type="text" name="NCaracteres" disabled size="4" value="<?php  echo $NCaracteres ?>" class="textonormal"><br>
																		<textarea name="DescMaterialServico" maxlength="3000"  cols="60" rows="10" size="<?php  echo "$QtdeCaracteres" ?>" OnKeyUp="javascript:ncaracteres(1)" OnBlur="javascript:ncaracteres(0)" OnSelect="javascript:ncaracteres(1)" class="textonormal"><?php  echo stripslashes($DescMaterialServico); ?></textarea>
																	</td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7">Observação</td>
																	<td class="textonormal">
																		<font class="textonormal">máximo de 2000 caracteres</font>
																		<input type="text" name="NCaracteresO" disabled size="3" value="<?php  echo $NCaracteresO ?>" class="textonormal"><br>
																		<textarea name="Observacao" maxlength="2000" cols="60" rows="4" OnKeyUp="javascript:ncaracteresO(1)" OnBlur="javascript:ncaracteresO(0)" OnSelect="javascript:ncaracteresO(1)" class="textonormal"><?php  echo stripslashes($Observacao); ?></textarea>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td colspan="2" align="right">
												<input type="hidden" name="Grupo" value="<?php  echo $Grupo;?>">
												<input type="hidden" name="Classe" value="<?php  echo $Classe;?>">
												<input type="hidden" name="TipoGrupo" value="<?php  echo $TipoGrupo;?>">
												<input type="hidden" name="Subclasse" value="<?php  echo $Subclasse;?>">
												<input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
												<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
												<input type="hidden" name="Botao" value="">
											</td>
										</tr>
									</table>
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
