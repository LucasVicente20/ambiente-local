<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadInventarioInicialContagem.php
# Objetivo: Programa de Carga Inicial em Estoque Fixo
# Autor:    Carlos Abreu
# Data:     16/11/2006
# Alterado: Carlos Abreu
# Data:     04/06/2007 - Filtro no combo do almoxarifado para que quando usuario for do tipo 
#                        atendimento apareça apenas o almox. que ele esteja relacionado
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Aumenta o tempo de espera do servidor web para término de execução da página #
set_time_limit(600);

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadIncluirItem.php' );
AddMenuAcesso( '/estoques/CadItemDetalhe.php' );

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao               = $_POST['Botao'];
		$InicioPrograma      = $_POST['InicioPrograma'];
		$Montou              = $_POST['Montou'];
		$Almoxarifado        = $_POST['Almoxarifado'];
		$DescAlmoxarifado    = $_POST['DescAlmoxarifado'];
		$CarregaAlmoxarifado = $_POST['CarregaAlmoxarifado'];
		$Localizacao	     = $_POST['Localizacao'];
		$CarregaLocalizacao  = $_POST['CarregaLocalizacao'];
		$CheckItem           = $_POST['CheckItem'];
		$ItemCarga           = $_POST['ItemCarga'];
		$QtdEstocada         = $_POST['QtdEstocada'];
		$ValorUnitario       = $_POST['ValorUnitario'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
if($Botao == "Voltar"){
		header("location: CadInventarioInicialContagem.php");
		exit;
}elseif($Botao == "Carregar"){
		unset($_SESSION['item']);
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
				$Mensagem .= "<a href=\"javascript:document.CadInventarioInicialContagem.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
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
				$Mensagem .= "<a href=\"javascript:document.CadInventarioInicialContagem.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}
		if(count($QtdEstocada) != 0){
				if($Existe == ""){
						$Posicao = "";
						for($i=0;$i<count($QtdEstocada);$i++){
								if( ( ! SoNumVirg($QtdEstocada[$i]) ) and $QtdEstocada[$i] != "" and $Existe == "" ){
										$Existe  = "S";
										$Posicao = $i;
								}
						}
						if($Existe == ""){
								for( $k=0;$k<count($QtdEstocada);$k++ ){
										if( ( ! SoNumVirg($QtdEstocada[$i]) ) and $QtdEstocada[$i] != "" and str_replace(",",".",$QtdEstocada[$k]) != 0 and $Existe == "" ){
												$Existe  = "S";
												$Posicao = $k;
										}
								}
						}
						if($Existe == ""){
								for($j=0;$j<count($QtdEstocada);$j++){
										if( ( ! Decimal($QtdEstocada[$j]) ) and $Existe == "" ){
												$Existe  = "S";
												$Posicao = $j;
										}
								}
						}
						if( $Existe == "S" ){
								if( $Mens == 1 ){ $Mensagem .= ", "; }
								$Posicao   = ( $Posicao * 4 ) + 7;
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadInventarioInicialContagem.elements[$Posicao].focus();\" class=\"titulo2\">Quantidade Válida</a>";
						}
				}
		}
		if(count($ValorUnitario) != 0 ){
				if($Existe == ""){
						$PosVal = "";
						for($i=0;$i<count($ValorUnitario);$i++){
								if( ( ! SoNumVirg($ValorUnitario[$i]) ) and $ValorUnitario[$i] != "" and $Existe == "" ){
										$Existe = "S";
										$PosVal = $i;
								}
						}
						if($Existe == ""){
								for($k=0;$k<count($ValorUnitario);$k++){
										if( ( ! SoNumVirg($ValorUnitario[$k]) ) and $ValorUnitario[$k] != "" and str_replace(",",".",$ValorUnitario[$k]) != 0 and $Existe == "" ){
												$Existe = "S";
												$PosVal = $k;
										}
								}
						}
						if($Existe == ""){
								for($j=0;$j<count($ValorUnitario);$j++){
										if( ( ! DecimalValor($ValorUnitario[$j]) ) and $Existe == "" ){
												$Existe = "S";
												$PosVal = $j;
										}
								}
						}
						if($Existe == "S"){
								if($Mens == 1){ $Mensagem .= ", "; }
								$PosVal    = ( $PosVal * 4 ) + 8;
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "<a href=\"javascript:document.CadInventarioInicialContagem.elements[$PosVal].focus();\" class=\"titulo2\">Valor Unitário Válido</a>";
						}
				}
		}
		if($Mens == 0){
				$db = Conexao();
				$db->query("BEGIN TRANSACTION");
				# Apaga item da carga inicial - Verifica se o item está ligado a alguma movimentação #
				for($i=0; $i< count($_SESSION['ItemDelete']) and $Rollback == ""; $i++){
						$Dados          = explode($SimboloConcatenacaoDesc,$_SESSION['ItemDelete'][$i]);
						$MaterialDel    = $Dados[0];
						$LocalizacaoDel = $Dados[1];

						# Apaga os itens que foram selecionados pelo botão retirar #
						$sql  = "DELETE ";
						$sql .= "  FROM SFPC.TBINVENTARIOMATERIAL ";
						$sql .= " WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) = ( ";
						$sql .= "       SELECT CLOCMACODI, AINVCOANOB,MAX(AINVCOSEQU) AS AINVCOSEQU ";
						$sql .= "         FROM SFPC.TBINVENTARIOCONTAGEM ";
						$sql .= "        WHERE CLOCMACODI=$Localizacao ";
						$sql .= "          AND AINVCOANOB=( ";
						$sql .= "              SELECT MAX(AINVCOANOB) ";
						$sql .= "                FROM SFPC.TBINVENTARIOCONTAGEM ";
						$sql .= "               WHERE CLOCMACODI=$Localizacao";
						$sql .= "              ) ";
						$sql .= "        GROUP BY CLOCMACODI, AINVCOANOB ";
						$sql .= "       ) ";
						$sql .= "   AND CMATEPSEQU=$MaterialDel ";
						$res  = $db->query($sql);
						if(db::isError($res)){
								$Rollback = 1;
								$db->query("ROLLBACK");
								$db->query("END TRANSACTION");
								$db->disconnect();
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Apagou = "S";
						}
				}
				# Atualiza/Insere itens na carga inicial #
				for( $i=0; $i< count($ItemCarga) and !$Rollback; $i++ ){
						$Dados = explode($SimboloConcatenacaoArray,$ItemCarga[$i]);
						$DescMaterial    = $Dados[0];
						$Material        = $Dados[1];
						$Unidade         = $Dados[2];
						$QtdEst          = $QtdEstocada[$i];   // Dados que vieram do post
						$ValorUnit       = $ValorUnitario[$i]; // Dados que vieram do post
						$Movimentado     = $Dados[5];

						# Só atualiza ou insere se o item não estiver ligado a uma requisição ou inventário ou nota fiscal #
						if(!$Movimentado){
								$Carregou = "";
								# Trocando a vírgula pelo ponto #
								if($QtdEst == ""){ $Quantidade = 0; }else{ $Quantidade = str_replace(",",".",$QtdEst); }
								if($ValorUnit == ""){ $Valor = "NULL"; }else{ $Valor = str_replace(",",".",$ValorUnit); }

								# Verifica se existe o Material em Estoque #
								$sqlest  = "SELECT A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU, COUNT(B.*) ";
								$sqlest .= "  FROM SFPC.TBINVENTARIOCONTAGEM A ";
								$sqlest .= "  LEFT OUTER JOIN SFPC.TBINVENTARIOMATERIAL B ";
								$sqlest .= "    ON A.CLOCMACODI=B.CLOCMACODI ";
								$sqlest .= "   AND A.AINVCOANOB=B.AINVCOANOB ";
								$sqlest .= "   AND A.AINVCOSEQU=B.AINVCOSEQU ";
								$sqlest .= "   AND B.CMATEPSEQU=$Material ";
								$sqlest .= " WHERE A.CLOCMACODI=$Localizacao "; 
								$sqlest .= "   AND A.AINVCOANOB=( ";
								$sqlest .= "       SELECT MAX(AINVCOANOB) ";
								$sqlest .= "         FROM SFPC.TBINVENTARIOCONTAGEM ";
								$sqlest .= "        WHERE CLOCMACODI=$Localizacao";
								$sqlest .= "       ) ";
								$sqlest .= " GROUP BY A.CLOCMACODI, A.AINVCOANOB";
								$resest  = $db->query($sqlest);
								if(db::isError($resest)){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlest");
								}else{
										$Linha = $resest->fetchRow();
										$Ano        = $Linha[0];
										$Sequencial = $Linha[1];
										$Qtd        = $Linha[2];
										# Se o material não existe no estoque Insere #
										if($Qtd == 0){
												# Inserindo Itens em Armazenamento Material #
												$sql  = "INSERT INTO SFPC.TBINVENTARIOMATERIAL (";
												$sql .= "                                       CLOCMACODI, CMATEPSEQU, AINVCOANOB, AINVCOSEQU, AINVMAESTO, ";
												$sql .= "                                       VINVMAUNIT, TINVMAULAT";
												$sql .= "                                      ) VALUES (";
												$sql .= "                                       $Localizacao, $Material, $Ano, $Sequencial, $Quantidade, ";
												$sql .= "                                       $Valor, '".date("Y-m-d H:i:s")."'";
												$sql .= "                                      )";
												$res  = $db->query($sql);
												if(db::isError($res)){
														$Rollback = 1;
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Carregou = "S";
												}
										}else{
												# Se existe, atualiza os Itens em Armazenamento Material #
												$sql  = "UPDATE SFPC.TBINVENTARIOMATERIAL";
												$sql .= "   SET AINVMAESTO = $Quantidade, VINVMAUNIT = $Valor, TINVMAULAT = '".date("Y-m-d H:i:s")."'";
												$sql .= " WHERE CMATEPSEQU = $Material ";
												$sql .= "   AND CLOCMACODI = $Localizacao ";
												$sql .= "   AND AINVCOANOB = $Ano ";
												$sql .= "   AND AINVCOSEQU = $Sequencial ";
												$res  = $db->query($sql);
												if(db::isError($res)){
														$Rollback = 1;
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Carregou = "S";
												}
										}
								}
						}
				}
				
				/*
				$db   = Conexao();
				$sql  = " DELETE 
									  FROM SFPC.TBINVENTARIOMATERIAL 
									 WHERE (CLOCMACODI, AINVCOANOB, AINVCOSEQU) = ( 
									 				SELECT CLOCMACODI, AINVCOANOB,MAX(AINVCOSEQU) AS AINVCOSEQU 
									 				  FROM SFPC.TBINVENTARIOCONTAGEM 
									 				 WHERE CLOCMACODI=$Localizacao
									 				   AND AINVCOANOB=(SELECT MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM WHERE CLOCMACODI=$Localizacao)
									 				 GROUP BY CLOCMACODI, AINVCOANOB) 
										 AND CMATEPSEQU=$Material ";
				$res  = $db->query($sql);
				if(db::isError($res)){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						# Monta um Array para Deletar itens da Carga Inicial #
						if( $_SESSION['ItemDelete'] == "" or ! in_array($Material.$SimboloConcatenacaoDesc.$Localizacao,$_SESSION['ItemDelete']) ){
								$_SESSION['ItemDelete'][count($_SESSION['ItemDelete'])] = $Material.$SimboloConcatenacaoDesc.$Localizacao;
						}
				}
				$db->disconnect();
				*/
				
				
				if( ($Carregou == "S" or $Apagou == "S") and !$Rollback ){
						$Mens           = 1;
						$Tipo           = 1;
						$Mensagem       = "Contagem Salva com Sucesso";

						# Limpando as variáveis #
						$InicioPrograma = "";
						$Montou         = "";
						$Almoxarifado	  = "";
						$Localizacao	  = "";
						unset($ItemCarga);
						unset($QtdEstocada);
						unset($ValorUnitario);
						unset($_SESSION['item']);
						unset($_SESSION['ItemDelete']);
				}else{
						$Mens     = 1;
						$Tipo     = 1;
						$Mensagem = "Nenhuma Atualização foi Efetuada";
				}
				$db->query("COMMIT");
				$db->query("END TRANSACTION");
				$db->disconnect();
		}
}elseif($Botao == "Retirar"){
		if(count($ItemCarga) != 0){
				for($i=0; $i< count($ItemCarga); $i++){
						if($CheckItem[$i] == ""){
								$Qtd++;
								$CheckItem[$i]           = "";
								$ItemCarga[$Qtd-1]       = $ItemCarga[$i];
								$QtdEstocada[$Qtd-1]     = $QtdEstocada[$i];
								$ValorUnitario[$Qtd-1]   = $ValorUnitario[$i];
						}else{
								$ItemArray = explode("Æ",$ItemCarga[$i]);
								$Material = $ItemArray[1];
								# Monta um Array para Deletar itens da Carga Inicial #
								if( $_SESSION['ItemDelete'] == "" or ! in_array($Material.$SimboloConcatenacaoDesc.$Localizacao,$_SESSION['ItemDelete']) ){
										$_SESSION['ItemDelete'][count($_SESSION['ItemDelete'])] = $Material.$SimboloConcatenacaoDesc.$Localizacao;
								}
						}
				}
				if(count($ItemCarga) > 1){
						$ItemCarga       = array_slice($ItemCarga,0,$Qtd);
						$QtdEstocada     = array_slice($QtdEstocada,0,$Qtd);
						$ValorUnitario   = array_slice($ValorUnitario,0,$Qtd);
				}else{
						unset($ItemCarga);
						unset($QtdEstocada);
						unset($ValorUnitario);
				}
		}
		unset($_SESSION['item']);
}

if($Botao == "" and $Montou == ""){
		if($InicioPrograma == ""){
				unset($_SESSION['item']);
				unset($_SESSION['ItemDelete']);
		}else{
				if($Almoxarifado == ""){
						$Mens = 1;
						$Tipo = 2;
						$Mensagem = "Informe: <a href=\"javascript:document.CadInventarioInicialContagem.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
				}else{
						if($Localizacao == ""){
								if($Mens == 1) { $Mensagem .= ", "; }
								$Mens      = 1;
								$Tipo      = 2;
								$Mensagem .= "Informe: <a href=\"javascript:document.CadInventarioInicialContagem.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
						}
						if($InicioPrograma == 2 and $Localizacao == ""){
								if($Mens == 1){ $Mensagem .= ", "; }
								$Mens = 1;
								$Tipo = 2;
								$Mensagem .= "Informe: <a href=\"javascript:document.CadInventarioInicialContagem.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
						}else{
								if($Localizacao != ""){
										# Carrega os dados do Estoque de acordo com a Localização #
										$db   = Conexao();
										$sql  = "SELECT A.CMATEPSEQU, A.AINVMAESTO, B.EMATEPDESC, C.EUNIDMSIGL, A.VINVMAUNIT ";
										$sql .= "  FROM SFPC.TBINVENTARIOMATERIAL A, ";
										$sql .= "       SFPC.TBMATERIALPORTAL B, ";
										$sql .= "       SFPC.TBUNIDADEDEMEDIDA C ";
										$sql .= " WHERE (A.CLOCMACODI, A.AINVCOANOB, A.AINVCOSEQU) = ( ";
										$sql .= "       SELECT A.CLOCMACODI, A.AINVCOANOB, MAX(A.AINVCOSEQU) AS AINVCOSEQU ";
										$sql .= "         FROM SFPC.TBINVENTARIOCONTAGEM A ";
										$sql .= "         LEFT OUTER JOIN SFPC.TBINVENTARIOMATERIAL B ";
										$sql .= "           ON A.CLOCMACODI=B.CLOCMACODI ";
										$sql .= "          AND A.AINVCOANOB=B.AINVCOANOB ";
										$sql .= "          AND A.AINVCOSEQU=B.AINVCOSEQU ";
										$sql .= "        WHERE A.CLOCMACODI=$Localizacao ";
										$sql .= "          AND A.AINVCOANOB=( ";
										$sql .= "              SELECT MAX(AINVCOANOB)  ";
										$sql .= "                FROM SFPC.TBINVENTARIOCONTAGEM  ";
										$sql .= "               WHERE CLOCMACODI=$Localizacao ";
										$sql .= "              ) ";
										$sql .= "        GROUP BY A.CLOCMACODI, A.AINVCOANOB ";
										$sql .= "       ) ";
										$sql .= "   AND A.CMATEPSEQU = B.CMATEPSEQU ";
										$sql .= "   AND B.CUNIDMCODI = C.CUNIDMCODI ";
										$sql .= " ORDER BY TRANSLATE(B.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC')";
										$res  = $db->query($sql);
										if(db::isError($res)){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$j = 0;
												while($Linha = $res->fetchRow()){
														$Movimentado[$j]      = null;
														$MaterialProx         = $Linha[0];
														$QtdEstocadaProx      = str_replace(".",",",$Linha[1]);
														$DescMaterialProx     = str_replace("\"","”",$Linha[2]);
														$UnidadeProx          = $Linha[3];
														$ValorUnitarioProx    = str_replace(".",",",$Linha[4]);
														$TipoMovimentacaoProx = $Linha[5];

														# Checa se houve movimentações neste item #
														if($TipoMovimentacaoAtual){
																$Movimentado[$j] = 1;
														}
														
														if($MaterialProx != $MaterialAtual) {
																if($MaterialAtual){
																		$ItemCarga[$j] = $DescMaterialAtual.$SimboloConcatenacaoArray.$MaterialAtual.$SimboloConcatenacaoArray.$UnidadeAtual.$SimboloConcatenacaoArray.$QtdEstocadaAtual.$SimboloConcatenacaoArray.$ValorUnitarioAtual.$SimboloConcatenacaoArray.$Movimentado[$j];
																		$j++;
																}
																$MaterialAtual         = $MaterialProx;
																$QtdEstocadaAtual      = $QtdEstocadaProx;
																$DescMaterialAtual     = $DescMaterialProx;
																$UnidadeAtual          = $UnidadeProx;
																$ValorUnitarioAtual    = $ValorUnitarioProx;
																$TipoMovimentacaoAtual = $TipoMovimentacaoProx;
														}
														$Montou = "S";
												}
												
												# Para receber a última ocorrência já fora do loop #
												if($MaterialAtual){
														# Checa se houve movimentações deste último item #
														if ($TipoMovimentacaoAtual) {
																$Movimentado[$j] = 1;
														}
														$ItemCarga[$j] = $DescMaterialAtual.$SimboloConcatenacaoArray.$MaterialAtual.$SimboloConcatenacaoArray.$UnidadeAtual.$SimboloConcatenacaoArray.$QtdEstocadaAtual.$SimboloConcatenacaoArray.$ValorUnitarioAtual.$SimboloConcatenacaoArray.$Movimentado[$j];
														$j++;
												}

										}
											$db->disconnect();
								}
						}
				}
		}
}

# Monta o array de itens da Carga de material com os dados que vieram da Inclusão de itens #
if( count($_SESSION['item']) != 0 ){
		sort($_SESSION['item']);
		
		# Retira o primeiro bloco da descrição (sem acentuação) #
		$ItensAdd = $_SESSION['item'];
		for($j=0;$j<count($ItensAdd);$j++){
				$TiraSemAcento = explode("æ",$ItensAdd[$j]);
				$ItensAdd[$j]  = $TiraSemAcento[1];
		}
		
		if( $ItemCarga == "" ){
				for( $i=0;$i<count($ItensAdd);$i++ ){
						$ItemCarga[count($ItemCarga)] = $ItensAdd[$i];
				}
		}else{
				for( $i=0;$i<count($ItemCarga);$i++ ){
						$DadosItem            = explode($SimboloConcatenacaoArray,$ItemCarga[$i]);
						$SequencialItem[$i]   = $DadosItem[1];
				}
			  for( $i=0;$i<count($ItensAdd);$i++ ){
						$DadosSessao          = explode($SimboloConcatenacaoArray,$ItensAdd[$i]);
						$SequencialSessao[$i] = $DadosSessao[1];
				 		if( ! in_array($SequencialSessao[$i],$SequencialItem) ){
			  				$ItemCarga[count($ItemCarga)] = $ItensAdd[$i];
			 			}
		 		}
    }
    unset($_SESSION['item']);
}

?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadInventarioInicialContagem.Botao.value = valor;
	document.CadInventarioInicialContagem.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
}
function AbreJanelaItem(url,largura,altura) {
	if( ! document.CadInventarioInicialContagem.Almoxarifado.value ){
		document.CadInventarioInicialContagem.submit();
	}else	if( ! document.CadInventarioInicialContagem.Localizacao.value ){
		document.CadInventarioInicialContagem.InicioPrograma.value = 2;
		document.CadInventarioInicialContagem.submit();
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
<form action="CadInventarioInicialContagem.php" method="post" name="CadInventarioInicialContagem">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Inventário > Inicial > Contagem
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
			<table  border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									INVENTÁRIO INICIAL - CONTAGEM
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para efetuar uma Carga Inicial em Estoque, informe os dados abaixo, escolha os itens desejados informe a Quantidade e o Valor Unitário e clique no botão "Salvar". Os itens obrigatórios estão com *.
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
												# CORREÇÃO FORÇADA PARA ACEITAR EQUIPE DA DLC CADASTRANDO A CARGA INICIAL DE TODOS OS ALMOXARIFADOS
												# CADASTRADOS SEM SER PELO PERFIL/USUÁRIO --- RETIRAR ---
												
												if( ($_SESSION['_cgrempcodi_'] == 0) or ($_SESSION['_fperficorp_'] == 'S')){
														$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A ";
														$sql .= "  LEFT OUTER JOIN SFPC.TBMOVIMENTACAOMATERIAL C ";
														$sql .= "    ON A.CALMPOCODI = C.CALMPOCODI ";
														$sql .= " WHERE A.FALMPOSITU = 'A' ";
														$sql .= "   AND A.FALMPOINVE = 'S' ";
														$sql .= " GROUP BY A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "HAVING COUNT(C.*)=0";
												} else {
														$sql  = "SELECT A.CALMPOCODI, A.EALMPODESC, COUNT(C.*) ";
														$sql .= "  FROM SFPC.TBALMOXARIFADOPORTAL A ";
														$sql .= "  LEFT OUTER JOIN SFPC.TBMOVIMENTACAOMATERIAL C ";
														$sql .= "    ON A.CALMPOCODI = C.CALMPOCODI, ";
														$sql .= "       SFPC.TBALMOXARIFADOORGAO B ";
														$sql .= " WHERE A.CALMPOCODI = B.CALMPOCODI ";
														$sql .= "	  AND A.FALMPOSITU = 'A' ";
														$sql .= "   AND A.FALMPOINVE = 'S' ";
														$sql .= "   AND B.CORGLICODI IN ( ";
														$sql .= "       SELECT DISTINCT CEN.CORGLICODI ";
														$sql .= "         FROM SFPC.TBCENTROCUSTOPORTAL CEN, SFPC.TBUSUARIOCENTROCUSTO USU ";
														$sql .= "        WHERE USU.CCENPOSEQU = CEN.CCENPOSEQU AND USU.FUSUCCTIPO IN ('T','R') ";
														$sql .= "          AND USU.CUSUPOCODI =  ".$_SESSION['_cusupocodi_']." ";
														$sql .= "          AND CEN.FCENPOSITU <> 'I'";
														
														# restringir almoxarifado quando requisitante
														$sql .= "            AND CASE WHEN USU.FUSUCCTIPO = 'T' THEN B.CALMPOCODI = USU.CALMPOCODI ELSE CEN.FCENPOSITU <> 'I' END";
														
														$sql .= "       ) ";
														$sql .= " GROUP BY A.CALMPOCODI, A.EALMPODESC ";
														$sql .= "HAVING COUNT(C.*)=0";
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
														}else{
																echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
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
																}else{
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
										<?php }
										if( ( $Almoxarifado != "" ) and ($Localizacao != "" )){
												$db   = Conexao();
												# Calcula o total de itens da carga inicial do almoxarifado selecionado #
												
												$sqltot  = "SELECT COUNT(B.*)
																			FROM (
																			SELECT CLOCMACODI, AINVCOANOB, MAX(AINVCOSEQU) AS AINVCOSEQU
																			FROM SFPC.TBINVENTARIOCONTAGEM
																			WHERE CLOCMACODI=$Localizacao
																			AND AINVCOANOB=(SELECT MAX(AINVCOANOB) FROM SFPC.TBINVENTARIOCONTAGEM WHERE CLOCMACODI=$Localizacao)
																			GROUP BY CLOCMACODI, AINVCOANOB
																			) AS A LEFT OUTER JOIN SFPC.TBINVENTARIOMATERIAL AS B
																			 ON A.CLOCMACODI=B.CLOCMACODI
																			AND A.AINVCOANOB=B.AINVCOANOB
																			AND A.AINVCOSEQU=B.AINVCOSEQU
																		GROUP BY A.CLOCMACODI, A.AINVCOANOB, A.AINVCOSEQU";
												
												$restot  = $db->query($sqltot);
												if(db::isError($restot)){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqltot");
												}else{
														$Qtd               = $restot->fetchRow();
														$TotalItens			   = $Qtd[0];
												}
												echo "<tr>";
												echo "   <td class=\"textonormal\" bgcolor=\"#DCEDF7\" height=\"20\">Total de Itens Carregados</td>";
												echo "   <td class=\"textonormal\">";
												echo $TotalItens;
												echo "	  	<input type=\"hidden\" name=\"TotalItens\" value=$TotalItens;";
												echo "   </td>";
												echo "</tr>";
												$db->disconnect();
										}?>
										<tr>
											<td class="textonormal" colspan="4">
												<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
													<?php
													for($i=0;$i< count($ItemCarga);$i++){
															$Dados = explode($SimboloConcatenacaoArray,$ItemCarga[$i]);
															$DescMaterial    = $Dados[0];
															$Material        = $Dados[1];
															$Unidade         = $Dados[2];
															# Se os dados vierem de post, haverá informação no $QtdEstocada[$i] / $ValorUnitario[$i], usa tais valores #
															# Se não, a informação virá do explode da variável $ItemCarga, com os dados do banco de dados #
															if(!$QtdEstocada[$i]){
																	$QtdEst      = $Dados[3];
															}else{
																	$QtdEst      = $QtdEstocada[$i];
															}
															if(!$ValorUnitario[$i]){
																	$ValorUnit = $Dados[4];
															}else{
																	$ValorUnit = $ValorUnitario[$i];
															}
															$Movimentado     = $Dados[5];
															if($i == 0){
																	echo "		<tr>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" wdith=\"5%\">&nbsp;</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\">DESCRIÇÃO DO MATERIAL</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" wdith=\"5%\">UNIDADE</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"10%\">QUANTIDADE</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" align=\"center\" width=\"10%\">VALOR</td>\n";
																	echo "		</tr>\n";
															}
													?>
													<tr>
														<td class="textonormal" align="right">
														<?php
														if (!$Movimentado){
																echo "	<input type=\"checkbox\" name=\"CheckItem[$i]\" value=\"$Material\"\n";
														}else{
																echo "&nbsp;&nbsp;&nbsp;\n";
																# Inclue campo hidden apenas para não diferenciar a contagem dos elements para mensagens de erro, no caso da não aparição do checkbox #
																echo "<input type=\"hidden\" name=\"MaterialMovimentado[$i]\" value=\"$Material\"\n";
														}
														?>
														</td>
														<td class="textonormal">
															<?
															$Url = "CadItemDetalhe.php?ProgramaOrigem=CadRequisicaoMaterialIncluir&Material=$Material";
															if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
															?>
															<a href="javascript:AbreJanela('<?=$Url;?>',700,370);">
																<font color="#000000">
																	<?php
																	echo trim($DescMaterial);
																	?>
	  							    					</font>
  							    					</a>
  							    					<input type="hidden" name="ItemCarga[<?php echo $i; ?>]" value="<?php echo $ItemCarga[$i]; ?>">
							  	        	</td>
					              		<td class="textonormal" align="center">
					              			<?php echo $Unidade; ?>
				          	    		</td>
					              		<td class="textonormal" align="right">
			          	    			<?php
			          	    			if (!$Movimentado){
				          	    				if( $QtdEst == "" ){ $QtdEst = 0; }
				          	    				echo "<input type=\"text\" name=\"QtdEstocada[$i]\" size=\"11\" maxlength=\"11\" value=\"$QtdEst\" class=\"textonormal\">\n";
					          	    	}else{
				          	    				echo converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdEst)));
				          	    				echo "<input type=\"hidden\" name=\"QtdEstocada[$i]\" value=\"$QtdEst\">\n";
					          	    	}
					          	    	?>
				          	    		</td>
					              		<td class="textonormal" align="right">
			          	    			<?php
			          	    			if (!$Movimentado){
				          	    				echo "<input type=\"text\" name=\"ValorUnitario[$i]\" size=\"11\" maxlength=\"11\" value=\"$ValorUnit\" class=\"textonormal\">\n";
					          	    	}else{
			          	    					echo converte_valor_estoques(sprintf("%01.4f",str_replace(",",".",$ValorUnit)));
				          	    				echo "<input type=\"hidden\" name=\"ValorUnitario[$i]\" value=\"$ValorUnit\">\n";
					          	    	}
					          	    	?>
				          	    		</td>
								        	</tr>
								        	<?php } ?>
								        	<?php if ($Localizacao != "" ){?>
				            				<tr>
						   	  	  			<td class="textonormal" colspan="9" align="center">
					   	  	  				<?
											$Url = "CadIncluirItem.php?PesqApenas=C&ProgramaOrigem=CadInventarioInicialContagem&Almoxarifado=$Almoxarifado";
											if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
											?>
						         	      	<input type="button" name="IncluirItem" value="Incluir Item" class="botao" onclick="javascript:AbreJanelaItem('<?=$Url;?>',700,350);">
						         	      	<input type="button" name="Retirar" value="Retirar Item" class="botao" onClick="javascript:enviar('Retirar');">
						            		</td>
								        	</tr>
								        	<?php } ?>
								        </table>
								     	 </td>
										</tr>
	           			</table>
	           		</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
               		<input type="hidden" name="Montou" value="<?php echo $Montou; ?>">
               		<input type="hidden" name="InicioPrograma" value="1">
			  	      	<input type="button" name="Carregar" value="Salvar" class="botao" onClick="javascript:enviar('Carregar');">
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
