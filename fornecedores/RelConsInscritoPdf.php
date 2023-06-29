<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelConsInscritoPdf.php
# Autor:    Roberta Costa
# Data:     09/09/04
# Objetivo: Programa de Impressão da Consulta do Inscrito
# Alterado: Rossana Lira
# Data:     18/05/07 - Exibição da Comissão responsável pela análise e data
#           29/05/2007 - Receber novos campos (índice Endividamento e Microempresa ou EPP)
# Alterado: Carlos Abreu
# Data:     18/06/2007 - Receber novo campo (índice de solvência)
# Alterado: Ariston
# Data:     09/08/2010	- Adicionado opção para incluir sócios
# Autor:    Everton Lino
# Data:     30/08/2010
# Alterado: Rodrigo Melo
# Data:     07/06/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
# Alterado: Rodrigo Melo
# Data:     07/06/2011 - Tarefa Redmine: 2727 - Incluir novos campos para preenchimento no SICREF - Dados do Representante Legal
# Alterado: Rodrigo Melo
# Data:     16/09/2011 - Tarefa Redmine: 3718 - Remoção de campos de Representante Legal no módulo de fornecedores (SICREF)
#----------------------------------
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------
# Alterado: Ernesto Ferreira
# Data:		05/11/2018
# Objetivo: Tarefa Redmine 201709
# -----------------------------------------------------------------------------------------------------------------------------------------------



# Acesso ao arquivo de funções #
include "../funcoes.php";
require_once( "funcoesDocumento.php");

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Sequencial = $_GET['Sequencial'];
		$anoAnexacao = $_GET['anoAnexacao'];
//		$Mensagem   = urldecode($_GET['Mensagem']);
 		$Mensagem   =  $_GET['Mensagem'];
		
		if ($Mensagem <> "") {
			$Mensagem  =  "ATENÇÃO! ".$Mensagem;
		}
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório do Fornecedor Inscrito";

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

# Pega os Dados do Inscrito Cadastrado #
$db	  = Conexao();
$sql  = " SELECT APREFOSEQU, APREFOCCGC, APREFOCCPF, APREFOIDEN, NPREFOORGU, "; // 5
$sql .= "        NPREFORAZS, NPREFOFANT, CCEPPOCODI, CCELOCCODI, EPREFOLOGR, "; // 10
$sql .= "        APREFONUME, EPREFOCOMP, EPREFOBAIR, NPREFOCIDA, CPREFOESTA, "; // 15
$sql .= "        APREFOCDDD, APREFOTELS, APREFONFAX, NPREFOMAIL, APREFOCPFC, "; // 20
$sql .= "        NPREFOCONT, NPREFOCARG, APREFODDDC, APREFOTELC, APREFOREGJ, "; // 25
$sql .= "        DPREFOREGJ, APREFOINES, APREFOINME, APREFOINSM, VPREFOCAPS, "; // 30
$sql .= "        VPREFOCAPI, VPREFOPATL, VPREFOINLC, VPREFOINLG, DPREFOULTB, "; // 35
$sql .= "        DPREFOCNFC, NPREFOENTP, APREFOENTR, DPREFOVIGE, APREFOENTT, "; // 40
$sql .= "        DPREFOGERA, ECOMLIDESC, DPREFOANAL, FPREFOMEPP, VPREFOINDI, "; // 45
$sql .= "        VPREFOINSO, DPREFOCONT "; // 47
$sql .= "   FROM SFPC.TBPREFORNECEDOR PRE ";
$sql .= "   LEFT OUTER JOIN SFPC.TBCOMISSAOLICITACAO COM ON PRE.CCOMLICODI = COM.CCOMLICODI ";
$sql .= "  WHERE APREFOSEQU = $Sequencial ";
$result = $db->query($sql);
if( PEAR::isError($result) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$Linha = $result->fetchRow();

		# Variáveis Formulário A #
		$Sequencial		    = $Linha[0];
		$CNPJ							= $Linha[1];
		$CPF							= $Linha[2];
		if( $CNPJ != 0 ){
				$CPF_CNPJ     = $CNPJ;
				$DescCNPJCPF  = "CNPJ";
				$CNPJCPFForm	= FormataCNPJ($CNPJ);
				$MicroEmpresa = $Linha[43];
		}else{
				$CPF_CNPJ     = $CPF;
				$DescCNPJCPF  = "CPF";
				$CNPJCPFForm  = FormataCPF($CPF);
		}
		if( $Linha[3] != "" ){ $Identidade = $Linha[3]; }else{ $Identidade = "NÃO INFORMADO"; }
		if( $Linha[4] != "" ){ $OrgaoEmissorUF=$Linha[4]; }else{ $OrgaoEmissorUF= "NÃO INFORMADO"; }
		$RazaoSocial = $Linha[5];
		if( $Linha[6] != "" ){ $NomeFantasia = $Linha[6]; }else{ $NomeFantasia = "NÃO INFORMADO"; }
		if( $Linha[7] != "" ){
				$CEP = $Linha[7];
		}else{
				$CEP = $Linha[8];
		}
		$Logradouro = substr($Linha[9],0,60);
		if( $Linha[10] != "" ){ $Numero = $Linha[10]; }else{ $Numero = "NÃO INFORMADO"; }
		if( $Linha[11] != "" ){ $Complemento = $Linha[11]; }else{ $Complemento = "NÃO INFORMADO"; }
		$Bairro   	 			= $Linha[12];
		$Cidade 					= $Linha[13];
		$UF       				= $Linha[14];
		if( $Linha[15] != "" ){ $DDD = $Linha[15]; }else{ $DDD = "NÃO INFORMADO"; }
		if( $Linha[16] != "" ){ $Telefone = $Linha[16]; }else{ $Telefone = "NÃO INFORMADO"; }
		if( $Linha[17] != "" ){ $Fax = $Linha[17]; }else{ $Fax = "NÃO INFORMADO"; }
		if( $Linha[18] != "" ){ $Email = $Linha[18]; }else{ $Email = "NÃO INFORMADO"; }
		if( $Linha[19] != "" ){ $CPFContato = FormataCPF($Linha[19]); }else{ $CPFContato = "NÃO INFORMADO"; }
		if( $Linha[20] != "" ){ $NomeContato = $Linha[20]; }else{ $NomeContato = "NÃO INFORMADO"; }
		if( $Linha[21] != "" ){ $CargoContato = $Linha[21]; }else{ $CargoContato = "NÃO INFORMADO"; }
		if( $Linha[22] != "" ){ $DDDContato = $Linha[22]; }else{ $DDDContato = "NÃO INFORMADO"; }
		if( $Linha[23] != "" ){ $TelefoneContato = $Linha[23]; }else{ $TelefoneContato = "NÃO INFORMADO"; }
		$RegistroJunta		= $Linha[24];
		$DataRegistro			= DataBarra($Linha[25]);

		# Variáveis Formulário B #
		if( $Linha[26] != "" ){ $InscEstadual = $Linha[26]; }else{ $InscEstadual = "NÃO INFORMADO"; }
		if( $Linha[27] != "" ){ $InscMercantil = $Linha[27]; }else{ $InscMercantil = "-"; }
		if( $Linha[28] != "" ){ $InscOMunic	= $Linha[28]; }else{ $InscOMunic = "-"; }

		# Variáveis Formulário C #
		$CapSocial = converte_valor($Linha[29]);
		if( $Linha[30] != "" ){ $CapIntegralizado = converte_valor($Linha[30]); }else{ $CapIntegralizado = "NÃO INFORMADO"; }
		$Patrimonio = converte_valor($Linha[31]);
		if( $Linha[32] != "" ){ $IndLiqCorrente = converte_valor($Linha[32]); }else{ $IndLiqCorrente = "NÃO INFORMADO"; }
		if( $Linha[33] != "" ){ $IndLiqGeral = converte_valor($Linha[33]); }else{ $IndLiqGeral = "NÃO INFORMADO"; }
		if( $Linha[44] != "" ){ $IndEndividamento = converte_valor($Linha[44]); }else{ $IndEndividamento = "NÃO INFORMADO"; }
		if( $Linha[45] != "" ){ $IndSolvencia = converte_valor($Linha[45]); }else{ $IndSolvencia = "NÃO INFORMADO"; }
		if( $Linha[34] != "" ){
				$DataUltBalanco	= DataBarra($Linha[34]);
		}else{
				$DataUltBalanco = "NÃO INFORMADO";
		}
		if( $Linha[35] != "" ){
				$DataCertidaoNeg	= DataBarra($Linha[35]);
		}else{
				$DataCertidaoNeg = "NÃO INFORMADO";
		}
		if( $Linha[46] != "" ){
				$DataContratoEstatuto	= DataBarra($Linha[46]);
		}else{
				$DataContratoEstatuto = "NÃO INFORMADO";
		}
		# Variáveis Formulário D #
		if( $Linha[36] != "" ){ $NomeEntidade = $Linha[36]; }else{ $NomeEntidade = "NÃO INFORMADO"; }
		if( $Linha[37] != "" ){ $RegistroEntidade = $Linha[37]; }else{ $RegistroEntidade = "NÃO INFORMADO"; }
		if( $Linha[38] != "" ){
				$DataVigencia	= DataBarra($Linha[38]);
		}else{
				$DataVigencia = "NÃO INFORMADO";
		}
		if( $Linha[39] != "" ){ $TecnicoEntidade = $Linha[39]; }else{ $TecnicoEntidade = "NÃO INFORMADO"; }
		$DataInscricao  = DataBarra($Linha[40]);
		$ComissaoResp	  = $Linha[41];
		if( $Linha[42] <> "" ){
				$DataAnaliseDoc   = substr($Linha[42],8,2)."/".substr($Linha[42],5,2)."/".substr($Linha[42],0,4);
		}

		# Pega os Dados da Tabela de Situação #
		$db	    = Conexao();
		$sql    = "SELECT A.CPREFSCODI, A.EPREFOMOTI, B.EPREFSDESC ";
		$sql   .= "  FROM SFPC.TBPREFORNECEDOR A, SFPC.TBPREFORNTIPOSITUACAO B";
		$sql   .= " WHERE A.CPREFSCODI = B.CPREFSCODI AND A.APREFOSEQU = $Sequencial ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$situacao     = $result->fetchRow();
				$Situacao     = $situacao[0];
				$Motivo       = $situacao[1];
				$DescSituacao = $situacao[2];
		}

		# Busca os Dados da Tabela de Conta Bancária de acordo com o sequencial do Fornecedor #
		$sql    = "SELECT CPRECOBANC, CPRECOAGEN, CPRECOCONT, TPRECOULAT ";
		$sql   .= "  FROM SFPC.TBPREFORNCONTABANCARIA ";
		$sql   .= " WHERE APREFOSEQU = $Sequencial ";
		$sql   .= " ORDER BY TPRECOULAT";
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
		$pdf->Cell(60,5,'Código da Inscrição',1,0,'L',1);
		$pdf->Cell(130,5,$Sequencial,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Cumprimento Inc.XXXIII Art.7º Cons.Fed.',1,0,'L',1);
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
		$pdf->Cell(60,5,'Data Cadastramento',1,0,'L',1);
		$pdf->Cell(130,5,$DataInscricao,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Comissão Responsável Análise',1,0,'L',1);
		$pdf->Cell(130,5,$ComissaoResp,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Data de Análise Documentação',1,0,'L',1);
		$pdf->Cell(130,5,$DataAnaliseDoc,1,0,'L',0);
		$pdf->ln(5);

		# HABILITAÇÃO JURÍDICA #
		$pdf->SetFont("Arial","B",9);
		$pdf->Cell(190,5,'HABILITAÇÃO JURÍDICA',1,0,'C',1);
		$pdf->SetFont("Arial","",9);
		$pdf->ln(5);
		$pdf->Cell(60,5,$DescCNPJCPF,1,0,'L',1);
		$pdf->Cell(130,5,$CNPJCPFForm,1,0,'L',0);
		$pdf->ln(5);
		if( $CNPJ != 0 ){
				$pdf->Cell(60,5,'Microempresa ou Emp.Pequeno Porte',1,0,'L',1);
				if ( $MicroEmpresa == "S") {
						$pdf->Cell(130,5,'SIM',1,0,'L',0);
				} else {
						$pdf->Cell(130,5,'NÃO',1,0,'L',0);
				}
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
		$pdf->Cell(130,5,$RegistroJunta,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(60,5,'Data Reg. Junta Comercial ou Cartório',1,0,'L',1);
		$pdf->Cell(130,5,$DataRegistro,1,0,'L',0);
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
					asoprecada, nsoprenome
				FROM SFPC.TBsocioprefornecedor
				WHERE aprefosequ = ".$Sequencial."
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
				$sqlData  = "SELECT DPREFCVALI FROM SFPC.TBPREFORNCERTIDAO ";
				$sqlData .= " WHERE APREFOSEQU = $Sequencial AND CTIPCECODI = $CertidaoOb";
				$resData = $db->query($sqlData);
				if( PEAR::isError($resData) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
				
					$LinhaData = $resData->fetchRow();

					if( $LinhaData[0] != 0 ){
						$DataCertidaoOb[$ob-1] = substr($LinhaData[0],8,2)."/".substr($LinhaData[0],5,2)."/".substr($LinhaData[0],0,4);
						$pdf->Cell(155,5,strtoupper2($DescricaoOb),1,0,'L',0);
						$pdf->Cell(35,5,$DataCertidaoOb[$ob-1],1,0,'C',0);
						$pdf->ln(5);
					}else{
						$DataCertidaoOb[$ob-1] = null;
						$pdf->Cell(155,5,strtoupper2($DescricaoOb),1,0,'L',0);
						$pdf->Cell(35,5,$DataCertidaoOb[$ob-1],1,0,'C',0);
						$pdf->ln(5);
					}



			}

      	}
  	}
		$pdf->Cell(190,5,'COMPLEMENTARES',1,0,'C',0);
		$pdf->ln(5);
		# Verifica se existem certidões complementares cadastradas para o Fornecedor #
		$sql  = "SELECT A.DPREFCVALI, B.CTIPCECODI, B.ETIPCEDESC  ";
		$sql .= "  FROM SFPC.TBPREFORNCERTIDAO A, SFPC.TBTIPOCERTIDAO B ";
		$sql .= " WHERE A.APREFOSEQU = $Sequencial AND A.CTIPCECODI = B.CTIPCECODI";
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
		if( $CNPJ != 0 ){
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
		if( $Banco1 == "" and $Banco2 == ""){
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
		$sql  = "SELECT APREFANUMA, NPREFANOMA, DPREFAVIGE FROM SFPC.TBPREFORNAUTORIZACAOESPECIFICA ";
		$sql .= " WHERE APREFOSEQU = $Sequencial";
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
		# Mostra os Grupos de materiais já cadastrados do Fornecedor #
		$sql  = "SELECT A.APREFOSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
		$sql .= "  FROM SFPC.TBGRUPOPREFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B  ";
		$sql .= " WHERE A.APREFOSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
		$sql .= "   AND B.FGRUMSTIPO = 'M' ORDER BY 1,3";
		$res = $db->query($sql);
	  if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				if( $Rows != 0 ){
  					# Mostra os Grupos de materiais cadastrados #
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
		$sql  = "SELECT A.APREFOSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
		$sql .= "  FROM SFPC.TBGRUPOPREFORNECEDOR A, SFPC.TBGRUPOMATERIALSERVICO B ";
		$sql .= " WHERE A.APREFOSEQU = $Sequencial AND A.CGRUMSCODI = B.CGRUMSCODI ";
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
		   WHERE aprefosequ = " . $Sequencial . " AND ffdocusitu = 'A' 
		   ".$txtAnexacao ." order by tfdoctulat, doc.cfdocusequ asc" ;

   
   



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
		$pdf->Cell(40,5,'ANEXO ',"LTR",0,'C',1);
		$pdf->Cell(30,5,'RESPONSÁVEL ',"LTR",0,'C',1);
		$pdf->Cell(26,5,'DATA/HORA',"LTR",0,'L',1);
		$pdf->Cell(24,5,'SITUAÇÃO ',"LTR",0,'L',1);
		$pdf->Cell(30,5,'OBSERVAÇÃO',"LTR",0,'L',1);
		$pdf->ln(5);

		$pdf->Cell(40,5,'DO DOCUMENTO',"LR",0,'L',1);
		$pdf->Cell(40,5,' ',"LR",0,'L',1);
		$pdf->Cell(30,5,'ANEXAÇÃO',"LR",0,'C',1);
		$pdf->Cell(26,5,'ANEXAÇÃO',"LR",0,'L',1);
		$pdf->Cell(24,5,'',"LR",0,'L',1);
		$pdf->Cell(30,5,'',"LR",0,'L',1);
		$pdf->ln(5);


		while ($linha = $resultDoc->fetchRow()) {
	

			//verifica se quem cadastrou foi PCR ou o próprio fornecedor
			$nomeUsuAnex = '';

			if($linha[7] == 'S'){
				$nomeUsuAnex = $CNPJCPFForm;
			}else{
				$nomeUsuAnex = $linha[18];
			}

			//|| strlen($linha[5]) > 19 || strlen($linha[13]) > 10
			$pdf->Cell(40,5, substr($linha[14],0,20) ,"LTR",0,'L',0);
			$pdf->Cell(40,5,substr(strtoupper2($linha[5]),0,16),"LTR",0,'L',0);
			$pdf->Cell(30,5,substr($nomeUsuAnex,0,15),"LTR",0,'C',0);
			$pdf->Cell(26,5,substr(formatarDataHora($linha[8]),0,10),"LTR",0,'C',0);
			$pdf->Cell(24,5,substr($linha[19],0,20),"LTR",0,'C',0);
			$pdf->Cell(30,5,substr($linha[13],0,13),"LTR",0,'L',0);

			$pdf->ln(5);

			$pdf->Cell(40,5, substr($linha[14],20,20) ,"LR",0,'L',0);
			$pdf->Cell(40,5,substr(strtoupper2($linha[5]),16,16),"LR",0,'L',0);
			$pdf->Cell(30,5,substr($nomeUsuAnex,15,15),"LR",0,'C',0);
			$pdf->Cell(26,5,substr(formatarDataHora($linha[8]),11,20),"LR",0,'C',0);
			$pdf->Cell(24,5,substr($linha[19],20,20),"LR",0,'C',0);
			$pdf->Cell(30,5,substr($linha[13],13,10),"LR",0,'L',0);

			$pdf->ln(5);

			$pdf->Cell(40,5,substr($linha[14],40,20),"LR",0,'L',0);
			$pdf->Cell(40,5,substr(strtoupper2($linha[5]),32,16),"LR",0,'L',0);
			$pdf->Cell(30,5,substr($nomeUsuAnex,30,15),"LR",0,'C',0);
			$pdf->Cell(26,5,' ',"LR",0,'C',0);
			$pdf->Cell(24,5,substr($linha[19],40,20),"LR",0,'C',0);
			$pdf->Cell(30,5,substr($linha[13],23,10),"LR",0,'L',0);

			$pdf->ln(5);
	
		   }
		}
	}
// -----------------------------------------------------

$pdf->Cell(60,5,"Número de Controle",1,0,'L',1);
$pdf->Cell(130,5,"1".$NumeroCont."-".$NumControle,1,0,'L',0);
}

$db->disconnect();
$pdf->Output();
?>
