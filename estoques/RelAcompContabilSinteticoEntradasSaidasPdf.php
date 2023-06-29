<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAcompContabilSinteticoEntradasSaidasPdf.php
# Autor:    Rodrigo Melo
# Data:      03/06/2008
# Objetivo: Relatório criado para atender aos contadores da prefeitura tomando como base o relatório RelSinteticoEntradasSaidasPdf.php.
#               Este relatório preocupa-se em exibir as informações das movimentações confirmadas e pendentes dos materiais permanentes e de
#               consumo com base nas movimentações dos almoxarifados.
# Alterado: Rodrigo Melo
# Data:     13/06/2008 - Correção para exibir o total das notas fiscais e as requisições pendentes.
# Alterado: Ariston Cordeiro
# Data:     06/04/2009 - Nova movimentação: "saída por processo administrativo" (37)
# OBS.:     Tabulação 2 espaços
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
$TituloRelatorio = "RELATÓRIO SINTÉTICO DE ACOMPANHAMENTO CONTÁBIL";

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

//Função resposável por separa as movimentações passados por parâmetro, para facilitar na comparação.
function SeparaMovimentacoes($Movimentacoes) {
  $MovimentacoesSeparadas = array();

  if($Movimentacoes != null){
    $MovimentacoesSeparadas = explode(",",$Movimentacoes);

    for($i = 0; $i < count($MovimentacoesSeparadas); $i++){
      $MovimentacoesSeparadas[$i] = trim($MovimentacoesSeparadas[$i]);
    }

  }

  return $MovimentacoesSeparadas;
}

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
		$sql  = " SELECT MAT.CMATEPSEQU, ARM.AARMATQTDE, ";  # Alterado
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
								$QtdEstoque[$i]     = $Linha[1]; # Alterado
								$QuantMov[$i]       = $Linha[2];
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
								//$sqlvalor .= " ORDER BY AMOVMAANOM DESC, CMOVMACODI DESC"; # Alterado
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
function Movimentacoes($Almoxarifado, $TipoMov, $TipoMaterial, $DataPesquisa1, $DataPesquisa2, $Movimentacoes, $Operacao, $UtilizaFlagCorresp, $db){
		# Converte a data para o formato de pesquisa no banco de dados
		if($DataPesquisa1) $DataPesquisa1 = DataInvertida($DataPesquisa1);
		if($DataPesquisa2) $DataPesquisa2 = DataInvertida($DataPesquisa2);

    $MovimentacoesSeparadas = SeparaMovimentacoes($Movimentacoes);

		$sql  = " SELECT SUM(MOV.AMOVMAQTDM";
		$sql .= "        * ";
		$sql .= "        CASE WHEN MOV.CTIPMVCODI IN (3,7,8) THEN (MOV.VMOVMAVALO)";
		$sql .= "        ELSE (MOV.VMOVMAUMED) END )";
		$sql .= "   FROM SFPC.TBMOVIMENTACAOMATERIAL MOV, SFPC.TBTIPOMOVIMENTACAO TIP, ";
		$sql .= "        SFPC.TBMATERIALPORTAL MAT, ";
		$sql .= "        SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBGRUPOMATERIALSERVICO GRU ";

    if( (in_array("4",$MovimentacoesSeparadas)  ||
           in_array("18",$MovimentacoesSeparadas) ||
           in_array("19",$MovimentacoesSeparadas) ||
           in_array("20",$MovimentacoesSeparadas)) && $Operacao == 'I'){
      $sql .= " ,        SFPC.TBSITUACAOREQUISICAO SIT ";
    }

		$sql .= "  WHERE MOV.CTIPMVCODI = TIP.CTIPMVCODI ";
		$sql .= "    AND MOV.CMATEPSEQU = MAT.CMATEPSEQU ";
		$sql .= "    AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
		$sql .= "    AND SUB.CGRUMSCODI = GRU.CGRUMSCODI ";

    if( (in_array("4",$MovimentacoesSeparadas)  ||
           in_array("18",$MovimentacoesSeparadas) ||
           in_array("19",$MovimentacoesSeparadas) ||
           in_array("20",$MovimentacoesSeparadas)) && $Operacao == 'I'){
      $sql .= "    AND MOV.CREQMASEQU = SIT.CREQMASEQU ";
  		$sql .= "    AND TSITREULAT IN ";
  		$sql .= "        (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO  ";
  		$sql .= "        WHERE CREQMASEQU = SIT.CREQMASEQU)  ";
    }

		if($Movimentacoes){
      if($Operacao == 'I'){
          $sql .= "    AND MOV.CTIPMVCODI IN ($Movimentacoes)" ;
      }elseif($Operacao == 'N'){
          $sql .= "    AND MOV.CTIPMVCODI NOT IN ($Movimentacoes)" ;
      }

      //OBS: A movimentação 18 deve vir com a variável $UtilizaFlagCorresp = 'N', pois o ajuste apenas ocorre em requisições não baixadas (pendentes)
      if( (in_array("4",$MovimentacoesSeparadas)  ||
           in_array("18",$MovimentacoesSeparadas) ||
           in_array("19",$MovimentacoesSeparadas) ||
           in_array("20",$MovimentacoesSeparadas)) && $Operacao == 'I'){
        if($UtilizaFlagCorresp == "S") {
          $sql .= " AND se_requisicao_baixada(MOV.CREQMASEQU) = 1 "; //RETORNA 1 SE A REQUISIÇÃO ESTIVER BAIXADA
        } else {
          $sql .= " AND se_requisicao_baixada(MOV.CREQMASEQU) = 0 "; //RETORNA 0 SE A REQUISIÇÃO NÃO ESTIVER BAIXADA
        }
      } else {
        if( (in_array("12",$MovimentacoesSeparadas)  ||
             in_array("13",$MovimentacoesSeparadas)  ||
             in_array("15",$MovimentacoesSeparadas) ||
             in_array("30",$MovimentacoesSeparadas)) && $Operacao == 'I'){

          if($UtilizaFlagCorresp == "S") {
            $sql .= " AND  MOV.FMOVMACORR = 'S'  ";
          } else {
            $sql .= " AND (MOV.FMOVMACORR = 'N' OR MOV.FMOVMACORR IS NULL)  ";
          }
        }
      }
		}

		$sql .= "    AND TIP.FTIPMVTIPO = '$TipoMov' ";
		$sql .= "    AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
		$sql .= "    AND MOV.CALMPOCODI = $Almoxarifado ";

    if( (in_array("4",$MovimentacoesSeparadas)  ||
           in_array("18",$MovimentacoesSeparadas) ||
           in_array("19",$MovimentacoesSeparadas) ||
           in_array("20",$MovimentacoesSeparadas)) && $Operacao == 'I'){

      $sql .= " AND   (CASE WHEN MOV.CREQMASEQU IS NOT NULL AND MOV.CTIPMVCODI NOT IN (2,21,22) THEN ";
      $sql .= "        TO_DATE(TO_CHAR(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') > '$DataPesquisa1 23:59:59' ";
      if ($DataPesquisa2){
          $sql .= "    AND  TO_DATE(TO_CHAR(SIT.TSITRESITU,'YYYY-MM-DD'),'YYYY-MM-DD') <= '$DataPesquisa2 23:59:59' ";
  		}
      $sql .= "     ELSE  ";

      $sql .= "         (CASE WHEN MOV.CTIPMVCODI IN (12,13,15,30) THEN ";
      $sql .= "              (SELECT DATATRANSACAO.TMOVMAULAT ";
      $sql .= "                 FROM SFPC.TBMOVIMENTACAOMATERIAL DATATRANSACAO ";
      $sql .= "                WHERE DATATRANSACAO.CALMPOCOD1 = MOV.CALMPOCODI ";
      $sql .= "                  AND DATATRANSACAO.AMOVMAANO1 = MOV.AMOVMAANOM ";
      $sql .= "                  AND DATATRANSACAO.CMOVMACOD1 = MOV.CMOVMACODI ";
      $sql .= "                  AND DATATRANSACAO.CTIPMVCODI IN (6,9,11,29) ";
      $sql .= "                  AND CASE WHEN MOV.CTIPMVCODI = 11 THEN ";
      $sql .= "                           DATATRANSACAO.CMATEPSEQ1 = MOV.CMATEPSEQU ";
      $sql .= "                      ELSE ";
      $sql .= "                           DATATRANSACAO.CMATEPSEQU = MOV.CMATEPSEQU ";
      $sql .= "                      END";
      $sql .= "              ) > '$DataPesquisa1 23:59:59' ";
      if ($DataPesquisa2){
          $sql .= "     AND    (SELECT DATATRANSACAO.TMOVMAULAT ";
          $sql .= "                 FROM SFPC.TBMOVIMENTACAOMATERIAL DATATRANSACAO ";
          $sql .= "                WHERE DATATRANSACAO.CALMPOCOD1 = MOV.CALMPOCODI ";
          $sql .= "                  AND DATATRANSACAO.AMOVMAANO1 = MOV.AMOVMAANOM ";
          $sql .= "                  AND DATATRANSACAO.CMOVMACOD1 = MOV.CMOVMACODI ";
          $sql .= "                  AND DATATRANSACAO.CTIPMVCODI IN (6,9,11,29) ";
          $sql .= "                  AND CASE WHEN MOV.CTIPMVCODI = 11 THEN ";
          $sql .= "                           DATATRANSACAO.CMATEPSEQ1 = MOV.CMATEPSEQU ";
          $sql .= "                      ELSE ";
          $sql .= "                           DATATRANSACAO.CMATEPSEQU = MOV.CMATEPSEQU ";
          $sql .= "                      END";
          $sql .= "              ) <= '$DataPesquisa2 23:59:59' ";
  		}

      $sql .= "         ELSE  ";
      $sql .= "	           MOV.TMOVMAULAT > '$DataPesquisa1 23:59:59' ";
      if ($DataPesquisa2){
  				$sql .= "    AND MOV.TMOVMAULAT <= '$DataPesquisa2 23:59:59' ";
  		}
      $sql .= "         END) ";

      $sql .= "     END)  ";

    } else {

      /*
                 Caso as movimentações sejam 7 (Entrada por alteração de nota fiscal) ou 8 (Saída por alteração de nota fiscal) serão válidas
                 quaisquer alterações nas notas fiscais que entraram no almoxarifado no período determinado (MAIOR QUE '$DataPesquisa1 23:59:59' && MENOR OU IGUAL A '$DataPesquisa2 23:59:59')
                 e após a data incial ($DataPesquisa1 23:59:59), logo, pode ocorrer alterações das notas fiscais após a data final ($DataPesquisa2 23:59:59) do periodo determinado.
                 Exemplo: Uma nota fiscal que teve sua entrada no mês de maio pode sofrer uma alteração (Entrada ou Saída) no mês de junho. Logo, as alterações após o mês o de
                 julho pode ocorrer referenciando as notas fiscais que entraram no mês de maio.
              */
      if((in_array("7",$MovimentacoesSeparadas)  ||
          in_array("8",$MovimentacoesSeparadas)) && $Operacao == 'I') {

        $sql .= " AND CENTNFCODI IN ( ";
        $sql .= "    SELECT DISTINCT CENTNFCODI FROM SFPC.TBMOVIMENTACAOMATERIAL ";
        $sql .= "    WHERE CALMPOCODI = $Almoxarifado AND CTIPMVCODI = 3  ";
        $sql .= "    AND  TMOVMAULAT > '$DataPesquisa1 23:59:59' ";

        if ($DataPesquisa2){
          $sql .= "    AND TMOVMAULAT <= '$DataPesquisa2 23:59:59' ";
        }
        $sql .= " ) ";
        $sql .= "  AND  MOV.TMOVMAULAT > '$DataPesquisa1 23:59:59' ";

      } else {

        if( (in_array("12",$MovimentacoesSeparadas)  ||
             in_array("13",$MovimentacoesSeparadas)  ||
             in_array("15",$MovimentacoesSeparadas) ||
             in_array("30",$MovimentacoesSeparadas)) && $Operacao == 'I'){
          $sql .= " AND         (SELECT DATATRANSACAO.DMOVMAMOVI ";
          $sql .= "                 FROM SFPC.TBMOVIMENTACAOMATERIAL DATATRANSACAO ";
          $sql .= "                WHERE DATATRANSACAO.CALMPOCOD1 = MOV.CALMPOCODI ";
          $sql .= "                  AND DATATRANSACAO.AMOVMAANO1 = MOV.AMOVMAANOM ";
          $sql .= "                  AND DATATRANSACAO.CMOVMACOD1 = MOV.CMOVMACODI ";
          $sql .= "                  AND DATATRANSACAO.CTIPMVCODI IN (6,9,11,29) ";
          $sql .= "                  AND CASE WHEN MOV.CTIPMVCODI = 11 THEN ";
          $sql .= "                           DATATRANSACAO.CMATEPSEQ1 = MOV.CMATEPSEQU ";
          $sql .= "                      ELSE ";
          $sql .= "                           DATATRANSACAO.CMATEPSEQU = MOV.CMATEPSEQU ";
          $sql .= "                      END";
          $sql .= "              ) > '$DataPesquisa1 23:59:59' ";

          if ($DataPesquisa2){
            $sql .= " AND         (SELECT DATATRANSACAO.DMOVMAMOVI ";
            $sql .= "                 FROM SFPC.TBMOVIMENTACAOMATERIAL DATATRANSACAO ";
            $sql .= "                WHERE DATATRANSACAO.CALMPOCOD1 = MOV.CALMPOCODI ";
            $sql .= "                  AND DATATRANSACAO.AMOVMAANO1 = MOV.AMOVMAANOM ";
            $sql .= "                  AND DATATRANSACAO.CMOVMACOD1 = MOV.CMOVMACODI ";
            $sql .= "                  AND DATATRANSACAO.CTIPMVCODI IN (6,9,11,29) ";
            $sql .= "                  AND CASE WHEN MOV.CTIPMVCODI = 11 THEN ";
            $sql .= "                           DATATRANSACAO.CMATEPSEQ1 = MOV.CMATEPSEQU ";
            $sql .= "                      ELSE ";
            $sql .= "                           DATATRANSACAO.CMATEPSEQU = MOV.CMATEPSEQU ";
            $sql .= "                      END";
            $sql .= "              ) <= '$DataPesquisa2 23:59:59' ";
        	}
        } else {
          $sql .= "  AND  MOV.TMOVMAULAT > '$DataPesquisa1 23:59:59' ";
        	if ($DataPesquisa2){
        		$sql .= "    AND MOV.TMOVMAULAT <= '$DataPesquisa2 23:59:59' ";
        	}
        }
      }
    }

		$sql .= "    AND (MOV.FMOVMASITU IS NULL OR MOV.FMOVMASITU = 'A') "; // Traz só as movimentações ativas
    $res  = $db->query($sql);

		if( PEAR::isError($res) ){
				$CodErroEmail  = $res->getCode(); //Obtém o código de erro do Banco de dados
        $DescErroEmail = $res->getMessage(); //Obtém a descrição do erro do Banco de dados

        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nsql: $sql\n\n$DescErroEmail ($CodErroEmail)");
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
$pdf->Cell(280,5,"MOVIMENTAÇÕES CONFIRMADAS",1,1,"C",1);
$pdf->Cell(50,5,"SALDO INICIAL","TLR",0,"C",1);
$pdf->Cell(108,5,"ENTRADAS",1,0,"C",1);
$pdf->Cell(72,5,"SAÍDAS",1,0,"C",1);
$pdf->Cell(50,5,"SALDO FINAL","TLR",1,"C",1);

$pdf->SetFontSize(6);
$pdf->Cell(50,5,"(MOVIMENTAÇÃO CONFIRMADA)","BLR",0,"C",1);
$pdf->SetFontSize(9);

$pdf->Cell(36,5,"AQUISIÇÕES",1,0,"C",1);
$pdf->Cell(36,5,"AJUSTES",1,0,"C",1);
$pdf->Cell(36,5,"OUTRAS",1,0,"C",1);
$pdf->Cell(36,5,"REQUISIÇÕES",1,0,"C",1);
$pdf->Cell(36,5,"OUTRAS",1,0,"C",1);
$pdf->SetFontSize(6);
$pdf->Cell(50,5,"(MOVIMENTAÇÃO CONFIRMADA)","BLR",1,"C",1);
$pdf->SetFontSize(9);

$pdf->Cell(50,5,"$DataPesquisaI","BLR",0,"C",1);
$pdf->Cell(36,5,"","BLR",0,"L",1);
$pdf->Cell(36,5,"AQUISIÇÕES","BLR",0,"C",1);
$pdf->Cell(36,5,"","BLR",0,"C",1);
$pdf->Cell(36,5,"","BLR",0,"L",1);
$pdf->Cell(36,5,"","BLR",0,"L",1);
$pdf->Cell(50,5,"$DataPesquisaF","BLR",1,"C",1);


# OBTEM AS INFORMAÇÕES NECESSÁRIAS PARA O RELATÓRIO ATRAVÉS DA INVOCAÇÃO ÀS FUNÇÕES #

# Calcula o estoque do último dia do mês anterior ao anterior#
$EstoqueCIConfirmada = EstoqueData($Almoxarifado, "C", $DataPesquisaI, $db);
$EstoquePIConfirmada = EstoqueData($Almoxarifado, "P", $DataPesquisaI, $db);
# Calcula as movimentações (Aquisições) dentro do mês #

#Obtém o valor total das notas fiscais para exibir no PDF
$ResumoCMesAquConfirmada = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "3", "I", "S", $db);
$ResumoPMesAquConfirmada = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "3", "I", "S", $db);

#Obtém o valor total da entrada dos ajustes das notas fiscais (Mov 7)
$EntradasCMesAquAjustesConfirmada = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "7", "I", "S", $db);
$EntradasPMesAquAjustesConfirmada = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "7", "I", "S", $db);

#Obtém o valor total da saída dos ajustes das notas fiscais (Mov 8)
$SaidasCMesAquAjustesConfirmada = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "8", "I", "S", $db);
$SaidasPMesAquAjustesConfirmada = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "8", "I", "S", $db);

#Obtém o valor total das entradas válidas para as movimentações confirmadas (Movimentações: 2, 6, 9, 10, 11, 21, 26, 28, 29, 31, 32, 33) para exibir no PDF
$ResumoCMesAquOutrasConfirmada = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "3,7,18,19", "N", "S", $db); //Obtem todas as movimentações de entrada para os materiais de consumo diferentes das movimentações: 3 (Entrada por nota fiscal), 7 (Entrada por alteração de nota fiscal), 18 (Entrada por cancelamento de requisição), 19 (Entrada para acerto da requisição)

$ResumoPMesAquOutrasConfirmada = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "3,7,18,19", "N", "S", $db); //Obtem todas as movimentações de entrada para os materiais permanentes diferentes das movimentações: 3 (Entrada por nota fiscal), 7 (Entrada por alteração de nota fiscal), 18 (Entrada por cancelamento de requisição), 19 (Entrada para acerto da requisição)

#Calculo os ajustes das notas fiscais (Entrada - Saída: Mov 7 - Mov 8) para exibir no PDF. Este valor pode resultar num valor negativo.
$ResumoCMesAquAjustesConfirmada = $EntradasCMesAquAjustesConfirmada - $SaidasCMesAquAjustesConfirmada;
$ResumoPMesAquAjustesConfirmada = $EntradasPMesAquAjustesConfirmada - $SaidasPMesAquAjustesConfirmada;


# Calcula as movimentações confirmadas (Requisição) dentro do mês anterior #

#Obtém o valor total das requisições dos materiais de consumo e peramente baixadas (Mov. 4) para exibir no PDF
$SaidasCMesReqConfirmada = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "4", "I", "S", $db);
$SaidasPMesReqConfirmada = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "4", "I", "S", $db);

#Obtém as modificações nas requisições confirmadas, para realizar o cálculo das requisições confimadas (Mov 4 + Mov 20 - Mov 19)

#Obtém o valor total da saída dos ajustes das requisições para os materiais de consumo e peramente (Mov 20).
$SaidasCMesReqModificacaoConfirmada = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "20", "I", "S", $db);
$SaidasPMesReqModificacaoConfirmada = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "20", "I", "S", $db);

#Obtém o valor total da entrada dos ajustes das requisições para os materiais de consumo e peramente (Mov 19).
$EntradasCMesReqModificacaoConfirmada = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "19", "I", "S", $db);
$EntradasPMesReqModificacaoConfirmada = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "19", "I", "S", $db);

#Obtém o valor total das saídas válidas para as movimentações confirmadas (Movimentações: 14, 16, 17, 22, 23, 24, 25, 27, 34, 37).
$ResumoCMesReqOutrasConfirmada = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "14, 16, 17, 22, 23, 24, 25, 27, 34, 37", "I", "S", $db);
$ResumoPMesReqOutrasConfirmada = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "14, 16, 17, 22, 23, 24, 25, 27, 34, 37", "I", "S", $db);

#Obtém o valor total das saídas válidas para as movimentações confirmadas com suas movimentações correspondentes(Movimentações: 12,13,15,30, caso tenham sido anteriormente confirmadas com suas respectivas movimentações correspondentes)
$ResumoCMesReqOutrasConfirmada = $ResumoCMesReqOutrasConfirmada + Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "12,13,15,30", "I", "S", $db);
$ResumoPMesReqOutrasConfirmada = $ResumoPMesReqOutrasConfirmada + Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "12,13,15,30", "I", "S", $db);

$ResumoCMesReqConfirmada   = ($SaidasCMesReqConfirmada + $SaidasCMesReqModificacaoConfirmada) - $EntradasCMesReqModificacaoConfirmada;
$ResumoPMesReqConfirmada   = ($SaidasPMesReqConfirmada + $SaidasPMesReqModificacaoConfirmada) - $EntradasPMesReqModificacaoConfirmada;



# Calcula o estoque do último dia do mês anterior #

$EstoqueCEntradas = $ResumoCMesAquConfirmada + $ResumoCMesAquAjustesConfirmada + $ResumoCMesAquOutrasConfirmada;
$EstoquePEntradas = $ResumoPMesAquConfirmada + $ResumoPMesAquAjustesConfirmada + $ResumoPMesAquOutrasConfirmada;

$EstoqueCSaidas = $ResumoCMesReqConfirmada + $ResumoCMesReqOutrasConfirmada;
$EstoquePSaidas = $ResumoPMesReqConfirmada + $ResumoPMesReqOutrasConfirmada;

$EstoqueCF = ($EstoqueCIConfirmada + $EstoqueCEntradas) - $EstoqueCSaidas;
$EstoquePF = ($EstoquePIConfirmada + $EstoquePEntradas) - $EstoquePSaidas;

//$db->disconnect(); //ORIGINAL

# Calcula Totais #
$EstoqueI       = $EstoqueCIConfirmada + $EstoquePIConfirmada;

$ResumoMesAqu   = $ResumoCMesAquConfirmada   + $ResumoPMesAquConfirmada;
$ResumoMesAquAjustes = $ResumoCMesAquAjustesConfirmada + $ResumoPMesAquAjustesConfirmada;
$ResumoMesAquOutrasConfirmada = $ResumoCMesAquOutrasConfirmada + $ResumoPMesAquOutrasConfirmada;

$ResumoMesReqConfirmada   = $ResumoCMesReqConfirmada   + $ResumoPMesReqConfirmada;
$ResumoMesReqOutrasConfirmadas   = $ResumoCMesReqOutrasConfirmada   + $ResumoPMesReqOutrasConfirmada;

$EstoqueF = $EstoqueCF + $EstoquePF;

# IMPRIME DADOS #

//Obter os dados para as movimentações confirmadas de consumo e imprimir aqui...
# Dados Consumo #
$EstoqueCIConfirmada       = converte_valor_estoques($EstoqueCIConfirmada);
$ResumoCMesAquConfirmada   = converte_valor_estoques($ResumoCMesAquConfirmada);
$ResumoCMesAquOutrasConfirmada = converte_valor_estoques($ResumoCMesAquOutrasConfirmada);
$ResumoCMesAquAjustesConfirmada = converte_valor_estoques($ResumoCMesAquAjustesConfirmada);
$ResumoCMesReqConfirmada   = converte_valor_estoques($ResumoCMesReqConfirmada);
$ResumoCMesReqOutrasConfirmada = converte_valor_estoques($ResumoCMesReqOutrasConfirmada);
$EstoqueCF       = converte_valor_estoques($EstoqueCF);

$pdf->SetFont("Arial","B",10);
$pdf->Cell(280,5,"CONSUMO",1,1,"C",0);
$pdf->SetFont("Arial","",9);
$pdf->Cell(50,5,"$EstoqueCIConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoCMesAquConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoCMesAquAjustesConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoCMesAquOutrasConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoCMesReqConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoCMesReqOutrasConfirmada",1,0,"R",0);
$pdf->Cell(50,5,"$EstoqueCF",1,1,"R",0);


//Obter os dados para as movimentações confirmadas permanentes e imprimir aqui...
# Dados Permanente #
$EstoquePIConfirmada       = converte_valor_estoques($EstoquePIConfirmada);

$ResumoPMesAquConfirmada   = converte_valor_estoques($ResumoPMesAquConfirmada);
$ResumoPMesAquAjustesConfirmada = converte_valor_estoques($ResumoPMesAquAjustesConfirmada);
$ResumoPMesAquOutrasConfirmada = converte_valor_estoques($ResumoPMesAquOutrasConfirmada);

$ResumoPMesReqConfirmada   = converte_valor_estoques($ResumoPMesReqConfirmada);
$ResumoPMesReqOutrasConfirmada = converte_valor_estoques($ResumoPMesReqOutrasConfirmada);
$EstoquePF       = converte_valor_estoques($EstoquePF);

$pdf->SetFont("Arial","B",10);
$pdf->Cell(280,5,"PERMANENTE",1,1,"C",0);
$pdf->SetFont("Arial","",9);

$pdf->Cell(50,5,"$EstoquePIConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoPMesAquConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoPMesAquAjustesConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoPMesAquOutrasConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoPMesReqConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoPMesReqOutrasConfirmada",1,0,"R",0);
$pdf->Cell(50,5,"$EstoquePF",1,1,"R",0);

//Obter os dados para o TOTAL das movimentações confirmadas de consumo e permanentes. E prepara os dados para imprimir.
# Dados Totais #
$EstoqueI        = converte_valor_estoques($EstoqueI);
$ResumoMesAqu    = converte_valor_estoques($ResumoMesAqu);
$ResumoMesAquAjustes = converte_valor_estoques($ResumoMesAquAjustes);
$ResumoMesAquOutrasConfirmada  = converte_valor_estoques($ResumoMesAquOutrasConfirmada);
$ResumoMesReqConfirmada    = converte_valor_estoques($ResumoMesReqConfirmada);
$ResumoMesReqOutrasConfirmadas  = converte_valor_estoques($ResumoMesReqOutrasConfirmadas);
$EstoqueF        = converte_valor_estoques($EstoqueF);

$pdf->SetFont("Arial","B",10);
$pdf->Cell(280,5,"TOTAL",1,1,"C",0);
$pdf->SetFont("Arial","",9);

$pdf->Cell(50,5,"$EstoqueI",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoMesAqu",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoMesAquAjustes",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoMesAquOutrasConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoMesReqConfirmada",1,0,"R",0);
$pdf->Cell(36,5,"$ResumoMesReqOutrasConfirmadas",1,0,"R",0);
$pdf->Cell(50,5,"$EstoqueF",1,1,"R",0);

#################################
//Movimentações pendentes
#################################

# Calcula as movimentações pendentes (Requisição) dentro do mês anterior #

#Obtém o valor total das requisições dos materiais de consumo e peramente NÃO baixadas (Mov. 4) para exibir no PDF
$SaidasCMesReqPendente = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "4", "I", "N", $db);
$SaidasPMesReqPendente = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "4", "I", "N", $db);

#Obtém as modificações nas requisições confirmadas, para realizar o cálculo das requisições PENDENTES (Mov 4 + Mov 20 - Mov 19)

#Obtém o valor total da saída dos ajustes das requisições para os materiais de consumo e peramente (Mov 20) NÃO BAIXADAS.
$SaidasCMesReqModificacaoPendente = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "20", "I", "N", $db);
$SaidasPMesReqModificacaoPendente = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "20", "I", "N", $db);

#Obtém o valor total da entrada dos ajustes das requisições para os materiais de consumo e peramente (Mov 19) NÃO BAIXADAS.
$EntradasCMesReqModificacaoPendente = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "19", "I", "N", $db);
$EntradasPMesReqModificacaoPendente = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "19", "I", "N", $db);

#Obtém o valor total das entradas por cancelamento das requisições (Mov 18), essas movimentações são apenas para requisições que ainda não foram baixadas, pois os ajustes não podem ser feitas em requisições baixadas. Logo, o parâmetro $UtilizaFlagCorresp deve ser igual 'N'.
$ResumoCMesReqAjustesPendente = Movimentacoes($Almoxarifado, "E", "C", $DataPesquisaI, $DataPesquisaF, "18", "I", "N", $db);
$ResumoPMesReqAjustesPendente = Movimentacoes($Almoxarifado, "E", "P", $DataPesquisaI, $DataPesquisaF, "18", "I", "N", $db);

#Obtém o valor total das saídas válidas para as movimentações PENDENTES, ou seja, que não possuem suas movimentações correspondentes(Movimentações: 12,13,15,30, caso NÃO tenham sido anteriormente confirmadas com suas respectivas movimentações correspondentes)
$ResumoCMesReqOutrasPendente = Movimentacoes($Almoxarifado, "S", "C", $DataPesquisaI, $DataPesquisaF, "12,13,15,30", "I", "N", $db);
$ResumoPMesReqOutrasPendente = Movimentacoes($Almoxarifado, "S", "P", $DataPesquisaI, $DataPesquisaF, "12,13,15,30", "I", "N", $db);

#Calculando o valor das requisições pendentes (Mov 4 + Mov 20 - Mov 19)  para exibir no PDF
$ResumoCMesReqPendente   = ($SaidasCMesReqPendente + $SaidasCMesReqModificacaoPendente) - $EntradasCMesReqModificacaoPendente;
$ResumoPMesReqPendente   = ($SaidasPMesReqPendente + $SaidasPMesReqModificacaoPendente) - $EntradasPMesReqModificacaoPendente;

#Calculando os subtotais para o materiais de consumo e permanente
//Soma as requisições, ajustes e outras requisições pendentes para os materiais de consumo. A soma é obtida da seguinte forma: (Req. Pendentes + Outras Mov. Pendentes) - Ajustes (Entrada por cancelamento de Requisição).
$ResumoCMesSubTotalPendente = ($ResumoCMesReqPendente + $ResumoCMesReqOutrasPendente) - $ResumoCMesReqAjustesPendente ;
//Soma as requisições, ajustes e outras requisições pendentes para os materiais permanentes
$ResumoPMesSubTotalPendente = ($ResumoPMesReqPendente + $ResumoPMesReqOutrasPendente) - $ResumoPMesReqAjustesPendente;

#Calculando os TOTAIS das movimentações pendentes
$ResumoMesReqPendente = $ResumoCMesReqPendente + $ResumoPMesReqPendente;
$ResumoMesReqAjustesPendente = $ResumoCMesReqAjustesPendente + $ResumoPMesReqAjustesPendente;
$ResumoMesReqOutrasPendente = $ResumoCMesReqOutrasPendente + $ResumoPMesReqOutrasPendente;
$ResumoMesSubTotalPendente = $ResumoCMesSubTotalPendente + $ResumoPMesSubTotalPendente;

$db->disconnect(); //Fecha conexão com o oracle

$pdf->ln(10);
$pdf->Cell(280,5,"MOVIMENTAÇÕES PENDENTES",1,1,"C",1);

$pdf->Cell(230,5,"SAÍDAS","TLR",0,"C",1);
$pdf->Cell(50,5,"SUB-TOTAL","TLR",1,"C",1);

$pdf->Cell(76,5,"REQUISIÇÕES","TLR",0,"C",1);
$pdf->Cell(77,5,"AJUSTES REQUISIÇÕES","TLR",0,"C",1);
$pdf->Cell(77,5,"OUTRAS","TLR",0,"C",1);
$pdf->SetFontSize(6);
$pdf->Cell(50,5,"(MOVIMENTAÇÃO PENDENTE)","BLR",1,"C",1);
$pdf->SetFontSize(9);


//Obter os dados para as movimentações pendentes de consumo e imprimir aqui...
$ResumoCMesReqPendente = converte_valor_estoques($ResumoCMesReqPendente);
$ResumoCMesReqAjustesPendente = converte_valor_estoques($ResumoCMesReqAjustesPendente);
$ResumoCMesReqOutrasPendente = converte_valor_estoques($ResumoCMesReqOutrasPendente);
$ResumoCMesSubTotalPendente = converte_valor_estoques($ResumoCMesSubTotalPendente);

$pdf->SetFont("Arial","B",10);
$pdf->Cell(280,5,"CONSUMO",1,1,"C",0);
$pdf->SetFont("Arial","",9);

$pdf->Cell(76,5,"$ResumoCMesReqPendente",1,0,"R",0);
$pdf->Cell(77,5,"$ResumoCMesReqAjustesPendente",1,0,"R",0);
$pdf->Cell(77,5,"$ResumoCMesReqOutrasPendente",1,0,"R",0);
$pdf->Cell(50,5,"$ResumoCMesSubTotalPendente",1,1,"R",0);

//Obter os dados para as movimentações pendentes permanentes e imprimir aqui...
$ResumoPMesReqPendente = converte_valor_estoques($ResumoPMesReqPendente);
$ResumoPMesReqAjustesPendente = converte_valor_estoques($ResumoPMesReqAjustesPendente);
$ResumoPMesReqOutrasPendente = converte_valor_estoques($ResumoPMesReqOutrasPendente);
$ResumoPMesSubTotalPendente = converte_valor_estoques($ResumoPMesSubTotalPendente);

$pdf->SetFont("Arial","B",10);
$pdf->Cell(280,5,"PERMANENTE",1,1,"C",0);
$pdf->SetFont("Arial","",9);

$pdf->Cell(76,5,"$ResumoPMesReqPendente",1,0,"R",0);
$pdf->Cell(77,5,"$ResumoPMesReqAjustesPendente",1,0,"R",0);
$pdf->Cell(77,5,"$ResumoPMesReqOutrasPendente",1,0,"R",0);
$pdf->Cell(50,5,"$ResumoPMesSubTotalPendente",1,1,"R",0);

//Obter os dados para o TOTAL das movimentações pendentes de consumo e permanentes. E prepara os dados para imprimir.
$ResumoMesReqPendente = converte_valor_estoques($ResumoMesReqPendente);
$ResumoMesReqAjustesPendente = converte_valor_estoques($ResumoMesReqAjustesPendente);
$ResumoMesReqOutrasPendente = converte_valor_estoques($ResumoMesReqOutrasPendente);
$ResumoMesSubTotalPendente = converte_valor_estoques($ResumoMesSubTotalPendente);


$pdf->SetFont("Arial","B",10);
$pdf->Cell(280,5,"TOTAL",1,1,"C",0);
$pdf->SetFont("Arial","",9);

$pdf->Cell(76,5,"$ResumoMesReqPendente",1,0,"R",0);
$pdf->Cell(77,5,"$ResumoMesReqAjustesPendente",1,0,"R",0);
$pdf->Cell(77,5,"$ResumoMesReqOutrasPendente",1,0,"R",0);
$pdf->Cell(50,5,"$ResumoMesSubTotalPendente",1,1,"R",0);

//iNFORMAÇÕES:
$pdf->ln(5);
$observacoes = "1) Este relatório integra ao valor das requisições as modificações por elas sofridas";
$pdf->SetFont("Arial","B",8);
$pdf->Cell(111,5,$observacoes,"",1,"L",0);
$observacoes = "2) O saldo final = saldo inicial + entradas – saídas confirmadas";
$pdf->Cell(111,5,$observacoes,"",1,"L",0);
$observacoes = "3) O subtotal  = requisições pendentes + outras";
$pdf->Cell(111,5,$observacoes,"",1,"L",0);
$pdf->SetFont("Arial","",9);

$pdf->Output();
?>
