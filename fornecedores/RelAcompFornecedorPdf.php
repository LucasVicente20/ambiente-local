<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelAcompFornecedorPdf.php
# Autor:    Roberta Costa
# Data:     09/09/04
# Objetivo: Programa de Impressão do Acompanhamento do Fornecedor
#                      - Exibir data de última alteração
# 			    28/05/2007 - Receber comissão e data análise documentação
#           29/05/2007 - Receber novos campos (índice Endividamento e Microempresa ou EPP)
# Alterado: Carlos Abreu
# Data:     18/06/2007 - Receber novo campo (índice de solvência)
# Alterado: Ariston
# Data:     09/08/2010	- Adicionado opção para incluir sócios
# Autor:    Everton Lino
# Data:     30/08/2010
# Alterado: Rodrigo Melo
# Data:     02/06/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
#                      - Alteração do nome do arquivo de "CadIncluirClasses.php" para "CadIncluirGrupos.php"
# Alterado: Rodrigo Melo
# Data:     02/06/2011 - Tarefa Redmine: 2727 - Incluir novos campos para preenchimento no SICREF - Dados do Representante Legal
# Alterado: Rodrigo Melo
# Data:     16/09/2011 - Tarefa Redmine: 3718 - Remoção de campos de Representante Legal no módulo de fornecedores (SICREF)
# Objetivo: Data de última alteração de contrato ou estatuto
#-------------------------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Ernesto Ferreira
# Data:     07/11/2018 
# Objetivo: Tarefa Redmine 206429
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "funcoesFornecedores.php";
require_once( "funcoesDocumento.php");
# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Sequencial = $_GET['Sequencial'];
		$anoAnexacao = $_GET['anoAnexacao'];
		$Mensagem   = urldecode($_GET['Mensagem']);
		if ($Mensagem <> "") {
			$Mensagem  = "ATENÇÃO! ".$Mensagem;
		}
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Acompanhamento do Fornecedor";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("P","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

$db	  = Conexao();
# Pega os Dados do Fornecedor Cadastrado #
$sql  = " SELECT AFORCRSEQU, FORN.APREFOSEQU, AFORCRCCGC, AFORCRCCPF, AFORCRIDEN, ";
$sql .= "        NFORCRORGU, NFORCRRAZS, NFORCRFANT, FORN.CCEPPOCODI, FORN.CCELOCCODI, ";
$sql .= "        EFORCRLOGR, AFORCRNUME, EFORCRCOMP, EFORCRBAIR, NFORCRCIDA, ";
$sql .= "        CFORCRESTA, AFORCRCDDD, AFORCRTELS, AFORCRNFAX, NFORCRMAIL, ";
$sql .= "        AFORCRCPFC, NFORCRCONT, NFORCRCARG, AFORCRDDDC, AFORCRTELC, ";
$sql .= "        AFORCRREGJ, DFORCRREGJ, AFORCRINES, AFORCRINME, AFORCRINSM, ";
$sql .= "        VFORCRCAPS, VFORCRCAPI, VFORCRPATL, VFORCRINLC, VFORCRINLG, ";
$sql .= "        DFORCRULTB, DFORCRCNFC, NFORCRENTP, AFORCRENTR, DFORCRVIGE, ";
$sql .= "     	 AFORCRENTT, DFORCRGERA, FFORCRCUMP, ECOMLIDESC, DFORCRANAL, ";
$sql .= "     	 FFORCRMEPP, VFORCRINDI, VFORCRINSO, FFORCRTIPO, DFORCRCONT, ";
$sql .= "		 B.DPREFOGERA ";
$sql .= "   FROM SFPC.TBFORNECEDORCREDENCIADO FORN";
$sql .= "   LEFT OUTER JOIN SFPC.TBCOMISSAOLICITACAO COM ON FORN.CCOMLICODI = COM.CCOMLICODI ";
$sql .= "   LEFT JOIN  SFPC.TBPREFORNECEDOR AS B ON B.APREFOSEQU = FORN.APREFOSEQU";
$sql .= "  WHERE AFORCRSEQU = $Sequencial";
$result = $db->query($sql);
if( PEAR::isError($result) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Linha = $result->fetchRow();

		# Variáveis Formulário A #
		$Sequencial		    = $Linha[0];
		$PreInscricao   	= $Linha[1];
		$CNPJ							= $Linha[2];
		$CPF							= $Linha[3];
		if( $CNPJ != 0 ){
				$CPF_CNPJ     = $CNPJ;
				$DescCNPJCPF  = "CNPJ";
				$CNPJCPFForm	= FormataCNPJ($CNPJ);
				$MicroEmpresa = $Linha[45];
		}else{
				$CPF_CNPJ     = $CPF;
				$DescCNPJCPF  = "CPF";
				$CNPJCPFForm  = FormataCPF($CPF);
		}
		if( $Linha[4] != "" ){ $Identidade = $Linha[4]; }else{ $Identidade = "NÃO INFORMADO"; }
		if( $Linha[5] != "" ){ $OrgaoEmissorUF=$Linha[5]; }else{ $OrgaoEmissorUF= "NÃO INFORMADO"; }

		$RazaoSocial  		= $Linha[6];
		if( $Linha[7] != "" ){ $NomeFantasia = $Linha[7]; }else{ $NomeFantasia = "NÃO INFORMADO"; }
		if( $Linha[8] != "" ){
				$CEP = $Linha[8];
		}else{
				$CEP = $Linha[9];
		}
		$Logradouro 			= substr($Linha[10],0,60);
		if( $Linha[11] != "" ){ $Numero = $Linha[11]; }else{ $Numero = "NÃO INFORMADO"; }
		if( $Linha[12] != "" ){ $Complemento = $Linha[12]; }else{ $Complemento = "NÃO INFORMADO"; }
		$Bairro   	 			= $Linha[13];
		$Cidade 					= $Linha[14];
		$UF       				= $Linha[15];
		if( $Linha[16] != "" ){ $DDD = $Linha[16]; }else{ $DDD = "NÃO INFORMADO"; }
		if( $Linha[17] != "" ){ $Telefone = $Linha[17]; }else{ $Telefone = "NÃO INFORMADO"; }
		if( $Linha[18] != "" ){ $Fax = $Linha[18]; }else{ $Fax = "NÃO INFORMADO"; }
		if( $Linha[19] != "" ){ $Email = $Linha[19]; }else{ $Email = "NÃO INFORMADO"; }
		if( $Linha[20] != "" ){ $CPFContato = substr($Linha[20],0,3).".".substr($Linha[20],3,3).".".substr($Linha[20],6,3)."-".substr($Linha[20],9,2); }else{ $CPFContato = "NÃO INFORMADO"; }
		if( $Linha[21] != "" ){ $NomeContato = $Linha[21]; }else{ $NomeContato = "NÃO INFORMADO"; }
		if( $Linha[22] != "" ){ $CargoContato = $Linha[22]; }else{ $CargoContato = "NÃO INFORMADO"; }
		if( $Linha[23] != "" ){ $DDDContato = $Linha[23]; }else{ $DDDContato = "NÃO INFORMADO"; }
		if( $Linha[24] != "" ){ $TelefoneContato = $Linha[24]; }else{ $TelefoneContato = "NÃO INFORMADO"; }
		$RegistroJunta		= $Linha[25];
		if( $Linha[26] != "" ){
			$DataRegistro		= DataBarra($Linha[26]);
		} else {
			$DataRegistro			= "";
		}
		# Variáveis Formulário B #
		if( $Linha[27] != "" ){ $InscEstadual = $Linha[27]; }else{ $InscEstadual = "NÃO INFORMADO"; }
		if( $Linha[28] != "" ){ $InscMercantil = $Linha[28]; }else{ $InscMercantil = "-"; }
		if( $Linha[29] != "" ){ $InscOMunic = $Linha[29]; }else{ $InscOMunic = "-"; }

		# Variáveis Formulário C #
		if( $Linha[30] != "" ){ $CapSocial = converte_valor($Linha[30]); } else { $CapSocial = "NÃO INFORMADO";  }
		if( $Linha[31] != "" ){ $CapIntegralizado = converte_valor($Linha[31]); }else{ $CapIntegralizado = "NÃO INFORMADO"; }
		if( $Linha[32] != "" ){ $Patrimonio	= converte_valor($Linha[32]); }else{ $Patrimonio = "NÃO INFORMADO"; }
		if( $Linha[33] != "" ){ $IndLiqCorrente = converte_valor($Linha[33]); }else{ $IndLiqCorrente = "NÃO INFORMADO"; }
		if( $Linha[34] != "" ){ $IndLiqGeral = converte_valor($Linha[34]); }else{ $IndLiqGeral = "NÃO INFORMADO"; }
		if( $Linha[46] != "" ){ $IndEndividamento = converte_valor($Linha[46]); }else{ $IndEndividamento = "NÃO INFORMADO"; }
		if( $Linha[47] != "" ){ $IndSolvencia = converte_valor($Linha[47]); }else{ $IndSolvencia = "NÃO INFORMADO"; }
		if( $Linha[35] != "" ){
				$DataUltBalanco	= DataBarra($Linha[35]);
		}else{
				$DataUltBalanco = "NÃO INFORMADO";
		}
		if( $Linha[36] != "" ){
				$DataCertidaoNeg	= DataBarra($Linha[36]);
		}else{
				$DataCertidaoNeg = "NÃO INFORMADO";
		}
		if( $Linha[49] != "" ){
				$DataContratoEstatuto	= DataBarra($Linha[49]);
		}else{
				$DataContratoEstatuto = "NÃO INFORMADO";
		}

		# Variáveis Formulário D #
		if( $Linha[37] != "" ){ $NomeEntidade = $Linha[37]; }else{ $NomeEntidade = "NÃO INFORMADO"; }
		if( $Linha[38] != "" ){ $RegistroEntidade = $Linha[38]; }else{ $RegistroEntidade = "NÃO INFORMADO"; }
		if( $Linha[39] != "" ){
				$DataVigencia	= DataBarra($Linha[39]);
		}else{
				$DataVigencia = "NÃO INFORMADO";
		}
		if( $Linha[40] != "" ){ $TecnicoEntidade = $Linha[40]; }else{ $TecnicoEntidade = "NÃO INFORMADO"; }
		$DataInscricao		= DataBarra($Linha[41]);
		$Cumprimento			= $Linha[42];
		$ComissaoResp		  = $Linha[43];
		if( $Linha[44] <> "" ){
				$DataAnaliseDoc= substr($Linha[44],8,2)."/".substr($Linha[44],5,2)."/".substr($Linha[44],0,4);
		}	else {
				$DataAnaliseDoc= "";
		}
		$TipoHabilitacao = $Linha[48];

		# Data de inscrição no SICREF #
		if( $Linha[50] != "" ){ 
			$DataInscSicref	= substr($Linha[50], 8, 2).'/'.substr($Linha[50], 5, 2).'/'.substr($Linha[50], 0, 4).' '.substr($Linha[50], 11, 9);
		} else { 
			$DataInscSicref = "NÃO INFORMADO"; 
		}

		# Pega os Dados da Tabela de Situação #
		$sql    = "SELECT A.DFORSISITU, B.CFORTSCODI, A.EFORSIMOTI, A.DFORSIEXPI ";
		$sql   .= "  FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B ";
		$sql   .= " WHERE A.AFORCRSEQU = $Sequencial ";
		$sql   .= "   AND A.CFORTSCODI = B.CFORTSCODI ";
		$sql   .= " ORDER BY A.DFORSISITU DESC";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				for( $i=0;$i<1;$i++ ){
						$Linha 	 									 = $result->fetchRow();
						if( $Linha[0] != "" ){
								$DataSituacao  = substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
						}else{
								$DataSituacao  = "";
						}
						$Situacao			 = $Linha[1];
						$Motivo				 = strtoupper2($Linha[2]);
						if( $Linha[3] != "" ){
								$DataSuspensao = substr($Linha[3],8,2)."/".substr($Linha[3],5,2)."/".substr($Linha[3],0,4);
						}else{
								$DataSuspensao = "";
						}
				}
		}

		# Mostra Tabela de Situação #
		$db	    = Conexao();
		$sql    = "SELECT EFORTSDESC FROM SFPC.TBFORNECEDORTIPOSITUACAO";
		$sql   .= " WHERE CFORTSCODI = ".$Situacao."";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$situacao = $result->fetchRow();
				$DescSituacao = $situacao[0];
		}

		# Verifica a Validação das Certidões do Fornecedor #
		$sql  = "SELECT A.CTIPCECODI, A.ETIPCEDESC, B.DFORCEVALI ";
		$sql .= "  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBFORNECEDORCERTIDAO B ";
		$sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI AND A.FTIPCEOBRI = 'S' ";
		$sql .= "   AND B.AFORCRSEQU = $Sequencial";
		$sql .= " ORDER BY 1";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $result->numRows();
				for( $i=0; $i<=$Rows;$i++ ){
						$DataHoje = date("Y-m-d");
						$Linha 	  = $result->fetchRow();
						if( $i == 0 ){
								if( $Linha[2] < $DataHoje ){
										$Cadastrado = "INABILITADO";
								}else{
										$Cadastrado = "HABILITADO";
								}
						}
				}
		}

		# Verifica se já Existe Data de CHF #
		$sql    = "SELECT DFORCHGERA,DFORCHVALI FROM SFPC.TBFORNECEDORCHF ";
		$sql   .= " WHERE AFORCRSEQU = $Sequencial ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $result->numRows();
				if( $Rows != 0 ){
						$Linha 	= $result->fetchRow();
						$DataGeracaoCHF  = substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
						$DataValidadeCHF = substr($Linha[1],8,2)."/".substr($Linha[1],5,2)."/".substr($Linha[1],0,4);
				}else{
						$DataGeracaoCHF  = "-";
						$DataValidadeCHF = "-";
				}
		}

		# Busca os Dados da Tabela de Conta Bancária de acordo com o sequencial do Fornecedor #
		$sql    = "SELECT CFORCBBANC, CFORCBAGEN, CFORCBCONT, TFORCBULAT ";
		$sql   .= "  FROM SFPC.TBFORNCONTABANCARIA ";
		$sql   .= " WHERE AFORCRSEQU = $Sequencial ";
		$sql   .= " ORDER BY TFORCBULAT";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $result->numRows();
				for( $i=0;$i<$Rows;$i++ ){
						$Linha 	= $result->fetchRow();
						if( $i == 0	){
								$Banco1					= $Linha[0];
								$Agencia1				= $Linha[1];
								$ContaCorrente1	= $Linha[2];
						}else{
								$Banco2					= $Linha[0];
								$Agencia2				= $Linha[1];
								$ContaCorrente2	= $Linha[2];
						}
				}
		}

		# Gera o Número de Controle do Fornecedor #
		$NumeroCont  = $Sequencial.$CPF_CNPJ.date("Ymd");
		$NumControle = ControlaDocumento($NumeroCont);

		# Mensagem de Irregularidade #
		$pdf->SetFont("Arial","B",9);
		if( $Mensagem != "" ){
				$pdf->MultiCell(190,5,strtoupper2($Mensagem),0,'L',0);
		}
		$pdf->SetFont("Arial","",9);
		$pdf->Cell(60,5,'Código do Fornecedor',1,0,'L',1);
		$pdf->Cell(130,5,$Sequencial,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Cumprimento Inc.XXXIII Art.7ºCons.Fed.',1,0,'L',1);
		$pdf->Cell(130,5,'SIM',1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Situação',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($DescSituacao),1,0,'L',0);
		$pdf->ln(5);
		if( $Motivo != "" ){
				$pdf->Cell(60	,5,'Motivo',1,0,'L',1);
				$pdf->Cell(130,5,strtoupper2($Motivo),1,0,'L',0);
				$pdf->ln(5);
		}
		$pdf->Cell(60,5,'Data de Geração de CHF',1,0,'L',1);
		$pdf->Cell(130,5,$DataGeracaoCHF,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Data de Validade de CHF',1,0,'L',1);
		$pdf->Cell(130,5,$DataValidadeCHF,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Data de Inscrição do SICREF',1,0,'L',1);
		$pdf->Cell(130,5,$DataInscSicref,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Data Cadastramento',1,0,'L',1);
		$pdf->Cell(130,5,$DataInscricao,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Comissão Responsável Análise',1,0,'L',1);
		$pdf->Cell(130,5,$ComissaoResp,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Data de Análise Documentação',1,0,'L',1);
		$pdf->Cell(130,5,$DataAnaliseDoc,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Tipo de habilitação',1,0,'L',1);
		if($TipoHabilitacao=='L'){
			$pdf->Cell(130,5,'LICITAÇÃO',1,0,'L',0);
		}else if($TipoHabilitacao=='D'){
			$pdf->Cell(130,5,'COMPRA DIRETA',1,0,'L',0);
		}else if($TipoHabilitacao=='E'){
			$pdf->Cell(130,5,'ESTOQUES',1,0,'L',0);
		}
		$pdf->ln(5);


		# OCORRÊNCIAS #
		$pdf->Cell(190,5,'OCORRÊNCIAS',1,1,'C',1);

		# Busca os Dados da Tabela de Ocorrências de acordo com o sequencial do Fornecedor #
		$sql  = "SELECT A.CFORTOCODI, A.EFOROCDETA, A.DFOROCDATA, B.EFORTODESC ";
		$sql .= "  FROM SFPC.TBFORNECEDOROCORRENCIA A, SFPC.TBFORNTIPOOCORRENCIA B";
		$sql .= " WHERE A.CFORTOCODI = B.CFORTOCODI AND A.AFORCRSEQU = $Sequencial ORDER BY 3,1";
		$res  = $db->query($sql);
	  if( PEAR::isError($res) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				if( $Rows == 0 ){
            $pdf->Cell(190,5,'NENHUMA OCORRÊNCIA ENCONTRADA',1,1,'C',0);
				}else{
						for( $i=0;$i<$Rows;$i++ ){
								$Linha     = $res->fetchRow();
  	      			$Codigo    = $Linha[0];
  	      			$Detalhe   = $Linha[1];
  	      			$Data      = $Linha[2];
  	      			$Descricao = $Linha[3];
  	      			if( $i == 0 ){
										$pdf->Cell(20,5,'Data',1,0,'L',1);
										$pdf->Cell(170,5,'Tipo de ocorrência/ Detalhamento',1,1,'L',1);
	        	  	}
								$pdf->Cell(20,5,substr($Data,8,2)."/".substr($Data,5,2)."/".substr($Data,0,4),1,0,'C',0);
								$pdf->Cell(170,5,strtoupper2($Descricao),1,1,'L',0);
								$pdf->MultiCell(190,5,strtoupper2($Detalhe),1,'L',0);
          	}
        }
		}

		# HABILITAÇÃO JURÍDICA #
		$pdf->SetFont("Arial","B",9);
		$pdf->Cell(190,5,'HABILITAÇÃO JURÍDICA',1,0,'C',1);
		$pdf->SetFont("Arial","",9);
		$pdf->ln(5);
		$pdf->Cell(60,5,$DescCNPJCPF,1,0,'L',1);
		$pdf->Cell(130,5,$CNPJCPFForm,1,0,'L',0);
		$pdf->ln(5);
		
		
		if( $CNPJ != 0 ){
			$pdf->Cell(60,5,getDescPorteEmpresaTitulo(),1,0,'L',1);
			$pdf->Cell(130,5,getDescPorteEmpresa($MicroEmpresa),1,0,'L',0);
			//if ( $MicroEmpresa == "S") {
			//		$pdf->Cell(130,5,'SIM',1,0,'L',0);
			//} else {
			//		$pdf->Cell(130,5,'NÃO',1,0,'L',0);
			//}
			$pdf->ln(5);
		}
		if( $Identidade != 0 ){
			if( $CNPJ != 0 ){
				$pdf->Cell(60,5,'Identidade Representante Legal',1,0,'L',1);
				$pdf->Cell(130,5,$Identidade,1,0,'L',0);
				$pdf->ln(5);
			} else {
				$pdf->Cell(60,5,'Identidade',1,0,'L',1);
				$pdf->Cell(130,5,$Identidade,1,0,'L',0);
				$pdf->ln(5);
			}
			$pdf->Cell(60,5,'Órgao Emissor/UF',1,0,'L',1);
			$pdf->Cell(130,5,$OrgaoEmissorUF,1,0,'L',0);
			$pdf->ln(5);
		}
		if( $CNPJ != 0 ){
			$pdf->Cell(60,5,'Razão Social',1,0,'L',1);
		} else {
			$pdf->Cell(60,5,'Nome',1,0,'L',1);
		}
		$pdf->Cell(130,5,strtoupper2($RazaoSocial),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Nome Fantasia',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($NomeFantasia),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'CEP',1,0,'L',1);
		$pdf->Cell(130,5,$CEP,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Logradouro',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($Logradouro),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Número',1,0,'L',1);
		$pdf->Cell(130,5,$Numero,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Complemento',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($Complemento),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Bairro',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($Bairro),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Cidade',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($Cidade),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'UF',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($UF),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'DDD',1,0,'L',1);
		$pdf->Cell(130,5,$DDD,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Telefone(s)',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($Telefone),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'E-mail',1,0,'L',1);
		$pdf->Cell(130,5,$Email,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Fax',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($Fax),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Registro Junta Comercial ou Cartório',1,0,'L',1);
		if($RegistroJunta=='' or is_null($RegistroJunta)){
			$pdf->Cell(130,5,'NÃO INFORMADO',1,0,'L',0);
		}else{
			$pdf->Cell(130,5,$RegistroJunta,1,0,'L',0);
		}
		$pdf->ln(5);
		$pdf->Cell(60,5,'Data Reg. Junta Comercial ou Cartório',1,0,'L',1);
		if($DataRegistro=='' or is_null($DataRegistro)){
			$pdf->Cell(130,5,'NÃO INFORMADA',1,0,'L',0);
		}else{
			$pdf->Cell(130,5,$DataRegistro,1,0,'L',0);
		}
		$pdf->ln(5);
		$pdf->Cell(60,5,'Nome do Contato',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($NomeContato),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'CPF do Contato',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($CPFContato),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Cargo do Contato',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($CargoContato),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'DDD do Contato',1,0,'L',1);
		$pdf->Cell(130,5,$DDDContato,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Telefone do Contato',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($TelefoneContato),1,0,'L',0);
		$pdf->ln(5);


		# SÓCIOS
		if( $CNPJ != 0 ){
			$pdf->SetFont("Arial","B",9);
			$pdf->Cell(190,5,'SÓCIOS',1,0,'C',1);
			$pdf->SetFont("Arial","",9);
			$pdf->ln(5);

			$sql  = "
				SELECT
					asoforcada, nsofornome
				FROM SFPC.TBsociofornecedor
				WHERE aforcrsequ = ".$Sequencial."
			";
		  $res = $db->query($sql);

			if( PEAR::isError($res) ){
				EmailErroSQL('Erro ao obter sócios de fornecedor', __FILE__, __LINE__, 'Erro ao obter sócios de fornecedor', $sql, $res);
			}	else{
				$Rows = $res->numRows();
				if($Rows==0){
					$pdf->Cell(190,5,'NENHUM CADASTRADO',1,0,'C',0);
				}else{
					$pdf->Cell(160,5,'Nome',1,0,'L',1);
					$pdf->Cell(30,5,'CPF/CNPJ',1,0,'L',1);

					for($itr=0; $itr<$Rows; $itr++){
						$Linha = $res->fetchRow();
						$socioCPF = $Linha[0];
						$socioNome = $Linha[1];
						$pdf->ln(5);
						$pdf->Cell(160,5,$socioNome,1,0,'L',0);
						$pdf->Cell(30,5,$socioCPF,1,0,'L',0);
					}
				}
			}
			$pdf->ln(5);

		}


		# REGULARIDADE FISCAL #
		$pdf->SetFont("Arial","B",9);
		$pdf->Cell(190,5,'REGULARIDADE FISCAL',1,0,'C',1);
		$pdf->SetFont("Arial","",9);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Inscrição Mercantil',1,0,'L',1);
		$pdf->Cell(130,5,$InscMercantil,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Inscrição Estadual',1,0,'L',1);
		$pdf->Cell(130,5,$InscEstadual,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Inscrição Outro Município',1,0,'L',1);
		$pdf->Cell(130,5,$InscOMunic,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(190,5,'CERTIDÃO FISCAL',1,0,'C',1);
		$pdf->ln(5);
		$pdf->Cell(190,5,'OBRIGATÓRIAS',1,0,'C',0);
		$pdf->ln(5);
		$pdf->Cell(155,5,'Nome da Certidão',1,0,'L',1);
		$pdf->Cell(35,5,'Data de Validade',1,0,'C',1);
		$pdf->ln(5);
		# Mostra a lista de certidões obrigatórias com datas vazias #
		$sql = "SELECT CTIPCECODI, ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO WHERE FTIPCEOBRI = 'S' ORDER BY 1";
		$res = $db->query($sql);
	  if( PEAR::isError($res) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
    		for( $i=0; $i<$Rows;$i++ ){
						$Linha = $res->fetchRow();
      			$DescricaoOb = substr($Linha[1],0,75);
      			$CertidaoOb  = $Linha[0];

  	      	# Verifica se existem certidões obrigatórias cadastradas para o Fornecedor #
						$sqlData  = "SELECT DFORCEVALI FROM SFPC.TBFORNECEDORCERTIDAO ";
						$sqlData .= " WHERE AFORCRSEQU = $Sequencial AND CTIPCECODI = $CertidaoOb";
						$resData = $db->query($sqlData);
					  if( PEAR::isError($resData) ){
							  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$LinhaData = $resData->fetchRow();
  	      			if( $LinhaData[0] != 0 ){
										$DataCertidaoOb[$ob-1] = substr($LinhaData[0],8,2)."/".substr($LinhaData[0],5,2)."/".substr($LinhaData[0],0,4);
								}else{
										$DataCertidaoOb[$ob-1] = 'NÃO INFORMADA';
              	}
            }
						$pdf->Cell(155,5,strtoupper2($DescricaoOb),1,0,'L',0);
						$pdf->Cell(35,5,$DataCertidaoOb[$ob-1],1,0,'C',0);
						$pdf->ln(5);
      	}
  	}
		$pdf->Cell(190,5,'COMPLEMENTARES',1,0,'C',0);
		$pdf->ln(5);
		# Verifica se existem certidões complementares cadastradas para o Fornecedor #
		$sql  = "SELECT A.DFORCEVALI, B.CTIPCECODI, B.ETIPCEDESC  ";
		$sql .= "  FROM SFPC.TBFORNECEDORCERTIDAO A, SFPC.TBTIPOCERTIDAO B ";
		$sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CTIPCECODI = B.CTIPCECODI";
		$sql .= "   AND B.FTIPCEOBRI = 'N' ORDER BY 2";
		$res = $db->query($sql);
	  if( PEAR::isError($res) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				if( $Rows != 0 ){
    				# Mostra as certidões complementares cadastradas #
        		for( $i=0; $i<$Rows;$i++ ){
								$Linha = $res->fetchRow();
  	      			$DescricaoOp					= substr($Linha[2],0,75);
  	      			$CertidaoOpCodigo			= $Linha[1];
  	      			$CertidaoOpcional[$i] = $Linha[1];
								$DataCertidaoOp[$i]		= substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
        				if( $i == 0 ){
										$pdf->Cell(155,5,'Nome da Certidão',1,0,'L',1);
										$pdf->Cell(35,5,'Data de Validade',1,0,'C',1);
										$pdf->ln(5);
            		}
    						$pdf->Cell(155,5,strtoupper2($DescricaoOp),1,0,'L',0);
								$pdf->Cell(35,5,$DataCertidaoOp[$i],1,0,'C',0);
								$pdf->ln(5);
            }
        }else{
				    $pdf->Cell(190,5,'NÃO INFORMADO',1,0,'C',0);
						$pdf->ln(5);
		  	}
    }

		# QUALIFICAÇÃO ECONÔMICA E FINANCEIRA #
		$pdf->SetFont("Arial","B",9);
		$pdf->Cell(190,5,'QUALIFICAÇÃO ECONÔMICA E FINANCEIRA',1,0,'C',1);
		$pdf->SetFont("Arial","",9);
		$pdf->ln(5);
		if( $CNPJ != 0 and $TipoHabilitacao =='L' ){
			$pdf->Cell(60,5,'Capital Social',1,0,'L',1);
			$pdf->Cell(130,5,$CapSocial,1,0,'L',0);
			$pdf->ln(5);
			$pdf->Cell(60,5,'Capital Integralizado',1,0,'L',1);
			$pdf->Cell(130,5,$CapIntegralizado,1,0,'L',0);
			$pdf->ln(5);
			$pdf->Cell(60,5,'Patrimônio Líquido',1,0,'L',1);
			$pdf->Cell(130,5,$Patrimonio,1,0,'L',0);
			$pdf->ln(5);
			$pdf->Cell(60,5,'Índice de Liquidez Corrente',1,0,'L',1);
			$pdf->Cell(130,5,$IndLiqCorrente,1,0,'L',0);
			$pdf->ln(5);
			$pdf->Cell(60,5,'Índice de Liquidez Geral',1,0,'L',1);
			$pdf->Cell(130,5,$IndLiqGeral,1,0,'L',0);
			$pdf->ln(5);
			$pdf->Cell(60,5,'Índice de Endividamento',1,0,'L',1);
			$pdf->Cell(130,5,$IndEndividamento,1,0,'L',0);
			$pdf->ln(5);
			$pdf->Cell(60,5,'Índice de Solvência',1,0,'L',1);
			$pdf->Cell(130,5,$IndSolvencia,1,0,'L',0);
			$pdf->ln(5);
			$pdf->Cell(60,5,'Data de validade do balanço',1,0,'L',1);
			$pdf->Cell(130,5,$DataUltBalanco,1,0,'L',0);
			$pdf->ln(5);
			$pdf->Cell(60,5,'Data da Certidão Negativa',1,0,'L',1);
			$pdf->Cell(130,5,$DataCertidaoNeg,1,0,'L',0);
			$pdf->ln(5);
			$pdf->Cell(60,5,'Data Última Alt. Contrato ou Estatuto',1,0,'L',1);
			$pdf->Cell(130,5,$DataContratoEstatuto,1,0,'L',0);
			$pdf->ln(5);
		}
		$pdf->Cell(60,5,'Banco',1,0,'C',1);
		$pdf->Cell(60,5,'Agência ',1,0,'C',1);
		$pdf->Cell(70,5,'Conta Corrente',1,0,'C',1);
		$pdf->ln(5);

		if( $Banco1 == "" and $Banco2 == "" ){
				$pdf->Cell(190,5,'NÃO INFORMADO',1,0,'C',0);
				$pdf->ln(5);
		}else{
				if( $Banco1 != "" ){
						$pdf->Cell(60,5,$Banco1,1,0,'C',0);
						$pdf->Cell(60,5,$Agencia1,1,0,'C',0);
						$pdf->Cell(70,5,$ContaCorrente1,1,0,'C',0);
						$pdf->ln(5);
				}
				if( $Banco2 != "" ){
						$pdf->Cell(60,5,$Banco2,1,0,'C',0);
						$pdf->Cell(60,5,$Agencia2,1,0,'C',0);
						$pdf->Cell(70,5,$ContaCorrente2,1,0,'C',0);
						$pdf->ln(5);
				}
		}

		# QUALIFICAÇÃO TÉCNICA #
		$pdf->SetFont("Arial","B",9);
		$pdf->Cell(190,5,'QUALIFICAÇÃO TÉCNICA',1,0,'C',1);
		$pdf->SetFont("Arial","",9);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Nome da Entidade ',1,0,'L',1);
		$pdf->Cell(130,5,strtoupper2($NomeEntidade),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Registro ou Inscrição ',1,0,'L',1);
		$pdf->Cell(130,5,$RegistroEntidade,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Data da Vigência',1,0,'L',1);
		$pdf->Cell(130,5,$DataVigencia,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Tecnico da Entidade',1,0,'L',1);
		$pdf->Cell(130,5,$TecnicoEntidade,1,0,'L',0);
		$pdf->ln(5);

		$pdf->Cell(190,5,'AUTORIZAÇÃO ESPECÍFICA',1,0,'C',1);
		$pdf->ln(5);
		# Mostra as autorizações específicas do Inscrito cadatradas #
		$sql  = "SELECT AFORAENUMA, NFORAENOMA, DFORAEVIGE FROM SFPC.TBFORNAUTORIZACAOESPECIFICA ";
		$sql .= " WHERE AFORCRSEQU = $Sequencial";
		$res = $db->query($sql);
	  if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				if( $Rows <> 0 ){
						$pdf->Cell(60,5,'Nome da Entidade Emissora',1,0,'C',0);
						$pdf->Cell(60,5,'Registro ou Inscrição',1,0,'C',0);
						$pdf->Cell(70,5,'Data de Vigência',1,0,'C',0);
        		for( $i=0; $i<$Rows;$i++ ){
								$Linha				= $res->fetchRow();
  	      			$RegistroAutor= $Linha[0];
  	      			$NomeAutor		= $Linha[1];
  	      			$DataVigAutor	= substr($Linha[2],8,2)."/".substr($Linha[2],5,2)."/".substr($Linha[2],0,4);
								$pdf->ln(5);
	              if( $NomeAutor != "" ) {
    							$pdf->Cell(60,5,strtoupper2($NomeAutor),1,0,'L',0);
    						} else {
    							$pdf->Cell(60,5,'NÃO INFORMADO',1,0,'L',0);
    						}
	              if( $RegistroAutor != "" ) {
	    						$pdf->Cell(60,5,strtoupper2($RegistroAutor),1,0,'L',0);
    						} else {
    							$pdf->Cell(60,5,'NÃO INFORMADO',1,0,'L',0);
    						}
	              if( $DataVigAutor != "" ) {
    							$pdf->Cell(70,5,strtoupper2($DataVigAutor),1,0,'C',0);
    						} else {
    							$pdf->Cell(70,5,'-',1,0,'C',0);
    						}
	      		}
      	} else {
						$pdf->Cell(190,5,'NÃO INFORMADO',1,0,'C',0);
        }
  	}
		$pdf->ln(5);
		$pdf->Cell(190,5,'GRUPOS DE FORNECIMENTO',1,0,'C',1);
		$pdf->ln(5);

		# Mostra os grupos de materiais já cadastrados do Fornecedor #
		$sql  = "SELECT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
		$sql .= "  FROM SFPC.TBGRUPOFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B  ";
		$sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
		$sql .= "   AND B.FGRUMSTIPO = 'M' ORDER BY 1,3";
		$res = $db->query($sql);
	  if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				if( $Rows != 0 ){
  					# Mostra os grupos de materiais cadastrados #
						$pdf->Cell(190,5,'MATERIAIS',1,0,'C',0);
						$pdf->ln(5);
        		$DescricaoGrupoAntes = "";
        		for( $i=0; $i<$Rows;$i++ ){
								$Linha										= $res->fetchRow();
  	      			$DescricaoGrupo   				= substr($Linha[2],0,75);
  	      			$Materiais[$i]= "M#".$Linha[1];
  	      			if( $DescricaoGrupoAntes != $DescricaoGrupo ){
        						$pdf->Cell(190,5,strtoupper2($DescricaoGrupo),1,0,'L',1);
										$pdf->ln(5);

        				}
  	      			$DescricaoGrupoAntes = $DescricaoGrupo;
	      		}
      	}
  	}

		# Mostra os grupos de serviços já cadastrados do Fornecedor #
		$sql  = "SELECT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
		$sql .= "  FROM SFPC.TBGRUPOFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B  ";
		$sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
		$sql .= "   AND B.FGRUMSTIPO = 'S' ORDER BY 1,3";
		$res = $db->query($sql);
	  if( PEAR::isError($res) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				if ($Rows != 0) {
  					# Mostra os grupos de serviços cadastrados #
						$pdf->Cell(190,5,'SERVIÇOS',1,0,'C',0);
						$pdf->ln(5);
      			$DescricaoGrupoAntes = "";
        		for( $i=0; $i<$Rows;$i++ ){
							$Linha = $res->fetchRow();
	      			$DescricaoGrupo   = substr($Linha[2],0,75);
	      			$Servicos[$i]= "S#".$Linha[1];
	      			if( $DescricaoGrupo != $DescricaoGrupoAntes ){
									$pdf->Cell(190,5,strtoupper2($DescricaoGrupo),1,0,'L',1);
									$pdf->ln(5);
      				}
  	      		$DescricaoGrupoAntes = $DescricaoGrupo;
	      		}
    		}
	}
	

	$db = Conexao();

	if($anoAnexacao){
		$txtAnexacao = ' AND doc.afdocuanoa = '.$anoAnexacao;
	}else{
		$txtAnexacao = '';
	}

	$sql = "  SELECT doc.cfdocusequ, doc.aprefosequ, doc.aforcrsequ, doc.afdocuanoa, 
			   doc.cfdoctcodi, doc.efdocunome, doc.ifdocuarqu, doc.ffdocuforn, 
			   doc.tfdocuanex, doc.ffdocusitu, doc.cusupocodi, doc.tfdoctulat,
			   (SELECT h.cfdocscodi
			   FROM sfpc.tbfornecedordocumentohistorico h
			   where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as situacao, 
			   (SELECT h.efdochobse
			   FROM sfpc.tbfornecedordocumentohistorico h
			   where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as observacao, 

			   t.efdoctdesc, 

			   (SELECT h.cusupocodi
			   FROM sfpc.tbfornecedordocumentohistorico h
			   where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as usuarioUltimaAlt, 
			   (SELECT u.eusuporesp
			   FROM sfpc.tbfornecedordocumentohistorico h
			   join sfpc.tbusuarioportal u on h.cusupocodi = u.cusupocodi
			   where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as nomeUsuUltimaAlt, 
			   
			   (SELECT h.tfdochulat
			   FROM sfpc.tbfornecedordocumentohistorico h
			   where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as datahoraUltimaAlt, 
			   u.eusuporesp,

			   (SELECT s.efdocsdesc
			   FROM sfpc.tbfornecedordocumentohistorico h
			   join sfpc.tbfornecedordocumentosituacao s ON s.cfdocscodi = h.cfdocscodi
			   where h.cfdocusequ = doc.cfdocusequ order by h.tfdochulat desc limit 1) as situacao_nome
			   
			   
		   FROM sfpc.tbfornecedordocumento doc
		   join sfpc.tbfornecedordocumentotipo t ON t.cfdoctcodi = doc.cfdoctcodi
		   join sfpc.tbusuarioportal u on doc.cusupocodi = u.cusupocodi
		   WHERE aforcrsequ = " . $Sequencial . " AND ffdocusitu = 'A' 
		   ".$txtAnexacao ." order by tfdoctulat, doc.cfdocusequ asc";

   

   $resultDoc = $db->query($sql);
   if (db :: isError($resultDoc)) {
	   ExibeErroBD($ErroPrograma . "\nLinha: " . __LINE__ . "\nSql: $sql");
   } else {

		// DOCUMENTOS
		if($resultDoc->numRows() > 0){

		$pdf->ln(5);
		$pdf->Cell(190,5,'DOCUMENTOS ANEXADOS',1,0,'C',1);
		$pdf->ln(5); 
		$pdf->Cell(190,5,'ANO DA ANEXAÇÃO: '.$anoAnexacao,1,0,'L',0);
		$pdf->ln(5);


		$pdf->Cell(40,5,'TIPO',"LTR",0,'L',1);
		$pdf->Cell(40,5,'NOME ',"LTR",0,'L',1);
		$pdf->Cell(30,5,'RESPONSÁVEL ',"LTR",0,'C',1);
		$pdf->Cell(26,5,'DATA/HORA',"LTR",0,'L',1);
		$pdf->Cell(24,5,'SITUAÇÃO ',"LTR",0,'L',1);
		$pdf->Cell(30,5,'OBSERVAÇÃO',"LTR",0,'L',1);
		$pdf->ln(5);

		$pdf->Cell(40,5,'DO DOCUMENTO',"LR",0,'L',1);
		$pdf->Cell(40,5,'DO DOCUMENTO ',"LR",0,'L',1);
		$pdf->Cell(30,5,'ANEXAÇÃO',"LR",0,'C',1);
		$pdf->Cell(26,5,'ANEXAÇÃO',"LR",0,'L',1);
		$pdf->Cell(24,5,'',"LR",0,'L',1);
		$pdf->Cell(30,5,'',"LR",0,'L',1);
		$pdf->ln(5);


		while ($linha = $resultDoc->fetchRow()) {
			//dentro da repetição



			//|| strlen($linha[5]) > 19 || strlen($linha[13]) > 10
			$pdf->Cell(40,5, substr($linha[14],0,20) ,"LTR",0,'L',0);
			$pdf->Cell(40,5,substr(strtoupper2($linha[5]),0,16),"LTR",0,'L',0);
			$pdf->Cell(30,5,substr($linha[18],0,20),"LTR",0,'C',0);
			$pdf->Cell(26,5,substr(formatarDataHora($linha[8]),0,10),"LTR",0,'C',0);
			$pdf->Cell(24,5,substr($linha[19],0,20),"LTR",0,'C',0);
			$pdf->Cell(30,5,substr($linha[13],0,13),"LTR",0,'L',0);

			$pdf->ln(5);

			$pdf->Cell(40,5, substr($linha[14],20,20) ,"LR",0,'L',0);
			$pdf->Cell(40,5,substr(strtoupper2($linha[5]),16,16),"LR",0,'L',0);
			$pdf->Cell(30,5,substr($linha[18],20,20),"LR",0,'C',0);
			$pdf->Cell(26,5,substr(formatarDataHora($linha[8]),11,20),"LR",0,'C',0);
			$pdf->Cell(24,5,substr($linha[19],20,20),"LR",0,'C',0);
			$pdf->Cell(30,5,substr($linha[13],13,10),"LR",0,'L',0);

			$pdf->ln(5);

			$pdf->Cell(40,5,substr($linha[14],40,20),"LR",0,'L',0);
			$pdf->Cell(40,5,substr(strtoupper2($linha[5]),32,16),"LR",0,'L',0);
			$pdf->Cell(30,5,substr($linha[18],40,20),"LR",0,'C',0);
			$pdf->Cell(26,5,' ',"LR",0,'C',0);
			$pdf->Cell(24,5,substr($linha[19],40,20),"LR",0,'C',0);
			$pdf->Cell(30,5,substr($linha[13],23,10),"LR",0,'L',0);

			$pdf->ln(5);
	
		   }
		   
		}
	}
// -----------------------------------------------------


 		$pdf->Cell(60,5,"Número de Controle",1,0,'L',1);
		$pdf->Cell(130,5,$NumeroCont."-".$NumControle,1,0,'L',0);
}

$db->disconnect();
$pdf->Output();
?>
