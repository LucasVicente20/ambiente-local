<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelSinteticoEntradasSaidasAntes2008Pdf.php
# Autor:    Álvaro Faria
# Data:     12/09/2006
# Alterado: Álvaro Faria
# Data:     25/09/2006 - Pesquisa por Ano/Mês
# Objetivo: Programa de impressão do resumo de estoque e movimentações do mês anterior.
#           No caso das movimentações que foram criadas no mês anterior ao de referência
#           e modificadas no mês de referência, o sistema irá apresentar compensando para
#           mais ou para menos no mês de referência. Podendo para isso, gerar e
#           apresentar valores negativos. Isso também pode gerar relatórios com totais
#           diferentes entre este (sintético) e o RelEntradaNotaFiscal e o
#           Relatório de Movimentação por Tipo (Saída por requisição)

#           A coluna Entradas Aquisições exibe o total de movimentações de nota fiscal
#           influenciadas por cancelamentos e alterações de nota fiscal de meses anteriores
#           no mês de referência, podendo exibir valores negativos

#           A coluna Saídas Requisições mostra as saídas no mês de referência e sofre
#           influência de cancelamentos ou alterações de requisições de meses anteriores.
#           Podendo inclusive mostrar valores negativos.
#           Desta forma, a informação desta coluna, pode não coincidir com o
#           "TOTAL SAÍDA POR REQUISIÇÃO" do relatório de
#           "Relatório de Movimentação por Tipo", que mostra apenas o acontecido no mês.
#           Para se chegar a diferença entre estes valores, rodar o seguinte select:
#           SELECT (Cancelamento + EntradaAcerto - SaidaAcerto) FROM 
#          (SELECT CASE WHEN SUM(vmovmavalo*amovmaqtdm) IS NULL THEN 0 ELSE SUM(vmovmavalo*amovmaqtdm) END AS Cancelamento FROM SFPC.TBmovimentacaomaterial WHERE calmpocodi = $Almoxarifado AND ctipmvcodi = 18 AND dmovmamovi >= '$ANO-12-01' AND dmovmamovi < '$ANO-12-$ULTIMODIA') AS Cancelamento,
#          (SELECT CASE WHEN SUM(vmovmavalo*amovmaqtdm) IS NULL THEN 0 ELSE SUM(vmovmavalo*amovmaqtdm) END AS EntradaAcerto FROM SFPC.TBmovimentacaomaterial WHERE calmpocodi = $Almoxarifado AND ctipmvcodi = 19 AND dmovmamovi >= '$ANO-12-01' AND dmovmamovi < '$ANO-12-$ULTIMODIA') AS EntradaAcerto,
#          (SELECT CASE WHEN SUM(vmovmavalo*amovmaqtdm) IS NULL THEN 0 ELSE SUM(vmovmavalo*amovmaqtdm) END AS SaidaAcerto FROM SFPC.TBmovimentacaomaterial WHERE calmpocodi = $Almoxarifado AND ctipmvcodi = 20 AND dmovmamovi >= '$ANO-12-01' AND dmovmamovi < '$ANO-12-$ULTIMODIA') AS SaidaAcerto
#           OBS: Substituir as variáveis $Almoxarifado, $ANO e $ULTIMODIA por valores

#           A coluna Outras (Entradas ou Saídas) exibe movimentações extras, como
#           empréstimos, doações, trocas, avarias e etc. Esta coluna não é influenciada
#           por movimentações de meses anteriores, apenas quando a própria movimentação
#           é alterada através do programa CadMovimentacaoManter.php

# Alterado: Carlos Abreu
# Data:     09/05/2007 - Ajuste no sql para padronização de relatórios
# Alterado: Carlos Abreu
# Data:     01/10/2007 - Ajustes na query e programacao para evitar estouro de memoria na execução
# Alterado: Álvaro Faria
# Data:     21/12/2007 - Relatório que exibe as informações antes de 2008, baseado no vmovmavalo
# OBS.:     Tabulação 2 espaços
#           Quando o inventário for aberto e fechado no mesmo mês e não houverem movimentações neste mês, o total do mês 
#           baterá com o relatório de fechamento de inventário. Porém, se houver movimentações no mês do 
#           inventário terá que ser acrescido ao valor do fechamento de inventário, estas movimentações
#------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

session_cache_limiter('private_no_expire');

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado = $_GET['Almoxarifado'];
		$DataAtual    = $_GET['DataAtual'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Função exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório Sintético de Entradas e Saídas";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("L","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

# DEFININDO A DATA DA PESQUISA #
# Descobre a data atual #
if(!$DataAtual) $DataAtual = date("d/m/Y");
$DataAtual   = explode("/",$DataAtual);
$DiaAtual    = $DataAtual[0];
$MesAtual    = $DataAtual[1];
$AnoAtual    = $DataAtual[2];
if($MesAtual == 1){      # Se o mês for janeiro, a ano inicial e final de pesquisa será o anterior
		$AnoPesquisaF = $AnoAtual - 1;
		$MesPesquisaF = 12;
		$AnoPesquisaI = $AnoAtual - 1;
		$MesPesquisaI = 11;
		$Mes          = "DEZEMBRO";
}elseif($MesAtual == 2){ # Se o mês for fevereiro, a ano inicial de pesquisa será o anterior
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

# FUNÇÕES DE RESGATE DE INFORMAÇÕES #
# Resgata o estoque em uma data definida, com cálculos no PHP #
function EstoqueData($Almoxarifado, $TipoMaterial, $DataPesquisa, $db){
		# Converte a data para o formato de pesquisa no banco de dados
		if($DataPesquisa) $DataPesquisa = DataInvertida($DataPesquisa);
		# Resgata os itens do estoque #
		$sql  = " SELECT MAT.CMATEPSEQU, ARM.AARMATQTDE, ARM.VARMATUMED, ";
		$sql .= "        AARMATQTDE + SUM(CASE WHEN FTIPMVTIPO = 'S' THEN AMOVMAQTDM ELSE CASE WHEN FTIPMVTIPO = 'E' THEN -AMOVMAQTDM ELSE 0 END END ) AS QTD ";
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
		$sql .= " GROUP BY MAT.CMATEPSEQU, ARM.AARMATQTDE, ARM.VARMATUMED ";
		$sql .= " ORDER BY MAT.CMATEPSEQU ";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
		}else{
				# Linhas de Itens de Material #
				$rows = $res->numRows();
				if($rows == 0){
						$Totalizador = 0;
				}else{
						for($i=0; $i< $rows; $i++){
								$Linha = $res->fetchRow();
								$CodigoReduzido[$i] = $Linha[0];
								$QtdEstoque[$i]     = $Linha[1];
								$ValorUnitario[$i]  = $Linha[2];
								$QuantMov[$i]       = $Linha[3];
								# Montando o array de Itens do Estoque #	

								# Descobre o valor unitário para o material na data especificada #
								$sqlvalor  = " SELECT VMOVMAUMED FROM SFPC.TBMOVIMENTACAOMATERIAL ";
								$sqlvalor .= "  WHERE CALMPOCODI = $Almoxarifado ";
								$sqlvalor .= "    AND CMATEPSEQU = ".$CodigoReduzido[$i]." ";
								$sqlvalor .= "    AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') ";
								$sqlvalor .= "    AND TMOVMAULAT = ";
								$sqlvalor .= "        (SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
								$sqlvalor .= "          WHERE CALMPOCODI = $Almoxarifado ";
								$sqlvalor .= "            AND CMATEPSEQU = ".$CodigoReduzido[$i]." ";
								$sqlvalor .= "            AND TMOVMAULAT <= '$DataPesquisa 23:59:59' ";
								$sqlvalor .= "            AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A') )";
								$sqlvalor .= " ORDER BY AMOVMAANOM DESC, CMOVMACODI DESC";
								$resvalor    = $db->query($sqlvalor);
								if( PEAR::isError($resvalor) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlvalor");
								}else{
										$LinhaValor              = $resvalor->fetchRow();
										$ValorUnitarioPesquisado = $LinhaValor[0];
								}

								$QtdTotal        = $QuantMov[$i];
								$Totalizador     = $Totalizador + ($QtdTotal * $ValorUnitarioPesquisado);
						}
				}
		}
		return $Totalizador;
}


# Resgata movimentações para exibição e cálculo de estoque em datas específicas #
function Movimentacoes($Almoxarifado, $TipoMov, $TipoMaterial, $DataPesquisa1, $DataPesquisa2, $Movimentacoes, $Operacao, $db){
		# Converte a data para o formato de pesquisa no banco de dados
		if($DataPesquisa1) $DataPesquisa1 = DataInvertida($DataPesquisa1);
		if($DataPesquisa2) $DataPesquisa2 = DataInvertida($DataPesquisa2);
		$sql  = " SELECT SUM(MOV.AMOVMAQTDM";
		$sql .= "        * ";
		$sql .= "        CASE WHEN MOV.CTIPMVCODI IN (4,19,20) THEN ";
		$sql .= "        ( ";
		$sql .= "        SELECT VMOVMAVALO FROM SFPC.TBMOVIMENTACAOMATERIAL ";
		$sql .= "         WHERE CTIPMVCODI IN (4,19,20) ";
		$sql .= "           AND CMATEPSEQU = MOV.CMATEPSEQU ";
		$sql .= "           AND CREQMASEQU = MOV.CREQMASEQU ";
		$sql .= "           AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
		$sql .= "           AND TMOVMAULAT = ( ";
		$sql .= "               SELECT MAX(TMOVMAULAT) FROM SFPC.TBMOVIMENTACAOMATERIAL ";
		$sql .= "               WHERE CTIPMVCODI IN (4,19,20) ";
		$sql .= "                 AND CMATEPSEQU = MOV.CMATEPSEQU ";
		$sql .= "                 AND CREQMASEQU = MOV.CREQMASEQU ";
		$sql .= "                 AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' ) ";
		$sql .= "              ) ";
		$sql .= "        ) ";
		$sql .= "        ELSE ";
		$sql .= "            MOV.VMOVMAVALO ";
		$sql .= "        END ";
		$sql .= "        ) ";
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
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql");
		}else{
				$Linha        = $res->fetchRow();
				$Movimentacao = $Linha[0];
				return $Movimentacao;
		}
}

# Conecta ao banco de dados #
$db = Conexao();

# Imprime as informações do Almoxarifado #
$sqlalmo = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
$resalmo = $db->query($sqlalmo);
if( PEAR::isError($resalmo) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sqlalmo");
}else{
		$Almox     = $resalmo->fetchRow();
		$DescAlmox = $Almox[0];
		$pdf->Cell(40,5,"ALMOXARIFADO",1,0,"L",1);
		$pdf->Cell(240,5,$DescAlmox,1,1,"L",0);
}
# Imprime a data das informações #
$pdf->Cell(40,5,"MÊS/ANO REFERÊNCIA",1,0,"L",1);
$pdf->Cell(240,5,"$Mes / $AnoPesquisaF",1,0,"L",0);
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
$EstoquePF       = EstoqueData($Almoxarifado, "P", $DataPesquisaF, $db);
$EstoqueCF       = EstoqueData($Almoxarifado, "C", $DataPesquisaF, $db);
# Calcula as movimentações (Requisição) dentro do mês anterior #
$SaidasPMesReq   = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "4,20,22", "I", $db);
$SaidasCMesReq   = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "4,20,22", "I", $db);
$EntradasPMesReq = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "2,18,19,21", "I", $db);
$EntradasCMesReq = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "2,18,19,21", "I", $db);
$ResumoPMesReq   = $SaidasPMesReq - $EntradasPMesReq;
$ResumoCMesReq   = $SaidasCMesReq - $EntradasCMesReq;
# Calcula as movimentações (Aquisições) dentro do mês anterior #
$EntradasPMesAqu = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "3,7", "I", $db);
$EntradasCMesAqu = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "3,7", "I", $db);
$SaidasPMesAqu   = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "8", "I", $db);
$SaidasCMesAqu   = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "8", "I", $db);
$ResumoPMesAqu   = $EntradasPMesAqu - $SaidasPMesAqu;
$ResumoCMesAqu   = $EntradasCMesAqu - $SaidasCMesAqu;
# Calcula as movimentações (Outras) dentro do mês anterior #
$EntradasPMesOut = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "2,18,19,21,3,7", "N", $db);
$EntradasCMesOut = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "2,18,19,21,3,7", "N", $db);
$SaidasPMesOut   = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "4,20,22,8", "N", $db);
$SaidasCMesOut   = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "4,20,22,8", "N", $db);
# Calcula o estoque do último dia do mês anterior ao anterior#
$EstoquePI = EstoqueData($Almoxarifado, "P", $DataPesquisaI, $db);
$EstoqueCI = EstoqueData($Almoxarifado, "C", $DataPesquisaI, $db);

$db->disconnect();

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

$pdf->Output();
?>
