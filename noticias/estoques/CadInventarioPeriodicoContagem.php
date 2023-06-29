<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadInventarioPeriodicoContagem.php
# Objetivo: Programa de Carga Inicial em Estoque Fixo
# Autor:    Carlos Abreu
# Data:     22/11/2006
#-----------------
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo 
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# Alterado: Eduardo Caldas / Rodrigo Melo
# Data:     04/01/2008 - Alteração para tornar mais rápido o inclusão e remoção de itens.
# Alterado: Rodrigo Melo
# Data:     26/02/2008 - Alteração para mudar a exibição da mensagem "MATERIAL NÃO CADASTRADO NO ALMOXARIFADO" para 
#                                  "MATERIAL NÃO CADASTRADO OU INATIVO NO ALMOXARIFADO" e não permitir que materiais inativos sejam contados
# Alterado: Ariston Cordeiro
# Data:     16/06/2008 - Ao incluir item, agora o campo 'Código reduzido' recebe foco.
#-----------------.
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Aumenta o tempo de espera do servidor web para término de execução da página #
set_time_limit(1200);

# Executa o controle de segurança #
session_start();
Seguranca();

session_unregister('Localizacao');

function MaterialDescricao( $Db, $Localizacao, $CodigoMaterial ){
		$CodigoMaterial = (int)$CodigoMaterial;
		if (!$Localizacao){ $Localizacao = 0; }
		if (!$CodigoMaterial){ $CodigoMaterial = 0; }
		$sql  = "SELECT A.EMATEPDESC, C.EUNIDMSIGL ";
		$sql .= "  FROM SFPC.TBMATERIALPORTAL A, ( ";
		$sql .= "       SELECT CMATEPSEQU ";
		$sql .= "         FROM SFPC.TBARMAZENAMENTOMATERIAL ";
		$sql .= "        WHERE CLOCMACODI = $Localizacao ";
		$sql .= "        UNION ";
		$sql .= "       SELECT CMATEPSEQU ";
		$sql .= "         FROM SFPC.TBINVENTARIOMATERIAL ";
		$sql .= "        WHERE CLOCMACODI = $Localizacao ";
		$sql .= "       ) AS B, SFPC.TBUNIDADEDEMEDIDA AS C ";
		$sql .= " WHERE A.CMATEPSEQU = B.CMATEPSEQU ";
		$sql .= "   AND A.CMATEPSEQU = $CodigoMaterial ";
		$sql .= "   AND A.CUNIDMCODI = C.CUNIDMCODI ";
		$result = $Db->query($sql);
		if( db::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		    $Db->disconnect();
		    exit;
		}else{
				$Rows = $result->numRows();
				if ($Rows>0){
						$Linha = $result->fetchRow();
						return str_replace(array("“","”","”","\r","\n"),array(" "," "," "," "," "),$Linha[0]." (".$Linha[1].")");
				} else {
						return "<b>MATERIAL NÃO CADASTRADO OU INATIVO NO ALMOXARIFADO</b>";
            
				}
		}
}

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$InicioPrograma      = $_POST['InicioPrograma'];
		$Almoxarifado        = $_POST['Almoxarifado'];
		$DescAlmoxarifado    = $_POST['DescAlmoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Localizacao	       = $_POST['Localizacao'];
		$_SESSION['Localizacao'] = $Localizacao;
		$CarregaLocalizacao  = $_POST['CarregaLocalizacao'];
		
		$Responsavel         = $_POST['Responsavel'];
		$Cpf                 = $_POST['Cpf'];
		$Data                = $_POST['Data'];
		$Etapa               = $_POST['Etapa'];
		$Posicao             = $_POST['Posicao'];
    		

		$db = Conexao();
		
		  for ($pos=1;$pos<=$Posicao;$pos++){
				${'dado'.$pos}       = $_POST["dado".$pos];
				${'valor'.$pos}      = $_POST["valor".$pos];
				${'descricao'.$pos} = MaterialDescricao($db,$Localizacao,${'dado'.$pos});
				${'check'.$pos} =  $_POST["check".$pos];
				
		  }
		  
		$db->disconnect();

}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if($Botao == "Limpar"){
		header("location: CadInventarioPeriodicoContagem.php");
		exit;
}elseif($Botao == "CarregaDados" and $Almoxarifado){
		$Mensagem = "Informe: ";
		if(!$Etapa){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoContagem.Etapa.focus();\" class=\"titulo2\">Etapa</a>";
		}
		if(!valida_CPF($Cpf)){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoContagem.Cpf.focus();document.CadInventarioPeriodicoContagem.Cpf.select();\" class=\"titulo2\">CPF Válido</a>";
				$Cpf='';
		}
}elseif($Botao == "Carregar"){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ($Almoxarifado == "") && ($CarregaAlmoxarifado == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Almoxarifado";
		}elseif($Almoxarifado == ""){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoContagem.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') ){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		}elseif($Localizacao == "") {
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoContagem.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		if(!$Responsavel){
				if ( $Mens == 1 ) { $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoContagem.Responsavel.focus();document.CadInventarioPeriodicoContagem.Responsavel.select();\" class=\"titulo2\">Responsável</a>";
		}
		$MaterialCodigo = array();
		$MaterialQuantidade = array();
		for ($pos=1;$pos<=$Posicao;$pos++){
				if(!SoNumeros(${'dado'.$pos})){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoContagem.dado".$pos.".focus();document.CadInventarioPeriodicoContagem.dado".$pos.".select();\" class=\"titulo2\">Código Reduzido (".$pos.")</a>";
				} else {
						if (${'descricao'.$pos}=="<b>MATERIAL NÃO CADASTRADO OU INATIVO NO ALMOXARIFADO</b>"){            
								if ( $Mens == 1 ) { $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoContagem.dado".$pos.".focus();document.CadInventarioPeriodicoContagem.dado".$pos.".select();\" class=\"titulo2\">Código Reduzido Válido (".$pos.")</a>";
						} else {
								if (in_array(${'dado'.$pos},$MaterialCodigo)){
										if ( $Mens == 1 ) { $Mensagem .= ", "; }
										$Mens      = 1;
										$Tipo      = 2;
										$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoContagem.dado".$pos.".focus();document.CadInventarioPeriodicoContagem.dado".$pos.".select();\" class=\"titulo2\">Código Reduzido Não Repetido (".$pos.")</a>";
								} else {
										$MaterialCodigo[]=${'dado'.$pos};
										$MaterialQuantidade[]=${'valor'.$pos};
								}
						}
				}
				if(!SoNumVirg(${'valor'.$pos})){
						if ( $Mens == 1 ) { $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoContagem.valor".$pos.".focus();document.CadInventarioPeriodicoContagem.valor".$pos.".select();\" class=\"titulo2\">Quantidade (".$pos.")</a>";
				}
		}
		
		if ($Mens==0){
				$db   = Conexao();
				$sql  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU ";
				$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM A ";
				$sql .= " WHERE A.CLOCMACODI=$Localizacao  ";
				$sql .= "   AND A.FINVCOFECH IS NULL ";
				$sql .= "   AND A.AINVCOANOB=(";
				$sql .= "       SELECT MAX(AINVCOANOB) ";
				$sql .= "         FROM SFPC.TBINVENTARIOCONTAGEM ";
				$sql .= "        WHERE CLOCMACODI=$Localizacao";
				$sql .= "       ) ";
				$sql .= " GROUP BY A.AINVCOANOB";
				$res  = $db->query($sql);
				if(db::isError($res)){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Rows = $res->numRows();
						if( $Rows != 0 ){
								$Linha = $res->fetchRow();
						}
						$Ano        = $Linha[0];
						if (!$Ano){$Ano=date("Y");}
						$Sequencial = $Linha[1];
						$sql  = "SELECT CMATEPSEQU, CINVPOCCPF ";
						$sql .= "  FROM SFPC.TBINVENTARIOREGISTRO ";
						$sql .= " WHERE FINVREETAP = $Etapa";
						$sql .= "   AND CLOCMACODI = $Localizacao";
						$sql .= "   AND AINVCOANOB = $Ano";
						$sql .= "   AND AINVCOSEQU = $Sequencial";
						$sql .= "   AND CINVPOCCPF <> '$Cpf'";
						$res  = $db->query($sql);
						if(db::isError($res)){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Rows = $res->numRows();
								if( $Rows != 0 ){
										while ($Linha = $res->fetchRow()){
												if ( in_array($Linha[0],$MaterialCodigo) ){
														$pos = array_search($Linha[0],$MaterialCodigo)+1;
														if ( $Mens == 1 ) { $Mensagem .= ", "; }
														$Mensagem .= "<a href=\"javascript:document.CadInventarioPeriodicoContagem.dado".$pos.".focus();document.CadInventarioPeriodicoContagem.dado".$pos.".select();\" class=\"titulo2\">Código Reduzido (".$pos.") já digitado pelo Responsável do CPF número ".FormataCPF($Linha[1])."</a>";
														$Mens      = 1;
														$Tipo      = 2;
												}
										}
										
								}
						}
						if ($Mens==0)	{
								# Armazena ou Atualiza Dados Inventariante
								$sql  = "SELECT CINVPOCCPF ";
								$sql .= "  FROM SFPC.TBINVENTARIANTEPORTAL ";
								$sql .= " WHERE CINVPOCCPF = '$Cpf'";
								$res  = $db->query($sql);
								if(db::isError($res)){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Rows = $res->numRows();
										$datahora = date('Y-m-d H:i:s');
										if($Rows == 0){
												$sql  = "INSERT INTO SFPC.TBINVENTARIANTEPORTAL ( CINVPOCCPF, NINVPONOME, TINVPOULAT ) ";
												$sql .= "VALUES ('$Cpf', '".strtoupper2($Responsavel)."', '$datahora')";
												$res  = $db->query($sql);
												if(db::isError($res)){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														exit;
												}
										} else {
												$sql  = "UPDATE SFPC.TBINVENTARIANTEPORTAL ";
												$sql .= "   SET NINVPONOME = '".strtoupper2($Responsavel)."', TINVPOULAT = '$datahora' ";
												$sql .= " WHERE CINVPOCCPF='$Cpf'";
												$res  = $db->query($sql);
												if(db::isError($res)){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														exit;
												}
										}
								}
								
								$sql  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU ";
								$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM A ";
								$sql .= " WHERE A.CLOCMACODI=$Localizacao ";
								$sql .= "   AND A.FINVCOFECH IS NULL ";
								$sql .= "   AND A.AINVCOANOB=(";
								$sql .= "       SELECT MAX(AINVCOANOB) ";
								$sql .= "         FROM SFPC.TBINVENTARIOCONTAGEM ";
								$sql .= "        WHERE CLOCMACODI=$Localizacao";
								$sql .= "       ) ";
								$sql .= " GROUP BY A.AINVCOANOB";
								$res  = $db->query($sql);
								if(db::isError($res)){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$Rows = $res->numRows();
										if( $Rows != 0 ){
												$Linha = $res->fetchRow();
										}
										$Ano        = $Linha[0];
										if (!$Ano){$Ano=date("Y");}
										$Sequencial = $Linha[1];
										
										$db->query("BEGIN");
										$sql  = "SELECT CMATEPSEQU, AINVREQTDE ";
										$sql .= "  FROM SFPC.TBINVENTARIOREGISTRO ";
										$sql .= " WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU, FINVREETAP) = ($Localizacao, $Ano, $Sequencial, $Etapa)";
										$res  = $db->query($sql);
										if(db::isError($res)){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												$db->query("ROLLBACK");
												$db->query("END");
												exit;
										}
										$Rows = $res->numRows();
										$MaterialAnterior = array();
										$QuantidadeAnterior = array();
										if( $Rows > 0 ){
												while ($Linha = $res->fetchRow()){
														$MaterialAnterior[] = $Linha[0];
														$QuantidadeAnterior[] = $Linha[1];
												}
										}
										
										$sql  = "DELETE ";
										$sql .= "  FROM SFPC.TBINVENTARIOREGISTRO";
										$sql .= " WHERE CLOCMACODI = $Localizacao";
										$sql .= "   AND AINVCOANOB = $Ano";
										$sql .= "   AND AINVCOSEQU = $Sequencial";
										$sql .= "   AND CINVPOCCPF = '$Cpf'";
										$sql .= "   AND FINVREETAP = $Etapa";
										$res  = $db->query($sql);
										if(db::isError($res)){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												$db->query("ROLLBACK");
												$db->query("END");
												exit;
										}
										foreach ($MaterialCodigo as $Chave => $Codigo){
												if (in_array($Codigo, $MaterialAnterior)){
														if ( $QuantidadeAnterior[array_search($Codigo, $MaterialAnterior)] != str_replace(",",".",$MaterialQuantidade[$Chave]) ){
																$sql  = "SELECT * ";
																$sql .= "  FROM SFPC.TBINVENTARIOMATERIAL ";
																$sql .= " WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU, CMATEPSEQU) = ($Localizacao, $Ano, $Sequencial, $Codigo)";
																$resMat  = $db->query($sql);
																if(db::isError($resMat)){
																		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		$db->query("ROLLBACK");
																		$db->query("END");
																		exit;
																} else {
																		$RowsMat = $resMat->numRows();
																		if( $RowsMat >= 0 ){
																				$sql  = "UPDATE SFPC.TBINVENTARIOMATERIAL ";
																				$sql .= "   SET AINVMAESTO = NULL ";
																				$sql .= " WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU, CMATEPSEQU) = ($Localizacao, $Ano, $Sequencial, $Codigo) ";
																				$resMat  = $db->query($sql);
																				if(db::isError($resMat)){
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																						$db->query("ROLLBACK");
																						$db->query("END");
																						exit;
																				}
																		}
																}
														}
												}
												
												$sql  = "INSERT INTO SFPC.TBINVENTARIOREGISTRO ";
												$sql .= "       (CLOCMACODI, AINVCOANOB, AINVCOSEQU, CINVPOCCPF, CMATEPSEQU, ";
												$sql .= "        FINVREETAP, AINVREQTDE, TINVREULAT, CGREMPCODI, CUSUPOCODI) ";
												$sql .= "VALUES ";
												$sql .= "       ($Localizacao, $Ano, $Sequencial, '$Cpf', $Codigo, ";
												$sql .= "        '$Etapa', ".str_replace(",",".",$MaterialQuantidade[$Chave]).", '$datahora', ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].")";
												$res  = $db->query($sql);
												if(db::isError($res)){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														$db->query("ROLLBACK");
														$db->query("END");
														exit;
												}
										}
										$db->query("COMMIT");
										$db->query("END");
										$Mens     = 1;
										$Tipo     = 1;
										$Mensagem = "Dados Registrados com Sucesso";
										$Cpf = '';
								}
						}
				}
				$db->disconnect();
		}
  if ($Mens == 1) {
    $Botao = "CarregarDados1";
    
  }                                  
  		
} elseif($Botao=="") { // Evita que a etapa de carregamento quando o usuario aperta tecla ENTER nao seja executada
		$Cpf = "";
}

?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script type="text/javascript" language="javascript" src="ajax/ajax.js"></script>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadInventarioPeriodicoContagem.Botao.value = valor;
	document.CadInventarioPeriodicoContagem.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
}
function AbreJanelaItem(url,largura,altura) {
	if( ! document.CadInventarioPeriodicoContagem.Almoxarifado.value ){
		document.CadInventarioPeriodicoContagem.submit();
	}else	if( ! document.CadInventarioPeriodicoContagem.Localizacao.value ){
		document.CadInventarioPeriodicoContagem.submit();
	}else{
		window.open(url,'item','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
	}
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadInventarioPeriodicoContagem.php" method="post" name="CadInventarioPeriodicoContagem" id="CadInventarioPeriodicoContagem">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Inventário > Periódico > Contagem/Recontagem
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr id="MensagemErro">
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									INVENTÁRIO PERIÓDICO - CONTAGEM/RECONTAGEM
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										<?
										if (!$Etapa or !$Cpf){
												echo "Para iniciar o processo de Contagem/Recontagem informe os dados abaixo e clique no botão \"Avançar\". Os itens obrigatórios estão com *.";
										} else {
												echo "Informe o nome do Responsável pelo CPF, os Códigos Reduzidos dos Materiais e suas respectivas Quantidades. Para incluir novo item clique no botão \"Incluir Item\". Para remover 01 ou mais itens marque a(s) caixa(s) correspondente(s) ao lado da descrição do material e clique no botão \"Remover\". Para concluir clique no botão \"Salvar\". Para reiniciar o processo clique no botão \"Limpar\". Os itens obrigatórios estão com *.";
										}
										?>
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado*</td>
											<td class="textonormal">
												<?php
												# Mostra o(s) Almoxarifado(s) de Acordo com o Usuário Logado #
												$db   = Conexao();
												if(($_SESSION['_cgrempcodi_'] == 0) or ($_SESSION['_fperficorp_'] == 'S')){
														$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A ";
														$sql .= " WHERE A.FALMPOSITU = 'A'";
														$sql .= "   AND A.FALMPOINVE = 'S'";
												} else {
														$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBALMOXARIFADOORGAO B ";
														$sql .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
														$sql .= "   AND A.FALMPOSITU = 'A'";
														$sql .= "   AND A.FALMPOINVE = 'S'";
														$sql .= "   AND B.CORGLICODI IN ";
														$sql .= "       ( SELECT DISTINCT CEN.CORGLICODI ";
														$sql .= "           FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
														$sql .= "          WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.FUSUCCTIPO IN ('T','R') ";
														$sql .= "            AND USU.CUSUPOCODI = ".$_SESSION['_cusupocodi_']." ";
														$sql .= "            AND CEN.FCENPOSITU <> 'I' ";
														
														# restringir almoxarifado quando requisitante
														$sql .= "            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END";
														
														$sql .= "       ) ";
														$sql .= "   AND A.CALMPOCODI NOT IN ";
														$sql .= "       ( SELECT CALMPOCODI ";
														$sql .= "           FROM SFPC.TBMOVIMENTACAOMATERIAL ";
														$sql .= "          GROUP BY CALMPOCODI ";
														$sql .= "         HAVING COUNT(*) = 0)";
												}
												$sql .= " ORDER BY A.EALMPODESC ";
												$res  = $db->query($sql);
												if(db::isError($res)){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Rows = $res->numRows();
														if($Rows == 1){
																$Linha = $res->fetchRow();
																$Almoxarifado = $Linha[0];
																echo "$Linha[1]<br>";
																echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
																echo "<input type=\"hidden\" name=\"DescAlmoxarifado\" value=\"$DescAlmoxarifado\">";
																echo $DescAlmoxarifado;
														}elseif($Rows > 1){
																echo "<select name=\"Almoxarifado\" class=\"textonormal\" onChange=\"javascript:enviar('TrocaAlmoxarifado');\">\n";
																echo "	<option value=\"\">Selecione um Almoxarifado...</option>\n";
																for($i=0;$i< $Rows; $i++){
																		$Linha = $res->fetchRow();
																		$DescAlmoxarifado = $Linha[1];
																		if($Linha[0] == $Almoxarifado){
																				echo"<option value=\"$Linha[0]\" selected>$DescAlmoxarifado</option>\n";
																		}else{
																				echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
																		}
																}
																echo "</select>\n";
																$CarregaAlmoxarifado = "";
																echo "<input type=\"hidden\" name=\"DescAlmoxarifado\" value=\"\">\n";
														}else{
																echo "NENHUM ALMOXARIFADO DISPONÍVEL";
																echo "<input type=\"hidden\" name=\"CarregaAlmoxarifado\" value=\"N\">";
																echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
														}
												}
												$db->disconnect();
												?>
												<input type="hidden" name="DefineAlmoxarifado" value="<?php echo $DefineAlmoxarifado; ?>">
											</td>
										</tr>
										<?php if( $Almoxarifado != "" ){ ?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização*</td>
											<td class="textonormal">
												<?php
												$db = Conexao();
												if($Localizacao != ""){
														# Mostra a Descrição de Acordo com o Almoxarifado #
														$sql    = "SELECT A.FLOCMAEQUI, A.ALOCMANEQU, A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B";
														$sql   .= " WHERE A.CLOCMACODI = $Localizacao AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$res  = $db->query($sql);
														if(db::isError($res)){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Linha = $res->fetchRow();
																if($Linha[0] == "E"){
																		$Equipamento = "ESTANTE";
																}if($Linha[0] == "A"){
																		$Equipamento = "ARMÁRIO";
																}if($Linha[0] == "P"){
																		$Equipamento = "PALETE";
																}
																$DescArea = $Linha[4];
																echo "ÁREA: $DescArea - $Equipamento - $Linha[1]: ESCANINHO $Linha[2]$Linha[3]";
																echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
														}
												}else{
														# Mostra as Localizações de acordo com o Almoxarifado #
														$sql    = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
														$sql   .= " WHERE A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql   .= "   AND A.CALMPOCODI = $Almoxarifado ";
														$sql   .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
														$res  = $db->query($sql);
														if(db::isError($res)){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Rows = $res->numRows();
																if($Rows == 0){
																		echo "NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO";
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																}elseif($Rows == 1){
																		$Linha = $res->fetchRow();
																		if($Linha[1] == "E"){
																				$Equipamento = "ESTANTE";
																		}if($Linha[1] == "A"){
																				$Equipamento = "ARMÁRIO";
																		}if($Linha[1] == "P"){
																				$Equipamento = "PALETE";
																		}
																		$DescArea = $Linha[5];
																		$Localizacao = $Linha[0];
																		echo "ÁREA: $DescArea - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																		echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																} else {
																		echo "<select name=\"Localizacao\" class=\"textonormal\" onChange=\"submit();\">\n";
																		echo "	<option value=\"\">Selecione uma Localização...</option>\n";
																		$EquipamentoAntes = "";
																		$DescAreaAntes    = "";
																		for($i=0;$i< $Rows; $i++){
																				$Linha = $res->fetchRow();
																				$CodEquipamento = $Linha[2];
																				if($Linha[1] == "E"){
																						$Equipamento = "ESTANTE";
																				}if($Linha[1] == "A"){
																						$Equipamento = "ARMÁRIO";
																				}if($Linha[1] == "P"){
																						$Equipamento = "PALETE";
																				}
																				$NumeroEquip = $Linha[2];
																				$Prateleira  = $Linha[3];
																				$Coluna      = $Linha[4];
																				$DescArea    = $Linha[5];
																				if( $DescAreaAntes != $DescArea ){
																						echo"<option value=\"\">$DescArea</option>\n";
																						$Edentecao = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																				}
																				if( $CodEquipamentoAntes != $CodEquipamento or $EquipamentoAntes != $Equipamento ){
																						echo"<option value=\"\">$Edentecao $Equipamento - $NumeroEquip</option>\n";
																				}
																				if( $Localizacao == $Linha[0] ){
																						echo"<option value=\"$Linha[0]\" selected>$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
																				}else{
																						echo"<option value=\"$Linha[0]\">$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
																				}
																				$DescAreaAntes       = $DescArea;
																				$CodEquipamentoAntes = $CodEquipamento;
																				$EquipamentoAntes    = $Equipamento;
																		}
																		echo "</select>\n";
																		$CarregaLocalizacao = "";
																}
														}
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<?
										if ($Localizacao){
												if (!$Etapa or !$Cpf){
												?>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" width="30%">Etapa*</td>
												<td class="textonormal">
													<select name="Etapa" class="textonormal">
														<option value="" selected>Selecione uma Etapa...</option>
														<option value="1" <?php if( $Etapa == "1" ){ echo "selected"; }?>>CONTAGEM</option>
														<option value="2" <?php if( $Etapa == "2" ){ echo "selected"; }?>>RECONTAGEM</option>
													</select>
												</td>
											</tr>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" width="30%">CPF*</td>
												<td class="textonormal">
													<input type="text" class="textonormal" name="Cpf" id="Cpf" value="<?=$Cpf;?>" size="11" maxlength="11" onblur="enviar('CarregaDados');">
													<input type="hidden" class="textonormal" name="Responsavel" id="Responsavel">
												</td>
											</tr>
												<?
												} else {
												?>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" width="30%">Etapa*</td>
												<td class="textonormal">
													<?
													switch ($Etapa){
															case 1:
																	echo "CONTAGEM<input name=\"Etapa\" type=\"hidden\" value=\"1\">";
																	break;
															case 2:
																	echo "RECONTAGEM<input name=\"Etapa\" type=\"hidden\" value=\"2\">";
																	break;
													}
													?>
												</td>
											</tr>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7" width="30%">CPF* - Nome do Responsável*</td>
												<td class="textonormal">
													<?
													echo "$Cpf - <input type=\"hidden\" name=\"Cpf\" value=\"$Cpf\">";
													if (( valida_CPF($Cpf) && $Botao=='CarregaDados')){
															$db   = Conexao();
															$sql  = "SELECT NINVPONOME ";
															$sql .= "  FROM SFPC.TBINVENTARIANTEPORTAL ";
															$sql .= " WHERE CINVPOCCPF = '$Cpf'";
															$res  = $db->query($sql);
															if(db::isError($res)){
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															} else {
																	$Linha = $res->fetchRow();
																	$Responsavel = strtoupper2($Linha[0]);
															}
															$sql  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU ";
															$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM A ";
															$sql .= " WHERE A.CLOCMACODI=$Localizacao ";
															$sql .= "   AND A.FINVCOFECH IS NULL ";
															$sql .= "   AND A.AINVCOANOB=(";
															$sql .= "       SELECT MAX(AINVCOANOB) ";
															$sql .= "         FROM SFPC.TBINVENTARIOCONTAGEM ";
															$sql .= "        WHERE CLOCMACODI=$Localizacao";
															$sql .= "       ) ";
															$sql .= " GROUP BY A.AINVCOANOB";
															$res  = $db->query($sql);
															
													}
													?>
													<input type="text" class="textonormal" name="Responsavel" id="Responsavel" value="<?=$Responsavel;?>" size="40" maxlength="60">
												</td>
											</tr>
											<tr>
					        			<td colspan="2">
													<br>
													
													<table border="1" cellpadding="3" cellspacing="0" width="100%" bordercolor="#75ADE6">
														<tr>
															<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">
																MATERIAIS EM ESTOQUE
															</td>
														</tr>
														<tr>
															<td>
																<table width=100%>
																	<tr>
																		<td>
																			<table width="100%" cellpadding="3" cellspacing="1">
																				<tr bgcolor="#bfdaf2" bordercolor="#75ADE6">
																					<td width="5%" class="textoabason">ORD</td>
																					<td width="25%" class="textoabason">CÓDIGO REDUZIDO</td>
																					<td width="25%" class="textoabason">QUANTIDADE</td>
																					<td width="45%" class="textoabason">DESCRIÇÃO DO MATERIAL (UNIDADE DE MEDIDA)</td>
																				</tr>
																			</table>
															
															 				
															<?				
															if (( valida_CPF($Cpf) && $Botao=='CarregaDados')){
															if(db::isError($res)){
																	ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
															}else{
																	$Rows = $res->numRows();
																	if( $Rows != 0 ){
																			$Linha = $res->fetchRow();
																	}
																	$Ano        = $Linha[0];
																	if (!$Ano){$Ano=date("Y");}
																	$Sequencial = $Linha[1];
															
																	$sql  = "SELECT A.CMATEPSEQU, A.AINVREQTDE, B.EMATEPDESC, C.EUNIDMSIGL ";
																	$sql .= "  FROM SFPC.TBINVENTARIOREGISTRO A, SFPC.TBMATERIALPORTAL B, SFPC.TBUNIDADEDEMEDIDA C ";
																	$sql .= " WHERE A.CLOCMACODI = $Localizacao ";
																	$sql .= "   AND A.AINVCOANOB = $Ano ";
																	$sql .= "   AND A.AINVCOSEQU = $Sequencial ";
																	$sql .= "   AND A.CINVPOCCPF = '$Cpf' ";
																	$sql .= "   AND A.FINVREETAP = $Etapa ";
																	$sql .= "   AND A.CMATEPSEQU = B.CMATEPSEQU ";
																	$sql .= "   AND B.CUNIDMCODI = C.CUNIDMCODI ";
																	$sql .= " ORDER BY B.EMATEPDESC";
																	$res  = $db->query($sql);
                                  
                                  
																	if(db::isError($res)){
																			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																	}else{
																			$Rows = $res->numRows();
																			$Posicao = $Rows;
																			if( $Rows != 0 ){
																					for($Row=1;$Row<=$Rows;$Row++){
																							$Linha = $res->fetchRow();
																							${'dado'.$Row} = $Linha[0];
																							${'valor'.$Row} = str_replace(".",",",$Linha[1]);
                                              												${'descricao'.$Row} = str_replace(array("“","”","”","\r","\n"),array(" "," "," "," "," "),$Linha[2]." (".$Linha[3].")");;
                                              ?>
					     <table id="<?php echo "tabela".$Row;?>" border="0" class="textonormal" cellpadding="3" cellspacing="1" width="100%">
                                               <tr bgcolor="DDECF9">
                                                 <td width="5%" align="right">
                                                   <?php echo $Row; ?>
                                                 </td>
                                                 <td width="25%">
                                                   <input type="text" class="textonormal" id="<?php echo "dado".$Row;?>" name="<?php echo "dado".$Row;?>" value="<?php echo ${'dado'.$Row} ?>" size="14" onBlur="javascript:makeRequest('ajax/AjaxDescricaoMaterial.php', '?Codigo='+document.getElementById('CadInventarioPeriodicoContagem').dado<?echo $Row; ?>.value, 'descricao<?echo $Row; ?>');">
                                                 </td>
                                                 <td width="25%">
                                                   <input type="text" class="textonormal" name="<?php echo "valor".$Row;?>" id="<?php echo "valor".$Row;?>" size="14" value="<?php echo ${'valor'.$Row}?>">
                                                 </td>
                                                 <td width="45%">
                                                   <input type="checkbox" class="textonormal" name="<?php echo "check".$Row;?>">
                                                   <span name="<?php echo "descricao".$Row;?>" id="<?php echo "descricao".$Row;?>"  > 
                                                     <?php echo ${'descricao'.$Row}; ?>
                                                   </span>
                                                 </td>
                                               </tr>
                                             </table>
                                             
                                             <?php 
   					  }
                                          $numlinha = $Row -1;
                                          echo "<input type=\"hidden\" name=\"Posicao\" id=\"Posicao\" value=\"$numlinha\">"; 
                                        } else {
                                          echo "<input type=\"hidden\" name=\"Posicao\" id=\"Posicao\" value=\"0\">"; 	
                                          }
																	}
															
														}
														
					  $db->disconnect();
					}
					  $Row= 0;
					  if (($Botao=="CarregarDados1") or ($Botao=="Excluir")){
					     for ($pos=1;$pos<=$Posicao;$pos++){
					       if ($Botao=="CarregarDados1") {
					          $Row = $pos;	
					       ?>	
					     <table id="<?php echo "tabela".$Row;?>" border="0" class="textonormal" cellpadding="3" cellspacing="1" width="100%">
                                               <tr bgcolor="DDECF9">
                                                 <td width="5%" align="right">
                                                   <?php echo $Row; ?>
                                                 </td>
                                                 <td width="25%">
                                                   <input type="text" class="textonormal" id="<?php echo "dado".$Row;?>" name="<?php echo "dado".$Row;?>" value="<?php echo ${'dado'.$Row} ?>" size="14" onBlur="javascript:makeRequest('ajax/AjaxDescricaoMaterial.php', '?Codigo='+document.getElementById('CadInventarioPeriodicoContagem').dado<?echo $Row; ?>.value, 'descricao<?echo $Row; ?>');">
                                                 </td>
                                                 <td width="25%">
                                                   <input type="text" class="textonormal" name="<?php echo "valor".$Row;?>" id="<?php echo "valor".$Row;?>" size="14" value="<?php echo ${'valor'.$Row}?>">
                                                 </td>
                                                 <td width="45%">
                                                   <input type="checkbox" class="textonormal" name="<?php echo "check".$Row;?>">
                                                   <span name="<?php echo "descricao".$Row;?>" id="<?php echo "descricao".$Row;?>"  > 
                                                     <?php echo ${'descricao'.$Row}; ?>
                                                   </span>
                                                 </td>
                                               </tr>
                                             </table>					       
				             <?php 
					       }else if (($Botao=="Excluir") and (${'check'.$pos}!="on") ){
					       	$Row++; 
					       	
					       	?>	
					       	
					      <table id="<?php echo "tabela".$Row;?>" border="0" class="textonormal" cellpadding="3" cellspacing="1" width="100%">
                                               <tr bgcolor="DDECF9">
                                                 <td width="5%" align="right">
                                                   <?php echo $Row; ?>
                                                 </td>
                                                 <td width="25%">
                                                   <input type="text" class="textonormal" id="<?php echo "dado".$Row;?>" name="<?php echo "dado".$Row;?>" value="<?php echo ${'dado'.$pos} ?>" size="14" onBlur="javascript:makeRequest('ajax/AjaxDescricaoMaterial.php', '?Codigo='+document.getElementById('CadInventarioPeriodicoContagem').dado<?echo $pos; ?>.value, 'descricao<?echo $pos; ?>');">
                                                 </td>
                                                 <td width="25%">
                                                   <input type="text" class="textonormal" name="<?php echo "valor".$Row;?>" id="<?php echo "valor".$Row;?>" size="14" value="<?php echo ${'valor'.$pos}?>">
                                                 </td>
                                                 <td width="45%">
                                                   <input type="checkbox" class="textonormal" name="<?php echo "check".$Row;?>">
                                                   <span name="<?php echo "descricao".$Row;?>" id="<?php echo "descricao".$Row;?>"  > 
                                                     <?php echo ${'descricao'.$pos}; ?>
                                                   </span>
                                                 </td>
                                               </tr>
                                             </table>					       
				             <?php  
				              
					}	
					}
					$numlinha = $Row;
					echo "<input type=\"hidden\" name=\"Posicao\" id=\"Posicao\" value=\"$numlinha\">"; 
					}
					  ?><div id="dados"></div>
 					<table width="100%" cellpadding="3" cellspacing="1">
																				<tr bgcolor="#bfdaf2" bordercolor="#75ADE6">
																					<td colspan="5" align="center">
																						<input type="button" class="botao" value="Adicionar Item" onclick="incluir();">
																						<input type="button" class="botao" value="Remover" onclick="javascript:enviar('Excluir');">
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
										<?php }// Etapa ?>
										<?php }// Localizacao ?>
										<?php } // Almoxarifado ?>
	           			</table>
	           		</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
               		<input type="hidden" name="InicioPrograma" value="1">
               		<?php if ($Etapa and $Cpf){ ?>
			  	      	<input type="button" name="Carregar" value="Salvar" class="botao" onClick="javascript:enviar('Carregar');">
			  	      	<?php } else { ?>
			  	      	<input type="button" name="Carregar" value="Avançar" class="botao" onClick="javascript:enviar('CarregaDados');">
			  	      	<?}?>
			  	      	<input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar');">
         	      	<input type="hidden" name="Botao" value="">
         	      	
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
<?php if ($Etapa and $Cpf){ ?>
<script language="javascript">
novosdados = '';
guardados = Array();
descricao=Array();
contador = 0;
cont = 0;
function incluir(){
	  if (cont == 0){
	    contador = ((document.getElementById('Posicao').value * 3) + 8);
	    cont++;
	  }
	  guardadados();
	  document.getElementById('Posicao').value++;
		inputName='dado'+document.getElementById('Posicao').value;
	  document.getElementById('dados').innerHTML+='<table border="0" class="textonormal" cellpadding="3" cellspacing="1" width="100%"><tr bgcolor="DDECF9"><td width="5%" align="right">'+document.getElementById('Posicao').value+'</td><td width="25%"><input type="text" class="textonormal" name="'+inputName+'" id="'+inputName+'" size="14" onBlur="javascript:makeRequest(\'ajax/AjaxDescricaoMaterial.php\', \'?Codigo=\'+document.CadInventarioPeriodicoContagem.dado'+document.getElementById('Posicao').value+'.value, \'descricao'+document.getElementById('Posicao').value+'\');"></td><td width="25%"><input type="text" class="textonormal" name="valor'+document.getElementById('Posicao').value+'" size="14"></td><td width="45%"><input type="checkbox" class="textonormal" name="check'+document.getElementById('Posicao').value+'"><span name="descricao'+document.getElementById('Posicao').value+'" id="descricao'+document.getElementById('Posicao').value+'"></span></td></tr></table>';	
		document.getElementById(inputName).focus();
	  carregadados();
}
function guardadados(){
        for (a=contador;a<document.getElementById('CadInventarioPeriodicoContagem').elements.length-7;a++){
        	guardados[a]=document.getElementById('CadInventarioPeriodicoContagem').elements[a].value;
	}
}

function carregadados(){
	for (a=contador;a<document.getElementById('CadInventarioPeriodicoContagem').elements.length-10;a++){
		if (guardados[a]){
			document.getElementById('CadInventarioPeriodicoContagem').elements[a].value = guardados[a];
		}
	}
}


</script>
<?php } ?>
</html>
