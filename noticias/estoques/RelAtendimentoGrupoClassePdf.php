<?php
#---------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAtendimentoGrupoClassePdf.php
# Objetivo: Programa de Impressão do Relatório de Atendimento por Grupo, Classe e Subclasse.
# Autor:    Filipe Cavalcanti
# Data:     16/08/2005
# Alterado: Carlos Abreu
# Data:     07/06/2007 - Conclusão do desenvolvimento do relatório
# OBS.:     Tabulação 2 espaços
#---------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/RelAtendimentoGrupoClasse.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Almoxarifado     = $_GET['Almoxarifado'];
		$Grupo						= $_GET['Grupo'];
		$Classe						= $_GET['Classe'];
		$Subclasse				= $_GET['Subclasse'];
		$DataFim					= $_GET['DataFim'];
		$DataIni					= $_GET['DataIni'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapePaisagem();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Atendimento por Grupo, Classe e Subclasse";

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

# Período para consulta no banco de dados #
$DataInibd = substr($DataIni,6,4)."-".substr($DataIni,3,2)."-".substr($DataIni,0,2);
$DataFimbd = substr($DataFim,6,4)."-".substr($DataFim,3,2)."-".substr($DataFim,0,2);

# Conecta no banco de dados #
$db = Conexao();

# Query para escrever o almoxarifado #
$SqlAlmoxarifado  = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado ";
$cms              = $db->query($SqlAlmoxarifado);
if( db::isError($cms) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $SqlAlmoxarifado");
}else{
		$campo    = $cms->fetchRow();
		$DescAlmo = $campo[0];
}

# Pega a descrição do Grupo e da Classe #
$SqlGrupoClasse  = " SELECT GRU.EGRUMSDESC, CLA.ECLAMSDESC ";
$SqlGrupoClasse .= "  FROM SFPC.TBCLASSEMATERIALSERVICO CLA, SFPC.TBGRUPOMATERIALSERVICO GRU ";
$SqlGrupoClasse .= " WHERE CLA.CGRUMSCODI = GRU.CGRUMSCODI AND CLA.CCLAMSCODI = $Classe";
$SqlGrupoClasse .= "   AND GRU.CGRUMSCODI = $Grupo ";
$CsGrupoClasse   = $db->query($SqlGrupoClasse);
if( db::isError($CsGrupoClasse) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $CsGrupoClasse");
}else{
		$campo      = $CsGrupoClasse->fetchRow();
		$DescGrupo  = $campo[0];
		$DescClasse = $campo[1];
}

$pdf->Cell(30,5,"ALMOXARIFADO",1,0,"L",1);
$pdf->Cell(250,5,$DescAlmo,1,1,"L",0);
$pdf->Cell(30,5,"PERÍODO",1,0,"L",1);
$pdf->Cell(250,5,$DataIni." À ".$DataFim,1,1,"L",0);
$pdf->Cell(30,5, "GRUPO / CLASSE", 1, 0, "L", 1);
$pdf->Cell(250,5,$DescGrupo." / ".$DescClasse,1,1,"L",0);

#SQL PRINCIPAL #

/*
$Sql  = "		SELECT REQ.TREQMAULAT, REQ.AREQMAANOR, REQ.CREQMACODI, ITEM.AITEMRQTSO, ";
$Sql  .= "	       ITEM.AITEMRQTAT, REQ.DREQMADATA, CENTRO.ECENPODESC, CENTRO.ECENPODETA, CENTRO.CCENPONRPA, ";
$Sql  .= "         SUB.ESUBCLDESC, MAT.EMATEPDESC, MAT.CMATEPSEQU, UNI.EUNIDMSIGL ";
$Sql  .= "	  FROM SFPC.TBREQUISICAOMATERIAL REQ,SFPC.TBSITUACAOREQUISICAO SITU, SFPC.TBCENTROCUSTOPORTAL CENTRO, ";
$Sql  .= "	       SFPC.TBITEMREQUISICAO ITEM, SFPC.TBMATERIALPORTAL MAT, ";
$Sql  .= "	       SFPC.TBLOCALIZACAOMATERIAL LOCM, SFPC.TBARMAZENAMENTOMATERIAL ARM, ";
$Sql  .= "	       SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
$Sql  .= "  	   	 SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBUNIDADEDEMEDIDA UNI ";
$Sql  .= "	 WHERE SITU.CTIPSRCODI BETWEEN 3 AND 4 AND SITU.CREQMASEQU = REQ.CREQMASEQU ";
$Sql  .= "	   AND REQ.DREQMADATA >= '$DataInibd' AND REQ.DREQMADATA <= '$DataFimbd' ";
$Sql  .= "	 	 AND REQ.CCENPOSEQU = CENTRO.CCENPOSEQU AND GRU.CGRUMSCODI = $Grupo ";
$Sql  .= "	 	 AND CLA.CCLAMSCODI = $Classe AND MAT.CUNIDMCODI = UNI.CUNIDMCODI ";
if( $Subclasse != 0 ){
		$Sql  .= "	 	 AND SUB.CSUBCLSEQU = $Subclasse ";
}
$Sql  .= "	 	 AND REQ.CREQMASEQU = ITEM.CREQMASEQU AND ITEM.AITEMRQTAT > 0 ";
$Sql  .= "	 	 AND ITEM.CMATEPSEQU = MAT.CMATEPSEQU AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
$Sql  .= "	 	 AND SUB.CCLAMSCODI = CLA.CCLAMSCODI ";
$Sql  .= "   	 AND SUB.CGRUMSCODI = CLA.CGRUMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
$Sql  .= "	 	 AND MAT.CMATEPSEQU = ARM.CMATEPSEQU AND ARM.CLOCMACODI = LOCM.CLOCMACODI ";
$Sql  .= "	 	 AND LOCM.CALMPOCODI = $Almoxarifado ";
$Sql  .= "ORDER BY  SUB.ESUBCLDESC,MAT.EMATEPDESC ";
*/

$Sql  = "SELECT (SELECT MAX(TSITRESITU) FROM SFPC.TBSITUACAOREQUISICAO WHERE CTIPSRCODI IN (3,4) AND CREQMASEQU = REQ.CREQMASEQU), ";
$Sql .= "       REQ.AREQMAANOR, REQ.CREQMACODI, ITEM.AITEMRQTSO,ITEM.AITEMRQTAT, REQ.DREQMADATA, ";
$Sql .= "       CENTRO.ECENPODESC, CENTRO.ECENPODETA, CENTRO.CCENPONRPA, SUB.ESUBCLDESC, MAT.EMATEPDESC, MAT.CMATEPSEQU, UNI.EUNIDMSIGL ";
$Sql .= "  FROM SFPC.TBITEMREQUISICAO ITEM ";
$Sql .= " INNER JOIN SFPC.TBREQUISICAOMATERIAL REQ ";
$Sql .= "    ON ITEM.CREQMASEQU = REQ.CREQMASEQU ";
$Sql .= "   AND REQ.CALMPOCODI = $Almoxarifado ";
$Sql .= " INNER JOIN SFPC.TBCENTROCUSTOPORTAL CENTRO ";
$Sql .= "    ON REQ.CCENPOSEQU = CENTRO.CCENPOSEQU ";
$Sql .= " INNER JOIN SFPC.TBSITUACAOREQUISICAO SITU ";
$Sql .= "    ON REQ.CREQMASEQU = SITU.CREQMASEQU ";
$Sql .= "   AND SITU.TSITREULAT = (SELECT MAX(TSITREULAT) FROM SFPC.TBSITUACAOREQUISICAO WHERE CREQMASEQU = REQ.CREQMASEQU) ";
$Sql .= "   AND SITU.CTIPSRCODI IN (3,4,5) ";
$Sql .= " INNER JOIN SFPC.TBMATERIALPORTAL MAT ";
$Sql .= "    ON ITEM.CMATEPSEQU = MAT.CMATEPSEQU ";
$Sql .= " INNER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
$Sql .= "    ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
$Sql .= "   AND SUB.CSUBCLSEQU = $Subclasse ";
$Sql .= " INNER JOIN SFPC.TBUNIDADEDEMEDIDA UNI ";
$Sql .= "    ON MAT.CUNIDMCODI = UNI.CUNIDMCODI ";
$Sql .= " WHERE REQ.DREQMADATA >= '$DataInibd' AND REQ.DREQMADATA <= '$DataFimbd' ";
$Sql .= " ORDER BY SUB.ESUBCLDESC, MAT.EMATEPDESC ";

$res  = $db->query($Sql);
if( db::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $Sql");
}else{
		$rows = $res->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelAtendimentoGrupoClasse.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1&Grupo=$Grupo&Classe=$Classe&Subclasse=$Subclasse";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
				for( $i=0; $i< $rows; $i++ ){
						$Linha               = $res->fetchRow();
						$DataAtendimento   	 = $Linha[0];
						$AnoRequisicao   		 = $Linha[1];
						$NumRequisicao   	   = $Linha[2];
						$QtdSolicitada   		 = $Linha[3];
						$QtdAtendida   		   = $Linha[4];
						$DataRequisicao  		 = $Linha[5];
						$DescCC  				     = $Linha[6];
						$DetaCC  				     = $Linha[7];
						$RpaCC  					   = $Linha[8];
						$SubclasseDesc			 = $Linha[9];
						$DescMaterial				 = $Linha[10];
						$CodMaterial				 = $Linha[11];
						$UniSigl						 = $Linha[12];
						$DescCentro   			 = "RPA ".$RpaCC." - ".$DescCC. " - ".$DetaCC;
						$DataRequisicao 		 = substr($DataRequisicao,8,2)."/".substr($DataRequisicao,5,2)."/".substr($DataRequisicao,0,4);
						$DataAtendimento		 = substr($DataAtendimento,8,2)."/".substr($DataAtendimento,5,2)."/".substr($DataAtendimento,0,4);
						$TamDescCentro = strlen($DescCentro);
						if( $SubclasseDesc != $SubclasseAntes or $DescMaterial != $MaterialAntes ){
								if ($TotalMaterial){
									$pdf->Cell(259,5,"TOTAL ATENDIDA",1,0,"R",1);
									$pdf->Cell(20,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalMaterial))),1,1,"R",0);
									$TotalMaterial = null;
								}
								$pdf->ln(5);
								$pdf->Cell(279,5,"CÓDIGO REDUZIDO / MATERIAL / UNIDADE",1,1,"C",1);
								$pdf->MultiCell(279,5,$CodMaterial." / ".$DescMaterial." / ".$UniSigl,1,"J",0);
								$pdf->SetFont("Arial","",8);
								$pdf->Cell(25,5,"Data Atendimento",1,0,"C",1);
								$pdf->Cell(180,5,"Centro de Custo",1,0,"C",1);
								$pdf->Cell(34,5,"Número/Ano Requisição",1,0,"C",1);
								$pdf->Cell(20,5,"Qtd. Solicitada",1,0,"C",1);
								$pdf->Cell(20,5,"Qtd. Atendida",1,1,"C",1);
						}
						$pdf->SetFont("Arial","",9);
						if ($TamDescCentro >= 89 ){
								$pdf->Cell(25, 10,$DataAtendimento,1,0,"C",0);
								$pdf->SetX(35);
								$pdf->Cell(180,10," ",1,0,"L",0);
								$pdf->SetX(35);
								$pdf->Cell(180,5,"RPA ".$RpaCC." - ".$DescCC,0,0,"L",0);
								$pdf->Cell(34, 10,substr($NumRequisicao+100000,1)."/".$AnoRequisicao,1,0,"C",0);
								$pdf->Cell(20, 10,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdSolicitada))),1,0,"R",0);
								$pdf->Cell(20, 10,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdAtendida))),1,0,"R",0);
								$pdf->ln(5);
								$pdf->SetX(35);
								$pdf->Cell(180,5,$DetaCC,0,0,"L",0);
								$pdf->ln(5);
					  }else{
						  	$pdf->Cell(25, 5,$DataAtendimento,1,0,"C",0);
								$pdf->Cell(180,5,"RPA ".$RpaCC." - ".$DescCC. " - ".$DetaCC,1,0,"L",0);
			  					$pdf->Cell(34, 5,substr($NumRequisicao+100000,1)."/".$AnoRequisicao,1,0,"C",0);
								$pdf->Cell(20, 5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdSolicitada))),1,0,"R",0);
								$pdf->Cell(20, 5,converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdAtendida))),1,1,"R",0);
					  }
						$TotalAtendida  = $TotalAtendida + $QtdAtendida;
						$TotalMaterial += $QtdAtendida;
						$MaterialAntes  = $DescMaterial;
						$SubclasseAntes = $SubclasseDesc;
  			}
  			if ($TotalMaterial){
				$pdf->Cell(259,5,"TOTAL ATENDIDA",1,0,"R",1);
				$pdf->Cell(20,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalMaterial))),1,1,"R",0);
				$TotalMaterial = null;
			}
  			$pdf->Cell(259,5,"TOTAL GERAL ATENDIDA",1,0,"R",1);
			$pdf->Cell(20,5,converte_quant(sprintf("%01.2f",str_replace(",",".",$TotalAtendida))),1,1,"R",0);
		}
}
$db->disconnect();
$pdf->Output();
?> 
