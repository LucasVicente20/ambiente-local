<?php
# ------------------------------------------------------------------------------------
# Portal da DGCO
# Programa:   RotAutoRelSinteticoEntradasSaidas.php
# Autor:      Álvaro Faria
# Data:       22/09/2006
# Objetivo:   Programa de geração de espelho de impressão do resumo de estoque e 
#             do mês anterior Para todos os almoxarifados. No caso das  
#             movimentações que foram criadas no mês anterior ao de referência
#             e modificadas no mês de referência, o sistema irá apresentar compensando
#             para mais ou para menos no mês de referência. Podendo para isso, gerar e
#             apresentar valores negativos. Isso também pode gerar relatórios com totais
#             diferentes entre este (sintético) e o RelEntradaNotaFiscal e o
#             Relatório de Movimentação por Tipo (Saída por requisição)
# OBS.:       Tabulação 2 espaços
#             Formatação do arquivo gerado: RELENTSAISINTETICO_$ANO_$MÊS_$ALMOXARIFADO.pdf
#             Este programa não está sendo usado diretamente pelo sistema. Está servindo
#             para garantir mensalmente as informações de movimentação dos almoxarifados
# ------------------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# ------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "/home/wwwdisco1/portal/html/pr/secfinancas/portalcompras/programas/funcoes.php";

# Define o tempo máximo para execução do script #
ini_set(max_execution_time, '1200');

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# FUNÇÕES DE RESGATE DE INFORMAÇÕES #
# Resgata o estoque em uma data definida, com cálculos no PHP #
function EstoqueData($Almoxarifado, $TipoMaterial, $DataPesquisa, $db, $ErroPrograma){
		# Converte a data para o formato de pesquisa no banco de dados
		if($DataPesquisa) $DataPesquisa = DataInvertida($DataPesquisa);
		# Resgata os itens do estoque #
		$sql  = " SELECT MAT.CMATEPSEQU, ARM.AARMATQTDE, ";
		$sql .= "        ARM.VARMATUMED, TIP.FTIPMVTIPO, MOV.AMOVMAQTDM ";
		$sql .= "   FROM SFPC.TBMATERIALPORTAL MAT, ";
		$sql .= "        SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBGRUPOMATERIALSERVICO GRU, ";
		$sql .= "        SFPC.TBLOCALIZACAOMATERIAL LOC, SFPC.TBARMAZENAMENTOMATERIAL ARM ";
		$sql .= "   LEFT OUTER JOIN SFPC.TBMOVIMENTACAOMATERIAL MOV ";
		$sql .= "     ON ARM.CMATEPSEQU = MOV.CMATEPSEQU ";
		$sql .= "    AND MOV.CALMPOCODI = $Almoxarifado ";
		$sql .= "    AND MOV.TMOVMAULAT > '$DataPesquisa 23:59:59' ";
		$sql .= "    AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') ";
		$sql .= "   LEFT OUTER JOIN SFPC.TBTIPOMOVIMENTACAO TIP ";
		$sql .= "     ON MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
		$sql .= "  WHERE MAT.CMATEPSEQU = ARM.CMATEPSEQU ";
		$sql .= "    AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
		$sql .= "    AND SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
		$sql .= "    AND LOC.CLOCMACODI = ARM.CLOCMACODI ";
		$sql .= "    AND LOC.CALMPOCODI = $Almoxarifado ";
		$sql .= "    AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
		$sql .= " ORDER BY MAT.CMATEPSEQU ";
		$res  = $db->query($sql);
		if( db::isError($res) ){
				$CodErroEmail  = $res->getCode();
				$DescErroEmail = $res->getMessage();
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		}else{
				# Linhas de Itens de Material #
				$rows = $res->numRows();
				if($rows == 0){
						$Totalizador = 0;
				}else{
						for($i=0; $i<= $rows; $i++){
								$Linha = $res->fetchRow();
								$CodigoReduzido[$i] = $Linha[0];
								$QtdEstoque[$i]     = $Linha[1];
								$ValorUnitario[$i]  = $Linha[2];
								$TipoMov[$i]        = $Linha[3];
								$QuantMov[$i]       = $Linha[4];
								if(!$MaterialTestado) $MaterialTestado = $CodigoReduzido[$i];
								if(!$j) $j=0;
								# Montando o array de Itens do Estoque #	
								if ($CodigoReduzido[$i] != $MaterialTestado) {
										$MaterialTestado = $CodigoReduzido[$i];
										# Descobre o valor unitário para o material na data especificada #
										$sqlvalor  = " SELECT VMOVMAUMED FROM SFPC.TBMOVIMENTACAOMATERIAL ";
										$sqlvalor .= "  WHERE CALMPOCODI = $Almoxarifado ";
										$sqlvalor .= "    AND CMATEPSEQU = ".$CodigoReduzido[$i-1]." ";
										$sqlvalor .= "    AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
										$sqlvalor .= "    AND TMOVMAULAT = ";
										$sqlvalor .= "        (SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
										$sqlvalor .= "          WHERE CALMPOCODI = $Almoxarifado ";
										$sqlvalor .= "            AND CMATEPSEQU = ".$CodigoReduzido[$i-1]." ";
										$sqlvalor .= "            AND TMOVMAULAT <= '$DataPesquisa 23:59:59' ";
										$sqlvalor .= "            AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') )";
										$resvalor    = $db->query($sqlvalor);
										if( db::isError($resvalor) ){
												$CodErroEmail  = $resvalor->getCode();
												$DescErroEmail = $resvalor->getMessage();
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlvalor\n\n$DescErroEmail ($CodErroEmail)");
										}else{
												$LinhaValor              = $resvalor->fetchRow();
												$ValorUnitarioPesquisado = $LinhaValor[0];
										}

										$QtdTotal        = $QtdTotal + $QtdEstoqueCalc;
										$Totalizador     = $Totalizador + ($QtdEstoqueCalc * $ValorUnitarioPesquisado);
										$j = $j + 1;
										
										if($TipoMov[$i] == 'S'){
												$QtdEstoqueCalc = $QtdEstoque[$i] + $QuantMov[$i];
										}elseif($TipoMov[$i] == 'E'){
												$QtdEstoqueCalc = $QtdEstoque[$i] - $QuantMov[$i];
										}else{
												$QtdEstoqueCalc = $QtdEstoque[$i];
										}
								}else{
										if($TipoMov[$i] == 'S'){
												if(is_null($QtdEstoqueCalc)) $QtdEstoqueCalc = $QtdEstoque[$i];
												$QtdEstoqueCalc = $QtdEstoqueCalc + $QuantMov[$i];
										}elseif($TipoMov[$i] == 'E'){
												if(is_null($QtdEstoqueCalc)) $QtdEstoqueCalc = $QtdEstoque[$i];
												$QtdEstoqueCalc = $QtdEstoqueCalc - $QuantMov[$i];
										}else{
												if(is_null($QtdEstoqueCalc)) $QtdEstoqueCalc = $QtdEstoque[$i]; // Para entrada do primeiro material resultante do select quando neste momento não tem movimentação modificando. A variavel recebe a quantidade em estoque, se esta variável ainda estiver vazia.
										}
								}
						}
				}
		}
		return $Totalizador;
}

# Resgata movimentações para exibição e cálculo de estoque em datas específicas #
function Movimentacoes($Almoxarifado, $TipoMov, $TipoMaterial, $DataPesquisa1, $DataPesquisa2, $Movimentacoes, $Operacao, $db, $ErroPrograma){
		# Converte a data para o formato de pesquisa no banco de dados
		if($DataPesquisa1) $DataPesquisa1 = DataInvertida($DataPesquisa1);
		if($DataPesquisa2) $DataPesquisa2 = DataInvertida($DataPesquisa2);
		$sql  = " SELECT SUM(MOV.AMOVMAQTDM*MOV.VMOVMAVALO) ";
		$sql .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL MOV, SFPC.TBTIPOMOVIMENTACAO TIP, ";
		$sql .= "        SFPC.TBMATERIALPORTAL MAT, ";
		$sql .= "        SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBGRUPOMATERIALSERVICO GRU ";
		$sql .= "  WHERE MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
		$sql .= "    AND MOV.CMATEPSEQU = MAT.CMATEPSEQU ";
		$sql .= "    AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
		$sql .= "    AND SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
		if($Movimentacoes){
				if($Operacao == 'I'){
						$sql .= "    AND MOV.CTIPMVCODI IN ($Movimentacoes)" ;
				}elseif($Operacao == 'N'){
						$sql .= "    AND MOV.CTIPMVCODI NOT IN ($Movimentacoes)" ;
				}
		}
		$sql .= "    AND TIP.FTIPMVTIPO = '$TipoMov' ";
		$sql .= "    AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
		$sql .= "    AND MOV.CALMPOCODI = $Almoxarifado ";
		$sql .= "    AND MOV.TMOVMAULAT > '$DataPesquisa1 23:59:59' ";
		if ($DataPesquisa2){
				$sql .= "    AND MOV.TMOVMAULAT <= '$DataPesquisa2 23:59:59' ";
		}
		$sql .= "    AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') "; // Traz só as movimentações ativas
		$res  = $db->query($sql);
		if( db::isError($res) ){
				$CodErroEmail  = $res->getCode();
				$DescErroEmail = $res->getMessage();
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql\n\n$DescErroEmail ($CodErroEmail)");
		}else{
				$Linha        = $res->fetchRow();
				$Movimentacao = $Linha[0];
				return $Movimentacao;
		}
}


# DEFININDO A DATA DA PESQUISA #
# Descobre a data atual #
$DataAtual   = date("d/m/Y");
//$DataAtual   = "20/09/2006"; # Para forçar uma data, descomentar esta linha
$DataAtual   = explode("/",$DataAtual);
$DiaAtual    = $DataAtual[0];
$MesAtual    = $DataAtual[1];
$AnoAtual    = $DataAtual[2];
if($MesAtual == 1){      // Se o mês for janeiro, a ano inicial e final de pesquisa será o anterior
		$AnoPesquisaF = $AnoAtual - 1;
		$MesPesquisaF = 12;
		$AnoPesquisaI = $AnoAtual - 1;
		$MesPesquisaI = 11;
		$Mes          = "DEZEMBRO";
}elseif($MesAtual == 2){ // Se o mês for fevereiro, a ano inicial de pesquisa será o anterior
		$AnoPesquisaF = $AnoAtual;
		$MesPesquisaF = 1;
		$AnoPesquisaI = $AnoAtual - 1;
		$MesPesquisaI = 12;
		$Mes          = "JANEIRO";
}else{
		$AnoPesquisaF = $AnoAtual;
		$MesPesquisaF = $MesAtual - 1;
		$AnoPesquisaI = $AnoAtual;
		$MesPesquisaI = $MesAtual - 2;
		$Meses = array("JANEIRO","FEVEREIRO","MARÇO","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");
		$Mes          = $Meses[$MesPesquisaF-1];
}
# Descobre os útimos dias dos meses #
$Dias1       = array("31","28","31","30","31","30","31","31","30","31","30","31");
$Dias2       = array("31","29","31","30","31","30","31","31","30","31","30","31");
if($AnoPesquisaI%4 == 0 ){
		$DiaPesquisaI = $Dias2[$MesPesquisaI-1];
}else{
		$DiaPesquisaI = $Dias1[$MesPesquisaI-1];
}
if($AnoPesquisaF%4 == 0 ){
		$DiaPesquisaF = $Dias2[$MesPesquisaF-1];
}else{
		$DiaPesquisaF = $Dias1[$MesPesquisaF-1];
}
$DataPesquisaI = sprintf("%02d",$DiaPesquisaI)."/".sprintf("%02d",$MesPesquisaI)."/".sprintf("%04d",$AnoPesquisaI);
$DataPesquisaF = sprintf("%02d",$DiaPesquisaF)."/".sprintf("%02d",$MesPesquisaF)."/".sprintf("%04d",$AnoPesquisaF);


# BUSCA INFORMAÇÕES NO BANCO #
# Conecta ao banco de dados #
$db = Conexao();
# Busca todos os almoxarifados cadastrados na SFPC.TBALMOXARIFADOPORTAL #
$sqlalmo  = "SELECT CALMPOCODI, EALMPODESC ";
$sqlalmo .= "  FROM SFPC.TBALMOXARIFADOPORTAL ";
$sqlalmo .= " ORDER BY CALMPOCODI ";
$resalmo = $db->query($sqlalmo);
if( db::isError($resalmo) ){
		$CodErroEmail  = $resalmo->getCode();
		$DescErroEmail = $resalmo->getMessage();
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlalmo\n\n$DescErroEmail ($CodErroEmail)");
}else{
		CabecalhoRodapePaisagem();
		while($Almox = $resalmo->fetchRow() ){
				$Almoxarifado = $Almox[0];
				$DescAlmox    = $Almox[1];
				# Gera nome de arquivo para este almoxarifado #
				$NomeArquivo  = "imagemrel/RELENTSAISINTETICO_";
				$NomeArquivo .= sprintf("%04d",$AnoPesquisaF);
				$NomeArquivo .= "_";
				$NomeArquivo .= sprintf("%02d",$MesPesquisaF);
				$NomeArquivo .= "_".$Almoxarifado.".pdf";
				
				# Verifica se o arquivo para este mês e para este almoxarifado já foi criado #
				# Função exibe o Cabeçalho e o Rodapé #
				if(!file_exists($NomeArquivo)){
						# Se não foi criado, INICIA UM PDF PARA ESTE ALMOXARIFADO #
						# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
						$pdf = new PDF("L","mm","A4");
						# Informa o Título do Relatório #
						$TituloRelatorio = "Relatório Sintético de Entradas e Saídas";
						# Define um apelido para o número total de páginas #
						$pdf->AliasNbPages();
						# Define as cores do preenchimentos que serão usados #
						$pdf->SetFillColor(220,220,220);
						# Adiciona uma página no documento #
						$pdf->AddPage();
						# Seta as fontes que serão usadas na impressão de strings #
						$pdf->SetFont("Arial","",9);
						
						$pdf->Cell(40,5,"ALMOXARIFADO",1,0,"L",1);
						$pdf->Cell(240,5,$DescAlmox,1,1,"L",0);
				
						# Imprime a data das informações #
						$pdf->Cell(40,5,"MÊS DE REFERÊNCIA",1,0,"L",1);
						$pdf->Cell(240,5,"$Mes",1,0,"L",0);
						$pdf->ln(8);
						
						# Cabeçalho da tabela #
						$pdf->Cell(40,5,"TIPO DO MATERIAL","TLR",0,"C",1);
						$pdf->Cell(40,5,"SALDO INICIAL","TLR",0,"C",1);
						$pdf->Cell(80,5,"ENTRADAS",1,0,"C",1);
						$pdf->Cell(80,5,"SAÍDAS",1,0,"C",1);
						$pdf->Cell(40,5,"SALDO FINAL","TLR",1,"C",1);
						
						$pdf->Cell(40,5,"","BLR",0,"L",1);
						$pdf->Cell(40,5,"$DataPesquisaI","BLR",0,"C",1);
						$pdf->Cell(40,5,"AQUISIÇÕES",1,0,"C",1);
						$pdf->Cell(40,5,"OUTRAS",1,0,"C",1);
						$pdf->Cell(40,5,"REQUISIÇÕES",1,0,"C",1);
						$pdf->Cell(40,5,"OUTRAS",1,0,"C",1);
						$pdf->Cell(40,5,"$DataPesquisaF","BLR",1,"C",1);
						
						# OBTEM AS INFORMAÇÕES NECESSÁRIAS PARA O RELATÓRIO ATRAVÉS DA INVOCAÇÃO ÀS FUNÇÕES #
						# Calcula o estoque do último dia do mês anterior #
						$EstoquePF       = EstoqueData($Almoxarifado, "P", $DataPesquisaF, $db, $ErroPrograma);
						$EstoqueCF       = EstoqueData($Almoxarifado, "C", $DataPesquisaF, $db, $ErroPrograma);
						# Calcula as movimentações (Requisição) dentro do mês anterior #
						$SaidasPMesReq   = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "4,20,22", "I", $db, $ErroPrograma);
						$SaidasCMesReq   = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "4,20,22", "I", $db, $ErroPrograma);
						$EntradasPMesReq = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "2,18,19,21", "I", $db, $ErroPrograma);
						$EntradasCMesReq = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "2,18,19,21", "I", $db, $ErroPrograma);
						$ResumoPMesReq   = $SaidasPMesReq - $EntradasPMesReq;
						$ResumoCMesReq   = $SaidasCMesReq - $EntradasCMesReq;
						# Calcula as movimentações (Aquisições) dentro do mês anterior #
						$EntradasPMesAqu = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "3,7", "I", $db, $ErroPrograma);
						$EntradasCMesAqu = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "3,7", "I", $db, $ErroPrograma);
						$SaidasPMesAqu   = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "8", "I", $db, $ErroPrograma);
						$SaidasCMesAqu   = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "8", "I", $db, $ErroPrograma);
						$ResumoPMesAqu   = $EntradasPMesAqu - $SaidasPMesAqu;
						$ResumoCMesAqu   = $EntradasCMesAqu - $SaidasCMesAqu;
						# Calcula as movimentações (Outras) dentro do mês anterior #
						$EntradasPMesOut = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "2,18,19,21,3,7", "N", $db, $ErroPrograma);
						$EntradasCMesOut = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "2,18,19,21,3,7", "N", $db, $ErroPrograma);
						$SaidasPMesOut   = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "4,20,22,8", "N", $db, $ErroPrograma);
						$SaidasCMesOut   = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "4,20,22,8", "N", $db, $ErroPrograma);
						# Calcula o estoque do último dia do mês anterior ao anterior#
						$EstoquePI = EstoqueData($Almoxarifado, "P", $DataPesquisaI, $db, $ErroPrograma);
						$EstoqueCI = EstoqueData($Almoxarifado, "C", $DataPesquisaI, $db, $ErroPrograma);
		
						# Resume as movimentações #
						$MovimentacaoP   = ($ResumoPMesAqu - $ResumoPMesReq) + ($EntradasPMesOut - $SaidasPMesOut);
						$MovimentacaoC   = ($ResumoCMesAqu - $ResumoCMesReq) + ($EntradasCMesOut - $SaidasCMesOut);
						
						# Calcula Totais #
						$EstoqueI       = $EstoqueCI       + $EstoquePI;
						$ResumoMesAqu   = $ResumoCMesAqu   + $ResumoPMesAqu;
						$EntradasMesOut = $EntradasCMesOut + $EntradasPMesOut;
						$ResumoMesReq   = $ResumoCMesReq   + $ResumoPMesReq;
						$SaidasMesOut   = $SaidasCMesOut   + $SaidasPMesOut;
						$EstoqueF       = $EstoqueCF       + $EstoquePF;
						
						# IMPRIME DADOS #
						# Dados Consumo #
						$EstoqueCI       = converte_valor_estoques($EstoqueCI);
						$ResumoCMesAqu   = converte_valor_estoques($ResumoCMesAqu);
						$EntradasCMesOut = converte_valor_estoques($EntradasCMesOut);
						$ResumoCMesReq   = converte_valor_estoques($ResumoCMesReq);
						$SaidasCMesOut   = converte_valor_estoques($SaidasCMesOut);
						$EstoqueCF       = converte_valor_estoques($EstoqueCF);
						$pdf->Cell(40,5,"CONSUMO",1,0,"L",1);
						$pdf->Cell(40,5,"$EstoqueCI",1,0,"R",0);
						$pdf->Cell(40,5,"$ResumoCMesAqu",1,0,"R",0);
						$pdf->Cell(40,5,"$EntradasCMesOut",1,0,"R",0);
						$pdf->Cell(40,5,"$ResumoCMesReq",1,0,"R",0);
						$pdf->Cell(40,5,"$SaidasCMesOut",1,0,"R",0);
						$pdf->Cell(40,5,"$EstoqueCF",1,1,"R",0);
						
						# Dados Permanente #
						$EstoquePI       = converte_valor_estoques($EstoquePI);
						$ResumoPMesAqu   = converte_valor_estoques($ResumoPMesAqu);
						$EntradasPMesOut = converte_valor_estoques($EntradasPMesOut);
						$ResumoPMesReq   = converte_valor_estoques($ResumoPMesReq);
						$SaidasPMesOut   = converte_valor_estoques($SaidasPMesOut);
						$EstoquePF       = converte_valor_estoques($EstoquePF);
						$pdf->Cell(40,5,"PERMANENTE",1,0,"L",1);
						$pdf->Cell(40,5,"$EstoquePI",1,0,"R",0);
						$pdf->Cell(40,5,"$ResumoPMesAqu",1,0,"R",0);
						$pdf->Cell(40,5,"$EntradasPMesOut",1,0,"R",0);
						$pdf->Cell(40,5,"$ResumoPMesReq",1,0,"R",0);
						$pdf->Cell(40,5,"$SaidasPMesOut",1,0,"R",0);
						$pdf->Cell(40,5,"$EstoquePF",1,1,"R",0);
						
						# Dados Totais #
						$EstoqueI        = converte_valor_estoques($EstoqueI);
						$ResumoMesAqu    = converte_valor_estoques($ResumoMesAqu);
						$EntradasMesOut  = converte_valor_estoques($EntradasMesOut);
						$ResumoMesReq    = converte_valor_estoques($ResumoMesReq);
						$SaidasMesOut    = converte_valor_estoques($SaidasMesOut);
						$EstoqueF        = converte_valor_estoques($EstoqueF);
						$pdf->Cell(40,5,"TOTAL",1,0,"L",1);
						$pdf->Cell(40,5,"$EstoqueI",1,0,"R",0);
						$pdf->Cell(40,5,"$ResumoMesAqu",1,0,"R",0);
						$pdf->Cell(40,5,"$EntradasMesOut",1,0,"R",0);
						$pdf->Cell(40,5,"$ResumoMesReq",1,0,"R",0);
						$pdf->Cell(40,5,"$SaidasMesOut",1,0,"R",0);
						$pdf->Cell(40,5,"$EstoqueF",1,1,"R",0);
						
						# Grava arquivo e fecha sessão PDF #
						$pdf->Output($NomeArquivo,'F');
				}
		}
}
$db->disconnect();
?>
