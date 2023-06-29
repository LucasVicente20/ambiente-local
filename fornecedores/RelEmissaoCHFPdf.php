<?php
#--------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelEmissaoCHFPdf.php
# Autor:    Roberta Costa
# Data:     19/10/04
# Objetivo: Programa de Impressão do Certificado de Habilitação de Firmas
# Alterado: Rossana Lira
# Data:     16/05/07 - Troca do nome fornecedor para firma
# Data:     09/07/07 - Exibir mensagem de fornecedor c/certidões fora do
#           prazo de validade
# Alterado: Rodrigo Melo
# Data:     25/04/2011 - Exibir a certidão de falência, no final de certidões obrigatórias, devido a solicitação do usuário. Tarefa Redmine: 2205.
#                      - Retirar exibição de classes de fornecedores (manter apenas o grupo), devido a solicitação do usuário. Tarefa Redmine: 2205.
# Alterado: Rodrigo Melo
# Data:     02/06/2011 - Tarefa Redmine: 2203 - Fornecedores devem estar associados a grupos de materiais
#                      - Alteração do nome do arquivo de "CadIncluirClasses.php" para "CadIncluirGrupos.php"
#--------------------------------------------------------------------------------
# Alterado: Ernesto Ferreira
# Data:     19/02/2019 
# Objetivo: Tarefa Redmine 211438
#--------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";
include "funcoesDocumento.php";

# Sempre vai buscar o programa no servidor #
header("Expires: 0");
header("Cache-Control: private");

# Executa o controle de segurança #
session_cache_limiter('private');
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){
		$Sequencial = $_GET['Sequencial'];
		$Mensagem   = urldecode($_GET['Mensagem']);
		if ($Mensagem <> "") {
			$Mensagem  = "Atenção! ".$Mensagem;
		}
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodapeBG();

# Informa o Título do Relatório #
$TituloRelatorio = "CERTIFICADO DE HABILITAÇÃO DE FIRMAS - CHF";

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

# Pega os Dados do Fornecedor Cadastrado #
$db	  = Conexao();
$sql  = " SELECT CRE.AFORCRSEQU, CRE.APREFOSEQU, CRE.AFORCRCCGC, CRE.AFORCRCCPF, CRE.AFORCRIDEN, ";
$sql .= "        CRE.NFORCRORGU, CRE.NFORCRRAZS, CRE.NFORCRFANT, CRE.CCEPPOCODI, CRE.CCELOCCODI, ";
$sql .= "        CRE.EFORCRLOGR, CRE.AFORCRNUME, CRE.EFORCRCOMP, CRE.EFORCRBAIR, CRE.NFORCRCIDA, ";
$sql .= "        CRE.CFORCRESTA, CRE.AFORCRCDDD, CRE.AFORCRTELS, CRE.AFORCRNFAX, CRE.NFORCRMAIL, ";
$sql .= "        CRE.AFORCRCPFC, CRE.NFORCRCONT, CRE.NFORCRCARG, CRE.AFORCRDDDC, CRE.AFORCRTELC, ";
$sql .= "        CRE.AFORCRREGJ, CRE.DFORCRREGJ, CRE.AFORCRINES, CRE.AFORCRINME, CRE.AFORCRINSM, ";
$sql .= "        CRE.VFORCRCAPS, CRE.VFORCRCAPI, CRE.VFORCRPATL, CRE.VFORCRINLC, CRE.VFORCRINLG, ";
$sql .= "        CRE.DFORCRULTB, CRE.DFORCRCNFC, CRE.NFORCRENTP, CRE.AFORCRENTR, CRE.DFORCRVIGE, ";
$sql .= "        CRE.DFORCRGERA, CRE.FFORCRCUMP, CRE.CGREMPCODI, CRE.CUSUPOCODI, PRE.DPREFOGERA ";
$sql .= "   FROM SFPC.TBFORNECEDORCREDENCIADO AS CRE";
$sql .= "   LEFT JOIN  SFPC.TBPREFORNECEDOR AS PRE ON PRE.APREFOSEQU = CRE.APREFOSEQU ";
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
		$Identidade				= $Linha[4];
		$OrgaoUF 				  = $Linha[5];
		if( $CNPJ != 0 ){
				$CPF_CNPJ       = $CNPJ;
				$DescCNPJCPF    = "CNPJ";
				$CNPJCPFForm	  = FormataCNPJ($CNPJ);
				$TipoIdentidade = "Identidade Repr.Legal";
				$Tipo           = 2; //TipoFornecedor
		}elseif( $CPF != 0 ){
				$CPF_CNPJ       = $CPF;
				$DescCNPJCPF    = "CPF";
				$CNPJCPFForm    = FormataCPF($CPF);
				$TipoIdentidade = "Identidade";
				$Tipo           = 1; //TipoFornecedor
		}
		$RazaoSocial  		= $Linha[6];
		if( $Linha[7] != "" ){ $NomeFantasia = $Linha[7]; }else{ $NomeFantasia = "NÃO INFORMADO"; }
		if( $Linha[8] != "" ){
				$CEP = $Linha[8];
		}else{
				$CEP = $Linha[9];
		}
		$Logradouro 			= $Linha[10];
		$Numero           = $Linha[11];
		$Complemento      = $Linha[12];
		$Bairro   	 			= $Linha[13];
		$Cidade 					= $Linha[14];
		$UF       				= $Linha[15];
		if( $Linha[16] != "" ){ $DDD = $Linha[16]; }else{ $DDD = "NÃO INFORMADO"; }
		if( $Linha[17] != "" ){ $Telefone = $Linha[17]; }else{ $Telefone = "NÃO INFORMADO"; }
		if( $Linha[18] != "" ){ $Fax = $Linha[18]; }else{ $Fax = "NÃO INFORMADO"; }
		if( $Linha[19] != "" ){ $Email = $Linha[19]; }else{ $Email = "NÃO INFORMADO"; }
		if( $Linha[20] != "" ){ $CPFContato = FormataCPF($Linha[20]); }else{ $CPFContato = "NÃO INFORMADO"; }
		if( $Linha[21] != "" ){ $NomeContato = $Linha[21]; }else{ $NomeContato = "NÃO INFORMADO"; }
		if( $Linha[22] != "" ){ $CargoContato = $Linha[22]; }else{ $CargoContato = "NÃO INFORMADO"; }
		if( $Linha[23] != "" ){ $DDDContato = $Linha[23]; }else{ $DDDContato = "NÃO INFORMADO"; }
		if( $Linha[24] != "" ){ $TelefoneContato = $Linha[24]; }else{ $TelefoneContato = "NÃO INFORMADO"; }
		$RegistroJunta = $Linha[25];
		$DataRegistro	 = DataBarra($Linha[26]);

		# Colocando o Endereço Agrupado #
		if( $Numero == ""){ $Numero = "S/N"; }
		if( $Complemento != "" ){
				$Endereco = $Logradouro.", ".$Numero." ".$Compemento;
		}else{
				$Endereco = $Logradouro.", ".$Numero;
		}

		# Variáveis Formulário B #
		if( $Linha[27] != "" ){ $InscEstadual = $Linha[27]; }else{ $InscEstadual = "NÃO INFORMADO"; }
		if( $Linha[28] != "" ){ $InscMercantil = $Linha[28]; }else{ $InscEstadual = "-"; }
		if( $Linha[29] != "" ){ $InscOMunic = $Linha[29]; }else{ $InscOMunic = "-"; }

		# Variáveis Formulário C #
		if( $Linha[30] != "" ){ $CapSocial =  converte_valor($Linha[30]); }else{ $CapSocial = "NÃO INFORMADO"; }
		if( $Linha[31] != "" ){ $CapIntegralizado = converte_valor($Linha[31]); }else{ $CapIntegralizado = "NÃO INFORMADO"; }
		if( $Linha[32] != "" ){ $Patrimonio = converte_valor($Linha[32]); }else{ $Patrimonio = "NÃO INFORMADO"; }
		if( $Linha[33] != "" ){ $IndLiqCorrente = converte_valor($Linha[33]); }else{ $IndLiqCorrente = "NÃO INFORMADO"; }
		if( $Linha[34] != "" ){ $IndLiqGeral = converte_valor($Linha[34]); }else{ $IndLiqGeral = "NÃO INFORMADO"; }
		if( $Linha[35] != "" ){ $DataBalanco = DataBarra($Linha[35]); }else{ $DataBalanco = "NÃO INFORMADO"; }
		if( $Linha[36] != "" ){ $DataNegativa = DataBarra($Linha[36]); }else{ $DataNegativa = "NÃO INFORMADO"; }

		# Variáveis Formulário D #
		if( $Linha[37] != "" ){ $NomeEntidade = $Linha[37]; }else{ $NomeEntidade = "NÃO INFORMADO"; }
		if( $Linha[38] != "" ){ $RegistroEntidade = $Linha[38]; }else{ $RegistroEntidade = "NÃO INFORMADO"; }
		if( $Linha[39] != "" ){ $DataVigencia	= DataBarra($Linha[39]); }else{ $DataVigencia = "NÃO INFORMADO"; }
		$DataInscricao = DataBarra($Linha[40]);
		$Cumprimento	 = $Linha[41];

		# Data de inscrição no SICREF #
		if( $Linha[44] != "" ){ 
			$Linha[44] = $Linha[44] . ' 12:10:30';
			$DataInscSicref = substr($Linha[44], 8, 2).'/'.substr($Linha[44], 5, 2).'/'.substr($Linha[44], 0, 4);
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
						$Linha = $result->fetchRow();
						if( $Linha[0] != "" ){ $DataSituacao = DataBarra($Linha[0]); }else{ $DataSituacao  = ""; }
						$Situacao			 = $Linha[1];
						$Motivo				 = strtoupper2($Linha[2]);
						if( $Linha[3] != "" ){ $DataSuspensao = DataBarra($Linha[3]); }else{ $DataSuspensao = ""; }
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
		$sql    = "SELECT DFORCHGERA, DFORCHVALI FROM SFPC.TBFORNECEDORCHF ";
		$sql   .= " WHERE AFORCRSEQU = $Sequencial ";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $result->numRows();
				if( $Rows != 0 ){
						$Linha        	 = $result->fetchRow();
						$DataGeracaoCHF  = DataBarra($Linha[0]);
						if( $Linha[1] != "" ){ $DataValidadeCHF = DataBarra($Linha[1]); }else{ $DataValidadeCHF = ""; }
				}else{
						$DataGeracaoCHF = "";
				}
		}

		# CHF #

		# Imprime mensagem de
		if( $Mensagem != "" ){
    		$pdf->SetFont("Arial","B",9);
				$pdf->MultiCell(190,5,$Mensagem,0,'L',0);
				$pdf->SetFont("Arial","",9);
		}
		$pdf->ln(03);
		$pdf->Cell(35,5,$DescCNPJCPF,1,0,'L',1);
		$pdf->Cell(35,5,$CNPJCPFForm,1,0,'L',0);
		$pdf->Cell(35,5,'Código do Fornecedor',1,0,'L',1);
		$pdf->Cell(50,5,$Sequencial,1,0,'L',0);
		$pdf->Cell(15,5,'Validade',1,0,'L',1);
    $pdf->SetFont("Arial","B",9);
		$pdf->Cell(20,5,$DataValidadeCHF,1,0,'L',0);
		$pdf->SetFont("Arial","",9);
		$pdf->ln(10);

		# Fornecedor #
		if( $Identidade != "" ){
				$pdf->Cell(35,5,$TipoIdentidade,1,0,'L',1);
				$pdf->Cell(60,5,$Identidade,1,0,'L',0);
				$pdf->Cell(35,5,'Orgão/UF',1,0,'L',1);
				$pdf->Cell(60,5,$OrgaoUF,1,1,'L',0);
		}
		$pdf->Cell(35,5,'Razão Social/Nome',1,0,'L',1);
		$pdf->Cell(155,5,strtoupper2($RazaoSocial),1,0,'L',0);
		$pdf->ln(5);
		if( strlen($Endereco) < 91 ){
				$Linha = 5;
		}elseif( strlen($Endereco) >= 91 and  strlen($Endereco) < 182 ){
				$Linha = 10;
		}else{
				$Linha = 15;
		}
		$pdf->Cell(35,$Linha,'Endereço',1,0,'L',1);
		$pdf->MultiCell(155,5,$Endereco,1,'L',0);
		$pdf->Cell(35,5,'Bairro',1,0,'L',1);
		$pdf->Cell(120,5,strtoupper2($Bairro),1,0,'L',0);
		$pdf->Cell(15,5,'CEP',1,0,'L',1);
		$pdf->Cell(20,5,substr($CEP,0,2).".".substr($CEP,2,3)."-".substr($CEP,5,3),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(35,5,'Cidade',1,0,'L',1);
		$pdf->Cell(120,5,strtoupper2($Cidade),1,0,'L',0);
		$pdf->Cell(15,5,'UF',1,0,'L',1);
		$pdf->Cell(20,5,strtoupper2($UF),1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(35,5,'Telefone(s)',1,0,'L',1);
		if( $DDD != "NÃO INFORMADO" ){
				$Fone = "(".$DDD.") ".strtoupper2($Telefone);
		}else{
				$Fone = strtoupper2($Telefone);
		}
		$pdf->Cell(155,5,$Fone,1,0,'L',0);
		$pdf->ln(5);
		if( $CNPJ != "" ){
				$pdf->Cell(35,5,'Capital Social',1,0,'L',1);
				$pdf->Cell(60,5,$CapSocial,1,0,'L',0);
				$pdf->Cell(35,5,'Patrimônio Líquido',1,0,'L',1);
				$pdf->Cell(60,5,$Patrimonio,1,1,'L',0);
				$pdf->Cell(35,5,'Validade do balanço',1,0,'L',1);
				$pdf->Cell(155,5,$DataBalanco,1,0,'L',0);
				$pdf->ln(5);
		}
		
		$pdf->Cell(35,5,'Inscrição do SICREF',1,0,'L',1);
		$pdf->Cell(155,5,$DataInscSicref,1,0,'L',0);
		$pdf->ln(5);

		$pdf->Cell(35,5,'E-mail',1,0,'L',1);
		$pdf->Cell(155,5,$Email,1,0,'L',0);
		$pdf->ln(5);
			
		# Contato #
		$pdf->Cell(190,5,'CONTATO',1,0,'C',1);
		$pdf->ln(5);
		$pdf->Cell(35,5,'Nome do Contato',1,0,'L',1);
		$pdf->Cell(60,5,substr(strtoupper2($NomeContato),0,30),1,0,'L',0);
		$pdf->Cell(35,5,'CPF do Contato',1,0,'L',1);
		$pdf->Cell(60,5,$CPFContato,1,0,'L',0);
		$pdf->ln(5);
		$pdf->Cell(35,5,'Cargo do Contato',1,0,'L',1);
		$pdf->Cell(60,5,strtoupper2($CargoContato),1,0,'L',0);
		$pdf->Cell(35,5,'Telefone do Contato',1,0,'L',1);
		if( $DDDContato != "NÃO INFORMADO" ){
				$FoneContato = "(".$DDDContato.")".strtoupper2($TelefoneContato);
		}else{
				$FoneContato = strtoupper2($TelefoneContato);
		}
		$pdf->Cell(60,5,$FoneContato,1,0,'L',0);
		$pdf->ln(10);


		#Alteração para exibir apenas os grupos de fornecimento de materias e serviços dos fornecedores, conforme solicitação do usuário
		# - Tarefas do Redmine: 2205 e 2203

		# Grupos de Fornecimento #
		$pdf->Cell(190,5,'GRUPOS DE FORNECIMENTO (OBJETO SOCIAL)',1,0,'C',1);
		$pdf->ln(5);

		# Mostra os grupos de materiais já cadastrados do Fornecedor #
		$sql  = "SELECT DISTINCT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
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
					$pdf->Cell(190,5,'MATERIAIS',1,0,'C',1);
					$pdf->ln(5);

        		for( $i=0; $i<$Rows;$i++ ){
					$Linha							= $res->fetchRow();
  	      			$DescricaoGrupo   				= substr($Linha[2],0,75);

        			$pdf->Cell(190,5,strtoupper2($DescricaoGrupo),1,0,'L',0);
					$pdf->ln(5);
	      		}
      	}
  	}

		# Mostra os grupos de serviços já cadastrados do Fornecedor #
		$sql  = "SELECT DISTINCT A.AFORCRSEQU, B.CGRUMSCODI, B.EGRUMSDESC ";
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
				$pdf->Cell(190,5,'SERVIÇOS',1,0,'C',1);
				$pdf->ln(5);

	        	for( $i=0; $i<$Rows;$i++ ){
							$Linha = $res->fetchRow();
	      			$DescricaoGrupo   = substr($Linha[2],0,75);

					$pdf->Cell(190,5,strtoupper2($DescricaoGrupo),1,0,'L',0);
					$pdf->ln(5);
	        	}
    		}
    	}

		$pdf->ln(5);

		# Certidões Fiscal #
		$pdf->Cell(190,5,'CERTIDÃO FISCAL',1,0,'C',1);
		$pdf->ln(5);
		$pdf->Cell(190,5,'OBRIGATÓRIA',1,0,'C',1);
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
										$DataCertidaoOb[$ob-1] = null;
              	}
            }
						$pdf->Cell(155,5,strtoupper2($DescricaoOb),1,0,'L',0);
						$pdf->Cell(35,5,$DataCertidaoOb[$ob-1],1,0,'C',0);
						$pdf->ln(5);
      	}

      	#Exibição da certidão de falência e concordata, no final de certidões obrigatórias. Tarefa Redmine: 2205
      	$pdf->Cell(155,5,'CERTIDÃO NEGATIVA DE FALÊNCIA OU CONCORDATA',1,0,'L',0);
		$pdf->Cell(35,5,$DataNegativa,1,0,'C',0);
		$pdf->ln(5);
  	}
		$pdf->Cell(190,5,'COMPLEMENTAR',1,0,'C',1);
		$pdf->ln(5);

		# Verifica se existem Certidões Complementares cadastradas para o Fornecedor #
		$sql  = "SELECT A.DFORCEVALI, B.CTIPCECODI, B.ETIPCEDESC  ";
		$sql .= "  FROM SFPC.TBFORNECEDORCERTIDAO A, SFPC.TBTIPOCERTIDAO B ";
		$sql .= " WHERE A.AFORCRSEQU = $Sequencial AND A.CTIPCECODI = B.CTIPCECODI";
		$sql .= "   AND B.FTIPCEOBRI = 'N' ORDER BY 1";
		$res = $db->query($sql);
	  if( PEAR::isError($res) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Rows = $res->numRows();
				if( $Rows != 0 ){
    				# Mostra as certidões Complementares cadastradas #
        		for( $i=0; $i<$Rows;$i++ ){
								$Linha = $res->fetchRow();
  	      			$DescricaoOp					= substr($Linha[2],0,75);
  	      			$CertidaoOpCodigo			= $Linha[1];
  	      			$CertidaoOpcional[$i] = $Linha[1];
								$DataCertidaoOp[$i]		= substr($Linha[0],8,2)."/".substr($Linha[0],5,2)."/".substr($Linha[0],0,4);
        				if( $i == 0 ){
										$pdf->Cell(155,5,'NOME DA CERTIDÃO',1,0,'L',1);
										$pdf->Cell(35,5,'DATA DE VALIDADE',1,0,'C',1);
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
		//$pdf->ln(5);

	//----------------------------DOCUMENTOS---------------------


	$db = Conexao();
	$anoAnexacao = date(Y);

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
		//$pdf->ln(5); 
		//$pdf->Cell(190,5,'ANO DA ANEXAÇÃO: '.$anoAnexacao,1,0,'L',0);
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
	//-----------------------------------------------------------



	$pdf->MultiCell(190,0.05,'',1,'L',0);
	$pdf->ln(5);
		# Gera o Número de Controle do Fornecedor #
		$Numero      = $Sequencial.$CPF_CNPJ.date("Ymd");
		$NumControle = ControlaDocumento($Numero);

	  # Certificação #
	  $Frase  = "ESTE CERTIFICADO NECESSITA DE DOCUMENTAÇÃO COMPLEMENTAR CONFORME EDITAL. ";
	  $Frase .= "EMITIDO DE ACORDO COM A LEI 8.666/93.\nEMITIDO EM ".date("d/m/Y")."";
	  $Frase .= "(Nº CONTROLE ".$Tipo.$Numero."-".$NumControle.") \n";
	  $pdf->MultiCell(190,5,$Frase,1,'L',0);
		$pdf->ln(5);

}

$db->disconnect();
$pdf->Output();
?>
