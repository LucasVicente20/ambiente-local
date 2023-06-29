<?php
#----------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMovimentacaoAlterar.php
# Autor:    Álvaro Faria
# Objetivo: Alteração da movimentação de entrada e saída dos itens estocados
# OBS.:     Tabulação 2 espaços
# Data:     06/10/2005
# Alterado: Álvaro Faria
# Data:     24/10/2005
# Alterado: Álvaro Faria
# Data:     26/05/2006 - Suporte as novas movimentações
# Alterado: Álvaro Faria
# Data:     01/08/2006 - Custo para acerto de inventário e correções diversas de querys,
#           principalmente com relação a flag de inatividade da tabela de movimentação.
#           Implantação do Centro de Custo Patrimônio
# Alterado: Álvaro Faria
# Data:     09/10/2006 - Correção da manutenção da devolução de empréstimo que permitia
#                        alterar devolução além do empréstimo e também não resetava a
#                        flag ao alterar a movimentação para menos
# Alterado: Álvaro Faria
# Data:     24/11/2006 - Suporte ao include da rotina de Custo/Contabilidade
# Alterado: Álvaro Faria
# Data:     14/12/2006 - Alteração para pegar a RPA da tabela de Almoxarifado, não mais na tabela de CC
# Alterado: Álvaro Faria
# Data:     03/01/2007 - Suporte a materiais didáticos, fardamento e limpeza
# Alterado: Álvaro Faria
# Data:     05/01/2007 - Não alteração do campo DMOVMAMOVI ao atualizar uma movimentação
# Alterado: Carlos Abreu
# Data:     15/01/2007 - Ajuste para trabalhar apenas com inserts na tabela de movimentacao material
#                        Apresentar todas as movimentações de alteração relacionadas (35,36) com a movimentação apresentada
# Alterado: Rodrigo Melo
# Data:     10/01/2008 - Ajuste na query para evitar que a movimentação seja realizada no período anterior ao último inventário
#                                do almoxarifado, ou seja, ajuste para buscar apenas o último sequencial e o último ano do inventário do almoxarifado.
# Alterado: Ariston Cordeiro
# Data:     06/04/2009 - Nova movimentação: "saída por processo administrativo" (37)
#----------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao            = $_POST['Botao'];
		$Sequencial       = $_POST['Sequencial'];
		$AnoMovimentacao  = $_POST['AnoMovimentacao'];
		$Material         = $_POST['Material'];
		$TipoMovimentacao = $_POST['TipoMovimentacao'];
		$Almoxarifado     = $_POST['Almoxarifado'];
		$Localizacao      = $_POST['Localizacao'];
		$Movimentacao     = $_POST['Movimentacao'];
		$DescMovimentacao = $_POST['DescMovimentacao'];
		$QtdOriginal      = $_POST['QtdOriginal'];
		$QtdMovimentada   = $_POST['QtdMovimentada'];
		$QtdAtual         = sprintf("%01.2f",str_replace(",",".",$_POST['QtdAtual']));
		$Valor            = $_POST['Valor'];
		$UnidSigl         = $_POST['UnidSigl'];
		$DescMaterial     = $_POST['DescMaterial'];
		$MovNumero        = $_POST['MovNumero'];
		$Matricula        = $_POST['Matricula'];
		$Responsavel      = RetiraAcentos(strtoupper2(trim($_POST['Responsavel'])));
		$Observacao       = RetiraAcentos(strtoupper2(trim($_POST['Observacao'])));
		$NCaracteresO     = $_POST['NCaracteresO'];
		$SeqRequisicao    = $_POST['SeqRequisicao'];
		$Situacao         = $_POST['Situacao'];
}else{
		$Material         = $_GET['CodigoReduzido'];
		$TipoMovimentacao = $_GET['TipoMovimentacao'];
		$Localizacao      = $_GET['Localizacao'];
		$Movimentacao     = $_GET['Movimentacao'];
		$Sequencial       = $_GET['Sequencial'];
		$AnoMovimentacao  = $_GET['AnoMovimentacao'];
		$Almoxarifado     = $_GET['Almoxarifado'];
		$Mens             = $_GET['Mens'];
		$Tipo             = $_GET['Tipo'];
		$Mensagem         = $_GET['Mensagem'];
}

$ProgramaDestino = "CadMovimentacaoAlterar.php";

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# DAta da Movimentação Atual #
$DataMovimentacao = date("Y-m-d");

# Grupo e Código do Usuário #
$GrupoEmp = $_SESSION['_cgrempcodi_'];
$Usuario  = $_SESSION['_cusupocodi_'];
$Det      = 77;

if($Botao == null){
		# Resgata os dados da Movimentação #
		$db     = Conexao();
		$sql    = "SELECT TIP.FTIPMVTIPO, MOV.CTIPMVCODI, TIP.ETIPMVDESC, MAT.EMATEPDESC, ";
		$sql   .= "       UND.EUNIDMSIGL, MOV.AMOVMAQTDM, MOV.VMOVMAVALO, MOV.CMATEPSEQU, ";
		$sql   .= "       MOV.CMOVMACODI, MOV.CMOVMACODT, MOV.AMOVMAMATR, MOV.NMOVMARESP, ";
		$sql   .= "       MOV.CREQMASEQU, MOV.EMOVMAOBSE, MOVALT.AMOVMAQTDM ";
		$sql   .= "  FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBMOVIMENTACAOMATERIAL MOV, ";
		$sql   .= "            ( SELECT SUM(CASE WHEN CTIPMVCODI = 35 THEN AMOVMAQTDM ELSE -AMOVMAQTDM END) AS AMOVMAQTDM ";
		$sql   .= "                FROM SFPC.TBMOVIMENTACAOMATERIAL ";
		$sql   .= "               WHERE CALMPOCOD1 = $Almoxarifado ";
		$sql   .= "                 AND AMOVMAANO1 = $AnoMovimentacao ";
		$sql   .= "                 AND CMOVMACOD1 = $Sequencial ";
		$sql   .= "            ) MOVALT, ";
		$sql   .= "       SFPC.TBTIPOMOVIMENTACAO TIP, SFPC.TBUNIDADEDEMEDIDA UND ";
		$sql   .= " WHERE MAT.CMATEPSEQU = MOV.CMATEPSEQU "; // Material: SFPC.TBMATERIALPORTAL = SFPC.TBMOVIMENTACAOMATERIAL
		$sql   .= "   AND MOV.CTIPMVCODI = TIP.CTIPMVCODI "; // Tipo de movimentação: SFPC.TBMOVIMENTACAOMATERIAL = SFPC.TBTIPOMOVIMENTACAO
		$sql   .= "   AND MAT.CUNIDMCODI = UND.CUNIDMCODI "; // Unidade de medida: SFPC.TBMATERIALPORTAL = SFPC.TBUNIDADEDEMEDIDA
		$sql   .= "   AND MOV.CALMPOCODI = $Almoxarifado ";
		$sql   .= "   AND MOV.AMOVMAANOM = $AnoMovimentacao ";
		$sql   .= "   AND MOV.CMOVMACODI = $Sequencial ";
		$sql   .= "   AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') ";
		$res    = $db->query($sql);
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$qtdres = $res->numRows();
				if($qtdres > 0){
						$row = $res->fetchRow();
						$TipoMovimentacao = $row[0];
						$Movimentacao     = $row[1];
						$DescMovimentacao = $row[2];
						$DescMaterial     = $row[3];
						$UnidSigl         = $row[4];
						$QtdOriginal      = $row[5];
						$QtdMovimentada   = $row[14];
						$QtdAtual         = $row[5]+$row[14];
						$Valor            = $row[6];
						$Material         = $row[7];
						$Sequencial       = $row[8];
						$MovNumero        = $row[9];
						$Matricula        = $row[10];
						$Responsavel      = $row[11];
						$Observacao       = $row[13];
						if($Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20){
								$SeqRequisicao = $row[12];
						}
				}
		}
}

if($Botao == "Voltar"){
		header("Location: CadMovimentacaoSelecionar.php");
		exit;
}

if ($Sequencial){
	$db   = Conexao();
	$sql  = "SELECT COUNT(B.TINVCOFECH) ";
	$sql .= "  FROM SFPC.TBINVENTARIOCONTAGEM B ";
	$sql .= " INNER JOIN SFPC.TBLOCALIZACAOMATERIAL C ";
	$sql .= "    ON B.CLOCMACODI = C.CLOCMACODI ";
	$sql .= "   AND C.CALMPOCODI = $Almoxarifado ";
	$sql .= " WHERE (B.AINVCOANOB,B.AINVCOSEQU) = ";
	$sql .= "       (SELECT MAX(A.AINVCOANOB),MAX(A.AINVCOSEQU) ";
	$sql .= "          FROM SFPC.TBINVENTARIOCONTAGEM A ";
	$sql .= "         WHERE A.AINVCOANOB = ";
	$sql .= "               (SELECT MAX(AINVCOANOB) ";
	$sql .= "                  FROM SFPC.TBINVENTARIOCONTAGEM  ";
	$sql .= "                 WHERE A.AINVCOANOB = AINVCOANOB ) ";
	$sql .= "           AND A.CLOCMACODI = B.CLOCMACODI) ";
	$sql .= "   AND B.TINVCOFECH < (SELECT A.DMOVMAMOVI ";
	$sql .= "                         FROM SFPC.TBMOVIMENTACAOMATERIAL A ";
	$sql .= "                        WHERE A.CALMPOCODI = C.CALMPOCODI ";
	$sql .= "                          AND A.AMOVMAANOM = $AnoMovimentacao ";
	$sql .= "                          AND A.CMOVMACODI = $Sequencial) ";
	$res  = $db->query($sql);
	if( PEAR::isError($res) ){
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}else{
			$Linha = $res->fetchRow();
			if ($Linha[0]==0){
				$Mens     = 1;
				$Tipo     = 2;
				$Mensagem = "Movimentação não pode ser realizada pois possui referência a período anterior ao último inventário do almoxarifado";
				$Botao = "";
			}
	}
	$db->disconnect();
}

if($Botao == "Alterar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if(!$QtdAtual or $QtdAtual == 0){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoAlterar.QtdAtual.focus();\" class=\"titulo2\">Quantidade</a>";
		}else{
				# Verifica o valor digitado #
				if(!Decimal($QtdAtual)){
						if($Mens == 1){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadMovimentacaoAlterar.QtdAtual.focus();\" class=\"titulo2\">Quantidade Válida</a>";
				}
		}
		if($Matricula == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoAlterar.Matricula.focus();\" class=\"titulo2\">Matrícula</a>";
		}elseif(!SoNumeros($Matricula)){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoAlterar.Matricula.focus();\" class=\"titulo2\">Matrícula Válida</a>";
		}
		if($Responsavel == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoAlterar.Responsavel.focus();\" class=\"titulo2\">Responsável</a>";
		}elseif(!NomeSobrenome($Responsavel)){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoAlterar.Responsavel.focus();\" class=\"titulo2\">Nome e Sobrenome do Responsável</a>";
		}
		if($NCaracteresO > "200"){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Observação menor que 200 caracteres";
		}

		if($Movimentacao == 13){
				$db   = Conexao();
				# Busca informações da Entrada por Empréstimo (6) através do tipo 13 #
				# Informações de códigos usadas no update (mais abaixo) e informação de #
				# quantidade usada para checar se será possível alterar para o valor digitado #
				$sqlMov6  = "SELECT A.AMOVMAANO1, A.CMOVMACOD1, A.AMOVMAQTDM ";
				$sqlMov6 .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A, SFPC.TBMOVIMENTACAOMATERIAL B";
				$sqlMov6 .= " WHERE A.CTIPMVCODI = 12 AND B.CTIPMVCODI = 13 ";
				$sqlMov6 .= "   AND A.CALMPOCODI = B.CALMPOCOD1 ";
				$sqlMov6 .= "   AND A.AMOVMAANOM = B.AMOVMAANO1 ";
				$sqlMov6 .= "   AND A.CMOVMACODI = B.CMOVMACOD1 ";
				$sqlMov6 .= "   AND B.CALMPOCODI = $Almoxarifado ";
				$sqlMov6 .= "   AND B.AMOVMAANOM = $AnoMovimentacao ";
				$sqlMov6 .= "   AND B.CMOVMACODI = $Sequencial ";
				$sqlMov6 .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";
				$sqlMov6 .= "   AND (B.FMOVMASITU IS NULL OR B.FMOVMASITU = 'A') ";
				$resMov6 = $db->query($sqlMov6);
				if( PEAR::isError($resMov6) ){
						$db->disconnect();
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMov6");
				}else{
						$LinhaMov6  = $resMov6->fetchRow();
						$AnoMov6    = $LinhaMov6[0];
						$SeqMov6    = $LinhaMov6[1];
						$QtdMov6    = $LinhaMov6[2];

						# Busca somatório de todas as devoluções para o empréstimo que está sendo devolvido, menos esta própria devolução #
						$sqlMov6  = "SELECT CASE WHEN (SUM(B.AMOVMAQTDM) > 0) THEN SUM(B.AMOVMAQTDM) ELSE 0 END ";
						$sqlMov6 .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL A, ";
						$sqlMov6 .= "       SFPC.TBMOVIMENTACAOMATERIAL B ";
						$sqlMov6 .= " WHERE A.CTIPMVCODI = 13 AND A.CTIPMVCODI = B.CTIPMVCODI ";
						$sqlMov6 .= "   AND A.CALMPOCOD1 = B.CALMPOCOD1 ";   # Todas as movimentações que devolvem o mesmo empréstimo #
						$sqlMov6 .= "   AND A.AMOVMAANO1 = B.AMOVMAANO1 ";
						$sqlMov6 .= "   AND A.CMOVMACOD1 = B.CMOVMACOD1 ";
						$sqlMov6 .= "   AND A.CALMPOCODI = $Almoxarifado ";
						$sqlMov6 .= "   AND A.AMOVMAANOM = $AnoMovimentacao ";
						$sqlMov6 .= "   AND A.CMOVMACODI = $Sequencial ";
						$sqlMov6 .= "   AND B.CALMPOCODI = $Almoxarifado ";
						$sqlMov6 .= "   AND (B.CALMPOCODI,B.AMOVMAANOM,B.CMOVMACODI) <> ($Almoxarifado,$AnoMovimentacao,$Sequencial) "; # Menos a própria movimentação que está sendo tratada #
						$sqlMov6 .= "   AND (A.FMOVMASITU IS NULL OR A.FMOVMASITU = 'A') ";
						$sqlMov6 .= "   AND (B.FMOVMASITU IS NULL OR B.FMOVMASITU = 'A') ";
						$resMov6  = $db->query($sqlMov6);
						if( PEAR::isError($resMov6) ){
								$db->disconnect();
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMov6");
						}else{
								$LinhaMov6    = $resMov6->fetchRow();
								$OutrasMovs13 = $LinhaMov6[0];
								# Se a quantidade alterada agora somada com outras movimentações de devolução para o mesmo empréstimo superar a quantidade emprestada, emite mensagem de erro #
								if( ($QtdAtual+$OutrasMovs13) > $QtdMov6){
										if($Mens == 1){ $Mensagem .= ", "; }
										$Mens      = 1;
										$Tipo      = 2;
										$QtdMax = $QtdMov6 - $OutrasMovs13;
										$Mensagem .= "<a href=\"javascript: document.CadMovimentacaoAlterar.QtdMovimentada.focus();\" class=\"titulo2\">Quantidade Movimentada Máxima de ".converte_quant($QtdMax)." (a Soma desta com a Quantidade já Devolvida por outras movimentações (".converte_quant($OutrasMovs13).") não pode superar a Quantidade Emprestada (".converte_quant($QtdMov6).")</a>";
								}
						}
				}
				$db->disconnect();
		}

		# Se Devolução Interna ou Acerto de Requisição #
		if( ( $Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20 ) and ($QtdOriginal+$QtdMovimentada) != $QtdAtual ){
				# Resgata as quantidades do material na requisição #
				$db   = Conexao();
				$sql  = " SELECT B.AITEMRQTAT, B.AITEMRQTSO ";
				$sql .= "   FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBITEMREQUISICAO B";
				$sql .= "  WHERE A.CREQMASEQU = B.CREQMASEQU AND B.CMATEPSEQU = $Material ";
				$sql .= "    AND A.CREQMASEQU = $SeqRequisicao ";
				$res  = $db->query($sql);
				if( PEAR::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha         = $res->fetchRow();
						$QtdAtendida   = $Linha[0];
						$QtdSolicitada = $Linha[1];
				}
				# Verifica se a movimentação ficará negativa #
				if(   ( $QtdAtual < ($QtdOriginal+$QtdMovimentada) ) and ( $QtdAtendida - ( ($QtdOriginal+$QtdMovimentada) - $QtdAtual ) ) < 0 and ( $Movimentacao == 20 )                          // Este Caso é para Saída
					 or ( $QtdAtual > ($QtdOriginal+$QtdMovimentada) ) and ( $QtdAtendida - ( $QtdAtual - ($QtdOriginal+$QtdMovimentada) ) ) < 0 and ( $Movimentacao == 2 or $Movimentacao == 19 ) ){ // Este Caso é para Entrada
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens       = 1;
						$Tipo       = 2;
						$Virgula    = 2;
						if( $Movimentacao == 20 ){
								$Diferenca  = $QtdAtendida - ( ($QtdOriginal+$QtdMovimentada) - $QtdAtual );
						}else{
								$Diferenca  = $QtdAtendida - ( $QtdAtual - ($QtdOriginal+$QtdMovimentada) );
						}
						$QtdVirgula = converte_quant($Diferenca);
						$Mensagem   = "Movimentação Cancelada! Quantidade Movimentada deixará a Quantidade Atendida negativa ($QtdVirgula)";
				}else{
						# Sql para pegar a quantidade em estoque #
						$sql  = " SELECT AARMATQTDE ";
						$sql .= "   FROM SFPC.TBARMAZENAMENTOMATERIAL ARM, SFPC.TBMATERIALPORTAL MAT, ";
						$sql .= " 	     SFPC.TBLOCALIZACAOMATERIAL LOC ";
						$sql .= "  WHERE MAT.CMATEPSEQU = ARM.CMATEPSEQU AND ARM.CLOCMACODI = LOC.CLOCMACODI ";
						$sql .= "    AND LOC.CALMPOCODI = $Almoxarifado AND MAT.CMATEPSEQU = $Material ";
						$res  = $db->query($sql);
						if( PEAR::isError($res) ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha      = $res->fetchRow();
								$QtdEstoque = $Linha[0];
						}

						# Entrada para Acerto de Requisição ou Entrada por Devolução Interna #
						if($Movimentacao == 19 or $Movimentacao == 2){
								if( $QtdAtual > ($QtdOriginal+$QtdMovimentada) ){
										if( ( $QtdAtual - ($QtdOriginal+$QtdMovimentada) ) > $QtdSolicitada  ){
												if( $Mens == 1 ){ $Mensagem .= ", "; }
												$Mens       = 1;
												$Tipo       = 2;
												$Virgula    = 2;
												$QtdVirgula = str_replace('.',',',sprintf("%01.2f",$QtdSolicitada));
												$Mensagem   .= "Quantidade Movimentada menor ou igual a Quantidade Solicitada ($QtdVirgula)";
										}
								}
						}

						# Saída para Acerto de Requisição #
						if($Movimentacao == 20){
								if($QtdAtual > ($QtdOriginal+$QtdMovimentada)){
										# Verifica se a Qtd Movimentada + Atendida não ultrapassa a Solicitada #
										if( ( $QtdAtendida + ($QtdAtual - ($QtdOriginal+$QtdMovimentada)) ) > $QtdSolicitada  ){
												if( $Mens == 1 ){ $Mensagem .= ", "; }
												$Mens       = 1;
												$Tipo       = 2;
												$Virgula    = 2;
												$QtdVirgula = str_replace('.',',',sprintf("%01.2f",$QtdSolicitada));
												$Mensagem  .= "Quantidade Movimentada menor ou igual a Quantidade Solicitada ($QtdVirgula)";
										}else{
												# Verifica se a Qtd Movimentada não ultrapassa o Estoque #
												if( ($QtdAtual - ($QtdOriginal+$QtdMovimentada)) > $QtdEstoque  ){
														if( $Mens == 1 ){ $Mensagem .= ", "; }
														$Mens        = 1;
														$Tipo        = 2;
														$Virgula     = 2;
														$QtdVirgula  = str_replace('.',',',sprintf("%01.2f",$QtdEstoque));
														$Mensagem   .= "Quantidade Movimentada menor ou igual a Quantidade em Estoque ($QtdVirgula)";
												}
										}
								}
						}
				}
				$db->disconnect();
		}
		if($Mens == 0){
				# Seta data de gravação no banco de dados #
				$DataGravacao = date("Y-m-d H:i:s");

				# Descobre a quantidade em estoque #
				$sql  = " SELECT AARMATQTDE ";
				$sql .= "   FROM SFPC.TBARMAZENAMENTOMATERIAL ARM,  SFPC.TBMATERIALPORTAL MAT, ";
				$sql .= " 	     SFPC.TBLOCALIZACAOMATERIAL LOC ";
				$sql .= "  WHERE MAT.CMATEPSEQU = ARM.CMATEPSEQU AND ARM.CLOCMACODI = LOC.CLOCMACODI ";
				$sql .= "    AND LOC.CALMPOCODI = $Almoxarifado AND MAT.CMATEPSEQU = $Material ";
				$db   = Conexao();
				$result = $db->query($sql);
				if(PEAR::isError($result)){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Linha = $result->fetchRow();
						$QtdEstoque = $Linha[0];

						# Verifica se o item em estoque ficará menor que zero #
						if( ( $TipoMovimentacao == "E" and $QtdAtual < ($QtdOriginal+$QtdMovimentada) and ( $QtdEstoque - ( ($QtdOriginal+$QtdMovimentada) - $QtdAtual ) ) < 0 ) or ( $TipoMovimentacao == "S" and $QtdAtual > ($QtdOriginal+$QtdMovimentada) and ($QtdEstoque - ($QtdAtual - ($QtdOriginal+$QtdMovimentada)) < 0 ) ) ){
								$Mensagem = "Alteração Cancelada!<br>O item em estoque ficará menor que zero";
								$Url = "CadMovimentacaoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
								if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						}else{
								//$MovsProibidas = array(0,1,3,4,5,7,8,18, 6,9,11,29, 21,22,26,27,31);
								$MovsProibidas = array(0,1,3,4,5,6,7,8,9,11,18,21,22,25,26,27,28,29,31,33,34,35,36);
								if(in_array($Movimentacao,$MovsProibidas)){
										$Mensagem = "Alteração Cancelada!<br>Não é possível fazer manutenção deste tipo de Movimentação";
										$Url = "CadMovimentacaoSelecionar.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=2";
										if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										header("location: ".$Url);
										exit;
								}else{
										if($Mens == 0){
												# Altera a movimentação da tabela SFPC.TBMOVIMENTACAOMATERIAL #
												$db->query("BEGIN TRANSACTION");
												$QtdMovimentadaDiferenca = $QtdAtual - ($QtdOriginal+$QtdMovimentada) ;
												if ( $QtdMovimentadaDiferenca > 0 ){ // Rossana
														$Movimentacao = 35; // Entrada
												} else {
														$Movimentacao = 36; // Saída
												}
												$AnoMovimentacaoAlteracao = date("Y");
												# Carrega o próximo número de movimentação que será incluído #
												$sql  = "SELECT MAX(CMOVMACODI) ";
												$sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL  ";
												$sql .= " WHERE CALMPOCODI = '$Almoxarifado' ";
												$sql .= "   AND AMOVMAANOM = '$AnoMovimentacaoAlteracao' ";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														$MovNumeroAlteracao = $Linha[0] + 1;
												}
												# Carrega o próximo número de movimentação para o tipo de movimentacao que será incluído #
												$sql  = "SELECT MAX(CMOVMACODT) ";
												$sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL  ";
												$sql .= " WHERE CTIPMVCODI = '$Movimentacao' ";
												$sql .= "   AND CALMPOCODI = '$Almoxarifado' ";
												$sql .= "   AND AMOVMAANOM = '$AnoMovimentacaoAlteracao' ";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														$ProxMovimentacao = $Linha[0] + 1;
												}
												$sqlInsert  = "INSERT INTO SFPC.TBMOVIMENTACAOMATERIAL( ";
												$sqlInsert .= "CALMPOCODI, AMOVMAANOM, CMOVMACODI, DMOVMAMOVI, CTIPMVCODI, ";
												$sqlInsert .= "CMATEPSEQU, AMOVMAQTDM, VMOVMAVALO, VMOVMAUMED, CGREMPCODI, ";
												$sqlInsert .= "CUSUPOCODI, TMOVMAULAT, CMOVMACODT, AMOVMAMATR, NMOVMARESP, ";
												$sqlInsert .= "CALMPOCOD1, AMOVMAANO1, CMOVMACOD1, EMOVMAOBSE ";
												$sqlInsert .= ") VALUES ( ";
												$sqlInsert .= "$Almoxarifado, $AnoMovimentacaoAlteracao, $MovNumeroAlteracao, '".date("Y-m-d")."', $Movimentacao, ";
												$sqlInsert .= "$Material, ".abs($QtdMovimentadaDiferenca).", $Valor, $Valor, $GrupoEmp, ";
												$sqlInsert .= "$Usuario, '$DataGravacao', $ProxMovimentacao, $Matricula, '$Responsavel', ";
												$sqlInsert .= "$Almoxarifado, $AnoMovimentacao, $Sequencial, '$Observacao' ";
												$sqlInsert .= ");";

												$result = $db->query($sqlInsert);
												if( PEAR::isError($result) ){
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlInsert");
												}else{
														# Diminui ou aumenta estoque em SFPC.TBARMAZENAMENTOMATERIAL #
														if($QtdAtual == ($QtdOriginal+$QtdMovimentada)){
																$QtdFinal = $QtdEstoque;
														}else{
																if($TipoMovimentacao == "E"){
																		if($QtdAtual > ($QtdOriginal+$QtdMovimentada)){
																				$QtdFinal = $QtdEstoque + ($QtdAtual - ($QtdOriginal+$QtdMovimentada));
																		}elseif($QtdAtual < ($QtdOriginal+$QtdMovimentada)){
																				$QtdFinal = $QtdEstoque - (($QtdOriginal+$QtdMovimentada) - $QtdAtual);
																		}
																}elseif($TipoMovimentacao == "S"){
																		if($QtdAtual > ($QtdOriginal+$QtdMovimentada)){
																				$QtdFinal = $QtdEstoque - ($QtdAtual - ($QtdOriginal+$QtdMovimentada));
																		}elseif( $QtdAtual < ($QtdOriginal+$QtdMovimentada) ){
																				$QtdFinal = $QtdEstoque + (($QtdOriginal+$QtdMovimentada) - $QtdAtual);
																		}
																}
														}

														# Atualiza a tabela de Estoque #
														$sql  = " UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
														$sql .= "    SET AARMATQTDE = $QtdFinal, ";
														$sql .= "        CGREMPCODI = $GrupoEmp, ";
														$sql .= "        CUSUPOCODI = $Usuario, TARMATULAT = '$DataGravacao' ";
														$sql .= "  WHERE CMATEPSEQU = $Material AND CLOCMACODI = $Localizacao";
														$result = $db->query($sql);
														if( PEAR::isError($result) ){
																$db->query("ROLLBACK");
																$db->query("END TRANSACTION");
																$db->disconnect();
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																# Faz tratamentos especiais para Alteração de Movimentação do tipo 13 #
																if($Movimentacao == 13){
																		# Se for uma alteração para menos de uma Saída por Devolução de Empréstimo (13), #
																		# reseta a flag de correspondência da Entrada por Empréstimo (6) #
																		if($QtdAtual < ($QtdOriginal+$QtdMovimentada)){
																				$sqlMov6	= "UPDATE SFPC.TBMOVIMENTACAOMATERIAL SET FMOVMACORR = NULL ";
																				$sqlMov6 .= " WHERE CTIPMVCODI = 6 ";
																				$sqlMov6 .= "		AND CALMPOCODI = $Almoxarifado ";
																				$sqlMov6 .= "   AND AMOVMAANOM = $AnoMov6 ";
																				$sqlMov6 .= "   AND CMOVMACODI = $SeqMov6 ";
																				$sqlMov6 .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
																				$resMov6  = $db->query($sqlMov6);
																				if( PEAR::isError($resMov6) ){
																						$db->query("ROLLBACK");
																						$db->query("END TRANSACTION");
																						$db->disconnect();
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMov6");
																				}
																		# Se for uma alteração para mais de uma Saída por Devolução de Empréstimo (13), #
																		# seta a flag de correspondência da Entrada por Empréstimo (6) se a devolução, somadas com outras devoluções alcançar o empréstimo #
																		}elseif($QtdAtual > ($QtdOriginal+$QtdMovimentada)){
																				$sqlMov6	= "UPDATE SFPC.TBMOVIMENTACAOMATERIAL SET FMOVMACORR = 'S' ";
																				$sqlMov6 .= " WHERE CTIPMVCODI = 6 ";
																				$sqlMov6 .= "   AND CALMPOCODI = $Almoxarifado ";
																				$sqlMov6 .= "   AND AMOVMAANOM = $AnoMov6 ";
																				$sqlMov6 .= "   AND CMOVMACODI = $SeqMov6 ";
																				$sqlMov6 .= "   AND AMOVMAQTDM = ($OutrasMovs13 + $QtdAtual) ";            # Se a soma do que foi devolvido anteriormente com o que está sendo devolvido agora der o total do empréstimo, setará a Flag de correspondência na Movimentação de Empréstimo
																				$sqlMov6 .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
																				$resMov6  = $db->query($sqlMov6);
																				if( PEAR::isError($resMov6) ){
																						$db->query("ROLLBACK");
																						$db->query("END TRANSACTION");
																						$db->disconnect();
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlMov6");
																				}
																		}
																}

																# Caso seja DEVOLUÇÃO INTERNA OU ACERTO DE REQUISIÇÃO #
																if($Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20){
																		# Alterando a quantidade antendida na requisição #
																		$sql  = "UPDATE SFPC.TBITEMREQUISICAO ";
																		# Altera a quantidade de acordo com a Movimentação #
																		if( $Movimentacao == 20 ){ // Saída para Acerto de Requisição
																				if( $QtdAtual < ($QtdOriginal+$QtdMovimentada) ){
																						$sql .= "   SET AITEMRQTAT = AITEMRQTAT - ( ($QtdOriginal+$QtdMovimentada) - $QtdAtual ), ";
																				}else{
																						$sql .= "   SET AITEMRQTAT = AITEMRQTAT + ( $QtdAtual - ($QtdOriginal+$QtdMovimentada) ), ";
																				}
																		}elseif( $Movimentacao == 2 or $Movimentacao == 19 ){ //Se Devolução Interna ou Entrada para Acerto de Requisição
																				if( $QtdAtual < ($QtdOriginal+$QtdMovimentada) ){
																						$sql .= "   SET AITEMRQTAT = AITEMRQTAT + ( ($QtdOriginal+$QtdMovimentada) - $QtdAtual ), ";
																				}else{
																						$sql .= "   SET AITEMRQTAT = AITEMRQTAT - ( $QtdAtual - ($QtdOriginal+$QtdMovimentada) ), ";
																				}
																		}
																		$sql .= "       CGREMPCODI = $GrupoEmp, ";
																		$sql .= "       CUSUPOCODI = $Usuario, TITEMRULAT = '$DataGravacao' ";
																		$sql .= " WHERE CMATEPSEQU = $Material AND CREQMASEQU = $SeqRequisicao ";
																		$res  = $db->query($sql);
																		if( PEAR::isError($res) ){
																				$db->query("ROLLBACK");
																				$db->query("END TRANSACTION");
																				$db->disconnect();
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}
																}

																# Se for Acerto de Requisição e houve diferença da quantidade antendida antes da alteração #
																if( ($Movimentacao == 19 or $Movimentacao == 20) and (($QtdOriginal+$QtdMovimentada) != $QtdAtual) ){
																		# Verifica as qtd atendida e solicitada #
																		$sqlqtd  = " SELECT AITEMRQTAT, AITEMRQTSO FROM SFPC.TBITEMREQUISICAO ";
																		$sqlqtd .= "  WHERE CREQMASEQU = $SeqRequisicao AND CMATEPSEQU = $Material ";
																		$resqtd  = $db->query($sqlqtd);
																		if( PEAR::isError($resqtd) ){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlqtd");
																		}else{
																				$Linha  = $resqtd->fetchRow();
																				$QtdAte = $Linha[0];
																				$QtdSol = $Linha[1];
																				if($Situacao == 3 and $QtdAte < $QtdSol){ //Situacao = 3 - Atendimento Total
																						# Modifica a situação da requisição para Atendimento Parcial #
																						$sqlsit  = "INSERT INTO SFPC.TBSITUACAOREQUISICAO( ";
																						$sqlsit .= "CREQMASEQU, CTIPSRCODI, TSITRESITU, CGREMPCODI, ";
																						$sqlsit .= "CUSUPOCODI, ESITREMOTI, TSITREULAT ";
																						$sqlsit .= ") VALUES ( ";
																						$sqlsit .= "$SeqRequisicao, 4, '".$DataGravacao."', $GrupoEmp, ";
																						$sqlsit .= "$Usuario, NULL, '".$DataGravacao."' ) ";
																						$ressit  = $db->query($sqlsit);
																						if(PEAR::isError($ressit)){
																								$db->query("ROLLBACK");
																								$db->query("END TRANSACTION");
																								$db->disconnect();
																								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlsit");
																						}
																				}elseif($Situacao == 4 and $QtdAte == $QtdSol){ //Situacao = 4 - Atendimento Parcial
																						# Modifica a situação da requisição para Atendimento Total #
																						$sqlsit  = "INSERT INTO SFPC.TBSITUACAOREQUISICAO( ";
																						$sqlsit .= "CREQMASEQU, CTIPSRCODI, TSITRESITU, CGREMPCODI, ";
																						$sqlsit .= "CUSUPOCODI, ESITREMOTI, TSITREULAT ";
																						$sqlsit .= ") VALUES ( ";
																						$sqlsit .= "$SeqRequisicao, 3 , '".$DataGravacao."', $GrupoEmp, ";
																						$sqlsit .= "$Usuario, NULL, '".$DataGravacao."' ) ";
																						$ressit  = $db->query($sqlsit);
																						if(PEAR::isError($ressit)){
																								$db->query("ROLLBACK");
																								$db->query("END TRANSACTION");
																								$db->disconnect();
																								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlsit");
																						}
																				}
																		}
																}

																# Entrada por Devolução Interna e Situação BAIXADA #
																if($Movimentacao == 2 and $Situacao == 5){
																		# Verifica o tipo do material e o valor médio #
																		$sql  = "SELECT D.FGRUMSTIPC, E.VARMATUMED ";
																		$sql .= "  FROM SFPC.TBMATERIALPORTAL A, SFPC.TBSUBCLASSEMATERIAL B, ";
																		$sql .= "       SFPC.TBCLASSEMATERIALSERVICO C, SFPC.TBGRUPOMATERIALSERVICO D, ";
																		$sql .= "       SFPC.TBARMAZENAMENTOMATERIAL E ";
																		$sql .= " WHERE A.CMATEPSEQU = $Material AND A.CSUBCLSEQU = B.CSUBCLSEQU ";
																		$sql .= "   AND B.CGRUMSCODI = C.CGRUMSCODI AND B.CCLAMSCODI = C.CCLAMSCODI ";
																		$sql .= "   AND C.CGRUMSCODI = D.CGRUMSCODI AND A.CMATEPSEQU = E.CMATEPSEQU ";
																		$sql .= "		AND E.CLOCMACODI = $Localizacao ";
																		$res  = $db->query($sql);
																		if(PEAR::isError($res)){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				$Linha         = $res->fetchRow();
																				$TipoMaterial  = $Linha[0];
																				$ValorMedio    = $Linha[1];
																				if(($QtdOriginal+$QtdMovimentada) > $QtdAtual){
																						$TipoMovAlmox  = "S";
																						$TipoMovCC     = "E";
																						$ValorCusto = ( ($QtdOriginal+$QtdMovimentada) - $QtdAtual ) * $ValorMedio;
																				}else{
																						$TipoMovAlmox  = "E";
																						$TipoMovCC     = "S";
																						$ValorCusto    = ( $QtdAtual - ($QtdOriginal+$QtdMovimentada) ) * $ValorMedio;
																				}
																				if($TipoMaterial == "C"){
																						$ValorCustoConsumo = $ValorCusto;
																				}elseif($TipoMaterial == "P"){
																						$ValorCustoPermanente = $ValorCusto;
																				}elseif($TipoMaterial == "D"){
																						$ValorCustoDidatico = $ValorCusto;
																				}elseif($TipoMaterial == "F"){
																						$ValorCustoFardamento = $ValorCusto;
																				}elseif($TipoMaterial == "L"){
																						$ValorCustoLimpeza = $ValorCusto;
																				}

																				# Resgata os valores na tabela de centro de custo #
																				$sql  = "SELECT CCENPOCORG, CCENPOUNID, CCENPONRPA, CCENPOCENT, CCENPODETA ";
																				$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBREQUISICAOMATERIAL B ";
																				$sql .= " WHERE A.CCENPOSEQU = B.CCENPOSEQU AND B.CREQMASEQU = $SeqRequisicao ";
																				$sql .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
																				$res  = $db->query($sql);
																				if(PEAR::isError($res)){
																						$db->query("ROLLBACK");
																						$db->query("END TRANSACTION");
																						$db->disconnect();
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				}else{
																						$Linha        = $res->fetchRow();
																						$Orgao        = $Linha[0];
																						$Unidade      = $Linha[1];
																						$RPA          = $Linha[2];
																						$CentroCusto  = $Linha[3];
																						$Detalhamento = $Linha[4];

																						$ValorCustoPermanente = sprintf("%01.2f",round($ValorCustoPermanente,2));
																						$ValorCustoConsumo    = sprintf("%01.2f",round($ValorCustoConsumo,2));
																						$ValorCustoDidatico   = sprintf("%01.2f",round($ValorCustoDidatico,2));
																						$ValorCustoFardamento = sprintf("%01.2f",round($ValorCustoFardamento,2));
																						$ValorCustoLimpeza    = sprintf("%01.2f",round($ValorCustoLimpeza,2));
																						$TimeStamp            = $DataGravacao;
																						$DiaBaixa             = date("d");
																						$MesBaixa             = date("m");
																						$AnoBaixa             = date("Y");
																						# GERA CUSTO PARA O ORACLE #
																						include "../oracle/estoques/RotIncluirMovimentoCustoContabil.php";
																						exit;
																				}
																		}

																# Movimentação de Entrada que gera custo e pode ter manutenção #
																}elseif($Movimentacao == 10 or $Movimentacao == 28){
																		# Verifica o tipo do material e o valor médio #
																		$sql  = "SELECT D.FGRUMSTIPC, E.VARMATUMED ";
																		$sql .= "  FROM SFPC.TBMATERIALPORTAL A, SFPC.TBSUBCLASSEMATERIAL B, ";
																		$sql .= "       SFPC.TBCLASSEMATERIALSERVICO C, SFPC.TBGRUPOMATERIALSERVICO D, ";
																		$sql .= "       SFPC.TBARMAZENAMENTOMATERIAL E ";
																		$sql .= " WHERE A.CMATEPSEQU = $Material AND A.CSUBCLSEQU = B.CSUBCLSEQU ";
																		$sql .= "   AND B.CGRUMSCODI = C.CGRUMSCODI AND B.CCLAMSCODI = C.CCLAMSCODI ";
																		$sql .= "   AND C.CGRUMSCODI = D.CGRUMSCODI AND A.CMATEPSEQU = E.CMATEPSEQU ";
																		$sql .= "		AND E.CLOCMACODI = $Localizacao ";
																		$res  = $db->query($sql);
																		if(PEAR::isError($res)){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				$Linha         = $res->fetchRow();
																				$TipoMaterial  = $Linha[0];
																				$ValorMedio    = $Linha[1];
																				if(($QtdOriginal+$QtdMovimentada) > $QtdAtual){
																						$TipoMovAlmox  = "S";
																						$ValorCusto    = (($QtdOriginal+$QtdMovimentada) - $QtdAtual) * $ValorMedio;
																				}else{
																						$TipoMovAlmox  = "E";
																						$ValorCusto    = ($QtdAtual - ($QtdOriginal+$QtdMovimentada)) * $ValorMedio;
																				}
																				if($TipoMaterial == "C"){
																						$ValorCustoConsumo = $ValorCusto;
																						$CC = 799; # Almoxarifado
																				}elseif($TipoMaterial == "P"){
																						$ValorCustoPermanente = $ValorCusto;
																						$CC = 800; # Patrimônio
																				}elseif($TipoMaterial == "D"){
																						$ValorCustoDidatico = $ValorCusto;
																						$CC = 799; # Almoxarifado
																				}elseif($TipoMaterial == "F"){
																						$ValorCustoFardamento = $ValorCusto;
																						$CC = 799; # Almoxarifado
																				}elseif($TipoMaterial == "L"){
																						$ValorCustoLimpeza = $ValorCusto;
																						$CC = 799; # Almoxarifado
																				}

																				$sql  = "SELECT DISTINCT A.CCENPOCORG, A.CCENPOUNID, C.CALMPONRPA ";
																				$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBALMOXARIFADOORGAO B, SFPC.TBALMOXARIFADOPORTAL C ";
																				$sql .= " WHERE A.CORGLICODI = B.CORGLICODI ";
																				$sql .= "   AND B.CALMPOCODI = C.CALMPOCODI ";
																				$sql .= "   AND B.CALMPOCODI = $Almoxarifado AND A.CCENPOCENT = $CC AND A.CCENPODETA = $Det ";
																				$sql .= "   AND (A.FCENPOSITU IS NULL OR A.FCENPOSITU <> 'I') "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
																				$res  = $db->query($sql);
																				if(PEAR::isError($res)){
																						$db->query("ROLLBACK");
																						$db->query("END TRANSACTION");
																						$db->disconnect();
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				}else{
																						$Linha        = $res->fetchRow();
																						$Orgao        = $Linha[0];
																						$Unidade      = $Linha[1];
																						$RPA          = $Linha[2];
																						$CentroCusto  = $CC;
																						$Detalhamento = $Det;

																						$ValorCustoPermanente = sprintf("%01.2f",round($ValorCustoPermanente,2));
																						$ValorCustoConsumo    = sprintf("%01.2f",round($ValorCustoConsumo,2));
																						$ValorCustoDidatico   = sprintf("%01.2f",round($ValorCustoDidatico,2));
																						$ValorCustoFardamento = sprintf("%01.2f",round($ValorCustoFardamento,2));
																						$ValorCustoLimpeza    = sprintf("%01.2f",round($ValorCustoLimpeza,2));
																						$TimeStamp            = $DataGravacao;
																						$DiaBaixa             = date("d");
																						$MesBaixa             = date("m");
																						$AnoBaixa             = date("Y");
																						# GERA CUSTO PARA O ORACLE #
																						include "../oracle/estoques/RotIncluirMovimentoCustoContabil.php";
																						exit;
																				}
																		}

																# Movimentações de Saída que geram custo e podem ter manutenção #
																}elseif($Movimentacao == 14 or $Movimentacao == 16 or $Movimentacao == 17 or $Movimentacao == 23 or $Movimentacao == 24 or $Movimentacao == 25 or $Movimentacao == 37){
																		# Verifica o tipo do material e o valor médio #
																		$sql  = "SELECT D.FGRUMSTIPC, E.VARMATUMED ";
																		$sql .= "  FROM SFPC.TBMATERIALPORTAL A, SFPC.TBSUBCLASSEMATERIAL B, ";
																		$sql .= "       SFPC.TBCLASSEMATERIALSERVICO C, SFPC.TBGRUPOMATERIALSERVICO D, ";
																		$sql .= "       SFPC.TBARMAZENAMENTOMATERIAL E ";
																		$sql .= " WHERE A.CMATEPSEQU = $Material AND A.CSUBCLSEQU = B.CSUBCLSEQU ";
																		$sql .= "   AND B.CGRUMSCODI = C.CGRUMSCODI AND B.CCLAMSCODI = C.CCLAMSCODI ";
																		$sql .= "   AND C.CGRUMSCODI = D.CGRUMSCODI AND A.CMATEPSEQU = E.CMATEPSEQU ";
																		$sql .= "   AND E.CLOCMACODI = $Localizacao ";
																		$res  = $db->query($sql);
																		if(PEAR::isError($res)){
																				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																		}else{
																				$Linha         = $res->fetchRow();
																				$TipoMaterial  = $Linha[0];
																				$ValorMedio    = $Linha[1];
																				if(($QtdOriginal+$QtdMovimentada) > $QtdAtual){
																						$TipoMovAlmox  = "E";
																						$ValorCusto    = (($QtdOriginal+$QtdMovimentada) - $QtdAtual) * $ValorMedio;
																				}else{
																						$TipoMovAlmox  = "S";
																						$ValorCusto    = ($QtdAtual - ($QtdOriginal+$QtdMovimentada)) * $ValorMedio;
																				}
																				if($TipoMaterial == "C"){
																						$ValorCustoConsumo = $ValorCusto;
																						$CC = 799; # Almoxarifado
																				}elseif($TipoMaterial == "P"){
																						$ValorCustoPermanente = $ValorCusto;
																						$CC = 800; # Patrimônio
																				}elseif($TipoMaterial == "D"){
																						$ValorCustoDidatico = $ValorCusto;
																						$CC = 799; # Almoxarifado
																				}elseif($TipoMaterial == "F"){
																						$ValorCustoFardamento = $ValorCusto;
																						$CC = 799; # Almoxarifado
																				}elseif($TipoMaterial == "L"){
																						$ValorCustoLimpeza = $ValorCusto;
																						$CC = 799; # Almoxarifado
																				}

																				$sql  = "SELECT DISTINCT A.CCENPOCORG, A.CCENPOUNID, C.CALMPONRPA ";
																				$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBALMOXARIFADOORGAO B, SFPC.TBALMOXARIFADOPORTAL C ";
																				$sql .= " WHERE A.CORGLICODI = B.CORGLICODI ";
																				$sql .= "   AND B.CALMPOCODI = C.CALMPOCODI ";
																				$sql .= "   AND B.CALMPOCODI = $Almoxarifado AND A.CCENPOCENT = $CC AND A.CCENPODETA = $Det ";
																				$sql .= "   AND (A.FCENPOSITU IS NULL OR A.FCENPOSITU <> 'I') "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
																				$res  = $db->query($sql);
																				if(PEAR::isError($res)){
																						$db->query("ROLLBACK");
																						$db->query("END TRANSACTION");
																						$db->disconnect();
																						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																				}else{
																						$Linha        = $res->fetchRow();
																						$Orgao        = $Linha[0];
																						$Unidade      = $Linha[1];
																						$RPA          = $Linha[2];
																						$CentroCusto  = $Linha[3];
																						$Detalhamento = $Linha[4];

																						$ValorCustoPermanente = sprintf("%01.2f",round($ValorCustoPermanente,2));
																						$ValorCustoConsumo    = sprintf("%01.2f",round($ValorCustoConsumo,2));
																						$ValorCustoDidatico   = sprintf("%01.2f",round($ValorCustoDidatico,2));
																						$ValorCustoFardamento = sprintf("%01.2f",round($ValorCustoFardamento,2));
																						$ValorCustoLimpeza    = sprintf("%01.2f",round($ValorCustoLimpeza,2));
																						$TimeStamp            = $DataGravacao;
																						$DiaBaixa             = date("d");
																						$MesBaixa             = date("m");
																						$AnoBaixa             = date("Y");
																						# GERA CUSTO PARA O ORACLE #
																						$db->query("ROLLBACK");
																						$db->query("END TRANSACTION");
																						include "../oracle/estoques/RotIncluirMovimentoCustoContabil.php";
																						exit;
																				}
																		}

																}else{ # Commita movimetações que não vão para o Oracle
																		$db->query("COMMIT");
																		//$db->query("ROLLBACK");
																		$db->query("END TRANSACTION");
																		$db->disconnect();

																		# Envia mensagem para página selecionar #
																		$Mensagem = urlencode("Movimentação Alterada com Sucesso");
																		$Url = "CadMovimentacaoSelecionar.php?Mensagem=$Mensagem&Mens=1&Tipo=1";
																		if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
																		header("location: ".$Url);
																		exit;

																}
														}
												}
										}
								}
						}
				}
		}
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function enviar(valor){
	document.CadMovimentacaoAlterar.Botao.value = valor;
	document.CadMovimentacaoAlterar.submit();
}
<?php MenuAcesso(); ?>
function ncaracteresO(valor){
	document.CadMovimentacaoAlterar.NCaracteresO.value = '' +  document.CadMovimentacaoAlterar.Observacao.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadMovimentacaoAlterar.NCaracteresO.focus();
	}
}
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadMovimentacaoAlterar.php" method="post" name="CadMovimentacaoAlterar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Movimentação > Manter
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Virgula); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
	<td width="150"></td>
		<td class="textonormal">
			<table border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									ALTERAR - MOVIMENTAÇÃO DE MATERIAL
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para Atualizar a Movimentação do Material, digite a quantidade a ser movimentada e clique no botão "Alterar".
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
											<td class="textonormal">
												<?php
												# Mostra a Descrição de Acordo com o Almoxarifado #
												$db   = Conexao();
												$sql  = "SELECT EALMPODESC FROM  SFPC.TBALMOXARIFADOPORTAL";
												$sql .= " WHERE CALMPOCODI = $Almoxarifado AND FALMPOSITU = 'A'";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														echo "$Linha[0]";
												}
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização</td>
											<td class="textonormal">
												<?php
												# Mostra a Descrição de Acordo com o Almoxarifado #
												$sql  = "SELECT A.FLOCMAEQUI, A.ALOCMANEQU, A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
												$sql .= "  FROM  SFPC.TBLOCALIZACAOMATERIAL A,  SFPC.TBAREAALMOXARIFADO B";
												$sql .= " WHERE A.CLOCMACODI = $Localizacao AND A.FLOCMASITU = 'A'";
												$sql .= "   AND A.CALMPOCODI = B.CALMPOCODI ";
												$res  = $db->query($sql);
												if( PEAR::isError($res) ){
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
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Tipo de Movimentação</td>
											<td class="textonormal">
												<?php
												# Mostra o tipo de Movimentação#
												if($TipoMovimentacao == "E"){
														echo "ENTRADA";
												}elseif($TipoMovimentacao == "S"){
														echo "SAÍDA";
												}
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Movimentação</td>
											<td class="textonormal"><?php echo $DescMovimentacao; ?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Número da Movimentação</td>
											<td class="textonormal"><?php echo $MovNumero; ?></td>
										</tr>
										<?php if($Movimentacao == 2 or $Movimentacao == 19 or $Movimentacao == 20){ ?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Número/Ano da Requisição</td>
											<td class="textonormal" colspan="2">
											<?php
											# Mostra o material e a unidade #
											$sql  = "SELECT A.CREQMACODI, A.AREQMAANOR, B.CTIPSRCODI ";
											$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL A, SFPC.TBSITUACAOREQUISICAO B ";
											$sql .= " WHERE A.CREQMASEQU = $SeqRequisicao AND A.CREQMASEQU = B.CREQMASEQU ";
											$sql .= "   AND B.TSITREULAT IN ";
											$sql .= "       (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO SIT";
											$sql .= "           WHERE SIT.CREQMASEQU = A.CREQMASEQU) ";
											$res  = $db->query($sql);
											if(PEAR::isError($res)){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											}else{
													$Linha            = $res->fetchRow();
													$Requisicao       = $Linha[0];
													$AnoRequisicao    = $Linha[1];
													$Situacao         = $Linha[2];
													echo substr($Requisicao+100000,1)."/".$AnoRequisicao;
											}
											?>
											</td>
										</tr>
										<?php } ?>
 										<tr>
										  <td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Material</td>
										  <td class="textonormal"><?php echo $DescMaterial; ?></td>
										</tr>
 										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Unidade</td>
											<td class="textonormal"><?php echo $UnidSigl; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Quantidade</td>
											<td class="textonormal">
												<input type="text" maxlength="10" name="QtdAtual" value="<?php echo converte_quant($QtdAtual); ?>" class="textonormal">
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Matrícula do Responsável pela Autorização da Movimentação*</td>
											<td class="textonormal">
												<input type="text" size=9 maxlength=9 name="Matricula" value="<?php echo $Matricula; ?>" class="textonormal">
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Nome do Responsável*</td>
											<td class="textonormal">
												<input type="text" size=60 maxlength=100 name="Responsavel" value="<?php echo $Responsavel; ?>" class="textonormal">
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7">Observação</td>
											<td class="textonormal">
												<font class="textonormal">máximo de 200 caracteres</font>
												<input type="text" name="NCaracteresO" disabled size="3" value="<?php echo $NCaracteresO ?>" class="textonormal"><br>
												<textarea name="Observacao" cols="50" rows="4" OnKeyUp="javascript:ncaracteresO(1)" OnBlur="javascript:ncaracteresO(0)" OnSelect="javascript:ncaracteresO(1)" class="textonormal"><?php echo $Observacao; ?></textarea>
											</td>
										</tr>
										<?php
										# Exibe as movimentacoes relacionadas #
										$db = Conexao();
										$sql  = "SELECT MOV.DMOVMAMOVI, TIP.ETIPMVDESC, MOV.AMOVMAQTDM, MOV.CMOVMACODT ";
										$sql .= "  FROM SFPC.TBMOVIMENTACAOMATERIAL MOV, ";
										$sql .= "       SFPC.TBTIPOMOVIMENTACAO TIP ";
										$sql .= " WHERE MOV.CALMPOCOD1 = $Almoxarifado ";
										$sql .= "   AND MOV.AMOVMAANO1 = $AnoMovimentacao ";
										$sql .= "   AND MOV.CMOVMACOD1 = $Sequencial ";
										$sql .= "   AND TIP.CTIPMVCODI = MOV.CTIPMVCODI ";
										$sql .= " ORDER BY TIP.ETIPMVDESC, MOV.CMOVMACODT, MOV.DMOVMAMOVI";
										$res    = $db->query($sql);
										if( PEAR::isError($res) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Rows = $res->numRows();
												if ($Rows > 0){
														echo "<tr>\n";
														echo "<td colspan=\"2\">\n";
														echo "<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bgcolor=\"#bfdaf2\" bordercolor=\"#75ADE6\" width=\"100%\" summary=\"\">\n";
														echo "<tr>\n";
														echo "<td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\" colspan=\"3\">\n";
														echo "MOVIMENTAÇÔES CORRESPONDENTES\n";
														echo "</td>\n";
														echo "</tr>\n";
														for ($row=0;$Row<$Rows;$Row++){
																$Linha = $res->fetchRow();
																if($MovimentacaoExibida!=$Linha[1]) {
																		$MovimentacaoExibida = $Linha[1];
																		echo "<tr>\n";
																		echo "	<td align=\"center\" bgcolor=\"#BFDAF2\" colspan=\"3\" class=\"titulo3\">$Linha[1]</td>\n";
																		echo "</tr>\n";
																		echo "<tr>\n";
																		echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"CENTER\">CÓD MOV</td>\n";
																		echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"CENTER\">DATA</td>\n";
																		echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" align=\"center\">QTD MOVIMENTADA</td>\n";
																		echo "</tr>\n";
																}
																# Exibe o conteúdo das movimentações
																echo "<tr>\n";
																echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">$Linha[3]</td>\n";
																echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">".databarra($Linha[0])."</td>\n";
																echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\">".converte_quant($Linha[2])."</td>\n";
																echo "</tr>\n";

														}
														echo "</table>\n";
														echo "</td>\n";
														echo "</tr>\n";
												}
										}
										$db->disconnect();
										?>
								</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right">
									<input type="hidden" name="SeqRequisicao" value="<?php echo $SeqRequisicao; ?>">
									<input type="hidden" name="Situacao" value="<?php echo $Situacao; ?>">
									<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
									<input type="hidden" name="Sequencial" value="<?php echo $Sequencial ?>">
									<input type="hidden" name="AnoMovimentacao" value="<?php echo $AnoMovimentacao ?>">
									<input type="hidden" name="Material" value="<?php echo $Material ?>">
									<input type="hidden" name="TipoMovimentacao" value="<?php echo $TipoMovimentacao ?>">
									<input type="hidden" name="Localizacao" value="<?php echo $Localizacao ?>">
									<input type="hidden" name="Movimentacao" value="<?php echo $Movimentacao ?>">
									<input type="hidden" name="MovNumero" value="<?php echo $MovNumero ?>">
									<input type="hidden" name="DescMovimentacao" value="<?php echo $DescMovimentacao ?>">
									<input type="hidden" name="QtdOriginal" value="<?php echo $QtdOriginal ?>">
									<input type="hidden" name="QtdMovimentada" value="<?php echo $QtdMovimentada ?>">
									<input type="hidden" name="UnidSigl" value="<?php echo $UnidSigl ?>">
									<input type="hidden" name="DescMaterial" value="<?php echo $DescMaterial ?>">
									<input type="hidden" name="Valor" value="<?php echo $Valor ?>">
									<input type="button" name="Alterar" value="Alterar" class="botao" onClick="javascript:enviar('Alterar');">
									<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar');">
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
</html>
